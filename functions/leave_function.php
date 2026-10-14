<?php
        function selectLeaveRequestsByStaffId(mysqli $conn, int $staffId){
            if($conn === false){
                exit;
            }
            $staffId = $conn->real_escape_string($staffId);
            $sql = "SELECT * FROM leave_request WHERE staff_id = '$staffId'";
            if($result = $conn->query($sql)){
                $leaveRequests = [];
                while($row = $result->fetch_assoc()){
                    $leaveRequests[] = $row;
                }
                $result->free();
                return $leaveRequests;
            }else{
                return $conn->error;
            }
        }


?>