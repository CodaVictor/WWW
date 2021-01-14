<?php
if(!$_SESSION['loggedUser']) {
    header("Location:" . BASE_URL);
}

// Reakce na POST
if(!empty($_POST)) {
    if(isset($_POST['btnUserInfoSave'])) {
        $userInfoTempData = array('first_name'=>Universal::cureString($_POST['userFirstName']),
            'last_name'=>Universal::cureString($_POST['userLastName']), 'email'=>Universal::cureString($_POST['userEmail']),
            'phone'=>Universal::cureString($_POST['userPhone']), 'vat_number'=>Universal::cureString($_POST['userVatNumber']),
            'city'=>Universal::cureString($_POST['userCity']), 'street'=>Universal::cureString($_POST['userStreet']),
            'zip_code'=>Universal::cureString($_POST['userZipCode']));

        $userPasswordOld = $_POST['userPasswordOld'];
        $userPassword1 = $_POST['userPassword1'];
        $userPassword2 = $_POST['userPassword2'];

        // Dodatečná data pro update
        $partialData = Account::getUserById($_SESSION['userId']);

        // SLužební proměnné pro kontrolu hesla
        $newPasswordEmpty = strlen($userPassword1) == 0 && strlen($userPassword2) == 0;
        $passwordChange = false;

        // Kontrola validity ===========================================================================================
        // Kontrola nového emailu
        if($userInfoTempData['email'] != $_SESSION["userEmail"]) {
            if(!filter_var($_POST['userEmail'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['errorMessage'][] = 'Neplatný emailový formát.';
            } else {
                if(Account::existEmail($userInfoTempData['email'])) {
                    $_SESSION['errorMessage'][] = 'Účet s tímto emailem již existuje.';
                }
            }
        }

        // Kontrola hesla
        if ($_SESSION['role'] == 'admin') {
            if(!$newPasswordEmpty) {
                if($userPassword1 == $userPassword2) {
                    $passwordChange = true;
                } else {
                    $_SESSION['errorMessage'][] = 'Nová hesla se neshodují.';
                }
            }
        } else {
            if(!$newPasswordEmpty) { // Nějaké nové heslo
                if(Account::checkUserPassword($_SESSION['userId'], $userPasswordOld)) {
                    if($userPassword1 == $userPassword2) {
                        $passwordChange = true;
                    } else {
                        $_SESSION['errorMessage'][] = 'Nová hesla se neshodují.';
                    }
                } else {
                    $_SESSION['errorMessage'][] = 'Staré heslo se neshoduje.';
                }
            } else {
                if(strlen($userPasswordOld) > 0) {
                    $_SESSION['errorMessage'][] = 'Nové heslo nesmí být prázdné.';
                }
            }
        }

        // Kontrola telefonu
        if(!preg_match('/^[\d]{9}$/', $_POST['userPhone'])) {
            $_SESSION['errorMessage'][] = 'Neplatný formát telefonního čísla.';
        }
        //Kontrola DIČ
        if(!empty($_POST['userVatNumber']) AND !preg_match('/^[a-zA-Z\d]+$/', $_POST['userVatNumber'])) {
            $_SESSION['errorMessage'][] = 'Neplatný formát DIČ.';
        }

        // Kontrola adresy
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

        // Update
        if(empty($_SESSION["errorMessage"])) {
            if($passwordChange) {
                $updateSuccessInfo = Account::updateUser($_SESSION['userId'], $userInfoTempData['first_name'], $userInfoTempData['last_name'], $userInfoTempData['email'],
                  $userPassword1, $userInfoTempData['phone'], $partialData['role'], $partialData['active'], $userInfoTempData['vat_number']);
            } else {
                $updateSuccessInfo = Account::updateUser($_SESSION['userId'], $userInfoTempData['first_name'], $userInfoTempData['last_name'], $userInfoTempData['email'],
                  NULL, $userInfoTempData['phone'], $partialData['role'], $partialData['active'], $userInfoTempData['vat_number']);
            }
            $updateSuccessAddress = Account::updateAddress($_SESSION['userId'], $userInfoTempData['city'], $userInfoTempData['street'],
            $userInfoTempData['zip_code'], 'F');

            if($updateSuccessInfo && $updateSuccessAddress) {
                $_SESSION['successMessage'] = 'Profilové údaje byli uloženy.';
            } else {
                $_SESSION["errorMessage"][] = 'Nové profilové údaje se nepodařilo uložit.';
            }
        }
    }
}

//TODO: Upravit zobrazované informace o účtu po updatu
$userData = Account::getUserById($_SESSION['userId']);
$userDataAddress = Account::getUserAddress($_SESSION['userId']);
?>

<!--FORMULÁŘ----------------------------------------------------------------------------------------------------------->
<div class="generalContainer">
    <div class="generalHeader1">
        Úprava profilových údajů
    </div>

    <?php
    // Výpis reakčních zpráv
    if(!empty($_SESSION['errorMessage']) OR !empty($_SESSION['successMessage'])) {
        echo '<div style="margin-bottom: 15px">';
        foreach ($_SESSION['errorMessage'] as $errorMsgItem) {
            echo $errorMsgItem . " ";
        }
        unset($_SESSION['errorMessage']);
        echo $_SESSION['successMessage'];
        unset($_SESSION['successMessage']);
        echo '</div>';
    }
    ?>

    <form method="post" action="?page=user-info" style="display: contents">
    <table>
        <col style="width:15%">
        <col style="width:85%">
        <tr>
            <td><label>Jméno</label></td>
            <td><input type="text" name="userFirstName" class="userInfoInputField" value="<?php echo $userData['first_name'] ?>" autocomplete="off" required></td>
        </tr>
        <tr>
            <td><label>Příjmení</label></td>
            <td><input type="text" name="userLastName" class="userInfoInputField" value="<?php echo $userData['last_name'] ?>" autocomplete="off" required></td>
        </tr>
        <tr>
            <td><label>Email</label></td>
            <td><input type="email" name="userEmail" class="userInfoInputField" value="<?php echo $userData['email'] ?>" autocomplete="off" required></td>
        </tr>
        <?php
        if($userData['role'] != 'admin') {
            echo '
            <tr>
                <td><label>Staré heslo</label></td>
                <td><input type="password" name="userPasswordOld" class="userInfoInputField" autocomplete="off"></td>
            </tr>
            ';
        }
        ?>
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
            <td><input type="text" name="userPhone" class="userInfoInputField" value="<?php echo $userData['phone'] ?>" autocomplete="off" required></td>
        </tr>
        <tr>
            <td><label>DIČ</label></td>
            <td><input type="text" name="userVatNumber" class="userInfoInputField" value="<?php echo $userData['vat_number'] ?>" autocomplete="off"></td>
        </tr>
    </table>

    <div class="generalHeader2">Adresa</div>
    <table>
        <col style="width:15%">
        <col style="width:85%">
        <tr>
            <td><label>Město</label></td>
            <td><input type="text" name="userCity" class="userInfoInputField" value="<?php echo $userDataAddress['city'] ?>" autocomplete="off" required></td>
        </tr>
        <tr>
            <td><label>Ulice a č. p.</label></td>
            <td><input type="text" name="userStreet" class="userInfoInputField" value="<?php echo $userDataAddress['street'] ?>" autocomplete="off" required></td>
        </tr>
        <tr>
            <td><label>PSČ</label></td>
            <td><input type="text" name="userZipCode" class="userInfoInputField" value="<?php echo $userDataAddress['zip_code'] ?>" autocomplete="off" required></td>
        </tr>
    </table>

    <input type="submit" name="btnUserInfoSave" value="Uložit" class="generalButton1">
    </form>
</div>