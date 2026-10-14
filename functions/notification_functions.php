<?php

function setNotification($type, $message) {
    $_SESSION['notification'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getNotification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        return $notification;
    }
    return null;
}

function countVouchersByStatus(mysqli $conn) {
    $sql = "SELECT status, COUNT(*) as total FROM transaction_voucher 
            WHERE deleted_at IS NULL 
            AND voucher_type NOT IN ('purchases', 'sales', 'bulky-payment')
            GROUP BY status";
    $result = $conn->query($sql);
    $counts = ['pending' => 0, 'draft' => 0, 'rejected' => 0];
    if($result){
        while($row = $result->fetch_assoc()){
            $counts[$row['status']] = (int)$row['total'];
        }
        $result->free();
    }
    return $counts;
}

function countSalesVouchersByStatus(mysqli $conn) {
    $sql = "SELECT status, COUNT(*) as total FROM sales_voucher 
            WHERE deleted_at IS NULL 
            GROUP BY status";
    $result = $conn->query($sql);
    $counts = ['pending' => 0, 'draft' => 0, 'rejected' => 0];
    if($result){
        while($row = $result->fetch_assoc()){
            $counts[$row['status']] = (int)$row['total'];
        }
        $result->free();
    }
    return $counts;
}

function countPurchaseVouchersByStatus(mysqli $conn) {
    $sql = "SELECT status, COUNT(*) as total FROM purchase_voucher 
            WHERE deleted_at IS NULL 
            GROUP BY status";
    $result = $conn->query($sql);
    $counts = ['pending' => 0, 'draft' => 0, 'rejected' => 0];
    if($result){
        while($row = $result->fetch_assoc()){
            $counts[$row['status']] = (int)$row['total'];
        }
        $result->free();
    }
    return $counts;
}

function countBulkyVouchersByStatus(mysqli $conn) {
    $sql = "SELECT status, COUNT(*) as total FROM bulky_transactions 
            WHERE deleted_at IS NULL 
            GROUP BY status";
    $result = $conn->query($sql);
    $counts = ['pending' => 0, 'draft' => 0, 'rejected' => 0];
    if($result){
        while($row = $result->fetch_assoc()){
            $counts[$row['status']] = (int)$row['total'];
        }
        $result->free();
    }
    return $counts;
}

function countStockByStatus(mysqli $conn, $cost_center = null) {
    $sql = "SELECT status, COUNT(DISTINCT batch) as total FROM stocks 
            WHERE deleted_at IS NULL";
    if($cost_center){
        $sql .= " AND store_id = " . (int)$cost_center;
    }
    $sql .= " GROUP BY status";
    $result = $conn->query($sql);
    $counts = ['pending' => 0, 'reviewed' => 0, 'rejected' => 0];
    if($result){
        while($row = $result->fetch_assoc()){
            $counts[$row['status']] = (int)$row['total'];
        }
        $result->free();
    }
    return $counts;
}

 
?>