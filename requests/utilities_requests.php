<?php
    require_once "../functions/utilities_functions.php";
     require_once "../configs.php";
     $conn = openConn();
    print_r($_GET);
     if($_SERVER['REQUEST_METHOD'] === "GET"){
        if(isset($_GET['select_currency_value'])){
            
            $currency = selectCurrencyById($conn, (int) $_GET['currency_id']);
            if($currency && is_array($currency)){
                echo $currency['value'];
            }
        }
     }
?>