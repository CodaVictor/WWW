<!-- využívá soubor CSS orders.css -->
<div class="generalContainer">
    <?php
    // Na stránce je definována globální proměnná $_SESSION['orderManDetId']
    // Reakce na POST
    if(!empty($_POST)) {
        if(isset($_POST['btnOrderDetailMan']) || isset($_POST['btnOrderDetailUser'])) {
            $_SESSION['orderManDetId'] = $_POST['orderId'];
        } else if(isset($_POST['btnOrderStatusSave'])) {
            if(Order::setOrderStatus($_POST['hiddenOrderId'], $_POST['selectOrderStatus'])) {
                $_SESSION['successMessage'] = 'Stav objednávky byl změněn.';
            } else {
                $_SESSION['errorMessage'][] = 'Stav objednávky se nepodařilo změnit.';
            }
        }

        // Formulář ----------------------------------------------------------------------------------------------------
        $orderData = Order::getOrderData($_SESSION['orderManDetId']);
        $orderItems = Order::getOrderItems($_SESSION['orderManDetId']);
        $orderPrice = $orderData['sum_price'];
        $orderPriceVat = $orderData['sum_price'] * VAT_MULTIPLIER;

        // Nadpis
        echo '<div class="generalHeader1">Objednávka: ' . $orderData['order_id'] .
            (!empty($orderData['custom_order_id']) ? ' [' . $orderData['custom_order_id'] . ']' : '') .
        '</div>';

        // Výpis hlášek
        if(isset($_SESSION['errorMessage']) || isset($_SESSION['successMessage'])) {
            echo '<div style="margin-bottom: 15px">';
                foreach ($_SESSION['errorMessage'] as $errorMsgItem) {
                    echo $errorMsgItem . " ";
                }
                unset($_SESSION['errorMessage']);
                echo $_SESSION['successMessage'];
                unset($_SESSION['successMessage']);
            echo '</div>';
        }

        // Info
        echo '<div style="margin-bottom: 15px">
            <div style="font-weight: bold">Celková částka bez : ' . round($orderData['sum_price']) . ' Kč' . '</div>' .
            '<div style="font-size: 19px; font-weight: bold; margin-bottom: 10px">Celková částka s DPH: ' . round($orderData['sum_price'] * VAT_MULTIPLIER) . ' Kč' . '</div>' .
            '<div>Jméno objednavatele: ' . $orderData['user_name'] . '</div>' .
            '<div>Datum objednání: ' . date("d. m. Y H:i:s", strtotime($orderData['order_date'])) .  '</div>' .
            '<div>Datum odeslání: ' . (!empty($orderData['shipped_date']) ? date("d. m. Y H:i:s", strtotime($orderData['shipped_date'])) : 'Neodesláno') .'</div>';

            // Objednací adresa
            $addressData = Account::getAddress($orderData['addresses_address_id']);
            echo '<div style="margin-top: 10px">Město: ' . $addressData['city'] . '</div>';
            echo '<div>Ulice a č. p.: ' . $addressData['street'] . '</div>';
            echo '<div>PSČ: ' . $addressData['zip_code'] . '</div>';

            // Stav
            echo '<div style="font-weight: bold; margin-top: 10px;">Stav objednávky: ' . ORDER_STATUS[intval($orderData['order_status'])] . '</div>';
        echo '</div>';

        // Změna stavu (jen pro zaměstnance nebo admina)
        if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'zamestnanec') {
            echo '<div>Změna stavu</div>';
            echo '<form method="post" action="?page=order-man-det" class="order-form-component" style="display: contents">';
                echo '<select name="selectOrderStatus" class="userInputField order-form-component">';
                for ($i = 0; $i < count(ORDER_STATUS); $i++) {
                    echo '<option value="' . $i . '"' . (intval($orderData['order_status']) == $i ? 'selected' : '') . '>' . ORDER_STATUS[$i] . '</option>';
                }
                echo '</select>';
                echo '<input type="hidden" name="hiddenOrderId" value="' . $orderData['order_id'] . '">';
                echo '<input type="submit" name="btnOrderStatusSave" value="Změnit" class="generalButton1 order-form-component">';
            echo '</form>';
        }

        // Výpis produktů
        echo '<div class="generalHeader2">Položky objednávky</div>';
        foreach($orderItems as $item) {
            echo '<div class="cart-row-grid">';
            echo '<div class="cart-item-image-container">';
            if((is_null($item['image_path']) or empty($item['image_path'])) or
                !file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/eshop/" . $item['image_path'])) {
                echo "<a href='#'><img src='./images/eshop/blank.png' alt='img' class='cart-item-image'></a>";
            } else {
                echo "<a href='#'><img src='./images/eshop/" . $item['image_path'] . "' alt='". $item['product_name'] . "' class='cart-item-image'></a>";
            }
            echo  '</div>';

            echo '
                <div>
                <div class="cart-item-name">' . $item['product_name'] . '</div>' .
                $item['product_code'] .
            '</div>';
            echo '<div>' . $item['quantity'] . '</div>';
            echo '<div>' . round($item['price'] * VAT_MULTIPLIER) . ' Kč/kus' . '</div>';
            echo '<div class="cart-item-sumprice">' . round($item['price'] * VAT_MULTIPLIER * $item['quantity']) . ' Kč' . '</div>';
            echo '</div>';
        }

        // Výpis dopravy
        echo '<div class="generalHeader2">Doprava</div>';
        echo '
            <div class="order-row-grid-payship">' .
            '<div>' . $orderData['delivery_name'] . '</div>' .
            '<div>' . round(Cart::getPriceWithVat($orderData['delivery_price'])) . ' Kč'. '</div>' .
            '</div>'
        ;

        // Výpis platby
        echo '<div class="generalHeader2">Platba</div>';
        echo '
            <div class="order-row-grid-payship">' .
            '<div>' . $orderData['payment_name'] . '</div>' .
            '<div>' . round(Cart::getPriceWithVat($orderData['payment_price'])) . ' Kč'. '</div>' .
            '</div>'
        ;
    } else {
        header("Location:" . BASE_URL);
    }
    ?>
</div>
