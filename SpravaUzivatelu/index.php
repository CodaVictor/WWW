<?php
session_start();
include "config.php";

// __autoload slouží k načtení tříd v době, kdy se budou používat. Proto je není nutné zahrnovat ručně
function __autoload($className) {
    if (file_exists('./classes/' . $className . '.php')) {
        require_once './classes/' . $className . '.php';
    }
}

if ($_GET["page"] == "logout") {
    $_SESSION = [];
    session_destroy();
    header("Location:" . BASE_URL);
}

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Správa uživatelů</title>
    <link href="css/index.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/logo.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/account.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/users.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <link href="css/responsive-index.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
</head>
<body>
<header id="pageHeader">
    <div id = "topBar" class="colorTemplate3">
        <?php
        include "./page/top_bar_component.php";
        ?>
    </div>
    <div id="pageHeaderDown" class="colorTemplate2">
        <div id="logo">G</div>
        <h1 id="pageHeaderTitle">Správa uživatelů</h1>
    </div>
</header>

<div id="pageCenter">
    <?php
    // zobrazení menu stránky
    include "./page/menu_component.php";
    ?>
    <div id="pageContent">
        <section>
            <?php
            $pathToFile = "./page/" . $_GET["page"] . ".php";
            if(file_exists($pathToFile)) {
                include $pathToFile;
            } else {
                include "./page/page_main_content.php";
                echo $pathToFile;
            }

            if ($_SERVER['REQUEST_METHOD'] == "POST"){
                if(isset($_POST['formLoginEmail'])) { // PŘIHLAŠOVÁNÍ
                    Account::loginUser($_POST['formLoginEmail'], $_POST['formLoginPwd']);
                    if($_SESSION["errorMessage"] == NULL) {
                        header("Location:" . BASE_URL);
                    } else {
                        header("Location:" . BASE_URL . '?page=login');
                    }
                } else if(isset($_POST['formRegEmail'])) { // REGISTRACE
                    // Uložení údajů pro případné opětovné načtení
                    $_SESSION["regTemp"] = ["first_name" => $_POST['formRegFirstName'],
                        "last_name" => $_POST['formRegLastName'], "email" => $_POST['formRegEmail']];

                    if($_POST['formRegPwd1'] == $_POST['formRegPwd2']) {
                        Account::registrationUser($_POST['formRegFirstName'], $_POST['formRegLastName'],
                            $_POST['formRegEmail'], $_POST['formRegPwd1'], 'registered');
                    } else {
                        $_SESSION["errorMessage"] = "Zadaná hesla se neshodují.";
                    }

                    if($_SESSION["errorMessage"] == NULL) {
                        header("Location:" . BASE_URL);
                        $_SESSION["regTemp"] = [];
                    } else {
                        header("Location:" . BASE_URL . '?page=user-registration');
                    }
                } else if(isset($_POST['userEdit'])) { // EDITACE uživatele
                    $_SESSION["editingUserId"] = $_POST['userId']; // uložení ID uživatele, kterého dal admin editovat
                    $userInfo = Account::getUserById($_SESSION["editingUserId"]);
                    $_SESSION["userEditTemp"] = ["first_name" => $userInfo['first_name'],
                        "last_name" => $userInfo['last_name']];
                    header("Location:" . BASE_URL . '?page=user-edit');
                } else if(isset($_POST['userDelete'])) {
                    if($_SESSION["userId"] != $_POST['userId']) {
                        Account::deleteUser(intval($_POST['userId']));
                        header("Location:" . BASE_URL . '?page=users');
                    } else {
                        // tuto akci nelze provést
                    }
                } else if(isset($_POST['saveUserEdit'])) {
                    // uložení údajů editačního formuláře
                    $_SESSION["userEditTemp"] = ["first_name" => $_POST['formUserEditFirstName'],
                        "last_name" => $_POST['formUserEditLastName']];
                    //==============================================================

                    if($_SESSION["editingUserId"] != $_SESSION["userId"]) { // uživatel je admin
                        if($_POST['formUserEditPwd1'] == '' and $_POST['formUserEditPwd2'] = '') { // prázdné heslo (nebude updatováno)
                            Account::updateUserAdmin($_SESSION["editingUserId"], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName'], NULL);
                        } else {
                            if($_POST['formUserEditPwd1'] == $_POST['formUserEditPwd2']) {
                                Account::updateUserAdmin($_SESSION["editingUserId"], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName'], $_POST['formUserEditPwd1']);
                            } else {
                                $_SESSION["errorMessage"] = "Nová hesla se neshodují.";
                            }
                        }
                    } else { // klasický uživatel
                        if($_POST['formUserEditPwd1'] == '' and $_POST['formUserEditPwd2'] = '') { // prázdné heslo (nebude updatováno)
                            Account::updateUser($_SESSION["userId"], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName'], NULL, NULL);
                        } else {
                            if($_POST['formUserEditPwd1'] == $_POST['formUserEditPwd2']) {
                                Account::updateUser($_SESSION["userId"], $_POST['formUserEditFirstName'], $_POST['formUserEditLastName'],
                                    $_POST['formUserEditOldPwd'], $_POST['formUserEditPwd1']);
                            } else {
                                $_SESSION["errorMessage"] = "Nová hesla se neshodují.";
                            }
                        }
                    }

                    if($_SESSION["errorMessage"] == NULL) {
                        header("Location:" . BASE_URL);
                        $_SESSION["userEditTemp"] = [];
                        $_SESSION["editingUserId"] = NULL;
                    } else {
                        header("Location:" . BASE_URL . '?page=user-edit');
                    }
                }
            }
            ?>
        </section>
    </div>
</div>

<footer>
    <div>
        <p>Vytvořil: Viktor Homolka</p>
    </div>
</footer>
</body>
</html>
