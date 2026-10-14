<?php
function createPurchaseVoucher(mysqli $conn, string $date, string $reference, int $currency, float $currency_rate, float $amount, int $supplier_id,int $transaction_id,int $tax_transaction_id, int $project_id, int $dr_account, int $created_by){
    $sql = "INSERT INTO `purchase_voucher` (`date_`, `reference`, `currency`, `currency_rate`, `amount`, `supplier_id`,`transaction_id`,`tax_transaction_id`,`dr_account`, `project_id`, `discount`, `description`, `created_by`) 
            VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?, 0, 'Purchase', ?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return false;
    }
    $stmt->bind_param("ssiddiiiiii", $date, $reference, $currency, $currency_rate, $amount, $supplier_id,$transaction_id,$tax_transaction_id, $dr_account,$project_id, $created_by);
    if($stmt->execute()){
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function createPurchaseVoucherDetail(mysqli $conn, int $pv_id, int $transaction_id, float $quantity, int $unit, float $price, float $amt, float $amount, string $description, int $item_id, float $vat_rate , float $vat_amount, int $created_by){
    $sql = "INSERT INTO `purchase_voucher_details` (`pv_id`, `transaction_id`, `quantity`, `unit`, `price`, `amt`, `amount`, `description`, `item_id`,`vat_rate`,`vat_amount`, `created_by`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return false;
    }
    $stmt->bind_param("iididdsiiddi", $pv_id, $transaction_id, $quantity, $unit, $price, $amt, $amount, $description, $item_id, $vat_rate, $vat_amount, $created_by);
    if($stmt->execute()){
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function updatePurchaseVoucher(mysqli $conn, int $id, string $date, string $reference, int $currency, float $currency_rate, float $amount, int $supplier_id, int $transaction_id,int $tax_transaction_id, int $project_id, int $dr_account, int $updated_by) {
    if($conn === false) return false;
    $sql = "UPDATE `purchase_voucher` SET
                `date_`         = '$date',
                `reference`     = '$reference',
                `currency`      = $currency,
                `currency_rate` = $currency_rate,
                `amount`        = $amount,
                `supplier_id`   = $supplier_id,
                `transaction_id`= $transaction_id,
                `tax_transaction_id`=$tax_transaction_id,
                `dr_account`    = $dr_account,
                `project_id`    = $project_id,
                `updated_by`    = $updated_by
            WHERE `id` = $id";
    if($conn->query($sql)) return true;
    return false;
}

function updatePurchaseVoucherDetail(mysqli $conn, int $id, int $pv_id, int $transaction_id, float $quantity, int $unit, float $price, float $amt, float $amount, string $description, int $item_id, float $vat_rate, float $vat_amount, int $updated_by) {
    if($conn === false) return false;
    $sql = "UPDATE `purchase_voucher_details` SET
                `pv_id`          = $pv_id,
                `transaction_id` = $transaction_id,
                `quantity`       = $quantity,
                `unit`           = $unit,
                `price`          = $price,
                `amt`            = $amt,
                `amount`         = $amount,
                `description`    = '$description',
                `item_id`        = $item_id,
                `vat_rate`       = $vat_rate,
                `vat_amount`     = $vat_amount,
                `updated_by`     = $updated_by
            WHERE `id` = $id";
    if($conn->query($sql)) return true;
    return false;
}

function createSalesVoucher(mysqli $conn, string $date, string $reference, int $currency, float $currency_rate, float $amount, int $customer_id, int $transaction_id,int $tax_transaction_id,int $cr_account, int $project_id, int $created_by,$invoice_heading,$due_date){
    $sql = "INSERT INTO `sales_voucher` (`date_`, `reference`, `currency`, `currency_rate`, `amount`, `customer_id`, `transaction_id`,`tax_transaction_id`,`cr_account`,`project_id`, `discount`, `description`, `created_by`,`invoice_heading`,`due_date`) 
            VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?, 0, 'Sales', ?,?,?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return false;
    }
    $stmt->bind_param("ssiddiiiiiiss", $date, $reference, $currency, $currency_rate, $amount, $customer_id,$transaction_id,$tax_transaction_id,$cr_account, $project_id, $created_by,$invoice_heading,$due_date);
    if($stmt->execute()){
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function createSalesVoucherDetail(mysqli $conn, int $sv_id, int $transaction_id, float $quantity, int $unit, float $price, float $amt, float $amount, string $description, int $item_id,float $vat_rate , float $vat_amount, int $created_by){
    $sql = "INSERT INTO `sales_voucher_details` (`sv_id`, `transaction_id`, `quantity`, `unit`, `price`, `amt`, `amount`, `description`, `item_id`, `vat_rate` , `vat_amount`,`created_by`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return false;
    }
    $stmt->bind_param("iididdsiiddi", $sv_id, $transaction_id, $quantity, $unit, $price, $amt, $amount, $description, $item_id, $vat_rate , $vat_amount, $created_by);
    if($stmt->execute()){
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
} 

function updateSalesVoucher(mysqli $conn, int $id, string $date, string $reference, int $currency, float $currency_rate, float $amount, int $customer_id, int $transaction_id,$tax_transaction_id, int $cr_account, int $project_id, int $updated_by, string $invoice_heading, string $due_date) {
    if($conn === false) return false;
    $sql = "UPDATE `sales_voucher` SET
                `date_`           = '$date',
                `reference`       = '$reference',
                `currency`        = $currency,
                `currency_rate`   = $currency_rate,
                `amount`          = $amount,
                `customer_id`     = $customer_id,
                `transaction_id`  = $transaction_id,
                `tax_transaction_id`=$tax_transaction_id,
                `cr_account`      = $cr_account,
                `project_id`      = $project_id,
                `invoice_heading` = '$invoice_heading',
                `due_date`        = '$due_date',
                `updated_by`      = $updated_by
            WHERE `id` = $id";
    if($conn->query($sql)) return true;
    return false;
}

function updateSalesVoucherDetail(mysqli $conn, int $id, int $sv_id, int $transaction_id, float $quantity, int $unit, float $price, float $amt, float $amount, string $description, int $item_id, float $vat_rate, float $vat_amount, int $updated_by) {
    if($conn === false) return false;
    $sql = "UPDATE `sales_voucher_details` SET
                `sv_id`          = $sv_id,
                `transaction_id` = $transaction_id,
                `quantity`       = $quantity,
                `unit`           = $unit,
                `price`          = $price,
                `amt`            = $amt,
                `amount`         = $amount,
                `description`    = '$description',
                `item_id`        = $item_id,
                `vat_rate`       = $vat_rate,
                `vat_amount`     = $vat_amount,
                `updated_by`     = $updated_by
            WHERE `id` = $id";
    if($conn->query($sql)) return true;
    return false;
}

function selectAllSalesVouchers(mysqli $conn) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT sv.*, c.name as currency_name, cust.name as customer_name FROM sales_voucher sv 
            LEFT JOIN currency c ON sv.currency = c.id 
            LEFT JOIN subsidiaries cust ON sv.customer_id = cust.id 
            WHERE sv.deleted_at IS NULL ORDER BY sv.date_ DESC";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectSalesVoucherById(mysqli $conn, int $svId) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT sv.*, 
            c.name as currency_name, 
            cust.name as customer_name,
            u1.names as created_by_name,
            u2.names as reviewed_by_name,
            u3.names as approved_by_name
            FROM sales_voucher sv 
            LEFT JOIN currency c ON sv.currency = c.id 
            LEFT JOIN subsidiaries cust ON sv.customer_id = cust.id
            LEFT JOIN users u1 ON sv.created_by = u1.id
            LEFT JOIN users u2 ON sv.reviewed_by = u2.id
            LEFT JOIN users u3 ON sv.approved_by = u3.id
            WHERE sv.id = $svId AND sv.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free(); 
        return $data;
    } else{
        return $conn->error;
    }
}
 
function selectSalesVoucherItems(mysqli $conn, int $svId) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT svd.*, 
            ms.name as item_name,ms.image_url,
            u.name as unit_name
            FROM sales_voucher_details svd 
            LEFT JOIN min_subs ms ON svd.item_id = ms.id 
            LEFT JOIN units u ON svd.unit = u.id
            WHERE svd.sv_id = $svId AND svd.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function softDeleteSalesVoucherDetails(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) {
        exit();
    }
    $sql = "UPDATE sales_voucher_details 
            SET deleted_at = NOW(), 
                deleted_by = $deleted_by
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteSalesVoucherDetailsBySvId(mysqli $conn, int $svId, int $deleted_by) {
    if ($conn === false) {
        exit();
    }
    $sql = "UPDATE sales_voucher_details 
            SET deleted_at = NOW(), 
                deleted_by = $deleted_by
            WHERE sv_id = $svId AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectAllPurchaseVouchers(mysqli $conn) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT pv.*, c.name as currency_name, sup.name as supplier_name FROM purchase_voucher pv 
            LEFT JOIN currency c ON pv.currency = c.id 
            LEFT JOIN subsidiaries sup ON pv.supplier_id = sup.id 
            WHERE pv.deleted_at IS NULL ORDER BY pv.date_ DESC";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectPurchaseVoucherById(mysqli $conn, int $pvId) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT pv.*, 
                c.name as currency_name, 
                sup.name as supplier_name,
                u1.names as created_by_name,
                u2.names as reviewed_by_name,
                u3.names as approved_by_name
            FROM purchase_voucher pv 
            LEFT JOIN currency c ON pv.currency = c.id 
            LEFT JOIN subsidiaries sup ON pv.supplier_id = sup.id 
            LEFT JOIN users u1 ON pv.created_by = u1.id
            LEFT JOIN users u2 ON pv.reviewed_by = u2.id
            LEFT JOIN users u3 ON pv.approved_by = u3.id
            WHERE pv.id = $pvId AND pv.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectPurchaseVoucherItems(mysqli $conn, int $pvId) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT pvd.*, 
            ms.name as item_name,ms.image_url,
            u.name as unit_name
            FROM purchase_voucher_details pvd 
            LEFT JOIN min_subs ms ON pvd.item_id = ms.id 
            LEFT JOIN units u ON pvd.unit = u.id
            WHERE pvd.pv_id = $pvId AND pvd.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function softDeletePurchaseVoucherDetails(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) {
        exit();
    }
    $sql = "UPDATE purchase_voucher_details 
            SET deleted_at = NOW(), 
                deleted_by = $deleted_by
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectVoucherByStatusAndDateRange(mysqli $conn, string $status, string $date1, string $date2) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT t.*, dr.name as debit_acc, cr.name as credit_acc FROM transaction_voucher t LEFT JOIN subsidiaries dr ON t.dr_account = dr.id LEFT JOIN subsidiaries cr ON t.cr_account = cr.id
            WHERE t.date_ BETWEEN '$date1' AND '$date2'
            AND t.voucher_type NOT IN ('purchases','sales','bulky-payment')
            AND t.status = '$status' AND t.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectVoucherByStatusAndTypeAndDateRange(mysqli $conn, string $status, string $type, string $date1, string $date2) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT t.*, dr.name as debit_acc, cr.name as credit_acc FROM transaction_voucher t LEFT JOIN subsidiaries dr ON t.dr_account = dr.id LEFT JOIN subsidiaries cr ON t.cr_account = cr.id
            WHERE t.voucher_type = '$type' AND t.date_ BETWEEN '$date1' AND '$date2'
            AND t.status = '$status' AND t.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}




function selectSalesVouchersByDateRangeAndStatus(mysqli $conn, string $date1, string $date2, string $status) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT sv.*, c.name as currency_name, cust.name as customer_name FROM sales_voucher sv 
            LEFT JOIN currency c ON sv.currency = c.id 
            LEFT JOIN subsidiaries cust ON sv.customer_id = cust.id 
            WHERE sv.date_ BETWEEN '$date1' AND '$date2' AND sv.deleted_at IS NULL AND sv.status = '$status' ORDER BY sv.date_ DESC";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectPurchaseVouchersByDateRangeAndStatus(mysqli $conn, string $date1, string $date2, string $status) {
    if($conn === false){
        exit();
    }
    $sql = "SELECT pv.*, c.name as currency_name, sup.name as supplier_name FROM purchase_voucher pv 
            LEFT JOIN currency c ON pv.currency = c.id 
            LEFT JOIN subsidiaries sup ON pv.supplier_id = sup.id 
            WHERE pv.date_ BETWEEN '$date1' AND '$date2' AND pv.deleted_at IS NULL AND pv.status = '$status' ORDER BY pv.date_ DESC";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

  
function softDeletePurchaseVoucher(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) return false;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher pv ON t.id = pv.transaction_id
                 SET t.deleted_at = NOW(), t.deleted_by = $deleted_by
                 WHERE pv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher pv ON t.id = pv.tax_transaction_id
                 SET t.deleted_at = NOW(), t.deleted_by = $deleted_by
                 WHERE pv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE purchase_voucher
                 SET deleted_at = NOW(), deleted_by = $deleted_by
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function softDeleteSalesVoucher(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) return false;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher sv ON t.id = sv.transaction_id
                 SET t.deleted_at = NOW(), t.deleted_by = $deleted_by
                 WHERE sv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher sv ON t.id = sv.tax_transaction_id
                 SET t.deleted_at = NOW(), t.deleted_by = $deleted_by
                 WHERE sv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE sales_voucher_details
                 SET deleted_at = NOW(), deleted_by = $deleted_by
                 WHERE sv_id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $sql4 = "UPDATE sales_voucher
                 SET deleted_at = NOW(), deleted_by = $deleted_by
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql4)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function reviewPurchaseVoucher(mysqli $conn, int $id, int $reviewed_by) {
    if ($conn === false) exit;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE purchase_voucher
                 SET status = 'pending', reviewed_by = $reviewed_by, reviewed_at = NOW()
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher_details pvd ON t.id = pvd.transaction_id
                 SET t.status = 'pending', t.reviewed_by = $reviewed_by, t.reviewed_at = NOW()
                 WHERE pvd.pv_id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher pv ON t.id = pv.tax_transaction_id
                 SET t.status = 'pending', t.reviewed_by = $reviewed_by, t.reviewed_at = NOW()
                 WHERE pv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function reviewSalesVoucher(mysqli $conn, int $id, int $reviewed_by) {   
    if ($conn === false) exit;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE sales_voucher
                 SET status = 'pending', reviewed_by = $reviewed_by, reviewed_at = NOW()
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher_details svd ON t.id = svd.transaction_id
                 SET t.status = 'pending', t.reviewed_by = $reviewed_by, t.reviewed_at = NOW()
                 WHERE svd.sv_id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher sv ON t.id = sv.tax_transaction_id
                 SET t.status = 'pending', t.reviewed_by = $reviewed_by, t.reviewed_at = NOW()
                 WHERE sv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function approvePurchaseVoucher(mysqli $conn, int $id, int $approved_by) {
    if ($conn === false) exit;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE purchase_voucher
                 SET status = 'active', approved_by = $approved_by, approved_at = NOW()
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher_details pvd ON t.id = pvd.transaction_id
                 SET t.status = 'active', t.approved_by = $approved_by, t.approved_at = NOW()
                 WHERE pvd.pv_id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher pv ON t.id = pv.tax_transaction_id
                 SET t.status = 'active', t.approved_by = $approved_by, t.approved_at = NOW()
                 WHERE pv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function approveSalesVoucher(mysqli $conn, int $id, int $approved_by) {
    if ($conn === false) exit;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE sales_voucher
                 SET status = 'active', approved_by = $approved_by, approved_at = NOW()
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher_details svd ON t.id = svd.transaction_id
                 SET t.status = 'active', t.approved_by = $approved_by, t.approved_at = NOW()
                 WHERE svd.sv_id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher sv ON t.id = sv.tax_transaction_id
                 SET t.status = 'active', t.approved_by = $approved_by, t.approved_at = NOW()
                 WHERE sv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function rejectPurchaseVoucher(mysqli $conn, int $id, int $rejected_by, string $rejection_reason) {
    if ($conn === false) exit;
    $rejection_reason = $conn->real_escape_string($rejection_reason);
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE purchase_voucher
                 SET status = 'rejected', rejected_by = $rejected_by, rejected_at = NOW(), rejection_reason = '$rejection_reason'
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher_details pvd ON t.id = pvd.transaction_id
                 SET t.status = 'rejected', t.rejected_by = $rejected_by, t.rejected_at = NOW(), t.rejection_reason = '$rejection_reason'
                 WHERE pvd.pv_id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t
                 INNER JOIN purchase_voucher pv ON t.id = pv.tax_transaction_id
                 SET t.status = 'rejected', t.rejected_by = $rejected_by, t.rejected_at = NOW(), t.rejection_reason = '$rejection_reason'
                 WHERE pv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function rejectSalesVoucher(mysqli $conn, int $id, int $rejected_by, string $rejection_reason) {
    if ($conn === false) exit;
    $rejection_reason = $conn->real_escape_string($rejection_reason);
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE sales_voucher
                 SET status = 'rejected', rejected_by = $rejected_by, rejected_at = NOW(), rejection_reason = '$rejection_reason'
                 WHERE id = $id AND deleted_at IS NULL";
        if (!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher_details svd ON t.id = svd.transaction_id
                 SET t.status = 'rejected', t.rejected_by = $rejected_by, t.rejected_at = NOW(), t.rejection_reason = '$rejection_reason'
                 WHERE svd.sv_id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t
                 INNER JOIN sales_voucher sv ON t.id = sv.tax_transaction_id
                 SET t.status = 'rejected', t.rejected_by = $rejected_by, t.rejected_at = NOW(), t.rejection_reason = '$rejection_reason'
                 WHERE sv.id = $id AND t.deleted_at IS NULL";
        if (!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

 
 
function createBulkyTransaction(mysqli $conn, string $date, string $reference, string $voucher_type, float $subtotal, float $tax, float $amount, int $currency, float $currency_rate, int $project_id, int $created_by, string $supplier = null, string $payment_heading = null) {
    $sql = "INSERT INTO `bulky_transactions` (`date_`, `reference`, `voucher_type`, `subtotal`, `tax`, `amount`, `currency`, `currency_rate`, `project_id`, `created_by`, `supplier`, `payment_heading`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false) return false;
    $stmt->bind_param("sssdddidiiss", $date, $reference, $voucher_type, $subtotal, $tax, $amount, $currency, $currency_rate, $project_id, $created_by, $supplier, $payment_heading);
    if($stmt->execute()){
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}
function createBulkyTransactionItem(mysqli $conn, int $bv_id, int $dr_account, int $cr_account, int $transaction_id, int $tax_transaction_id, int $sub_id, int $item_id, string $description, float $quantity, int $unit, float $unit_price, float $amount, float $equiv_amount, float $vat_rate, float $vat_amount, string $attachment = null, int $created_by = null, int $project_id = null) {
    $sql = "INSERT INTO `bulky_transaction_items` (`bv_id`, `dr_account`, `cr_account`, `transaction_id`, `tax_transaction_id`, `sub_id`, `item_id`, `description`, `quantity`, `unit`, `unit_price`, `amount`, `equiv_amount`, `vat_rate`, `vat_amount`, `attachment`, `created_by`, `project_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false) return false;
    $stmt->bind_param("iiiiiiisdidddddsii", $bv_id, $dr_account, $cr_account, $transaction_id, $tax_transaction_id, $sub_id, $item_id, $description, $quantity, $unit, $unit_price, $amount, $equiv_amount, $vat_rate, $vat_amount, $attachment, $created_by, $project_id);
    if($stmt->execute()){
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function selectBulkyVouchersByDateRangeAndType(mysqli $conn, string $date1, string $date2, string $type, string $status) {
    if($conn === false) exit();
    $typeFilter   = $type == 'all' ? "bv.voucher_type IN ('bulky-payment','bulky-receipt','bulky-journal')" : "bv.voucher_type = '$type'";
    $statusFilter = !empty($status) ? "AND bv.status = '$status'" : "";
    $sql = "SELECT bv.*, 
            c.name as currency_name,
            u1.names as created_by_name
            FROM bulky_transactions bv
            LEFT JOIN currency c ON bv.currency = c.id
            LEFT JOIN users u1 ON bv.created_by = u1.id
            WHERE bv.date_ BETWEEN '$date1' AND '$date2'
            AND $typeFilter
            $statusFilter
            AND bv.deleted_at IS NULL
            ORDER BY bv.date_ DESC";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectBulkyVoucherById(mysqli $conn, int $id) {
    if($conn === false) exit();
    $sql = "SELECT bv.*,
            c.name as currency_name,
            u1.names as created_by_name,
            u2.names as reviewed_by_name,
            u3.names as approved_by_name
            FROM bulky_transactions bv
            LEFT JOIN currency c ON bv.currency = c.id
            LEFT JOIN users u1 ON bv.created_by = u1.id
            LEFT JOIN users u2 ON bv.reviewed_by = u2.id
            LEFT JOIN users u3 ON bv.approved_by = u3.id
            WHERE bv.id = $id AND bv.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectBulkyVoucherItems(mysqli $conn, int $bvId) {
    if($conn === false) exit();
    $sql = "SELECT bvi.*,
            ms.name as item_name,
            u.name as unit_name
            FROM bulky_transaction_items bvi
            LEFT JOIN min_subs ms ON bvi.item_id = ms.id
            LEFT JOIN units u ON bvi.unit = u.id
            WHERE bvi.bv_id = $bvId AND bvi.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function softDeleteBulkyVoucherItem(mysqli $conn, int $id, int $deleted_by) {
    if($conn === false) exit();
    $sql = "UPDATE bulky_transaction_items 
            SET deleted_at = NOW(), deleted_by = $deleted_by 
            WHERE id = $id AND deleted_at IS NULL";
    if($conn->query($sql)) return true;
    return $conn->error;
}

function reviewBulkyVoucher(mysqli $conn, int $id, int $reviewed_by) {
    if($conn === false) exit;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE bulky_transactions 
                 SET status = 'pending', reviewed_by = $reviewed_by, reviewed_at = NOW() 
                 WHERE id = $id AND deleted_at IS NULL";
        if(!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t 
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.transaction_id 
                 SET t.status = 'pending', t.reviewed_by = $reviewed_by, t.reviewed_at = NOW() 
                 WHERE bvi.bv_id = $id AND t.deleted_at IS NULL";
        if(!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t 
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.tax_transaction_id 
                 SET t.status = 'pending', t.reviewed_by = $reviewed_by, t.reviewed_at = NOW() 
                 WHERE bvi.bv_id = $id AND bvi.tax_transaction_id > 0 AND t.deleted_at IS NULL";
        if(!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch(Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function approveBulkyVoucher(mysqli $conn, int $id, int $approved_by) {
    if($conn === false) exit;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE bulky_transactions 
                 SET status = 'active', approved_by = $approved_by, approved_at = NOW() 
                 WHERE id = $id AND deleted_at IS NULL";
        if(!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t 
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.transaction_id 
                 SET t.status = 'active', t.approved_by = $approved_by, t.approved_at = NOW() 
                 WHERE bvi.bv_id = $id AND t.deleted_at IS NULL";
        if(!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t 
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.tax_transaction_id 
                 SET t.status = 'active', t.approved_by = $approved_by, t.approved_at = NOW() 
                 WHERE bvi.bv_id = $id AND bvi.tax_transaction_id > 0 AND t.deleted_at IS NULL";
        if(!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch(Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

 

function softDeleteBulkyVoucher(mysqli $conn, int $id, int $deleted_by) {
    if($conn === false) return false;
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE transaction_voucher t 
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.transaction_id 
                 SET t.deleted_at = NOW(), t.deleted_by = $deleted_by 
                 WHERE bvi.bv_id = $id AND t.deleted_at IS NULL";
        if(!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t 
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.tax_transaction_id 
                 SET t.deleted_at = NOW(), t.deleted_by = $deleted_by 
                 WHERE bvi.bv_id = $id AND bvi.tax_transaction_id > 0 AND t.deleted_at IS NULL";
        if(!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE bulky_transaction_items 
                 SET deleted_at = NOW(), deleted_by = $deleted_by 
                 WHERE bv_id = $id AND deleted_at IS NULL";
        if(!$conn->query($sql3)) throw new Exception($conn->error);
        $sql4 = "UPDATE bulky_transactions 
                 SET deleted_at = NOW(), deleted_by = $deleted_by 
                 WHERE id = $id AND deleted_at IS NULL";
        if(!$conn->query($sql4)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch(Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function rejectBulkyVoucher(mysqli $conn, int $id, int $rejected_by, string $rejection_reason) {
    if($conn === false) exit;
    $rejection_reason = $conn->real_escape_string($rejection_reason);
    $conn->begin_transaction();
    try {
        $sql1 = "UPDATE bulky_transactions
                 SET status = 'rejected',
                     rejected_by = $rejected_by,
                     rejected_at = NOW(),
                     rejection_reason = '$rejection_reason'
                 WHERE id = $id AND deleted_at IS NULL";
        if(!$conn->query($sql1)) throw new Exception($conn->error);
        $sql2 = "UPDATE transaction_voucher t
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.transaction_id
                 SET t.status = 'rejected',
                     t.rejected_by = $rejected_by,
                     t.rejected_at = NOW(),
                     t.rejection_reason = '$rejection_reason'
                 WHERE bvi.bv_id = $id AND t.deleted_at IS NULL";
        if(!$conn->query($sql2)) throw new Exception($conn->error);
        $sql3 = "UPDATE transaction_voucher t
                 INNER JOIN bulky_transaction_items bvi ON t.id = bvi.tax_transaction_id
                 SET t.status = 'rejected',
                     t.rejected_by = $rejected_by,
                     t.rejected_at = NOW(),
                     t.rejection_reason = '$rejection_reason'
                 WHERE bvi.bv_id = $id AND bvi.tax_transaction_id > 0 AND t.deleted_at IS NULL";
        if(!$conn->query($sql3)) throw new Exception($conn->error);
        $conn->commit();
        return true;
    } catch(Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}



















/**
 * Gets monthly sales and purchase trends for chart visualization
 * 
 * @param mysqli $conn Database connection
 * @param string|null $startDate Optional start date filter
 * @param string|null $endDate Optional end date filter
 * @return array|false Returns array with monthly trend data or false on error
 */
 
function getSalesPurchaseReportSummary(mysqli $conn, ?string $startDate = null, ?string $endDate = null, ?int $projectId = null) {
    if($conn === false) return false;
    $salesDateFilter    = ($startDate && $endDate) ? " AND sv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $purchaseDateFilter = ($startDate && $endDate) ? " AND pv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $salesProjectFilter    = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $purchaseProjectFilter = ($projectId !== null) ? " AND pv.project_id = $projectId" : "";
    $sql = "SELECT
                COALESCE(sales_data.total_sales_amount, 0)        AS total_sales_amount,
                COALESCE(sales_data.total_sales_invoices, 0)      AS total_sales_invoices,
                COALESCE(purchase_data.total_purchase_amount, 0)  AS total_purchase_amount,
                COALESCE(purchase_data.total_purchase_invoices, 0) AS total_purchase_invoices,
                (COALESCE(sales_data.total_sales_amount, 0) - COALESCE(purchase_data.total_purchase_amount, 0)) AS net_profit_loss,
                COALESCE(partner_data.total_customers, 0)         AS total_customers,
                COALESCE(partner_data.total_suppliers, 0)         AS total_suppliers
            FROM (
                SELECT SUM(sv.amount) AS total_sales_amount, COUNT(DISTINCT sv.id) AS total_sales_invoices
                FROM sales_voucher sv
                WHERE sv.deleted_at IS NULL $salesDateFilter $salesProjectFilter
            ) sales_data
            CROSS JOIN (
                SELECT SUM(pv.amount) AS total_purchase_amount, COUNT(DISTINCT pv.id) AS total_purchase_invoices
                FROM purchase_voucher pv
                WHERE pv.deleted_at IS NULL $purchaseDateFilter $purchaseProjectFilter
            ) purchase_data
            CROSS JOIN (
                SELECT
                    COUNT(CASE WHEN type = 'customer' THEN 1 END) AS total_customers,
                    COUNT(CASE WHEN type = 'supplier' THEN 1 END) AS total_suppliers
                FROM subsidiaries
                WHERE deleted_at IS NULL
            ) partner_data";
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : false;
}

function getTopSellingItems(mysqli $conn, ?string $startDate = null, ?string $endDate = null, int $limit = 10, ?int $projectId = null) {
    if($conn === false) return false;
    $dateFilter    = ($startDate && $endDate) ? " AND sv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                s.name                AS item_name,
                SUM(svd.quantity)     AS total_quantity,
                SUM(svd.amount)       AS total_amount,
                AVG(svd.price)        AS average_price,
                COUNT(DISTINCT sv.id) AS transaction_count
            FROM sales_voucher_details svd
            JOIN sales_voucher sv ON svd.sv_id = sv.id
            JOIN min_subs s ON svd.item_id = s.id
            WHERE sv.deleted_at IS NULL
            AND svd.deleted_at IS NULL
            $dateFilter $projectFilter
            GROUP BY svd.item_id, s.name
            ORDER BY total_amount DESC
            LIMIT $limit";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getMostPurchasedItems(mysqli $conn, ?string $startDate = null, ?string $endDate = null, int $limit = 1000, ?int $projectId = null) {
    if($conn === false) return false;
    $dateFilter    = ($startDate && $endDate) ? " AND pv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $projectFilter = ($projectId !== null) ? " AND pv.project_id = $projectId" : "";
    $sql = "SELECT
                ms.name               AS item_name,
                SUM(pvd.quantity)     AS total_quantity,
                SUM(pvd.amount)       AS total_amount,
                AVG(pvd.price)        AS average_price,
                COUNT(DISTINCT pv.id) AS transaction_count
            FROM purchase_voucher_details pvd
            JOIN purchase_voucher pv ON pvd.pv_id = pv.id
            JOIN min_subs ms ON pvd.item_id = ms.id
            WHERE pv.deleted_at IS NULL
            AND pvd.deleted_at IS NULL
            $dateFilter $projectFilter
            GROUP BY pvd.item_id, ms.name
            ORDER BY total_amount DESC
            LIMIT $limit";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getTopCustomers(mysqli $conn, ?string $startDate = null, ?string $endDate = null, int $limit = 10, ?int $projectId = null) {
    if($conn === false) return false;
    $dateFilter    = ($startDate && $endDate) ? " AND sv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                s.id                  AS customer_id,
                s.name                AS customer_name,
                s.address             AS customer_address,
                COUNT(DISTINCT sv.id) AS total_invoices,
                SUM(sv.amount)        AS total_amount,
                AVG(sv.amount)        AS average_invoice_amount,
                MAX(sv.date_)         AS last_purchase_date
            FROM sales_voucher sv
            JOIN subsidiaries s ON sv.customer_id = s.id
            WHERE sv.deleted_at IS NULL
            AND s.deleted_at IS NULL
            $dateFilter $projectFilter
            GROUP BY s.id, s.name, s.address
            ORDER BY total_amount DESC
            LIMIT $limit";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getTopSuppliers(mysqli $conn, ?string $startDate = null, ?string $endDate = null, int $limit = 10, ?int $projectId = null) {
    if($conn === false) return false;
    $dateFilter    = ($startDate && $endDate) ? " AND pv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $projectFilter = ($projectId !== null) ? " AND pv.project_id = $projectId" : "";
    $sql = "SELECT
                s.id                  AS supplier_id,
                s.name                AS supplier_name,
                s.address             AS supplier_address,
                COUNT(DISTINCT pv.id) AS total_orders,
                SUM(pv.amount)        AS total_amount,
                AVG(pv.amount)        AS average_order_amount,
                MAX(pv.date_)         AS last_order_date
            FROM purchase_voucher pv
            JOIN subsidiaries s ON pv.supplier_id = s.id
            WHERE pv.deleted_at IS NULL
            AND s.deleted_at IS NULL
            $dateFilter $projectFilter
            GROUP BY s.id, s.name, s.address
            ORDER BY total_amount DESC
            LIMIT $limit";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getSalesPurchaseTrends(mysqli $conn, ?string $startDate = null, ?string $endDate = null, ?int $projectId = null) {
    if($conn === false) return false;
    $dateFilter            = ($startDate && $endDate) ? " AND date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $salesProjectFilter    = ($projectId !== null) ? " AND project_id = $projectId" : "";
    $purchaseProjectFilter = ($projectId !== null) ? " AND project_id = $projectId" : "";
    $sql = "SELECT
                DATE_FORMAT(date_, '%Y-%m') AS month_year,
                'Sales'                     AS type,
                SUM(amount)                 AS amount,
                COUNT(*)                    AS transaction_count
            FROM sales_voucher
            WHERE deleted_at IS NULL
            $dateFilter $salesProjectFilter
            GROUP BY DATE_FORMAT(date_, '%Y-%m')
            UNION ALL
            SELECT
                DATE_FORMAT(date_, '%Y-%m') AS month_year,
                'Purchase'                  AS type,
                SUM(amount)                 AS amount,
                COUNT(*)                    AS transaction_count
            FROM purchase_voucher
            WHERE deleted_at IS NULL
            $dateFilter $purchaseProjectFilter
            GROUP BY DATE_FORMAT(date_, '%Y-%m')
            ORDER BY month_year, type";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getProfitAnalysis(mysqli $conn, ?string $startDate = null, ?string $endDate = null, ?int $projectId = null) {
    if($conn === false) return false;
    $salesDateFilter       = ($startDate && $endDate) ? " AND sv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $purchaseDateFilter    = ($startDate && $endDate) ? " AND pv.date_ BETWEEN '$startDate' AND '$endDate'" : "";
    $salesProjectFilter    = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $purchaseProjectFilter = ($projectId !== null) ? " AND pv.project_id = $projectId" : "";
    $sql = "SELECT
                item_name,
                total_sales_amount,
                total_purchase_cost,
                (total_sales_amount - total_purchase_cost) AS profit,
                CASE
                    WHEN total_purchase_cost > 0
                    THEN ROUND(((total_sales_amount - total_purchase_cost) / total_purchase_cost) * 100, 2)
                    ELSE 0
                END AS profit_margin_percentage
            FROM (
                SELECT
                    COALESCE(sales.item_name, purchases.item_name) AS item_name,
                    COALESCE(sales.total_amount, 0)                AS total_sales_amount,
                    COALESCE(purchases.total_amount, 0)            AS total_purchase_cost
                FROM (
                    SELECT ms.name AS item_name, SUM(svd.amount) AS total_amount
                    FROM sales_voucher_details svd
                    JOIN sales_voucher sv ON svd.sv_id = sv.id
                    JOIN min_subs ms ON svd.item_id = ms.id
                    WHERE sv.deleted_at IS NULL
                    AND svd.deleted_at IS NULL
                    $salesDateFilter $salesProjectFilter
                    GROUP BY ms.id, ms.name
                ) sales
                LEFT JOIN (
                    SELECT ms.name AS item_name, SUM(pvd.amount) AS total_amount
                    FROM purchase_voucher_details pvd
                    JOIN purchase_voucher pv ON pvd.pv_id = pv.id
                    JOIN min_subs ms ON pvd.item_id = ms.id
                    WHERE pv.deleted_at IS NULL
                    AND pvd.deleted_at IS NULL
                    $purchaseDateFilter $purchaseProjectFilter
                    GROUP BY ms.id, ms.name
                ) purchases ON sales.item_name = purchases.item_name
                UNION
                SELECT
                    COALESCE(purchases.item_name, sales.item_name) AS item_name,
                    COALESCE(sales.total_amount, 0)                AS total_sales_amount,
                    COALESCE(purchases.total_amount, 0)            AS total_purchase_cost
                FROM (
                    SELECT ms.name AS item_name, SUM(pvd.amount) AS total_amount
                    FROM purchase_voucher_details pvd
                    JOIN purchase_voucher pv ON pvd.pv_id = pv.id
                    JOIN min_subs ms ON pvd.item_id = ms.id
                    WHERE pv.deleted_at IS NULL
                    AND pvd.deleted_at IS NULL
                    $purchaseDateFilter $purchaseProjectFilter
                    GROUP BY ms.id, ms.name
                ) purchases
                LEFT JOIN (
                    SELECT ms.name AS item_name, SUM(svd.amount) AS total_amount
                    FROM sales_voucher_details svd
                    JOIN sales_voucher sv ON svd.sv_id = sv.id
                    JOIN min_subs ms ON svd.item_id = ms.id
                    WHERE sv.deleted_at IS NULL
                    AND svd.deleted_at IS NULL
                    $salesDateFilter $salesProjectFilter
                    GROUP BY ms.id, ms.name
                ) sales ON purchases.item_name = sales.item_name
                WHERE sales.item_name IS NULL
            ) profit_data
            GROUP BY item_name, total_sales_amount, total_purchase_cost
            ORDER BY profit DESC";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getSalesVoucherReport(mysqli $conn, ?string $startDate = null, ?string $endDate = null, ?int $projectId = null) {
    if($conn === false) return false;
    if(!$startDate || !$endDate) {
        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');
    }
    $report                       = [];
    $report['summary']            = getSalesSummary($conn, $startDate, $endDate, $projectId);
    $report['top_customers']      = getTopPerformingCustomers($conn, $startDate, $endDate, 10, $projectId);
    $report['daily_sales']        = getDailySalesTrend($conn, $startDate, $endDate, $projectId);
    $report['sales_by_region']    = getSalesByRegion($conn, $startDate, $endDate, $projectId);
    $report['top_items']          = getTopSellingItemsDetailed($conn, $startDate, $endDate, 10, $projectId);
    $report['monthly_comparison'] = getMonthlySalesComparison($conn, $startDate, $endDate, $projectId);
    $report['insights']           = getSalesInsights($conn, $startDate, $endDate, $projectId);
    return $report;
}

function getSalesSummary(mysqli $conn, string $startDate, string $endDate, ?int $projectId = null) {
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                COUNT(DISTINCT sv.id)          AS total_invoices,
                SUM(sv.amount)                 AS total_sales_amount,
                AVG(sv.amount)                 AS average_invoice_amount,
                COUNT(DISTINCT sv.customer_id) AS unique_customers,
                MIN(sv.date_)                  AS first_sale_date,
                MAX(sv.date_)                  AS last_sale_date
            FROM sales_voucher sv
            WHERE sv.deleted_at IS NULL
            AND sv.date_ BETWEEN '$startDate' AND '$endDate'
            $projectFilter";
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : false;
}

function getTopPerformingCustomers(mysqli $conn, string $startDate, string $endDate, int $limit = 10, ?int $projectId = null) {
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                s.id                              AS customer_id,
                s.name                            AS customer_name,
                s.address                         AS customer_address,
                s.region,
                s.district,
                s.phone,
                s.email,
                COUNT(DISTINCT sv.id)             AS total_invoices,
                SUM(sv.amount)                    AS total_amount,
                AVG(sv.amount)                    AS average_invoice_amount,
                MIN(sv.date_)                     AS first_purchase_date,
                MAX(sv.date_)                     AS last_purchase_date,
                DATEDIFF('$endDate', MAX(sv.date_)) AS days_since_last_purchase,
                COALESCE(SUM(svd.quantity), 0)    AS total_items_purchased
            FROM sales_voucher sv
            JOIN subsidiaries s ON sv.customer_id = s.id
            LEFT JOIN sales_voucher_details svd ON sv.id = svd.sv_id AND svd.deleted_at IS NULL
            WHERE sv.deleted_at IS NULL
            AND s.deleted_at IS NULL
            AND sv.date_ BETWEEN '$startDate' AND '$endDate'
            $projectFilter
            GROUP BY s.id, s.name, s.address, s.region, s.district, s.phone, s.email
            ORDER BY total_amount DESC
            LIMIT $limit";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getDailySalesTrend(mysqli $conn, string $startDate, string $endDate, ?int $projectId = null) {
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                DATE(sv.date_)                 AS sale_date,
                COUNT(DISTINCT sv.id)          AS daily_invoices,
                SUM(sv.amount)                 AS daily_amount,
                AVG(sv.amount)                 AS avg_invoice_amount,
                COUNT(DISTINCT sv.customer_id) AS unique_customers
            FROM sales_voucher sv
            WHERE sv.deleted_at IS NULL
            AND sv.date_ BETWEEN '$startDate' AND '$endDate'
            $projectFilter
            GROUP BY DATE(sv.date_)
            ORDER BY sale_date";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getSalesByRegion(mysqli $conn, string $startDate, string $endDate, ?int $projectId = null) {
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                COALESCE(s.region, 'Unknown') AS region,
                COUNT(DISTINCT sv.id)         AS total_invoices,
                SUM(sv.amount)                AS total_amount,
                COUNT(DISTINCT s.id)          AS unique_customers,
                AVG(sv.amount)                AS average_invoice_amount
            FROM sales_voucher sv
            JOIN subsidiaries s ON sv.customer_id = s.id
            WHERE sv.deleted_at IS NULL
            AND s.deleted_at IS NULL
            AND sv.date_ BETWEEN '$startDate' AND '$endDate'
            $projectFilter
            GROUP BY s.region
            ORDER BY total_amount DESC";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getTopSellingItemsDetailed(mysqli $conn, string $startDate, string $endDate, int $limit = 10, ?int $projectId = null) {
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                ms.id                          AS item_id,
                ms.name                        AS item_name,
                ms.subcategory,
                SUM(svd.quantity)              AS total_quantity,
                SUM(svd.amount)                AS total_amount,
                AVG(svd.price)                 AS average_price,
                COUNT(DISTINCT svd.sv_id)      AS transaction_count,
                COUNT(DISTINCT sv.customer_id) AS unique_customers,
                MIN(svd.price)                 AS min_price,
                MAX(svd.price)                 AS max_price
            FROM sales_voucher_details svd
            JOIN sales_voucher sv ON svd.sv_id = sv.id
            JOIN min_subs ms ON svd.item_id = ms.id
            WHERE sv.deleted_at IS NULL
            AND svd.deleted_at IS NULL
            AND sv.date_ BETWEEN '$startDate' AND '$endDate'
            $projectFilter
            GROUP BY ms.id, ms.name, ms.subcategory
            ORDER BY total_amount DESC
            LIMIT $limit";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getMonthlySalesComparison(mysqli $conn, string $startDate, string $endDate, ?int $projectId = null) {
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                DATE_FORMAT(sv.date_, '%Y-%m')      AS month_year,
                DATE_FORMAT(MIN(sv.date_), '%M %Y') AS month_name,
                COUNT(DISTINCT sv.id)               AS total_invoices,
                SUM(sv.amount)                      AS total_amount,
                AVG(sv.amount)                      AS average_invoice_amount,
                COUNT(DISTINCT sv.customer_id)      AS unique_customers
            FROM sales_voucher sv
            WHERE sv.deleted_at IS NULL
            AND sv.date_ BETWEEN DATE_SUB('$startDate', INTERVAL 12 MONTH) AND '$endDate'
            $projectFilter
            GROUP BY DATE_FORMAT(sv.date_, '%Y-%m')
            ORDER BY month_year DESC
            LIMIT 12";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function getSalesInsights(mysqli $conn, string $startDate, string $endDate, ?int $projectId = null) {
    $insights      = [];
    $projectFilter = ($projectId !== null) ? " AND sv.project_id = $projectId" : "";
    $sql = "SELECT
                DAYNAME(sv.date_) AS day_name,
                COUNT(*)          AS invoice_count,
                SUM(sv.amount)    AS total_amount
            FROM sales_voucher sv
            WHERE sv.deleted_at IS NULL
            AND sv.date_ BETWEEN '$startDate' AND '$endDate'
            $projectFilter
            GROUP BY DAYNAME(sv.date_)
            ORDER BY total_amount DESC
            LIMIT 1";
    $result = $conn->query($sql);
    $insights['peak_sales_day'] = $result ? $result->fetch_assoc() : null;
    $sql = "SELECT
                COUNT(CASE WHEN invoice_count > 1 THEN 1 END) AS repeat_customers,
                COUNT(CASE WHEN invoice_count = 1 THEN 1 END) AS one_time_customers,
                COUNT(*)                                       AS total_customers
            FROM (
                SELECT sv.customer_id, COUNT(*) AS invoice_count
                FROM sales_voucher sv
                WHERE sv.deleted_at IS NULL
                AND sv.date_ BETWEEN '$startDate' AND '$endDate'
                $projectFilter
                GROUP BY sv.customer_id
            ) customer_purchases";
    $result = $conn->query($sql);
    $insights['customer_retention'] = $result ? $result->fetch_assoc() : null;
    $periodDays    = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
    $prevStartDate = date('Y-m-d', strtotime($startDate . " -$periodDays days"));
    $prevEndDate   = date('Y-m-d', strtotime($endDate   . " -$periodDays days"));
    $sql = "SELECT 'current' AS period, SUM(sv.amount) AS total_amount, COUNT(*) AS invoice_count
            FROM sales_voucher sv
            WHERE sv.deleted_at IS NULL
            AND sv.date_ BETWEEN '$startDate' AND '$endDate'
            $projectFilter
            UNION ALL
            SELECT 'previous' AS period, SUM(sv.amount) AS total_amount, COUNT(*) AS invoice_count
            FROM sales_voucher sv
            WHERE sv.deleted_at IS NULL
            AND sv.date_ BETWEEN '$prevStartDate' AND '$prevEndDate'
            $projectFilter";
    $result = $conn->query($sql);
    if($result) {
        $periodData = [];
        while($row = $result->fetch_assoc()) $periodData[$row['period']] = $row;
        if(isset($periodData['current'], $periodData['previous'])) {
            $currentAmount  = $periodData['current']['total_amount']  ?? 0;
            $previousAmount = $periodData['previous']['total_amount'] ?? 0;
            $insights['growth_rate'] = $previousAmount > 0
                ? round((($currentAmount - $previousAmount) / $previousAmount) * 100, 2)
                : ($currentAmount > 0 ? 100 : 0);
        }
    }
    return $insights;
}

?>