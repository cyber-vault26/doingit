<?php
session_start();
require_once "../configs.php";
require_once "../functions/project_functions.php";
require_once "../functions/transaction_functions.php";
require_once "../functions/notification_functions.php";
$conn = openConn();
if ($_SERVER['REQUEST_METHOD'] === "POST") {
   if (isset($_POST['createProject'])) {
        try {
            $conn->begin_transaction();
            $name = $conn->real_escape_string($_POST['title']);
            $date = $conn->real_escape_string($_POST['date_']);
            $location = $conn->real_escape_string($_POST['location']);
            $customerId = (int) $_POST['customer_id'];
            $price = (float) $_POST['price'];
            $duration = (int) $_POST['duration'];
            $projectManager = $conn->real_escape_string($_POST['project_manager']);
            $progressPhases = $conn->real_escape_string($_POST['progress_phases']);
            $newProject = createProject($conn, $name, $location, $date, $customerId, $price, $duration, $projectManager, $progressPhases, (int)$_SESSION['userid']);
            if (!is_numeric($newProject)) throw new Exception($newProject);
            $conn->commit();
            setNotification('success', 'Project created successfully');
            echo "<script>window.location.href='../?page=create_project';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
     
    
    if(isset($_POST['addpaymentVoucher'])){
        $ref             = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $date            = $conn->real_escape_string(trim($_POST['voucherdate']));
        $currency        = (int) $_POST['currency'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $eqv             = $_POST['equiv_dr'];
        $cr_accounts     = $_POST['cr_account'];
        $dr_accounts     = $_POST['deb_account'];
        $desc            = $_POST['desc'];
        $project_id      = (int) $_POST['project_id'];
        $transaction_ref = $userId;
        $uploadedFiles   = $_FILES['attachment'] ?? null;
        $uploadDir="../uploads/vouchers";
        $conn->begin_transaction();
        try {
            for($i = 0; $i < count($eqv); $i++){
                $eqvVal   = (float) str_replace(',', '', $eqv[$i]);
                $dr       = (int) $dr_accounts[$i];
                $cr       = (int) $cr_accounts[$i];
                $d        = $conn->real_escape_string(trim($desc[$i]));
                $fileName = null;
                if($uploadedFiles && isset($uploadedFiles['name'][$i]) && !empty($uploadedFiles['name'][$i]) && $uploadedFiles['error'][$i] === UPLOAD_ERR_OK){
                    $originalFileName = basename($uploadedFiles['name'][$i]);
                    $fileName         = time() . '_' . $originalFileName;
                    $targetPath       = $uploadDir . $fileName;
                    $fileTmpName      = $uploadedFiles['tmp_name'][$i];
                    if(!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to move uploaded file for row " . ($i + 1) . ".");
                }
                $transactionId = createTransaction($conn, $ref, 'payment', $dr, $eqvVal, $cr, $date,  $d, $currency, $currency_rate, 'draft', $userId, $transaction_ref, $fileName, $project_id);
                if(!is_numeric($transactionId)) throw new Exception($transactionId);
            }
            $conn->commit();
            setNotification('success', 'Project cost added successfully');
            echo "<script>window.location.href='../?page=open_project&id=$project_id';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    
    if (isset($_POST['upload_project_budget_items'])) {
        try {
            $conn->begin_transaction();
            $project_id = (int) $conn->real_escape_string($_POST['project_id']);
            $subId = $_POST['sub_id'];
            $amount = $_POST['amount'];
            for ($i = 0; $i < count($subId); $i++) {
                $sub = (int) $conn->real_escape_string($subId[$i]);
                $amt = (float) $conn->real_escape_string($amount[$i]);
                $newBudget = insertProjectBudget($conn, $project_id, $sub, $amt);
                if ($newBudget !== true) throw new Exception($newBudget);
            }
            $conn->commit();
            setNotification('success', 'Budget items uploaded successfully');
            echo "<script>window.location.href='../?page=open_project&id=$project_id';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    
   if (isset($_POST['update_project_details'])) {
        $project_id = (int) $_POST['project_id'];
        $title = $conn->real_escape_string($_POST['title']);
        $location = $conn->real_escape_string($_POST['location']);
        $date = $conn->real_escape_string($_POST['date_']);
        $customerId = (int) $_POST['customer_id'];
        $price = (float) $_POST['price'];
        $duration = (int) $_POST['duration'];
        $projectManager = $conn->real_escape_string($_POST['project_manager']);
        $progressPhases = $conn->real_escape_string($_POST['progress_phases']);
        $result = updateProject($conn, $project_id, $title, $location, $date, $customerId, $price, $duration, $projectManager, $progressPhases);
        if ($result !== true) {
            echo $result;
        } else {
            setNotification('success', 'Project updated successfully');
            echo "<script>window.location.href='../?page=open_project&id={$project_id}';</script>";
        }
    }
    
    if (isset($_POST['add_project_progress'])) {
        $project_id = (int) $_POST['project_id'];
        $date1 = $conn->real_escape_string($_POST['date1']);
        $date2 = $conn->real_escape_string($_POST['date2']);
        $work_done_items = $_POST['work_done'];
        $uploadedFiles = $_FILES['attachment'];
        $uploadDir = "../uploads/projects/";
        $conn->begin_transaction();
        try {
            for ($i = 0; $i < count($work_done_items); $i++) {
                $work_done = $conn->real_escape_string(trim($work_done_items[$i]));
                $fileName = null;
                if ($uploadedFiles && isset($uploadedFiles['name'][$i]) && !empty($uploadedFiles['name'][$i]) && $uploadedFiles['error'][$i] === UPLOAD_ERR_OK) {
                    $originalFileName = basename($uploadedFiles['name'][$i]);
                    $fileName = time() . '_' . $originalFileName;
                    $targetPath = $uploadDir . $fileName;
                    $fileTmpName = $uploadedFiles['tmp_name'][$i];
                    if (!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to upload file for row " . ($i + 1) . ".");
                }
                $result = createProjectProgress($conn, $project_id, $date1, $date2, $work_done, $fileName, (int) $_SESSION['userid']);
                if (!is_numeric($result)) throw new Exception($result);
            }
            $conn->commit();
            setNotification('success', 'Project progress saved successfully');
            echo "<script>window.location.href='../?page=open_project&id={$project_id}';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    
    if (isset($_POST['cancel_project'])) {       
        $project_id = (int) $_POST['project_id'];
        $cancel_reason = $conn->real_escape_string($_POST['cancel_reason']);
        $result = cancelProject($conn, $project_id, $cancel_reason, (int) $_SESSION['userid']);
        if ($result !== true) {
            echo $result;
        } else {
            setNotification('success', 'Project cancelled successfully');
            echo "<script>window.location.href='../?page=open_project&id={$project_id}';</script>";
        }
    }
    
    if (isset($_POST['close_project'])) {
        $project_id = (int) $_POST['project_id'];
        $close_remarks = $conn->real_escape_string($_POST['close_reason']);
        $closed_at = $conn->real_escape_string($_POST['closed_at']);
        $result = closeProject($conn, $project_id, $close_remarks, (int) $_SESSION['userid'], $closed_at);
        if ($result !== true) {
            echo $result;
        } else {
            setNotification('success', 'Project closed successfully');
            echo "<script>window.location.href='../?page=open_project&id={$project_id}';</script>";
        }
    }
    
}
?>