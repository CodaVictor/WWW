<!-- využívá soubor CSS account.css -->
<?php
if($_SESSION['loggedUser']) {
    header("Location:" . BASE_URL);
}

if(!empty($_POST)) {
    if(isset($_POST['btnLogin'])) { // PŘIHLAŠOVÁNÍ
        if(Account::existUser($_POST['formLoginEmail'])) {
            $_SESSION["loginTemp"] = $_POST['formLoginEmail'];

            // Ověření hesla
            $passwordHash = Account::getUserPassword($_POST['formLoginEmail']);
            if(!password_verify($_POST['formLoginPwd'], $passwordHash)) {
                $_SESSION["errorMessage"][] = "Neplatné přihlašovací údaje.";
            }
        } else {
            $_SESSION["errorMessage"][] = "Účet s tímto emailem neexistuje.";
        }

        if(is_null($_SESSION["errorMessage"])) {
            $userActive = Account::getUserActive($_POST['formLoginEmail']);
            if($userActive == 1) {
                Account::loginUser($_POST['formLoginEmail']);
            } else {
                $_SESSION["errorMessage"][] = "Váš účet je deaktivovaný.";
            }
        }

        if(is_null($_SESSION["errorMessage"])) {
            $_SESSION['loginTemp'] = NULL;
            header("Location:" . BASE_URL);
        } else {
            header("Location:" . BASE_URL . '?page=user-login');
        }
    }
}
?>

<div class="generalContainer">
    <div class="generalHeader1">
        Přihlášení
    </div>
    <form method="post" action="?page=user-login" autocomplete="off">
        <!-- echo $_SERVER['PHP_SELF'] znamená, že formulář bude zpracován v místě, odkud byl zahrnut (include) - v tomto případě z index.php -->
        <div>
            <label class="form-label" for="email">Email</label>
            <input required class="form-textField" type="email" placeholder="" name="formLoginEmail" value=<?php echo $_SESSION['loginTemp'];?>>
        </div>
        <div>
            <label class="form-label" for="password">Heslo</label>
            <input class="form-textField" type="password" placeholder="" name="formLoginPwd" required>
        </div>
        <button class="form-button" type="submit" name="btnLogin">Přihlásit</button>
    </form>
</div>

<?php
// zpráva se objeví jen jednou a potom bude smazána
if(empty($_POST)) {
    foreach ($_SESSION["errorMessage"] as $errorMsgItem) {
        echo $errorMsgItem . " ";
    }
    $_SESSION["errorMessage"] = NULL;
    $_SESSION['loginTemp'] = NULL;
}
?>



