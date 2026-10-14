<?php
// Include necessary files
require_once  __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/leave_functions.php";
require_once __DIR__ . "/../../functions/user_functions.php";

// Get database connection
$conn = openConn();

// Get all leave types for dropdown
$leaveTypes = getAllLeaveTypes($conn);

// Get user's leave balances
$userId = $_SESSION['userid'];
$leaveBalances = getAllLeaveBalancesByUser($conn, $userId);

// Get user's leave policies to determine available leave types
$userPolicies = getLeavePoliciesByUserId($conn, $userId);

// Get backup person options (other active users)
$allUsers = selectAllUsers($conn);
$backupUsers = [];
foreach ($allUsers as $user) {
    // Filter out current user and inactive users
    if ($user['id'] != $userId && $user['status'] == 'active') {
        $backupUsers[] = [
            'id' => $user['id'],
            'first_name' => $user['names'], // Using names column instead of first_name
            'last_name' => '', // Empty since we have single names column
            'employee_id' => $user['employee_id'] ?? null
        ];
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
                    <h2><i class="fas fa-calendar-plus me-2"></i>Request Leave</h2>
                    <div class="action-buttons">
                        <a href="./?page=my_leaves" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>My Leaves
                        </a>
                    </div>
                </div>

                <!-- Leave Balance Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-info text-white">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Your Current Leave Balances
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if ($leaveBalances && is_array($leaveBalances) && !empty($leaveBalances)): ?>
                                        <?php foreach ($leaveBalances as $balance): ?>
                                            <div class="col-md-3 col-sm-6 mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="text-white-50"><?php echo htmlspecialchars($balance['leave_type_name']); ?></small>
                                                        <h5 class="mb-0"><?php echo number_format($balance['available_balance'], 1); ?> days</h5>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-white-50">Available</small>
                                                        <div class="badge bg-light text-dark"><?php echo $balance['leave_type_name']; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <p class="text-center mb-0">No leave balances found. Please contact HR.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Request Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit me-2"></i>Leave Request Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="leaveRequestForm" method="POST" action="./controllers/leave_controller.php">
                            <input type="hidden" name="action" value="create_request">
                            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                            
                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Leave Information</h6>
                                    
                                    <div class="mb-3">
                                        <label for="leave_type_id" class="form-label">Leave Type *</label>
                                        <select class="form-select form-control" id="leave_type_id" name="leave_type_id" required>
                                            <option value="">Select Leave Type</option>
                                            <?php foreach ($leaveTypes as $type): ?>
                                                <?php 
                                                // Check if user has policy for this leave type
                                                $hasPolicy = true;
                                                foreach ($userPolicies as $policy) {
                                                    if ($policy['leave_type_id'] == $type['id'] && $policy['is_active']) {
                                                       // $hasPolicy = true;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <?php if ($hasPolicy): ?>
                                                    <option value="<?php echo $type['id']; ?>" 
                                                            data-color="<?php echo $type['color_code']; ?>"
                                                            data-paid="<?php echo $type['is_paid'] ? 'true' : 'false'; ?>"
                                                            data-notice="<?php echo $type['min_advance_notice_days']; ?>"
                                                            data-max-consecutive="<?php echo $type['max_consecutive_days']; ?>">
                                                        <?php echo htmlspecialchars($type['name']); ?>
                                                        (Available: <?php 
                                                            $balance = 0;
                                                            if ($leaveBalances && is_array($leaveBalances)) {
                                                                foreach ($leaveBalances as $b) {
                                                                    if ($b['leave_type_id'] == $type['id']) {
                                                                        $balance = $b['available_balance'];
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            echo number_format($balance, 1); 
                                                        ?> days)
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">Select from your available leave types</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="priority_level" class="form-label">Priority Level</label>
                                        <select class="form-select form-control" id="priority_level" name="priority_level">
                                            <option value="low">Low</option>
                                            <option value="normal" selected>Normal</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                        <small class="form-text text-muted">Select the urgency of this request</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Leave Period</h6>
                                    
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Start Date *</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                        <small class="form-text text-muted">First day of leave</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">End Date *</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                        <small class="form-text text-muted" id="total_days_info">Total days will be calculated</small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="half_day" name="half_day">
                                            <label class="form-check-label" for="half_day">
                                                Half Day Request
                                            </label>
                                        </div>
                                    </div>

                                    <div id="half_day_times" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="start_time" class="form-label">Start Time</label>
                                                <input type="time" class="form-control" id="start_time" name="start_time" value="08:00">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="end_time" class="form-label">End Time</label>
                                                <input type="time" class="form-control" id="end_time" name="end_time" value="13:00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h6 class="text-muted mb-3">Additional Information</h6>
                                    
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Reason for Leave *</label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3" required
                                                  placeholder="Please provide a brief reason for your leave request"></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="backup_person_id" class="form-label">Backup Person (Optional)</label>
                                                <select class="form-select form-control" id="backup_person_id" name="backup_person_id">
                                                    <option value="">Select Backup Person</option>
                                                    <?php foreach ($backupUsers as $user): ?>
                                                        <option value="<?php echo $user['id']; ?>">
                                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                            <?php if ($user['employee_id']): ?>
                                                                (<?php echo $user['employee_id']; ?>)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="form-text text-muted">Person covering your responsibilities</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="emergency_contact_phone" class="form-label">Emergency Contact (Optional)</label>
                                                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                                                       placeholder="+255 7XX XXX XXX">
                                                <small class="form-text text-muted">Contact number during leave</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="work_handover_notes" class="form-label">Work Handover Notes (Optional)</label>
                                        <textarea class="form-control" id="work_handover_notes" name="work_handover_notes" rows="2"
                                                  placeholder="Any important notes about work handover"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Upload -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h6 class="text-muted mb-3">Supporting Documents</h6>
                                    
                                    <div class="mb-3">
                                        <label for="attachment" class="form-label">Upload Document (if required)</label>
                                        <input type="file" class="form-control" id="attachment" name="attachment" 
                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Upload if documentation is required for this leave type</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Validation Summary -->
                            <div id="validation_summary" class="alert alert-warning" style="display: none;">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Validation Summary</h6>
                                <ul id="validation_issues"></ul>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary" id="submit_btn">
                                            <i class="fas fa-paper-plane me-1"></i>Submit Request
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                            <i class="fas fa-undo me-1"></i>Reset Form
                                        </button>
                                        <a href="./?page=my_leaves" class="btn btn-outline-secondary">
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
        // Leave type data storage
        let leaveTypeData = {};
        
        // Initialize leave type data
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('leave_type_id');
            for (let option of select.options) {
                if (option.value) {
                    leaveTypeData[option.value] = {
                        color: option.dataset.color,
                        paid: option.dataset.paid === 'true',
                        notice: parseInt(option.dataset.notice),
                        maxConsecutive: option.dataset.maxConsecutive ? parseInt(option.dataset.maxConsecutive) : null
                    };
                }
            }
            
            // Set minimum dates
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').min = today;
            document.getElementById('end_date').min = today;
        });

        // Toggle half day fields
        document.getElementById('half_day').addEventListener('change', function() {
            const halfDayTimes = document.getElementById('half_day_times');
            halfDayTimes.style.display = this.checked ? 'block' : 'none';
            
            if (this.checked) {
                calculateHalfDays();
            } else {
                calculateTotalDays();
            }
        });

        // Calculate total days when dates change
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
            if (document.getElementById('end_date').value) {
                calculateTotalDays();
            }
            validateDates();
        });

        document.getElementById('end_date').addEventListener('change', function() {
            calculateTotalDays();
            validateDates();
        });

        // Calculate total days
        function calculateTotalDays() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (startDate && endDate && startDate <= endDate) {
                // Calculate business days (excluding weekends)
                let totalDays = 0;
                let currentDate = new Date(startDate);
                
                while (currentDate <= endDate) {
                    const dayOfWeek = currentDate.getDay();
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday (0) or Saturday (6)
                        totalDays++;
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                
                const totalDaysInfo = document.getElementById('total_days_info');
                totalDaysInfo.innerHTML = `<strong>Total business days: ${totalDays}</strong>`;
                
                // Update hidden field for total days
                let totalDaysField = document.getElementById('total_days');
                if (!totalDaysField) {
                    totalDaysField = document.createElement('input');
                    totalDaysField.type = 'hidden';
                    totalDaysField.name = 'total_days';
                    totalDaysField.id = 'total_days';
                    document.getElementById('leaveRequestForm').appendChild(totalDaysField);
                }
                totalDaysField.value = totalDays;
                
                validateLeaveBalance();
            }
        }

        // Calculate half days
        function calculateHalfDays() {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime && endTime) {
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                const hours = (end - start) / (1000 * 60 * 60);
                
                let totalDays = 0;
                if (hours <= 4) {
                    totalDays = 0.5;
                } else if (hours <= 8) {
                    totalDays = 1.0;
                } else {
                    totalDays = 1.5;
                }
                
                const totalDaysInfo = document.getElementById('total_days_info');
                totalDaysInfo.innerHTML = `<strong>Total days: ${totalDays}</strong>`;
                
                // Update hidden field
                let totalDaysField = document.getElementById('total_days');
                if (!totalDaysField) {
                    totalDaysField = document.createElement('input');
                    totalDaysField.type = 'hidden';
                    totalDaysField.name = 'total_days';
                    totalDaysField.id = 'total_days';
                    document.getElementById('leaveRequestForm').appendChild(totalDaysField);
                }
                totalDaysField.value = totalDays;
                
                validateLeaveBalance();
            }
        }

        // Validate dates
        function validateDates() {
            const leaveTypeId = document.getElementById('leave_type_id').value;
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (!leaveTypeData[leaveTypeId]) return true;
            
            const typeData = leaveTypeData[leaveTypeId];
            const issues = [];
            
            // Check minimum notice period
            const today = new Date();
            const daysDiff = Math.ceil((startDate - today) / (1000 * 60 * 60 * 24));
            
            // if (daysDiff < typeData.notice) {
            //     issues.push(`Minimum notice period of ${typeData.notice} days required. You have given ${daysDiff} days.`);
            // }
            
            // Check maximum consecutive days
            // if (typeData.maxConsecutive && !document.getElementById('half_day').checked) {
            //     const totalDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            //     if (totalDays > typeData.maxConsecutive) {
            //         issues.push(`Maximum ${typeData.maxConsecutive} consecutive days allowed. You requested ${totalDays} days.`);
            //     }
            // }
            
            updateValidationSummary(issues);
            return issues.length === 0;
        }

        // Validate leave balance
        function validateLeaveBalance() {
            const leaveTypeId = document.getElementById('leave_type_id').value;
            const totalDaysField = document.getElementById('total_days');
            
            if (!totalDaysField || !totalDaysField.value) return true;
            
            const totalDays = parseFloat(totalDaysField.value);
            const availableBalance = getAvailableBalance(leaveTypeId);
            
            const issues = [];
            
            if (totalDays > availableBalance) {
                issues.push(`Insufficient leave balance. Available: ${availableBalance} days, Requested: ${totalDays} days.`);
            }
            
            updateValidationSummary(issues);
            return issues.length === 0;
        }

        // Get available balance for leave type
        function getAvailableBalance(leaveTypeId) {
            const balances = <?php echo ($leaveBalances && is_array($leaveBalances)) ? json_encode(array_column($leaveBalances, 'available_balance', 'leave_type_id')) : '[]'; ?>;
            return balances[leaveTypeId] || 0;
        }

        // Update validation summary
        function updateValidationSummary(issues) {
            const summaryDiv = document.getElementById('validation_summary');
            const issuesList = document.getElementById('validation_issues');
            
            if (issues.length > 0) {
                issuesList.innerHTML = issues.map(issue => `<li>${issue}</li>`).join('');
                summaryDiv.style.display = 'block';
                document.getElementById('submit_btn').disabled = true;
            } else {
                summaryDiv.style.display = 'none';
                document.getElementById('submit_btn').disabled = false;
            }
        }

        // Form validation before submit
        document.getElementById('leaveRequestForm').addEventListener('submit', function(e) {
            // Run all validations
            const datesValid = validateDates();
            const balanceValid = validateLeaveBalance();
            
            if (!datesValid || !balanceValid) {
                e.preventDefault();
                alert('Please fix validation issues before submitting.');
                return false;
            }
            
            // Confirm submission
            if (!confirm('Are you sure you want to submit this leave request?')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });

        // Reset form
        function resetForm() {
            document.getElementById('leaveRequestForm').reset();
            document.getElementById('validation_summary').style.display = 'none';
            document.getElementById('half_day_times').style.display = 'none';
            document.getElementById('total_days_info').innerHTML = 'Total days will be calculated';
        }

        // Leave type change handler
        document.getElementById('leave_type_id').addEventListener('change', function() {
            validateDates();
            validateLeaveBalance();
        });
    </script>
