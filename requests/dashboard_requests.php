<?php
require_once "../configs.php";
require_once "../functions/subsidiary_functions.php";
require_once "../functions/stock_functions.php";
require_once "../functions/min_sub_functions.php";
require_once "../functions/utilities_functions.php";
require_once "../functions/summary_function.php";
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    if(isset($_GET['stock_summaries'])){
        $date1    = $conn->real_escape_string($_GET['date1']);
        $date2    = $conn->real_escape_string($_GET['date2']);
        $store_id = $_GET['store_id'] !== '' ? (int)$_GET['store_id'] : null;
        $in           = sumStockIn($conn, $date1, $date2, $store_id);
        $out          = sumStockOut($conn, $date1, $date2, $store_id);
        $transfer     = sumStockTransfers($conn, $date1, $date2, $store_id);
        $reorder      = countItemsBelowReorder($conn, $store_id);
        $low_stock    = selectLowStockItems($conn, $store_id);
        $most_sold    = selectMostSoldItems($conn, $date1, $date2, $store_id);
        $most_bought  = selectMostPurchasedItems($conn, $date1, $date2, $store_id);
        $trend        = selectInOutTrend($conn, $date1, $date2, $store_id);
        $suppliers    = selectTopSuppliers($conn, $date1, $date2, $store_id);
        $by_store     = selectInOutByStore($conn, $date1, $date2);
        $slow_moving  = selectSlowMovingItems($conn, $date1, $date2, $store_id);
        ?>

        <!-- KPI CARDS -->
        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 style="font-size:2rem;"><?= number_format($in['total_grns']) ?></h3>
                        <p>GRNs Posted</p>
                    </div>
                    <div class="icon"><i class="fas fa-truck"></i></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3 style="font-size:1.4rem;"><?= number_format($in['total_value'], 2) ?></h3>
                        <p>Value Received</p>
                    </div>
                    <div class="icon"><i class="fas fa-coins"></i></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 style="font-size:2rem;"><?= number_format($out['total_gdns']) ?></h3>
                        <p>GDNs Posted</p>
                    </div>
                    <div class="icon"><i class="fas fa-dolly"></i></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="small-box bg-orange">
                    <div class="inner">
                        <h3 style="font-size:1.4rem;"><?= number_format($out['total_value'], 2) ?></h3>
                        <p>Value Issued</p>
                    </div>
                    <div class="icon"><i class="fas fa-coins"></i></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 style="font-size:2rem;"><?= number_format($transfer['total_transfers']) ?></h3>
                        <p>Transfers</p>
                    </div>
                    <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 style="font-size:2rem;"><?= number_format($reorder['reorder_count']) ?></h3>
                        <p>Reorder Alerts</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>

        <!-- ROW: LOW STOCK ALERTS + SLOW MOVING -->
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Low Stock / Reorder Alerts</h3>
                    </div>
                    <div class="card-body p-0" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-danger text-white" style="position:sticky;top:0;">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Net Stock</th>
                                    <th>Reorder At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($low_stock && is_array($low_stock) && count($low_stock) > 0): ?>
                                    <?php foreach($low_stock as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                                            <td><?= htmlspecialchars($row['base_unit'] ?? '-') ?></td>
                                            <td><?= number_format($row['net_stock'], 2) ?></td>
                                            <td><?= number_format($row['reorder_level'], 2) ?></td>
                                            <td>
                                                <?php if($row['net_stock'] <= 0): ?>
                                                    <span class="badge badge-danger">Out of Stock</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">All items are sufficiently stocked</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
           <div class="col-md-6 col-12">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Slow Moving Items</h3>
                </div>
                <div class="card-body p-0" style="max-height:320px;overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-secondary text-white" style="position:sticky;top:0;">
                            <tr>
                                <th>Item</th>
                                <th>Unit</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($slow_moving && is_array($slow_moving) && count($slow_moving) > 0): ?>
                                <?php foreach($slow_moving as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                                        <td><?= htmlspecialchars($row['base_unit'] ?? '-') ?></td>
                                        <td><?= number_format($row['balance'] ?? 0, 2) ?></td>
                                        <td><span class="badge badge-warning">No Sales</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No slow moving items in this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
         </div>

        <!-- ROW: MOST SOLD + MOST PURCHASED -->
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Most Sold Items</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-warning" style="position:sticky;top:0;">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Qnty</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($most_sold && is_array($most_sold) && count($most_sold) > 0): ?>
                                    <?php foreach($most_sold as $i => $row): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                                            <td><?= number_format($row['total_base_quantity']) ?></td>
                                            <td><?= number_format($row['total_value'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No sales data in this period</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Most Purchased Items</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-success text-white" style="position:sticky;top:0;">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>GRNs</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($most_bought && is_array($most_bought) && count($most_bought) > 0): ?>
                                    <?php foreach($most_bought as $i => $row): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                                            <td><?= number_format($row['total_grns']) ?></td>
                                            <td><?= number_format($row['total_value'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No purchase data in this period</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW: TREND LINE + TOP SUPPLIERS PIE -->
        <div class="row">
            <div class="col-md-8 col-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">IN vs OUT Trend</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" style="height:280px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Top Suppliers</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="suppliersChart" style="height:280px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW: IN vs OUT BY STORE -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">IN vs OUT by Store</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="storeChart" style="height:260px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function(){
            <?php
            $trend_labels   = json_encode(array_column($trend, 'period'));
            $trend_in       = json_encode(array_map(fn($r) => round($r['value_in'], 2), $trend));
            $trend_out      = json_encode(array_map(fn($r) => round($r['value_out'], 2), $trend));
            $sup_labels     = json_encode(array_column($suppliers, 'supplier_name'));
            $sup_values     = json_encode(array_map(fn($r) => round($r['total_value'], 2), $suppliers));
            $store_labels   = json_encode(array_column($by_store, 'store_name'));
            $store_in       = json_encode(array_map(fn($r) => round($r['value_in'], 2), $by_store));
            $store_out      = json_encode(array_map(fn($r) => round($r['value_out'], 2), $by_store));
            ?>
            var trendLabels  = <?= $trend_labels ?>;
            var trendIn      = <?= $trend_in ?>;
            var trendOut     = <?= $trend_out ?>;
            var supLabels    = <?= $sup_labels ?>;
            var supValues    = <?= $sup_values ?>;
            var storeLabels  = <?= $store_labels ?>;
            var storeIn      = <?= $store_in ?>;
            var storeOut     = <?= $store_out ?>;

            if(document.getElementById('trendChart')){
                new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: [
                            { label: 'Value IN', data: trendIn,  borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,0.1)',  fill: true, tension: 0.3 },
                            { label: 'Value OUT', data: trendOut, borderColor: '#ffc107', backgroundColor: 'rgba(255,193,7,0.1)', fill: true, tension: 0.3 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                });
            }

            if(document.getElementById('suppliersChart')){
                new Chart(document.getElementById('suppliersChart'), {
                    type: 'doughnut',
                    data: {
                        labels: supLabels,
                        datasets: [{ data: supValues, backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#17a2b8','#6f42c1','#fd7e14'] }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }

            if(document.getElementById('storeChart')){
                new Chart(document.getElementById('storeChart'), {
                    type: 'bar',
                    data: {
                        labels: storeLabels,
                        datasets: [
                            { label: 'Value IN',  data: storeIn,  backgroundColor: 'rgba(40,167,69,0.8)' },
                            { label: 'Value OUT', data: storeOut, backgroundColor: 'rgba(255,193,7,0.8)' }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { x: { stacked: false }, y: { stacked: false } } }
                });
            }
        })();
        </script>
        <?php
    }
}