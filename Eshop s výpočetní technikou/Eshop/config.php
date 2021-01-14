<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'eshop');

// User-man
define('USER_ROLES_DEFINABLE', array('zakaznik'=>'Zákazník', 'zamestnanec'=>'Zaměstnanec', 'admin'=>'Administrátor'));
define('USER_ACTIVE', array('1'=>'Ano (1)', '0'=>'Ne (0)'));

// Eshop
define('VAT_MULTIPLIER', 1.21);
define('ORDER_STATUS', array(
    'Storno',
    'Nezaplaceno',
    'Zaplaceno',
    'Expedováno',
    'Vyřízeno'
));

define('BASE_URL' , "http://" . $_SERVER['SERVER_NAME'] . "/Eshop/");
?>
