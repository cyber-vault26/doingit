<?php
    session_start();
    if(!$_SESSION){
        echo "<script>window.history.back();</script>";
    }
    require_once "../functions/transaction_functions.php";
    require_once "../functions/reconciliation_functions.php";
    require_once __DIR__ . '/../functions/notification_functions.php';
    require_once '../configs.php';
    $conn = openConn();
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
       
        if(isset($_POST['action']) && $_POST['action'] === 'add_monthly_balance'){
            // Prepare data for recordReconciliation function
            $data = [
                'bank_account_id' => (int) $_POST['bank_account_id'],
                'month' => $_POST['month'],
                'closing_balance' => (float) $_POST['closing_balance'],
                'balance_type' => $_POST['balance_type'],
                'posted_by' => $_SESSION['userid']
            ];
            
            // Call recordReconciliation function
            $result = recordReconciliation($conn, $data);
            
            if ($result['success']) {
                setNotification('success', $result['message']);
                echo "<script> window.location.href='../?page=add_monthly_balance'</script>";
            } else {
                echo $result['message'];
                // setNotification('error', $result['message']);
                echo "<script> window.history.back();</script>";
            }
        }
        if(isset($_POST['action']) && $_POST['action'] === 'edit_monthly_closing_balance'){
            // Prepare data for recordReconciliation function
            $data = [
                'id' => (int) $_POST['id'],
                'bank_account_id' => (int) $_POST['bank_account_id'],
                'month' => $_POST['month'],
                'closing_balance' => (float) $_POST['closing_balance'],
                'balance_type' => $_POST['balance_type'],
                'posted_by' => $_SESSION['userid']
            ];
            
            // Call recordReconciliation function
            $result = updateMonthlyBalance($conn, $data);
            
            if ($result['success']) {
                setNotification('success', $result['message']);
                echo "<script> window.location.href='../?page=edit_monthly_closing_balance&balance_id=" . $data['id'] . "'</script>";
            } else {
                echo $result['message'];
                // setNotification('error', $result['message']);
                echo "<script> window.history.back();</script>";
            }
        }
        
       
    }
?>