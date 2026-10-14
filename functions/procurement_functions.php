<?php

function createAdvanceCashRequest(mysqli $conn, string $ref_no, string $date_, string $type, int $requester_id, int $project_id, int $currency, float $currency_rate, string $reason, float $total_amount, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `advance_cash_requests` (`ref_no`, `date_`, `type`, `requester_id`, `project_id`, `currency`, `currency_rate`, `reason`, `total_amount`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sssiiidsdi", $ref_no, $date_, $type, $requester_id, $project_id, $currency, $currency_rate, $reason, $total_amount, $created_by);
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

function createAdvanceCashRequestItem(mysqli $conn, int $advance_cash_request_id, int $item_id, float $amount, float $amount_eqv, string $bank, string $account, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `advance_cash_request_items` (`advance_cash_request_id`, `item_id`, `amount`, `amount_eqv`, `bank`, `account`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiddssi", $advance_cash_request_id, $item_id, $amount, $amount_eqv, $bank, $account, $created_by);      
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

function selectAllAdvanceCashRequests(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT acr.*, p.name as project_name, c.name as currency_name, u.names as requester_name
            FROM advance_cash_requests acr
            LEFT JOIN projects p ON p.id = acr.project_id
            LEFT JOIN currency c ON c.id = acr.currency
            LEFT JOIN users u ON u.id = acr.requester_id
            WHERE acr.deleted_at IS NULL ORDER BY acr.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectAdvanceCashRequestById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT acr.*, 
                p.name as project_name, 
                c.name as currency_name, 
                s.name as requester_name,
                uc.names as created_by_name,
                ua.names as approved_by_name,
                up.names as paid_by_name
            FROM advance_cash_requests acr
            LEFT JOIN projects p ON p.id = acr.project_id
            LEFT JOIN currency c ON c.id = acr.currency
            LEFT JOIN subsidiaries s ON s.id = acr.requester_id
            LEFT JOIN users uc ON uc.id = acr.created_by
            LEFT JOIN users ua ON ua.id = acr.approved_by
            LEFT JOIN users up ON up.id = acr.paid_by
            WHERE acr.id = '$id' AND acr.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectAdvanceCashRequestsByStatus(mysqli $conn, string $status) {
    if ($conn === false) exit();
    $sql = "SELECT acr.*, p.name as project_name, c.name as currency_name, s.name as requester_name
            FROM advance_cash_requests acr
            LEFT JOIN projects p ON p.id = acr.project_id
            LEFT JOIN currency c ON c.id = acr.currency
            LEFT JOIN subsidiaries s ON s.id = acr.requester_id
            WHERE acr.status = '$status' AND acr.deleted_at IS NULL ORDER BY acr.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectAdvanceCashRequestItemsByRequestId(mysqli $conn, int $advance_cash_request_id) {
    if ($conn === false) exit();
    $sql = "SELECT acri.*, ms.name as item_name
            FROM advance_cash_request_items acri
            LEFT JOIN min_subs ms ON ms.id = acri.item_id
            WHERE acri.advance_cash_request_id = '$advance_cash_request_id' AND acri.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function updateAdvanceCashRequestStatus(mysqli $conn, int $id, string $status, int $updated_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_requests SET status = '$status', updated_by = $updated_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function approveAdvanceCashRequest(mysqli $conn, int $id, int $approved_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_requests 
            SET status = 'approved', 
                approved_by = $approved_by,
                approved_at = NOW(),
                updated_by = $approved_by,
                rejection_reason = NULL
            WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function rejectAdvanceCashRequest(mysqli $conn, int $id, int $rejected_by, string $rejection_reason) {
    if ($conn === false) exit();
    $rejection_reason = $conn->real_escape_string($rejection_reason);
    $sql = "UPDATE advance_cash_requests 
            SET status = 'rejected', 
                updated_by = $rejected_by,
                rejection_reason = '$rejection_reason'
            WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function markAdvanceCashRequestAsPaid(mysqli $conn, int $id, int $paid_by, int $transaction_id) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_requests 
            SET status = 'paid', 
                paid_by = $paid_by,
                paid_at = NOW(),
                updated_by = $paid_by,
                transaction_id = $transaction_id
            WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteAdvanceCashRequest(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_requests SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = '$id' AND deleted_at IS NULL";
    if (!$conn->query($sql)) return $conn->error;
    $sql2 = "UPDATE transaction_voucher tv
             JOIN advance_cash_requests acr ON acr.transaction_id = tv.id
             SET tv.deleted_at = NOW(), tv.deleted_by = $deleted_by
             WHERE acr.id = '$id' AND tv.deleted_at IS NULL AND acr.transaction_id IS NOT NULL";
    if (!$conn->query($sql2)) return $conn->error;
    return true;
}

function softDeleteAdvanceCashRequestItemsByRequestId(mysqli $conn, int $advance_cash_request_id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_request_items SET deleted_at = NOW(), deleted_by = $deleted_by WHERE advance_cash_request_id = '$advance_cash_request_id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function updateAdvanceCashRequest(mysqli $conn, int $id, string $date_, string $type, int $requester_id, int $project_id, int $currency, float $currency_rate, string $reason, float $total_amount, int $updated_by) {
    if ($conn === false) exit();
    $reason = $conn->real_escape_string($reason);
    $sql = "UPDATE advance_cash_requests 
            SET date_ = '$date_', type = '$type', requester_id = $requester_id, project_id = $project_id,
                currency = $currency, currency_rate = $currency_rate, reason = '$reason',
                total_amount = $total_amount, status = 'pending', updated_by = $updated_by,
                rejection_reason = NULL
            WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function updateAdvanceCashRequestItem(mysqli $conn, int $id, int $item_id, float $amount, float $amount_eqv, string $bank, string $account, int $updated_by) {
    if ($conn === false) exit();
    $bank = $conn->real_escape_string($bank);
    $account = $conn->real_escape_string($account);
    $sql = "UPDATE advance_cash_request_items 
            SET item_id = $item_id, amount = $amount, amount_eqv = $amount_eqv,
                bank = '$bank', account = '$account', updated_by = $updated_by
            WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteAdvanceCashRequestItemById(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_request_items SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}





























 

function createRetirement(mysqli $conn, string $ref_no, string $date_, int $advance_cash_request_id, int $requester_id, int $project_id, int $currency, float $currency_rate, float $requested_amount, float $total_spent, float $refund_amount, float $excess_amount, string $notes, int $created_by) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `advance_cash_retirements` (`ref_no`, `date_`, `advance_cash_request_id`, `requester_id`, `project_id`, `currency`, `currency_rate`, `requested_amount`, `total_spent`, `refund_amount`, `excess_amount`, `notes`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("ssiiiidddddsi", $ref_no, $date_, $advance_cash_request_id, $requester_id, $project_id, $currency, $currency_rate, $requested_amount, $total_spent, $refund_amount, $excess_amount, $notes, $created_by);
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

function createRetirementItem(mysqli $conn, int $retirement_id, int $advance_cash_request_id, int $item_id, string $date_spent, float $amount_spent, float $amount_spent_eqv, string $receipt_path=null, int $created_by=null) {
    if ($conn === false) return false;
    $sql = "INSERT INTO `advance_cash_retirement_items` (`retirement_id`, `advance_cash_request_id`, `item_id`, `date_spent`, `amount_spent`, `amount_spent_eqv`, `receipt_path`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("iiisddsi", $retirement_id, $advance_cash_request_id, $item_id, $date_spent, $amount_spent, $amount_spent_eqv, $receipt_path, $created_by);
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

function selectAllRetirements(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT r.*, 
                p.name as project_name, 
                c.name as currency_name, 
                s.name as requester_name,
                acr.ref_no as advance_ref_no,
                uc.names as created_by_name,
                ua.names as approved_by_name
            FROM advance_cash_retirements r
            LEFT JOIN advance_cash_requests acr ON acr.id = r.advance_cash_request_id
            LEFT JOIN projects p ON p.id = r.project_id
            LEFT JOIN currency c ON c.id = r.currency
            LEFT JOIN subsidiaries s ON s.id = r.requester_id
            LEFT JOIN users uc ON uc.id = r.created_by
            LEFT JOIN users ua ON ua.id = r.approved_by
            WHERE r.deleted_at IS NULL ORDER BY r.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectRetirementById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT r.*, 
                p.name as project_name, 
                c.name as currency_name, 
                s.name as requester_name,
                acr.ref_no as advance_ref_no,
                uc.names as created_by_name,
                ua.names as approved_by_name
            FROM advance_cash_retirements r
            LEFT JOIN advance_cash_requests acr ON acr.id = r.advance_cash_request_id
            LEFT JOIN projects p ON p.id = r.project_id
            LEFT JOIN currency c ON c.id = r.currency
            LEFT JOIN subsidiaries s ON s.id = r.requester_id
            LEFT JOIN users uc ON uc.id = r.created_by
            LEFT JOIN users ua ON ua.id = r.approved_by
            WHERE r.id = '$id' AND r.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectRetirementsByStatus(mysqli $conn, string $status) {
    if ($conn === false) exit();
    $sql = "SELECT r.*, 
                p.name as project_name, 
                c.name as currency_name, 
                s.name as requester_name,
                acr.ref_no as advance_ref_no
            FROM advance_cash_retirements r
            LEFT JOIN advance_cash_requests acr ON acr.id = r.advance_cash_request_id
            LEFT JOIN projects p ON p.id = r.project_id
            LEFT JOIN currency c ON c.id = r.currency
            LEFT JOIN subsidiaries s ON s.id = r.requester_id
            WHERE r.status = '$status' AND r.deleted_at IS NULL ORDER BY r.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectRetirementsByAdvanceCashRequestId(mysqli $conn, int $advance_cash_request_id) {
    if ($conn === false) exit();
    $sql = "SELECT r.*, c.name as currency_name
            FROM advance_cash_retirements r
            LEFT JOIN currency c ON c.id = r.currency
            WHERE r.advance_cash_request_id = '$advance_cash_request_id' AND r.deleted_at IS NULL ORDER BY r.date_ DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectRetirementItemsByRetirementId(mysqli $conn, int $retirement_id) {
    if ($conn === false) exit();
    $sql = "SELECT ri.*, s.name as item_name
            FROM advance_cash_retirement_items ri
            LEFT JOIN subsidiaries s ON s.id = ri.item_id
            WHERE ri.retirement_id = '$retirement_id' AND ri.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function approveRetirement(mysqli $conn, int $id, int $approved_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_retirements SET status = 'approved', approved_by = $approved_by, approved_at = NOW(), rejection_reason = NULL WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function rejectRetirement(mysqli $conn, int $id, int $rejected_by, string $rejection_reason) {
    if ($conn === false) exit();
    $rejection_reason = $conn->real_escape_string($rejection_reason);
    $sql = "UPDATE advance_cash_retirements SET status = 'rejected', rejection_reason = '$rejection_reason', updated_by = $rejected_by, updated_at = NOW() WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteRetirement(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_retirements SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = '$id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function softDeleteRetirementItemsByRetirementId(mysqli $conn, int $retirement_id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE advance_cash_retirement_items SET deleted_at = NOW(), deleted_by = $deleted_by WHERE retirement_id = '$retirement_id' AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

?>