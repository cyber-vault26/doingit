<?php
$transaction = selectTransactionById($conn, $id);
$co          = selectAllCompanyDetails($conn);

$phones = [];
foreach(['phone','phone2','phone3'] as $col){
    if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
}
$phone_str = implode(' / ', $phones);

if($transaction && is_array($transaction)){
    $voucherType     = $transaction['voucher_type'] ?? '';
    $subsidiary      = selectSubsidiaryById($conn, $transaction['dr_account']);
    $subsidiaryLabel = $voucherType === 'payment' ? 'Paid To' : ($voucherType === 'receipt' ? 'Received From' : 'Debited Account');
    $creditLabel     = $voucherType === 'payment' ? 'Paid From' : ($voucherType === 'receipt' ? 'Deposited To' : 'Credited Account');
    $voucherDate     = date('d-M-Y', strtotime($transaction['date_']));
    $typeLabel       = ucfirst($voucherType) . ' Voucher';
    $status          = $transaction['status'];
    $createdByName   = !empty($transaction['created_by_name'])  ? htmlspecialchars($transaction['created_by_name'])  : '-';
    $reviewedByName  = !empty($transaction['reviewed_by_name']) ? htmlspecialchars($transaction['reviewed_by_name']) : '-';
    $approvedByName  = !empty($transaction['approved_by_name']) ? htmlspecialchars($transaction['approved_by_name']) : '-';
    $createdAt       = !empty($transaction['created_at'])  ? date('d-M-Y', strtotime($transaction['created_at']))  : '-';
    $reviewedAt      = !empty($transaction['reviewed_at']) ? date('d-M-Y', strtotime($transaction['reviewed_at'])) : '-';
    $approvedAt      = !empty($transaction['approved_at']) ? date('d-M-Y', strtotime($transaction['approved_at'])) : '-';

    // edit page resolution
    $editPage = '';
    if($voucherType == 'payment') $editPage = 'payment_voucher';
    if($voucherType == 'receipt') $editPage = 'receipt_voucher';
    if($voucherType == 'journal') $editPage = 'journal_voucher';
}
?>



<div class="card card-info">
    <div class="data-container">
        <div class="card-header">
            <h4 class="text-center">Voucher Preview</h4>
        </div>
        <div class="card-body">

        <?php if($transaction && is_array($transaction)): ?>

            <div id="normal-voucher-<?= $id ?>" class="normal-voucher">

                <div class="normal-voucher-header">
                    <div class="normal-voucher-logo">
                        <img src="./dist/img/logo.png" alt="Company Logo">
                    </div>
                    <div class="normal-voucher-title"><?= htmlspecialchars($typeLabel) ?></div>
                </div>

                <div class="normal-voucher-info-row">
                    <div class="normal-voucher-party">
                        <div class="normal-voucher-party-label"><?= $subsidiaryLabel ?>:</div>
                        <div class="normal-voucher-party-value"><?= htmlspecialchars($subsidiary['name'] ?? '') ?></div>
                        <?php if(!empty($subsidiary['email'])): ?>
                        <div class="normal-voucher-party-value"><?= htmlspecialchars($subsidiary['email']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($subsidiary['tin'])): ?>
                        <div class="normal-voucher-party-value">TIN: <?= htmlspecialchars($subsidiary['tin']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($subsidiary['phone'])): ?>
                        <div class="normal-voucher-party-value"><?= htmlspecialchars($subsidiary['phone']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($subsidiary['address'])): ?>
                        <div class="normal-voucher-party-value"><?= htmlspecialchars($subsidiary['address']) ?></div>
                        <?php endif; ?>
                        <div class="normal-voucher-party-label"><?= $creditLabel ?>:</div>
                        <div class="normal-voucher-party-value"><?= htmlspecialchars($transaction['credit_acc'] ?? '') ?></div>
                    </div>
                    <div class="normal-voucher-meta">
                        <div class="normal-voucher-meta-item">
                            <span class="normal-voucher-meta-label">Reference:</span>
                            <span class="normal-voucher-meta-value"><?= htmlspecialchars($transaction['reference_no']) ?></span>
                        </div>
                        <div class="normal-voucher-meta-item">
                            <span class="normal-voucher-meta-label">Date:</span>
                            <span class="normal-voucher-meta-value"><?= $voucherDate ?></span>
                        </div>
                        <div class="normal-voucher-meta-item">
                            <span class="normal-voucher-meta-label">Status:</span>
                            <span class="normal-voucher-meta-value">
                                <span class="normal-voucher-status-badge normal-voucher-status-<?= $status ?>"><?= ucfirst($status) ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <table class="normal-voucher-table">
                    <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:70%;">Narration</th>
                            <th style="width:15%;" class="normal-voucher-text-right">Amount</th>
                            <th style="width:10%;">Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><?= htmlspecialchars($transaction['description']) ?></td>
                            <td class="normal-voucher-text-right"><?= number_format(floatval($transaction['amount']), 2) ?></td>
                            <td>
                                <?php if(!empty($transaction['attachment_url'])): ?>
                                <a href="./uploads/vouchers/<?= htmlspecialchars($transaction['attachment_url']) ?>" target="_blank" class="normal-voucher-attach-link">
                                    <i class="fas fa-paperclip"></i> View
                                </a>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="normal-voucher-summary">
                    <div class="normal-voucher-summary-row normal-voucher-grand-total">
                        <div class="normal-voucher-summary-label">Total Amount:</div>
                        <div class="normal-voucher-summary-value"><?= number_format(floatval($transaction['amount']), 2) ?></div>
                    </div>
                </div>

                <div class="normal-voucher-sig-rows">
                    <?php if(in_array($status, ['draft', 'pending', 'active'])): ?>
                    <div class="normal-voucher-sig-row">
                        <div class="normal-voucher-sig-label">Prepared by:</div>
                        <div class="normal-voucher-sig-field"><span>Name:</span> <?= $createdByName ?></div>
                        <div class="normal-voucher-sig-field"><span>Signature:</span><div class="normal-voucher-sig-line"></div></div>
                        <div class="normal-voucher-sig-field"><span>Date:</span> <?= $createdAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(in_array($status, ['pending', 'active'])): ?>
                    <div class="normal-voucher-sig-row">
                        <div class="normal-voucher-sig-label">Reviewed by:</div>
                        <div class="normal-voucher-sig-field"><span>Name:</span> <?= $reviewedByName ?></div>
                        <div class="normal-voucher-sig-field"><span>Signature:</span><div class="normal-voucher-sig-line"></div></div>
                        <div class="normal-voucher-sig-field"><span>Date:</span> <?= $reviewedAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($status === 'active'): ?>
                    <div class="normal-voucher-sig-row">
                        <div class="normal-voucher-sig-label">Approved by:</div>
                        <div class="normal-voucher-sig-field"><span>Name:</span> <?= $approvedByName ?></div>
                        <div class="normal-voucher-sig-field"><span>Signature:</span><div class="normal-voucher-sig-line"></div></div>
                        <div class="normal-voucher-sig-field"><span>Date:</span> <?= $approvedAt ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="normal-voucher-footer">
                    <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
                    <?= htmlspecialchars($co['address'] ?? '') ?><br>
                    Tel: <?= $phone_str ?>
                </div>

            </div>

            <?php if($status === 'rejected'): ?>
            <div class="alert alert-danger mt-3 mx-3">
                <strong>Rejected by:</strong> <?= htmlspecialchars($transaction['rejected_by_name'] ?? '-') ?><br>
                <strong>Rejected at:</strong> <?= !empty($transaction['rejected_at']) ? date('d-M-Y', strtotime($transaction['rejected_at'])) : '-' ?><br>
                <strong>Reason:</strong> <?= htmlspecialchars($transaction['rejection_reason'] ?? '-') ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>

        </div>
    </div>

    <div class="card-footer card-footer-actions">
        <?php if($status === 'draft'): ?>
            <button class="btn btn-info btn-sm mr-1" onclick="markAsReview()">Mark as Reviewed</button>
        <?php elseif($status === 'pending'): ?>
            <button class="btn btn-warning btn-sm mr-1" onclick="markAsApprove()">Approve Voucher</button>
            <button class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#rejectModal">Reject Voucher</button>
        <?php endif; ?>
        <?php if(!in_array($status, ['active']) && !empty($editPage)): ?>
            <a class="btn btn-warning btn-sm mr-1" href="./?page=<?= $editPage ?>&id=<?= $id ?>">Amend</a>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm mr-1" onclick="printNormalVoucher('normal-voucher-<?= $id ?>')">
            <i class="fas fa-print"></i> Print
        </button>
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
function printNormalVoucher(contentId) {
    var base = window.location.origin + '/';
    var el = document.getElementById(contentId);
    if(!el){ alert('Voucher not loaded yet.'); return; }
    var content = el.outerHTML;
    var win = window.open('', '_blank');
    if(!win){ alert('Please allow popups for printing.'); return; }
    win.document.write('<html><head><title>Voucher</title>');
    win.document.write('<link rel="stylesheet" href="' + base + 'plugins/fontawesome-free/css/all.min.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/primary-color.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/normal-voucher.css">');
    win.document.write('</head><body>');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function(){ win.print(); win.close(); }, 1200);
}
function markAsReview(){
    if(!confirm('Mark this voucher as reviewed?')) return;
    $.ajax({
        url: './controllers/bulk_action_controller.php',
        type: 'POST',
        data: 'selected_ids[]=<?= $id ?>&review_voucher=1',
        success: function(response){
            if(response.trim() == 'SUCCESS'){ location.reload(); } else { alert('Error: ' + response); }
        }
    });
}
function markAsApprove(){
    if(!confirm('Approve this voucher?')) return;
    $.ajax({
        url: './controllers/bulk_action_controller.php',
        type: 'POST',
        data: 'selected_ids[]=<?= $id ?>&approve_voucher=1',
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
        data: { 'selected_ids[]': <?= $id ?>, 'reject_voucher': 1, 'rejection_reason': reason },
        success: function(response){
            if(response.trim() == 'SUCCESS'){ $('#rejectModal').modal('hide'); location.reload(); } else { alert('Error: ' + response); }
        }
    });
}
</script>