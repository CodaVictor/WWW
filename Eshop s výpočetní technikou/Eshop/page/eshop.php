<?php
    $pathToFile = "./eshop/" . $_GET["es"] . ".php";
    if(file_exists($pathToFile)) {
        include $pathToFile;
    } else {
        include "catalog.php";
        //echo $pathToFile;
    }
?>