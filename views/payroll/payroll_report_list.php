<div class="card card-info">
    <div class="card-header">
        <h4>Payroll Report List</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-search table-striped">
            <thead class="bg-primary">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Month</th>
                    <th style="width: 70%; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $payrolls = selectSavedPayrolls($conn);
            if($payrolls && is_array($payrolls)){
                $counter = 1;
                foreach($payrolls as $payroll){
                    echo "<tr>";
                    echo "<td>$counter</td>";
                    echo "<td>". date("F-Y", strtotime($payroll['month']))."</td>";
                    echo "<td style='text-align: right;'>";
                    echo "<a class='btn btn-sm btn-info m-1' href='./?page=preview_payroll_report&payroll_id=$payroll[id]&month=$payroll[month]'>Preview Payroll</a>";
                    if($payroll['entry_generated'] == 0){
                        echo "<form method='POST' action='./controllers/payroll_controller.php' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to remove this payroll record?\");'>
                            <input type='hidden' name='delete_id' value='$payroll[id]'>
                            <button type='submit' name='deleteSVDpayroll' class='btn btn-sm btn-danger'>Remove</button>
                        </form>
                        <a class='btn btn-sm btn-primary m-1' href='./?page=generate_payroll_entry&payroll_id=$payroll[id]'>Generate Entry</a>";
                    }
                    echo "</td>";
                    echo "</tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='3' class='text-center'>No payroll records found</td></tr>";
            }
            ?>
            </tbody>
            <tfoot class="bg-primary">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Month</th>
                    <th style="width: 70%; text-align: right;">Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>