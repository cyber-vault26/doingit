<?php

// ZKTeco Integration Functions
// These functions handle ZKTeco device data and save to database

require_once __DIR__ . '/attendance_functions.php';

// Process ZKTeco device data and save to database
function processZKTecoData($conn, $deviceData) {
    $timestamp = date('Y-m-d H:i:s');
    $successCount = 0;
    $errorCount = 0;
    $results = [];
    
    // Log raw data for debugging
    error_log("ZKTeco Data Received: " . json_encode($deviceData));
    
    // Process each record from the device
    if (isset($deviceData['records']) && is_array($deviceData['records'])) {
        foreach ($deviceData['records'] as $record) {
            $result = processSingleZKTecoRecord($conn, $record, $timestamp);
            $results[] = $result;
            
            if ($result['status'] === 'success') {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
    }
    
    return [
        'status' => $errorCount === 0 ? 'success' : 'partial',
        'message' => "Processed $successCount records successfully, $errorCount errors",
        'results' => $results,
        'summary' => [
            'total' => count($results),
            'success' => $successCount,
            'errors' => $errorCount
        ]
    ];
}

// Process a single ZKTeco attendance record
function processSingleZKTecoRecord($conn, $record, $timestamp) {
    try {
        // Extract employee PIN from record
        $employeePin = extractEmployeePin($record);
        
        if (empty($employeePin)) {
            return [
                'status' => 'error',
                'message' => 'No employee PIN found in record',
                'record' => $record
            ];
        }
        
        // Find employee by device PIN
        $employee = findEmployeeByDevicePin($conn, $employeePin);
        
        if (!$employee) {
            // Log unknown employee for admin review
            logUnknownEmployee($conn, $employeePin, $record);
            
            return [
                'status' => 'error',
                'message' => "Unknown employee PIN: $employeePin",
                'employee_pin' => $employeePin,
                'record' => $record
            ];
        }
        
        // Extract punch time and type
        $punchTime = extractPunchTime($record);
        $punchType = extractPunchType($record);
        
        if (empty($punchTime)) {
            return [
                'status' => 'error',
                'message' => 'No punch time found in record',
                'employee_id' => $employee['id'],
                'employee_pin' => $employeePin,
                'record' => $record
            ];
        }
        
        // Record attendance in database
        $attendanceResult = recordZKTecoAttendance($conn, $employee, $punchTime, $punchType, $record);
        
        return $attendanceResult;
        
    } catch (Exception $e) {
        error_log("Error processing ZKTeco record: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Processing error: ' . $e->getMessage(),
            'record' => $record
        ];
    }
}

// Extract employee PIN from various ZKTeco record formats
function extractEmployeePin($record) {
    // Format 1: Direct employee_id field
    if (isset($record['employee_id']) && is_numeric($record['employee_id'])) {
        return $record['employee_id'];
    }
    
    // Format 2: From raw line data (tab-separated)
    if (isset($record['raw_line'])) {
        $fields = explode("\t", $record['raw_line']);
        if (isset($fields[0]) && is_numeric($fields[0])) {
            return $fields[0];
        }
    }
    
    // Format 3: From user info
    if (isset($record['user_info']['PIN'])) {
        return $record['user_info']['PIN'];
    }
    
    return null;
}

// Extract punch time from ZKTeco record
function extractPunchTime($record) {
    // Format 1: Direct punch_time field
    if (isset($record['punch_time']) && !empty($record['punch_time'])) {
        return $record['punch_time'];
    }
    
    // Format 2: From raw line data (tab-separated)
    if (isset($record['raw_line'])) {
        $fields = explode("\t", $record['raw_line']);
        if (isset($fields[1]) && !empty($fields[1])) {
            return $fields[1];
        }
    }
    
    return null;
}

// Extract punch type from ZKTeco record
function extractPunchType($record) {
    // Format 1: Direct punch_type field
    if (isset($record['punch_type'])) {
        return $record['punch_type'] == '1' ? 'checkin' : 'checkout';
    }
    
    // Format 2: From raw line data (tab-separated)
    if (isset($record['raw_line'])) {
        $fields = explode("\t", $record['raw_line']);
        if (isset($fields[3])) {
            return $fields[3] == '1' ? 'checkin' : 'checkout';
        }
    }
    
    // Default: try to determine from time (morning = check-in, afternoon = check-out)
    $punchTime = extractPunchTime($record);
    if ($punchTime) {
        $hour = date('H', strtotime($punchTime));
        return $hour < 12 ? 'checkin' : 'checkout';
    }
    
    return 'checkin'; // Default fallback
}

// Find employee by device PIN
function findEmployeeByDevicePin($conn, $devicePin) {
    $sql = "SELECT e.*, u.name, u.email 
            FROM employees e 
            JOIN users u ON e.user_id = u.id 
            WHERE e.device_pin = ? AND (u.status = 'active' OR u.status IS NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $devicePin);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Record attendance from ZKTeco device
function recordZKTecoAttendance($conn, $employee, $punchTime, $punchType, $rawRecord) {
    try {
        $date = date('Y-m-d', strtotime($punchTime));
        $employeeId = $employee['id'];
        $deviceSerial = $rawRecord['device_serial'] ?? 'Unknown';
        
        // Check if attendance already exists for this date and time
        $checkSql = "SELECT * FROM attendance 
                     WHERE employee_id = ? AND date = ? AND 
                           (check_in = ? OR check_out = ?)";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('isss', $employeeId, $date, $punchTime, $punchTime);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            return [
                'status' => 'error',
                'message' => 'Attendance already recorded for this time',
                'employee_id' => $employeeId,
                'punch_time' => $punchTime
            ];
        }
        
        if ($punchType === 'checkin') {
            // Check if already checked in today
            $existingSql = "SELECT * FROM attendance WHERE employee_id = ? AND date = ?";
            $existingStmt = $conn->prepare($existingSql);
            $existingStmt->bind_param('is', $employeeId, $date);
            $existingStmt->execute();
            $existing = $existingStmt->get_result()->fetch_assoc();
            
            if ($existing && $existing['check_in']) {
                return [
                    'status' => 'error',
                    'message' => 'Already checked in today',
                    'employee_id' => $employeeId,
                    'date' => $date
                ];
            }
            
            // Insert check-in
            $sql = "INSERT INTO attendance (employee_id, date, check_in, method, ip_address) 
                    VALUES (?, ?, ?, 'biometric', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isss', $employeeId, $date, $punchTime, $deviceSerial);
            
        } else { // checkout
            // Find today's check-in record
            $existingSql = "SELECT * FROM attendance WHERE employee_id = ? AND date = ? AND check_in IS NOT NULL";
            $existingStmt = $conn->prepare($existingSql);
            $existingStmt->bind_param('is', $employeeId, $date);
            $existingStmt->execute();
            $existing = $existingStmt->get_result()->fetch_assoc();
            
            if (!$existing) {
                return [
                    'status' => 'error',
                    'message' => 'No check-in record found for today',
                    'employee_id' => $employeeId,
                    'date' => $date
                ];
            }
            
            if ($existing['check_out']) {
                return [
                    'status' => 'error',
                    'message' => 'Already checked out today',
                    'employee_id' => $employeeId,
                    'date' => $date
                ];
            }
            
            // Calculate work hours
            $checkIn = new DateTime($existing['check_in']);
            $checkOut = new DateTime($punchTime);
            $workHours = $checkOut->diff($checkIn)->h + ($checkOut->diff($checkIn)->i / 60);
            
            // Update check-out
            $sql = "UPDATE attendance SET check_out = ?, work_hours = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sdi', $punchTime, $workHours, $existing['id']);
        }
        
        if ($stmt->execute()) {
            return [
                'status' => 'success',
                'message' => ucfirst($punchType) . ' recorded successfully',
                'employee_id' => $employeeId,
                'employee_name' => $employee['name'],
                'punch_time' => $punchTime,
                'punch_type' => $punchType,
                'work_hours' => $workHours ?? null
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to record attendance',
                'employee_id' => $employeeId
            ];
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage(),
            'employee_id' => $employee['id'] ?? null
        ];
    }
}

// Log unknown employees for admin review
function logUnknownEmployee($conn, $employeePin, $record) {
    $sql = "INSERT INTO zkteco_data_log (device_serial, employee_pin, punch_time, raw_data, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $deviceSerial = $record['device_serial'] ?? 'Unknown';
    $punchTime = extractPunchTime($record) ?? date('Y-m-d H:i:s');
    $rawData = json_encode($record);
    $stmt->bind_param('ssss', $deviceSerial, $employeePin, $punchTime, $rawData);
    $stmt->execute();
    
    error_log("Unknown ZKTeco employee PIN: $employeePin, Record: " . json_encode($record));
}

// Get unknown employees for admin review
function getUnknownEmployees($conn, $limit = 50) {
    $sql = "SELECT * FROM zkteco_data_log 
            WHERE status = 'pending' AND employee_id IS NULL
            ORDER BY processed_at DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Map unknown employee to existing user
function mapUnknownEmployee($conn, $logId, $userId) {
    try {
        // Get the log entry
        $sql = "SELECT * FROM zkteco_data_log WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $logId);
        $stmt->execute();
        $logEntry = $stmt->get_result()->fetch_assoc();
        
        if (!$logEntry) {
            return ['status' => 'error', 'message' => 'Log entry not found'];
        }
        
        // Get employee record for this user
        $sql = "SELECT * FROM employees WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $employee = $stmt->get_result()->fetch_assoc();
        
        if (!$employee) {
            return ['status' => 'error', 'message' => 'Employee not found for this user'];
        }
        
        // Update employee with device PIN
        $updateSql = "UPDATE employees SET device_pin = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('si', $logEntry['employee_pin'], $employee['id']);
        $updateStmt->execute();
        
        // Update log entry
        $updateLogSql = "UPDATE zkteco_data_log SET employee_id = ?, status = 'processed' WHERE id = ?";
        $updateLogStmt = $conn->prepare($updateLogSql);
        $updateLogStmt->bind_param('ii', $employee['id'], $logId);
        $updateLogStmt->execute();
        
        // Process the original record
        $record = json_decode($logEntry['raw_data'], true);
        $result = processSingleZKTecoRecord($conn, $record, date('Y-m-d H:i:s'));
        
        return [
            'status' => 'success',
            'message' => 'Employee mapped and attendance processed',
            'employee_id' => $employee['id'],
            'device_pin' => $logEntry['employee_pin'],
            'attendance_result' => $result
        ];
        
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Error mapping employee: ' . $e->getMessage()];
    }
}

?>
