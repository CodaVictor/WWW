<!-- využívá soubor CSS index.css -->
<nav id="menu">
    <a href="./">Katalog</a>
    <?php
    if ($_SESSION["loggedUser"] and $_SESSION["role"] == "admin") {
        echo '<a href="./?page=users">Uživatelé</a>';
    }
    ?>
</nav>