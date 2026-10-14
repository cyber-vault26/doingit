<?php
        function createProduct(mysqli $conn, string $name, int $subId, string $category, string $imgUrl){
                if($conn === false){
                    exit();
                }

                $sql ="INSERT INTO `min_subs`(`id`, `name`, `sub_id`, `subcategory`, `type`, `img_url`, `status`) 
                                      VALUES (NULL,'$name','$subId','$category','product','$imgUrl', 'active')";
                if($conn->query($sql) === true){
                    return $conn->insert_id;
                } else{
                    return $conn->error;
                }
        }

        function selectAllProducts(mysqli $conn, string $type, string $status){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM min_subs WHERE type='$type' AND status='$status'";
            if($result =$conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function addProductPrice(mysqli $conn, int $productId, float $amount){
            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `prices`(`id`, `stock_item`, `price`) 
                                VALUES (NULL,'$productId','$amount')";
             if($conn->query($sql) === true){
                return $conn->insert_id;
            } else{
                return $conn->error;
            }                     
        }
        function selectProductPrice(mysqli $conn, int $item){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM food_prices WHERE stock_item='$item' AND deleted_at IS NULL;";
            if($result =$conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function selectProductStockBYItemAndDate(mysqli $conn, int $item, string $date){

                if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM stocks WHERE date_='$date' AND item_id='$item' AND deleted_at IS NULL;";
            if($result =$conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function placeOrder(mysqli $conn, int $waiterId, string $date, string $time ){

            if($conn === false){
                exit();
            }
            $sql = "INSERT INTO `hotel_orders`(`id`, `waiter_id`, `date_`, `time_`)
                                 VALUES (NULL,'$waiterId','$date','$time')";
            if($conn->query($sql) === true){
                return $conn->insert_id;
            } else{
                return $conn->error;
            } 
        }
        function placeOrderItem(mysqli $conn, int $item_id, int $order_id, float $qty, float $price, int $unit){
            if($conn === false){
                exit();
            }
             $sql = "INSERT INTO `hotel_order_items`(`id`, `item_id`, `qty`, `price`, `unit`, `order_id`)
                                     VALUES (NULL,'$item_id','$qty','$price','$unit','$order_id')";
            if($conn->query($sql) === true){
                return $conn->insert_id;
            } else{
                return $conn->error;
            }    
        }
        function selectOrderByStatus(mysqli $conn, string $status){
            if($conn === false){
                exit();
            }
            $sql = "SELECT hotel_orders.*,  users.names as waiter FROM hotel_orders JOIN users ON hotel_orders.waiter_id = users.id WHERE hotel_orders.status ='$status'";
            if($result =$conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }
        }
        function selectOrderById(mysqli $conn, int $orderId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT hotel_orders.*,  users.names as waiter FROM hotel_orders JOIN users ON hotel_orders.waiter_id = users.id WHERE hotel_orders.id ='$orderId'";
            if($result =$conn->query($sql)){
                $data = $result->fetch_assoc();
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }  
        }
        function selecteOrderItemByOrderId(mysqli $conn, int $orderId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT hotel_order_items.*, min_subs.name, units.name as unit, units.id as unit_id FROM hotel_order_items JOIN min_subs ON hotel_order_items.item_id = min_subs.id JOIN units ON hotel_order_items.unit = units.id WHERE hotel_order_items.order_id ='$orderId'";
            if($result =$conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            }  
        }
        function addRejectionComment(mysqli $conn, int $orderId, string $comment){
            if($conn === false){
                exit();
            }
            $sql ="UPDATE `hotel_orders` SET `status`='rejected',`comment`='$comment' WHERE id='$orderId'";
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }
        }
        function updateOrderStatus(mysqli $conn, int $orderId, string $status){
            if($conn === false){
                exit();
            }
            $sql ="UPDATE `hotel_orders` SET `status`='$status' WHERE id='$orderId'";
            if($conn->query($sql) === true){
                return true;
            } else{
                return $conn->error;
            }
        }
        function selectOrderByWaiterId(mysqli $conn, int $waiterId){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM hotel_orders WHERE waiter_id='$waiterId'";
            if($result =$conn->query($sql)){
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } else{
                return $conn->error;
            } 
        }
        function insertItemPrice(mysqli $conn, int $itemId, float $amount){
            if($conn === true){
                exit();
            }
            $sql = "INSERT INTO `food_prices`(`id`, `stock_item`, `price`)
                                 VALUES (NULL,'$itemId','$amount')";
            if($conn->query($sql)=== true){
                return true;
              } else{
                return $conn->error;
              }
        }

?>