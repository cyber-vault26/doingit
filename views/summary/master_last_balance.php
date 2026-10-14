<div class=" card card-info card-outline">
    <div class=" card-header"> <?= $_GET['name'] ?> Last Balance</div>
    <div class=" card-body">
        <table class=" table table-sm table-bordered data-table">
            <thead class=" table-primary">
                <tr>
                    <td>#</td>
                    <td>Item</td>
                    <td>Balance</td>
                </tr>
            </thead>
            <tbody>
                <?php
                $subs = selectCOAByMasterId($conn, (int) $_GET['master_id']);
                //print_r($subs);
                if($subs && is_array($subs)){
                    $counter = 1;
                    foreach($subs as $sub){
                        $balance = getSubCLosingBalance($conn, $sub['sub_id'], date("Y-m-d"));
                        echo "<tr>";
                        echo "<td>$counter</td>";
                        echo "<td>$sub[subs]</td>";
                        echo "<td> ". number_format( $balance,2) ."</td>";
                        echo "</tr>";
                        $counter++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
 