<?php


class DataTable
{
    private $dataSet;
    private $columnArray;
    private $customTableMark; // značka tabulky + vlastní styl (nebo class/id)
    private $customColumn;
    private $customContent;

    //===================
    private $customFormMark;
    private $customFormItems;
    private $customHiddenData;
    private $customHiddenDataName;

    public function __construct($dataSet) {
        $this->dataSet = $dataSet;
        $this->customTableMark = NULL;
        $this->customColumn = array();
        $this->customContent = array();

        //=======================
        $this->customFormMark = NULL;
        $this->customFormItems = array();
        $this->customHiddenData = array();
    }

    // Pracuje z daty, která byla výsledkem dotazu z databáze
    public function setDBDataSet($dbDataSet) {
        $this->dataSet = $dbDataSet;
    }

    // Pracuje s daty, která jsou definována klíčem a hodnotou
    public function setArrayDataSet($arrayDataSet) {
        $this->dataSet = $arrayDataSet;
    }

    public function setCustomTableMark($tableMark) {
        $this->customTableMark = $tableMark;
    }

    public function addDbColumn($dbColumnName, $displayName) { // přidává sloupce ze vstupního datasetu a dává jim jména, která se budou zobrazovat
        $this->columnArray[$dbColumnName] = $displayName;
    }

    public function addCustomColumn($custColumnName) {
        $this->customColumn[] = $custColumnName;
    }

    public function addCustomContentRow($custContent) { // přidá na konec každého řádku vložený obsah
        $this->customContent[] = $custContent;
    }

    public function addActionFormRow($formMark, $formItemsArr, $hiddenDataArr, $hiddenDataName) { // přidá pro každý řádek data z indexu pole;
        // velikost $hiddenDataArr MUSÍ být stejná jako počet řádků tabulky!
        $this->customFormMark = $formMark;
        $this->customFormItems = $formItemsArr;
        $this->customHiddenData = $hiddenDataArr;
        $this->customHiddenDataName =$hiddenDataName;
    }

    public function render() { // zobrazí tabulku
        if($this->customTableMark != NULL) {
            echo $this->customTableMark;
        } else {
            echo '<table style="width: 100%">';
        }

        // hlavička tabulky
        echo '<tr>';
        foreach ($this->columnArray as $key => $header) {
            echo '<th>' . $header . '</th>';
        }
        foreach ($this->customColumn as $custCol) { // custom
            echo '<th>' . $custCol . '</th>';
        }
        echo '</tr>';
        //===========================

        // data tabulky
        $iCounter = 0;
        foreach ($this->dataSet as $row) {
            echo '<tr>';
            foreach ($this->columnArray as $keyHeadName => $value) {
                echo '<td>' . $row[$keyHeadName] . '</td>';
            }

            // action form pro každý řádek s unikátními daty
            echo '<td>';
            echo $this->customFormMark;
            foreach ($this->customFormItems as $item) {
                echo $item;
            }
            echo '<input type="hidden" id="custId" name="'. $this->customHiddenDataName .'" value="' . $this->customHiddenData[$iCounter] . '">';
            echo '</form>';
            echo '</td>';
            $iCounter++;

            // volitelný obsah pro každý konec řádku
            foreach ($this->customContent as $custCont) {
                echo '<td>' . $custCont . '</td>';
            }
            echo '</tr>';
        }
        //===========================
        echo '</table>';
    }
}