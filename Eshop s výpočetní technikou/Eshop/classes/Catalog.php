<?php

class Catalog
{
    //const PRODUCT_ID = 'product_id';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_NAME = 'product_name';
    const PRODUCT_CODE = 'product_code';
    const PRODUCT_PRICE = 'price';

    const ORDER_RULE_DESC = 'DESC'; // Sestupně
    const ORDER_RULE_ASC = 'ASC'; // Vzestupně

    const CATEGORY_NONE = '*';

    /**
     * @param int $limit - default 10
     * @param int $offset - default 0
     * @param Catalog $orderBy - default PRODUCT_ID
     * @param Catalog $orderRule - default ORDER_RULE_ASC
     * @return array | bool (chyba)
     */
    public static function getAllItems($categoryId = 1, $limit = 30, $offset = 0, $orderBy = self::PRODUCT_ID, $orderRule = self::ORDER_RULE_ASC) {
        if(!is_numeric($limit) OR !is_numeric($offset)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $query = 'SELECT *  FROM products ';
        if($categoryId != '*') { // Jiná než obecná kategorie
            $query .= 'WHERE categories_category_id=:inCategoryId ';
        }
        $query .= 'ORDER BY ';
        $query .= self::checkOrderBy($orderBy) . ' ';
        $query .= self::checkOrderRule($orderRule) . ' ';
        $query .= 'LIMIT :inLimit OFFSET :inOffset';

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':inLimit', $limit,PDO::PARAM_INT);
        $stmt->bindParam(':inOffset', $offset,PDO::PARAM_INT);
        if($categoryId != '*') {
            $stmt->bindParam(':inCategoryId', $categoryId, PDO::PARAM_INT);
        }

        try {
            if(!$stmt->execute()) {
                return false;
            } else {
                //$stmt->setFetchMode(PDO::FETCH_CLASS, "Catalog_item");
                return $stmt->fetchAll();
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    // return = array {product_id, product_name, product_code, price, number_in_stock, listed_date, image_path, specs, categories_category_id, category_name}
    public static function getAllItemsEx($categoryId = 1, $limit = 30, $offset = 0, $orderBy = self::PRODUCT_ID, $orderRule = self::ORDER_RULE_ASC) {
        if(!is_numeric($limit) OR !is_numeric($offset)) {
            return false;
        }

        $conn = Connection::getPdoInstance();

        $query = 'SELECT products.*, categories.category_name FROM products INNER JOIN categories ON 
          products.categories_category_id=categories.category_id ';
        if($categoryId != '*') { // Jiná než obecná kategorie
            $query .= 'WHERE categories_category_id=:inCategoryId ';
        }
        $query .= 'ORDER BY ';
        $query .= self::checkOrderBy($orderBy) . ' ';
        $query .= self::checkOrderRule($orderRule) . ' ';
        $query .= 'LIMIT :inLimit OFFSET :inOffset';

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':inLimit', $limit,PDO::PARAM_INT);
        $stmt->bindParam(':inOffset', $offset,PDO::PARAM_INT);
        if($categoryId != '*') {
            $stmt->bindParam(':inCategoryId', $categoryId);
        }

        try {
            if(!$stmt->execute()) {
                return false;
            } else {
                //$stmt->setFetchMode(PDO::FETCH_CLASS, "Catalog_item");
                return $stmt->fetchAll();
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    /**
     * @param $itemIdentifierType
     * @param $valueIdentifier
     * @return array | null | bool (chyba)
     */
    public static function getItemByValue($itemIdentifierType, $identifierVal) {
        $conn = Connection::getPdoInstance();

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
        try {
            if($stmt->execute()) {
                return $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return $exception->getMessage();
        }
    }

    // return = {product_id, product_name, product_code, price, number_in_stock, listed_date, image_path, specs, categories_category_id, category_name}
    public static function getItemByValueEx($itemIdentifierType, $identifierVal) {
        $conn = Connection::getPdoInstance();

        switch ($itemIdentifierType) {
            case self::PRODUCT_ID:
                $stmt = $conn->prepare('SELECT products.*, categories.category_name FROM products INNER JOIN categories ON 
                  products.categories_category_id=categories.category_id WHERE product_id = :inItemIdentifierVal');
                $stmt->bindParam(':inItemIdentifierVal', $identifierVal, PDO::PARAM_INT);
                break;
            case self::PRODUCT_CODE:
                $stmt = $conn->prepare('SELECT products.*, categories.category_name FROM products INNER JOIN categories ON 
                  products.categories_category_id=categories.category_id WHERE product_code = :inItemIdentifierVal');
                $stmt->bindParam(':inItemIdentifierVal', $identifierVal);
                break;
            default:
                return false;
        }

        // Chyba zpracování dotazu?
        try {
            if($stmt->execute()) {
                return $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
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

    public static function addItem($product_name, $product_code, $price, $number_in_stock, $imagePath, $specs, $category_id) {
        if(!is_numeric($price) OR !is_numeric($number_in_stock) OR !is_numeric($category_id)) {
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
            $stmt = $conn->prepare("INSERT INTO products (product_name, product_code, price, number_in_stock, listed_date, image_path, specs, categories_category_id) 
                                    VALUE (:inProductName, :inProductCode, :inPrice, :inNumberInStock, UTC_TIMESTAMP(), :inImagePath, :inSpecs, :inCategoryId)");
            $stmt->bindParam(':inProductName', $product_name);
            $stmt->bindParam(':inProductCode', $product_code);
            $stmt->bindValue(':inPrice', $price, PDO::PARAM_STR);
            $stmt->bindValue(':inNumberInStock', $number_in_stock, PDO::PARAM_INT);
            $stmt->bindValue(':inImagePath', $imagePath);
            $stmt->bindValue(':inSpecs', $specs);
            $stmt->bindValue(':inCategoryId', $category_id, PDO::PARAM_INT);

            try {
                if($stmt->execute()) {
                   return true;
                } else {
                    return false;
                }
            } catch (PDOException $exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    // Pokud je $imagePath = NULL, nebude se tento atribut aktualizovat
    public static function updateItem($product_id, $product_name, $product_code, $price, $number_in_stock, $imagePath, $specs, $category_id) {
        if(!is_numeric($product_id) OR !is_numeric($price) OR !is_numeric($number_in_stock) OR !is_numeric($category_id)) {
            return false;
        }

        $conn = Connection::getPdoInstance();

        // Kontrola
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_id = :inProductId");
        $stmtCheck->bindParam(':inProductId', $product_id);

        try {
            if($stmtCheck->execute()) {
                $result = $stmtCheck->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }

        // Kontrola, zda souhlasí ID + update
        if(intval($result[0]) == 1) {
            if(is_null($imagePath)) { // Pokud je imagePath NULL, tak ho needitovat
                $stmt = $conn->prepare('UPDATE products SET product_name = :inProductName, product_code = :inProductCode, price = :inPrice, number_in_stock = :inNumberInStock,  
                  specs = :inSpecs, categories_category_id = :inCategoryId WHERE product_id = :inProductId');
            } else {
                $stmt = $conn->prepare('UPDATE products SET product_name = :inProductName, product_code = :inProductCode, price = :inPrice, image_path = :inImagePath,
                  number_in_stock = :inNumberInStock, specs = :inSpecs, categories_category_id = :inCategoryId WHERE product_id = :inProductId');
            }
            $stmt->bindParam(':inProductName', $product_name);
            $stmt->bindParam(':inProductCode', $product_code);
            $stmt->bindValue(':inPrice', $price, PDO::PARAM_STR);
            $stmt->bindValue(':inNumberInStock', $number_in_stock, PDO::PARAM_INT);
            $stmt->bindValue(':inSpecs', $specs);
            $stmt->bindValue(':inCategoryId', $category_id, PDO::PARAM_INT);
            $stmt->bindValue(':inProductId', $product_id, PDO::PARAM_INT);
            if(!is_null($imagePath)) {
                $stmt->bindValue(':inImagePath', $imagePath);
            }

            try {
                if($stmt->execute()) {
                    return true;
                } else {
                    return false;
                }
            } catch (PDOException $exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    // Přičítá/odčítá počet kusů produktu na skladě a přitom kontroluje, zda nejde jejich skladový
    // počet do záporu. Pokud jde, tak vrací maximální možný počet, který se dá odečíst
    public static function stockItemRemain($productCode) {
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare("SELECT number_in_stock FROM products WHERE product_code = :inProductCode");
        $stmt->bindParam(':inProductCode', $productCode);

        try {
            if($stmt->execute()) {
                return intval($stmt->fetch()[0]);
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    /*
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
    */

    public static function getProductIdFromDataSet($dataSet) : array {
        $idArray = array();
        foreach ($dataSet as $row) {
            $idArray[] = $row['product_id'];
        }
        return $idArray;
    }

    public static function addItemReview($userId, $productId, $review) {
        if(!is_numeric($userId) || !is_numeric($productId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare("INSERT INTO reviews (text, users_user_id, products_product_id) VALUES (:inReview, :inUserId, :inProductId)");
        $stmt->bindParam(':inReview', $review);
        $stmt->bindParam(':inUserId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':inProductId', $productId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getItemReviews($productId) {
        if(!is_numeric($productId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare("SELECT reviews.text, CONCAT(users.first_name, ' ',users.last_name) AS user_name FROM reviews 
            LEFT JOIN users ON reviews.users_user_id = users.user_id WHERE products_product_id = :inProductId");
        $stmt->bindParam(':inProductId', $productId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return $stmt->fetchAll();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function hasUserReviewedItem($userId, $productId) {
        if(!is_numeric($userId) ||  !is_numeric($productId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE users_user_id = :inUserId AND products_product_id = :inProductId");
        $stmt->bindParam(':inUserId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':inProductId', $productId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                $result = $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }

        if(intval($result[0]) == 1) {
            return true;
        } else {
            return false;
        }
    }

    private function checkOrderBy($option) {
        switch ($option) {
            case self::PRODUCT_ID:
                return self::PRODUCT_ID;
            case self::PRODUCT_PRICE:
                return self::PRODUCT_PRICE;
            case self::PRODUCT_NAME:
                return self::PRODUCT_NAME;
            default:
                return self::PRODUCT_ID;
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
