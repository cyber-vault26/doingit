<?php
session_start();
if(!$_SESSION){
    echo "<script>window.history.back();</script>";
    exit;
}
require_once "../configs.php";
require_once "../functions/stock_date_request_functions.php";
require_once "../functions/notification_functions.php";
$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $created_by = (int) $_SESSION['userid'];

   if(isset($_POST['send_stock_date_request'])){
        $user_id          = $created_by;
        $requested_date   = $conn->real_escape_string(trim($_POST['requested_date']));
        $transaction_type = $conn->real_escape_string(trim($_POST['transaction_type']));
        $reason           = $conn->real_escape_string(trim($_POST['reason']));
        try {
            $conn->begin_transaction();
            $existing = checkStockDateRequestExists($conn, $user_id, $requested_date, $transaction_type);
                if($existing && $existing['status'] === 'pending'){
                    throw new Exception('You already have a pending request for this date and transaction type.');
                }
                if($existing && $existing['status'] === 'approved'){
                    throw new Exception('This date is already approved for you.');
                }
                $insert = insertStockDateRequest($conn, $user_id, $requested_date, $transaction_type, $reason, $created_by);
                if(!is_numeric($insert) || $insert <= 0){
                    throw new Exception($insert);
                }
            $conn->commit();
            setNotification('success', 'Date request submitted successfully. Awaiting admin approval.');
            echo "<script>window.location.href='../';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
        exit;
    }
    
    
    
    if(isset($_POST['review_stock_date_request'])){
        $request_id  = (int) $_POST['request_id'];
        $decision    = $_POST['decision'];
        $admin_note  = trim($_POST['admin_note']);
        $approved_by = (int) $_SESSION['userid'];
    
        try {
            $conn->begin_transaction();
            if($decision === 'approve'){
                $result = approveStockDateRequest($conn, $request_id, $approved_by, $admin_note);
            } elseif($decision === 'reject'){
                $result = rejectStockDateRequest($conn, $request_id, $approved_by, $admin_note);
            } else {
                throw new Exception('Invalid decision selected.');
            }
            if($result !== true){
                throw new Exception($result);
            }
            $conn->commit();
            setNotification('success', 'Request has been ' . ($decision === 'approve' ? 'approved' : 'rejected') . ' successfully.');
            echo "<script>window.location.href='../?page=stock_date_requests';</script>";
        } catch (Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
        exit;
    }
}