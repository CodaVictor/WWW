<!-- využívá soubor CSS account.css -->
<div class="login-form-container colorTemplate3">
    <div class="form-header">
        Přihlášení
    </div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <!-- echo $_SERVER['PHP_SELF'] znamená, že formulář bude zpracován v místě, odkud byl zahrnut (include) - v tomto případě z index.php -->
        <div>
            <label class="form-label" for="email">Email</label>
            <input class="form-textField" type="email" placeholder="" name="formLoginEmail" required>
        </div>
        <div>
            <label class="form-label" for="password">Heslo</label>
            <input class="form-textField" type="password" placeholder="" name="formLoginPwd" required>
        </div>
        <button class="form-button" type="submit">Přihlásit</button>
    </form>
</div>

<?php
if($_SESSION["errorMessage"] != NULL) {
    // zpráva se objeví jen jednou a potom bude smazána
    echo $_SESSION["errorMessage"];
    $_SESSION["errorMessage"] = NULL;
}
?>



