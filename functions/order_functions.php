<?php
        function countOrders(mysqli $conn){
            if($conn === false){
                exit();
            } 
            $sql = "SELECT COUNT(id) as jumla FROM orders ";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }

        function createOrder(mysqli $conn,string $refNo, int $customerId, string $date, int $routeId, string $loadType, string $description, float $weight ){

            if($conn === false){
                exit();
            } 
            $sql = "INSERT INTO `orders`(`id`, `ref_no`, `customer_id`, `route_id`, `load_type`, `weight`, `date_`, `load_description`, `status`) 
                                VALUES (NULL,'$refNo','$customerId','$routeId','$loadType','$weight','$date','$description','new')"; 
            if($conn->query($sql) === true){
                return $conn->insert_id;

            }else{
                return $conn->error;
            }
        }
        function selectOrderByStatus(mysqli $conn, string $status){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM orders WHERE status='$status'";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function updateOrderStatus(mysqli $conn, string $status, int $id){
            if($conn === false){
                exit();
            }
            $sql = "UPDATE `orders` SET status='$status' WHERE id='$id'"; 
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }
        }
        function selectOrderById(mysqli $conn, int $orderId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT orders.*, subsidiaries.name as route FROM orders JOIN subsidiaries ON orders.route_id = subsidiaries.id WHERE orders.id='$orderId';";
            if($result = $conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }

        function selectOrderRoutesByRouterId(mysqli $conn, int $orderId){
            $sql = "SELECT routes.*, orders.ref_no FROM routes JOIN orders ON routes.id = orders.route_id WHERE orders.id='$orderId';";
            if($result = $conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
               
        }
    }

?>
