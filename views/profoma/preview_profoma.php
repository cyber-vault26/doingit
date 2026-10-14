<?php
$profomaId = (int) $_GET['id'];
$signature_name     = $_GET['signature_name']     ?? '';
$signature_position = $_GET['signature_position'] ?? '';
$profoma  = selectProfomaById($conn, $profomaId);
$items    = selectProfomaItemsByProfomaId($conn, $profomaId);
$customer = selectSubsidiaryById($conn, (int)$profoma['customer_id']);
$co       = selectAllCompanyDetails($conn);
$isTSH    = strtoupper($profoma['currency_name']) === 'TSH';
$rate     = floatval($profoma['currency_rate']) ?: 1;
$invoiceHeading = !empty($profoma['invoice_heading']) ? htmlspecialchars($profoma['invoice_heading']) : '';
$paymentTerms   = !empty($profoma['payment_terms'])   ? htmlspecialchars($profoma['payment_terms'])   : '';
$phones = [];
foreach(['phone','phone2','phone3'] as $col){
    if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
}
$phone_str = implode(' / ', $phones);
$subtotal     = floatval($profoma['subtotal']);
$taxAmount    = floatval($profoma['tax_amount']);
$grandTotal   = floatval($profoma['total_amount']);
$displaySub   = $isTSH ? $subtotal   : $subtotal   / $rate;
$displayTax   = $isTSH ? $taxAmount  : $taxAmount  / $rate;
$displayGrand = $isTSH ? $grandTotal : $grandTotal / $rate;
?>

<div class="card card-light profoma-preview-wrap" id="profoma-preview-<?= $profomaId ?>">
    <div class="card-header">
        <h4 class="text-center">Proforma Invoice Preview</h4>
    </div>
    <div class="card-body">

        <div class="v-header">
            <div class="v-logo"><img src="/dist/img/logo.png" alt="Logo"></div>
            <div class="v-title">Proforma Invoice</div>
        </div>

        <div class="v-info-row">
            <div class="v-party">
                <div class="v-party-label">To:</div>
                <div class="v-party-value"><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></div>
                <?php if(!empty($customer['email'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($customer['email']) ?></div>
                <?php endif; ?>
                <?php if(!empty($customer['tin'])): ?>
                <div class="v-party-value">TIN: <?= htmlspecialchars($customer['tin']) ?></div>
                <?php endif; ?>
                <?php if(!empty($customer['vrn'])): ?>
                <div class="v-party-value">VRN: <?= htmlspecialchars($customer['vrn']) ?></div>
                <?php endif; ?>
                <?php if(!empty($customer['phone'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($customer['phone']) ?></div>
                <?php endif; ?>
                <?php if(!empty($customer['address'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($customer['address']) ?></div>
                <?php endif; ?>
            </div>
            <div class="v-meta">
                <div class="v-meta-item"><span class="v-meta-label">Issue Date:</span><span class="v-meta-value"><?= date('d-M-Y', strtotime($profoma['date_'])) ?></span></div>
                <div class="v-meta-item"><span class="v-meta-label">Expiry Date:</span><span class="v-meta-value"><?= date('d-M-Y', strtotime($profoma['expiry_date'])) ?></span></div>
                <div class="v-meta-item"><span class="v-meta-label">Reference #:</span><span class="v-meta-value"><?= htmlspecialchars($profoma['ref_no']) ?></span></div>
                <div class="v-meta-item"><span class="v-meta-label">Currency:</span><span class="v-meta-value"><?= htmlspecialchars($profoma['currency_name']) ?></span></div>
                <?php if(!$isTSH): ?>
                <div class="v-meta-item"><span class="v-meta-label">Rate:</span><span class="v-meta-value">1 <?= htmlspecialchars($profoma['currency_name']) ?> = <?= number_format($rate, 2) ?> TSH</span></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(!empty($invoiceHeading)): ?>
        <div class="v-description"><?= $invoiceHeading ?></div>
        <?php endif; ?>

        <?php if(is_array($items) && count($items) > 0): ?>
        <!--<div class="prod-grid">-->
            <?php foreach($items as $item):
                // $imgFile = './uploads/items/' . ($item['image_url'] ?? '');
                // $hasImage = !empty($item['image_url']) && file_exists($imgFile);
                // $itemTaxRateGrid = floatval($item['tax_rate'] ?? 0);
            ?>
            <!--<div class="prod-card">-->
            <!--    <div class="prod-img-wrap">-->
            <!--        <?php if($hasImage): ?>-->
            <!--            <img src="<?= htmlspecialchars($imgFile) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>">-->
            <!--        <?php else: ?>-->
            <!--            <div class="no-img">No Image</div>-->
            <!--        <?php endif; ?>-->
            <!--    </div>-->
            <!--    <div class="prod-card-info">-->
            <!--        <div class="prod-card-name"><?= htmlspecialchars($item['item_name']) ?></div>-->
            <!--        <div class="prod-card-meta">-->
            <!--            Qty: <?= number_format(floatval($item['quantity']), 2) ?>-->
            <!--            <?php if(!empty($item['unit_name'])): ?> · <?= htmlspecialchars($item['unit_name']) ?><?php endif; ?>-->
            <!--            <?php if($itemTaxRateGrid > 0): ?>-->
            <!--                <span class="vat-badge">VAT <?= number_format($itemTaxRateGrid, 0) ?>%</span>-->
            <!--            <?php endif; ?>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
            <?php endforeach; ?>
        <!--</div>-->
        <?php endif; ?>

        <table class="v-table">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:35%;">Description</th>
                    <th style="width:8%;">Qty</th>
                    <th style="width:8%;">Unit</th>
                    <th style="width:15%;">Unit Price (<?= htmlspecialchars($profoma['currency_name']) ?>)</th>
                    <th style="width:15%;" class="text-right">Amount</th>
                    <th style="width:15%;" class="text-right">Total (inc. VAT)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($items) && count($items) > 0):
                    $counter = 1;
                    foreach($items as $item):
                        $itemTaxRate = floatval($item['tax_rate'] ?? 0);
                        $itemPrice   = floatval($item['price']);
                        $itemAmount  = floatval($item['amount']);
                        $itemEqv     = floatval($item['eqv_amount']);
                        $itemVat     = ($itemTaxRate / 100) * $itemEqv;
                        $itemTotal   = $isTSH ? ($itemEqv + $itemVat) : (($itemEqv + $itemVat) / $rate);
                ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td>
                        <?= htmlspecialchars($item['item_name']) ?>
                        <?php if($itemTaxRate > 0): ?>
                            <span class="vat-badge">VAT <?= number_format($itemTaxRate, 0) ?>%</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format(floatval($item['quantity']), 2) ?></td>
                    <td><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                    <td><?= number_format($itemPrice, 2) ?></td>
                    <td class="text-right"><?= number_format($itemAmount, 2) ?></td>
                    <td class="text-right"><?= number_format($itemTotal, 2) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center;color:#999;padding:30px;">NO ITEMS FOUND.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="v-summary">
            <div class="v-summary-row">
                <div class="v-summary-label">Sub-total:</div>
                <div class="v-summary-value"><?= number_format($displaySub, 2) ?> <?= htmlspecialchars($profoma['currency_name']) ?></div>
            </div>
            <?php if($taxAmount > 0): ?>
            <div class="v-summary-row">
                <div class="v-summary-label">Total VAT:</div>
                <div class="v-summary-value"><?= number_format($displayTax, 2) ?> <?= htmlspecialchars($profoma['currency_name']) ?></div>
            </div>
            <?php endif; ?>
            <div class="v-summary-row v-grand">
                <div class="v-summary-label">Grand Total:</div>
                <div class="v-summary-value"><?= number_format($displayGrand, 2) ?> <?= htmlspecialchars($profoma['currency_name']) ?></div>
            </div>
            <?php if(!$isTSH): ?>
            <div class="v-summary-row">
                <div class="v-summary-label">Grand Total (TSH):</div>
                <div class="v-summary-value"><?= number_format($grandTotal, 2) ?> TSH</div>
            </div>
            <?php endif; ?>
        </div>

        <?php if(!empty($paymentTerms)): ?>
        <div class="payment-terms-section">
            <div class="payment-terms-title">PAYMENT TERMS</div>
            <?= nl2br($paymentTerms) ?>
        </div>
        <?php endif; ?>

        <div class="sig-rows">
            <div class="sig-row">
                <div class="sig-row-label">Prepared by:</div>
                <div class="sig-field"><span>Name:</span> <?= htmlspecialchars($signature_name) ?></div>
                <div class="sig-field"><span>Position:</span> <?= htmlspecialchars($signature_position) ?></div>
                <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
            </div>
        </div>

        <div class="v-footer">
            <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
            <?= htmlspecialchars($co['address'] ?? '') ?><br>
            Tel: <?= $phone_str ?> &nbsp;|&nbsp; Email: <?= htmlspecialchars($co['email'] ?? '') ?><br>
            Website: sembogroup.co.tz
        </div>

    </div>
    <div class="card-footer card-footer-actions no-print">
        <button class="btn btn-primary btn-sm" onclick="printVoucherPreview('profoma-preview-<?= $profomaId ?>')">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<script>
function printVoucherPreview(contentId) {
    var base = window.location.origin + '/';
    var content = document.getElementById(contentId).innerHTML;
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Proforma Invoice</title>');
    
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/primary-color.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/preview-profoma.css">');
    win.document.write('</head><body>');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function(){ win.print(); win.close(); }, 1200);
}
</script>