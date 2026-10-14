<?php
// Get date filters from URL parameters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Get report data using the functions we created
$summary = getSalesPurchaseReportSummary($conn, $startDate, $endDate);
$topSellingItems = getTopSellingItems($conn, $startDate, $endDate, 10);
$mostPurchasedItems = getMostPurchasedItems($conn, $startDate, $endDate, 10);
$topCustomers = getTopCustomers($conn, $startDate, $endDate, 10);
$topSuppliers = getTopSuppliers($conn, $startDate, $endDate, 10);
$salesPurchaseTrends = getSalesPurchaseTrends($conn, $startDate, $endDate);
$profitAnalysis = getProfitAnalysis($conn, $startDate, $endDate);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Sales & Purchase Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Vouchers</a></li>
                    <li class="breadcrumb-item active">Sales & Purchase Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Date Filter Row -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i> Date Filter
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date">
                                            <i class="fas fa-calendar-alt"></i> Start Date
                                        </label>
                                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $startDate ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date">
                                            <i class="fas fa-calendar-alt"></i> End Date
                                        </label>
                                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $endDate ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="d-block">
                                            <button type="button" id="filterBtn" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Filter Report
                                            </button>
                                            <button type="button" id="clearBtn" class="btn btn-default ml-2">
                                                <i class="fas fa-times"></i> Clear
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

        <!-- Summary Statistics Row -->
        <div id="summaryContainer">
        <?php if($summary): ?>
        <div class="row">
            <!-- Total Sales -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($summary['total_sales_amount'], 0) ?></h3>
                        <p>Total Sales Amount</p>
                        <small><?= number_format($summary['total_sales_invoices']) ?> Invoices</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            
            <!-- Total Purchases -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($summary['total_purchase_amount'], 0) ?></h3>
                        <p>Total Purchase Amount</p>
                        <small><?= number_format($summary['total_purchase_invoices']) ?> Orders</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            
            <!-- Net Profit/Loss -->
            <div class="col-lg-3 col-6">
                <div class="small-box <?= $summary['net_profit_loss'] >= 0 ? 'bg-info' : 'bg-warning' ?>">
                    <div class="inner">
                        <h3><?= number_format($summary['net_profit_loss'], 0) ?></h3>
                        <p><?= $summary['net_profit_loss'] >= 0 ? 'Net Profit' : 'Net Loss' ?></p>
                        <small>
                            <?php 
                            $profitMargin = $summary['total_sales_amount'] > 0 ? 
                                round(($summary['net_profit_loss'] / $summary['total_sales_amount']) * 100, 2) : 0;
                            echo $profitMargin . '% Margin';
                            ?>
                        </small>
                    </div>
                    <div class="icon">
                        <i class="fas <?= $summary['net_profit_loss'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                    </div>
                </div>
            </div>
            
            <!-- Active Partners -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?= number_format($summary['total_customers'] + $summary['total_suppliers']) ?></h3>
                        <p>Active Partners</p>
                        <small><?= number_format($summary['total_customers']) ?> Customers | <?= number_format($summary['total_suppliers']) ?> Suppliers</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        </div>

        <!-- Charts Analytics Section -->
        <div id="chartsContainer">
        <div class="row">
            <!-- Sales vs Purchase Trends -->
            <div class="col-md-8">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i> Sales vs Purchase Trends
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="trendsChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Key Performance Indicators -->
            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tachometer-alt"></i> Key Performance Indicators
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if($summary): ?>
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-percentage"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Profit Margin</span>
                                <span class="info-box-number">
                                    <?php 
                                    $margin = $summary['total_sales_amount'] > 0 ? 
                                        round(($summary['net_profit_loss'] / $summary['total_sales_amount']) * 100, 2) : 0;
                                    echo $margin . '%';
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-receipt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Avg Invoice Value</span>
                                <span class="info-box-number">
                                    <?= $summary['total_sales_invoices'] > 0 ? 
                                        number_format($summary['total_sales_amount'] / $summary['total_sales_invoices'], 0) : 0 ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-shopping-bag"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Avg Purchase Order</span>
                                <span class="info-box-number">
                                    <?= $summary['total_purchase_invoices'] > 0 ? 
                                        number_format($summary['total_purchase_amount'] / $summary['total_purchase_invoices'], 0) : 0 ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="callout <?= $summary['net_profit_loss'] >= 0 ? 'callout-success' : 'callout-warning' ?>">
                            <h5><i class="fas <?= $summary['net_profit_loss'] >= 0 ? 'fa-thumbs-up' : 'fa-exclamation-triangle' ?>"></i> 
                                <?= $summary['net_profit_loss'] >= 0 ? 'Profitable Period' : 'Loss Period' ?>
                            </h5>
                            <p>
                                <?php if($summary['net_profit_loss'] >= 0): ?>
                                    Business is generating positive returns with a <?= $margin ?>% profit margin.
                                <?php else: ?>
                                    Business is operating at a loss. Consider reviewing pricing strategies and cost optimization.
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Items Analysis -->
        <div class="row">
            <!-- Top Selling Items -->
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-fire"></i> Top Selling Items
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="topSellingChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Most Purchased Items -->
            <div class="col-md-6">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-shopping-cart"></i> Most Purchased Items
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="mostPurchasedChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Business Partners Analysis -->
        <div id="partnersContainer">
        <div class="row">
            <!-- Top Customers -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-tie"></i> Top Customers
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Customer Name</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-center">Invoices</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($topCustomers && is_array($topCustomers)): 
                                    $counter = 1;
                                    foreach($topCustomers as $customer):
                                ?>
                                <tr>
                                    <td><?= $counter ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($customer['customer_name']) ?></strong>
                                        <?php if($customer['customer_address']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($customer['customer_address']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-success">
                                            <?= number_format($customer['total_amount'], 0) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            <?= number_format($customer['total_invoices']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    $counter++;
                                    endforeach; 
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fas fa-exclamation-circle"></i> No customer data found
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Top Suppliers -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-truck"></i> Top Suppliers
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Supplier Name</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-center">Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($topSuppliers && is_array($topSuppliers)): 
                                    $counter = 1;
                                    foreach($topSuppliers as $supplier):
                                ?>
                                <tr>
                                    <td><?= $counter ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($supplier['supplier_name']) ?></strong>
                                        <?php if($supplier['supplier_address']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($supplier['supplier_address']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-danger">
                                            <?= number_format($supplier['total_amount'], 0) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning">
                                            <?= number_format($supplier['total_orders']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    $counter++;
                                    endforeach; 
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fas fa-exclamation-circle"></i> No supplier data found
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

        <!-- Profit Analysis Table -->
        <div id="profitContainer">
        <?php if($profitAnalysis && is_array($profitAnalysis)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i> Profit Analysis by Item
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="profitAnalysisTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th>Item Name</th>
                                        <th class="text-right">Sales Amount</th>
                                        <th class="text-right">Purchase Cost</th>
                                        <th class="text-right">Profit/Loss</th>
                                        <th class="text-right">Margin %</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    foreach($profitAnalysis as $item):
                                        $isProfitable = $item['profit'] > 0;
                                    ?>
                                    <tr>
                                        <td><?= $counter ?></td>
                                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                                        <td class="text-right">
                                            <span class="text-success">
                                                <?= number_format($item['total_sales_amount'], 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-danger">
                                                <?= number_format($item['total_purchase_cost'], 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="<?= $isProfitable ? 'text-success' : 'text-danger' ?>">
                                                <strong><?= number_format($item['profit'], 2) ?></strong>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="<?= $isProfitable ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($item['profit_margin_percentage'], 2) ?>%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $isProfitable ? 'badge-success' : 'badge-danger' ?>">
                                                <?= $isProfitable ? 'Profitable' : 'Loss' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                    $counter++;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        </div>
    </div>
</section>

<!-- Initialize chart data -->
<?php 
// Prepare data for JavaScript charts
$chartData = [
    'trends' => $salesPurchaseTrends,
    'topSelling' => $topSellingItems,
    'mostPurchased' => $mostPurchasedItems
];
?>
<script>
window.chartData = <?= json_encode($chartData) ?>;
</script>

<script>
// Global variables
let currentStartDate = '<?= $startDate ?? '' ?>';
let currentEndDate = '<?= $endDate ?? '' ?>';
let trendsChart = null;
let topSellingChart = null;
let mostPurchasedChart = null;

// Initialize all charts
function initializeCharts() {
    initializeTrendsChart();
    initializeTopSellingChart();
    initializeMostPurchasedChart();
}

// Initialize Sales vs Purchase Trends Chart
function initializeTrendsChart() {
    const ctx = document.getElementById('trendsChart');
    if (!ctx) return;
    
    if (trendsChart) {
        trendsChart.destroy();
    }
    
    const trendsData = window.chartData.trends || [];
    
    // Process data for chart
    const months = [...new Set(trendsData.map(item => item.month_year))].sort();
    const salesData = [];
    const purchaseData = [];
    
    months.forEach(month => {
        const salesItem = trendsData.find(item => item.month_year === month && item.type === 'Sales');
        const purchaseItem = trendsData.find(item => item.month_year === month && item.type === 'Purchase');
        
        salesData.push(salesItem ? parseFloat(salesItem.amount) : 0);
        purchaseData.push(purchaseItem ? parseFloat(purchaseItem.amount) : 0);
    });
    
    trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Sales',
                data: salesData,
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Purchases',
                data: purchaseData,
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Monthly Sales vs Purchase Trends'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Initialize Top Selling Items Chart
function initializeTopSellingChart() {
    const ctx = document.getElementById('topSellingChart');
    if (!ctx) return;
    
    if (topSellingChart) {
        topSellingChart.destroy();
    }
    
    const topSellingData = window.chartData.topSelling || [];
    
    topSellingChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: topSellingData.map(item => item.item_name),
            datasets: [{
                data: topSellingData.map(item => parseFloat(item.total_amount)),
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(102, 16, 242, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Top Selling Items Distribution'
                }
            }
        }
    });
}

// Initialize Most Purchased Items Chart
function initializeMostPurchasedChart() {
    const ctx = document.getElementById('mostPurchasedChart');
    if (!ctx) return;
    
    if (mostPurchasedChart) {
        mostPurchasedChart.destroy();
    }
    
    const mostPurchasedData = window.chartData.mostPurchased || [];
    
    mostPurchasedChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: mostPurchasedData.map(item => item.item_name),
            datasets: [{
                label: 'Purchase Amount',
                data: mostPurchasedData.map(item => parseFloat(item.total_amount)),
                backgroundColor: 'rgba(220, 53, 69, 0.6)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Most Purchased Items'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });
}

// Filter functionality with AJAX and loading spinner
function showLoadingSpinner() {
    const spinnerHtml = `
        <div class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 text-muted">
                    <i class="fas fa-sync-alt fa-spin mr-2"></i>
                    Updating report data...
                </p>
            </div>
        </div>
    `;
    
    $('#summaryContainer').html(spinnerHtml);
    $('#chartsContainer').html(spinnerHtml);
    $('#partnersContainer').html(spinnerHtml);
    $('#profitContainer').html(spinnerHtml);
}

function filterReport() {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    
    // Update global variables
    currentStartDate = startDate;
    currentEndDate = endDate;
    
    // Show loading spinner
    showLoadingSpinner();
    
    // Disable filter button
    $('#filterBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    
    $.ajax({
        url: './requests/voucher_requests.php',
        method: 'POST',
        data: {
            filter_sales_purchase_report: true,
            start_date: startDate,
            end_date: endDate
        },
        success: function(response) {
            // Parse the response and update containers
            const tempDiv = $('<div>').html(response);
            
            // Extract different sections from the response
            const allRows = tempDiv.find('.row');
            
            // Log for debugging
            console.log('Total rows found:', allRows.length);
            
            // Update containers based on what we find
            if (allRows.length >= 1) {
                $('#summaryContainer').html(allRows.eq(0)); // Summary row
            }
            if (allRows.length >= 2) {
                $('#chartsContainer').html(allRows.eq(1));  // Charts row  
            }
            if (allRows.length >= 3) {
                $('#partnersContainer').html(allRows.eq(2)); // Partners row
            }
            if (allRows.length >= 4) {
                $('#profitContainer').html(allRows.eq(3));   // Profit analysis row
            }
            
            // If we have fewer rows, try to find specific containers
            if (allRows.length < 4) {
                // Try to find profit analysis section specifically
                const profitSection = tempDiv.find('#profitAnalysisTable').closest('.row');
                if (profitSection.length > 0) {
                    $('#profitContainer').html(profitSection);
                }
                
                // Try to find charts section
                const chartsSection = tempDiv.find('canvas').closest('.row');
                if (chartsSection.length > 0) {
                    $('#chartsContainer').html(chartsSection);
                }
            }
            
            // Show success toast
            $(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                subtitle: '',
                body: '<i class="fas fa-check mr-2"></i>Report updated successfully!',
                autohide: true,
                delay: 3000
            });
        },
        error: function(xhr, status, error) {
            // Show error message
            const errorHtml = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error!</strong> Failed to update report. Please try again.
                    <br><small>Error: ${error}</small>
                </div>
            `;
            $('#summaryContainer').html(errorHtml);
            $('#chartsContainer').html(errorHtml);
            $('#partnersContainer').html(errorHtml);
            $('#profitContainer').html(errorHtml);
            
            // Show error toast
            $(document).Toasts('create', {
                class: 'bg-danger',
                title: 'Error',
                subtitle: '',
                body: '<i class="fas fa-exclamation-triangle mr-2"></i>Failed to update report!',
                autohide: true,
                delay: 5000
            });
        },
        complete: function() {
            // Re-enable filter button
            $('#filterBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Filter Report');
        }
    });
}

function clearFilters() {
    $('#start_date').val('');
    $('#end_date').val('');
    currentStartDate = '';
    currentEndDate = '';
    filterReport();
}

// Initialize DataTable for profit analysis
function initializeDataTable() {
    if ($.fn.DataTable && $('#profitAnalysisTable').length) {
        $('#profitAnalysisTable').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "ordering": true,
            "info": true,
            "paging": true,
            "searching": true,
            "pageLength": 25,
            "order": [[4, "desc"]], // Sort by profit column
            "language": {
                "emptyTable": "No profit analysis data available",
                "info": "Showing _START_ to _END_ of _TOTAL_ items",
                "search": "Search items:"
            }
        });
    }
}

$(document).ready(function() {
    // Initialize charts and DataTable
    initializeCharts();
    initializeDataTable();
    
    // Event handlers
    $('#filterBtn').on('click', function(e) {
        e.preventDefault();
        filterReport();
    });
    
    $('#clearBtn').on('click', function(e) {
        e.preventDefault();
        clearFilters();
    });
    
    // Enter key support for date inputs
    $('#start_date, #end_date').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            filterReport();
        }
    });
});
</script>

<?php closeConn($conn); ?>