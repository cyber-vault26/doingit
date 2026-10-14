<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>window.history.back();</script>";
    exit();
}
require_once '../configs.php';
require_once "../functions/profoma_functions.php";
require_once "../functions/notification_functions.php";
$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

   if(isset($_POST['create_profoma'])){
        $created_by = (int) $_SESSION['userid'];
        $ref_no = $_POST['ref_no'];
        $customer_id = (int) $_POST['customer_id'];
        $invoice_heading = $_POST['invoice_heading'];
        $date_ = $_POST['date'];
        $currency = (int) $_POST['currency'];
        $currency_rate = floatval(str_replace(',', '', $_POST['curr']));
        $subtotal = floatval(str_replace(',', '', $_POST['subtotal']));
        $tax_amount = floatval(str_replace(',', '', $_POST['tax_amount']));
        $total_amount = floatval(str_replace(',', '', $_POST['totalAmount']));
        $items = $_POST['item'];
        $quantities = $_POST['quantity'];
        $units = $_POST['unit'];
        $prices = $_POST['price'];
        $amounts = $_POST['amt'];
        $eqv_amounts = $_POST['amount'];
        $tax_rates = $_POST['tax_rate'];
        $payment_terms = $_POST['payment_terms'];
        $expiry_date = $_POST['expiry_date'];
        $min_item_ids = [];
        $conn->begin_transaction();
        try {
             
            $profoma_id = createProfoma($conn, $ref_no, $date_, $expiry_date, $customer_id, $invoice_heading, $payment_terms, $currency, $currency_rate, $subtotal, $tax_amount, $total_amount, $created_by);
            if(!is_numeric($profoma_id)) throw new Exception($profoma_id);
            for($i = 0; $i < count($items); $i++){
                $item_id = (int) $items[$i];
                $unit = (int) $units[$i];
                $quantity = floatval(str_replace(',', '', $quantities[$i]));
                $price = floatval(str_replace(',', '', $prices[$i]));
                $amount = floatval(str_replace(',', '', $amounts[$i]));
                $eqv_amount = floatval(str_replace(',', '', $eqv_amounts[$i]));
                $tax_rate = floatval($tax_rates[$i]);
                $item = createProfomaItem($conn, $profoma_id, $item_id, $unit, $quantity, $tax_rate, $price, $amount, $eqv_amount, $created_by);
                if(!is_numeric($item)) throw new Exception($item);
            }
            $conn->commit();
            setNotification('success', 'Profoma created successfully');
            echo "<script>window.location.href='../?page=create_profoma';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['update_profoma_status'])){
        $profoma_id = (int) $_POST['profoma_id'];
        $status = $_POST['status'];
        $updated_by = (int) $_SESSION['userid'];
        $update = updateProfomaStatus($conn, $profoma_id, $status, $updated_by);
        if($update === true){
            setNotification('success', 'Profoma status updated successfully');
            echo "<script>window.location.href='../?page=pending_profoma_list';</script>";
        } else {
            echo $update;
        }
    }
    
    
    if(isset($_POST['delete_profoma'])){
        $profoma_id = (int) $_POST['profoma_id'];
        $deleted_by = (int) $_SESSION['userid'];
        $delete = softDeleteProfoma($conn, $profoma_id, $deleted_by);
        $deleteItems = softDeleteProfomaItemsByProfomaId($conn, $profoma_id, $deleted_by);
        if($delete === true && $deleteItems === true){
            setNotification('success', 'Profoma deleted successfully');
            echo "<script>window.location.href='../?page=profoma_list';</script>";
        } else {
            echo $delete;
        }
    }
}
?>