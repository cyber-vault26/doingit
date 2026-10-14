<?php
        function registerCostCenter(mysqli $conn, string $number, string $name, int $createdBy){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `cost_centers`( `ref_no`, `name`,  `created_by`) VALUES (?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                return $conn->error;
            }
            $stmt->bind_param("ssi", $number, $name, $createdBy);
            if($stmt->execute()){
                return true;
            } else{
                return $stmt->error;
            }
           }
           function selectCostCenter(mysqli $conn){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM cost_centers WHERE deleted_at IS NULL;";
            if($result = $conn->query($sql)){
             
                return $result->fetch_all(MYSQLI_ASSOC);
            }else{
                return $conn->error;
            }
           }
           function selectCostCenterById(mysqli $conn, int $costCenterId){
                if($conn === false){
                    exit();
                }
                $sql = "SELECT * FROM cost_centers WHERE id = $costCenterId;";
                return ($result = $conn->query($sql)) ? $result->fetch_assoc() : $conn->error;
           }
           function selectCostCenterByName(mysqli $conn, string $CostCenterName){
                if($conn === false){
                    exit();
                }
                $sql = "SELECT * FROM cost_centers WHERE name LIKE '$CostCenterName%';";
                return ($result = $conn->query($sql)) ? $result->fetch_assoc() : $conn->error;
           }
           function registerSubCostCenter(mysqli $conn, int $costCenterId, string $name, string $location, int $createdBy){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `sub_cost_centers`( `cost_center_id`, `name`, `location`, `created_by`) VALUES (?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if($stmt === false){
                exit();
            }
            $stmt->bind_param("issi",$costCenterId,$name,$location,$createdBy);
            return ($stmt->execute() === true) ? true : $stmt->errno;
           }
           function selectSubCostCenters(mysqli $conn){
            if($conn === false){
                exit();
            }
            $sql = "SELECT sub_cost_centers.*, cost_centers.name AS main_center FROM sub_cost_centers
                    JOIN cost_centers ON sub_cost_centers.cost_center_id = cost_centers.id
                    WHERE sub_cost_centers.deleted_at IS NULL AND cost_centers.deleted_at IS NULL
                    ORDER BY main_center, name";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
           }
           function selectSubCostCentersByCostCenterId(mysqli $conn, int $costCenterId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT sub_cost_centers.* FROM sub_cost_centers 
                    WHERE sub_cost_centers.cost_center_id = $costCenterId
                    ORDER BY  name";
            return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
           }
           function deleteSubCostCenter(mysqli $conn, int $subCostCenterId){
            if($conn === false){
                return;
            }
            $now = date("Y-m-d H:m:s");
            $sql = "UPDATE `sub_cost_centers` SET deleted_at ='$now' WHERE id=$subCostCenterId;";
            return ($conn->query($sql) === true) ? true : $conn->errno;
           }
        //      function selectSubCostCentersByCostCenterId(mysqli $conn, int $costCenterId){
        //     if($conn === false){
        //         exit();
        //     }
        //     $sql = "SELECT sub_cost_centers.* FROM sub_cost_centers 
        //             WHERE sub_cost_centers.cost_center_id = $costCenterId
        //             ORDER BY  name";
        //     return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
        //   }
        //   function deleteSubCostCenter(mysqli $conn, int $subCostCenterId){
        //     if($conn === false){
        //         return;
        //     }
        //     $now = date("Y-m-d H:m:s");
        //     $sql = "UPDATE `sub_cost_centers` SET deleted_at ='$now' WHERE id=$subCostCenterId;";
        //     return ($conn->query($sql) === true) ? true : $conn->errno;
        //   }
        //      function selectSubCostCentersByCostCenterId(mysqli $conn, int $costCenterId){
        //     if($conn === false){
        //         exit();
        //     }
        //     $sql = "SELECT sub_cost_centers.* FROM sub_cost_centers 
        //             WHERE sub_cost_centers.cost_center_id = $costCenterId
        //             ORDER BY  name";
        //     return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
        //   }
        //   function deleteSubCostCenter(mysqli $conn, int $subCostCenterId){
        //     if($conn === false){
        //         return;
        //     }
        //     $now = date("Y-m-d H:m:s");
        //     $sql = "UPDATE `sub_cost_centers` SET deleted_at ='$now' WHERE id=$subCostCenterId;";
        //     return ($conn->query($sql) === true) ? true : $conn->errno;
        //   }
?>