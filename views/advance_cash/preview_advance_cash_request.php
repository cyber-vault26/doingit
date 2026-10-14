<style>
@media print {
    .main-footer, .btn, .card-footer-actions { display: none !important; }
}
.card-footer-actions { display: flex !important; flex-wrap: wrap; gap: 5px; }
.card-footer-actions .btn { display: inline-flex !important; width: auto !important; flex: none !important; }
</style>

<div class="card card-light">
    <div class="card-header">
        <h4 class="text-center">Advance Cash Request Preview</h4>
    </div>
    <div class="card-body">
        <?php
        $advCashRequestId = (int) $_GET['id'];
        $request = selectAdvanceCashRequestById($conn, $advCashRequestId);
        $items = selectAdvanceCashRequestItemsByRequestId($conn, $advCashRequestId);
        $requester = selectSubsidiaryById($conn, (int)$request['requester_id']);
        $isTSH = strtoupper($request['currency_name']) === 'TSH';
        $rate = floatval($request['currency_rate']) ?: 1;
        $status = $request['status'];

        $createdByName  = !empty($request['created_by_name'])  ? htmlspecialchars($request['created_by_name'])  : '-';
        $approvedByName = !empty($request['approved_by_name']) ? htmlspecialchars($request['approved_by_name']) : '-';
        $paidByName     = !empty($request['paid_by_name'])     ? htmlspecialchars($request['paid_by_name'])     : '-';

        $createdAt  = !empty($request['created_at'])  ? date('d-M-Y', strtotime($request['created_at']))  : '-';
        $approvedAt = !empty($request['approved_at']) ? date('d-M-Y', strtotime($request['approved_at'])) : '-';
        $paidAt     = !empty($request['paid_at'])     ? date('d-M-Y', strtotime($request['paid_at']))     : '-';
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
            .status-paid { background: #cfe2ff; color: #084298; }
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
        </style>

        <div class="v-header">
            <div class="v-logo">
                <img src="../../dist/img/logo.png" alt="Logo">
            </div>
            <div class="v-title">Advance Cash Request</div>
        </div>

        <div class="v-info-row">
            <div class="v-party">
                <div class="v-party-label">Requested By:</div>
                <div class="v-party-value"><?= htmlspecialchars($requester['name'] ?? '-') ?></div>
                <?php if(!empty($requester['email'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($requester['email']) ?></div>
                <?php endif; ?>
                <?php if(!empty($requester['phone'])): ?>
                <div class="v-party-value"><?= htmlspecialchars($requester['phone']) ?></div>
                <?php endif; ?>
                <?php if(!empty($request['project_name'])): ?>
                <div class="v-party-label">Project:</div>
                <div class="v-party-value"><?= htmlspecialchars($request['project_name']) ?></div>
                <?php endif; ?>
                <div class="v-party-label">Reason:</div>
                <div class="v-party-value"><?= nl2br(htmlspecialchars($request['reason'])) ?></div>
            </div>
            <div class="v-meta">
                <div class="v-meta-item">
                    <span class="v-meta-label">Ref No:</span>
                    <span class="v-meta-value"><?= htmlspecialchars($request['ref_no']) ?></span>
                </div>
                <div class="v-meta-item">
                    <span class="v-meta-label">Date:</span>
                    <span class="v-meta-value"><?= date('d-M-Y', strtotime($request['date_'])) ?></span>
                </div>
                <div class="v-meta-item">
                    <span class="v-meta-label">Type:</span>
                    <span class="v-meta-value"><?= ucfirst($request['type']) ?></span>
                </div>
                <div class="v-meta-item">
                    <span class="v-meta-label">Currency:</span>
                    <span class="v-meta-value"><?= htmlspecialchars($request['currency_name']) ?></span>
                </div>
                <?php if(!$isTSH): ?>
                <div class="v-meta-item">
                    <span class="v-meta-label">Exchange Rate:</span>
                    <span class="v-meta-value">1 <?= htmlspecialchars($request['currency_name']) ?> = <?= number_format($rate, 2) ?> TSH</span>
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

        <?php if($status === 'rejected' && !empty($request['rejection_reason'])): ?>
        <div class="rejection-box">
            <div class="rejection-box-title"><i class="fas fa-times-circle mr-1"></i> Rejected</div>
            <div class="rejection-box-reason"><?= nl2br(htmlspecialchars($request['rejection_reason'])) ?></div>
        </div>
        <?php endif; ?>

        <table class="v-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:30%;">Item</th>
                    <th style="width:20%;">Amount (<?= htmlspecialchars($request['currency_name']) ?>)</th>
                    <th style="width:20%;">Equiv Amount (TSH)</th>
                    <th style="width:13%;">Bank</th>
                    <th style="width:12%;">Account</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(is_array($items) && count($items) > 0):
                    $counter = 1;
                    foreach($items as $item):
                        $displayAmount = $isTSH ? floatval($item['amount']) : floatval($item['amount']) / $rate;
                ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= number_format($displayAmount, 2) ?></td>
                    <td><?= number_format(floatval($item['amount_eqv']), 2) ?></td>
                    <td><?= htmlspecialchars($item['bank']) ?></td>
                    <td><?= htmlspecialchars($item['account']) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center; color:#999; padding:30px;">NO ITEMS FOUND.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="v-summary">
            <div class="v-summary-row v-grand">
                <div class="v-summary-label">Total Amount:</div>
                <div class="v-summary-value"><?= number_format(floatval($request['total_amount']), 2) ?> <?= htmlspecialchars($request['currency_name']) ?></div>
            </div>
            <?php if(!$isTSH): ?>
            <div class="v-summary-row">
                <div class="v-summary-label">Total (TSH):</div>
                <div class="v-summary-value"><?= number_format(floatval($request['total_amount']), 2) ?> TSH</div>
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
            <?php if(in_array($status, ['approved', 'paid'])): ?>
            <div class="sig-row">
                <div class="sig-row-label">Approved by:</div>
                <div class="sig-field"><span>Name:</span> <?= $approvedByName ?></div>
                <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
                <div class="sig-field"><span>Date:</span> <?= $approvedAt ?></div>
            </div>
            <?php endif; ?>
            <?php if($status === 'paid'): ?>
            <div class="sig-row">
                <div class="sig-row-label">Paid by:</div>
                <div class="sig-field"><span>Name:</span> <?= $paidByName ?></div>
                <div class="sig-field"><span>Signature:</span><div class="sig-line"></div></div>
                <div class="sig-field"><span>Date:</span> <?= $paidAt ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="v-footer">
            <strong>JADON LIMITED</strong><br>
            2nd Floor, Tevi Commercial Park, Bagamoyo Rd, Plot No.576, Mbezi Beach, P.O. Box 60163, Dar es Salaam, Tanzania.<br>
            Email: info@jadon.co.tz | Tel: +255621009936 / +255 737 829 077 | TIN: 138-847-209 | VRN: 40-034505-T
        </div>

    </div>

    <div class="card-footer card-footer-actions">
        <?php if($status === 'pending'): ?>
        <button class="btn btn-success btn-sm mr-1" onclick="approveRequest()"><i class="fas fa-check"></i> Approve Request</button>
        <button class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#rejectModal"><i class="fas fa-times"></i> Reject Request</button>
        <?php elseif($status === 'approved'): ?>
        <button class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#payModal"><i class="fas fa-money-bill-wave"></i> Mark as Paid</button>
        <?php endif; ?>
        <button class="btn btn-info btn-sm" onclick="printRequest()"><i class="fas fa-print"></i> Print</button>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Reason for Rejection <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" class="form-control" rows="4" placeholder="Enter reason for rejection..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="rejectRequest()">Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave mr-1"></i> Process Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pay_requester_id" value="<?= $request['requester_id'] ?>">
                <input type="hidden" id="pay_currency" value="<?= $request['currency'] ?>">
                <input type="hidden" id="pay_currency_rate" value="<?= $request['currency_rate'] ?>">
                <input type="hidden" id="pay_ref_no" value="<?= htmlspecialchars($request['ref_no']) ?>">
                <input type="hidden" id="pay_project_id" value="<?= $request['project_id'] ?>">
                <div class="form-group">
                    <label>Requester (Debit Account)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($requester['name'] ?? '-') ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Amount</label>
                    <input type="text" class="form-control" id="pay_amount" value="<?= number_format(floatval($request['total_amount']), 2) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" id="pay_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Credit Account (Paying From) <span class="text-danger">*</span></label>
                    <select id="pay_cr_account" required class="form-control select2bs4-form">
                        <option value="">Choose below</option>
                        <?php
                        $sql = "SELECT * FROM subsidiaries WHERE ledger_id IN (32, 77, 33) AND deleted_at IS NULL ORDER BY name ASC";
                        $query = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($query)){
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description / Narration</label>
                    <textarea id="pay_description" class="form-control" rows="3" placeholder="Payment narration..."><?= htmlspecialchars($request['reason']) ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitPayment()"><i class="fas fa-paper-plane mr-1"></i> Submit Payment</button>
            </div>
        </div>
    </div>
</div>

<script>
function approveRequest() {
    if(!confirm('Approve this advance cash request?')) return;
    $.ajax({
        url: './controllers/procurement_controller.php',
        type: 'POST',
        data: { advance_cash_request_id: <?= $advCashRequestId ?>, approve_advance_cash_request: 1 },
        success: function(response){ location.reload(); }
    });
}

function rejectRequest() {
    var reason = $('#rejection_reason').val().trim();
    if(!reason){ alert('Please enter a rejection reason.'); return; }
    $.ajax({
        url: './controllers/procurement_controller.php',
        type: 'POST',
        data: { advance_cash_request_id: <?= $advCashRequestId ?>, rejection_reason: reason, reject_advance_cash_request: 1 },
        success: function(response){
            $('#rejectModal').modal('hide');
            location.reload();
        }
    });
}

function submitPayment() {
    var cr = $('#pay_cr_account').val();
    var date = $('#pay_date').val();
    var description = $('#pay_description').val().trim();
    if(!cr){ alert('Please select a credit account.'); return; }
    if(!date){ alert('Please select a date.'); return; }
    if(!description){ alert('Please enter a narration.'); return; }
    if(!confirm('Submit payment for this advance cash request?')) return;
    $.ajax({
        url: './controllers/procurement_controller.php',
        type: 'POST',
        data: {
            advance_cash_request_id: <?= $advCashRequestId ?>,
            pay_advance_cash_request: 1,
            cr_account: cr,
            dr_account: $('#pay_requester_id').val(),
            amount: $('#pay_amount').val().replace(/,/g, ''),
            date_: date,
            description: description,
            currency: $('#pay_currency').val(),
            currency_rate: $('#pay_currency_rate').val(),
            ref_no: $('#pay_ref_no').val(),
            project_id: $('#pay_project_id').val()
        },
        success: function(response){
            if(response.trim() === 'SUCCESS'){
                $('#payModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response);
            }
        }
    });
}

function printRequest() {
    window.open('./processes/printings/advance_cash_request/print_advance_cash_request.php?id=<?= $advCashRequestId ?>', '_blank');
}
</script>