<?php
$batch     = (int) $_GET['batch'];
$type      = 'in';
$rows = selectStockBatchesByType($conn, $type, null, null, null, null, $batch);
if(!$rows || !is_array($rows) || count($rows) === 0){
    echo "<div class='alert alert-danger'>Record not found.</div>";
    return;
}
$first             = $rows[0];
$ref_no            = $first['ref_no'];
$date              = $first['date_'];
$supplier_id       = $first['supplier_id'];
$store_id          = $first['store_id'];
$purchase_order_id = $first['purchase_order_id'];
$minSubs           = selectAllMinSub($conn);
$suppliers         = selectAllSubsidiaries($conn);
$stores            = selectAllStockStores($conn);
?>

<form action="./controllers/stock_controllers.php" method="post" class="was-validated">

    <input type="hidden" name="batch"      value="<?= $batch ?>">
    <input type="hidden" name="type"       value="in">
    <input type="hidden" id="delete_ids"   name="delete_ids" value="">

    <div class="card card-info">
        <div class="card-header">
            <h4 class="card-title">Edit Received Stock (GRN)</h4>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-md-3 my-2">
                    <label class="form-label">Voucher Reference</label>
                    <input type="text" name="voucher_ref" readonly value="<?= htmlspecialchars($ref_no) ?>" class="form-control">
                </div>
                <div class="col-md-3 my-2">
                    <label class="form-label">PO Number</label>
                    <input type="number" name="purchase_order_id" class="form-control" placeholder="Enter PO Number" value="<?= htmlspecialchars($purchase_order_id) ?>">
                </div>
                <div class="col-md-3 my-2">
                    <label class="form-label">Date</label>
                    <input type="date" required name="date" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date) ?>" class="form-control">
                </div>
                <div class="col-md-3 my-2">
                    <label class="form-label">Supplier</label>
                    <select name="supplier" class="form-control select2-form select2bs4-form" required>
                        <option value="">Select below</option>
                        <?php foreach($suppliers as $s):
                            $sel = ($s['id'] == $supplier_id) ? 'selected' : '';
                            echo "<option value='{$s['id']}' $sel>{$s['name']}</option>";
                        endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 my-2">
                    <label class="form-label">Received To</label>
                    <select name="store_id_select" class="form-control select2-form select2bs4-form" required>
                        <option value="">Select below</option>
                        <?php foreach($stores as $st):
                            $sel = ($st['id'] == $store_id) ? 'selected' : '';
                            echo "<option value='{$st['id']}' $sel>{$st['name']}</option>";
                        endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12 my-2">
                    <button type="button" class="btn btn-info btn-sm"   onclick="egrnAddRow()">Add Row</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="egrnRemoveRow()">Remove Row</button>
                </div>
            </div>

            <style>
                #grn-edit-table { table-layout: fixed; width: 100%; }
                #grn-edit-table th:nth-child(1), #grn-edit-table td:nth-child(1) { width: 28%; }
                #grn-edit-table th:nth-child(2), #grn-edit-table td:nth-child(2) { width: 13%; }
                #grn-edit-table th:nth-child(3), #grn-edit-table td:nth-child(3) { width: 12%; }
                #grn-edit-table th:nth-child(4), #grn-edit-table td:nth-child(4) { width: 15%; }
                #grn-edit-table th:nth-child(5), #grn-edit-table td:nth-child(5) { width: 15%; }
                #grn-edit-table th:nth-child(6), #grn-edit-table td:nth-child(6) { width: 17%; }
                #grn-edit-table td select, #grn-edit-table td input { width: 100%; }
            </style>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="grn-edit-table">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Item</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Base Quantity</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $row): ?>
                        <tr class="data-row" data-stock-id="<?= $row['id'] ?>" data-is-new="0">
                            <td>
                                <input type="hidden" name="stock_id[]" value="<?= $row['id'] ?>">
                                <input type="hidden" name="is_new[]"   value="0">
                                <select name="item[]" class="form-control select2-form select2bs4-form egrn-item-select" required>
                                    <option value="">Select below</option>
                                    <?php foreach($minSubs as $minSub):
                                        $sel = ($minSub['id'] == $row['item_id']) ? 'selected' : '';
                                        echo "<option value='{$minSub['id']}' $sel>{$minSub['name']}</option>";
                                    endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="unit[]" class="form-control egrn-unit-select" required data-selected-unit="<?= $row['unit'] ?>">
                                    <option value="">Loading...</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="quantity[]"      class="form-control format-number egrn-qty"   value="<?= $row['quantity'] ?>" required>
                            </td>
                            <td>
                                <input type="text" name="base_quantity[]" class="form-control egrn-base-qty"            value="<?= $row['base_quantity'] ?>" readonly>
                            </td>
                            <td>
                                <input type="text" name="price[]"         class="form-control format-number egrn-price" value="<?= number_format($row['price'], 2) ?>" placeholder="0.00">
                            </td>
                            <td>
                                <input type="text" name="amount[]"        class="form-control egrn-amount" readonly
                                    value="<?= number_format($row['quantity'] * $row['price'], 2) ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-5 col-12">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <td><strong>Grand Total</strong></td>
                            <td><input id="egrn-grand-total" name="grand_total" class="form-control form-control-sm" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" name="editgrn" class="btn btn-warning w-100">UPDATE</button>
        </div>
    </div>

</form>

<script>
function egrnCalc(row){
    var qty   = parseFloat(row.find('.egrn-qty').val().replace(/,/g,''))   || 0;
    var price = parseFloat(row.find('.egrn-price').val().replace(/,/g,'')) || 0;
    row.find('.egrn-amount').val((qty * price).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
    egrnGrandTotal();
}

function egrnGrandTotal(){
    var total = 0;
    $('#grn-edit-table tbody tr.data-row').each(function(){
        total += parseFloat($(this).find('.egrn-amount').val().replace(/,/g,'')) || 0;
    });
    $('#egrn-grand-total').val(total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
}

function egrnLoadUnits(itemId, unitSel, baseQtyEl, preSelectUnit, callback){
    if(!itemId){ return; }
    unitSel.html('<option value="">Loading...</option>').prop('disabled', true);
    baseQtyEl.val('');
    $.get('./requests/stock_requests.php', {get_item_units:1, item_id:itemId}, function(data){
        if(!data || typeof data !== 'object') return;
        unitSel.html('');
        unitSel.append('<option value="'+data.base_unit+'" data-factor="1">'+data.base_unit_name+'</option>');
        $.each(data.alt_units||[], function(i,u){
            unitSel.append('<option value="'+u.id+'" data-factor="'+u.factor+'">'+u.name+'</option>');
        });
        if(preSelectUnit){
            unitSel.val(preSelectUnit);
        } else {
            unitSel.prop('selectedIndex', 0);
        }
        unitSel.prop('disabled', false);
        if(typeof callback === 'function') callback();
    }, 'json').fail(function(){
        unitSel.html('<option value="">Failed to load</option>').prop('disabled', false);
    });
}

function egrnBindRow(row){
    var itemSel   = row.find('.egrn-item-select');
    var unitSel   = row.find('.egrn-unit-select');
    var baseQtyEl = row.find('.egrn-base-qty');
    var qtyEl     = row.find('.egrn-qty');

    itemSel.off('select2:select').on('select2:select', function(){
        var itemId = $(this).val();
        baseQtyEl.val('');
        unitSel.html('<option value="">Loading...</option>').prop('disabled', true);
        egrnLoadUnits(itemId, unitSel, baseQtyEl, null, function(){
            stockSetBaseQty(qtyEl, unitSel, baseQtyEl);
            egrnCalc(row);
        });
    });

    unitSel.off('change').on('change', function(){
        stockSetBaseQty(qtyEl, unitSel, baseQtyEl);
        egrnCalc(row);
    });

    qtyEl.off('input').on('input', function(){
        stockSetBaseQty(qtyEl, unitSel, baseQtyEl);
    });
}

$(document).off('input.egrn', '.egrn-qty, .egrn-price').on('input.egrn', '.egrn-qty, .egrn-price', function(){
    var row = $(this).closest('tr');
    egrnCalc(row);
});

$(document).ready(function(){
    $('#grn-edit-table tbody tr.data-row').each(function(){
        var row         = $(this);
        var itemId      = row.find('.egrn-item-select').val();
        var unitSel     = row.find('.egrn-unit-select');
        var baseQtyEl   = row.find('.egrn-base-qty');
        var preSelected = unitSel.data('selected-unit');
        egrnBindRow(row);
        egrnLoadUnits(itemId, unitSel, baseQtyEl, preSelected, function(){
            stockSetBaseQty(row.find('.egrn-qty'), unitSel, baseQtyEl);
            egrnCalc(row);
        });
    });
    egrnGrandTotal();
});

function egrnAddRow(){
    var tbody  = $('#grn-edit-table tbody');
    var newRow = tbody.find('tr.data-row:first').clone();
    newRow.attr('data-stock-id', '0');
    newRow.attr('data-is-new', '1');
    newRow.find('input[name="stock_id[]"]').val('0');
    newRow.find('input[name="is_new[]"]').val('1');
    newRow.find('span.select2').remove();
    newRow.find('select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id').show().val('');
    newRow.find('.egrn-unit-select').html('<option value="">Select item first</option>').prop('disabled', true);
    newRow.find('input').not('[name="stock_id[]"]').not('[name="is_new[]"]').val('');
    tbody.append(newRow);
    newRow.find('.egrn-item-select').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    egrnBindRow(newRow);
}

function egrnRemoveRow(){
    var tbody = $('#grn-edit-table tbody');
    if(tbody.find('tr.data-row').length <= 1) return;
    var lastRow = tbody.find('tr.data-row:last');
    var stockId = lastRow.data('stock-id');
    if(stockId && stockId != 0){
        var current = $('#delete_ids').val();
        $('#delete_ids').val(current ? current + ',' + stockId : stockId);
    }
    lastRow.remove();
    egrnGrandTotal();
}
</script>