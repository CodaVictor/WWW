<!-- využívá soubor CSS account.css -->
<div class="user-form-container colorTemplate3">
    <div class="form-header">
        Úprava profilových údajů
    </div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
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
        if($_SESSION["editingUserId"] == $_SESSION["userId"]) {
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

<?php
if($_SESSION["errorMessage"] != NULL) {
    // zpráva se objeví jen jednou a potom bude smazána
    echo $_SESSION["errorMessage"];
    $_SESSION["errorMessage"] = NULL;
    $_SESSION["userEditTemp"] = NULL;
}