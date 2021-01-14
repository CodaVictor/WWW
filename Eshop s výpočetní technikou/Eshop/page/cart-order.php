<?php
// Obsluha POST
if(!empty($_POST)) {
    // Proklik přes stránku card-pay-ship
    if (isset($_POST['btnCartOrder'])){
        if(isset($_POST['radioBtnDelivery']) AND isset($_POST['radioBtnPayment'])) {
            Cart::setDeliveryType($_POST['radioBtnDelivery']); // $_SESSION['cartOrderPayShip']['deliveryId']
            Cart::setPaymentType($_POST['radioBtnPayment']); // $_SESSION['cartOrderPayShip']['paymentId']
        }
    // Lokální tlačítko, potvrdit objednávku
    } else if(isset($_POST['btnCartAcceptOrder'])) {
        //TODO: Snížení počtu zboží na skladě
        Order::addOrderToDb(123456, 'Ramdom poznámka');

        // Čištění globálních proměnných
        unset($_SESSION['cartOrderPayShip']);
        unset($_SESSION['cart']);

        header("Location:" . BASE_URL);
    }
} else {
    header("Location:" . BASE_URL);
}
?>

<div class="colorTemplate3 cart-order-container">
    <div id="cart-header">
        Souhrn před odesláním objednávky
    </div>
        <?php
        $cartItems = Cart::getAllItemsData();
        $cartPriceSum = Cart::getCartPriceSum($cartItems)['price'];
        $cartPriceVatSum = Cart::getCartPriceSum($cartItems)['price_vat'];
        // Zakoupené položky
        foreach($cartItems as $item) {
            echo '<div class="cart-row-grid">';
            echo  '<div class="cart-item-image-container">';
            if((is_null($item['image_path']) or empty($item['image_path'])) or
                !file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/eshop/" . $item['image_path'])) {
                echo "<a href='#'><img src='./images/eshop/blank.png' alt='img' class='cart-item-image'></a>";
            } else {
                echo "<a href='#'><img src='./images/eshop/" . $item['image_path'] . "' alt='". $item['product_name'] . "' class='cart-item-image'></a>";
            }
            echo  '</div>';

            echo '<div class="cart-item-name">' . $item['product_name'] . '</div>';
            echo '<div>' . $item['amount'] . '</div>';
            echo '<div>' . Cart::roundPrice(Cart::getPriceWithVat($item['price'])) . ' Kč/kus' . '</div>';
            echo '<div class="cart-item-sumprice">' . Cart::roundPrice(Cart::getPriceWithVat($item['price_sum'])) . ' Kč' . '</div>';
            echo '</div>';
        }
        ?>

        <div class="pay-ship-container2">
            <?php
            // Dopravné
            $deliveryData = Cart::getDeliveryDataById($_SESSION['cartOrderPayShip']['deliveryId']);
            $paymentData = Cart::getPaymentDataById($_SESSION['cartOrderPayShip']['paymentId']);
            echo '<div>';
                echo '<div class="cart-order-column-grid">';
                echo '<div>' . 'Poštovné a balné' . '</div>';;
                echo '<div>' . round(Cart::getPriceWithVat($deliveryData['price'] + $paymentData['price'])) . ' Kč' . '</div>';
                echo '</div>';
            echo '</div>';
            ?>
        </div>

        <?php
        // Cenový souhrn
        $cartPriceSum += ($deliveryData['price'] + $paymentData['price']);
        $cartPriceVatSum += Cart::getPriceWithVat($deliveryData['price'] + $paymentData['price']);
        echo '<div id="cart-price">';
        echo '<div style="font-size: 18px">' . 'Cena bez DPH: ' . Cart::roundPrice($cartPriceSum) . ' Kč' . '</div>';
        echo '<div style="font-size: 22px">' . 'Celkem k úhradě: <b>' . Cart::roundPrice($cartPriceVatSum) . ' Kč</b>'  . '</div>';
        echo '</div>';

        // Tlačítko Objednat
        echo '<div>';
        echo '<form method="post" action="?page=cart-order">';
        echo '<input type="submit" name="btnCartAcceptOrder" value="Objednat" class="cart-order-button">';
        echo '</form>';
        echo '</div>';
        ?>

</div>
