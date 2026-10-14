<?php

/**
 * Audit Log Functions
 * Purpose: Track all critical operations for security and compliance
 */

/**
 * Log an audit event
 * @param mysqli $conn Database connection
 * @param int $userId User ID performing the action
 * @param string $action Action performed (e.g., 'delete_subsidiary', 'update_transaction')
 * @param string|null $tableName Table name affected
 * @param int|null $recordId Record ID affected
 * @param string|null $oldValues Previous values (JSON)
 * @param string|null $newValues New values (JSON)
 * @param string $status Status: success, failed, unauthorized
 * @param string $riskLevel Risk level: low, medium, high, critical
 * @param string|null $description Description of the action
 * @return bool|string True on success, error message on failure
 */
function logAuditEvent(mysqli $conn, int $userId, string $action, ?string $tableName = null, ?int $recordId = null, ?string $oldValues = null, ?string $newValues = null, string $status = 'success', string $riskLevel = 'medium', ?string $description = null) {
    if ($conn === false) {
        return "Database connection failed";
    }

    // Get session information
    $sessionId = session_id() ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $sql = "INSERT INTO audit_log (
        user_id, username, action, table_name, record_id, 
        old_values, new_values, ip_address, user_agent, session_id,
        status, risk_level, description, timestamp, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Prepare failed: " . $conn->error;
    }

    $username = $_SESSION['username'] ?? 'Unknown';
    
    $stmt->bind_param(
        "ississsssssss",
        $userId, $username, $action, $tableName, $recordId,
        $oldValues, $newValues, $ipAddress, $userAgent, $sessionId,
        $status, $riskLevel, $description
    );

    $result = $stmt->execute();
    $stmt->close();

    return $result ? true : $stmt->error;
}

/**
 * Log transaction edit access
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param int $transactionId Transaction ID
 * @param string $status Status: success, unauthorized
 * @return bool|string
 */
function logTransactionEditAccess(mysqli $conn, int $userId, int $transactionId, string $status = 'success') {
    $description = $status === 'success' 
        ? "User accessed transaction edit page for transaction ID: $transactionId"
        : "Unauthorized attempt to edit transaction ID: $transactionId";
    
    return logAuditEvent(
        $conn,
        $userId,
        'edit_transaction_access',
        'transaction_voucher',
        $transactionId,
        null,
        null,
        $status,
        $status === 'unauthorized' ? 'high' : 'low',
        $description
    );
}

/**
 * Log transaction deletion
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param int $transactionId Transaction ID
 * @param string $status Status: success, failed, unauthorized
 * @param string|null $errorMessage Error message if failed
 * @return bool|string
 */
function logTransactionDeletion(mysqli $conn, int $userId, int $transactionId, string $status = 'success', ?string $errorMessage = null) {
    $description = $status === 'success' 
        ? "Successfully deleted transaction ID: $transactionId"
        : ($errorMessage ?? "Failed to delete transaction ID: $transactionId");
    
    return logAuditEvent(
        $conn,
        $userId,
        'delete_transaction',
        'transaction_voucher',
        $transactionId,
        null,
        null,
        $status,
        $status === 'success' ? 'medium' : 'high',
        $description
    );
}

/**
 * Log subsidiary deletion
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param int $subId Subsidiary ID being deleted
 * @param array|null $subData Subsidiary data before deletion
 * @return bool|string
 */
function logSubsidiaryDeletion(mysqli $conn, int $userId, int $subId, ?array $subData = null) {
    $oldValues = $subData ? json_encode($subData) : null;
    $description = "Subsidiary ID {$subId} deleted by user {$userId}";
    
    return logAuditEvent(
        $conn, 
        $userId, 
        'delete_subsidiary', 
        'subsidiaries', 
        $subId, 
        $oldValues, 
        null, 
        'success', 
        'critical', 
        $description
    );
}

/**
 * Log unauthorized access attempt
 * @param mysqli $conn Database connection
 * @param int|null $userId User ID (if logged in)
 * @param string $action Action attempted
 * @param string $description Description of the attempt
 * @return bool|string
 */
function logUnauthorizedAccess(mysqli $conn, ?int $userId, string $action, string $description) {
    return logAuditEvent(
        $conn, 
        $userId ?? 0, 
        $action, 
        null, 
        null, 
        null, 
        null, 
        'unauthorized', 
        'high', 
        $description
    );
}

/**
 * Log transaction modification
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param int $transactionId Transaction ID
 * @param string $action Action (create, update, delete)
 * @param array|null $oldData Previous transaction data
 * @param array|null $newData New transaction data
 * @return bool|string
 */
function logTransactionModification(mysqli $conn, int $userId, int $transactionId, string $action, ?array $oldData = null, ?array $newData = null) {
    $riskLevel = 'medium';
    if ($action === 'delete') {
        $riskLevel = 'high';
    }
    
    $description = "Transaction {$action}: ID {$transactionId} by user {$userId}";
    
    return logAuditEvent(
        $conn, 
        $userId, 
        $action . '_transaction', 
        'transaction_voucher', 
        $transactionId, 
        $oldData ? json_encode($oldData) : null, 
        $newData ? json_encode($newData) : null, 
        'success', 
        $riskLevel, 
        $description
    );
}

/**
 * Get audit log entries with filtering
 * @param mysqli $conn Database connection
 * @param array $filters Filter options
 * @return array|string
 */
function getAuditLog(mysqli $conn, array $filters = []) {
    if ($conn === false) {
        return "Database connection failed";
    }

    $sql = "SELECT al.*, u.names as username 
            FROM audit_log al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE 1=1";
    
    $params = [];
    $types = '';

    // Apply filters
    if (!empty($filters['user_id'])) {
        $sql .= " AND al.user_id = ?";
        $params[] = $filters['user_id'];
        $types .= 'i';
    }
    
    if (!empty($filters['action'])) {
        $sql .= " AND al.action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
        $types .= 's';
    }
    
    if (!empty($filters['table_name'])) {
        $sql .= " AND al.table_name = ?";
        $params[] = $filters['table_name'];
        $types .= 's';
    }
    
    if (!empty($filters['risk_level'])) {
        $sql .= " AND al.risk_level = ?";
        $params[] = $filters['risk_level'];
        $types .= 's';
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND al.status = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND al.timestamp >= ?";
        $params[] = $filters['date_from'];
        $types .= 's';
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND al.timestamp <= ?";
        $params[] = $filters['date_to'];
        $types .= 's';
    }

    $sql .= " ORDER BY al.timestamp DESC";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = $filters['limit'];
        $types .= 'i';
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Prepare failed: " . $conn->error;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return "Execute failed: " . $error;
    }
}

/**
 * Get critical security events
 * @param mysqli $conn Database connection
 * @param int $limit Number of records to return
 * @return array|string
 */
function getCriticalSecurityEvents(mysqli $conn, int $limit = 50) {
    return getAuditLog($conn, [
        'risk_level' => 'critical',
        'limit' => $limit
    ]);
}

/**
 * Check for suspicious activity patterns
 * @param mysqli $conn Database connection
 * @param int $userId User ID to check
 * @return array Suspicious activities found
 */
function checkSuspiciousActivity(mysqli $conn, int $userId) {
    $suspicious = [];
    
    // Check for multiple deletions in short time
    $recentDeletions = getAuditLog($conn, [
        'user_id' => $userId,
        'action' => 'delete',
        'date_from' => date('Y-m-d H:i:s', strtotime('-1 hour'))
    ]);
    
    if (is_array($recentDeletions) && count($recentDeletions) > 5) {
        $suspicious[] = [
            'type' => 'multiple_deletions',
            'count' => count($recentDeletions),
            'timeframe' => '1 hour'
        ];
    }
    
    // Check for unauthorized attempts
    $unauthorizedAttempts = getAuditLog($conn, [
        'user_id' => $userId,
        'status' => 'unauthorized',
        'date_from' => date('Y-m-d H:i:s', strtotime('-24 hours'))
    ]);
    
    if (is_array($unauthorizedAttempts) && count($unauthorizedAttempts) > 3) {
        $suspicious[] = [
            'type' => 'unauthorized_attempts',
            'count' => count($unauthorizedAttempts),
            'timeframe' => '24 hours'
        ];
    }
    
    return $suspicious;
}

/**
 * Create audit summary report
 * @param mysqli $conn Database connection
 * @param string $dateFrom Start date
 * @param string $dateTo End date
 * @return array|string
 */
function getAuditSummaryReport(mysqli $conn, string $dateFrom, string $dateTo) {
    if ($conn === false) {
        return "Database connection failed";
    }

    $sql = "SELECT 
                COUNT(*) as total_events,
                COUNT(CASE WHEN status = 'unauthorized' THEN 1 END) as unauthorized_count,
                COUNT(CASE WHEN risk_level = 'critical' THEN 1 END) as critical_count,
                COUNT(CASE WHEN action LIKE '%delete%' THEN 1 END) as delete_count,
                COUNT(CASE WHEN action LIKE '%login%' THEN 1 END) as login_count,
                COUNT(DISTINCT user_id) as unique_users,
                DATE(timestamp) as event_date
            FROM audit_log 
            WHERE timestamp BETWEEN ? AND ?
            GROUP BY DATE(timestamp)
            ORDER BY event_date DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Prepare failed: " . $conn->error;
    }

    $stmt->bind_param("ss", $dateFrom, $dateTo);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return "Execute failed: " . $error;
    }
}

?>
