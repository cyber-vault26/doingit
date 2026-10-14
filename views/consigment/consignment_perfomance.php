<?php
$totalAmountProvided = 0;
$totalCost = 0;
$projects = listAllProjects($conn);
if($projects && is_array($projects)){
    foreach($projects as $project){
        $projectPrice = $project['price'];
        $totalAmountProvided += $projectPrice;
        $costs = selectRevCostByProjectId($conn, $project['id']);
        if(is_array($costs) && count($costs) > 0){
            foreach($costs as $cost){
                $totalCost += $cost['amount'];
            }
        }
    }
}
?>

<div class="card card-info">
    <div class="card-header"><h4>Project List</h4></div>
    <div class="card-body">
        
        <!-- Summary Statistics Cards as Buttons -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card-footer" style="cursor: pointer;" onclick="window.location.href='?page=all_consignment'">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Projects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            $totalProjectsQuery = "SELECT COUNT(id) as jumla FROM `projects` WHERE deleted_at is null";
                                $totalProjectsResult = mysqli_query($conn, $totalProjectsQuery);
                                $totalProjects = 0;
                                if($totalProjectsResult && mysqli_num_rows($totalProjectsResult) > 0) {
                                    $row = mysqli_fetch_assoc($totalProjectsResult);
                                    $totalProjects = $row['jumla'];
                            }

                            echo number_format($totalProjects); 
                            
                            ?>
                            </div>
                            <div class="text-sm text-primary mt-1 font-weight-bold">View More</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card-footer" style="cursor: pointer;" onclick="window.location.href='?page=consignment_cost_report'">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Costs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">TZS <?php echo number_format($totalCost, 2); ?></div>
                            <div class="text-sm text-primary mt-1 font-weight-bold">View More</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card-footer" style="cursor: pointer;" onclick="window.location.href='?page=project_profit_report'"> 
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Amount Provided</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">TZS <?php echo number_format($totalAmountProvided, 2); ?></div>
                            <div class="text-sm text-primary mt-1 font-weight-bold">View More</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card-footer" style="cursor: pointer;" onclick="window.location.href='?page=project_profit_report'">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Net Profit/Loss</div>
                           <div class="h5 mb-0 font-weight-bold text-gray-800">TZS <?php echo number_format($totalAmountProvided - $totalCost, 2); ?></div>
                            <div class="text-sm text-primary mt-1 font-weight-bold">View More</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
         
        <!-- Project Table -->
        <div class="card-footer">
            <h4>Available Project List</h4>
        </div>
        
        <div class="card-body">
            <table class="table table-sm">
            <tr class='table-primary'>
                <td><label for="">#</label></td>
                <td><label for="">Project Name</label></td>
                <td><label for="">Date</label></td>
                <td><label for="">Customer</label></td>
                <td><label for="">Location</label></td>
                <td><label for="">Price</label></td>
                <td><label for="">Status</label></td>
                <td><label for="">Actions</label></td>
            </tr>
            <?php
                $projects = listAllProjects($conn);
                if($projects && is_array($projects)){
                    $counter = 1;
                    foreach($projects as $project){
                        echo "<tr>";
                            echo "<td>$counter</td>";
                            echo "<td>{$project['name']}</td>";
                            echo "<td>{$project['date_']}</td>";
                            echo "<td>{$project['customer_name']}</td>";
                            echo "<td>{$project['location']}</td>";
                            echo "<td>{$project['price']}</td>";
                            echo "<td>{$project['status']}</td>";
                            echo "<td>
                                 <a href='./?page=view_project_performance&project_id={$project['id']}' class='btn btn-sm btn-outline-primary'>View Report</a>";
                        echo "</td>";
                        echo "</tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='8'>No data</td></tr>";
                }
            ?>
            <tr class='table-primary'>
                <td><label for="">#</label></td>
                <td><label for="">Project Name</label></td>
                <td><label for="">Date</label></td>
                <td><label for="">Customer</label></td>
                <td><label for="">Location</label></td>
                <td><label for="">Price</label></td>
                <td><label for="">Status</label></td>
                <td><label for="">Actions</label></td>
            </tr>
                    </table>
        </div>  
    </div>
    <div class="card-footer"> -- end --</div>
</div>