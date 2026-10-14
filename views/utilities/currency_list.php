<?php
$currencies = selectAllCurrency($conn);
?>
<div class="text-end mb-3">
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
        <i class="fas fa-plus"></i> Add Currency
    </button>
</div>
<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">List of Currencies</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-search table-striped">
            <thead class="bg-primary">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $num = 0;
                if($currencies && is_array($currencies)){
                    foreach($currencies as $currency){
                        $num++;
                ?>
                <tr>
                    <td><?php echo $num; ?></td>
                    <td><?php echo htmlspecialchars($currency['name']); ?></td>
                    <td><?php echo number_format($currency['value'], 2); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCurrencyModal" onclick="populateEditCurrency(<?php echo $currency['id']; ?>, '<?php echo addslashes($currency['name']); ?>', <?php echo $currency['value']; ?>)">Edit</button>
                        <form action="./controllers/utilities_controller.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this currency?');">
                            <input type="hidden" name="currency_id" value="<?php echo $currency['id']; ?>">
                            <button type="submit" name="delete_currency" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center">No currencies found</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editCurrencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Currency</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/utilities_controller.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <input type="hidden" name="currency_id" id="edit_currency_id">
                    <div class="mb-3">
                        <label class="form-label">Currency Name</label>
                        <input type="text" class="form-control" name="name" id="edit_currency_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Currency Value (in TSH)</label>
                        <input type="number" step="any" class="form-control" name="value" id="edit_currency_value" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="editcurrency" class="btn btn-warning">Update Currency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addCurrencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add New Currency</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/utilities_controller.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Currency Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Currency Value (in TSH)</label>
                        <input type="number" step="any" class="form-control" name="value" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="addcurrency" class="btn btn-primary">Add Currency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function populateEditCurrency(id, name, value) {
    document.getElementById('edit_currency_id').value = id;
    document.getElementById('edit_currency_name').value = name;
    document.getElementById('edit_currency_value').value = value;
}
</script>