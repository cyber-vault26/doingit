<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['usertype'])) {
    echo "<script>window.location.href='./login.php';</script>";
    exit();
}
require_once "../../functions/subsidiary_functions.php";
require_once "../../functions/loan_functions.php";
require_once "../../functions/user_functions.php";
require_once "../../functions/min_sub_functions.php";
require_once "../../functions/voucher_functions.php";
require_once "../../functions/transaction_functions.php";
require_once "../../functions/opening_balance_functions.php";
require_once "../../configs.php";
$conn = openConn();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

if (isset($_GET['get_subsidiary_report'])) {
    $sub_id    = $_GET['sub_id']    ?? null;
    $loan_type = $_GET['loan_type'] ?? null;

    $subsidiary = selectSubsidiaryById($conn, $sub_id);
    $name = $subsidiary['name'] ?? 'Unknown';

    $balance = 0;
    $counter = 1;
    $total_debit = 0;
    $total_credit = 0;

    $openingBalance = selectOpeningBalance($conn, $sub_id);
    $opening_debit = 0;
    $opening_credit = 0;
    $opening_date = date("d-M-Y");

    if ($openingBalance && is_array($openingBalance)) {
        $opening_date = date("d-M-Y", strtotime($openingBalance['date_']));
        if ($openingBalance['type'] === 'credit') {
            $balance -= $openingBalance['ammount'];
            $opening_credit = $openingBalance['ammount'];
            $total_credit += $openingBalance['ammount'];
        } elseif ($openingBalance['type'] === 'debit') {
            $balance += $openingBalance['ammount'];
            $opening_debit = $openingBalance['ammount'];
            $total_debit += $openingBalance['ammount'];
        }
    }
    $opening_balance = $balance;

    $transactions = selectAllTransactionsBySubIdAndLoanId($conn, $sub_id, $loan_type, null, null, null);
    ?>
    <div class="card m-2 card-outline card-info">
        <div class="card-header">
            <h4 class="card-title">Subsidiary Report - <?php echo htmlspecialchars($name); ?></h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered data-table-basic data-table">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Narration</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-success">
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $opening_date; ?></td>
                            <td></td>
                            <td>Opening Balance</td>
                            <td><?php echo number_format($opening_debit, 2); ?></td>
                            <td><?php echo number_format($opening_credit, 2); ?></td>
                            <td><?php echo number_format($opening_balance, 2); ?></td>
                        </tr>
                        <?php
                        if ($transactions && count($transactions) > 0) {
                            foreach ($transactions as $transaction) {
                                $trans_debit = 0;
                                $trans_credit = 0;
                                $details = '';
                                if ($transaction['dr_account'] == $sub_id) {
                                    $corresponding = selectSubsidiaryById($conn, (int) $transaction['cr_account']);
                                    $details = "From - " . htmlspecialchars($corresponding['name'] ?? 'Unknown');
                                    $trans_debit = $transaction['amount'];
                                    $balance += $trans_debit;
                                    $total_debit += $trans_debit;
                                } elseif ($transaction['cr_account'] == $sub_id) {
                                    $corresponding = selectSubsidiaryById($conn, (int) $transaction['dr_account']);
                                    $details = "To - " . htmlspecialchars($corresponding['name'] ?? 'Unknown');
                                    $trans_credit = $transaction['amount'];
                                    $balance -= $trans_credit;
                                    $total_credit += $trans_credit;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($transaction['date_'])); ?></td>
                                    <td><?php echo $details; ?></td>
                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                    <td><?php echo number_format($trans_debit, 2); ?></td>
                                    <td><?php echo number_format($trans_credit, 2); ?></td>
                                    <td><?php echo number_format($balance, 2); ?></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <tr class="table-danger font-weight-bold">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><strong>Closing Balance</strong></td>
                            <td><strong><?php echo number_format($total_debit, 2); ?></strong></td>
                            <td><strong><?php echo number_format($total_credit, 2); ?></strong></td>
                            <td><strong><?php echo number_format($balance, 2); ?></strong></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Narration</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php
    exit;
}

if (isset($_GET['get_subsidiary_report_by_date'])) {
    $sub_id    = $_GET['sub_id']    ?? null;
    $loan_type = $_GET['loan_type'] ?? null;
    $date1     = $_GET['date1']     ?? null;
    $date2     = $_GET['date2']     ?? null;

    $subsidiary = selectSubsidiaryById($conn, $sub_id);
    $name = $subsidiary['name'] ?? 'Unknown';

    $balance = 0;
    $counter = 1;
    $total_debit = 0;
    $total_credit = 0;

    // step 1: opening balance records before date1
    $openingBeforeDate = selectOpeningBalanceBeforeDateRange($conn, $sub_id, $date1);
    $opening_debit  = $openingBeforeDate['debit']  ?? 0;
    $opening_credit = $openingBeforeDate['credit'] ?? 0;

    // step 2: opening balance records between date1 and date2
    $openingInRange = selectOpeningBalanceByDateRange($conn, $sub_id, $date1, $date2);
    if ($openingInRange && is_array($openingInRange)) {
        if ($openingInRange['type'] === 'debit') {
            $opening_debit  += $openingInRange['ammount'];
        } elseif ($openingInRange['type'] === 'credit') {
            $opening_credit += $openingInRange['ammount'];
        }
    }

    // step 3: transactions before date1 — calculate their net balance
    $transactionsBefore = selectAllTransactionsBeforeDateBySubId($conn, $sub_id, $date1, $loan_type);
    foreach ($transactionsBefore as $t) {
        if ($t['dr_account'] == $sub_id) {
            $opening_debit += $t['amount'];
        } elseif ($t['cr_account'] == $sub_id) {
            $opening_credit += $t['amount'];
        }
    }

    // step 4: overall opening balance
    $balance         = $opening_debit - $opening_credit;
    $opening_balance = $balance;
    $total_debit    += $opening_debit;
    $total_credit   += $opening_credit;

    // step 5: transactions within date range
    $transactions = selectAllTransactionsBySubIdAndLoanId($conn, $sub_id, $loan_type, null, $date1, $date2);
    ?>
    <div class="card m-2 card-outline card-info">
        <div class="card-header">
            <h4 class="card-title">Subsidiary Report - <?php echo htmlspecialchars($name); ?> (<?php echo $date1; ?> to <?php echo $date2; ?>)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered data-table-basic data-table">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Narration</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-success">
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $date1; ?></td>
                            <td></td>
                            <td>Opening Balance</td>
                            <td><?php echo number_format($opening_debit, 2); ?></td>
                            <td><?php echo number_format($opening_credit, 2); ?></td>
                            <td><?php echo number_format($opening_balance, 2); ?></td>
                        </tr>
                        <?php
                        if ($transactions && count($transactions) > 0) {
                            foreach ($transactions as $transaction) {
                                $trans_debit  = 0;
                                $trans_credit = 0;
                                $details      = '';
                                if ($transaction['dr_account'] == $sub_id) {
                                    $corresponding = selectSubsidiaryById($conn, (int) $transaction['cr_account']);
                                    $details       = "From - " . htmlspecialchars($corresponding['name'] ?? 'Unknown');
                                    $trans_debit   = $transaction['amount'];
                                    $balance      += $trans_debit;
                                    $total_debit  += $trans_debit;
                                } elseif ($transaction['cr_account'] == $sub_id) {
                                    $corresponding = selectSubsidiaryById($conn, (int) $transaction['dr_account']);
                                    $details       = "To - " . htmlspecialchars($corresponding['name'] ?? 'Unknown');
                                    $trans_credit  = $transaction['amount'];
                                    $balance      -= $trans_credit;
                                    $total_credit += $trans_credit;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($transaction['date_'])); ?></td>
                                    <td><?php echo $details; ?></td>
                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                    <td><?php echo number_format($trans_debit, 2); ?></td>
                                    <td><?php echo number_format($trans_credit, 2); ?></td>
                                    <td><?php echo number_format($balance, 2); ?></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <tr class="table-danger font-weight-bold">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><strong>Closing Balance</strong></td>
                            <td><strong><?php echo number_format($total_debit, 2); ?></strong></td>
                            <td><strong><?php echo number_format($total_credit, 2); ?></strong></td>
                            <td><strong><?php echo number_format($balance, 2); ?></strong></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Narration</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php
    exit;
}
}