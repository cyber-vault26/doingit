<?php
session_start();
if(!$_SESSION){
    echo "<script>window.history.back();</script>";
}
require_once "../functions/opening_balance_functions.php";
require_once "../functions/notification_functions.php";
require_once "../configs.php";
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    
    if(isset($_POST['add_opening_balance'])){
        $created_by = (int) $_SESSION['userid'];
        $date = trim($conn->real_escape_string($_POST['date']));
        $currecny = trim($conn->real_escape_string($_POST['currency']));
        $currencyValue = (float) $_POST['curr'];
        $subIds = $_POST['item'];
        $amounts = $_POST['amount'];
        $types = $_POST['type'];
        $eqv_amounts = $_POST['eqv'];
        for($i = 0; $i < count($subIds); $i++){
            $subId = (int) $subIds[$i];
            $amount = (float) str_replace(',', '', $amounts[$i]);
            $type = trim($conn->real_escape_string($types[$i]));
            $eqv_amount = (float) str_replace(',', '', $eqv_amounts[$i]);
            $newOpeningBalance = addOpeningBalance($conn, $subId, $eqv_amount, $currecny, $currencyValue, $type, $date, $created_by);
            if(!$newOpeningBalance){
                echo "Failed to add opening balance";
                return;
            }
        }
        setNotification('success', 'Opening balance added successfully');
        echo "<script>window.location.href='../?page=opening_balance_list';</script>";
    }
    
    
   if(isset($_POST['edit_opening_balance'])){
        $updated_by = (int) $_SESSION['userid'];
        $id = (int) $_POST['id'];
        $account_id = (int) $_POST['account_id'];
        $amount = (float) str_replace(',', '', $_POST['amount']);
        $type = trim($conn->real_escape_string($_POST['type']));
        $date = trim($conn->real_escape_string($_POST['date']));
        $openingBalance = selectOpeningBalanceById($conn, $id);
            if($openingBalance && $openingBalance['reconciled'] === 'yes'){
                echo "You cannot edit a reconciled opening balance";
                return;
            }
            $updated = editOpeningBalance($conn, $id, $account_id, $amount, $type, $date, $updated_by);
            if(!$updated){
                echo "Failed to update opening balance";
                return;
            }
        setNotification('success', 'Opening balance updated successfully');
        echo "<script>window.location.href='../?page=opening_balance_list';</script>";
    }
}


if($_SERVER['REQUEST_METHOD'] === 'GET'){
    if(isset($_GET['delete_opneing_balance'])){
        $deleted_by = (int) $_SESSION['userid'];
        $id = (int) $_GET['delete_opneing_balance'];
        $openingBalance = selectOpeningBalanceById($conn, $id);
            if($openingBalance && $openingBalance['reconciled'] === 'yes'){
                echo "You cannot delete a reconciled opening balance";
                return;
            }
            $deleteBalance = deleteOpeningBalance($conn, $id, $deleted_by);
            if(!$deleteBalance){
                echo "Failed to delete opening balance";
                return;
            }
        setNotification('success', 'Opening balance deleted successfully');
        echo "<script>window.location.href='../?page=opening_balance_list';</script>";
    }
}
?>