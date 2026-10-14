<?php
$customers = selectAllSubsidiaries($conn);
$minSubs   = selectAllMinSub($conn);
$units     = selectAllUnit($conn);
$pendingOrders   = selectManufacturingOrdersByStatus($conn, 'pending');
$completedOrders = selectManufacturingOrdersByStatus($conn, 'completed');
?>
<div class="text-end mb-3">
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createOrderModal">
        <i class="fas fa-plus"></i> Create Order
    </button>
</div>
<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="mfg-order-tabs" role="tablist">
            <li class="pt-2 px-3"><h3 class="card-title">Manufacturing Orders</h3></li>
            <li class="nav-item">
                <a class="nav-link active" id="tab-pending" data-toggle="pill" href="#tab-content-pending" role="tab" aria-selected="true">Pending Orders</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-completed" data-toggle="pill" href="#tab-content-completed" role="tab" aria-selected="false">Completed Orders</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-content-pending" role="tabpanel">
                <div class="bulk-actions mb-2" id="pending-bulk-actions" style="display:none;">
                    <form id="manufacture-form">
                        <button type="submit" class="btn btn-sm btn-success">Manufacture Selected</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-search table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="select-all-pending" onclick="toggleAllPending(this)" style="cursor:pointer;"></th>
                                <th>#</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($pendingOrders && is_array($pendingOrders)):
                                $i = 1; foreach($pendingOrders as $order): ?>
                            <tr>
                                <td><input type="checkbox" class="pending-select" value="<?= $order['id'] ?>" onchange="togglePendingRow(this)" style="cursor:pointer;"></td>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($order['reference_no']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= $order['date_'] ?></td>
                                <td><span class="badge badge-warning">Pending</span></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" onclick="openPreviewOrder(<?= $order['id'] ?>)">Preview</button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="openEditOrder(<?= $order['id'] ?>, '<?= addslashes($order['reference_no']) ?>', <?= $order['customer_id'] ?>, '<?= $order['date_'] ?>')">Edit</button>
                                    <form action="./controllers/stock_controllers.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit" name="delete_manufacturing_order" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="7" class="text-center">No pending orders</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-primary">
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-content-completed" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-search table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th>#</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($completedOrders && is_array($completedOrders)):
                                $i = 1; foreach($completedOrders as $order): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($order['reference_no']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= $order['date_'] ?></td>
                                <td><span class="badge badge-success">Completed</span></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center">No completed orders</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-primary">
                            <tr>
                                <th>#</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CREATE ORDER MODAL -->
<div class="modal fade" id="createOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Create Manufacturing Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/stock_controllers.php" method="post" class="was-validated">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 my-2">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" readonly value="<?= generateRefNumber($conn, 'MFG/'.date('Y').'/') ?>" class="form-control">
                        </div>
                        <div class="col-md-4 my-2">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-control select2-form select2bs4-form" required>
                                <option value="">Select below</option>
                                <?php if($customers && is_array($customers)) foreach($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 my-2">
                            <label class="form-label">Order Date</label>
                            <input type="date" name="date_" max="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                    </div>
                    <div class="row m-1">
                        <div class="col-md-12 my-2">
                            <button type="button" class="btn btn-info btn-sm" onclick="addTableRow('mfgitemstable')">Add Row</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeTableRow('mfgitemstable')">Remove Row</button>
                        </div>
                    </div>
                    <style>
                        #mfgitemstable, #editmfgitemstable { table-layout: fixed; width: 100%; }
                        #mfgitemstable th:nth-child(1), #mfgitemstable td:nth-child(1),
                        #editmfgitemstable th:nth-child(1), #editmfgitemstable td:nth-child(1) { width: 50%; }
                        #mfgitemstable th:nth-child(2), #mfgitemstable td:nth-child(2),
                        #editmfgitemstable th:nth-child(2), #editmfgitemstable td:nth-child(2) { width: 25%; }
                        #mfgitemstable th:nth-child(3), #mfgitemstable td:nth-child(3),
                        #editmfgitemstable th:nth-child(3), #editmfgitemstable td:nth-child(3) { width: 25%; }
                        #mfgitemstable td select, #mfgitemstable td input,
                        #editmfgitemstable td select, #editmfgitemstable td input { width: 100%; }
                    </style>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="mfgitemstable">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="data-row">
                                    <td>
                                        <select name="item[]" class="select2-form select2bs4-form form-control" required>
                                            <option value="">Select below</option>
                                            <?php if($minSubs && is_array($minSubs)) foreach($minSubs as $minSub): ?>
                                                <option value="<?= $minSub['id'] ?>"><?= $minSub['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="unit[]" class="select2-form select2bs4-form form-control" required>
                                            <option value="">Select below</option>
                                            <?php if($units && is_array($units)) foreach($units as $unit): ?>
                                                <option value="<?= $unit['id'] ?>"><?= $unit['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="quantity[]" min="0.01" step="any" class="form-control" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_manufacturing_order" class="btn btn-success">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT ORDER MODAL -->
<div class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Manufacturing Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/stock_controllers.php" method="post" class="was-validated">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="edit_order_id">
                    <div class="row">
                        <div class="col-md-4 my-2">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" id="edit_reference_no" readonly class="form-control">
                        </div>
                        <div class="col-md-4 my-2">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" id="edit_customer_id" class="form-control select2-form select2bs4-form" required>
                                <option value="">Select below</option>
                                <?php if($customers && is_array($customers)) foreach($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 my-2">
                            <label class="form-label">Order Date</label>
                            <input type="date" name="date_" id="edit_date_" max="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                    </div>
                    <div class="row m-1">
                        <div class="col-md-12 my-2">
                            <button type="button" class="btn btn-info btn-sm" onclick="addEditRow()">Add Row</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeTableRow('editmfgitemstable')">Remove Row</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="editmfgitemstable">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="edit_items_tbody">
                                <tr><td colspan="3" class="text-center"><div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_manufacturing_order" class="btn btn-warning">Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PREVIEW ORDER MODAL -->
<div class="modal fade" id="previewOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Order Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="preview_order_body">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAllPending(checkbox){
    $('.pending-select').prop('checked', checkbox.checked).each(function(){ togglePendingRow(this); });
}
function togglePendingRow(checkbox){
    var row = $(checkbox).closest('tr');
    $(checkbox).is(':checked') ? row.addClass('table-warning') : row.removeClass('table-warning');
    $('#pending-bulk-actions').toggle($('.pending-select:checked').length > 0);
}
document.getElementById('manufacture-form').addEventListener('submit', function(e){
    e.preventDefault();
    var ids = $('.pending-select:checked').map(function(){ return this.value; }).get();
    if(!ids.length) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = './?page=stock_manufacturing';
    ids.forEach(function(id){
        var input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'order_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
});
function fetchOrderItems(orderId){
    $('#edit_items_tbody').html('<tr><td colspan="3" class="text-center"><div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div></td></tr>');
    $.get('./requests/stock_requests.php', {
        get_manufacturing_order_items: 1,
        order_id: orderId
    }, function(response){
        $('#edit_items_tbody').html(response);
    }).fail(function(){
        $('#edit_items_tbody').html("<tr><td colspan='3'><div class='alert alert-danger'>Failed to load items.</div></td></tr>");
    });
}
function openEditOrder(id, ref, customer_id, date_){
    document.getElementById('edit_order_id').value     = id;
    document.getElementById('edit_reference_no').value = ref;
    document.getElementById('edit_date_').value        = date_;
    $('#edit_customer_id').val(customer_id).trigger('change');
    fetchOrderItems(id);
    var modal = new bootstrap.Modal(document.getElementById('editOrderModal'));
    modal.show();
}
function addEditRow(){
    var minSubsOptions = '';
    var unitsOptions   = '';
    <?php if($minSubs && is_array($minSubs)) foreach($minSubs as $ms): ?>
    minSubsOptions += '<option value="<?= $ms['id'] ?>"><?= addslashes($ms['name']) ?></option>';
    <?php endforeach; ?>
    <?php if($units && is_array($units)) foreach($units as $u): ?>
    unitsOptions += '<option value="<?= $u['id'] ?>"><?= addslashes($u['name']) ?></option>';
    <?php endforeach; ?>
    var row = '<tr class="data-row">'
        + '<input type="hidden" name="item_row_id[]" value="0">'
        + '<td><select name="item[]" class="form-control" required><option value="">Select below</option>'+minSubsOptions+'</select></td>'
        + '<td><select name="unit[]" class="form-control" required><option value="">Select below</option>'+unitsOptions+'</select></td>'
        + '<td><input type="number" name="quantity[]" min="0.01" step="any" class="form-control" required></td>'
        + '</tr>';
    $('#edit_items_tbody').append(row);
}
function fetchOrderPreview(orderId){
    $('#preview_order_body').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $.get('./requests/stock_requests.php', {
        get_manufacturing_order_preview: 1,
        order_id: orderId
    }, function(response){
        $('#preview_order_body').html(response);
    }).fail(function(){
        $('#preview_order_body').html("<div class='alert alert-danger'>Failed to load preview.</div>");
    });
}
function openPreviewOrder(id){
    fetchOrderPreview(id);
    var modal = new bootstrap.Modal(document.getElementById('previewOrderModal'));
    modal.show();
}
</script>