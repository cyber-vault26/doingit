<?php
        function recordInsurance(mysqli $conn, int $VehicleId, int $supplierId, float $amount, string $dateIsuued, string $exipireDate){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `insurances`(`vehicle_id`, `supplier_id`, `amount`, `issued_date`, `expire_date`) VALUES (?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("iidss", $VehicleId, $supplierId, $amount, $dateIsuued, $exipireDate);
            return ($stmt->execute()) ? $stmt->insert_id : $stmt->error;
        }

        function listInsurance(mysqli $conn){
            $sql = "SELECT insurances.*, vehicles.plate_no, subsidiaries.name FROM insurances INNER JOIN vehicles ON insurances.vehicle_id = vehicles.id INNER JOIN subsidiaries ON insurances.supplier_id = subsidiaries.id WHERE insurances.deleted_at IS NULL;";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error;
        }
?>