<?php

function createPurchaseRequest(mysqli $conn, string $ref_no, string $date_, string $request_heading, int $currency, float $currency_rate, float $subtotal, float $total_amount, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `purchase_requests` (`ref_no`, `date_`, `request_heading`, `currency`, `currency_rate`, `subtotal`, `total_amount`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sssidddi", $ref_no, $date_, $request_heading, $currency, $currency_rate, $subtotal, $total_amount, $created_by);
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

function createPurchaseRequestItem(mysqli $conn, int $purchase_request_id,int $project_id, int $description, int $unit, float $quantity, float $price, float $amount, float $amount_eqv, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `purchase_request_items` (`purchase_request_id`,`project_id`, `item_id`, `unit`, `quantity`, `price`, `amount`, `amount_eqv`, `created_by`) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiiiddddi", $purchase_request_id,$project_id, $description, $unit, $quantity, $price, $amount, $amount_eqv, $created_by);
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

function selectAllPurchaseRequests(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT pr.*,  c.name as currency_name 
            FROM purchase_requests pr 
            LEFT JOIN currency c ON c.id = pr.currency
            WHERE pr.deleted_at IS NULL ORDER BY pr.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectPurchaseRequestById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT pr.*, c.name as currency_name,
            cb.names as created_by_name,
            chb.names as checked_by_name,
            rb.names as reviewed_by_name,
            ab.names as approved_by_name,
            rejb.names as rejected_by_name
            FROM purchase_requests pr
            LEFT JOIN currency c ON c.id = pr.currency
            LEFT JOIN users cb ON cb.id = pr.created_by
            LEFT JOIN users chb ON chb.id = pr.checked_by
            LEFT JOIN users rb ON rb.id = pr.reviewed_by
            LEFT JOIN users ab ON ab.id = pr.approved_by
            LEFT JOIN users rejb ON rejb.id = pr.rejected_by
            WHERE pr.id = '$id' AND pr.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectPurchaseRequestsByStatus(mysqli $conn, string $status) {
    if ($conn === false) exit();
    $sql = "SELECT pr.*,  c.name as currency_name 
            FROM purchase_requests pr 
            LEFT JOIN currency c ON c.id = pr.currency
            WHERE pr.status = '$status' AND pr.deleted_at IS NULL ORDER BY pr.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectPurchaseRequestItemsByPurchaseRequestId(mysqli $conn, int $purchase_request_id) {
    if ($conn === false) exit();
    $sql = "SELECT pri.*, u.name as unit_name, ms.name as item_name 
            FROM purchase_request_items pri 
            LEFT JOIN units u ON u.id = pri.unit
            LEFT JOIN min_subs ms ON ms.id = pri.item_id
            WHERE pri.purchase_request_id = '$purchase_request_id' AND pri.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function updatePurchaseRequestStatus(mysqli $conn, int $id, string $status, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_requests SET status = '$status', updated_by = $updated_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeletePurchaseRequest(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_requests SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeletePurchaseRequestItemsByPurchaseRequestId(mysqli $conn, int $purchase_request_id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_request_items SET deleted_at = NOW(), deleted_by = $deleted_by WHERE purchase_request_id = '$purchase_request_id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}


function checkPurchaseRequest(mysqli $conn, int $id, int $user_id) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_requests SET status = 'checked', checked_by = $user_id, checked_at = NOW() WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function reviewPurchaseRequest(mysqli $conn, int $id, int $user_id) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_requests SET status = 'reviewed', reviewed_by = $user_id, reviewed_at = NOW() WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function approvePurchaseRequest(mysqli $conn, int $id, int $user_id) {
    if ($conn === false) exit();
    $sql = "UPDATE purchase_requests SET status = 'approved', approved_by = $user_id, approved_at = NOW() WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function rejectPurchaseRequest(mysqli $conn, int $id, int $user_id, string $reason) {
    if ($conn === false) exit();
    $reason = $conn->real_escape_string($reason);
    $sql = "UPDATE purchase_requests SET status = 'rejected', rejected_by = $user_id, rejected_at = NOW(), rejection_reason = '$reason' WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

?>