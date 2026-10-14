<?php
// Include necessary files
require_once  __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/leave_functions.php";
require_once __DIR__ . "/../../functions/user_functions.php";

// Get database connection
$conn = openConn();

// Only allow HR/Admin to access this page
if ($_SESSION['usertype'] !== 'hr_admin' && $_SESSION['usertype'] !== 'system_admin') {
    echo "<script>alert('Access denied'); window.location.href='./';</script>";
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['initialize_all'])) {
        // Initialize balances for all users
        $allUsers = selectAllUsers($conn);
        $leaveTypes = getAllLeaveTypes($conn);
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($allUsers as $user) {
            foreach ($leaveTypes as $leaveType) {
                // Check if balance already exists
                $existingBalance = getLeaveBalance($conn, $user['id'], $leaveType['id']);
                
                if (!$existingBalance) {
                    // Get user's policies for this leave type
                    $userPolicies = getLeavePoliciesByUserId($conn, $user['id']);
                    $applicablePolicy = null;
                    
                    foreach ($userPolicies as $policy) {
                        if ($policy['leave_type_id'] == $leaveType['id'] && $policy['is_active']) {
                            $applicablePolicy = $policy;
                            break;
                        }
                    }
                    
                    if ($applicablePolicy) {
                        // Calculate initial balance based on policy
                        $initialBalance = 0;
                        
                        if ($applicablePolicy['accrual_period'] === 'annually') {
                            // Give full annual accrual for new users
                            $initialBalance = $applicablePolicy['accrual_rate'];
                        } elseif ($applicablePolicy['accrual_period'] === 'monthly') {
                            // Calculate months since user joined
                            $joinDate = new DateTime($user['created_at']);
                            $currentDate = new DateTime();
                            $monthsDiff = $joinDate->diff($currentDate)->m + ($joinDate->diff($currentDate)->y * 12);
                            $initialBalance = min($monthsDiff * $applicablePolicy['accrual_rate'], $applicablePolicy['accrual_rate'] * 12);
                        }
                        
                        // Create balance record
                        $sql = "INSERT INTO leave_balances (user_id, leave_type_id, available_balance, accrued_balance, used_days, pending_days, created_by) 
                                VALUES (?, ?, ?, ?, 0, 0, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iidis", $user['id'], $leaveType['id'], $initialBalance, $initialBalance, $_SESSION['userid']);
                        
                        if ($stmt->execute()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        $stmt->close();
                    }
                }
            }
        }
        
        $message = "Successfully initialized $successCount balances. $errorCount errors occurred.";
    }
    
    if (isset($_POST['initialize_user']) && !empty($_POST['user_id'])) {
        // Initialize balance for specific user
        $userId = (int) $_POST['user_id'];
        $leaveTypes = getAllLeaveTypes($conn);
        
        $successCount = 0;
        foreach ($leaveTypes as $leaveType) {
            $existingBalance = getLeaveBalance($conn, $userId, $leaveType['id']);
            
            if (!$existingBalance) {
                // Get user's policies
                $userPolicies = getLeavePoliciesByUserId($conn, $userId);
                $applicablePolicy = null;
                
                foreach ($userPolicies as $policy) {
                    if ($policy['leave_type_id'] == $leaveType['id'] && $policy['is_active']) {
                        $applicablePolicy = $policy;
                        break;
                    }
                }
                
                if ($applicablePolicy) {
                    $initialBalance = $applicablePolicy['accrual_rate']; // Simple: give one period's accrual
                    
                    $sql = "INSERT INTO leave_balances (user_id, leave_type_id, available_balance, accrued_balance, used_days, pending_days, created_by) 
                            VALUES (?, ?, ?, ?, 0, 0, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iidis", $userId, $leaveType['id'], $initialBalance, $initialBalance, $_SESSION['userid']);
                    
                    if ($stmt->execute()) {
                        $successCount++;
                    }
                    $stmt->close();
                }
            }
        }
        
        $message = "Successfully initialized $successCount balances for selected user.";
    }
}

// Get all users for dropdown
$allUsers = selectAllUsers($conn);
$leaveTypes = getAllLeaveTypes($conn);

$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-calculator me-2"></i>Initialize Leave Balances</h2>
                    <div class="action-buttons">
                        <a href="./?page=leave_policies_list" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Policies
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Initialize All Users -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Initialize All Users
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This will create leave balances for all users who don't have them yet, based on their applicable leave policies.</p>
                        
                        <form method="POST">
                            <button type="submit" name="initialize_all" class="btn btn-primary" 
                                    onclick="return confirm('This will create balances for all users. Are you sure?')">
                                <i class="fas fa-play me-1"></i>Initialize All User Balances
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Initialize Specific User -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>Initialize Specific User
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">Select User</option>
                                        <?php foreach ($allUsers as $user): ?>
                                            <option value="<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['names']); ?>
                                                (<?php echo $user['email']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" name="initialize_user" class="btn btn-success">
                                        <i class="fas fa-plus me-1"></i>Initialize User Balances
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Current Status -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Current Balance Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-primary"><?php echo count($allUsers); ?></h4>
                                    <p class="text-muted">Total Users</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-success"><?php echo count($leaveTypes); ?></h4>
                                    <p class="text-muted">Leave Types</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-info">
                                        <?php 
                                        $usersWithBalances = 0;
                                        foreach ($allUsers as $user) {
                                            $hasBalance = false;
                                            foreach ($leaveTypes as $type) {
                                                if (getLeaveBalance($conn, $user['id'], $type['id'])) {
                                                    $hasBalance = true;
                                                    break;
                                                }
                                            }
                                            if ($hasBalance) $usersWithBalances++;
                                        }
                                        echo $usersWithBalances;
                                        ?>
                                    </h4>
                                    <p class="text-muted">Users with Balances</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
