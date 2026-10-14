<?php

// Set content type header FIRST - before any potential errors
header('Content-Type: application/json');

// Start session


// // Check if user is logged in
// if (!isset($_SESSION['user']['id'])) {
//     echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
//     exit;
// }

// Include required files with error handling
try {
    require_once "../configs.php";
    require_once "../functions/attendance_functions.php";
    $conn = openConn();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'get_company_summary':
                $startDate = $_POST['start_date'] ?? date('Y-m-01');
                $endDate = $_POST['end_date'] ?? date('Y-m-d');
                
                $summary = getCompanyWideSummary($conn, $startDate, $endDate);
                echo json_encode(['status' => 'success', 'data' => $summary]);
                break;
                
            case 'get_department_summary':
                $startDate = $_POST['start_date'] ?? date('Y-m-01');
                $endDate = $_POST['end_date'] ?? date('Y-m-d');
                
                $summary = getDepartmentWiseSummary($conn, $startDate, $endDate);
                echo json_encode(['status' => 'success', 'data' => $summary]);
                break;
                
            case 'get_top_performers':
                $startDate = $_POST['start_date'] ?? date('Y-m-01');
                $endDate = $_POST['end_date'] ?? date('Y-m-d');
                $limit = $_POST['limit'] ?? 10;
                
                $performers = getTopPerformers($conn, $startDate, $endDate, $limit);
                echo json_encode(['status' => 'success', 'data' => $performers]);
                break;
                
            case 'get_employee_assessments':
                $startDate = $_POST['start_date'] ?? date('Y-m-01');
                $endDate = $_POST['end_date'] ?? date('Y-m-d');
                
                $assessments = getAllEmployeeAssessments($conn, $startDate, $endDate);
                echo json_encode(['status' => 'success', 'data' => $assessments]);
                break;
                
            case 'get_attendance_list':
                $startDate = $_POST['start_date'] ?? date('Y-m-d');
                $endDate = $_POST['end_date'] ?? date('Y-m-d');
                
                $attendanceList = getAttendanceListForReport($conn, $startDate, $endDate);
                echo json_encode(['status' => 'success', 'data' => $attendanceList]);
                break;
                
            case 'get_all_data':
                $startDate = $_POST['start_date'] ?? date('Y-m-01');
                $endDate = $_POST['end_date'] ?? date('Y-m-d');
                
                $companySummary = getCompanyWideSummary($conn, $startDate, $endDate);
                $departmentSummaries = getDepartmentWiseSummary($conn, $startDate, $endDate);
                $topPerformers = getTopPerformers($conn, $startDate, $endDate, 20);
                $allEmployeeAssessments = getAllEmployeeAssessments($conn, $startDate, $endDate);
                $allEmployees = getAllNonAdminEmployees($conn);
                
                // Get analytics data
                $dailyAttendance = getDailyAttendancePattern($conn, $startDate, $endDate);
                $hourlyDistribution = getHourlyDistribution($conn, $startDate, $endDate);
                // $checkinDistribution = getCheckinTimeDistribution($conn, $startDate, $endDate);
                $workHoursDistribution = getWorkHoursDistribution($conn, $startDate, $endDate);
                $methodUsage = getMethodUsageAnalysis($conn, $startDate, $endDate);
                $weeklyTrend = getWeeklyTrendAnalysis($conn, $startDate, $endDate);
                $punctualityProductivity = getPunctualityProductivityCorrelation($conn, $startDate, $endDate);
                $departmentHeatmap = getDepartmentAttendanceHeatmap($conn, $startDate, $endDate);
                
                // Set default values if data is missing
                if (!$companySummary) {
                    $companySummary = [
                        'total_employees' => 0,
                        'total_work_days' => 0,
                        'avg_daily_hours' => 0,
                        'avg_punctuality_rate' => 0,
                        'total_overtime' => 0,
                        'avg_productivity_rate' => 0
                    ];
                }
                
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'company_summary' => $companySummary,
                        'department_summaries' => $departmentSummaries,
                        'top_performers' => $topPerformers,
                        'employee_assessments' => $allEmployeeAssessments,
                        'all_employees' => $allEmployees,
                        'daily_attendance' => $dailyAttendance,
                        'hourly_distribution' => $hourlyDistribution,
                        // 'checkin_distribution' => $checkinDistribution,
                        'work_hours_distribution' => $workHoursDistribution,
                        'method_usage' => $methodUsage,
                        'weekly_trend' => $weeklyTrend,
                        'punctuality_productivity' => $punctualityProductivity,
                        'department_heatmap' => $departmentHeatmap
                    ]
                ]);
                break;
                
            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
