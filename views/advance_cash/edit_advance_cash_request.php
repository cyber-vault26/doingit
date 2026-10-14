<?php
$advId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($advId <= 0) header('Location: ../?page=advance_cash_request');
$editRequest = selectAdvanceCashRequestById($conn, $advId);
$editItems   = selectAdvanceCashRequestItemsByRequestId($conn, $advId);
$editRate    = floatval($editRequest['currency_rate']) ?: 1;
?>

<div class="card card-outline card-info">
    <div class="card-header">
        <h4 class="card-title"><i class="fas fa-edit mr-2"></i>Amend Advance Cash Request</h4>
    </div>
    <form action="./controllers/procurement_controller.php" class="was-validated" method="post">
        <input type="hidden" name="advance_cash_request_id" value="<?= $advId ?>">
        <div class="card-body">

            <div class="row m-1">
                <div class="col-md-4 form-group">
                    <label>Ref No</label>
                    <input type="text" readonly name="ref_no" value="<?= htmlspecialchars($editRequest['ref_no']) ?>" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Request Date</label>
                    <input type="date" name="date_" max="<?= date('Y-m-d') ?>" class="form-control" value="<?= date('Y-m-d', strtotime($editRequest['date_'])) ?>" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Type of Request</label>
                    <select name="type" class="form-control" required>
                        <option value="">Select Below</option>
                        <option value="normal" <?= $editRequest['type'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="travel" <?= $editRequest['type'] === 'travel' ? 'selected' : '' ?>>Travel</option>
                    </select>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-6 form-group">
                    <label>Requester</label>
                    <select name="requester_id" required class="select2-form select2bs4-form form-control">
                        <option value="">Select Below</option>
                        <?php
                            $staffList = selectSubsidiaryByType($conn, 'staff');
                            if($staffList && is_array($staffList)){
                                foreach($staffList as $staff){
                                    $selected = ($editRequest['requester_id'] == $staff['id']) ? 'selected' : '';
                                    echo "<option value='{$staff['id']}' {$selected}>{$staff['name']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Project</label>
                    <select name="project_id" required class="select2-form select2bs4-form form-control">
                        <option value="">Select Project</option>
                        <option value="0" <?= ($editRequest['project_id'] == 0) ? 'selected' : '' ?>>No Specific Project</option>
                        <?php
                            $projects = listProjectsByStatus($conn, 'active');
                            if($projects && is_array($projects)){
                                foreach($projects as $project){
                                    $selected = ($editRequest['project_id'] == $project['id']) ? 'selected' : '';
                                    echo "<option value='{$project['id']}' {$selected}>{$project['name']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-6 form-group">
                    <label>Currency</label>
                    <select name="currency" required class="form-control select2bs4-form" onchange="selectCurrency(this.value)">
                        <option value="">Select below</option>
                        <?php
                            $currencies = selectAllCurrency($conn);
                            foreach($currencies as $currency){
                                $selected = ($editRequest['currency'] == $currency['id']) ? 'selected' : '';
                                echo "<option value='{$currency['id']}' {$selected}>{$currency['name']}</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Exchange Rate</label>
                    <div id="currencyx">
                        <input type="text" name="curr" id="curr_value" class="form-control" value="<?= number_format($editRate, 2, '.', '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 form-group">
                    <label>Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" required rows="3" style="resize: vertical;" placeholder="Enter reason for this advance cash request"><?= htmlspecialchars($editRequest['reason']) ?></textarea>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 my-2">
                    <button type="button" class="btn btn-primary" onclick="addTableRow('adv-cash-request-table')">Add Row</button>
                    <button type="button" onclick="removeAdvRow()" class="btn btn-danger">Remove Row</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="adv-cash-request-table">
                    <thead class="bg-info text-white">
                        <tr>
                            <th style="width:25%;">Item Description</th>
                            <th style="width:15%;">Amount</th>
                            <th style="width:15%;">Equiv Amount</th>
                            <th style="width:22%;">Bank Name</th>
                            <th style="width:23%;">Account Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($editItems as $item): ?>
                        <tr class="data-row">
                            <input type="hidden" name="item_original_id[]" value="<?= (int)$item['id'] ?>">
                            <td>
                                <input type="text" name="item_description[]" class="form-control" value="<?= htmlspecialchars($item['item_name']) ?>" placeholder="Describe what you need" required>
                            </td>
                            <td>
                                <input type="text" name="actual[]" class="form-control format-number debt_amount" oninput="calculateAdvEqv(this); sumAdvRequestTotalAmount();" value="<?= number_format(floatval($item['amount']), 2) ?>" placeholder="0.00" required>
                            </td>
                            <td>
                                <input type="text" name="amount[]" class="form-control equiv_dr amount" value="<?= number_format(floatval($item['amount_eqv']), 2) ?>" readonly required>
                            </td>
                            <td>
                                <input type="text" name="bank[]" class="form-control" value="<?= htmlspecialchars($item['bank']) ?>" placeholder="Bank name" required>
                            </td>
                            <td>
                                <input type="text" name="account[]" class="form-control" value="<?= htmlspecialchars($item['account']) ?>" placeholder="Account number" required>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right font-weight-bold">Amount Requested:</td>
                            <td colspan="3">
                                <input type="text" name="totalamount" class="form-control font-weight-bold" readonly required id="total-adv-amount">
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <input type="hidden" name="deleted_item_ids" id="deleted_item_ids" value="">
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning btn-block font-weight-bold" name="editadvcashreq">
                <i class="fas fa-paper-plane mr-1"></i> Update Request
            </button>
        </div>
    </form>
</div>

<script>
function calculateAdvEqv(input) {
    var currencyField = document.getElementById("curr_value");
    var currency = currencyField ? (parseFloat(currencyField.value.replace(/,/g, '')) || 1) : 1;
    var row = input.closest('tr');
    var actual = parseFloat(input.value.replace(/,/g, '')) || 0;
    var eqv = row.querySelector('.equiv_dr');
    if(eqv) eqv.value = (actual * currency).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function sumAdvRequestTotalAmount() {
    var inputs = document.getElementsByClassName("debt_amount");
    var currencyField = document.getElementById("curr_value");
    var currency = currencyField ? (parseFloat(currencyField.value.replace(/,/g, '')) || 1) : 1;
    var sum = 0;
    for(var i = 0; i < inputs.length; i++){
        sum += parseFloat(inputs[i].value.replace(/,/g, '')) || 0;
    }
    document.getElementById("total-adv-amount").value = (sum * currency).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function removeAdvRow() {
    var rows = $('#adv-cash-request-table tbody tr.data-row');
    if(rows.length <= 1) return;
    var last = rows.last();
    var oid = last.find('input[name="item_original_id[]"]').val();
    if(oid && oid != '0'){
        var curr = $('#deleted_item_ids').val();
        $('#deleted_item_ids').val(curr ? curr + ',' + oid : oid);
    }
    last.remove();
    sumAdvRequestTotalAmount();
}

var currDiv = document.getElementById('currencyx');
if(currDiv){
    new MutationObserver(function(){
        setTimeout(function(){
            var inputs = document.getElementsByClassName("debt_amount");
            for(var i = 0; i < inputs.length; i++){
                calculateAdvEqv(inputs[i]);
            }
            sumAdvRequestTotalAmount();
        }, 100);
    }).observe(currDiv, {childList: true, subtree: true});
}

document.addEventListener('DOMContentLoaded', function(){
    selectCurrency(<?= (int)$editRequest['currency'] ?>);
    setTimeout(function(){
        document.getElementById('curr_value').value = '<?= number_format($editRate, 2, '.', '') ?>';
        sumAdvRequestTotalAmount();
    }, 600);
});
</script>