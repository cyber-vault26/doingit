<?php
$minSubs      = selectAllMinSub($conn);
$subsidiaries = selectAllSubsidiaries($conn);
$units        = selectAllUnit($conn);
?>
<div class="text-end mb-3">
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addMinSubModal">
        <i class="fas fa-plus"></i> Add Min Sub
    </button>
</div>
<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">List of Min Subs</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-search table-striped" id="minsubsTable">
            <thead class="bg-primary text-white">
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Subsidiary</th>
                    <th>Subcategory</th>
                    <th>Type</th>
                    <th>Base Unit</th>
                    <th>Packaging Size</th>
                    <th>Reorder Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($minSubs && is_array($minSubs)) {
                    $num = 0;
                    foreach ($minSubs as $row) {
                        $num++;
                        $base_unit = $row['base_unit'] ? selectUnitById($conn, $row['base_unit']) : null;
                        $imagePath = !empty($row['image_url']) ? '../uploads/items/' . htmlspecialchars($row['image_url']) : null;
                ?>
                <tr>
                    <td><?= $num ?></td>
                    <td>
                        <?php if ($imagePath): ?>
                            <a href="<?= $imagePath ?>" target="_blank" title="View Image">
                                <img src="<?= $imagePath ?>" alt="Product Image" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                            </a>
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td><?= !empty($row['code']) ? htmlspecialchars($row['code']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= isset($row['price']) && $row['price'] !== '' ? number_format($row['price'], 2) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= htmlspecialchars($row['subsidiary_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['subcategory']) ?></td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                    <td><?= $base_unit ? htmlspecialchars($base_unit['name']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= isset($row['packaging_size']) && $row['packaging_size'] !== '' ? htmlspecialchars($row['packaging_size']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= isset($row['reorder_level']) && $row['reorder_level'] !== '' ? htmlspecialchars($row['reorder_level']) : '<span class="text-muted">—</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning btn-edit-minsub"
                            data-id="<?= $row['id'] ?>"
                            data-name="<?= htmlspecialchars($row['name']) ?>"
                            data-code="<?= htmlspecialchars($row['code'] ?? '') ?>"
                            data-price="<?= htmlspecialchars($row['price'] ?? '') ?>"
                            data-image_url="<?= htmlspecialchars($row['image_url'] ?? '') ?>"
                            data-sub_id="<?= $row['sub_id'] ?>"
                            data-subcategory="<?= htmlspecialchars($row['subcategory']) ?>"
                            data-type="<?= htmlspecialchars($row['type']) ?>"
                            data-base_unit="<?= $row['base_unit'] ?? '' ?>"
                            data-packaging_size="<?= htmlspecialchars($row['packaging_size'] ?? '') ?>"
                            data-reorder_level="<?= htmlspecialchars($row['reorder_level'] ?? '') ?>"
                            data-bs-toggle="modal" data-bs-target="#editMinSubModal">
                            Edit
                        </button>
                        <form action="controllers/min_sub_controller.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this min sub?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete_minsub" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php
                    }
                } else {
                ?>
                <tr>
                    <td colspan="12" class="text-center">No min subs found</td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot class="bg-primary text-white">
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Subsidiary</th>
                    <th>Subcategory</th>
                    <th>Type</th>
                    <th>Base Unit</th>
                    <th>Packaging Size</th>
                    <th>Reorder Level</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
        <div class="d-flex justify-content-end mt-4">
    <button class="btn btn-primary btn-sm mr-1" onclick="window.open('./processes/printings/stocks/print_stock_items.php')">
        <i class="fas fa-print"></i> Print
    </button>
    <button class="btn btn-success btn-sm" onclick="window.open('./processes/printings/stocks/stock_items_excel.php')"> 
        <i class="fas fa-file-excel"></i> Excel
    </button>
</div>
    </div>
    
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editMinSubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Min Sub</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="controllers/min_sub_controller.php" method="POST" enctype="multipart/form-data" class="was-validated">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="existing_image_url" id="edit_existing_image_url">
                    <div class="mb-3">
                        <label class="form-label">Product Code <span class="text-muted">(optional)</span></label>
                        <input type="text" name="code" id="edit_code" class="form-control" placeholder="e.g. TYL-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image <span class="text-muted">(optional)</span></label>
                        <div id="edit_current_image_wrap" class="mb-2" style="display:none;">
                            <a id="edit_current_image_link" href="#" target="_blank">
                                <img id="edit_current_image_preview" src="" alt="Current Image" style="height:60px;border-radius:4px;object-fit:cover;">
                            </a>
                            <small class="text-muted d-block">Current image — pick a new one below to replace it</small>
                        </div>
                        <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sub Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price <span class="text-muted">(optional)</span></label>
                        <input type="number" name="price" id="edit_price" class="form-control" min="0" step="0.01" placeholder="e.g. 1500.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Subsidiary</label>
                        <select name="sub_id" id="edit_sub_id" class="form-control select2-form select2bs4-form" required>
                            <option value="">-- Select Subsidiary --</option>
                            <?php foreach ($subsidiaries as $sub) { ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory" id="edit_subcategory" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select Below</option>
                            <option value="others">Others</option>
                            <option value="raw-material">Raw Materials</option>
                            <option value="stock">Stock</option>
                            <option value="asset">Asset</option>
                            <option value="food">Food</option>
                            <option value="drink">Drink</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subtype</label>
                        <select name="type" id="edit_type" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select Below</option>
                            <option value="others">Others</option>
                            <option value="default">Default</option>
                            <option value="trailer">Trailer</option>
                            <option value="truck">Truck</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base Unit <span class="text-muted">(optional)</span></label>
                        <select name="base_unit" id="edit_base_unit" class="form-control select2-form select2bs4-form">
                            <option value="">— No Base Unit —</option>
                            <?php if ($units && is_array($units)) { foreach ($units as $u) { ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Packaging Size <span class="text-muted">(optional)</span></label>
                        <input type="text" name="packaging_size" id="edit_packaging_size" class="form-control" placeholder="e.g. 500ml, 1kg, 24pcs">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Level <span class="text-muted">(optional)</span></label>
                        <input type="number" name="reorder_level" id="edit_reorder_level" class="form-control" min="0" placeholder="e.g. 50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_minsub" class="btn btn-warning">Update Min Sub</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addMinSubModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add New Min Sub</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="controllers/min_sub_controller.php" method="POST" enctype="multipart/form-data" class="was-validated">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Code <span class="text-muted">(optional)</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. TYL-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image <span class="text-muted">(optional)</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sub Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price <span class="text-muted">(optional)</span></label>
                        <input type="number" name="price" class="form-control" min="0" step="0.01" placeholder="e.g. 1500.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Subsidiary</label>
                        <select name="sub_id" class="form-control select2-form select2bs4-form" required>
                            <option value="">-- Select Subsidiary --</option>
                            <?php foreach ($subsidiaries as $sub) { ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select Below</option>
                            <option value="others">Others</option>
                            <option value="raw-material">Raw Materials</option>
                            <option value="stock">Stock</option>
                            <option value="asset">Asset</option>
                            <option value="food">Food</option>
                            <option value="drink">Drink</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subtype</label>
                        <select name="type" class="form-control select2-form select2bs4-form" required>
                            <option value="">Select Below</option>
                            <option value="others">Others</option>
                            <option value="default">Default</option>
                            <option value="trailer">Trailer</option>
                            <option value="truck">Truck</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base Unit <span class="text-muted">(optional)</span></label>
                        <select name="base_unit" id="add_base_unit" class="form-control select2-form select2bs4-form">
                            <option value="">— No Base Unit —</option>
                            <?php if ($units && is_array($units)) { foreach ($units as $u) { ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Packaging Size <span class="text-muted">(optional)</span></label>
                        <input type="text" name="packaging_size" class="form-control" placeholder="e.g. 500ml, 1kg, 24pcs">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Level <span class="text-muted">(optional)</span></label>
                        <input type="number" name="reorder_level" class="form-control" min="0" placeholder="e.g. 50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_minsub" class="btn btn-primary">Add Min Sub</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-edit-minsub').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value                 = this.dataset.id;
        document.getElementById('edit_name').value               = this.dataset.name;
        document.getElementById('edit_code').value               = this.dataset.code;
        document.getElementById('edit_price').value              = this.dataset.price;
        document.getElementById('edit_sub_id').value             = this.dataset.sub_id;
        document.getElementById('edit_base_unit').value          = this.dataset.base_unit;
        document.getElementById('edit_packaging_size').value     = this.dataset.packaging_size;
        document.getElementById('edit_reorder_level').value      = this.dataset.reorder_level;
        document.getElementById('edit_existing_image_url').value = this.dataset.image_url;

        document.getElementById('edit_subcategory').value = this.dataset.subcategory;
        document.getElementById('edit_type').value        = this.dataset.type;

        // Show current image if exists
        var imageWrap    = document.getElementById('edit_current_image_wrap');
        var imagePreview = document.getElementById('edit_current_image_preview');
        var imageLink    = document.getElementById('edit_current_image_link');
        if (this.dataset.image_url && this.dataset.image_url !== '') {
            var imgSrc       = '../uploads/items/' + this.dataset.image_url;
            imagePreview.src = imgSrc;
            imageLink.href   = imgSrc;
            imageWrap.style.display = 'block';
        } else {
            imageWrap.style.display = 'none';
            imagePreview.src = '';
        }

        // Reset file input
        document.getElementById('edit_image').value = '';

        if (typeof $.fn.select2 !== 'undefined') {
            $('#edit_sub_id').trigger('change');
            $('#edit_subcategory').trigger('change');
            $('#edit_type').trigger('change');
            $('#edit_base_unit').trigger('change');
        }
    });
});
</script>