<?php
session_start();
if (!$_SESSION) {
    echo "<script>window.location.href='../../login.php'</script>";
}
$cost_center = ($_SESSION['usertype'] === 'shop-keeper') ? (int)$_SESSION['cost_center'] : null;
require_once "../../configs.php";
require_once "../../functions/subsidiary_functions.php";
require_once "../../functions/stock_functions.php";
require_once "../../functions/min_sub_functions.php";
require_once "../../functions/utilities_functions.php";
require_once "../../functions/stock_date_request_functions.php";
$conn = openConn();
$minSubs   = selectAllMinSub($conn);
$stores    = selectAllStockStores($conn);
$suppliers = selectAllSubsidiaries($conn);
$approvedDateRequests = selectUserApprovedStockDatesToday($conn, (int)$_SESSION['userid']);

if($_SERVER['REQUEST_METHOD'] === 'GET'){
if(isset($_GET['grn_form_input'])) { ?>
<form action="./controllers/stock_controllers.php" method="post" class="was-validated">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 my-2">
                <label class="form-label">Voucher Reference</label>
                <input type="text" name="voucher_ref" readonly value="<?= generateRefNumber($conn, 'GRN/'.date('Y').'/'); ?>" class="form-control">
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">PO Number</label>
                <input type="number" name="purchase_order_id" class="form-control" placeholder="Enter PO Number" required>
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Date</label>
                 <input type="date" required name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" class="form-control stock-date-input" data-type="grn">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 my-2">
                <label class="form-label">Supplier</label>
                <select name="supplier" class="form-control select2-form select2bs4-form" required>
                    <option value="">Select below</option>
                    <?php if($suppliers && is_array($suppliers)) foreach($suppliers as $supplier): ?>
                        <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 my-2">
                <label class="form-label">Received To</label>
                <select name="store_id" class="form-control select2-form select2bs4-form" required <?= $cost_center ? 'disabled' : '' ?>>
                    <option value="">Select below</option>
                    <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                        <option value="<?= $store['id'] ?>" <?= $store['id'] == $cost_center ? 'selected' : '' ?>><?= $store['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if($cost_center): ?>
                    <input type="hidden" name="store_id" value="<?= $cost_center ?>">
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 my-2">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-control select2-form select2bs4-form" onchange="selectCurrency(this.value)" required>
                    <option value="">Select below</option>
                    <?php
                    $sql = "SELECT * FROM currency";
                    $query = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($query)){
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 my-2">
                <label class="form-label">Exchange Rate</label>
                <div id="currency">
                    <input type="text" name="curr" id="curr_value" class="form-control format-number" required placeholder="Select currency first" value="">
                </div>
            </div>
        </div>
        <hr>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <strong>Line Items</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="grnAddItem()">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
        <div id="grn-items-container">
            <div class="border rounded p-3 mb-3 bg-white position-relative grn-item-block">
                <span class="badge badge-primary position-absolute" style="top:-10px;left:14px;">ITEM 1</span>
                <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:10px;" onclick="grnRemoveItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-12 col-md-6 form-group">
                            <label class="form-label">Item</label>
                            <select name="item[]" class="form-control select2-form select2bs4-form grn-item-select" required>
                                <option value="">Select below</option>
                                <?php if($minSubs && is_array($minSubs)) foreach($minSubs as $minSub): ?>
                                    <option value="<?= $minSub['id'] ?>"><?= $minSub['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Unit</label>
                            <select name="unit[]" class="form-control grn-unit-select" required disabled>
                                <option value="">Select item first</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Quantity</label>
                            <input type="text" name="quantity[]" class="form-control format-number grn-qty" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Base Quantity</label>
                            <input type="text" name="base_quantity[]" class="form-control grn-base-qty" readonly>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Unit Price</label>
                            <input type="text" name="price[]" class="form-control format-number grn-price" required>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Amount</label>
                            <input type="text" name="amount[]" class="form-control grn-amt-col" readonly>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Equiv Amount</label>
                            <input type="text" name="eqv[]" class="form-control grn-eqv-col" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">VAT %</label>
                            <select name="rates[]" class="form-control grn-vat-select" required>
                                <option value="0">0%</option>
                                <option value="18">18%</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">VAT Amount</label>
                            <input type="text" name="vat_amount[]" class="form-control grn-vat-amt-col" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-block mb-3" onclick="grnAddItem()">
            <i class="fas fa-plus"></i> Add Another Item
        </button>
        <div class="row justify-content-end">
            <div class="col-md-5 col-12">
                <table class="table table-sm table-bordered">
                    <tr>
                        <td><strong>Sub Total</strong></td>
                        <td><input name="subtotal" id="grn_subtotal" class="form-control form-control-sm" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Total VAT</strong></td>
                        <td><input name="tax" id="grn_tax" class="form-control form-control-sm" readonly></td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Grand Total</strong></td>
                        <td><input name="total" id="grn_total" class="form-control form-control-sm" readonly></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" name="addgrn" class="btn btn-success w-100">POST</button>
    </div>
</form>
<script>
window.grnCalculateRow = function(el) {
    if (!el) return;
    var block = $(el).closest('.grn-item-block');
    if (!block.length) return;
    var qty   = parseFloat(block.find('.grn-qty').val().replace(/,/g,''))   || 0;
    var price = parseFloat(block.find('.grn-price').val().replace(/,/g,'')) || 0;
    var vat   = parseFloat(block.find('.grn-vat-select').val())             || 0;
    var curr  = parseFloat(document.getElementById('curr_value').value.replace(/,/g,'')) || 0;
    var local = qty * price;
    var equiv = local * curr;
    var vatAmt = equiv * (vat / 100);
    var fmt = function(n){ return n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); };
    block.find('.grn-amt-col').val(fmt(local));
    block.find('.grn-eqv-col').val(fmt(equiv));
    block.find('.grn-vat-amt-col').val(fmt(vatAmt));
    var sub = 0, totalVat = 0;
    $('.grn-item-block').each(function(){
        sub      += parseFloat($(this).find('.grn-eqv-col').val().replace(/,/g,''))     || 0;
        totalVat += parseFloat($(this).find('.grn-vat-amt-col').val().replace(/,/g,'')) || 0;
    });
    $('#grn_subtotal').val(fmt(sub));
    $('#grn_tax').val(fmt(totalVat));
    $('#grn_total').val(fmt(sub + totalVat));
};

$(document).off('change','.grn-item-select').on('change','.grn-item-select', function(){
    var block = $(this).closest('.grn-item-block');
    stockLoadUnits($(this).val(), block.find('.grn-unit-select'), block.find('.grn-base-qty'));
});
$(document).off('change','.grn-unit-select').on('change','.grn-unit-select', function(){
    var block = $(this).closest('.grn-item-block');
    stockSetBaseQty(block.find('.grn-qty'), $(this), block.find('.grn-base-qty'));
    window.grnCalculateRow(this);
});
$(document).off('input','.grn-qty,.grn-price').on('input','.grn-qty,.grn-price', function(){
    var block = $(this).closest('.grn-item-block');
    stockSetBaseQty(block.find('.grn-qty'), block.find('.grn-unit-select'), block.find('.grn-base-qty'));
    window.grnCalculateRow(this);
});
$(document).off('change','.grn-vat-select').on('change','.grn-vat-select', function(){
    window.grnCalculateRow(this);
});

var grnCurrDiv = document.getElementById('grn-currency');
if (grnCurrDiv) {
    new MutationObserver(function(){
        setTimeout(function(){
            $('.grn-item-block').each(function(){
                window.grnCalculateRow($(this).find('.grn-qty').get(0));
            });
        }, 150);
    }).observe(grnCurrDiv, {childList:true, subtree:true});
}

window.grnItemCount = 1;
window.grnAddItem = function(){
    window.grnItemCount++;
    var newBlock = $('.grn-item-block:first').clone();
    newBlock.find('.badge').text('ITEM ' + window.grnItemCount);
    newBlock.find('input').val('');
    newBlock.find('.item-img-preview').remove(); 
    newBlock.find('select').each(function(){
        try{ $(this).select2('destroy'); } catch(e){}
        $(this).next('.select2-container').remove();
        $(this).removeClass('select2-hidden-accessible').show();
        this.selectedIndex = 0;
    });
    newBlock.find('.grn-unit-select').html('<option value="">Select item first</option>').prop('disabled', true);
    $('#grn-items-container').append(newBlock);
    newBlock.find('.select2-form').select2({placeholder:"Select an option"});
    newBlock.find('.select2bs4-form').select2({theme:'bootstrap4'});
    formatNumber();
};
window.grnRemoveItem = function(btn){
    if ($('.grn-item-block').length > 1) {
        $(btn).closest('.grn-item-block').remove();
        $('.grn-item-block').each(function(i){
            $(this).find('.badge').text('ITEM ' + (i + 1));
        });
        window.grnItemCount = $('.grn-item-block').length;
        $('.grn-item-block').each(function(){
            window.grnCalculateRow($(this).find('.grn-qty').get(0));
        });
    }
};
initializeSelect();
formatNumber();
</script>
<script>
var approvedStockDates = <?= json_encode(array_map(function($r){
    return ['date' => $r['requested_date'], 'type' => $r['transaction_type']];
}, $approvedDateRequests ?: [])) ?>;
</script>
<?php
    }
    
    if(isset($_GET['gdn_form_input'])) {
?>
<form action="./controllers/stock_controllers.php" method="post" class="was-validated">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 my-2">
                <label class="form-label">Voucher Reference</label>
                <input type="text" name="voucher_ref" readonly value="<?= generateRefNumber($conn, 'GDN/'.date('Y').'/'); ?>" class="form-control">
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Date</label>
                <input type="date" required name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" class="form-control stock-date-input" data-type="gdn">
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Issued To</label>
                <select name="supplier" class="form-control select2-form select2bs4-form" required>
                    <option value="">Select below</option>
                    <?php if($suppliers && is_array($suppliers)) foreach($suppliers as $supplier): ?>
                        <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 my-2">
                <label class="form-label">Issued From:</label>
                <select name="store_id" class="form-control select2-form select2bs4-form" required <?= $cost_center ? 'disabled' : '' ?>>
                    <option value="">Select below</option>
                    <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                        <option value="<?= $store['id'] ?>" <?= $store['id'] == $cost_center ? 'selected' : '' ?>><?= $store['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if($cost_center): ?>
                    <input type="hidden" name="store_id" value="<?= $cost_center ?>">
                <?php endif; ?>
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-control select2-form select2bs4-form" onchange="selectCurrency(this.value)" required>
                    <option value="">Select below</option>
                    <?php
                    $sql = "SELECT * FROM currency";
                    $query = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($query)){
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Exchange Rate</label>
                <div id="currency">
                    <input type="text" name="curr" id="curr_value" class="form-control format-number" required placeholder="Select currency first" value="">
                </div>
            </div>
        </div>
        <hr>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <strong>Line Items</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="gdnAddItem()">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
        <div id="gdn-items-container">
            <div class="border rounded p-3 mb-3 bg-white position-relative gdn-item-block">
                <span class="badge badge-primary position-absolute" style="top:-10px;left:14px;">ITEM 1</span>
                <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:10px;" onclick="gdnRemoveItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-12 col-md-6 form-group">
                            <label class="form-label">Item</label>
                            <select name="item[]" class="form-control select2-form select2bs4-form gdn-item-select" required>
                                <option value="">Select below</option>
                                <?php if($minSubs && is_array($minSubs)) foreach($minSubs as $minSub): ?>
                                    <option value="<?= $minSub['id'] ?>"><?= $minSub['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Unit</label>
                            <select name="unit[]" class="form-control gdn-unit-select" required disabled>
                                <option value="">Select item first</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Quantity</label>
                            <input type="text" name="quantity[]" class="form-control format-number gdn-qty" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label base-unit-label">Base Quantity</label>
                            <input type="text" name="base_quantity[]" class="form-control gdn-base-qty" readonly>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Price</label>
                            <input type="text" name="price[]" class="form-control format-number gdn-price" required>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Amount</label>
                            <input type="text" name="amount[]" class="form-control gdn-amt-col" readonly>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Equiv Amount</label>
                            <input type="text" name="eqv[]" class="form-control gdn-amount-col" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">VAT %</label>
                            <select name="rates[]" class="form-control gdn-vat-select" required>
                                <option value="0">0%</option>
                                <option value="18">18%</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">VAT Amount</label>
                            <input type="text" name="vat_amount[]" class="form-control gdn-vat-amt-col" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-block mb-3" onclick="gdnAddItem()">
            <i class="fas fa-plus"></i> Add Another Item
        </button>
        <div class="row justify-content-end">
            <div class="col-md-5 col-12">
                <table class="table table-sm table-bordered">
                    <tr>
                        <td><strong>Sub Total</strong></td>
                        <td><input name="subtotal" id="gdn_subtotal" class="form-control form-control-sm" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Total VAT</strong></td>
                        <td><input name="tax" id="gdn_tax" class="form-control form-control-sm" readonly></td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Grand Total</strong></td>
                        <td><input name="total" id="gdn_total" class="form-control form-control-sm" readonly></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" name="addgdn" class="btn btn-success w-100">POST</button>
    </div>
</form>
<script>
console.log('[GDN] Form loaded');
window.gdnCalculateRow=function(el){  
    if(!el) return;
    var block=$(el).closest('.gdn-item-block');
    if(!block.length) return;
    var qtyEl=block.find('.gdn-qty');
    var priceEl=block.find('.gdn-price');
    var vatEl=block.find('.gdn-vat-select');
    if(!qtyEl.length||!priceEl.length||!vatEl.length) return;
    var qty=parseFloat(qtyEl.val().replace(/,/g,''))||0;
    var price=parseFloat(priceEl.val().replace(/,/g,''))||0;
    var vat=parseFloat(vatEl.val())||0;
    var local=qty*price;
    block.find('.gdn-amt-col').val(local.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    var currValue=parseFloat(document.getElementById('curr_value').value.replace(/,/g,''))||0;
    var equiv=local*currValue;
    block.find('.gdn-amount-col').val(equiv.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    var vatAmt=equiv*(vat/100);
    block.find('.gdn-vat-amt-col').val(vatAmt.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    var sub=0,totalVat=0;
    $('.gdn-item-block').each(function(){
        sub+=parseFloat($(this).find('.gdn-amount-col').val().replace(/,/g,''))||0;
        totalVat+=parseFloat($(this).find('.gdn-vat-amt-col').val().replace(/,/g,''))||0;
    });
    $('#gdn_subtotal').val(sub.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#gdn_tax').val(totalVat.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#gdn_total').val((sub+totalVat).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
};
function gdnGetStoreId(){
    var v=$('#form-container-gdn select[name="store_id"] option:selected').val();
    if(!v) v=$('#form-container-gdn input[name="store_id"]').val();
    console.log('[gdnGetStoreId] storeId resolved:',v);
    return v||'';
}
$(document).off('change','.gdn-item-select').on('change','.gdn-item-select',function(){
    var block=$(this).closest('.gdn-item-block');
    var itemId=$(this).val();
    var storeId=gdnGetStoreId();
    console.log('[GDN item change] itemId:',itemId,'| storeId:',storeId);
    stockLoadUnits(itemId,block.find('.gdn-unit-select'),block.find('.gdn-base-qty'),storeId,block.find('.gdn-price'));
});
$(document).off('change','#form-container-gdn select[name="store_id"]').on('change','#form-container-gdn select[name="store_id"]',function(){
    var storeId=$(this).find('option:selected').val();
    console.log('[GDN store change] storeId:',storeId);
    $('.gdn-item-block').each(function(){
        var block=$(this);
        var itemId=block.find('.gdn-item-select').val();
        block.find('.gdn-qty').val('').css({'border-color':'','background-color':''});
        block.find('.gdn-base-qty').val('');
        if(itemId&&storeId){
            stockLoadUnits(itemId,block.find('.gdn-unit-select'),block.find('.gdn-base-qty'),storeId);
        }
    });
});
$(document).off('change','.gdn-unit-select').on('change','.gdn-unit-select',function(){
    var block=$(this).closest('.gdn-item-block');
    stockSetBaseQty(block.find('.gdn-qty'),$(this),block.find('.gdn-base-qty'));
    window.gdnCalculateRow(block.find('.gdn-qty').get(0));
});
$(document).off('input','.gdn-qty').on('input','.gdn-qty',function(){
    var block=$(this).closest('.gdn-item-block');
    stockSetBaseQty($(this),block.find('.gdn-unit-select'),block.find('.gdn-base-qty'));
    window.gdnCalculateRow(this);
});
$(document).off('input','.gdn-price').on('input','.gdn-price',function(){
    window.gdnCalculateRow(this);
});
$(document).off('change','.gdn-vat-select').on('change','.gdn-vat-select',function(){
    window.gdnCalculateRow(this);
});
var gdnCurrDiv=document.getElementById('currency');
if(gdnCurrDiv){
    new MutationObserver(function(){
        setTimeout(function(){
            $('.gdn-item-block').each(function(){
                window.gdnCalculateRow($(this).find('.gdn-qty').get(0));
            });
        },150);
    }).observe(gdnCurrDiv,{childList:true,subtree:true});
}
window.gdnItemCount=1;
window.gdnAddItem=function(){
    window.gdnItemCount++;
    var newBlock=$('.gdn-item-block:first').clone();
    newBlock.find('.badge').text('ITEM '+window.gdnItemCount);
    newBlock.find('input').val('');
    newBlock.find('.item-img-preview').remove(); 
    newBlock.find('select').each(function(){
        try{$(this).select2('destroy');}catch(e){}
        $(this).next('.select2-container').remove();
        $(this).removeClass('select2-hidden-accessible').show();
        this.selectedIndex=0;
    });
    newBlock.find('.gdn-unit-select').html('<option value="">Select item first</option>').prop('disabled',true);
    $('#gdn-items-container').append(newBlock);
    newBlock.find('.select2-form').select2({placeholder:"Select an option"});
    newBlock.find('.select2bs4-form').select2({theme:'bootstrap4'});
    formatNumber();
};
window.gdnRemoveItem=function(btn){
    if($('.gdn-item-block').length>1){
        $(btn).closest('.gdn-item-block').remove();
        $('.gdn-item-block').each(function(i){
            $(this).find('.badge').text('ITEM '+(i+1));
        });
        window.gdnItemCount=$('.gdn-item-block').length;
        $('.gdn-item-block').each(function(){
            window.gdnCalculateRow($(this).find('.gdn-qty').get(0));
        });
    }
};
initializeSelect();
formatNumber();
</script>
<script>
var approvedStockDates = <?= json_encode(array_map(function($r){
    return ['date' => $r['requested_date'], 'type' => $r['transaction_type']];
}, $approvedDateRequests ?: [])) ?>;
</script>
<?php
    }

if(isset($_GET['stock_transfer_input'])) {
?>
<form action="./controllers/stock_controllers.php" method="post" class="was-validated">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 my-2">
                <label class="form-label">Voucher Reference</label>
                <input type="text" name="voucher_ref" readonly value="<?= generateRefNumber($conn, 'STR/'.date('Y').'/'); ?>" class="form-control">
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Date</label>
                <input type="date" required name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" class="form-control stock-date-input" data-type="transfer">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 my-2">
                <label class="form-label">Transfer From:</label>
                <select name="from_store_id" class="form-control select2-form select2bs4-form" required <?= $cost_center ? 'disabled' : '' ?>>
                    <option value="">Select below</option>
                    <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                        <option value="<?= $store['id'] ?>" <?= $store['id'] == $cost_center ? 'selected' : '' ?>><?= $store['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if($cost_center): ?>
                    <input type="hidden" name="from_store_id" value="<?= $cost_center ?>">
                <?php endif; ?>
            </div>
            <div class="col-md-6 my-2">
                <label class="form-label">Transfer To:</label>
                <select name="to_store_id" class="form-control select2-form select2bs4-form" required>
                    <option value="">Select below</option>
                    <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                        <option value="<?= $store['id'] ?>"><?= $store['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <hr>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <strong>Line Items</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="strAddItem()">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
        <div id="str-items-container">
            <div class="border rounded p-3 mb-3 bg-white position-relative str-item-block">
                <span class="badge badge-primary position-absolute" style="top:-10px;left:14px;">ITEM 1</span>
                <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:10px;" onclick="strRemoveItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-12 col-md-6 form-group">
                            <label class="form-label">Item</label>
                            <select name="item[]" class="form-control select2-form select2bs4-form str-item-select" required>
                                <option value="">Select below</option>
                                <?php if($minSubs && is_array($minSubs)) foreach($minSubs as $minSub): ?>
                                    <option value="<?= $minSub['id'] ?>"><?= $minSub['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Unit</label>
                            <select name="unit[]" class="form-control str-unit-select" required disabled>
                                <option value="">Select item first</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Quantity</label>
                            <input type="text" name="quantity[]" class="form-control format-number str-qty" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label base-unit-label">Base Quantity</label>
                            <input type="text" name="base_quantity[]" class="form-control str-base-qty" readonly>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Unit Price</label>
                            <input type="text" name="price[]" class="form-control format-number str-price" placeholder="0.00">
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Amount</label>
                            <input type="text" name="amount[]" class="form-control str-amount" readonly placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-block mb-3" onclick="strAddItem()">
            <i class="fas fa-plus"></i> Add Another Item
        </button>
        <div class="row justify-content-end">
            <div class="col-md-5 col-12">
                <table class="table table-sm table-bordered">
                    <tr class="table-success">
                        <td><strong>Grand Total</strong></td>
                        <td><input name="grand_total" id="str-grand-total" class="form-control form-control-sm" readonly></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" name="addstocktransfer" class="btn btn-success w-100">POST</button>
    </div>
</form>
<script>
console.log('[STR] Form loaded');
function strGetFromStoreId(){
    var v=$('#form-container-transfer select[name="from_store_id"] option:selected').val();
    if(!v) v=$('#form-container-transfer input[name="from_store_id"]').val();
    return v||'';
}
function strUpdateGrandTotal(){
    var total=0;
    $('.str-item-block').each(function(){
        total+=parseFloat($(this).find('.str-amount').val().replace(/,/g,''))||0;
    });
    $('#str-grand-total').val(total.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
}
$(document).off('change','.str-item-select').on('change','.str-item-select',function(){
    var block=$(this).closest('.str-item-block');
    var itemId=$(this).val();
    var storeId=strGetFromStoreId();
    stockLoadUnits(itemId,block.find('.str-unit-select'),block.find('.str-base-qty'),storeId);
});
$(document).off('change','#form-container-transfer select[name="from_store_id"]').on('change','#form-container-transfer select[name="from_store_id"]',function(){
    var storeId=$(this).find('option:selected').val();
    $('.str-item-block').each(function(){
        var block=$(this);
        var itemId=block.find('.str-item-select').val();
        block.find('.str-qty').val('').css({'border-color':'','background-color':''});
        block.find('.str-base-qty').val('');
        if(itemId&&storeId){
            stockLoadUnits(itemId,block.find('.str-unit-select'),block.find('.str-base-qty'),storeId);
        }
    });
});
$(document).off('change','.str-unit-select').on('change','.str-unit-select',function(){
    var block=$(this).closest('.str-item-block');
    stockSetBaseQty(block.find('.str-qty'),$(this),block.find('.str-base-qty'));
});
$(document).off('input','.str-qty,.str-price').on('input','.str-qty,.str-price',function(){
    var block=$(this).closest('.str-item-block');
    stockSetBaseQty(block.find('.str-qty'),block.find('.str-unit-select'),block.find('.str-base-qty'));
    var qty=parseFloat(block.find('.str-qty').val().replace(/,/g,''))||0;
    var price=parseFloat(block.find('.str-price').val().replace(/,/g,''))||0;
    block.find('.str-amount').val((qty*price).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    strUpdateGrandTotal();
});
window.strItemCount=1;
window.strAddItem=function(){
    window.strItemCount++;
    var newBlock=$('.str-item-block:first').clone();
    newBlock.find('.badge').text('ITEM '+window.strItemCount);
    newBlock.find('input').val('');
    newBlock.find('.item-img-preview').remove();
    newBlock.find('select').each(function(){
        try{$(this).select2('destroy');}catch(e){}
        $(this).next('.select2-container').remove();
        $(this).removeClass('select2-hidden-accessible').show();
        this.selectedIndex=0;
    });
    newBlock.find('.str-unit-select').html('<option value="">Select item first</option>').prop('disabled',true);
    $('#str-items-container').append(newBlock);
    newBlock.find('.select2-form').select2({placeholder:"Select an option"});
    newBlock.find('.select2bs4-form').select2({theme:'bootstrap4'});
    formatNumber();
};
window.strRemoveItem=function(btn){
    if($('.str-item-block').length>1){
        $(btn).closest('.str-item-block').remove();
        $('.str-item-block').each(function(i){
            $(this).find('.badge').text('ITEM '+(i+1));
        });
        window.strItemCount=$('.str-item-block').length;
        strUpdateGrandTotal();
    }
};
initializeSelect();
formatNumber();
</script>
<script>
var approvedStockDates = <?= json_encode(array_map(function($r){
    return ['date' => $r['requested_date'], 'type' => $r['transaction_type']];
}, $approvedDateRequests ?: [])) ?>;
</script>
<?php
    }
}
?>

 