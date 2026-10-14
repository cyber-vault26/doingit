<?php
require_once "../../functions/profoma_functions.php";
require_once "../../functions/subsidiary_functions.php";
require_once "../../configs.php";
$conn = openConn();
$profomaId = (int) $_GET['id'];
$signature_name = $_GET['signature_name'] ?? '';
$signature_position = $_GET['signature_position'] ?? '';
$profoma = selectProfomaById($conn, $profomaId);
$items = selectProfomaItemsByProfomaId($conn, $profomaId);
$customer = selectSubsidiaryById($conn, (int)$profoma['customer_id']);
$isTSH = strtoupper($profoma['currency_name']) === 'TSH';
$rate = floatval($profoma['currency_rate']) ?: 1;
$invoiceHeading = !empty($profoma['invoice_heading']) ? htmlspecialchars($profoma['invoice_heading']) : '';
$paymentTerms = !empty($profoma['payment_terms']) ? htmlspecialchars($profoma['payment_terms']) : '';
?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.4; }
        .invoice-header { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; margin-bottom: 20px; }
        .invoice-header-logo { width: 200px; height: 150px; flex-shrink: 0; }
        .invoice-header-logo img { width: 100%; height: 100%; object-fit: contain; }
        .invoice-main-title { font-size: 48px; font-weight: bold; color: #d97c2b; text-align: right; letter-spacing: 1px; flex: 1; }
        .info-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 20px 0; padding: 15px 0; border-bottom: 1px solid #ddd; }
        .bill-to-section { flex: 1; }
        .bill-to-label { font-weight: bold; font-size: 13px; color: #000; margin-bottom: 5px; }
        .bill-to-value { font-size: 13px; color: #333; font-weight: bold; margin-bottom: 2px; }
        .meta-vertical-list { flex: 1; text-align: right; }
        .meta-item { margin-bottom: 8px; font-size: 13px; }
        .meta-label { font-weight: bold; color: #000; }
        .meta-value { color: #333; margin-left: 5px; }
        .invoice-description { margin: 20px 0; font-size: 13px; line-height: 1.5; color: #333; }
        .invoice-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .invoice-table thead tr { background: #d97c2b; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .invoice-table th { color: #fff; padding: 12px 8px; text-align: left; font-size: 13px; font-weight: bold; border: 1px solid #c06a1a; }
        .invoice-table td { padding: 10px 8px; border: 1px solid #ddd; font-size: 12px; }
        .invoice-table tbody tr:nth-child(even) { background: #fdf3e7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .text-right { text-align: right; }
        .vat-badge { display: inline-block; background: #d97c2b; color: #fff; font-size: 9px; font-weight: bold; padding: 1px 4px; border-radius: 3px; margin-left: 4px; vertical-align: middle; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .summary-section { margin-top: 20px; width: 100%; }
        .summary-row { display: flex; justify-content: flex-end; margin-bottom: 8px; }
        .summary-label { width: 200px; font-weight: bold; text-align: right; padding-right: 20px; color: #000; font-size: 13px; }
        .summary-value { width: 150px; text-align: right; font-size: 13px; }
        .grand-total { font-size: 16px; border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; }
        .grand-total .summary-label, .grand-total .summary-value { font-weight: bold; }
        .payment-terms-section { margin-top: 30px; font-size: 12px; line-height: 1.6; }
        .payment-terms-title { font-weight: bold; margin-bottom: 5px; color: #000; font-size: 14px; }
        .signature-section { margin-top: 50px; display: flex; flex-direction: column; align-items: flex-start; width: 300px; }
        .signature-line { width: 100%; border-top: 2px solid #000; margin-bottom: 10px; }
        .signature-name { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .signature-position { font-size: 12px; color: #333; }
        .invoice-footer { background: #d97c2b; color: #fff; padding: 15px 20px; text-align: center; font-size: 11px; line-height: 1.7; margin-top: 40px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .invoice-footer strong { color: #fff; font-size: 13px; }
        @media print { body { padding: 10px; } }

        /* ── Product image grid (added) ── */
        .prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; margin-bottom: 20px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .prod-card { border: 1px solid #e5e5e5; background: #fff; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .prod-img-wrap { width: 100%; aspect-ratio: 1 / 1; background: #f7f7f7; overflow: hidden; }
        .prod-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .prod-img-wrap .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #bbb; background: #f5f5f5; }
        .prod-card-info { padding: 8px; }
        .prod-card-name { font-size: 12px; font-weight: bold; color: #222; line-height: 1.3; margin-bottom: 3px; }
        .prod-card-meta { font-size: 11px; color: #888; }
    </style>
 
<div class="invoice-header">
    <div class="invoice-header-logo"><img src="../../dist/img/logo.png" alt="Company Logo"></div>
    <div class="invoice-main-title">Proforma Invoice</div>
</div>
<div class="info-row">
    <div class="bill-to-section">
        <div class="bill-to-label">To:</div>
        <div class="bill-to-value"><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></div>
        <?php if(!empty($customer['email'])): ?><div class="bill-to-value"><?= htmlspecialchars($customer['email']) ?></div><?php endif; ?> 
        <?php if(!empty($customer['tin'])): ?><div class="bill-to-value">TIN: <?= htmlspecialchars($customer['tin']) ?></div><?php endif; ?> 
        <?php if(!empty($customer['vrn'])): ?><div class="bill-to-value">VRN: <?= htmlspecialchars($customer['vrn']) ?></div><?php endif; ?> 
        <?php if(!empty($customer['phone'])): ?><div class="bill-to-value"><?= htmlspecialchars($customer['phone']) ?></div><?php endif; ?>
        <?php if(!empty($customer['address'])): ?><div class="bill-to-value"><?= htmlspecialchars($customer['address']) ?></div><?php endif; ?>
    </div>
    <div class="meta-vertical-list">
        <div class="meta-item"><span class="meta-label">Issue Date:</span><span class="meta-value"><?= date('d-M-Y', strtotime($profoma['date_'])) ?></span></div>
        <div class="meta-item"><span class="meta-label">Expiry Date:</span><span class="meta-value"><?= date('d-M-Y', strtotime($profoma['expiry_date'])) ?></span></div>
        <div class="meta-item"><span class="meta-label">Reference #:</span><span class="meta-value"><?= htmlspecialchars($profoma['ref_no']) ?></span></div>
        <div class="meta-item"><span class="meta-label">Currency:</span><span class="meta-value"><?= htmlspecialchars($profoma['currency_name']) ?></span></div>
        <?php if(!$isTSH): ?>
        <div class="meta-item"><span class="meta-label">Exchange Rate:</span><span class="meta-value">1 <?= htmlspecialchars($profoma['currency_name']) ?> = <?= number_format($rate, 2) ?> TSH</span></div>
        <?php endif; ?>
    </div>
</div>
<?php if(!empty($invoiceHeading)): ?>
<div class="invoice-description"><?= $invoiceHeading ?></div>
<?php endif; ?>

<!-- Product image grid (added) -->
<?php if(is_array($items) && count($items) > 0): ?>
<div class="prod-grid">
    <?php foreach($items as $item):
        $imgFile = '../../uploads/items/' . ($item['image_url'] ?? '');
        $hasImage = !empty($item['image_url']) && file_exists($imgFile);
        $itemTaxRateGrid = floatval($item['tax_rate'] ?? 0);
    ?>
    <div class="prod-card">
        <div class="prod-img-wrap">
            <?php if($hasImage): ?>
                <img src="<?= htmlspecialchars($imgFile) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>">
            <?php else: ?>
                <div class="no-img">No Image</div>
            <?php endif; ?>
        </div>
        <div class="prod-card-info">
            <div class="prod-card-name"><?= htmlspecialchars($item['item_name']) ?></div>
            <div class="prod-card-meta">
                Qty: <?= number_format(floatval($item['quantity']), 2) ?>
                <?php if(!empty($item['unit_name'])): ?> · <?= htmlspecialchars($item['unit_name']) ?><?php endif; ?>
                <?php if($itemTaxRateGrid > 0): ?>
                    <span class="vat-badge">VAT <?= number_format($itemTaxRateGrid, 0) ?>%</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<table class="invoice-table">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:35%;">Description</th>
            <th style="width:8%;">Qty</th>
            <th style="width:8%;">Unit</th>
            <th style="width:15%;">Unit Price (<?= htmlspecialchars($profoma['currency_name']) ?>)</th>
            <th style="width:15%;" class="text-right">Amount (<?= htmlspecialchars($profoma['currency_name']) ?>)</th>
            <th style="width:15%;" class="text-right">Total Amount (<?= htmlspecialchars($profoma['currency_name']) ?>)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if(is_array($items) && count($items) > 0):
            $counter = 1;
            foreach($items as $item):
                $itemTaxRate = floatval($item['tax_rate'] ?? 0);
                $itemPrice = floatval($item['price']);
                $itemAmount = floatval($item['amount']);
                $itemEqv = floatval($item['eqv_amount']);
                $itemVat = ($itemTaxRate / 100) * $itemEqv;
                $itemTotal = $isTSH ? ($itemEqv + $itemVat) : (($itemEqv + $itemVat) / $rate);
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
        <tr><td colspan="7" style="text-align:center; color:#999; padding:30px;">NO ITEMS FOUND.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
$subtotal = floatval($profoma['subtotal']);
$taxAmount = floatval($profoma['tax_amount']);
$grandTotal = floatval($profoma['total_amount']);
$displaySubtotal = $isTSH ? $subtotal : $subtotal / $rate;
$displayTax = $isTSH ? $taxAmount : $taxAmount / $rate;
$displayGrand = $isTSH ? $grandTotal : $grandTotal / $rate;
?>
<div class="summary-section">
    <div class="summary-row">
        <div class="summary-label">Sub-total:</div>
        <div class="summary-value"><?= number_format($displaySubtotal, 2) ?> <?= htmlspecialchars($profoma['currency_name']) ?></div>
    </div>
    <?php if($taxAmount > 0): ?>
    <div class="summary-row">
        <div class="summary-label">Total VAT:</div>
        <div class="summary-value"><?= number_format($displayTax, 2) ?> <?= htmlspecialchars($profoma['currency_name']) ?></div>
    </div>
    <?php endif; ?>
    <div class="summary-row grand-total">
        <div class="summary-label">Grand Total:</div>
        <div class="summary-value"><?= number_format($displayGrand, 2) ?> <?= htmlspecialchars($profoma['currency_name']) ?></div>
    </div>
    <?php if(!$isTSH): ?>
    <div class="summary-row">
        <div class="summary-label">Grand Total (TSH):</div>
        <div class="summary-value"><?= number_format($grandTotal, 2) ?> TSH</div>
    </div>
    <?php endif; ?>
</div>
<?php if(!empty($paymentTerms)): ?>
<div class="payment-terms-section">
    <div class="payment-terms-title">PAYMENT TERMS</div>
    <?= nl2br($paymentTerms) ?>
</div>
<?php endif; ?>
<div class="signature-section">
    <div class="signature-line"></div>
    <div class="signature-name"><?= htmlspecialchars($signature_name) ?></div>
    <div class="signature-position"><?= htmlspecialchars($signature_position) ?></div>
</div>
<div class="invoice-footer">
   <strong>MANGOWO TRADING AND SUPPLIES</strong><br>
            Kariakoo, Narung'ombe Na Sikukuu, P.O. Box xxx, Dar es Salaam, Tanzania.<br>
            Tel: +255 785 182 121 / +255 759 266 699 / +255 655 606 676
</div>
<script>
   window.onload = function() {
    window.print();
};
window.onafterprint = function() {
    window.close();
};
</script>