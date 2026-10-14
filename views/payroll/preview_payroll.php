<div class="card card-primary">
    <div class="card-header">
        <h4 class="card-title">Preview Payroll Voucher</h4>
    </div>
    <div class="card-body">
        <h5>Month: <?= date("F-Y", strtotime($_GET['month'])) ?></h5>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <h5>Payroll Distributions</h5>
        <a href="./processes/printings/print_payroll.php?payroll_id=<?= $_GET['payroll_id'] ?>&month=<?= $_GET['month'] ?>" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-print"></i> Print
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Basic Salary</th>
                    <th>Allowances</th>
                    <th>Gross salary</th>
                    <th>NSSF (10%)</th>
                    <th>Taxable Amount</th>
                    <th colspan="3" class="text-center">Deductions</th>
                    <th>Net Pay</th>
                    <th colspan="4" class="text-center">Employment Cost</th>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Payee</td>
                    <td>Advance Cash Loan</td>
                    <td>Total</td>
                    <td></td>
                    <td>NSSF</td>
                    <td>SDL</td>
                    <td>WCF</td>
                    <td>Total</td>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = selectPayrollDetails($conn, (int) $_GET['payroll_id']);
                if($items && is_array($items)){
                    $counter = 1;
                    $totalBasicSalary = 0;
                    $totalGrossSalary = 0;
                    $totalAllowance = 0;
                    $totalNssf = 0;
                    $totalTaxableAmount = 0;
                    $totalPayee = 0;
                    $totalSalaryAdvance = 0;
                    $totalDeduction = 0;
                    $totalNetPay = 0;
                    $totalSdl = 0;
                    $totalWcf = 0;
                    foreach($items as $item){
                        echo "<tr>";
                        echo "<td>$counter</td>";
                        echo "<td>$item[staff]</td>";
                        echo "<td>". number_format($item['basic_salary'],2)."</td>";
                        echo "<td>". number_format($item['allowance'],2)."</td>";
                        echo "<td>". number_format($item['basic_salary'] + $item['allowance'],2)."</td>";
                        echo "<td>". number_format($item['nssf'],2)."</td>";
                        echo "<td>". number_format($item['taxable_amount'],2)."</td>";
                        echo "<td>". number_format($item['payee'],2)."</td>";
                        echo "<td>". number_format($item['advanced_salary'],2)."</td>";
                        echo "<td>". number_format($item['payee'] + $item['advanced_salary'],2)."</td>";
                        echo "<td>". number_format($item['taxable_amount'] - $item['payee'] - $item['advanced_salary'],2)."</td>";
                        echo "<td>". number_format($item['nssf'],2)."</td>";
                        echo "<td>". number_format($item['sdl'],2)."</td>";
                        echo "<td>". number_format($item['wcf'],2)."</td>";
                        echo "<td>". number_format($item['wcf']+$item['sdl']+$item['nssf'],2)."</td>";
                        $counter++;
                        echo "</tr>";
                        $totalBasicSalary += $item['basic_salary'];
                        $totalAllowance += $item['allowance'];
                        $totalGrossSalary += $item['basic_salary'] + $item['allowance'];
                        $totalNssf += $item['nssf'];
                        $totalTaxableAmount += $item['taxable_amount'];
                        $totalPayee += $item['payee'];
                        $totalSalaryAdvance += $item['advanced_salary'];
                        $totalDeduction += $item['taxable_amount'] - $item['payee'] - $item['advanced_salary'];
                        $totalNetPay += $item['taxable_amount'] - $item['payee'] - $item['advanced_salary'];
                        $totalSdl += $item['sdl'];
                        $totalWcf += $item['wcf'];
                    }
                    echo "<tr class='table-info'>";
                    echo "<td colspan='2'>Total</td>";
                    echo "<td>". number_format($totalBasicSalary,2)."</td>";
                    echo "<td>". number_format($totalAllowance,2)."</td>";
                    echo "<td>". number_format($totalGrossSalary,2)."</td>";
                    echo "<td>". number_format($totalNssf,2)."</td>";
                    echo "<td>". number_format($totalTaxableAmount,2)."</td>";
                    echo "<td>". number_format($totalPayee,2)."</td>";
                    echo "<td>". number_format($totalSalaryAdvance,2)."</td>";
                    echo "<td>". number_format($totalDeduction,2)."</td>";
                    echo "<td>". number_format($totalNetPay,2)."</td>";
                    echo "<td>". number_format($totalNssf,2)."</td>";
                    echo "<td>". number_format($totalSdl,2)."</td>";
                    echo "<td>". number_format($totalWcf,2)."</td>";
                    echo "<td>". number_format($totalWcf+$totalSdl+$totalNssf,2)."</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>