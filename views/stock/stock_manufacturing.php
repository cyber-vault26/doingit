<?php
$minSubs  = selectAllMinSub($conn);
$units    = selectAllUnit($conn);
$orderIds = isset($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : [];
$orders   = [];
foreach($orderIds as $oid){
    $o = selectManufacturingOrderById($conn, $oid);
    if($o && is_array($o)) $orders[] = $o;
}

$minSubsJson = json_encode($minSubs ?: []);
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">New Manufacturing Entry</h3>
    </div>
    <form action="./controllers/stock_controllers.php" method="POST" class="was-validated">
        <div class="card-body">
            <?php if($orders): ?>
            <div class="mb-3">
                <strong>Manufactured Orders</strong>
                <ul class="list-unstyled mt-1 mb-0">
                    <?php foreach($orders as $order): ?>
                    <li>
                        <input type="hidden" name="order_ids[]" value="<?= $order['id'] ?>">
                        <h6 class="mb-0 d-inline"><?= htmlspecialchars($order['reference_no']) ?></h6>
                        &nbsp;&mdash;&nbsp;<a href="#" onclick="openPreviewOrder(<?= $order['id'] ?>); return false;">Preview</a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <hr>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Batch / Reference No</label>
                        <input type="text" name="batch_ref" class="form-control" readonly
                               value="<?= generateRefNumber($conn, 'MFG/BATCH/'.date('Y').'/') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Manufacture Date</label>
                        <input type="date" name="manufacture_date" class="form-control"
                               max="<?= date('Y-m-d') ?>" value="" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Notes <small class="text-muted">(optional)</small></label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Raw Materials Used</strong>
                <div>
                    <button type="button" class="btn btn-info btn-xs" onclick="mfgAddRawRow()">Add Row</button>
                    <button type="button" class="btn btn-danger btn-xs" onclick="mfgRemoveTableRow('mfg-raw-table')">Remove Row</button>
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered" id="mfg-raw-table" style="table-layout:fixed;width:100%;">
                    <thead class="bg-warning">
                        <tr>
                            <th style="width:35%;">Material</th>
                            <th style="width:20%;">Unit</th>
                            <th style="width:15%;">Qty Used</th>
                            <th style="width:15%;">Base Qty</th>
                            <th style="width:15%;">Wastage</th>
                        </tr>
                    </thead>
                    <tbody id="mfg-raw-tbody"></tbody>
                </table>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Finished Goods Produced</strong>
                <div>
                    <button type="button" class="btn btn-info btn-xs" onclick="mfgAddFinishedRow()">Add Row</button>
                    <button type="button" class="btn btn-danger btn-xs" onclick="mfgRemoveTableRow('mfg-finished-table')">Remove Row</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="mfg-finished-table" style="table-layout:fixed;width:100%;">
                    <thead class="bg-success">
                        <tr>
                            <th style="width:30%;">Product</th>
                            <th style="width:20%;">Unit</th>
                            <th style="width:15%;">Qty Produced</th>
                            <th style="width:15%;">Base Qty</th>
                            <th style="width:20%;">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody id="mfg-finished-tbody"></tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" name="save_manufacturing_entry" class="btn btn-success btn-sm">Save & Add to Store</button>
            <a href="./?page=stock_manufacturing" class="btn btn-secondary btn-sm ml-2">Reset</a>
        </div>
    </form>
</div>

<div class="modal fade" id="previewOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Order Preview</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="preview_order_body">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var MFG_ITEMS = <?= $minSubsJson ?>;

function buildItemOptions(){
    var html='<option value="">Select below</option>';
    $.each(MFG_ITEMS, function(i, ms){
        html+='<option value="'+ms.id+'">'+$('<div>').text(ms.name).html()+'</option>';
    });
    return html;
}

function mfgBuildRawRow(){
    var tr=$('<tr class="data-row">');
    tr.html(
        '<td><select name="raw_item[]" class="form-control mfg-raw-item-select" required>'+buildItemOptions()+'</select></td>'+
        '<td><select name="raw_unit[]" class="form-control mfg-raw-unit-select" required disabled><option value="">Select item first</option></select></td>'+
        '<td><input type="text" name="raw_qty[]" class="form-control format-number mfg-raw-qty" placeholder="0.00" required></td>'+
        '<td><input type="text" name="raw_base_qty[]" class="form-control mfg-raw-base-qty" readonly></td>'+
        '<td><input type="text" name="raw_waste[]" class="form-control format-number" placeholder="0.00"></td>'
    );
    return tr;
}

function mfgBuildFinishedRow(){
    var tr=$('<tr class="data-row">');
    tr.html(
        '<td><select name="prod_item[]" class="form-control mfg-prod-item-select" required>'+buildItemOptions()+'</select></td>'+
        '<td><select name="prod_unit[]" class="form-control mfg-prod-unit-select" required disabled><option value="">Select item first</option></select></td>'+
        '<td><input type="text" name="prod_qty[]" class="form-control format-number mfg-prod-qty" placeholder="0.00" required></td>'+
        '<td><input type="text" name="prod_base_qty[]" class="form-control mfg-prod-base-qty" readonly></td>'+
        '<td><input type="date" name="prod_expiry_date[]" class="form-control"></td>'
    );
    return tr;
}

function mfgInitRowSelect2(tr){
    var itemSel = tr.find('.mfg-raw-item-select, .mfg-prod-item-select');
    itemSel.select2({
        theme: 'bootstrap4',
        placeholder: 'Select an option',
        width: '100%'
    });
    itemSel.on('select2:select', function(){
        var row = $(this).closest('tr');
        var itemId = $(this).val();
        if($(this).hasClass('mfg-raw-item-select')){
            stockLoadUnits(itemId, row.find('.mfg-raw-unit-select'), row.find('.mfg-raw-base-qty'));
        } else {
            stockLoadUnits(itemId, row.find('.mfg-prod-unit-select'), row.find('.mfg-prod-base-qty'));
        }
    });
}

function mfgAddRawRow(){
    var tr=mfgBuildRawRow();
    $('#mfg-raw-tbody').append(tr);
    mfgInitRowSelect2(tr);
    if(typeof formatNumber==='function') formatNumber();
}

function mfgAddFinishedRow(){
    var tr=mfgBuildFinishedRow();
    $('#mfg-finished-tbody').append(tr);
    mfgInitRowSelect2(tr);
    if(typeof formatNumber==='function') formatNumber();
}

function mfgRemoveTableRow(tableId){
    var tbody=$('#'+tableId).find('tbody');
    if(tbody.find('tr').length>1){
        tbody.find('tr:last').remove();
    }
}

function stockLoadUnits(item_id, unitSel, baseQtyEl){
    if(!item_id) return;
    unitSel.html('<option value="">Loading...</option>').prop('disabled', true);
    baseQtyEl.val('');
    $.get('./requests/stock_requests.php', {get_item_units:1, item_id:item_id}, function(data){
        if(!data || typeof data!=='object') return;
        unitSel.html('');
        unitSel.append('<option value="'+data.base_unit+'" data-factor="1" selected>'+data.base_unit_name+'</option>');
        $.each(data.alt_units||[], function(i,u){
            unitSel.append('<option value="'+u.id+'" data-factor="'+u.factor+'">'+u.name+'</option>');
        });
        unitSel.prop('disabled', false).trigger('change');
    }, 'json').fail(function(){
        unitSel.html('<option value="">Failed to load</option>').prop('disabled', false);
    });
}

function stockSetBaseQty(qtyEl, unitSel, baseQtyEl){
    var qty=parseFloat($(qtyEl).val().replace(/,/g,''))||0;
    var factor=parseFloat($(unitSel).find('option:selected').data('factor'))||1;
    var parts=(qty*factor).toFixed(4).split('.');
    parts[0]=Number(parts[0]).toLocaleString();
    $(baseQtyEl).val(parts.join('.'));
}

$(document).on('change', '.mfg-raw-unit-select', function(){
    var row=$(this).closest('tr');
    stockSetBaseQty(row.find('.mfg-raw-qty'), $(this), row.find('.mfg-raw-base-qty'));
});
$(document).on('input', '.mfg-raw-qty', function(){
    var row=$(this).closest('tr');
    stockSetBaseQty($(this), row.find('.mfg-raw-unit-select'), row.find('.mfg-raw-base-qty'));
});

$(document).on('change', '.mfg-prod-unit-select', function(){
    var row=$(this).closest('tr');
    stockSetBaseQty(row.find('.mfg-prod-qty'), $(this), row.find('.mfg-prod-base-qty'));
});
$(document).on('input', '.mfg-prod-qty', function(){
    var row=$(this).closest('tr');
    stockSetBaseQty($(this), row.find('.mfg-prod-unit-select'), row.find('.mfg-prod-base-qty'));
});

function openPreviewOrder(id){
    $('#preview_order_body').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $.get('./requests/stock_requests.php',{get_manufacturing_order_preview:1,order_id:id},function(response){
        $('#preview_order_body').html(response);
    }).fail(function(){
        $('#preview_order_body').html("<div class='alert alert-danger'>Failed to load preview.</div>");
    });
    $('#previewOrderModal').modal('show');
}

mfgAddRawRow();
mfgAddFinishedRow();
</script>