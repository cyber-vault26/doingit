<?php

// Leave Management Functions
// This file contains functions for managing leave types, policies, and related operations

// Function to get all leave types
function getAllLeaveTypes(mysqli $conn) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT * FROM leave_types WHERE is_active = TRUE ORDER BY sort_order ASC";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $result = $stmt->get_result();
   return ($result->fetch_all(MYSQLI_ASSOC)) ?: false;
}

// Function to get leave type by ID
function getLeaveTypeById(mysqli $conn, int $leaveTypeId) {
    if ($conn === false) {
        return false;
    }
    
    $leaveTypeId = $conn->real_escape_string($leaveTypeId);
    $sql = "SELECT * FROM leave_types WHERE id = '$leaveTypeId' AND is_active = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->fetch_assoc()) ?: false;
    }


// Function to get all leave policies
function getAllLeavePolicies(mysqli $conn) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT lp.*, lt.name as leave_type_name 
             FROM leave_policies lp 
             JOIN leave_types lt ON lp.leave_type_id = lt.id 
             WHERE lp.is_active = TRUE 
             ORDER BY lp.priority DESC, lp.effective_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return ($result->fetch_all(MYSQLI_ASSOC)) ?: false;
}

// Function to get leave policies by user ID
function getLeavePoliciesByUserId(mysqli $conn, int $userId) {
    if ($conn === false) {
        return false;
    }
    
    $userId = $conn->real_escape_string($userId);
    $sql = "SELECT lp.*, lt.name as leave_type_name 
             FROM leave_policies lp 
             JOIN leave_types lt ON lp.leave_type_id = lt.id 
             WHERE (lp.user_id = '$userId' OR lp.user_id IS NULL) 
               AND lp.is_active = TRUE 
               AND (lp.expiry_date IS NULL OR lp.expiry_date > CURDATE())
             ORDER BY lp.priority DESC, lp.effective_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return ($result->fetch_all(MYSQLI_ASSOC)) ?: false;
}

// Function to get leave policy by ID
function getLeavePolicyById(mysqli $conn, int $policyId) {
    if ($conn === false) {
        return false;
    }
    
    $policyId = $conn->real_escape_string($policyId);
    $sql = "SELECT lp.*, lt.name as leave_type_name 
             FROM leave_policies lp 
             JOIN leave_types lt ON lp.leave_type_id = lt.id 
             WHERE lp.id = '$policyId'";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return ($result->fetch_assoc()) ?: false;
    }


// Function to get leave balance for user and leave type
function getLeaveBalance(mysqli $conn, int $userId, int $leaveTypeId) {
    if ($conn === false) {
        return false;
    }
    
    $userId = $conn->real_escape_string($userId);
    $leaveTypeId = $conn->real_escape_string($leaveTypeId);
    
    $sql = "SELECT * FROM leave_balances 
             WHERE user_id = '$userId' 
               AND leave_type_id = '$leaveTypeId' 
               AND period_start <= CURDATE() 
               AND period_end >= CURDATE()";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return ($result->fetch_assoc()) ?: false;
    }

// Function to get leave balance for user and leave type (NEW LOGIC - MAX ENTITLEMENT)
function getLeaveBalanceNew(mysqli $conn, int $userId, int $leaveTypeId) {
    if ($conn === false) {
        return false;
    }
    
    // Get leave type to get max days per year
    $sql = "SELECT max_days_per_year FROM leave_types WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leaveTypeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $leaveType = $result->fetch_assoc();
    $stmt->close();
    
    if (!$leaveType) {
        return false;
    }
    
    $maxDaysPerYear = $leaveType['max_days_per_year'];
    
    // Get user's approved leave for this type in current year
    $currentYear = date('Y');
    $sql = "SELECT COALESCE(SUM(total_days), 0) as used_days 
            FROM leave_requests 
            WHERE user_id = ? AND leave_type_id = ? AND status = 'approved' 
            AND YEAR(start_date) = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $userId, $leaveTypeId, $currentYear);
    $stmt->execute();
    $result = $stmt->get_result();
    $usedData = $result->fetch_assoc();
    $stmt->close();
    
    $usedDays = $usedData['used_days'];
    $availableBalance = $maxDaysPerYear - $usedDays;
    
    return [
        'leave_type_id' => $leaveTypeId,
        'available_balance' => max(0, $availableBalance), // Never show negative
        'accrued_balance' => $maxDaysPerYear, // Show full entitlement as accrued
        'used_days' => $usedDays,
        'pending_days' => 0, // We don't track pending anymore
        'max_days_per_year' => $maxDaysPerYear
    ];
}


// Function to get all leave balances for user (NEW LOGIC - MAX ENTITLEMENT)
function getAllLeaveBalancesByUser(mysqli $conn, int $userId) {
    if ($conn === false) {
        return false;
    }
    
    // Get all leave types
    $sql = "SELECT id, name, color_code, max_days_per_year FROM leave_types WHERE is_active = 1 ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $leaveTypes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $balances = [];
    $currentYear = date('Y');
    
    foreach ($leaveTypes as $leaveType) {
        // Get user's approved leave for this type in current year
        $sql = "SELECT COALESCE(SUM(total_days), 0) as used_days 
                FROM leave_requests 
                WHERE user_id = ? AND leave_type_id = ? AND status = 'approved' 
                AND YEAR(start_date) = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $userId, $leaveType['id'], $currentYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $usedData = $result->fetch_assoc();
        $stmt->close();
        
        $usedDays = $usedData['used_days'];
        $maxDaysPerYear = $leaveType['max_days_per_year'];
        $availableBalance = $maxDaysPerYear - $usedDays;
        
        $balances[] = [
            'leave_type_id' => $leaveType['id'],
            'leave_type_name' => $leaveType['name'],
            'color_code' => $leaveType['color_code'],
            'available_balance' => max(0, $availableBalance), // Never show negative
            'accrued_balance' => $maxDaysPerYear, // Show full entitlement as accrued
            'used_days' => $usedDays,
            'pending_days' => 0, // We don't track pending anymore
            'max_days_per_year' => $maxDaysPerYear
        ];
    }
    
    return $balances;
}

// Function to create leave request
function createLeaveRequest(mysqli $conn, array $requestData) {
    if ($conn === false) {
        return false;
    }
   
    // Sanitize inputs
    $userId = $conn->real_escape_string($requestData['user_id']);
    $leaveTypeId = $conn->real_escape_string($requestData['leave_type_id']);
    $startDate = $conn->real_escape_string($requestData['start_date']);
    $endDate = $conn->real_escape_string($requestData['end_date']);
    $startTime = isset($requestData['start_time']) ? "'".$conn->real_escape_string($requestData['start_time'])."'" : 'NULL';
    $endTime = isset($requestData['end_time']) ? "'".$conn->real_escape_string($requestData['end_time'])."'" : 'NULL';
    $totalDays = $conn->real_escape_string($requestData['total_days']);
    $reason = isset($requestData['reason']) ? "'".$conn->real_escape_string($requestData['reason'])."'" : 'NULL';
    $priorityLevel = $conn->real_escape_string($requestData['priority_level'] ?? 'normal');
    $attachmentPath = isset($requestData['attachment_path']) ? "'".$conn->real_escape_string($requestData['attachment_path'])."'" : 'NULL';
    $attachmentName = isset($requestData['attachment_name']) ? "'".$conn->real_escape_string($requestData['attachment_name'])."'" : 'NULL';
    $attachmentSize = isset($requestData['attachment_size']) ? $conn->real_escape_string($requestData['attachment_size']) : 'NULL';
    $emergencyContact = isset($requestData['emergency_contact_phone']) ? "'".$conn->real_escape_string($requestData['emergency_contact_phone'])."'" : 'NULL';
    $handoverNotes = isset($requestData['work_handover_notes']) ? "'".$conn->real_escape_string($requestData['work_handover_notes'])."'" : 'NULL';
    $backupPerson = isset($requestData['backup_person_id']) ? $conn->real_escape_string($requestData['backup_person_id']) : 'NULL';
     $leave_request_number = "LV/" . date("Y/") . $userId  ."/" . $totalDays . date("ms");
    $sql = "INSERT INTO leave_requests (
                user_id, leave_type_id, start_date, end_date, start_time, end_time,
                total_days, reason, priority_level, attachment_path, attachment_name, 
                attachment_size, emergency_contact_phone, work_handover_notes, backup_person_id, request_number
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssissssssssss", $userId, $leaveTypeId, $startDate, $endDate, $startTime, $endTime, $totalDays, $reason, $priorityLevel, $attachmentPath, $attachmentName, $attachmentSize, $emergencyContact, $handoverNotes, $backupPerson,$leave_request_number );
   
    
   return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
}

// Function to get leave requests by user ID
function getLeaveRequestsByUserId(mysqli $conn, int $userId, $limit = 50, $offset = 0) {
    if ($conn === false) {
        return false;
    }
    
    $userId = $conn->real_escape_string($userId);
    $limit = $conn->real_escape_string($limit);
    $offset = $conn->real_escape_string($offset);
    
    $sql = "SELECT lr.*, lt.name as leave_type_name, lt.color_code,
                    u.first_name, u.last_name
             FROM leave_requests lr 
             JOIN leave_types lt ON lr.leave_type_id = lt.id 
             JOIN users u ON lr.user_id = u.id 
             WHERE lr.user_id = ?
             ORDER BY lr.created_at DESC 
             LIMIT $limit OFFSET $offset";
    
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $userId);
   return ($stmt->execute()) ? $stmt->fetch_all(MYSQLI_ASSOC) : $stmt->error;
   
}

// Function to get pending approvals for manager
function getPendingApprovals(mysqli $conn, int $managerId) {
    if ($conn === false) {
        return false;
    }
    
    $managerId = $conn->real_escape_string($managerId);
    $sql = "SELECT lr.*, lt.name as leave_type_name, lt.color_code,
                    u.first_name, u.last_name, u.employee_id
             FROM leave_requests lr 
             JOIN leave_types lt ON lr.leave_type_id = lt.id 
             JOIN users u ON lr.user_id = u.id 
             WHERE lr.status = 'pending' 
               AND lr.approver_id = ?
             ORDER BY lr.submitted_at ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $managerId);
    return ($stmt->execute()) ? $stmt->fetch_all(MYSQLI_ASSOC) : $stmt->error;
}
// Function to approve leave request
function approveLeaveRequest(mysqli $conn, int $requestId, int $approverId, string $status, string $comments = '') {
    if ($conn === false) {
        return false;
    }
    
    $requestId = $conn->real_escape_string($requestId);
    $approverId = $conn->real_escape_string($approverId);
    $comments = $conn->real_escape_string($comments);
    
    $conn->begin_transaction();
    
    try {
        // Update leave request status
        $sql = "UPDATE leave_requests 
                 SET status = ?, 
                     approved_at = NOW(),
                     approver_id = ?
                 WHERE id = ?  ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $approverId, $requestId);
        $stmt->execute() ? error_log("Error updating leave request: " . $conn->error) : true;
       
        
        // Update leave balance (call stored procedure if available, otherwise manual update)
        $sql = "SELECT user_id, leave_type_id, total_days 
                 FROM leave_requests 
                 WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $requestId);
        $result = $stmt->execute() ? $stmt->get_result() : null;
        
        $request = $result->fetch_assoc();
        $result->free();
        
        // Update used days and reduce pending days
        $updateSql = "UPDATE leave_balances 
                      SET used_days = used_days + {$request['total_days']},
                          pending_days = pending_days - {$request['total_days']},
                          last_updated_at = NOW()
                      WHERE user_id = ? 
                        AND leave_type_id = ?
                        AND period_start <= CURDATE() 
                        AND period_end >= CURDATE()";
        
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $request['user_id'], $request['leave_type_id']);
        $stmt->execute() ? error_log("Error updating leave balance: " . $conn->error) : true;
        
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

// Function to reject leave request
function rejectLeaveRequest(mysqli $conn, int $requestId, int $approverId, string $status, string $rejectionReason) {
    if ($conn === false) {
        return false;
    }
    
    $requestId = $conn->real_escape_string($requestId);
    $approverId = $conn->real_escape_string($approverId);
    $rejectionReason = $conn->real_escape_string($rejectionReason);
    
    $sql = "UPDATE leave_requests 
             SET status = '$status', 
                 rejected_at = NOW(),
                 approver_id = '$approverId',
                 rejection_reason = '$rejectionReason'
             WHERE id = '$requestId' AND status = 'pending'";
    
    if ($conn->query($sql)) {
        // Update pending days back to available
        $updateSql = "SELECT user_id, leave_type_id, total_days 
                     FROM leave_requests 
                     WHERE id = '$requestId'";
        
        $result = $conn->query($updateSql);
        if ($result) {
            $request = $result->fetch_assoc();
            $result->free();
            
            $balanceSql = "UPDATE leave_balances 
                          SET pending_days = pending_days - {$request['total_days']},
                              last_updated_at = NOW()
                          WHERE user_id = {$request['user_id']} 
                            AND leave_type_id = {$request['leave_type_id']}
                            AND period_start <= CURDATE() 
                            AND period_end >= CURDATE()";
            $conn->query($balanceSql);
        }
        
        return true;
    } else {
        return $conn->error;
    }
}
// Function to cancel leave request
function cancelLeaveRequest(mysqli $conn, int $requestId, int $userId) {
    if ($conn === false) {
        return false;
    }
    
    $requestId = $conn->real_escape_string($requestId);
    $userId = $conn->real_escape_string($userId);
    
    $sql = "UPDATE leave_requests 
             SET status = 'cancelled', 
                 cancelled_at = NOW()
             WHERE id = '$requestId' 
               AND user_id = '$userId' 
               AND status IN ('pending', 'approved')";
    
    if ($conn->query($sql)) {
        // If was pending, reduce pending days
        // If was approved, restore used days
        $updateSql = "SELECT user_id, leave_type_id, total_days, status
                     FROM leave_requests 
                     WHERE id = '$requestId'";
        
        $result = $conn->query($updateSql);
        if ($result) {
            $request = $result->fetch_assoc();
            $result->free();
            
            if ($request['status'] === 'pending') {
                $balanceSql = "UPDATE leave_balances 
                              SET pending_days = pending_days - {$request['total_days']},
                                  last_updated_at = NOW()
                              WHERE user_id = {$request['user_id']} 
                                AND leave_type_id = {$request['leave_type_id']}
                                AND period_start <= CURDATE() 
                                AND period_end >= CURDATE()";
            } elseif ($request['status'] === 'approved') {
                $balanceSql = "UPDATE leave_balances 
                              SET used_days = used_days - {$request['total_days']},
                                  last_updated_at = NOW()
                              WHERE user_id = {$request['user_id']} 
                                AND leave_type_id = {$request['leave_type_id']}
                                AND period_start <= CURDATE() 
                                AND period_end >= CURDATE()";
            }
            
            $conn->query($balanceSql);
        }
        
        return true;
    } else {
        return $conn->error;
    }
}

// Function to validate leave request
function validateLeaveRequest(mysqli $conn, int $userId, int $leaveTypeId, string $startDate, string $endDate, float $totalDays) {
    if ($conn === false) {
        return ['valid' => false, 'error' => 'Database connection failed'];
    }
    
    $userId = $conn->real_escape_string($userId);
    $leaveTypeId = $conn->real_escape_string($leaveTypeId);
    $startDate = $conn->real_escape_string($startDate);
    $endDate = $conn->real_escape_string($endDate);
    $totalDays = $conn->real_escape_string($totalDays);
    
    // Check leave balance
    $balanceSql = "SELECT available_balance 
                   FROM leave_balances 
                   WHERE user_id = '$userId' 
                     AND leave_type_id = '$leaveTypeId' 
                     AND period_start <= CURDATE() 
                     AND period_end >= CURDATE()";
    
    $result = $conn->query($balanceSql);
    if (!$result) {
        return ['valid' => false, 'error' => $conn->error];
    }
    
    $balance = $result->fetch_assoc();
    $result->free();
    
    if ($balance['available_balance'] < $totalDays) {
        return ['valid' => false, 'error' => 'Insufficient leave balance. Available: ' . $balance['available_balance']];
    }
    
    // Check for overlapping requests
    $overlapSql = "SELECT COUNT(*) as count 
                   FROM leave_requests 
                   WHERE user_id = '$userId' 
                     AND status IN ('pending', 'approved')
                     AND (
                         (start_date <= '$startDate' AND end_date >= '$startDate') OR
                         (start_date <= '$endDate' AND end_date >= '$endDate') OR
                         (start_date >= '$startDate' AND end_date <= '$endDate')
                     )";
    
    $result = $conn->query($overlapSql);
    if (!$result) {
        return ['valid' => false, 'error' => $conn->error];
    }
    
    $overlap = $result->fetch_assoc();
    $result->free();
    
    if ($overlap['count'] > 0) {
        return ['valid' => false, 'error' => 'Overlapping leave request exists'];
    }
    
    // Check minimum notice period
    $noticeSql = "SELECT min_advance_notice_days 
                 FROM leave_types 
                 WHERE id = '$leaveTypeId'";
    
    $result = $conn->query($noticeSql);
    if (!$result) {
        return ['valid' => false, 'error' => $conn->error];
    }
    
    $notice = $result->fetch_assoc();
    $result->free();
    
    $daysDiff = (strtotime($startDate) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
    if ($daysDiff < $notice['min_advance_notice_days']) {
        return ['valid' => false, 'error' => 'Minimum notice period of ' . $notice['min_advance_notice_days'] . ' days required'];
    }
    
    return ['valid' => true, 'error' => null];
}

// Function to get leave statistics
function getLeaveStatistics(mysqli $conn, $userId = null, $leaveTypeId = null) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT lt.name as leave_type_name, 
                    COUNT(lr.id) as total_requests,
                    SUM(CASE WHEN lr.status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
                    SUM(CASE WHEN lr.status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests,
                    SUM(CASE WHEN lr.status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                    SUM(CASE WHEN lr.status = 'approved' THEN lr.total_days ELSE 0 END) as total_days_taken
             FROM leave_types lt 
             LEFT JOIN leave_requests lr ON lt.id = lr.leave_type_id";
    
    $whereConditions = [];
    
    if ($userId !== null) {
        $userId = $conn->real_escape_string($userId);
        $whereConditions[] = "lr.user_id = '$userId'";
    }
    
    if ($leaveTypeId !== null) {
        $leaveTypeId = $conn->real_escape_string($leaveTypeId);
        $whereConditions[] = "lr.leave_type_id = '$leaveTypeId'";
    }
    
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    $sql .= " GROUP BY lt.id, lt.name 
               ORDER BY lt.sort_order ASC";
    
    if ($result = $conn->query($sql)) {
        $statistics = [];
        while ($row = $result->fetch_assoc()) {
            $statistics[] = $row;
        }
        $result->free();
        return $statistics;
    } else {
        return $conn->error;
    }
}

// Function to create leave policy
function createLeavePolicy(mysqli $conn, array $policyData) {
    if ($conn === false) {
        return false;
    }
    
    // Prepare the SQL statement
    $sql = "INSERT INTO leave_policies (
                leave_type_id, user_id, policy_name, description, 
                accrual_rate, accrual_period, max_carry_over_days, 
                probation_period_days, tenure_based_accrual, accrual_tiers,
                blackout_periods, minimum_staffing_required, approval_levels,
                auto_approve_conditions, documentation_requirements,
                is_active, priority, effective_date, expiry_date, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return $conn->error;
    }
    
    // Bind parameters
    $stmt->bind_param("iisssdiisssiissiisssi", 
        $policyData['leave_type_id'],
        $policyData['user_id'],
        $policyData['policy_name'],
        $policyData['description'],
        $policyData['accrual_rate'],
        $policyData['accrual_period'],
        $policyData['max_carry_over_days'],
        $policyData['probation_period_days'],
        $policyData['tenure_based_accrual'],
        $policyData['accrual_tiers'],
        $policyData['blackout_periods'],
        $policyData['minimum_staffing_required'],
        $policyData['approval_levels'],
        $policyData['auto_approve_conditions'],
        $policyData['documentation_requirements'],
        $policyData['is_active'],
        $policyData['priority'],
        $policyData['effective_date'],
        $policyData['expiry_date'],
        $policyData['created_by']
    );
    
    // Execute the statement
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

// Function to delete leave policy
function deleteLeavePolicy(mysqli $conn, int $policyId) {
    if ($conn === false) {
        return false;
    }
    
    $policyId = $conn->real_escape_string($policyId);
    
    // Soft delete by setting is_active to false
    $sql = "UPDATE leave_policies SET is_active = 0, updated_by = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return $conn->error;
    }
    
    $stmt->bind_param("ii", $_SESSION['userid'], $policyId);
    
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

// Function to toggle policy status
function togglePolicyStatus(mysqli $conn, int $policyId) {
    if ($conn === false) {
        return false;
    }
    
    $policyId = $conn->real_escape_string($policyId);
    
    // Get current status
    $sql = "SELECT is_active FROM leave_policies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $policyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $policy = $result->fetch_assoc();
    $stmt->close();
    
    if (!$policy) {
        return "Policy not found";
    }
    
    // Toggle status
    $newStatus = $policy['is_active'] ? 0 : 1;
    $sql = "UPDATE leave_policies SET is_active = ?, updated_by = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return $conn->error;
    }
    
    $stmt->bind_param("iii", $newStatus, $_SESSION['userid'], $policyId);
    
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}
function getLeaveRequest(mysqli $conn){
    $sql = "SELECT leave_requests.*, users.names as user, leave_types.name as leave_type_name FROM
        leave_requests
        LEFT JOIN users ON users.id = leave_requests.user_id
        LEFT JOIN leave_types on leave_types.id = leave_requests.leave_type_id
        WHERE users.deleted_at IS NULL ;";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
    
}

function selectLeaveRequestsByUserId(mysqli $conn, int $userId){
    $sql = "SELECT leave_requests.*, users.names as user, leave_types.name as leave_type_name FROM
        leave_requests
        LEFT JOIN users ON users.id = leave_requests.user_id
        LEFT JOIN leave_types on leave_types.id = leave_requests.leave_type_id
        WHERE users.deleted_at IS NULL AND users.id = ? ;";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
   return ($stmt->execute() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : false);
    
}
function getLeaveRequestByDepartment(mysqli $conn, int $departmentId){
    $sql = "SELECT leave_requests.*, users.names as user, leave_types.name as leave_type_name FROM
        leave_requests
        LEFT JOIN users ON users.id = leave_requests.user_id
        LEFT JOIN leave_types on leave_types.id = leave_requests.leave_type_id
        WHERE users.deleted_at IS NULL AND users.department_id = ? ;";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $departmentId);
   return ($stmt->execute() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : false);
    
}
