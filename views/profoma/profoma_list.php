<?php
// Fetch proformas
$profomas = getAllProfoma($conn);

// Initialize counters
$totalProfomas = is_array($profomas) ? count($profomas) : 0;
$totalAmount = 0;
$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;

if ($totalProfomas > 0) {
    foreach ($profomas as $p) {
        $totalAmount += isset($p['total_amount']) ? (float)$p['total_amount'] : 0;

        // Normalize status to lowercase to be safe
        $status = strtolower($p['status'] ?? '');

        if ($status === 'pending') {
            $pendingCount++;
        } elseif ($status === 'approved') {
            $approvedCount++;
        } elseif ($status === 'rejected') {
            $rejectedCount++;
        }
    }
}
?>

<!-- Content Header -->
<section class="content-header">
    <h1>Proforma List</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- Total Proformas -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= $totalProfomas ?></h3>
                    <p>Total Proformas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?= number_format($totalAmount, 2) ?></h3>
                    <p>Total Amount</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= $pendingCount ?></h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?= $approvedCount ?></h3>
                    <p>Approved</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><?= $rejectedCount ?></h3>
                    <p>Rejected</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Proforma Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Proformas</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalProfomas > 0): ?>
                        <?php foreach ($profomas as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['id']) ?></td>
                                <td><?= htmlspecialchars($p['date_']) ?></td>
                                <td><?= htmlspecialchars($p['supplier']) ?></td>
                                <td><?= number_format((float)$p['totalAmount'], 2) ?></td>
                                <td><?= htmlspecialchars($p['status']) ?></td>
                               <td>
                                    <a href="./?page=view_profoma&id=<?= urlencode($p['id']) ?>" class="btn btn-primary btn-sm mb-1">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <a href="./?page=delete_profoma&id=<?= urlencode($p['id']) ?>" 
                                    class="btn btn-danger btn-sm mb-1"
                                    onclick="return confirm('Are you sure you want to delete this proforma?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>

                                    <?php 
                                        $status = strtolower($p['status']);
                                        if ($status !== 'approved') : ?>
                                            <a href="./?page=approve_profoma&id=<?= urlencode($p['id']) ?>" 
                                            class="btn btn-success btn-sm mb-1">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                    <?php endif; ?>

                                    <?php if ($status !== 'rejected') : ?>
                                            <a href="./?page=reject_profoma&id=<?= urlencode($p['id']) ?>" 
                                            class="btn btn-warning btn-sm mb-1">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No proformas found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
