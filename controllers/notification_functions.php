<?php

function setNotification($type, $message) {
    $_SESSION['notification'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getNotification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        return $notification;
    }
    return null;
}


// 1. Check if user has pending grantor requests
function countPendingGrantorRequests(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT COUNT(*) as total FROM loan_grantors 
            WHERE user_id = $userId 
            AND status = 'pending' 
            AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data['total'];
    } else {
        return 0;
    }
}

// 2. Check if user's loan request has been approved/disbursed
function countApprovedLoans(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT COUNT(*) as total FROM loan_requests 
            WHERE user_id = $userId 
            AND status = 1 
            AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data['total'];
    } else {
        return 0;
    }
}

// 3. Check if grantors accepted/rejected for user's loan
function countGrantorResponses(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT COUNT(*) as total FROM loan_grantors lg
            INNER JOIN loan_requests lr ON lg.loan_request_id = lr.id
            WHERE lr.user_id = $userId 
            AND lg.status != 'pending' 
            AND lg.deleted_at IS NULL 
            AND lr.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data['total'];
    } else {
        return 0;
    }
}

// 4. Check for upcoming repayments (10 days or less)
function countUpcomingRepayments(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT COUNT(*) as total FROM loan_schedule 
            WHERE user_id = $userId 
            AND status = 'pending' 
            AND DATEDIFF(date_, CURDATE()) <= 10 
            AND DATEDIFF(date_, CURDATE()) >= 0 
            AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data['total'];
    } else {
        return 0;
    }
}

// 5. Check for overdue repayments
function countOverdueRepayments(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT COUNT(*) as total FROM loan_schedule 
            WHERE user_id = $userId 
            AND status = 'pending' 
            AND date_ < CURDATE() 
            AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data['total'];
    } else {
        return 0;
    }
}

// 6. Check recent successful repayments (last 7 days)
function countRecentRepayments(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT COUNT(*) as total FROM loan_schedule 
            WHERE user_id = $userId 
            AND status = 'paid' 
            AND date_paid >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
            AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data['total'];
    } else {
        return 0;
    }
}

// Main function to get total notifications
function getTotalNotifications(mysqli $conn, int $userId) {
    if ($conn === false) {
        return 0;
    }
    
    $total = 0;
    
    $total += countPendingGrantorRequests($conn, $userId);
    $total += countApprovedLoans($conn, $userId);
    $total += countGrantorResponses($conn, $userId);
    $total += countUpcomingRepayments($conn, $userId);
    $total += countOverdueRepayments($conn, $userId);
    $total += countRecentRepayments($conn, $userId);
    
    return $total;
}
 
 
// Get pending grantor requests details
function getPendingGrantorRequests(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT * FROM loan_grantors 
            WHERE user_id = $userId 
            AND status = 'pending' 
            AND deleted_at IS NULL
            ORDER BY created_at DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return [];
    }
}

// Get approved loans details
function getApprovedLoans(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT * FROM loan_requests 
            WHERE user_id = $userId 
            AND status = 1 
            AND deleted_at IS NULL
            ORDER BY updated_at DESC
            LIMIT 5";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return [];
    }
}

// Get grantor responses details
function getGrantorResponses(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT lg.* FROM loan_grantors lg
            INNER JOIN loan_requests lr ON lg.loan_request_id = lr.id
            WHERE lr.user_id = $userId 
            AND lg.status != 'pending' 
            AND lg.deleted_at IS NULL 
            AND lr.deleted_at IS NULL
            ORDER BY lg.updated_at DESC
            LIMIT 5";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return [];
    }
}

// Get upcoming repayments details
function getUpcomingRepayments(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT * FROM loan_schedule 
            WHERE user_id = $userId 
            AND status = 'pending' 
            AND DATEDIFF(date_, CURDATE()) <= 10 
            AND DATEDIFF(date_, CURDATE()) >= 0 
            AND deleted_at IS NULL
            ORDER BY date_ ASC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return [];
    }
}

// Get overdue repayments details
function getOverdueRepayments(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT * FROM loan_schedule 
            WHERE user_id = $userId 
            AND status = 'pending' 
            AND date_ < CURDATE() 
            AND deleted_at IS NULL
            ORDER BY date_ ASC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return [];
    }
}

// Get recent repayments details
function getRecentRepayments(mysqli $conn, int $userId) {
    if ($conn === false) {
        exit;
    }
    $sql = "SELECT * FROM loan_schedule 
            WHERE user_id = $userId 
            AND status = 'paid' 
            AND date_paid >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
            AND deleted_at IS NULL
            ORDER BY date_paid DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return [];
    }
}
?>