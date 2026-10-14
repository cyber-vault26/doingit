<?php


function insertMinSub(mysqli $conn, string $name, string $code = null, string $image_url = null, float $price = null, int $sub_id, string $subcategory, string $type, int $created_by, int $base_unit = null, string $packaging_size = null, float $reorder_level = null) {
    if ($conn === false) exit();
    $sql  = "INSERT INTO min_subs (`name`, `code`, `image_url`, `price`, `sub_id`, `subcategory`, `type`, `created_by`, `base_unit`, `packaging_size`, `reorder_level`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) return $conn->error;
    $stmt->bind_param("sssdissiisd", $name, $code, $image_url, $price, $sub_id, $subcategory, $type, $created_by, $base_unit, $packaging_size, $reorder_level);
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

function updateMinSub(mysqli $conn, int $id, string $name, string $code = null, string $image_url = null, float $price = null, int $sub_id, string $subcategory, string $type, int $updated_by, int $base_unit = null, string $packaging_size = null, float $reorder_level = null) {
    if ($conn === false) exit();

    $code_sql           = $code           !== null ? "'" . $conn->real_escape_string($code)           . "'" : "NULL";
    $image_url_sql      = $image_url      !== null ? "'" . $conn->real_escape_string($image_url)      . "'" : "NULL";
    $price_sql          = $price          !== null ? (float) $price                                        : "NULL";
    $base_unit_sql      = $base_unit      !== null ? (int) $base_unit                                      : "NULL";
    $packaging_size_sql = $packaging_size !== null ? "'" . $conn->real_escape_string($packaging_size) . "'" : "NULL";
    $reorder_level_sql  = $reorder_level  !== null ? (float) $reorder_level                               : "NULL";

    $sql = "UPDATE min_subs
            SET name           = '$name',
                code           = $code_sql,
                image_url      = $image_url_sql,
                price          = $price_sql,
                sub_id         = $sub_id,
                subcategory    = '$subcategory',
                type           = '$type',
                base_unit      = $base_unit_sql,
                packaging_size = $packaging_size_sql,
                reorder_level  = $reorder_level_sql,
                updated_by     = $updated_by,
                updated_at     = NOW()
            WHERE id = $id";

    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}
 
function softDeleteMinSub(mysqli $conn, int $id, int $deleted_by) {
    if ($conn === false) exit();
    $sql = "UPDATE min_subs SET deleted_at = NOW(), deleted_by = $deleted_by WHERE id = $id AND deleted_at IS NULL";
    if ($conn->query($sql)) {
        return true;
    } else {
        return $conn->error;
    }
}

function selectAllMinSub(mysqli $conn) {
    if ($conn === false) exit();
    $sql = "SELECT ms.*, s.name as subsidiary_name FROM min_subs ms LEFT JOIN subsidiaries s ON ms.sub_id = s.id WHERE ms.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}
function selectMinSubById(mysqli $conn, int $id) {
    if ($conn === false) exit();
    $sql = "SELECT ms.*, s.name as subsidiary_name FROM min_subs ms LEFT JOIN subsidiaries s ON ms.sub_id = s.id WHERE ms.id = $id AND ms.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}
function selectMinSubsByParentSubsidiary(mysqli $conn, int $sub_id) {
    if ($conn === false) exit();
    $sql = "SELECT ms.*, s.name as subsidiary_name FROM min_subs ms LEFT JOIN subsidiaries s ON ms.sub_id = s.id WHERE ms.sub_id = $sub_id AND ms.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}
function selectMinSubsByType(mysqli $conn, string $type) {
    if ($conn === false) exit();
    $sql = "SELECT ms.*, s.name as subsidiary_name FROM min_subs ms LEFT JOIN subsidiaries s ON ms.sub_id = s.id WHERE ms.type = '$type' AND ms.deleted_at IS NULL";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

?>