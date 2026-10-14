<?php
$bvId    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$bvId) { echo "Invalid voucher ID."; exit; }
$voucher = selectBulkyVoucherById($conn, $bvId);
if(!$voucher) { echo "Voucher not found."; exit; }
$items   = selectBulkyVoucherItems($conn, $bvId);
$status  = $voucher['status'];
$createdByName  = !empty($voucher['created_by_name'])  ? htmlspecialchars($voucher['created_by_name'])  : '-';
$reviewedByName = !empty($voucher['reviewed_by_name']) ? htmlspecialchars($voucher['reviewed_by_name']) : '-';
$approvedByName = !empty($voucher['approved_by_name']) ? htmlspecialchars($voucher['approved_by_name']) : '-';
$createdAt  = !empty($voucher['created_at'])  ? date('d-M-Y', strtotime($voucher['created_at']))  : '-';
$reviewedAt = !empty($voucher['reviewed_at']) ? date('d-M-Y', strtotime($voucher['reviewed_at'])) : '-';
$approvedAt = !empty($voucher['approved_at']) ? date('d-M-Y', strtotime($voucher['approved_at'])) : '-';
$isTSH   = strtoupper($voucher['currency_name'] ?? 'TSH') === 'TSH';
$crItems = $items ? $items : [];
$crAccId = !empty($crItems[0]['cr_account']) ? $crItems[0]['cr_account'] : 0;
$crSub   = $crAccId ? selectSubsidiaryById($conn, $crAccId) : null;
$crName  = $crSub ? htmlspecialchars($crSub['name']) : '-';
$uploadPath = '../../uploads/vouchers/';
$grouped = [];
if($items && is_array($items)){
    foreach($items as $item){
        $pid = intval($item['project_id']);
        $grouped[$pid][] = $item;
    }
}
?>
<style>
@media print { .main-footer, .btn, .card-footer-actions, .card-header { display: none !important; } }
.pv-wrap { font-family: Arial, sans-serif; padding: 20px; line-height: 1.4; }
.pv-wrap .pv-header { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; margin-bottom: 20px; }
.pv-wrap .pv-logo { width:135px; height:135px; overflow:hidden; margin-top:70px; }
.pv-wrap .pv-logo  img { width:150%; height:100%; object-fit:cover; transform:translateX(-25%); }
.pv-wrap .pv-title { font-size: 50px; font-weight: bold; color: #00a0e8; text-align: right; flex: 1; padding-top: 40px; }
.pv-wrap .info-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 20px 0; padding: 15px 0; border-bottom: 2px solid #162171; }
.pv-wrap .pv-from { flex: 1; }
.pv-wrap .pv-from-label { font-weight: bold; font-size: 12px; color: #00a0e8; margin-bottom: 3px; text-transform: uppercase; }
.pv-wrap .pv-from-value { font-size: 14px; color: #000; font-weight: bold; margin-bottom: 2px; }
.pv-wrap .pv-meta { flex: 1; text-align: right; }
.pv-wrap .pv-meta-item { margin-bottom: 6px; font-size: 13px; }
.pv-wrap .pv-meta-label { font-weight: bold; color: #00a0e8; }
.pv-wrap .pv-meta-value { color: #333; margin-left: 5px; }
.pv-wrap .pv-heading { margin: 0 0 10px 0; font-size: 13px; color: #333; font-style: italic; }
.pv-wrap .pv-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.pv-wrap .pv-table thead tr { background: #00a0e8; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.pv-wrap .pv-table th { color: #fff; padding: 10px 8px; text-align: left; font-size: 12px; font-weight: bold; border: 1px solid #162171; }
.pv-wrap .pv-table td { padding: 9px 8px; border: 1px solid #ddd; font-size: 12px; }
.pv-wrap .pv-table tbody tr:nth-child(even) { background: #eef6fd; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.pv-wrap .pv-table .project-row { background: #eef6fd !important; font-weight: bold; color: #00a0e8; font-size: 12px; border-left: 3px solid #00a0e8; }
.pv-wrap .text-right { text-align: right; }
.pv-wrap .vat-badge { display: inline-block; background: #00a0e8; color: #fff; font-size: 9px; font-weight: bold; padding: 1px 4px; border-radius: 3px; margin-left: 4px; vertical-align: middle; }
.pv-wrap .summary-section { margin-top: 10px; width: 100%; }
.pv-wrap .summary-row { display: flex; justify-content: flex-end; margin-bottom: 6px; }
.pv-wrap .summary-label { width: 200px; font-weight: bold; text-align: right; padding-right: 20px; color: #00a0e8; font-size: 13px; }
.pv-wrap .summary-value { width: 150px; text-align: right; font-size: 13px; }
.pv-wrap .grand-total { font-size: 15px; border-top: 2px solid #162171; padding-top: 8px; margin-top: 8px; }
.pv-wrap .grand-total .summary-label, .pv-wrap .grand-total .summary-value { font-weight: bold; }
.pv-wrap .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
.pv-wrap .status-pending  { background: #fff3cd; color: #856404; }
.pv-wrap .status-active   { background: #d1e7dd; color: #0a3622; }
.pv-wrap .status-draft    { background: #e2e3e5; color: #41464b; }
.pv-wrap .status-rejected { background: #f8d7da; color: #842029; }
.pv-wrap .sig-rows { margin-top: 40px; width: 100%; }
.pv-wrap .sig-row { display: flex; align-items: center; margin-bottom: 25px; gap: 20px; }
.pv-wrap .sig-row-label { width: 110px; font-weight: bold; font-size: 12px; flex-shrink: 0; color: #00a0e8; }
.pv-wrap .sig-field { display: flex; align-items: center; gap: 6px; flex: 1; font-size: 12px; border-bottom: 1px solid #000; padding-bottom: 4px; }
.pv-wrap .sig-field span { font-weight: bold; white-space: nowrap; }
.pv-wrap .sig-line { flex: 1; min-width: 60px; }
.pv-wrap .pv-footer { background: #00a0e8; color: #fff; padding: 15px 20px; text-align: center; font-size: 11px; line-height: 1.7; margin-top: 40px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.pv-wrap .pv-footer strong { color: #162171; font-size: 13px; }
.pv-wrap .attach-link { color: #00a0e8; font-size: 11px; }
</style>

<div class="card card-info">
    <div class="data-container">
        <div class="card-header">
            <h4 class="text-center">Payment Voucher</h4>
        </div>
        <div class="card-body">
            <div class="pv-wrap">
                <div class="pv-header">
                    <div class="pv-logo"><img src="../../dist/img/logo.png" alt="Company Logo"></div>
                    <div class="pv-title">Payment Voucher</div>
                </div>
                <div class="info-row">
                    <div class="pv-from">
                        <div class="pv-from-label">From</div>
                        <div class="pv-from-value"><?= $crName ?></div>
                        <div class="pv-from-label" style="margin-top:10px;">Supplier</div>
                        <div class="pv-from-value"><?= htmlspecialchars($voucher['supplier'] ?? '-') ?></div>
                    </div>
                    <div class="pv-meta">
                        <div class="pv-meta-item">
                            <span class="pv-meta-label">Date:</span>
                            <span class="pv-meta-value"><?= date('d-M-Y', strtotime($voucher['date_'])) ?></span>
                        </div>
                        <div class="pv-meta-item">
                            <span class="pv-meta-label">Voucher No:</span>
                            <span class="pv-meta-value"><?= htmlspecialchars($voucher['reference']) ?></span>
                        </div>
                        <div class="pv-meta-item">
                            <span class="pv-meta-label">Status:</span>
                            <span class="pv-meta-value"><span class="status-badge status-<?= $status ?>"><?= ucfirst($status) ?></span></span>
                        </div>
                    </div>
                </div>
                <?php if(!empty($voucher['payment_heading'])): ?>
                <div class="pv-heading"><?= htmlspecialchars($voucher['payment_heading']) ?></div>
                <?php endif; ?>
                <?php
                $grandTotal = 0;
                $grandVat   = 0;
                $grandSub   = 0;
                ?>
                <table class="pv-table">
                    <thead>
                        <tr>
                            <th style="width:4%;">#</th>
                            <th style="width:20%;">Debit Account</th>
                            <th style="width:28%;">Description</th>
                            <th style="width:6%;">Qty</th>
                            <th style="width:7%;">Unit</th>
                            <th style="width:11%;" class="text-right">Amount</th>
                            <th style="width:10%;" class="text-right">Total</th>
                            <th style="width:14%;">Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <td colspan="8"><?= $projectName ?></td>
                        </tr>
                        <?php foreach($pitems as $item):
                            $drSub   = selectSubsidiaryById($conn, $item['dr_account']);
                            $drName  = $drSub ? htmlspecialchars($drSub['name']) : '-';
                            $vatRate = floatval($item['vat_rate']);
                            $vatAmt  = floatval($item['vat_amount']);
                            $equiv   = floatval($item['equiv_amount']);
                            $total   = $equiv + $vatAmt;
                            $grandSub   += $equiv;
                            $grandVat   += $vatAmt;
                            $grandTotal += $total;
                            $attachLink = !empty($item['attachment'])
                                ? "<a href='{$uploadPath}{$item['attachment']}' target='_blank' class='attach-link'><i class='fas fa-paperclip'></i> View</a>"
                                : '-';
                        ?>
                        <tr>
                            <td><?= $pCounter++ ?></td>
                            <td><?= $drName ?></td>
                            <td><?= htmlspecialchars($item['description']) ?><?= $vatRate > 0 ? " <span class='vat-badge'>VAT " . number_format($vatRate, 0) . "%</span>" : '' ?></td>
                            <td><?= number_format($item['quantity'], 2) ?></td>
                            <td><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                            <td class="text-right"><?= number_format($equiv, 2) ?></td>
                            <td class="text-right"><?= number_format($total, 2) ?></td>
                            <td><?= $attachLink ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php $grandTotalTSH = $grandTotal * floatval($voucher['currency_rate']); ?>
                <div class="summary-section">
                    <div class="summary-row">
                        <div class="summary-label">Sub Total:</div>
                        <div class="summary-value"><?= number_format($grandSub, 2) ?></div>
                    </div>
                    <?php if($grandVat > 0): ?>
                    <div class="summary-row">
                        <div class="summary-label">Total VAT:</div>
                        <div class="summary-value"><?= number_format($grandVat, 2) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row grand-total">
                        <div class="summary-label">Grand Total:</div>
                        <div class="summary-value"><?= number_format($grandTotal, 2) ?></div>
                    </div>
                    <?php if(!$isTSH): ?>
                    <div class="summary-row">
                        <div class="summary-label">Grand Total (TSH):</div>
                        <div class="summary-value"><?= number_format($grandTotalTSH, 2) ?> TSH</div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="sig-rows">
                    <?php if(in_array($status, ['draft', 'pending', 'active'])): ?>
                    <div class="sig-row">
                        <div class="sig-row-label">Prepared by:</div>
                        <div class="sig-field"><span>Name:</span> <?= $createdByName ?></div>
                        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
                        <div class="sig-field"><span>Date:</span> <?= $createdAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(in_array($status, ['pending', 'active'])): ?>
                    <div class="sig-row">
                        <div class="sig-row-label">Reviewed by:</div>
                        <div class="sig-field"><span>Name:</span> <?= $reviewedByName ?></div>
                        <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
                        <div class="sig-field"><span>Date:</span> <?= $reviewedAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($status === 'active'): ?>
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
                    <strong>Rejected by:</strong> <?= htmlspecialchars($voucher['rejected_by_name'] ?? '-') ?><br>
                    <strong>Rejected at:</strong> <?= !empty($voucher['rejected_at']) ? date('d-M-Y', strtotime($voucher['rejected_at'])) : '-' ?><br>
                    <strong>Reason:</strong> <?= htmlspecialchars($voucher['rejection_reason'] ?? '-') ?>
                </div>
                <?php endif; ?>
                <div class="pv-footer">
                    <strong>TYCOON TRADING COMPANY LIMITED</strong><br>
                    SIDO Arusha, Unga Ltd, Azimio Industrial Estate, P.O. Box 6113, Arusha, Tanzania.<br>
                    Tel: +255 654 577 267 / +255 687 878 700
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer card-footer-actions">
        <?php if($status === 'draft'): ?>
            <button class="btn btn-info btn-sm mr-1" onclick="bvMarkAsReview()">Mark as Reviewed</button>
        <?php elseif($status === 'pending'): ?>
            <button class="btn btn-warning btn-sm mr-1" onclick="bvMarkAsApprove()">Approve Voucher</button>
            <button class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#rejectModal">Reject Voucher</button>
        <?php endif; ?>
        <?php if(!in_array($status, ['active'])): ?>
            <a class="btn btn-warning btn-sm mr-1" href="./?page=edit_bulky_voucher&id=<?= $bvId ?>">Amend</a>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm mr-1" onclick="downloadPDF('voucher_<?= $voucher['reference'] ?>.pdf')">Download PDF</button>
        <button class="btn btn-success btn-sm" onclick="window.print()">Print</button>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Voucher</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Reason for Rejection <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" class="form-control" rows="4" placeholder="Enter reason for rejection..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="rejectVoucher()">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
function bvMarkAsReview(){
    if(!confirm('Mark this voucher as reviewed?')) return;
    $.ajax({
        url: './controllers/bulk_action_controller.php',
        type: 'POST',
        data: 'selected_ids[]=<?= $bvId ?>&review_bulky=1',
        success: function(response){
            if(response.trim() == 'SUCCESS'){ location.reload(); } else { alert('Error: ' + response); }
        }
    });
}
function bvMarkAsApprove(){
    if(!confirm('Approve this voucher?')) return;
    $.ajax({
        url: './controllers/bulk_action_controller.php',
        type: 'POST',
        data: 'selected_ids[]=<?= $bvId ?>&approve_bulky=1',
        success: function(response){
            if(response.trim() == 'SUCCESS'){ location.reload(); } else { alert('Error: ' + response); }
        }
    });
}
function rejectVoucher(){
    const reason = $('#rejection_reason').val().trim();
    if(!reason){ alert('Please enter a reason for rejection.'); return; }
    if(!confirm('Are you sure you want to reject this voucher?')) return;
    $.ajax({
        url: './controllers/bulk_action_controller.php',
        type: 'POST',
        data: { 'selected_ids[]': <?= $bvId ?>, 'reject_bulky': 1, 'rejection_reason': reason },
        success: function(response){
            if(response.trim() == 'SUCCESS'){ $('#rejectModal').modal('hide'); location.reload(); } else { alert('Error: ' + response); }
        }
    });
}
</script>