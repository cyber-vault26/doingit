<?php
// Include necessary files
require_once  __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/leave_functions.php";

// Get database connection
$conn = openConn();

// Get all leave types for dropdown
$leaveTypes = getAllLeaveTypes($conn);

// Get all users for user-specific policies (optional)
$users = [];
if ($_SESSION['usertype'] === 'hr_admin' || $_SESSION['usertype'] === 'system_admin') {
    $sql = "SELECT id, first_name, last_name, employee_id FROM users WHERE is_active = 1 ORDER BY first_name, last_name";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
    }
}

// Close connection
$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-plus-circle me-2"></i>Add New Leave Policy</h2>
                    <div class="action-buttons">
                        <a href="./?page=leave_policies_list" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Policies
                        </a>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-contract me-2"></i>Policy Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="policyForm" method="POST" action="./controllers/leave_policy_controller.php">
                            <input type="hidden" name="action" value="create">
                            
                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Basic Information</h6>
                                    
                                    <div class="mb-3">
                                        <label for="policy_name" class="form-label">Policy Name *</label>
                                        <input type="text" class="form-control" id="policy_name" name="policy_name" required
                                               placeholder="e.g., Standard Annual Leave Policy">
                                    </div>

                                    <div class="mb-3">
                                        <label for="leave_type_id" class="form-label">Leave Type *</label>
                                        <select class="form-select" id="leave_type_id" name="leave_type_id" required>
                                            <option value="">Select Leave Type</option>
                                            <?php foreach ($leaveTypes as $type): ?>
                                                <option value="<?php echo $type['id']; ?>">
                                                    <?php echo htmlspecialchars($type['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"
                                                  placeholder="Describe the policy details and purpose"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="priority" class="form-label">Priority</label>
                                        <input type="number" class="form-control" id="priority" name="priority" 
                                               value="0" min="0" max="10"
                                               placeholder="Higher numbers override lower priority policies">
                                        <small class="form-text text-muted">Higher priority policies override lower priority ones (0-10)</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Policy Scope</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Policy Scope *</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="scope" id="scope_company" value="company" checked>
                                            <label class="form-check-label" for="scope_company">
                                                <i class="fas fa-building me-1"></i>Company-wide Policy
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="scope" id="scope_user" value="user">
                                            <label class="form-check-label" for="scope_user">
                                                <i class="fas fa-user me-1"></i>User-specific Policy
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3" id="user_selection" style="display: none;">
                                        <label for="user_id" class="form-label">Select User</label>
                                        <select class="form-select" id="user_id" name="user_id">
                                            <option value="">Select User</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?php echo $user['id']; ?>">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                    <?php if ($user['employee_id']): ?>
                                                        (<?php echo $user['employee_id']; ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="effective_date" class="form-label">Effective Date *</label>
                                        <input type="date" class="form-control" id="effective_date" name="effective_date" required
                                               value="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="expiry_date" class="form-label">Expiry Date</label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                        <small class="form-text text-muted">Leave blank for no expiry date</small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                            <label class="form-check-label" for="is_active">
                                                Active Policy
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Accrual Settings -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">Accrual Settings</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="accrual_rate" class="form-label">Accrual Rate *</label>
                                        <input type="number" class="form-control" id="accrual_rate" name="accrual_rate" 
                                               step="0.01" min="0" required
                                               placeholder="e.g., 1.75">
                                        <small class="form-text text-muted">Days accrued per period</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="accrual_period" class="form-label">Accrual Period *</label>
                                        <select class="form-select" id="accrual_period" name="accrual_period" required>
                                            <option value="">Select Period</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="bi_weekly">Bi-weekly</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="annually">Annually</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="max_carry_over_days" class="form-label">Max Carry Over Days</label>
                                        <input type="number" class="form-control" id="max_carry_over_days" name="max_carry_over_days" 
                                               step="0.01" min="0" value="0">
                                        <small class="form-text text-muted">Maximum days that can be carried to next period</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="probation_period_days" class="form-label">Probation Period (days)</label>
                                        <input type="number" class="form-control" id="probation_period_days" name="probation_period_days" 
                                               min="0" value="0">
                                        <small class="form-text text-muted">Days before leave can be taken</small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="tenure_based_accrual" name="tenure_based_accrual">
                                            <label class="form-check-label" for="tenure_based_accrual">
                                                Enable Tenure-based Accrual
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Different accrual rates based on years of service</small>
                                    </div>

                                    <div class="mb-3" id="accrual_tiers_section" style="display: none;">
                                        <label class="form-label">Accrual Tiers</label>
                                        <div class="table-responsive">
                                            <table class="table table-sm" id="accrual_tiers_table">
                                                <thead>
                                                    <tr>
                                                        <th>Years of Service</th>
                                                        <th>Accrual Rate</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="number" class="form-control form-control-sm" name="tier_years[]" min="0" value="1"></td>
                                                        <td><input type="number" class="form-control form-control-sm" name="tier_rates[]" step="0.01" min="0" value="1.75"></td>
                                                        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeTier(this)">Remove</button></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTier()">
                                            <i class="fas fa-plus me-1"></i>Add Tier
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Settings -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Approval Settings</h6>
                                    
                                    <div class="mb-3">
                                        <label for="approval_levels" class="form-label">Approval Levels</label>
                                        <input type="number" class="form-control" id="approval_levels" name="approval_levels" 
                                               min="1" max="5" value="1">
                                        <small class="form-text text-muted">Number of approval levels required</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="minimum_staffing_required" class="form-label">Minimum Staffing Required</label>
                                        <input type="number" class="form-control" id="minimum_staffing_required" name="minimum_staffing_required" 
                                               min="0" value="0">
                                        <small class="form-text text-muted">Minimum staff required during leave</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Auto-approval Settings</h6>
                                    
                                    <div class="mb-3">
                                        <label for="auto_approve_threshold_days" class="form-label">Auto-approve Threshold (days)</label>
                                        <input type="number" class="form-control" id="auto_approve_threshold_days" name="auto_approve_threshold_days" 
                                               min="0" value="0">
                                        <small class="form-text text-muted">Requests <= this many days auto-approved</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="auto_approve_conditions" class="form-label">Auto-approve Conditions (JSON)</label>
                                        <textarea class="form-control" id="auto_approve_conditions" name="auto_approve_conditions" rows="3"
                                                  placeholder='{"min_balance": 5, "max_consecutive": 3}'></textarea>
                                        <small class="form-text text-muted">JSON format for auto-approval conditions</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Settings -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Additional Settings</h6>
                                    
                                    <div class="mb-3">
                                        <label for="blackout_periods" class="form-label">Blackout Periods (JSON)</label>
                                        <textarea class="form-control" id="blackout_periods" name="blackout_periods" rows="3"
                                                  placeholder='[{"start": "2024-12-20", "end": "2024-12-31", "reason": "Holiday season"}]'></textarea>
                                        <small class="form-text text-muted">JSON array of blackout periods</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Documentation Requirements</h6>
                                    
                                    <div class="mb-3">
                                        <label for="documentation_requirements" class="form-label">Requirements (JSON)</label>
                                        <textarea class="form-control" id="documentation_requirements" name="documentation_requirements" rows="3"
                                                  placeholder='{"medical": "required", "duration": ">3 days"}]'></textarea>
                                        <small class="form-text text-muted">JSON format for required documents</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Create Policy
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo me-1"></i>Reset Form
                                        </button>
                                        <a href="./?page=leave_policies_list" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle user selection based on scope
        document.querySelectorAll('input[name="scope"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const userSelection = document.getElementById('user_selection');
                const userId = document.getElementById('user_id');
                
                if (this.value === 'user') {
                    userSelection.style.display = 'block';
                    userId.required = true;
                } else {
                    userSelection.style.display = 'none';
                    userId.required = false;
                    userId.value = '';
                }
            });
        });

        // Toggle accrual tiers section
        document.getElementById('tenure_based_accrual').addEventListener('change', function() {
            const tiersSection = document.getElementById('accrual_tiers_section');
            tiersSection.style.display = this.checked ? 'block' : 'none';
        });

        // Add new tier row
        function addTier() {
            const tbody = document.querySelector('#accrual_tiers_table tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="number" class="form-control form-control-sm" name="tier_years[]" min="0"></td>
                <td><input type="number" class="form-control form-control-sm" name="tier_rates[]" step="0.01" min="0"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeTier(this)">Remove</button></td>
            `;
            tbody.appendChild(newRow);
        }

        // Remove tier row
        function removeTier(button) {
            const row = button.closest('tr');
            row.remove();
        }

        // Form validation
        document.getElementById('policyForm').addEventListener('submit', function(e) {
            // Validate dates
            const effectiveDate = new Date(document.getElementById('effective_date').value);
            const expiryDate = new Date(document.getElementById('expiry_date').value);
            
            if (document.getElementById('expiry_date').value && expiryDate <= effectiveDate) {
                e.preventDefault();
                alert('Expiry date must be after effective date');
                return false;
            }

            // Validate accrual tiers if enabled
            if (document.getElementById('tenure_based_accrual').checked) {
                const tierYears = document.querySelectorAll('input[name="tier_years[]"]');
                const tierRates = document.querySelectorAll('input[name="tier_rates[]"]');
                
                for (let i = 0; i < tierYears.length; i++) {
                    if (!tierYears[i].value || !tierRates[i].value) {
                        e.preventDefault();
                        alert('Please fill in all accrual tier fields');
                        return false;
                    }
                }
            }

            return true;
        });

        // Set minimum date for effective date to today
        document.getElementById('effective_date').min = new Date().toISOString().split('T')[0];
        
        // Set minimum date for expiry date to effective date
        document.getElementById('effective_date').addEventListener('change', function() {
            document.getElementById('expiry_date').min = this.value;
        });
    </script>
