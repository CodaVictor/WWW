<!-- využívá soubor CSS index.css -->
<?php
echo '<ul id="headerListUser">';
if($_SESSION["loggedUser"]) {
    echo '<li class="headerListUserItem"><a href="./index.php?page=user-edit">Registrační údaje</a></li>';
    echo '<li class="headerListUserItem"><a href="./index.php?page=logout">Odhlásit</a></li>';
    echo '<li class="headerListUserItem">Přihlášený uživatel: ' . $_SESSION["loggedUserFullName"] . " ({$_SESSION["role"]})" . '</li>';
} else {
    echo '<li class="headerListUserItem"><a href="./index.php?page=user-registration">Registrace</a></li>';
    echo '<li class="headerListUserItem"><a href="./index.php?page=login">Přihlášení</a></li>';
}
echo '</ul>';
?>





