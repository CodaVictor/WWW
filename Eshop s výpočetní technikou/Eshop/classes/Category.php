<?php


class Category
{
    // return = {category_id, category_name, note}
    public static function getCategories() {
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('SELECT * FROM categories');
        try {
            if(!$stmt->execute()) {
                return false;
            } else {
                return $stmt->fetchAll();
            }
        } catch (PDOException $exception) {
            return false;
        }
    }

    public static function getCategoryById($categoryId) {
        if(!is_numeric($categoryId)) {
            return false;
        }
        $conn = Connection::getPdoInstance();

        $stmt = $conn->prepare('SELECT * FROM categories WHERE category_id = :inCategoryId');
        $stmt->bindParam(':inCategoryId', $categoryId, PDO::PARAM_INT);

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
}