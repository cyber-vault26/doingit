<?php

/**
 * List monthly balances by bank account ID
 * 
 * @param mysqli $conn Database connection
 * @param int $bankAccountId Bank account ID
 * 
 * @return array Result array with success status and monthly balance data
 */
function listMonthlyBalanceByAccountId(mysqli $conn, int $bankAccountId, int $year) {
    // Validate input
    if (empty($bankAccountId) || $bankAccountId <= 0) {
        return [
            'success' => false,
            'message' => "Invalid bank account ID provided.",
            'data' => []
        ];
    }
    
    // Build query to get monthly balances for specific bank account
    $sql = "SELECT mcb.*, 
                   ba.name as account_name, 
                  
                   u.names as posted_by_name
            FROM monthly_closing_balances mcb
            JOIN subsidiaries ba ON mcb.bank_account_id = ba.id
            JOIN users u ON mcb.posted_by = u.id
            WHERE mcb.bank_account_id = ? 
              AND YEAR(CONCAT(mcb.month, '-01')) = ?
              AND mcb.deleted_at IS NULL
            ORDER BY mcb.month DESC";
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error,
            'data' => []
        ];
    }
    
    $stmt->bind_param("ii", $bankAccountId, $year);
    
    if ($stmt->execute()) {
        $monthlyBalances = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return [
            'success' => true,
            'message' => "Monthly balances fetched successfully!",
            'data' => $monthlyBalances,
            'count' => count($monthlyBalances)
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        
        return [
            'success' => false,
            'message' => "Error fetching monthly balances: " . $error,
            'data' => []
        ];
    }
}
function listMonthlyBalanceByBalanceId(mysqli $conn, int $balanceId) {
    // Validate input
    
    
    // Build query to get monthly balances for specific bank account
    $sql = "SELECT mcb.*, 
                   ba.name as account_name, 
                  
                   u.names as posted_by_name
            FROM monthly_closing_balances mcb
            JOIN subsidiaries ba ON mcb.bank_account_id = ba.id
            JOIN users u ON mcb.posted_by = u.id
            WHERE mcb.id = ? 
             
              AND mcb.deleted_at IS NULL
            ";
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error,
            'data' => []
        ];
    }
    
    $stmt->bind_param("i", $balanceId);
    
    if ($stmt->execute()) {
        $monthlyBalances = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return [
            'success' => true,
            'message' => "Monthly balances fetched successfully!",
            'data' => $monthlyBalances,
            'count' => count($monthlyBalances)
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        
        return [
            'success' => false,
            'message' => "Error fetching monthly balances: " . $error,
            'data' => []
        ];
    }
}



/**
 * Get monthly balance trend for a bank account
 * 
 * @param mysqli $conn Database connection
 * @param int $bankAccountId Bank account ID
 * @param int $months Number of months to include (default: 12)
 * 
 * @return array Result array with success status and trend data
 */
function getMonthlyBalanceTrend(mysqli $conn, int $bankAccountId, int $months = 12) {
    // Validate input
    if (empty($bankAccountId) || $bankAccountId <= 0) {
        return [
            'success' => false,
            'message' => "Invalid bank account ID provided.",
            'trend' => []
        ];
    }
    
    // Get monthly balances for trend analysis
    $sql = "SELECT month, closing_balance, balance_type, created_at
            FROM monthly_closing_balances 
            WHERE bank_account_id = ? 
              AND deleted_at IS NULL
            ORDER BY month ASC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error,
            'trend' => []
        ];
    }
    
    $stmt->bind_param("ii", $bankAccountId, $months);
    
    if ($stmt->execute()) {
        $trendData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Process trend data
        $trend = [];
        foreach ($trendData as $data) {
            $trend[] = [
                'month' => $data['month'],
                'effective_balance' => $data['balance_type'] === 'credit' ? 
                                    $data['closing_balance'] : 
                                    -$data['closing_balance'],
                'closing_balance' => $data['closing_balance'],
                'balance_type' => $data['balance_type'],
                'posted_date' => $data['created_at']
            ];
        }
        
        return [
            'success' => true,
            'message' => "Balance trend fetched successfully!",
            'trend' => $trend,
            'count' => count($trend)
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        
        return [
            'success' => false,
            'message' => "Error fetching balance trend: " . $error,
            'trend' => []
        ];
    }
}

/**
 * Fetch reconciliation data with filters
 * 
 * @param mysqli $conn Database connection
 * @param array $filters Optional filters:
 *                    - bank_account_id (int)
 *                    - month (string, format: YYYY-MM)
 *                    - balance_type (string: 'credit' or 'debit')
 *                    - posted_by (int)
 *                    - limit (int, default: 100)
 *                    - offset (int, default: 0)
 * 
 * @return array Result array with success status and data
 */
function fetchReconciliationData(mysqli $conn, array $filters = []) {
    // Build base query
    $sql = "SELECT mcb.*, 
                   ba.account_name, 
                   ba.account_number, 
                   ba.bank_name,
                   u.username as posted_by_name
            FROM monthly_closing_balances mcb
            JOIN bank_accounts ba ON mcb.bank_account_id = ba.id
            JOIN users u ON mcb.posted_by = u.id
            WHERE mcb.deleted_at IS NULL";
    
    $params = [];
    $types = "";
    
    // Apply filters
    if (isset($filters['bank_account_id']) && !empty($filters['bank_account_id'])) {
        $sql .= " AND mcb.bank_account_id = ?";
        $params[] = (int) $filters['bank_account_id'];
        $types .= "i";
    }
    
    if (isset($filters['month']) && !empty($filters['month'])) {
        $sql .= " AND mcb.month = ?";
        $params[] = $conn->real_escape_string($filters['month']);
        $types .= "s";
    }
    
    if (isset($filters['balance_type']) && !empty($filters['balance_type'])) {
        $sql .= " AND mcb.balance_type = ?";
        $params[] = $conn->real_escape_string($filters['balance_type']);
        $types .= "s";
    }
    
    if (isset($filters['posted_by']) && !empty($filters['posted_by'])) {
        $sql .= " AND mcb.posted_by = ?";
        $params[] = (int) $filters['posted_by'];
        $types .= "i";
    }
    
    // Default ordering
    $sql .= " ORDER BY mcb.month DESC, ba.bank_name, ba.account_name";
    
    // Apply limit if specified
    $limit = isset($filters['limit']) ? (int) $filters['limit'] : 100;
    $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error,
            'data' => []
        ];
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return [
            'success' => true,
            'message' => "Reconciliation data fetched successfully!",
            'data' => $data,
            'count' => count($data)
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        
        return [
            'success' => false,
            'message' => "Error fetching data: " . $error,
            'data' => []
        ];
    }
}

/**
 * Count total reconciliation records with filters
 * 
 * @param mysqli $conn Database connection
 * @param array $filters Same filters as fetchReconciliationData
 * 
 * @return array Result array with success status and count
 */
function countReconciliationData(mysqli $conn, array $filters = []) {
    // Build count query
    $sql = "SELECT COUNT(*) as total
            FROM monthly_closing_balances mcb
            JOIN bank_accounts ba ON mcb.bank_account_id = ba.id
            JOIN users u ON mcb.posted_by = u.id
            WHERE mcb.deleted_at IS NULL";
    
    $params = [];
    $types = "";
    
    // Apply same filters as fetchReconciliationData (except limit/offset)
    if (isset($filters['bank_account_id']) && !empty($filters['bank_account_id'])) {
        $sql .= " AND mcb.bank_account_id = ?";
        $params[] = (int) $filters['bank_account_id'];
        $types .= "i";
    }
    
    if (isset($filters['month']) && !empty($filters['month'])) {
        $sql .= " AND mcb.month = ?";
        $params[] = $conn->real_escape_string($filters['month']);
        $types .= "s";
    }
    
    if (isset($filters['balance_type']) && !empty($filters['balance_type'])) {
        $sql .= " AND mcb.balance_type = ?";
        $params[] = $conn->real_escape_string($filters['balance_type']);
        $types .= "s";
    }
    
    if (isset($filters['posted_by']) && !empty($filters['posted_by'])) {
        $sql .= " AND mcb.posted_by = ?";
        $params[] = (int) $filters['posted_by'];
        $types .= "i";
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error,
            'count' => 0
        ];
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return [
            'success' => true,
            'message' => "Count fetched successfully!",
            'count' => (int) $result['total']
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        
        return [
            'success' => false,
            'message' => "Error counting data: " . $error,
            'count' => 0
        ];
    }
}

/**
 * Get reconciliation statistics
 * 
 * @param mysqli $conn Database connection
 * @param array $filters Optional filters
 * 
 * @return array Result array with success status and statistics
 */
function getReconciliationStatistics(mysqli $conn, array $filters = []) {
    // Build statistics query
    $sql = "SELECT 
                COUNT(*) as total_records,
                COUNT(CASE WHEN balance_type = 'credit' THEN 1 END) as credit_count,
                COUNT(CASE WHEN balance_type = 'debit' THEN 1 END) as debit_count,
                SUM(closing_balance) as total_amount,
                AVG(closing_balance) as avg_amount,
                MIN(closing_balance) as min_amount,
                MAX(closing_balance) as max_amount
            FROM monthly_closing_balances mcb
            WHERE mcb.deleted_at IS NULL";
    
    $params = [];
    $types = "";
    
    // Apply filters
    if (isset($filters['bank_account_id']) && !empty($filters['bank_account_id'])) {
        $sql .= " AND mcb.bank_account_id = ?";
        $params[] = (int) $filters['bank_account_id'];
        $types .= "i";
    }
    
    if (isset($filters['month']) && !empty($filters['month'])) {
        $sql .= " AND mcb.month = ?";
        $params[] = $conn->real_escape_string($filters['month']);
        $types .= "s";
    }
    
    if (isset($filters['balance_type']) && !empty($filters['balance_type'])) {
        $sql .= " AND mcb.balance_type = ?";
        $params[] = $conn->real_escape_string($filters['balance_type']);
        $types .= "s";
    }
    
    if (isset($filters['posted_by']) && !empty($filters['posted_by'])) {
        $sql .= " AND mcb.posted_by = ?";
        $params[] = (int) $filters['posted_by'];
        $types .= "i";
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error,
            'statistics' => []
        ];
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $statistics = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return [
            'success' => true,
            'message' => "Statistics fetched successfully!",
            'statistics' => $statistics
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        
        return [
            'success' => false,
            'message' => "Error fetching statistics: " . $error,
            'statistics' => []
        ];
    }
}

/**
 * Record monthly closing balance for reconciliation
 * 
 * @param mysqli $conn Database connection
 * @param array $data Array containing:
 *                   - bank_account_id (int)
 *                   - month (string, format: YYYY-MM)
 *                   - closing_balance (float)
 *                   - balance_type (string: 'credit' or 'debit')
 *                   - posted_by (int)
 * 
 * @return array Result array with success status and message
 */
function recordReconciliation(mysqli $conn, array $data) {
    // Validate required fields
    $required_fields = ['bank_account_id', 'month', 'closing_balance', 'balance_type', 'posted_by'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return [
                'success' => false,
                'message' => "Missing required field: $field"
            ];
        }
    }
    
    // Validate data types
    $bank_account_id = (int) $data['bank_account_id'];
    $month = $conn->real_escape_string($data['month']);
    $closing_balance = (float) $data['closing_balance'];
    $balance_type = $conn->real_escape_string($data['balance_type']);
    $posted_by = (int) $data['posted_by'];
    
    // Validate month format (YYYY-MM)
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        return [
            'success' => false,
            'message' => "Invalid month format. Please use YYYY-MM format."
        ];
    }
    
    // Validate balance type
    if (!in_array($balance_type, ['credit', 'debit'])) {
        return [
            'success' => false,
            'message' => "Invalid balance type. Must be 'credit' or 'debit'."
        ];
    }
    
    // Validate balance amount
    if ($closing_balance < 0) {
        return [
            'success' => false,
            'message' => "Closing balance cannot be negative."
        ];
    }
    
    // Check if balance already exists for this account and month
    $check_sql = "SELECT id FROM monthly_closing_balances 
                  WHERE bank_account_id = ? AND month = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($check_sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error
        ];
    }
    
    $stmt->bind_param("is", $bank_account_id, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        return [
            'success' => false,
            'message' => "Balance already exists for this account and month!"
        ];
    }
    $stmt->close();
    
    // Insert new monthly balance
    $sql = "INSERT INTO monthly_closing_balances 
            (bank_account_id, month, closing_balance, balance_type, posted_by) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => "Database error: " . $conn->error
        ];
    }
    
    $stmt->bind_param("isdss", $bank_account_id, $month, $closing_balance, $balance_type, $posted_by);
    
    if ($stmt->execute()) {
        $inserted_id = $stmt->insert_id;
        $stmt->close();
        
        return [
            'success' => true,
            'message' => "Monthly balance recorded successfully!",
            'inserted_id' => $inserted_id
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        
        return [
            'success' => false,
            'message' => "Error recording balance: " . $error
        ];
    }
}
function updateMonthlyBalance(mysqli $conn, array $data) {
    $id = $data['id'];
    $bank_account_id = $data['bank_account_id'];
    $month = $data['month'];
    $closing_balance = $data['closing_balance'];
    $balance_type = $data['balance_type'];
   
    $sql = "UPDATE monthly_closing_balances SET bank_account_id = ?, month = ?, closing_balance = ?, balance_type = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdsi", $bank_account_id, $month, $closing_balance, $balance_type, $id);
    return ($stmt->execute()) ? ['success' => true, 'message' => 'Monthly balance updated successfully!'] : ['success' => false, 'message' => 'Error updating monthly balance: ' . $stmt->error];
    
}
?>