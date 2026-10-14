<?php
     
      require_once "../configs.php";
    require_once "../functions/transaction_functions.php";
    $conn = openConn();
     if($_SERVER['REQUEST_METHOD'] === "GET"){
      
       // print_r($_GET);
       if(isset($_GET['get_transaction_credit'])){
        $data = [];
         $transactionId = (int) $_GET['id'];
         $transactionDetails = selectTransactionById($conn, $transactionId);
         if($transactionDetails && is_array($transactionDetails)){
              $data[] = [
                "id" => $transactionDetails['cr_account'],
                "name" => $transactionDetails['credit_acc'],
              ];
         }
         echo json_encode($data);
       }
       if(isset($_GET['get_transaction_debit'])){
        $data = [];
         $transactionId = (int) $_GET['id'];
         $transactionDetails = selectTransactionById($conn, $transactionId);
         if($transactionDetails && is_array($transactionDetails)){
              $data[] = [
                "id" => $transactionDetails['dr_account'],
                "name" => $transactionDetails['debit_acc'],
              ];
         }
         echo json_encode($data);
       }
     }
?>