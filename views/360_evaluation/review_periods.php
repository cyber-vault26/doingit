<?php
// Include necessary files
require_once __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/department_functions.php";
require_once __DIR__ . "/../../functions/user_functions.php";
require_once __DIR__ . "/../../functions/360_evaluation_functions.php";

// Get database connection
$conn = openConn();

// Check if user has permission to manage review periods
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'system_admin' && $_SESSION['usertype'] !== 'hr_admin') {
    echo "<script>alert('Access denied. You do not have permission to manage review periods.'); window.location.href='./';</script>";
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

// Get all review periods
$periods = getAllReviewPeriods($conn);

// Get active review period
$activePeriod = getActiveReviewPeriod($conn);

$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-calendar-alt me-2"></i>Review Periods</h2>
                    <div class="action-buttons">
                        <a href="./?page=360_dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                            <i class="fas fa-plus me-1"></i>Add Review Period
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

                <!-- Active Period Alert -->
                <?php if ($activePeriod): ?>
                    <div class="alert alert-info alert-dismissible fade show">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Currently Active:</strong> <?php echo htmlspecialchars($activePeriod['period_name']); ?>
                        (<?php echo date('M d, Y', strtotime($activePeriod['start_date'])); ?> - <?php echo date('M d, Y', strtotime($activePeriod['end_date'])); ?>)
                    </div>
                <?php endif; ?>

                <!-- Review Periods List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Review Periods
                            <span class="badge bg-primary ms-2"><?php echo count($periods); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($periods)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No review periods found. Create your first review period above.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="60">ID</th>
                                            <th>Period Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Total Reviews</th>
                                            <th>Completed</th>
                                            <th>Completion Rate</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($periods as $period): ?>
                                            <tr>
                                                <td><?php echo $period['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($period['period_name']); ?></strong>
                                                    <?php if ($period['description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($period['description']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($period['start_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($period['end_date'])); ?></td>
                                                <td>
                                                    <?php if ($period['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $period['total_reviews'] ?? 0; ?></td>
                                                <td><?php echo $period['completed_reviews'] ?? 0; ?></td>
                                                <td>
                                                    <?php 
                                                    $completionRate = 0;
                                                    if (isset($period['total_reviews']) && $period['total_reviews'] > 0) {
                                                        $completionRate = round(($period['completed_reviews'] / $period['total_reviews']) * 100, 1);
                                                    }
                                                    ?>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?php echo $completionRate >= 80 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger'); ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $completionRate; ?>%">
                                                            <?php echo $completionRate; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info" 
                                                                onclick="viewPeriod(<?php echo $period['id']; ?>)"
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning" 
                                                                onclick="editPeriod(<?php echo $period['id']; ?>)"
                                                                title="Edit Period">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-<?php echo $period['is_active'] ? 'secondary' : 'success'; ?>" 
                                                                onclick="togglePeriod(<?php echo $period['id']; ?>, <?php echo $period['is_active'] ? 0 : 1; ?>)"
                                                                title="<?php echo $period['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                            <i class="fas fa-<?php echo $period['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deletePeriod(<?php echo $period['id']; ?>)"
                                                                title="Delete Period">
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

    <!-- Add Period Modal -->
    <div class="modal fade" id="addPeriodModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add Review Period
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="./controllers/360_evaluation_controller.php">
                    <input type="hidden" name="action" value="create_review_period">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="period_name" class="form-label">Period Name *</label>
                                    <input type="text" class="form-control" id="period_name" name="period_name" required
                                           placeholder="e.g., Q1 2026, Annual 2026">
                                    <small class="form-text text-muted">Enter a descriptive name for the review period</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                                        <label class="form-check-label" for="is_active">
                                            Active (immediately available for evaluations)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    <small class="form-text text-muted">When the review period begins</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    <small class="form-text text-muted">When the review period ends</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                              placeholder="Optional description of the review period"></textarea>
                                    <small class="form-text text-muted">Optional additional information about this review period</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Period
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Period Modal -->
    <div class="modal fade" id="editPeriodModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Review Period
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="./controllers/360_evaluation_controller.php" id="editPeriodForm">
                    <input type="hidden" name="action" value="update_review_period">
                    <input type="hidden" name="period_id" id="edit_period_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_period_name" class="form-label">Period Name *</label>
                                    <input type="text" class="form-control" id="edit_period_name" name="period_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_is_active" class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                        <label class="form-check-label" for="edit_is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">End Date *</label>
                                    <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Period
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
                    end_date: '<?php echo $period['end_date']; ?>',
                    is_active: <?php echo $period['is_active']; ?>,
                    description: '<?php echo addslashes($period['description'] ?? ''); ?>',
                    total_reviews: <?php echo $period['total_reviews'] ?? 0; ?>,
                    completed_reviews: <?php echo $period['completed_reviews'] ?? 0; ?>
                }
            <?php endforeach; ?>
        };
        
        // View period details
        function viewPeriod(periodId) {
            const period = allPeriods[periodId];
            if (period) {
                const completionRate = period.total_reviews > 0 ? 
                    Math.round((period.completed_reviews / period.total_reviews) * 100 * 10) / 10 : 0;
                
                let content = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Period ID:</strong> ${period.id}<br>
                            <strong>Period Name:</strong> ${period.period_name}<br>
                            <strong>Status:</strong> <span class="badge bg-${period.is_active ? 'success' : 'secondary'}">${period.is_active ? 'Active' : 'Inactive'}</span><br>
                            <strong>Start Date:</strong> ${new Date(period.start_date).toLocaleDateString()}<br>
                            <strong>End Date:</strong> ${new Date(period.end_date).toLocaleDateString()}
                        </div>
                        <div class="col-md-6">
                            <strong>Total Reviews:</strong> ${period.total_reviews}<br>
                            <strong>Completed Reviews:</strong> ${period.completed_reviews}<br>
                            <strong>Completion Rate:</strong> ${completionRate}%<br>
                            <strong>Duration:</strong> ${Math.ceil((new Date(period.end_date) - new Date(period.start_date)) / (1000 * 60 * 60 * 24))} days
                        </div>
                        ${period.description ? `
                        <div class="col-12 mt-3">
                            <strong>Description:</strong><br>
                            <div class="alert alert-light">
                                ${period.description}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                document.getElementById('periodDetails').innerHTML = content;
                var modal = new bootstrap.Modal(document.getElementById('viewPeriodModal'));
                modal.show();
            }
        }
        
        // Edit period
        function editPeriod(periodId) {
            const period = allPeriods[periodId];
            if (period) {
                document.getElementById('edit_period_id').value = period.id;
                document.getElementById('edit_period_name').value = period.period_name;
                document.getElementById('edit_start_date').value = period.start_date;
                document.getElementById('edit_end_date').value = period.end_date;
                document.getElementById('edit_description').value = period.description;
                document.getElementById('edit_is_active').checked = period.is_active;
                
                var modal = new bootstrap.Modal(document.getElementById('editPeriodModal'));
                modal.show();
            }
        }
        
        // Toggle period status
        function togglePeriod(periodId, newStatus) {
            if (confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this review period?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = './controllers/360_evaluation_controller.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_review_period">
                    <input type="hidden" name="period_id" value="${periodId}">
                    <input type="hidden" name="is_active" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Delete period
        function deletePeriod(periodId) {
            if (confirm('Are you sure you want to delete this review period? This action cannot be undone.')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = './controllers/360_evaluation_controller.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_review_period">
                    <input type="hidden" name="period_id" value="${periodId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Form validation
        document.getElementById('addPeriodModal').querySelector('form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('End date must be after start date.');
                return false;
            }
        });
        
        document.getElementById('editPeriodForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('edit_start_date').value);
            const endDate = new Date(document.getElementById('edit_end_date').value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('End date must be after start date.');
                return false;
            }
        });
    </script>

    <!-- View Period Details Modal -->
    <div class="modal fade" id="viewPeriodModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Review Period Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="periodDetails">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
