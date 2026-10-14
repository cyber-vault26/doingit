<?php
$batch   = (int) $_GET['batch'];
$type    = $conn->real_escape_string($_GET['type']);
$rows    = selectStockBatchesByType($conn, $type, null, null, null, null, $batch);
if(!$rows || !is_array($rows) || count($rows) === 0){
    echo "<div class='alert alert-danger'>Record not found.</div>";
    return;
}
$first       = $rows[0];
$ref_no      = $first['ref_no'];
$date        = date('d-M-Y', strtotime($first['date_']));
$supplier_id = $first['supplier_id'];
$store_id    = $first['store_id'];
$to_store_id = $first['to_store_id'] ?? null;
$po_id       = $first['purchase_order_id'];
$status      = $first['status'];
$created_by  = $first['created_by'];
$reviewed_by = $first['reviewed_by'];
$approved_by = $first['approved_by'];
$rejected_by = $first['rejected_by'];
$created_at  = !empty($first['created_at'])  ? date('d-M-Y', strtotime($first['created_at']))  : '-';
$reviewed_at = !empty($first['reviewed_at']) ? date('d-M-Y', strtotime($first['reviewed_at'])) : '-';
$approved_at = !empty($first['approved_at']) ? date('d-M-Y', strtotime($first['approved_at'])) : '-';
$rejected_at = !empty($first['rejected_at']) ? date('d-M-Y', strtotime($first['rejected_at'])) : '-';
$rejection_reason = $first['rejection_reason'] ?? '-';
$isGRN       = $type === 'in';
$isTransfer  = ($type === 'transfer-in' || $type === 'transfer-out');
$typeLabel   = $isGRN ? 'Goods Received Note' : ($isTransfer ? 'Stock Transfer Note' : 'Goods Delivery Note');
$voucherCode = $isGRN ? 'GRN' : ($isTransfer ? 'STN' : 'GDN');
$supplierLabel = $isGRN ? 'Supplier' : ($isTransfer ? 'Transfer From' : 'Issued To');
$storeLabel    = $isGRN ? 'Received To' : ($isTransfer ? 'Transfer To' : 'Issued From');
$supplier      = !$isTransfer ? selectSubsidiaryById($conn, $supplier_id) : null;
$storeData     = selectStockStoreById($conn, $store_id);
$toStoreData   = ($isTransfer && $to_store_id) ? selectStockStoreById($conn, $to_store_id) : null;
$createdUser   = $created_by  ? selectUserById($conn, $created_by)  : null;
$reviewedUser  = $reviewed_by ? selectUserById($conn, $reviewed_by) : null;
$approvedUser  = $approved_by ? selectUserById($conn, $approved_by) : null;
$rejectedUser  = $rejected_by ? selectUserById($conn, $rejected_by) : null;
$createdName   = $createdUser  ? htmlspecialchars($createdUser['names'])  : '-';
$reviewedName  = $reviewedUser ? htmlspecialchars($reviewedUser['names']) : '-';
$approvedName  = $approvedUser ? htmlspecialchars($approvedUser['names']) : '-';
$rejectedName  = $rejectedUser ? htmlspecialchars($rejectedUser['names']) : '-';
?>

<div class="card card-light">
    <div class="card-header">
        <h4 class="text-center"><?= $voucherCode ?> Preview</h4>
    </div>
    <div class="card-body" id="stock-batch-preview-<?= $batch ?>">

        <div class="v-header">
            <div class="v-logo">
                <img src="./dist/img/logo.png" alt="Logo">
            </div>
            <div class="v-title"><?= $typeLabel ?></div>
        </div>

        <div class="v-info-row">
            <div class="v-party">
                <?php if($isTransfer): ?>
                    <div class="v-party-label">Transfer From:</div>
                    <div class="v-party-value"><strong><?= htmlspecialchars($storeData['name'] ?? '-') ?></strong></div>
                    <div class="v-party-label">Transfer To:</div>
                    <div class="v-party-value"><strong><?= htmlspecialchars($toStoreData['name'] ?? '-') ?></strong></div>
                <?php else: ?>
                    <div class="v-party-label"><?= $supplierLabel ?>:</div>
                    <div class="v-party-value"><strong><?= htmlspecialchars($supplier['name'] ?? '-') ?></strong></div>
                    <?php if(!empty($supplier['phone'])): ?>
                    <div class="v-party-value"><?= htmlspecialchars($supplier['phone']) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($supplier['email'])): ?>
                    <div class="v-party-value"><?= htmlspecialchars($supplier['email']) ?></div>
                    <?php endif; ?>
                    <div class="v-party-label"><?= $storeLabel ?>:</div>
                    <div class="v-party-value"><strong><?= htmlspecialchars($storeData['name'] ?? '-') ?></strong></div>
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

        <?php
        // Pre-fetch item data once for the grid + table
        $itemsData = [];
        foreach($rows as $row){
            $itemsData[] = !empty($row['item_id']) ? selectMinSubById($conn, (int)$row['item_id']) : null;
        }
        ?>

        <div class="prod-grid">
            <?php foreach($rows as $i => $row):
                $itemData = $itemsData[$i];
                $imgPath  = './uploads/items/' . ($itemData['image_url'] ?? '');
                $hasImage = !empty($itemData['image_url']) && file_exists($imgPath);
                $vat_rate = floatval($row['tax_rate'] ?? 0);
                $qty      = floatval($row['quantity'] ?? 0);
                $unitData = !empty($row['unit']) ? selectUnitById($conn, (int)$row['unit']) : null;
            ?>
            <div class="prod-card">
                <div class="prod-img-wrap">
                    <?php if($hasImage): ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($itemData['name'] ?? '') ?>">
                    <?php else: ?>
                        <div class="no-img">No Image</div>
                    <?php endif; ?>
                </div>
                <div class="prod-card-info">
                    <div class="prod-card-name"><?= htmlspecialchars($itemData['name'] ?? '-') ?></div>
                    <div class="prod-card-meta">
                        Qty: <?= number_format($qty, 2) ?>
                        <?php if(!empty($unitData['name'])): ?> · <?= htmlspecialchars($unitData['name']) ?><?php endif; ?>
                        <?php if($vat_rate > 0): ?>
                            <span class="status-badge status-reviewed"><?= $vat_rate ?>% VAT</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
                foreach($rows as $i => $row):
                    $num++;
                    $itemData  = $itemsData[$i];
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
            <tfoot>
                <tr class="subtotal-row">
                    <td colspan="5" style="text-align:right;font-weight:bold;">Sub Total</td>
                    <td style="font-weight:bold;"><?= number_format($grand_subtotal, 2) ?></td>
                </tr>
                <tr class="vat-row">
                    <td colspan="5" style="text-align:right;font-weight:bold;">Total VAT</td>
                    <td style="font-weight:bold;"><?= number_format($grand_vat, 2) ?></td>
                </tr>
                <tr class="grand-row">
                    <td colspan="5" style="text-align:right;font-weight:bold;">Grand Total</td>
                    <td style="font-weight:bold;"><?= number_format($grand_subtotal + $grand_vat, 2) ?></td>
                </tr>
            </tfoot>
        </table>

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
        <div class="alert alert-danger mt-3">
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
    </div>
    <div class="card-footer card-footer-actions no-print">
        <?php if($status === 'pending'): ?>
            <button class="btn btn-info btn-sm mr-1" onclick="markAsReviewed()">Mark as Reviewed</button>
        <?php endif; ?>
        <?php if($status === 'reviewed'): ?>
            <button class="btn btn-success btn-sm mr-1" onclick="markAsApproved()">Approve</button>
            <button class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#rejectModal">Reject</button>
        <?php endif; ?>
        <?php if(!in_array($status, ['approved'])): ?>
            <a class="btn btn-warning btn-sm mr-1" href="./?page=<?= $type === 'in' ? 'edit_grn' : ($type === 'out' ? 'edit_gdn' : 'edit_tranfered_stocks') ?>&batch=<?= $batch ?>&type=<?= $type ?>">
                <i class="fas fa-edit"></i> Amend
            </a>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm mr-1" onclick="printVoucherPreview('stock-batch-preview-<?= $batch ?>')">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<div class="modal fade no-print" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject <?= $voucherCode ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Reason for Rejection <span class="text-danger">*</span></label>
                <textarea id="rejection_reason_input" class="form-control" rows="4" placeholder="Enter reason for rejection..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="rejectStock()">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
function markAsReviewed() {
    if(!confirm('Mark this <?= $voucherCode ?> as reviewed?')) return;
    $.post('./controllers/stock_controllers.php', {
        review_stock: 1,
        'selected_batches[]': <?= $batch ?>
    }, function(response){
        if(response.trim() === 'SUCCESS'){ location.reload(); } else { alert('Error: ' + response); }
    });
}
function markAsApproved() {
    if(!confirm('Approve this <?= $voucherCode ?>?')) return;
    $.post('./controllers/stock_controllers.php', {
        approve_stock: 1,
        'selected_batches[]': <?= $batch ?>
    }, function(response){
        if(response.trim() === 'SUCCESS'){ location.reload(); } else { alert('Error: ' + response); }
    });
}
function rejectStock() {
    const reason = $('#rejection_reason_input').val().trim();
    if(!reason){ alert('Please enter a reason for rejection.'); return; }
    if(!confirm('Are you sure you want to reject this <?= $voucherCode ?>?')) return;
    $.post('./controllers/stock_controllers.php', {
        reject_stock: 1,
        'selected_batches[]': <?= $batch ?>,
        rejection_reason: reason
    }, function(response){
        if(response.trim() === 'SUCCESS'){ $('#rejectModal').modal('hide'); location.reload(); } else { alert('Error: ' + response); }
    });
}

function printVoucherPreview(contentId) {
    var base = window.location.origin + '/';
    var content = document.getElementById(contentId).innerHTML;
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Stock Voucher</title>');
    win.document.write('<link rel="stylesheet" href="' + base + 'plugins/fontawesome-free/css/all.min.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/primary-color.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/preview-stock-batch.css">');
    win.document.write('</head><body>');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function(){ win.print(); win.close(); }, 1200);
}
</script>