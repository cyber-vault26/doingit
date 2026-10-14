<?php
       
      
  // In your functions file
function getNetProfit($conn) {
    $totalIncome = sumAllConsignmentIncome($conn);
    $totalCosts = sumAllConsignmentCost($conn);
    return $totalIncome - $totalCosts;
}
  
     
function getTotalConsignmentIncome($conn) {
    $consignments = selectAllConsignment($conn);
    $grandTotal = 0;
    
    if($consignments && is_array($consignments)) {
        foreach($consignments as $consignment) {
            $consgId = $consignment['id'];
            $distributions = selectTransactionsByCosingnId($conn, $consgId);
            $totalIncome = 0;
            
            if($distributions && is_array($distributions)){
                foreach($distributions as $distribution){
                    if(strpos($distribution['reference_no'], 'SV/') === 0){
                        $incomeAmount = 0;
                        if(!empty($distribution['cr_account'])){
                            $incomeAmount = $distribution['cr_ammount'];
                        } elseif(!empty($distribution['dr_account'])){
                            $incomeAmount = $distribution['dr_ammount'];
                        }
                        if($incomeAmount > 0){
                            $totalIncome += $incomeAmount;
                        }
                    }
                }  
            }
            $grandTotal += $totalIncome;
        }
    }
    
    return $grandTotal;
}
      
      
      
      
       
       
      
function getTotalConsignmentCosts($conn) {
    // Get all available consignments
    $consignments = selectAllConsignment($conn);
    $grandTotal = 0;
    
    if($consignments && is_array($consignments)) {
        foreach($consignments as $consignment) {
            $consgId = $consignment['id'];
            
            // Get only active drivers from consignment
            $body = selectConsignmentBody($conn, $consgId);
            $activeDriverIds = [];
            
            if($body && is_array($body)){
                foreach($body as $b){
                    $activeDriverIds[] = $b['driver_id'];
                }
            }
            
            // Get costs for this consignment
            $costs = selectConsignmentCost($conn, $consgId);
            $totalCosts = 0;
            
            if($costs && is_array($costs)){
                // Group costs by cost item and sum amounts for active drivers only
                foreach($costs as $cost){
                    if(in_array($cost['driver_id'], $activeDriverIds)){
                        $totalCosts += $cost['amount'];
                    }
                }
            }
            
            $grandTotal += $totalCosts;
        }
    }
    
    return $grandTotal;
}
       
       
       
       
       
       
       
       
       
       
function selectConsignmentTrucksByConsignmentId(mysqli $conn, int $consignId) {
    $sql = "
        SELECT cd.truck_id, ms.*
        FROM consignment_details cd
        JOIN min_subs ms ON cd.truck_id = ms.id
        WHERE cd.consigment_id = $consignId
          AND cd.deleted_at IS NULL
          AND ms.type = 'truck'
    ";

    $result = $conn->query($sql);
    $trucks = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $trucks[] = $row;
        }
    }

    return $trucks;
}


       
        function countConsignment(mysqli $conn, string $year){
            if($conn=== false){
                exit();
            }
            $sql = "SELECT COUNT(id) as jumla FROM `consignments` WHERE date_ LIKE '$year%'";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function createConsingment(mysqli $conn, string $refNo, int $customerId, string $date, string $shippingDate, string $returnDate, string $loadType, int $loadWeight, string $description){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `consignments`( `ref_no`, `customer_id`, `date_`, `load_type`, `load_weight`, `description`, `ship_date`, `return_date`) VALUES(?,?,?,?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt===false){
                exit();
            }
            $stmt->bind_param("sissdsss", $refNo, $customerId, $date, $loadType,$loadWeight,$description,$shippingDate,$returnDate);
            if($stmt->execute()){
                return $stmt->insert_id;
            }else{
                return $stmt->error;
            }
        }
        function CreateConsignmentBody(mysqli $conn, int $consignId, int $truckId, int $trailerId,  int $driverId, int $routeId, float $distance){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `consignment_details`( `consigment_id`, `truck_id`,`trailer_id`, `driver_id`, `route_id`, `distance`) VALUES (?,?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt=== false){
                exit();
            }
            $stmt->bind_param("iiiiid", $consignId, $truckId, $trailerId, $driverId, $routeId, $distance);
            if($stmt->execute()){
                return true;
            } else{
                return $stmt->error;
            }
        }
        
        function selectAllConsignment(mysqli $conn){
            if($conn=== false){
                exit();
            }
            $sql = "SELECT consignments.*, subsidiaries.name as mteja FROM `consignments` JOIN subsidiaries ON consignments.customer_id = subsidiaries.id WHERE consignments.deleted_at IS NULL ORDER BY date_ DESC";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function selectAllConsignmentByStatus(mysqli $conn, string $status){
            if($conn=== false){
                exit();
            }
            $sql = "SELECT consignments.*, subsidiaries.name as mteja FROM `consignments` JOIN subsidiaries ON consignments.customer_id = subsidiaries.id WHERE consignments.status='$status' AND  consignments.deleted_at IS NULL ORDER BY date_ DESC";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function selectConsignmentById(mysqli $conn, int $consignId){
            if($conn=== false){
                exit();
            }
            $sql = "SELECT consignments.*, subsidiaries.name as mteja  FROM `consignments` JOIN subsidiaries ON consignments.customer_id = subsidiaries.id WHERE consignments.id='$consignId' ORDER BY date_ DESC";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function selectConsignmentBody(mysqli $conn, int $consignId){
            if($conn=== false){
                exit();
            }
            $sql = "SELECT consignment_details.*, d.name AS dereva,  v.name AS truck, t.name AS trailer, r.name AS route FROM consignment_details
                    JOIN subsidiaries d ON d.id = consignment_details.driver_id
                    JOIN min_subs v ON v.id = consignment_details.truck_id
                    JOIN min_subs t ON t.id = consignment_details.trailer_id
                    JOIN subsidiaries r ON r.id = consignment_details.route_id
                    WHERE consignment_details.consigment_id='$consignId'
                    AND consignment_details.deleted_at IS NULL;";
            if($result = $conn->query($sql)){
                $data = $result->fetch_All(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function updateConsignment(mysqli $conn, int $consignId, 
        string $status){
            if($conn=== false){
                exit();
            }
            $sql = "UPDATE `consignments` SET `status`='$status' WHERE id='$consignId'";
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }
        }
        function deleteConsgn(mysqli $conn, int $consignId){
            if($conn=== false){
                exit();
            }
            $sql = "UPDATE   `consignments` SET `deleted_at`=NOW()   WHERE id='$consignId'";
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }
        }

        function selectConsignmentByVehicleInvolved(mysqli $conn, int $vehicleId){
            if($conn=== false){
                exit();
            }
            $sql = "SELECT *  FROM `consignments`  WHERE nose_id='$vehicleId' ";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }

        function selectAllConsignmentByNotStatus(mysqli $conn, string $status){
            $sql = "SELECT consignments.*  FROM `consignments` WHERE status !='$status' ORDER BY  date_ DESC";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function selectConsignmentDriver(mysqli $conn, int $consignId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT consignment_details.*, subsidiaries.name FROM consignment_details
                JOIN subsidiaries ON consignment_details.driver_id = subsidiaries.id  WHERE consignment_details.consigment_id=$consignId";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function addConsignmentRemarks(mysqli $conn, int $consignId, string $remarks){
            if($conn === false){
                exit();
            }
            $sql ="UPDATE `consignments` SET `remarks` ='$remarks' WHERE id=$consignId";
            return ($conn->query($sql) === true) ? true : $conn->error;
        }
          function addConsignmentCost(mysqli $conn, int $consignId, int $driverId, int $itemId, float $amount, string $date){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `consignment_costs`( `consigment_id`, `driver_id`, `item_id`, `amount`, `date_`) VALUES (?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("iiids", $consignId, $driverId, $itemId, $amount,$date );
            return ($stmt->execute()) ? true : $conn->error;
        }
        
        
           function updateConsignmentVehicleDistance(mysqli $conn, int $bodyId, float $distance){
            if($conn === false){
                exit();
            }
            $sql = "UPDATE `consignment_details` SET `distance`='$distance' WHERE id=$bodyId;";
            return ($conn->query($sql) === true) ? true : $conn->error;
        }
        function removeCosigmentVehicleInDetails(mysqli $conn, int $bodyId){
            if($conn === false){
                exit();
            }
            $now = date('Y-m-d H:i:s');
            $sql = "UPDATE `consignment_details` SET `deleted_at`='$now' WHERE id=$bodyId;";
            return ($conn->query($sql) === true) ? true : $conn->error;
        }
         function selectConsignmentByStatusAndDateRange(mysqli $conn, string $status, string $startDate, string $endDate){
            if($conn === false){
                exit();
            
            }
                       $sql = "SELECT consignments.*, subsidiaries.name as mteja FROM `consignments` JOIN subsidiaries ON consignments.customer_id = subsidiaries.id 
                       
                             WHERE consignments.status=? AND consignments.date_ BETWEEN ? AND ? AND consignments.deleted_at IS NULL ORDER BY date_ DESC";
          $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("sss", $status, $startDate, $endDate);
            return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : $stmt->errno;
        }
        
        function getConsignmentByDateRange(mysqli $conn, string $startDate, string $endDate){
            if($conn === false){
                exit();
            }
            $sql = " SELECT consignments.*, subsidiaries.name as mteja FROM `consignments` JOIN subsidiaries ON consignments.customer_id = subsidiaries.id 
                       
                             WHERE  consignments.date_ BETWEEN ? AND ? AND consignments.deleted_at IS NULL ORDER BY date_ DESC;";
            
          $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("ss", $startDate, $endDate);
            return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : $stmt->errno;
        }
        function getTrashedConsignmentByDateRange(mysqli $conn, string $startDate, string $endDate){
            if($conn === false){
                exit();
            }
            $sql = " SELECT consignments.*, subsidiaries.name as mteja FROM `consignments` JOIN subsidiaries ON consignments.customer_id = subsidiaries.id 
                       
                             WHERE  consignments.date_ BETWEEN ? AND ? AND consignments.deleted_at IS NOT NULL ORDER BY date_ DESC;";
            
          $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("ss", $startDate, $endDate);
            return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : $stmt->errno;
        }
        
        function softDeleteConisgnmentCost(mysqli $conn, int $costItemId, int $consignId, int $driverId, float $amount, int $deletedBy){
        if($conn === false){
            exit();
        }

        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE `consignment_costs` SET `deleted_at`='$now', deleted_by=$deletedBy WHERE id=$costItemId;";
        if($conn->query($sql) === true){
            return softDeleteConisgnmentCostEntry($conn, $consignId, $driverId, $amount, $deletedBy);
        } else{
            return $conn->error;
        }
    }

    function softDeleteConisgnmentCostEntry( mysqli $conn, int $consignId, int $driverId, float $amount,  int $deletedBy ){
        if($conn === false){
            exit();
        }
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE transaction_voucher SET deleted_at='$now', deleted_by=$deletedBy WHERE consign_id=$consignId AND dr_account=$driverId AND cr_ammount=$amount;";
        return ($conn->query($sql) === true) ? true : $conn->error;
    }
     function updateConsignmentCustomer(mysqli $conn, int $consignId, int $customerId, int $updatedBy){
        if($conn === false){
            exit();
        }
        $sql = "UPDATE consignments SET customer_id=?, updated_by=? WHERE id=?;";
        $stmt = $conn->prepare($sql);
        if($stmt === false){
            exit();
        }
        $stmt->bind_param("iii", $customerId, $updatedBy, $consignId);
        return ($stmt->execute()) ? true : $stmt->errno;
    }
     

  function sumAllConsignmentCost(mysqli $conn):float{
    if($conn === false){
        exit();
    }
    $sql = "SELECT SUM(dr_ammount) as cost FROM transaction_voucher WHERE consign_id >0 AND deleted_at IS NULL AND reference_no LIKE 'PV/%';";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $totalCost = 0;
    
     if($result && $result['cost'] >0){
        $totalCost += $result['cost'];
     }  
     //$result['cost'];
   
    
    return $totalCost;
}
function sumAllConsignmentIncome(mysqli $conn):float{
    if($conn === false){
        exit();
    }
    $sql = "SELECT SUM(dr_ammount) as cost FROM transaction_voucher WHERE consign_id >0 AND deleted_at IS NULL AND reference_no LIKE 'SV/%';";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $totalCost = 0;
    
     if($result && $result['cost'] >0){
        $totalCost += $result['cost'];
     }  
     //$result['cost'];
   
    
    return $totalCost;
}
  function selectConsignmentCost(mysqli $conn, int $consignId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT transaction_voucher.*, subsidiaries.name FROM transaction_voucher
                    LEFT JOIN subsidiaries ON subsidiaries.id = transaction_voucher.dr_account
                    WHERE transaction_voucher.consign_id = ?  AND transaction_voucher.deleted_at IS NULL 
                    AND transaction_voucher.reference_no LIKE 'PV/%'";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("i", $consignId);
            return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : $stmt->error;
           
            
        }
        function selectConsignmentSales(mysqli $conn, int $consignId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT transaction_voucher.*, subsidiaries.name FROM transaction_voucher
                    LEFT JOIN subsidiaries ON subsidiaries.id = transaction_voucher.cr_account
                    WHERE transaction_voucher.consign_id = ?  AND transaction_voucher.deleted_at IS NULL 
                    AND transaction_voucher.reference_no LIKE 'SV/%'";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("i", $consignId);
            return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : $stmt->error;
           
            
        }
?>