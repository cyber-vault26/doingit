<?php
// Process the form submission
if(isset($_POST['submitLeaveRequest'])) {
    // Get form data
    $staff_id = $_POST['staff_id'];
    $staff_type = $_POST['staff_type']; // Added staff_type to track source table
    $leave_type_id = $_POST['type_id'];
    $date_of_leaving = $_POST['date_of_leaving'];
    $date_of_returning = $_POST['date_of_returning'];
    
    // Get current date and creator ID
    $request_date = date('Y-m-d');
    $created_by = $_SESSION['userid'];
    
    // Insert data into database - modified to include staff_type
    $sql = "INSERT INTO leave_request (staff_id, staff_type, type_id, request_date, date_of_leaving, date_of_returning, created_by) 
            VALUES ('$staff_id', '$staff_type', '$leave_type_id', '$request_date', '$date_of_leaving', '$date_of_returning', '$created_by')";
    
    // Execute query and show result
    if(mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>Leave request submitted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>
<!-- Staff Leave Request Page -->
<?php
// Check if user_id is passed in URL
$specific_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i> Staff Leave Management</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Check if specific user_id is provided
                                if($specific_user_id) {
                                    // Query only for the specific user from users table
                                    $query = "SELECT id, names as name, phone, email, 'Staff' as status, 'users' as source 
                                             FROM users 
                                             WHERE id = $specific_user_id";
                                } else {
                                    // Combined query to get all staff from users and people/drivers from subsidiaries
                                    $query = "
                                        (SELECT id, names as name, phone, email, 'Staff' as status, 'users' as source 
                                        FROM users 
                                        WHERE usertype = 'staff')
                                        
                                        UNION
                                        
                                        (SELECT id, name, phone, email, 
                                            CASE 
                                                WHEN subcategory = 'person' THEN 'Person'
                                                WHEN subcategory = 'driver' THEN 'Driver'
                                                ELSE subcategory
                                            END as status,
                                            'subsidiaries' as source
                                        FROM subsidiaries 
                                        WHERE subcategory IN ('person', 'driver'))
                                        
                                        ORDER BY name";
                                }
                                
                                $result = mysqli_query($conn, $query);
                                
                                if(mysqli_num_rows($result) > 0) {
                                    $counter = 1;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>';
                                        echo '<td>'.$counter.'</td>';
                                        echo '<td>'.$row['name'].'</td>';
                                        echo '<td>'.$row['phone'].'</td>';
                                        echo '<td>'.$row['email'].'</td>';
                                        echo '<td><span class="badge badge-info">'.$row['status'].'</span></td>';
                                        echo '<td>
                                            <button class="btn btn-sm btn-primary request-leave-btn" 
                                                data-id="'.$row['id'].'" 
                                                data-name="'.$row['name'].'"
                                                data-source="'.$row['source'].'"
                                                data-status="'.$row['status'].'">
                                                <i class="fas fa-calendar-plus mr-1"></i> Request Leave
                                            </button>
                                        </td>';
                                        echo '</tr>';
                                        $counter++;
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">No staff records found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <!-- Leave Request Form Modal -->
    <!-- Leave Request Form Modal -->
<form class="was-validated" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?page=leave_request" method="post" id="leaveRequestForm">
    <div class="modal fade" id="leaveRequestModal" tabindex="-1" role="dialog" aria-labelledby="leaveRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="leaveRequestModalLabel">New Leave Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="staff_id" name="staff_id">
                    <input type="hidden" id="staff_type" name="staff_type">
                    <input type="hidden" id="max_days" name="max_days" value="0">
                    <input type="hidden" id="remaining_days" name="remaining_days" value="0">
                    <div class="form-group">
                        <label for="staff_name">Name</label>
                        <input type="text" class="form-control" id="staff_name" name="staff_name" readonly>
                    </div>
                    <div class="form-group">
                        <label for="staff_status">Status</label>
                        <input type="text" class="form-control" id="staff_status" name="staff_status" readonly>
                    </div>
                    <div class="form-group">
                        <label for="leave_type">Leave Type</label>
                        <select class="form-control" id="leave_type" name="type_id" required>
                            <option value="">Select Leave Type</option>
                            <?php
                            // Fetch leave types from database
                            $sql = "SELECT id, type_name FROM leave_types ORDER BY type_name";
                            $result = mysqli_query($conn, $sql);
                            
                            if(mysqli_num_rows($result) > 0) {
                                while($type = mysqli_fetch_assoc($result)) {
                                    echo '<option value="'.$type['id'].'">'.$type['type_name'].'</option>';
                                }
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Please select a leave type</div>
                    </div>
                    
                    <!-- Leave Information Card - Will be shown after leave type is selected -->
                    <div class="card mt-3 mb-3" id="leave_info" style="display: none;">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Leave Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Maximum:</strong> <span id="max_days_display">0</span> days</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Used:</strong> <span id="days_used_display">0</span> days</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Remaining:</strong> <span id="days_remaining_display">0</span> days</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_leaving">Start Date</label>
                        <input type="date" class="form-control date-input" id="date_of_leaving" name="date_of_leaving" min="<?= date("Y-m-d")?>" required>
                        <div class="invalid-feedback">Please select a start date</div>
                    </div>
                    <div class="form-group">
                        <label for="date_of_returning">End Date</label>
                        <input type="date" class="form-control date-input" id="date_of_returning" min="<?= date("Y-m-d")?>" name="date_of_returning" required>
                        <div class="invalid-feedback">Please select an end date</div>
                    </div>
                    <div class="form-group">
                        <label for="num_days">Number of Days</label>
                        <input type="text" class="form-control" id="num_days" name="num_days" readonly value="0">
                        <div class="text-danger" id="days_error" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="submitLeaveRequest" id="submitBtn">
                        <i class="fas fa-paper-plane mr-1"></i> Submit Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
 
<script>
// Script to handle leave request functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get all request leave buttons
    var requestLeaveButtons = document.querySelectorAll('.request-leave-btn');
    
    // Add click event to each button
    requestLeaveButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Get staff ID, name, source and status from data attributes
            var staffId = this.getAttribute('data-id');
            var staffName = this.getAttribute('data-name');
            var staffSource = this.getAttribute('data-source');
            var staffStatus = this.getAttribute('data-status');
            
            // Set values in the form
            document.getElementById('staff_id').value = staffId;
            document.getElementById('staff_name').value = staffName;
            document.getElementById('staff_type').value = staffSource;
            document.getElementById('staff_status').value = staffStatus;
            
            // Reset date inputs and leave information
            document.getElementById('date_of_leaving').value = '';
            document.getElementById('date_of_returning').value = '';
            document.getElementById('num_days').value = '0';
            document.getElementById('leave_info').style.display = 'none';
            document.getElementById('days_error').style.display = 'none';
            
            // Reset leave type selection
            document.getElementById('leave_type').selectedIndex = 0;
            
            // Reset hidden fields
            document.getElementById('max_days').value = '0';
            document.getElementById('remaining_days').value = '0';
            
            // Show the modal
            $('#leaveRequestModal').modal('show');
        });
    });
    
    // Function to calculate and display number of days
    function calculateDays() {
        var startDate = document.getElementById('date_of_leaving').value;
        var endDate = document.getElementById('date_of_returning').value;
        var daysError = document.getElementById('days_error');
        var submitBtn = document.getElementById('submitBtn');
        var maxDays = parseInt(document.getElementById('max_days').value || 0);
        var remainingDays = parseInt(document.getElementById('remaining_days').value || 0);
        
        if(startDate && endDate) {
            // Convert to Date objects
            var start = new Date(startDate);
            var end = new Date(endDate);
            
            // Calculate the time difference in milliseconds
            var timeDiff = end - start;
            
            // Convert to days and add 1 to include both start and end dates
            var daysDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24)) + 1;
            
            if(daysDiff < 1) {
                document.getElementById('num_days').value = '0';
                daysError.textContent = 'End date must be on or after start date';
                daysError.style.display = 'block';
                submitBtn.disabled = true;
            } else if(daysDiff > maxDays) {
                document.getElementById('num_days').value = daysDiff;
                daysError.textContent = 'Requested days exceed maximum allowed (' + maxDays + ' days)';
                daysError.style.display = 'block';
                submitBtn.disabled = true;
            } else if(daysDiff > remainingDays) {
                document.getElementById('num_days').value = daysDiff;
                daysError.textContent = 'Requested days exceed remaining days (' + remainingDays + ' days)';
                daysError.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                document.getElementById('num_days').value = daysDiff;
                daysError.style.display = 'none';
                submitBtn.disabled = false;
            }
        } else {
            document.getElementById('num_days').value = '0';
        }
    }
    
    // Get leave type select element
    var leaveTypeSelect = document.getElementById('leave_type');
    
    // Add event listener for leave type change
    leaveTypeSelect.addEventListener('change', function() {
        var leaveTypeId = this.value;
        var staffId = document.getElementById('staff_id').value;
        var staffType = document.getElementById('staff_type').value;
        
        if(leaveTypeId) {
            // Reset date inputs when leave type changes
            document.getElementById('date_of_leaving').value = '';
            document.getElementById('date_of_returning').value = '';
            document.getElementById('num_days').value = '0';
            document.getElementById('days_error').style.display = 'none';
            
            // Fetch maximum leave days for selected leave type
            fetchMaximumLeaveDays(leaveTypeId, staffId, staffType);
        } else {
            document.getElementById('leave_info').style.display = 'none';
            document.getElementById('max_days').value = '0';
            document.getElementById('remaining_days').value = '0';
        }
    });
    
    // Function to fetch maximum leave days from server
    function fetchMaximumLeaveDays(leaveTypeId, staffId, staffType) {
        // Show loading indicator or message if needed
        
        var formData = new FormData();
        formData.append('leave_type_id', leaveTypeId);
        formData.append('staff_id', staffId);
        formData.append('staff_type', staffType);
        
        fetch('controllers/get_maximun_leaves.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Update hidden fields with maximum and remaining days
                document.getElementById('max_days').value = data.maximum_days;
                document.getElementById('remaining_days').value = data.days_remaining;
                
                // Update the info display
                document.getElementById('max_days_display').textContent = data.maximum_days;
                document.getElementById('days_used_display').textContent = data.days_used;
                document.getElementById('days_remaining_display').textContent = data.days_remaining;
                
                // Show leave info section
                document.getElementById('leave_info').style.display = 'block';
                
                // If remaining days is 0, disable date inputs and show message
                if(data.days_remaining <= 0) {
                    document.getElementById('date_of_leaving').disabled = true;
                    document.getElementById('date_of_returning').disabled = true;
                    document.getElementById('days_error').textContent = 'No leave days remaining for this leave type';
                    document.getElementById('days_error').style.display = 'block';
                    document.getElementById('submitBtn').disabled = true;
                } else {
                    document.getElementById('date_of_leaving').disabled = false;
                    document.getElementById('date_of_returning').disabled = false;
                    document.getElementById('days_error').style.display = 'none';
                    document.getElementById('submitBtn').disabled = false;
                }
            } else {
                console.error('Error fetching leave information:', data.message);
                document.getElementById('leave_info').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('leave_info').style.display = 'none';
        });
    }
    
    // Add event listeners to date inputs to calculate days
    document.getElementById('date_of_leaving').addEventListener('change', function() {
        calculateDays();
        updateReturnDateMin();
    });
    
    document.getElementById('date_of_returning').addEventListener('change', calculateDays);
    
    // Function to update the minimum date for return date
    function updateReturnDateMin() {
        var startDate = document.getElementById('date_of_leaving').value;
        var endDateInput = document.getElementById('date_of_returning');
        
        if(startDate) {
            endDateInput.min = startDate;
            
            // If end date is now before start date, clear it
            if(endDateInput.value && new Date(endDateInput.value) < new Date(startDate)) {
                endDateInput.value = '';
                document.getElementById('num_days').value = '0';
            }
        }
    }
});
</script>