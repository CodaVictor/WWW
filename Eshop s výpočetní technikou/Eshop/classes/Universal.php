<?php


class Universal
{
    // Odstraní mezery a převede nepovolené znaky na povolené
    public static function cureString($string) {
        $string = trim($string);
        $string = stripslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }

    // Vykopíruje sloupec z datasetu
    public static function getColumnFromDataSet($dataSet, $columnName) : array {
        $colData = array();
        foreach ($dataSet as $row) {
            $colData[] = $row[$columnName];
        }
        return $colData;
    }
}