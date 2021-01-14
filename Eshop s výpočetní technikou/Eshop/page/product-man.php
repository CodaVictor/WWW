<?php
// využívá soubory CSS index.css a product.css
$formDataName = 'productId';
if(!($_SESSION['loggedUser'] AND ($_SESSION['role'] == 'admin' OR $_SESSION['role'] == 'zamestnanec'))) {
    header("Location:" . BASE_URL);
}

// Reakce na POST
if(!empty($_POST)) {
    if (isset($_POST['btnProductSave']) OR isset($_POST['btnProductEditSave'])) { // Přidávání nebo editace
        unset($_SESSION["errorMessage"]);
        $paramProdName = htmlspecialchars($_POST['productName']);
        $paramProdCode = htmlspecialchars($_POST['productCode']);
        $paramProdPrice = htmlspecialchars($_POST['price']);
        $paramProdNiS = htmlspecialchars($_POST['numberInStock']);
        $paramProdSpecs = htmlspecialchars($_POST['specs']);
        $paramProdCategory = htmlspecialchars($_POST['category']);

        if(!preg_match('/^(?=.+)(?:[1-9]\d*|0)?(?:,\d+)?$/', $paramProdPrice) OR
          !preg_match('/^\d{1,9}$/', $paramProdNiS)) {
            $_SESSION["errorMessage"][] = 'Neplatný číselný formát.';
        } else {
            $uploadOk = true;
            $imgBaseName = NULL;
            if(file_exists($_FILES["productImage"]["tmp_name"]) AND is_uploaded_file($_FILES["productImage"]["tmp_name"])) {
                // ================ Zpracování obrázku ================
                $targerDir = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/eshop/";
                $imgBaseName = basename($_FILES["productImage"]["name"]);
                $targetFile = $targerDir . $imgBaseName;
                $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

                // Kontrola, zda je soubor opravdu obrázek
                $check = getimagesize($_FILES["productImage"]["tmp_name"]);
                if(!($check !== false)) {
                    $_SESSION["errorMessage"][] = "Soubor není obrázek.";
                    $uploadOk = false;
                }

                // Kontrola, zda již daný obrázek s tímto jménem existuje. Pokud existuje, upravím název
                if (file_exists($targetFile)) {
                    //TODO: Úprava názvu $targetFile
                }

                // Kontrola velikosti obrázku (max. 1 MB)
                if ($_FILES["productImage"]["size"] > 1048576) {
                    $_SESSION["errorMessage"][] = "Obrázek je příliš velký.";
                    $uploadOk = false;
                }

                // Kontrola formátu
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    $_SESSION["errorMessage"][] = "Povolené extenze obrázku jsou .png, .jpg a .jpeg.";
                    $uploadOk = false;
                }

                if($uploadOk) {
                    if (!move_uploaded_file($_FILES["productImage"]["tmp_name"], $targetFile)) {
                        $_SESSION["errorMessage"][] = 'Obrázek se nepodařilo nahrát.';
                        $uploadOk = false;
                    }
                }
                // ======================================================
            }
            if(empty($_SESSION["errorMessage"])) {
                if(isset($_POST['btnProductSave'])) { // Přidání
                    if(Catalog::addItem($paramProdName, $paramProdCode, $paramProdPrice, $paramProdNiS, $imgBaseName, $paramProdSpecs, $paramProdCategory)) {
                        $_SESSION['successMessage'] = 'Produkt byl úspěšně vložen do katalogu.';
                    } else {
                        $_SESSION["errorMessage"][] = 'Produkt nelze přidat, je již v katalogu.';
                    }
                } else { // Editace
                    $paramProdId = htmlspecialchars($_POST['hiddenProdId']);
                    if(Catalog::updateItem($paramProdId, $paramProdName, $paramProdCode, $paramProdPrice, $paramProdNiS, $imgBaseName, $paramProdSpecs, $paramProdCategory)) {
                        $_SESSION['successMessage'] = 'Data produktu byla úspěšně upravena.';
                    } else {
                        $_SESSION["errorMessage"][] = 'Produkt se nepodařilo upravit.';
                    }
                }
            }
        }
    } else if (isset($_POST['btnProductEdit'])) { // Editace položky
        $data = Catalog::getItemByValueEx(Catalog::PRODUCT_ID, $_POST[$formDataName]); // Proměnná $_POST[$formDataName] existuje jen při prvním odeslání formuláře
        $_SESSION['prodManTemp'] = $data;
    } else if (isset($_POST['btnProductDelete'])) { // Odstranění položky
        if(isset($_POST[$formDataName])) {
            if(Catalog::deleteItemByValue(Catalog::PRODUCT_ID, $_POST[$formDataName])) {
                $_SESSION['successMessage'] = 'Produkt byl odstraněn';
            } else {
                $_SESSION['errorMessage'][] = 'Produkt se nepodařilo odstranit';
            }
        }
    }
}
?>

<div class="generalContainer">
    <div class="generalHeader1">Správa produktů</div>
    <?php
    // Výpis reakčních zpráv
    echo '<div>';
    foreach ($_SESSION['errorMessage'] as $errorMsgItem) {
        echo $errorMsgItem . " ";
    }
    unset($_SESSION['errorMessage']);
    echo $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
    echo '</div>';
    echo '<div class="generalHeader2">' . (isset($_SESSION['prodManTemp']) ? "Editovat" : "Přidat") .' produkt</div>';
    ?>
    <div class="container-border">
        <div class="product-man-row-grid" style="margin-bottom: 0">
            <div>Název produktu*</div>
            <div>Produktový kód*</div>
            <div>Cena bez DPH*</div>
            <div>Sklad [ks]*</div>
            <div>Obrázek (max. 1 MB)</div>
            <div>Specifikace</div>
            <div>Kategorie*</div>
        </div>

        <div class="product-man-row-grid">
            <form method="post" action="?page=product-man" style="display: contents" enctype="multipart/form-data">
                <?php
                echo '
                    <input type="text" name="productName" class="productInputField" value="' . $_SESSION['prodManTemp']['product_name'] . '" autocomplete="off" required>
                    <input type="text" name="productCode" class="productInputField" value="' . $_SESSION['prodManTemp']['product_code'] . '" autocomplete="off" required>
                    <input type="text" name="price" class="productInputField" value="' . $_SESSION['prodManTemp']['price'] . '" autocomplete="off" required>
                    <input type="text" name="numberInStock" class="productInputField" value="' . $_SESSION['prodManTemp']['number_in_stock'] . '" autocomplete="off" required>
                    <input type="file" name="productImage" class="productInputField" value="Nahrát" accept=".jpg,.png,.jpeg">
                    <textarea name="specs" rows="4" style="resize: none; height: auto" class="productInputField" autocomplete="off">' . $_SESSION['prodManTemp']['specs'] . '</textarea>
                    <select name="category" class="productInputField">
                ';
                $data = Category::getCategories();
                foreach ($data as $item) {
                    echo '<option value="' . $item['category_id'] . '"' . ((isset($_SESSION['prodManTemp']) AND $item['category_id'] == $_SESSION['prodManTemp']['categories_category_id']) ? 'selected' : '') . '>' . $item['category_name'] . '</option>';
                }
                echo '</select>';
                echo (isset($_SESSION['prodManTemp']) ? ('<input type="hidden" name="hiddenProdId" value="' . $_SESSION['prodManTemp']['product_id'] . '">') : '');
        echo '</div>';
        echo '<input type="submit"' . (isset($_SESSION['prodManTemp']) ? ' name="btnProductEditSave" value="Uložit"' : ' name="btnProductSave" value="Přidat"') . '" class="generalButton1" style="margin: 0px 10px 10px 0px">';
        unset($_SESSION['prodManTemp']);
        ?>
        </form>
    </div>
    <div class="generalHeader2">Produkty</div>
    <?php
    $dataSet = Catalog::getAllItemsEx(Catalog::CATEGORY_NONE, 50, 0, Catalog::PRODUCT_NAME);
    $idArray = Catalog::getProductIdFromDataSet($dataSet);

    $productTable = new DataTable($dataSet);
    $productTable->setCustomTableMark('<table class="generalTable">');
    $productTable->addDbColumn('product_id', 'ID');
    $productTable->addDbColumn('product_name', 'Název produktu');
    $productTable->addDbColumn('product_code', 'Produktový kód');
    $productTable->addDbColumn('price', 'Cena bez DPH');
    $productTable->addDbColumn('number_in_stock', 'Sklad [ks]');
    $productTable->addDbColumn('image_path', 'Obrázek');
    $productTable->addDbColumn('specs', 'Specifikace');
    $productTable->addDbColumn('category_name', 'Kategorie');

    $productTable->addCustomColumn('Akce');
    $productTable->addActionFormRow(
        '<form class="generalForm" method="post" action="?page=product-man">',
        array('<button class="generalButton2" type="submit" name="btnProductEdit">Upravit</button>',
            '<button class="generalButton2" type="submit" name="btnProductDelete">Odstranit</button>'),
        $idArray, $formDataName
    );
    $productTable->render();
    ?>
</div>




