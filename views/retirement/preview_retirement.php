<style>
@media print {
    .main-footer, .btn, .card-footer-actions { display: none !important; }
}
.card-footer-actions { display: flex !important; flex-wrap: wrap; gap: 5px; }
.card-footer-actions .btn { display: inline-flex !important; width: auto !important; flex: none !important; }
</style>

<div class="card card-light">
    <div class="card-header">
        <h4 class="text-center">Retirement Preview</h4>
    </div>
    <div class="card-body">
        <?php
        $retirementId = (int) $_GET['id'];
        $retirement = selectRetirementById($conn, $retirementId);
        $items = selectRetirementItemsByRetirementId($conn, $retirementId);
        $requester = selectSubsidiaryById($conn, (int)$retirement['requester_id']);
        $isTSH = strtoupper($retirement['currency_name']) === 'TSH';
        $rate = floatval($retirement['currency_rate']) ?: 1;
        $status = $retirement['status'];

        $createdByName  = !empty($retirement['created_by_name'])  ? htmlspecialchars($retirement['created_by_name'])  : '-';
        $approvedByName = !empty($retirement['approved_by_name']) ? htmlspecialchars($retirement['approved_by_name']) : '-';
        $createdAt      = !empty($retirement['created_at'])  ? date('d-M-Y', strtotime($retirement['created_at']))  : '-';
        $approvedAt     = !empty($retirement['approved_at']) ? date('d-M-Y', strtotime($retirement['approved_at'])) : '-';
        ?>

        <style>
            .v-header { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; margin-bottom: 20px; }
            .v-logo { width: 130px; height: 130px; flex-shrink: 0; }
            .v-logo img { width: 250%; height: 200%; object-fit: contain; }
            .v-title { font-size: 40px; font-weight: bold; color: #d97c2b; text-align: right; flex: 1; }
            .v-info-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 20px 0; padding: 15px 0; border-bottom: 1px solid #ddd; }
            .v-party { flex: 1; }
            .v-party-label { font-weight: bold; font-size: 13px; color: #000; margin-bottom: 6px; margin-top: 10px; }
            .v-party-label:first-child { margin-top: 0; }
            .v-party-value { font-size: 13px; color: #333; font-weight: bold; margin-bottom: 3px; }
            .v-meta { flex: 1; text-align: right; }
            .v-meta-item { margin-bottom: 8px; font-size: 13px; }
            .v-meta-label { font-weight: bold; color: #000; }
            .v-meta-value { color: #333; margin-left: 5px; }
            .v-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .v-table thead tr { background: #d97c2b; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .v-table th { padding: 11px 8px; text-align: left; font-size: 12px; font-weight: bold; border: 1px solid #c06a1a; color: #fff; }
            .v-table td { padding: 9px 8px; border: 1px solid #ddd; font-size: 12px; }
            .v-table tbody tr:nth-child(even) { background: #fdf3e7; }
            .text-right { text-align: right; }
            .v-summary { margin-top: 15px; width: 100%; }
            .v-summary-row { display: flex; justify-content: flex-end; margin-bottom: 8px; }
            .v-summary-label { width: 200px; font-weight: bold; text-align: right; padding-right: 20px; font-size: 13px; color: #000; }
            .v-summary-value { width: 160px; text-align: right; font-size: 13px; }
            .v-grand { border-top: 2px solid #000; padding-top: 10px; margin-top: 5px; }
            .v-grand .v-summary-label, .v-grand .v-summary-value { font-weight: bold; font-size: 15px; }
            .v-footer { margin-top: 40px; font-size: 11px; color: #fff; text-align: center; background: #d97c2b; padding: 15px 20px; line-height: 1.7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .v-footer strong { color: #fff; font-size: 13px; }
            .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
            .status-pending { background: #fff3cd; color: #856404; }
            .status-approved { background: #d1e7dd; color: #0a3622; }
            .status-rejected { background: #f8d7da; color: #842029; }
            .sig-rows { margin-top: 40px; width: 100%; }
            .sig-row { display: flex; align-items: center; margin-bottom: 25px; gap: 20px; }
            .sig-row-label { width: 110px; font-weight: bold; font-size: 12px; flex-shrink: 0; }
            .sig-field { display: flex; align-items: center; gap: 6px; flex: 1; font-size: 12px; border-bottom: 1px solid #000; padding-bottom: 4px; }
            .sig-field span { font-weight: bold; white-space: nowrap; }
            .sig-field .sig-line { flex: 1; min-width: 60px; }
            .rejection-box { background: #f8d7da; border: 1px solid #f5c2c7; border-radius: 6px; padding: 15px; margin: 20px 0; }
            .rejection-box-title { font-weight: bold; color: #842029; margin-bottom: 5px; }
            .rejection-box-reason { color: #842029; font-size: 13px; }
            .receipt-link { color: #0d6efd; font-size: 11px; }
        </style>

        <div class="v-header">
            <div class="v-logo">
                <img src="../../dist/img/logo.png" alt="Logo">
            </div>
            <div class="v-title">Retirement</div>
        </div>

        <div class="v-info-row">
            <div class="v-party">
                <div class="v-party-label">Requester:</div>
                <div class="v-party-value"><?= htmlspecialchars($requester['name'] ?? '-') ?></div>
                <?php if(!empty($requester['email'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($requester['email']) ?></div>
                <?php endif; ?>
                <?php if(!empty($requester['phone'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($requester['phone']) ?></div>
                <?php endif; ?>
                <?php if(!empty($retirement['project_name'])): ?>
                <div class="v-party-label">Project:</div>
                <div class="v-party-value"><?= htmlspecialchars($retirement['project_name']) ?></div>
                <?php endif; ?>
                <div class="v-party-label">Advance Ref No:</div>
                <div class="v-party-value"><?= htmlspecialchars($retirement['advance_ref_no']) ?></div>
                <div class="v-party-label">Notes:</div>
                <div class="v-party-value"><?= nl2br(htmlspecialchars($retirement['notes'])) ?></div>
            </div>
            <div class="v-meta">
                <div class="v-meta-item">
                    <span class="v-meta-label">Ref No:</span>
                    <span class="v-meta-value"><?= htmlspecialchars($retirement['ref_no']) ?></span>
                </div>
                <div class="v-meta-item">
                    <span class="v-meta-label">Date:</span>
                    <span class="v-meta-value"><?= date('d-M-Y', strtotime($retirement['date_'])) ?></span>
                </div>
                <div class="v-meta-item">
                    <span class="v-meta-label">Currency:</span>
                    <span class="v-meta-value"><?= htmlspecialchars($retirement['currency_name']) ?></span>
                </div>
                <?php if(!$isTSH): ?>
                <div class="v-meta-item">
                    <span class="v-meta-label">Exchange Rate:</span>
                    <span class="v-meta-value">1 <?= htmlspecialchars($retirement['currency_name']) ?> = <?= number_format($rate, 2) ?> TSH</span>
                </div>
                <?php endif; ?>
                <div class="v-meta-item">
                    <span class="v-meta-label">Status:</span>
                    <span class="v-meta-value">
                        <span class="status-badge status-<?= $status ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <?php if($status === 'rejected' && !empty($retirement['rejection_reason'])): ?>
        <div class="rejection-box">
            <div class="rejection-box-title"><i class="fas fa-times-circle mr-1"></i> Rejected</div>
            <div class="rejection-box-reason"><?= nl2br(htmlspecialchars($retirement['rejection_reason'])) ?></div>
        </div>
        <?php endif; ?>

        <table class="v-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:28%;">Expense Item</th>
                    <th style="width:15%;">Date Spent</th>
                    <th style="width:20%;">Amount (TSH)</th>
                    <th style="width:32%;">Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(is_array($items) && count($items) > 0):
                    $counter = 1;
                    foreach($items as $item):
                ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= date('d-M-Y', strtotime($item['date_spent'])) ?></td>
                    <td><?= number_format(floatval($item['amount_spent_eqv']), 2) ?></td>
                    <td>
                        <?php if(!empty($item['receipt_path'])): ?>
                        <a href="../../uploads/vouchers/<?= htmlspecialchars($item['receipt_path']) ?>" target="_blank" class="receipt-link">
                            <i class="fas fa-paperclip mr-1"></i><?= htmlspecialchars($item['receipt_path']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">No receipt</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" style="text-align:center; color:#999; padding:30px;">NO ITEMS FOUND.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="v-summary">
            <div class="v-summary-row">
                <div class="v-summary-label">Amount Requested:</div>
                <div class="v-summary-value"><?= number_format(floatval($retirement['requested_amount']), 2) ?> <?= htmlspecialchars($retirement['currency_name']) ?></div>
            </div>
            <div class="v-summary-row v-grand">
                <div class="v-summary-label">Total Spent:</div>
                <div class="v-summary-value"><?= number_format(floatval($retirement['total_spent']), 2) ?> <?= htmlspecialchars($retirement['currency_name']) ?></div>
            </div>
            <?php
            $diff = floatval($retirement['requested_amount']) - floatval($retirement['total_spent']);
            if($diff > 0): ?>
            <div class="v-summary-row">
                <div class="v-summary-label" style="color:green;">Difference (Refund):</div>
                <div class="v-summary-value" style="color:green;">+<?= number_format($diff, 2) ?> <?= htmlspecialchars($retirement['currency_name']) ?></div>
            </div>
            <?php elseif($diff < 0): ?>
            <div class="v-summary-row">
                <div class="v-summary-label" style="color:red;">Difference (Excess):</div>
                <div class="v-summary-value" style="color:red;"><?= number_format($diff, 2) ?> <?= htmlspecialchars($retirement['currency_name']) ?></div>
            </div>
            <?php else: ?>
            <div class="v-summary-row">
                <div class="v-summary-label" style="color:#0d6efd;">Difference:</div>
                <div class="v-summary-value" style="color:#0d6efd;">0.00 (Exact)</div>
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
            <?php if($status === 'approved'): ?>
            <div class="sig-row">
                <div class="sig-row-label">Approved by:</div>
                <div class="sig-field"><span>Name:</span> <?= $approvedByName ?></div>
                <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
                <div class="sig-field"><span>Date:</span> <?= $approvedAt ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="v-footer">
            <strong>JADON LIMITED</strong><br>
            2nd Floor, Tevi Commercial Park, Bagamoyo Rd, Plot No.576, Mbezi Beach, P.O. Box 60163, Dar es Salaam, Tanzania.<br>
            Email: info@jadon.co.tz | Tel: +255621009936 / +255 737 829 077 | TIN: 138-847-209 | VRN: 40-034505-T
        </div>
        
        

    </div>
    <div class="">
    <div class="v-summary-row">
             <a href="./?page=preview_advance_cash_request&id=<?= $retirement['advance_cash_request_id'] ?>"  >
                  View Original Advance Cash Request (<?= htmlspecialchars($retirement['advance_ref_no']) ?>)
            </a>
            </div>
             </div>

    <div class="card-footer card-footer-actions">
        <?php if($status === 'pending'): ?>
        <button class="btn btn-success btn-sm mr-1" onclick="approveRetirement()"><i class="fas fa-check"></i> Approve</button>
        <button class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#rejectModal"><i class="fas fa-times"></i> Reject</button>
        <?php endif; ?>
        <button class="btn btn-info btn-sm" onclick="printRetirement()"><i class="fas fa-print"></i> Print</button>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Retirement</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Reason for Rejection <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" class="form-control" rows="4" placeholder="Enter reason for rejection..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="rejectRetirement()">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
function approveRetirement() {
    if(!confirm('Approve this retirement? This will create journal entries for each item.')) return;
    $.ajax({
        url: './controllers/procurement_controller.php',
        type: 'POST',
        data: { retirement_id: <?= $retirementId ?>, approve_retirement: 1 },
        success: function(response){
            if(response.trim() === 'SUCCESS'){
                location.reload();
            } else {
                alert('Error: ' + response);
            }
        }
    });
}

function rejectRetirement() {
    var reason = $('#rejection_reason').val().trim();
    if(!reason){ alert('Please enter a rejection reason.'); return; }
    $.ajax({
        url: './controllers/procurement_controller.php',
        type: 'POST',
        data: { retirement_id: <?= $retirementId ?>, rejection_reason: reason, reject_retirement: 1 },
        success: function(response){
            if(response.trim() === 'SUCCESS'){
                $('#rejectModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response);
            }
        }
    });
}

function printRetirement() {
    window.open('./processes/printings/retirement/print_retirement.php?id=<?= $retirementId ?>', '_blank');
}
</script>