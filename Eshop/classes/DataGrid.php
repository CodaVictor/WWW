<?php

// Bude spoužit pouze jako jednoduchý obal pro libovolný obsah
// Položky se budou skládat pod sebe v požadí, v jakém byli přidány

class DataGrid
{
    private $items; // 1D Pole zdroje dat

    public function __construct($items) {
        $this->items = $items;
    }

    public function render() {
        foreach ($this->items as $item) {

        }
    }
}