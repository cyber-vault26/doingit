<?php
//    print_r($_GET);

    require_once __DIR__ . "/../../functions/reconciliation_functions.php";
    $conn = openConn();
    $data = [];
    $reconcilationDetails = listMonthlyBalanceByBalanceId($conn, $_GET['balance_id']);

    if($reconcilationDetails && isset($reconcilationDetails['data'])){
        $data = $reconcilationDetails['data'];
    }

    $date1 = date("Y-m-01", strtotime($data['month']));
    $date2 = date("Y-m-t", strtotime($data['month']));
    $subId = $data['bank_account_id'];

    $transactions = listTransactionBySubIdAndDateRange($conn,$subId, $date1,$date2);
//    print_r($transactions);
?>

<style>
@media print {
    .card-tools {
        display: none !important;
    }
    
    /* Additional print optimizations */
    .card-header {
        padding: 10px !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    body {
        margin: 0;
        padding: 10px;
    }
}
</style>

<script>

</script>



<div class=" card card-info">
    <div class=" card-header">
        <h4 class="card-title">Transaction betwee <?= $date1 . " And " . $date2 ." In " . $data['account_name'] ?></h4>
        <div class=" card-tools">

            <button onclick="window.print()" class="btn btn-sm btn-primary"> <i class="fa fa-print"></i> Print</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="exportToPDF('<?= htmlspecialchars($data['account_name']) ?>', '<?= $data['month'] ?>')">  <i class="fa fa-file-pdf"></i> Save as PDF</button>
            <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel('<?= $data['account_name'] ?>', '<?= $data['month'] ?>')"> <i class="fa fa-file-excel"></i> save as excel</button>
                        <a href="./?page=reconcile" class="btn btn-sm btn-danger"> <i class="fa fa-times"></i> close</a>
        </div>
    </div>
    <div class=" card-body">
            <div class=" table-responsive">
                <table class=" table table-bordered">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>
                                Date
                            </td>
                            <td>Voucher Type</td>
                            <td>Corresponding</td>
                            
                            <td>Narration</td>
                            <td>type</td>
                            <td>Amount</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if($transactions && is_array($transactions)){
                                $counter = 1;
                                foreach($transactions as $t){
                                    echo "<tr>";
                                        echo "<td>$counter</td>";
                                        echo "<td>{$t['date_']}</td>";
                                        echo "<td>{$t['voucher_type']}</td>";
                                        echo "<td>" . ($t['cr_account']==$subId ? $t['debit_account'] : $t['credit_account']) ."</td>";
                                        echo "<td>{$t['description']}</td>";
                                        echo "<td>" . ($t['cr_account']==$subId ? '<span class="badge badge-danger">Credit</span>' : '<span class="badge badge-success">Debit</span>') . "</td>";
                                        echo "<td>" . number_format($t['amount'], 2) . "</td>";
                                    echo "</tr>";
                                $counter++;
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>    
    </div>
</div>