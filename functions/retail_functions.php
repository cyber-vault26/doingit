<?php

        function registerStation(mysqli $conn, int $subCostCenter, string $name, string $address, int $region){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `fuel_stations`(`sub_cost_center_id`,`name`, `address`, `region`) VALUES (?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("issi",$subCostCenter, $name, $address, $region);
            return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
        }
        
        function selectStations(mysqli $conn){
            if($conn === false){
                exit();
            }
            $sql = "SELECT  fuel_stations.*, mikoa.name as mkoa FROM  fuel_stations JOIN mikoa ON mikoa.id = fuel_stations.region  WHERE deleted_at IS NULL ORDER BY region";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
        }

        function addstationPump(mysqli $conn, int $stationId, string $pumpNumber){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `station_pumps`(`station_id`, `pump_name`) VALUES (?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("is", $stationId,$pumpNumber);
            return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
        }
        function selectPumpFromStation(mysqli $conn, int $stationId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM station_pumps  WHERE station_id=$stationId AND deleted_at IS NULL ";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error; 
        }
        function addOpeningReading(mysqli $conn, int $stationId,int $pumpId, int $fuelId, string $date, string $openingTime, float $meterReading, int $openedBy){
            if($conn === true){
                exit();
            }
            $sql = "INSERT INTO `station_fuels_meter_readings`( `station_id`,`pump_id`, `fuel_id`, `date_`, `opening_time`, `open_meter_reading`, `opened_by`) VALUES (?,?,?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("iiissdi",$stationId,$pumpId,$fuelId,$date,$openingTime,$meterReading,$openedBy);
            return ( $stmt->execute()) ? $stmt->insert_id : $stmt->error;
        }
        function addClosingReading(mysqli $conn, int $fillingId,  string $closingTime, float $closingMeterReading, int $closedBy){
            if($conn === false){
                exit();
            }
            $sql = "UPDATE `station_fuels_meter_readings` SET `closing_time`=?,`closing_meter_reading`=?,`closed_by`=? WHERE id =?;";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("siii", $closingTime,$closingMeterReading,$closedBy,$fillingId);
            return ($stmt->execute()) ? true : $conn->error;
        }
        function selectDailyReading(mysqli $conn){
            if($conn === false){
                exit();
            }
            $sql = "SELECT station_fuels_meter_readings.*, station_pumps.pump_name, users.names as opener, mikoa.name as mkoa, min_subs.name as fuel, fuel_stations.name as station 
                        FROM station_fuels_meter_readings 
                        JOIN station_pumps ON station_pumps.id = station_fuels_meter_readings.pump_id
                        JOIN users ON users.id = station_fuels_meter_readings.opened_by 
                        JOIN fuel_stations ON fuel_stations.id = station_fuels_meter_readings.station_id
                        JOIN mikoa ON mikoa.id = fuel_stations.region
                        JOIN min_subs ON min_subs.id = station_fuels_meter_readings.fuel_id
                        WHERE station_fuels_meter_readings.deleted_at IS NULL ORDER BY date_ DESC";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;

        }
        function updateDailyMeterReadingStatus(mysqli $conn, int $readingId, string $status){
            if($conn === false){
                exit();
            }
            $sql = "UPDATE `station_fuels_meter_readings` SET `status` =? WHERE id=?;";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("si", $status, $readingId);

            return ($stmt->execute()) ? true : $stmt->error;
        }
        function selectDailyreadingById(mysqli $conn, int $readingId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT station_fuels_meter_readings.*,
                        sub_cost_centers.cost_center_id, 
                        sub_cost_centers.name AS sub_center,
                        sub_cost_centers.id as sub_cost_center_id,
                        fuel_stations.id as station_id,
                        min_subs.name as item 
                        FROM station_fuels_meter_readings 
                        JOIN fuel_stations ON station_fuels_meter_readings.station_id = fuel_stations.id
                        JOIN sub_cost_centers ON fuel_stations.sub_cost_center_id = sub_cost_centers.id
                        JOIN min_subs ON station_fuels_meter_readings.fuel_id = min_subs.id
                        WHERE station_fuels_meter_readings.id =?;";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("i", $readingId);
            $stmt->execute();

            $result = $stmt->get_result();
            return ($result ) ? $result->fetch_assoc() : $stmt->errno; 
        }
?>