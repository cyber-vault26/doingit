<?php
    include("../../configs.php");
    require_once "../../functions/transaction_functions.php";
    require_once "../../functions/subsidiary_functions.php";
    require_once "../../functions/ledger_functions.php";
    require_once "../../functions/utility_functions.php";
    require_once "../../functions/opening_balance_functions.php";
    require_once "../../functions/sub_transaction_functions.php";
    require_once "../../functions/master_functions.php";
    require_once "../../functions/submain_functions.php";
    require_once "../../functions/min_sub_functions.php";
    $conn = openConn();
    $date1 = $_GET['date1'];
    $date2 = $_GET['date2'];

    // ── calcSubBalance: exact same as trial balance ─────────────────────────
    function calcSubBalance(mysqli $conn, int $sub_id, string $date1, string $date2): float {
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
        $transactionsBefore = selectAllTransactionsBeforeDateBySubId($conn, $sub_id, $date1);
        foreach ($transactionsBefore as $tx) {
            if ($tx['dr_account'] == $sub_id) $account_balance += $tx['amount'];
            if ($tx['cr_account'] == $sub_id) $account_balance -= $tx['amount'];
        }
        $transactions = selectAllTransactionsBySubIdAndDateRange($conn, $sub_id, $date1, $date2);
        foreach ($transactions as $tx) {
            if ($tx['dr_account'] == $sub_id) $account_balance += $tx['amount'];
            if ($tx['cr_account'] == $sub_id) $account_balance -= $tx['amount'];
        }
        return $account_balance;
    }

    // ── Opening balance: everything before date1 ────────────────────────────
    function calcSubBalanceOpening(mysqli $conn, int $sub_id, string $date1): float {
        $account_balance = 0;
        $openingBefore = selectOpeningBalanceBeforeDateRange($conn, $sub_id, $date1);
        $account_balance += $openingBefore['debit'] - $openingBefore['credit'];
        $transactionsBefore = selectAllTransactionsBeforeDateBySubId($conn, $sub_id, $date1);
        foreach ($transactionsBefore as $tx) {
            if ($tx['dr_account'] == $sub_id) $account_balance += $tx['amount'];
            if ($tx['cr_account'] == $sub_id) $account_balance -= $tx['amount'];
        }
        return $account_balance;
    }

    // ── SubMain opening balance ─────────────────────────────────────────────
    function calcSubMainOpeningBalance(mysqli $conn, int $subMainId, string $date1): float {
        $total = 0;
        $ledgers = selectLedgerBySubMainId($conn, $subMainId);
        if ($ledgers && is_array($ledgers)) {
            foreach ($ledgers as $ledger) {
                $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                if ($subsidiaries && is_array($subsidiaries)) {
                    foreach ($subsidiaries as $sub) {
                        $total += calcSubBalanceOpening($conn, $sub['id'], $date1);
                    }
                }
            }
        }
        return $total;
    }

    // ── SubMain closing balance ─────────────────────────────────────────────
    function calcSubMainClosingBalance(mysqli $conn, int $subMainId, string $date1, string $date2): float {
        $total = 0;
        $ledgers = selectLedgerBySubMainId($conn, $subMainId);
        if ($ledgers && is_array($ledgers)) {
            foreach ($ledgers as $ledger) {
                $subsidiaries = selectSubsidiariesBYledgerId($conn, $ledger['id']);
                if ($subsidiaries && is_array($subsidiaries)) {
                    foreach ($subsidiaries as $sub) {
                        $total += calcSubBalance($conn, $sub['id'], $date1, $date2);
                    }
                }
            }
        }
        return $total;
    }

    // ── Net Profit ──────────────────────────────────────────────────────────
    function calcNetProfit(mysqli $conn, string $date1, string $date2): float {
        $total_income   = 0;
        $total_expenses = 0;
        $incomeMaster = selectMasterByMasterName($conn, "Income");
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
                                    $total_income += -calcSubBalance($conn, $sub['id'], $date1, $date2);
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
                                    $total_expenses += calcSubBalance($conn, $sub['id'], $date1, $date2);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $total_income - $total_expenses;
    }

    // ══════════════════════════════════════════════════════════════════════
    // CASH SUBM ID = 9 — used for reconciliation only, excluded from WC
    // ══════════════════════════════════════════════════════════════════════
    $CASH_SUBM_ID = 9;

    // 1. Net Profit
    $net_profit = calcNetProfit($conn, $date1, $date2);

    // 2. Non-cash items
    // Depreciation — subsidiary 1181
    $depreciation = calcSubBalance($conn, 1181, $date1, $date2);

    // Loan loss provision — movement only during period (subsidiary 1246)
    $loan_loss_closing = calcSubBalance($conn, 1246, $date1, $date2);
    $loan_loss_opening = calcSubBalanceOpening($conn, 1246, $date1);
    $loan_loss_provision = $loan_loss_closing - $loan_loss_opening;

    $total_non_cash = $depreciation + $loan_loss_provision;

    // 3. Working capital changes
    // FORMULA: cash_impact = opening_balance - closing_balance
    // This works for ALL account types (assets, liabilities, capital)
    // because calcSubBalance returns debit-positive for all accounts:
    // Asset increases   → closing > opening → negative cash impact ✅
    // Liability increases → closing more negative → opening - closing = positive ✅
    // Liability decreases → closing less negative → opening - closing = negative ✅

    $working_capital_rows  = [];
    $total_working_capital = 0;

    $swahili_labels = [
        'Earning Assets- Loans'                  => '(Kuongezeka) / kupungua kwa mikopo kwa wanachama',
        'Earning Assets- Investments'            => 'Uwekezaji kwenye UTT-AMIS Liquid fund',
        'Non-Earning Assets ( Fixed Assets)'     => 'Ununuzi wa mali za kudumu',
        'Non-Earning Assets (Other Assets)'      => '(Kuongezeka) / kupungua kwa madeni mengine',
        'Interest Bearing  Savings and Deposits' => '(Kuongezeka) / kupungua kwa hisa za wanachama',
        'Non-Interest Bearing Liabilities'       => 'Kuongezeka / (kupungua) kwa wadaiwa',
        'Current lialibiliteis'                  => 'Kuongezeka / (kupungua) kwa Wadai',
        'Equity'                                 => 'Kuongezeka / (kupungua) kwa mtaji wa wanachama',
    ];

    foreach (['Assets', 'Liabilities', 'Capital'] as $masterName) {
        $master = selectMasterByMasterName($conn, $masterName);
        if (!$master || !is_array($master)) continue;

        $submasters = selectAllSubmasterByMasterId($conn, $master['id']);
        if ($submasters && is_array($submasters)) {
            foreach ($submasters as $submain) {
                // Skip cash subMain — used only for reconciliation
                if ($submain['id'] == $CASH_SUBM_ID) continue;

                $opening = calcSubMainOpeningBalance($conn, $submain['id'], $date1);
                $closing = calcSubMainClosingBalance($conn, $submain['id'], $date1, $date2);

                // opening - closing works universally for all account types
                $cash_impact = $opening - $closing;

                if (abs($cash_impact) > 0.001) {
                    $label = $swahili_labels[$submain['name']] ?? $submain['name'];
                    $working_capital_rows[] = [
                        'name'   => $label,
                        'amount' => $cash_impact
                    ];
                    $total_working_capital += $cash_impact;
                }
            }
        }
    }

    // 4. Net cash from operating activities
    $net_cash_operating = $net_profit + $total_non_cash + $total_working_capital;

    // 5. Bank reconciliation
    $closing_bank        = calcSubMainClosingBalance($conn, $CASH_SUBM_ID, $date1, $date2);
    $opening_bank        = calcSubMainOpeningBalance($conn, $CASH_SUBM_ID, $date1);
    $reconciliation_diff = ($opening_bank + $net_cash_operating) - $closing_bank;
?>

<style>
    .report-header { text-align: center; padding: 30px 0 20px 0; border-bottom: 3px double #333; margin-bottom: 30px; }
    .report-header h3 { font-size: 24px; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; }
    .report-header h4 { font-size: 18px; font-weight: 600; margin-bottom: 6px; text-decoration: underline; }
    .report-header h5 { font-size: 14px; font-weight: normal; color: #666; }
    .cf-section-header td { font-weight: bold; font-size: 13px; background-color: #e9ecef; padding: 8px 6px; }
    .cf-total td { font-weight: bold; }
    @media print {
        @page { size: A4; margin: 15mm; }
        .no-print { display: none !important; }
        .report-header { border-bottom: 2px solid #000; }
    }
</style>

<div class="card-footer">
    <div class="report-header">
        <h3>ST JOHN SACCOSS</h3>
        <h4>TAARIFA YA MTIRIRIKO WA FEDHA</h4>
        <h5>KWA KIPINDI KATI YA <?php echo date("d-M-Y", strtotime($date1)); ?> NA <?php echo date("d-M-Y", strtotime($date2)); ?></h5>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="cashFlowTable">
            <thead class="bg-info">
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 72%;">MAELEZO</th>
                    <th style="width: 20%;" class="text-right">TSHS</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>

                <!-- SECTION A: OPERATING -->
                <tr class="cf-section-header">
                    <td colspan="3">MTIRIRIKO WA FEDHA KUTOKANA NA SHUGHULIYA UENDESHAJI</td>
                </tr>

                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td>Faida (Hasara) kabla ya kodi</td>
                    <td class="text-right"><?php echo number_format($net_profit, 2); ?></td>
                </tr>

                <?php if (abs($depreciation) > 0.001): ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td>Uchakavu</td>
                    <td class="text-right"><?php echo number_format($depreciation, 2); ?></td>
                </tr>
                <?php endif; ?>

                <?php if (abs($loan_loss_provision) > 0.001): ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td>Tengo la mikopo mibaya</td>
                    <td class="text-right"><?php echo number_format($loan_loss_provision, 2); ?></td>
                </tr>
                <?php endif; ?>

                <tr class="table-light cf-total">
                    <td></td>
                    <td><strong>Faida (Hasara) kabla ya mabadiliko ya mtaji uzungukao</strong></td>
                    <td class="text-right"><strong><?php echo number_format($net_profit + $total_non_cash, 2); ?></strong></td>
                </tr>

                <tr class="cf-section-header">
                    <td colspan="3">Mabadiliko ya mtaji uzungukao</td>
                </tr>

                <?php foreach ($working_capital_rows as $row): ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="text-right"><?php echo number_format($row['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>

                <tr class="cf-total table-primary">
                    <td></td>
                    <td><strong>Fedha kutokana na mabadiliko ya mtaji</strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_working_capital, 2); ?></strong></td>
                </tr>

                <tr class="cf-total table-info">
                    <td></td>
                    <td><strong>Mtiririko wa fedha halisi ingia (toka) shughuli za uendeshaji</strong></td>
                    <td class="text-right"><strong><?php echo number_format($net_cash_operating, 2); ?></strong></td>
                </tr>

                <!-- SECTION B: RECONCILIATION -->
                <tr class="cf-section-header">
                    <td colspan="3">UPATANISHO WA FEDHA MKONONI / BENKI</td>
                </tr>

                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td>Fedha mkononi/ benki mwanzo wa mwaka</td>
                    <td class="text-right"><?php echo number_format($opening_bank, 2); ?></td>
                </tr>

                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td>Ongezeko (upungufu) wa fedha</td>
                    <td class="text-right"><?php echo number_format($net_cash_operating, 2); ?></td>
                </tr>

                <tr class="cf-total table-success">
                    <td></td>
                    <td><strong>Fedha mkononi/ benki mwisho wa mwaka</strong></td>
                    <td class="text-right"><strong><?php echo number_format($opening_bank + $net_cash_operating, 2); ?></strong></td>
                </tr>

                <tr>
                    <td></td>
                    <td><em>Salio halisi la benki (kutoka kwenye hesabu)</em></td>
                    <td class="text-right"><em><?php echo number_format($closing_bank, 2); ?></em></td>
                </tr>

                <tr class="<?php echo (abs($reconciliation_diff) < 0.01) ? 'table-success' : 'table-danger'; ?> cf-total">
                    <td></td>
                    <td><strong>Tofauti ya upatanisho</strong></td>
                    <td class="text-right">
                        <strong><?php echo number_format(abs($reconciliation_diff), 2); ?></strong>
                        <?php echo (abs($reconciliation_diff) < 0.01) ? ' ✅ Imepatana' : ' ⚠️ Haijapatana'; ?>
                    </td>
                </tr>

                <tr class="table-secondary text-center">
                    <td colspan="3"><strong><?php echo number_format($reconciliation_diff, 2); ?></strong></td>
                </tr>

            </tbody>
        </table>
    </div>

    <div style="font-size: 13px; margin-top: 10px; line-height: 2;">
        <div>Faida kabla ya kodi: <strong><?php echo number_format($net_profit, 2); ?></strong></div>
        <div>Marekebisho yasiyo ya fedha: <strong><?php echo number_format($total_non_cash, 2); ?></strong></div>
        <div>Mabadiliko ya mtaji uzungukao: <strong><?php echo number_format($total_working_capital, 2); ?></strong></div>
        <div>Mtiririko wa fedha halisi: <strong><?php echo number_format($net_cash_operating, 2); ?></strong></div>
        <div>Fedha mkononi/benki mwanzo: <strong><?php echo number_format($opening_bank, 2); ?></strong></div>
        <div>Fedha mkononi/benki mwisho: <strong><?php echo number_format($closing_bank, 2); ?></strong></div>
        <div>Tofauti: <strong><?php echo number_format(abs($reconciliation_diff), 2); ?></strong>
            <?php echo (abs($reconciliation_diff) < 0.01) ? '(Imepatana)' : '(Haijapatana)'; ?>
        </div>
    </div>

    <div class="text-right mt-4 pt-3 border-top no-print">
        <button class="btn btn-success mr-2" id="exportExcelBtn" onclick="exportTableToExcel('table', 'cash_flow', 'cash_flow')">Excel</button>
        <button class="btn btn-danger" onclick="exportToPDF()">Print</button>
    </div>
</div>