<?php
$purchaseRequestId = (int) $_GET['id'];
$purchaseRequest = selectPurchaseRequestById($conn, $purchaseRequestId);
$items = selectPurchaseRequestItemsByPurchaseRequestId($conn, $purchaseRequestId);
$isTSH = strtoupper($purchaseRequest['currency_name']) === 'TSH';
$rate = floatval($purchaseRequest['currency_rate']) ?: 1;
$requestHeading = !empty($purchaseRequest['request_heading']) ? htmlspecialchars($purchaseRequest['request_heading']) : '';
$status = $purchaseRequest['status'];
$createdByName  = !empty($purchaseRequest['created_by_name'])  ? htmlspecialchars($purchaseRequest['created_by_name'])  : '-';
$checkedByName  = !empty($purchaseRequest['checked_by_name'])  ? htmlspecialchars($purchaseRequest['checked_by_name'])  : '-';
$reviewedByName = !empty($purchaseRequest['reviewed_by_name']) ? htmlspecialchars($purchaseRequest['reviewed_by_name']) : '-';
$approvedByName = !empty($purchaseRequest['approved_by_name']) ? htmlspecialchars($purchaseRequest['approved_by_name']) : '-';
$rejectedByName = !empty($purchaseRequest['rejected_by_name']) ? htmlspecialchars($purchaseRequest['rejected_by_name']) : '-';
$createdAt  = !empty($purchaseRequest['created_at'])  ? date('d-M-Y', strtotime($purchaseRequest['created_at']))  : '-';
$checkedAt  = !empty($purchaseRequest['checked_at'])  ? date('d-M-Y', strtotime($purchaseRequest['checked_at']))  : '-';
$reviewedAt = !empty($purchaseRequest['reviewed_at']) ? date('d-M-Y', strtotime($purchaseRequest['reviewed_at'])) : '-';
$approvedAt = !empty($purchaseRequest['approved_at']) ? date('d-M-Y', strtotime($purchaseRequest['approved_at'])) : '-';
$rejectedAt = !empty($purchaseRequest['rejected_at']) ? date('d-M-Y', strtotime($purchaseRequest['rejected_at'])) : '-';
$grouped = [];
if($items && is_array($items)){
    foreach($items as $item){
        $pid = intval($item['project_id']);
        $grouped[$pid][] = $item;
    }
}
?>
<div class="card card-warning">
    <div class="data-container">
        <div class="card-header">
            <h4 class="text-center">Purchase Request</h4>
        </div>
        <div class="card-body">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
.invoice-header { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; margin-bottom: 20px; }
.invoice-header-logo { width: 200px; height: 150px; flex-shrink: 0; }
.invoice-header-logo img { width: 100%; height: 100%; object-fit: contain; }
.invoice-main-title { font-size: 48px; font-weight: bold; color: #d97c2b; text-align: right; letter-spacing: 1px; flex: 1; }
.info-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 20px 0; padding: 15px 0; border-bottom: 1px solid #ddd; }
.bill-to-section { flex: 1; }
.bill-to-label { font-weight: bold; font-size: 13px; color: #000; margin-bottom: 5px; }
.bill-to-value { font-size: 13px; color: #333; font-weight: bold; }
.meta-vertical-list { flex: 1; text-align: right; }
.meta-item { margin-bottom: 8px; font-size: 13px; }
.meta-label { font-weight: bold; color: #000; }
.meta-value { color: #333; margin-left: 5px; }
.invoice-description { margin: 20px 0; font-size: 13px; line-height: 1.5; color: #333; }
.invoice-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
.invoice-table th { background: #d97c2b; color: #fff; padding: 12px 8px; text-align: left; font-size: 13px; font-weight: bold; border: 1px solid #ddd; }
.invoice-table td { padding: 10px 8px; border: 1px solid #ddd; font-size: 12px; }
.invoice-table tbody tr:nth-child(even) { background: #fafafa; }
.project-row td { background: #fdf3e7 !important; font-weight: bold; color: #d97c2b; font-size: 12px;   solid #d97c2b; }
.text-right { text-align: right; }
.summary-section { margin-top: 20px; width: 100%; }
.summary-row { display: flex; justify-content: flex-end; margin-bottom: 8px; }
.summary-label { width: 200px; font-weight: bold; text-align: right; padding-right: 20px; color: #000; font-size: 13px; }
.summary-value { width: 150px; text-align: right; font-size: 13px; }
.grand-total { font-size: 16px; border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; }
.grand-total .summary-label, .grand-total .summary-value { font-weight: bold; }
.sig-rows { margin-top: 40px; width: 100%; }
.sig-row { display: flex; align-items: center; margin-bottom: 25px; gap: 20px; }
.sig-row-label { width: 110px; font-weight: bold; font-size: 12px; flex-shrink: 0; }
.sig-field { display: flex; align-items: center; gap: 6px; flex: 1; font-size: 12px; border-bottom: 1px solid #000; padding-bottom: 4px; }
.sig-field span { font-weight: bold; white-space: nowrap; }
.sig-line { flex: 1; min-width: 60px; }
.invoice-footer { background: #d97c2b; color: #fff; padding: 15px 20px; text-align: center; font-size: 11px; line-height: 1.7; margin-top: 40px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.invoice-footer strong { color: #fff; font-size: 13px; }
@media print { .main-footer, .btn, .card-footer, .card-header { display: none !important; } }
</style>
<div class="invoice-header">
    <div class="invoice-header-logo"><img src="../../dist/img/logo.png" alt="Company Logo"></div>
    <div class="invoice-main-title">Purchase Request</div>
</div>
<div class="info-row">
    <div class="bill-to-section">
        <div class="bill-to-label">Ref No:</div>
        <div class="bill-to-value"><?= htmlspecialchars($purchaseRequest['ref_no']) ?></div>
    </div>
    <div class="meta-vertical-list">
        <div class="meta-item">
            <span class="meta-label">Date:</span>
            <span class="meta-value"><?= date('d-M-Y', strtotime($purchaseRequest['date_'])) ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Status:</span>
            <span class="meta-value"><?= ucfirst($status) ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Currency:</span>
            <span class="meta-value"><?= htmlspecialchars($purchaseRequest['currency_name']) ?></span>
        </div>
        <?php if(!$isTSH): ?>
        <div class="meta-item">
            <span class="meta-label">Exchange Rate:</span>
            <span class="meta-value">1 <?= htmlspecialchars($purchaseRequest['currency_name']) ?> = <?= number_format($rate, 2) ?> TSH</span>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php if(!empty($requestHeading)): ?>
<div class="invoice-description"><?= $requestHeading ?></div>
<?php endif; ?>
<table class="invoice-table">
    <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th style="width:35%;">Description</th>
            <th style="width:10%;">Qty</th>
            <th style="width:10%;">Unit</th>
            <th style="width:15%;">Unit Price (<?= htmlspecialchars($purchaseRequest['currency_name']) ?>)</th>
            <th style="width:15%;" class="text-right">Amount (<?= htmlspecialchars($purchaseRequest['currency_name']) ?>)</th>
            <?php if(!$isTSH): ?>
            <th style="width:10%;" class="text-right">Equiv (TSH)</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($grouped)): ?>
        <?php foreach($grouped as $pid => $pitems):
            if($pid == 0){
                $projectName = 'General';
            } else {
                $project = listProjectById($conn, $pid);
                $projectName = is_array($project) ? htmlspecialchars($project['name']) : 'General';
            }
            $pCounter = 1;
        ?>
        <tr class="project-row">
            <td colspan="<?= $isTSH ? 6 : 7 ?>"><?= $projectName ?></td>
        </tr>
        <?php foreach($pitems as $item):
            $itemPrice = $isTSH ? floatval($item['price']) : floatval($item['price']) / $rate;
            $itemAmount = $isTSH ? floatval($item['amount']) : floatval($item['amount']) / $rate;
        ?>
        <tr>
            <td><?= $pCounter++ ?></td>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= number_format(floatval($item['quantity']), 2) ?></td>
            <td><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
            <td><?= number_format($itemPrice, 2) ?></td>
            <td class="text-right"><?= number_format($itemAmount, 2) ?></td>
            <?php if(!$isTSH): ?>
            <td class="text-right"><?= number_format(floatval($item['amount_eqv']), 2) ?></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="<?= $isTSH ? 6 : 7 ?>" style="text-align:center; color:#999; padding:30px;">NO ITEMS FOUND.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
$subtotal = floatval($purchaseRequest['subtotal']);
$grandTotal = floatval($purchaseRequest['total_amount']);
$displaySubtotal = $isTSH ? $subtotal : $subtotal / $rate;
$displayGrand = $isTSH ? $grandTotal : $grandTotal / $rate;
?>
<div class="summary-section">
    <div class="summary-row">
        <div class="summary-label">Sub-total:</div>
        <div class="summary-value"><?= number_format($displaySubtotal, 2) ?> <?= htmlspecialchars($purchaseRequest['currency_name']) ?></div>
    </div>
    <div class="summary-row grand-total">
        <div class="summary-label">Total:</div>
        <div class="summary-value"><?= number_format($displayGrand, 2) ?> <?= htmlspecialchars($purchaseRequest['currency_name']) ?></div>
    </div>
    <?php if(!$isTSH): ?>
    <div class="summary-row">
        <div class="summary-label">Total (TSH):</div>
        <div class="summary-value"><?= number_format($grandTotal, 2) ?> TSH</div>
    </div>
    <?php endif; ?>
</div>
<div class="sig-rows">
    <div class="sig-row">
        <div class="sig-row-label">Prepared by:</div>
        <div class="sig-field"><span>Name:</span> <?= $createdByName ?></div>
        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Date:</span> <?= $createdAt ?></div>
    </div>
    <?php if(in_array($status, ['checked', 'reviewed', 'approved', 'rejected'])): ?>
    <div class="sig-row">
        <div class="sig-row-label">Checked by:</div>
        <div class="sig-field"><span>Name:</span> <?= $checkedByName ?></div>
        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Date:</span> <?= $checkedAt ?></div>
    </div>
    <?php endif; ?>
    <?php if(in_array($status, ['reviewed', 'approved', 'rejected'])): ?>
    <div class="sig-row">
        <div class="sig-row-label">Reviewed by:</div>
        <div class="sig-field"><span>Name:</span> <?= $reviewedByName ?></div>
        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Date:</span> <?= $reviewedAt ?></div>
    </div>
    <?php endif; ?>
    <?php if($status === 'approved'): ?>
    <div class="sig-row">
        <div class="sig-row-label">Approved by:</div>
        <div class="sig-field"><span>Name:</span> <?= $approvedByName ?></div>
        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
        <div class="sig-field"><span>Date:</span> <?= $approvedAt ?></div>
    </div>
    <?php endif; ?>
</div>
<?php if($status === 'rejected'): ?>
<div class="alert alert-danger mt-3">
    <strong>Rejected by:</strong> <?= $rejectedByName ?><br>
    <strong>Rejected at:</strong> <?= $rejectedAt ?><br>
    <strong>Reason:</strong> <?= htmlspecialchars($purchaseRequest['rejection_reason'] ?? '-') ?>
</div>
<?php endif; ?>
<div class="invoice-footer">
    <strong>JADON LIMITED</strong><br>
    2nd Floor, Tevi Commercial Park, Bagamoyo Rd, Plot No.576, Mbezi Beach, P.O. Box 60163, Dar es Salaam, Tanzania.<br>
    Email: info@jadon.co.tz | Tel: +255621009936 / +255 737 829 077 | TIN: 138-847-209 | VRN: 40-034505-T
</div>
        </div>
    </div>
    <div class="card-footer">
        <?php if($status === 'pending'): ?>
            <button class="btn btn-info btn-sm mr-1" onclick="prMarkAsChecked()">Mark as Checked</button>
        <?php endif; ?>
        <?php if($status === 'checked'): ?>
            <button class="btn btn-primary btn-sm mr-1" onclick="prMarkAsReviewed()">Mark as Reviewed</button>
        <?php endif; ?>
        <?php if($status === 'reviewed'): ?>
            <button class="btn btn-success btn-sm mr-1" onclick="prMarkAsApproved()">Approve</button>
        <?php endif; ?>
        <?php if(in_array($status, ['pending', 'checked', 'reviewed'])): ?>
            <button class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#rejectModal">Reject</button>
        <?php endif; ?>
        <?php if(!in_array($status, ['approved'])): ?>
            <a class="btn btn-warning btn-sm mr-1" href="./?page=edit_purchase_request&id=<?= $purchaseRequestId ?>">Amend</a>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm mr-1" onclick="window.print()">Print</button>
    </div>
</div>
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Purchase Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Reason for Rejection <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" class="form-control" rows="4" placeholder="Enter reason for rejection..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="prReject()">Reject</button>
            </div>
        </div>
    </div>
</div>
<script>
function prMarkAsChecked() {
    if(!confirm('Mark this purchase request as checked?')) return;
    $.post('./controllers/purchase_request_controller.php', { purchase_request_id: <?= $purchaseRequestId ?>, pr_mark_checked: 1 }, function(r){
        if(r.trim() === 'SUCCESS') location.reload();
        else alert('Error: ' + r);
    });
}
function prMarkAsReviewed() {
    if(!confirm('Mark this purchase request as reviewed?')) return;
    $.post('./controllers/purchase_request_controller.php', { purchase_request_id: <?= $purchaseRequestId ?>, pr_mark_reviewed: 1 }, function(r){
        if(r.trim() === 'SUCCESS') location.reload();
        else alert('Error: ' + r);
    });
}
function prMarkAsApproved() {
    if(!confirm('Approve this purchase request?')) return;
    $.post('./controllers/purchase_request_controller.php', { purchase_request_id: <?= $purchaseRequestId ?>, pr_mark_approved: 1 }, function(r){
        if(r.trim() === 'SUCCESS') location.reload();
        else alert('Error: ' + r);
    });
}
function prReject() {
    const reason = $('#rejection_reason').val().trim();
    if(!reason){ alert('Please enter a reason for rejection.'); return; }
    if(!confirm('Are you sure you want to reject this purchase request?')) return;
    $.post('./controllers/purchase_request_controller.php', { purchase_request_id: <?= $purchaseRequestId ?>, pr_reject: 1, rejection_reason: reason }, function(r){
        if(r.trim() === 'SUCCESS'){ $('#rejectModal').modal('hide'); location.reload(); }
        else alert('Error: ' + r);
    });
}
</script>