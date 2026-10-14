<?php
require_once "../configs.php";
require_once "../functions/utilities_functions.php";
require_once "../functions/transaction_functions.php";
require_once "../functions/voucher_functions.php";
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    if(isset($_GET['select_voucher_by_type'])){
        $date1 = $conn->real_escape_string($_GET['date1']);
        $date2 = $conn->real_escape_string($_GET['date2']);
        $type = $conn->real_escape_string($_GET['type']);
        $status = $conn->real_escape_string($_GET['status']);

        if($type == "sales"){
            $invoices = selectSalesVouchersByDateRangeAndStatus($conn, $date1, $date2, $status);
            ?>
            <div class="bulk-actions" style="display:none;" class="mb-2">
                <?php if($status == 'draft'): ?>
                <button class="btn btn-sm btn-info" onclick="bulkAction('review_sales')">Review Selected</button>
                <?php endif; ?>
                <?php if($status == 'pending'): ?>
                <button class="btn btn-sm btn-warning" onclick="bulkAction('approve_sales')">Approve Selected</button>
                <?php endif; ?>
                <!--<button class="btn btn-sm btn-danger" onclick="bulkAction('delete_sales')">Delete Selected</button>-->
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-search">
                    <tr class="table-primary">
                        <td style="cursor:pointer;"><input type="checkbox" class="select-all" onclick="toggleAll(this)" style="cursor:pointer;"></td>
                        <td>#</td>
                        <td>Customer</td>
                        <td>Reference</td>
                        <td>Date</td>
                        <td>Amount</td>
                        <td>Currency</td>
                        <td>Action</td>
                    </tr>
                    <?php
                    if($invoices && is_array($invoices)){
                        $counter = 1;
                        foreach($invoices as $invoice){
                            $formatted_date = date('Y-M-d', strtotime($invoice['date_']));
                            echo "<tr>
                            <td style='cursor:pointer;'><input type='checkbox' class='row-select' value='$invoice[id]' onchange='toggleRow(this)' style='cursor:pointer;'></td>
                            <td>$counter</td>
                            <td>{$invoice['customer_name']}</td>
                            <td>{$invoice['reference']}</td>
                            <td>$formatted_date</td>
                            <td>".number_format($invoice['amount'],2)."</td>
                            <td>{$invoice['currency_name']}</td>
                            <td>
                                <a class='btn btn-outline-info btn-sm' href='./?page=preview_invoice&id={$invoice['id']}' title='Preview Invoice'>
                                    <i class='fas fa-eye'></i> Preview
                                </a>
                                
                                <a class='btn btn-outline-warning btn-sm d-none' href='./?page=edit_sales_voucher&id={$invoice['id']}' title='Edit Sales Voucher'>
                                    <i class='fas fa-edit'></i> Amend
                                </a>
                            </td>
                            </tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='8'>No Record Found</td></tr>";
                    }
                    ?>
                </table>
            </div>
           
            <?php
            exit;
        }
        
        if($type == "purchases"){
            $purchases = selectPurchaseVouchersByDateRangeAndStatus($conn, $date1, $date2, $status);
            ?>
            <div class="bulk-actions" style="display:none;" class="mb-2">
                <?php if($status == 'draft'): ?>
                <button class="btn btn-sm btn-info" onclick="bulkAction('review_purchase')">Review Selected</button>
                <?php endif; ?>
                <?php if($status == 'pending'): ?>
                <button class="btn btn-sm btn-warning" onclick="bulkAction('approve_purchase')">Approve Selected</button>
                <?php endif; ?>
                <button class="btn btn-sm btn-danger" onclick="bulkAction('delete_purchase')">Delete Selected</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-search">
                    <tr class="table-primary">
                        <td style="cursor:pointer;"><input type="checkbox" class="select-all" onclick="toggleAll(this)" style="cursor:pointer;"></td>
                        <td>#</td>
                        <td>Supplier</td>
                        <td>Reference</td>
                        <td>Date</td>
                        <td>Amount</td>
                        <td>Currency</td>
                        <td>Action</td>
                    </tr>
                    <?php
                    if($purchases && is_array($purchases)){
                        $counter = 1;
                        foreach($purchases as $purchase){
                            $formatted_date = date('Y-M-d', strtotime($purchase['date_']));
                            echo "<tr>
                            <td style='cursor:pointer;'><input type='checkbox' class='row-select' value='$purchase[id]' onchange='toggleRow(this)' style='cursor:pointer;'></td>
                            <td>$counter</td>
                            <td>{$purchase['supplier_name']}</td>
                            <td>{$purchase['reference']}</td>
                            <td>$formatted_date</td>
                            <td>".number_format($purchase['amount'],2)."</td>
                            <td>{$purchase['currency_name']}</td>
                            <td>
                                <a class='btn btn-outline-info btn-sm' href='./?page=preview_purchase_invoice&id={$purchase['id']}' title='Preview Purchase'>
                                    <i class='fas fa-eye'></i> Preview
                                </a>
                                <a class='btn btn-outline-warning btn-sm' href='./?page=edit_purchase_voucher&id={$purchase['id']}' title='Edit Purchase Voucher'>
                                    <i class='fas fa-edit'></i> Amend
                                </a>
                            </td>
                            </tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='8'>No Record Found</td></tr>";
                    }
                    ?>
                </table>
            </div>
            <script>
            function toggleAll(checkbox){
                $('.row-select').prop('checked', checkbox.checked).each(function(){ toggleRow(this); });
            }
            function toggleRow(checkbox){
                var row = $(checkbox).closest('tr');
                $(checkbox).is(':checked') ? row.addClass('table-danger') : row.removeClass('table-danger');
                $('.bulk-actions').toggle($('.row-select:checked').length > 0);
            }
            function bulkAction(action){
                var selected_ids = $('.row-select:checked').map(function(){ return this.value; }).get();
                if(!selected_ids.length || !confirm('Are you sure you want to ' + action + ' selected items?')) return;
                var formData = selected_ids.map(function(id){ return 'selected_ids[]=' + encodeURIComponent(id); }).join('&') + '&' + action + '=1';
                $.ajax({
                    url: './controllers/bulk_action_controller.php',
                    type: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    data: formData,
                    success: function(response){
                        if(response.trim() == 'SUCCESS'){
                            $('.row-select:checked').prop('checked', false);
                            $('.select-all').prop('checked', false);
                            fetchVoucherByType('purchases');
                        } else {
                            alert('Error: ' + response);
                        }
                    }
                });
            }
            </script>
            <?php
            exit;
        }

        $transactions = [];
        if($type == "all"){
            $transactions = selectVoucherByStatusAndDateRange($conn, $status, $date1, $date2);
        } else {
            $transactions = selectVoucherByStatusAndTypeAndDateRange($conn, $status, $type, $date1, $date2);
        }
        ?>
        <div class="bulk-actions mb-2" style="display:none;">
            <?php if($status == 'draft'){ ?>
            <button class="btn btn-sm btn-info" onclick="bulkAction('review_voucher')">Mark AS Reviewed</button>
            <?php } ?>
            <?php if($status == 'pending'){ ?>
            <button class="btn btn-sm btn-warning" onclick="bulkAction('approve_voucher')">Approve Selected</button>
            <?php } ?>
            <button class="btn btn-sm btn-danger" onclick="bulkAction('delete_voucher')">Delete Selected</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-search">
                <tr class="table-primary">
                    <td style="cursor:pointer;"><input type="checkbox" class="select-all" onclick="toggleAll(this)" style="cursor:pointer;"></td>
                    <td>#</td>
                    <td>Date</td>
                    <td>Ref No</td>
                    <td>Debit Account</td>
                    <td>Amount</td>
                    <td>Credit Account</td>
                    <td>Narration</td>
                    <td>Action</td>
                </tr>
                <?php
                if($transactions && is_array($transactions)){
                    $counter = 1;
                    foreach($transactions as $transaction){
                        $editPage = '';
                        if($transaction['voucher_type'] == 'payment')  $editPage = 'payment_voucher';
                        if($transaction['voucher_type'] == 'receipt')  $editPage = 'receipt_voucher';
                        if($transaction['voucher_type'] == 'journal')  $editPage = 'journal_voucher';
                        $editBtn = !empty($editPage) ? "<a class='btn btn-outline-warning btn-sm' href='./?page={$editPage}&id={$transaction['id']}'><i class='fas fa-edit'></i> Amend</a>" : '';
                
                        echo "<tr>
                        <td style='cursor:pointer;'><input type='checkbox' class='row-select' value='{$transaction['id']}' onchange='toggleRow(this)' style='cursor:pointer;'></td>
                        <td>{$counter}</td>
                        <td>{$transaction['date_']}</td>
                        <td>{$transaction['reference_no']}</td>
                        <td>{$transaction['debit_acc']}</td>
                        <td>".number_format($transaction['amount'],2)."</td>
                        <td>{$transaction['credit_acc']}</td>
                        <td>{$transaction['description']}</td>
                        <td>
                            <a class='btn btn-info btn-sm' href='./?page=preview_voucher&id={$transaction['id']}'>Preview</a>
                            {$editBtn}
                        </td>
                        </tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='9'>No Record Found</td></tr>";
                }
                ?>
            </table>
        </div>
        <script>
        function toggleAll(checkbox){
            $('.row-select').prop('checked', checkbox.checked).each(function(){ toggleRow(this); });
        }
        function toggleRow(checkbox){
            var row = $(checkbox).closest('tr');
            $(checkbox).is(':checked') ? row.addClass('table-danger') : row.removeClass('table-danger');
            $('.bulk-actions').toggle($('.row-select:checked').length > 0);
        }
        function bulkAction(action){
            var selected_ids = $('.row-select:checked').map(function(){ return this.value; }).get();
            if(!selected_ids.length || !confirm('Are you sure you want to ' + action.replace('_voucher','') + ' selected items?')) return;
            var formData = selected_ids.map(function(id){ return 'selected_ids[]=' + encodeURIComponent(id); }).join('&') + '&' + action + '=1';
            $.ajax({
                url: './controllers/bulk_action_controller.php',
                type: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: formData,
                success: function(response){
                    if(response.trim() == 'SUCCESS'){
                        $('.row-select:checked').prop('checked', false);
                        $('.select-all').prop('checked', false);
                        fetchVoucherByType('<?php echo $type; ?>');
                    } else {
                        alert('Error: ' + response);
                    }
                }
            });
        }
        </script>
        <?php
    }
    
    
    
  if(isset($_GET['select_bulky_voucher_by_type'])){
    $date1  = $conn->real_escape_string($_GET['date1']);
    $date2  = $conn->real_escape_string($_GET['date2']);
    $type   = $conn->real_escape_string($_GET['type']);
    $status = $conn->real_escape_string($_GET['status']);

    $vouchers = selectBulkyVouchersByDateRangeAndType($conn, $date1, $date2, $type, $status);
    
    ?>
    <div class="bulk-actions" style="display:none;" class="mb-2">
        <?php if($status == 'draft'): ?>
        <button class="btn btn-sm btn-info" onclick="bvBulkAction('review_bulky')">Mark As Reviewed</button>
        <?php endif; ?>
        <?php if($status == 'pending'): ?>
        <button class="btn btn-sm btn-warning" onclick="bvBulkAction('approve_bulky')">Mark As Active</button>
        <?php endif; ?>
        <button class="btn btn-sm btn-danger" onclick="bvBulkAction('delete_bulky')">Delete Selected</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm table-search">
            <tr class="table-primary">
                <td style="cursor:pointer;"><input type="checkbox" class="bv-select-all" onclick="bvToggleAll(this)" style="cursor:pointer;"></td>
                <td>#</td>
                <td>Date</td>
                <td>Reference</td>
                <td>Supplier</td>
                <td>Amount</td>
                <td>Currency</td>
                <td>Action</td>
            </tr>
            <?php
            if($vouchers && is_array($vouchers)){
                $counter = 1;
                foreach($vouchers as $v){
                    $formatted_date = date('Y-M-d', strtotime($v['date_']));
                    echo "<tr>
                        <td style='cursor:pointer;'><input type='checkbox' class='bv-row-select' value='{$v['id']}' onchange='bvToggleRow(this)' style='cursor:pointer;'></td>
                        <td>{$counter}</td>
                        <td>{$formatted_date}</td>
                        <td>{$v['reference']}</td>
                        <td>" . htmlspecialchars($v['supplier'] ?? '-') . "</td>
                        <td>" . number_format($v['amount'], 2) . "</td>
                        <td>{$v['currency_name']}</td>
                        <td>
                            <a class='btn btn-info btn-sm' href='./?page=preview_bulky_voucher&id={$v['id']}'><i class='fas fa-eye'></i> Preview</a>
                            <a class='btn btn-outline-warning btn-sm' href='./?page=edit_bulky_voucher&id={$v['id']}'><i class='fas fa-edit'></i> Amend</a>
                        </td>
                    </tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='8'>No Record Found</td></tr>";
            }
            ?>
        </table>
    </div>
    <script>
    function bvToggleAll(checkbox){
        $('.bv-row-select').prop('checked', checkbox.checked).each(function(){ bvToggleRow(this); });
    }
    function bvToggleRow(checkbox){
        var row = $(checkbox).closest('tr');
        $(checkbox).is(':checked') ? row.addClass('table-danger') : row.removeClass('table-danger');
        $('.bulk-actions').toggle($('.bv-row-select:checked').length > 0);
    }
    function bvBulkAction(action){
        var selected_ids = $('.bv-row-select:checked').map(function(){ return this.value; }).get();
        if(!selected_ids.length || !confirm('Are you sure you want to ' + action + ' selected items?')) return;
        var formData = selected_ids.map(function(id){ return 'selected_ids[]=' + encodeURIComponent(id); }).join('&') + '&' + action + '=1';
        $.ajax({
            url: './controllers/bulk_action_controller.php',
            type: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: formData,
            success: function(response){
                if(response.trim() == 'SUCCESS'){
                    $('.bv-row-select:checked').prop('checked', false);
                    $('.bv-select-all').prop('checked', false);
                    fetchBulkyVoucherByType('<?= $type ?>');
                } else {
                    alert('Error: ' + response);
                }
            }
        });
    }
    </script>
    <?php
    exit;
}
    
    
    
}




if($_SERVER['REQUEST_METHOD'] ==='POST'){
    if(isset($_POST['filter_sales_voucher_report'])){
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate   = $_POST['end_date'] ?? date('Y-m-d');
        $store_id  = !empty($_POST['store_id']) ? (int)$_POST['store_id'] : null;
        $reportData = getSalesVoucherReport($conn, $startDate, $endDate, $store_id);
        $chartData = [
            'daily_sales'          => $reportData['daily_sales'] ?? [],
            'top_customers_chart'  => array_slice($reportData['top_customers'] ?? [], 0, 8),
            'top_items'            => $reportData['top_items'] ?? [],
            'monthly_comparison'   => $reportData['monthly_comparison'] ?? []
        ];
        ?>
        <div class="row">
            <?php if($reportData && $reportData['summary']): ?>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($reportData['summary']['total_sales_amount'] ?? 0, 0) ?></h3>
                        <p>Total Sales Amount</p>
                        <small><?= number_format($reportData['summary']['total_invoices'] ?? 0) ?> Invoices</small>
                    </div>
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($reportData['summary']['average_invoice_amount'] ?? 0, 0) ?></h3>
                        <p>Average Invoice</p>
                        <small><?= number_format($reportData['summary']['unique_customers'] ?? 0) ?> Unique Customers</small>
                    </div>
                    <div class="icon"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($reportData['summary']['taxable_sales'] ?? 0, 0) ?></h3>
                        <p>Taxable Sales</p>
                        <small><?= number_format($reportData['summary']['non_taxable_sales'] ?? 0, 0) ?> Non-Taxable</small>
                    </div>
                    <div class="icon"><i class="fas fa-percentage"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box <?= ($reportData['insights']['growth_rate'] ?? 0) >= 0 ? 'bg-primary' : 'bg-danger' ?>">
                    <div class="inner">
                        <h3><?= number_format($reportData['insights']['growth_rate'] ?? 0, 1) ?>%</h3>
                        <p>Growth Rate</p>
                        <small><?= ($reportData['insights']['growth_rate'] ?? 0) >= 0 ? 'Increased' : 'Decreased' ?> vs Previous Period</small>
                    </div>
                    <div class="icon">
                        <i class="fas <?= ($reportData['insights']['growth_rate'] ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>No Data Available</strong><br>
                    No sales data found for the selected date range: <?= htmlspecialchars($startDate) ?> to <?= htmlspecialchars($endDate) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-area"></i> Daily Sales Trend</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="dailySalesChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-lightbulb"></i> Sales Insights</h3>
                    </div>
                    <div class="card-body">
                        <?php if($reportData && $reportData['insights']): ?>
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-success"><i class="fas fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Peak Sales Day</span>
                                <span class="info-box-number"><?= $reportData['insights']['peak_sales_day']['day_name'] ?? 'N/A' ?></span>
                                <div class="progress"><div class="progress-bar bg-success" style="width: 100%"></div></div>
                                <span class="progress-description"><?= number_format($reportData['insights']['peak_sales_day']['total_amount'] ?? 0, 0) ?> Total Sales</span>
                            </div>
                        </div>
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Customer Retention</span>
                                <span class="info-box-number">
                                    <?php
                                    $retention = $reportData['insights']['customer_retention'] ?? [];
                                    $retentionRate = 0;
                                    if(($retention['total_customers'] ?? 0) > 0){
                                        $retentionRate = round((($retention['repeat_customers'] ?? 0) / $retention['total_customers']) * 100, 1);
                                    }
                                    echo $retentionRate . '%';
                                    ?>
                                </span>
                                <div class="progress"><div class="progress-bar bg-info" style="width: <?= $retentionRate ?>%"></div></div>
                                <span class="progress-description"><?= number_format($retention['repeat_customers'] ?? 0) ?> of <?= number_format($retention['total_customers'] ?? 0) ?> customers returned</span>
                            </div>
                        </div>
                        <div class="callout <?= ($reportData['insights']['growth_rate'] ?? 0) >= 0 ? 'callout-success' : 'callout-warning' ?>">
                            <h5>
                                <i class="fas <?= ($reportData['insights']['growth_rate'] ?? 0) >= 0 ? 'fa-thumbs-up' : 'fa-exclamation-triangle' ?>"></i>
                                Performance Summary
                            </h5>
                            <p>
                                <?php if(($reportData['insights']['growth_rate'] ?? 0) >= 0): ?>
                                    Sales are growing at <?= number_format($reportData['insights']['growth_rate'], 1) ?>% compared to the previous period.
                                <?php else: ?>
                                    Sales have declined by <?= abs(number_format($reportData['insights']['growth_rate'], 1)) ?>%. Review strategies needed.
                                <?php endif; ?>
                            </p>
                            <p class="mb-0"><small>Customer retention rate: <?= $retentionRate ?>%</small></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> Sales by Top Customers</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="topCustomersChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-fire"></i> Top Selling Items</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="topItemsChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-crown"></i> Top Performing Customers</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover" id="customersTable">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Customer Name</th>
                                    <th>Location</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-right">Avg Invoice</th>
                                    <th class="text-center">Last Purchase</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($reportData && $reportData['top_customers'] && is_array($reportData['top_customers'])):
                                    $counter = 1;
                                    foreach($reportData['top_customers'] as $customer):
                                        $daysSinceLastPurchase = $customer['days_since_last_purchase'] ?? 0;
                                        $status      = 'Active';
                                        $statusClass = 'badge-success';
                                        if($daysSinceLastPurchase > 30){ $status = 'Inactive'; $statusClass = 'badge-warning'; }
                                        if($daysSinceLastPurchase > 90){ $status = 'Dormant';  $statusClass = 'badge-danger';  }
                                ?>
                                <tr>
                                    <td><span class="badge badge-<?= $counter <= 3 ? 'gold' : 'secondary' ?>"><?= $counter ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($customer['customer_name']) ?></strong>
                                        <?php if($customer['phone']): ?>
                                        <br><small class="text-muted"><i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($customer['region'] || $customer['district']): ?>
                                        <span class="badge badge-outline badge-info"><?= htmlspecialchars($customer['region'] ?? $customer['district'] ?? 'N/A') ?></span>
                                        <?php if($customer['district'] && $customer['region']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($customer['district']) ?></small>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right"><span class="badge badge-success"><?= number_format($customer['total_amount'], 0) ?></span></td>
                                    <td class="text-center"><span class="badge badge-info"><?= number_format($customer['total_invoices']) ?></span></td>
                                    <td class="text-right"><?= number_format($customer['average_invoice_amount'], 0) ?></td>
                                    <td class="text-center">
                                        <small><?= date('M d, Y', strtotime($customer['last_purchase_date'])) ?></small>
                                        <br><small class="text-muted"><?= $daysSinceLastPurchase ?> days ago</small>
                                    </td>
                                    <td class="text-center"><span class="badge <?= $statusClass ?>"><?= $status ?></span></td>
                                </tr>
                                <?php
                                    $counter++;
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-exclamation-circle"></i> No customer data found for the selected period
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php if($reportData && $reportData['monthly_comparison'] && is_array($reportData['monthly_comparison'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Monthly Sales Comparison (Last 12 Months)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="monthlyChart" style="min-height: 400px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <script>
        window.salesChartData = <?= json_encode($chartData) ?>;
        window.currentStartDate = '<?= $startDate ?>';
        window.currentEndDate = '<?= $endDate ?>';
        </script>
        <?php
        exit();
    }
}
?>