<?php

function selectManufacturingOrdersByStatus(mysqli $conn, string $status) {
    $sql = "SELECT o.*, s.name AS customer_name 
            FROM stock_manufacturing_orders o
            LEFT JOIN subsidiaries s ON s.id = o.customer_id
            WHERE o.status = '$status' AND o.deleted_at IS NULL
            ORDER BY o.created_at DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function insertManufacturingOrder(mysqli $conn, string $reference_no, int $customer_id, string $date_, int $created_by) {
    $sql = "INSERT INTO stock_manufacturing_orders (reference_no, customer_id, date_, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sisi", $reference_no, $customer_id, $date_, $created_by);
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

function insertManufacturingOrderItem(mysqli $conn, int $order_id, int $item_id, int $unit_id, float $quantity, int $created_by) {
    $sql = "INSERT INTO stock_manufacturing_order_items (order_id, item_id, unit_id, quantity, created_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiidi", $order_id, $item_id, $unit_id, $quantity, $created_by);
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

 

function selectManufacturingOrderById(mysqli $conn, int $id) {
    $sql = "SELECT o.*, s.name AS customer_name 
            FROM stock_manufacturing_orders o
            LEFT JOIN subsidiaries s ON s.id = o.customer_id
            WHERE o.id = $id AND o.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function insertManufacturingRawMaterial(mysqli $conn, int $batch, int $item_id, int $unit_id, float $qty_used, float $base_qty, float $wastage, int $created_by) {
    $sql = "INSERT INTO stock_manufacturing_raw_materials (batch, item_id, unit_id, qty_used, base_qty, wastage, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiiiddi", $batch, $item_id, $unit_id, $qty_used, $base_qty, $wastage, $created_by);
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

function selectManufacturingOrderItems(mysqli $conn, int $order_id) {
    $sql = "SELECT oi.*, ms.name AS item_name, u.name AS unit_name
            FROM stock_manufacturing_order_items oi
            LEFT JOIN min_subs ms ON ms.id = oi.item_id
            LEFT JOIN units u ON u.id = oi.unit_id
            WHERE oi.order_id = $order_id AND oi.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function completeManufacturingOrder(mysqli $conn, int $id, int $updated_by) {
    $sql = "UPDATE stock_manufacturing_orders 
            SET status = 'completed', updated_at = NOW(), updated_by = $updated_by 
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteManufacturingOrder(mysqli $conn, int $id, int $deleted_by) {
    $sql = "UPDATE stock_manufacturing_orders 
            SET deleted_at = NOW(), deleted_by = $deleted_by 
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function updateManufacturingOrder(mysqli $conn, int $id, int $customer_id, string $date_, int $updated_by) {
    $sql = "UPDATE stock_manufacturing_orders 
            SET customer_id = $customer_id, date_ = '$date_', updated_at = NOW(), updated_by = $updated_by 
            WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function updateManufacturingOrderItem(mysqli $conn, int $id, int $item_id, int $unit_id, float $quantity, int $updated_by) {
    $sql = "UPDATE stock_manufacturing_order_items 
            SET item_id = $item_id, unit_id = $unit_id, quantity = $quantity, updated_at = NOW(), updated_by = $updated_by 
            WHERE id = $id";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteManufacturingOrderItem(mysqli $conn, int $id, int $deleted_by) {
    $sql = "UPDATE stock_manufacturing_order_items 
            SET deleted_at = NOW(), deleted_by = $deleted_by 
            WHERE id = $id";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function generateRefNumber(mysqli $conn, string $initial){
    $sql = "SELECT COUNT(DISTINCT ref_no) as number FROM stocks WHERE ref_no LIKE '$initial%'";
    if($result = $conn->query($sql)){
        $count = $result->fetch_assoc();
        $result->free();
        return $initial . ($count['number'] + 1);
    } else {
        return $conn->error;
    }
}

function selectAllStockStores(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stock_stores WHERE deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectStockStoreById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stock_stores WHERE id = $id AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function listProjectById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stock_stores WHERE id = $id AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function listProjectsByStatus(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stock_stores WHERE deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function insertStockStore(mysqli $conn, string $name, string $address, int $created_by) {
    if ($conn === false) exit();
    $sql = "INSERT INTO stock_stores (name, address, created_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("ssi", $name, $address, $created_by);
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

function updateStockStore(mysqli $conn, int $id, string $name, string $address, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE stock_stores SET name = '$name', address = '$address', updated_at = NOW(), updated_by = $updated_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteStockStore(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE stock_stores SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectAllItemUnitConversions(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT siuc.*, 
                   ms.name      AS item_name,
                   ms.base_unit AS base_unit_id,
                   ub.name      AS base_unit_name,
                   u2.name      AS unit_two_name
            FROM stock_item_unit_conversions siuc
            JOIN min_subs ms ON ms.id   = siuc.item_id
            JOIN units ub   ON ub.id    = ms.base_unit
            JOIN units u2   ON u2.id    = siuc.unit_two_id
            WHERE siuc.deleted_at IS NULL
            ORDER BY ms.name";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectItemUnitConversionById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stock_item_unit_conversions WHERE id = $id AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectItemUnitConversionByItemId(mysqli $conn, int $itemId) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stock_item_unit_conversions WHERE item_id = $itemId AND deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function insertItemUnitConversion(mysqli $conn, int $item_id, int $unit_two_id, float $quantity, int $created_by) {
    if ($conn === false) exit();
    $sql = "INSERT INTO stock_item_unit_conversions (item_id, unit_two_id, quantity, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iidi", $item_id, $unit_two_id, $quantity, $created_by);
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

function updateItemUnitConversion(mysqli $conn, int $id, int $item_id, int $unit_two_id, float $quantity, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE stock_item_unit_conversions 
            SET item_id    = $item_id, 
                unit_two_id = $unit_two_id, 
                quantity    = $quantity, 
                updated_at  = NOW(), 
                updated_by  = $updated_by 
            WHERE id = $id AND deleted_at IS NULL";  
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteItemUnitConversion(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE stock_item_unit_conversions SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function insertStockMovement(mysqli $conn, string $ref_no, ?int $voucher_id, ?int $purchase_order_id, string $date, int $item_id, float $quantity, float $price, float $base_quantity, int $unit, string $type, int $store_id, int $supplier_id, int $batch, ?string $expiry_date, int $created_by, int $currency_id = 4, float $currency_rate = 1.00, float $tax_rate = 0, int $to_store_id = 0) {
    $sql = "INSERT INTO `stocks` (`ref_no`, `voucher_id`, `purchase_order_id`, `date_`, `item_id`, `quantity`, `price`,`base_quantity`, `unit`, `type`, `store_id`, `to_store_id`, `supplier_id`, `batch`, `expiry_date`, `created_by` , `currency_id`, `currency_rate`, `tax_rate`) 
           VALUES (?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("siisidddisiiiisiidd", $ref_no, $voucher_id, $purchase_order_id, $date, $item_id, $quantity,$price, $base_quantity, $unit, $type, $store_id, $to_store_id , $supplier_id, $batch, $expiry_date, $created_by, $currency_id, $currency_rate, $tax_rate);
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


function updateStockMovement(mysqli $conn, int $id, ?int $purchase_order_id, string $date, int $item_id, float $quantity, float $base_quantity, float $price, int $unit, int $store_id, ?int $supplier_id, int $updated_by, int $currency_id = 4, float $currency_rate = 1.00, float $tax_rate = 0) {
    if($conn === false) return false;
    $po       = $purchase_order_id !== null ? (int) $purchase_order_id : 'NULL';
    $supplier = $supplier_id !== null ? (int) $supplier_id : 'NULL';
    $sql = "UPDATE `stocks` SET
                `purchase_order_id` = $po,
                `date_`             = '$date',
                `item_id`           = $item_id,
                `quantity`          = $quantity,
                `base_quantity`     = $base_quantity,
                `price`             = $price,
                `currency_id`       = $currency_id,
                `currency_rate`     = $currency_rate,
                `tax_rate`          = $tax_rate,
                `unit`              = $unit,
                `store_id`          = $store_id,
                `supplier_id`       = $supplier,
                `updated_by`        = $updated_by,
                `updated_at`        = NOW()
            WHERE `id` = $id
              AND `deleted_at` IS NULL";
    if($conn->query($sql)) return true;
    return false;
}
 

function selectStockBatchesByType(mysqli $conn, string $type, string $date1 = null, string $date2 = null, int $store = null, string $status = null, int $batch = null) {
    if ($conn === false) exit();
    $sql = "SELECT *
            FROM stocks
            WHERE type = '$type' AND deleted_at IS NULL";
    if ($batch  !== null) $sql .= " AND batch = $batch";
    if ($date1  !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2  !== null) $sql .= " AND date_ <= '$date2'";
    if ($store  !== null) $sql .= " AND store_id = $store";
    if ($status !== null) $sql .= " AND status = '$status'";
    if ($batch  === null) $sql .= " GROUP BY batch ORDER BY date_";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectStockMovementByItemId(mysqli $conn, int $itemId, string $date1 = null, string $date2 = null, int $storeId = null) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stocks WHERE item_id = $itemId AND deleted_at IS NULL";
    if ($date1   !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2   !== null) $sql .= " AND date_ <= '$date2'";
    if ($storeId !== null) $sql .= " AND store_id = $storeId";
    $sql .= " ORDER BY date_ ASC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function sumStockMovementByItemId(mysqli $conn, int $itemId, string $date1 = null, string $date2 = null, int $storeId = null) {
    if ($conn === false) exit();
    $sql = "SELECT SUM(CASE WHEN type IN ('in', 'transfer-in') THEN base_quantity ELSE 0 END) -
                   SUM(CASE WHEN type IN ('out', 'transfer-out') THEN base_quantity ELSE 0 END)
            AS balance FROM stocks WHERE item_id = $itemId AND deleted_at IS NULL";
    if ($date1   !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2   !== null) $sql .= " AND date_ <= '$date2'";
    if ($storeId !== null) $sql .= " AND store_id = $storeId";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return (float) ($data['balance'] ?? 0);
    } else {
        return $conn->error;
    }
}

function selectStockMovementByItemIdAndType(mysqli $conn, int $itemId, string $type = null, string $date1 = null, string $date2 = null, int $storeId = null) {
    if ($conn === false) exit();
    $sql = "SELECT * FROM stocks WHERE item_id = $itemId AND deleted_at IS NULL";
    if ($type    !== null) $sql .= " AND type = '$type'";
    if ($date1   !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2   !== null) $sql .= " AND date_ <= '$date2'";
    if ($storeId !== null) $sql .= " AND store_id = $storeId";
    $sql .= " ORDER BY date_ ASC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}



function selectStockBatchMeta(mysqli $conn, int $batch) {
    $sql = "SELECT type, voucher_id FROM stocks WHERE batch = $batch AND deleted_at IS NULL LIMIT 1";
    if($result = $conn->query($sql)){
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    }
    return null;
} 


function reviewStock(mysqli $conn, int $batch, int $reviewed_by) {
    if($conn === false) return false;
    $sql = "UPDATE `stocks` SET
                `status`      = 'reviewed',
                `reviewed_at` = NOW(),
                `reviewed_by` = $reviewed_by
            WHERE `batch` = $batch AND `deleted_at` IS NULL";
    if($conn->query($sql)) return true;
    return $conn->error;
}
 
function approveStock(mysqli $conn, int $batch, int $approved_by) {
    if($conn === false) return false;
    $sql = "UPDATE `stocks` SET
                `status`      = 'approved',
                `approved_at` = NOW(),
                `approved_by` = $approved_by
            WHERE `batch` = $batch AND `deleted_at` IS NULL";
    if($conn->query($sql)) return true;
    return $conn->error;
}
 
function rejectStock(mysqli $conn, int $batch, int $rejected_by, string $rejection_reason) {
    if($conn === false) return false;
    $rejection_reason = $conn->real_escape_string($rejection_reason);
    $sql = "UPDATE `stocks` SET
                `status`           = 'rejected',
                `rejected_at`      = NOW(),
                `rejected_by`      = $rejected_by,
                `rejection_reason` = '$rejection_reason'
            WHERE `batch` = $batch AND `deleted_at` IS NULL";
    if($conn->query($sql)) return true;
    return $conn->error;
}

function deleteStockByRefNo(mysqli $conn, string $ref_no, int $deleted_by) {
    $sql = "UPDATE `stocks` SET `deleted_at` = NOW(), `deleted_by` = $deleted_by WHERE `ref_no` = '$ref_no' AND `deleted_at` IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteStock(mysqli $conn, string $id, int $deleted_by) {
    $sql = "UPDATE `stocks` SET `deleted_at` = NOW(), `deleted_by` = $deleted_by WHERE `id` = $id AND `deleted_at` IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteStockByBatch(mysqli $conn, int $batch, int $deleted_by) {
    $sql = "UPDATE `stocks` SET `deleted_at` = NOW(), `deleted_by` = $deleted_by WHERE `batch` = $batch AND `deleted_at` IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

 

?>