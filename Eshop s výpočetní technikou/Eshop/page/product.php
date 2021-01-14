<?php
// Načtení ID produktu
if(!empty($_GET['p'])) {
    if(is_numeric($_GET['p'])) {
        $categoryParam = $_GET['p'];
    }
}

$prodData = Catalog::getItemByValue(Catalog::PRODUCT_ID, $categoryParam);
if(empty($prodData) || is_null($prodData)) {
    header("Location:" . BASE_URL);
}

// Reakce na POST
if(!empty($_POST)) {
    if(isset($_POST['btnAddToCard'])) {
        Cart::addItem($_POST['productCode']);
        header("Location:" . BASE_URL . '?' . $_SERVER['QUERY_STRING']);
    } else if(isset($_POST['btnAddReview'])) {
        $paramNewReview = Universal::cureString($_POST['newReview']);
        if(!(empty($paramNewReview) || is_null($paramNewReview))) {
            Catalog::addItemReview($_SESSION['userId'], $prodData['product_id'], $paramNewReview);
        }
    }
}
?>

<div class="generalContainer">
    <div class="product-detail-container">
        <?php
        echo '<div class="product-detail-image-container">';
        if((is_null($prodData['image_path']) or empty($prodData['image_path'])) or
            !file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/eshop/" . $prodData['image_path'])) {
            echo '<img src="./images/eshop/blank.png" alt="' . $prodData['product_name'] . '" class="cart-item-image">';
        } else {
            echo '<img src="./images/eshop/' . $prodData['image_path'] . '" alt="' . $prodData['product_name'] . '" class="cart-item-image">';
        }
        echo '</div>';

        echo '<div class="product-detail-info">' .
            '<div class="product-detail-item-name">' . $prodData['product_name'] . '</div>' .
            '<div>Produktový kód: ' . $prodData['product_code'] . '</div>' .
            '<div class="product-detail-desc">' . $prodData['specs'] . '</div>' .
            '<div class="product-detail-desc">Skladem: <span class="product-detail-stock">' . $prodData['number_in_stock'] . '<span> kusů</div>' .
            '<div class="product-detail-price">
                <span class="item-price-vat">' . round($prodData['price'] * VAT_MULTIPLIER) . ' Kč</span>
                <span class="item-price">bez DPH: ' . round($prodData['price']) . ' Kč</span>
            </div>';
            echo '<div>';
                echo '<form method="post" action="" style="max-height: 100%">';
                echo '<input type="submit" class="generalButton1" name="btnAddToCard" value="Koupit">';
                echo '<input type="hidden" name="productCode" value="' . $prodData['product_code'] . '">';
                echo '</form>';
            echo '</div>';
        '</div>';
        echo '</div>';
    echo '</div>';

    // Hodnocení zákazníků
    echo '<div class="generalHeader1" style="margin-top: 25px">' . 'Hodnocení zákazníků' . '</div>';
    $reviews = Catalog::getItemReviews($prodData['product_id']);
    if(!empty($reviews)) {
        foreach ($reviews AS $item) {
            echo '<div class="product-review-container" style="padding: 8px">';
            echo '<div class="product-review-username">' . $item['user_name'] . '</div>';
            echo '<div class="product-review-comment">' . $item['text'] . '</div>';
            echo '</div>';
        }
    } else {
        echo 'Nikdo produkt zatím neohodnotil.';
    }

    // Napsat hodnocení (jen pokud už nebylo napsáno)
    if($_SESSION["loggedUser"] && !Catalog::hasUserReviewedItem($_SESSION['userId'], $prodData['product_id'])) {
        echo '<div class="generalHeader1" style="margin-top: 25px">' . 'Napsat hodnocení' . '</div>';
            echo '<form method="post" action="" style="max-height: 100%">';
            echo '<textarea name="newReview" rows="7" style="width: 100%; resize: none"></textarea>';
            echo '<input type="submit" name="btnAddReview" value="Vložit" class="generalButton1" style="float: right">';
        echo '</form>';
    }
    ?>
</div>
