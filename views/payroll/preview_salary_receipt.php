<?php
$staffDetails = selectSubsidiaryById($conn, (int) $_GET['staff_id']);
if ($staffDetails && is_array($staffDetails)) {
    $basicSalary = (float) $_GET['bs'];
    $allowanceAmount = 0;
    $allowance = selectAllowanceByStaffId($conn, (int) $_GET['staff_id']);
    if ($allowance && is_array($allowance)) {
        $allowanceAmount = (float) $allowance['amount'];
    }
    $grossSalary = $basicSalary + $allowanceAmount;
    $nssf = $basicSalary * 0.10;
    $payee = calculatePayee($grossSalary);
    $netPay = $grossSalary - $nssf - $payee;
?>

<div class="card card-light my-3 print-preview-salary-jadon">
    <div class="card-header">
        <h5 class="mb-0">Salary Slip Preview</h5>
    </div>
    <div class="card-body">

        <style>
            .js-slip * { font-family: Arial, sans-serif; color: #333; box-sizing: border-box; }
            .js-slip { background: #fff; }

            /* Header: logo left, title right */
            .js-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 14px; border-bottom: 3px solid #d97c2b; margin-bottom: 20px; }
            .js-header img { height: 65px; object-fit: contain; }
            .js-slip-title { font-size: 26px; font-weight: bold; color: #d97c2b; text-align: right; }
            .js-generated { font-size: 11px; color: #aaa; text-align: right; margin-top: 4px; }

            /* Month bar */
            .js-month-bar { background: #fdf3e7; border-left: 4px solid #d97c2b; padding: 10px 16px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .js-month-bar span { font-size: 13px; font-weight: bold; color: #d97c2b; }

            /* Section title */
            .js-section-title { background: #d97c2b; color: #fff; font-size: 11px; font-weight: bold; padding: 7px 14px; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            /* Rows */
            .js-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 14px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
            .js-row:last-child { border-bottom: none; }
            .js-row-label { font-weight: 600; color: #444; }
            .js-row-value { color: #333; font-weight: 500; }

            /* Divider inside rows */
            .js-divider { height: 1px; background: #dee2e6; margin: 4px 14px; }

            /* Net pay */
            .js-net-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #fdf3e7; border-top: 2px solid #d97c2b; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .js-net-row .js-row-label, .js-net-row .js-row-value { font-size: 15px; font-weight: bold; color: #b85e10; }

            /* Outer border wrapping all rows */
            .js-block { border: 1px solid #dee2e6; margin-bottom: 16px; }

            /* Footer band */
            .js-footer { margin-top: 20px; background: #d97c2b; color: #fff; text-align: center; padding: 12px 20px; font-size: 11px; line-height: 1.8; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .js-footer strong { color: #fff; font-size: 12px; }

            /* Print */
            @media print {
                body * { visibility: hidden !important; }
                .print-preview-salary-jadon, .print-preview-salary-jadon * { visibility: visible !important; }
                .print-preview-salary-jadon { position: absolute; top: 0; left: 0; width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
                .print-preview-salary-jadon .card-header { display: none !important; }
                .print-preview-salary-jadon .card-body { padding: 20px !important; }
                .no-print { display: none !important; }
            }
        </style>

        <div class="js-slip">

            <!-- HEADER: logo + title -->
            <div class="js-header">
                <img src="../../dist/img/logo.png" alt="Logo">
                <div>
                    <div class="js-slip-title">Salary Slip</div>
                    <div class="js-generated">Generated: <?php echo date('d-M-Y'); ?></div>
                </div>
            </div>

            <!-- MONTH SELECTOR screen only -->
            <div class="no-print mb-3">
                <div class="row">
                    <div class="col-md-4 offset-md-8">
                        <label class="form-label" style="font-size:12px; font-weight:600;">Select Month:</label>
                        <input type="month" id="monthInput" class="form-control form-control-sm" onchange="updateMonthText()">
                    </div>
                </div>
            </div>

            <!-- MONTH BAR -->
            <div class="js-month-bar">
                <span>Month / Period:</span>
                <span id="printedMonth">__________</span>
            </div>

            <!-- EMPLOYEE INFO -->
            <div class="js-block">
                <div class="js-section-title">Employee Information</div>
                <div class="js-row">
                    <div class="js-row-label">Name</div>
                    <div class="js-row-value"><?= htmlspecialchars($staffDetails['name']) ?></div>
                </div>
                <div class="js-row">
                    <div class="js-row-label">Position / Title</div>
                    <div class="js-row-value"><?= htmlspecialchars($staffDetails['subcategory'] ?? '—') ?></div>
                </div>
                <div class="js-row">
                    <div class="js-row-label">Phone</div>
                    <div class="js-row-value"><?= htmlspecialchars($staffDetails['phone'] ?? '—') ?></div>
                </div>
            </div>

            <!-- SALARY BREAKDOWN -->
            <div class="js-block">
                <div class="js-section-title">Salary Breakdown</div>
                <div class="js-row">
                    <div class="js-row-label">Basic Salary</div>
                    <div class="js-row-value">TSh <?= number_format($basicSalary, 2) ?></div>
                </div>
                <div class="js-row">
                    <div class="js-row-label">Allowance</div>
                    <div class="js-row-value">TSh <?= number_format($allowanceAmount, 2) ?></div>
                </div>
                <div class="js-row" style="font-weight:bold;">
                    <div class="js-row-label">Gross Salary</div>
                    <div class="js-row-value">TSh <?= number_format($grossSalary, 2) ?></div>
                </div>
                <div class="js-divider"></div>
                <div class="js-row">
                    <div class="js-row-label">NSSF (10%)</div>
                    <div class="js-row-value" style="color:#c0392b;">- TSh <?= number_format($nssf, 2) ?></div>
                </div>
                <div class="js-row">
                    <div class="js-row-label">PAYE</div>
                    <div class="js-row-value" style="color:#c0392b;">- TSh <?= number_format($payee, 2) ?></div>
                </div>
                <div class="js-net-row">
                    <div class="js-row-label">Net Pay</div>
                    <div class="js-row-value">TSh <?= number_format($netPay, 2) ?></div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="js-footer">
                <strong>JADON LIMITED</strong><br>
                2nd Floor, Tevi Commercial Park, Bagamoyo Rd, Plot No.576, Mbezi Beach, P.O. Box 60163, Dar es Salaam, Tanzania.<br>
                Email: info@jadon.co.tz &nbsp;|&nbsp; Tel: +255621009936 / +255 737 829 077 &nbsp;|&nbsp; TIN: 138-847-209 &nbsp;|&nbsp; VRN: 40-034505-T
            </div>

            <!-- PRINT BUTTON -->
            <div class="text-center mt-3 no-print">
                <button class="btn btn-warning btn-lg" onclick="window.print()">
                    <i class="fas fa-print mr-2"></i> Print Salary Slip
                </button>
            </div>

        </div>
    </div>
</div>

<script>
function updateMonthText() {
    var input = document.getElementById('monthInput');
    var output = document.getElementById('printedMonth');
    if (input.value) {
        var parts = input.value.split('-');
        var year = parts[0];
        var month = parseInt(parts[1]);
        var names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        output.textContent = names[month - 1] + ' ' + year;
    } else {
        output.textContent = '__________';
    }
}
window.addEventListener('DOMContentLoaded', function() {
    var monthInput = document.getElementById('monthInput');
    monthInput.value = new Date().toISOString().slice(0, 7);
    updateMonthText();
});
</script>

<?php } ?>