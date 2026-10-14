<?php
function insertStockDateRequest(mysqli $conn, int $user_id, string $requested_date, string $transaction_type, string $reason, int $created_by) {
    $sql = "INSERT INTO stock_date_requests (user_id, requested_date, transaction_type, reason, status, created_by, created_at)
            VALUES (?, ?, ?, ?, 'pending', ?, NOW())";
    $stmt = $conn->prepare($sql);
    if($stmt === false) return $conn->error;
    $stmt->bind_param("isssi", $user_id, $requested_date, $transaction_type, $reason, $created_by);
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

function checkStockDateRequestExists(mysqli $conn, int $user_id, string $requested_date, string $transaction_type) {
    $sql = "SELECT id, status 
            FROM stock_date_requests 
            WHERE user_id = $user_id AND requested_date = '" . $conn->real_escape_string($requested_date) . "' AND transaction_type = '" . $conn->real_escape_string($transaction_type) . "' AND deleted_at IS NULL 
            ORDER BY id DESC LIMIT 1";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}


// Select requests by status - for ADMIN (all users)
function selectAllStockDateRequestsByStatus(mysqli $conn, string $status) {
    $sql = "SELECT r.*, u.names AS user_name 
            FROM stock_date_requests r
            LEFT JOIN users u ON u.id = r.user_id
            WHERE r.status = '$status' AND r.deleted_at IS NULL
            ORDER BY r.created_at DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

// Select requests by status - for USER (own only)
function selectUserStockDateRequestsByStatus(mysqli $conn, int $user_id, string $status) {
    $sql = "SELECT * FROM stock_date_requests 
            WHERE user_id = $user_id AND status = '$status' AND deleted_at IS NULL
            ORDER BY created_at DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

// Count requests by status - for ADMIN (all users)
function countAllStockDateRequestsByStatus(mysqli $conn, string $status) {
    $sql = "SELECT COUNT(*) AS total FROM stock_date_requests WHERE status = '$status' AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $row = $result->fetch_assoc();
        $result->free();
        return (int) $row['total'];
    } else {
        return $conn->error;
    }
}

// Count requests by status - for USER (own only)
function countUserStockDateRequestsByStatus(mysqli $conn, int $user_id, string $status) {
    $sql = "SELECT COUNT(*) AS total FROM stock_date_requests WHERE user_id = $user_id AND status = '$status' AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $row = $result->fetch_assoc();
        $result->free();
        return (int) $row['total'];
    } else {
        return $conn->error;
    }
}

function approveStockDateRequest(mysqli $conn, int $id, int $approved_by, ?string $admin_note = null) {
    $admin_note_sql = $admin_note !== null ? "'" . $conn->real_escape_string($admin_note) . "'" : "NULL";
    $sql = "UPDATE stock_date_requests 
            SET status = 'approved', approved_by = $approved_by, approved_at = NOW(), admin_note = $admin_note_sql, updated_at = NOW(), updated_by = $approved_by, seen_by_user = 0
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function rejectStockDateRequest(mysqli $conn, int $id, int $approved_by, ?string $admin_note = null) {
    $admin_note_sql = $admin_note !== null ? "'" . $conn->real_escape_string($admin_note) . "'" : "NULL";
    $sql = "UPDATE stock_date_requests 
            SET status = 'rejected', approved_by = $approved_by, approved_at = NOW(), admin_note = $admin_note_sql, updated_at = NOW(), updated_by = $approved_by, seen_by_user = 0
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectUserApprovedStockDatesToday(mysqli $conn, int $user_id) {
    $sql = "SELECT requested_date, transaction_type 
            FROM stock_date_requests 
            WHERE user_id = $user_id 
              AND status = 'approved' 
              AND DATE(approved_at) = CURDATE() 
              AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}