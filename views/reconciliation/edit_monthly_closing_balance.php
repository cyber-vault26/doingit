<?php
  require_once __DIR__ . "/../../functions/reconciliation_functions.php";
// Get database connection
$conn = openConn();
$bankAccounts = selectSubsidiariesBYledgerId($conn, 33);
$balanceId = $_GET['balance_id'];
$monthlyBalance = listMonthlyBalanceByBalanceId($conn,$balanceId);
$data = [];
if($monthlyBalance && isset($monthlyBalance['data'])){
    $data = $monthlyBalance['data'];
}
//print_r($data);

// Check if user has permission (only Admin/Finance)
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'system_admin' && $_SESSION['usertype'] !== 'Finance') {
    echo "<script>alert('Access denied. You do not have permission to add monthly balances.'); window.location.href='./';</script>";
    exit;
}

?>

<div class="card card-outline card-info">
    <div class="card-header">
        <h4 class="card-title"><i class="fas fa-balance-scale mr-2"></i>Add Monthly Balance</h4>
    </div>
  
    
    <form action="./controllers/reconciliation_controller.php" method="post" class="was-validated">
        <input type="hidden" name="action" value="edit_monthly_closing_balance">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        <div class="card-body">
            <div class="row m-1">
                <div class="col-md-6 form-group">
                    <label for="bank_account_id">Bank Account <span class="text-danger">*</span></label>
                    <select class="form-control select2bs4-form" id="bank_account_id" name="bank_account_id" required>
                        <option value="">Select Bank Account</option>
                        <?php
                          foreach ($bankAccounts as $account) {
                                echo "<option " . ($account['id'] == $data['bank_account_id'] ? 'selected' : '') . " value='" . $account['id'] . "'>" . htmlspecialchars($account['name']) . "</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="month">Month <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="month" name="month" 
                           placeholder="e.g., 2025-01" pattern="\d{4}-\d{2}" required
                           maxlength="7" value="<?= $data['month'] ?>">
                    <small class="form-text text-muted">Enter month in YYYY-MM format</small>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-6 form-group">
                    <label for="closing_balance">Closing Balance <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="closing_balance" name="closing_balance" 
                           step="0.01" min="0" placeholder="0.00" required value="<?= $data['closing_balance'] ?>">
                </div>

                <div class="col-md-6 form-group">
                    <label for="balance_type">Balance Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="balance_type" name="balance_type" required>
                        <option value="">Select Balance Type</option>
                        <option value="credit" <?= $data['balance_type'] === 'credit' ? 'selected' : '' ?>>Credit Balance</option>
                        <option value="debit" <?= $data['balance_type'] === 'debit' ? 'selected' : '' ?>>Debit Balance</option>
                    </select>
                </div>
            </div>

            <div class="row m-1">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Important Notes:</h6>
                        <ul class="mb-0">
                            <li>Enter the closing balance as shown on your bank statement</li>
                            <li>Balance type indicates whether the account has a credit or debit balance</li>
                            <li>Most bank accounts have credit balances (positive cash position)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" name="edit_balance" class="btn btn-info">
                <i class="fas fa-save mr-2"></i>Edit Monthly Balance
            </button>
            <button type="reset" class="btn btn-secondary">
                <i class="fas fa-redo mr-2"></i>Reset Form
            </button>
            <a href="./?page=reconcile" class="btn btn-warning float-right">
                <i class="fas fa-arrow-left mr-2"></i>Back to Reconciliation
            </a>
        </div>
    </form>
</div>

