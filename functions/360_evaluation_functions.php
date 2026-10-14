<?php

// Get all review questions with department names
function getAllReviewQuestions(mysqli $conn) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT rq.*, d.name as department_name 
            FROM review_questions rq 
            LEFT JOIN departments d ON rq.department_id = d.id 
            ORDER BY rq.category, rq.department_id, rq.id";
    
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return false;
}

// Get review questions with filters
function getReviewQuestionsByFilters(mysqli $conn, $category = '', $department_id = null, $is_active = null) {
    if ($conn === false) {
        return false;
    }
    
    $where_conditions = [];
    $params = [];
    $types = "";
    
    if ($category) {
        $where_conditions[] = "rq.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    if ($department_id !== null) {
        if ($department_id === 'general') {
            $where_conditions[] = "rq.department_id IS NULL";
        } else {
            $where_conditions[] = "rq.department_id = ?";
            $params[] = $department_id;
            $types .= "i";
        }
    }
    
    if ($is_active !== null) {
        $where_conditions[] = "rq.is_active = ?";
        $params[] = $is_active;
        $types .= "i";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $sql = "SELECT rq.*, d.name as department_name 
            FROM review_questions rq 
            LEFT JOIN departments d ON rq.department_id = d.id 
            $where_clause
            ORDER BY rq.category, rq.department_id, rq.id";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $result = $conn->query($sql);
    }
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return false;
}

// Get review question by ID
function getReviewQuestionById(mysqli $conn, int $question_id) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT rq.*, d.name as department_name 
            FROM review_questions rq 
            LEFT JOIN departments d ON rq.department_id = d.id 
            WHERE rq.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    $stmt->close();
    
    return $question ?: false;
}

// Create new review question
function createReviewQuestion(mysqli $conn, array $questionData) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "INSERT INTO review_questions (category, department_id, question_text, question_type, weight, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    $department_id = $questionData['department_id'];
    if ($department_id === null) {
        $stmt->bind_param("ssssdi", $questionData['category'], $department_id, $questionData['question_text'], 
                          $questionData['question_type'], $questionData['weight'], $questionData['created_by']);
    } else {
        $stmt->bind_param("sissdi", $questionData['category'], $department_id, $questionData['question_text'], 
                          $questionData['question_type'], $questionData['weight'], $questionData['created_by']);
    }
    
    return($stmt->execute()) ? true : $stmt->error;
}

// Update review question
function updateReviewQuestion(mysqli $conn, int $question_id, array $questionData) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "UPDATE review_questions SET category = ?, department_id = ?, question_text = ?, question_type = ?, weight = ?, updated_by = ?, updated_at = NOW() WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    $department_id = $questionData['department_id'];
    if ($department_id === null) {
        $stmt->bind_param("sssdiii", $questionData['category'], $department_id, $questionData['question_text'], 
                          $questionData['question_type'], $questionData['weight'], $questionData['updated_by'], $question_id);
    } else {
        $stmt->bind_param("sisdiii", $questionData['category'], $department_id, $questionData['question_text'], 
                          $questionData['question_type'], $questionData['weight'], $questionData['updated_by'], $question_id);
    }
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Toggle review question status
function toggleReviewQuestionStatus(mysqli $conn, int $question_id, int $is_active, int $updated_by) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "UPDATE review_questions SET is_active = ?, updated_by = ?, updated_at = NOW() WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $is_active, $updated_by, $question_id);
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Delete review question (with safety check)
function deleteReviewQuestion(mysqli $conn, int $question_id) {
    if ($conn === false) {
        return false;
    }
    
    // Check if question has responses
    $check_sql = "SELECT COUNT(*) as count FROM review_responses WHERE question_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $question_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($count > 0) {
        return "Cannot delete question - it has existing responses. Deactivate it instead.";
    }
    
    // Delete the question
    $sql = "DELETE FROM review_questions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Get review questions for a specific department
function getReviewQuestionsByDepartment(mysqli $conn, int $department_id) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT rq.*, d.name as department_name 
            FROM review_questions rq 
            LEFT JOIN departments d ON rq.department_id = d.id 
            WHERE (rq.department_id = ? OR rq.department_id IS NULL) 
            AND rq.is_active = 1
            ORDER BY rq.category, rq.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $questions;
}

// Get general review questions (apply to all departments)
function getGeneralReviewQuestions(mysqli $conn) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT * FROM review_questions 
            WHERE department_id IS NULL 
            AND is_active = 1 
            ORDER BY category, id";
    
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return false;
}

// Get department-specific review questions
function getDepartmentSpecificReviewQuestions(mysqli $conn, int $department_id) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT rq.*, d.name as department_name 
            FROM review_questions rq 
            LEFT JOIN departments d ON rq.department_id = d.id 
            WHERE rq.department_id = ? 
            AND rq.is_active = 1 
            ORDER BY rq.category, rq.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $questions;
}

// Get review questions statistics
function getReviewQuestionsStatistics(mysqli $conn) {
    if ($conn === false) {
        return false;
    }
    
    $stats = [];
    
    // Total questions
    $result = $conn->query("SELECT COUNT(*) as total FROM review_questions");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Active questions
    $result = $conn->query("SELECT COUNT(*) as active FROM review_questions WHERE is_active = 1");
    $stats['active'] = $result->fetch_assoc()['active'];
    
    // Department-specific questions
    $result = $conn->query("SELECT COUNT(*) as dept_specific FROM review_questions WHERE department_id IS NOT NULL");
    $stats['department_specific'] = $result->fetch_assoc()['dept_specific'];
    
    // General questions
    $result = $conn->query("SELECT COUNT(*) as general FROM review_questions WHERE department_id IS NULL");
    $stats['general'] = $result->fetch_assoc()['general'];
    
    // Questions by category
    $result = $conn->query("SELECT category, COUNT(*) as count FROM review_questions GROUP BY category ORDER BY count DESC");
    $stats['by_category'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $stats;
}

// Get review questions by category
function getReviewQuestionsByCategory(mysqli $conn, string $category) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT rq.*, d.name as department_name 
            FROM review_questions rq 
            LEFT JOIN departments d ON rq.department_id = d.id 
            WHERE rq.category = ? 
            AND rq.is_active = 1 
            ORDER BY rq.department_id, rq.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $questions;
}

// Search review questions
function searchReviewQuestions(mysqli $conn, string $search_term) {
    if ($conn === false) {
        return false;
    }
    
    $search_term = '%' . $search_term . '%';
    
    $sql = "SELECT rq.*, d.name as department_name 
            FROM review_questions rq 
            LEFT JOIN departments d ON rq.department_id = d.id 
            WHERE rq.question_text LIKE ? 
            OR rq.category LIKE ?
            ORDER BY rq.category, rq.department_id, rq.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $questions;
}

// =============================================
// REVIEW PERIODS FUNCTIONS
// =============================================

// Get all review periods
function getAllReviewPeriods(mysqli $conn) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT rp.*, 
                (SELECT COUNT(*) FROM performance_reviews pr WHERE pr.review_period_id = rp.id) as total_reviews,
                (SELECT COUNT(*) FROM performance_reviews pr WHERE pr.review_period_id = rp.id AND pr.status = 'completed') as completed_reviews
            FROM review_periods rp 
            ORDER BY rp.start_date DESC";
    
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return false;
}

// Get active review period
function getActiveReviewPeriod(mysqli $conn) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT * FROM review_periods WHERE is_active = TRUE ORDER BY start_date DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_assoc();
    }
    
    return false;
}

// Get review period by ID
function getReviewPeriodById(mysqli $conn, int $period_id) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT * FROM review_periods WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $period = $result->fetch_assoc();
    $stmt->close();
    
    return $period ?: false;
}

// Create new review period
function createReviewPeriod(mysqli $conn, array $periodData) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "INSERT INTO review_periods (period_name, start_date, end_date, is_active, description, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisi", 
        $periodData['period_name'], 
        $periodData['start_date'], 
        $periodData['end_date'], 
        $periodData['is_active'], 
        $periodData['description'], 
        $periodData['created_by']
    );
    
    return ($stmt->execute()) ? true : $stmt->error;
}

// Update review period
function updateReviewPeriod(mysqli $conn, int $period_id, array $periodData) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "UPDATE review_periods SET 
                period_name = ?, 
                start_date = ?, 
                end_date = ?, 
                is_active = ?, 
                description = ?, 
                updated_by = ?, 
                updated_at = NOW() 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisi", 
        $periodData['period_name'], 
        $periodData['start_date'], 
        $periodData['end_date'], 
        $periodData['is_active'], 
        $periodData['description'], 
        $periodData['updated_by'], 
        $period_id
    );
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Toggle review period status
function toggleReviewPeriodStatus(mysqli $conn, int $period_id, int $is_active, int $updated_by) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "UPDATE review_periods SET is_active = ?, updated_by = ?, updated_at = NOW() WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $is_active, $updated_by, $period_id);
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Delete review period (with safety check)
function deleteReviewPeriod(mysqli $conn, int $period_id) {
    if ($conn === false) {
        return false;
    }
    
    // Check if period has reviews
    $check_sql = "SELECT COUNT(*) as count FROM performance_reviews WHERE review_period_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $period_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($count > 0) {
        return "Cannot delete review period - it has existing reviews. Deactivate it instead.";
    }
    
    // Delete the period
    $sql = "DELETE FROM review_periods WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $period_id);
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Get review period statistics
function getReviewPeriodStatistics(mysqli $conn, int $period_id) {
    if ($conn === false) {
        return false;
    }
    
    $stats = [];
    
    // Total reviews
    $sql = "SELECT COUNT(*) as total FROM performance_reviews WHERE review_period_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_reviews'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Completed reviews
    $sql = "SELECT COUNT(*) as completed FROM performance_reviews WHERE review_period_id = ? AND status = 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['completed_reviews'] = $result->fetch_assoc()['completed'];
    $stmt->close();
    
    // Pending reviews
    $stats['pending_reviews'] = $stats['total_reviews'] - $stats['completed_reviews'];
    
    // Completion rate
    $stats['completion_rate'] = $stats['total_reviews'] > 0 ? 
        round(($stats['completed_reviews'] / $stats['total_reviews']) * 100, 2) : 0;
    
    return $stats;
}

// =============================================
// REVIEWER ASSIGNMENT FUNCTIONS
// =============================================

// Get user's manager (department head)
function getUserManager(mysqli $conn, int $user_id) {
    if ($conn === false) {
        return false;
    }
    
    // First get user's department
    $sql = "SELECT department_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user || !$user['department_id']) {
        return null;
    }
    
    // Then get department head from department_role table
    $sql = "SELECT user_id FROM department_user_roles WHERE department_id = ? AND role = 'department_head' AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user['department_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $dept_head = $result->fetch_assoc();
    $stmt->close();
    
    return $dept_head ? $dept_head['user_id'] : null;
}

// Get user's peers (same department, similar level)
function getUserPeers(mysqli $conn, int $user_id, int $limit = 3) {
    if ($conn === false) {
        return false;
    }
    
    // Get user's department and level
    $sql = "SELECT department_id, user_level FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        return [];
    }
    
    // Get peers from same department and level
    $sql = "SELECT id,names FROM users 
            WHERE department_id = ? 
            AND user_level = ? 
            AND id != ? 
            AND deleted_at IS NULL
            ORDER BY RAND() 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user['department_id'], $user['user_level'], $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $peers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $peers;
}

// Get user's subordinates (people in same department who are lower level)
function getUserSubordinates(mysqli $conn, int $user_id) {
    if ($conn === false) {
        return false;
    }
    
    // First get user's department and level
    $sql = "SELECT department_id, user_level FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user || !$user['department_id']) {
        return [];
    }
    
    // Get people in same department with lower user_level (higher numbers typically mean lower level)
    $sql = "SELECT id, names FROM users 
            WHERE department_id = ? 
            AND user_level > ? 
            AND deleted_at IS NULL
            ORDER BY user_level, names";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user['department_id'], $user['user_level']);
    $stmt->execute();
    $result = $stmt->get_result();
    $subordinates = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $subordinates;
}

// Get user's department
function getUserDepartment(mysqli $conn, int $user_id) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT department_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dept = $result->fetch_assoc();
    $stmt->close();
    
    return $dept ? $dept['department_id'] : null;
}

// Assign reviewers for a user in a review period
function assignReviewersForUser(mysqli $conn, int $user_id, int $review_period_id) {
    if ($conn === false) {
        return false;
    }
    
    $assignments = [];
    
    // 1. Self-evaluation (always included)
    $assignments[] = [
        'reviewee_id' => $user_id,
        'reviewer_id' => $user_id,
        'review_type' => 'self',
        'review_period_id' => $review_period_id
    ];
    
    // 2. Manager evaluation
    $managerId = getUserManager($conn, $user_id);
    if ($managerId) {
        $assignments[] = [
            'reviewee_id' => $user_id,
            'reviewer_id' => $managerId,
            'review_type' => 'manager',
            'review_period_id' => $review_period_id
        ];
    }
    
    // 3. Peer evaluations (2-3 peers)
    $peers = getUserPeers($conn, $user_id, 3);
    foreach ($peers as $peer) {
        $assignments[] = [
            'reviewee_id' => $user_id,
            'reviewer_id' => $peer['id'],
            'review_type' => 'peer',
            'review_period_id' => $review_period_id
        ];
    }
    
    // 4. Subordinate evaluations (if applicable)
    $subordinates = getUserSubordinates($conn, $user_id);
    foreach ($subordinates as $subordinate) {
        $assignments[] = [
            'reviewee_id' => $user_id,
            'reviewer_id' => $subordinate['id'],
            'review_type' => 'subordinate',
            'review_period_id' => $review_period_id
        ];
    }
    
    // Insert all assignments
    foreach ($assignments as $assignment) {
        // Check if assignment already exists
        $check_sql = "SELECT id FROM performance_reviews 
                      WHERE reviewee_id = ? AND reviewer_id = ? AND review_period_id = ? AND review_type = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iiis", $assignment['reviewee_id'], $assignment['reviewer_id'], 
                                    $assignment['review_period_id'], $assignment['review_type']);
        $check_stmt->execute();
        $exists = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if (!$exists) {
            $sql = "INSERT INTO performance_reviews (reviewee_id, reviewer_id, review_period_id, review_type, status, created_by) 
                    VALUES (?, ?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiisi", $assignment['reviewee_id'], $assignment['reviewer_id'], 
                                $assignment['review_period_id'], $assignment['review_type'], $_SESSION['userid']);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    return $assignments;
}

// Get all assignments for a review period
function getReviewAssignments(mysqli $conn, int $review_period_id = null) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT pr.*, 
                u_reviewer.names as reviewer_name, 
                
                u_reviewee.names as reviewee_name,
                d.name as department_name
            FROM performance_reviews pr
            LEFT JOIN users u_reviewer ON pr.reviewer_id = u_reviewer.id
            LEFT JOIN users u_reviewee ON pr.reviewee_id = u_reviewee.id
            LEFT JOIN departments d ON u_reviewee.department_id = d.id";
    
    $params = [];
    $types = "";
    
    if ($review_period_id !== null) {
        $sql .= " WHERE pr.review_period_id = ?";
        $params[] = $review_period_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY u_reviewee.names, pr.review_type";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $result = $conn->query($sql);
    }
    
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    
    return $assignments;
}

// Get assignments for a specific user (as reviewee)
function getUserReviewAssignments(mysqli $conn, int $user_id, int $review_period_id = null) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT pr.*, 
                u_reviewer.firstname as reviewer_firstname, 
                u_reviewer.lastname as reviewer_lastname,
                rp.period_name,
                rp.start_date,
                rp.end_date
            FROM performance_reviews pr
            LEFT JOIN users u_reviewer ON pr.reviewer_id = u_reviewer.id
            LEFT JOIN review_periods rp ON pr.review_period_id = rp.id
            WHERE pr.reviewee_id = ?";
    
    $params = [$user_id];
    $types = "i";
    
    if ($review_period_id) {
        $sql .= " AND pr.review_period_id = ?";
        $params[] = $review_period_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY rp.start_date DESC, pr.review_type";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $assignments;
}

// Get assignments for a specific user (as reviewer)
function getReviewerAssignments(mysqli $conn, int $reviewer_id, int $review_period_id = null) {
    if ($conn === false) {
        return false;
    }
    
    $sql = "SELECT pr.*, 
                u_reviewee.firstname as reviewee_firstname, 
                u_reviewee.lastname as reviewee_lastname,
                u_reviewee.department_id,
                d.name as department_name,
                rp.period_name,
                rp.start_date,
                rp.end_date
            FROM performance_reviews pr
            LEFT JOIN users u_reviewee ON pr.reviewee_id = u_reviewee.id
            LEFT JOIN departments d ON u_reviewee.department_id = d.id
            LEFT JOIN review_periods rp ON pr.review_period_id = rp.id
            WHERE pr.reviewer_id = ?";
    
    $params = [$reviewer_id];
    $types = "i";
    
    if ($review_period_id) {
        $sql .= " AND pr.review_period_id = ?";
        $params[] = $review_period_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY rp.start_date DESC, pr.review_type";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $assignments;
}

// Update review assignment status
function updateReviewAssignmentStatus(mysqli $conn, int $review_id, string $status, int $updated_by = null) {
    if ($conn === false) {
        return false;
    }
    
    $updated_by = $updated_by ?? $_SESSION['userid'];
    
    $sql = "UPDATE performance_reviews SET status = ?, updated_by = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $updated_by, $review_id);
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Delete review assignment
function deleteReviewAssignment(mysqli $conn, int $review_id) {
    if ($conn === false) {
        return false;
    }
    
    // Check if review has responses
    $check_sql = "SELECT COUNT(*) as count FROM review_responses WHERE review_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $review_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($count > 0) {
        return "Cannot delete review assignment - it has existing responses.";
    }
    
    // Delete the assignment
    $sql = "DELETE FROM performance_reviews WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_id);
    
    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    
    if ($success) {
        return true;
    }
    
    return $error;
}

// Get assignment statistics
function getAssignmentStatistics(mysqli $conn, int $review_period_id) {
    if ($conn === false) {
        return false;
    }
    
    $stats = [];
    
    // Total assignments
    $sql = "SELECT COUNT(*) as total FROM performance_reviews WHERE review_period_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_assignments'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // By status
    $sql = "SELECT status, COUNT(*) as count FROM performance_reviews 
            WHERE review_period_id = ? GROUP BY status";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $status_counts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $stats['by_status'] = [];
    foreach ($status_counts as $status_count) {
        $stats['by_status'][$status_count['status']] = $status_count['count'];
    }
    
    // By review type
    $sql = "SELECT review_type, COUNT(*) as count FROM performance_reviews 
            WHERE review_period_id = ? GROUP BY review_type";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $type_counts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $stats['by_type'] = [];
    foreach ($type_counts as $type_count) {
        $stats['by_type'][$type_count['review_type']] = $type_count['count'];
    }
    
    return $stats;
}

?>
