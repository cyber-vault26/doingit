<?php
require_once '../configs.php';
require_once '../functions/vehicle_functions.php';

$conn = openConn();

if(isset($_POST['filter_report'])) {
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;
    
    // Get updated data
    $summary = getVehicleReportSummary($conn, $startDate, $endDate);
    $vehicleData = getVehicleExpensesAndSales($conn, $startDate, $endDate);
    
    // Return summary statistics
    if($summary) {
        echo '<div class="row">';
        echo '<div class="col-lg-3 col-6">';
        echo '<div class="small-box bg-info">';
        echo '<div class="inner">';
        echo '<h3>' . number_format($summary['total_vehicles']) . '</h3>';
        echo '<p>Total Vehicles</p>';
        echo '</div>';
        echo '<div class="icon"><i class="fas fa-truck"></i></div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-lg-3 col-6">';
        echo '<div class="small-box bg-warning">';
        echo '<div class="inner">';
        echo '<h3>' . number_format($summary['total_trailers']) . '</h3>';
        echo '<p>Total Trailers</p>';
        echo '</div>';
        echo '<div class="icon"><i class="fas fa-trailer"></i></div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-lg-3 col-6">';
        echo '<div class="small-box bg-danger">';
        echo '<div class="inner">';
        echo '<h3>' . number_format($summary['total_expenses'], 0) . '</h3>';
        echo '<p>Total Expenses</p>';
        echo '</div>';
        echo '<div class="icon"><i class="fas fa-money-bill-alt"></i></div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-lg-3 col-6">';
        echo '<div class="small-box bg-success">';
        echo '<div class="inner">';
        echo '<h3>' . number_format($summary['total_sales'], 0) . '</h3>';
        echo '<p>Total Sales</p>';
        echo '</div>';
        echo '<div class="icon"><i class="fas fa-chart-line"></i></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    // Return vehicle details table
    echo '<div class="row">';
    echo '<div class="col-12">';
    echo '<div class="card">';
    echo '<div class="card-header">';
    echo '<h3 class="card-title"><i class="fas fa-list"></i> Vehicle Performance Details</h3>';
    echo '<div class="card-tools">';
    echo '<button type="button" class="btn btn-tool" data-card-widget="collapse">';
    echo '<i class="fas fa-minus"></i>';
    echo '</button>';
    echo '<button type="button" class="btn btn-tool" data-card-widget="maximize">';
    echo '<i class="fas fa-expand"></i>';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped table-hover" id="vehicleReportTable">';
    echo '<thead class="thead-dark">';
    echo '<tr>';
    echo '<th style="width: 5%">#</th>';
    echo '<th>Plate No</th>';
    echo '<th>Manufacturer</th>';
    echo '<th>Model</th>';
    echo '<th>Type</th>';
    echo '<th>Subtype</th>';
    echo '<th class="text-right">Expenses</th>';
    echo '<th class="text-right">Sales</th>';
    echo '<th class="text-right">Net Profit</th>';
    echo '<th class="text-center" style="width: 12%">Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if($vehicleData && is_array($vehicleData)) {
        $counter = 1;
        foreach($vehicleData as $vehicle) {
            echo '<tr>';
            echo '<td>' . $counter . '</td>';
            echo '<td>';
            echo '<span class="badge badge-secondary">' . htmlspecialchars($vehicle['plate_no']) . '</span>';
            echo '</td>';
            echo '<td>' . htmlspecialchars($vehicle['manufacturer']) . '</td>';
            echo '<td>' . $vehicle['model'] . '</td>';
            echo '<td>';
            echo '<span class="badge badge-info">' . htmlspecialchars($vehicle['vehicle_type']) . '</span>';
            echo '</td>';
            echo '<td>' . htmlspecialchars($vehicle['subtype']) . '</td>';
            echo '<td class="text-right">';
            echo '<span class="text-danger"><strong>' . number_format($vehicle['total_expenses'], 2) . '</strong></span>';
            echo '</td>';
            echo '<td class="text-right">';
            echo '<span class="text-success"><strong>' . number_format($vehicle['total_sales'], 2) . '</strong></span>';
            echo '</td>';
            echo '<td class="text-right">';
            $badgeClass = $vehicle['net_profit'] >= 0 ? 'badge-success' : 'badge-danger';
            echo '<span class="badge ' . $badgeClass . '">' . number_format($vehicle['net_profit'], 2) . '</span>';
            echo '</td>';
            echo '<td class="text-center">';
            echo '<button class="btn btn-sm btn-outline-info" onclick="viewVehicleDetails(' . $vehicle['id'] . ')" title="View Details">';
            echo '<i class="fas fa-eye"></i>';
            echo '</button>';
            echo '</td>';
            echo '</tr>';
            $counter++;
        }
    } else {
        echo '<tr>';
        echo '<td colspan="10" class="text-center text-muted">';
        echo '<i class="fas fa-exclamation-circle"></i> No vehicle data found';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    closeConn($conn);
} else {
    echo '<div class="alert alert-danger">';
    echo '<i class="fas fa-exclamation-triangle"></i> Invalid request';
    echo '</div>';
}
?>