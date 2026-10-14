<?php

function createTransaction(mysqli $conn, string $reference_no, string $voucher_type, int $dr_account, float $amount, int $cr_account, string $date, string $description = null, int $currency = null, float $currency_rate = null, string $status = 'draft', int $created_by = null,  string $transaction_reference = null, string $attachment_url = null, int $project_id = null,float $tax_rate=0.00) {
    if ($conn === false) {
        return false;
    }
    $sql = "INSERT INTO `transaction_voucher` (`reference_no`, `transaction_reference`, `voucher_type`, `currency`, `currency_rate`, `tax_rate`,`dr_account`, `amount`, `description`, `cr_account`, `date_`, `status`, `created_by`, `attachment_url`, `project_id`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param("sssiddidsissisi", $reference_no, $transaction_reference, $voucher_type, $currency, $currency_rate,$tax_rate, $dr_account, $amount, $description, $cr_account, $date, $status, $created_by, $attachment_url, $project_id);
    if ($stmt->execute()) {
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}


function updateTransaction(mysqli $conn, int $id, string $reference_no, string $voucher_type, int $dr_account, float $amount, int $cr_account, string $date, string $transaction_reference, string $description, int $currency, float $currency_rate, int $project_id, int $updated_by, string $attachment_url = null,float $tax_rate=0.00) {
    if ($conn === false) {
        return false;
    }

    $sql = "UPDATE `transaction_voucher` SET
                `reference_no`          = '$reference_no',
                `transaction_reference` = '$transaction_reference',
                `voucher_type`          = '$voucher_type',
                `currency`              = $currency,
                `currency_rate`         = $currency_rate,
                `dr_account`            = $dr_account,
                `amount`                = $amount,
                `description`           = '$description',
                `cr_account`            = $cr_account,
                `date_`                 = '$date',
                `project_id`            = $project_id,
                `updated_by`            = $updated_by,
                `attachment_url`        = " . ($attachment_url ? "'$attachment_url'" : "NULL") . ",
                `tax_rate`              = $tax_rate,
                `status`                = 'draft'
            WHERE `id` = $id";

    if ($conn->query($sql)) {
        return true;
    } else {
        return false;
    }
}

     
                         


 
function approveTransaction(mysqli $conn, int $id, int $approved_by) {
    if ($conn === false) {
        return false;
    }

    $sql = "UPDATE transaction_voucher 
            SET status = 'active', 
                approved_by = $approved_by,
                approved_at = NOW()
            WHERE id = $id AND deleted_at IS NULL";

    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}


function rejectTransaction(mysqli $conn, int $id, int $rejected_by, string $rejection_reason) {
    if ($conn === false) {
        return false;
    }
    $rejection_reason = $conn->real_escape_string($rejection_reason);
    $sql = "UPDATE transaction_voucher 
            SET status = 'rejected', 
                rejected_by = $rejected_by,
                rejected_at = NOW(),
                rejection_reason = '$rejection_reason'
            WHERE id = $id AND deleted_at IS NULL";  
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function reviewTransaction(mysqli $conn, int $id, int $reviewed_by) {
    if ($conn === false) {
        return false;
    }

    $sql = "UPDATE transaction_voucher 
            SET status = 'pending', 
                reviewed_by = $reviewed_by,
                reviewed_at = NOW()
            WHERE id = $id AND deleted_at IS NULL";

    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}
     
function softDeleteTransaction(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) {
        exit();
    }
    $sql = "UPDATE transaction_voucher 
            SET deleted_at = NOW(), 
                deleted_by = $deleted_by
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}
 
 
function selectTransactionById(mysqli $conn, int $id) {
    if ($conn === false) {
        exit;
    }

    $sql = "SELECT 
                tv.*, 
                debit.name AS debit_acc, 
                credit.name AS credit_acc,
                u.names AS created_by_name,
                r.names AS reviewed_by_name,
                a.names AS approved_by_name
            FROM transaction_voucher tv
            JOIN subsidiaries debit ON debit.id = tv.dr_account 
            JOIN subsidiaries credit ON credit.id = tv.cr_account
            LEFT JOIN users u ON u.id = tv.created_by
            LEFT JOIN users r ON r.id = tv.reviewed_by
            LEFT JOIN users a ON a.id = tv.approved_by
            WHERE tv.id = $id 
            AND tv.deleted_at IS NULL";

    if ($result = $conn->query($sql)) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    } else {
        return $conn->error;
    }
}

function selectTransactionByProjectId(mysqli $conn, int $projectId) {
    if ($conn === false) exit;
    $sql = "SELECT tv.*, debit.name AS debit_acc, credit.name AS credit_acc
            FROM transaction_voucher tv
            JOIN subsidiaries debit ON debit.id = tv.dr_account
            JOIN subsidiaries credit ON credit.id = tv.cr_account
            WHERE tv.project_id = $projectId
            AND tv.voucher_type='payment'
            AND tv.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $rows;
    } else {
        return $conn->error;
    }
}

function selectAllTransactionsBySubId(mysqli $conn, int $subId, ?int $projectId = null){    
    if($conn === false){
        exit();
    }
    $projectFilter = ($projectId !== null) ? "AND project_id = '$projectId'" : "";
    
    $sql = "SELECT * FROM transaction_voucher 
        WHERE (cr_account ='$subId' OR dr_account='$subId') 
        $projectFilter
        AND deleted_at IS NULL ORDER BY date_ ASC";

     if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
     } else{
        return $conn->error;
     }
}

function selectAllTransactionsBySubIdAndDateRange(mysqli $conn, int $subId, string $date1, string $date2, ?int $projectId = null){
    if($conn === false){
        exit();
    }
    $projectFilter = ($projectId !== null) ? "AND t.project_id = '$projectId'" : "";
    
    $sql = "SELECT * FROM transaction_voucher t
        WHERE (t.cr_account = '$subId' OR t.dr_account = '$subId') 
        AND t.date_ BETWEEN '$date1' AND '$date2'
        $projectFilter
        AND t.deleted_at IS NULL ORDER BY date_ ASC";
        
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function softDeleteTransactionBySubId(mysqli $conn, int $subId, int $deleted_by) {
    if ($conn === false) {
        exit;
    }
    $sql = "UPDATE transaction_voucher 
            SET deleted_at = NOW(), 
                deleted_by = $deleted_by,
                restore_status = 'sub-delete'
            WHERE (cr_account = '$subId' OR dr_account = '$subId') 
            AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function restoreDeletedTransactionBySubId(mysqli $conn, int $subId, int $restored_by) {
    if ($conn === false) {
        exit;
    }
    $sql = "UPDATE transaction_voucher 
            SET deleted_at = NULL, 
                restored_at = NOW(),
                restored_by = $restored_by,
                restore_status = NULL
            WHERE (cr_account = '$subId' OR dr_account = '$subId') 
            AND restore_status = 'sub-delete'
            AND deleted_at IS NOT NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}
            
function countTransactionByVoucherRefNo(mysqli $conn, string $refKey ){
    if($conn === false){
        exit();

    }
    $sql = "SELECT COUNT(id) as jumla FROM transaction_voucher WHERE reference_no LIKE '$refKey%';";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}            
            
function calculateSubClosingBalanceByMaxDate(mysqli $conn, int $subId, string $maxDate){
    $date  = date('Y-m-d', strtotime($maxDate));
    $balance = 0;
    // get openingBalabce
    $openingBalance = selectOpeningBalance($conn, $subId, $date);
    if($openingBalance && is_array($openingBalance)){
        if($openingBalance['type'] == 'debit'){
            $balance = $openingBalance['balance'];
        } else {
            $balance = -$openingBalance['balance'];
        }
    }

    // fetchTransanction
    $transactions = selectAllTransactionsBySubIdAndMaxDate($conn, $subId, $date);
    if($transactions && is_array($transactions)){
        foreach($transactions as $transaction){
            if($transaction['dr_account'] == $subId){
                $balance += $transaction['amount'];
            } elseif($transaction['cr_account'] == $subId) {
                $balance -= $transaction['amount'];
            }
        }
    }
    return $balance;
    
    
}

function listTransactionBySubIdAndDateRange(mysqli $conn, int $subId, string $startDate, string $endDate){
    $sql = "SELECT t.*, debit.name as debit_account, credit.name as credit_account FROM transaction_voucher t
            JOIN subsidiaries debit ON t.dr_account = debit.id AND debit.deleted_at IS NULL
            JOIN subsidiaries credit ON t.cr_account = credit.id AND credit.deleted_at IS NULL
            WHERE ( t.cr_account = ? OR t.dr_account = ? )
            AND t.date_ >= ? AND t.date_ <= ? AND t.deleted_at IS NULL
            ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $subId, $subId, $startDate, $endDate);
    return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : false;
}       
function selectAllTransactionsBySubIdAndMaxDate(mysqli $conn, int $subId, string $date){
    if($conn === false){
        exit();
    }
    
    
    $sql = "SELECT t.*, debit.name, credit.name FROM transaction_voucher t
        LEFT JOIN subsidiaries debit ON t.dr_account = debit.id AND debit.deleted_at IS NULL
        LEFT JOIN subsidiaries credit ON t.cr_account = credit.id AND credit.deleted_at IS NULL
        WHERE (t.cr_account = ? OR t.dr_account = ?) 
        AND t.date_ <= ?
        
        AND t.deleted_at IS NULL ORDER BY date_ ASC";
        
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("iis", $subId, $subId, $date);

   return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : $conn->error;
}


function selectAllTransactionsBySubIdAndProjectId(mysqli $conn, int $subId,  string $projectId = null, string $date1 = null, string $date2 = null) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM transaction_voucher WHERE (cr_account = '$subId' OR dr_account = '$subId')  AND deleted_at IS NULL";
    if ($projectId !== null && $projectId !== '')         $sql .= " AND project_id = '$projectId'";
    if ($date1 !== null && $date1 !== '')           $sql .= " AND date_ >= '$date1'";
    if ($date2 !== null && $date2 !== '')           $sql .= " AND date_ <= '$date2'";
    $sql .= " ORDER BY date_ ASC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectAllTransactionsBeforeDateBySubId(mysqli $conn, int $subId, string $date1,  string $projectId = null) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM transaction_voucher WHERE (cr_account = '$subId' OR dr_account = '$subId') AND date_ < '$date1' AND deleted_at IS NULL";
    if ($projectId !== null && $projectId !== '')         $sql .= " AND project_id = '$projectId'";
    $sql .= " ORDER BY date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

?>
