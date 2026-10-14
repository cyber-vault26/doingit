<?php
$conn = openConn();
$employee = getEmployeeByUserId($conn, $_SESSION['userid']);
$todayAttendance = getTodayAttendance($conn, $employee['id'], date('Y-m-d'));
$summary = getAttendanceSummary($conn, $employee['id'], date('m'), date('Y'));

// Get comprehensive attendance data for visualizations
$startDate = date('Y-m-01');
$endDate = date('Y-m-d');
$personalSummary = getPersonalAttendanceSummary($conn, $employee['id'], $startDate, $endDate);
$workPatterns = getWorkPatternAnalysis($conn, $employee['id'], $startDate, $endDate);
$monthlyTrends = getMonthlyAttendanceTrends($conn, $employee['id'], date('Y'));
$attendanceScore = getAttendanceScore($conn, $employee['id'], $startDate, $endDate);
$riskAssessment = getAttendanceRiskAssessment($conn, $employee['id'], $startDate, $endDate);
$latePatterns = getLateArrivalPatterns($conn, $employee['id'], $startDate, $endDate);
$methodAnalysis = getCheckInMethodAnalysis($conn, $employee['id'], $startDate, $endDate);

// Get check-in/check-out trends
$checkInOutTrends = getCheckInOutTrends($conn, $employee['id'], $startDate, $endDate);

// Determine if user is currently checked in but not checked out
$isCheckedIn = ($todayAttendance && !$todayAttendance['check_out']);
$isCheckedOut = ($todayAttendance && $todayAttendance['check_out']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar will be included by main index.php -->
    
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-clock mr-2"></i>Attendance Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active">Attendance</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Employee Info Card -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-primary card-outline mb-3">
                        <div class="card-body">
                            <div class="text-center">
                                <h5 class="card-title"><?php echo $_SESSION['username']; ?></h5>
                                <p class="text-muted">Employee Code: <?php echo $employee['employee_code']; ?></p>
                                <p class="text-muted">Department: <?php echo $employee['department']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Actions -->
                <div class="col-md-9">
                    <div class="row">
                        <!-- Check-in/Check-out Card -->
                        <div class="col-md-6">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-sign-in-alt mr-2"></i>Mark Attendance
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!$isCheckedIn && !$isCheckedOut): ?>
                                        <!-- Show check-in button -->
                                        <button disabled onclick="" class="btn btn-success btn-lg btn-block">
                                            <i class="fas fa-clock mr-2"></i>Check In
                                        </button>
                                    <?php elseif ($isCheckedIn): ?>
                                        <!-- Show check-out button with check-in time -->
                                        <div class="alert alert-info mb-3">
                                            <strong>Checked in at:</strong> <?php echo date('H:i:s', strtotime($todayAttendance['check_in'])); ?>
                                        </div>
                                        <button disabled onclick="checkOut()" class="btn btn-danger btn-lg btn-block">
                                            <i class="fas fa-sign-out-alt mr-2"></i>Check Out
                                        </button>
                                    <?php else: ?>
                                        <!-- Already checked out, show check-in button -->
                                        <div class="alert alert-success mb-3">
                                            <strong>Already completed today's attendance</strong><br>
                                            Check-in: <?php echo date('H:i:s', strtotime($todayAttendance['check_in'])); ?><br>
                                            Check-out: <?php echo date('H:i:s', strtotime($todayAttendance['check_out'])); ?><br>
                                            Work Hours: <?php echo $todayAttendance['work_hours']; ?> hrs
                                        </div>
                                        <button onclick="checkIn()" class="btn btn-success btn-lg btn-block" disabled>
                                            <i class="fas fa-check mr-2"></i>Attendance Complete
                                        </button>
                                    <?php endif; ?>
                                    
                                    <div id="attendanceMessage" class="mt-3" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Today's Status Card -->
                        <div class="col-md-6">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-line mr-2"></i>Today's Status
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge <?php echo $todayAttendance ? 'badge-success' : 'badge-secondary'; ?>">
                                                    <?php echo $todayAttendance ? 'Present' : 'Not Marked'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        
                                        <?php if ($todayAttendance): ?>
                                            <tr>
                                                <td><strong>Check-in:</strong></td>
                                                <td><?php echo date('H:i:s', strtotime($todayAttendance['check_in'])); ?></td>
                                            </tr>
                                            
                                            <?php if ($todayAttendance['check_out']): ?>
                                                <tr>
                                                    <td><strong>Check-out:</strong></td>
                                                    <td><?php echo date('H:i:s', strtotime($todayAttendance['check_out'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Work Hours:</strong></td>
                                                    <td><?php echo $todayAttendance['work_hours']; ?> hrs</td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Comprehensive Analytics Dashboard -->
            <div class="row mt-4">
                <!-- Personal Attendance Summary -->
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-2"></i>Personal Attendance Summary
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-calendar-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Days</span>
                                            <span class="info-box-number"><?php echo $personalSummary['total_days'] ?? 0; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Days Present</span>
                                            <span class="info-box-number"><?php echo $personalSummary['days_present'] ?? 0; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-user-times"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Days Absent</span>
                                            <span class="info-box-number"><?php echo $personalSummary['days_absent'] ?? 0; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Days Late</span>
                                            <span class="info-box-number"><?php echo $personalSummary['days_late'] ?? 0; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Attendance %</span>
                                            <span class="info-box-number"><?php echo $personalSummary['attendance_percentage'] ?? 0; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-secondary"><i class="fas fa-star"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Overall Score</span>
                                            <span class="info-box-number"><?php echo $attendanceScore['overall_attendance_score'] ?? 0; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mt-4">
                <!-- Work Pattern Analysis -->
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-2"></i>Work Pattern Analysis
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="workPatternChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Monthly Trends -->
                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>Monthly Trends
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyTrendsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in/Check-out Trends Chart -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-2"></i>Check-in & Check-out Time Trends
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="checkInOutTrendsChart" width="400" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk Assessment & Recommendations -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Risk Assessment
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert <?php echo $riskAssessment['risk_level'] === 'Low Risk' ? 'alert-success' : ($riskAssessment['risk_level'] === 'High Risk - Action Required' ? 'alert-danger' : 'alert-warning'); ?>">
                                <h5><strong>Risk Level:</strong> <?php echo $riskAssessment['risk_level']; ?></h5>
                                <p><strong>Recommended Action:</strong> <?php echo $riskAssessment['recommended_action']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Method Analysis -->
                <div class="col-md-6">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-mobile-alt mr-2"></i>Check-in Methods
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="methodChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Report -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-2"></i>Recent Attendance
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Start Date:</label>
                                    <input type="date" id="startDate" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date:</label>
                                    <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button onclick="loadReport()" class="btn btn-primary">
                                            <i class="fas fa-search mr-1"></i>Load Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Work Hours</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendanceTableBody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                Click "Load Report" to view attendance history
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        function initializeCharts() {
            // Work Pattern Chart
            const workPatternCtx = document.getElementById('workPatternChart').getContext('2d');
            const workPatternData = <?php echo json_encode($workPatterns); ?>;
            
            new Chart(workPatternCtx, {
                type: 'bar',
                data: {
                    labels: workPatternData.map(item => item.day_of_week),
                    datasets: [{
                        label: 'Average Work Hours',
                        data: workPatternData.map(item => parseFloat(item.avg_hours).toFixed(2)),
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });

            // Monthly Trends Chart
            const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
            const monthlyTrendsData = <?php echo json_encode($monthlyTrends); ?>;
            
            new Chart(monthlyTrendsCtx, {
                type: 'line',
                data: {
                    labels: monthlyTrendsData.map(item => `Month ${item.month}`),
                    datasets: [{
                        label: 'Days Present',
                        data: monthlyTrendsData.map(item => item.days_present),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }, {
                        label: 'Days Late',
                        data: monthlyTrendsData.map(item => item.days_late),
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Method Analysis Chart
            const methodCtx = document.getElementById('methodChart').getContext('2d');
            const methodData = <?php echo json_encode($methodAnalysis); ?>;
            
            new Chart(methodCtx, {
                type: 'doughnut',
                data: {
                    labels: methodData.map(item => item.method.charAt(0).toUpperCase() + item.method.slice(1)),
                    datasets: [{
                        data: methodData.map(item => item.usage_count),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Check-in/Check-out Trends Chart
            const checkInOutCtx = document.getElementById('checkInOutTrendsChart');
            if (checkInOutCtx) {
                try {
                    const checkInOutData = <?php echo json_encode($checkInOutTrends); ?>;
                    console.log('Raw data:', checkInOutData);
                    
                    // Simple test with minimal data
                    const testLabels = [];
                    const testCheckIn = [];
                    const testCheckOut = [];
                    const testWorkHours = [];
                    
                    if (checkInOutData && Array.isArray(checkInOutData)) {
                        checkInOutData.forEach((item, index) => {
                            if (index < 10) { // Only first 10 items for testing
                                testLabels.push(item.date || 'Date ' + (index + 1));
                                testCheckIn.push(item.check_in_decimal || 8);
                                testCheckOut.push(item.check_out_decimal || 17);
                                testWorkHours.push(item.work_hours || 8);
                            }
                        });
                    }
                    
                    console.log('Test data:', { testLabels, testCheckIn, testCheckOut, testWorkHours });
                    
                    if (testLabels.length > 0) {
                        new Chart(checkInOutCtx, {
                            type: 'line',
                            data: {
                                labels: testLabels,
                                datasets: [{
                                    label: 'Check-in Time',
                                    data: testCheckIn,
                                    borderColor: 'rgb(75, 192, 192)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    tension: 0.1,
                                    fill: false
                                }, {
                                    label: 'Check-out Time',
                                    data: testCheckOut,
                                    borderColor: 'rgb(255, 99, 132)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    tension: 0.1,
                                    fill: false
                                }, {
                                    label: 'Work Hours',
                                    data: testWorkHours,
                                    borderColor: 'rgb(54, 162, 235)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                    tension: 0.1,
                                    fill: false
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                    tooltip: {
                                        enabled: true
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Date'
                                        }
                                    },
                                    y: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Hours'
                                        },
                                        min: 0
                                    }
                                }
                            }
                        });
                    } else {
                        const ctx = checkInOutCtx.getContext('2d');
                        ctx.font = '20px Arial';
                        ctx.fillStyle = '#999';
                        ctx.textAlign = 'center';
                        ctx.fillText('No data available', checkInOutCtx.width / 2, checkInOutCtx.height / 2);
                    }
                } catch (error) {
                    console.error('Chart creation error:', error);
                    const ctx = checkInOutCtx.getContext('2d');
                    ctx.font = '20px Arial';
                    ctx.fillStyle = '#f44336';
                    ctx.textAlign = 'center';
                    ctx.fillText('Chart error: ' + error.message, checkInOutCtx.width / 2, checkInOutCtx.height / 2);
                }
            } else {
                console.error('checkInOutTrendsChart canvas not found');
            }
        }

        // Function to check current attendance status from database
        function checkCurrentAttendanceStatus() {
            fetch('./controllers/attendance_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_today_status'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Attendance status checked:', data);
            })
            .catch(error => {
                console.error('Error checking attendance status:', error);
            });
        }
        
        function checkIn() {
            showMessage('Checking in...', 'info');
            
            fetch('./controllers/attendance_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=check_in'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage(data.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Check-in error:', error);
                showMessage('Error: ' + error.message, 'error');
            });
        }
        
        function checkOut() {
            showMessage('Checking out...', 'info');
            
            fetch('./controllers/attendance_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=check_out'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage(data.message + '. Work hours: ' + data.work_hours, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Check-out error:', error);
                showMessage('Error: ' + error.message, 'error');
            });
        }
        
        function loadReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            const formData = new FormData();
            formData.append('action', 'get_report');
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);
            
            fetch('./controllers/attendance_controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayReport(data.data);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Error: ' + error.message, 'error');
            });
        }
        
        function displayReport(data) {
            const tbody = document.getElementById('attendanceTableBody');
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No attendance records found</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.map(record => `
                <tr>
                    <td>${record.date}</td>
                    <td>${record.check_in } </td>
                    <td>${record.check_out ? record.check_out : '-'}</td>
                    <td>${record.work_hours || '-'}</td>
                    <td>
                        <span class="badge ${
                            record.status === 'present' ? 'badge-success' : 
                            record.status === 'late' ? 'badge-warning' : 
                            'badge-secondary'
                        }">
                            ${record.status}
                        </span>
                    </td>
                </tr>
            `).join('');
        }
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('attendanceMessage');
            
            const alertClass = type === 'success' ? 'alert-success' :
                              type === 'error' ? 'alert-danger' :
                              type === 'info' ? 'alert-info' :
                              'alert-warning';
            
            messageDiv.className = `alert ${alertClass}`;
            messageDiv.innerHTML = `
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' : 
                    type === 'error' ? 'fa-exclamation-circle' : 
                    type === 'info' ? 'fa-info-circle' : 
                    'fa-exclamation-triangle'
                } mr-2"></i>
                ${message}
            `;
            messageDiv.style.display = 'block';
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
