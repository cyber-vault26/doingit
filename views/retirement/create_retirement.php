<?php
$advanceRequestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($advanceRequestId <= 0){ echo "<div class='alert alert-danger'>Invalid Request ID.</div>"; exit(); }
$advanceRequest = selectAdvanceCashRequestById($conn, $advanceRequestId);
if(!$advanceRequest){ echo "<div class='alert alert-danger'>Advance Cash Request not found.</div>"; exit(); }
if(!in_array($advanceRequest['status'], ['paid','partially_retired'])){ echo "<div class='alert alert-warning'>Only paid or partially retired advance cash requests can be retired. Current status: <strong>" . ucfirst($advanceRequest['status']) . "</strong></div>"; exit(); }
$requester = selectSubsidiaryById($conn, (int)$advanceRequest['requester_id']);
$isTSH = strtoupper($advanceRequest['currency_name']) === 'TSH';
$rate = floatval($advanceRequest['currency_rate']) ?: 1;

$subsidiaries = [];
$subsidiaries = selectAllSubsidiaries($conn);
                     

?>

<div class="card card-outline card-success">
    <div class="card-header">
        <h4 class="card-title"><i class="fas fa-undo mr-2"></i>Create Retirement</h4>
    </div>
    <form action="./controllers/procurement_controller.php" class="was-validated" method="post" enctype="multipart/form-data">
        <input type="hidden" name="advance_cash_request_id" value="<?= $advanceRequestId ?>">
        <input type="hidden" name="requester_id" value="<?= $advanceRequest['requester_id'] ?>">
        <input type="hidden" name="project_id" value="<?= $advanceRequest['project_id'] ?>">
        <input type="hidden" name="requested_amount" value="<?= $advanceRequest['total_amount'] ?>">
        <input type="hidden" name="refund_amount" id="refund_amount" value="0">
        <input type="hidden" name="excess_amount" id="excess_amount" value="0">

        <div class="card-body">

            <div class="row m-1">
                <div class="col-md-4 form-group">
                    <label>Ref No</label>
                    <input type="text" readonly name="ref_no" value="<?= generateRefNo($conn, 'advance_cash_retirements', 'RET/' . date('Y')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Retirement Date</label>
                    <input type="date" name="date_" max="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Requester</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($requester['name'] ?? '-') ?>" readonly>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-6 form-group">
                    <label>Currency</label>
                    <select name="currency" required class="form-control select2bs4-form" onchange="selectCurrency(this.value)">
                        <option value="">Select below</option>
                        <?php foreach(selectAllCurrency($conn) as $currency):
                            $selected = ($advanceRequest['currency'] == $currency['id']) ? 'selected' : ''; ?>
                            <option value="<?= $currency['id'] ?>" <?= $selected ?>><?= $currency['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Exchange Rate</label>
                    <div id="currencyx">
                        <input type="text" name="curr" id="curr_value" class="form-control" value="<?= number_format($rate, 2, '.', '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 form-group">
                    <label>Notes / Justification <span class="text-danger">*</span></label>
                    <textarea name="notes" class="form-control" required rows="3" style="resize:vertical;" placeholder="Describe how the money was spent..."></textarea>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 my-2">
                    <button type="button" class="btn btn-primary" onclick="addTableRow('retirement-table')">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                    <button type="button" class="btn btn-danger" onclick="removeTableRow('retirement-table')">
                        <i class="fas fa-minus mr-1"></i> Remove Row
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="retirement-table">
                    <thead class="bg-success text-white">
                        <tr>
                            <th style="width:25%;">Expense Item</th>
                            <th style="width:15%;">Date Spent</th>
                            <th style="width:15%;">Amount Spent</th>
                            <th style="width:15%;">Equiv Amount</th>
                            <th style="width:30%;">Receipt / Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="data-row">
                            <td>
                                <select name="item_id[]" required class="form-control select2bs4-form">
                                    <option value="">Select Expense Item</option>
                                    <?php foreach($subsidiaries as $sub): ?>
                                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="date" name="date_spent[]" max="<?= date('Y-m-d') ?>" class="form-control" required></td>
                            <td><input type="text" name="amount_used[]" class="form-control format-number ret_amount" oninput="calculateRetirementEqv(this); sumRetirementTotal();" placeholder="0.00" required></td>
                            <td><input type="text" name="amount_used_eqv[]" class="form-control ret_eqv" readonly></td>
                            <td><input type="file" name="receipt[]" class="form-control ret_file" accept=".jpg,.jpeg,.png,.pdf"></td>
                        </tr>
                    </tbody>
                </table>

                <table class="table table-sm table-bordered w-50 ml-auto mt-2">
                    <tr>
                        <td><strong>Amount Requested</strong></td>
                        <td><input type="text" class="form-control font-weight-bold bg-light" value="<?= number_format(floatval($advanceRequest['total_amount']), 2) ?>" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Total Spent</strong></td>
                        <td><input type="text" name="actual_amount" id="actual_amount" class="form-control font-weight-bold bg-light" readonly required></td>
                    </tr>
                    <tr>
                        <td><strong>Difference</strong></td>
                        <td><input type="text" id="difference_display" class="form-control font-weight-bold" readonly></td>
                    </tr>
                </table>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success btn-block font-weight-bold" name="create_retirement">
                <i class="fas fa-paper-plane mr-1"></i> Submit Retirement
            </button>
        </div>
    </form>
</div>

<script>
var requestedAmount = <?= floatval($advanceRequest['total_amount']) ?>;

function calculateRetirementEqv(input) {
    var currency = parseFloat(document.getElementById("curr_value").value.replace(/,/g, '')) || 1;
    var row = input.closest('tr');
    var actual = parseFloat(input.value.replace(/,/g, '')) || 0;
    var eqv = row.querySelector('.ret_eqv');
    if(eqv) eqv.value = (actual * currency).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function sumRetirementTotal() {
    var inputs = document.getElementsByClassName("ret_amount");
    var currency = parseFloat(document.getElementById("curr_value").value.replace(/,/g, '')) || 1;
    var sum = 0;
    for(var i = 0; i < inputs.length; i++){
        sum += parseFloat(inputs[i].value.replace(/,/g, '')) || 0;
    }
    var total = sum * currency;
    document.getElementById('actual_amount').value = total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    calculateRetirementDiff(total);
}

function calculateRetirementDiff(actual) {
    var diff = requestedAmount - actual;
    document.getElementById('refund_amount').value = 0;
    document.getElementById('excess_amount').value = 0;

    if(diff > 0){
        document.getElementById('difference_display').value = '+' + diff.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' (Refund)';
        document.getElementById('difference_display').className = 'form-control font-weight-bold text-success';
        document.getElementById('refund_amount').value = diff.toFixed(2);
    } else if(diff < 0){
        var excess = Math.abs(diff);
        document.getElementById('difference_display').value = '-' + excess.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' (Excess)';
        document.getElementById('difference_display').className = 'form-control font-weight-bold text-danger';
        document.getElementById('excess_amount').value = excess.toFixed(2);
    } else if(actual > 0){
        document.getElementById('difference_display').value = '0.00 (Exact)';
        document.getElementById('difference_display').className = 'form-control font-weight-bold text-info';
    }
}

var currDiv = document.getElementById('currencyx');
if(currDiv){
    new MutationObserver(function(){
        setTimeout(function(){
            var inputs = document.getElementsByClassName("ret_amount");
            for(var i = 0; i < inputs.length; i++) calculateRetirementEqv(inputs[i]);
            sumRetirementTotal();
        }, 100);
    }).observe(currDiv, {childList:true, subtree:true});
}

document.addEventListener('DOMContentLoaded', function(){
    selectCurrency(<?= (int)$advanceRequest['currency'] ?>);
    setTimeout(function(){
        document.getElementById('curr_value').value = '<?= number_format($rate, 2, '.', '') ?>';
    }, 600);
});
</script>