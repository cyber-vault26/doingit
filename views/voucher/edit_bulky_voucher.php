<?php
$bvId = intval($_GET['id'] ?? 0);
$voucher = selectBulkyVoucherById($conn, $bvId);
$items = selectBulkyVoucherItems($conn, $bvId);
$projects = listAllProjects($conn);
$allSubs = [];
$subQuery = mysqli_query($conn, "SELECT * FROM subsidiaries WHERE deleted_at IS NULL ORDER BY name ASC");
while($s = mysqli_fetch_assoc($subQuery)){ $allSubs[] = $s; }
$unitsList = selectAllUnit($conn);
if(!$voucher){ echo "<div class='alert alert-danger'>Voucher not found.</div>"; return; }
?>
<div class="card card-warning card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" role="tablist">
            <li class="pt-2 px-3"><h3 class="card-title">Voucher Category</h3></li>
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#tab-payment-pane" role="tab">
                    <i class="fas fa-money-bill-wave"></i> Payment Voucher
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-payment-pane" role="tabpanel">
                <div class="card card-outline card-warning mt-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">Edit Bulky Payment Voucher</h4>
                        <span class="badge badge-warning"><?= $voucher['reference'] ?></span>
                    </div>
                    <form action="./controllers/voucher_controller.php" method="post" enctype="multipart/form-data" class="was-validated">
                        <input type="hidden" name="bv_id" value="<?= $bvId ?>">
                        <input type="hidden" name="deleted_item_ids" id="deleted_item_ids" value="">
                        <input type="hidden" name="new_item_flags" id="new_item_flags" value="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-12 form-group">
                                    <label>Payment Account (CR)</label>
                                    <select name="cr_account" class="form-control select2bs4-form" required>
                                        <option value="">Select Below</option>
                                        <?php
                                        $q = mysqli_query($conn, "SELECT * FROM subsidiaries WHERE ledger_id IN (32,77,33) AND deleted_at IS NULL ORDER BY name ASC");
                                        while($r = mysqli_fetch_assoc($q)){
                                            $sel = $r['id'] == $voucher['cr_account'] ? 'selected' : '';
                                            echo "<option value='{$r['id']}' $sel>{$r['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2 col-6 form-group">
                                    <label>Date</label>
                                    <input type="date" name="date" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= $voucher['date_'] ?>">
                                </div>
                                <div class="col-md-2 col-6 form-group">
                                    <label>Currency</label>
                                    <select name="currency" class="form-control select2bs4-form" required onchange="selectCurrency(this.value)">
                                        <option value="">Select Below</option>
                                        <?php
                                        $q = mysqli_query($conn, "SELECT * FROM currency");
                                        while($r = mysqli_fetch_assoc($q)){
                                            $sel = $r['id'] == $voucher['currency'] ? 'selected' : '';
                                            echo "<option value='{$r['id']}' $sel>{$r['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2 col-6 form-group">
                                    <label>Exchange Rate</label>
                                    <div id="currencybpv">
                                        <input type="text" name="curr" id="bpv_curr_value" class="form-control" required value="<?= $voucher['currency_rate'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 form-group">
                                    <label>Ref No</label>
                                    <input type="text" name="ref_no" class="form-control" value="<?= $voucher['reference'] ?>" readonly>
                                </div>
                                <div class="col-md-4 col-12 form-group">
                                    <label>Supplier</label>
                                    <input type="text" name="supplier" class="form-control" required placeholder="Enter supplier name" value="<?= $voucher['supplier'] ?>">
                                </div>
                                <div class="col-12 form-group">
                                    <label>Payment Heading / Description</label>
                                    <textarea name="over_all_description" class="form-control" rows="2" placeholder="Enter overall payment heading or description"><?= $voucher['payment_heading'] ?></textarea>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <strong>Line Items</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="bpvAddItem()">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                            <div id="bpv-items-container">
                                <?php foreach($items as $i => $item): ?>
                                <div class="border rounded p-3 mb-3 bg-white position-relative bpv-item-block" data-item-id="<?= $item['id'] ?>">
                                    <span class="badge badge-warning position-absolute" style="top:-10px;left:14px;">ITEM <?= $i+1 ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:10px;" onclick="bpvRemoveItem(this)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                    <input type="hidden" name="item_id[]" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="transaction_id[]" value="<?= $item['transaction_id'] ?>">
                                    <input type="hidden" name="tax_transaction_id[]" value="<?= $item['tax_transaction_id'] ?>">
                                    <div class="mt-3">
                                        <div class="row">
                                            <div class="col-md-6 col-12 form-group">
                                                <label>Debit Account</label>
                                                <select name="dr_account[]" class="form-control select2bs4-form" required>
                                                    <option value="">Select Account</option>
                                                    <?php foreach($allSubs as $s){
                                                        $sel = $s['id'] == $item['dr_account'] ? 'selected' : '';
                                                        echo "<option value='{$s['id']}' $sel>{$s['name']}</option>";
                                                    } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-12 form-group">
                                                <label>Description</label>
                                                <textarea name="description[]" class="form-control" rows="2" placeholder="Place item description here" required><?= $item['description'] ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4 col-md-2 form-group">
                                                <label>Qty</label>
                                                <input type="text" name="quantity[]" class="form-control format-number bpv-qty" oninput="formatNumber();calculateRowAmount(this);calculateBPVSummary();" required value="<?= $item['quantity'] ?>">
                                            </div>
                                            <div class="col-4 col-md-2 form-group">
                                                <label>Unit</label>
                                                <select name="unit[]" class="form-control" required>
                                                    <option value="">-</option>
                                                    <?php if($unitsList && is_array($unitsList)){ foreach($unitsList as $u){
                                                        $sel = $u['id'] == $item['unit'] ? 'selected' : '';
                                                        echo "<option value='{$u['id']}' $sel>{$u['name']}</option>";
                                                    }} ?>
                                                </select>
                                            </div>
                                            <div class="col-4 col-md-2 form-group">
                                                <label>Unit Price</label>
                                                <input type="text" name="unit_price[]" class="form-control format-number bpv-unit-price" oninput="formatNumber();calculateRowAmount(this);calculateBPVSummary();" required value="<?= $item['unit_price'] ?>">
                                            </div>
                                            <div class="col-4 col-md-2 form-group">
                                                <label>Amount</label>
                                                <input type="text" name="amount[]" class="form-control format-number debt_amount" readonly value="<?= $item['amount'] ?>">
                                            </div>
                                            <div class="col-4 col-md-2 form-group">
                                                <label>Equiv Amt</label>
                                                <input type="text" name="equivalent_amount[]" class="form-control format-number equiv_dr bpv-equiv" readonly value="<?= $item['equiv_amount'] ?>">
                                            </div>
                                            <div class="col-4 col-md-2 form-group">
                                                <label>VAT %</label>
                                                <select name="rates[]" class="form-control bpv-vat-select" onchange="calculateBPVSummary()" required>
                                                    <option value="0" <?= $item['vat_rate'] == 0 ? 'selected' : '' ?>>0%</option>
                                                    <option value="18" <?= $item['vat_rate'] == 18 ? 'selected' : '' ?>>18%</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4 col-md-2 form-group">
                                                <label>VAT Amount</label>
                                                <input type="text" name="vat_amount[]" class="form-control bpv-vat-amt" readonly value="<?= $item['vat_amount'] ?>">
                                            </div>
                                            <div class="col-8 col-md-4 form-group">
                                                <label>Project</label>
                                                <select name="project_id[]" class="form-control select2bs4-form" required>
                                                    <option value="">Select Project</option>
                                                    <option value="0" <?= $item['project_id'] == 0 ? 'selected' : '' ?>>Default Project</option>
                                                    <?php foreach($projects as $p){
                                                        $sel = $p['id'] == $item['project_id'] ? 'selected' : '';
                                                        echo "<option value='{$p['id']}' $sel>{$p['name']}</option>";
                                                    } ?>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6 form-group">
                                                <label>Attachment <?= $item['attachment'] ? '<a href="'.$item['attachment'].'" target="_blank" class="badge badge-info ml-1">View Current</a>' : '' ?></label>
                                                <input type="file" name="attachment[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-block mb-3" onclick="bpvAddItem()">
                                <i class="fas fa-plus"></i> Add Another Item
                            </button>
                            <div class="row justify-content-end">
                                <div class="col-md-5 col-12">
                                    <table class="table table-sm table-bordered">
                                        <tr><td><strong>Sub Total</strong></td><td><input id="bpv_subtotal" name="subtotal" class="form-control form-control-sm" readonly value="<?= $voucher['subtotal'] ?>"></td></tr>
                                        <tr><td><strong>Total VAT</strong></td><td><input id="bpv_tax" name="tax" class="form-control form-control-sm" readonly value="<?= $voucher['tax'] ?>"></td></tr>
                                        <tr class="table-success"><td><strong>Grand Total</strong></td><td><input id="bpv_total" name="total_equivalent_amount" class="form-control form-control-sm" readonly value="<?= $voucher['amount'] ?>"></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="update_bulky_payment_voucher" class="btn btn-block btn-warning">
                                <i class="fas fa-save"></i> Update Voucher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
var bpvSubsOptions = '<?php foreach($allSubs as $s){ echo "<option value=\'{$s['id']}\'>{$s['name']}</option>"; } ?>';
var bpvUnitOptions = '<?php if($unitsList && is_array($unitsList)){ foreach($unitsList as $u){ echo "<option value=\'{$u['id']}\'>{$u['name']}</option>"; } } ?>';
var bpvProjectOptions = '<option value="0">Default Project</option><?php foreach($projects as $p){ echo "<option value=\'{$p['id']}\'>{$p['name']}</option>"; } ?>';
var bpvCount = <?= count($items) ?>;
var deletedIds = [];
var newItemIndexes = [];
function bpvAddItem() {
    bpvCount++;
    var idx = bpvCount;
    newItemIndexes.push(idx);
    $('#new_item_flags').val(newItemIndexes.join(','));
    var html = '<div class="border rounded p-3 mb-3 bg-white position-relative bpv-item-block" data-item-id="new_' + idx + '">'
        + '<span class="badge badge-primary position-absolute" style="top:-10px;left:14px;">ITEM ' + idx + '</span>'
        + '<button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:10px;" onclick="bpvRemoveItem(this)"><i class="fas fa-trash"></i> Remove</button>'
        + '<input type="hidden" name="item_id[]" value="new_' + idx + '">'
        + '<input type="hidden" name="transaction_id[]" value="">'
        + '<input type="hidden" name="tax_transaction_id[]" value="">'
        + '<div class="mt-3">'
        + '<div class="row">'
        + '<div class="col-md-6 col-12 form-group"><label>Debit Account</label><select name="dr_account[]" class="form-control" required><option value="">Select Account</option>' + bpvSubsOptions + '</select></div>'
        + '<div class="col-md-6 col-12 form-group"><label>Description</label><textarea name="description[]" class="form-control" rows="2" placeholder="Place item description here" required></textarea></div>'
        + '</div>'
        + '<div class="row">'
        + '<div class="col-4 col-md-2 form-group"><label>Qty</label><input type="text" name="quantity[]" class="form-control format-number bpv-qty" oninput="formatNumber();calculateRowAmount(this);calculateBPVSummary();" required></div>'
        + '<div class="col-4 col-md-2 form-group"><label>Unit</label><select name="unit[]" class="form-control" required><option value="">-</option>' + bpvUnitOptions + '</select></div>'
        + '<div class="col-4 col-md-2 form-group"><label>Unit Price</label><input type="text" name="unit_price[]" class="form-control format-number bpv-unit-price" oninput="formatNumber();calculateRowAmount(this);calculateBPVSummary();" required></div>'
        + '<div class="col-4 col-md-2 form-group"><label>Amount</label><input type="text" name="amount[]" class="form-control format-number debt_amount" readonly></div>'
        + '<div class="col-4 col-md-2 form-group"><label>Equiv Amt</label><input type="text" name="equivalent_amount[]" class="form-control format-number equiv_dr bpv-equiv" readonly></div>'
        + '<div class="col-4 col-md-2 form-group"><label>VAT %</label><select name="rates[]" class="form-control bpv-vat-select" onchange="calculateBPVSummary()" required><option value="0">0%</option><option value="18">18%</option></select></div>'
        + '</div>'
        + '<div class="row">'
        + '<div class="col-4 col-md-2 form-group"><label>VAT Amount</label><input type="text" name="vat_amount[]" class="form-control bpv-vat-amt" readonly></div>'
        + '<div class="col-8 col-md-4 form-group"><label>Project</label><select name="project_id[]" class="form-control" required><option value="">Select Project</option>' + bpvProjectOptions + '</select></div>'
        + '<div class="col-12 col-md-6 form-group"><label>Attachment</label><input type="file" name="attachment[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>'
        + '</div>'
        + '</div></div>';
    $('#bpv-items-container').append(html);
    bpvRenumber();
}
function bpvRemoveItem(btn) {
    if ($('#bpv-items-container .bpv-item-block').length <= 1) {
        alert('At least one item is required.');
        return;
    }
    var block = $(btn).closest('.bpv-item-block');
    var itemId = block.data('item-id');
    if(itemId && String(itemId).indexOf('new_') === -1){
        deletedIds.push(itemId);
        $('#deleted_item_ids').val(deletedIds.join(','));
    }
    block.remove();
    bpvRenumber();
    calculateBPVSummary();
}
function bpvRenumber() {
    $('#bpv-items-container .bpv-item-block').each(function(i){ $(this).find('.badge').text('ITEM ' + (i+1)); });
    bpvCount = $('#bpv-items-container .bpv-item-block').length;
}
function calculateRowAmount(el) {
    var block = $(el).closest('.bpv-item-block');
    var qty = parseFloat(block.find('.bpv-qty').val().replace(/,/g,'')) || 0;
    var price = parseFloat(block.find('.bpv-unit-price').val().replace(/,/g,'')) || 0;
    block.find('.debt_amount').val((qty * price).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    calculateEqv();
    calculateBPVSummary();
}
$(document).on('input','#bpv-items-container .bpv-qty',function(){ calculateRowAmount(this); });
function calculateBPVSummary() {
    var sub = 0, vat = 0;
    $('#bpv-items-container .bpv-item-block').each(function(){
        var equiv = parseFloat($(this).find('.bpv-equiv').val().replace(/,/g,'')) || 0;
        var rate = parseFloat($(this).find('.bpv-vat-select').val()) || 0;
        var vatAmt = equiv * (rate / 100);
        $(this).find('.bpv-vat-amt').val(vatAmt.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
        sub += equiv;
        vat += vatAmt;
    });
    $('#bpv_subtotal').val(sub.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#bpv_tax').val(vat.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#bpv_total').val((sub+vat).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
}
$(document).on('input change','#bpv_curr_value',function(){ calculateEqv(); calculateBPVSummary(); });
var bpvCurrDiv = document.getElementById('currencybpv');
if(bpvCurrDiv){ new MutationObserver(function(){ calculateEqv(); calculateBPVSummary(); }).observe(bpvCurrDiv,{childList:true,subtree:true}); }
</script>