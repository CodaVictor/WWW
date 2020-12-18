<?php
// Obsluha POST
if (!empty($_POST)) {
    if(isset($_POST['btnItemRemove'])) {
        Cart::removeItem($_POST['productCode']);
        header("Refresh:0"); // Refrest aktuální stránky
    } else if(isset($_POST['btnCartOrder'])) {
        echo 'YES!';
    }
}
?>

<div class="cart-container colorTemplate3">
    <div id="cart-header">
        Košík
    </div>

    <div id="cart-container">
    <?php
    $cartItems = NULL;
    $cartPriceSum = 0;
    $cartPriceVatSum = 0;
    if(Cart::getItemsCount() > 0) {
        $cartItems = Cart::getAllItemsData();
        foreach($cartItems as $item) {
            $cartPriceSum += $item['price_sum'];
            echo '<div class="row-grid">';
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
            echo '<div>' . $item['price_vat'] . 'Kč/kus' . '</div>';
            echo '<div class="cart-item-sumprice">' . $item['price_vat_sum'] . ' Kč' . '</div>';

            // Tlačítko pro odebrání produktu z košíku
            echo '<form method="post" action="?page=cart">';
            echo '<input type="image" src="./images/cross25x25.png" alt="Submit" name="btnItemRemove">';
            echo '<input type="hidden" name="productCode" value="' . $item['product_code'] . '">';
            echo '</form>';

            echo '</div>';
        }
        $cartPriceVatSum = Cart::roundPrice(Cart::getPriceWithVat($cartPriceSum));
        echo '<div id="cart-price">';
        echo '<div style="font-size: 18px">' . 'Cena bez DPH: ' . $cartPriceSum . ' Kč' . '</div>';
        echo '<div style="font-size: 22px">' . 'Celkem k úhradě: <b>' . $cartPriceVatSum . ' Kč</b>'  . '</div>';
        echo '</div>';

        // Tlačítko, pokračovat v objednávce
        echo '<div>';
        echo '<form method="post" action="?page=cart" id="cart-order-form">';
        echo '<input type="submit" name="btnCartOrder" value="Pokračovat v objednávce" id="cart-order-button">';
        echo '</form>';
        echo '</div>';
    } else {
        echo 'Váš košík je prázdný';
    }
    ?>
    </div>
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