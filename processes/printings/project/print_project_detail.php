<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>alert('Unauthorized'); window.close();</script>";
    exit();
}
require_once "../../../configs.php";
require_once "../../../functions/project_functions.php";
require_once "../../../functions/subsidiary_functions.php";
require_once "../../../functions/transaction_functions.php";
$conn = openConn();
$id = (int) $_GET['id'];
$projectDetail = listProjectById($conn, $id);
if(!$projectDetail){ echo "Project not found."; exit(); }
$budgets = selectProjectBudgetItemsByProjectId($conn, $id);
$totalBudget = 0;
if($budgets && is_array($budgets)){ foreach($budgets as $b){ $totalBudget += $b['amount']; } }
$transactions = selectTransactionByProjectId($conn, $id);
$totalCosts = 0;
if($transactions && is_array($transactions)){ foreach($transactions as $t){ $totalCosts += $t['amount']; } }
$progressItems = selectProjectProgressByProjectId($conn, $id);
$remainingBudget = $totalBudget - $totalCosts;
$budgetUsedPct = $totalBudget > 0 ? min(($totalCosts / $totalBudget) * 100, 100) : 0;
$projectDate = !empty($projectDetail['date_']) ? new DateTime($projectDetail['date_']) : null;
$today = new DateTime();
$duration = (int)($projectDetail['duration'] ?? 0);
$daysElapsed = $projectDate ? (int)$projectDate->diff($today)->days : 0;
$daysRemaining = $duration > 0 ? $duration - $daysElapsed : 0;
$timeUsedPct = $duration > 0 ? min(($daysElapsed / $duration) * 100, 100) : 0;
$isOverdue = $duration > 0 && $daysElapsed > $duration;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Project Report - <?= htmlspecialchars($projectDetail['name']) ?></title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; padding: 30px; line-height: 1.4; color: #333; }
.v-header { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; margin-bottom: 20px; }
.v-logo { width: 200px; height: 150px; flex-shrink: 0; }
.v-logo img { width: 100%; height: 100%; object-fit: contain; }
.v-title { font-size: 40px; font-weight: bold; color: #d97c2b; text-align: right; flex: 1; }
.v-info-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 20px 0; padding: 15px 0; border-bottom: 1px solid #ddd; }
.v-party { flex: 1; }
.v-party-label { font-weight: bold; font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 3px; margin-top: 8px; }
.v-party-label:first-child { margin-top: 0; }
.v-party-value { font-size: 13px; color: #000; font-weight: bold; margin-bottom: 2px; }
.v-meta { flex: 1; text-align: right; }
.v-meta-item { margin-bottom: 8px; font-size: 13px; }
.v-meta-label { font-weight: bold; color: #000; }
.v-meta-value { color: #333; margin-left: 5px; }
.section-title { font-size: 13px; font-weight: bold; color: #d97c2b; border-bottom: 2px solid #d97c2b; padding-bottom: 4px; margin: 20px 0 10px 0; text-transform: uppercase; }
.info-grid { display: flex; flex-wrap: wrap; margin-bottom: 10px; }
.info-item { width: 33.33%; margin-bottom: 8px; font-size: 12px; }
.info-item strong { color: #000; }
.v-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.v-table thead tr { background: #d97c2b; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.v-table th { padding: 9px 8px; text-align: left; font-size: 11px; font-weight: bold; border: 1px solid #c06a1a; color: #fff; }
.v-table td { padding: 8px; border: 1px solid #ddd; font-size: 11px; }
.v-table tbody tr:nth-child(even) { background: #fdf3e7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.text-right { text-align: right; }
.progress-bar-wrap { width: 100%; background: #eee; border-radius: 4px; height: 10px; margin: 4px 0; }
.progress-bar-fill { height: 10px; border-radius: 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.progress-orange { background: #d97c2b; }
.progress-green { background: #28a745; }
.progress-red { background: #dc3545; }
.progress-warning { background: #ffc107; }
.summary-box { display: flex; justify-content: flex-end; margin-bottom: 5px; }
.summary-label { width: 200px; font-weight: bold; text-align: right; padding-right: 20px; font-size: 12px; }
.summary-value { width: 150px; text-align: right; font-size: 12px; }
.sig-rows { margin-top: 40px; width: 100%; }
.sig-row { display: flex; align-items: center; margin-bottom: 30px; gap: 15px; }
.sig-row-label { font-size: 12px; font-weight: bold; color: #000; width: 110px; flex-shrink: 0; }
.sig-field { display: flex; align-items: center; gap: 6px; flex: 1; font-size: 12px; border-bottom: 1px solid #000; padding-bottom: 4px; }
.sig-field span { font-size: 11px; font-weight: bold; white-space: nowrap; }
.sig-line { flex: 1; min-width: 60px; }
.v-footer { margin-top: 40px; font-size: 11px; color: #fff; text-align: center; background: #d97c2b; padding: 15px 20px; line-height: 1.7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.v-footer strong { color: #fff; font-size: 13px; }
@media print { body { padding: 15px; } }
</style>
</head>
<body>
<div class="v-header">
    <div class="v-logo"><img src="../../../dist/img/logo.png" alt="Logo"></div>
    <div class="v-title">Project Report</div>
</div>
<div class="v-info-row">
    <div class="v-party">
        <div class="v-party-label">Project Name</div>
        <div class="v-party-value"><?= htmlspecialchars($projectDetail['name']) ?></div>
        <div class="v-party-label">Client</div>
        <div class="v-party-value"><?= htmlspecialchars($projectDetail['customer_name'] ?? '-') ?></div>
        <div class="v-party-label">Project Manager</div>
        <div class="v-party-value"><?= htmlspecialchars($projectDetail['project_manager'] ?? '-') ?></div>
    </div>
    <div class="v-meta">
        <div class="v-meta-item"><span class="v-meta-label">Location:</span><span class="v-meta-value"><?= htmlspecialchars($projectDetail['location']) ?></span></div>
        <div class="v-meta-item"><span class="v-meta-label">Start Date:</span><span class="v-meta-value"><?= $projectDate ? $projectDate->format('d-M-Y') : '-' ?></span></div>
        <div class="v-meta-item"><span class="v-meta-label">Duration:</span><span class="v-meta-value"><?= $duration > 0 ? $duration . ' days' : '-' ?></span></div>
        <div class="v-meta-item"><span class="v-meta-label">Status:</span><span class="v-meta-value"><?= ucfirst(str_replace('_', ' ', $projectDetail['status'])) ?></span></div>
        <div class="v-meta-item"><span class="v-meta-label">Contract Price:</span><span class="v-meta-value"><?= number_format($projectDetail['price'], 2) ?></span></div>
    </div>
</div>
<div class="section-title">Timeline Tracking</div>
<div class="info-grid">
    <div class="info-item"><strong>Days Elapsed:</strong> <?= $daysElapsed ?> days</div>
    <div class="info-item"><strong>Days Remaining:</strong> <?= $isOverdue ? 'Overdue by ' . abs($daysRemaining) . ' days' : $daysRemaining . ' days' ?></div>
    <div class="info-item"><strong>Time Used:</strong> <?= number_format($timeUsedPct, 1) ?>%</div>
</div>
<div class="progress-bar-wrap">
    <div class="progress-bar-fill <?= $isOverdue ? 'progress-red' : ($timeUsedPct > 80 ? 'progress-warning' : 'progress-orange') ?>" style="width:<?= $timeUsedPct ?>%;"></div>
</div>
<div class="section-title">Budget Tracking</div>
<div class="info-grid">
    <div class="info-item"><strong>Total Budget:</strong> <?= number_format($totalBudget, 2) ?></div>
    <div class="info-item"><strong>Total Costs:</strong> <?= number_format($totalCosts, 2) ?></div>
    <div class="info-item"><strong>Remaining:</strong> <?= number_format($remainingBudget, 2) ?></div>
</div>
<div class="progress-bar-wrap">
    <div class="progress-bar-fill <?= $budgetUsedPct >= 100 ? 'progress-red' : ($budgetUsedPct > 80 ? 'progress-warning' : 'progress-green') ?>" style="width:<?= $budgetUsedPct ?>%;"></div>
</div>
<p style="font-size:11px; text-align:right; margin-top:3px;">Budget Used: <?= number_format($budgetUsedPct, 1) ?>%</p>
<div class="section-title">Budget Items</div>
<table class="v-table">
    <thead>
        <tr><th width="5%">#</th><th width="70%">Item Name</th><th width="25%" class="text-right">Amount</th></tr>
    </thead>
    <tbody>
        <?php if($budgets && is_array($budgets) && count($budgets) > 0):
            $c = 1; foreach($budgets as $b): ?>
        <tr><td><?= $c++ ?></td><td><?= htmlspecialchars($b['cost_item']) ?></td><td class="text-right"><?= number_format($b['amount'], 2) ?></td></tr>
        <?php endforeach; else: ?>
        <tr><td colspan="3" style="text-align:center; color:#999;">No budget items</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr style="background:#fdf3e7;"><td colspan="2" style="text-align:right; font-weight:bold; padding:8px;">Total Budget:</td><td class="text-right" style="font-weight:bold; padding:8px;"><?= number_format($totalBudget, 2) ?></td></tr>
    </tfoot>
</table>
<div class="section-title">Project Costs</div>
<table class="v-table">
    <thead>
        <tr><th width="4%">#</th><th width="14%">Date</th><th width="22%">DR Account</th><th>Description</th><th width="15%" class="text-right">Amount</th></tr>
    </thead>
    <tbody>
        <?php if($transactions && is_array($transactions) && count($transactions) > 0):
            $c = 1; foreach($transactions as $t): ?>
        <tr>
            <td><?= $c++ ?></td>
            <td><?= date('d-M-Y', strtotime($t['date_'])) ?></td>
            <td><?= htmlspecialchars($t['debit_acc']) ?></td>
            <td><?= htmlspecialchars($t['description']) ?></td>
            <td class="text-right"><?= number_format($t['amount'], 2) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="5" style="text-align:center; color:#999;">No costs recorded</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr style="background:#fdf3e7;"><td colspan="4" style="text-align:right; font-weight:bold; padding:8px;">Total Costs:</td><td class="text-right" style="font-weight:bold; padding:8px;"><?= number_format($totalCosts, 2) ?></td></tr>
    </tfoot>
</table>
<div class="section-title">Project Progress</div>
<table class="v-table">
    <thead>
        <tr><th width="4%">#</th><th width="13%">From</th><th width="13%">To</th><th>Work Done</th><th width="12%">Added By</th></tr>
    </thead>
    <tbody>
        <?php if(!empty($progressItems) && is_array($progressItems)):
            $c = 1; foreach($progressItems as $p): ?>
        <tr>
            <td><?= $c++ ?></td>
            <td><?= date('d-M-Y', strtotime($p['date1'])) ?></td>
            <td><?= date('d-M-Y', strtotime($p['date2'])) ?></td>
            <td><?= htmlspecialchars($p['work_done']) ?></td>
            <td><?= htmlspecialchars($p['created_by_name'] ?? '-') ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="5" style="text-align:center; color:#999;">No progress records</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<div class="sig-rows">
    <div class="sig-row">
        <div class="sig-row-label">Prepared by:</div>
        <div class="sig-field"><span>Name:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Date:</span><div class="sig-line"></div></div>
    </div>
    <div class="sig-row">
        <div class="sig-row-label">Reviewed by:</div>
        <div class="sig-field"><span>Name:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Date:</span><div class="sig-line"></div></div>
    </div>
    <div class="sig-row">
        <div class="sig-row-label">Approved by:</div>
        <div class="sig-field"><span>Name:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Date:</span><div class="sig-line"></div></div>
    </div>
</div>
<div class="v-footer">
    <strong>JADON LIMITED</strong><br>
    2nd Floor, Tevi Commercial Park, Bagamoyo Rd, Plot No.576, Mbezi Beach, P.O. Box 60163, Dar es Salaam, Tanzania.<br>
    Email: info@jadon.co.tz | Tel: +255621009936 / +255 737 829 077 | TIN: 138-847-209 | VRN: 40-034505-T
</div>
<script>
window.onload = function() {
    window.print();
    window.onafterprint = function() { window.close(); };
};
</script>
</body>
</html>