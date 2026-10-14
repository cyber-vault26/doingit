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

$itemId  = (int) $_GET['item_id'];
$date1   = isset($_GET['date1']) && $_GET['date1'] !== '' ? $conn->real_escape_string($_GET['date1']) : null;
$date2   = isset($_GET['date2']) && $_GET['date2'] !== '' ? $conn->real_escape_string($_GET['date2']) : null;
$storeId = isset($_GET['store']) && $_GET['store'] !== '' ? (int) $_GET['store'] : null;

$itemDetails = selectMinSubById($conn, $itemId);
if(!$itemDetails){
    echo "<script>alert('Item not found.'); window.close();</script>";
    exit();
}

$movements       = selectStockMovementByItemId($conn, $itemId, $date1, $date2, $storeId);
$baseUnitData    = selectUnitById($conn, (int)$itemDetails['base_unit']);
$baseUnitName    = $baseUnitData['name'] ?? '-';
$storeData       = $storeId ? selectStockStoreById($conn, $storeId) : null;
$storeName       = $storeData ? htmlspecialchars($storeData['name']) : 'All Stores';
$quantityBalance = 0;
$counter         = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Movement Report — <?= htmlspecialchars($itemDetails['name']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
        }
        .v-header { display: flex; align-items: center; justify-content: space-between; padding: 15px 0;  }
         .v-logo { width:135px; height:135px; overflow:hidden; margin-top:70px; }
         .v-logo img { width:150%; height:100%; object-fit:cover; transform:translateX(-25%); }
        .v-title { font-size: 36px; font-weight: bold; color: #00a0e8; text-align: right; flex: 1; padding-left: 20px; }
        .v-info-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 15px 0; padding-bottom: 15px; border-bottom: 2px solid #162171; }
        .v-party { flex: 1; }
        .v-meta { flex: 1; text-align: right; }
        .v-party-label { font-weight: bold; font-size: 12px; color: #00a0e8; margin-bottom: 3px; margin-top: 8px; }
        .v-party-label:first-child { margin-top: 0; }
        .v-party-value { font-size: 12px; color: #333; margin-bottom: 2px; }
        .v-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .v-table thead tr { background: #00a0e8; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .v-table th { padding: 9px 8px; text-align: left; font-size: 11px; font-weight: bold; border: 1px solid #162171; color: #fff; }
        .v-table td { padding: 7px 8px; border: 1px solid #ddd; font-size: 11px; }
        .v-table tbody tr:nth-child(even) { background: #eef6fd; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-in  { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; background: #d1e7dd; color: #0a3622; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-out { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; background: #f8d7da; color: #842029; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .balance-row td { background: #162171 !important; color: #fff; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .v-footer { margin-top: 30px; font-size: 10px; color: #fff; text-align: center; background: #00a0e8; padding: 12px 20px; line-height: 1.7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .v-footer strong { color: #162171; font-size: 12px; }
        .no-print { text-align: center; margin: 20px 0; }
        .no-print button { padding: 8px 20px; background: #00a0e8; color: #fff; border: none; border-radius: 4px; font-size: 13px; cursor: pointer; }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">&#128438; Print</button>
</div>

<div class="v-header">
    <div class="v-logo">
        <img src="../../../dist/img/logo.png" alt="Logo">
    </div>
    <div class="v-title">Stock Movement Report</div>
</div>

<div class="v-info-row">
    <div class="v-party">
        <div class="v-party-label">Item:</div>
        <div class="v-party-value"><strong><?= htmlspecialchars($itemDetails['name']) ?></strong></div>
        <div class="v-party-label">Base Unit:</div>
        <div class="v-party-value"><strong><?= htmlspecialchars($baseUnitName) ?></strong></div>
    </div>
    <div class="v-meta">
        <div class="v-party-label">Store:</div>
        <div class="v-party-value"><strong><?= $storeName ?></strong></div>
        <div class="v-party-label">Period:</div>
        <div class="v-party-value">
            <strong>
                <?= $date1 ? date('d M Y', strtotime($date1)) : 'Beginning' ?>
                &nbsp;to&nbsp;
                <?= $date2 ? date('d M Y', strtotime($date2)) : 'Today' ?>
            </strong>
        </div>
    </div>
</div>

<table class="v-table">
    <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th style="width:15%;">Date</th>
            <th style="width:20%;">Ref No</th>
            <th style="width:10%;" class="text-center">Type</th>
            <th style="width:15%;" class="text-right">Quantity</th>
            <th style="width:15%;">Unit</th>
            <th style="width:20%;" class="text-right">Base Qty (<?= htmlspecialchars($baseUnitName) ?>)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if($movements && is_array($movements) && count($movements) > 0){
            foreach($movements as $movement){
                $qty      = (float) $movement['quantity'];
                $baseQty  = (float) $movement['base_quantity'];
                $unitData = selectUnitById($conn, (int)$movement['unit']);
                $unitName = $unitData['name'] ?? '-';
                if($movement['type'] === 'in'){
                    $quantityBalance += $baseQty;
                } else {
                    $quantityBalance -= $baseQty;
                }
                ?>
                <tr>
                    <td><?= $counter ?></td>
                    <td><?= date('d-M-Y', strtotime($movement['date_'])) ?></td>
                    <td><?= htmlspecialchars($movement['ref_no']) ?></td>
                    <td class="text-center">
                        <span class="<?= $movement['type'] === 'in' ? 'badge-in' : 'badge-out' ?>">
                            <?= strtoupper($movement['type']) ?>
                        </span>
                    </td>
                    <td class="text-right"><?= number_format($qty, 2) ?></td>
                    <td><?= htmlspecialchars($unitName) ?></td>
                    <td class="text-right"><?= number_format($baseQty, 2) ?></td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo "<tr><td colspan='7' class='text-center' style='padding:20px;color:#999;'>No movements found for this period.</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="balance-row">
            <td colspan="6" class="text-right">CLOSING BALANCE (<?= htmlspecialchars($baseUnitName) ?>):</td>
            <td class="text-right"><?= number_format($quantityBalance, 2) ?></td>
        </tr>
        <tr style="background:#00a0e8; -webkit-print-color-adjust:exact; print-color-adjust:exact;">
            <th style="color:#fff;">#</th>
            <th style="color:#fff;">Date</th>
            <th style="color:#fff;">Ref No</th>
            <th style="color:#fff;" class="text-center">Type</th>
            <th style="color:#fff;" class="text-right">Quantity</th>
            <th style="color:#fff;">Unit</th>
            <th style="color:#fff;" class="text-right">Base Qty (<?= htmlspecialchars($baseUnitName) ?>)</th>
        </tr>
    </tfoot>
</table>

<div class="v-footer">
    <strong>TYCOON TRADING COMPANY LIMITED</strong><br>
    SIDO Arusha, Unga Ltd, Azimio Industrial Estate, P.O. Box 6113, Arusha, Tanzania.<br>
            Tel: +255 654 577 267 / +255 687 878 700
</div>

<script>
window.onload = function() {
    window.print();
    window.onafterprint = function() { window.close(); };
};
</script>

</body>
</html>