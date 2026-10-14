<?php
$pvId         = (int) $_GET['id'];
$voucher      = selectPurchaseVoucherById($conn, $pvId);
$items        = selectPurchaseVoucherItems($conn, $pvId);
$supplier     = selectSubsidiaryById($conn, $voucher['supplier_id']);
$co           = selectAllCompanyDetails($conn);
$currencyName = $voucher['currency_name'];
$currencyRate = floatval($voucher['currency_rate']) ?: 1;
$voucherDate  = date('d-M-Y', strtotime($voucher['date_']));
$isTSH        = strtoupper($currencyName) === 'TSH';
$status       = $voucher['status'];
$createdByName  = !empty($voucher['created_by_name'])  ? htmlspecialchars($voucher['created_by_name'])  : '-';
$reviewedByName = !empty($voucher['reviewed_by_name']) ? htmlspecialchars($voucher['reviewed_by_name']) : '-';
$approvedByName = !empty($voucher['approved_by_name']) ? htmlspecialchars($voucher['approved_by_name']) : '-';
$createdAt  = !empty($voucher['created_at'])  ? date('d-M-Y', strtotime($voucher['created_at']))  : '-';
$reviewedAt = !empty($voucher['reviewed_at']) ? date('d-M-Y', strtotime($voucher['reviewed_at'])) : '-';
$approvedAt = !empty($voucher['approved_at']) ? date('d-M-Y', strtotime($voucher['approved_at'])) : '-';

$phones = [];
foreach(['phone','phone2','phone3'] as $col){
    if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
}
$phone_str = implode(' / ', $phones);
?>



<div class="card card-info">
    <div class="data-container">
        <div class="card-header">
            <h4 class="text-center">Purchase Voucher</h4>
        </div>
        <div class="card-body">

            <div id="purchase-voucher-invoice-<?= $pvId ?>" class="purchase-voucher-invoice">

                <div class="purchase-voucher-invoice-header">
                    <div class="purchase-voucher-invoice-logo">
                        <img src="./dist/img/logo.png" alt="Company Logo">
                    </div>
                    <div class="purchase-voucher-invoice-title">Purchase Voucher</div>
                </div>

                <div class="purchase-voucher-invoice-info-row">
                    <div class="purchase-voucher-invoice-party">
                        <div class="purchase-voucher-invoice-party-label">Supplier:</div>
                        <div class="purchase-voucher-invoice-party-value"><?= htmlspecialchars($voucher['supplier_name']) ?></div>
                        <?php if(!empty($supplier['email'])): ?>
                        <div class="purchase-voucher-invoice-party-value"><?= htmlspecialchars($supplier['email']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($supplier['tin'])): ?>
                        <div class="purchase-voucher-invoice-party-value">TIN: <?= htmlspecialchars($supplier['tin']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($supplier['vrn'])): ?>
                        <div class="purchase-voucher-invoice-party-value">VRN: <?= htmlspecialchars($supplier['vrn']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($supplier['phone'])): ?>
                        <div class="purchase-voucher-invoice-party-value"><?= htmlspecialchars($supplier['phone']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($supplier['address'])): ?>
                        <div class="purchase-voucher-invoice-party-value"><?= htmlspecialchars($supplier['address']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="purchase-voucher-invoice-meta">
                        <div class="purchase-voucher-invoice-meta-item">
                            <span class="purchase-voucher-invoice-meta-label">Voucher Date:</span>
                            <span class="purchase-voucher-invoice-meta-value"><?= $voucherDate ?></span>
                        </div>
                        <div class="purchase-voucher-invoice-meta-item">
                            <span class="purchase-voucher-invoice-meta-label">Reference:</span>
                            <span class="purchase-voucher-invoice-meta-value"><?= htmlspecialchars($voucher['reference']) ?></span>
                        </div>
                        <div class="purchase-voucher-invoice-meta-item">
                            <span class="purchase-voucher-invoice-meta-label">Currency:</span>
                            <span class="purchase-voucher-invoice-meta-value"><?= htmlspecialchars($currencyName) ?></span>
                        </div>
                        <?php if(!$isTSH): ?>
                        <div class="purchase-voucher-invoice-meta-item">
                            <span class="purchase-voucher-invoice-meta-label">Rate:</span>
                            <span class="purchase-voucher-invoice-meta-value">1 <?= htmlspecialchars($currencyName) ?> = <?= number_format($currencyRate, 2) ?> TSH</span>
                        </div>
                        <?php endif; ?>
                        <div class="purchase-voucher-invoice-meta-item">
                            <span class="purchase-voucher-invoice-meta-label">Status:</span>
                            <span class="purchase-voucher-invoice-meta-value">
                                <span class="purchase-voucher-invoice-status-badge purchase-voucher-invoice-status-<?= $status ?>"><?= ucfirst($status) ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if($items && is_array($items) && count($items) > 0): ?>
                <div class="purchase-voucher-invoice-prod-grid">
                    <?php foreach($items as $gridItem):
                        $gridImgPath  = './uploads/items/' . ($gridItem['image_url'] ?? '');
                        $gridHasImage = !empty($gridItem['image_url']) && file_exists($gridImgPath);
                        $gridVatRate  = floatval($gridItem['vat_rate'] ?? 0);
                    ?>
                    <div class="purchase-voucher-invoice-prod-card">
                        <div class="purchase-voucher-invoice-prod-img-wrap">
                            <?php if($gridHasImage): ?>
                                <img src="<?= htmlspecialchars($gridImgPath) ?>" alt="<?= htmlspecialchars($gridItem['item_name']) ?>">
                            <?php else: ?>
                                <div class="purchase-voucher-invoice-no-img">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="purchase-voucher-invoice-prod-card-info">
                            <div class="purchase-voucher-invoice-prod-card-name"><?= htmlspecialchars($gridItem['item_name']) ?></div>
                            <div class="purchase-voucher-invoice-prod-card-meta">
                                Qty: <?= number_format(floatval($gridItem['quantity']), 0) ?>
                                <?php if(!empty($gridItem['unit_name'])): ?> · <?= htmlspecialchars($gridItem['unit_name']) ?><?php endif; ?>
                                <?php if($gridVatRate > 0): ?>
                                    <span class="purchase-voucher-invoice-vat-badge">VAT <?= number_format($gridVatRate, 0) ?>%</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <table class="purchase-voucher-invoice-table">
                    <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:37%;">Description</th>
                            <th style="width:9%;">Qty</th>
                            <th style="width:9%;">Unit</th>
                            <th style="width:16%;" class="purchase-voucher-invoice-text-right">Unit Price</th>
                            <th style="width:16%;" class="purchase-voucher-invoice-text-right">Amount</th>
                            <th style="width:16%;" class="purchase-voucher-invoice-text-right">Total (inc. VAT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subtotal = 0;
                        $totalVat = 0;
                        $hasVat   = false;
                        if($items && is_array($items)){
                            $counter = 1;
                            foreach($items as $item){
                                $itemAmt       = floatval($item['amt']);
                                $itemPrice     = floatval($item['price']);
                                $itemQty       = floatval($item['quantity']);
                                $itemVatRate   = floatval($item['vat_rate']);
                                $itemVatAmount = ($itemVatRate / 100) * $itemAmt;
                                $itemTotal     = $itemAmt + $itemVatAmount;
                                $subtotal     += $itemAmt;
                                $totalVat     += $itemVatAmount;
                                if($itemVatRate > 0) $hasVat = true;
                        ?>
                        <tr>
                            <td><?= $counter ?></td>
                            <td>
                                <?= htmlspecialchars($item['item_name']) ?>
                                <?php if($itemVatRate > 0): ?>
                                    <span class="purchase-voucher-invoice-vat-badge">VAT <?= number_format($itemVatRate, 0) ?>%</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($itemQty, 0) ?></td>
                            <td><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                            <td class="purchase-voucher-invoice-text-right"><?= number_format($itemPrice, 2) ?></td>
                            <td class="purchase-voucher-invoice-text-right"><?= number_format($itemAmt, 2) ?></td>
                            <td class="purchase-voucher-invoice-text-right"><?= number_format($itemTotal, 2) ?></td>
                        </tr>
                        <?php
                                $counter++;
                            }
                        }
                        $grandTotal    = $subtotal + $totalVat;
                        $grandTotalTSH = $grandTotal * $currencyRate;
                        ?>
                    </tbody>
                </table>

                <div class="purchase-voucher-invoice-summary">
                    <div class="purchase-voucher-invoice-summary-row">
                        <div class="purchase-voucher-invoice-summary-label">Sub Total:</div>
                        <div class="purchase-voucher-invoice-summary-value"><?= number_format($subtotal, 2) ?> <?= htmlspecialchars($currencyName) ?></div>
                    </div>
                    <?php if($hasVat): ?>
                    <div class="purchase-voucher-invoice-summary-row">
                        <div class="purchase-voucher-invoice-summary-label">Total VAT:</div>
                        <div class="purchase-voucher-invoice-summary-value"><?= number_format($totalVat, 2) ?> <?= htmlspecialchars($currencyName) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="purchase-voucher-invoice-summary-row purchase-voucher-invoice-grand-total">
                        <div class="purchase-voucher-invoice-summary-label">Grand Total:</div>
                        <div class="purchase-voucher-invoice-summary-value"><?= number_format($grandTotal, 2) ?> <?= htmlspecialchars($currencyName) ?></div>
                    </div>
                    <?php if(!$isTSH): ?>
                    <div class="purchase-voucher-invoice-summary-row">
                        <div class="purchase-voucher-invoice-summary-label">Grand Total (TSH):</div>
                        <div class="purchase-voucher-invoice-summary-value"><?= number_format($grandTotalTSH, 2) ?> TSH</div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="purchase-voucher-invoice-sig-rows">
                    <?php if(in_array($status, ['draft', 'pending', 'active'])): ?>
                    <div class="purchase-voucher-invoice-sig-row">
                        <div class="purchase-voucher-invoice-sig-label">Prepared by:</div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Name:</span> <?= $createdByName ?></div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Signature:</span><div class="purchase-voucher-invoice-sig-line"></div></div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Date:</span> <?= $createdAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(in_array($status, ['pending', 'active'])): ?>
                    <div class="purchase-voucher-invoice-sig-row">
                        <div class="purchase-voucher-invoice-sig-label">Reviewed by:</div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Name:</span> <?= $reviewedByName ?></div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Signature:</span><div class="purchase-voucher-invoice-sig-line"></div></div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Date:</span> <?= $reviewedAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($status === 'active'): ?>
                    <div class="purchase-voucher-invoice-sig-row">
                        <div class="purchase-voucher-invoice-sig-label">Approved by:</div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Name:</span> <?= $approvedByName ?></div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Signature:</span><div class="purchase-voucher-invoice-sig-line"></div></div>
                        <div class="purchase-voucher-invoice-sig-field"><span>Date:</span> <?= $approvedAt ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="purchase-voucher-invoice-footer">
                    <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
                    <?= htmlspecialchars($co['address'] ?? '') ?><br>
                    Tel: <?= $phone_str ?>
                </div>

            </div>

            <?php if($status === 'rejected'): ?>
            <div class="alert alert-danger mt-3 mx-3">
                <strong>Rejected by:</strong> <?= htmlspecialchars($voucher['rejected_by_name'] ?? '-') ?><br>
                <strong>Rejected at:</strong> <?= !empty($voucher['rejected_at']) ? date('d-M-Y', strtotime($voucher['rejected_at'])) : '-' ?><br>
                <strong>Reason:</strong> <?= htmlspecialchars($voucher['rejection_reason'] ?? '-') ?>
            </div>
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
        <?php if(!in_array($status, ['active'])): ?>
            <a class="btn btn-warning btn-sm mr-1" href="./?page=purchase_voucher&id=<?= $pvId ?>">Amend</a>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm mr-1" onclick="printPurchaseVoucherInvoice('purchase-voucher-invoice-<?= $pvId ?>')">
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
function printPurchaseVoucherInvoice(contentId) {
    var base = window.location.origin + '/';
    var el = document.getElementById(contentId);
    if(!el){ alert('Voucher not loaded yet.'); return; }
    var content = el.outerHTML;
    var win = window.open('', '_blank');
    if(!win){ alert('Please allow popups for printing.'); return; }
    win.document.write('<html><head><title>Purchase Voucher</title>');
    win.document.write('<link rel="stylesheet" href="' + base + 'plugins/fontawesome-free/css/all.min.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/primary-color.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/purchase-voucher-invoice.css">');
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
        data: 'selected_ids[]=<?= $pvId ?>&review_purchase=1',
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
        data: 'selected_ids[]=<?= $pvId ?>&approve_purchase=1',
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
        data: { 'selected_ids[]': <?= $pvId ?>, 'reject_purchase': 1, 'rejection_reason': reason },
        success: function(response){
            if(response.trim() == 'SUCCESS'){ $('#rejectModal').modal('hide'); location.reload(); } else { alert('Error: ' + response); }
        }
    });
}
</script>