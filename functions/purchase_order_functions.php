<?php

function createPurchaseOrder(mysqli $conn, string $ref_no, string $date_, string $expiry_date, int $supplier_id, int $project_id, string $invoice_heading, string $payment_terms, int $currency, float $currency_rate, float $subtotal, float $tax_rate, float $tax_amount, float $total_amount, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `purchase_orders` (`ref_no`, `date_`, `expiry_date`, `supplier_id`, `project_id`, `invoice_heading`, `payment_terms`, `currency`, `currency_rate`, `subtotal`, `tax_rate`, `tax_amount`, `total_amount`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sssiissidddddi", $ref_no, $date_, $expiry_date, $supplier_id, $project_id, $invoice_heading, $payment_terms, $currency, $currency_rate, $subtotal, $tax_rate, $tax_amount, $total_amount, $created_by);
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

function createPurchaseOrderItem(mysqli $conn, int $purchase_order_id, int $item_id, int $unit, float $quantity, float $price, float $amount, float $eqv_amount, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `purchase_order_items` (`purchase_order_id`, `item_id`, `unit`, `quantity`, `price`, `amount`, `eqv_amount`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiiddddi", $purchase_order_id, $item_id, $unit, $quantity, $price, $amount, $eqv_amount, $created_by);
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

function selectAllPurchaseOrders(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT po.*, s.name as supplier_name, c.name as currency_name 
            FROM purchase_orders po 
            LEFT JOIN subsidiaries s ON s.id = po.supplier_id 
            LEFT JOIN currency c ON c.id = po.currency
            WHERE po.deleted_at IS NULL ORDER BY po.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectPurchaseOrderById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT po.*, s.name as supplier_name, c.name as currency_name 
            FROM purchase_orders po 
            LEFT JOIN subsidiaries s ON s.id = po.supplier_id 
            LEFT JOIN currency c ON c.id = po.currency
            WHERE po.id = '$id' AND po.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectPurchaseOrdersByStatus(mysqli $conn, string $status) {
    if ($conn === false) exit();
    $sql = "SELECT po.*, s.name as supplier_name, c.name as currency_name 
            FROM purchase_orders po 
            LEFT JOIN subsidiaries s ON s.id = po.supplier_id 
            LEFT JOIN currency c ON c.id = po.currency
            WHERE po.status = '$status' AND po.deleted_at IS NULL ORDER BY po.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectPurchaseOrderItemsByPurchaseOrderId(mysqli $conn, int $purchase_order_id) {
    if ($conn === false) exit();
    $sql = "SELECT poi.*, u.name as unit_name, ms.name as item_name 
            FROM purchase_order_items poi 
            LEFT JOIN units u ON u.id = poi.unit 
            LEFT JOIN min_subs ms ON ms.id = poi.item_id 
            WHERE poi.purchase_order_id = '$purchase_order_id' AND poi.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function updatePurchaseOrderStatus(mysqli $conn, int $id, string $status, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_orders SET status = '$status', updated_by = $updated_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeletePurchaseOrder(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_orders SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}
function updatePurchaseOrder(mysqli $conn, int $id, string $ref_no, string $date_, string $expiry_date, int $supplier_id, int $project_id, string $invoice_heading, string $payment_terms, int $currency, float $currency_rate, float $subtotal, float $tax_rate, float $tax_amount, float $total_amount, int $updated_by) {
    if ($conn === false) return false;
    $sql = "UPDATE `purchase_orders` SET `ref_no` = ?, `date_` = ?, `expiry_date` = ?, `supplier_id` = ?, `project_id` = ?, `invoice_heading` = ?, `payment_terms` = ?, `currency` = ?, `currency_rate` = ?, `subtotal` = ?, `tax_rate` = ?, `tax_amount` = ?, `total_amount` = ?, `updated_by` = ? WHERE `id` = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sssiissidddddii", $ref_no, $date_, $expiry_date, $supplier_id, $project_id, $invoice_heading, $payment_terms, $currency, $currency_rate, $subtotal, $tax_rate, $tax_amount, $total_amount, $updated_by, $id);
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows > 0;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

function updatePurchaseOrderItem(mysqli $conn, int $id, int $item_id, int $unit, float $quantity, float $price, float $amount, float $eqv_amount, int $updated_by) {
    if ($conn === false) return false;
    $sql = "UPDATE `purchase_order_items` SET `item_id` = ?, `unit` = ?, `quantity` = ?, `price` = ?, `amount` = ?, `eqv_amount` = ?, `updated_by` = ? WHERE `id` = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiddddii", $item_id, $unit, $quantity, $price, $amount, $eqv_amount, $updated_by, $id);
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows > 0;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}
function softDeletePurchaseOrderItemsByPurchaseOrderId(mysqli $conn, int $purchase_order_id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_order_items SET deleted_at = NOW(), deleted_by = $deleted_by WHERE purchase_order_id = '$purchase_order_id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

?>