<!-- využívá soubor CSS account.css -->
<?php
// Na stránce je definována globální proměnná $_SESSION['userManEditUserId']
$formDataName = 'userId';
if(!$_SESSION['loggedUser'] or $_SESSION['role'] != 'admin') {
    header("Location:" . BASE_URL);
}

// reakce na POST
if(!empty($_POST)) {
    if (isset($_POST['btnUserSave']) || isset($_POST['btnUserEditSave'])) { // přidávání nebo editace
        $paramUserPassword1 = $_POST['userPassword1'];
        $paramUserPassword2 = $_POST['userPassword2'];
        $userManTempData = array('first_name'=>Universal::cureString($_POST['userFirstName']), 'last_name'=>Universal::cureString($_POST['userLastName']),
            'email'=>Universal::cureString($_POST['userEmail']), 'phone'=>Universal::cureString($_POST['userPhone']),
            'vat_number'=>Universal::cureString($_POST['userVat']), 'role'=>Universal::cureString($_POST['userRole']),
            'active'=>Universal::cureString($_POST['userActive']));
        if(isset($_POST['btnUserSave'])) { // Přidání
            if(strlen($paramUserPassword1) > 0 && strlen($paramUserPassword2) > 0) {
                if($paramUserPassword1 == $paramUserPassword2) {
                    if(Account::addUser($userManTempData['first_name'], $userManTempData['last_name'], $userManTempData['email'], $paramUserPassword1,
                        $userManTempData['phone'], $userManTempData['role'], $userManTempData['active'], $userManTempData['vat_number'])) {
                        $_SESSION['successMessage'] = 'Uživatel byl úspěšně přidán.';
                        unset($userManTempData);
                    } else {
                        $_SESSION["errorMessage"][] = 'Uživatele s tímto emailem nelze přidat, již existuje.';
                    }
                } else {
                    $_SESSION["errorMessage"][] = 'Hesla se neshodují.';
                }
            } else {
                $_SESSION["errorMessage"][] = 'Heslo nesmí být prázdné.';
            }
        } else { // Editace
            // Musím si pamatovat ID naposledy editovaného uživatele
            if(isset($_POST['hiddenUserId']) && is_numeric($_POST['hiddenUserId'])) {
                $_SESSION['userManEditUserId'] = htmlspecialchars($_POST['hiddenUserId']);
            }
            //$paramUserId = htmlspecialchars($_POST['hiddenUserId']);
            if(strlen($paramUserPassword1) == 0 && strlen($paramUserPassword2) == 0) {
                if (Account::updateUser($_SESSION['userManEditUserId'], $userManTempData['first_name'], $userManTempData['last_name'], $userManTempData['email'], NULL,
                    $userManTempData['phone'], $userManTempData['role'], $userManTempData['active'], $userManTempData['vat_number'])) {
                    $_SESSION['successMessage'] = 'Data uživatele byla úspěšně upravena.';
                    unset($userManTempData);
                } else {
                    $_SESSION["errorMessage"][] = 'Uživatelská data se nepodařilo upravit.';
                }
            } else {
                if($paramUserPassword1 == $paramUserPassword2) {
                    if (Account::updateUser($_SESSION['userManEditUserId'], $userManTempData['first_name'], $userManTempData['last_name'], $userManTempData['email'], $paramUserPassword1,
                        $userManTempData['phone'], $userManTempData['role'], $userManTempData['active'], $userManTempData['vat_number'])) {
                        $_SESSION['successMessage'] = 'Data uživatele byla úspěšně upravena.';
                        unset($userManTempData);
                    } else {
                        $_SESSION["errorMessage"][] = 'Uživatelská data se nepodařilo upravit.';
                    }
                } else {
                    $_SESSION["errorMessage"][] = 'Hesla se neshodují.';
                }
            }
        }
    } else if(isset($_POST['btnUserEdit'])) {
        $userManTempData = Account::getUserById($_POST[$formDataName]);
    } else if(isset($_POST['btnUserDelete'])) {
        if (isset($_POST[$formDataName])) {
            if ($_POST[$formDataName] != $_SESSION['userId']) { // Účet lze odstranit jen tehdy, pokud z tohoto účtu nedávám pokyn k jeho odstranění
                if (Account::deleteUser($_POST[$formDataName])) {
                    $_SESSION['successMessage'] = 'Uživatel byl odstraněn.';
                } else {
                    $_SESSION['errorMessage'][] = 'Uživatele se nepodařilo odstranit.';
                }
            } else {
                $_SESSION['errorMessage'][] = 'Nelze smazat váš účet, protože jste na něj právě přihlášen.';
            }
        }
    }
} else {
    unset($userManTempData);
}
?>

<!--FORMULÁŘ----------------------------------------------------------------------------------------------------------->
<div class="generalContainer">
    <div class="generalHeader1">
        Správa uživatelů
    </div>
    <?php
    $roleData = USER_ROLES_DEFINABLE;
    $activeData = USER_ACTIVE;
    // Výpis reakčních zpráv
    echo '<div>';
    foreach ($_SESSION['errorMessage'] as $errorMsgItem) {
        echo $errorMsgItem . " ";
    }
    unset($_SESSION['errorMessage']);
    echo $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
    echo '</div>';
    echo '<div class="generalHeader2">' . (isset($userManTempData) ? "Editovat" : "Přidat") .' uživatele</div>';
    ?>
    <div class="container-border">
        <div class="user-man-row-grid">
            <div>Jméno*</div>
            <div>Příjmení*</div>
            <div>Email*</div>
            <div>Heslo*</div>
            <div>Heslo (znovu)*</div>
            <div>Telefon*</div>
            <div>DIČ</div>
            <div>Role*</div>
            <div>Aktivní*</div>
        </div>

        <div class="user-man-row-grid">
            <form method="post" action="?page=user-man" style="display: contents">
                <?php
                echo '
                    <input type="text" name="userFirstName" class="userInputField" value="' . $userManTempData['first_name'] . '" autocomplete="off" required>
                    <input type="text" name="userLastName" class="userInputField" value="' . $userManTempData['last_name'] . '" autocomplete="off" required>
                    <input type="email" name="userEmail" class="userInputField" value="' . $userManTempData['email'] . '" autocomplete="off" required>
                    <input type="password" name="userPassword1" class="userInputField" autocomplete="off">
                    <input type="password" name="userPassword2" class="userInputField" autocomplete="off">
                    <input type="text" name="userPhone" class="userInputField" value="' . $userManTempData['phone'] . '" autocomplete="off" required>
                    <input type="text" name="userVat" class="userInputField" value="' . $userManTempData['vat_number'] . '" autocomplete="off">                   
                    <select name="userRole" class="userInputField">
                ';
                foreach (array_keys($roleData) as $item) {
                    echo '<option value="' . $item . '"' . ((isset($userManTempData) && $userManTempData['role'] == $item) ? 'selected' : '') . '>' . $roleData[$item] . '</option>';
                }
                echo '
                </select>
                <select name="userActive" class="userInputField">
                ';
                foreach (array_keys($activeData) as $item) {
                    echo '<option value="' . $item . '"' . ((isset($userManTempData) && $userManTempData['active'] == $item) ? 'selected' : '') . '>' . $activeData[$item] . '</option>';
                }
                echo '</select>';
                echo (isset($userManTempData) ? ('<input type="hidden" name="hiddenUserId" value="' . $userManTempData['user_id'] . '">') : '');
        echo '</div>';
        echo '<input type="submit"' . (isset($userManTempData) ? ' name="btnUserEditSave" value="Uložit"' : ' name="btnUserSave" value="Přidat"') . '" class="generalButton1" style="margin: 0px 10px 10px 0px">';
        ?>
        </form>
    </div>
    <div class="generalHeader2">Uživatelé</div>
    <?php
    $dataSet = Account::getAllUsers(50, 0, Account::USER_LAST_NAME);
    $idArray = Account::getUserIdFromDataSet($dataSet);

    $usersTable = new DataTable($dataSet);
    $usersTable->setCustomTableMark('<table class="generalTable">');
    $usersTable->addDbColumn('user_id', 'ID');
    $usersTable->addDbColumn('first_name', 'Jméno');
    $usersTable->addDbColumn('last_name', 'Příjmení');
    $usersTable->addDbColumn('email', 'Email');
    $usersTable->addDbColumn('phone', 'Telefon');
    $usersTable->addDbColumn('vat_number', 'DIČ');
    $usersTable->addDbColumn('role', 'Role');
    $usersTable->addDbColumn('active', 'Aktivní');

    $usersTable->addCustomColumn('Akce');
    $usersTable->addActionFormRow(
        '<form class="actionBtnForm" method="post" action="?page=user-man">',
         array('<button class="generalButton2" type="submit" name="btnUserEdit">Upravit</button>',
               '<button class="generalButton2" type="submit" name="btnUserDelete">Odstranit</button>'),
        $idArray, $formDataName);

    $usersTable->render();
    ?>
</div>
