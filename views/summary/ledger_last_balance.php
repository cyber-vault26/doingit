<div class=" card card-info card-outline">
    <div class=" card-header"> <h4 class=" card-title"><?= $_GET['ledger_name'] ?> Last Balance</h4> </div>
    <div class=" card-body">
        <table class=" table table-sm table-bordered data-table">
            <thead class=" table-primary">
                <tr>
                    <td>#</td>
                    <td>Subsidiary</td>
                    <td>debit</td>
                    <td>Credit</td>
                    <td>Balance</td>
                </tr>
            </thead>
            <tbody>
                <?php
                    $lastBalance =0;
                    $subs = selectSubsidiariesBYledgerId($conn, (int) $_GET['ledger_id']);
                    if($subs && is_array($subs)){
                        $counter = 1;
                        foreach($subs as $sub){
                            $balance = getSubCLosingBalance($conn, $sub['id'], date("Y-m-d"));
                            $lastBalance += $balance;
                            echo "<tr>";
                            echo "<td>$counter</td>";
                            echo "<td>$sub[name]</td>";
                            if($balance < 0){
                                echo "<td>0.00</td>";
                                echo "<td > ". number_format( $balance,2) ."</td>";
                                echo "<td > ". number_format( $lastBalance,2) ."</td>";
                            } elseif($balance > 0){
                                echo "<td > ". number_format( $balance,2) ."</td>";
                                echo "<td>0.00</td>";
                                echo "<td > ". number_format( $lastBalance,2) ."</td>";
                            }else{
                                echo "<td>0.00</td>";
                                echo "<td>0.00</td>";
                                echo "<td > ". number_format( $lastBalance,2) ."</td>";
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
 