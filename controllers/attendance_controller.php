<?php
// Set content type header FIRST - before any potential errors
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    error_log("error");
    exit;
}

// Include required files with error handling
try {
    require_once "../configs.php";
    require_once "../functions/attendance_functions.php";
    $conn = openConn();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    error_log(print_r($e->getMessage(), yes));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("running");
    $action = $_POST['action'] ?? '';
    $time = date("H:m:s");
    try {
        switch ($action) {
            case 'check_in':
                $result = recordAttendance($conn, $_SESSION['userid'], $time, 'check_in');
                echo json_encode($result);
                break;
                
            case 'check_out':
                $result = recordAttendance($conn, $_SESSION['userid'], $time, 'check_out');
                echo json_encode($result);
                break;
                
            case 'get_today_status':
                $employee = getEmployeeByUserId($conn, $_SESSION['userid']);
                if ($employee) {
                    $today = date('Y-m-d');
                    $attendance = getTodayAttendance($conn, $employee['id'], $today);
                    
                    if ($attendance) {
                        $response = [
                            'status' => 'success',
                            'attendance' => [
                                'check_in_time' => date('H:i:s', strtotime($attendance['check_in'])),
                                'check_out_time' => $attendance['check_out'] ? date('H:i:s', strtotime($attendance['check_out'])) : null,
                                'work_hours' => $attendance['work_hours'],
                                'local_time' => date('H:i:s')
                            ]
                        ];
                    } else {
                        $response = ['status' => 'success', 'attendance' => null];
                    }
                    echo json_encode($response);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
                }
                break;
                
            case 'get_report':
                $startDate = $_POST['start_date'] ?? date('Y-m-01');
                $endDate = $_POST['end_date'] ?? date('Y-m-d');
                $employee = getEmployeeByUserId($conn, $_SESSION['userid']);
                
                if ($employee) {
                    $report = getAttendanceReport($conn, $employee['id'], $startDate, $endDate);
                    echo json_encode(['status' => 'success', 'data' => $report]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
                }
                break;
                
            case 'get_summary':
                $month = $_POST['month'] ?? date('m');
                $year = $_POST['year'] ?? date('Y');
                $employee = getEmployeeByUserId($conn, $_SESSION['userid']);
                
                if ($employee) {
                    $summary = getAttendanceSummary($conn, $employee['id'], $month, $year);
                    echo json_encode(['status' => 'success', 'data' => $summary]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
                }
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
                    $checkinDistribution = getCheckinTimeDistribution($conn, $startDate, $endDate);
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
                            'checkin_distribution' => $checkinDistribution,
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
        error_log(print_r($e->getMessage()));
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
     error_log(print_r($e->getMessage()));
}
?>
