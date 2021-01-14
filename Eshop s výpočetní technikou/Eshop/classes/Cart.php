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
                return true;
            } else {
                // Číselný index pole se rozbije, přebudování by se provedlo "$_SESSION['cart'] = array_values($_SESSION['cart'])"
                unset($_SESSION['cart'][$productCode]);
                return true;
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

    // return = 2D array {product_name, product_code, price, price_sum, image_path, amount}
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
                $item['amount'] = $_SESSION['cart'][$item['product_code']];
                $item['price_sum'] = $item['amount'] * $item['price'];
                $returnData[] = $item;
            }
            return $returnData;
        }
    }

    // return = {price, price_vat}
    // reálné ceny
    public static function getCartPriceSum($data = NULL) : array {
        if($data == NULL) {
            $data = self::getAllItemsData();
        }
        $arrPrices = array('price'=>0, 'price_vat'=>0);
        foreach ($data as $item) {
            $arrPrices['price'] += $item['price_sum'];
            $arrPrices['price_vat'] += self::getPriceWithVat($item['price_sum']);
        }
        //$arrPrices['price_vat'] = self::getPriceWithVat($arrPrices['price']);
        return $arrPrices;
    }

    public static function getPriceWithVat($price) {
        return $price * VAT_MULTIPLIER;
    }

    public static function getPriceWithoutVat($price) {
        return $price / VAT_MULTIPLIER;
    }

    public static function roundPrice($price) {
        return round($price);
    }

    // return = {delivery_id, delivery_name, price, note}
    public static function getAllDeliveryTypes() {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT * FROM delivery_types');
        if(!$stmt->execute()) {
            return false;
        }
        return $stmt->fetchAll();
    }

    // return = {delivery_id, delivery_name, price, note}
    public static function getDeliveryDataById($deliveryId) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT * FROM delivery_types WHERE delivery_id = :inDeliveryId');
        $stmt->bindParam(':inDeliveryId', $deliveryId, PDO::PARAM_INT);
        if(!$stmt->execute()) {
            return false;
        }
        return $stmt->fetch();
    }

    // return = {payment_id, payment_name, price, note}
    public static function getAllPaymentTypes() {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT * FROM payment');
        if(!$stmt->execute()) {
            return false;
        }
        return $stmt->fetchAll();
    }

    // return = {payment_id, payment_name, price, note}
    public static function getPaymentDataById($paymentId) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT * FROM payment WHERE payment_id = :inPaymentId');
        $stmt->bindParam(':inPaymentId', $paymentId, PDO::PARAM_INT);
        if(!$stmt->execute()) {
            return false;
        }
        return $stmt->fetch();
    }

    public static function setDeliveryType($deliveryId) {
        $_SESSION['cartOrderPayShip']['deliveryId'] = $deliveryId;
    }

    public static function setPaymentType($paymentId) {
        $_SESSION['cartOrderPayShip']['paymentId'] = $paymentId;
    }
}













