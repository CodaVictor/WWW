<?php
// Obsluha POST
if (!empty($_POST)) {
    if(isset($_POST['btnRemoveItem_x'])) {
        Cart::removeItem($_POST['productCode']);
        header("Refresh:0"); // Refrest aktuální stránky
    }
}
?>

<div class="colorTemplate3 cart-order-container">
    <!-- <div id="cart-header"> -->
    <div class="generalHeader1">
        Košík
    </div>

    <?php
    $cartItems = NULL;
    $cartPriceSum = 0;
    $cartPriceVatSum = 0;
    if(Cart::getItemsCount() > 0) {
        $cartItems = Cart::getAllItemsData();
        $cartPriceSum = Cart::getCartPriceSum($cartItems)['price'];
        $cartPriceVatSum = Cart::getCartPriceSum($cartItems)['price_vat'];
        foreach($cartItems as $item) {
            echo '<div class="cart-row-grid">';
            echo  '<div class="cart-item-image-container">';
            if((is_null($item['image_path']) or empty($item['image_path'])) or
                !file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/eshop/" . $item['image_path'])) {
                echo "<img src='./images/eshop/blank.png' alt='img' class='cart-item-image'>";
            } else {
                echo "<img src='./images/eshop/" . $item['image_path'] . "' alt='". $item['product_name'] . "' class='cart-item-image'>";
            }
            echo  '</div>';

            echo '
            <div>
            <div class="cart-item-name">' . $item['product_name'] . '</div>' .
            $item['product_code'] .
                '</div>';
            echo '<div>' . $item['amount'] . '</div>';
            echo '<div>' . Cart::roundPrice(Cart::getPriceWithVat($item['price'])) . ' Kč/kus' . '</div>';
            echo '<div class="cart-item-sumprice">' . Cart::roundPrice(Cart::getPriceWithVat($item['price_sum'])) . ' Kč' . '</div>';

            // Tlačítko pro odebrání produktu z košíku
            echo '<form method="post" action="?page=cart">';
            //echo '<button type="submit" name="btnRemoveItem"><img src="./images/cross25x25.png"></button>';

            echo '<input type="image" src="./images/cross25x25.png" alt="Submit" name="btnRemoveItem">';
            echo '<input type="hidden" name="productCode" value="' . $item['product_code'] . '">';
            echo '</form>';

            echo '</div>';
        }

        // LOKÁLNÍ ZAOKROUHLENÍ! (nechci zákazníkovi prezentovat desetinná čísla)
        echo '<div id="cart-price">';
        echo '<div style="font-size: 18px">' . 'Cena bez DPH: ' . Cart::roundPrice($cartPriceSum) . ' Kč' . '</div>';
        echo '<div style="font-size: 22px">' . 'Celkem k úhradě: <b>' . Cart::roundPrice(Cart::getPriceWithVat($cartPriceSum)) . ' Kč</b>'  . '</div>';
        echo '</div>';

        // Tlačítko, pokračovat v objednávce
        echo '<div>';
        echo '<form method="post" action="?page=cart-pay-ship">';
        echo '<input type="submit" name="btnPayShip" value="Pokračovat v objednávce" class="cart-order-button">';
        echo '</form>';
        echo '</div>';
    } else {
        echo 'Váš košík je prázdný';
    }
    ?>
</div>


<!--
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>
    function removeItemFromCart(callingElement) {
        var productCode = callingElement.parentNode.querySelector('input[name="productCode"]').getAttribute('value');
        var func = function(id) {
            $.ajax({
                url: 'localhost/Eshop/?page=cart',
                type: 'POST',
                data: {productCode:productCode},
            });
            //alert(productCode);
        }
</script>
-->