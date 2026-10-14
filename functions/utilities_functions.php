<?php

function insertUnit(mysqli $conn, string $name, string $short, int $created_by) {
    if ($conn === false) exit();
    $sql = "INSERT INTO units (`name`, `short`, `created_by`) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("ssi", $name, $short, $created_by);
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function insertCurrency(mysqli $conn, string $name, float $value, int $created_by) {
    if ($conn === false) exit();
    $sql = "INSERT INTO currency (`name`, `value`, `created_by`) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sdi", $name, $value, $created_by);
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function updateUnit(mysqli $conn, int $id, string $name, string $short, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE units SET name = '$name', short = '$short', updated_by = $updated_by WHERE id = $id";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectUnitById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM  units WHERE id = $id AND deleted_at is null";
    if ($result=$conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function updateCurrency(mysqli $conn, int $id, string $name, float $value, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE currency SET name = '$name', value = $value, updated_by = $updated_by WHERE id = $id";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteUnit(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE units SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteCurrency(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE currency SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}
       function selectAllCompanyDetails(mysqli $conn){
                if($conn === false){
                    exit();
                }
            $sql ="SELECT * FROM company_details";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
       }

       function getAll(mysqli $conn, string $table){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM $table";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
       }

       function selectCurrency(mysqli $conn, string $name){

            if($conn === false){
                    exit();
            }
            $sql = "SELECT * FROM currency WHERE name LIKE '$name%'";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
       }

       function softDeleteFromTable(mysqli $conn, string $tableName, int $id){
            if($conn === false){
                exit();
            }
            $sql = "UPDATE $tableName SET deleted_at = NOW() WHERE id = $id";
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }
       }
       function generateRefNo(mysqli $conn, string $tableName, string $initial){
        if($conn === false){
            exit();

        }
        $sql = "SELECT COUNT(id) as total FROM $tableName WHERE ref_no LIKE '$initial%';";
        $ref ="";
        if($result = $conn->query($sql)){
            $data = $result->fetch_assoc();
            if($data && is_array($data)){
                $ref = $initial . "/" . $data['total'] +1;
            }else{
                $ref = $initial . "/" . 1;
  
            }

        }else{
            $ref = $initial . "/" . 1; 
        }
        return $ref;

       } 
      
    
      
     function selectAllCurrency(mysqli $conn){
        if($conn === false){
            exit();
        }
        $sql = "SELECT  * FROM currency WHERE deleted_at IS NULL; ";
        return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
     }
     function selectCurrencyById(mysqli $conn, int $currencyId){
        if($conn === false){
            exit();
        }
        $sql = "SELECT  * FROM currency WHERE id = $currencyId; ";
        return ($result = $conn->query($sql)) ? $result->fetch_assoc() : $conn->errno;
     }

      
 function selectAllUnit(mysqli $conn){
        if($conn === false){
            exit();
        }
        $sql = "SELECT * from units  WHERE 1 ";
        return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->errno;
     }
     function selectUnitByname(mysqli $conn, string $name){
        if($conn === false){
            exit();
        }
        $sql = "SELECT  units.*, users.names as creator FROM units JOIN users ON units.created_by = users.id WHERE units.name LIKE '%$name%' AND units.deleted_at IS NULL; ";
        return ($result = $conn->query($sql)) ? $result->fetch_assoc() : $conn->errno;
     }
      
     
       function selectRegions(mysqli $conn){
        if($conn === false){
            exit();
        }
        $sql = "SELECT * FROM mikoa ORDER BY name;";
        return ($result = $conn->query($sql)) ? $result->fetch_all(MYSQLI_ASSOC) : $conn->error; 

       }
           function searchUnitByName(mysqli $conn, string $searchKey =''){
        if ($conn === false) {
            exit;
        }
        $sql = "SELECT * FROM units ";
        if(!empty($searchKey)){
            $searchKey = $conn->real_escape_string($searchKey);
            $sql .= "WHERE (name LIKE '%$searchKey%') OR short LIKE '%$searchKey%'";

        }
        $sql .= " ORDER BY name";
       // echo $sql;
        if($result = $conn->query($sql)){
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
            return $data;
        } else{
            return $conn->error;
        }
        
    }
?>