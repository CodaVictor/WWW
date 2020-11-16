<!-- využívá soubor CSS account.css -->
<div class="user-form-container colorTemplate3">
    <div class="form-header">
        Registrace nového uživatele
    </div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <!-- echo $_SERVER['PHP_SELF'] znamená, že formulář bude zpracován v místě, odkud byl zahrnut (include) - v tomto případě z index.php -->
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

<?php
if($_SESSION["errorMessage"] != NULL) {
    // zpráva se objeví jen jednou a potom bude smazána
    echo $_SESSION["errorMessage"];
    $_SESSION["errorMessage"] = NULL;
    $_SESSION["regTemp"] = NULL;
}
