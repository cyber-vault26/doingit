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
            <h4 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;">STATEMENT OF PROFIT OR LOSS BY <?php echo $levelText[$level]; ?></h4>
            <h5 style="font-size: 13px; font-weight: normal; margin: 0; color: #555;">FOR THE PERIOD BETWEEN <?php echo date("d-M-Y", strtotime($date1)); ?> AND <?php echo date("d-M-Y", strtotime($date2)); ?></h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm" id="incomeStatementTable">
            <thead class="bg-info">
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 72%;">Particular</th>
                    <th style="width: 20%;" class="text-right">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter        = 1;
                $total_income   = 0;
                $total_expenses = 0;

                // ── INCOME ──────────────────────────────────────────────────
                $incomeMaster = selectMasterByMasterName($conn, "Sales");
                if ($incomeMaster && is_array($incomeMaster)) {

                    $income_rendered = '';
                    $submasters = selectAllSubmasterByMasterId($conn, $incomeMaster['id']);
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
                                            $subBalance = -calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);

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
                                $income_rendered .= "<tr><td>{$counter}</td><td>" . htmlspecialchars($submain['name']) . "</td><td class='text-right'>" . number_format($submain_balance, 2) . "</td></tr>";
                                $counter++;
                            } elseif ($level == 'ledger' || $level == 'subsidiary') {
                                $income_rendered .= $submain_rows;
                            }

                            $total_income += $submain_balance;
                        }
                    }

                    if (abs($total_income) > 0.001) {
                        if ($level == 'master') {
                            echo "<tr class='table-success font-weight-bold'>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td><strong>Income</strong></td>";
                            echo "<td class='text-right'><strong>" . number_format($total_income, 2) . "</strong></td>";
                            echo "</tr>";
                        } else {
                            echo "<tr class='table-success font-weight-bold'><td>-</td><td><strong>Income</strong></td><td>-</td></tr>";
                            echo $income_rendered;
                            echo "<tr class='table-success font-weight-bold'><td>-</td><td><strong>Total Income</strong></td><td class='text-right'><strong>" . number_format($total_income, 2) . "</strong></td></tr>";
                        }
                    }
                }

                // ── EXPENSES ─────────────────────────────────────────────────
                $expensesMaster = selectMasterByMasterName($conn, "Expenses");
                if ($expensesMaster && is_array($expensesMaster)) {

                    $expense_rendered = '';
                    $submasters = selectAllSubmasterByMasterId($conn, $expensesMaster['id']);
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
                                $expense_rendered .= "<tr><td>{$counter}</td><td>" . htmlspecialchars($submain['name']) . "</td><td class='text-right'>" . number_format($submain_balance, 2) . "</td></tr>";
                                $counter++;
                            } elseif ($level == 'ledger' || $level == 'subsidiary') {
                                $expense_rendered .= $submain_rows;
                            }

                            $total_expenses += $submain_balance;
                        }
                    }

                    if (abs($total_expenses) > 0.001) {
                        if ($level == 'master') {
                            echo "<tr class='table-danger font-weight-bold'>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td><strong>Expenses</strong></td>";
                            echo "<td class='text-right'><strong>" . number_format($total_expenses, 2) . "</strong></td>";
                            echo "</tr>";
                        } else {
                            echo "<tr class='table-danger font-weight-bold'><td>-</td><td><strong>Expenses</strong></td><td>-</td></tr>";
                            echo $expense_rendered;
                            echo "<tr class='table-danger font-weight-bold'><td>-</td><td><strong>Total Expenses</strong></td><td class='text-right'><strong>" . number_format($total_expenses, 2) . "</strong></td></tr>";
                        }
                    }
                }

                // ── NET PROFIT / LOSS ────────────────────────────────────────
                $net_profit   = $total_income - $total_expenses;
                $profit_class = ($net_profit >= 0) ? 'table-primary' : 'table-warning';
                $profit_label = ($net_profit >= 0) ? 'Net Profit' : 'Net Loss';
                echo "<tr class='{$profit_class} font-weight-bold'>";
                echo "<td>-</td>";
                echo "<td><strong>{$profit_label}</strong></td>";
                echo "<td class='text-right'><strong>" . number_format(abs($net_profit), 2) . "</strong></td>";
                echo "</tr>";
                ?>
            </tbody>
        </table>
    </div>

    <div style="font-size: 13px; margin-top: 10px; line-height: 2;">
        <div>Total Income: <strong><?php echo number_format($total_income, 2); ?></strong></div>
        <div>Total Expenses: <strong><?php echo number_format($total_expenses, 2); ?></strong></div>
        <div><?php echo ($net_profit >= 0) ? 'Net Profit' : 'Net Loss'; ?>: <strong><?php echo number_format(abs($net_profit), 2); ?></strong></div>
    </div>

    <div class="text-right mt-4 pt-3 border-top no-print">
        <button class="btn btn-success mr-2" id="exportExcelBtn" onclick="exportTableToExcel('table', 'income_statement', 'income_statement')">Excel</button>
        <button class="btn btn-danger" onclick="exportToPDF()">Print</button>
    </div>
</div>