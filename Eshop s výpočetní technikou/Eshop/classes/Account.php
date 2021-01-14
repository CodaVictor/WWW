<?php


class Account
{
    const USER_ID = 'user_id';
    const USER_FIRST_NAME = 'first_name';
    const USER_LAST_NAME = 'last_name';

    const ORDER_RULE_DESC = 'DESC'; // Sestupně
    const ORDER_RULE_ASC = 'ASC'; // Vzestupně

    // Zajišťuje načtení uživatelského profilu na základě emailu
    public static function loginUser($email) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, role, active FROM users WHERE email = :inEmail");
        $stmt->bindParam(':inEmail', $email);
        if(!$stmt->execute()) {
            return false;
        }
        $user = $stmt->fetch();
        if($user) {
            $_SESSION["loggedUser"] = true;
            $_SESSION["userId"] = $user["user_id"];
            $_SESSION["userEmail"] = $user["email"];
            $_SESSION["userFullName"] = $user["first_name"] . ' ' . $user["last_name"];
            $_SESSION["role"] = $user["role"];
            return true;
        } else {
            return false;
        }
    }

    // Vrátí heslo uživatele (uloženo jako hash)
    public static function getUserPassword($email) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT password FROM users WHERE email = :inEmail');
        $stmt->bindParam(':inEmail', $email);
        if(!$stmt->execute()) {
            return false;
        }
        return $stmt->fetch()['password'];
    }

    public static function getUserActive($email) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT active FROM users WHERE email = :inEmail');
        $stmt->bindParam(':inEmail', $email);
        if(!$stmt->execute()) {
            return false;
        }
        return $stmt->fetch()['active'];
    }

    public static function updateSessionUserData() {
        $newData = self::getUserById($_SESSION["userId"]);
        $_SESSION["userEmail"] = $newData["email"];
        $_SESSION["userFullName"] = $newData["first_name"] . ' ' . $newData["last_name"];
        $_SESSION["role"] = $newData["role"];
    }

    public static function existUser($email) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE email = :inEmail');
        $stmt->bindParam(':inEmail', $email);

        try {
            if($stmt->execute()) {
                $result = $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }

        return intval($result[0]) == 1;
    }

    public static function addUser($firstName, $lastName, $email, $password, $phone, $role, $active, $vatNumber) // Registruje nového uživatele
    {
        $conn = Connection::getPdoInstance();

        /* Kontrola existence uživatele se stejným emailem */
        $checkStmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE email = :inEmail');
        $checkStmt->bindParam(':inEmail', $email);

        try {
            if($checkStmt->execute()) {
                $existUser = $checkStmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }

        // Pokud existuje uživatel s tímto emailem, nemohu provést registraci
        if(intval($existUser[0]) == 0) {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, role, active, vat_number) 
              VALUES (:inFirstName, :inLastName, :inEmail, :inPassword, :inPhone, :inRole, :inActive, :inVatNumber)");
            $stmt->bindParam(':inFirstName', $firstName);
            $stmt->bindParam(':inLastName', $lastName);
            $stmt->bindParam(':inEmail', $email);
            $stmt->bindParam(':inPassword', password_hash($password, PASSWORD_DEFAULT));
            $stmt->bindParam(':inPhone', $phone);
            $stmt->bindParam(':inRole', $role);
            $stmt->bindParam(':inActive', $active);
            $stmt->bindParam(':inVatNumber', $vatNumber);

            try {
                if($stmt->execute()) {
                    return true;
                } else {
                    return false;
                }
            } catch (PDOException $exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function existEmail($email) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE email = :inEmail');
        $stmt->bindParam(':inEmail', $email);

        try {
            if($stmt->execute()) {
                $result = $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }

        if(intval($result[0]) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkUserPassword($userId, $userPassword) : bool {
        if(!is_numeric($userId)) {
            return false;
        }

        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE user_id = :inUserId AND password = :inPassword");
        $stmt->bindParam(':inUserId', intval($userId));
        $stmt->bindParam(':inPassword', $userPassword);

        try {
            if($stmt->execute()) {
                $result = $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }

        if(intval($result[0]) == 1) {
            return true;
        } else {
            return false;
        }
    }

    /*
    public static function updateUserAll($userId, $firstName, $lastName, $password) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('UPDATE users SET first_name = :inFirstName, 
                            last_name = :inLastName, password = :inPassword WHERE user_id = :inUserId');
        $stmt->bindParam(':inFirstName', $firstName);
        $stmt->bindParam(':inLastName', $lastName);
        $stmt->bindParam(':inPassword', $password);
        $stmt->bindParam(':inUserId', intval($userId));
        $stmt->execute();
    }
    */

    public static function updateUser($userId, $firstName, $lastName, $email, $password, $phone, $role, $active, $vatNumber) {
        if(!is_numeric($userId) OR !is_numeric($active)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        // Kontrola
        $stmtCheck = $conn->prepare('SELECT COUNT(*) FROM users WHERE user_id = :inUserId');
        $stmtCheck->bindParam(':inUserId', $userId);

        try {
            if($stmtCheck->execute()) {
                $result = $stmtCheck->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }

        if(intval($result[0]) == 1) {
            // Úprava údajů
            $query = 'UPDATE users SET first_name = :inFirstName, last_name = :inLastName, email = :inEmail, phone = :inPhone, 
              role = :inRole, active = :inActive, vat_number = :inVatNumber';
            if(!is_null($password)) {
                $query .= ', password = :inPassword';
            }

            $query .= ' WHERE user_id = :inUserId';
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':inFirstName', $firstName);
            $stmt->bindParam(':inLastName', $lastName);
            $stmt->bindParam(':inEmail', $email);
            $stmt->bindParam(':inPhone', $phone);
            $stmt->bindParam(':inRole', $role);
            $stmt->bindParam(':inActive', $active);
            $stmt->bindParam(':inVatNumber', $vatNumber);
            $stmt->bindParam(':inUserId', $userId);

            if(!is_null($password)) {
                $stmt->bindParam(':inPassword', password_hash($password, PASSWORD_DEFAULT));
            }

            try {
                if($stmt->execute()) {
                    return true;
                } else {
                    return false;
                }
            } catch (PDOException $exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    // return = array{user_id, first_name, last_name, email, password, phone, role, active, vat_number}
    public static function getAllUsers($limit = 30, $offset = 0, $orderBy = self::USER_ID, $orderRule = self::ORDER_RULE_ASC) {
        if(!is_numeric($limit) OR !is_numeric($offset)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $query = 'SELECT user_id, first_name, last_name, email, phone, role, active, vat_number FROM users ';
        $query .= 'ORDER BY ';
        $query .= self::checkOrderBy($orderBy) . ' ';
        $query .= self::checkOrderRule($orderRule) . ' ';
        $query .= 'LIMIT :inLimit OFFSET :inOffset';

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':inLimit', $limit,PDO::PARAM_INT);
        $stmt->bindParam(':inOffset', $offset,PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return $stmt->fetchAll();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getUserById($userId) {
        if(!is_numeric($userId)){
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('SELECT user_id, first_name, last_name, email, phone, role, active, vat_number
          FROM users WHERE user_id = :inUserId');
        $stmt->bindParam(':inUserId', $userId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getUserByEmail($userEmail) {
        $conn = Connection::getPdoInstance();
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = :inUserEmail');
        $stmt->bindParam(':inUserEmail',$userEmail);

        try {
            if($stmt->execute()) {
                return $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function deleteUser($userId) {
        if(!is_numeric($userId)){
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('DELETE FROM users WHERE user_id = :inUserId');
        $stmt->bindParam(':inUserId', $userId);

        try {
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function addAddress($userId, $city, $street, $zipCode, $type) {
        if(!is_numeric($userId)){
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('INSERT INTO addresses (city, street, zip_code, type, users_user_id) 
            VALUES (:inCity, :inStreet, :inZipCode, :inType, :inUserId)');
        $stmt->bindParam(':inCity', $city);
        $stmt->bindParam(':inStreet', $street);
        $stmt->bindParam(':inZipCode', $zipCode);
        $stmt->bindParam(':inType', $type);
        $stmt->bindParam(':inUserId', $userId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getAddress($addressId) {
        if(!is_numeric($addressId)){
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('SELECT * FROM addresses WHERE address_id = :inAddressId');
        $stmt->bindParam(':inAddressId', $addressId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function updateAddress($userId, $city, $street, $zipCode, $type) {
        if(!is_numeric($userId)){
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('UPDATE addresses SET city = :inCity, street = :inStreet, 
            zip_code = :inZipCode, type = :inType WHERE users_user_id = :inUserId');
        $stmt->bindParam(':inCity', $city);
        $stmt->bindParam(':inStreet', $street);
        $stmt->bindParam(':inZipCode', $zipCode);
        $stmt->bindParam(':inType', $type);
        $stmt->bindParam(':inUserId', $userId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getUserAddress($userId) {
        if(!is_numeric($userId)){
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('SELECT * FROM addresses WHERE users_user_id = :inUserId');
        $stmt->bindParam(':inUserId', $userId, PDO::PARAM_INT);

        try {
            if($stmt->execute()) {
                return $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getUserIdFromDataSet($dataSet) : array {
        $idArray = array();
        foreach ($dataSet as $row) {
            $idArray[] = $row['user_id'];
        }
        return $idArray;
    }

    private function checkOrderBy($option) {
        switch ($option) {
            case self::USER_ID:
                return self::USER_ID;
            case self::USER_FIRST_NAME:
                return self::USER_FIRST_NAME;
            case self::USER_LAST_NAME:
                return self::USER_LAST_NAME;
            default:
                return self::USER_ID;
        }
    }

    private function checkOrderRule($rule) {
        switch ($rule) {
            case self::ORDER_RULE_DESC:
                return self::ORDER_RULE_DESC;
            case self::ORDER_RULE_ASC:
                return self::ORDER_RULE_ASC;
            default:
                return self::ORDER_RULE_ASC;
        }
    }
}
