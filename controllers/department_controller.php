<?php
session_start();

require_once __DIR__ . "/../configs.php";
require_once __DIR__ . "/../functions/department_functions.php";
require_once __DIR__ . "/../functions/user_functions.php";
require_once __DIR__ . "/../functions/notification_functions.php";

$conn = openConn();
if(!$_SESSION){
    echo "<script>window.location.href='../'</script>";
}
// Set response header for JSON
//header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_department':
            $data = [
                "name" => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
                "parent_department_id" => !empty($_POST['parent_department_id']) ? (int)$_POST['parent_department_id'] : null,
                "department_code" => trim($_POST['department_code']),
                "head_of_department_id" => !empty($_POST['head_of_department_id']) ? (int)$_POST['head_of_department_id'] : null,
                "is_active" => 1,
                "created_by" => $_SESSION['userid'],
            ];

            $newDepartment = createDepartment($conn, $data);
            if ($newDepartment['success'] == true) {
                // Create default department settings
                $settings_sql = "INSERT INTO department_settings (department_id, created_by) VALUES (?, ?)";
                $settings_stmt = $conn->prepare($settings_sql);
                $settings_stmt->bind_param("ii", $newDepartment['id'], $_SESSION['userid']);
                $settings_stmt->execute();
                
                // Assign department head if specified
                if (!empty($data['head_of_department_id'])) {
                    $assign_sql = "INSERT INTO department_user_roles (user_id, department_id, role, is_primary_department, assigned_by) 
                                  VALUES (?, ?, 'department_head', TRUE, ?)";
                    $assign_stmt = $conn->prepare($assign_sql);
                    $assign_stmt->bind_param("iii", $data['head_of_department_id'], $newDepartment['id'], $_SESSION['userid']);
                    $assign_stmt->execute();
                    
                    // Update user's primary department
                    $update_user_sql = "UPDATE users SET department_id = ? WHERE id = ?";
                    $update_user_stmt = $conn->prepare($update_user_sql);
                    $update_user_stmt->bind_param("ii", $newDepartment['id'], $data['head_of_department_id']);
                    $update_user_stmt->execute();
                }
                
                // echo json_encode(['success' => true, 'message' => $newDepartment['message']]);
                echo "<script>window.location.href ='../?page=department_list';</script>";
            } else {
                // echo json_encode(['success' => false, 'message' => $newDepartment['message']]);
                echo "<script>window.location.href ='../?page=department_list';</script>";
            }
            break;
            
        case 'get_department':
            $department_id = (int)$_POST['department_id'];
            $sql = "SELECT * FROM departments WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $department_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $department = $result->fetch_assoc();
            
            if ($department) {
                echo json_encode(['success' => true, 'data' => $department]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Department not found']);
            }
            break;
            
        case 'get_departments_for_dropdown':
           $departments = listdepartments($conn);
            echo json_encode(['success' => true, 'data' => $departments]);
            break;
            
        case 'get_users_for_dropdown':
            $sql = "SELECT id, names, rank_id FROM users WHERE deleted_at IS NULL ORDER BY names";
            $result = $conn->query($sql);
            $users = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $users]);
            break;
            
        case 'update_department':
            $department_id = (int)$_POST['department_id'];
            $data = [
                "name" => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
                "parent_department_id" => !empty($_POST['parent_department_id']) ? (int)$_POST['parent_department_id'] : null,
                "department_code" => trim($_POST['department_code']),
                "head_of_department_id" => !empty($_POST['head_of_department_id']) ? (int)$_POST['head_of_department_id'] : null,
                "is_active" => (int)$_POST['is_active'],
                "updated_by" => $_SESSION['userid'] ?? 1,
            ];
            
            $updateDeaprtment = updateDepartment($conn, $department_id, $data);
            
            if ($updateDeaprtment['success']) {
                // Update department head assignment if changed
                if (!empty($data['head_of_department_id'])) {
                    // Remove existing department head role
                    $time =date("Y-m-d H:m:s");
                    $remove_sql = "UPDATE  department_user_roles SET deleted_at = NOW() WHERE department_id= ? AND role='department_head'";
                    $remove_stmt = $conn->prepare($remove_sql);
                    $remove_stmt->bind_param("i", $department_id);
                    $remove_stmt->execute();
                    
                    // Add new department head role
                    $assign_sql = "INSERT INTO department_user_roles (user_id, department_id, role, is_primary_department, assigned_by) 
                                  VALUES (?, ?, 'department_head', TRUE, ?)";
                    $assign_stmt = $conn->prepare($assign_sql);
                    $assign_stmt->bind_param("iii", $data['head_of_department_id'], $department_id, $_SESSION['userid']);
                    $assign_stmt->execute();
                    
                    // Update user's primary department
                    $update_user_sql = "UPDATE users SET department_id = ? WHERE id = ?";
                    $update_user_stmt = $conn->prepare($update_user_sql);
                    $update_user_stmt->bind_param("ii", $department_id, $data['head_of_department_id']);
                    $update_user_stmt->execute();
                }
                setNotification("success", "Department updated successfully");
                echo "<script>window.location.href='../?page=department_list'</script>";
                
                // echo json_encode(['success' => true, 'message' => 'Department updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => $updateDeaprtment['message']]);
            }
            break;
            
        case 'toggle_status':
            $department_id = (int)$_POST['department_id'];
            $is_active = (int)$_POST['is_active'];
            
            $toggleStatus = toggleDepartmentStatus($conn, $department_id, $is_active, $_SESSION['userid']);
            
            if ($toggleStatus['success'] == true) {
               echo json_encode(['success' => true, 'message' => $toggleStatus['message']]);
            } else {
               echo json_encode(['success' => false, 'message' => $toggleStatus['error']]);
            }
            break;
            
        case 'delete_department':
            $department_id = (int)$_POST['department_id'];
            
            // Check if department has child departments
            $check_sql = "SELECT COUNT(*) as count FROM departments WHERE parent_department_id = ? AND deleted_at IS NULL";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $department_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $child_count = $check_result->fetch_assoc()['count'];
            
            if ($child_count > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete department with child departments']);
                break;
            }
            
            // Check if department has users
            $user_sql = "SELECT COUNT(*) as count FROM users WHERE department_id = ? AND deleted_at IS NULL";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $department_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user_count = $user_result->fetch_assoc()['count'];
            
            if ($user_count > 0) {
                setNotification("error", "Cannot delete department with assigned users");
                echo "<script>window.location.href='../?page=department_list'</script>";
                break;
            }
            
            // Soft delete department
            $deleteDepartment =deleteDepartment($conn, $department_id, $_SESSION['userid']);
            
            if ($deleteDepartment['success'] == true) {
                echo json_encode(['success' => true, 'message' => 'Department deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => $deleteDepartment['error']]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>