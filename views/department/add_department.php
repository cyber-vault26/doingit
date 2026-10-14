<?php
// Include necessary files
require_once __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/user_functions.php";

// Get database connection
$conn = openConn();

// Check if user has permission to create departments (only HR Admin and System Admin)
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'hr_admin') {
    echo "<script>alert('Access denied. You do not have permission to create departments.'); window.location.href='../../index.php';</script>";
    exit;
}
$departments = [];
?>

<!-- Main Content -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building me-2"></i>Add New Department</h2>
                <div class="action-buttons">
                    <a href="departments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Departments
                    </a>
                </div>
            </div>

            

            <!-- Department Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building"></i> Department Information
                    </h5>
                </div>
                <form method="POST" action="./controllers/department_controller.php" id="departmentForm">
                    <input type="hidden" name="action" value="add_department">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Department Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                           placeholder="Enter department name" required>
                                    <small class="form-text text-muted">The official name of department</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_code">Department Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="department_code" name="department_code" 
                                           value="<?php echo htmlspecialchars($_POST['department_code'] ?? ''); ?>" 
                                           placeholder="e.g., IT, HR, FIN" required maxlength="10" style="text-transform: uppercase;">
                                    <small class="form-text text-muted">Unique identifier for department (max 10 characters)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                              placeholder="Describe department's purpose and responsibilities"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    <small class="form-text text-muted">Brief description of department's function and scope</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="parent_department_id">Parent Department</label>
                                    <select class="form-control" id="parent_department_id" name="parent_department_id">
                                        <option value="">None (Top Level)</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>" 
                                                    <?php echo (isset($_POST['parent_department_id']) && $_POST['parent_department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept['name'] . ' (' . $dept['department_code'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Select parent department if this is a sub-department</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="head_of_department_id">Department Head</label>
                                    <select class="form-control" id="head_of_department_id" name="head_of_department_id">
                                        <option value="">Select Department Head</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" 
                                                    <?php echo (isset($_POST['head_of_department_id']) && $_POST['head_of_department_id'] == $user['id']) ? 'selected' : ''; ?>
                                                    data-rank="<?php echo htmlspecialchars($user['rank_id'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($user['names']); ?>
                                                <?php if (!empty($user['rank_id'])): ?>
                                                    (<?php echo htmlspecialchars($user['rank_id']); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">The person who will lead this department</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Department
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
