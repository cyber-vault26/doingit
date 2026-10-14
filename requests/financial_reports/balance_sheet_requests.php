<?php
include("../../configs.php");
require_once "../../functions/transaction_functions.php";
require_once "../../functions/subsidiary_functions.php";
require_once "../../functions/ledger_functions.php";
require_once "../../functions/utilities_functions.php";
require_once "../../functions/opening_balance_functions.php";
require_once "../../functions/sub_transaction_functions.php";
require_once "../../functions/master_functions.php";
require_once "../../functions/submain_functions.php";
require_once "../../functions/min_sub_functions.php";
$conn = openConn();
$level     = $_GET['level'];
$date1     = $_GET['date1'];
$date2     = $_GET['date2'];
$projectId = !empty($_GET['store_id']) ? (string)$_GET['store_id'] : null;
$levelText = [
    'subsidiary' => 'Subsidiary',
    'ledger'     => 'Ledger',
    'submaster'  => 'Sub Master',
    'master'     => 'Master'
];
function calcSubBalance(mysqli $conn, int $sub_id, string $date1, string $date2, ?string $projectId = null): float {
    $account_balance = 0;
    $openingBefore = selectOpeningBalanceBeforeDateRange($conn, $sub_id, $date1);
    $account_balance += $openingBefore['debit'] - $openingBefore['credit'];
    $openingInRange = selectOpeningBalanceByDateRange($conn, $sub_id, $date1, $date2);
    if ($openingInRange && is_array($openingInRange)) {
        if ($openingInRange['type'] === 'debit') {
            $account_balance += $openingInRange['ammount'];
        } elseif ($openingInRange['type'] === 'credit') {
            $account_balance -= $openingInRange['ammount'];
        }
    }
    $transactionsBefore = selectAllTransactionsBeforeDateBySubId($conn, $sub_id, $date1, $projectId);
    foreach ($transactionsBefore as $tx) {
        if ($tx['dr_account'] == $sub_id) $account_balance += $tx['amount'];
        if ($tx['cr_account'] == $sub_id) $account_balance -= $tx['amount'];
    }
    $transactions = selectAllTransactionsBySubIdAndProjectId($conn, $sub_id, $projectId, $date1, $date2);
    foreach ($transactions as $tx) {
        if ($tx['dr_account'] == $sub_id) $account_balance += $tx['amount'];
        if ($tx['cr_account'] == $sub_id) $account_balance -= $tx['amount'];
    }
    return $account_balance;
}
function calcNetProfit(mysqli $conn, string $date1, string $date2, ?string $projectId = null): float {
    $total_income   = 0;
    $total_expenses = 0;
    $incomeMaster = selectMasterByMasterName($conn, "Sales");
    if ($incomeMaster && is_array($incomeMaster)) {
        $submasters = selectAllSubmasterByMasterId($conn, $incomeMaster['id']);
        if ($submasters && is_array($submasters)) {
            foreach ($submasters as $submain) {
                $ledgers = selectLedgerBySubMainId($conn, $submain['id']);
                if ($ledgers && is_array($ledgers)) {
                    foreach ($ledgers as $ledger) {
                        $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                        if ($subsidiaries && is_array($subsidiaries)) {
                            foreach ($subsidiaries as $sub) {
                                $total_income += -calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);
                            }
                        }
                    }
                }
            }
        }
    }
    $expensesMaster = selectMasterByMasterName($conn, "Expenses");
    if ($expensesMaster && is_array($expensesMaster)) {
        $submasters = selectAllSubmasterByMasterId($conn, $expensesMaster['id']);
        if ($submasters && is_array($submasters)) {
            foreach ($submasters as $submain) {
                $ledgers = selectLedgerBySubMainId($conn, $submain['id']);
                if ($ledgers && is_array($ledgers)) {
                    foreach ($ledgers as $ledger) {
                        $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                        if ($subsidiaries && is_array($subsidiaries)) {
                            foreach ($subsidiaries as $sub) {
                                $total_expenses += calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);
                            }
                        }
                    }
                }
            }
        }
    }
    return $total_income - $total_expenses;
}
$net_profit = calcNetProfit($conn, $date1, $date2, $projectId);
?>
<style>
    .report-header { text-align: center; padding: 30px 0 20px 0; border-bottom: 3px double #333; margin-bottom: 30px; }
    .report-header h3 { font-size: 24px; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; }
    .report-header h4 { font-size: 18px; font-weight: 600; margin-bottom: 6px; text-decoration: underline; }
    .report-header h5 { font-size: 14px; font-weight: normal; color: #666; }
    @media print {
        @page { size: A4; margin: 15mm; }
        .no-print { display: none !important; }
        .report-header { border-bottom: 2px solid #000; }
    }
</style>
<div class="card-footer">
   <div class="report-header" style="border-bottom: 3px double #333; margin-bottom: 30px; padding-bottom: 15px;">
    <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <div style="flex: 0 0 auto; margin-right: 20px;">
            <img src="./dist/img/logo.png" alt="Logo" style="height: 90px; width: auto;">
        </div>
        <div style="flex: 1; text-align: left;">
            <h3 style="font-size: 22px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;">TYCOON TRADING COMPANY LIMITED</h3>
            <p style="margin: 0; font-size: 13px;">SIDO Arusha, Unga Ltd, Azimio Industrial Estate</p>
            <p style="margin: 0; font-size: 13px;">P.O. Box 6113, Arusha, Tanzania</p>
            <p style="margin: 0; font-size: 13px;">Tel: +255 654 577 267 / +255 687 878 700</p>
        </div>
    </div>
    <div style="text-align: center; border-top: 1px solid #ccc; padding-top: 12px;">
        <h4 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;">STATEMENT OF FINANCIAL POSITION BY <?php echo $levelText[$level]; ?></h4>
        <h5 style="font-size: 13px; font-weight: normal; margin: 0; color: #555;">FOR THE PERIOD BETWEEN <?php echo date("d-M-Y", strtotime($date1)); ?> AND <?php echo date("d-M-Y", strtotime($date2)); ?></h5>
    </div>
</div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm" id="BalanceSheetTable">
            <thead class="bg-info">
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 72%;">Particular</th>
                    <th style="width: 20%;" class="text-right">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter           = 1;
                $assets_total      = 0;
                $liabilities_total = 0;
                $capital_total     = 0;
                foreach (['Assets', 'Liabilities', 'Capital'] as $masterName) {
                    $master = selectMasterByMasterName($conn, $masterName);
                    if (!$master || !is_array($master)) continue;
                    $isAsset   = strtolower($masterName) === 'assets';
                    $isCapital = strtolower($masterName) === 'capital';
                    $master_color = match(strtolower($masterName)) {
                        'assets'      => 'table-success',
                        'liabilities' => 'table-warning',
                        'capital'     => 'table-info',
                        default       => 'table-primary'
                    };
                    $master_balance = 0;
                    $rendered_rows  = '';
                    $submasters = selectAllSubmasterByMasterId($conn, $master['id']);
                    if ($submasters && is_array($submasters)) {
                        foreach ($submasters as $submain) {
                            $submain_balance = 0;
                            $submain_rows    = '';
                            $ledgers = selectLedgerBySubMainId($conn, $submain['id']);
                            if ($ledgers && is_array($ledgers)) {
                                foreach ($ledgers as $ledger) {
                                    $ledger_balance = 0;
                                    $ledger_rows    = '';
                                    $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                                    if ($subsidiaries && is_array($subsidiaries)) {
                                        foreach ($subsidiaries as $sub) {
                                            $subBalance = calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);
                                            $subBalance = $isAsset ? $subBalance : -$subBalance;
                                            if ($sub['id'] == 1441 && $isCapital) {
                                                $subBalance += $net_profit;
                                            }
                                            if ($level == 'subsidiary' && abs($subBalance) > 0.001) {
                                                $ledger_rows .= "<tr><td>{$counter}</td><td>" . htmlspecialchars($sub['name']) . "</td><td class='text-right'>" . number_format($subBalance, 2) . "</td></tr>";
                                                $counter++;
                                            }
                                            $ledger_balance += $subBalance;
                                        }
                                    }
                                    if ($level == 'ledger' && abs($ledger_balance) > 0.001) {
                                        $submain_rows .= "<tr><td>{$counter}</td><td>" . htmlspecialchars($ledger['name']) . "</td><td class='text-right'>" . number_format($ledger_balance, 2) . "</td></tr>";
                                        $counter++;
                                    } elseif ($level == 'subsidiary') {
                                        $submain_rows .= $ledger_rows;
                                    }
                                    $submain_balance += $ledger_balance;
                                }
                            }
                            if ($level == 'submaster' && abs($submain_balance) > 0.001) {
                                $rendered_rows .= "<tr><td>{$counter}</td><td>" . htmlspecialchars($submain['name']) . "</td><td class='text-right'>" . number_format($submain_balance, 2) . "</td></tr>";
                                $counter++;
                            } elseif ($level == 'ledger' || $level == 'subsidiary') {
                                $rendered_rows .= $submain_rows;
                            }
                            $master_balance += $submain_balance;
                        }
                    }
                    if ($isCapital && $level == 'master') {
                        $master_balance += $net_profit;
                    }
                    if (abs($master_balance) <= 0.001) continue;
                    if ($level == 'master') {
                        echo "<tr class='{$master_color} font-weight-bold'>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td><strong>" . htmlspecialchars($masterName) . "</strong></td>";
                        echo "<td class='text-right'><strong>" . number_format($master_balance, 2) . "</strong></td>";
                        echo "</tr>";
                    } else {
                        echo "<tr class='{$master_color} font-weight-bold'>";
                        echo "<td>-</td>";
                        echo "<td><strong>" . htmlspecialchars($masterName) . "</strong></td>";
                        echo "<td>-</td>";
                        echo "</tr>";
                        echo $rendered_rows;
                        echo "<tr class='{$master_color} font-weight-bold'>";
                        echo "<td>-</td>";
                        echo "<td><strong>Total " . htmlspecialchars($masterName) . "</strong></td>";
                        echo "<td class='text-right'><strong>" . number_format($master_balance, 2) . "</strong></td>";
                        echo "</tr>";
                    }
                    if ($isAsset)        $assets_total      = $master_balance;
                    elseif ($isCapital)  $capital_total     = $master_balance;
                    else                 $liabilities_total = $master_balance;
                }
                $liabilities_plus_equity = $liabilities_total + $capital_total;
                $difference  = $assets_total - $liabilities_plus_equity;
                $is_balanced = abs($difference) < 0.01;
                ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <h4 class="text-info">Balance Sheet Verification</h4>
        <h5>Total Assets: <?php echo number_format($assets_total, 2); ?></h5>
        <h5>Total Liabilities: <?php echo number_format($liabilities_total, 2); ?></h5>
        <h5>Total Equity: <?php echo number_format($capital_total, 2); ?></h5>
        <?php $liabilities_plus_equity = $liabilities_total + $capital_total; ?>
        <h5>Total Liabilities + Equity: <?php echo number_format($liabilities_plus_equity, 2); ?></h5>
        <?php $difference = $assets_total - $liabilities_plus_equity; ?>
        <h5>Difference: <?php echo number_format($difference, 2); ?></h5>
        <?php if (abs($difference) < 0.01): ?>
            <h5 class="text-success">BALANCE SHEET IS BALANCED</h5>
        <?php else: ?>
            <h5 class="text-danger">BALANCE SHEET IS NOT BALANCED</h5>
        <?php endif; ?>
    </div>
    <div class="text-right mt-4 pt-3 border-top no-print">
        <button class="btn btn-success mr-2" id="exportExcelBtn" onclick="exportTableToExcel('table', 'balance_sheet', 'balance_sheet')">Excel</button>
        <button class="btn btn-danger" onclick="exportToPDF()">Print</button>
    </div>
</div>