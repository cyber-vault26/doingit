<?php
$conversions = selectAllItemUnitConversions($conn);
$minSubs     = selectAllMinSub($conn);
$units       = selectAllUnit($conn);
?>

<div class="text-end mb-3">
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addConversionModal">
        <i class="fas fa-plus"></i> Add Unit Conversion
    </button>
</div>

<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">Item Unit Conversions</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-search table-striped">
            <thead class="bg-primary">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Base Unit</th>
                    <th>Alt Unit</th>
                    <th>1 Alt Unit =</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $num = 0;
                if ($conversions && is_array($conversions)) {
                    foreach ($conversions as $conv) {
                        $num++;
                ?>
                <tr>
                    <td><?= $num ?></td>
                    <td><?= htmlspecialchars($conv['item_name']) ?></td>
                    <td><?= htmlspecialchars($conv['base_unit_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($conv['unit_two_name']) ?></td>
                    <td><?= htmlspecialchars($conv['quantity']) ?> <?= htmlspecialchars($conv['base_unit_name'] ?? '') ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editConversionModal"
                            onclick="populateEditConversion(
                                <?= $conv['id'] ?>,
                                <?= $conv['item_id'] ?>,
                                <?= $conv['unit_two_id'] ?>,
                                '<?= $conv['quantity'] ?>'
                            )">Edit</button>
                        <form action="./controllers/stock_controllers.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this conversion?');">
                            <input type="hidden" name="conversion_id" value="<?= $conv['id'] ?>">
                            <button type="submit" name="delete_unit_conversion" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php
                    }
                } else { ?>
                <tr>
                    <td colspan="6" class="text-center">No unit conversions found</td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot class="bg-primary">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Base Unit</th>
                    <th>Alt Unit</th>
                    <th>1 Alt Unit =</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addConversionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add Unit Conversion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/stock_controllers.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select name="item_id" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select below</option>
                            <?php if($minSubs && is_array($minSubs)) foreach($minSubs as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?> <?= !empty($item['base_unit_name']) ? '(' . htmlspecialchars($item['base_unit_name']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alternative Unit</label>
                        <select name="unit_two_id" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select below</option>
                            <?php if($units && is_array($units)) foreach($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity (how many base units = 1 alt unit)</label>
                        <input type="number" name="quantity" class="form-control" min="0.0001" step="any" required placeholder="e.g. 25">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_unit_conversion" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editConversionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Unit Conversion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/stock_controllers.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <input type="hidden" name="conversion_id" id="edit_conversion_id">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select name="item_id" id="edit_item_id" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select below</option>
                            <?php if($minSubs && is_array($minSubs)) foreach($minSubs as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?> <?= !empty($item['base_unit_name']) ? '(' . htmlspecialchars($item['base_unit_name']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alternative Unit</label>
                        <select name="unit_two_id" id="edit_unit_two_id" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select below</option>
                            <?php if($units && is_array($units)) foreach($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity (how many base units = 1 alt unit)</label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-control" min="0.0001" step="any" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_unit_conversion" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function populateEditConversion(id, item_id, unit_two_id, quantity) {
    document.getElementById('edit_conversion_id').value = id;
    document.getElementById('edit_quantity').value      = quantity;
    $('#edit_item_id').val(item_id).trigger('change');
    $('#edit_unit_two_id').val(unit_two_id).trigger('change');
}
</script>