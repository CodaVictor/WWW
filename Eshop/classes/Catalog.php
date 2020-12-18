<?php

class Catalog
{
    const PRODUCT_ID = 'product_id';
    const PRODUCT_CODE = 'product_code';

    const ORDER_BY_PRICE = 'price';
    const ORDER_BY_NAME = 'product_name';
    const ORDER_BY_ID = 'product_id';

    const ORDER_RULE_DESC = 'DESC'; // Sestupně
    const ORDER_RULE_ASC = 'ASC'; // Vzestupně

    /**
     * @param int $limit - default 10
     * @param int $offset - default 0
     * @param Catalog $orderBy - default ORDER_BY_ID
     * @param Catalog $orderRule - default ORDER_RULE_ASC
     * @return Catalog_item | null | bool (chyba)
     */
    public static function getAllItems($limit = 10, $offset = 0, $orderBy = self::ORDER_BY_ID, $orderRule = self::ORDER_RULE_ASC) : array {
        if(!is_numeric($limit) OR !is_numeric($offset)) {
            return false;
        }

        $conn = Connection::getPdoInstance();

        $query = 'SELECT *  FROM products ORDER BY ';
        $query .= self::checkOrderBy($orderBy) . ' ';
        $query .= self::checkOrderRule($orderRule) . ' ';
        $query .= 'LIMIT :inLimit OFFSET :inOffset';

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':inLimit', $limit,PDO::PARAM_INT);
        $stmt->bindParam(':inOffset', $offset,PDO::PARAM_INT);

        if(!$stmt->execute()) {
            return false;
        }
        $stmt->setFetchMode(PDO::FETCH_CLASS, "Catalog_item");
        $data = $stmt->fetchAll();
        return $data;
    }

    /**
     * @param $itemIdentifierType
     * @param $valueIdentifier
     * @return array | null | bool (chyba)
     */
    public static function getItemByValue($itemIdentifierType, $identifierVal) {
        $conn = Connection::getPdoInstance();

        $stmt = NULL;
        switch ($itemIdentifierType) {
            case self::PRODUCT_ID:
                $stmt = $conn->prepare("SELECT *  FROM products WHERE product_id = :inItemIdentifierVal");
                $stmt->bindParam(':inItemIdentifierVal', $identifierVal, PDO::PARAM_INT);
                break;
            case self::PRODUCT_CODE:
                $stmt = $conn->prepare("SELECT *  FROM products WHERE product_code = :inItemIdentifierVal");
                $stmt->bindParam(':inItemIdentifierVal', $identifierVal);
                break;
            default:
                return false;
        }

        // Chyba zpracování dotazu?
        if(!$stmt->execute()) {
            return false;
        }
        $stmt->setFetchMode(PDO::FETCH_CLASS, "Catalog_item");
        $data = $stmt->fetch();
        return $data;
    }

    public static function deleteItemByValue($itemIdentifierType, $identifierVal) {
        $conn = Connection::getPdoInstance();

        $stmt = NULL;
        switch ($itemIdentifierType) {
            case self::PRODUCT_ID:
                $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :inItemIdentifierVal");
                $stmt->bindParam(':inItemIdentifierVal', $identifierVal, PDO::PARAM_INT);
                break;
            case self::PRODUCT_CODE:
                $stmt = $conn->prepare("DELETE FROM products WHERE product_code = :inItemIdentifierVal");
                $stmt->bindParam(':inItemIdentifierVal', $identifierVal);
                break;
            default:
                return false;
        }
        return $stmt->execute();
    }

    public static function addItem($product_name, $product_code, $price, $number_in_stock, $imagePath, $specs) {
        if(!is_numeric($price) OR !is_numeric($number_in_stock)) {
            return false;
        }

        $conn = Connection::getPdoInstance();

        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_code = :inProductCode");
        $stmtCheck->bindParam(':inProductCode', $product_code);

        if(!$stmtCheck->execute()) {
            return false;
        }
        $result = $stmtCheck->fetch();

        // Není už položka se stejným product_code či catalog_code v databázi?
        if(intval($result[0]) == 0) {
            $stmt = $conn->prepare("INSERT INTO products (product_name, product_code, price, number_in_stock, listed_date, imagePath, specs) 
                                    VALUE (:inProductName, :inProductCode, :inPrice, :inNumberInStock, UTC_TIMESTAMP(), :inImagePath, :inSpecs)");
            $stmt->bindParam(':inProductName', $product_name);
            $stmt->bindParam(':inProductCode', $product_code);
            $stmt->bindValue(':inPrice', $price, PDO::PARAM_STR);
            $stmt->bindValue(':inNumberInStock', $number_in_stock, PDO::PARAM_INT);
            $stmt->bindValue(':inImagePath', $imagePath);
            $stmt->bindValue(':inSpecs', $specs);
            return $stmt->execute();
        } else {
            return false;
        }
    }

    public static function updateItem($product_id, $product_name, $product_code, $price, $imagePath, $specs) {
        if(!is_numeric($price) OR !is_numeric($product_id)) {
            return false;
        }

        $conn = Connection::getPdoInstance();

        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_id = :inProductId");
        $stmtCheck->bindParam(':inProductCode', $product_id);

        // chyba?
        if(!$stmtCheck->execute()) {
            return false;
        }
        $result = $stmtCheck->fetch();

        // kontrola + update
        if(intval($result[0]) == 1) {
            $stmt = $conn->prepare("UPDATE products SET product_name = :inProductName, product_code = :inProductCode, price = :inPrice, 
                                    imagePath = :inImagePath, specs = :inSpecs WHERE product_id = :inProductId");
            $stmt->bindParam(':inProductName', $product_name);
            $stmt->bindParam(':inProductCode', $product_code);
            $stmt->bindValue(':inPrice', $price, PDO::PARAM_STR);
            $stmt->bindValue(':inImagePath', $imagePath);
            $stmt->bindValue(':inSpecs', $specs);
            $stmt->bindValue(':inProductId', $product_id);
            return $stmt->execute();
        } else {
            return false;
        }
    }

    // Přičítá/odčítá počet kusů produktu na skladě a přitom kontroluje, zda nejde jejich skladový
    // počet do záporu. Pokud jde, tak vrací maximální možný počet, který se dá odečíst
    /**
     * @param $itemIdentifierType
     * @param $valueIdentifier
     * @param $change int
     * @return int | bool (chyba)
     */
    public function updateStock($itemIdentifierType, $identifierVal, int $change) {
        if(!is_numeric($change)) {
            return false;
        }

        $conn = Connection::getPdoInstance();

        // Kontrola se provede jen v případě hodnoty změny menší než nula (nesmí dojít k zápornému stavu skladu)
        if($change < 0) {
            $stmtCheck = NULL;
            switch ($itemIdentifierType) {
                case self::PRODUCT_ID:
                    $stmtCheck = $conn->prepare("SELECT number_in_stock  FROM products WHERE product_id = :inItemIdentifierVal");
                    $stmtCheck->bindParam(':inItemIdentifierVal', $identifierVal, PDO::PARAM_INT);
                    break;
                case self::PRODUCT_CODE:
                    $stmtCheck = $conn->prepare("SELECT number_in_stock  FROM products WHERE product_code = :inItemIdentifierVal");
                    $stmtCheck->bindParam(':inItemIdentifierVal', $identifierVal);
                    break;
                default:
                    return false;
            }

            // chyba?
            if (!$stmtCheck->execute()) {
                return false;
            }
            $resultStockNumber = $stmtCheck->fetch();

            // Pokud bude nová hodnota skladu větší nebo rovna nule, mohu provést update
            // Jinak vracím maximum, které lze odečíst
            if(intval($resultStockNumber[0]) - $change >= 0) {
                $stmt = NULL;
                switch ($itemIdentifierType) {
                    case self::PRODUCT_ID:
                        $stmt = $conn->prepare("UPDATE products SET number_in_stock = :inNumberInStock WHERE product_id = :inProductId");
                        $stmt->bindParam(':inItemIdentifierVal', $identifierVal, PDO::PARAM_INT);
                        break;
                    case self::PRODUCT_CODE:
                        $stmt = $conn->prepare("UPDATE products SET number_in_stock = :inNumberInStock WHERE product_code = :inProductCode");
                        $stmt->bindParam(':inProductCode', $identifierVal);
                        break;
                    default:
                        return false;
                }
                return $stmt->execute();
            } else {
                return intval($resultStockNumber[0]);
            }
        }
    }

    private function checkOrderBy($option) {
        switch ($option) {
            case self::ORDER_BY_ID:
                return self::ORDER_BY_ID;
            case self::ORDER_BY_PRICE:
                return self::ORDER_BY_PRICE;
            case self::ORDER_BY_NAME:
                return self::ORDER_BY_NAME;
            default:
                return self::ORDER_BY_ID;
        }
    }

    private function checkOrderRule($rule) {
        switch ($rule) {
            case self::ORDER_RULE_DESC:
                return self::ORDER_RULE_DESC;
            case self::ORDER_RULE_ASC:
                return self::ORDER_RULE_ASC;
            default:
                return self::ORDER_RULE_ASC;
        }
    }
}
