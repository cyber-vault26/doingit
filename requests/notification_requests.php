<?php
session_start();
if (!$_SESSION) {
    echo "<script>window.location.href='../login.php'</script>";
}
$cost_center = ($_SESSION['usertype'] === 'shop-keeper') ? (int)$_SESSION['cost_center'] : null;
require_once "../configs.php";
require_once "../functions/transaction_functions.php";
require_once "../functions/subsidiary_functions.php";
require_once "../functions/opening_balance_functions.php";
require_once "../functions/notification_functions.php";
require_once "../functions/stock_functions.php";
require_once "../functions/stock_date_request_functions.php";
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] === 'GET'){
   if(isset($_GET['voucher_notification_badge'])){
        $transactions = countVouchersByStatus($conn);
        $sales        = countSalesVouchersByStatus($conn);
        $purchases    = countPurchaseVouchersByStatus($conn);
        $bulky        = countBulkyVouchersByStatus($conn);
        $stock = countStockByStatus($conn, $cost_center);
        $data = [
            'transactions' => $transactions,
            'sales'        => $sales,
            'purchases'    => $purchases,
            'bulky'        => $bulky,
            'stock'        => $stock
        ];
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
     // ── badge count ───────────────────────────────────────────────────
if(isset($_GET['stock_date_request'])){
    $usertype = $_SESSION['usertype'] ?? '';
    $userid   = (int)($_SESSION['userid'] ?? 0);

    if(in_array($usertype, ['Admin', 'manager'])){
        $count = countAllStockDateRequestsByStatus($conn, 'pending');
    } else {
        $count = countUserStockDateRequestsByStatus($conn, $userid, 'pending');
    }

    header('Content-Type: application/json');
    echo json_encode(['stock_date_requests' => $count]);
    exit;
}

// ── full pending list (dropdown opened) ──────────────────────────
if(isset($_GET['stock_date_request_list'])){
    $usertype = $_SESSION['usertype'] ?? '';
    $userid   = (int)($_SESSION['userid'] ?? 0);

    if(in_array($usertype, ['Admin', 'manager'])){
        $requests = selectAllStockDateRequestsByStatus($conn, 'pending');
    } else {
        $requests = selectUserStockDateRequestsByStatus($conn, $userid, 'pending');
    }

    header('Content-Type: application/json');
    echo json_encode(['requests' => $requests]);
    exit;
}

// ── approve inline from dropdown ─────────────────────────────────
if(isset($_POST['approve_stock_request'])){
    $id          = (int)($_POST['id'] ?? 0);
    $approved_by = (int)($_SESSION['userid'] ?? 0);

    $sql = "UPDATE stock_date_requests 
            SET status='approved', approved_by=$approved_by, approved_at=NOW(), updated_at=NOW(), updated_by=$approved_by
            WHERE id=$id AND deleted_at IS NULL";

    header('Content-Type: application/json');
    if($conn->query($sql)){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}
}
?>