<?php
function sumStockIn(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null) {
    if ($conn === false) exit();
    $sql = "SELECT COUNT(DISTINCT batch) as total_grns, COALESCE(SUM(base_quantity * price), 0) as total_value FROM stocks WHERE type = 'in' AND ref_no LIKE 'GRN/%' AND deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND date_ <= '$date2'";
    if ($store !== null) $sql .= " AND store_id = $store";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function sumStockOut(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null) {
    if ($conn === false) exit();
    $sql = "SELECT COUNT(DISTINCT batch) as total_gdns, COALESCE(SUM(quantity * price), 0) as total_value FROM stocks WHERE type = 'out' AND ref_no LIKE 'GDN/%' AND deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND date_ <= '$date2'";
    if ($store !== null) $sql .= " AND store_id = $store";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function sumStockTransfers(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null) {
    if ($conn === false) exit();
    $sql = "SELECT COUNT(DISTINCT batch) as total_transfers FROM stocks WHERE type = 'out' AND ref_no LIKE 'STR/%' AND deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND date_ <= '$date2'";
    if ($store !== null) $sql .= " AND store_id = $store";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function countItemsBelowReorder(mysqli $conn, int $store = null) {
    if ($conn === false) exit();
    $store_filter = $store !== null ? " AND s.store_id = $store" : "";
    $sql = "SELECT COUNT(*) as reorder_count FROM (
                SELECT m.id FROM min_subs m
                LEFT JOIN stocks s ON s.item_id = m.id AND s.deleted_at IS NULL $store_filter
                WHERE m.deleted_at IS NULL
                GROUP BY m.id, m.reorder_level
                HAVING COUNT(s.id) > 0 AND
                       (COALESCE(SUM(CASE WHEN s.type='in' THEN s.base_quantity ELSE 0 END),0) -
                        COALESCE(SUM(CASE WHEN s.type='out' THEN s.base_quantity ELSE 0 END),0)) <= m.reorder_level
            ) as low_stock";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_assoc();
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectLowStockItems(mysqli $conn, int $store = null) {
    if ($conn === false) exit();
    $store_filter = $store !== null ? " AND s.store_id = $store" : "";
    $sql = "SELECT m.id, m.name as item_name, m.reorder_level,
                u.name as base_unit,
                COALESCE(SUM(CASE WHEN s.type='in'  THEN s.base_quantity ELSE 0 END),0) as total_in,
                COALESCE(SUM(CASE WHEN s.type='out' THEN s.base_quantity ELSE 0 END),0) as total_out,
                (COALESCE(SUM(CASE WHEN s.type='in'  THEN s.base_quantity ELSE 0 END),0) -
                 COALESCE(SUM(CASE WHEN s.type='out' THEN s.base_quantity ELSE 0 END),0)) as net_stock
            FROM min_subs m
            LEFT JOIN units u ON u.id = m.base_unit
            LEFT JOIN stocks s ON s.item_id = m.id AND s.deleted_at IS NULL $store_filter
            WHERE m.deleted_at IS NULL
            GROUP BY m.id, m.name, m.reorder_level, u.name
            HAVING COUNT(s.id) > 0 AND net_stock <= m.reorder_level
            ORDER BY net_stock ASC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectMostSoldItems(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null, int $limit = 10) {
    if ($conn === false) exit();
    $sql = "SELECT m.name as item_name,
                COALESCE(SUM(s.base_quantity), 0) as total_base_quantity,
                COALESCE(SUM(s.quantity * s.price), 0) as total_value
            FROM stocks s
            JOIN min_subs m ON m.id = s.item_id
            WHERE s.type = 'out' AND s.ref_no LIKE 'GDN/%' AND s.deleted_at IS NULL AND m.deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND s.date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND s.date_ <= '$date2'";
    if ($store !== null) $sql .= " AND s.store_id = $store";
    $sql .= " GROUP BY m.id, m.name ORDER BY total_value DESC LIMIT $limit";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectMostPurchasedItems(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null, int $limit = 10) {
    if ($conn === false) exit();
    $sql = "SELECT m.name as item_name,
                COALESCE(SUM(s.base_quantity * s.price), 0) as total_value,
                COUNT(DISTINCT s.batch) as total_grns
            FROM stocks s
            JOIN min_subs m ON m.id = s.item_id
            WHERE s.type = 'in' AND s.ref_no LIKE 'GRN/%' AND s.deleted_at IS NULL AND m.deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND s.date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND s.date_ <= '$date2'";
    if ($store !== null) $sql .= " AND s.store_id = $store";
    $sql .= " GROUP BY m.id, m.name ORDER BY total_value DESC LIMIT $limit";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectInOutTrend(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null) {
    if ($conn === false) exit();
    $sql = "SELECT DATE_FORMAT(date_, '%Y-%m') as period,
                COALESCE(SUM(CASE WHEN type='in'  AND ref_no LIKE 'GRN/%' THEN base_quantity * price ELSE 0 END),0) as value_in,
                COALESCE(SUM(CASE WHEN type='out' AND ref_no LIKE 'GDN/%' THEN base_quantity * price ELSE 0 END),0) as value_out
            FROM stocks
            WHERE deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND date_ <= '$date2'";
    if ($store !== null) $sql .= " AND store_id = $store";
    $sql .= " GROUP BY DATE_FORMAT(date_, '%Y-%m') ORDER BY period ASC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectTopSuppliers(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null, int $limit = 7) {
    if ($conn === false) exit();
    $sql = "SELECT COALESCE(sub.name, 'Unknown') as supplier_name,
                COALESCE(SUM(s.base_quantity * s.price), 0) as total_value
            FROM stocks s
            LEFT JOIN subsidiaries sub ON sub.id = s.supplier_id
            WHERE s.type = 'in' AND s.ref_no LIKE 'GRN/%' AND s.deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND s.date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND s.date_ <= '$date2'";
    if ($store !== null) $sql .= " AND s.store_id = $store";
    $sql .= " GROUP BY s.supplier_id, sub.name ORDER BY total_value DESC LIMIT $limit";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectInOutByStore(mysqli $conn, string $date1 = null, string $date2 = null) {
    if ($conn === false) exit();
    $sql = "SELECT ss.name as store_name,
                COALESCE(SUM(CASE WHEN s.type='in'  AND s.ref_no LIKE 'GRN/%' THEN s.base_quantity * s.price ELSE 0 END),0) as value_in,
                COALESCE(SUM(CASE WHEN s.type='out' AND s.ref_no LIKE 'GDN/%' THEN s.base_quantity * s.price ELSE 0 END),0) as value_out
            FROM stock_stores ss
            LEFT JOIN stocks s ON s.store_id = ss.id AND s.deleted_at IS NULL";
    if ($date1 !== null) $sql .= " AND s.date_ >= '$date1'";
    if ($date2 !== null) $sql .= " AND s.date_ <= '$date2'";
    $sql .= " WHERE ss.deleted_at IS NULL GROUP BY ss.id, ss.name ORDER BY value_in DESC";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}

function selectSlowMovingItems(mysqli $conn, string $date1 = null, string $date2 = null, int $store = null, int $limit = 10) {
    if ($conn === false) exit();
    $store_filter_in  = $store !== null ? " AND sin.store_id = $store"  : "";
    $store_filter_out = $store !== null ? " AND sout.store_id = $store" : "";
    $date_filter_out1 = $date1 !== null ? " AND sout.date_ >= '$date1'" : "";
    $date_filter_out2 = $date2 !== null ? " AND sout.date_ <= '$date2'" : "";
    $sql = "SELECT m.name as item_name,
                u.name as base_unit,
                COALESCE(SUM(CASE WHEN sin.type='in' THEN sin.base_quantity ELSE 0 END),0) as total_in,
                COALESCE(SUM(CASE WHEN sout.type='out' THEN sout.base_quantity ELSE 0 END),0) as total_out_alltime,
                COALESCE(SUM(CASE WHEN sout.type='out' $date_filter_out1 $date_filter_out2 THEN sout.base_quantity ELSE 0 END),0) as total_out_period,
                COALESCE(SUM(CASE WHEN sin.type='in' THEN sin.base_quantity ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN sout.type='out' THEN sout.base_quantity ELSE 0 END),0) as balance
            FROM min_subs m
            LEFT JOIN units u ON u.id = m.base_unit
            LEFT JOIN stocks sin  ON sin.item_id  = m.id AND sin.type  = 'in'  AND sin.deleted_at IS NULL $store_filter_in
            LEFT JOIN stocks sout ON sout.item_id = m.id AND sout.type = 'out' AND sout.deleted_at IS NULL $store_filter_out
            WHERE m.deleted_at IS NULL
            GROUP BY m.id, m.name, u.name
            HAVING total_in > 0 AND total_out_period = 0
            ORDER BY total_in DESC
            LIMIT $limit";
    if ($result = $conn->query($sql)) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    } else {
        return $conn->error;
    }
}