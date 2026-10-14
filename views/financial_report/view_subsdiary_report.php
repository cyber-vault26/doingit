<?php
$id = $_POST['sub_id'] ?? 0;
$projectId = isset($_POST['project_id']) && $_POST['project_id'] !== '' ? (int)$_POST['project_id'] : null;

$subsidiary = selectSubsidiaryById($conn, $id);
$name = $subsidiary['name'] ?? 'Unknown';

$balance = 0;
$counter = 1;
$total_debit = 0;
$total_credit = 0;

$openingBalance = selectOpeningBalance($conn, $id);
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

$transactions = selectAllTransactionsBySubId($conn, $id, $projectId);

$project = null;
if ($projectId !== null) {
    $project = listProjectById($conn, $projectId);
}
?>

<style>
    .v-header { display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: 15px; border-bottom: 1px solid #dee2e6; margin-bottom: 20px; }
    .v-logo img { height: 70px; object-fit: contain; }
    .v-title { font-size: 22px; font-weight: bold; color: #d97c2b; margin-bottom: 4px; }
    .v-party-label { font-weight: bold; font-size: 13px; color: #000; margin-bottom: 4px; margin-top: 10px; }
    .v-party-label:first-child { margin-top: 0; }
    .v-party-value { font-size: 13px; color: #333; margin-bottom: 2px; }
    .v-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .v-table thead tr { background: #d97c2b; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .v-table th { padding: 9px 10px; font-size: 12px; font-weight: bold; color: #fff; border: 1px solid #c06a1a; }
    .v-table th.num { text-align: right; }
    .v-table td { padding: 8px 10px; font-size: 12px; border: 1px solid #dee2e6; }
    .v-table td.num { text-align: right; }
    .v-table .tr-opening td { background: #d1e7dd; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .v-table .tr-closing td { background: #f8d7da; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .v-footer { margin-top: 20px; font-size: 11px; color: #fff; text-align: center; background: #d97c2b; padding: 12px 20px; line-height: 1.7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .v-footer strong { color: #fff; font-size: 13px; }

    /* Signature input section */
    .signature-section { margin-top: 10px; }
    .signature-line { border-top: 1px solid #dee2e6; margin-bottom: 12px; }
    .signature-input-group { margin-bottom: 8px; }
    .signature-input {
        width: 100%;
        border: none;
        border-bottom: 1px solid #aaa;
        outline: none;
        font-size: 13px;
        padding: 4px 2px;
        background: transparent;
        color: #333;
    }
    .signature-input::placeholder { color: #aaa; font-style: italic; }
    .sig-label { font-size: 11px; font-weight: 600; color: #888; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em; }
</style>

<div class="card card-light">
    <div class="card-header">
        <h4 class="text-center">Account Statement</h4>
    </div>
    <div class="card-body">

        <!-- HEADER -->
        <div class="v-header">
            <div class="v-left">
                <div class="v-logo">
                    <img src="../../dist/img/logo.png" alt="Logo">
                </div>
                <?php if ($project): ?>
                <div style="margin-top:12px;">
                    <div class="v-party-label">Project:</div>
                    <div class="v-party-value"><strong><?php echo htmlspecialchars($project['name']); ?></strong></div>
                    <?php if (!empty($project['customer_name'])): ?>
                    <div class="v-party-value"><?php echo htmlspecialchars($project['customer_name']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($project['location'])): ?>
                    <div class="v-party-value"><?php echo htmlspecialchars($project['location']); ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="text-align:right;">
                <div class="v-title">Account Statement</div>
                <div class="v-party-label">Account:</div>
                <div class="v-party-value"><strong><?php echo htmlspecialchars($name); ?></strong></div>
                <?php if (!empty($subsidiary['email'])): ?>
                <div class="v-party-value"><?php echo htmlspecialchars($subsidiary['email']); ?></div>
                <?php endif; ?>
                <?php if (!empty($subsidiary['tin'])): ?>
                <div class="v-party-value">TIN: <?php echo htmlspecialchars($subsidiary['tin']); ?></div>
                <?php endif; ?>
                <?php if (!empty($subsidiary['phone'])): ?>
                <div class="v-party-value"><?php echo htmlspecialchars($subsidiary['phone']); ?></div>
                <?php endif; ?>
                <?php if (!empty($subsidiary['address'])): ?>
                <div class="v-party-value"><?php echo htmlspecialchars($subsidiary['address']); ?></div>
                <?php endif; ?>
                <div class="v-party-value" style="margin-top:6px; color:#888; font-size:11px;">Generated: <?php echo date('d-M-Y'); ?></div>
            </div>
        </div>

        <!-- TRANSACTIONS TABLE -->
        <table class="v-table" id="subsidiaryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Details</th>
                    <th>Narration</th>
                    <th class="num">Debit</th>
                    <th class="num">Credit</th>
                    <th class="num">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="tr-opening">
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo $opening_date; ?></td>
                    <td><strong>Opening Balance</strong></td>
                    <td></td>
                    <td class="num"><?php echo $opening_debit ? number_format($opening_debit, 2) : '—'; ?></td>
                    <td class="num"><?php echo $opening_credit ? number_format($opening_credit, 2) : '—'; ?></td>
                    <td class="num"><strong><?php echo number_format($opening_balance, 2); ?></strong></td>
                </tr>
                <?php
                if ($transactions && count($transactions) > 0) {
                    foreach ($transactions as $transaction) {
                        $trans_debit = 0;
                        $trans_credit = 0;
                        $details = '';
                        if ($transaction['dr_account'] == $id) {
                            $corresponding = selectSubsidiaryById($conn, (int)$transaction['cr_account']);
                            $details = htmlspecialchars($corresponding['name'] ?? 'Unknown');
                            $trans_debit = $transaction['amount'];
                            $balance += $trans_debit;
                            $total_debit += $trans_debit;
                        } elseif ($transaction['cr_account'] == $id) {
                            $corresponding = selectSubsidiaryById($conn, (int)$transaction['dr_account']);
                            $details = htmlspecialchars($corresponding['name'] ?? 'Unknown');
                            $trans_credit = $transaction['amount'];
                            $balance -= $trans_credit;
                            $total_credit += $trans_credit;
                        }
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo date("d-M-Y", strtotime($transaction['date_'])); ?></td>
                            <td><?php echo $details; ?></td>
                            <td><?php echo htmlspecialchars($transaction['description'] ?? ''); ?></td>
                            <td class="num"><?php echo $trans_debit ? number_format($trans_debit, 2) : '—'; ?></td>
                            <td class="num"><?php echo $trans_credit ? number_format($trans_credit, 2) : '—'; ?></td>
                            <td class="num"><?php echo number_format($balance, 2); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                <tr class="tr-closing">
                    <td colspan="4">Closing Balance</td>
                    <td class="num"><?php echo number_format($total_debit, 2); ?></td>
                    <td class="num"><?php echo number_format($total_credit, 2); ?></td>
                    <td class="num"><?php echo number_format($balance, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- SIGNATURE INPUT (screen only) -->
        <div class="row no-print">
            <div class="col-md-4 offset-md-8">
                <div class="signature-section">
                    <div class="signature-line"></div>
                    <div class="sig-label">Prepared by</div>
                    <div class="signature-input-group">
                        <input type="text" class="signature-input" id="signature_name" placeholder="Name">
                    </div>
                    <div class="signature-input-group">
                        <input type="text" class="signature-input" id="signature_position" placeholder="Position">
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="v-footer">
            <strong>JADON LIMITED</strong><br>
            2nd Floor, Tevi Commercial Park, Bagamoyo Rd, Plot No.576, Mbezi Beach, P.O. Box 60163, Dar es Salaam, Tanzania.<br>
            Email: info@jadon.co.tz &nbsp;|&nbsp; Tel: +255621009936 / +255 737 829 077 &nbsp;|&nbsp; TIN: 138-847-209 &nbsp;|&nbsp; VRN: 40-034505-T
        </div>

    </div>

    <!-- BUTTONS -->
    <div class="card-footer no-print text-right">
        <button class="btn btn-success mr-2"
            onclick="exportTableToExcel(document.getElementById('subsidiaryTable'), 'Account Statement', 'statement_<?php echo htmlspecialchars($name); ?>')">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </button>
        <button class="btn btn-warning" onclick="printWithSignature()">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
</div>

<script>
function printWithSignature() {
    var name = document.getElementById('signature_name').value;
    var position = document.getElementById('signature_position').value;
    var url = './processes/printings/finacial_reports/print_account_statement.php?id=<?php echo (int)$id; ?>&project_id=<?php echo $projectId ?? ''; ?>&signature_name=' + encodeURIComponent(name) + '&signature_position=' + encodeURIComponent(position);
    window.open(url, '_blank');
}
</script>