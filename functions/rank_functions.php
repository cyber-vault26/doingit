<?php
        function insertRank(mysqli $conn, array $data) : array {
             $check_sql = "SELECT id FROM staff_ranks WHERE rank_name = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("s", $data['rank_name']);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                return ['success' => false, 'message' => 'Rank name already exists!'];
                exit();
            }
              // Insert new rank
                $sql = "INSERT INTO staff_ranks (rank_name, description, sort_order, created_by) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssii", $data['rank_name'], $data['description'], $data['sort_order'], $data['created_by']);
                
        return ($stmt->execute()) ? ['success' => true, 'message' => 'Rank created successfully!'] : ['success' => false, 'message' => 'Error creating rank: ' . $conn->error];
           
        }


        function listRanks(mysqli $conn){
            $sql = "SELECT * FROM staff_ranks WHERE is_active = 1 ORDER BY sort_order ASC";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
?>