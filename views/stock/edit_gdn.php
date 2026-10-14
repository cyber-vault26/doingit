<?php
$batch     = (int) $_GET['batch'];
$type      = 'out';
$rows      = selectStockBatchesByType($conn, $type, null, null, null, null, $batch);
if(!$rows || !is_array($rows) || count($rows) === 0){
    echo "<div class='alert alert-danger'>Record not found.</div>";
    return;
}
$first       = $rows[0];
$ref_no      = $first['ref_no'];
$date        = $first['date_'];
$supplier_id = $first['supplier_id'];
$store_id    = $first['store_id'];
$currency_id = $first['currency_id'];
$voucher_id  = $first['voucher_id'];
$minSubs     = selectAllMinSub($conn);
$suppliers   = selectAllSubsidiaries($conn);
$stores      = selectAllStockStores($conn);
?>

<form action="./controllers/stock_controllers.php" method="post" class="was-validated">

    <input type="hidden" name="batch"      value="<?= $batch ?>">
    <input type="hidden"   name="voucher_id" value="<?= $voucher_id ?>">
    <input type="hidden" name="type"       value="out">
    <input type="hidden" id="egdn_delete_ids" name="delete_ids" value="">
  

    <div class="card-body">

        <div class="row">
            <div class="col-md-4 my-2">
                <label class="form-label">Voucher Reference</label>
                <input type="text" name="voucher_ref" readonly value="<?= htmlspecialchars($ref_no) ?>" class="form-control">
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Date</label>
                <input type="date" required name="date" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date) ?>" class="form-control">
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Issued To</label>
                <select name="supplier" class="form-control select2-form select2bs4-form" required>
                    <option value="">Select below</option>
                    <?php foreach($suppliers as $s):
                        $sel = ($s['id'] == $supplier_id) ? 'selected' : '';
                        echo "<option value='{$s['id']}' $sel>{$s['name']}</option>";
                    endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 my-2">
                <label class="form-label">Issued From</label>
                <select name="store_id_select" class="form-control select2-form select2bs4-form" required>
                    <option value="">Select below</option>
                    <?php foreach($stores as $st):
                        $sel = ($st['id'] == $store_id) ? 'selected' : '';
                        echo "<option value='{$st['id']}' $sel>{$st['name']}</option>";
                    endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-control select2-form select2bs4-form" onchange="selectCurrency(this.value)" required>
                    <option value="">Select below</option>
                    <?php
                    $curQuery = $conn->query("SELECT * FROM currency");
                    while($cur = $curQuery->fetch_assoc()):
                        $sel = ($cur['id'] == $currency_id) ? 'selected' : '';
                        echo "<option value='{$cur['id']}' $sel>{$cur['name']}</option>";
                    endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 my-2">
                <label class="form-label">Exchange Rate</label>
                <div id="currency">
                    <input type="text" name="curr" id="curr_value" class="form-control format-number" required
                           placeholder="Select currency first"
                           value="<?= number_format($first['currency_rate'], 4) ?>">
                </div>
            </div>
        </div>

        <hr>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <strong>Line Items</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="egdnAddItem()">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>

        <div id="egdn-items-container">
            <?php $itemCount = 0; foreach($rows as $row): $itemCount++; ?>
            <div class="border rounded p-3 mb-3 bg-white position-relative egdn-item-block">
                <span class="badge badge-primary position-absolute" style="top:-10px;left:14px;">ITEM <?= $itemCount ?></span>
                <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:10px;" onclick="egdnRemoveItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
                <div class="mt-3">
                    <input type="hidden" name="stock_id[]" value="<?= $row['id'] ?>">
                    <input type="hidden" name="is_new[]"   value="0">
                    <div class="row">
                        <div class="col-12 col-md-6 form-group">
                            <label class="form-label">Item</label>
                            <select name="item[]" class="form-control select2-form select2bs4-form egdn-item-select" required>
                                <option value="">Select below</option>
                                <?php foreach($minSubs as $minSub):
                                    $sel = ($minSub['id'] == $row['item_id']) ? 'selected' : '';
                                    echo "<option value='{$minSub['id']}' $sel>{$minSub['name']}</option>";
                                endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Unit</label>
                            <select name="unit[]" class="form-control egdn-unit-select" required data-selected-unit="<?= $row['unit'] ?>">
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Quantity</label>
                            <input type="text" name="quantity[]" class="form-control format-number egdn-qty" value="<?= $row['quantity'] ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label base-unit-label">Base Quantity</label>
                            <input type="text" name="base_quantity[]" class="form-control egdn-base-qty" value="<?= $row['base_quantity'] ?>" readonly>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Price</label>
                            <input type="text" name="price[]" class="form-control format-number egdn-price" value="<?= number_format($row['price'], 2) ?>" required>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Amount</label>
                            <input type="text" name="amount[]" class="form-control egdn-amt-col" value="<?= number_format($row['quantity'] * $row['price'], 2) ?>" readonly>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">Equiv Amount</label>
                            <input type="text" name="eqv[]" class="form-control egdn-amount-col" value="<?= number_format($row['quantity'] * $row['price'] * $first['currency_rate'], 2) ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">VAT %</label>
                            <select name="rates[]" class="form-control egdn-vat-select" required>
                                <option value="0"  <?= ($row['tax_rate'] == 0)  ? 'selected' : '' ?>>0%</option>
                                <option value="18" <?= ($row['tax_rate'] == 18) ? 'selected' : '' ?>>18%</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="form-label">VAT Amount</label>
                            <input type="text" name="vat_amount[]" class="form-control egdn-vat-amt-col" value="<?= number_format(($row['quantity'] * $row['price'] * $first['currency_rate']) * ($row['tax_rate'] / 100), 2) ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-outline-primary btn-block mb-3" onclick="egdnAddItem()">
            <i class="fas fa-plus"></i> Add Another Item
        </button>

        <div class="row justify-content-end">
            <div class="col-md-5 col-12">
                <table class="table table-sm table-bordered">
                    <tr>
                        <td><strong>Sub Total</strong></td>
                        <td><input name="subtotal" id="egdn_subtotal" class="form-control form-control-sm" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Total VAT</strong></td>
                        <td><input name="tax" id="egdn_tax" class="form-control form-control-sm" readonly></td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Grand Total</strong></td>
                        <td><input name="total" id="egdn_total" class="form-control form-control-sm" readonly></td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    <div class="card-footer">
        <button type="submit" name="editgdn" class="btn btn-warning w-100">UPDATE</button>
    </div>

</form>

<script>
function egdnCalculateRow(el){
    var block=$(el).closest('.egdn-item-block');
    var qty=parseFloat(block.find('.egdn-qty').val().replace(/,/g,''))||0;
    var price=parseFloat(block.find('.egdn-price').val().replace(/,/g,''))||0;
    var vat=parseFloat(block.find('.egdn-vat-select').val())||0;
    var curr=parseFloat($('#curr_value').val().replace(/,/g,''))||0;
    var local=qty*price;
    var equiv=local*curr;
    var vatAmt=equiv*(vat/100);
    block.find('.egdn-amt-col').val(local.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    block.find('.egdn-amount-col').val(equiv.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    block.find('.egdn-vat-amt-col').val(vatAmt.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    egdnUpdateTotals();
}
function egdnUpdateTotals(){
    var sub=0,totalVat=0;
    $('.egdn-item-block').each(function(){
        sub+=parseFloat($(this).find('.egdn-amount-col').val().replace(/,/g,''))||0;
        totalVat+=parseFloat($(this).find('.egdn-vat-amt-col').val().replace(/,/g,''))||0;
    });
    $('#egdn_subtotal').val(sub.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#egdn_tax').val(totalVat.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    $('#egdn_total').val((sub+totalVat).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
}
function egrnLoadUnits(itemId,unitSel,baseQtyEl,preSelectUnit,storeId,excludeBaseQty,callback){
    if(!itemId) return;
    unitSel.html('<option value="">Loading...</option>').prop('disabled',true).removeAttr('data-balance');
    baseQtyEl.val('');
    $.get('./requests/stock_requests.php',{
        get_item_units:1,
        item_id:itemId,
        store_id:storeId||'',
        exclude_base_qty:excludeBaseQty||0
    },function(data){
        if(!data||typeof data!=='object'){
            unitSel.html('<option value="">Error</option>').prop('disabled',false);
            return;
        }
        var balance=parseFloat(data.base_unit_balance)||0;
        if(storeId){
            unitSel.attr('data-balance',balance);
        } else {
            unitSel.attr('data-balance','Infinity');
        }
        unitSel.html('');
        unitSel.append('<option value="'+data.base_unit+'" data-factor="1">'+data.base_unit_name+' ('+balance.toLocaleString('en-US',{minimumFractionDigits:2})+' left)</option>');
        $.each(data.alt_units||[],function(i,u){
            unitSel.append('<option value="'+u.id+'" data-factor="'+u.factor+'">'+u.name+' ('+parseFloat(u.balance_in_unit).toLocaleString('en-US',{minimumFractionDigits:2})+' left)</option>');
        });
        if(preSelectUnit){
            unitSel.val(preSelectUnit);
        } else {
            unitSel.prop('selectedIndex',0);
        }
        unitSel.prop('disabled',false);
        unitSel.closest('.egdn-item-block').find('.base-unit-label').text('Base Quantity ('+data.base_unit_name+')');
        if(typeof callback==='function') callback();
    },'json').fail(function(){
        unitSel.html('<option value="">Failed to load</option>').prop('disabled',false);
    });
}
function egdnBindBlock(block){
    var itemSel=block.find('.egdn-item-select');
    var unitSel=block.find('.egdn-unit-select');
    var baseQtyEl=block.find('.egdn-base-qty');
    var qtyEl=block.find('.egdn-qty');
    itemSel.off('select2:select').on('select2:select',function(){
        var storeId=$('select[name="store_id_select"]').val()||'';
        egrnLoadUnits($(this).val(),unitSel,baseQtyEl,null,storeId,0,function(){
            stockSetBaseQty(qtyEl,unitSel,baseQtyEl);
            egdnCalculateRow(qtyEl.get(0));
        });
    });
    unitSel.off('change').on('change',function(){
        stockSetBaseQty(qtyEl,unitSel,baseQtyEl);
        egdnCalculateRow(qtyEl.get(0));
    });
    qtyEl.off('input').on('input',function(){
        stockSetBaseQty($(this),block.find('.egdn-unit-select'),block.find('.egdn-base-qty'));
        egdnCalculateRow($(this).get(0));
    });
}
$(document).off('input.egdn','.egdn-qty,.egdn-price').on('input.egdn','.egdn-qty,.egdn-price',function(){
    egdnCalculateRow(this);
});
$(document).off('change.egdn','.egdn-vat-select').on('change.egdn','.egdn-vat-select',function(){
    egdnCalculateRow(this);
});
var egdnCurrDiv=document.getElementById('currency');
if(egdnCurrDiv){
    new MutationObserver(function(){
        setTimeout(function(){
            $('.egdn-item-block').each(function(){
                egdnCalculateRow($(this).find('.egdn-qty').get(0));
            });
        },150);
    }).observe(egdnCurrDiv,{childList:true,subtree:true});
}
$(document).off('input.egdncurr','#curr_value').on('input.egdncurr','#curr_value',function(){
    $('.egdn-item-block').each(function(){
        egdnCalculateRow($(this).find('.egdn-qty').get(0));
    });
});
$(document).ready(function(){
    $('.egdn-item-block').each(function(){
        var block=$(this);
        var itemId=block.find('.egdn-item-select').val();
        var unitSel=block.find('.egdn-unit-select');
        var baseQtyEl=block.find('.egdn-base-qty');
        var qtyEl=block.find('.egdn-qty');
        var preSelected=unitSel.data('selected-unit');
        var storeId=$('select[name="store_id_select"]').val()||'';
        var originalBase=parseFloat(baseQtyEl.val())||0;
        var originalQty=parseFloat(qtyEl.val())||0;
        egdnBindBlock(block);
        egrnLoadUnits(itemId,unitSel,baseQtyEl,preSelected,storeId,originalBase,function(){
            baseQtyEl.val(originalBase);
            qtyEl.val(originalQty);
            egdnCalculateRow(qtyEl.get(0));
        });
    });
    $('select[name="store_id_select"]').off('select2:select').on('select2:select',function(){
        var storeId=$(this).val();
        $('.egdn-item-block').each(function(){
            var block=$(this);
            var itemId=block.find('.egdn-item-select').val();
            var unitSel=block.find('.egdn-unit-select');
            var baseQtyEl=block.find('.egdn-base-qty');
            var qtyEl=block.find('.egdn-qty');
            var originalBase=parseFloat(baseQtyEl.val())||0;
            qtyEl.val('').css({'border-color':'','background-color':''});
            baseQtyEl.val('');
            if(itemId&&storeId){
                egrnLoadUnits(itemId,unitSel,baseQtyEl,unitSel.val(),storeId,originalBase,function(){
                    egdnCalculateRow(qtyEl.get(0));
                });
            }
        });
    });
    egdnUpdateTotals();
    initializeSelect();
    formatNumber();
    if($('#curr_value').val()){
        selectCurrency($('select[name="currency"]').val());
    }
});
window.egdnItemCount=<?= $itemCount ?>;
function egdnAddItem(){
    window.egdnItemCount++;
    var newBlock=$('.egdn-item-block:first').clone();
    newBlock.find('.badge').text('ITEM '+window.egdnItemCount);
    newBlock.find('input[name="stock_id[]"]').val('0');
    newBlock.find('input[name="is_new[]"]').val('1');
    newBlock.find('input').not('[name="stock_id[]"]').not('[name="is_new[]"]').val('');
    newBlock.find('select').each(function(){
        try{$(this).select2('destroy');}catch(e){}
        $(this).next('.select2-container').remove();
        $(this).removeClass('select2-hidden-accessible').removeAttr('data-select2-id').show();
        this.selectedIndex=0;
    });
    newBlock.find('.egdn-unit-select').html('<option value="">Select item first</option>').prop('disabled',true);
    $('#egdn-items-container').append(newBlock);
    newBlock.find('.select2-form').select2({placeholder:'Select an option'});
    newBlock.find('.select2bs4-form').select2({theme:'bootstrap4'});
    egdnBindBlock(newBlock);
    formatNumber();
}
function egdnRemoveItem(btn){
    if($('.egdn-item-block').length<=1) return;
    var block=$(btn).closest('.egdn-item-block');
    var stockId=block.find('input[name="stock_id[]"]').val();
    if(stockId&&stockId!='0'){
        var current=$('#egdn_delete_ids').val();
        $('#egdn_delete_ids').val(current?current+','+stockId:stockId);
    }
    block.remove();
    $('.egdn-item-block').each(function(i){
        $(this).find('.badge').text('ITEM '+(i+1));
    });
    window.egdnItemCount=$('.egdn-item-block').length;
    egdnUpdateTotals();
}
</script>