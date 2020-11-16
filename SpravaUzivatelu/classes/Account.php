<?php


class Account
{
    public static function loginUser($email, $password) // zajišťuje přihlášení načtení uživatelského profilu na základě emailu a hesla
    {
        $conn = Connection::getPdoInstance();
        /*Vložených přístupových údajů z parametru do SQL dotazu*/
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, role FROM spravauzivatelu.users WHERE email = :inEmail AND password = :inPassword");
        $stmt->bindParam(':inEmail', $email);
        $stmt->bindParam(':inPassword', $password);
        $stmt->execute();

        /*Načtení vrácenách hodnot z databáze*/
        $user = $stmt->fetch();
        $_SESSION["errorMessage"] = NULL;
        if(!$user) {
            $_SESSION["errorMessage"] = "Neplatné příhlašovací údaje.";
        } else {
            $_SESSION["loggedUser"] = true;
            $_SESSION["userId"] = $user["user_id"];
            $_SESSION["loggedUserEmail"] = $user["email"];
            $_SESSION["loggedUserFullName"] = $user["first_name"] . ' ' . $user["last_name"];
            $_SESSION["role"] = $user["role"];
        }
    }

    public static function registrationUser($firstName, $lastName, $email, $password, $role) // registruje nového uživatele
    {
        $conn = Connection::getPdoInstance();
        /*Vložených registračních údajů z parametrů do SQL dotazu*/
        /*Nejprve se musím ujistit, zda již není nějaký uživatel registrován pod stejnýn emailem*/
        $stmt = $conn->prepare('SELECT COUNT(*) FROM spravauzivatelu.users WHERE email = :inEmail');
        $stmt->bindParam(':inEmail', $email);
        $stmt->execute();

        $exUser = $stmt->fetch();
        $_SESSION["errorMessage"] = NULL;

        /*Pokud existuje uživatel s tímto emailem, nemohu provést registraci*/
        if(intval($exUser[0]) != 0) {
            $_SESSION["errorMessage"] = "Uživatel s tímto emailem je již registrován.";
            //$_SESSION["errorMessage"] = $exUser[0];
        } else {
            $stmt = $conn->prepare("INSERT INTO spravauzivatelu.users (first_name, last_name, email, password, role) 
              VALUES (:inFirstName, :inLastName, :inEmail, :inPassword, :inRole)");
            $stmt->bindParam(':inFirstName', $firstName);
            $stmt->bindParam(':inLastName', $lastName);
            $stmt->bindParam(':inEmail', $email);
            $stmt->bindParam(':inPassword', $password);
            $stmt->bindParam(':inRole', $role);
            $stmt->execute();
        }
    }

    public static function updateUserAdmin($userId, $firstName, $lastName, $password) {
        $conn = Connection::getPdoInstance();
        if($password == NULL) {
            $stmt = $conn->prepare('UPDATE spravauzivatelu.users SET first_name = :inFirstName, 
                                last_name = :inLastName WHERE user_id = :inUserId');
            $stmt->bindParam(':inFirstName', $firstName);
            $stmt->bindParam(':inLastName', $lastName);
            $stmt->bindParam(':inUserId', intval($userId));
        } else {
            $stmt = $conn->prepare('UPDATE spravauzivatelu.users SET first_name = :inFirstName, 
                                last_name = :inLastName, password = :inPassword WHERE user_id = :inUserId');
            $stmt->bindParam(':inFirstName', $firstName);
            $stmt->bindParam(':inLastName', $lastName);
            $stmt->bindParam(':inPassword', $password);
            $stmt->bindParam(':inUserId', intval($userId));
        }
        $stmt->execute();
    }

    public static function updateUser($userId, $firstName, $lastName, $oldPassword, $newPassword) {
        $conn = Connection::getPdoInstance();
        if($oldPassword == NULL or $newPassword == NULL) { // update pouze jména a příjmení
            updateUserAdmin($userId, $firstName, $lastName, NULL);
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM spravauzivatelu.users WHERE user_id = :inUserId AND password = :inOldPassword");
            $stmt->bindParam(':inUserId', intval($userId));
            $stmt->bindParam(':inOldPassword', $oldPassword);
            $stmt->execute();

            $matchPwd = $stmt->fetch();
            $_SESSION["errorMessage"] = NULL;
            if(intval($matchPwd[0]) != 1) {
                $_SESSION["errorMessage"] = "Neplatné staré heslo.";
            } else {
                updateUserAdmin($userId, $firstName, $lastName, $newPassword);
            }
        }
    }

    public static function getAllUsersFromDB() { // načte všechny uživatelské profil z databáze
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT user_id, first_name, last_name, email, role FROM spravauzivatelu.users');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getUserIdFromDataSet($dataSet) : array {
        $idArray = array();
        foreach ($dataSet as $row) {
            $idArray[] = $row['user_id'];
        }
        return $idArray;
    }

    public static function getUserById($userId) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT user_id, first_name, last_name FROM spravauzivatelu.users WHERE user_id = :inUserId');
        $stmt->bindParam(':inUserId', intval($userId));
        $stmt->execute();
        $user = $stmt->fetchAll();
        return array("user_id"=>$user[0][0], "first_name"=>$user[0][1], "last_name"=>$user[0][2]);
    }

    public static function deleteUser($userId) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('DELETE FROM spravauzivatelu.users WHERE user_id = :inUserId');
        $stmt->bindParam(':inUserId', $userId);
        $stmt->execute();
    }
}
