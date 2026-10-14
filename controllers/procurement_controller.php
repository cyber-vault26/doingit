<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>window.history.back();</script>";
    exit();
}
require_once '../configs.php';
require_once "../functions/procurement_functions.php";
require_once "../functions/notification_functions.php";
require_once "../functions/subsidiary_functions.php";
require_once "../functions/transaction_functions.php";
$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(isset($_POST['sendadvcashreq'])){
        $created_by = (int) $_SESSION['userid'];
        $ref_no = $_POST['ref_no'];
        $date_ = $_POST['date_'];
        $type = $_POST['type'];
        $requester_id = (int) $_POST['requester_id'];
        $project_id = (int) $_POST['project_id'];
        $currency = (int) $_POST['currency'];
        $currency_rate = floatval(str_replace(',', '', $_POST['curr']));
        $reason = $_POST['reason'];
        $total_amount = floatval(str_replace(',', '', $_POST['totalamount']));
        $item_descriptions = $_POST['item_description'];
        $amounts = $_POST['actual'];
        $amounts_eqv = $_POST['amount'];
        $banks = $_POST['bank'];
        $accounts = $_POST['account'];
        $min_item_ids = [];
        $conn->begin_transaction();
        try {
            foreach($item_descriptions as $desc){
                $item_description = $conn->real_escape_string($desc);
                $check_result = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
                if($check_result && $check_result->num_rows > 0){
                    $min_item_ids[] = $check_result->fetch_assoc()['id'];
                } else {
                    if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting item: " . $conn->error);
                    $min_item_ids[] = $conn->insert_id;
                }
            }
            $advance_cash_request_id = createAdvanceCashRequest($conn, $ref_no, $date_, $type, $requester_id, $project_id, $currency, $currency_rate, $reason, $total_amount, $created_by);
            if(!is_numeric($advance_cash_request_id)) throw new Exception($advance_cash_request_id);
            for($i = 0; $i < count($min_item_ids); $i++){
                $item_id = (int) $min_item_ids[$i];
                $amount = floatval(str_replace(',', '', $amounts[$i]));
                $amount_eqv = floatval(str_replace(',', '', $amounts_eqv[$i]));
                $bank = $banks[$i];
                $account = $accounts[$i];
                $item = createAdvanceCashRequestItem($conn, $advance_cash_request_id, $item_id, $amount, $amount_eqv, $bank, $account, $created_by);
                if(!is_numeric($item)) throw new Exception($item);
            }
            $conn->commit();
            setNotification('success', 'Advance Cash Request created successfully');
            echo "<script>window.location.href='../?page=advance_cash_request';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    
     if(isset($_POST['editadvcashreq'])){
        $advance_cash_request_id = (int) $_POST['advance_cash_request_id'];
        $updated_by      = (int) $_SESSION['userid'];
        $date_           = $_POST['date_'];
        $type            = $_POST['type'];
        $requester_id    = (int) $_POST['requester_id'];
        $project_id      = (int) $_POST['project_id'];
        $currency        = (int) $_POST['currency'];
        $currency_rate   = floatval(str_replace(',', '', $_POST['curr']));
        $reason          = $_POST['reason'];
        $total_amount    = floatval(str_replace(',', '', $_POST['totalamount']));
        $item_descriptions = $_POST['item_description'];
        $amounts           = $_POST['actual'];
        $amounts_eqv       = $_POST['amount'];
        $banks             = $_POST['bank'];
        $accounts          = $_POST['account'];
        $item_original_ids = $_POST['item_original_id'] ?? [];
        $item_min_sub_ids  = $_POST['item_min_sub_id'] ?? [];
        $deleted_item_ids  = isset($_POST['deleted_item_ids']) && !empty($_POST['deleted_item_ids'])
                             ? explode(',', $_POST['deleted_item_ids']) : [];
        $conn->begin_transaction();
        try {
            $update = updateAdvanceCashRequest($conn, $advance_cash_request_id, $date_, $type, $requester_id, $project_id, $currency, $currency_rate, $reason, $total_amount, $updated_by);
            if($update !== true) throw new Exception($update);

            foreach($deleted_item_ids as $del_id){
                $del_id = (int) trim($del_id);
                if($del_id <= 0) continue;
                $del = softDeleteAdvanceCashRequestItemById($conn, $del_id, $updated_by);
                if($del !== true) throw new Exception($del);
            }

            for($i = 0; $i < count($item_descriptions); $i++){
                $oid        = isset($item_original_ids[$i]) ? (int) $item_original_ids[$i] : 0;
                $amount     = floatval(str_replace(',', '', $amounts[$i]));
                $amount_eqv = floatval(str_replace(',', '', $amounts_eqv[$i]));
                $bank       = $banks[$i];
                $account    = $accounts[$i];

                if($oid > 0){
                    $item_id = (int) $item_min_sub_ids[$i];
                    $item_description = $conn->real_escape_string($item_descriptions[$i]);
                    if(!$conn->query("UPDATE min_subs SET name = '$item_description' WHERE id = $item_id")) throw new Exception("Error updating item: " . $conn->error);
                    $res = updateAdvanceCashRequestItem($conn, $oid, $item_id, $amount, $amount_eqv, $bank, $account, $updated_by);
                    if($res !== true) throw new Exception($res);
                } else {
                    $item_description = $conn->real_escape_string($item_descriptions[$i]);
                    $check_result = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
                    if($check_result && $check_result->num_rows > 0){
                        $item_id = (int) $check_result->fetch_assoc()['id'];
                    } else {
                        if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting item: " . $conn->error);
                        $item_id = $conn->insert_id;
                    }
                    $res = createAdvanceCashRequestItem($conn, $advance_cash_request_id, $item_id, $amount, $amount_eqv, $bank, $account, $updated_by);
                    if(!is_numeric($res)) throw new Exception($res);
                }
            }

            $conn->commit();
            setNotification('success', 'Advance Cash Request updated successfully');
            echo "<script>window.location.href='../?page=advance_cash_request_list&status=pending';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if(isset($_POST['approve_advance_cash_request'])){
        $advance_cash_request_id = (int) $_POST['advance_cash_request_id'];
        $approved_by = (int) $_SESSION['userid'];
        $update = approveAdvanceCashRequest($conn, $advance_cash_request_id, $approved_by);
        if($update === true){
            setNotification('success', 'Advance Cash Request approved successfully');
            echo "<script>window.location.href='../?page=advance_cash_request_list';</script>";
        } else {
            echo $update;
        }
    }
    
    if(isset($_POST['reject_advance_cash_request'])){
        $advance_cash_request_id = (int) $_POST['advance_cash_request_id'];
        $rejected_by = (int) $_SESSION['userid'];
        $rejection_reason = $_POST['rejection_reason'];
        $update = rejectAdvanceCashRequest($conn, $advance_cash_request_id, $rejected_by, $rejection_reason);
        if($update === true){
            setNotification('success', 'Advance Cash Request rejected successfully');
            echo "<script>window.location.href='../?page=advance_cash_request_list';</script>";
        } else {
            echo $update;
        }
    }  
    
    if(isset($_POST['pay_advance_cash_request'])){
        $advance_cash_request_id = (int) $_POST['advance_cash_request_id'];
        $paid_by = (int) $_SESSION['userid'];
        $dr_account = (int) $_POST['dr_account'];
        $cr_account = (int) $_POST['cr_account'];
        $amount = floatval(str_replace(',', '', $_POST['amount']));
        $date_ = $_POST['date_'];
        $description = $_POST['description'];
        $currency = (int) $_POST['currency'];
        $currency_rate = floatval($_POST['currency_rate']);
        $ref_no = $_POST['ref_no'];
        $project_id = (int) $_POST['project_id'];
        $conn->begin_transaction();
        try {
            $transactionId = createTransaction($conn, $ref_no, 'payment', $dr_account, $amount, $cr_account, $date_, $description, $currency, $currency_rate, 'draft', $paid_by, $ref_no, '', $project_id);
            if(!is_numeric($transactionId)) throw new Exception($transactionId);
            $paid = markAdvanceCashRequestAsPaid($conn, $advance_cash_request_id, $paid_by, $transactionId);
            if($paid !== true) throw new Exception($paid);
            $conn->commit();
            echo 'SUCCESS';
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if(isset($_POST['delete_advance_cash_request'])){
        $advance_cash_request_id = (int) $_POST['advance_cash_request_id'];
        $deleted_by = (int) $_SESSION['userid'];
        $delete = softDeleteAdvanceCashRequest($conn, $advance_cash_request_id, $deleted_by);
        $deleteItems = softDeleteAdvanceCashRequestItemsByRequestId($conn, $advance_cash_request_id, $deleted_by);
        if($delete === true && $deleteItems === true){
            setNotification('success', 'Advance Cash Request deleted successfully');
            echo "<script>window.location.href='../?page=advance_cash_request_list';</script>";
        } else {
            echo $delete;
        }
    }
    
    
    
    if(isset($_POST['create_retirement'])){
        $created_by              = (int) $_SESSION['userid'];
        $ref_no                  = $_POST['ref_no'];
        $date_                   = $_POST['date_'];
        $advance_cash_request_id = (int) $_POST['advance_cash_request_id'];
        $requester_id            = (int) $_POST['requester_id'];
        $project_id              = (int) $_POST['project_id'];
        $currency                = (int) $_POST['currency'];
        $currency_rate           = floatval(str_replace(',', '', $_POST['curr']));
        $requested_amount        = floatval($_POST['requested_amount']);
        $notes                   = $_POST['notes'];
        $refund_amount           = floatval($_POST['refund_amount']);
        $excess_amount           = floatval($_POST['excess_amount']);
        $total_spent             = floatval(str_replace(',', '', $_POST['actual_amount']));
        $item_ids                = $_POST['item_id'];
        $dates_spent             = $_POST['date_spent'];
        $amounts_used            = $_POST['amount_used'];
        $amounts_used_eqv        = $_POST['amount_used_eqv'];
        $receipts                = $_FILES['receipt'] ?? [];
    
        $conn->begin_transaction();
        try {
            $retirement_id = createRetirement($conn, $ref_no, $date_, $advance_cash_request_id, $requester_id, $project_id, $currency, $currency_rate, $requested_amount, $total_spent, $refund_amount, $excess_amount, $notes, $created_by);
            if(!is_numeric($retirement_id)) throw new Exception($retirement_id);
    
            for($i = 0; $i < count($item_ids); $i++){
                $item_id          = (int) $item_ids[$i];
                $date_spent       = $dates_spent[$i];
                $amount_spent     = floatval(str_replace(',', '', $amounts_used[$i]));
                $amount_spent_eqv = floatval(str_replace(',', '', $amounts_used_eqv[$i]));
                $receipt_path     = null;
    
                if($receipts && isset($receipts['name'][$i]) && !empty($receipts['name'][$i]) && $receipts['error'][$i] === UPLOAD_ERR_OK){
                    $upload_dir = '../uploads/vouchers/';
                    if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $original_name = basename($receipts['name'][$i]);
                    $filename      = time() . '_' . $original_name;
                    $target_path   = $upload_dir . $filename;
                    $tmp_name      = $receipts['tmp_name'][$i];
                    if(!move_uploaded_file($tmp_name, $target_path)) throw new Exception("Failed to upload receipt for row " . ($i + 1) . ".");
                    $receipt_path = $filename;
                }
    
                $item = createRetirementItem($conn, $retirement_id, $advance_cash_request_id, $item_id, $date_spent, $amount_spent, $amount_spent_eqv, $receipt_path, $created_by);
                if(!is_numeric($item)) throw new Exception($item);
            }
    
            $update_status = updateAdvanceCashRequestStatus($conn, $advance_cash_request_id, 'retired', $created_by);
            if($update_status !== true) throw new Exception($update_status);
    
            $conn->commit();
            setNotification('success', 'Retirement submitted successfully');
            echo "<script>window.location.href='../?page=advance_cash_request_list&status=paid';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    
    if(isset($_POST['approve_retirement'])){
        $retirement_id = (int) $_POST['retirement_id'];
        $approved_by   = (int) $_SESSION['userid'];
    
        $retirement = selectRetirementById($conn, $retirement_id);
        if(!$retirement){ echo "Retirement not found."; exit(); }
    
        $items       = selectRetirementItemsByRetirementId($conn, $retirement_id);
        $currency    = (int) $retirement['currency'];
        $currency_rate = floatval($retirement['currency_rate']);
        $requester_id  = (int) $retirement['requester_id'];
        $project_id    = (int) $retirement['project_id'];
        $ref_no        = $retirement['ref_no'];
        //$date_         = $retirement['date_'];
    
        $conn->begin_transaction();
        try {
            foreach($items as $item){
                $dr            = (int) $item['item_id'];
                $cr            = $requester_id;
                $amount_eqv    = floatval($item['amount_spent_eqv']);
                $description   =  "Retirement Transction ";
                $receipt_path  = !empty($item['receipt_path']) ? $item['receipt_path'] : null;
                $date_         = $conn->real_escape_string($item['date_spent']);
                $transactionId = createTransaction($conn, $ref_no, 'journal', $dr, $amount_eqv, $cr, $date_, $description, $currency, $currency_rate, 'draft', $approved_by, $ref_no, $receipt_path, $project_id);
                if(!is_numeric($transactionId)) throw new Exception($transactionId);
    
                if(!$conn->query("UPDATE advance_cash_retirement_items SET transaction_id = $transactionId WHERE id = {$item['id']}")) throw new Exception("Failed to update retirement item transaction: " . $conn->error);
            }
    
            $approve = approveRetirement($conn, $retirement_id, $approved_by);
            if($approve !== true) throw new Exception($approve);
    
            $conn->commit();
            echo 'SUCCESS';
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['reject_retirement'])){
        $retirement_id    = (int) $_POST['retirement_id'];
        $rejected_by      = (int) $_SESSION['userid'];
        $rejection_reason = $_POST['rejection_reason'];
    
        $reject = rejectRetirement($conn, $retirement_id, $rejected_by, $rejection_reason);
        if($reject === true){
            echo 'SUCCESS';
        } else {
            echo $reject;
        }
    }
    
    if(isset($_POST['delete_retirement'])){
        $retirement_id = (int) $_POST['retirement_id'];
        $deleted_by    = (int) $_SESSION['userid'];
    
        $retirement = selectRetirementById($conn, $retirement_id);
        if(!$retirement){ echo "Retirement not found."; exit(); }
        $advance_cash_request_id = (int) $retirement['advance_cash_request_id'];
    
        $conn->begin_transaction();
        try {
            $deleteItems = softDeleteRetirementItemsByRetirementId($conn, $retirement_id, $deleted_by);
            if($deleteItems !== true) throw new Exception($deleteItems);
    
            $delete = softDeleteRetirement($conn, $retirement_id, $deleted_by);
            if($delete !== true) throw new Exception($delete);
    
            $update_status = updateAdvanceCashRequestStatus($conn, $advance_cash_request_id, 'paid', $deleted_by);
            if($update_status !== true) throw new Exception($update_status);
    
            $conn->commit();
            setNotification('success', 'Retirement deleted successfully');
            echo "<script>window.location.href='../?page=retirement_list&status=pending';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
}
?>