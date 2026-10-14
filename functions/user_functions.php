<?php
        function selectUserByEmail(mysqli $conn, string $email){
            if($conn === false){
                exit;
            } 

            $sql ="SELECT * FROM users WHERE email='$email' AND deleted_at IS NULL";
            if($result =$conn->query($sql)){
                $user = $result->fetch_assoc();
                return $user;
            } else{
                return $conn->error;
            }
        }
        
       function recordUserLogin(mysqli $conn, array $data) {
            $sql = "INSERT INTO `login_logs` (`user_id`, `email`, `session_id`, `ip_address`, `user_agent`, `status`, `failure_reason`) VALUES (?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                exit();
            }
            $user_id        = $data['user_id'] ?? NULL;
            $failure_reason = $data['failure_reason'] ?? NULL;
            $stmt->bind_param("issssss", $user_id, $data['email'], $data['session_id'], $data['ip_address'], $data['user_agent'], $data['status'], $failure_reason);
            return ($stmt->execute()) ? $stmt->insert_id : $stmt->errno;
        }
          
        function selectAllUsers(mysqli $conn){
            if($conn === false){
                exit;
            }
            $sql = "SELECT * FROM users WHERE deleted_at IS NULL ORDER BY names ";
            if($result = $conn->query($sql)){
                $users = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $users;
            } else{
                return $conn->error;
            }
        }
        function selectUserById(mysqli $conn, int $id){
            if($conn === false){
                exit;
            }
            $sql = "SELECT * FROM users WHERE id ='$id'";
            if($result = $conn->query($sql)){
                $user = $result->fetch_assoc();
                $result->free();
                return $user;
            }else{
                return $conn->error;
            }
        }

        function EditUserDetails(
                            mysqli $conn,
                            string $name,
                            string $phone,
                            string $nida,
                            string $address,
                            string $birthdate,
                            string $email,
                            string $password,
                            int $id


        ){
            if($conn === false){
                exit;
            }
            $sql = "UPDATE `users` SET `name` = '$name', `phone` ='$phone',`nida`='$nida', `address` ='$address',  `birthdate` ='$birthdate', `email`= '$email', `password`='$password' WHERE `id` ='$id'";
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }

            
        }
        function updateUserStatus(mysqli $conn, string $status, int $id){
            if($conn === false){
                exit;
            }
            $sql = "UPDATE `users` SET `status` ='$status' WHERE id='$id'";
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }
        }

function createUser(mysqli $conn, array $data){
    if($conn === false){
        exit();
    }
    $sql = "INSERT INTO `users`(`names`, `phone`, `email`, `password`, `usertype`, `user_level`, `status`, `rank_id`, `department_id`, `store_id`) VALUES (?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return ['success' => false, 'message' => $conn->error];
    }
    $stmt->bind_param("sssssisiii",
        $data['names'],
        $data['phone'],
        $data['email'],
        $data['password'],
        $data['usertype'],
        $data['userlevel'],
        $data['status'],
        $data['rank_id'],
        $data['department_id'],
        $data['store_id']
    );
    return ($stmt->execute())
        ? ['success' => true, 'user_id' => $stmt->insert_id, 'message' => 'User created successfully']
        : ['success' => false, 'message' => $stmt->error];
}

        // function to return json
        function selectAllUsersInJSONFormat(mysqli $conn){
            if($conn === false){
                exit;
            }
            $sql = "SELECT * FROM users ORDER BY name";
            if($result = $conn->query($sql)){
                $user = $result->fetch_assoc();
                $result->free();
                return json_encode( $user);
            }else{
                return json_encode( $conn->error);
            }
        }

        function searchUserByName(mysqli $conn, string $searchKey =''){
            if ($conn === false) {
                exit;
            }
            $sql = "SELECT * FROM users WHERE status != 'deleted'";
            if(!empty($searchKey)){
                $searchKey = $conn->real_escape_string($searchKey);
                $sql .= "AND (name LIKE '%$searchKey%')";

            }
            $sql .= " ORDER BY name";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
            
        }
        function selectUserByName(mysqli $conn, string $userName){
            if ($conn === false) {
                exit;
            }
            $sql = "SELECT * FROM users WHERE name LIKE '$userName'";
           
          
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function softDeleteUser(mysqli $conn, int $userId){
            if($conn === false){
                exit();
            }
            $sql = "UPDATE `users` SET   `deleted_at` =? WHERE id=?";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
                $deleted_at = date("Y-m-d H:m:s");
            $stmt->bind_param("si", $deleted_at, $userId);
            if($stmt->execute()=== true){
                return true;
            } else{
                return $stmt->error;
            }
        }
         function addUserToEmployee(mysqli $conn, array $data){
            $sql = "INSERT INTO employees (user_id, employee_code, shift_start, shift_end) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("isss", $data['user_id'], $data['employee_code'], $data['shift_start'], $data['shift_end']);
            if($stmt->execute()=== true){
                return true;
            } else{
                return $stmt->error;
            }
        }

        function automateUserToEmployee(mysqli $conn, array $data){
            $sql = "SELECT * FROM employees WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("i", $data['user_id']);
            if($stmt->execute()=== true){
                $result = $stmt->get_result();
                if($result->num_rows > 0){
                    return true;
                } else{
                    return addUserToEmployee($conn, $data);
                }
            } else{
                return $stmt->error;
            }
        }
        
        function updateUserDetails(mysqli $conn, array $data){
            $sql = "UPDATE `users` SET  `names`=?,`phone`=?,`email`=?,`usertype`=?,`user_level`=?,`rank_id`=?,`department_id`=? WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("sssssiii", $data['names'], $data['phone'], $data['email'], $data['usertype'], $data['user_level'], $data['rank_id'], $data['department_id'], $data['user_id']);
            return ($stmt->execute()) ? true : false;
        }
         function selectEmploYdeeDetailsByUserId(mysqli $conn, int $id){
            $sql = "SELECT * FROM employees WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("i", $id);
            return ($stmt->execute()) ? $stmt->get_result()->fetch_assoc() : false;
        }

?>