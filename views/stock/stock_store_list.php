<?php
$stockStores = selectAllStockStores($conn);
?>
<div class="text-end mb-3">
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addStockStoreModal">
        <i class="fas fa-plus"></i> Add Stock Store
    </button>
</div>
<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">List of Stock Stores</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-search table-striped">
            <thead class="bg-primary">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $num = 0;
                if ($stockStores && is_array($stockStores)) {
                    foreach ($stockStores as $store) {
                        $num++;
                ?>
                <tr>
                    <td><?php echo $num; ?></td>
                    <td><?php echo htmlspecialchars($store['name']); ?></td>
                    <td><?php echo htmlspecialchars($store['address']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editStockStoreModal" onclick="populateEditStockStore(<?php echo $store['id']; ?>, '<?php echo addslashes($store['name']); ?>', '<?php echo addslashes($store['address']); ?>')">Edit</button>
                        <form action="./controllers/stock_controllers.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this stock store?');">
                            <input type="hidden" name="store_id" value="<?php echo $store['id']; ?>">
                            <button type="submit" name="delete_stock_store" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php
                    }
                } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center">No stock stores found</td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot class="bg-primary">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editStockStoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Stock Store</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/stock_controllers.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <input type="hidden" name="store_id" id="edit_store_id">
                    <div class="mb-3">
                        <label class="form-label">Store Name</label>
                        <input type="text" class="form-control" name="name" id="edit_store_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="edit_store_address" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_stock_store" class="btn btn-warning">Update Store</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addStockStoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add New Stock Store</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/stock_controllers.php" method="POST" class="was-validated">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Store Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="3" placeholder="e.g. Tetra Tea, Dar es Salaam" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_stock_store" class="btn btn-primary">Add Store</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function populateEditStockStore(id, name, address) {
    document.getElementById('edit_store_id').value = id;
    document.getElementById('edit_store_name').value = name;
    document.getElementById('edit_store_address').value = address;
}
</script>