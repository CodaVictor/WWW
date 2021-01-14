<!-- využívá soubor CSS account.css -->
<?php
if($_SESSION['loggedUser']) {
    header("Location:" . BASE_URL);
}

// Reakce na POST
if(!empty($_POST)) {
    if($_POST['btnUserReg']) {
        $userRegTempData = array('first_name'=>Universal::cureString($_POST['userFirstName']),
            'last_name'=>Universal::cureString($_POST['userLastName']), 'email'=>Universal::cureString($_POST['userEmail']),
            'phone'=>Universal::cureString($_POST['userPhone']), 'vat_number'=>Universal::cureString($_POST['userVatNumber']),
            'city'=>Universal::cureString($_POST['userCity']), 'street'=>Universal::cureString($_POST['userStreet']),
            'zip_code'=>Universal::cureString($_POST['userZipCode']));

        // Kontrola validity ===========================================================================================
        // Kontrola emailu
        if(!filter_var($_POST['userEmail'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errorMessage'][] = 'Neplatný emailový formát.';
        } else {
            if(Account::existEmail($userRegTempData['email'])) {
                $_SESSION['errorMessage'][] = 'Účet s tímto emailem již existuje.';
            }
        }
        // Kontrola hesel
        if(empty($_POST['userPassword1']) AND empty($_POST['userPassword2'])) {
            $_SESSION['errorMessage'][] = 'Heslo nesmí být prázdné.';
        } else if ($_POST['userPassword1'] != $_POST['userPassword2']) {
            $_SESSION['errorMessage'][] = 'Hesla se neshodují.';
        }
        if(!preg_match('/^[\d]{9}$/', $_POST['userPhone'])) {
            $_SESSION['errorMessage'][] = 'Neplatný formát telefonního čísla.';
        }
        if(!empty($_POST['userVatNumber']) AND !preg_match('/^[a-zA-Z\d]+$/', $_POST['userVatNumber'])) {
            $_SESSION['errorMessage'][] = 'Neplatný formát DIČ.';
        }

        // Kontrola adresy (první část údajů musí být bez chyb)
        if(empty($_SESSION['errorMessage'])) {
            if(strlen($_POST['userCity']) == 0) {
                $_SESSION['errorMessage'][] = 'Název města musí být vyplněn.';
            }
            if(strlen($_POST['userStreet']) == 0) {
                $_SESSION['errorMessage'][] = 'Název ulice musí být vyplněn.';
            }
            if(!preg_match('/^[\d]{5}$/', $_POST['userZipCode'])) {
                $_SESSION['errorMessage'][] = 'Neplatný formát PSČ.';
            }
        }
        // =============================================================================================================

        // Registrace
        if(empty($_SESSION['errorMessage'])) {
            if(!Account::addUser($userRegTempData['first_name'], $userRegTempData['last_name'], $userRegTempData['email'],
                $_POST['userPassword1'], $userRegTempData['phone'], 'zakaznik', 1, $userRegTempData['vat_number'])) {
                $_SESSION['errorMessage'][] = 'Účet se nepovedlo vytvořit.';
            } else {
                $regUser = Account::getUserByEmail($userRegTempData['email']);
                $test = Account::addAddress($regUser['user_id'], $userRegTempData['city'], $userRegTempData['street'],
                    $userRegTempData['zip_code'], 'F');
                header("Location:" . BASE_URL);
            }
        }
    }
}
?>

<!--FORMULÁŘ----------------------------------------------------------------------------------------------------------->
<div class="generalContainer">
    <div class="generalHeader1">
        Registrace nového uživatele
    </div>

    <?php
    // Výpis chybové zpráv
    if(!empty($_SESSION['errorMessage'])) {
        echo '<div style="margin-bottom: 15px">';
        foreach ($_SESSION['errorMessage'] as $errorMsgItem) {
            echo $errorMsgItem . " ";
        }
        unset($_SESSION['errorMessage']);
        echo '</div>';
    }
    ?>

    <form method="post" action="?page=user-reg" style="display: contents">
        <div class="generalHeader2">Profilové údaje</div>
        <table>
            <col style="width:15%">
            <col style="width:85%">
            <tr>
                <td><label>Jméno</label></td>
                <td><input type="text" name="userFirstName" class="userInfoInputField" value="<?php echo $userRegTempData['first_name'] ?>" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><label>Příjmení</label></td>
                <td><input type="text" name="userLastName" class="userInfoInputField" value="<?php echo $userRegTempData['last_name'] ?>" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><label>Email</label></td>
                <td><input type="email" name="userEmail" class="userInfoInputField" value="<?php echo $userRegTempData['email'] ?>" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><label>Nové heslo</label></td>
                <td><input type="password" name="userPassword1" class="userInfoInputField" autocomplete="off"></td>
            </tr>
            <tr>
                <td><label>Nové heslo (znovu)</label></td>
                <td><input type="password" name="userPassword2" class="userInfoInputField" autocomplete="off"></td>
            </tr>
            <tr>
                <td><label>Telefon</label></td>
                <td><input type="text" name="userPhone" class="userInfoInputField" value="<?php echo $userRegTempData['phone'] ?>" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><label>DIČ</label></td>
                <td><input type="text" name="userVatNumber" class="userInfoInputField" value="<?php echo $userRegTempData['vat_number'] ?>" autocomplete="off"></td>
            </tr>
        </table>

        <div class="generalHeader2">Adresa</div>
        <table>
            <col style="width:15%">
            <col style="width:85%">
            <tr>
                <td><label>Město*</label></td>
                <td><input type="text" name="userCity" class="userInfoInputField" value="<?php echo $userRegTempData['city'] ?>" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><label>Ulice a č. p.*</label></td>
                <td><input type="text" name="userStreet" class="userInfoInputField" value="<?php echo $userRegTempData['street'] ?>" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><label>PSČ*</label></td>
                <td><input type="text" name="userZipCode" class="userInfoInputField" value="<?php echo $userRegTempData['zip_code'] ?>" autocomplete="off" required></td>
            </tr>
        </table>
        <input type="submit" name="btnUserReg" value="Registrovat" class="generalButton1">
    </form>
</div>