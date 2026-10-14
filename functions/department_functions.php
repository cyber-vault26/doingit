<?php
function createDepartment(mysqli $conn, array $data){
    $sql = "INSERT INTO `departments`( `name`, `description`, `parent_department_id`, `department_code`, `head_of_department_id`, `is_active`,  `created_by`) VALUES( ?, ?, ?, ?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisiii", $data['name'], $data['description'], $data['parent_department_id'], $data['department_code'], $data['head_of_department_id'], $data['is_active'], $data['created_by']);
    return ($stmt->execute()) ? ['success' => true, "message" => "Department created successfully", "id" => $stmt->insert_id] : ['success' => false, "message" => "Failed to create department", "error" => $stmt->error ];
}

function listdepartments(mysqli $conn){
    $sql = "SELECT departments.*, users.names as creator FROM departments LEFT JOIN users ON departments.created_by = users.id WHERE departments.deleted_at IS NULL ORDER BY departments.name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDepartmentById(mysqli $conn, int $department_id){
    $sql = "SELECT departments.*, users.names as creator FROM departments LEFT JOIN users ON departments.created_by = users.id WHERE departments.id = ? AND departments.deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateDepartment(mysqli $conn, int $department_id, array $data){
    $sql = "UPDATE departments SET name = ?, description = ?, parent_department_id = ?, department_code = ?, head_of_department_id = ?, is_active = ?, updated_at = NOW(), updated_by = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisiiii", $data['name'], $data['description'], $data['parent_department_id'], $data['department_code'], $data['head_of_department_id'], $data['is_active'], $data['updated_by'], $department_id);
    return ($stmt->execute()) ? ['success' => true, "message" => "Department updated successfully"] : ['success' => false, "message" => "Failed to update department", "error" => $stmt->error];
}

function toggleDepartmentStatus(mysqli $conn, int $department_id, int $is_active, int $updated_by){
    $sql = "UPDATE departments SET is_active = ?, updated_at = NOW(), updated_by = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $is_active, $updated_by, $department_id);
    return ($stmt->execute()) ? ['success' => true, "message" => "Department status updated successfully"] : ['success' => false, "message" => "Failed to update department status", "error" => $stmt->error];
}

function deleteDepartment(mysqli $conn, int $department_id, int $deleted_by){
    $sql = "UPDATE departments SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $deleted_by, $department_id);
    return ($stmt->execute()) ? ['success' => true, "message" => "Department deleted successfully"] : ['success' => false, "message" => "Failed to delete department", "error" => $stmt->error];
}

function getActiveDepartments(mysqli $conn){
    $sql = "SELECT id, name, department_code FROM departments WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function checkDepartmentHasChildren(mysqli $conn, int $department_id){
    $sql = "SELECT COUNT(*) as count FROM departments WHERE parent_department_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['count'] > 0;
}

function checkDepartmentHasUsers(mysqli $conn, int $department_id){
    $sql = "SELECT COUNT(*) as count FROM users WHERE department_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['count'] > 0;
}

function getDepartmentUsers(mysqli $conn, int $department_id){
    $sql = "SELECT u.*, dur.role FROM users u LEFT JOIN department_user_roles dur ON u.id = dur.user_id AND dur.department_id = ? WHERE u.deleted_at IS NULL ORDER BY u.names";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDepartmentStats(mysqli $conn){
    $stats = [];
    
    // Total departments
    $sql = "SELECT COUNT(*) as total FROM departments WHERE deleted_at IS NULL";
    $result = $conn->query($sql);
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Active departments
    $sql = "SELECT COUNT(*) as active FROM departments WHERE is_active = 1 AND deleted_at IS NULL";
    $result = $conn->query($sql);
    $stats['active'] = $result->fetch_assoc()['active'];
    
    // Inactive departments
    $sql = "SELECT COUNT(*) as inactive FROM departments WHERE is_active = 0 AND deleted_at IS NULL";
    $result = $conn->query($sql);
    $stats['inactive'] = $result->fetch_assoc()['inactive'];
    
    // Departments without heads
    $sql = "SELECT COUNT(*) as without_head FROM departments WHERE (head_of_department_id IS NULL OR head_of_department_id = 0) AND deleted_at IS NULL";
    $result = $conn->query($sql);
    $stats['without_head'] = $result->fetch_assoc()['without_head'];
    
    return $stats;
}

function getDepartmentDataByUserId(mysqli $conn, int $userId){
    $daparmentData = [];
    $sql = "SELECT d.* FROM departments d JOIN users u ON d.id = u.department_id WHERE u.id = ? AND d.deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc() ?: [];
    
    $daparmentData['department_details'] = $result;
    // check if department level role
    $sql = "SELECT * FROM department_user_roles WHERE user_id = ? AND department_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $result['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc() ?: [];
    $daparmentData['department_role'] = $result;
    return $daparmentData;
   
}

?>