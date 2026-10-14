<?php
$pvId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $pvId > 0;
$editVoucher = null;
$editRate = 1.00;
$editEquiv = 0.00;
$editActual = 0.00;
if($isEdit){
    $editVoucher = selectTransactionById($conn, $pvId);
    $editRate   = floatval($editVoucher['currency_rate']) ?: 1.00;
    $editEquiv  = floatval($editVoucher['amount']);
    $existing_attachment= $editVoucher['attachment_url'];
    $editActual = $editEquiv / $editRate;
}
?>

<div class="card card-primary" id="modal2">
    <div class="card-header w3-text-white bg-primary">
        <h4><?= $isEdit ? 'Edit Payment Voucher' : 'Payment Voucher' ?></h4>
    </div>

    <div class="modal-body">
        <form id="voucherForm" enctype="multipart/form-data" class="was-validated"
              action="./controllers/voucher_controller.php" method="post">

            <?php if($isEdit): ?>
            <input type="hidden" name="voucher_id" value="<?= $pvId ?>">
            <input type="hidden" name="existing_attachment" value="<?= $existing_attachment ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-4 form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="voucherdate" class="form-control"
                           max="<?= date('Y-m-d') ?>"
                           value="<?= $isEdit ? date('Y-m-d', strtotime($editVoucher['date_'])) : '' ?>" required>
                </div>

                <div class="col-md-4 form-group">
                    <label class="form-label">Voucher Reference</label>
                    <?php if(!$isEdit):
                        $v = "PV/" . date("Y") . "/";
                        $initial = "PV/" . date("Y/") . "%";
                        $sql = "SELECT COUNT(transaction_voucher.id) id FROM transaction_voucher WHERE reference_no LIKE '$initial'";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        $ref = $row['id'] + 1;
                    endif; ?>
                    <input type="text" name="voucher_ref"
                           value="<?= $isEdit ? htmlspecialchars($editVoucher['reference_no']) : $v . $ref ?>"
                           class="form-control" readonly>
                </div>

                <div class="col-md-4 form-group">
                    <label class="form-label">Currency Used</label>
                    <select name="currency" required class="form-control" onchange="selectCurrency(this.value);">
                        <option value="">Select Currency First</option>
                        <?php
                        $sql = "SELECT * FROM currency";
                        $query = mysqli_query($conn, $sql);
                        while($rows = mysqli_fetch_assoc($query)){
                            $selected = ($isEdit && $editVoucher['currency'] == $rows['id']) ? 'selected' : '';
                            echo "<option value='{$rows['id']}' {$selected}>{$rows['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 form-group">
                    <label class="form-label">Project</label>
                    <select name="project_id" required class="select2-form select2bs4-form form-control">
                        <option value="">Select Project</option>
                        <option value="0" <?= ($isEdit && $editVoucher['project_id'] == '0') ? 'selected' : '' ?>>Default Project</option>
                        <?php
                        $projects = listProjectsByStatus($conn, 'active');
                        if($projects && is_array($projects)){
                            foreach($projects as $project){
                                $selected = ($isEdit && $editVoucher['project_id'] == $project['id']) ? 'selected' : '';
                                echo "<option value='{$project['id']}' {$selected}>{$project['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label class="form-label">Exchange Rate</label>
                    <div id="currencyp">
                        <input type="text" name="curr" id="curr_value" class="form-control" required
                               placeholder="Select currency first" value="">
                    </div>
                </div>
            </div>

            <?php if(!$isEdit): ?>
            <div class="my-3">
                <button type="button" class="btn btn-sm btn-info" onclick="addTableRow('receipt-voucher-table')">Add Row</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeTableRow('receipt-voucher-table')">Remove Row</button>
                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#registerDrModal">+ Register DR Account</button>
            </div>
            <?php endif; ?>

            <table class="table table-sm table-bordered" id="receipt-voucher-table">
                <thead class="bg-primary text-white">
                    <tr>
                        <th style="width:18%;">DR Account</th>
                        <th style="width:18%;">CR Account</th>
                        <th style="width:12%;">Amount</th>
                        <th style="width:12%;">Equiv Amount</th>
                        <th style="width:15%;">Description</th>
                        <th style="width:12%;">Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="data-row">
                        <td>
                            <select name="deb_account[]" required class="form-control select2bs4-form">
                                <option value="">Choose below</option>
                                <?php
                                $sql = "SELECT * FROM subsidiaries WHERE deleted_at IS NULL ORDER BY name ASC";
                                $query = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($query)){
                                    $selected = ($isEdit && $editVoucher['dr_account'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' {$selected}>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select name="cr_account[]" required class="form-control select2bs4-form">
                                <option value="">Choose below</option>
                                <?php
                                $sql = "SELECT * FROM subsidiaries WHERE ledger_id IN (32, 77, 33) AND deleted_at IS NULL ORDER BY name ASC";
                                $query = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($query)){
                                    $selected = ($isEdit && $editVoucher['cr_account'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' {$selected}>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="debt_amount[]" class="form-control format-number debt_amount"
                                   value="<?= $isEdit ? number_format($editActual, 2) : '' ?>"
                                   oninput="calculateEqv()" required>
                        </td>
                        <td>
                            <input type="text" name="equiv_dr[]" readonly class="form-control format-number equiv_dr"
                                   value="<?= $isEdit ? number_format($editEquiv, 2) : '' ?>">
                        </td>
                        <td>
                           <textarea name="desc[]" class="form-control" required
                               placeholder="Please enter description here"><?= $isEdit ? htmlspecialchars($editVoucher['description'] ?? '') : '' ?></textarea>
                        </td>
                        <td>
                            <input type="file" name="attachment[]" class="form-control form-control-sm"
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <?php if($isEdit && !empty($editVoucher['attachment_url'])): ?>
                            <small class="text-muted"><?= htmlspecialchars($editVoucher['attachment_url']) ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

        </form>
    </div>

    <div class="card-footer text-end">
        <button type="submit" form="voucherForm"
                name="<?= $isEdit ? 'editpaymentVoucher' : 'addpaymentVoucher' ?>"
                class="btn btn-primary w-100">
            <?= $isEdit ? 'UPDATE' : 'POST' ?>
        </button>
    </div>
</div>

<!-- Register DR Account Modal - outside voucherForm -->
<div class="modal fade" id="registerDrModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Register New DR Account</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form class="was-validated" action="./controllers/voucher_controller.php" method="post">
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="form-label">Ledger</label>
                        <select name="ledger" class="form-control select2bs4-form" required>
                            <option value="">Select Ledger</option>
                            <?php
                            $ledgers = selectAllActiveLedgers($conn);
                            if($ledgers && is_array($ledgers)){
                                foreach($ledgers as $ledger){
                                    echo "<option value='{$ledger['id']}'>{$ledger['name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100" name="addothers" type="submit">Add Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).on('input change', '#curr_value', function(){
    calculateEqv();
});

var currObserver = new MutationObserver(function(){
    var currField = document.getElementById('curr_value');
    if(currField && currField.value){ calculateEqv(); }
});
var currencyp = document.getElementById('currencyp');
if(currencyp){ currObserver.observe(currencyp, { childList: true, subtree: true }); }

<?php if($isEdit): ?>
document.addEventListener('DOMContentLoaded', function(){
    selectCurrency(<?= (int)$editVoucher['currency'] ?>);
    setTimeout(function(){
        var currField = document.getElementById('curr_value');
        if(currField){
            currField.value = '<?= $editRate ?>';
            formatNumber();
            calculateEqv();
        }
    }, 600);
});
<?php endif; ?>
</script>