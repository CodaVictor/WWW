<!-- využívá soubor CSS orders.css -->
<?php
$formDataName = 'orderId';
if(!($_SESSION['loggedUser'] && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'zamestnanec'))) {
    header("Location:" . BASE_URL);
}
?>

<div class="generalContainer">
    <div class="generalHeader1">Správa objednávek</div>

    <?php
    $dataSet = Order::getAllOrders(50, 0);
    $idArray = Universal::getColumnFromDataSet($dataSet, 'order_id');

    // Úprava času
    foreach ($dataSet AS $key => $value) {
        $dataSet[$key]['order_status'] = ORDER_STATUS[intval($dataSet[$key]['order_status'])];
        !empty($dataSet[$key]['order_date']) ? $dataSet[$key]['order_date'] = date("d. m. Y H:i:s", strtotime($value['order_date'])) : $dataSet[$key]['order_date'] = '';
        !empty($dataSet[$key]['shipped_date']) ? $dataSet[$key]['shipped_date'] = date("d. m. Y H:i:s", strtotime($value['shipped_date'])) : $dataSet[$key]['shipped_date'] = '';
    }

    $usersTable = new DataTable($dataSet);
    $usersTable->setCustomTableMark('<table class="generalTable">');
    $usersTable->addDbColumn('order_id', 'ID');
    $usersTable->addDbColumn('user_name', 'Objednavatel');
    $usersTable->addDbColumn('order_date', 'Datum odjednání');
    $usersTable->addDbColumn('shipped_date', 'Datum odeslání');
    $usersTable->addDbColumn('custom_order_id', 'Zákaznické č. obj.');
    $usersTable->addDbColumn('sum_price', 'Cena bez DPH');
    $usersTable->addDbColumn('order_status', 'Stav');
    $usersTable->addDbColumn('note', 'Poznámka');

    $usersTable->addCustomColumn('Akce');
    $usersTable->addActionFormRow(
    '<form class="actionBtnForm" method="post" action="?page=order-man-det">',
        array('<button class="generalButton2" type="submit" name="btnOrderDetailMan">Detail</button>'), $idArray, $formDataName);
    $usersTable->render();
    ?>
</div>