<?php
session_start();
    require_once __DIR__ . "/../functions/rank_functions.php";
    require_once __DIR__ . "/../functions/notification_functions.php";
    require_once __DIR__ . "/../configs.php";
    $conn = openConn();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_rank') {
            $rank_name = $conn->real_escape_string($_POST['rank_name']);
            $description = $conn->real_escape_string($_POST['description']);
            $sort_order = (int) $_POST['sort_order'];
            $created_by = $_SESSION['userid'];
            
           $data = [
            'rank_name' => $rank_name,
            'description' => $description,
            'sort_order' => $sort_order,
            'created_by' => $created_by
           ];
           $newRank = insertRank($conn,$data);
           if($newRank['success'] == true){
            setNotification("success", "Rank created successfully!");
            echo "<script>window.location.href='../?page=manage_ranks';</script>";
            
           }else{
            setNotification("error", $newRank['message']);
            echo "<script>window.location.href='../?page=manage_ranks';</script>";
           }
        }
        
        elseif ($_POST['action'] === 'edit_rank') {
            $rank_id = (int) $_POST['rank_id'];
            $rank_name = $conn->real_escape_string($_POST['rank_name']);
            $description = $conn->real_escape_string($_POST['description']);
            $sort_order = (int) $_POST['sort_order'];
            $updated_by = $_SESSION['userid'];
            
            // Check if rank name already exists (excluding current rank)
            $check_sql = "SELECT id FROM staff_ranks WHERE rank_name = ? AND id != ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("si", $rank_name, $rank_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Rank name already exists!";
            } else {
                // Update rank
                $sql = "UPDATE staff_ranks SET rank_name = ?, description = ?, sort_order = ?, updated_by = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiii", $rank_name, $description, $sort_order, $updated_by, $rank_id);
                
                if ($stmt->execute()) {
                    $message = "Rank updated successfully!";
                } else {
                    $error = "Error updating rank: " . $conn->error;
                }
                $stmt->close();
            }
        }
        
        elseif ($_POST['action'] === 'toggle_status') {
            $rank_id = (int) $_POST['rank_id'];
            $updated_by = $_SESSION['userid'];
            
            // Get current status
            $sql = "SELECT is_active FROM staff_ranks WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $rank_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $rank = $result->fetch_assoc();
            $stmt->close();
            
            // Toggle status
            $new_status = $rank['is_active'] ? 0 : 1;
            $sql = "UPDATE staff_ranks SET is_active = ?, updated_by = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $new_status, $updated_by, $rank_id);
            
            if ($stmt->execute()) {
                $message = "Rank status updated successfully!";
            } else {
                $error = "Error updating rank status: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>