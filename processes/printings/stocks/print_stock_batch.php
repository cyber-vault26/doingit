<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>alert('Unauthorized'); window.close();</script>";
    exit();
}
require_once "../../../configs.php";
require_once "../../../functions/subsidiary_functions.php";
require_once "../../../functions/transaction_functions.php";
require_once "../../../functions/stock_functions.php";
require_once "../../../functions/user_functions.php";
require_once "../../../functions/utilities_functions.php";
require_once "../../../functions/min_sub_functions.php";
$conn = openConn();
$batch   = (int) $_GET['batch'];
$type    = $conn->real_escape_string($_GET['type']);
$rows    = selectStockBatchesByType($conn, $type, null, null, null, null, $batch);
if(!$rows || !is_array($rows) || count($rows) === 0){
    echo "<div class='alert alert-danger'>Record not found.</div>";
    exit();
}
$first            = $rows[0];
$ref_no           = $first['ref_no'];
$date             = date('d-M-Y', strtotime($first['date_']));
$supplier_id      = $first['supplier_id'];
$store_id         = $first['store_id'];
$to_store_id      = $first['to_store_id'] ?? null;
$po_id            = $first['purchase_order_id'];
$status           = $first['status'];
$created_by       = $first['created_by'];
$reviewed_by      = $first['reviewed_by'];
$approved_by      = $first['approved_by'];
$rejected_by      = $first['rejected_by'];
$created_at       = !empty($first['created_at'])  ? date('d-M-Y', strtotime($first['created_at']))  : '-';
$reviewed_at      = !empty($first['reviewed_at']) ? date('d-M-Y', strtotime($first['reviewed_at'])) : '-';
$approved_at      = !empty($first['approved_at']) ? date('d-M-Y', strtotime($first['approved_at'])) : '-';
$rejected_at      = !empty($first['rejected_at']) ? date('d-M-Y', strtotime($first['rejected_at'])) : '-';
$rejection_reason = $first['rejection_reason'] ?? '-';
$isGRN            = $type === 'in';
$isTransfer       = ($type === 'transfer-in' || $type === 'transfer-out');
$typeLabel        = $isGRN ? 'Goods Received Note' : ($isTransfer ? 'Stock Transfer Note' : 'Goods Delivery Note');
$voucherCode      = $isGRN ? 'GRN' : ($isTransfer ? 'STN' : 'GDN');
$supplier         = !$isTransfer ? selectSubsidiaryById($conn, $supplier_id) : null;
$store            = selectStockStoreById($conn, $store_id);
$toStore          = ($isTransfer && $to_store_id) ? selectStockStoreById($conn, $to_store_id) : null;
$createdUser      = $created_by  ? selectUserById($conn, $created_by)  : null;
$reviewedUser     = $reviewed_by ? selectUserById($conn, $reviewed_by) : null;
$approvedUser     = $approved_by ? selectUserById($conn, $approved_by) : null;
$rejectedUser     = $rejected_by ? selectUserById($conn, $rejected_by) : null;
$createdName      = $createdUser  ? htmlspecialchars($createdUser['names'])  : '-';
$reviewedName     = $reviewedUser ? htmlspecialchars($reviewedUser['names']) : '-';
$approvedName     = $approvedUser ? htmlspecialchars($approvedUser['names']) : '-';
$rejectedName     = $rejectedUser ? htmlspecialchars($rejectedUser['names']) : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $voucherCode ?> - <?= htmlspecialchars($ref_no) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 30px; background: #fff; }
        @media print { body { padding: 10px; } }
        .v-header { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; margin-bottom: 20px; }
        .v-logo { width: 200px; height: 150px; flex-shrink: 0; }
        .v-logo img { width: 100%; height: 100%; object-fit: contain; }
        .v-title { font-size: 48px; font-weight: bold; color: #d97c2b; text-align: right; letter-spacing: 1px; flex: 1; }
        .v-info-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 20px 0; padding: 15px 0; border-bottom: 1px solid #ddd; }
        .v-party { flex: 1; }
        .v-party-label { font-weight: bold; font-size: 13px; color: #000; margin-bottom: 4px; margin-top: 10px; }
        .v-party-label:first-child { margin-top: 0; }
        .v-party-value { font-size: 13px; color: #333; margin-bottom: 2px; }
        .v-meta { flex: 1; text-align: right; }
        .v-meta-item { margin-bottom: 8px; font-size: 13px; }
        .v-meta-label { font-weight: bold; color: #000; }
        .v-meta-value { color: #333; margin-left: 5px; }
        .v-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .v-table thead tr { background: #d97c2b; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .v-table th { padding: 11px 8px; text-align: left; font-size: 12px; font-weight: bold; border: 1px solid #c06a1a; color: #fff; }
        .v-table td { padding: 9px 8px; border: 1px solid #ddd; font-size: 12px; }
        .v-table tbody tr:nth-child(even) { background: #fdf3e7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-reviewed { background: #cfe2ff; color: #084298; }
        .totals-wrap { display: flex; justify-content: flex-end; margin-top: 10px; }
        .totals-table { width: 280px; border-collapse: collapse; font-size: 13px; }
        .totals-table td { padding: 6px 10px; border: 1px solid #ddd; }
        .totals-table tr:last-child { background: #d97c2b; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .totals-table tr:last-child td { color: #fff; font-weight: bold; }
        .sig-rows { margin-top: 40px; width: 100%; }
        .sig-row { display: flex; align-items: center; margin-bottom: 25px; gap: 20px; }
        .sig-row-label { width: 110px; font-weight: bold; font-size: 12px; flex-shrink: 0; }
        .sig-field { display: flex; align-items: center; gap: 6px; flex: 1; font-size: 12px; border-bottom: 1px solid #000; padding-bottom: 4px; }
        .sig-field span { font-weight: bold; white-space: nowrap; }
        .sig-field .sig-line { flex: 1; min-width: 60px; }
        .v-footer { margin-top: 40px; font-size: 11px; color: #fff; text-align: center; background: #d97c2b; padding: 15px 20px; line-height: 1.7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .v-footer strong { color: #fff; font-size: 13px; }
        .rejection-box { margin-top: 20px; padding: 12px; border: 1px solid #f5c2c7; background: #f8d7da; border-radius: 4px; font-size: 13px; color: #842029; }
    </style>
</head>
<body>
    <div class="v-header">
        <div class="v-logo">
            <img src="../../../dist/img/logo.png" alt="Logo">
        </div>
        <div class="v-title"><?= $typeLabel ?></div>
    </div>

    <div class="v-info-row">
        <div class="v-party">
            <?php if($isTransfer): ?>
                <div class="v-party-label">Transfer From:</div>
                <div class="v-party-value"><strong><?= htmlspecialchars($store['name'] ?? '-') ?></strong></div>
                <div class="v-party-label">Transfer To:</div>
                <div class="v-party-value"><strong><?= htmlspecialchars($toStore['name'] ?? '-') ?></strong></div>
            <?php else: ?>
                <div class="v-party-label"><?= $isGRN ? 'Supplier' : 'Issued To' ?>:</div>
                <div class="v-party-value"><strong><?= htmlspecialchars($supplier['name'] ?? '-') ?></strong></div>
                <?php if(!empty($supplier['phone'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($supplier['phone']) ?></div>
                <?php endif; ?>
                <?php if(!empty($supplier['email'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($supplier['email']) ?></div>
                <?php endif; ?>
                <div class="v-party-label"><?= $isGRN ? 'Received To' : 'Issued From' ?>:</div>
                <div class="v-party-value"><strong><?= htmlspecialchars($store['name'] ?? '-') ?></strong></div>
                <?php if($isGRN && $po_id): ?>
                <div class="v-party-label">PO Number:</div>
                <div class="v-party-value"><strong><?= htmlspecialchars($po_id) ?></strong></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="v-meta">
            <div class="v-meta-item">
                <span class="v-meta-label">Reference:</span>
                <span class="v-meta-value"><?= htmlspecialchars($ref_no) ?></span>
            </div>
            <div class="v-meta-item">
                <span class="v-meta-label">Date:</span>
                <span class="v-meta-value"><?= $date ?></span>
            </div>
            <div class="v-meta-item">
                <span class="v-meta-label">Status:</span>
                <span class="v-meta-value">
                    <span class="status-badge status-<?= $status ?>"><?= ucfirst($status) ?></span>
                </span>
            </div>
        </div>
    </div>

    <table class="v-table">
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:32%;">Item</th>
                <th style="width:12%;">Unit</th>
                <th style="width:10%;">Qty</th>
                <th style="width:14%;">Unit Price</th>
                <th style="width:18%;">Amount (inc. VAT)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_subtotal = 0;
            $grand_vat      = 0;
            $num = 0;
            foreach($rows as $row):
                $num++;
                $itemData  = !empty($row['item_id']) ? selectMinSubById($conn, (int)$row['item_id']) : null;
                $unitData  = !empty($row['unit'])    ? selectUnitById($conn, (int)$row['unit'])      : null;
                $qty       = floatval($row['quantity']      ?? 0);
                $price     = floatval($row['price']         ?? 0);
                $curr_rate = floatval($row['currency_rate'] ?? 1);
                if($curr_rate <= 0) $curr_rate = 1;
                $vat_rate  = floatval($row['tax_rate']      ?? 0);
                $equiv     = $qty * $price * $curr_rate;
                $vat_amt   = $equiv * ($vat_rate / 100);
                $total_amt = $equiv + $vat_amt;
                $grand_subtotal += $equiv;
                $grand_vat      += $vat_amt;
            ?>
            <tr>
                <td><?= $num ?></td>
                <td>
                    <?= htmlspecialchars($itemData['name'] ?? '-') ?>
                    <?php if($vat_rate > 0): ?>
                        <span class="status-badge status-reviewed" style="margin-left:5px;"><?= $vat_rate ?>% VAT</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($unitData['name'] ?? '-') ?></td>
                <td><?= number_format($qty, 2) ?></td>
                <td><?= number_format($price, 2) ?></td>
                <td><?= number_format($total_amt, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td><strong>Sub Total</strong></td>
                <td><?= number_format($grand_subtotal, 2) ?></td>
            </tr>
            <tr>
                <td><strong>Total VAT</strong></td>
                <td><?= number_format($grand_vat, 2) ?></td>
            </tr>
            <tr>
                <td>Grand Total</td>
                <td><?= number_format($grand_subtotal + $grand_vat, 2) ?></td>
            </tr>
        </table>
    </div>

    <div class="sig-rows">
        <div class="sig-row">
            <div class="sig-row-label">Prepared by:</div>
            <div class="sig-field"><span>Name:</span> <?= $createdName ?></div>
            <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
            <div class="sig-field"><span>Date:</span> <?= $created_at ?></div>
        </div>
        <?php if(in_array($status, ['reviewed', 'approved', 'rejected'])): ?>
        <div class="sig-row">
            <div class="sig-row-label">Reviewed by:</div>
            <div class="sig-field"><span>Name:</span> <?= $reviewedName ?></div>
            <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
            <div class="sig-field"><span>Date:</span> <?= $reviewed_at ?></div>
        </div>
        <?php endif; ?>
        <?php if($status === 'approved'): ?>
        <div class="sig-row">
            <div class="sig-row-label">Approved by:</div>
            <div class="sig-field"><span>Name:</span> <?= $approvedName ?></div>
            <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
            <div class="sig-field"><span>Date:</span> <?= $approved_at ?></div>
        </div>
        <?php endif; ?>
    </div>

    <?php if($status === 'rejected'): ?>
    <div class="rejection-box">
        <strong>Rejected by:</strong> <?= $rejectedName ?><br>
        <strong>Rejected at:</strong> <?= $rejected_at ?><br>
        <strong>Reason:</strong> <?= htmlspecialchars($rejection_reason) ?>
    </div>
    <?php endif; ?>

    <div class="v-footer">
        <strong>MANGOWO TRADING AND SUPPLIES</strong><br>
        Kariakoo, Narung'ombe Na Sikukuu, P.O. Box xxx, Dar es Salaam, Tanzania.<br>
        Tel: +255 785 182 121 / +255 759 266 699 / +255 655 606 676
    </div>

    <script>
    window.onload = function() {
        window.print();
        window.onafterprint = function() { window.close(); };
    };
    </script>
</body>
</html>