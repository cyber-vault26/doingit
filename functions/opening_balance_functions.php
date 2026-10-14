<?php

function selectOpeningBalanceBeforeDateRange(mysqli $conn, int $subId, string $date1) {
    if ($conn === false) exit();
    $sql = "SELECT SUM(CASE WHEN type = 'debit' THEN ammount ELSE 0 END) as total_debit, 
           SUM(CASE WHEN type = 'credit' THEN ammount ELSE 0 END) as total_credit 
           FROM starting_balance WHERE account_id = '$subId' AND date_ < '$date1' AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return ['debit' => floatval($data['total_debit']), 'credit' => floatval($data['total_credit'])];
    } else {
        return $conn->error;
    }
}  
 

function selectOpeningBalanceById(mysqli $conn, int $id){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM starting_balance WHERE id='$id'";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            }else{
                return $conn->error;
            }
        }

 function editOpeningBalance(mysqli $conn, int $id, int $accountId, float $amount, string $type, string $date, int $updated_by){
            if($conn=== false){
                exit;
            }
            $sql = "UPDATE `starting_balance` SET `account_id`=?, `ammount`=?, `type`=?, date_=?, updated_by=? WHERE id=?;";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit;
            }
            $stmt->bind_param("idssii", $accountId, $amount, $type,$date, $updated_by, $id);
            return ($stmt->execute()) ? true : $stmt->errno;
        }

        function selectOpeningBalance(mysqli $conn, int $subId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM starting_balance WHERE `account_id`='$subId' and deleted_at is null";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            }else{
                return $conn->error;
            }
        }
        
        function selectOpeningBalanceByDateRange(mysqli $conn, int $subId, string $date1, string $date2){
            if($conn === false){
                exit();
            }
            
            $sql = "SELECT * FROM starting_balance t
                WHERE t.account_id = '$subId' 
                AND t.date_ BETWEEN '$date1' AND '$date2' and t.deleted_at is null";
                
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        
        
          function addOpeningBalance(mysqli $conn, int $subId, float $amount, int $currencyId, float $currValue, string $type,  string $date,int $created_by){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `starting_balance`(`account_id`, `ammount`, `currency_id`,`curr_value`, `type`, `date_`,`created_by`) VALUES (?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("ididssi", $subId, $amount,$currencyId, $currValue, $type, $date,$created_by);
            return ($stmt->execute()) ? $stmt->insert_id : $conn->error;
        }
        function selectAllOpeningBalances(mysqli $conn){
            if($conn === false){
                exit();
            }
            $sql = "SELECT starting_balance.*, subsidiaries.name AS account FROM starting_balance
                        JOIN subsidiaries ON starting_balance.account_id = subsidiaries.id
                        WHERE starting_balance.deleted_at IS NULL 
                        AND subsidiaries.deleted_at IS NULL
                        ORDER BY date_ DESC, name ASC";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
        }
        function  deleteOpeningBalance(mysqli $conn, int $id,$deleted_by){
                if($conn === false){
                    exit();
                }
                $sql = "UPDATE `starting_balance` SET deleted_at =now(), deleted_by=$deleted_by WHERE id=$id ;";
                return ($conn->query($sql) === true) ? true : $conn->errno;
        }
        
         function addMinSubOpeningBalance(mysqli $conn, int $minSubId, float $amount,  float $currValue, string $type,  string $date){
                if($conn === false){
                    exit();
                }
            $sql = "INSERT INTO `min_sub_opening_balances`( `min_sub_id`, `amount`, `type`, `currValue`, `date_`) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("idsds", $minSubId, $amount, $type, $currValue, $date);
            return ($stmt->execute()) ? $stmt->insert_id : $conn->error;
        } 
        function selectAllMinSubOpeningBalances(mysqli $conn){
            if($conn === false){
                exit();
            }
            $sql = "SELECT min_sub_opening_balances.*, min_subs.name AS account FROM min_sub_opening_balances
                        JOIN min_subs ON min_sub_opening_balances.min_sub_id = min_subs.id
                        WHERE min_sub_opening_balances.deleted_at IS NULL 
                        AND min_subs.deleted_at IS NULL
                        ORDER BY date_ DESC, name ASC";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
        }  
        function deleteMinSubOpeningBalance(mysqli $conn, int $id){
                if($conn === false){
                    exit();
                }
                $now = date("Y-m-d H:m:s");
                $sql = "UPDATE `min_sub_opening_balances` SET deleted_at ='$now' WHERE id=? ;";
                $stmt = $conn->prepare($sql);
                if($stmt === false){
                    exit();
                }
                $stmt->bind_param("i", $id);
                return ($stmt->execute()) ? true : $stmt->errno;
        }
         function selectOpeningBalanceByMinSubId(mysqli $conn, int $minSubId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM min_sub_opening_balances WHERE `min_sub_id`='$minSubId'";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            }else{
                return $conn->error;
            }
        }
        
        function selectOpeningBalanceByDateRangeAndItemId(mysqli $conn, int $subId, string $startDate, string $endDate){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM starting_balance WHERE  `account_id`='$subId' AND deleted_at IS NULL AND `date_` BETWEEN '$startDate' AND '$endDate'";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            }else{
                return $conn->error;
            }
        }
?>