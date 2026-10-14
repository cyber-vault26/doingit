<?php

// Get date range from form or default to current month
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$scope = 'all'; // personal, team, all
$selectedUserId = $_GET['user_id'] ?? 0;

// Check user role and permissions
$canViewAll = in_array($_SESSION['usertype'], ['Admin', 'hr_admin', 'Manager']);
$canViewTeam = in_array($_SESSION['usertype'], ['manager', 'hr_admin']);

// Get current employee for personal view
$currentUser = getEmployeeByUserIdEnhanced($conn, $_SESSION['userid']);
$employee = $selectedUserId > 0 ? getEmployeeByUserIdEnhanced($conn, $selectedUserId) : $currentUser;

// Get initial data for attendance list (today by default)
$attendanceStartDate = $_GET['attendance_start_date'] ?? date('Y-m-d');
$attendanceEndDate = $_GET['attendance_end_date'] ?? date('Y-m-d');
$attendanceList = getAttendanceListForReport($conn, $attendanceStartDate, $attendanceEndDate);

// Get all non-admin employees for reference
$allEmployees = getAllNonAdminEmployees($conn);
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'Manager' ) {
    echo "<script>alert('Access denied. You do not have permission to view leave list.'); window.location.href='./';</script>";
    exit;
}else{
  //  echo $_SESSION['usertype'];
}
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-chart-line mr-2"></i>Time Management Report
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./">Home</a></li>
                    <li class="breadcrumb-item"><a href="./?page=attendance_dashboard">Attendance</a></li>
                    <li class="breadcrumb-item active">Time Management Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Date Filter Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter mr-2"></i>Filter Options
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="reportFilterForm" class="row g-3">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="user_id" class="form-label">Employee (Optional)</label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="0">All Employees</option>
                                    <?php foreach ($allEmployees as $emp): ?>
                                        <option value="<?php echo $emp['user_id']; ?>" <?php echo $selectedUserId == $emp['user_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['names'] . ' - ' . $emp['department']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-2"></i>Generate Report
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="resetFilters()">
                                    <i class="fas fa-redo mr-2"></i>Reset
                                </button>
                                <div id="loadingMessage" class="alert alert-info mt-2" style="display: none;">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading report data...
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Company-Wide Summary -->
        <?php if ($canViewAll): ?>
        <div class="row mb-4" id="companySummarySection">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building mr-2"></i>Company-Wide Summary
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Employees</span>
                                        <span class="info-box-number" id="totalEmployees">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-business-time"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Avg Hours</span>
                                        <span class="info-box-number" id="avgDailyHours">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-user-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Punctuality</span>
                                        <span class="info-box-number" id="avgPunctualityRate">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-hourglass-end"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Overtime Hours</span>
                                        <span class="info-box-number" id="totalOvertime">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Productivity</span>
                                        <span class="info-box-number" id="avgProductivityRate">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary"><i class="fas fa-calendar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Work Days</span>
                                        <span class="info-box-number" id="totalWorkDays">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Department-Wise Summary -->
        <?php if ($canViewTeam): ?>
        <div class="row mb-4" id="departmentSummarySection">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sitemap mr-2"></i>Department Performance
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="departmentSummaryTable">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Employees</th>
                                        <th>Work Days</th>
                                        <th>Avg Hours</th>
                                        <th>Punctuality</th>
                                        <th>Productivity</th>
                                        <th>Overtime</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Loading department data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Top Performers -->
        <?php if ($canViewAll): ?>
        <div class="row mb-4 d-none" id="topPerformersSection">
            <div class="col-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trophy mr-2"></i>Top Performers
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="topPerformersTable">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Work Days</th>
                                        <th>Avg Hours</th>
                                        <th>Punctuality</th>
                                        <th>Productivity</th>
                                        <th>Overall Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Loading top performers data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comprehensive Employee Assessments -->
        <?php if ($canViewAll): ?>
        <div class="row mb-4" id="employeeAssessmentsSection">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>Complete Employee Assessment
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool btn-sm" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool btn-sm" data-card-widget="maximize">
                                <i class="fas fa-expand"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-info" onclick="printReport()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="employeeAssessmentsTable">
                                <thead>
                                    <tr>
                                        <th>Overall Rank</th>
                                        <th>Employee Name</th>
                                        <th>Department</th>
                                        <th>Dept Rank</th>
                                        <th>Work Days</th>
                                        <th>Avg Hours</th>
                                        <th>Punctuality</th>
                                        <th>Productivity</th>
                                        <th>Overtime</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">Loading employee assessment data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Performance Analytics -->
        <?php if ($canViewAll): ?>
        <div class="row mb-4 d-none" id="performanceAnalyticsSection">
            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Performance Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceDistributionChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Department Comparison
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="departmentComparisonChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comprehensive Analytics Graphs -->
        <div class="row mb-4 d-none">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>Attendance Analytics Dashboard
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- First Row of Graphs -->
                        <div class="row mb-4">
                            <!-- Daily Attendance Pattern -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Daily Attendance Pattern</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="dailyAttendanceChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hourly Distribution -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Hourly Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="hourlyDistributionChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Second Row of Graphs -->
                        <div class="row mb-4">
                            <!-- Check-in Time Distribution -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Check-in Time Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="checkinDistributionChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Work Hours Distribution -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Work Hours Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="workHoursDistributionChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Method Usage -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Check-in Methods</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="methodUsageChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Third Row of Graphs -->
                        <div class="row mb-4">
                            <!-- Weekly Trend -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Weekly Trend Analysis</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="weeklyTrendChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Punctuality vs Productivity -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Punctuality vs Productivity</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="punctualityProductivityChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fourth Row - Department Heatmap -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">Department Attendance Heatmap</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="departmentHeatmapChart" height="150"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
<!-- Attendance List -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>Attendance List
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" onclick="exportAttendanceList()">
                                <i class="fas fa-file-excel"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Attendance Date Filter -->
                        <form id="attendanceFilterForm" class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="attendance_start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="attendance_start_date" name="attendance_start_date" value="<?php echo htmlspecialchars($attendanceStartDate); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="attendance_end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="attendance_end_date" name="attendance_end_date" value="<?php echo htmlspecialchars($attendanceEndDate); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-2"></i>Filter Attendance
                                    </button>
                                    <button type="button" class="btn btn-secondary ml-2" onclick="resetAttendanceFilters()">
                                        <i class="fas fa-redo mr-2"></i>Reset
                                    </button>
                                    <div id="attendanceLoadingMessage" class="alert alert-info mt-2" style="display: none;">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading attendance data...
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Attendance Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="attendanceListTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Work Hours</th>
                                        <th>Status</th>
                                        <th>Punctuality</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($attendanceList)): ?>
                                        <?php foreach ($attendanceList as $attendance): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($attendance['date'])); ?></td>
                                                <td><?php echo htmlspecialchars($attendance['employee_name']); ?></td>
                                                <td><?php echo htmlspecialchars($attendance['department']); ?></td>
                                                <td><?php echo $attendance['check_in'] ? date('H:i:s', strtotime($attendance['check_in'])) : '-'; ?></td>
                                                <td><?php echo $attendance['check_out'] ? date('H:i:s', strtotime($attendance['check_out'])) : '-'; ?></td>
                                                <td><?php echo number_format($attendance['work_hours'], 2); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $attendance['status_badge']; ?>">
                                                        <?php echo ucfirst($attendance['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($attendance['check_in']): ?>
                                                        <span class="badge badge-<?php echo $attendance['punctuality_status'] === 'On Time' ? 'success' : 'warning'; ?>">
                                                            <?php echo $attendance['punctuality_status']; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo ucfirst($attendance['method']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No attendance records found for the selected date range.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script src="plugins/chart.js/Chart.min.js"></script>
<script>
// Global variables to store chart instances
let performanceChart = null;
let departmentChart = null;

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadReportData();
    
    // Set up form event listeners
    document.getElementById('reportFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadReportData();
    });
    
    document.getElementById('attendanceFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadAttendanceList();
    });
});

// Load all report data via AJAX
function loadReportData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const userId = document.getElementById('user_id').value;
    
    // Show loading message
    document.getElementById('loadingMessage').style.display = 'block';
    
    const formData = new FormData();
    formData.append('action', 'get_all_data');
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);
    formData.append('user_id', userId);
    
    fetch('./controllers/time_management_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
             console.log("Raw response:", response); // full Response object
             console.log("fisrst");
             return response.json();
       }).then(data => {
           console.log(data);
        if (data.status === 'success') {
            updateReportSections(data.data);
            updateCharts(data.data);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error loading report data:', error);
        showMessage('Error loading report data', 'error');
    })
    .finally(() => {
        // Hide loading message
        document.getElementById('loadingMessage').style.display = 'none';
    });
}

// Update report sections with new data
function updateReportSections(data) {
    // Update company summary
    if (data.company_summary) {
        document.getElementById('totalEmployees').textContent = data.company_summary.total_employees || 0;
        document.getElementById('avgDailyHours').textContent = number_format(data.company_summary.avg_daily_hours || 0, 2);
        document.getElementById('avgPunctualityRate').textContent = number_format(data.company_summary.avg_punctuality_rate || 0, 1) + '%';
        document.getElementById('totalOvertime').textContent = number_format(data.company_summary.total_overtime || 0, 2);
        document.getElementById('avgProductivityRate').textContent = number_format(data.company_summary.avg_productivity_rate || 0, 1) + '%';
        document.getElementById('totalWorkDays').textContent = data.company_summary.total_work_days || 0;
    }
    
    // Update other sections (you can expand this as needed)
    // For now, the other sections will be updated when you implement their respective update functions
}

// Load attendance list via AJAX
function loadAttendanceList() {
   // alert("running");
    const startDate = document.getElementById('attendance_start_date').value;
    const endDate = document.getElementById('attendance_end_date').value;
    
    // Show loading message
    document.getElementById('attendanceLoadingMessage').style.display = 'block';
    
    const formData = new FormData();
    formData.append('action', 'get_attendance_list');
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);
    
    fetch('./controllers/time_management_controller.php', {
    method: 'POST',
    body: formData
        })
        .then(response => {
            // console.log("Raw response:", response); // full Response object
            return response.json();
        })
        .then(data => {
            console.log("Parsed JSON data:", data); // actual response data
        
            if (data.status === 'success') {
                updateAttendanceTable(data.data);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading attendance list:', error);
            showMessage('Error loading attendance list', 'error');
        })
        .finally(() => {
            document.getElementById('attendanceLoadingMessage').style.display = 'none';
        });
}

// Update attendance table with new data
// function updateAttendanceTable(attendanceData) {
//     const tbody = document.querySelector('#attendanceListTable tbody');
    
//     if (!attendanceData || attendanceData.length === 0) {
//         tbody.innerHTML = '<tr><td colspan="9" class="text-center">No attendance records found for selected date range.</td></tr>';
//         return;
//     }
    
//     tbody.innerHTML = attendanceData.map(record => `
//         <tr>
//             <td>${new Date(record.date).toLocaleDateString()}</td>
//             <td>${record.employee_name}</td>
//             <td>${record.department || '-'}</td>
//             <td>${record.check_in ? new Date(record.check_in).toLocaleTimeString() : '-'}</td>
//             <td>${record.check_out ? new Date(record.check_out).toLocaleTimeString() : '-'}</td>
//             <td>${number_format(record.work_hours, 2)}</td>
//             <td>
//                 <span class="badge badge-${record.status_badge}">
//                     ${record.status.charAt(0).toUpperCase() + record.status.slice(1)}
//                 </span>
//             </td>
//             <td>
//                 ${record.check_in ? `
//                     <span class="badge badge-${record.punctuality_status === 'On Time' ? 'success' : 'warning'}">
//                         ${record.punctuality_status}
//                     </span>
//                 ` : '<span class="badge badge-secondary">-</span>'}
//             </td>
//             <td>${record.method.charAt(0).toUpperCase() + record.method.slice(1)}</td>
//         </tr>
//     `).join('');
// }
// Update attendance table with new data
function updateAttendanceTable(attendanceData) {
    const tbody = document.querySelector('#attendanceListTable tbody');
    
    if (!attendanceData || attendanceData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">No attendance records found for selected date range.</td></tr>';
        return;
    }
    
    tbody.innerHTML = attendanceData.map(record => {
        // Safe date parsing
        const date = record.date 
            ? new Date(record.date).toLocaleDateString() 
            : '-';

        // Safe time parsing (fix for "Invalid Date")
        const checkIn = record.check_in
            ? new Date(`1970-01-01T${record.check_in}`)
                .toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false })
            : '-';

        const checkOut = record.check_out
            ? new Date(`1970-01-01T${record.check_out}`)
                .toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false })
            : '-';

        return `
            <tr>
                <td>${date}</td>
                <td>${record.employee_name}</td>
                <td>${record.department || '-'}</td>
                <td>${checkIn}</td>
                <td>${checkOut}</td>
                <td>${number_format(record.work_hours, 2)}</td>
                <td>
                    <span class="badge badge-${record.status_badge}">
                        ${record.status.charAt(0).toUpperCase() + record.status.slice(1)}
                    </span>
                </td>
                <td>
                    ${record.check_in ? `
                        <span class="badge badge-${record.punctuality_status === 'On Time' ? 'success' : 'warning'}">
                            ${record.punctuality_status}
                        </span>
                    ` : '<span class="badge badge-secondary">-</span>'}
                </td>
                <td>${record.method.charAt(0).toUpperCase() + record.method.slice(1)}</td>
            </tr>
        `;
    }).join('');
}

// Reset filters
function resetFilters() {
    document.getElementById('start_date').value = '<?php echo date('Y-m-01'); ?>';
    document.getElementById('end_date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('user_id').value = '0';
    loadReportData();
}

function resetAttendanceFilters() {
    document.getElementById('attendance_start_date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('attendance_end_date').value = '<?php echo date('Y-m-d'); ?>';
    loadAttendanceList();
}

// Show message function
function showMessage(message, type) {
    // Create a temporary message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type} alert-dismissible fade show`;
    messageDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    // Insert at the top of the content
    const content = document.querySelector('.content');
    content.insertBefore(messageDiv, content.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
        }
    }, 5000);
}

// PHP number_format equivalent in JavaScript
function number_format(number, decimals, decPoint, thousandsSep) {
    decimals = decimals || 0;
    decPoint = decPoint || '.';
    thousandsSep = thousandsSep || ',';
    
    const parts = parseFloat(number).toFixed(decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
    
    return parts.join(decPoint);
}

// Update charts with new data
function updateCharts(data) {
    // Update Performance Distribution Chart
    const performanceCtx = document.getElementById('performanceDistributionChart');
    if (performanceCtx && data.employee_assessments) {
        // Destroy existing chart if it exists
        if (performanceChart) {
            performanceChart.destroy();
        }
        
        // Count performance grades
        const gradeCounts = {};
        data.employee_assessments.forEach(emp => {
            gradeCounts[emp.performance_grade] = (gradeCounts[emp.performance_grade] || 0) + 1;
        });
        
        performanceChart = new Chart(performanceCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(gradeCounts),
                datasets: [{
                    data: Object.values(gradeCounts),
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',  // Excellent - Green
                        'rgba(23, 162, 184, 0.8)',  // Good - Cyan
                        'rgba(255, 193, 7, 0.8)',   // Average - Yellow
                        'rgba(220, 53, 69, 0.8)'    // Needs Improvement - Red
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Employee Performance Distribution'
                    }
                }
            }
        });
    }

    // Update Department Comparison Chart
    const deptCtx = document.getElementById('departmentComparisonChart');
    if (deptCtx && data.department_summaries) {
        // Destroy existing chart if it exists
        if (departmentChart) {
            departmentChart.destroy();
        }
        
        departmentChart = new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: data.department_summaries.map(dept => dept.department_name),
                datasets: [
                    {
                        label: 'Avg Daily Hours',
                        data: data.department_summaries.map(dept => dept.avg_daily_hours),
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Punctuality Rate',
                        data: data.department_summaries.map(dept => dept.avg_punctuality_rate),
                        backgroundColor: 'rgba(255, 206, 86, 0.8)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Productivity Rate',
                        data: data.department_summaries.map(dept => dept.avg_productivity_rate),
                        backgroundColor: 'rgba(75, 192, 192, 0.8)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Department Performance Comparison'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Rate (%) / Hours'
                        }
                    }
                }
            }
        });
    }
}

// Function to view employee details (placeholder)
function viewEmployeeDetails(employeeId) {
    // This could open a modal or navigate to detailed view
    alert('Employee details view for ID: ' + employeeId);
    // Future implementation: open modal with detailed employee analytics
}

// Export to Excel function
function exportToExcel() {
    const table = document.getElementById('employeeAssessmentsTable');
    if (!table) {
        alert('Employee assessment table not found!');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Remove HTML tags and get text content
            let text = cols[j].innerText || cols[j].textContent;
            // Escape quotes and commas for CSV
            text = text.replace(/"/g, '""');
            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                text = `"${text}"`;
            }
            row.push(text);
        }
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `time_management_report_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print function
function printReport() {
    window.print();
}

// Export attendance list function
function exportAttendanceList() {
    const table = document.getElementById('attendanceListTable');
    if (!table) {
        alert('Attendance list table not found!');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Remove HTML tags and get text content
            let text = cols[j].innerText || cols[j].textContent;
            // Escape quotes and commas for CSV
            text = text.replace(/"/g, '""');
            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                text = `"${text}"`;
            }
            row.push(text);
        }
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `attendance_list_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
