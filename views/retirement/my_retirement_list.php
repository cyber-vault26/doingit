<div class=" card card-primary">
    <div class=" card-header">
        <h4 class=" card-title"> My Retirement List</h4>
    </div>
    <div class=" card-body">
        <div class=" table-responsive">
            <table class=" table table-bordered table-hover" id="retirementTable">
                <thead>
                    <tr>
                        <td>#</td>
                        <td>ref No</td>
                        <td>Date</td>
                        <td>Retired By</td>
                        <td>Amount</td>
                        <td>Action</td>
                    
                    </tr>
                </thead>
                <tbody>
                    <?php
                            $retirements =  selectRetirementByUserId($conn, $_SESSION['userid']);
                            $counter = 1;
                            if($retirements && is_array($retirements)){
                                foreach($retirements as $retirement){
                                    echo "<tr>";
                                    echo "<td>$counter</td>";
                                    echo "<td>$retirement[ref_no]</td>";
                                    echo "<td>".date("d/m/Y", strtotime($retirement['date_']))."</td>";
                                    echo "<td>$retirement[mtumaji]</td>";
                                    echo "<td>".number_format($retirement['amount'],2)."</td>";
                                    echo "<td>";
                                    
                                        echo "<a class='btn btn-sm  btn-info' href='./?page=preview_retirement&id=$retirement[id]'>Preview the retirement</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                    $counter++;
                                }
                            }
                        

                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class=" card-footer">
        
    </div>
</div>