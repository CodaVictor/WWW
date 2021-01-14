<?php


class Order
{
    public static function addOrderToDb($customOrderId = NULL, $note = NULL) {
        $conn = Connection::getPdoInstance();

        // Načtení address_id (type='F'; F[fakturační] / D[doručovací]) uživatele
        $stmt2 = $conn->prepare('SELECT addresses.address_id FROM users INNER JOIN addresses ON users.user_id=addresses.users_user_id 
              WHERE users.user_id=:inUserId AND addresses.type=:inAddressType');
        $paramAddressType = 'F';
        $stmt2->bindParam(':inUserId', $_SESSION["userId"]);
        $stmt2->bindParam(':inAddressType', $paramAddressType);
        try {
            if(!$stmt2->execute()) {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
        $paramAddressId = $stmt2->fetch();

        // OBJEDNÁVKA (vložení do DB) ====================================================================
        $stmt = $conn->prepare('INSERT INTO orders (order_status, order_date, sum_price, custom_order_id, note, users_user_id, 
        delivery_types_delivery_id, payment_payment_id, addresses_address_id) VALUES (:inOrderStatus, UTC_TIMESTAMP(), :inSumPrice, 
        :inCustomOrderId, :inNote, :inUserId, :inDeliveryId, :inPaymentId, :inAddressId)');

        $paramStatus = 1; // Nezaplaceno

        // Výpočet částky
        $deliveryPrice = Cart::getDeliveryDataById($_SESSION['cartOrderPayShip']['deliveryId'])['price'];
        $paymentPrice = Cart::getPaymentDataById($_SESSION['cartOrderPayShip']['paymentId'])['price'];
        $paramSumPrice = ((Cart::getCartPriceSum()['price']) + $deliveryPrice + $paymentPrice);

        $stmt->bindParam('inOrderStatus', $paramStatus);
        $stmt->bindParam('inSumPrice', $paramSumPrice, PDO::PARAM_STR); // PDO::PARAM_STR se používá i pro decimal
        $stmt->bindParam('inCustomOrderId', $customOrderId);
        $stmt->bindParam('inNote', $note);
        $stmt->bindParam('inUserId', $_SESSION['userId'], PDO::PARAM_INT);
        $stmt->bindParam('inDeliveryId', $_SESSION['cartOrderPayShip']['deliveryId'], PDO::PARAM_INT);
        $stmt->bindParam('inPaymentId', $_SESSION['cartOrderPayShip']['paymentId'], PDO::PARAM_INT);
        $stmt->bindParam('inAddressId', $paramAddressId, PDO::PARAM_INT);
        //return $stmt->execute(); // RETURN!
        try {
            if(!$stmt->execute()) {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
        // ================================================================================================
        // POLOŽKY OBJEDNÁVKY (vložení do DB) ===========================
        // Dohledání product_id a ceny dle product_code
        $partIn = str_repeat('?,', count(array_keys($_SESSION['cart'])) - 1) . '?';
        $query = "SELECT product_code, product_id, price FROM products WHERE product_code IN ($partIn)";
        $stmt2 = $conn->prepare($query);
        $paramArray = array_keys($_SESSION['cart']);

        try {
            if(!$stmt2->execute($paramArray)) {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
        $paramProduct = $stmt2->fetchAll(PDO::FETCH_UNIQUE);

        // Dohledání poslední vložené objednávky dle uživatele ==============================================
        $stmt3 = $conn->prepare('SELECT order_id FROM orders WHERE users_user_id = :inUserId1 AND order_date = (SELECT MAX(order_date) FROM orders WHERE users_user_id = :inUserId2)');
        $stmt3->bindParam(':inUserId1', $_SESSION['userId']);
        $stmt3->bindParam(':inUserId2', $_SESSION['userId']);
        $stmt3->execute();
        $paramOrderId = $stmt3->fetch()[0];

        // Vkládání jednotlivých položek objednávky =======================
        foreach (array_keys($_SESSION['cart']) AS $itemProdKey) {
            $itemStmt = $conn->prepare('INSERT INTO order_items (quantity, price, orders_order_id, products_product_id) 
              VALUES (:inQuantity, :inPrice, :inOrderId, :inProductId)');
            $itemStmt->bindParam(':inQuantity', $_SESSION['cart'][$itemProdKey]);
            $itemStmt->bindParam(':inPrice', $paramProduct[$itemProdKey]['price']);
            $itemStmt->bindParam(':inOrderId', $paramOrderId);
            $itemStmt->bindParam(':inProductId', $paramProduct[$itemProdKey]['product_id']);

            try {
                if(!$itemStmt->execute()) {
                    return false;
                }
            } catch (PDOException $exception) {
                return false;
            }

            // Odebrání počtu kusů produktu ze skladu
            $newStockValue = (Catalog::stockItemRemain($itemProdKey) - $_SESSION['cart'][$itemProdKey]);
            $stockStmt = $conn->prepare('UPDATE products SET number_in_stock = :inNewStockValue WHERE product_code = :inProductCode');
            $stockStmt->bindParam(':inNewStockValue', $newStockValue, PDO::PARAM_INT);
            $stockStmt->bindParam(':inProductCode', $itemProdKey);

            try {
                if(!$stockStmt->execute()) {
                    return false;
                }
            } catch (PDOException $exception) {
                return false;
            }
        }
    }

    public static function getOrderCount($userId) {
        if(!is_numeric($userId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('SELECT COUNT(*) FROM orders WHERE users_user_id = :inUserId');
        $stmt->bindParam(':inUserId', $userId);

        try {
            if(!$stmt->execute()) {
                return false;
            } else {
                return $stmt->fetch()[0];
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getOrdersByUser($userId) {
        if(!is_numeric($userId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare("SELECT order_id, order_status, custom_order_id, 
            CONVERT_TZ(order_date, '+00:00', '+01:00') AS order_date, CONVERT_TZ(shipped_date, '+00:00', '+01:00') 
            AS shipped_date, sum_price, note FROM orders 
            WHERE users_user_id=:inUserId ORDER BY order_date DESC");
        $stmt->bindParam(':inUserId', $userId);

        try {
            if(!$stmt->execute()) {
                return false;
            } else {
                return $stmt->fetchAll();
            }
        } catch (PDOException $PDOException) {
            return false;
        }
    }

    public static function getOrderData($orderId) {
        if(!is_numeric($orderId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare("SELECT order_id, order_status, CONVERT_TZ(orders.order_date, '+00:00', '+01:00') AS order_date, 
            CONVERT_TZ(orders.shipped_date, '+00:00', '+01:00') AS shipped_date, sum_price, custom_order_id, orders.note, 
            orders.users_user_id, CONCAT(users.first_name, ' ',users.last_name) AS user_name, delivery_types.delivery_name,
            delivery_types.price AS delivery_price, payment.payment_name, payment.price AS payment_price, addresses_address_id
            FROM orders 
            LEFT JOIN delivery_types ON orders.delivery_types_delivery_id = delivery_types.delivery_id
            LEFT JOIN payment ON orders.payment_payment_id = payment.payment_id
            LEFT JOIN users ON orders.users_user_id = users.user_id
            WHERE order_id=:inOrderId");
        $stmt->bindParam(':inOrderId', $orderId);
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

    public static function getAllOrders($limit = 30, $offset = 0) {
        if(!is_numeric($limit) || !is_numeric($offset)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare("SELECT orders.order_id, orders.order_status, 
            CONVERT_TZ(orders.order_date, '+00:00', '+01:00') AS order_date, CONVERT_TZ(orders.shipped_date, '+00:00', '+01:00') 
            AS shipped_date, orders.sum_price, orders.custom_order_id, orders.note, orders.users_user_id, 
            orders.delivery_types_delivery_id, orders.payment_payment_id, orders.addresses_address_id, CONCAT(users.first_name, ' ',users.last_name) AS user_name 
            FROM orders LEFT JOIN users ON orders.users_user_id = users.user_id 
            ORDER BY order_id DESC LIMIT :inLimit OFFSET :inOffset");
        $stmt->bindParam(':inLimit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':inOffset', $offset, PDO::PARAM_INT);

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

    // return = array {product_id, product_name, product_code, image_path, price(objednací), quantity}
    public static function getOrderItems($orderId) {
        if(!is_numeric($orderId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('SELECT products.product_id, products.product_name, products.product_code, products.image_path, 
            order_items.price, order_items.quantity FROM order_items LEFT JOIN products ON order_items.products_product_id = products.product_id 
            WHERE order_items.orders_order_id = :inOrderId');
        $stmt->bindParam(':inOrderId', $orderId, PDO::PARAM_INT);

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

    public static function setOrderStatus($orderId, $status) {
        if(!is_numeric($orderId) || !is_numeric($status)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $query = 'UPDATE orders SET order_status = :inOrderStatus';
        if($status == 3) {
            $query .= ', shipped_date = UTC_TIMESTAMP()';
        }
        $query .= ' WHERE order_id = :inOrderId';

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':inOrderStatus', $status);
        $stmt->bindParam(':inOrderId', $orderId, PDO::PARAM_INT);

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
}