<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>window.history.back();</script>";
    exit();
}
require_once "../functions/payroll_functions.php";
require_once "../functions/transaction_functions.php";
require_once "../functions/notification_functions.php";
require_once "../configs.php";
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user_id = (int) $_SESSION['userid'];
    
    if(isset($_POST['save_payroll'])){
        $month = $_POST['month'];
        $userId = (int) $_SESSION['userid']; 
        $cost_center = 0;
        $staff_ids = $_POST['staff_id'];
        $basic_salary = $_POST['basic_salary'];
        $allowance = $_POST['allowance'];
        $nssf = $_POST['nssf'];
        $taxable_amount = $_POST['taxable_amount'];
        $payee = $_POST['payee'];
        $salary_advance = $_POST['salary_advance'];
        $sdl = $_POST['sdl'];
        $wcf = $_POST['wcf'];
        $conn->begin_transaction();
        try {
            $payrollHeaderId = insertPayrollHeader($conn, $month, $userId);
            if(!is_numeric($payrollHeaderId)) throw new Exception($payrollHeaderId);
            for($i = 0; $i < count($staff_ids); $i++){
                $staffId = (int) $staff_ids[$i];
                $staffBasicSalary = floatval(str_replace(',', '', $basic_salary[$i]));
                $staffAllowance = floatval(str_replace(',', '', $allowance[$i]));
                $staffNssf = floatval(str_replace(',', '', $nssf[$i]));
                $staffTaxableAmount = floatval(str_replace(',', '', $taxable_amount[$i]));
                $staffPayee = floatval(str_replace(',', '', $payee[$i]));
                $staffSalaryAdvance = floatval(str_replace(',', '', $salary_advance[$i]));
                $staffWcf = floatval(str_replace(',', '', $wcf[$i]));
                $staffSdl = floatval(str_replace(',', '', $sdl[$i]));
                $payrollItem = insertPayrollItem($conn, $payrollHeaderId, $staffId, $staffBasicSalary, $staffTaxableAmount, $staffAllowance, $staffPayee, $staffNssf, $staffWcf, $staffSdl, $staffSalaryAdvance, $userId);
                if(!is_numeric($payrollItem)) throw new Exception($payrollItem);
            }
            $conn->commit();
            setNotification('success', 'Payroll saved successfully');
            echo "<script>window.location.href='../?page=payroll_voucher_form';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['post_payroll_entry'])){
        $payroll_id = (int) $_POST['payroll_id'];
        $ref = $_POST['ref_no'];
        $dr_accounts = $_POST['debit_account'];
        $cr_accounts = $_POST['credit_account'];
        $amounts = $_POST['amount'];
        $descriptions = $_POST['description'];
        $date = $_POST['entry_date'];
        $userId = (int) $_SESSION['userid'];
        $conn->begin_transaction();
        try {
            for($i = 0; $i < count($amounts); $i++){
                $dr_account = (int) $dr_accounts[$i];
                $cr_account = (int) $cr_accounts[$i];
                $amount = floatval(str_replace(',', '', $amounts[$i]));
                $description = $descriptions[$i];
                if($amount <= 0) continue;
                $newTransaction = createTransaction($conn, $ref, 'journal', $dr_account, $amount, $cr_account, $date, null, $description, 1, 1.00, 'active', $userId, null, null);
                if(!is_numeric($newTransaction)) throw new Exception($newTransaction);
            }
            $updateHeader = updatePayrollHeader($conn, $payroll_id, $userId);
            if($updateHeader !== true) throw new Exception($updateHeader);
            $conn->commit();
            setNotification('success', 'Payroll entries posted successfully');
            echo "<script>window.location.href='../?page=generate_payroll_entry&payroll_id=$payroll_id';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['deleteSVDpayroll'])){
        $id = (int) $_POST['delete_id'];
        $deleted_by = (int) $_SESSION['userid'];
        $conn->begin_transaction();
        try {
            $deleteHeader = softDeletePayrollHeader($conn, $id, $deleted_by);
            if($deleteHeader !== true) throw new Exception($deleteHeader);
            $deleteItems = softDeletePayrollItemsByHeaderId($conn, $id, $deleted_by);
            if($deleteItems !== true) throw new Exception($deleteItems);
            $conn->commit();
            setNotification('success', 'Payroll deleted successfully');
            echo "<script>window.location.href='../?page=payrol_report_list';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
   

    if(isset($_POST['addsalary'])){
        $emp_ids = $_POST['emp_id'];
        $amounts = $_POST['amount'];
        $conn->begin_transaction();
        try {
            for($i = 0; $i < count($emp_ids); $i++){
                $emp_id = (int) $emp_ids[$i];
                $amount = floatval(str_replace(',', '', $amounts[$i]));
                $result = insertSalary($conn, $emp_id, $amount, $user_id);
                if(!is_numeric($result)){
                    throw new Exception($result);
                }
            }
            $conn->commit();
            setNotification('success', 'Basic salary added successfully');
            echo "<script>window.location.href='../?page=basic_salary_list';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['update_salary'])){
        $id = (int) $_POST['edit_id'];
        $amount = floatval(str_replace(',', '', $_POST['edit_amount']));
        $result = updateSalary($conn, $id, $amount, $user_id);
        if($result === true){
            setNotification('success', 'Salary updated successfully');
            echo "<script>window.location.href='../?page=basic_salary_list';</script>";
        } else {
            echo $result;
        }
    }
    
    if(isset($_POST['deactivate_salary'])){
        $id = (int) $_POST['salary_id'];
        $status = $_POST['status'];
        $result = updateSalaryStatus($conn, $id, $status, $user_id);
        if($result === true){
            setNotification('success', 'Salary deactivated successfully');
            echo "<script>window.location.href='../?page=basic_salary_list';</script>";
        } else {
            echo $result;
        }
    }
    
    if(isset($_POST['activate_salary'])){
        $id = (int) $_POST['salary_id'];
        $status = $_POST['status'];
        $result = updateSalaryStatus($conn, $id, $status, $user_id);
        if($result === true){
            setNotification('success', 'Salary activated successfully');
            echo "<script>window.location.href='../?page=basic_salary_list';</script>";
        } else {
            echo $result;
        }
    }
    
    if(isset($_POST['delete_salary'])){
        $id = (int) $_POST['delete_id'];
        $result = softDeleteSalary($conn, $id, $user_id);
        if($result === true){
            setNotification('success', 'Salary deleted successfully');
            echo "<script>window.location.href='../?page=basic_salary_list';</script>";
        } else {
            echo $result;
        }
    }
}
?>