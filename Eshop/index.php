<?php
session_start();
include "config.php";

// __autoload slouží k načtení tříd v době, kdy se budou používat. Proto je není nutné zahrnovat ručně
function __autoload($className) {
    if (file_exists('./classes/' . $className . '.php')) {
        require_once './classes/' . $className . '.php';
    }
}

if ($_GET["page"] == "logout") {
    $_SESSION = [];
    session_destroy();
    header("Location:" . BASE_URL);
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Eshop s výpočetní technikou</title>
    <link href="css/index.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/logo.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/account.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/users.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/catalog.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/cart.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/responsive-index.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
</head>
<body>
<header id="pageHeader">
    <div id = "topBar" class="colorTemplate3">
        <?php
        include "./page/top_bar_component.php";
        ?>
    </div>
    <div id="pageHeaderDown" class="colorTemplate2">
        <div id="logo">G</div>
        <h1 id="pageHeaderTitle">Eshop</h1>
    </div>
</header>

<div id="pageCenter">
    <?php
    // zobrazení menu stránky
    include "./page/menu_component.php";
    ?>
    <div id="pageContent">
        <section>
            <?php
            $pathToFile = "./page/" . $_GET["page"] . ".php";
            if(file_exists($pathToFile)) {
                include $pathToFile;
            } else {
                include "./page/eshop.php";
                //echo $pathToFile;
            }
            ?>
        </section>
    </div>
</div>

<footer>
    <div>
        <p>Vytvořil: Viktor Homolka</p>
    </div>
</footer>
</body>
</html>
