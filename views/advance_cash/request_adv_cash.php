<div class="card card-outline card-info">
    <div class="card-header">
        <h4 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Request Advance Cash</h4>
    </div>
    <form action="./controllers/procurement_controller.php" class="was-validated" method="post">
        <div class="card-body">

            <div class="row m-1">
                <div class="col-md-4 form-group">
                    <label>Ref No</label>
                    <input type="text" readonly name="ref_no" value="<?= generateRefNo($conn, 'advance_cash_requests', 'ADV/' . date('Y')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Request Date</label>
                    <input type="date" name="date_" max="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Type of Request</label>
                    <select name="type" class="form-control" required>
                        <option value="">Select Below</option>
                        <option value="normal">Normal</option>
                        <option value="travel">Travel</option>
                    </select>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-6 form-group">
                    <label>Requester</label>
                    <select name="requester_id" required class="select2-form select2bs4-form form-control">
                        <option value="">Select Below</option>
                        <?php
                            $users = selectSubsidiaryByType($conn,'staff');
                            if($users && is_array($users)){
                                foreach($users as $user){
                                    echo "<option value='{$user['id']}'>{$user['name']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Project</label>
                    <select name="project_id" required class="select2-form select2bs4-form form-control">
                        <option value="">Select Project</option>
                        <option value="0">No Specific Project</option>
                        <?php
                            $projects = listProjectsByStatus($conn, 'active');
                            if($projects && is_array($projects)){
                                foreach($projects as $project){
                                    echo "<option value='{$project['id']}'>{$project['name']}</option>";
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
                                echo "<option value='{$currency['id']}'>{$currency['name']}</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Exchange Rate</label>
                    <div id="currencyx"></div>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 form-group">
                    <label>Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" required rows="3" style="resize: vertical;" placeholder="Enter reason for this advance cash request"></textarea>
                    <!--<small class="text-muted">This will be displayed on the printed document</small>-->
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 my-2">
                    <button type="button" class="btn btn-primary" onclick="addTableRow('adv-cash-request-table')">Add Row</button>
                    <button type="button" onclick="removeTableRow('adv-cash-request-table')" class="btn btn-danger">Remove Row</button>
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
                        <tr class="data-row">
                            <td>
                                <input type="text" name="item_description[]" class="form-control" placeholder="Describe what you need" required>
                            </td>
                            <td>
                                <input type="text" name="actual[]" class="form-control format-number debt_amount" oninput="calculateAdvEqv(this); sumAdvRequestTotalAmount();" placeholder="0.00" required>
                            </td>
                            <td>
                                <input type="text" name="amount[]" class="form-control equiv_dr amount" readonly required>
                            </td>
                            <td>
                                <input type="text" name="bank[]" class="form-control" placeholder="Bank name" required>
                            </td>
                            <td>
                                <input type="text" name="account[]" class="form-control" placeholder="Account number" required>
                            </td>
                        </tr>
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
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info btn-block font-weight-bold" name="sendadvcashreq">
                <i class="fas fa-paper-plane mr-1"></i> Send Request
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
</script>