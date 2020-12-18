<!-- využívá soubor CSS index.css -->
<?php
echo '<ul id="headerListUser">';
echo '<li class="headerListUserItem"><a href="./?page=cart">Košík ['. ((Cart::getItemsCount()) == 0 ? "prázdný" : Cart::getItemsCount()) .']</a></li>';
if($_SESSION["loggedUser"]) {
    echo '<li class="headerListUserItem"><a href="./?page=user-edit">Registrační údaje</a></li>';
    echo '<li class="headerListUserItem"><a href="./?page=logout">Odhlásit</a></li>';
    echo '<li class="headerListUserItem">Přihlášený uživatel: ' . $_SESSION["loggedUserFullName"] . " ({$_SESSION["role"]})" . '</li>';
} else {
    echo '<li class="headerListUserItem"><a href="./?page=user-registration">Registrace</a></li>';
    echo '<li class="headerListUserItem"><a href="./?page=user-login">Přihlášení</a></li>';
}
echo '</ul>';
?>





