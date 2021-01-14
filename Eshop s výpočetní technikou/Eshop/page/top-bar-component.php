<!-- využívá soubor CSS index.css -->
<?php
echo '<ul id="headerListUser">';
if ($_SESSION["loggedUser"] AND $_SESSION["role"] == "admin") {
    echo '<li class="headerListUserItemSt"><a href="./?page=user-man">Uživatelé</a></li>';
}
if($_SESSION['loggedUser'] AND ($_SESSION['role'] == 'admin' OR $_SESSION['role'] == 'zamestnanec')) {
    echo '<li class="headerListUserItemSt"><a href="./?page=product-man">Produkty</a></li>';
    echo '<li class="headerListUserItemSt"><a href="./?page=order-man">Odjednávky</a></li>';
}
echo '<li class="headerListUserItem"><a href="./?page=cart">Košík ['. ((Cart::getItemsCount()) == 0 ? "prázdný" : Cart::getItemsCount()) .']</a></li>';
if($_SESSION["loggedUser"]) {
    echo '<li class="headerListUserItem"><a href="./?page=order">Moje objednávky</a></li>';
    echo '<li class="headerListUserItem"><a href="./?page=user-info">Profilové údaje</a></li>';
    echo '<li class="headerListUserItem"><a href="./?page=logout">Odhlásit</a></li>';
    echo '<li class="headerListUserItem">Přihlášený uživatel: ' . $_SESSION["userFullName"] . " ({$_SESSION["role"]})" . '</li>';
} else {
    echo '<li class="headerListUserItem"><a href="./?page=user-reg">Registrace</a></li>';
    echo '<li class="headerListUserItem"><a href="./?page=user-login">Přihlášení</a></li>';
}
echo '</ul>';
?>





