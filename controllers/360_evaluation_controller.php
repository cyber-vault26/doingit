<?php
session_start();
if(!$_SESSION){
    echo "<script> window.history.back() </script>";
}
require_once "../configs.php";
require_once "../functions/department_functions.php";
require_once "../functions/user_functions.php";
require_once "../functions/360_evaluation_functions.php";

$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    // Handle add question action
    if(isset($_POST['action']) && $_POST['action'] === 'add_question'){
        $category = $conn->real_escape_string($_POST['category']);
        $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $question_text = $conn->real_escape_string($_POST['question_text']);
        $question_type = $conn->real_escape_string($_POST['question_type']);
        $weight = (float) $_POST['weight'];
        $created_by = $_SESSION['userid'];
        print_r($_POST);
        
        $questionData = [
            'category' => $category,
            'department_id' => $department_id,
            'question_text' => $question_text,
            'question_type' => $question_type,
            'weight' => $weight,
            'created_by' => $created_by
        ];
        print_r($questionData);
        $result = createReviewQuestion($conn, $questionData);
        
        if ($result === true) {
            echo "<script>alert('Review question added successfully!'); window.location.href='../?page=add_review_questions';</script>";
        } else {
            echo "<script>alert('Error adding review question: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle toggle question status action
    elseif(isset($_POST['action']) && $_POST['action'] === 'toggle_question'){
        $question_id = (int) $_POST['question_id'];
        $is_active = (int) $_POST['is_active'];
        $updated_by = $_SESSION['userid'];
        
        $result = toggleReviewQuestionStatus($conn, $question_id, $is_active, $updated_by);
        
        if ($result === true) {
            echo "<script>alert('Question status updated successfully!'); window.location.href='../?page=review_questions_list';</script>";
        } else {
            echo "<script>alert('Error updating question status: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle delete question action
    elseif(isset($_POST['action']) && $_POST['action'] === 'delete_question'){
        $question_id = (int) $_POST['question_id'];
        
        $result = deleteReviewQuestion($conn, $question_id);
        
        if ($result === true) {
            echo "<script>alert('Review question deleted successfully!'); window.location.href='../?page=review_questions_list';</script>";
        } else {
            echo "<script>alert('Error deleting review question: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle edit question action (if needed in future)
    elseif(isset($_POST['action']) && $_POST['action'] === 'edit_question'){
        $question_id = (int) $_POST['question_id'];
        $category = $conn->real_escape_string($_POST['category']);
        $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $question_text = $conn->real_escape_string($_POST['question_text']);
        $question_type = $conn->real_escape_string($_POST['question_type']);
        $weight = (float) $_POST['weight'];
        $updated_by = $_SESSION['userid'];
        
        $questionData = [
            'category' => $category,
            'department_id' => $department_id,
            'question_text' => $question_text,
            'question_type' => $question_type,
            'weight' => $weight,
            'updated_by' => $updated_by
        ];
        
        $result = updateReviewQuestion($conn, $question_id, $questionData);
        
        if ($result === true) {
            echo "<script>alert('Review question updated successfully!'); window.location.href='../?page=review_questions_list';</script>";
        } else {
            echo "<script>alert('Error updating review question: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle performance review submission
    elseif(isset($_POST['action']) && $_POST['action'] === 'submit_review'){
        $reviewer_id = $_SESSION['userid'];
        $reviewee_id = (int) $_POST['reviewee_id'];
        $review_period = $conn->real_escape_string($_POST['review_period']);
        $review_type = $conn->real_escape_string($_POST['review_type']);
        
        // Create review record
        $sql = "INSERT INTO performance_reviews (reviewer_id, reviewee_id, review_period, review_type, status, created_by) 
                VALUES (?, ?, ?, ?, 'completed', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $reviewer_id, $reviewee_id, $review_period, $review_type, $reviewer_id);
        
        if ($stmt->execute()) {
            $review_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert responses
            foreach ($_POST['responses'] as $question_id => $response) {
                $rating = isset($response['rating']) ? (int) $response['rating'] : null;
                $response_text = isset($response['text']) ? $conn->real_escape_string($response['text']) : null;
                
                $sql = "INSERT INTO review_responses (review_id, question_id, rating, response_text, created_by) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiss", $review_id, $question_id, $rating, $response_text, $reviewer_id);
                $stmt->execute();
                $stmt->close();
            }
            
            echo "<script>alert('Performance review submitted successfully!'); window.location.href='../?page=360_perfomance';</script>";
        } else {
            echo "<script>alert('Error submitting performance review: " . addslashes($conn->error) . "'); window.history.back();</script>";
        }
    }
    
    // Handle review period management
    elseif(isset($_POST['action']) && $_POST['action'] === 'create_review_period'){
        $period_name = $conn->real_escape_string($_POST['period_name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $description = $conn->real_escape_string($_POST['description']);
        $created_by = $_SESSION['userid'];
        
        $periodData = [
            'period_name' => $period_name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_active' => $is_active,
            'description' => $description,
            'created_by' => $created_by
        ];
        
        $result = createReviewPeriod($conn, $periodData);
        
        if ($result === true) {
            echo "<script>alert('Review period created successfully!'); window.location.href=' ../?page=review_periods';</script>";
        } else {
            echo "<script>alert('Error creating review period: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle update review period
    elseif(isset($_POST['action']) && $_POST['action'] === 'update_review_period'){
        $period_id = (int) $_POST['period_id'];
        $period_name = $conn->real_escape_string($_POST['period_name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $description = $conn->real_escape_string($_POST['description']);
        $updated_by = $_SESSION['userid'];
        
        $periodData = [
            'period_name' => $period_name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_active' => $is_active,
            'description' => $description,
            'updated_by' => $updated_by
        ];
        
        $result = updateReviewPeriod($conn, $period_id, $periodData);
        
        if ($result === true) {
            echo "<script>alert('Review period updated successfully!'); window.location.href='../?page=review_periods';</script>";
        } else {
            echo "<script>alert('Error updating review period: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle toggle review period status
    elseif(isset($_POST['action']) && $_POST['action'] === 'toggle_review_period'){
        $period_id = (int) $_POST['period_id'];
        $is_active = (int) $_POST['is_active'];
        $updated_by = $_SESSION['userid'];
        
        $result = toggleReviewPeriodStatus($conn, $period_id, $is_active, $updated_by);
        
        if ($result === true) {
            echo "<script>alert('Review period status updated successfully!'); window.location.href='../?page=review_periods';</script>";
        } else {
            echo "<script>alert('Error updating review period status: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle delete review period
    elseif(isset($_POST['action']) && $_POST['action'] === 'delete_review_period'){
        $period_id = (int) $_POST['period_id'];
        
        $result = deleteReviewPeriod($conn, $period_id);
        
        if ($result === true) {
            echo "<script>alert('Review period deleted successfully!'); window.location.href='../?page=review_periods';</script>";
        } else {
            echo "<script>alert('Error deleting review period: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle assign reviewers for user
    elseif(isset($_POST['action']) && $_POST['action'] === 'assign_reviewers'){
        $user_id = (int) $_POST['user_id'];
        $review_period_id = (int) $_POST['review_period_id'];
        
        $assignments = assignReviewersForUser($conn, $user_id, $review_period_id);
        
        if ($assignments) {
            echo "<script>alert('Reviewers assigned successfully! " . count($assignments) . " assignments created.'); window.location.href='../?page=review_assignments&period_id=" . $review_period_id . "';</script>";
        } else {
            echo "<script>alert('Error assigning reviewers. Please try again.'); window.history.back();</script>";
        }
    }
    
    // Handle update assignment status
    elseif(isset($_POST['action']) && $_POST['action'] === 'update_assignment_status'){
        $review_id = (int) $_POST['review_id'];
        $status = $conn->real_escape_string($_POST['status']);
        
        $result = updateReviewAssignmentStatus($conn, $review_id, $status);
        
        if ($result === true) {
            echo "<script>alert('Assignment status updated successfully!'); window.location.href='../?page=review_assignments';</script>";
        } else {
            echo "<script>alert('Error updating assignment status: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
    
    // Handle delete assignment
    elseif(isset($_POST['action']) && $_POST['action'] === 'delete_assignment'){
        $review_id = (int) $_POST['review_id'];
        
        $result = deleteReviewAssignment($conn, $review_id);
        
        if ($result === true) {
            echo "<script>alert('Assignment deleted successfully!'); window.location.href='../?page=review_assignments';</script>";
        } else {
            echo "<script>alert('Error deleting assignment: " . addslashes($result) . "'); window.history.back();</script>";
        }
    }
}

$conn->close();
?>
