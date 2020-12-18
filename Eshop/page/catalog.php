<?php
if(!empty($_POST)) {
    if (isset($_POST['btnAddToCard'])) { // VLOŽENÍ do košíku
        Cart::addItem($_POST['productCode']); // uložení kódu produktu do košíku jako klíč a přiřazení počtu
        header("Location:" . BASE_URL . '?page=catalog');
    }
}
?>

<div id="catalog-container">
    <?php
    $catalogItems = Catalog::getAllItems(5, 0, Catalog::ORDER_BY_PRICE, Catalog::ORDER_RULE_DESC);
    foreach ($catalogItems as $item) {
        echo '<div class="catalog-item">';
        echo '<div>';
        if((is_null($item->image_path) or empty($item->image_path)) or
            !file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/eshop/" . $item->image_path)) {
            echo "<a href='#'><img src='./images/eshop/blank.png' alt='img' class='item-image'></a>";
        } else {
            echo "<a href='#'><img src='./images/eshop/$item->image_path' alt='$item->product_name' class='item-image'></a>";
        }
        echo'</div>';

        echo '<div class="item-name">' . $item->product_name . '</div>';
        echo '<div class="item-specs">' . $item->specs . '</div>';
        echo '<div class="item-price-outer">
                <span class="item-price">' . $item->getPriceWithVat() . ' Kč' . '</span>
                <span class="item-price-vat">bez DPH ' . $item->price . ' Kč' . '</span>             
              </div>';
        echo '<div class="item-stock-outer">Skladem: <span class="item-stock">' . $item->number_in_stock . '</div></span>';

        //======================== form ========================
        echo '<form method="post" action="?page=catalog">';
            echo '<input type="submit" class="item-buy-button" name="btnAddToCard" value="Koupit">';
            echo '<input type="hidden" name="productCode" value="' . $item->product_code . '">';
        echo '</form>';
        //======================================================
        echo '</div>';
    }
    ?>
</div>