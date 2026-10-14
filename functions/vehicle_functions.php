<?php

function registerVehicle(mysqli $conn, int $subsidaryId , string $regNo, string $manufacturer, float $capacity, string $type, string $ModelNo, string $purchaseDate, string $insuranceDate, float $distance, string $status, string $chasisNo, string $subtype ){
    if($conn === false){
        exit();
    }
    
   
        $sql = "INSERT INTO `vehicles`( `sub_id`,  `plate_no`, `model_number`, `manufacturer`, `capacity`, `type`, `purchase_date`, `insurance_date`, `initial_distance`,`status`, `chasis_no`, `subtype`)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?);";
        $stmt = $conn->prepare($sql);
        if($stmt === false){
            return;
        }
        $stmt->bind_param("isssdsssdsss", $subsidaryId,$regNo,$ModelNo,$manufacturer,$capacity,$type,$purchaseDate,$insuranceDate,$distance,$status, $chasisNo,$subtype);
        return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
    
   

}
function createTrailer(mysqli $conn,  string $regNo, string $manufacturer, float $capacity,  string $ModelNo, string $status ){
    if($conn === false){
        exit();
    }
    
        $sql = "INSERT INTO `trailers`(`id`,  `registration_number`, `model_number`, `manufacturer`, `capacity`,  `status`)
                                VALUES (Null ,'$regNo','$ModelNo','$manufacturer','$capacity','$status')";
   
    if($conn->query($sql) === true){
            return true;
    } else{
        return $conn->error;
    }

}

function selectNoiseByJoinStatus(mysqli $conn, string $status, string $joinStatus){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `vehicle_heads` WHERE status='$status' AND joined='$joinStatus'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectTrailerByJoinStatus(mysqli $conn, string $status, string $joinStatus){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `trailers` WHERE status='$status' AND joined='$joinStatus'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectNoseByStatus(mysqli $conn, string $status){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `vehicles` WHERE  status='$status'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectVehicleByStatus(mysqli $conn, string $status){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `vehicles` WHERE status !='$status'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function updateVehicleStatus(mysqli $conn, string $status, int $id){
    if($conn === false){
        exit();
    }
    $sql = "UPDATE `vehicles` SET status='$status' WHERE id='$id'"; 
    if($conn->query($sql) === true){
        return true;
    } else{
        return $conn->error;
    }
}


function selectVehicleById(mysqli $conn, int $vehicleId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `vehicles` WHERE id='$vehicleId'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function selectVehicleByMinSubId(mysqli $conn, int $minSubId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `vehicles` WHERE sub_id =$minSubId";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function selectNoseBySubId(mysqli $conn, int $noseId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `vehicle_heads` WHERE id='$noseId'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function selectTrailerById(mysqli $conn, int $Id){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `trailers` WHERE subsidiary_id='$Id'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function selectTrailerByStatus(mysqli $conn, string $status){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `vehicles` WHERE type='trailer' AND status='$status'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function selectTrailerBySubId(mysqli $conn, int $subId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM `trailers` WHERE subsidiary_id='$subId'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function selectVehiclePayment(mysqli $conn, int $vehicleId, string $type){
    if($conn === false){
        exit();
    }
    $sql = "SELECT subsidiaries.name as paid_to, transaction_voucher.* FROM transaction_voucher JOIN subsidiaries ON transaction_voucher.dr_account= subsidiaries.id  WHERE vehicle_id='$vehicleId' AND transaction_voucher.type='$type';";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function getAllVehicle(mysqli $conn){
    if($conn === false){
        exit();
    }
    //$sql = "SELECT * FROM `vehicles` WHERE deleted_at IS NULL ";
     $sql = "SELECT * FROM `vehicles`";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function getVehicleConsignment(mysqli $conn, int $vehicleSubId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT consignment_details.*, d.name as driver, r.name as route, consignments.date_  FROM consignment_details
            JOIN subsidiaries d ON d.id = consignment_details.driver_id 
            JOIN subsidiaries r ON r.id = consignment_details.route_id 
            JOIN consignments ON consignments.id = consignment_details.consigment_id
            WHERE consignment_details.vehicle_id ='$vehicleSubId'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function getVehicleCostByTimeRange(mysqli $conn, int $vehicleId, string $date1, string $date2){
    if($conn === false){
        exit();
    }
    $sql = "SELECT consignment_costs.*, subsidiaries.name, consignment_details.truck_id
                FROM consignment_costs
                JOIN consignment_details ON consignment_details.consigment_id = consignment_costs.consigment_id
                JOIN subsidiaries ON consignment_costs.item_id = subsidiaries.id
                WHERE consignment_details.truck_id = $vehicleId AND consignment_costs.deleted_at IS NULL 
                AND consignment_costs.date_ BETWEEN '$date1' AND '$date2'
                GROUP BY consignment_costs.id;";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function getVehicleconsignRetirement(mysqli $conn, int $vehicleId, string $date1, string $date2){
    if($conn === false){
        exit();
    }
    $sql = "SELECT consignment_details.truck_id, vehicles.plate_no,  transaction_voucher.*, debit.name AS debit_ac, credit.name AS credit_ac
            FROM consignment_details 
            JOIN vehicles ON consignment_details.truck_id = vehicles.sub_id
            JOIN transaction_voucher ON transaction_voucher.consign_id = consignment_details.consigment_id
            JOIN subsidiaries debit ON transaction_voucher.dr_account = debit.id
            JOIN subsidiaries credit ON transaction_voucher.cr_account = credit.id
            WHERE transaction_voucher.type ='consign_retirement'
            AND date_ BETWEEN '$date1' AND '$date2'
            AND transaction_voucher.deleted_at IS NULL
            AND vehicles.id =$vehicleId;";
    return ($result = $conn->query($sql))? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
}
function getVehicleSales(mysqli $conn, int $vehicleSubId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT consignment_details.*, r.name as route, transaction_voucher.cr_ammount, transaction_voucher.date_ FROM consignment_details
            JOIN subsidiaries r ON r.id = consignment_details.route_id
            JOIN transaction_voucher ON transaction_voucher.consign_id = consignment_details.consigment_id 
            WHERE consignment_details.vehicle_id = $vehicleSubId AND transaction_voucher.type='sales'";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}
function getVehicleSalesByDateRange(mysqli $conn, int $vehicleSubId, string $date1, string $date2){
    if($conn === false){
        exit();
    }
    $sql = "SELECT consignment_details.*, r.name as route, transaction_voucher.* FROM consignment_details
            JOIN subsidiaries r ON r.id = consignment_details.route_id
            JOIN transaction_voucher ON transaction_voucher.consign_id = consignment_details.consigment_id 
            WHERE consignment_details.truck_id = $vehicleSubId AND transaction_voucher.type='sales' 
            AND transaction_voucher.date_ BETWEEN '$date1' AND '$date2';";
    if($result = $conn->query($sql)){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else{
        return $conn->error;
    }
}

function insertFuelCost(mysqli $conn, int $routeId,float $amount, float $currencyVal,  string $date, string $refNo,string $fuelStation){
    if($conn === false){
        exit();
    }
    $sql = "INSERT INTO `fuel_requests`(`ref_no`, `route_id`,`amount`, `currency_val`, `date_`, `created_at`,`fuel_station`) VALUES (?,?,?,?,?,?,?);";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
       exit();
    
    }
    $created_at = date("Y-m-d H:m:s");
    $stmt->bind_param("siddsss", $refNo,$routeId,$amount,$currencyVal,$date, $created_at,$fuelStation);
    return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
}
function insertFuelCostItem(mysqli $conn, int $requestId, int $driverId, int $truckid, int $trailerId, float $amount, float $litre, float $price, string $fuelStation, ?string $comment = null){
    if($conn === false){
        exit();
    }
    $sql = "INSERT INTO `fuel_request_item`( `request_id`,`driver_id`, `trailer_id`, `truck_id`, `amount`, `litres`, `price`, `comment`, `fuel_station`) VALUES (?,?,?,?,?,?,?,?, ?);";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return $conn->error;
    }
    $stmt->bind_param("iiiidddss", $requestId, $driverId, $trailerId, $truckid,$amount,$litre, $price,$comment, $fuelStation);
    return ( $stmt->execute()) ? $stmt->insert_id : $stmt->error;
}
function fuelRequestList(mysqli $conn, string $status){
    if($conn === false){
        exit();
    }
    $sql = "SELECT fuel_requests.*, routes.from_, routes.to_ FROM fuel_requests
            JOIN routes ON fuel_requests.route_id = routes.id WHERE fuel_requests.deleted_at IS NULL";
    if($conn === false){
        exit();
    }
    return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
}
function fuelRequestByRequestId(mysqli $conn, int $id){
    if($conn === false){
        exit();
    }
    $sql = "SELECT fuel_requests.*, routes.from_, routes.to_ FROM fuel_requests
            JOIN routes ON fuel_requests.route_id = routes.id WHERE fuel_requests.deleted_at IS NULL AND fuel_requests.id=$id";
    if($conn === false){
        exit();
    }
    return ($result = $conn->query($sql)) ? $result->fetch_assoc() : $conn->error;
}

function selectFuelRequesItem(mysqli $conn, int $requestId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT fuel_request_item.*, trailer.name as trail, truck.name as truc, subsidiaries.name as dereva FROM fuel_request_item
            JOIN min_subs trailer ON fuel_request_item.trailer_id = trailer.id
            JOIN min_subs truck On truck.id = fuel_request_item.truck_id
            JOIN subsidiaries ON subsidiaries.id = fuel_request_item.driver_id
            WHERE fuel_request_item.request_id = $requestId;";
    return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
    // return ($result = $conn->query($sql)) ? $sql : $conn->errno;
}
 function getVehicleExpenses (mysqli $conn, int $vehicleId, string $date1, string $date2){
    if($conn === false){
        exit();
    }
    $sql = "SELECT transaction_voucher.*, debit.name as debit_ac, credit.name as credit_ac, vehicles.plate_no FROM
                transaction_voucher 
                JOIN subsidiaries debit  ON debit.id = transaction_voucher.dr_account 
                JOIN subsidiaries credit ON credit.id = transaction_voucher.cr_account
                JOIN vehicles ON vehicles.id = transaction_voucher.vehicle_id
                WHERE transaction_voucher.vehicle_id =$vehicleId 
                AND transaction_voucher.date_ BETWEEN '$date1' AND '$date2';";
    return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
 }
 function getVehicleByType(mysqli $conn, string $type){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM vehicles WHERE type = '$type'";
    return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
 }


function getVehicleReportSummary(mysqli $conn, ?string $startDate = null, ?string $endDate = null) {
    if($conn === false) {
        exit();
    }
    
    $dateFilter = "";
    if($startDate && $endDate) {
        $dateFilter = " AND tv.date_ BETWEEN '$startDate' AND '$endDate'";
    }
    
  $sql = "SELECT 
                COUNT(DISTINCT CASE WHEN v.type = 'truck' THEN v.id END) as total_vehicles,
                COUNT(DISTINCT CASE WHEN v.type = 'trailer' THEN v.id END) as total_trailers,
                COALESCE(SUM(CASE WHEN tv.vehicle_id IS NOT NULL AND tv.vehicle_id != '' AND tv.type IN ('payment', 'consign_in') AND v.type = 'truck' THEN tv.dr_ammount ELSE 0 END), 0) as total_expenses,
                COALESCE(SUM(CASE WHEN tv.vehicle_id IS NOT NULL AND tv.vehicle_id != '' AND tv.type = 'sales' AND v.type = 'truck' THEN tv.cr_ammount ELSE 0 END), 0) as total_sales
            FROM vehicles v
            LEFT JOIN transaction_voucher tv ON v.id = tv.vehicle_id
            WHERE v.deleted_at IS NULL $dateFilter";
    
    if($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return false;
    }
}

function getVehicleExpensesAndSales(mysqli $conn, ?string $startDate = null, ?string $endDate = null) {
    if($conn === false) {
        exit();
    }
    
    $dateFilter = "";
    if($startDate && $endDate) {
        $dateFilter = " AND tv.date_ BETWEEN '$startDate' AND '$endDate'";
    }
    
    $sql = "SELECT 
                v.id,
                v.plate_no,
                v.manufacturer,
                v.model,
                v.type as vehicle_type,
                v.subtype,
                COALESCE(SUM(CASE WHEN tv.type IN ('payment', 'consign_in') THEN tv.dr_ammount ELSE 0 END), 0) as total_expenses,
                COALESCE(SUM(CASE WHEN tv.type = 'sales' THEN tv.cr_ammount ELSE 0 END), 0) as total_sales,
                (COALESCE(SUM(CASE WHEN tv.type = 'sales' THEN tv.cr_ammount ELSE 0 END), 0) - 
                 COALESCE(SUM(CASE WHEN tv.type IN ('payment', 'consign_in') THEN tv.dr_ammount ELSE 0 END), 0)) as net_profit
            FROM vehicles v
            LEFT JOIN transaction_voucher tv ON v.id = tv.vehicle_id $dateFilter
            WHERE v.deleted_at IS NULL AND v.type='truck'
            GROUP BY v.id, v.plate_no, v.manufacturer, v.model, v.type, v.subtype
            ORDER BY v.plate_no";
    
    if($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return false;
    }
}

function getVehicleDetailedTransactions(mysqli $conn, int $vehicleId, ?string $startDate = null, ?string $endDate = null) {
    if($conn === false) {
        exit();
    }
    
    $dateFilter = "";
    if($startDate && $endDate) {
        $dateFilter = " AND date_ BETWEEN '$startDate' AND '$endDate'";
    }
    
    $sql = "SELECT 
                reference_no,
                description,
                type,
                CASE 
                    WHEN type IN ('payment', 'consign_in') THEN dr_ammount 
                    ELSE 0 
                END as expense_amount,
                CASE 
                    WHEN type = 'sales' THEN cr_ammount 
                    ELSE 0 
                END as sales_amount,
                date_,
                currency
            FROM transaction_voucher 
            WHERE vehicle_id = $vehicleId $dateFilter
            ORDER BY date_ DESC";
    
    if($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return false;
    }
}

function getVehicleInfo(mysqli $conn, int $vehicleId) {
    if($conn === false) {
        exit();
    }
    
    $sql = "SELECT * FROM vehicles WHERE id = $vehicleId AND deleted_at IS NULL";
    
    if($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return false;
    }
}
function addRequestFuelStation(mysqli $conn, int $requestId, string $station){
    if($conn === false){
        exit();
    }
    $sql = "INSERT INTO `fuel_request_stations`(`request_id`, `station`) VALUES (?,?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return $conn->error;
    }
    $stmt->bind_param("is", $requestId, $station);
    return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
 }
 function selectRequestStations(mysqli $conn, int $requestId){
    if($conn === false){
        exit();
    }
    $sql = "SELECT * FROM fuel_request_stations WHERE request_id = $requestId";
    return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
 }
  function insertMilleageRequest(mysqli $conn, array $data){
    if($conn === false){
        exit();
    }
    $sql = "INSERT INTO `driver_milleage_requests`( `ref_no`, `date_`, `requested_by`, `amount_requested`, `advance`) VALUES (?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return $conn->error;
    }
    $stmt->bind_param("ssidd", $data['ref_no'], $data['date_'], $data['requested_by'], $data['amount_requested'], $data['advance']);
    return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
 }
 function  insertMilleageRequestItems(mysqli $conn, int $requestId, int $driverId, int $truckId, int $trailerId, float $amount, float $advanceAmount, string $destination, string $bankDetails){
    if($conn === false){
        exit;
    }
    $sql = "INSERT INTO `driver_milleage_request_items`(`request_id`, `trailer_id`, `truck_id`, `driver_id`, `destination`, `amount`, `advance`, `bank_details`) VALUES (?,?,?,?,?,?,?,?);";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return $conn->error;
    }
    $stmt->bind_param("iiiisdds", $requestId, $trailerId, $truckId, $driverId, $destination, $amount, $advanceAmount, $bankDetails);
    return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
 }
 function listDriverMilleage(mysqli $conn){
    if($conn === false){
        exit();
    }
        $sql ="SELECT driver_milleage_requests.*, users.names as requester FROM driver_milleage_requests 
                LEFT JOIN users ON driver_milleage_requests.requested_by = users.id 
                WHERE driver_milleage_requests.deleted_at IS  NULL";
        return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
   
 }
 function getMilleageById(mysqli $conn, int $requestId){
     if($conn === false){
        exit();
    }
        $sql ="SELECT driver_milleage_requests.*, users.names as requester FROM driver_milleage_requests 
                LEFT JOIN users ON driver_milleage_requests.requested_by = users.id 
                WHERE driver_milleage_requests.deleted_at IS  NULL AND driver_milleage_requests.id = ?";
        $stmt = $conn->prepare($sql);
        if($stmt === false){
            return $conn->error;
        }
        $stmt->bind_param("i", $requestId);
        return ($stmt->execute()) ? $stmt->get_result()->fetch_assoc() : $stmt->error;
   
 }
 function listMilleageRequestItem(mysqli $conn, int $requestId){
    if($conn === false){
        exit();
    }
    $sql ="SELECT driver_milleage_request_items.*, truck.name as truck_name, trailer.name as trailer_number, subsidiaries.name AS driver FROM driver_milleage_request_items
            LEFT JOIN min_subs truck on truck.id = driver_milleage_request_items.truck_id
            LEFT JOIN min_subs trailer ON trailer.id = driver_milleage_request_items.trailer_id
            LEFT JOIN subsidiaries ON subsidiaries.id = driver_milleage_request_items.driver_id
            WHERE driver_milleage_request_items.request_id = ?";
    $stmt = $conn->prepare($sql);
    if($stmt === false){
        return $conn->error;
    }
    $stmt->bind_param("i", $requestId);
    return ($stmt->execute()) ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : $stmt->error;
 }
?>