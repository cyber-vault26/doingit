<?php
session_start();
if(!isset($_SESSION['userid'])){
    exit();
}
require_once "../configs.php";
require_once "../functions/voucher_functions.php";
require_once "../functions/transaction_functions.php";
require_once __DIR__ . "/../functions/project_functions.php";
//require_once "../functions/sub_transaction_functions.php"; 
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] ==='POST'){
    if(isset($_POST['delete_voucher'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i<count($ids); $i++){
            $deleteTransaction = softDeleteTransaction($conn, $ids[$i], $_SESSION['userid']);
            if(!$deleteTransaction){
                echo $deleteTransaction;
            }
        }
        echo "SUCCESS";
    }
    if(isset($_POST['approve_voucher'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i<count($ids); $i++){
            $id = (int) $ids[$i];
            $update = approveTransaction($conn, $id, $_SESSION['userid']);
            if(!$update){
                echo $update;
                return;
            }
        }
        echo "SUCCESS"; 
    }
    if(isset($_POST['reject_voucher'])){
        $ids = $_POST['selected_ids'];
        $reason = $conn->real_escape_string($_POST['rejection_reason']);
        for($i = 0; $i < count($ids); $i++){
            $id = (int) $ids[$i];
            $update = rejectTransaction($conn, $id, $_SESSION['userid'], $reason);
            if(!$update){
                echo $update;
                return;
            }
        }
        echo "SUCCESS";
    }
    if(isset($_POST['review_sales'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $result = reviewSalesVoucher($conn, (int)$ids[$i], $_SESSION['userid']);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    if(isset($_POST['approve_sales'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $result = approveSalesVoucher($conn, (int)$ids[$i], $_SESSION['userid']);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    if(isset($_POST['delete_sales'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i<count($ids); $i++){
            $delete = softDeleteSalesVoucher($conn, $ids[$i], $_SESSION['userid']);
            if(!$delete){
                echo $delete;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['review_purchase'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $result = reviewPurchaseVoucher($conn, (int)$ids[$i], $_SESSION['userid']);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['reject_sales'])){
        $ids = $_POST['selected_ids'];
        $reason = $conn->real_escape_string($_POST['rejection_reason']);
        for($i = 0; $i < count($ids); $i++){
            $result = rejectSalesVoucher($conn, (int)$ids[$i], $_SESSION['userid'], $reason);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['reject_purchase'])){
        $ids = $_POST['selected_ids'];
        $reason = $conn->real_escape_string($_POST['rejection_reason']);
        for($i = 0; $i < count($ids); $i++){
            $result = rejectPurchaseVoucher($conn, (int)$ids[$i], $_SESSION['userid'], $reason);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['approve_purchase'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $result = approvePurchaseVoucher($conn, (int)$ids[$i], $_SESSION['userid']);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }


    if(isset($_POST['delete_purchase'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i<count($ids); $i++){
            $delete = softDeletePurchaseVoucher($conn, $ids[$i], $_SESSION['userid']);
            if(!$delete){
                echo $delete;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['review_voucher'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $result = reviewTransaction($conn, $ids[$i], $_SESSION['userid']);
            if($result !== true){
                echo $result;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['review_bulky'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $result = reviewBulkyVoucher($conn, (int)$ids[$i], $_SESSION['userid']);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['approve_bulky'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $result = approveBulkyVoucher($conn, (int)$ids[$i], $_SESSION['userid']);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['reject_bulky'])){
        $ids = $_POST['selected_ids'];
        $reason = $conn->real_escape_string($_POST['rejection_reason']);
        for($i = 0; $i < count($ids); $i++){
            $result = rejectBulkyVoucher($conn, (int)$ids[$i], $_SESSION['userid'], $reason);
            if($result !== true){
                echo $result;
                return;
            }
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['delete_bulky'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $delete = softDeleteBulkyVoucher($conn, (int)$ids[$i], $_SESSION['userid']);
            if(!$delete){
                echo $delete;
                return;
            }
        }
        echo "SUCCESS";
    }
     if(isset($_POST['delete_project_item'])){
        $ids = $_POST['selected_ids'];
        for($i = 0; $i < count($ids); $i++){
            $delete = softDeleteBudgetItem($conn, (int)$ids[$i]);
            if(!$delete){
                echo $delete;
                return;
            }
        }
        echo "SUCCESS";
    }
    
}
?>