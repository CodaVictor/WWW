<!-- využívá soubor CSS users.css -->
<?php
    if(!$_SESSION['loggedUser'] or $_SESSION['role'] != 'admin') {
        header("Location:" . BASE_URL);
    } else {
        echo 'Vše v pohodě';
    }
?>

<div class="users-container colorTemplate3">
    <div class="form-header">
        Uživatelé
    </div>
    <?php
    $dataSet = Account::getAllUsersFromDB();
    $idArray = Account::getUserIdFromDataSet($dataSet);

    $usersTable = new DataTable($dataSet);
    $usersTable->setCustomTableMark('<table id="users-table">');
    $usersTable->addDbColumn('first_name', 'Jméno');
    $usersTable->addDbColumn('last_name', 'Příjmení');
    $usersTable->addDbColumn('email', 'Email');
    $usersTable->addDbColumn('role', 'Role');

    $usersTable->addCustomColumn('Akce');
    // $formMark, $formItemsArr, $hiddenDataArr
    $usersTable->addActionFormRow(
        '<form class="actionBtnForm" method="post" action="' . $_SERVER['PHP_SELF'] . '">',
         array('<input class="actionButton" type="submit" name="userEdit" value="Upravit" />',
               '<input class="actionButton" type="submit" name="userDelete" value="Odstranit" />'),
        $idArray
    );

    /*
    $usersTable->addCustomContentRow('
        <form class="actionBtnForm" method="post" action="'.$_SERVER['PHP_SELF']. '">
            <input class="actionButton" type="submit" name="userEdit" value="Upravit" />
            <input class="actionButton" type="submit" name="userDelete" value="Odstranit" />
        </form>
    ');
    */
    $usersTable->render();
    ?>
</div>
