<div class="card card-info m-2">
    <div class="card-header text-center">
        <h4 class="card-title mb-0">
            <?php 
            $date1 = $_GET['date1'] ?? date("Y-01-01");
            $date2 = $_GET['date2'] ?? date("Y-m-d");
            if ($date1 && $date2) {
                echo "Ledger Report for Accounting Period Between " 
                    . date("d-M-Y", strtotime($date1)) 
                    . " and " . date("d-M-Y", strtotime($date2));
            }
            ?>
        </h4>
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <h3>NAME: 
                <?php 
                $company = selectAllCompanyDetails($conn);
                echo htmlspecialchars($company['title'] ?? ''); 
                ?>
            </h3>
            <h4>
                <?php 
                $ledger_id = $_GET['ledger_id'] ?? '';
                $ledger = selectLedgerById($conn, (int)$ledger_id);
                echo htmlspecialchars($ledger['name'] ?? '');
                ?> Report
            </h4>
            <h5>Between <?php echo date("d-M-Y", strtotime($date1)); ?> and <?php echo date("d-M-Y", strtotime($date2)); ?></h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover data-table">
                <thead class="table-warning">
                    <tr>
                        <th>#</th>
                        <th>Subsidiary Name</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_balance = 0;
                    $row_no = 1;
                    $subsidiaries = selectSubsidiariesBYledgerId($conn, (int)$ledger_id);
                    if ($subsidiaries && count($subsidiaries) > 0) {
                        foreach ($subsidiaries as $sub) {
                            $account_id = $sub['id'];
                            $subsidiary_name = $sub['name'];
                            $subsidiary_balance = 0;

                            $openingBalance = selectOpeningBalance($conn, $account_id);
                            if ($openingBalance && is_array($openingBalance)) {
                                $amount = $openingBalance['ammount'];
                                $type = $openingBalance['type'];
                                if ($type === 'debit') {
                                    $subsidiary_balance += $amount;
                                } else {
                                    $subsidiary_balance -= $amount;
                                }
                            }

                            $transactions = selectAllTransactionsBySubIdAndDateRange($conn, $account_id, $date1, $date2);
                            if ($transactions && is_array($transactions)) {
                                foreach ($transactions as $tx) {
                                    if ($tx['dr_account'] == $account_id) {
                                        $subsidiary_balance += $tx['amount'];
                                    }
                                    if ($tx['cr_account'] == $account_id) {
                                        $subsidiary_balance -= $tx['amount'];
                                    }
                                }
                            }

                            $total_balance += $subsidiary_balance;

                            if ($subsidiary_balance > 0) {
                                $debit_col  = number_format($subsidiary_balance, 2);
                                $credit_col = '0.00';
                            } elseif ($subsidiary_balance < 0) {
                                $debit_col  = '0.00';
                                $credit_col = number_format(abs($subsidiary_balance), 2);
                            } else {
                                $debit_col  = '0.00';
                                $credit_col = '0.00';
                            }
                            ?>
                            <tr class="w3-hover-yellow">
                                <td><?php echo $row_no++; ?></td>
                                <td>
                                   <form action="./?page=view_subsidiary_report_by_date_range" method="post" style="margin:0;padding:0;">
                                        <input type="hidden" name="sub_id" value="<?php echo (int)$account_id; ?>">
                                        <input type="hidden" name="date1" value="<?php echo htmlspecialchars($date1); ?>">
                                        <input type="hidden" name="date2" value="<?php echo htmlspecialchars($date2); ?>">
                                        <button type="submit" style="background:none;border:none;padding:0;margin:0;cursor:pointer;width:100%;text-align:left;color:inherit;font:inherit;">
                                            <?php echo htmlspecialchars($subsidiary_name); ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo $debit_col; ?></td>
                                <td><?php echo $credit_col; ?></td>
                                <td><?php echo number_format($total_balance, 2); ?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="4" class="text-right">Total Balance:</th>
                        <th><?php echo number_format($total_balance, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>