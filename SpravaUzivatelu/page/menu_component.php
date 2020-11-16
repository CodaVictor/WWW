<!-- využívá soubor CSS index.css -->
<nav id="menu">
    <a href="./index.php">Úvod</a>
    <?php
    if ($_SESSION["loggedUser"] and $_SESSION["role"] == "admin") {
        echo '<a href="./index.php?page=users">Uživatelé</a>';
    }
    ?>
</nav>