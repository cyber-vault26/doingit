<?php
        function registerRoute(mysqli $conn, string $from, string $to, float $price, string $subsdiaryId ){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `routes`(`id`, `from_`, `to_`, `price`, `subsidiary_id`) 
                                VALUES (NULL,'$from','$to','$price','$subsdiaryId')";
            if($conn->query($sql)){
                return $conn->insert_id;
            } else{
                return $conn->error;
            }
        }
        function selectAllRoutes(mysqli $conn, string $status ){
            if($conn === false){
                exit();
            }
            $sql = "SELECT DISTINCT routes.*, subsidiaries.name FROM routes JOIN subsidiaries ON routes.subsidiary_id = subsidiaries.id WHERE routes.status='$status' GROUP BY routes.id; ";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                $conn->error;
            }

        }
          function softDeleteRoute(mysqli $conn, int $routeId){
            if($conn === false){
                exit();
            }
            $response = '';
            $sqlSub = "SELECT * FROM routes WHERE id = $routeId AND deleted_at IS NULL";
            if($result = $conn->query($sqlSub)){
                $data = $result->fetch_assoc();
                if($data && is_array($data)){
                    $subId = $data['subsidiary_id'];
                    $sql1 = "UPDATE subsidiaries SET deleted_at = NOW() WHERE id = $subId";
                    if($conn->query($sql1)){
                        $sql2 = "UPDATE routes SET deleted_at = NOW() WHERE id = $routeId";
                        if($conn->query($sql2)){
                            $response = "deleted";
                        } else{
                            $response = $conn->error;
                        }
                    } else{
                        $response = $conn->error;
                    }
                }
                
            }
            return $response;
        }
?>