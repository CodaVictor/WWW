<!-- využívá soubor CSS account.css -->
<?php
if(!$_SESSION['loggedUser']) {
    header("Location:" . BASE_URL);
}

// V souboru users.php proměnná ---> $_SESSION["iUserEdit"] = 0
/* Pokud bylo přistoupeno k editačnímu formuláři (odkazu user-edit) s dočasnámi cizími údaji více jak 1x a přitom nebyl vyvolán POST,
   tak dojde k jejich odstranění a budou načtena data aktuálně přihlášeného uživatele */
if(empty($_POST) and $_SESSION['editingUserId'] != NULL) {
    $_SESSION["iUserEdit"]++;
    if($_SESSION["iUserEdit"] > 1) {
        $_SESSION['editingUserId'] = NULL;
        $_SESSION["userEditTemp"] = NULL;
    }
} else {
    $_SESSION["iUserEdit"] = 0;
}

// pokud se uživatelské údaje nezistili ve formuláři users.php (nepřešel z tohoto formuláře), převezmu údaje aktuálně přihlášeného uživatele
if(!isset($_SESSION['editingUserId'])) {
    $userInfo = Account::getUserById($_SESSION['userId']);
    $_SESSION["userEditTemp"] = ['first_name' => $userInfo['first_name'],
        "last_name" => $userInfo['last_name']];
}

// reakce na POST
if(!empty($_POST)) {
    if(isset($_POST['saveUserEdit'])) {
        // uložení údajů editačního formuláře
        $_SESSION["userEditTemp"] = ["first_name" => $_POST['formUserEditFirstName'],
            "last_name" => $_POST['formUserEditLastName']];
        //==============================================================

        if($_SESSION["role"] == 'admin') { // uživatel je admin
            if(empty($_POST['formUserEditPwd1']) and empty($_POST['formUserEditPwd2'])) { // prázdné heslo (nebude updatováno)
                if(empty($_SESSION['editingUserId'])) {
                    Account::updateUser($_SESSION["userId"], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName']);
                } else {
                    Account::updateUser($_SESSION['editingUserId'], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName']);
                }
            } else {
                if($_POST['formUserEditPwd1'] == $_POST['formUserEditPwd2']) {
                    if(empty($_SESSION['editingUserId'])) {
                        Account::updateUserAll($_SESSION['userId'], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName'], $_POST['formUserEditPwd1']);
                    } else {
                        Account::updateUserAll($_SESSION['editingUserId'], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName'], $_POST['formUserEditPwd1']);
                    }
                } else {
                    $_SESSION["errorMessage"][] = "Nová hesla se neshodují.";
                }
            }
        } else { // klasický uživatel
            if(empty($_POST['formUserEditPwd1']) and empty($_POST['formUserEditPwd2'])) { // prázdné heslo (nebude updatováno)
                Account::updateUser($_SESSION["userId"], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName']);
            } else {
                if(!Account::checkUserPassword($_SESSION["userId"], $_POST['formUserEditOldPwd'])) {
                    $_SESSION["errorMessage"][] = "Staré heslo se neshoduje.";
                }
                if($_POST['formUserEditPwd1'] != $_POST['formUserEditPwd2']) {
                    $_SESSION["errorMessage"][] = "Nová hesla se neshodují.";
                }
                if(empty($_SESSION["errorMessage"])) {
                    Account::updateUserAll($_SESSION["userId"], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName'], $_POST['formUserEditPwd1']);
                }
            }
        }

        if(empty($_SESSION["errorMessage"])) {
            $_SESSION["userEditTemp"] = [];
            $_SESSION['editingUserId'] = NULL;
            header("Location:" . BASE_URL);
        } else {
            header("Location:" . BASE_URL . '?page=user-edit');
        }
    }
}
?>

<!--FORMULÁŘ-------------------------------------------------------------------------------------------------------->
<div class="user-form-container colorTemplate3">
    <div class="form-header">
        Úprava profilových údajů
    </div>
    <form method="post" action="?page=user-edit">
        <!-- echo $_SERVER['PHP_SELF'] znamená, že formulář bude zpracován v místě, odkud byl zahrnut (include) - v tomto případě z index.php -->
        <div class="user-row">
            <div class="user-value-container">
                <div class="user-label">Jméno</div>
                <div>
                    <?php
                    echo '<input required class="user-textField" type="text" placeholder="" name="formUserEditFirstName"' .
                        "value={$_SESSION['userEditTemp']['first_name']}>";
                    ?>
                </div>
            </div>
            <div class="user-value-container">
                <div class="user-label">Příjmení</div>
                <?php
                echo '<input required class="user-textField" type="text" placeholder="" name="formUserEditLastName"' .
                    "value={$_SESSION['userEditTemp']['last_name']}>";
                ?>
            </div>
        </div>
        <?php
        if($_SESSION['role'] != 'admin') {
        echo
        '<div class="user-row">'
            . '<div class="user-value-container">'
                . '<div class="user-label">Staré heslo</div>'
                . '<input class="user-textField" type="password" placeholder="" name="formUserEditOldPwd">'
            . '</div>'
        .'</div>';
        }
        ?>
        <div class="user-row">
            <div class="user-value-container">
                <div class="user-label">Heslo</div>
                <input class="user-textField" type="password" placeholder="" name="formUserEditPwd1">
            </div>
            <div class="user-value-container">
                <div class="user-label">Heslo (znovu)</div>
                <input class="user-textField" type="password" placeholder="" name="formUserEditPwd2">
            </div>
        </div>
        <button class="form-button" type="submit" name="saveUserEdit">Uložit</button>
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
    //$_SESSION["userEditTemp"] = NULL;
    //$_SESSION['editingUserId'] = NULL;
}
?>