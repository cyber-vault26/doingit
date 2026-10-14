<?php
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date']   ?? date('Y-m-d');
$store_id  = !empty($_GET['store_id']) ? (int)$_GET['store_id'] : null;
$reportData = getSalesVoucherReport($conn, $startDate, $endDate, $store_id);
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Sales Voucher Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Vouchers</a></li>
                    <li class="breadcrumb-item active">Sales Voucher Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Date Filter</h3>
                    </div>
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="start_date"><i class="fas fa-calendar-alt"></i> Start Date</label>
                                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $startDate ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="end_date"><i class="fas fa-calendar-alt"></i> End Date</label>
                                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $endDate ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter_store"><i class="fas fa-store"></i> Store</label>
                                        <select id="filter_store" name="store_id" class="form-control form-control-sm select2-form select2bs4-form" <?= $cost_center ? 'disabled' : '' ?>>
                                            <option value="">All Stores</option>
                                            <?php
                                            $stores = selectAllStockStores($conn);
                                            if($stores && is_array($stores)){
                                                foreach($stores as $store){
                                                    echo "<option value='$store[id]' " . ($store['id'] == $cost_center ? 'selected' : '') . ">$store[name]</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                        <?php if($cost_center): ?>
                                        <input type="hidden" name="store_id" value="<?= $cost_center ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="d-block">
                                            <button type="button" id="filterBtn" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Filter Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="summaryContainer">
        <?php if($reportData && $reportData['summary']): ?>
        <div class="row">
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
        </div>
        <?php endif; ?>
        </div>
        <div id="chartsContainer">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-area"></i> Daily Sales Trend</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
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
        </div>
        <div id="customersContainer">
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
        </div>
        <div id="monthlyContainer">
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
        </div>
    </div>
</section>
<?php
$chartData = [
    'daily_sales'         => $reportData['daily_sales'] ?? [],
    'top_customers_chart' => array_slice($reportData['top_customers'] ?? [], 0, 8),
    'top_items'           => $reportData['top_items'] ?? [],
    'monthly_comparison'  => $reportData['monthly_comparison'] ?? []
];
?>
<script>
window.salesChartData    = <?= json_encode($chartData) ?>;
window.currentStartDate  = '<?= $startDate ?>';
window.currentEndDate    = '<?= $endDate ?>';
window.dailySalesChart   = null;
window.topCustomersChart = null;
window.topItemsChart     = null;
window.monthlyChart      = null;

function destroyAllCharts() {
    if (window.dailySalesChart)   { window.dailySalesChart.destroy();   window.dailySalesChart   = null; }
    if (window.topCustomersChart) { window.topCustomersChart.destroy(); window.topCustomersChart = null; }
    if (window.topItemsChart)     { window.topItemsChart.destroy();     window.topItemsChart     = null; }
    if (window.monthlyChart)      { window.monthlyChart.destroy();      window.monthlyChart      = null; }
}

function destroyDataTable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#customersTable')) {
        $('#customersTable').DataTable().destroy();
    }
}

function initializeSalesCharts() {
    destroyAllCharts();
    initializeDailySalesChart();
    initializeTopCustomersChart();
    initializeTopItemsChart();
    initializeMonthlyChart();
}

function initializeDailySalesChart() {
    const ctx = document.getElementById('dailySalesChart');
    if (!ctx) return;
    const dailyData = (window.salesChartData?.daily_sales || []).filter(item =>
        item && item.sale_date && item.daily_amount != null && item.daily_invoices != null
    );
    if (!dailyData.length) return;
    window.dailySalesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(item => {
                try { return new Date(item.sale_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }); }
                catch(e) { return ''; }
            }),
            datasets: [{
                label: 'Daily Sales Amount',
                data: dailyData.map(item => parseFloat(item.daily_amount) || 0),
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } } }
        }
    });
}

function initializeTopCustomersChart() {
    const ctx = document.getElementById('topCustomersChart');
    if (!ctx) return;
    const validData = (window.salesChartData?.top_customers_chart || []).filter(item =>
        item && item.customer_name && item.total_amount > 0
    );
    if (!validData.length) return;
    window.topCustomersChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: validData.map(item => {
                const name = item.customer_name || 'Unknown';
                return name.length > 15 ? name.substring(0, 15) + '...' : name;
            }),
            datasets: [{
                data: validData.map(item => parseFloat(item.total_amount) || 0),
                backgroundColor: [
                    'rgba(255,99,132,0.8)','rgba(54,162,235,0.8)','rgba(255,206,86,0.8)',
                    'rgba(75,192,192,0.8)','rgba(153,102,255,0.8)','rgba(255,159,64,0.8)',
                    'rgba(199,199,199,0.8)','rgba(83,102,255,0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const c = validData[context.dataIndex];
                            return `${c.customer_name}: ${parseFloat(c.total_amount).toLocaleString()} (${c.total_invoices || 0} invoices)`;
                        }
                    }
                }
            }
        }
    });
}

function initializeTopItemsChart() {
    const ctx = document.getElementById('topItemsChart');
    if (!ctx) return;
    const topItemsData = window.salesChartData?.top_items || [];
    if (!topItemsData.length) return;
    window.topItemsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: topItemsData.map(item => item.item_name),
            datasets: [{
                label: 'Sales Amount',
                data: topItemsData.map(item => parseFloat(item.total_amount)),
                backgroundColor: 'rgba(54,162,235,0.6)',
                borderColor: 'rgba(54,162,235,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } },
                x: { ticks: { maxRotation: 45, minRotation: 0 } }
            }
        }
    });
}

function initializeMonthlyChart() {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    const validData = (window.salesChartData?.monthly_comparison || []).filter(item =>
        item && item.month_name && item.total_amount != null && item.total_invoices != null
    );
    if (!validData.length) return;
    window.monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: validData.map(item => item.month_name || 'Unknown'),
            datasets: [{
                label: 'Sales Amount',
                data: validData.map(item => parseFloat(item.total_amount) || 0),
                backgroundColor: 'rgba(40,167,69,0.6)',
                borderColor: 'rgba(40,167,69,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'top' } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } },
                x: { ticks: { maxRotation: 45, minRotation: 0 } }
            }
        }
    });
}

function initializeDataTables() {
    destroyDataTable();
    if ($.fn.DataTable && $('#customersTable').length && $('#customersTable tbody tr').length > 0 && $('#customersTable tbody tr td').length > 1) {
        $('#customersTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            ordering: true,
            info: true,
            paging: true,
            searching: true,
            pageLength: 25,
            order: [[3, 'desc']],
            columnDefs: [
                { defaultContent: '', targets: '_all' }
            ],
            language: {
                emptyTable: 'No customer data available',
                info: 'Showing _START_ to _END_ of _TOTAL_ customers',
                search: 'Search customers:'
            }
        });
    }
}

function showLoadingSpinner() {
    const overlay = '<div class="overlay dark"><div class="d-flex justify-content-center align-items-center" style="min-height: 200px;"><div class="spinner-border text-light" style="width: 2rem; height: 2rem;"></div></div></div>';
    $('#summaryContainer, #chartsContainer, #customersContainer, #monthlyContainer').addClass('overlay-wrapper').prepend(overlay);
}

function hideLoadingSpinner() {
    $('#summaryContainer, #chartsContainer, #customersContainer, #monthlyContainer').removeClass('overlay-wrapper').find('.overlay').remove();
}

function filterSalesReport() {
    const startDate = $('#start_date').val();
    const endDate   = $('#end_date').val();
    const storeId   = $('#filter_store').val();
    if (!startDate || !endDate) {
        $(document).Toasts('create', { class: 'bg-warning', title: 'Warning', body: '<i class="fas fa-exclamation-triangle mr-2"></i>Please select both start and end dates!', autohide: true, delay: 4000 });
        return;
    }
    if (new Date(startDate) > new Date(endDate)) {
        $(document).Toasts('create', { class: 'bg-warning', title: 'Warning', body: '<i class="fas fa-exclamation-triangle mr-2"></i>Start date cannot be after end date!', autohide: true, delay: 4000 });
        return;
    }
    window.currentStartDate = startDate;
    window.currentEndDate   = endDate;
    showLoadingSpinner();
    $.ajax({
        url: './requests/voucher_requests.php',
        method: 'POST',
        data: {
            filter_sales_voucher_report: true,
            start_date: startDate,
            end_date: endDate,
            store_id: storeId
        },
        success: function(response) {
            hideLoadingSpinner();
            const tempDiv = $('<div>').html(response);
            const scriptContent = tempDiv.find('script').text();
            if (scriptContent) {
                try { eval(scriptContent); } catch(e) {}
            }
            const allRows = tempDiv.find('.row');
            if (allRows.length >= 1) { $('#summaryContainer').html(allRows.eq(0)).hide().fadeIn(500); }
            if (allRows.length >= 2) { $('#chartsContainer').html(allRows.eq(1)).hide().fadeIn(700); }
            if (allRows.length >= 3) { $('#chartsContainer').append(allRows.eq(2)); }
            if (allRows.length >= 4) { $('#customersContainer').html(allRows.eq(3)).hide().fadeIn(900); }
            if (allRows.length >= 5) { $('#monthlyContainer').html(allRows.eq(4)).hide().fadeIn(1100); }
            setTimeout(function() {
                initializeSalesCharts();
                initializeDataTables();
            }, 1500);
            $(document).Toasts('create', { class: 'bg-success', title: 'Success!', body: '<i class="fas fa-check-circle mr-2"></i>Report updated successfully.', autohide: true, delay: 4000 });
        },
        error: function(xhr, status, error) {
            hideLoadingSpinner();
            $('#summaryContainer').html('<div class="alert alert-danger">Failed to load report. Please try again.</div>');
        }
    });
}

function clearFilters() {
    $('#clearBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Clearing...');
    $('#start_date').val('<?= date('Y-m-01') ?>');
    $('#end_date').val('<?= date('Y-m-d') ?>');
    $('#filter_store').val('').trigger('change');
    $(document).Toasts('create', { class: 'bg-info', title: 'Info', subtitle: 'Filters Cleared', body: '<i class="fas fa-broom mr-2"></i>Filters reset. Reloading report...', autohide: true, delay: 3000 });
    setTimeout(function() {
        $('#clearBtn').prop('disabled', false).html('<i class="fas fa-times mr-2"></i>Clear');
        filterSalesReport();
    }, 500);
}

function exportReport() {
    const startDate = $('#start_date').val();
    const endDate   = $('#end_date').val();
    const storeId   = $('#filter_store').val();
    if (!startDate || !endDate) {
        $(document).Toasts('create', { class: 'bg-warning', title: 'Warning', body: '<i class="fas fa-exclamation-triangle mr-2"></i>Please select dates before exporting!', autohide: true, delay: 4000 });
        return;
    }
    $('#exportBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...');
    $(document).Toasts('create', { class: 'bg-info', title: 'Export Started', subtitle: 'Please Wait', body: '<i class="fas fa-download mr-2"></i>Preparing export...', autohide: true, delay: 3000 });
    window.open(`./requests/voucher_requests.php?export_sales_report=1&start_date=${startDate}&end_date=${endDate}&store_id=${storeId}`, '_blank');
    setTimeout(function() {
        $('#exportBtn').prop('disabled', false).html('<i class="fas fa-download mr-2"></i>Export');
        $(document).Toasts('create', { class: 'bg-success', title: 'Export Ready', subtitle: 'Download Started', body: '<i class="fas fa-check-circle mr-2"></i>Export initiated.', autohide: true, delay: 4000 });
    }, 2000);
}

$(document).ready(function() {
    initializeSalesCharts();
    initializeDataTables();
    $('#filterBtn').on('click', function(e) { e.preventDefault(); filterSalesReport(); });
    $('#clearBtn').on('click',  function(e) { e.preventDefault(); clearFilters(); });
    $('#exportBtn').on('click', function(e) { e.preventDefault(); exportReport(); });
    $('#start_date, #end_date').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); filterSalesReport(); } });
});
</script>
<?php closeConn($conn); ?>
<style>
.btn-loading { position: relative; overflow: hidden; }
.btn-loading::after { content: ""; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); animation: loading-shimmer 1.5s infinite; }
@keyframes loading-shimmer { 0% { left: -100%; } 100% { left: 100%; } }
.overlay-wrapper { position: relative; }
.overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.overlay.dark { background-color: rgba(0,0,0,0.7); backdrop-filter: blur(2px); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.btn:focus, .btn:active { transform: scale(0.98); transition: transform 0.1s; }
input[type="date"]:focus { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25); transform: scale(1.02); transition: all 0.2s ease; }
</style>