<?php
$invoiceId    = (int) $_GET['id'];
$invoice      = selectSalesVoucherById($conn, $invoiceId);
$items        = selectSalesVoucherItems($conn, $invoiceId);
$customer     = selectSubsidiaryById($conn, $invoice['customer_id']);
$co           = selectAllCompanyDetails($conn);
$currencyName = $invoice['currency_name'];
$currencyRate = floatval($invoice['currency_rate']) ?: 1;
$invoiceDate  = date('d-M-Y', strtotime($invoice['date_']));
$dueDate      = isset($invoice['due_date']) ? date('d-M-Y', strtotime($invoice['due_date'])) : 'N/A';
$invoiceHeading = isset($invoice['invoice_heading']) ? htmlspecialchars($invoice['invoice_heading']) : '';
$isTSH        = strtoupper($currencyName) === 'TSH';
$status       = $invoice['status'];
$createdByName  = !empty($invoice['created_by_name'])  ? htmlspecialchars($invoice['created_by_name'])  : '-';
$reviewedByName = !empty($invoice['reviewed_by_name']) ? htmlspecialchars($invoice['reviewed_by_name']) : '-';
$approvedByName = !empty($invoice['approved_by_name']) ? htmlspecialchars($invoice['approved_by_name']) : '-';
$createdAt  = !empty($invoice['created_at'])  ? date('d-M-Y', strtotime($invoice['created_at']))  : '-';
$reviewedAt = !empty($invoice['reviewed_at']) ? date('d-M-Y', strtotime($invoice['reviewed_at'])) : '-';
$approvedAt = !empty($invoice['approved_at']) ? date('d-M-Y', strtotime($invoice['approved_at'])) : '-';

$phones = [];
foreach(['phone','phone2','phone3'] as $col){
    if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
}
$phone_str = implode(' / ', $phones);
?>

<div class="card card-info">
    <div class="data-container">
        <div class="card-header">
            <h4 class="text-center">Sales Voucher Invoice</h4>
        </div>
        <div class="card-body">

            <div id="sales-voucher-invoice-<?= $invoiceId ?>" class="sales-voucher-invoice">

                <div class="sales-voucher-invoice-header">
                    <div class="sales-voucher-invoice-logo">
                        <img src="./dist/img/logo.png" alt="Company Logo">
                    </div>
                    <div class="sales-voucher-invoice-title">Tax Invoice</div>
                </div>

                <div class="sales-voucher-invoice-info-row">
                    <div class="sales-voucher-invoice-party">
                        <div class="sales-voucher-invoice-party-label">Bill To:</div>
                        <div class="sales-voucher-invoice-party-value"><?= htmlspecialchars($invoice['customer_name']) ?></div>
                        <?php if(!empty($customer['email'])): ?>
                        <div class="sales-voucher-invoice-party-value"><?= htmlspecialchars($customer['email']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($customer['tin'])): ?>
                        <div class="sales-voucher-invoice-party-value">TIN: <?= htmlspecialchars($customer['tin']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($customer['vrn'])): ?>
                        <div class="sales-voucher-invoice-party-value">VRN: <?= htmlspecialchars($customer['vrn']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($customer['phone'])): ?>
                        <div class="sales-voucher-invoice-party-value"><?= htmlspecialchars($customer['phone']) ?></div>
                        <?php endif; ?>
                        <?php if(!empty($customer['address'])): ?>
                        <div class="sales-voucher-invoice-party-value"><?= htmlspecialchars($customer['address']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="sales-voucher-invoice-meta">
                        <div class="sales-voucher-invoice-meta-item">
                            <span class="sales-voucher-invoice-meta-label">Invoice Date:</span>
                            <span class="sales-voucher-invoice-meta-value"><?= $invoiceDate ?></span>
                        </div>
                        <div class="sales-voucher-invoice-meta-item">
                            <span class="sales-voucher-invoice-meta-label">Due Date:</span>
                            <span class="sales-voucher-invoice-meta-value"><?= $dueDate ?></span>
                        </div>
                        <div class="sales-voucher-invoice-meta-item">
                            <span class="sales-voucher-invoice-meta-label">Invoice Number:</span>
                            <span class="sales-voucher-invoice-meta-value"><?= htmlspecialchars($invoice['reference']) ?></span>
                        </div>
                        <div class="sales-voucher-invoice-meta-item">
                            <span class="sales-voucher-invoice-meta-label">Currency:</span>
                            <span class="sales-voucher-invoice-meta-value"><?= htmlspecialchars($currencyName) ?></span>
                        </div>
                        <?php if(!$isTSH): ?>
                        <div class="sales-voucher-invoice-meta-item">
                            <span class="sales-voucher-invoice-meta-label">Rate:</span>
                            <span class="sales-voucher-invoice-meta-value">1 <?= htmlspecialchars($currencyName) ?> = <?= number_format($currencyRate, 2) ?> TSH</span>
                        </div>
                        <?php endif; ?>
                        <div class="sales-voucher-invoice-meta-item">
                            <span class="sales-voucher-invoice-meta-label">Status:</span>
                            <span class="sales-voucher-invoice-meta-value">
                                <span class="sales-voucher-invoice-status-badge sales-voucher-invoice-status-<?= $status ?>"><?= ucfirst($status) ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if(!empty($invoiceHeading)): ?>
                <div class="sales-voucher-invoice-description"><?= $invoiceHeading ?></div>
                <?php endif; ?>

                <?php if($items && is_array($items) && count($items) > 0): ?>
                <div class="sales-voucher-invoice-prod-grid">
                    <?php foreach($items as $gridItem):
                        $gridImgPath  = './uploads/items/' . ($gridItem['image_url'] ?? '');
                        $gridHasImage = !empty($gridItem['image_url']) && file_exists($gridImgPath);
                        $gridVatRate  = floatval($gridItem['vat_rate'] ?? 0);
                    ?>
                    <div class="sales-voucher-invoice-prod-card">
                        <div class="sales-voucher-invoice-prod-img-wrap">
                            <?php if($gridHasImage): ?>
                                <img src="<?= htmlspecialchars($gridImgPath) ?>" alt="<?= htmlspecialchars($gridItem['item_name']) ?>">
                            <?php else: ?>
                                <div class="sales-voucher-invoice-no-img">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="sales-voucher-invoice-prod-card-info">
                            <div class="sales-voucher-invoice-prod-card-name"><?= htmlspecialchars($gridItem['item_name']) ?></div>
                            <div class="sales-voucher-invoice-prod-card-meta">
                                Qty: <?= number_format(floatval($gridItem['quantity']), 0) ?>
                                <?php if(!empty($gridItem['unit_name'])): ?> · <?= htmlspecialchars($gridItem['unit_name']) ?><?php endif; ?>
                                <?php if($gridVatRate > 0): ?>
                                    <span class="sales-voucher-invoice-vat-badge">VAT <?= number_format($gridVatRate, 0) ?>%</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <table class="sales-voucher-invoice-table">
                    <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:37%;">Description</th>
                            <th style="width:9%;">Qty</th>
                            <th style="width:9%;">Unit</th>
                            <th style="width:16%;" class="sales-voucher-invoice-text-right">Unit Price</th>
                            <th style="width:16%;" class="sales-voucher-invoice-text-right">Amount</th>
                            <th style="width:16%;" class="sales-voucher-invoice-text-right">Total (inc. VAT)</th>
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
                                $itemImgPath  = './uploads/items/' . ($item['image_url'] ?? '');
                                //print_r($itemImgPath);
                                $itemHasImage = !empty($item['image_url']) && file_exists($itemImgPath);
                        ?>
                        <tr>
                            <td><?= $counter ?></td>
                            <td>
                                <?= htmlspecialchars($item['item_name']) ?>
                                <?php if($itemVatRate > 0): ?>
                                    <span class="sales-voucher-invoice-vat-badge">VAT <?= number_format($itemVatRate, 0) ?>%</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($itemQty, 0) ?></td>
                            <td><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                            <td class="sales-voucher-invoice-text-right"><?= number_format($itemPrice, 2) ?></td>
                            <td class="sales-voucher-invoice-text-right"><?= number_format($itemAmt, 2) ?></td>
                            <td class="sales-voucher-invoice-text-right"><?= number_format($itemTotal, 2) ?></td>
                        </tr>
                        <?php
                                $counter++;
                            }
                        }
                        $grandTotal    = $subtotal + $totalVat;
                        $grandTotalTSH = $grandTotal * $currencyRate;
                        $displaySub    = $isTSH ? $subtotal  : $subtotal  / $currencyRate;
                        $displayVat    = $isTSH ? $totalVat  : $totalVat  / $currencyRate;
                        $displayGrand  = $isTSH ? $grandTotal : $grandTotal / $currencyRate;
                        ?>
                    </tbody>
                </table>

                <div class="sales-voucher-invoice-summary">
                    <div class="sales-voucher-invoice-summary-row">
                        <div class="sales-voucher-invoice-summary-label">Sub Total:</div>
                        <div class="sales-voucher-invoice-summary-value"><?= number_format($subtotal, 2) ?> <?= htmlspecialchars($currencyName) ?></div>
                    </div>
                    <?php if($hasVat): ?>
                    <div class="sales-voucher-invoice-summary-row">
                        <div class="sales-voucher-invoice-summary-label">Total VAT:</div>
                        <div class="sales-voucher-invoice-summary-value"><?= number_format($totalVat, 2) ?> <?= htmlspecialchars($currencyName) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="sales-voucher-invoice-summary-row sales-voucher-invoice-grand-total">
                        <div class="sales-voucher-invoice-summary-label">Grand Total:</div>
                        <div class="sales-voucher-invoice-summary-value"><?= number_format($grandTotal, 2) ?> <?= htmlspecialchars($currencyName) ?></div>
                    </div>
                    <?php if(!$isTSH): ?>
                    <div class="sales-voucher-invoice-summary-row">
                        <div class="sales-voucher-invoice-summary-label">Grand Total (TSH):</div>
                        <div class="sales-voucher-invoice-summary-value"><?= number_format($grandTotalTSH, 2) ?> TSH</div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="sales-voucher-invoice-bank">
                    <div class="sales-voucher-invoice-bank-title">PAYMENT DETAILS</div>
                    <strong>Account name:</strong> <?= htmlspecialchars($co['title'] ?? '') ?><br>
                    <?php if(!empty($co['bank_account_name'])): ?>
                    <strong>Account number:</strong> <?= htmlspecialchars($co['bank_account_name']) ?><br>
                    <?php endif; ?>
                    <?php if(!empty($co['bank_name'])): ?>
                    <strong>Bank name:</strong> <?= htmlspecialchars($co['bank_name']) ?><br>
                    <?php endif; ?>
                    <?php if(!empty($co['bank_branch'])): ?>
                    <strong>Branch name:</strong> <?= htmlspecialchars($co['bank_branch']) ?><br>
                    <?php endif; ?>
                </div>

                <div class="sales-voucher-invoice-sig-rows">
                    <?php if(in_array($status, ['draft', 'pending', 'active'])): ?>
                    <div class="sales-voucher-invoice-sig-row">
                        <div class="sales-voucher-invoice-sig-label">Prepared by:</div>
                        <div class="sales-voucher-invoice-sig-field"><span>Name:</span> <?= $createdByName ?></div>
                        <div class="sales-voucher-invoice-sig-field"><span>Signature:</span><div class="sales-voucher-invoice-sig-line"></div></div>
                        <div class="sales-voucher-invoice-sig-field"><span>Date:</span> <?= $createdAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(in_array($status, ['pending', 'active'])): ?>
                    <div class="sales-voucher-invoice-sig-row">
                        <div class="sales-voucher-invoice-sig-label">Reviewed by:</div>
                        <div class="sales-voucher-invoice-sig-field"><span>Name:</span> <?= $reviewedByName ?></div>
                        <div class="sales-voucher-invoice-sig-field"><span>Signature:</span><div class="sales-voucher-invoice-sig-line"></div></div>
                        <div class="sales-voucher-invoice-sig-field"><span>Date:</span> <?= $reviewedAt ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($status === 'active'): ?>
                    <div class="sales-voucher-invoice-sig-row">
                        <div class="sales-voucher-invoice-sig-label">Approved by:</div>
                        <div class="sales-voucher-invoice-sig-field"><span>Name:</span> <?= $approvedByName ?></div>
                        <div class="sales-voucher-invoice-sig-field"><span>Signature:</span><div class="sales-voucher-invoice-sig-line"></div></div>
                        <div class="sales-voucher-invoice-sig-field"><span>Date:</span> <?= $approvedAt ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="sales-voucher-invoice-footer">
                    <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
                    <?= htmlspecialchars($co['address'] ?? '') ?><br>
                    Tel: <?= $phone_str ?>
                </div>

            </div>

            <?php if($status === 'rejected'): ?>
            <div class="alert alert-danger mt-3 mx-3">
                <strong>Rejected by:</strong> <?= htmlspecialchars($invoice['rejected_by_name'] ?? '-') ?><br>
                <strong>Rejected at:</strong> <?= !empty($invoice['rejected_at']) ? date('d-M-Y', strtotime($invoice['rejected_at'])) : '-' ?><br>
                <strong>Reason:</strong> <?= htmlspecialchars($invoice['rejection_reason'] ?? '-') ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="card-footer card-footer-actions">
        <?php if($status === 'draft'): ?>
            <button class="btn btn-info btn-sm mr-1" onclick="markAsReview()">Mark as Reviewed</button>
        <?php elseif($status === 'pending'): ?>
            <button class="btn btn-warning btn-sm mr-1" onclick="markAsApprove()">Approve Voucher</button>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm mr-1" onclick="printSalesVoucherInvoice('sales-voucher-invoice-<?= $invoiceId ?>')">
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
function printSalesVoucherInvoice(contentId) {
    var base = window.location.origin + '/';
    var el = document.getElementById(contentId);
    if(!el){ alert('Invoice not loaded yet.'); return; }
    var content = el.outerHTML;
    var win = window.open('', '_blank');
    if(!win){ alert('Please allow popups for printing.'); return; }
    win.document.write('<html><head><title>Tax Invoice</title>');
    win.document.write('<link rel="stylesheet" href="' + base + 'plugins/fontawesome-free/css/all.min.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/primary-color.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/sales-voucher-invoice.css">');
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
        data: 'selected_ids[]=<?= $invoiceId ?>&review_sales=1',
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
        data: 'selected_ids[]=<?= $invoiceId ?>&approve_sales=1',
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
        data: { 'selected_ids[]': <?= $invoiceId ?>, 'reject_sales': 1, 'rejection_reason': reason },
        success: function(response){
            if(response.trim() == 'SUCCESS'){ $('#rejectModal').modal('hide'); location.reload(); } else { alert('Error: ' + response); }
        }
    });
}
</script>