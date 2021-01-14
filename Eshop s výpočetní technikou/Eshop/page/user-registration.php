<!-- využívá soubor CSS account.css -->
<?php
// právě přihlášený uživatel se nemůže registrovat
if($_SESSION['loggedUser']) {
    header("Location:" . BASE_URL);
}

// reakce na POST
if(!empty($_POST)) {
    //$_SESSION["errorMessage"] = NULL;
    $_SESSION["regTemp"] = ["first_name" => $_POST['formRegFirstName'],
        "last_name" => $_POST['formRegLastName'], "email" => $_POST['formRegEmail']];

    if(Account::existUser($_POST['formRegEmail'])) {
        $_SESSION["errorMessage"][] = "Uživatel s tímto emailem je již registrován.";
    }

    if($_POST['formRegPwd1'] != $_POST['formRegPwd2']) {
        $_SESSION["errorMessage"][] = "Zadaná hesla se neshodují.";
    }

    if(empty($_SESSION["errorMessage"])) {
        Account::addUser($_POST['formRegFirstName'], $_POST['formRegLastName'], $_POST['formRegEmail'], $_POST['formRegPwd1'],
            '123456789', 'zakaznik', 1, NULL);

        $_SESSION["regTemp"] = NULL;
        $_SESSION["errorMessage"] = NULL;
        header("Location:" . BASE_URL);
    } else {
        header("Location:" . BASE_URL . '?page=user-registration');
    }
}
?>

<!--FORMULÁŘ----------------------------------------------------------------------------------------------------------->
<div class="generalContainer">
    <div class="generalHeader1">
        Registrace nového uživatele
    </div>
    <form method="post" action="?page=user-registration" autocomplete="off">
        <div class="user-row">
            <div class="user-value-container">
                <div class="user-label">Jméno</div>
                <div>
                    <?php
                        echo '<input required class="user-textField" type="text" placeholder="" name="formRegFirstName"' .
                        "value={$_SESSION['regTemp']['first_name']}>";
                    ?>
                </div>
            </div>
            <div class="user-value-container">
                <div class="user-label">Příjmení</div>
                <?php
                echo '<input required class="user-textField" type="text" placeholder="" name="formRegLastName"' .
                    "value={$_SESSION['regTemp']['last_name']}>";
                ?>
            </div>
        </div>
        <div class="user-row">
            <div class="user-value-container">
                <div class="user-label">Email</div>
                <?php
                echo '<input required class="user-textField" type="email" placeholder="" name="formRegEmail"' .
                    "value={$_SESSION['regTemp']['email']}>";
                ?>
            </div>
        </div>
        <div class="user-row">
            <div class="user-value-container">
                <div class="user-label">Heslo</div>
                <input class="user-textField" type="password" placeholder="" name="formRegPwd1" required>
            </div>
            <div class="user-value-container">
                <div class="user-label">Heslo (znovu)</div>
                <input class="user-textField" type="password" placeholder="" name="formRegPwd2" required>
            </div>
        </div>
        <button class="form-button" type="submit">Registrovat</button>
    </form>
</div>

<!--VÝPIS CHYB-------------------------------------------------------------------------------------------------------->
<?php
// zpráva se objeví jen jednou a potom bude smazána
if(empty($_POST)) {
    foreach ($_SESSION["errorMessage"] as $errorMsgItem) {
        echo $errorMsgItem . " ";
    }
    $_SESSION["errorMessage"] = NULL;
    $_SESSION["regTemp"] = NULL;
}
?>
