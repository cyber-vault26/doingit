<?php
 $consgId = (int) $conn->real_escape_string($_GET['consgn_id']);
$consignement = selectConsignmentById($conn, $consgId);
?>

<div class="card card-info">
    <div class="card-header">
        <h4>Consignment <?= $consignement['ref_no'] ?> Report </h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <h5>Customer: <?= $consignement['mteja'] ?></h5>
                <p>Date: <?= $consignement['date_'] ?></p>
                <p>Status: <span class="badge badge-<?= $consignement['status'] === 'closed' ? 'success' : 'warning' ?>"><?= ucfirst($consignement['status']) ?></span></p>
            </div>
            <div class="col-md-6 text-right">
                <a href="./?page=view_csgnment_details&consgn_id=<?= $consgId ?>" class="btn btn-outline-primary">
                    <i class="fas fa-eye"></i> View Details
                </a>
            </div>
        </div>





        <!-- Cost Summary -->
        <div class="card card-outline card-danger mb-3">
            <div class="card-header">
                <h5 class="card-title text-danger">Total Costs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cost Item</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get only active drivers from consignment
                            $body = selectConsignmentBody($conn, $consgId);
                            $activDriverIds = [];
                            
                            if($body && is_array($body)){
                                foreach($body as $b){
                                    $activDriverIds[] = $b['driver_id'];
                                }
                            }
                            
                            $costs = selectConsignmentCost($conn, $consgId);
                            $totalCosts = 0;
                            $costSummary = [];
                            $counter = 1;
                          //  print_r($costs);
                          foreach($costs as $item){
                            $name = $item['name'];
                            $amount = floatval($item['dr_ammount']);
                            if(!isset($costSummary[$name])){
                                $costSummary[$name] = 0;
                            }
                            $costSummary[$name] += $amount;
                          }
                      //    print_r($costSummary);
                        //   $counter =1;
                          foreach($costSummary as $name => $amount){
                            echo "<tr>";
                                    echo "<td>$counter</td>";
                                    echo "<td >$name</td>";
                                    echo "<td class='text-right'>". number_format(  $amount,2). "</td>";
                            echo "</tr>";
                            $counter++;
                            $totalCosts += $amount;
                          }
                            
                           
                            ?>
                        </tbody>
                        <tfoot class="table-danger">
                            <tr>
                                <th colspan="2">Total Costs</th>
                                <th class="text-right"><?= number_format($totalCosts, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Income Summary (Hardcoded for now) -->
        <div class="card card-outline card-success mb-3">
            <div class="card-header">
                <h5 class="card-title text-success">Total Income</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Income Source</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php 
                           $incomeSummary =[];
                           $totalIncome = 0;
                           $counter = 1;

                            // Get transactions for this consignment
                           $consignmentSales = selectConsignmentSales($conn, $consgId);
                        //    print_r($consignmentSales);
                            
                           foreach($consignmentSales as $income){
                            $name = $income['name'];
                            $amount = floatval($income['dr_ammount']);
                            if(!isset($incomeSummary[$name])){
                                $incomeSummary[$name] = 0;
                            }
                            $incomeSummary[$name] += $amount;
                           
                           }
                          // print_r($incomeSummary);
                           foreach($incomeSummary as $incomeItem => $incomeAmount){
                                echo "<tr>";
                                        echo "<td>$counter</td>";
                                        echo "<td>$incomeItem</td>";
                                        echo "<td class='text-right'>". number_format( $incomeAmount,2)  ."</td>";
                                echo "</tr>";
                                $totalIncome += $incomeAmount;

                           }
                            
                            ?> 
                        </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <th colspan="2">Total Income</th>
                                <th class="text-right"><?= number_format($totalIncome, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>


<!-- Profit/Loss Summary -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h5 class="card-title">Profit/Loss Summary</h5>
    </div>
    <div class="card-body">
        <?php 
        $profit = $totalIncome - $totalCosts;
        $profitClass = $profit >= 0 ? 'text-success' : 'text-danger';
        $profitLabel = $profit >= 0 ? 'Profit' : 'Loss';
        ?>
        <div class="row text-center">
            <div class="col-md-4">
                <h6>Total Income</h6>
                <h4 class="text-success"><?= number_format($totalIncome, 2) ?></h4>
            </div>
            <div class="col-md-4">
                <h6>Total Costs</h6>
                <h4 class="text-danger"><?= number_format($totalCosts, 2) ?></h4>
            </div>
            <div class="col-md-4">
                <h6><?= $profitLabel ?></h6>
                <h4 class="<?= $profitClass ?>"><?= number_format(abs($profit), 2) ?></h4>
            </div>
        </div>
        
        <!-- Enhanced Profit Margin Indicator -->
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line mr-1"></i>
                    Profit Margin Analysis
                </h6>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Formula: (Profit ÷ Income) × 100
                </small>
            </div>
            
            <?php 
            $profitMargin = $totalIncome > 0 ? (($profit / $totalIncome) * 100) : 0;
            $progressClass = $profitMargin >= 0 ? 'bg-success' : 'bg-danger';
            $marginStatus = '';
            if($profitMargin >= 20) {
                $marginStatus = 'Excellent';
                $statusIcon = 'fas fa-thumbs-up';
                $statusColor = 'text-success';
            } elseif($profitMargin >= 10) {
                $marginStatus = 'Good';
                $statusIcon = 'fas fa-check';
                $statusColor = 'text-info';
            } elseif($profitMargin >= 0) {
                $marginStatus = 'Break Even';
                $statusIcon = 'fas fa-minus';
                $statusColor = 'text-warning';
            } else {
                $marginStatus = 'Loss';
                $statusIcon = 'fas fa-exclamation-triangle';
                $statusColor = 'text-danger';
            }
            ?>
            
            <div class="progress" style="height: 30px;">
                <div class="progress-bar <?= $progressClass ?>" 
                     style="width: <?= min(abs($profitMargin), 100) ?>%"
                     role="progressbar">
                    <strong style="font-size: 14px;">
                        <?= number_format($profitMargin, 1) ?>% Margin
                    </strong>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="<?= $statusColor ?>">
                    <i class="<?= $statusIcon ?> mr-1"></i>
                    <strong><?= $marginStatus ?></strong>
                </span>
                <small class="text-muted">
                    <?php if($profitMargin >= 0): ?>
                        Earning <?= number_format($profitMargin, 1) ?>¢ per 1 TZS income
                    <?php else: ?>
                        Losing <?= number_format(abs($profitMargin), 1) ?>¢ per 1 TZS income
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>
</div>


         