<div class=" card card-info card-outline">
    <div class=" card-header"> <h4 class=" card-title"><?= $_GET['sub_name'] ?> Last Balance</h4> </div>
 <div class=" card-body">
    <table class=" table table-sm table-bordered data-table">
        <thead class=" table-primary">
            <tr>
                <td>#</td>
                <td>Subsidiary</td>
                <td>Description</td>
                <td>debit</td>
                <td>Credit</td>
                <td>Balance</td>
            </tr>
        </thead>
        <tbody>
            <?php
                 $mainBalance =0;
                $transactions = selectTransactionBySubsidiaryId($conn, (int) $_GET['sub_id']);
                //print_r($transactions);
                if($transactions && is_array($transactions)){
                    $counter = 1;
                    foreach($transactions as $transaction){
                        $balance = getSubCLosingBalance($conn, $_GET['sub_id'], date("Y-m-d"));
                        $mainBalance += $balance;
                        echo "<tr>";
                        echo "<td>$counter</td>";
                            if($transaction['cr_account'] === $_GET['sub_id']){
                                echo "<td>$transaction[debit_acc]</td>";
                            } elseif($transaction['dr_account'] === $_GET['sub_id']){
                                echo "<td>$transaction[credit_acc]</td>";
                            }
                        echo "<td>$transaction[description]</td>";

                        if($transaction['cr_account'] === $_GET['sub_id']){
                            echo "<td></td>";
                            echo "<td> ". $transaction['cr_ammount'] ." </td>";
                            echo "<td> ". number_format( $mainBalance,2) ." </td>";
                        } elseif($transaction['dr_account'] === $_GET['sub_id']){
                            echo "<td> ". $transaction['dr_ammount'] ." </td>";
                            echo "<td></td>";
                            echo "<td> ". number_format( $mainBalance,2) ." </td>";
                        }
                        echo "</tr>";
                        $counter++;
                    }
                }
            ?>
        </tbody>
    </table>
 </div>
</div>


<script src="./dist/datatable2/jquery-3.7.1.js"></script>
 <script src="./dist/datatable2/datatables.js"></script>
 
 <script>
     $(document).ready(function(){
                 new DataTable('.data-table', {
                     responsive: true,
                     ordering:false,
                     layout: {
                 topStart: {
                     buttons: [
                         'copy', 'excel', 'pdf','print'
                     ]
                 }
             }
         });
     });
 </script>