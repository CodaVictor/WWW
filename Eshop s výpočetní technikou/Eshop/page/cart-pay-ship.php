<?php
// Obsluha POST
if(!isset($_SESSION["userId"])) {
    echo '<div class="colorTemplate3 cart-order-container">Pro pokračování v objednávce je nutné se nejprve přihlásit</div>';
    exit();
}

if (empty($_POST) OR !isset($_POST['btnPayShip'])){
    header("Location:" . BASE_URL);
}
?>

<div class="colorTemplate3 cart-order-container">
    <div id="pay-ship-header">
        Způsob dodání
    </div>
    <div class="pay-ship-container2">
    <form method="post" action="?page=cart-order">
        <?php
        foreach (Cart::getAllDeliveryTypes() as $item) {
            echo '<div class="pay-ship-shipping-row-grid">';

            echo '<div><img src="./images/shipping25x25.png" class="pay-ship-shipping-image" alt="' . $item['delivery_name'] . '"></div>';
            echo '<div><input type="radio" name="radioBtnDelivery" value=' . $item['delivery_id'] . '></div>';
            echo '<div>' . $item['delivery_name'] . '</div>';
            if($item['price'] == 0) {
                echo '<div class="pay-ship-center-text" style="color: #4CAF50; font-weight: bold;">' . 'Zdarma' . '</div>';
            } else {
                echo '<div class="pay-ship-center-text">' . round(Cart::getPriceWithVat($item['price'])) . ' Kč' . '</div>';
            }
            echo '<div class="pay-ship-center-text">' . 'Doba dodání: ' . $item['speed'] . ' pracovní dny' . '</div>';

            echo '</div>';
        }
        ?>
    </div>
    <script>
    // Nastavení výchozí dopravy
    document.querySelectorAll('[name=radioBtnShipping]')[1].checked = true;
    </script>

    <div id="pay-ship-header">
        Typ platby
    </div>
    <div class="pay-ship-container2">
        <?php
        $data = Cart::getAllPaymentTypes();
        foreach ($data as $item) {
            echo '<div class="pay-ship-payment-row-grid">';
            if($item['payment_name'] == 'Dobírka') {
                echo '<div><img src="./images/payment-money25x25.png" class="pay-ship-payment-image" alt="' . $item['payment_name'] . '"></div>';
            } else if($item['payment_name'] == 'Karta') {
                echo '<div><img src="./images/payment-card35x25.png" class="pay-ship-payment-image" alt="' . $item['payment_name'] . '"></div>';
            } else if($item['payment_name'] == 'Záloha') {
                echo '<div><img src="./images/payment-bank25x25.png" class="pay-ship-payment-image" alt="' . $item['payment_name'] . '"></div>';
            }
            echo '<div><input type="radio" name="radioBtnPayment" value=' . $item['payment_id'] . '></div>';

            echo '<div>' . $item['note'] . '</div>';;
            if($item['price'] == 0) {
                echo '<div style="color: #4CAF50; font-weight: bold;">' . 'Zdarma' . '</div>';
            } else {
                echo '<div>' . round(Cart::getPriceWithVat($item['price'])) . ' Kč' . '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
    <script>
        // Nastavení výchozí dopravy
        document.querySelectorAll('[name=radioBtnDelivery]')[1].checked = true;
        document.querySelectorAll('[name=radioBtnPayment]')[1].checked = true;
    </script>

    <!--Tlačítko, finální sumarizace objednávky-->
    <div>
    <input type="submit" name="btnCartOrder" value="Pokračovat v objednávce" class="cart-order-button">
    </form>
    </div>
</div>