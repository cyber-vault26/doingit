<?php
// Include necessary files
require_once  __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/leave_functions.php";

// Get database connection
$conn = openConn();

// Get all leave policies
$leavePolicies = getAllLeavePolicies($conn);

// Close connection
$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-file-contract me-2"></i>Leave Policies</h2>
                    <div class="action-buttons">
                        <button class="btn btn-success" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <?php if ($_SESSION['usertype'] === 'hr_admin' || $_SESSION['usertype'] === 'system_admin'): ?>
                            <button class="btn btn-primary" onclick="window.location.href='../admin/leave_policies_manage.php'">
                                <i class="fas fa-cog me-1"></i>Manage
                            </button>
                        <?php endif; ?>
                        <a href="./?page=add_new_policy" class="btn btn-info">
                            <i class="fas fa-plus me-1"></i>Add New Policy
                        </a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo count($leavePolicies); ?></h4>
                                        <p class="card-text">Total Policies</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-contract fa-2x opacity-75"></i>
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
                                            $activeCount = 0;
                                            foreach ($leavePolicies as $policy) {
                                                if ($policy['is_active']) $activeCount++;
                                            }
                                            echo $activeCount;
                                            ?>
                                        </h4>
                                        <p class="card-text">Active Policies</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                                            $companyWideCount = 0;
                                            foreach ($leavePolicies as $policy) {
                                                if ($policy['user_id'] === null) $companyWideCount++;
                                            }
                                            echo $companyWideCount;
                                            ?>
                                        </h4>
                                        <p class="card-text">Company-wide</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-building fa-2x opacity-75"></i>
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
                                            $userSpecificCount = 0;
                                            foreach ($leavePolicies as $policy) {
                                                if ($policy['user_id'] !== null) $userSpecificCount++;
                                            }
                                            echo $userSpecificCount;
                                            ?>
                                        </h4>
                                        <p class="card-text">User-specific</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Policies Grid -->
                <div class="stats-grid">
                    <?php foreach ($leavePolicies as $policy): ?>
                        <div class="card h-100" 
                             style="border-left: 4px solid <?php echo getPolicyColor($policy['leave_type_name']); ?>;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($policy['policy_name']); ?>
                                        </h5>
                                        <span class="badge bg-primary me-2">
                                            <?php echo htmlspecialchars($policy['leave_type_name']); ?>
                                        </span>
                                        <?php if ($policy['user_id'] === null): ?>
                                            <span class="badge bg-info">Company-wide</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">User-specific</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge-days badge bg-light text-dark">
                                            <?php echo $policy['accrual_rate']; ?>/<?php echo $policy['accrual_period']; ?>
                                        </span>
                                    </div>
                                </div>

                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($policy['description'] ?: 'No description available'); ?>
                                </p>

                                <!-- Policy Details -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Accrual: <?php echo $policy['accrual_rate']; ?> per <?php echo $policy['accrual_period']; ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-exchange-alt me-1"></i>
                                            Carry Over: <?php echo $policy['max_carry_over_days']; ?> days
                                        </small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Probation: <?php echo $policy['probation_period_days']; ?> days
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-user-check me-1"></i>
                                            Approval Levels: <?php echo $policy['approval_levels']; ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <?php if ($policy['tenure_based_accrual']): ?>
                                                <i class="fas fa-chart-bar me-1 text-success"></i>
                                                <span class="text-success">Tenure-based Accrual</span>
                                            <?php else: ?>
                                                <i class="fas fa-chart-bar me-1"></i>
                                                Fixed Accrual
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-flag me-1"></i>
                                            Priority: <?php echo $policy['priority']; ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Effective: <?php echo date('M d, Y', strtotime($policy['effective_date'])); ?>
                                            <?php if ($policy['expiry_date']): ?>
                                                - <?php echo date('M d, Y', strtotime($policy['expiry_date'])); ?>
                                            <?php else: ?>
                                                (No expiry)
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewDetails(<?php echo $policy['id']; ?>)">
                                        <i class="fas fa-eye me-1"></i>Details
                                    </button>
                                    <?php if ($_SESSION['usertype'] === 'hr_admin' || $_SESSION['usertype'] === 'system_admin'): ?>
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="editPolicy(<?php echo $policy['id']; ?>)">
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
                                <th>Policy Name</th>
                                <th>Leave Type</th>
                                <th>Scope</th>
                                <th>Accrual Rate</th>
                                <th>Carry Over</th>
                                <th>Approval Levels</th>
                                <th>Effective Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leavePolicies as $policy): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($policy['policy_name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($policy['leave_type_name']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($policy['user_id'] === null): ?>
                                            <span class="badge bg-info">Company-wide</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">User-specific</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $policy['accrual_rate']; ?>/<?php echo $policy['accrual_period']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $policy['max_carry_over_days'] > 0 ? 'success' : 'secondary'; ?>">
                                            <?php echo $policy['max_carry_over_days']; ?> days
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $policy['approval_levels']; ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($policy['effective_date'])); ?>
                                        <?php if ($policy['expiry_date']): ?>
                                            <br><small class="text-muted">to <?php echo date('M d, Y', strtotime($policy['expiry_date'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($policy['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                onclick="viewDetails(<?php echo $policy['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($_SESSION['usertype'] === 'hr_admin' || $_SESSION['usertype'] === 'system_admin'): ?>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="editPolicy(<?php echo $policy['id']; ?>)">
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

    <!-- Modal for Policy Details -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Policy Details</h5>
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

        function viewDetails(policyId) {
            // Find the policy data
            const policies = <?php echo json_encode($leavePolicies); ?>;
            const policy = policies.find(p => p.id == policyId);
            
            if (policy) {
                let accrualTiers = '';
                if (policy.accrual_tiers) {
                    try {
                        const tiers = JSON.parse(policy.accrual_tiers);
                        accrualTiers = '<ul>';
                        for (const [years, rate] of Object.entries(tiers)) {
                            accrualTiers += `<li>${years}+ years: ${rate} days per ${policy.accrual_period}</li>`;
                        }
                        accrualTiers += '</ul>';
                    } catch (e) {
                        accrualTiers = 'Invalid accrual tiers data';
                    }
                } else {
                    accrualTiers = 'Fixed rate (no tenure-based accrual)';
                }

                let content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>${policy.policy_name}</td></tr>
                                <tr><td><strong>Description:</strong></td><td>${policy.description || 'No description'}</td></tr>
                                <tr><td><strong>Leave Type:</strong></td><td>${policy.leave_type_name}</td></tr>
                                <tr><td><strong>Scope:</strong></td><td>${policy.user_id ? 'User-specific' : 'Company-wide'}</td></tr>
                                <tr><td><strong>Priority:</strong></td><td>${policy.priority}</td></tr>
                                <tr><td><strong>Status:</strong></td><td>${policy.is_active ? 'Active' : 'Inactive'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Accrual Settings</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Accrual Rate:</strong></td><td>${policy.accrual_rate} per ${policy.accrual_period}</td></tr>
                                <tr><td><strong>Max Carry Over:</strong></td><td>${policy.max_carry_over_days} days</td></tr>
                                <tr><td><strong>Probation Period:</strong></td><td>${policy.probation_period_days} days</td></tr>
                                <tr><td><strong>Tenure-based:</strong></td><td>${policy.tenure_based_accrual ? 'Yes' : 'No'}</td></tr>
                                <tr><td><strong>Approval Levels:</strong></td><td>${policy.approval_levels}</td></tr>
                                <tr><td><strong>Min Staffing:</strong></td><td>${policy.minimum_staffing_required}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Date Range</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Effective Date:</strong></td><td>${new Date(policy.effective_date).toLocaleDateString()}</td></tr>
                                <tr><td><strong>Expiry Date:</strong></td><td>${policy.expiry_date ? new Date(policy.expiry_date).toLocaleDateString() : 'No expiry'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Accrual Tiers</h6>
                            ${accrualTiers}
                        </div>
                    </div>
                `;
                
                document.getElementById('modalContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('detailsModal')).show();
            }
        }

        function editPolicy(policyId) {
            window.location.href = `../admin/leave_policy_edit.php?id=${policyId}`;
        }

        // Helper function to get color based on leave type name
        function getPolicyColor(leaveTypeName) {
            const colors = {
                'Annual Leave': '#28a745',
                'Sick Leave': '#dc3545',
                'Personal Leave': '#6c757d',
                'Maternity Leave': '#e83e8c',
                'Paternity Leave': '#20c997',
                'Unpaid Leave': '#6f42c1',
                'Bereavement Leave': '#343a40',
                'Study Leave': '#fd7e14'
            };
            return colors[leaveTypeName] || '#007bff';
        }
    </script>

<?php
// Helper function to get color based on leave type name
function getPolicyColor($leaveTypeName) {
    $colors = [
        'Annual Leave' => '#28a745',
        'Sick Leave' => '#dc3545',
        'Personal Leave' => '#6c757d',
        'Maternity Leave' => '#e83e8c',
        'Paternity Leave' => '#20c997',
        'Unpaid Leave' => '#6f42c1',
        'Bereavement Leave' => '#343a40',
        'Study Leave' => '#fd7e14'
    ];
    return $colors[$leaveTypeName] ?? '#007bff';
}
?>
