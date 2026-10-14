<?php

// Set timezone to match your local timezone
//date_default_timezone_set('Africa/Nairobi'); // Change this to your timezone

// Attendance Functions
function recordAttendance($conn, $userId, $checkTime,$type='check_in') {
    error_log("UserId from device & time: " . $userId . " " . $checkTime );
    $employee = getEmployeeByUserId($conn, $userId);
    
    if (!$employee) {
        return ['status' => 'error', 'message' => 'Employee not found'];
    }
    
    $dt = new DateTime($checkTime);
    $today = $dt->format('Y-m-d');
    
    // Check if already checked in today
    $existing = getTodayAttendance($conn, $employee['id'], $today);
    
    if ($type === 'check_in') {
        if ($existing && !$existing['check_out']) {
            return ['status' => 'error', 'message' => 'Already checked in today'];
        }
        
        // Record check-in
        $sql = "INSERT INTO attendance (employee_id, date, check_in, method, ip_address) 
                    VALUES (?, ?, ?, 'manual', ?)";
        $stmt = $conn->prepare($sql);
        // $checkInTime = date('Y-m-d H:i:s');
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param('isss', $employee['id'], $today, $checkTime, $ipAddress);
        
        if ($stmt->execute()) {
            return [
                'status' => 'success', 
                'message' => 'Check-in successful', 
                'time' => date('H:i:s'),
                'datetime' => date('Y-m-d H:i:s'),
                'timezone' => date_default_timezone_get()
            ];
        }else{
            error_log("error: " . $stmt->error );
        }
    } else {
        // Check-out
        if (!$existing || $existing['check_out']) {
            return ['status' => 'error', 'message' => 'No active check-in found'];
        }
        
        // Calculate work hours
        $checkIn = new DateTime($existing['check_in']);
        $checkOut = new DateTime();
        $interval = $checkIn->diff($checkOut);
        $workHours = $interval->h + ($interval->i / 60) + ($interval->days * 24);
        
        // Update check-out
        $sql = "UPDATE attendance SET check_out = ?, work_hours = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
      //  $checkOutTime = date('Y-m-d H:i:s');
        $stmt->bind_param('sdi', $checkTime, $workHours, $existing['id']);
        
        if ($stmt->execute()) {
            return [
                'status' => 'success', 
                'message' => 'Check-out successful', 
                'work_hours' => $workHours,
                'check_out_time' => date('H:i:s')
            ];
        }
    }
    
    return ['status' => 'error', 'message' => 'Database error'];
}

function getTodayAttendance($conn, $employeeId, $date) {
    $sql = "SELECT * FROM attendance WHERE employee_id = ? AND date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $employeeId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getEmployeeByUserId(mysqli $conn, int  $userId) {
    $sql = "SELECT * FROM employees WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getAttendanceReport($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT * FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ?
            ORDER BY date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllEmployeesAttendance($conn, $date) {
    $sql = "SELECT 
                e.employee_code,
                u.name,
                a.check_in,
                a.check_out,
                a.work_hours,
                a.status
            FROM employees e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
            ORDER BY e.employee_code";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAttendanceSummary($conn, $employeeId, $month, $year) {
    $sql = "SELECT 
                COUNT(*) as days_present,
                SUM(work_hours) as total_hours,
                COUNT(CASE WHEN status = 'late' THEN 1 END) as days_late
            FROM attendance 
            WHERE employee_id = ? AND MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $employeeId, $month, $year);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// =============================================
// COMPREHENSIVE ATTENDANCE REPORT FUNCTIONS
// =============================================

// Personal Attendance Summary
function getPersonalAttendanceSummary($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as days_present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as days_absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as days_late,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_days,
                SUM(work_hours) as total_hours_worked,
                ROUND(AVG(work_hours), 2) as avg_daily_hours
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $totalDays = $result['total_days'];
        $result['attendance_percentage'] = $totalDays > 0 ? round(($result['days_present'] / $totalDays) * 100, 2) : 0;
        $result['absenteeism_rate'] = $totalDays > 0 ? round(($result['days_absent'] / $totalDays) * 100, 2) : 0;
        $result['punctuality_rate'] = $totalDays > 0 ? round((($totalDays - $result['days_late']) / $totalDays) * 100, 2) : 0;
    }
    
    return $result;
}

// Work Pattern Analysis
function getWorkPatternAnalysis($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                DAYNAME(date) as day_of_week,
                AVG(work_hours) as avg_hours,
                MIN(work_hours) as min_hours,
                MAX(work_hours) as max_hours,
                COUNT(*) as total_days
            FROM attendance 
            WHERE employee_id = ? AND status = 'present' AND date BETWEEN ? AND ?
            GROUP BY DAYNAME(date)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Sort the result in PHP to avoid SQL GROUP BY issues
    $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    usort($result, function($a, $b) use ($dayOrder) {
        $aIndex = array_search($a['day_of_week'], $dayOrder);
        $bIndex = array_search($b['day_of_week'], $dayOrder);
        return $aIndex - $bIndex;
    });
    
    return $result;
}

// Monthly Attendance Trends
function getMonthlyAttendanceTrends($conn, $employeeId, $year) {
    $sql = "SELECT 
                MONTH(date) as month,
                YEAR(date) as year,
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as days_present,
                AVG(work_hours) as avg_monthly_hours,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as days_late
            FROM attendance 
            WHERE employee_id = ? AND YEAR(date) = ?
            GROUP BY YEAR(date), MONTH(date)
            ORDER BY year, month";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $employeeId, $year);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Late Arrival Patterns
function getLateArrivalPatterns($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                COUNT(*) as total_late_days,
                AVG(TIMESTAMPDIFF(MINUTE, '08:30:00', TIME(check_in))) as avg_late_minutes,
                MAX(TIMESTAMPDIFF(MINUTE, '08:30:00', TIME(check_in))) as max_late_minutes,
                DAYNAME(date) as most_frequent_late_day
            FROM attendance 
            WHERE employee_id = ? AND status = 'late' AND date BETWEEN ? AND ?
            GROUP BY employee_id, DAYNAME(date)
            ORDER BY COUNT(*) DESC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Check-in Time Distribution
function getCheckInTimeDistribution($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                CASE 
                    WHEN TIME(check_in) < '08:30:00' THEN 'Early'
                    WHEN TIME(check_in) BETWEEN '08:30:00' AND '09:00:00' THEN 'On Time'
                    WHEN TIME(check_in) BETWEEN '09:00:00' AND '09:30:00' THEN 'Late'
                    ELSE 'Very Late'
                END as punctuality_category,
                COUNT(*) as frequency,
                AVG(work_hours) as avg_hours_by_category
            FROM attendance 
            WHERE employee_id = ? AND check_in IS NOT NULL AND date BETWEEN ? AND ?
            GROUP BY punctuality_category
            ORDER BY frequency DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Department Comparison
function getDepartmentComparison($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                a.employee_id,
                a.attendance_percentage,
                d.avg_department_attendance,
                a.attendance_percentage - d.avg_department_attendance as performance_difference
            FROM (
                SELECT 
                    employee_id,
                    (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as attendance_percentage
                FROM attendance 
                WHERE employee_id = ? AND date BETWEEN ? AND ?
                GROUP BY employee_id
            ) a
            JOIN (
                SELECT 
                    u.department_id,
                    AVG(s.attendance_percentage) as avg_department_attendance
                FROM (
                    SELECT 
                        u.id as user_id,
                        u.department_id,
                        (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as attendance_percentage
                    FROM attendance att
                    JOIN users u ON att.employee_id = u.id
                    WHERE att.date BETWEEN ? AND ?
                    GROUP BY u.id, u.department_id
                ) s
                JOIN users u ON s.user_id = u.id
                GROUP BY u.department_id
            ) d ON a.employee_id IN (
                SELECT id FROM users WHERE department_id = d.department_id
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $employeeId, $startDate, $endDate, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Historical Performance Trends
function getHistoricalPerformanceTrends($conn, $employeeId, $year) {
    $sql = "SELECT 
                QUARTER(date) as quarter,
                YEAR(date) as year,
                (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as attendance_percentage,
                LAG((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100) OVER (ORDER BY YEAR(date), QUARTER(date)) as prev_quarter_attendance,
                ((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100) - 
                LAG((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100) OVER (ORDER BY YEAR(date), QUARTER(date)) as quarter_change
            FROM attendance 
            WHERE employee_id = ? AND YEAR(date) = ?
            GROUP BY YEAR(date), QUARTER(date)
            ORDER BY YEAR(date), QUARTER(date)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $employeeId, $year);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Attendance Score Calculation
function getAttendanceScore($conn, $employeeId, $startDate, $endDate) {
    $summary = getPersonalAttendanceSummary($conn, $employeeId, $startDate, $endDate);
    
    if (!$summary) {
        return null;
    }
    
    // Calculate consistency score (standard deviation of work hours)
    $sql = "SELECT 
                STDDEV(work_hours) as hours_stddev,
                AVG(work_hours) as avg_hours
            FROM attendance 
            WHERE employee_id = ? AND status = 'present' AND date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $consistencyData = $stmt->get_result()->fetch_assoc();
    
    $consistencyScore = 0;
    if ($consistencyData && $consistencyData['avg_hours'] > 0) {
        $cv = $consistencyData['hours_stddev'] / $consistencyData['avg_hours'];
        $consistencyScore = max(0, 1 - $cv) * 100; // Convert to percentage
    }
    
    // Calculate overtime contribution
    $sql = "SELECT 
                SUM(CASE WHEN work_hours > 8 THEN work_hours - 8 ELSE 0 END) as overtime_hours
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $overtimeData = $stmt->get_result()->fetch_assoc();
    
    $overtimeContribution = 0;
    if ($overtimeData && $overtimeData['overtime_hours'] > 0) {
        $overtimeContribution = min(100, ($overtimeData['overtime_hours'] / 20) * 100); // Max 10 points
    }
    
    // Calculate overall score
    $overallScore = (
        ($summary['attendance_percentage'] * 0.4) +                    // 40% weight for attendance
        ($summary['punctuality_rate'] * 0.3) +                     // 30% weight for punctuality
        ($consistencyScore * 0.2) +                                 // 20% weight for consistency
        ($overtimeContribution * 0.1)                                  // 10% weight for extra effort
    );
    
    return [
        'attendance_percentage' => $summary['attendance_percentage'],
        'punctuality_rate' => $summary['punctuality_rate'],
        'consistency_score' => round($consistencyScore, 2),
        'overtime_contribution' => round($overtimeContribution, 2),
        'overall_attendance_score' => round($overallScore, 2)
    ];
}

// Pattern Recognition
function getAttendancePatternRecognition($conn, $employeeId, $startDate, $endDate) {
    $summary = getPersonalAttendanceSummary($conn, $employeeId, $startDate, $endDate);
    
    if (!$summary) {
        return null;
    }
    
    $performanceCategory = 'Normal Performance';
    $recommendation = 'Continue current performance';
    
    if ($summary['absenteeism_rate'] > 10) {
        $performanceCategory = 'High Absenteeism Risk';
        $recommendation = 'Schedule meeting with HR to discuss attendance issues';
    } elseif ($summary['days_late'] > 8) {
        $performanceCategory = 'Punctuality Issue';
        $recommendation = 'Implement attendance improvement plan';
    } elseif ($summary['avg_daily_hours'] < 7.5) {
        $performanceCategory = 'Underperformance Risk';
        $recommendation = 'Provide additional training and support';
    }
    
    return [
        'performance_category' => $performanceCategory,
        'recommendation' => $recommendation,
        'metrics' => $summary
    ];
}

// Check-in Method Analysis
function getCheckInMethodAnalysis($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                method,
                COUNT(*) as usage_count,
                AVG(work_hours) as avg_hours_by_method
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ?
            GROUP BY method
            ORDER BY usage_count DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Calculate usage percentages
    $totalUsage = array_sum(array_column($result, 'usage_count'));
    foreach ($result as &$row) {
        $row['usage_percentage'] = $totalUsage > 0 ? round(($row['usage_count'] / $totalUsage) * 100, 2) : 0;
    }
    
    return $result;
}

// Risk Assessment
function getAttendanceRiskAssessment($conn, $employeeId, $startDate, $endDate) {
    $summary = getPersonalAttendanceSummary($conn, $employeeId, $startDate, $endDate);
    
    if (!$summary) {
        return null;
    }
    
    $riskLevel = 'Low Risk';
    $recommendedAction = 'Continue current performance';
    
    if ($summary['absenteeism_rate'] > 10) {
        $riskLevel = 'High Risk - Action Required';
        $recommendedAction = 'Schedule meeting with HR immediately';
    } elseif ($summary['absenteeism_rate'] > 5) {
        $riskLevel = 'Medium Risk - Monitor';
        $recommendedAction = 'Implement attendance improvement plan';
    } elseif ($summary['punctuality_rate'] < 80) {
        $riskLevel = 'Punctuality Risk';
        $recommendedAction = 'Provide time management training';
    }
    
    return [
        'risk_level' => $riskLevel,
        'recommended_action' => $recommendedAction,
        'metrics' => $summary
    ];
}

// Check-in/Check-out Trends
function getCheckInOutTrends($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                date,
                TIME(check_in) as check_in_time,
                TIME(check_out) as check_out_time,
                work_hours,
                status
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ?
            AND check_in IS NOT NULL AND check_out IS NOT NULL
            ORDER BY date";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Convert times to decimal hours for better charting
    foreach ($result as &$row) {
        if ($row['check_in_time']) {
            $checkInTime = explode(':', $row['check_in_time']);
            $row['check_in_decimal'] = $checkInTime[0] + ($checkInTime[1] / 60);
        }
        
        if ($row['check_out_time']) {
            $checkOutTime = explode(':', $row['check_out_time']);
            $row['check_out_decimal'] = $checkOutTime[0] + ($checkOutTime[1] / 60);
        }
    }
    
    return $result;
}

// Time Management Reports Functions

// Get Time Management Summary
function getTimeManagementSummary($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                COUNT(*) as total_work_days,
                AVG(work_hours) as avg_daily_hours,
                MIN(work_hours) as min_hours,
                MAX(work_hours) as max_hours,
                STDDEV(work_hours) as hours_variance,
                SUM(CASE WHEN TIME(check_in) <= '08:30:00' THEN 1 ELSE 0 END) as on_time_arrivals,
                SUM(CASE WHEN work_hours > 8 THEN work_hours - 8 ELSE 0 END) as total_overtime,
                SUM(CASE WHEN work_hours >= 7.5 THEN 1 ELSE 0 END) as productive_days,
                (SUM(CASE WHEN TIME(check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as punctuality_rate,
                (SUM(CASE WHEN work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as productivity_rate
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ? AND status = 'present'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Calculate efficiency score
    $efficiency = 0;
    if ($result['total_work_days'] > 0) {
        $punctuality_weight = 0.3;
        $productivity_weight = 0.4;
        $consistency_weight = 0.3;
        
        $punctuality_score = min($result['punctuality_rate'], 100);
        $productivity_score = min($result['productivity_rate'], 100);
        $consistency_score = $result['hours_variance'] > 0 ? max(0, 100 - ($result['hours_variance'] * 10)) : 100;
        
        $efficiency = ($punctuality_score * $punctuality_weight) + 
                     ($productivity_score * $productivity_weight) + 
                     ($consistency_score * $consistency_weight);
    }
    
    $result['efficiency_score'] = round($efficiency, 2);
    $result['overtime_percentage'] = $result['total_work_days'] > 0 ? 
        round(($result['total_overtime'] / ($result['avg_daily_hours'] * $result['total_work_days'])) * 100, 2) : 0;
    
    return $result;
}

// Get Check-in/Check-out Patterns
function getCheckInOutPatterns($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                DATE(check_in) as work_date,
                TIME(check_in) as arrival_time,
                TIME(check_out) as departure_time,
                work_hours,
                TIMESTAMPDIFF(MINUTE, '08:30:00', check_in) as arrival_deviation,
                TIMESTAMPDIFF(MINUTE, check_out, '17:30:00') as departure_deviation,
                CASE 
                    WHEN TIME(check_in) <= '08:30:00' THEN 'on_time'
                    WHEN TIME(check_in) <= '09:00:00' THEN 'slightly_late'
                    ELSE 'late'
                END as arrival_category,
                CASE 
                    WHEN TIME(check_out) >= '17:30:00' THEN 'overtime'
                    WHEN TIME(check_out) >= '17:00:00' THEN 'on_time'
                    ELSE 'early_departure'
                END as departure_category
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ? 
            AND status = 'present' AND check_in IS NOT NULL AND check_out IS NOT NULL
            ORDER BY work_date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get Overtime Analysis
function getOvertimeAnalysis($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                date,
                work_hours,
                CASE 
                    WHEN work_hours > 8 THEN work_hours - 8
                    ELSE 0 
                END as overtime_hours,
                CASE 
                    WHEN work_hours > 10 THEN 'excessive'
                    WHEN work_hours > 8 THEN 'standard'
                    ELSE 'none'
                END as overtime_category,
                TIME(check_in) as check_in_time,
                TIME(check_out) as check_out_time
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ? 
            AND work_hours > 8 AND status = 'present'
            ORDER BY date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $overtimeRecords = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Calculate overtime statistics
    $stats = [
        'total_overtime_days' => count($overtimeRecords),
        'total_overtime_hours' => array_sum(array_column($overtimeRecords, 'overtime_hours')),
        'avg_overtime_per_day' => 0,
        'excessive_overtime_days' => 0,
        'standard_overtime_days' => 0
    ];
    
    if ($stats['total_overtime_days'] > 0) {
        $stats['avg_overtime_per_day'] = round($stats['total_overtime_hours'] / $stats['total_overtime_days'], 2);
        $stats['excessive_overtime_days'] = count(array_filter($overtimeRecords, fn($r) => $r['overtime_category'] === 'excessive'));
        $stats['standard_overtime_days'] = count(array_filter($overtimeRecords, fn($r) => $r['overtime_category'] === 'standard'));
    }
    
    return [
        'records' => $overtimeRecords,
        'statistics' => $stats
    ];
}

// Analyze Break Patterns
function analyzeBreakPatterns($conn, $employeeId, $startDate, $endDate) {
    // This is a simplified version - in a real system, you'd need break tracking
    // For now, we'll analyze patterns based on work hour distributions
    
    $sql = "SELECT 
                date,
                work_hours,
                TIME(check_in) as check_in_time,
                TIME(check_out) as check_out_time
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ? 
            AND status = 'present' AND work_hours > 0
            ORDER BY date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($records)) {
        return [
            'avg_break_duration' => 0,
            'break_frequency' => 0,
            'long_break_days' => 0,
            'break_compliance' => 0,
            'patterns' => []
        ];
    }
    
    // Analyze work hour patterns to infer break patterns
    $workHours = array_column($records, 'work_hours');
    $avgWorkHours = array_sum($workHours) / count($workHours);
    
    // Simulated break analysis (would need actual break tracking data)
    $patterns = [];
    foreach ($records as $record) {
        $pattern = [
            'date' => $record['date'],
            'work_duration' => $record['work_hours'],
            'inferred_break_time' => max(0, 8.5 - $record['work_hours']), // Assuming 8.5h standard work day
            'pattern_type' => $record['work_hours'] < 7 ? 'short_day' : ($record['work_hours'] > 9 ? 'long_day' : 'normal')
        ];
        $patterns[] = $pattern;
    }
    
    return [
        'avg_break_duration' => round(array_sum(array_column($patterns, 'inferred_break_time')) / count($patterns), 2),
        'break_frequency' => round(count(array_filter($patterns, fn($p) => $p['inferred_break_time'] > 0.5)) / count($patterns), 2),
        'long_break_days' => count(array_filter($patterns, fn($p) => $p['inferred_break_time'] > 1)),
        'break_compliance' => round((count(array_filter($patterns, fn($p) => $p['pattern_type'] === 'normal')) / count($patterns)) * 100, 2),
        'patterns' => $patterns
    ];
}

// Get Productivity Metrics
function getProductivityMetrics($conn, $employeeId, $startDate, $endDate) {
    $sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN TIME(check_in) <= '08:30:00' THEN 1 ELSE 0 END) as on_time_arrivals,
                SUM(CASE WHEN work_hours >= 7.5 THEN 1 ELSE 0 END) as productive_days,
                AVG(work_hours) as avg_productivity_hours,
                (SUM(CASE WHEN TIME(check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as punctuality_rate,
                (SUM(CASE WHEN work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as productivity_rate,
                MIN(work_hours) as min_productivity_hours,
                MAX(work_hours) as max_productivity_hours,
                STDDEV(work_hours) as productivity_variance
            FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ? AND status = 'present'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Calculate productivity score
    $productivityScore = 0;
    if ($result['total_days'] > 0) {
        $hoursWeight = 0.4;
        $punctualityWeight = 0.3;
        $consistencyWeight = 0.3;
        
        $hoursScore = min(($result['avg_productivity_hours'] / 8) * 100, 100);
        $punctualityScore = $result['punctuality_rate'];
        $consistencyScore = $result['productivity_variance'] > 0 ? max(0, 100 - ($result['productivity_variance'] * 10)) : 100;
        
        $productivityScore = ($hoursScore * $hoursWeight) + 
                            ($punctualityScore * $punctualityWeight) + 
                            ($consistencyScore * $consistencyWeight);
    }
    
    $result['productivity_score'] = round($productivityScore, 2);
    $result['performance_grade'] = $productivityScore >= 90 ? 'Excellent' : 
                                  ($productivityScore >= 80 ? 'Good' : 
                                  ($productivityScore >= 70 ? 'Average' : 'Needs Improvement'));
    
    return $result;
}

// Get Team Time Management Comparison
function getTeamTimeManagementComparison($conn, $departmentId, $startDate, $endDate) {
    $sql = "SELECT 
                e.id,
                u.names as name,
                
                a.avg_work_hours,
                a.punctuality_rate,
                a.overtime_hours,
                a.productivity_score,
                RANK() OVER (ORDER BY a.productivity_score DESC) as productivity_rank,
                RANK() OVER (ORDER BY a.punctuality_rate DESC) as punctuality_rank,
                RANK() OVER (ORDER BY a.avg_work_hours DESC) as hours_rank
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN (
                SELECT 
                    a.employee_id,
                    AVG(a.work_hours) as avg_work_hours,
                    (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as punctuality_rate,
                    SUM(CASE WHEN a.work_hours > 8 THEN a.work_hours - 8 ELSE 0 END) as overtime_hours,
                    (AVG(a.work_hours) / 8) * 100 as productivity_score
                FROM attendance a
                JOIN employees e ON a.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                WHERE u.department_id = ? AND a.date BETWEEN ? AND ? AND a.status = 'present'
                GROUP BY a.employee_id
            ) a ON e.id = a.employee_id
            ORDER BY productivity_rank";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $departmentId, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get Time Distribution Data for Charts
function getTimeDistributionData($conn, $employeeId, $startDate, $endDate) {
    // Arrival time distribution
    $arrivalSql = "SELECT 
                    HOUR(TIME(check_in)) as hour,
                    COUNT(*) as frequency
                   FROM attendance 
                   WHERE employee_id = ? AND date BETWEEN ? AND ? 
                   AND status = 'present' AND check_in IS NOT NULL
                   GROUP BY HOUR(TIME(check_in))
                   ORDER BY hour";
    
    $stmt = $conn->prepare($arrivalSql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $arrivalDistribution = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Departure time distribution
    $departureSql = "SELECT 
                      HOUR(TIME(check_out)) as hour,
                      COUNT(*) as frequency
                     FROM attendance 
                     WHERE employee_id = ? AND date BETWEEN ? AND ? 
                     AND status = 'present' AND check_out IS NOT NULL
                     GROUP BY HOUR(TIME(check_out))
                     ORDER BY hour";
    
    $stmt = $conn->prepare($departureSql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $departureDistribution = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Work hours distribution
    $hoursSql = "SELECT 
                   CASE 
                       WHEN work_hours < 6 THEN '< 6 hours'
                       WHEN work_hours < 7 THEN '6-7 hours'
                       WHEN work_hours < 8 THEN '7-8 hours'
                       WHEN work_hours < 9 THEN '8-9 hours'
                       WHEN work_hours < 10 THEN '9-10 hours'
                       ELSE '> 10 hours'
                   END as hours_category,
                   COUNT(*) as frequency
                  FROM attendance 
                  WHERE employee_id = ? AND date BETWEEN ? AND ? 
                  AND status = 'present' AND work_hours > 0
                  GROUP BY 
                   CASE 
                       WHEN work_hours < 6 THEN '< 6 hours'
                       WHEN work_hours < 7 THEN '6-7 hours'
                       WHEN work_hours < 8 THEN '7-8 hours'
                       WHEN work_hours < 9 THEN '8-9 hours'
                       WHEN work_hours < 10 THEN '9-10 hours'
                       ELSE '> 10 hours'
                   END
                  ORDER BY MIN(work_hours)";
    
    $stmt = $conn->prepare($hoursSql);
    $stmt->bind_param('iss', $employeeId, $startDate, $endDate);
    $stmt->execute();
    $hoursDistribution = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return [
        'arrival_distribution' => $arrivalDistribution,
        'departure_distribution' => $departureDistribution,
        'hours_distribution' => $hoursDistribution
    ];
}

// Time Management Report Functions

// Get all non-admin employees for dropdown
function getAllNonAdminEmployees($conn) {
    $sql = "SELECT u.id as user_id, u.names, e.* 
            FROM users u 
            JOIN employees e ON u.id = e.user_id 
            WHERE u.usertype != 'Admin' 
            ORDER BY u.names";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}


// Get company-wide summary for all users view
function getCompanyWideSummary($conn, $startDate, $endDate) {
    $sql = "SELECT 
                COUNT(DISTINCT a.employee_id) as total_employees,
                COUNT(*) as total_work_days,
                AVG(a.work_hours) as avg_daily_hours,
                MIN(a.work_hours) as min_hours,
                MAX(a.work_hours) as max_hours,
                (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as avg_punctuality_rate,
                SUM(CASE WHEN a.work_hours > 8 THEN a.work_hours - 8 ELSE 0 END) as total_overtime,
                (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as avg_productivity_rate
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE a.date BETWEEN ? AND ? 
            AND a.status = 'present' 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get department-wise summary
function getDepartmentWiseSummary($conn, $startDate, $endDate) {
    $sql = "SELECT 
                d.name as department_name,
                COUNT(DISTINCT a.employee_id) as employee_count,
                COUNT(*) as total_work_days,
                AVG(a.work_hours) as avg_daily_hours,
                (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as avg_punctuality_rate,
                (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as avg_productivity_rate,
                SUM(CASE WHEN a.work_hours > 8 THEN a.work_hours - 8 ELSE 0 END) as total_overtime
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE a.date BETWEEN ? AND ? 
            AND a.status = 'present' 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            AND d.name IS NOT NULL
            GROUP BY d.id, d.name
            ORDER BY avg_productivity_rate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Get top performers
function getTopPerformers($conn, $startDate, $endDate, $limit = 10) {
    $sql = "SELECT 
                u.names,
                d.name as department,
                AVG(a.work_hours) as avg_hours,
                (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as punctuality_rate,
                (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as productivity_rate,
                COUNT(*) as work_days
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE a.date BETWEEN ? AND ? 
            AND a.status = 'present' 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY a.employee_id, e.id, u.names, d.name
            HAVING work_days >= 5
            ORDER BY productivity_rate DESC, punctuality_rate DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $startDate, $endDate, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Attendance Forecasting
function getAttendanceForecasting($conn, $employeeId, $year, $month) {
    // Get last 3 months of data for trend analysis
    $sql = "SELECT 
                (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as attendance_percentage,
                MONTH(date) as month
            FROM attendance 
            WHERE employee_id = ? AND YEAR(date) = ? AND MONTH(date) BETWEEN ? AND ?
            GROUP BY MONTH(date)
            ORDER BY month";
    $stmt = $conn->prepare($sql);
    $startMonth = max(1, $month - 3);
    $stmt->bind_param('iiii', $employeeId, $year, $startMonth, $month - 1);
    $stmt->execute();
    $trendData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($trendData)) {
        return null;
    }
    
    // Calculate moving average
    $avgAttendance = array_sum(array_column($trendData, 'attendance_percentage')) / count($trendData);
    
    $predictedPerformance = 'Average';
    if ($avgAttendance > 95) {
        $predictedPerformance = 'Excellent';
    } elseif ($avgAttendance > 90) {
        $predictedPerformance = 'Good';
    } elseif ($avgAttendance > 85) {
        $predictedPerformance = 'Average';
    } else {
        $predictedPerformance = 'Needs Improvement';
    }
    
    return [
        'predicted_performance_level' => $predictedPerformance,
        'moving_avg_attendance' => round($avgAttendance, 2),
        'trend_data' => $trendData
    ];
}

// Get employee by user ID (enhanced version)
function getEmployeeByUserIdEnhanced($conn, $userId) {
    $sql = "SELECT 
                e.*,
                u.names,
                u.email,
                u.usertype,
                d.name as department_name,
                d.id as department_id
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE u.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get attendance list for report
function getAttendanceListForReport($conn, $startDate, $endDate) {
    $sql = "SELECT 
                a.id,
                a.date,
                a.check_in,
                a.check_out,
                a.work_hours,
                a.status,
                a.method,
                a.ip_address,
                u.names as employee_name,
                d.name as department,
                CASE 
                    WHEN a.status = 'present' THEN 'success'
                    WHEN a.status = 'absent' THEN 'danger'
                    WHEN a.status = 'late' THEN 'warning'
                    WHEN a.status = 'half_day' THEN 'info'
                    ELSE 'secondary'
                END as status_badge,
                CASE 
                    WHEN TIME(a.check_in) <= '08:30:00' THEN 'On Time'
                    WHEN TIME(a.check_in) <= '09:00:00' THEN 'Late'
                    WHEN TIME(a.check_in) <= '10:00:00' THEN 'Very Late'
                    ELSE 'Extremely Late'
                END as punctuality_status
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE a.date BETWEEN ? AND ? 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            ORDER BY a.date DESC, u.names, a.check_in";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get all employee assessments for overall view
function getAllEmployeeAssessments($conn, $startDate, $endDate) {
    $sql = "SELECT 
                e.id,
                u.names as employee_name,
                d.name as department,
                AVG(a.work_hours) as avg_hours,
                (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as punctuality_rate,
                (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as productivity_rate,
                SUM(CASE WHEN a.work_hours > 8 THEN a.work_hours - 8 ELSE 0 END) as total_overtime,
                COUNT(*) as work_days,
                CASE 
                    WHEN (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 >= 90 
                    AND (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 >= 90 THEN 'Excellent'
                    WHEN (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 >= 80 
                    AND (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 >= 80 THEN 'Good'
                    WHEN (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 >= 70 
                    AND (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 >= 70 THEN 'Average'
                    ELSE 'Needs Improvement'
                END as performance_grade
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE a.date BETWEEN ? AND ? 
            AND a.status = 'present' 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY a.employee_id, e.id, u.names, d.name
            HAVING work_days >= 5
            ORDER BY productivity_rate DESC, punctuality_rate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Add rankings
    $overallRank = 1;
    $departmentRanks = [];
    
    foreach ($results as &$employee) {
        $employee['overall_rank'] = $overallRank++;
        
        // Department ranking
        $dept = $employee['department'];
        if (!isset($departmentRanks[$dept])) {
            $departmentRanks[$dept] = 1;
        }
        $employee['dept_rank'] = $departmentRanks[$dept]++;
    }
    
    return $results;
}

// Get daily attendance pattern
function getDailyAttendancePattern($conn, $startDate, $endDate) {
    $sql = "SELECT 
                a.date,
                COUNT(*) as total_employees,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status = 'half_day' THEN 1 ELSE 0 END) as half_day
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE a.date BETWEEN ? AND ? 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY a.date
            ORDER BY a.date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get hourly distribution
function getHourlyDistribution($conn, $startDate, $endDate) {
    $sql = "SELECT 
                HOUR(a.check_in) as hour,
                COUNT(*) as employee_count
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE a.date BETWEEN ? AND ? 
            AND a.check_in IS NOT NULL
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY HOUR(a.check_in)
            ORDER BY hour";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Get work hours distribution
function getWorkHoursDistribution($conn, $startDate, $endDate) {
    $sql = "SELECT 
                CASE 
                    WHEN a.work_hours < 6 THEN '< 6 hours'
                    WHEN a.work_hours < 7 THEN '6-7 hours'
                    WHEN a.work_hours < 8 THEN '7-8 hours'
                    WHEN a.work_hours < 9 THEN '8-9 hours'
                    ELSE '> 9 hours'
                END as hours_category,
                COUNT(*) as count,
                MIN(a.work_hours) as min_hours,
                MAX(a.work_hours) as max_hours,
                AVG(a.work_hours) as avg_hours
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE a.date BETWEEN ? AND ? 
            AND a.work_hours > 0
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY hours_category
            ORDER BY 
                CASE hours_category
                    WHEN '< 6 hours' THEN 1
                    WHEN '6-7 hours' THEN 2
                    WHEN '7-8 hours' THEN 3
                    WHEN '8-9 hours' THEN 4
                    ELSE 5
                END";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get method usage analysis
function getMethodUsageAnalysis($conn, $startDate, $endDate) {
    $sql = "SELECT 
                a.method,
                COUNT(*) as count,
                (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM attendance a2 
                    LEFT JOIN employees e2 ON a2.employee_id = e2.id 
                    LEFT JOIN users u2 ON e2.user_id = u2.id 
                    WHERE a2.date BETWEEN ? AND ? AND (u2.usertype IS NULL OR u2.usertype != 'Admin'))) as percentage
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE a.date BETWEEN ? AND ? 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY a.method";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $startDate, $endDate, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get weekly trend analysis
function getWeeklyTrendAnalysis($conn, $startDate, $endDate) {
    $sql = "SELECT 
                DATE_FORMAT(a.date, '%Y-%u') as week_year,
                DATE_FORMAT(a.date, '%Y-W%u') as week_label,
                COUNT(*) as total_days,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as attendance_percentage,
                AVG(a.work_hours) as avg_hours,
                SUM(CASE WHEN a.work_hours > 8 THEN a.work_hours - 8 ELSE 0 END) as total_overtime
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE a.date BETWEEN ? AND ? 
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY week_year, week_label
            ORDER BY week_year";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get punctuality vs productivity correlation
function getPunctualityProductivityCorrelation($conn, $startDate, $endDate) {
    $sql = "SELECT 
                u.names as employee_name,
                d.name as department,
                (SUM(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as punctuality_rate,
                (SUM(CASE WHEN a.work_hours >= 7.5 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as productivity_rate,
                COUNT(*) as work_days,
                AVG(a.work_hours) as avg_hours
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE a.date BETWEEN ? AND ? 
            AND a.status = 'present'
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY a.employee_id, u.names, d.name
            HAVING work_days >= 3";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get department attendance heatmap
function getDepartmentAttendanceHeatmap($conn, $startDate, $endDate) {
    $sql = "SELECT 
                d.name as department,
                a.date,
                (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as attendance_rate
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE a.date BETWEEN ? AND ? 
            AND d.name IS NOT NULL
            AND (u.usertype IS NULL OR u.usertype != 'Admin')
            GROUP BY d.name, a.date
            ORDER BY d.name, a.date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

