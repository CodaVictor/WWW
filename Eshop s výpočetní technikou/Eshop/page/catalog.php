<?php
// Reakce na POST
if(!empty($_POST)) {
    if (isset($_POST['btnAddToCard'])) { // VLOŽENÍ do košíku
        // Nesmím vložit do košíku víc produktů než je na skladu
        $stock = Catalog::stockItemRemain($_POST['productCode']);
        if(($stock - ($_SESSION['cart'][$_POST['productCode']])) > 0) {
            Cart::addItem($_POST['productCode']); // uložení kódu produktu do košíku jako klíč a přiřazení počtu
        }
        //header("Location:" . BASE_URL . '?page=catalog');
        header("Location:" . BASE_URL . '?' . $_SERVER['QUERY_STRING']);
    }
}

$categoryParam = 1; // Výchozí category_id
// Reakce na GET
if(!empty($_GET['c'])) {
    if(is_numeric($_GET['c'])) {
        $categoryParam = $_GET['c'];
    }
}

// Nadpis
$categoryName = Category::getCategoryById($categoryParam)['category_name'];
echo '<div class="generalHeader1">' . $categoryName . '</div>';
?>

<div id="catalog-container">
    <?php
    $catalogItems = Catalog::getAllItems($categoryParam,10, 0, Catalog::PRODUCT_PRICE, Catalog::ORDER_RULE_DESC);
    if(is_bool($catalogItems) && !$catalogItems) {
        echo 'Chyba načtení položek katalogu';
    }
    foreach ($catalogItems as $item) {
        echo '<div class="catalog-item">';
        echo '<div>';
        if((is_null($item['image_path']) || empty($item['image_path'])) ||
            !file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/eshop/" . $item['image_path'])) {
            echo '<a href="?page=product&p=' . $item['product_id'] . '"><img src="./images/eshop/blank.png" alt="img" class="item-image"></a>';
        } else {
            echo '<a href="?page=product&p=' . $item['product_id'] . '"><img src="./images/eshop/' . $item['image_path'] . '" alt="' . $item['product_name'] . '" class="item-image"></a>';
        }
        echo'</div>';

        echo '
        <div style="font-size: 13px; color: #969696">
        <div style="margin: 10px 5px 5px 0px;"><a class="item-name" href="?page=product&p=' . $item['product_id'] . '">' . $item['product_name'] . '</a></div>' .
        $item['product_code'] .
        '</div>';
        echo '<div class="item-specs">' . $item['specs'] . '</div>';
        echo '<div class="item-price-outer">
                <span class="item-price-vat">' . Cart::roundPrice(Cart::getPriceWithVat($item['price'])) . ' Kč' . '</span>
                <span class="item-price">bez DPH: ' . $item['price'] . ' Kč' . '</span>             
              </div>';
        echo '<div class="item-stock-outer">Skladem: <span class="item-stock">' . $item['number_in_stock'] . '</div></span>';

        //======================== form ========================
        echo '<div>';
        echo '<form method="post" action="" style="max-height: 100%">';
            echo '<input type="submit" class="item-buy-button" name="btnAddToCard" value="Koupit">';
            echo '<input type="hidden" name="productCode" value="' . $item['product_code'] . '">';
        echo '</form>';
        echo '</div>';
        //======================================================
        echo '</div>';
    }
    ?>
</div>