<?php

function createProfoma(mysqli $conn, string $ref_no, string $date_, string $expiry_date, int $customer_id, string $invoice_heading, string $payment_terms, int $currency, float $currency_rate, float $subtotal,   float $tax_amount, float $total_amount, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `profomas` (`ref_no`, `date_`, `expiry_date`, `customer_id`,  `invoice_heading`,`payment_terms`, `currency`, `currency_rate`, `subtotal`,  `tax_amount`, `total_amount`, `created_by`) VALUES (  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sssissiddddi", $ref_no, $date_, $expiry_date, $customer_id, $invoice_heading, $payment_terms, $currency, $currency_rate, $subtotal, $tax_amount, $total_amount, $created_by);
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

function createProfomaItem(mysqli $conn, int $profoma_id, int $item_id, int $unit, float $quantity,float $tax_rate, float $price, float $amount, float $eqv_amount, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `profoma_items` (`profoma_id`, `item_id`, `unit`, `quantity`,`tax_rate`, `price`, `amount`, `eqv_amount`, `created_by`) VALUES (?, ?,?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiidddddi", $profoma_id, $item_id, $unit, $quantity,$tax_rate, $price, $amount, $eqv_amount, $created_by);
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

function selectAllProfomas(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT p.*, s.name as customer_name, c.name as currency_name 
            FROM profomas p 
            LEFT JOIN subsidiaries s ON s.id = p.customer_id 
            LEFT JOIN currency c ON c.id = p.currency
            WHERE p.deleted_at IS NULL ORDER BY p.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectProfomaById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT p.*, s.name as customer_name, c.name as currency_name 
            FROM profomas p 
            LEFT JOIN subsidiaries s ON s.id = p.customer_id 
            LEFT JOIN currency c ON c.id = p.currency
            WHERE p.id = '$id' AND p.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectProfomasByStatus(mysqli $conn, string $status) {
    if ($conn === false) exit();
    $sql = "SELECT p.*, s.name as customer_name, c.name as currency_name 
            FROM profomas p 
            LEFT JOIN subsidiaries s ON s.id = p.customer_id 
            LEFT JOIN currency c ON c.id = p.currency
            WHERE p.status = '$status' AND p.deleted_at IS NULL ORDER BY p.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectProfomaItemsByProfomaId(mysqli $conn, int $profoma_id) {
    if ($conn === false) exit();
    $sql = "SELECT pi.*, u.name as unit_name, ms.name as item_name ,ms.image_url
            FROM profoma_items pi 
            LEFT JOIN units u ON u.id = pi.unit 
            LEFT JOIN min_subs ms ON ms.id = pi.item_id 
            WHERE pi.profoma_id = '$profoma_id' AND pi.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

 
function updateProfomaStatus(mysqli $conn, int $id, string $status, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE profomas SET status = '$status', updated_by = $updated_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteProfoma(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE profomas SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteProfomaItemsByProfomaId(mysqli $conn, int $profoma_id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE profoma_items SET deleted_at = NOW(), deleted_by = $deleted_by WHERE profoma_id = '$profoma_id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

?>