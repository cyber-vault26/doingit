<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>window.history.back();</script>";
    exit();
}
require_once '../configs.php';
require_once "../functions/purchase_order_functions.php";
require_once "../functions/notification_functions.php";
$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(isset($_POST['create_purchase_order'])){
        $created_by = (int) $_SESSION['userid'];
        $ref_no = $_POST['ref_no'];
        $supplier_id = (int) $_POST['supplier_id'];
        $project_id = (int) $_POST['project_id'];
        $invoice_heading = $_POST['invoice_heading'];
        $date_ = $_POST['date'];
        $expiry_date = $_POST['expiry_date'];
        $currency = (int) $_POST['currency'];
        $currency_rate = floatval(str_replace(',', '', $_POST['curr']));
        $subtotal = floatval(str_replace(',', '', $_POST['subtotal']));
        $tax_rate = floatval($_POST['tax_rate']);
        $tax_amount = floatval(str_replace(',', '', $_POST['tax_amount']));
        $total_amount = floatval(str_replace(',', '', $_POST['totalAmount']));
        $descriptions = $_POST['description'];
        $quantities = $_POST['quantity'];
        $units = $_POST['unit'];
        $prices = $_POST['price'];
        $amounts = $_POST['amt'];
        $eqv_amounts = $_POST['amount'];
        $payment_terms = $_POST['payment_terms'];
        $min_item_ids = [];
        $conn->begin_transaction();
        try {
            foreach($descriptions as $desc){
                $item_description = $conn->real_escape_string($desc);
                $check_result = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
                if($check_result && $check_result->num_rows > 0){
                    $min_item_ids[] = $check_result->fetch_assoc()['id'];
                } else {
                    if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting item: " . $conn->error);
                    $min_item_ids[] = $conn->insert_id;
                }
            }
            $purchase_order_id = createPurchaseOrder($conn, $ref_no, $date_, $expiry_date, $supplier_id, $project_id, $invoice_heading, $payment_terms, $currency, $currency_rate, $subtotal, $tax_rate, $tax_amount, $total_amount, $created_by);
            if(!is_numeric($purchase_order_id)) throw new Exception($purchase_order_id);
            for($i = 0; $i < count($min_item_ids); $i++){
                $item_id = (int) $min_item_ids[$i];
                $unit = (int) $units[$i];
                $quantity = floatval(str_replace(',', '', $quantities[$i]));
                $price = floatval(str_replace(',', '', $prices[$i]));
                $amount = floatval(str_replace(',', '', $amounts[$i]));
                $eqv_amount = floatval(str_replace(',', '', $eqv_amounts[$i]));
                $item = createPurchaseOrderItem($conn, $purchase_order_id, $item_id, $unit, $quantity, $price, $amount, $eqv_amount, $created_by);
                if(!is_numeric($item)) throw new Exception($item);
            }
            $conn->commit();
            setNotification('success', 'Purchase Order created successfully');
            echo "<script>window.location.href='../?page=create_purchase_order';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if(isset($_POST['update_purchase_order_status'])){
        $purchase_order_id = (int) $_POST['purchase_order_id'];
        $status = $_POST['status'];
        $updated_by = (int) $_SESSION['userid'];
        $update = updatePurchaseOrderStatus($conn, $purchase_order_id, $status, $updated_by);
        if($update === true){
            setNotification('success', 'Purchase Order status updated successfully');
            echo "<script>window.location.href='../?page=pending_purchase_order_list';</script>";
        } else {
            echo $update;
        }
    }
    
    if(isset($_POST['update_purchase_order'])){
        $purchase_order_id = (int) $_POST['purchase_order_id'];
        $updated_by = (int) $_SESSION['userid'];
        $ref_no = $_POST['ref_no'];
        $supplier_id = (int) $_POST['supplier_id'];
        $project_id = (int) $_POST['project_id'];
        $invoice_heading = $_POST['invoice_heading'];
        $date_ = $_POST['date'];
        $expiry_date = $_POST['expiry_date'];
        $currency = (int) $_POST['currency'];
        $currency_rate = floatval(str_replace(',', '', $_POST['curr']));
        $subtotal = floatval(str_replace(',', '', $_POST['subtotal']));
        $tax_rate = floatval($_POST['tax_rate']);
        $tax_amount = floatval(str_replace(',', '', $_POST['tax_amount']));
        $total_amount = floatval(str_replace(',', '', $_POST['totalAmount']));
        $descriptions = $_POST['description'];
        $quantities = $_POST['quantity'];
        $units = $_POST['unit'];
        $prices = $_POST['price'];
        $amounts = $_POST['amt'];
        $eqv_amounts = $_POST['amount'];
        $payment_terms = $_POST['payment_terms'];
        $existing_item_ids = $_POST['existing_item_id'] ?? [];
        
        $min_item_ids = [];
        $conn->begin_transaction();
        try {
            foreach($descriptions as $desc){
                $item_description = $conn->real_escape_string($desc);
                $check_result = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
                if($check_result && $check_result->num_rows > 0){
                    $min_item_ids[] = $check_result->fetch_assoc()['id'];
                } else {
                    if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting item: " . $conn->error);
                    $min_item_ids[] = $conn->insert_id;
                }
            }
            
            $update = updatePurchaseOrder($conn, $purchase_order_id, $ref_no, $date_, $expiry_date, $supplier_id, $project_id, $invoice_heading, $payment_terms, $currency, $currency_rate, $subtotal, $tax_rate, $tax_amount, $total_amount, $updated_by);
            if($update !== true) error_log("Error updating purchase order: " . $update);
            
            // Get existing items to determine which ones to delete
            $existing_items = selectPurchaseOrderItemsByPurchaseOrderId($conn, $purchase_order_id);
            $submitted_item_ids = array_filter($existing_item_ids);
            $items_to_delete = [];
            
            foreach($existing_items as $item){
                if(!in_array($item['id'], $submitted_item_ids)){
                    $items_to_delete[] = $item['id'];
                }
            }
            
            // Only delete items that were actually removed in UI
            foreach($items_to_delete as $item_id){
                softDeletePurchaseOrderItem($conn, $item_id, $updated_by);
            }
            
            for($i = 0; $i < count($min_item_ids); $i++){
                $item_id = (int) $min_item_ids[$i];
                $unit = (int) $units[$i];
                $quantity = floatval(str_replace(',', '', $quantities[$i]));
                $price = floatval(str_replace(',', '', $prices[$i]));
                $amount = floatval(str_replace(',', '', $amounts[$i]));
                $eqv_amount = floatval(str_replace(',', '', $eqv_amounts[$i]));
                
                if(isset($existing_item_ids[$i]) && !empty($existing_item_ids[$i])){
                    // Update existing item
                    $item = updatePurchaseOrderItem($conn, (int)$existing_item_ids[$i], $item_id, $unit, $quantity, $price, $amount, $eqv_amount, $updated_by);
                    if($item <0) error_log("Error updating purchase order item: here  " . $item);
                } else {
                    // Create new item
                    $item = createPurchaseOrderItem($conn, $purchase_order_id, $item_id, $unit, $quantity, $price, $amount, $eqv_amount, $updated_by);
                    if(!is_numeric($item)) error_log("Error creating purchase order item: " . $item);
                }
            }
            $conn->commit();
            setNotification('success', 'Purchase Order updated successfully');
            echo "<script>window.location.href='../?page=pending_purchase_order_list';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    if(isset($_POST['delete_purchase_order'])){
        $purchase_order_id = (int) $_POST['purchase_order_id'];
        $deleted_by = (int) $_SESSION['userid'];
        $delete = softDeletePurchaseOrder($conn, $purchase_order_id, $deleted_by);
        $deleteItems = softDeletePurchaseOrderItemsByPurchaseOrderId($conn, $purchase_order_id, $deleted_by);
        if($delete === true && $deleteItems === true){
            setNotification('success', 'Purchase Order deleted successfully');
            echo "<script>window.location.href='../?page=pending_purchase_order_list';</script>";
        } else {
            echo $delete;
        }
    }
}
?>