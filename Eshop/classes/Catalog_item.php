<?php


class Catalog_item {
    public $product_id;
    public $product_name;
    public $product_code;
    public $price;
    public $number_in_stock;
    public $listed_date;
    public $catalog_code;
    public $image_path;
    public $specs;

    /**
     * Catalog_item constructor.
     * @param $product_id
     * @param $product_name
     * @param $product_code
     * @param $price
     * @param $number_in_stock
     * @param $listed_date
     * @param $catalog_code
     * @param $image_path
     * @param $specs
     */
    public function init($product_id, $product_name, $product_code, $price, $number_in_stock, $listed_date, $catalog_code, $image_path, $specs)
    {
        $this->product_id = $product_id;
        $this->product_name = $product_name;
        $this->product_code = $product_code;
        $this->price = $price;
        $this->number_in_stock = $number_in_stock;
        $this->listed_date = $listed_date;
        $this->catalog_code = $catalog_code;
        $this->image_path = $image_path;
        $this->specs = $specs;
    }

    /**
     * @return bool|float|int
     */
    public function getPriceWithVat() {
        if(isset($this->price)) {
            return round($this->price * VAT_MULTIPLIER);
        } else {
            return false;
        }
    }

    public function getAsArray() : array{
        $array = array('product_id' => $this->product_id, 'product_name' => $this->product_name,
            'product_code' => $this->product_code, 'price' => $this->price, 'price_with_vat' => $this->getPriceWithVat(),
            'number_in_stock' => $this->number_in_stock, 'listed_date' => $this->listed_date,
            'catalog_code' => $this->catalog_code, 'image_path' => $this->image_path, 'specs' => $this->specs);
    }
}