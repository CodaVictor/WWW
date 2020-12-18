<!-- využívá soubor CSS users.css -->
<?php
if(!$_SESSION['loggedUser'] or $_SESSION['role'] != 'admin') {
    header("Location:" . BASE_URL);
}

// reakce na POST
if(!empty($_POST)) {
    if (isset($_POST['btnUserEdit'])) { // EDITACE uživatele
        $_SESSION['editingUserId'] = $_POST['userId']; // uložení ID uživatele, kterého dal admin editovat
        $userInfo = Account::getUserById($_SESSION['editingUserId']);
        $_SESSION["userEditTemp"] = ['first_name' => $userInfo['first_name'],
            "last_name" => $userInfo['last_name']];
        $_SESSION["iUserEdit"] = 0; // služební čítač přístupů do editace
        header("Location:" . BASE_URL . '?page=user-edit');
    } else if (isset($_POST['btnUserDelete'])) {
        if ($_SESSION['userId'] != $_POST['userId']) {
            Account::deleteUser(intval($_POST['userId']));
            header("Location:" . BASE_URL . '?page=users');
        } else {
            echo "Nelze odstranit účet, na který jste přihlášeni.";
        }
    }
}
?>

<!--FORMULÁŘ-------------------------------------------------------------------------------------------------------->
<div class="users-container colorTemplate3">
    <div class="form-header">
        Uživatelé
    </div>
    <?php
    $dataSet = Account::getAllUsersFromDB();
    $idArray = Account::getUserIdFromDataSet($dataSet); // identifikuje uživatele ve formuláři

    $usersTable = new DataTable($dataSet);
    $usersTable->setCustomTableMark('<table id="users-table">');
    $usersTable->addDbColumn('first_name', 'Jméno');
    $usersTable->addDbColumn('last_name', 'Příjmení');
    $usersTable->addDbColumn('email', 'Email');
    $usersTable->addDbColumn('role', 'Role');

    $usersTable->addCustomColumn('Akce');
    // $formMark, $formItemsArr, $hiddenDataArr
    $usersTable->addActionFormRow(
        '<form class="actionBtnForm" method="post" action="?page=users">',
         array('<button class="actionButton" type="submit" name="btnUserEdit">Upravit</button>',
               '<button class="actionButton" type="submit" name="btnUserDelete">Odstranit</button>'),
        $idArray
    );

    $usersTable->render();
    ?>
</div>
