<?php
$batch     = (int) $_GET['batch'];
$type      = $conn->real_escape_string($_GET['type']);
$rows      = selectStockBatchesByType($conn, $type, $batch);
if(!$rows || !is_array($rows) || count($rows) === 0){
    echo "<div class='alert alert-danger'>Record not found.</div>";
    return;
}
$first             = $rows[0];
$ref_no            = $first['ref_no'];
$date              = $first['date_'];
$project_id        = $first['project_id'];
$supplier_id       = $first['supplier_id'];
$store_id          = $first['store_id'];
$purchase_order_id = $first['purchase_order_id'];
$isGRN             = $type === 'in';
$tableId           = $isGRN ? 'grn_edit_table' : 'gdn_edit_table';
$submitBtn         = $isGRN ? 'editgrn' : 'editgdn';
$title             = $isGRN ? 'Edit Received Stock' : 'Edit Issued Stock';
$supplierLabel     = $isGRN ? 'Supplier' : 'Issued To';
$storeLabel        = $isGRN ? 'Received To' : 'Issued From';
$minSubs           = selectAllMinSub($conn);
$allUnits          = selectAllUnit($conn);
?>
<div class="card card-info">
    <div class="card-header">
        <h4 class="card-text"><?= $title ?></h4>
    </div>
    <form action="./controllers/stock_controllers.php" method="post" class="was-validated">
        <input type="hidden" name="batch" value="<?= $batch ?>">
        <input type="hidden" name="type"  value="<?= $type ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 my-2">
                    <label class="form-label">Voucher Reference</label>
                    <input type="text" name="voucher_ref" readonly value="<?= htmlspecialchars($ref_no) ?>" class="form-control">
                </div>
                <?php if($isGRN): ?>
                <div class="col-md-3 my-2">
                    <label class="form-label">PO Number</label>
                    <input type="number" name="purchase_order_id" class="form-control" placeholder="Enter PO Number" required value="<?= htmlspecialchars($purchase_order_id) ?>">
                </div>
                <?php endif; ?>
                <div class="col-md-3 my-2">
                    <label class="form-label">Date</label>
                    <input type="date" required name="date" max='<?= date('Y-m-d') ?>' value="<?= htmlspecialchars($date) ?>" class="form-control">
                </div>
                <div class="col-md-3 my-2">
                    <label class="form-label"><?= $supplierLabel ?></label>
                    <select name="supplier" class="form-control select2-form select2bs4-form" required>
                        <option value="">Select below</option>
                        <?php
                        $suppliers = selectAllSubsidiaries($conn);
                        if($suppliers && is_array($suppliers)){
                            foreach($suppliers as $supplier){
                                $sel = ($supplier['id'] == $supplier_id) ? 'selected' : '';
                                echo "<option value='$supplier[id]' $sel>$supplier[name]</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 my-2">
                    <label class="form-label">Project Reference</label>
                    <select name="project_id" class="form-control select2-form select2bs4-form" required>
                        <option value="">Select below</option>
                        <option value="0" <?= $project_id == 0 ? 'selected' : '' ?>>Default Project</option>
                        <?php
                        $projects = listProjectsByStatus($conn, "active");
                        if($projects && is_array($projects)){
                            foreach($projects as $project){
                                $sel = ($project['id'] == $project_id) ? 'selected' : '';
                                echo "<option value='$project[id]' $sel>$project[name]</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 my-2">
                    <label class="form-label"><?= $storeLabel ?></label>
                    <select name="store_id" class="form-control select2-form select2bs4-form" required>
                        <option value="">Select below</option>
                        <?php
                        $stores = selectAllStockStores($conn);
                        if($stores && is_array($stores)){
                            foreach($stores as $store){
                                $sel = ($store['id'] == $store_id) ? 'selected' : '';
                                echo "<option value='$store[id]' $sel>$store[name]</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row m-1">
                <div class="col-md-12 my-2">
                    <button type="button" class="btn btn-info btn-sm" onclick="addTableRow('<?= $tableId ?>')">Add Row</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="editRemoveRow('<?= $tableId ?>')">Remove Row</button>
                </div>
            </div>
            <div id="delete_ids_container"></div>
            <style>
                #<?= $tableId ?> { table-layout: fixed; width: 100%; }
                #<?= $tableId ?> th:nth-child(1), #<?= $tableId ?> td:nth-child(1) { width: 60%; }
                #<?= $tableId ?> th:nth-child(2), #<?= $tableId ?> td:nth-child(2) { width: 20%; }
                #<?= $tableId ?> th:nth-child(3), #<?= $tableId ?> td:nth-child(3) { width: 20%; }
                #<?= $tableId ?> td select, #<?= $tableId ?> td input { width: 100%; }
            </style>
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="<?= $tableId ?>">
                    <thead class="bg-primary">
                        <tr>
                            <th>Item</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $row): ?>
                        <tr class="data-row" data-stock-id="<?= $row['id'] ?>">
                            <td>
                                <input type="hidden" name="stock_id[]" value="<?= $row['id'] ?>">
                                <select name="item[]" class="select2-form select2bs4-form form-control" required>
                                    <option value="">Select below</option>
                                    <?php foreach($minSubs as $minSub):
                                        $sel = ($minSub['id'] == $row['item_id']) ? 'selected' : '';
                                        echo "<option value='$minSub[id]' $sel>$minSub[name]</option>";
                                    endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="unit[]" class="unit select2 form-control select2-form select2bs4-form" required>
                                    <option value="">Select below</option>
                                    <?php foreach($allUnits as $unit):
                                        $sel = ($unit['id'] == $row['unit']) ? 'selected' : '';
                                        echo "<option value='$unit[id]' $sel>$unit[name]</option>";
                                    endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="quantity[]" min="0.01" step="any" class="qty form-control" value="<?= $row['quantity'] ?>" required>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="<?= $submitBtn ?>" class="btn btn-warning w-100">UPDATE</button>
        </div>
    </form>
</div>
<script>
function editRemoveRow(tableId) {
    let table    = $("#" + tableId);
    let rowCount = table.find("tbody tr").length;
    if(rowCount <= 1) return;
    let lastRow = table.find("tbody tr:last");
    let stockId = lastRow.data("stock-id");
    if(stockId && stockId != 0){
        $("#delete_ids_container").append('<input type="hidden" name="delete_ids[]" value="' + stockId + '">');
    }
    lastRow.remove();
}
</script>