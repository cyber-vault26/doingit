<?php
require_once __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/department_functions.php";
require_once __DIR__ . "/../../functions/user_functions.php";

// Get database connection
$conn = openConn();

// Check if user has permission to view departments
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'hr_admin') {
    echo "<script>alert('Access denied. You do not have permission to view departments.'); window.location.href='../../index.php';</script>";
    exit;
}

// Get all departments
$departments = listdepartments($conn);
?>

<!-- Main Content -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building me-2"></i>Department List</h2>
                <div class="action-buttons">
                    <a href="./?page=add_department" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Department
                    </a>
                </div>
            </div>

            <!-- Departments Table -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> All Departments
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($departments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No departments found. 
                            <a href="./?page=add_department" class="alert-link">Create your first department</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Department Name</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Parent Department</th>
                                        <th>Department Head</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    foreach ($departments as $dept): 
                                        // Get department head name
                                        $head_name = 'Not Assigned';
                                        if (!empty($dept['head_of_department_id'])) {
                                            $head_sql = "SELECT names FROM users WHERE id = ?";
                                            $head_stmt = $conn->prepare($head_sql);
                                            $head_stmt->bind_param("i", $dept['head_of_department_id']);
                                            $head_stmt->execute();
                                            $head_result = $head_stmt->get_result();
                                            $head_data = $head_result->fetch_assoc();
                                            if ($head_data) {
                                                $head_name = $head_data['names'];
                                            }
                                        }
                                        
                                        // Get parent department name
                                        $parent_name = 'None (Top Level)';
                                        if (!empty($dept['parent_department_id'])) {
                                            $parent_sql = "SELECT name FROM departments WHERE id = ?";
                                            $parent_stmt = $conn->prepare($parent_sql);
                                            $parent_stmt->bind_param("i", $dept['parent_department_id']);
                                            $parent_stmt->execute();
                                            $parent_result = $parent_stmt->get_result();
                                            $parent_data = $parent_result->fetch_assoc();
                                            if ($parent_data) {
                                                $parent_name = $parent_data['name'];
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($dept['name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?php echo htmlspecialchars($dept['department_code']); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $description = htmlspecialchars($dept['description'] ?? '');
                                                echo strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($parent_name); ?></td>
                                            <td><?php echo htmlspecialchars($head_name); ?></td>
                                            <td>
                                                <?php if ($dept['is_active'] == 1): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($dept['creator'] ?? 'System'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($dept['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- Edit Button -->
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="editDepartment(<?php echo $dept['id']; ?>)"
                                                            title="Edit Department">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <!-- Toggle Status Button -->
                                                    <button type="button" class="btn <?php echo $dept['is_active'] == 1 ? 'btn-outline-warning' : 'btn-outline-success'; ?>" 
                                                            onclick="toggleDepartmentStatus(<?php echo $dept['id']; ?>, <?php echo $dept['is_active']; ?>)"
                                                            title="<?php echo $dept['is_active'] == 1 ? 'Deactivate' : 'Activate'; ?> Department">
                                                        <i class="fas <?php echo $dept['is_active'] == 1 ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </button>
                                                    
                                                    <!-- Delete Button -->
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteDepartment(<?php echo $dept['id']; ?>)"
                                                            title="Delete Department">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Summary Statistics -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Departments</h5>
                                        <h3><?php echo count($departments); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Active</h5>
                                        <h3><?php echo count(array_filter($departments, function($d) { return $d['is_active'] == 1; })); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Inactive</h5>
                                        <h3><?php echo count(array_filter($departments, function($d) { return $d['is_active'] == 0; })); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Without Head</h5>
                                        <h3><?php echo count(array_filter($departments, function($d) { return empty($d['head_of_department_id']); })); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editDepartmentForm" method="POST" action="./controllers/department_controller.php">
                <input type="hidden" name="action" value="update_department">
                <input type="hidden" name="department_id" id="edit_department_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Department Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_department_code">Department Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_department_code" name="department_code" 
                                       required maxlength="10" style="text-transform: uppercase;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_description">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_parent_department_id">Parent Department</label>
                                <select class="form-control" id="edit_parent_department_id" name="parent_department_id">
                                    <option value="">None (Top Level)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_head_of_department_id">Department Head</label>
                                <select class="form-control" id="edit_head_of_department_id" name="head_of_department_id">
                                    <option value="">Select Department Head</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_is_active">Status</label>
                                <select class="form-control" id="edit_is_active" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-format department code to uppercase
    $('#edit_department_code').on('input', function() {
        this.value = this.value.toUpperCase();
    });
});

function editDepartment(departmentId) {
    // Fetch department data via AJAX
    $.ajax({
        url: './controllers/department_controller.php',
        method: 'POST',
        data: { 
            action: 'get_department',
            department_id: departmentId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var dept = response.data;
                $('#edit_department_id').val(dept.id);
                $('#edit_name').val(dept.name);
                $('#edit_department_code').val(dept.department_code);
                $('#edit_description').val(dept.description || '');
                $('#edit_parent_department_id').val(dept.parent_department_id || '');
                $('#edit_head_of_department_id').val(dept.head_of_department_id || '');
                $('#edit_is_active').val(dept.is_active);
                
                // Load parent departments and department heads
                loadEditFormDropdowns();
                
                $('#editDepartmentModal').modal('show');
            } else {
                alert('Error loading department data: ' + response.message);
            }
        },
        error: function() {
            alert('Error loading department data. Please try again.');
        }
    });
}

function loadEditFormDropdowns() {
    // Load parent departments
    $.ajax({
        url: './controllers/department_controller.php',
        method: 'POST',
        data: { action: 'get_departments_for_dropdown' },
        dataType: 'json',
        success: function(response) {
            var parentSelect = $('#edit_parent_department_id');
            parentSelect.find('option:not(:first)').remove();
            
            if (response.success) {
                $.each(response.data, function(index, dept) {
                    parentSelect.append('<option value="' + dept.id + '">' + dept.name + ' (' + dept.department_code + ')</option>');
                });
            }
        }
    });
    
    // Load users for department heads
    $.ajax({
        url: './controllers/department_controller.php',
        method: 'POST',
        data: { action: 'get_users_for_dropdown' },
        dataType: 'json',
        success: function(response) {
            var headSelect = $('#edit_head_of_department_id');
            headSelect.find('option:not(:first)').remove();
            
            if (response.success) {
                $.each(response.data, function(index, user) {
                    headSelect.append('<option value="' + user.id + '">' + user.names + '</option>');
                });
            }
        }
    });
}

function toggleDepartmentStatus(departmentId, currentStatus) {
    var newStatus = currentStatus == 1 ? 0 : 1;
    var action = newStatus == 1 ? 'activate' : 'deactivate';
    
    if (!confirm('Are you sure you want to ' + action + ' this department?')) {
        return;
    }
    
    $.ajax({
        url: './controllers/department_controller.php',
        method: 'POST',
        data: { 
            action: 'toggle_status',
            department_id: departmentId,
            is_active: newStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error updating department status. Please try again.');
           // window.location.reload();
        }
    });
}

function deleteDepartment(departmentId) {
    if (!confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
        return;
    }
    
    if (!confirm('WARNING: Deleting this department will also remove all associated data. Continue?')) {
        return;
    }
    
    $.ajax({
        url: './controllers/department_controller.php',
        method: 'POST',
        data: { 
            action: 'delete_department',
            department_id: departmentId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error deleting department. Please try again.');
        }
    });
}
</script>