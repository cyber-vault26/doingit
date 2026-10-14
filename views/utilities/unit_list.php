<?php
$units = selectAllUnit($conn);
?>
<div class="text-end mb-3">
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addUnitModal">
        <i class="fas fa-plus"></i> Add Unit
    </button>
</div>
<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">List of Units</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-search table-striped">
            <thead class="bg-primary">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Short</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $num = 0;
                if($units && is_array($units)){
                    foreach($units as $unit){
                        $num++;
                ?>
                <tr>
                    <td><?php echo $num; ?></td>
                    <td><?php echo htmlspecialchars($unit['name']); ?></td>
                    <td><?php echo htmlspecialchars($unit['short']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUnitModal" onclick="populateEditUnit(<?php echo $unit['id']; ?>, '<?php echo addslashes($unit['name']); ?>', '<?php echo addslashes($unit['short']); ?>')">Edit</button>
                        <form action="./controllers/utilities_controller.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this unit?');">
                            <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
                            <button type="submit" name="delete_unit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center">No units found</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Unit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/utilities_controller.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <input type="hidden" name="unit_id" id="edit_unit_id">
                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input type="text" class="form-control" name="name" id="edit_unit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short / Initial</label>
                        <input type="text" class="form-control" name="short" id="edit_unit_short" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="editunit" class="btn btn-warning">Update Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add New Unit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/utilities_controller.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short / Initial</label>
                        <input type="text" class="form-control" name="short" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="addunit" class="btn btn-primary">Add Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function populateEditUnit(id, name, short) {
    document.getElementById('edit_unit_id').value = id;
    document.getElementById('edit_unit_name').value = name;
    document.getElementById('edit_unit_short').value = short;
}
</script>