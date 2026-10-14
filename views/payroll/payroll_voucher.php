<div class="card card-primary">
    <div class="card-header"><h4 class="card-title">Payroll Voucher</h4></div>
    <div class="card-body">
        <div class="form-label">
            <h5>Month : <?= date("F-Y", strtotime($_POST['month'])) ?></h5>
        </div>
    </div>
    <div class="card-footer">
        <h5>Payroll Distributions</h5>
    </div>
    <div class="card card-outline card-info">
        <form action="./controllers/payroll_controller.php" method="post">
            <input type="hidden" name="month" value="<?= $_POST['month'] ?>">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-stripped table-bordered">
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
                                <th>NSSF</th>
                                <th colspan="3" class="text-center">Employment Cost</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Payee</th>
                                <th>Advance Cash Loan</th>
                                <th>Total</th>
                                <th></th>
                                <th></th>
                                <th>SDL</th>
                                <th>WCF</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $staffs = selectSubsidiaryByType($conn, 'staff');
                            $totalGrossSalary = 0;
                            $totalBasicSalary = 0;
                            $totalAllowance = 0;
                            $totalPayee = 0;
                            $totalNSSF = 0;
                            $totalSdl = 0;
                            $totalWcf = 0;
                            $totalSalaryAdvance = 0;

                            if ($staffs && is_array($staffs)) {
                                $counter = 1;
                                foreach ($staffs as $staff) {
                                    $basicSalary = selectSalaryByStaffId($conn, $staff['id']);
                                    if (!$basicSalary || !is_array($basicSalary) || empty($basicSalary['amount'])) {
                                        continue;
                                    }

                                    $grossSalary = 0;
                                    $basicAmt = (float) preg_replace('/[^0-9.]/', '', $basicSalary['amount']);
                                    $grossSalary += $basicAmt;
                                    $totalBasicSalary += $basicAmt;

                                    $allowance = selectAllowanceBYStaffId($conn, $staff['id']);
                                    $allowanceAmt = 0;
                                    if ($allowance && is_array($allowance) && !empty($allowance['amount'])) {
                                        $allowanceAmt = (float) preg_replace('/[^0-9.]/', '', $allowance['amount']);
                                        $grossSalary += $allowanceAmt;
                                        $totalAllowance += $allowanceAmt;
                                    }

                                    $nssf = $grossSalary * 0.10;
                                    $taxable = $grossSalary - $nssf;
                                    $payee = calculatePayee($taxable);
                                    $deduction = $payee;
                                    $netPay = $taxable - $payee;
                                    $sdl = $grossSalary * 0.035;
                                    $wcf = $grossSalary * 0.005;
                                    $totalCost = $nssf + $sdl + $wcf;

                                    echo "<tr>";
                                    echo "<td>$counter</td>";
                                    echo "<td><h6>{$staff['name']}</h6><input type='hidden' name='staff_id[]' value='{$staff['id']}'></td>";
                                    echo "<td><input type='text' class='form-control format-number' name='basic_salary[]' value='" . number_format((float)($basicAmt), 2) . "' required></td>";
                                    echo "<td><input type='text' class='form-control format-number' name='allowance[]' value='" . number_format((float)($allowanceAmt), 2) . "' required></td>";
                                    echo "<td><input type='text' class='form-control' name='gross_salary[]' value='" . number_format((float)($grossSalary), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='nssf[]' value='" . number_format((float)($nssf), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='taxable_amount[]' value='" . number_format((float)($taxable), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='payee[]' value='" . number_format((float)($payee), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control format-number' name='salary_advance[]' value='0.00' required></td>";
                                    echo "<td><input type='text' class='form-control' name='deduction[]' value='" . number_format((float)($deduction), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='netpay[]' value='" . number_format((float)($netPay), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='emp_nssf[]' value='" . number_format((float)($nssf), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='sdl[]' value='" . number_format((float)($sdl), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='wcf[]' value='" . number_format((float)($wcf), 2) . "' readonly></td>";
                                    echo "<td><input type='text' class='form-control' name='total[]' value='" . number_format((float)($totalCost), 2) . "' readonly></td>";
                                    echo "</tr>";

                                    $counter++;
                                    $totalGrossSalary += $grossSalary;
                                    $totalNSSF += $nssf;
                                    $totalPayee += $payee;
                                    $totalSdl += $sdl;
                                    $totalWcf += $wcf;
                                }

                                echo "<tr class='table-danger'>";
                                echo "<td colspan='2'>Total</td>";
                                echo "<td><input type='text' class='form-control' name='total_basic_salary' value='" . number_format((float)($totalBasicSalary), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_allowance' value='" . number_format((float)($totalAllowance), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_gross_salary' value='" . number_format((float)($totalGrossSalary), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_nssf' value='" . number_format((float)($totalNSSF), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_taxable_amount' value='" . number_format((float)($totalGrossSalary - $totalNSSF), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_payee' value='" . number_format((float)($totalPayee), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_salary_advance' value='" . number_format((float)($totalSalaryAdvance), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_deduction' value='" . number_format((float)($totalSalaryAdvance + $totalPayee), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_netpay' value='" . number_format((float)($totalGrossSalary - $totalPayee - $totalNSSF), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_emp_nssf' value='" . number_format((float)($totalNSSF), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_sdl' value='" . number_format((float)($totalSdl), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_wcf' value='" . number_format((float)($totalWcf), 2) . "' readonly></td>";
                                echo "<td><input type='text' class='form-control' name='total_total' value='" . number_format((float)($totalNSSF + $totalSdl + $totalWcf), 2) . "' readonly></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="save_payroll" class="btn btn-sm btn-warning">Save Payroll</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {

    $(document).on("input", ".format-number", function () {
        var $row = $(this).closest("tr");

        var basic = parseFloat(String($row.find("input[name='basic_salary[]']").val()).replace(/,/g, '')) || 0;
        var allowance = parseFloat(String($row.find("input[name='allowance[]']").val()).replace(/,/g, '')) || 0;
        var gross = basic + allowance;
        var nssf = gross * 0.10;
        var taxable = gross - nssf;
        var advance = parseFloat(String($row.find("input[name='salary_advance[]']").val()).replace(/,/g, '')) || 0;

        var payee = 0;
        if (taxable <= 270000) {
            payee = 0;
        } else if (taxable > 270000 && taxable <= 520000) {
            payee = 0.08 * (taxable - 270000);
        } else if (taxable > 520000 && taxable <= 760000) {
            payee = 20000 + 0.20 * (taxable - 520000);
        } else if (taxable > 760000 && taxable <= 1000000) {
            payee = 68000 + 0.25 * (taxable - 760000);
        } else if (taxable > 1000000) {
            payee = 128000 + 0.30 * (taxable - 1000000);
        }

        var netPay = taxable - payee - advance;
        var sdl = gross * 0.035;
        var wcf = gross * 0.005;

        $row.find("input[name='gross_salary[]']").val(gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='nssf[]']").val(nssf.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='taxable_amount[]']").val(taxable.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='payee[]']").val(payee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='deduction[]']").val((payee + advance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='netpay[]']").val(netPay.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='emp_nssf[]']").val(nssf.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='sdl[]']").val(sdl.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='wcf[]']").val(wcf.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $row.find("input[name='total[]']").val((nssf + sdl + wcf).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        var totalBasic = 0, totalAllowance = 0, totalGross = 0, totalNssf = 0,
            totalTaxable = 0, totalPayee = 0, totalAdvance = 0, totalDeduction = 0,
            totalNet = 0, totalSdl = 0, totalWcf = 0, totalTotal = 0;

        $("tbody tr").not(".table-danger").each(function () {
            var $r = $(this);
            totalBasic      += parseFloat(String($r.find("input[name='basic_salary[]']").val()).replace(/,/g, '')) || 0;
            totalAllowance  += parseFloat(String($r.find("input[name='allowance[]']").val()).replace(/,/g, '')) || 0;
            totalGross      += parseFloat(String($r.find("input[name='gross_salary[]']").val()).replace(/,/g, '')) || 0;
            totalNssf       += parseFloat(String($r.find("input[name='nssf[]']").val()).replace(/,/g, '')) || 0;
            totalTaxable    += parseFloat(String($r.find("input[name='taxable_amount[]']").val()).replace(/,/g, '')) || 0;
            totalPayee      += parseFloat(String($r.find("input[name='payee[]']").val()).replace(/,/g, '')) || 0;
            totalAdvance    += parseFloat(String($r.find("input[name='salary_advance[]']").val()).replace(/,/g, '')) || 0;
            totalDeduction  += parseFloat(String($r.find("input[name='deduction[]']").val()).replace(/,/g, '')) || 0;
            totalNet        += parseFloat(String($r.find("input[name='netpay[]']").val()).replace(/,/g, '')) || 0;
            totalSdl        += parseFloat(String($r.find("input[name='sdl[]']").val()).replace(/,/g, '')) || 0;
            totalWcf        += parseFloat(String($r.find("input[name='wcf[]']").val()).replace(/,/g, '')) || 0;
            totalTotal      += parseFloat(String($r.find("input[name='total[]']").val()).replace(/,/g, '')) || 0;
        });

        var $t = $("tr.table-danger");
        $t.find("input[name='total_basic_salary']").val(totalBasic.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_allowance']").val(totalAllowance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_gross_salary']").val(totalGross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_nssf']").val(totalNssf.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_taxable_amount']").val(totalTaxable.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_payee']").val(totalPayee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_salary_advance']").val(totalAdvance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_deduction']").val(totalDeduction.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_netpay']").val(totalNet.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_sdl']").val(totalSdl.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_wcf']").val(totalWcf.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $t.find("input[name='total_total']").val(totalTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $("input[name='net_pay_in']").val(totalNet.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $("input[name='nssf_in']").val(totalNssf.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    });

    $("tbody tr").not(".table-danger").each(function () {
        $(this).find(".format-number").first().trigger("input");
    });
});
</script>