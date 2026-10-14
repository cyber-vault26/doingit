<?php
$totalProjectsQuery = "SELECT COUNT(id) as jumla FROM `projects` WHERE deleted_at is null";
$totalProjectsResult = mysqli_query($conn, $totalProjectsQuery);
$totalProjects = 0;
if($totalProjectsResult && mysqli_num_rows($totalProjectsResult) > 0) {
    $row = mysqli_fetch_assoc($totalProjectsResult);
    $totalProjects = $row['jumla'];
}
$totalBudget = getTotalProjectBudget($conn);
$totalCosts = getTotalProjectCosts($conn);
$remainingBudget = $totalBudget - $totalCosts;
$budgetUsedPercentage = $totalBudget > 0 ? (($totalCosts / $totalBudget) * 100) : 0;
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Project Budget Analysis Dashboard</h1>
        <div class="btn-group" role="group">
            <a href="?page=consignment_cost_report" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-chart-line"></i> View Cost Report
            </a>
            <a href="?page=consignment_income_report" class="btn btn-outline-success btn-sm">
                <i class="fas fa-chart-bar"></i> View Budget Report
            </a>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Projects
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($totalProjects); ?>
                            </div>
                            <div class="text-sm mt-1">
                                <a href="?page=all_projects" class="text-warning">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Budget
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                TZS <?php echo number_format($totalBudget ?: 0, 2); ?>
                            </div>
                            <div class="text-sm mt-1">
                                <a href="?page=consignment_income_report" class="text-success">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Costs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                TZS <?php echo number_format($totalCosts ?: 0, 2); ?>
                            </div>
                            <div class="text-sm mt-1">
                                <a href="?page=consignment_cost_report" class="text-warning">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?php echo $remainingBudget >= 0 ? 'info' : 'danger'; ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-<?php echo $remainingBudget >= 0 ? 'info' : 'danger'; ?> text-uppercase mb-1">
                                <?php echo $remainingBudget >= 0 ? 'Remaining Budget' : 'Budget Exceeded'; ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                TZS <?php echo number_format($remainingBudget ?: 0, 2); ?>
                            </div>
                            <div class="text-sm mt-1 text-<?php echo $remainingBudget >= 0 ? 'success' : 'danger'; ?>">
                                <?php echo number_format($budgetUsedPercentage, 1); ?>% utilized
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Budget Overview</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                             aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Export Options:</div>
                            <a class="dropdown-item" href="#">Download as PDF</a>
                            <a class="dropdown-item" href="#">Download as Excel</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary">Budget vs Costs Analysis</h5>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total Budget:</span>
                                    <strong class="text-success">TZS <?php echo number_format($totalBudget, 2); ?></strong>
                                </div>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo $totalBudget > 0 ? 100 : 0; ?>%" 
                                         aria-valuenow="<?php echo $totalBudget; ?>" aria-valuemin="0" 
                                         aria-valuemax="<?php echo max($totalBudget, $totalCosts); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total Costs:</span>
                                    <strong class="text-warning">TZS <?php echo number_format($totalCosts, 2); ?></strong>
                                </div>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: <?php echo $totalBudget > 0 ? (($totalCosts / $totalBudget) * 100) : 0; ?>%" 
                                         aria-valuenow="<?php echo $totalCosts; ?>" aria-valuemin="0" 
                                         aria-valuemax="<?php echo $totalBudget; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Remaining:</span>
                                    <strong class="text-<?php echo $remainingBudget >= 0 ? 'info' : 'danger'; ?>">
                                        TZS <?php echo number_format($remainingBudget, 2); ?>
                                    </strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small><strong>Budget Utilization:</strong></small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar <?= $budgetUsedPercentage > 100 ? 'bg-danger' : ($budgetUsedPercentage > 80 ? 'bg-warning' : 'bg-success') ?>" 
                                         role="progressbar" 
                                         style="width: <?= min($budgetUsedPercentage, 100) ?>%"
                                         aria-valuenow="<?= $budgetUsedPercentage ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?= number_format($budgetUsedPercentage, 1) ?>%
                                    </div>
                                </div>
                            </div>
                            <?php if ($remainingBudget < 0): ?>
                                <div class="alert alert-danger py-2 px-3">
                                    <strong><i class="fas fa-exclamation-triangle"></i> Budget Exceeded!</strong><br>
                                    <small>Projects have exceeded allocated budget by TZS <?= number_format(abs($remainingBudget), 2) ?></small>
                                </div>
                            <?php elseif ($budgetUsedPercentage > 80): ?>
                                <div class="alert alert-warning py-2 px-3">
                                    <strong><i class="fas fa-exclamation-circle"></i> Budget Warning!</strong><br>
                                    <small>Over 80% of budget has been utilized</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary">Key Metrics</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Average Budget per Project:</td>
                                        <td class="text-right font-weight-bold">
                                            TZS <?php echo $totalProjects > 0 ? number_format($totalBudget / $totalProjects, 2) : '0.00'; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Average Cost per Project:</td>
                                        <td class="text-right font-weight-bold">
                                            TZS <?php echo $totalProjects > 0 ? number_format($totalCosts / $totalProjects, 2) : '0.00'; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Average Remaining per Project:</td>
                                        <td class="text-right font-weight-bold text-<?php echo $remainingBudget >= 0 ? 'success' : 'danger'; ?>">
                                            TZS <?php echo $totalProjects > 0 ? number_format($remainingBudget / $totalProjects, 2) : '0.00'; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Budget Utilization Rate:</td>
                                        <td class="text-right font-weight-bold text-<?php echo $budgetUsedPercentage > 100 ? 'danger' : ($budgetUsedPercentage > 80 ? 'warning' : 'success'); ?>">
                                            <?php echo number_format($budgetUsedPercentage, 2); ?>%
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="?page=consignment_cost_report" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-danger">
                                    <i class="fas fa-chart-line"></i> View Cost Report
                                </h6>
                                <small class="text-muted">Detailed breakdown</small>
                            </div>
                            <p class="mb-1">View detailed costs for each project</p>
                        </a>
                        <a href="?page=consignment_income_report" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-success">
                                    <i class="fas fa-chart-bar"></i> View Budget Report
                                </h6>
                                <small class="text-muted">Budget analysis</small>
                            </div>
                            <p class="mb-1">View budget allocated to each project</p>
                        </a>
                        <a href="?page=all_projects" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-primary">
                                    <i class="fas fa-project-diagram"></i> Manage Projects
                                </h6>
                                <small class="text-muted">Operations</small>
                            </div>
                            <p class="mb-1">Create, edit, or view projects</p>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Budget Status</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <?php if($remainingBudget > 0 && $budgetUsedPercentage <= 80): ?>
                                <i class="fas fa-thumbs-up fa-3x text-success mb-2"></i>
                                <h5 class="text-success">Healthy Budget</h5>
                                <p class="text-muted">Budget is within safe limits</p>
                            <?php elseif($remainingBudget > 0 && $budgetUsedPercentage > 80): ?>
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-2"></i>
                                <h5 class="text-warning">Budget Warning</h5>
                                <p class="text-muted">Over 80% budget utilized - monitor closely</p>
                            <?php elseif($remainingBudget == 0): ?>
                                <i class="fas fa-balance-scale fa-3x text-info mb-2"></i>
                                <h5 class="text-info">Budget Fully Utilized</h5>
                                <p class="text-muted">All budget has been spent</p>
                            <?php else: ?>
                                <i class="fas fa-exclamation-circle fa-3x text-danger mb-2"></i>
                                <h5 class="text-danger">Budget Exceeded</h5>
                                <p class="text-muted">Review spending and adjust budgets</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>