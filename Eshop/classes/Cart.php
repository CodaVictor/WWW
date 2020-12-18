<?php

class Cart
{
    public static function isItemInCart($productCode) {
        return array_key_exists($productCode, $_SESSION['cart']);
    }

    public static function addItem($productCode, $amount = 1) {
        $_SESSION['cart'][$productCode] += $amount;
    }

    public static function removeItem($productCode, $amount = 1) {
        // Položka musí být v košíku a počet odebíraných položek větší než nula
        if(self::isItemInCart($productCode) AND $amount > 0) {
            if($_SESSION['cart'][$productCode] - amount > 1) {
                $_SESSION['cart'][$productCode] -= $amount;
            } else {
                // Číselný index pole se rozbije, přebudování by se provedlo "$_SESSION['cart'] = array_values($_SESSION['cart'])"
                unset($_SESSION['cart'][$productCode]);
                //$_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        } else {
            false;
        }
    }

    public static function getItemsCount() {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item;
        }
        return $count;
    }

    public static function removeAllItems($productCode) {
        $_SESSION['cart'] = array();
    }

    public static function getAllItems() {
        return $_SESSION['cart'];
    }

    // return = {product_name, product_code, price, image_path, amount}
    public static function getAllItemsData() {
        if(count($_SESSION['cart']) == 0) {
            return NULL;
        } else {
            $conn = Connection::getPdoInstance();

            // Načtení dat z databáze na základě položek v košíku (identifikace dle product_code)
            $qInputTemplate = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
            $query = "SELECT product_name, product_code, price, image_path FROM products WHERE product_code IN ($qInputTemplate)";
            $stmt = $conn->prepare($query);

            if(!$stmt->execute(array_keys($_SESSION['cart']))) {
                return false;
            }
            $data = $stmt->fetchAll();
            //===============================================

            // Doplnění jednotlivých produktů o ceny s daní a počet
            $returnData = array();
            foreach ($data as $item) {
                $item['price'] = self::roundPrice($item['price']);
                $item['amount'] = $_SESSION['cart'][$item['product_code']];
                $item['price_vat'] = self::roundPrice(self::getPriceWithVat($item['price']));
                $item['price_sum'] = self::roundPrice($item['amount'] * $item['price']);
                $item['price_vat_sum'] = self::roundPrice($item['amount'] * $item['price_vat']);
                $returnData[] = $item;
            }
            return $returnData;
        }
    }

    public static function getPriceWithVat($price) {
        return $price * VAT_MULTIPLIER;
    }

    public static function roundPrice($price) {
        return round($price);
    }
}













