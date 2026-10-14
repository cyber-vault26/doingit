<?php
// Include necessary files

require_once __DIR__ .  "/../../functions/leave_functions.php";


// Get database connection
$conn = openConn();

// Check if user has permission to view leave list
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'Manager' ) {
    echo "<script>alert('Access denied. You do not have permission to view leave list.'); window.location.href='./';</script>";
    exit;
}else{
  //  echo $_SESSION['usertype'];
}

// Handle form submissions - just display messages from controller
$message = '';
$error = '';

// Get success/error messages from URL parameters
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// Get all leave requests
$leaveRequests = getLeaveRequest($conn);
$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-calendar-alt me-2"></i>Leave Requests List</h2>
                    <div class="action-buttons">
                        <a href="./?page=leave_policies_list" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Policies
                        </a>
                    </div>
                </div>

              

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count($leaveRequests); ?></h4>
                                <p class="mb-0">Total Requests</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($leaveRequests, fn($r) => $r['status'] === 'pending')); ?></h4>
                                <p class="mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($leaveRequests, fn($r) => $r['status'] === 'approved')); ?></h4>
                                <p class="mb-0">Approved</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($leaveRequests, fn($r) => $r['status'] === 'rejected')); ?></h4>
                                <p class="mb-0">Rejected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Requests Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>All Leave Requests
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($leaveRequests)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No leave requests found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="60">ID</th>
                                            <th>Employee</th>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Days</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leaveRequests as $request):  ?>
                                            <tr>
                                                <td><?php echo $request['id']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($request['user']); ?></strong>
                                                        
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($request['leave_type_name']); ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($request['start_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($request['end_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $request['total_days']; ?></span>
                                                    <!-- <?php if ($request['half_day']): ?>
                                                        <small class="text-warning">(Half Day)</small>
                                                    <?php endif; ?> -->
                                                </td>
                                                <td>
                                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                        <?php echo htmlspecialchars(substr($request['reason'], 0, 50)); ?>
                                                        <?php if (strlen($request['reason']) > 50): ?>
                                                            <span class="text-muted">...</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'pending' => 'bg-warning',
                                                        'approved' => 'bg-success',
                                                        'department_approved' => 'bg-info',
                                                        'rejected' => 'bg-danger'
                                                    ];
                                                    $status = $request['status'];
                                                    ?>
                                                    <span class="badge <?php echo $statusClass[$status] ?? 'bg-secondary'; ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                    <?php if ($request['rejection_reason']): ?>
                                                        <br><small class="text-danger"><?php echo htmlspecialchars($request['rejection_reason']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#viewDetailsModal"
                                                                data-leave-id="<?php echo $request['id']; ?>"
                                                                data-leave-user="<?php echo $request['user']; ?>"
                                                                data-leave-leave-type="<?php echo $request['leave_type_name']; ?>"
                                                                data-leave-start-date="<?php echo $request['start_date']; ?>"
                                                                data-leave-end-date="<?php echo $request['end_date']; ?>"
                                                                data-leave-total-days="<?php echo $request['total_days']; ?>"
                                                                data-leave-reason="<?php echo $request['reason']; ?>"
                                                                data-leave-status="<?php echo $request['status']; ?>"
                                                                data-leave-rejection-reason="<?php echo $request['rejection_reason']; ?>"
                                                                data-leave-start-time="<?php echo $request['start_time']; ?>"
                                                                data-leave-end-time="<?php echo $request['end_time']; ?>"
                                                                data-leave-attachment-path="<?php echo $request['attachment_path']; ?>"
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        <?php if ($request['status'] === 'department_approved'): ?>
                                                            <a type="button" class="btn btn-outline-success" 
                                                                    href="./controllers/leave_controller.php?action=approve_leave&leave_id=<?php echo $request['id']; ?>&status=hr_approved"
                                                                    title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <a type="button" class="btn btn-outline-danger" 
                                                                    href="./controllers/leave_controller.php?action=approve_leave&leave_id=<?php echo $request['id']; ?>&status=hr_rejected"
                                                                    title="Reject">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Leave Request Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Reject Leave Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="rejectForm">
                    <input type="hidden" name="action" value="reject_leave">
                    <input type="hidden" id="reject_leave_id" name="leave_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason *</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" 
                                      placeholder="Please provide reason for rejection..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i>Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewDetailsBtn = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#viewDetailsModal"]');
            viewDetailsBtn.forEach(btn => {
                btn.addEventListener('click', function() {
                    const leaveId = this.getAttribute('data-leave-id');
                    const leaveUser = this.getAttribute('data-leave-user');
                    const leaveLeaveType = this.getAttribute('data-leave-leave-type');
                    const leaveStartDate = this.getAttribute('data-leave-start-date');
                    const leaveEndDate = this.getAttribute('data-leave-end-date');
                    const leaveTotalDays = this.getAttribute('data-leave-total-days');
                    const leaveReason = this.getAttribute('data-leave-reason');
                    const leaveStatus = this.getAttribute('data-leave-status');
                    const leaveRejectionReason = this.getAttribute('data-leave-rejection-reason');
                    const leaveStartTime = this.getAttribute('data-leave-start-time');
                    const leaveEndTime = this.getAttribute('data-leave-end-time');
                    const leaveAttachmentPath = this.getAttribute('data-leave-attachment-path');
                    
                    // Update modal content
                    document.getElementById('detailsContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>User:</strong> ${leaveUser}</p>
                                <p><strong>Leave Type:</strong> ${leaveLeaveType}</p>
                                <p><strong>Start Date:</strong> ${leaveStartDate}</p>
                                <p><strong>End Date:</strong> ${leaveEndDate}</p>
                                <p><strong>Total Days:</strong> ${leaveTotalDays}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Reason:</strong> ${leaveReason}</p>
                                <p><strong>Status:</strong> ${leaveStatus}</p>
                                <p><strong>Rejection Reason:</strong> ${leaveRejectionReason}</p>
                                <p><strong>Start Time:</strong> ${leaveStartTime}</p>
                                <p><strong>End Time:</strong> ${leaveEndTime}</p>
                                <p><strong>Attachment:</strong> ${leaveAttachmentPath}</p>
                            </div>
                        </div>
                    `;
                });
            });
        });
    </script>

    