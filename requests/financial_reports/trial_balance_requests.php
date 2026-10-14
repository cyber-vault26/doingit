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
    $levelText = ['subsidiary' => 'Subsidiary', 'ledger' => 'Ledger', 'submaster' => 'Sub Master', 'master' => 'Master'];

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
            <h4 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;">STATEMENT OF TRIAL BALANCE BY <?php echo $levelText[$level]; ?></h4>
            <h5 style="font-size: 13px; font-weight: normal; margin: 0; color: #555;">FOR THE PERIOD BETWEEN <?php echo date("d-M-Y", strtotime($date1)); ?> AND <?php echo date("d-M-Y", strtotime($date2)); ?></h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm" id="trialBalanceTable">
            <thead class="bg-info">
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 52%;">Particular</th>
                    <th style="width: 20%;" class="text-right">Debit (TZS)</th>
                    <th style="width: 20%;" class="text-right">Credit (TZS)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_credit = 0;
                $total_debit  = 0;
                $counter      = 1;

                if ($level == 'subsidiary') {
                    $subsidiaries = selectAllSubsidiaries($conn);
                    if ($subsidiaries && count($subsidiaries) > 0) {
                        foreach ($subsidiaries as $sub) {
                            $account_balance = calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);
                            if ($account_balance == 0) continue;
                            if ($account_balance > 0) $total_debit  += $account_balance;
                            else                       $total_credit += abs($account_balance);
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($sub['name']); ?></td>
                                <td class="text-right"><?php echo ($account_balance > 0) ? number_format($account_balance, 2) : '0.00'; ?></td>
                                <td class="text-right"><?php echo ($account_balance < 0) ? number_format(abs($account_balance), 2) : '0.00'; ?></td>
                            </tr>
                            <?php
                        }
                    }

                } elseif ($level == 'ledger') {
                    $ledgers = selectAllActiveLedgers($conn);
                    if ($ledgers && count($ledgers) > 0) {
                        foreach ($ledgers as $ledger) {
                            $ledger_balance = 0;
                            $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                            if ($subsidiaries && count($subsidiaries) > 0) {
                                foreach ($subsidiaries as $sub) {
                                    $ledger_balance += calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);
                                }
                            }
                            if ($ledger_balance == 0) continue;
                            if ($ledger_balance > 0) $total_debit  += $ledger_balance;
                            else                      $total_credit += abs($ledger_balance);
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($ledger['name']); ?></td>
                                <td class="text-right"><?php echo ($ledger_balance > 0) ? number_format($ledger_balance, 2) : '0.00'; ?></td>
                                <td class="text-right"><?php echo ($ledger_balance < 0) ? number_format(abs($ledger_balance), 2) : '0.00'; ?></td>
                            </tr>
                            <?php
                        }
                    }

                } elseif ($level == 'submaster') {
                    $submasters = selectAllSubmains($conn);
                    if ($submasters && count($submasters) > 0) {
                        foreach ($submasters as $submaster) {
                            $submaster_balance = 0;
                            $ledgers = selectLedgerBySubMainId($conn, $submaster['id']);
                            if ($ledgers && count($ledgers) > 0) {
                                foreach ($ledgers as $ledger) {
                                    $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                                    if ($subsidiaries && count($subsidiaries) > 0) {
                                        foreach ($subsidiaries as $sub) {
                                            $submaster_balance += calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);
                                        }
                                    }
                                }
                            }
                            if ($submaster_balance == 0) continue;
                            if ($submaster_balance > 0) $total_debit  += $submaster_balance;
                            else                         $total_credit += abs($submaster_balance);
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($submaster['name']); ?></td>
                                <td class="text-right"><?php echo ($submaster_balance > 0) ? number_format($submaster_balance, 2) : '0.00'; ?></td>
                                <td class="text-right"><?php echo ($submaster_balance < 0) ? number_format(abs($submaster_balance), 2) : '0.00'; ?></td>
                            </tr>
                            <?php
                        }
                    }

                } elseif ($level == 'master') {
                    $masters = selectAllMasters($conn);
                    if ($masters && count($masters) > 0) {
                        foreach ($masters as $master) {
                            $master_balance = 0;
                            $submasters = selectSubmainByMasterId($conn, $master['id']);
                            if ($submasters && count($submasters) > 0) {
                                foreach ($submasters as $submaster) {
                                    $ledgers = selectLedgerBySubMainId($conn, $submaster['id']);
                                    if ($ledgers && count($ledgers) > 0) {
                                        foreach ($ledgers as $ledger) {
                                            $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                                            if ($subsidiaries && count($subsidiaries) > 0) {
                                                foreach ($subsidiaries as $sub) {
                                                    $master_balance += calcSubBalance($conn, $sub['id'], $date1, $date2, $projectId);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($master_balance == 0) continue;
                            if ($master_balance > 0) $total_debit  += $master_balance;
                            else                      $total_credit += abs($master_balance);
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($master['name']); ?></td>
                                <td class="text-right"><?php echo ($master_balance > 0) ? number_format($master_balance, 2) : '0.00'; ?></td>
                                <td class="text-right"><?php echo ($master_balance < 0) ? number_format(abs($master_balance), 2) : '0.00'; ?></td>
                            </tr>
                            <?php
                        }
                    }
                }

                $difference = $total_debit - $total_credit;
                ?>

                <tr class="table-primary font-weight-bold">
                    <td></td>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_debit, 2); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_credit, 2); ?></strong></td>
                </tr>
                <tr class="<?php echo (abs($difference) < 0.01) ? 'table-success' : 'table-danger'; ?> font-weight-bold">
                    <td></td>
                    <td><strong>DIFFERENCE</strong></td>
                    <td class="text-right" colspan="2">
                        <strong><?php echo number_format(abs($difference), 2); ?></strong>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
    <div class="text-right mt-4 pt-3 border-top no-print">
        <button class="btn btn-success mr-2" id="exportExcelBtn" onclick="exportTableToExcel('table', 'trial_balance', 'trial_balance')">Excel</button>
        <button class="btn btn-danger" onclick="exportToPDF()">Print</button>
    </div>
</div>