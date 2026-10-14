<?php
// Get staff ID and type from URL
$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
$staff_type = isset($_GET['staff_type']) ? $_GET['staff_type'] : 'users';

// Get user data based on staff type
if ($staff_type == 'subsidiaries') {
    // Get staff from subsidiaries table
    $query = "SELECT id, name as names, email, phone, 'Subsidiary' as usertype, 'active' as status FROM subsidiaries WHERE id = $staff_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
} else {
    // Get staff from users table
    $user = selectUserById($conn, $staff_id);
}

// Get leave requests based on staff type
$query = "SELECT * FROM leave_request 
          WHERE staff_id = $staff_id AND staff_type = '$staff_type' 
          ORDER BY request_date DESC";
$leaveRequestsResult = mysqli_query($conn, $query);
$leaveRequests = [];

if ($leaveRequestsResult) {
    while ($row = mysqli_fetch_assoc($leaveRequestsResult)) {
        $leaveRequests[] = $row;
    }
}

// Check if user exists
if(!is_array($user)) {
    echo '<div class="alert alert-danger">User not found or database error occurred.</div>';
    exit();
}

// User type badge color
$userTypeColors = [
    'staff' => 'primary',
    'Manager' => 'success',
    'reception' => 'info',
    'accountant' => 'warning',
    'director' => 'danger',
    'loan_officer' => 'secondary',
    'sales_manager' => 'dark',
    'sales_person' => 'light text-dark',
    'Admin' => 'danger',
    'IT' => 'info',
    'Subsidiary' => 'primary' // Added for subsidiaries
];

$userTypeColor = isset($userTypeColors[$user['usertype']]) ? $userTypeColors[$user['usertype']] : 'secondary';

// Rest of the code remains the same...
                  
                  // Status badge color
                  $statusColors = [
                      'active' => 'success',
                      'suspended' => 'warning',
                      'deleted' => 'danger'
                  ];
                  
                  $statusColor = isset($statusColors[$user['status']]) ? $statusColors[$user['status']] : 'secondary';
                  
                  // Leave request status badge colors
                  $leaveStatusColors = [
                      'pending' => 'warning',
                      'approved' => 'success',
                      'rejected' => 'danger',
                      'closed' => 'secondary'
                  ];
              ?>
                  <!-- Staff Profile Header -->
                  <div class="row mb-4">
                      <div class="col-md-12">
                          <div class="card border-0 shadow-sm">
                              <div class="card-body p-4">
                                  <div class="row align-items-center">
                                      <div class="col-auto">
                                          <div class="bg-primary text-white rounded-circle p-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                                              <?php echo strtoupper(substr($user['names'], 0, 1)); ?>
                                          </div>
                                      </div>
                                      <div class="col">
                                          <h2 class="mb-1"><?php echo htmlspecialchars($user['names']); ?></h2>
                                          <div class="d-flex flex-wrap">
                                              <span class="badge badge-<?php echo $userTypeColor; ?> mr-2 p-2">
                                                  <i class="fas fa-id-badge mr-1"></i> <?php echo htmlspecialchars($user['usertype']); ?>
                                              </span>
                                              <span class="badge badge-<?php echo $statusColor; ?> mr-2 p-2">
                                                  <i class="fas fa-circle mr-1"></i> <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                              </span>
                                              <span class="badge badge-light p-2">
                                                  <i class="fas fa-hashtag mr-1"></i> ID: <?php echo $user['id']; ?>
                                              </span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  
                  <!-- Staff Information and Leave Summary -->
                  <div class="row mb-4">
                      <!-- Staff Information -->
                      <div class="col-md-6 mb-4 mb-md-0">
                          <div class="card border-0 shadow-sm h-100">
                              <div class="card-header bg-white">
                                  <h5 class="card-title mb-0">
                                      <i class="fas fa-user-circle text-primary mr-2"></i> Staff Information
                                  </h5>
                              </div>
                              <div class="card-body">
                                  <div class="list-group list-group-flush">
                                      <div class="list-group-item px-0">
                                          <div class="d-flex w-100 justify-content-between">
                                              <h6 class="mb-1 text-muted">Full Name</h6>
                                              <span class="text-primary font-weight-bold"><?php echo htmlspecialchars($user['names']); ?></span>
                                          </div>
                                      </div>
                                      <div class="list-group-item px-0">
                                          <div class="d-flex w-100 justify-content-between">
                                              <h6 class="mb-1 text-muted">Email Address</h6>
                                              <span class="text-primary"><?php echo htmlspecialchars($user['email']); ?></span>
                                          </div>
                                      </div>
                                      <div class="list-group-item px-0">
                                          <div class="d-flex w-100 justify-content-between">
                                              <h6 class="mb-1 text-muted">Phone Number</h6>
                                              <span class="text-primary"><?php echo htmlspecialchars($user['phone']); ?></span>
                                          </div>
                                      </div>
                                      <div class="list-group-item px-0">
                                          <div class="d-flex w-100 justify-content-between">
                                              <h6 class="mb-1 text-muted">Role</h6>
                                              <span class="badge badge-<?php echo $userTypeColor; ?> p-2"><?php echo htmlspecialchars($user['usertype']); ?></span>
                                          </div>
                                      </div>
                                      <div class="list-group-item px-0">
                                          <div class="d-flex w-100 justify-content-between">
                                              <h6 class="mb-1 text-muted">Account Status</h6>
                                              <span class="badge badge-<?php echo $statusColor; ?> p-2"><?php echo ucfirst(htmlspecialchars($user['status'])); ?></span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                      
                      <!-- Leave Summary -->
                      <div class="col-md-6">
                          <div class="card border-0 shadow-sm h-100">
                              <div class="card-header bg-white">
                                  <h5 class="card-title mb-0">
                                      <i class="fas fa-calendar-check text-primary mr-2"></i> Leave Summary
                                  </h5>
                              </div>
                              <div class="card-body">
                                  <?php
                                  // Count leave requests by status
                                  $pending = $approved = $rejected = $closed = 0;
                                  
                                  if(is_array($leaveRequests)) {
                                      foreach($leaveRequests as $request) {
                                          switch($request['status']) {
                                              case 'pending':
                                                  $pending++;
                                                  break;
                                              case 'approved':
                                                  $approved++;
                                                  break;
                                              case 'rejected':
                                                  $rejected++;
                                                  break;
                                              case 'closed':
                                                  $closed++;
                                                  break;
                                          }
                                      }
                                  }
                                  
                                  $totalRequests = $pending + $approved + $rejected + $closed;
                                  ?>
                                  
                                  <div class="row text-center mb-4">
                                      <div class="col-3">
                                          <div class="card border-0 bg-light h-100">
                                              <div class="card-body py-3">
                                                  <h3 class="text-warning mb-0"><?php echo $pending; ?></h3>
                                                  <small class="text-muted">Pending</small>
                                              </div>
                                          </div>
                                      </div>
                                      <div class="col-3">
                                          <div class="card border-0 bg-light h-100">
                                              <div class="card-body py-3">
                                                  <h3 class="text-success mb-0"><?php echo $approved; ?></h3>
                                                  <small class="text-muted">Approved</small>
                                              </div>
                                          </div>
                                      </div>
                                      <div class="col-3">
                                          <div class="card border-0 bg-light h-100">
                                              <div class="card-body py-3">
                                                  <h3 class="text-danger mb-0"><?php echo $rejected; ?></h3>
                                                  <small class="text-muted">Rejected</small>
                                              </div>
                                          </div>
                                      </div>
                                      <div class="col-3">
                                          <div class="card border-0 bg-light h-100">
                                              <div class="card-body py-3">
                                                  <h3 class="text-secondary mb-0"><?php echo $closed; ?></h3>
                                                  <small class="text-muted">Closed</small>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                                  
                                  <h6 class="text-center text-muted mb-3">Total Leave Requests: <?php echo $totalRequests; ?></h6>
                                  
                                  <?php if($totalRequests > 0): ?>
                                  <div class="progress" style="height: 25px;">
                                      <?php if($pending > 0): ?>
                                      <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($pending/$totalRequests)*100; ?>%" 
                                          title="Pending: <?php echo $pending; ?>">
                                          <?php echo round(($pending/$totalRequests)*100); ?>%
                                      </div>
                                      <?php endif; ?>
                                      
                                      <?php if($approved > 0): ?>
                                      <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($approved/$totalRequests)*100; ?>%" 
                                          title="Approved: <?php echo $approved; ?>">
                                          <?php echo round(($approved/$totalRequests)*100); ?>%
                                      </div>
                                      <?php endif; ?>
                                      
                                      <?php if($rejected > 0): ?>
                                      <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($rejected/$totalRequests)*100; ?>%" 
                                          title="Rejected: <?php echo $rejected; ?>">
                                          <?php echo round(($rejected/$totalRequests)*100); ?>%
                                      </div>
                                      <?php endif; ?>
                                      
                                      <?php if($closed > 0): ?>
                                      <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo ($closed/$totalRequests)*100; ?>%" 
                                          title="Closed: <?php echo $closed; ?>">
                                          <?php echo round(($closed/$totalRequests)*100); ?>%
                                      </div>
                                      <?php endif; ?>
                                  </div>
                                  <?php else: ?>
                                  <div class="alert alert-info text-center">
                                      No leave requests found for this staff member.
                                  </div>
                                  <?php endif; ?>
                              </div>
                          </div>
                      </div>
                  </div>
                  
                  <!-- Leave Request History -->
                  <div class="row">
                      <div class="col-md-12">
                          <div class="card border-0 shadow-sm">
                              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                  <h5 class="card-title mb-0">
                                      <i class="fas fa-history text-primary mr-2"></i> Leave Request History
                                  </h5>
                                  <a href="?page=leave_reports" class="btn btn-sm btn-outline-primary">
                                      <i class="fas fa-arrow-left mr-1"></i> Back to Reports
                                  </a>
                              </div>
                              <div class="card-body">
                                  <div class="table-responsive">
                                      <table class="table table-hover">
                                          <thead class="thead-light">
                                              <tr>
                                                  <th>#</th>
                                                  <th>Request Date</th>
                                                  <th>Leave Type</th>
                                                  <th>Start Date</th>
                                                  <th>End Date</th>
                                                  <th>Days</th>
                                                  <th>Status</th>
                                                  <th>Approved Dates</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                              <?php
                                              if(is_array($leaveRequests) && count($leaveRequests) > 0) {
                                                  // Get leave types
                                                  $leaveTypesQuery = "SELECT id, type_name FROM leave_types";
                                                  $leaveTypesResult = mysqli_query($conn, $leaveTypesQuery);
                                                  $leaveTypes = [];
                                                  
                                                  if($leaveTypesResult) {
                                                      while($row = mysqli_fetch_assoc($leaveTypesResult)) {
                                                          $leaveTypes[$row['id']] = $row['type_name'];
                                                      }
                                                  }
                                                  
                                                  $counter = 1;
                                                  foreach($leaveRequests as $request) {
                                                      $leaveType = isset($leaveTypes[$request['type_id']]) ? $leaveTypes[$request['type_id']] : 'Unknown';
                                                      $statusColor = isset($leaveStatusColors[$request['status']]) ? $leaveStatusColors[$request['status']] : 'secondary';
                                                      
                                                      echo '<tr>';
                                                      echo '<td>'.$counter.'</td>';
                                                      echo '<td>'.date('d-M-Y', strtotime($request['request_date'])).'</td>';
                                                      echo '<td>'.$leaveType.'</td>';
                                                      echo '<td>'.date('d-M-Y', strtotime($request['date_of_leaving'])).'</td>';
                                                      echo '<td>'.date('d-M-Y', strtotime($request['date_of_returning'])).'</td>';
                                                      echo '<td>'.$request['number_of_days_requested'].'</td>';
                                                      echo '<td><span class="badge badge-'.$statusColor.' p-2">'.ucfirst($request['status']).'</span></td>';
                                                      
                                                      if($request['status'] == 'approved' || $request['status'] == 'closed') {
                                                          echo '<td>
                                                              <small class="d-block">From: '.date('d-M-Y', strtotime($request['approved_start_leave_date'])).'</small>
                                                              <small class="d-block">To: '.date('d-M-Y', strtotime($request['approved_end_of_leave_date'])).'</small>
                                                              <small class="d-block">Days: '.$request['approved_number_of_days'].'</small>
                                                          </td>';
                                                      } else {
                                                          echo '<td class="text-muted">N/A</td>';
                                                      }
                                                      
                                                      echo '</tr>';
                                                      $counter++;
                                                  }
                                              } else {
                                                  echo '<tr><td colspan="8" class="text-center">No leave requests found for this staff member</td></tr>';
                                              }
                                              ?>
                                          </tbody>
                                      </table>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              
              