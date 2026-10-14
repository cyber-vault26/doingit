<?php

function insertSalary(mysqli $conn, int $subsidiary_id, float $amount, int $created_by) {
    if ($conn === false) exit();
    $sql = "INSERT INTO salary (`subsidiary_id`, `amount`, `status`, `created_by`) VALUES (?, ?, 'active', ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("idi", $subsidiary_id, $amount, $created_by);
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function updateSalary(mysqli $conn, int $id, float $amount, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE salary SET amount = $amount, updated_by = $updated_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteSalary(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE salary SET status = 'inactive', deleted_at = NOW(), deleted_by = $deleted_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectSalaryById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT s.*, sub.name as employee_name 
            FROM salary s 
            JOIN subsidiaries sub ON sub.id = s.subsidiary_id 
            WHERE s.id = $id AND s.deleted_at IS NULL";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

function selectAllSalaries(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT s.*, sub.name as employee_name 
            FROM salary s 
            JOIN subsidiaries sub ON sub.id = s.subsidiary_id 
            WHERE s.deleted_at IS NULL 
            ORDER BY sub.name";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return $conn->error;
    }
}

function selectSalaryByStatus(mysqli $conn, string $status) {
    if ($conn === false) exit();
    $sql = "SELECT s.*, sub.name as employee_name 
            FROM salary s 
            JOIN subsidiaries sub ON sub.id = s.subsidiary_id 
            WHERE s.status = '$status' AND s.deleted_at IS NULL
            ORDER BY sub.name";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return $conn->error;
    }
}

function selectSalaryByStaffId(mysqli $conn, int $subsidiary_id) {
    if ($conn === false) exit();
    $sql = "SELECT s.*, sub.name as employee_name 
            FROM salary s 
            JOIN subsidiaries sub ON sub.id = s.subsidiary_id 
            WHERE s.subsidiary_id = ? AND s.deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subsidiary_id);
    return ($stmt->execute()) ? $stmt->get_result()->fetch_assoc() : null;
}

function updateSalaryStatus(mysqli $conn, int $id, string $status, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE salary SET status = '$status', updated_by = $updated_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectStaffWithoutSalary(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT s.id, s.name 
            FROM subsidiaries s 
            LEFT JOIN salary sal ON sal.subsidiary_id = s.id AND sal.deleted_at IS NULL
            WHERE s.type='staff' 
            AND s.deleted_at IS NULL 
            AND sal.id IS NULL 
            ORDER BY s.name";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return $conn->error;
    }
}

function selectAllowanceByStaffId(mysqli $conn, int $staff_id){
    if($conn === false) exit();
    $sql = "SELECT * FROM allowance_staffs WHERE staff_id = '$staff_id' AND deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}       
      
function calculatePayee($amount) {
    $amount = (float) preg_replace('/[^0-9.]/', '', $amount);
    $payee = 0;
    if ($amount <= 270000) {
        $payee = 0;
    } elseif ($amount > 270000 && $amount <= 520000) {
        $payee = 0.08 * ($amount - 270000);
    } elseif ($amount > 520000 && $amount <= 760000) {
        $payee = 20000 + 0.20 * ($amount - 520000);
    } elseif ($amount > 760000 && $amount <= 1000000) {
        $payee = 68000 + 0.25 * ($amount - 760000);
    } elseif ($amount > 1000000) {
        $payee = 128000 + 0.30 * ($amount - 1000000);
    }
    return $payee;
}

function insertPayrollHeader(mysqli $conn, string $month, int $created_by){
    if($conn === false) exit();
    $sql = "INSERT INTO `payroll_headers` (`month`, `created_by`) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false) return $conn->error;
    $stmt->bind_param("si", $month, $created_by);
    if($stmt->execute()){
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function insertPayrollItem(mysqli $conn, int $payroll_id, int $staff_id, float $basic_salary, float $taxable_amount, float $allowance, float $payee, float $nssf, float $wcf, float $sdl, float $advanced_salary, int $created_by){
    if($conn === false) exit();
    $sql = "INSERT INTO `payroll_items` (`payroll_id`, `staff_id`, `basic_salary`, `allowance`, `nssf`, `taxable_amount`, `payee`, `advanced_salary`, `sdl`, `wcf`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false) return $conn->error;
    $stmt->bind_param("iiddddddddi", $payroll_id, $staff_id, $basic_salary, $allowance, $nssf, $taxable_amount, $payee, $advanced_salary, $sdl, $wcf, $created_by);
    if($stmt->execute()){
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function selectSavedPayrolls(mysqli $conn){
    if($conn === false) exit();
    $sql = "SELECT * FROM payroll_headers WHERE deleted_at IS NULL ORDER BY month DESC";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}
function selectPayrollDetails(mysqli $conn, int $payroll_header_id){
    if($conn === false) exit();
    $sql = "SELECT payroll_items.*, subsidiaries.name as staff FROM payroll_items JOIN subsidiaries ON subsidiaries.id = payroll_items.staff_id WHERE payroll_id = '$payroll_header_id' AND payroll_items.deleted_at IS NULL";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function updatePayrollHeader(mysqli $conn, int $payroll_id, int $updated_by){
    if($conn === false) exit();
    $sql = "UPDATE payroll_headers SET entry_generated = 1, updated_by = $updated_by WHERE id = $payroll_id";
    if($conn->query($sql)){
        return true;
    } else {
        return $conn->error;
    }
}

function softDeletePayrollHeader(mysqli $conn, int $id, int $deleted_by){
    if($conn === false) exit();
    $sql = "UPDATE payroll_headers SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = $id AND deleted_at IS NULL";
    if($conn->query($sql)){
        return true;
    } else {
        return $conn->error;
    }
}
function softDeletePayrollItemsByHeaderId(mysqli $conn, int $payroll_id, int $deleted_by){
    if($conn === false) exit();
    $sql = "UPDATE payroll_items SET deleted_at = NOW(), deleted_by = $deleted_by WHERE payroll_id = $payroll_id AND deleted_at IS NULL";
    if($conn->query($sql)){
        return true;
    } else {
        return $conn->error;
    }
}


?>