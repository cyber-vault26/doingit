<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>window.history.back();</script>";
    exit();
}
require_once '../configs.php';
require_once "../functions/purchase_request_functions.php";
require_once "../functions/notification_functions.php";
$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(isset($_POST['create_purchase_request'])){
        $created_by = (int) $_SESSION['userid'];
        $ref_no = $_POST['ref_no'];
        $project_ids = $_POST['project_id'];
        $request_heading = $_POST['request_heading'];
        $date_ = $_POST['date_'];
        $currency = (int) $_POST['currency'];
        $currency_rate = floatval(str_replace(',', '', $_POST['curr']));
        $subtotal = floatval(str_replace(',', '', $_POST['subtotal']));
        $total_amount = floatval(str_replace(',', '', $_POST['totalAmount']));
        $descriptions = $_POST['description'];
        $quantities = $_POST['quantity'];
        $units = $_POST['unit'];
        $prices = $_POST['price'];
        $amounts = $_POST['amount'];
        $amounts_eqv = $_POST['amount_eqv'];
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
            $purchase_request_id = createPurchaseRequest($conn, $ref_no, $date_, $request_heading, $currency, $currency_rate, $subtotal, $total_amount, $created_by);
            if(!is_numeric($purchase_request_id)) throw new Exception($purchase_request_id);
            for($i = 0; $i < count($min_item_ids); $i++){
                $item_id = (int) $min_item_ids[$i];
                $unit = (int) $units[$i];
                $quantity = floatval(str_replace(',', '', $quantities[$i]));
                $price = floatval(str_replace(',', '', $prices[$i]));
                $amount = floatval(str_replace(',', '', $amounts[$i]));
                $amount_eqv = floatval(str_replace(',', '', $amounts_eqv[$i]));
                $project_id=(int)  $project_ids[$i];
                $item = createPurchaseRequestItem($conn, $purchase_request_id,$project_id, $item_id, $unit, $quantity, $price, $amount, $amount_eqv, $created_by);
                if(!is_numeric($item)) throw new Exception($item);
            }
            $conn->commit();
            setNotification('success', 'Purchase Request created successfully');
            echo "<script>window.location.href='../?page=request_purchase';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if(isset($_POST['update_purchase_request_status'])){
        $purchase_request_id = (int) $_POST['purchase_request_id'];
        $status = $_POST['status'];
        $updated_by = (int) $_SESSION['userid'];
        $update = updatePurchaseRequestStatus($conn, $purchase_request_id, $status, $updated_by);
        if($update === true){
            setNotification('success', 'Purchase Request status updated successfully');
            echo "<script>window.location.href='../?page=pending_purchase_request_list';</script>";
        } else {
            echo $update;
        }
    }

    if(isset($_POST['delete_purchase_request'])){
        $purchase_request_id = (int) $_POST['purchase_request_id'];
        $deleted_by = (int) $_SESSION['userid'];
        $delete = softDeletePurchaseRequest($conn, $purchase_request_id, $deleted_by);
        $deleteItems = softDeletePurchaseRequestItemsByPurchaseRequestId($conn, $purchase_request_id, $deleted_by);
        if($delete === true && $deleteItems === true){
            setNotification('success', 'Purchase Request deleted successfully');
            echo "<script>window.location.href='../?page=pending_purchase_request_list';</script>";
        } else {
            echo $delete;
        }
    }
    
    
    if(isset($_POST['pr_mark_checked'])){
        $id = (int) $_POST['purchase_request_id'];
        $result = checkPurchaseRequest($conn, $id, (int)$_SESSION['userid']);
        if($result !== true){
            echo $result;
            return;
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['pr_mark_reviewed'])){
        $id = (int) $_POST['purchase_request_id'];
        $result = reviewPurchaseRequest($conn, $id, (int)$_SESSION['userid']);
        if($result !== true){
            echo $result;
            return;
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['pr_mark_approved'])){
        $id = (int) $_POST['purchase_request_id'];
        $result = approvePurchaseRequest($conn, $id, (int)$_SESSION['userid']);
        if($result !== true){
            echo $result;
            return;
        }
        echo "SUCCESS";
    }
    
    if(isset($_POST['pr_reject'])){
        $id = (int) $_POST['purchase_request_id'];
        $reason = $_POST['rejection_reason'];
        $result = rejectPurchaseRequest($conn, $id, (int)$_SESSION['userid'], $reason);
        if($result !== true){
            echo $result;
            return;
        }
        echo "SUCCESS";
    }
    
    
}
?>