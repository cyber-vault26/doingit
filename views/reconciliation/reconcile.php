<?php
// Get database connection
require_once __DIR__ . '/../../functions/reconciliation_functions.php';
$conn = openConn();

// Check if user has permission
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'system_admin' && $_SESSION['usertype'] !== 'Finance') {
    echo "<script>alert('Access denied. You do not have permission to access reconciliation.'); window.location.href='./';</script>";
    exit;
}

// Handle filters
$selectedBank = isset($_POST['bank_account_id']) ? (int) $_POST['bank_account_id'] : 0;
$selectedYear = isset($_POST['year']) ? $_POST['year'] : date("Y");

// Get bank accounts for dropdown
$bankAccounts = selectSubsidiariesBYledgerId($conn, 33);
$closingBalance = [];

$fetchedClosingBalance = listMonthlyBalanceByAccountId($conn, $selectedBank, $selectedYear);
if($fetchedClosingBalance && isset($fetchedClosingBalance['data'])) {
    $closingBalance = $fetchedClosingBalance['data'];
}
// print_r($closingBalance);

?>

<div class="card card-outline card-info">
    <div class="card-header">
        <h4 class="card-title"><i class="fas fa-balance-scale mr-2"></i>Bank Reconciliation</h4>
        <div class="card-tools">
            <a href="./?page=add_monthly_balance" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i>Add Balance
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form method="POST" class="mb-4">
            <input type="hidden" name="page" value="reconcile">
            <div class="row m-1">
                <div class="col-md-4 form-group">
                    <label for="bank_account_id">Bank Account <span class="text-danger">*</span></label>
                    <select class="form-control select2bs4-form" id="bank_account_id" name="bank_account_id" required>
                        <option value="">Select Bank Account</option>
                        <?php foreach ($bankAccounts as $account): ?>
                            <option value="<?= $account['id'] ?>" <?= $selectedBank == $account['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($account['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 form-group">
                    <label for="year">Year <span class="text-danger">*</span></label>
                    <input type="year" class="form-control" id="year" name="year" 
                           placeholder="YYYY" max="<?= date("Y") ?>" min="2020" value="<?= htmlspecialchars($selectedYear) ?>"
                           pattern="\d{4}" maxlength="4" required>
                    <small class="form-text text-muted">Format: YYYY</small>
                </div>
              
                
                <div class="col-md-2 form-group">
                    <label>&nbsp;</label><br>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search mr-1"></i>Reconcile
                    </button>
                </div>
                
                <div class="col-md-3 form-group">
                    <label>&nbsp;</label><br>
                    <a href="./?page=reconcile" class="btn btn-secondary">
                        <i class="fas fa-redo mr-1"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
        <?php
                if($selectedBank == 0 ){
                    echo '<div class="alert alert-warning">Please select a bank account to reconcile.</div>';
                    return;
                }
        ?>
    <div class=" card-body">
        <h4 class=" card-title">Fetch Balance</h4>
        <div class=" table-responible">
                <table class=" table table-bordered">
                        <thead>
                                <tr>
                                        <th>#</th>
                                        <th>Month</th>
                                        <th>Account Name</th>
                                        <th>System Balance</th>
                                        <th>Bank Balance</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php
                                // TODO: Fetch and display reconciliation data
                                $counter = 1;
                                foreach($closingBalance as $cb){
                                    $date  = date("Y-m-t", strtotime($cb['month']));
                                    $accountSystemBalance = calculateSubClosingBalanceByMaxDate($conn, $selectedBank, $date);
                                    $status = $accountSystemBalance == $cb['closing_balance'] ? 'Match' : 'Mismatch';
                                    $balanceType = $cb['balance_type'] == "credit" ? 'Cr' : 'Dr';
                                    echo "<tr>";
                                            echo "<td>$counter</td>";
                                            echo "<td>$cb[month]</td>";
                                            echo "<td>$cb[account_name]</td>";
                                            echo "<td>". number_format($accountSystemBalance, 2) ."</td>";
                                            echo "<td>". ($balanceType == 'Cr' ? '' : '-') . number_format($cb['closing_balance'], 2) ."</td>";

                                            echo "<td>". ($status == 'Match' ? '<span class="badge badge-success">Match</span>' : '<span class="badge badge-danger">Mismatch</span>') ."</td>";
                                            echo "<td>
                                                        ". ($status == 'Mismatch' ? '<a href="./?page=preview_transactions&sub_id=' . $selectedBank . '&balance_id=' . $cb["id"] . '" class="btn btn-success">Reconcile</a>' : '') ."
                                                        <a href='./?page=edit_monthly_closing_balance&balance_id={$cb["id"]}' class='btn btn-info'>Edit Statement Balance</a>
                                                </td>";
                                    echo "</tr>";
                                    $counter++;
                                }
                            ?>
                        </tbody>
                </table>
        </div>
    </div>
</div>

        
</script>


