<?php
// Include necessary files
require_once __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/department_functions.php";
require_once __DIR__ . "/../../functions/user_functions.php";
require_once __DIR__ . "/../../functions/360_evaluation_functions.php";

// Get database connection
$conn = openConn();

// Check if user has permission to manage review assignments
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'system_admin' && $_SESSION['usertype'] !== 'hr_admin') {
    echo "<script>alert('Access denied. You do not have permission to manage review assignments.'); window.location.href='./';</script>";
    exit;
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

// Get review periods for dropdown
$periods = getAllReviewPeriods($conn);

// Get current period ID
$period_id = isset($_GET['period_id']) ? (int) $_GET['period_id'] : null;

// Get ALL assignments (will be filtered by JavaScript)
$allAssignments = getReviewAssignments($conn, null); // Get all assignments

// Get assignments for current period (for initial display)
if ($period_id) {
    $assignments = array_filter($allAssignments, function($assignment) use ($period_id) {
        return $assignment['review_period_id'] == $period_id;
    });
    $currentPeriod = getReviewPeriodById($conn, $period_id);
    $statistics = getAssignmentStatistics($conn, $period_id);
} else {
    $assignments = $allAssignments;
    $currentPeriod = null;
    $statistics = null;
}


?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users me-2"></i>Review Assignments</h2>
                    <div class="action-buttons">
                        <a href="./?page=review_periods" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Periods
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignReviewersModal">
                            <i class="fas fa-user-plus me-1"></i>Assign Reviewers
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Period Selection -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="period_filter" class="form-label">Select Review Period</label>
                                <select class="form-control" id="period_filter" name="period_filter" onchange="filterAssignments()">
                                    <option value="">All Periods</option>
                                    <?php foreach ($periods as $period): ?>
                                        <option value="<?php echo $period['id']; ?>" 
                                                <?php echo ($period_id == $period['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($period['period_name']); ?>
                                            (<?php echo date('M d, Y', strtotime($period['start_date'])); ?> - <?php echo date('M d, Y', strtotime($period['end_date'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6" id="period_info" style="display: none;">
                                <label class="form-label">&nbsp;</label>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Current Period:</strong> <span id="current_period_name"></span>
                                    (<span id="current_period_dates"></span>)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($period_id): ?>
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4 id="totalAssignments"><?php echo $statistics['total_assignments']; ?></h4>
                                    <p class="mb-0">Total Assignments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 id="pendingAssignments"><?php echo $statistics['by_status']['pending'] ?? 0; ?></h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 id="completedAssignments"><?php echo $statistics['by_status']['completed'] ?? 0; ?></h4>
                                    <p class="mb-0">Completed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 id="inProgressAssignments"><?php echo $statistics['by_status']['in_progress'] ?? 0; ?></h4>
                                    <p class="mb-0">In Progress</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Type Breakdown -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h4 id="selfReviews"><?php echo $statistics['by_type']['self'] ?? 0; ?></h4>
                                    <p class="mb-0">Self Reviews</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 id="managerReviews"><?php echo $statistics['by_type']['manager'] ?? 0; ?></h4>
                                    <p class="mb-0">Manager Reviews</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 id="peerReviews"><?php echo $statistics['by_type']['peer'] ?? 0; ?></h4>
                                    <p class="mb-0">Peer Reviews</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 id="subordinateReviews"><?php echo $statistics['by_type']['subordinate'] ?? 0; ?></h4>
                                    <p class="mb-0">Subordinate Reviews</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Assignments Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Review Assignments
                            <?php if ($period_id): ?>
                                <span class="badge bg-primary ms-2"><?php echo count($assignments); ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$period_id): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Select a review period to view assignments.</p>
                            </div>
                        <?php elseif (empty($assignments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No review assignments found for this period.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignReviewersModal">
                                    <i class="fas fa-user-plus me-1"></i>Assign Reviewers
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="60">ID</th>
                                            <th>Reviewee</th>
                                            <th>Department</th>
                                            <th>Reviewer</th>
                                            <th>Review Type</th>
                                            <th>Period</th>
                                            <th>Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="assignmentsTableBody">
                                        <?php foreach ($assignments as $assignment): ?>
                                            <tr data-period-id="<?php echo $assignment['review_period_id']; ?>" 
                                                data-status="<?php echo $assignment['status']; ?>"
                                                data-review-type="<?php echo $assignment['review_type']; ?>"
                                                data-department="<?php echo htmlspecialchars($assignment['department_name']); ?>">
                                                <td><?php echo $assignment['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($assignment['reviewee_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($assignment['department_name']); ?></span>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($assignment['reviewer_name']); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $typeLabels = [
                                                        'self' => 'Self',
                                                        'manager' => 'Manager',
                                                        'peer' => 'Peer',
                                                        'subordinate' => 'Subordinate'
                                                    ];
                                                    $typeColors = [
                                                        'self' => 'primary',
                                                        'manager' => 'success',
                                                        'peer' => 'info',
                                                        'subordinate' => 'warning'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $typeColors[$assignment['review_type']]; ?>">
                                                        <?php echo $typeLabels[$assignment['review_type']]; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($assignment['period_name']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'pending' => 'secondary',
                                                        'in_progress' => 'warning',
                                                        'completed' => 'success',
                                                        'expired' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusColors[$assignment['status']]; ?>">
                                                        <?php echo ucfirst($assignment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info" 
                                                                onclick="viewAssignment(<?php echo $assignment['id']; ?>)"
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-<?php echo $assignment['status'] === 'pending' ? 'success' : 'secondary'; ?>" 
                                                                onclick="updateStatus(<?php echo $assignment['id']; ?>, '<?php echo $assignment['status'] === 'pending' ? 'in_progress' : 'pending'; ?>')"
                                                                title="<?php echo $assignment['status'] === 'pending' ? 'Start Review' : 'Reset to Pending'; ?>">
                                                            <i class="fas fa-<?php echo $assignment['status'] === 'pending' ? 'play' : 'undo'; ?>"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deleteAssignment(<?php echo $assignment['id']; ?>)"
                                                                title="Delete Assignment">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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

    <!-- Assign Reviewers Modal -->
    <div class="modal fade" id="assignReviewersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Assign Reviewers
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="./controllers/360_evaluation_controller.php">
                    <input type="hidden" name="action" value="assign_reviewers">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select User *</label>
                                    <select class="form-control" id="user_id" name="user_id" required>
                                        <option value="">Select User</option>
                                        <?php 
                                        // Get all active users for selection
                                        $users = selectAllUsers($conn);
                                        foreach ($users as $user) {
                                            echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['names']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <small class="form-text text-muted">Select the user to assign reviewers for</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="period_id" class="form-label">Review Period *</label>
                                    <select class="form-control" id="period_id" name="review_period_id" required>
                                        <option value="">Select Period</option>
                                        <?php foreach ($periods as $period): ?>
                                            <option value="<?php echo $period['id']; ?>">
                                                <?php echo htmlspecialchars($period['period_name']); ?>
                                                (<?php echo date('M d, Y', strtotime($period['start_date'])); ?> - <?php echo date('M d, Y', strtotime($period['end_date'])); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Select the review period</small>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Automatic Assignment:</strong> The system will automatically assign:
                            <ul class="mb-0">
                                <li><strong>Self Review:</strong> User evaluates themselves</li>
                                <li><strong>Manager Review:</strong> Their direct manager (if assigned)</li>
                                <li><strong>Peer Reviews:</strong> 2-3 colleagues from same department/level</li>
                                <li><strong>Subordinate Reviews:</strong> People they manage (if applicable)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-users me-1"></i>Assign Reviewers
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store all periods data
        const allPeriods = {
            <?php foreach ($periods as $period): ?>
                <?php echo $period['id']; ?>: {
                    id: <?php echo $period['id']; ?>,
                    period_name: '<?php echo addslashes($period['period_name']); ?>',
                    start_date: '<?php echo $period['start_date']; ?>',
                    end_date: '<?php echo $period['end_date']; ?>'
                }
            <?php endforeach; ?>
        };
        
        // Store all assignments data
        const allAssignments = {
            <?php foreach ($allAssignments as $assignment): ?>
                <?php echo $assignment['id']; ?>: {
                    id: <?php echo $assignment['id']; ?>,
                    review_period_id: <?php echo $assignment['review_period_id']; ?>,
                    reviewee_name: '<?php echo addslashes($assignment['reviewee_name']); ?>',
                    reviewer_name: '<?php echo addslashes($assignment['reviewer_name']); ?>',
                    review_type: '<?php echo $assignment['review_type']; ?>',
                    status: '<?php echo $assignment['status']; ?>',
                    period_name: '<?php echo addslashes($assignment['period_name']); ?>',
                    department_name: '<?php echo addslashes($assignment['department_name']); ?>'
                }
            <?php endforeach; ?>
        };
        
        // Filter assignments function
        function filterAssignments() {
            const periodId = document.getElementById('period_filter').value;
            const periodInfo = document.getElementById('period_info');
            const periodNameSpan = document.getElementById('current_period_name');
            const periodDatesSpan = document.getElementById('current_period_dates');
            
            if (periodId && allPeriods[periodId]) {
                // Show period info
                periodInfo.style.display = 'block';
                const period = allPeriods[periodId];
                periodNameSpan.textContent = period.period_name;
                periodDatesSpan.textContent = new Date(period.start_date).toLocaleDateString() + ' - ' + new Date(period.end_date).toLocaleDateString();
                
                // Filter assignments table
                const rows = document.querySelectorAll('#assignmentsTableBody tr');
                let visibleCount = 0;
                let statusCounts = { pending: 0, in_progress: 0, completed: 0 };
                let typeCounts = { self: 0, manager: 0, peer: 0, subordinate: 0 };
                
                rows.forEach(row => {
                    const rowPeriodId = row.getAttribute('data-period-id');
                    
                    if (periodId === '' || rowPeriodId === periodId) {
                        row.style.display = '';
                        visibleCount++;
                        
                        // Count statistics for visible rows
                        const status = row.getAttribute('data-status');
                        const type = row.getAttribute('data-review-type');
                        
                        statusCounts[status] = (statusCounts[status] || 0) + 1;
                        typeCounts[type] = (typeCounts[type] || 0) + 1;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Update statistics
                updateStatistics(visibleCount, statusCounts, typeCounts);
            } else {
                // Show all assignments
                const rows = document.querySelectorAll('#assignmentsTableBody tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
                
                // Calculate statistics for all assignments
                let totalCount = 0;
                let statusCounts = { pending: 0, in_progress: 0, completed: 0 };
                let typeCounts = { self: 0, manager: 0, peer: 0, subordinate: 0 };
                
                Object.values(allAssignments).forEach(assignment => {
                    totalCount++;
                    statusCounts[assignment.status] = (statusCounts[assignment.status] || 0) + 1;
                    typeCounts[assignment.review_type] = (typeCounts[assignment.review_type] || 0) + 1;
                });
                
                updateStatistics(totalCount, statusCounts, typeCounts);
                
                // Hide period info
                periodInfo.style.display = 'none';
            }
        }
        
        // Update statistics function
        function updateStatistics(total, statusCounts, typeCounts) {
            document.getElementById('totalAssignments').textContent = total;
            document.getElementById('pendingAssignments').textContent = statusCounts.pending || 0;
            document.getElementById('completedAssignments').textContent = statusCounts.completed || 0;
            document.getElementById('inProgressAssignments').textContent = statusCounts.in_progress || 0;
            document.getElementById('selfReviews').textContent = typeCounts.self || 0;
            document.getElementById('managerReviews').textContent = typeCounts.manager || 0;
            document.getElementById('peerReviews').textContent = typeCounts.peer || 0;
            document.getElementById('subordinateReviews').textContent = typeCounts.subordinate || 0;
        }
        
        // View assignment details
        function viewAssignment(assignmentId) {
            // This would show detailed assignment information
            alert('View assignment details functionality would be implemented here for assignment ID: ' + assignmentId);
        }
        
        // Update assignment status
        function updateStatus(assignmentId, newStatus) {
            if (confirm('Are you sure you want to change status to ' + newStatus + '?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '../controllers/360_evaluation_controller.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_assignment_status">
                    <input type="hidden" name="review_id" value="${assignmentId}">
                    <input type="hidden" name="status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Delete assignment
        function deleteAssignment(assignmentId) {
            if (confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '../controllers/360_evaluation_controller.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_assignment">
                    <input type="hidden" name="review_id" value="${assignmentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial filter state
            filterAssignments();
        });
    </script>
