<div class="text-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSalaryModal">
        <i class="fas fa-plus"></i> Add Salary
    </button>
</div>

<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="salary-tabs" role="tablist">
            <li class="pt-2 px-3"><h3 class="card-title">Salary Status:</h3></li>
            <li class="nav-item">
                <a class="nav-link active" id="active-tab" data-toggle="pill" href="#active-salaries" role="tab" aria-selected="true">Active Salaries</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="inactive-tab" data-toggle="pill" href="#inactive-salaries" role="tab" aria-selected="false">Inactive Salaries</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <div class="tab-pane fade show active" id="active-salaries" role="tabpanel">
                <?php
                $active_salaries = selectSalaryByStatus($conn, 'active');
                if (!empty($active_salaries) && is_array($active_salaries)):
                    $counter = 1;
                ?>
                <table class="table table-sm table-search table-striped">
                    <thead class="bg-primary">
                        <tr><th>#</th><th class="text-left">Name</th><th class="text-center">Amount</th><th class="text-right">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_salaries as $rows): ?>
                        <tr>
                            <td><?= $counter ?></td>
                            <td class="text-left"><?= htmlspecialchars($rows['employee_name']) ?></td>
                            <td class="text-center"><?= number_format($rows['amount'], 2) ?></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#editSalaryModal"
                                    onclick="populateEditSalary('<?= $rows['id'] ?>', '<?= htmlspecialchars($rows['employee_name'], ENT_QUOTES) ?>', '<?= $rows['amount'] ?>')">
                                    Edit
                                </button>
                                <a href="./?page=preview_salary_receipt&id=<?= $rows['id'] ?>&staff_id=<?= $rows['subsidiary_id'] ?>&bs=<?= $rows['amount'] ?>" class="btn btn-sm btn-info">Preview</a>
                                <form action="./controllers/payroll_controller.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                    <input type="hidden" name="salary_id" value="<?= $rows['id'] ?>">
                                    <input type="hidden" name="status" value="inactive">
                                    <input type="hidden" name="updated_by" value="<?= $_SESSION['userid'] ?>">
                                    <button type="submit" name="deactivate_salary" class="btn btn-sm btn-warning">Deactivate</button>
                                </form>
                                <form action="./controllers/payroll_controller.php" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this record?');">
                                    <input type="hidden" name="delete_id" value="<?= $rows['id'] ?>">
                                    <input type="hidden" name="deleted_by" value="<?= $_SESSION['userid'] ?>">
                                    <button type="submit" name="delete_salary" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php $counter++; endforeach; ?>
                    </tbody>
                    <tfoot class="bg-primary">
                        <tr><th>#</th><th class="text-left">Name</th><th class="text-center">Amount</th><th class="text-right">Action</th></tr>
                    </tfoot>
                </table>
                <?php else: ?>
                    <div class="alert alert-primary">No active salary records found.</div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="inactive-salaries" role="tabpanel">
                <?php
                $inactive_salaries = selectSalaryByStatus($conn, 'inactive');
                if (!empty($inactive_salaries) && is_array($inactive_salaries)):
                    $counter = 1;
                ?>
                <table class="table table-sm table-search table-striped">
                    <thead class="bg-secondary">
                        <tr><th>#</th><th class="text-left">Name</th><th class="text-center">Amount</th><th class="text-right">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inactive_salaries as $rows): ?>
                        <tr>
                            <td><?= $counter ?></td>
                            <td class="text-left"><?= htmlspecialchars($rows['employee_name']) ?></td>
                            <td class="text-center"><?= number_format($rows['amount'], 2) ?></td>
                            <td class="text-right">
                                <form action="./controllers/payroll_controller.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="salary_id" value="<?= $rows['id'] ?>">
                                    <input type="hidden" name="status" value="active">
                                    <input type="hidden" name="updated_by" value="<?= $_SESSION['userid'] ?>">
                                    <button type="submit" name="activate_salary" class="btn btn-sm btn-success">Activate</button>
                                </form>
                                <form action="./controllers/payroll_controller.php" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this record?');">
                                    <input type="hidden" name="delete_id" value="<?= $rows['id'] ?>">
                                    <input type="hidden" name="deleted_by" value="<?= $_SESSION['userid'] ?>">
                                    <button type="submit" name="delete_salary" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php $counter++; endforeach; ?>
                    </tbody>
                    <tfoot class="bg-secondary">
                        <tr><th>#</th><th class="text-left">Name</th><th class="text-center">Amount</th><th class="text-right">Action</th></tr>
                    </tfoot>
                </table>
                <?php else: ?>
                    <div class="alert alert-primary">No inactive salary records found.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>


<!-- Single Edit Salary Modal -->
<div class="modal fade" id="editSalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="./controllers/payroll_controller.php" method="POST">
            <input type="hidden" name="edit_id" id="edit_salary_id">
            <input type="hidden" name="updated_by" value="<?= $_SESSION['userid'] ?>">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Salary</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Staff Name</label>
                        <input type="text" class="form-control" id="edit_salary_name" disabled>
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="text" class="form-control format-number" name="edit_amount" id="edit_salary_amount" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_salary" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Add Salary Modal -->
<div class="modal fade" id="addSalaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add Salary</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./controllers/payroll_controller.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="addTableRow('salaryAddTable')">Add Row</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeTableRow('salaryAddTable')">Remove Row</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="salaryAddTable">
                            <thead class="table-light">
                                <tr><th>Employee Name</th><th>Amount</th></tr>
                            </thead>
                            <tbody>
                                <tr class="data-row">
                                    <td>
                                        <select name="emp_id[]" class="form-control" required>
                                            <option value="">Select Employee</option>
                                            <?php
                                            $staff_without_salary = selectStaffWithoutSalary($conn);
                                            if (!empty($staff_without_salary) && is_array($staff_without_salary)):
                                                foreach ($staff_without_salary as $staff):
                                            ?>
                                            <option value="<?= $staff['id'] ?>"><?= $staff['name'] ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="amount[]" class="form-control format-number" placeholder="0.00" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="addsalary" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function populateEditSalary(id, name, amount) {
    document.getElementById('edit_salary_id').value = id;
    document.getElementById('edit_salary_name').value = name;
    document.getElementById('edit_salary_amount').value = amount;
}
</script>