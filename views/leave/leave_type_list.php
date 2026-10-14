<?php
// Include necessary files
require_once  __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/leave_functions.php";


// Get database connection
$conn = openConn();

// Get all leave types
$leaveTypes = getAllLeaveTypes($conn);

// Close connection
$conn->close();
?>


    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-list me-2"></i>Leave Types</h2>
                    <div class="action-buttons">
                        <button class="btn btn-success" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <?php if ($_SESSION['usertype'] === 'hr_admin' || $_SESSION['usertype'] === 'system_admin'){ ?>
                            <button class="btn btn-primary" onclick="window.location.href='../admin/leave_types_manage.php'">
                                <i class="fas fa-cog me-1"></i>Manage
                            </button>
                        <?php } ?>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo count($leaveTypes); ?></h4>
                                        <p class="card-text">Total Leave Types</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-list fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">
                                            <?php 
                                            $paidCount = 0;
                                            foreach ($leaveTypes as $type) {
                                                if ($type['is_paid']){ $paidCount++;}
                                            }
                                            echo $paidCount;
                                            ?>
                                        </h4>
                                        <p class="card-text">Paid Leave Types</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-money-bill fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">
                                            <?php 
                                            $docRequiredCount = 0;
                                            foreach ($leaveTypes as $type) {
                                                if ($type['requires_documentation']){ $docRequiredCount++;}
                                            }
                                            echo $docRequiredCount;
                                            ?>
                                        </h4>
                                        <p class="card-text">Require Documentation</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-alt fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">
                                            <?php 
                                            $accruableCount = 0;
                                            foreach ($leaveTypes as $type) {
                                                if ($type['is_accruable']){ $accruableCount++;}
                                            }
                                            echo $accruableCount;
                                            ?>
                                        </h4>
                                        <p class="card-text">Accruable Types</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Types Grid -->
                <div class="stats-grid">
                    <?php foreach ($leaveTypes as $type): ?>
                        <div class="card leave-type-card h-100" 
                             style="border-left-color: <?php echo $type['color_code']; ?>;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title">
                                            <span class="color-indicator" 
                                                  style="background-color: <?php echo $type['color_code']; ?>;"></span>
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </h5>
                                        <span class="badge bg-<?php echo $type['is_paid'] ? 'success' : 'secondary'; ?> me-2">
                                            <?php echo $type['is_paid'] ? 'Paid' : 'Unpaid'; ?>
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge-days badge bg-light text-dark">
                                            <?php echo $type['max_days_per_year']; ?> days
                                        </span>
                                    </div>
                                </div>

                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($type['description'] ?: 'No description available'); ?>
                                </p>

                                <!-- Features -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Notice: <?php echo $type['min_advance_notice_days']; ?> days
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            <?php echo $type['max_consecutive_days'] ? 'Max: ' . $type['max_consecutive_days'] . ' days' : 'No limit'; ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <?php if ($type['requires_documentation']){ ?>
                                                <i class="fas fa-file-alt me-1 text-warning"></i>
                                                <span class="text-warning">Documentation Required</span>
                                            <?php } else { ?>
                                                <i class="fas fa-file-alt me-1"></i>
                                                No Documentation
                                            <?php } ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <?php if ($type['is_accruable']){ ?>
                                                <i class="fas fa-plus-circle me-1 text-success"></i>
                                                <span class="text-success">Accruable</span>
                                            <?php } else { ?>
                                                <i class="fas fa-times-circle me-1"></i>
                                                Not Accruable
                                            <?php } ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <?php if ($type['is_carry_over_allowed']) { ?>
                                                <i class="fas fa-exchange-alt me-1 text-info"></i>
                                                <span class="text-info">Carry Over: <?php echo $type['max_carry_over_days']; ?> days</span>
                                            <?php } else { ?>
                                                <i class="fas fa-ban me-1"></i>
                                                No Carry Over
                                            <?php } ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <?php if ($type['requires_approval']) { ?>
                                                <i class="fas fa-user-check me-1 text-primary"></i>
                                                Approval Required
                                            <?php } else { ?>
                                                <i class="fas fa-check-circle me-1 text-success"></i>
                                                Auto-Approved
                                            <?php } ?>
                                        </small>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewDetails(<?php echo $type['id']; ?>)">
                                        <i class="fas fa-eye me-1"></i>Details
                                    </button>
                                    <?php if ($_SESSION['usertype'] === 'hr_admin' || $_SESSION['usertype'] === 'system_admin'): ?>
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="editType(<?php echo $type['id']; ?>)">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Detailed Table View -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Leave Type</th>
                                <th>Max Days/Year</th>
                                <th>Notice Required</th>
                                <th>Paid</th>
                                <th>Documentation</th>
                                <th>Accruable</th>
                                <th>Carry Over</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaveTypes as $type): ?>
                                <tr>
                                    <td>
                                        <span class="color-indicator" 
                                              style="background-color: <?php echo $type['color_code']; ?>;"></span>
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $type['max_days_per_year']; ?></span>
                                    </td>
                                    <td><?php echo $type['min_advance_notice_days']; ?> days</td>
                                    <td>
                                        <?php if ($type['is_paid']){ ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary">Unpaid</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($type['requires_documentation']){ ?>
                                            <span class="badge bg-warning">Required</span>
                                        <?php } else { ?>
                                            <span class="badge bg-light text-dark">Not Required</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($type['is_accruable']){ ?>
                                            <span class="badge bg-info">Yes</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($type['is_carry_over_allowed']){ ?>
                                            <span class="badge bg-primary"><?php echo $type['max_carry_over_days']; ?> days</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($type['is_active']){ ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                onclick="viewDetails(<?php echo $type['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($_SESSION['usertype'] === 'hr_admin' || $_SESSION['usertype'] === 'system_admin'): ?>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="editType(<?php echo $type['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Leave Type Details -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Type Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshData() {
            location.reload();
        }

        function viewDetails(typeId) {
            // Find the leave type data
            const leaveTypes = <?php echo json_encode($leaveTypes); ?>;
            const type = leaveTypes.find(t => t.id == typeId);
            
            if (type) {
                let content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>${type.name}</td></tr>
                                <tr><td><strong>Description:</strong></td><td>${type.description || 'No description'}</td></tr>
                                <tr><td><strong>Max Days/Year:</strong></td><td>${type.max_days_per_year}</td></tr>
                                <tr><td><strong>Min Notice:</strong></td><td>${type.min_advance_notice_days} days</td></tr>
                                <tr><td><strong>Max Consecutive:</strong></td><td>${type.max_consecutive_days || 'No limit'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Settings</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Paid:</strong></td><td>${type.is_paid ? 'Yes' : 'No'}</td></tr>
                                <tr><td><strong>Documentation:</strong></td><td>${type.requires_documentation ? 'Required' : 'Not Required'}</td></tr>
                                <tr><td><strong>Accruable:</strong></td><td>${type.is_accruable ? 'Yes' : 'No'}</td></tr>
                                <tr><td><strong>Carry Over:</strong></td><td>${type.is_carry_over_allowed ? type.max_carry_over_days + ' days' : 'No'}</td></tr>
                                <tr><td><strong>Approval:</strong></td><td>${type.requires_approval ? 'Required' : 'Auto-approved'}</td></tr>
                                <tr><td><strong>Auto Approve:</strong></td><td>${type.auto_approve_threshold_days} days</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                document.getElementById('modalContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('detailsModal')).show();
            }
        }

        function editType(typeId) {
            window.location.href = `../admin/leave_type_edit.php?id=${typeId}`;
        }
    </script>
