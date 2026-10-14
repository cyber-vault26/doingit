<?php
        $grandTotal = 0;
?>
<div class="card card-info card-outline">
    <div class="card-header">
        <h4 class="card-title">Project Costs</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-bordered data-table">
            <thead class="table-info">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Cost Item</th>
                    <th class="text-right">Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
              <?php
                    $projects = listAllProjects($conn);
                    if($projects && is_array($projects)){
                        $projectCounter = 1;
                        foreach($projects as $project){
                            $projectTotal = 0;
                            echo "<tr class='table-warning'>";
                                    echo "<td>$projectCounter</td>";
                                    echo "<td>-</td>";
                                    echo "<td><strong>" . htmlspecialchars($project['name']) . "</strong></td>";
                                    echo "<td class='text-right'>-</td>";
                                    echo "<td><a href='./?page=open_project&id={$project['id']}' class='btn btn-sm btn-outline-danger'><i class='fas fa-eye'></i> Open</a></td>";
                            echo "</tr>";
                            $costs = selectTransactionByProjectId($conn, $project['id']);
                            if(is_array($costs) && count($costs) > 0){
                                foreach($costs as $cost){
                                    echo "<tr>";
                                            echo "<td>-</td>";
                                            echo "<td>" . (!empty($cost['date_']) ? date("d M Y", strtotime($cost['date_'])) : '-') . "</td>";
                                            echo "<td>" . htmlspecialchars($cost['description'] ?? '-') . "</td>";
                                            echo "<td class='text-right'>" . number_format($cost['amount'], 2) . "</td>";
                                            echo "<td>-</td>";
                                            $projectTotal += $cost['amount'];
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr>";
                                        echo "<td>-</td>";
                                        echo "<td>-</td>";
                                        echo "<td class='text-center text-muted'>No costs recorded yet</td>";
                                        echo "<td class='text-right'>-</td>";
                                        echo "<td>-</td>";
                                echo "</tr>";
                            }
                            echo "<tr class='table-primary'>";
                                    echo "<td>-</td>";
                                    echo "<td>-</td>";
                                    echo "<td><strong>" . htmlspecialchars($project['name']) . " Total</strong></td>";
                                    echo "<td class='text-right'><strong>" . number_format($projectTotal, 2) . "</strong></td>";
                                    echo "<td>-</td>";
                            echo "</tr>";
                            $grandTotal += $projectTotal;
                            $projectCounter++;
                        }
                    } else {
                        echo "<tr>";
                        echo "<td>-</td>";
                        echo "<td>-</td>";
                        echo "<td class='text-center text-muted'>No projects found</td>";
                        echo "<td class='text-right'>-</td>";
                        echo "<td>-</td>";
                        echo "</tr>";
                    }
              ?>
            </tbody>
            <tfoot class="table-danger">
                <tr>
                    <th>-</th>
                    <th>-</th>
                    <th><strong>Grand Total Costs</strong></th>
                    <th class="text-right"><strong><?= number_format($grandTotal ?: 0, 2) ?></strong></th>
                    <th><strong>-</strong></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>