<?php
$pvId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($pvId <= 0) header('Location: ../?page=purchase_voucher');
$editVoucher = selectPurchaseVoucherById($conn, $pvId);
$editItems   = selectPurchaseVoucherItems($conn, $pvId);
$editRate    = floatval($editVoucher['currency_rate']) ?: 1;
?>

<div class="card card-info">
    <div class="card-header">
        <h4>EDIT PURCHASE VOUCHER</h4>
    </div>
    <div class="card-body">
        <form action="./controllers/voucher_controller.php" class="was-validated" method="post">
            <input type="hidden" name="voucher_id" value="<?= $pvId ?>">
            <input type="hidden" name="transaction_id" value="<?= $editVoucher['transaction_id'] ?>">
            <input type="hidden" name="tax_transaction_id" value="<?= $editVoucher['tax_transaction_id'] ?>">

            <div class="row m-1">
                <div class="col-md-4 form-group">
                    <label class="form-label">Voucher Date</label>
                    <input type="date" name="voucherdate" class="form-control"
                           value="<?= date('Y-m-d', strtotime($editVoucher['date_'])) ?>" required>
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Voucher Reference</label>
                    <input type="text" name="voucher_ref"
                           value="<?= htmlspecialchars($editVoucher['reference']) ?>"
                           class="form-control" readonly>
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Supplier</label>
                    <select name="dr_account" class="form-control select2-form select2bs4-form" required>
                        <option value="">Choose Supplier</option>
                        <?php
                        $sql = "SELECT * FROM `subsidiaries` WHERE deleted_at IS NULL ORDER BY name ASC";
                        $query = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($query)){
                            $selected = ($editVoucher['supplier_id'] == $row['id']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' {$selected}>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-5 form-group">
                    <label class="form-label">Purchase Account (DR Account)</label>
                    <select name="sub_item" required class="select2-form select2bs4-form form-control">
                        <option value="">Select Below</option>
                        <?php
                        $vehicles = selectAllSubsidiaries($conn);
                        if($vehicles && is_array($vehicles)){
                            foreach($vehicles as $vehicle){
                                $selected = ($editVoucher['dr_account'] == $vehicle['id']) ? 'selected' : '';
                                echo "<option value='{$vehicle['id']}' {$selected}>{$vehicle['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Project</label>
                    <select name="project_id" required class="select2-form select2bs4-form form-control">
                        <option value="">Select Project</option>
                        <option value="0" <?= ($editVoucher['project_id'] == '0') ? 'selected' : '' ?>>Default Project</option>
                        <?php
                        $projects = listProjectsByStatus($conn, 'active');
                        if($projects && is_array($projects)){
                            foreach($projects as $project){
                                $selected = ($editVoucher['project_id'] == $project['id']) ? 'selected' : '';
                                echo "<option value='{$project['id']}' {$selected}>{$project['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-control" onchange="selectCurrency(this.value)" required>
                        <option value="">Select Currency</option>
                        <?php
                        $sql = "SELECT * FROM currency";
                        $query = mysqli_query($conn, $sql);
                        while($rows = mysqli_fetch_assoc($query)){
                            $selected = ($editVoucher['currency'] == $rows['id']) ? 'selected' : '';
                            echo "<option value='{$rows['id']}' {$selected}>{$rows['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 form-group">
                    <label class="form-label">Exchange Rate</label>
                    <div id="currency">
                        <input type="text" name="curr" id="curr_value" class="form-control" required
                               placeholder="Select currency first" value="">
                    </div>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 my-2">
                    <button type="button" class="btn btn-primary" onclick="addPurchaseRow()">Add Row</button>
                    <button type="button" class="btn btn-danger" onclick="removePurchaseRow()">Remove Row</button>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="purchase-table">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th style="width:18%;">Description</th>
                                    <th style="width:8%;">Qty</th>
                                    <th style="width:8%;">Unit</th>
                                    <th style="width:10%;">Price</th>
                                    <th style="width:10%;">Amount</th>
                                    <th style="width:10%;">Equiv Amt</th>
                                    <th style="width:9%;">VAT</th>
                                    <th style="width:10%;">VAT Amt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($editItems as $item):
                                    $itemEquiv  = floatval($item['amt']);
                                    $itemActual = $itemEquiv / $editRate;
                                ?>
                                <tr class="data-row">
                                    <input type="hidden" name="item_original_id[]" value="<?= (int)$item['id'] ?>">
                                    <input type="hidden" name="item_min_sub_id[]" value="<?= (int)$item['item_id'] ?>">
                                    <td><textarea name="description[]" class="form-control" required><?= htmlspecialchars($item['item_name'] ?? '') ?></textarea></td>
                                    <td><input type="text" name="quantity[]" class="form-control doFormat"
                                               value="<?= number_format(floatval($item['quantity']), 0) ?>"
                                               oninput="calculatePurchaseRow(this)" required></td>
                                    <td>
                                        <select name="unit[]" class="form-control" required>
                                            <option value="">Select</option>
                                            <?php
                                            $units = selectAllUnit($conn);
                                            if($units && is_array($units)){
                                                foreach($units as $unit){
                                                    $sel = ($unit['id'] == $item['unit']) ? 'selected' : '';
                                                    echo "<option value='{$unit['id']}' {$sel}>{$unit['name']}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="price[]" class="form-control doFormat"
                                               value="<?= number_format(floatval($item['price']), 2) ?>"
                                               oninput="calculatePurchaseRow(this)" required></td>
                                    <td><input type="text" name="amt[]" class="form-control amt-col" readonly
                                               value="<?= number_format($itemActual, 2) ?>"></td>
                                    <td><input type="text" name="amount[]" class="form-control amount-col" readonly
                                               value="<?= number_format($itemEquiv, 2) ?>"></td>
                                    <td>
                                        <select name="rates[]" class="form-control vat-select"
                                                onchange="calculatePurchaseRow(this)" required>
                                            <option value="0" <?= floatval($item['vat_rate']) == 0 ? 'selected' : '' ?>>0%</option>
                                            <option value="18" <?= floatval($item['vat_rate']) == 18 ? 'selected' : '' ?>>18%</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="vat_amount[]" class="form-control vat-amt-col" readonly
                                               value="<?= number_format(floatval($item['vat_amount']), 2) ?>"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <table class="table table-sm table-bordered w-50 ml-auto">
                            <tr>
                                <td><strong>Sub Total</strong></td>
                                <td><input name="subtotal" id="subtotal" class="form-control" readonly></td>
                            </tr>
                            <tr>
                                <td><strong>Total VAT</strong></td>
                                <td><input name="tax" id="tax" class="form-control" readonly></td>
                            </tr>
                            <tr>
                                <td><strong>Grand Total</strong></td>
                                <td><input name="total" id="total" class="form-control" readonly></td>
                            </tr>
                        </table>

                        <input type="hidden" name="deleted_item_ids" id="deleted_item_ids" value="">
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-success btn-block" name="editpurchvocher" type="submit">UPDATE</button>
            </div>
        </form>
    </div>
</div>

<script>
function calculatePurchaseRow(el) {
    var row      = $(el).closest('tr');
    var qty      = parseFloat(row.find('input[name="quantity[]"]').val().replace(/,/g,'')) || 0;
    var price    = parseFloat(row.find('input[name="price[]"]').val().replace(/,/g,'')) || 0;
    var vatRate  = parseFloat(row.find('select[name="rates[]"]').val()) || 0;
    var currency = parseFloat($('#curr_value').val().replace(/,/g,'')) || 1;
    var local    = qty * price;
    var equiv    = local * currency;
    var vatAmt   = equiv * (vatRate / 100);
    row.find('.amt-col').val(local.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    row.find('.amount-col').val(equiv.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    row.find('.vat-amt-col').val(vatAmt.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    calculatePurchaseSummary();
}
function calculatePurchaseSummary() {
    var sub = 0, vat = 0;
    $('#purchase-table tbody tr.data-row').each(function(){
        sub += parseFloat($(this).find('.amount-col').val().replace(/,/g,'')) || 0;
        vat += parseFloat($(this).find('.vat-amt-col').val().replace(/,/g,'')) || 0;
    });
    $('#subtotal').val(sub.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#tax').val(vat.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#total').val((sub+vat).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
}
function addPurchaseRow() {
    var newRow = $('#purchase-table tbody tr.data-row:first').clone();
    newRow.find('input[name="item_original_id[]"]').val('0');
    newRow.find('input[name="item_min_sub_id[]"]').val('0');
    newRow.find('input').not('[name="item_original_id[]"]').not('[name="item_min_sub_id[]"]').val('');
    newRow.find('select').val('');
    $('#purchase-table tbody').append(newRow);
}
function removePurchaseRow() {
    var rows = $('#purchase-table tbody tr.data-row');
    if(rows.length <= 1) return;
    var last = rows.last();
    var oid  = last.find('input[name="item_original_id[]"]').val();
    if(oid && oid != '0'){
        var curr = $('#deleted_item_ids').val();
        $('#deleted_item_ids').val(curr ? curr+','+oid : oid);
    }
    last.remove();
    calculatePurchaseSummary();
}
$(document).on('input change', '#curr_value', function(){
    $('#purchase-table tbody tr.data-row').each(function(){
        calculatePurchaseRow($(this).find('input[name="quantity[]"]')[0]);
    });
});
var purchCurrDiv = document.getElementById('currency');
if(purchCurrDiv){
    new MutationObserver(function(){
        setTimeout(function(){
            $('#purchase-table tbody tr.data-row').each(function(){
                calculatePurchaseRow($(this).find('input[name="quantity[]"]')[0]);
            });
        }, 100);
    }).observe(purchCurrDiv, {childList: true, subtree: true});
}
document.addEventListener('DOMContentLoaded', function(){
    selectCurrency(<?= (int)$editVoucher['currency'] ?>);
    setTimeout(function(){
        $('#curr_value').val('<?= number_format($editRate, 2, '.', '') ?>');
        calculatePurchaseSummary();
    }, 600);
});
</script>