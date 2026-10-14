<?php
$statuses = ['pending', 'checked', 'reviewed', 'approved', 'rejected'];
$activeStatus = isset($_GET['status']) && in_array($_GET['status'], $statuses) ? $_GET['status'] : 'pending';
$purchaseRequests = selectPurchaseRequestsByStatus($conn, $activeStatus);
$statusColors = [
    'pending'  => 'warning',
    'checked'  => 'info',
    'reviewed' => 'primary',
    'approved' => 'success',
    'rejected' => 'danger'
];
$statusIcons = [
    'pending'  => 'fa-clock',
    'checked'  => 'fa-check-circle',
    'reviewed' => 'fa-search',
    'approved' => 'fa-thumbs-up',
    'rejected' => 'fa-times-circle'
];
?>
<div class="card card-outline card-<?= $statusColors[$activeStatus] ?>">
    <div class="card-header">
        <h4 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Purchase Request List</h4>
        <div class="card-tools">
            <?php foreach($statuses as $s): ?>
                <a href="./?page=purchase_request_list&status=<?= $s ?>" class="btn btn-sm <?= $activeStatus === $s ? 'btn-'.$statusColors[$s] : 'btn-outline-'.$statusColors[$s] ?>">
                    <i class="fas <?= $statusIcons[$s] ?> mr-1"></i><?= ucfirst($s) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm table-bordered table-hover table-search mb-0">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Ref No</th>
                    <th>Date</th>
                    <th>Heading</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(!empty($purchaseRequests) && is_array($purchaseRequests)):
                $counter = 1;
                foreach($purchaseRequests as $p):
                    $displayAmount = $p['currency_name'] !== 'TSH'
                        ? number_format($p['total_amount'] / $p['currency_rate'], 2)
                        : number_format($p['total_amount'], 2);
            ?>
            <tr>
                <td><?= $counter++ ?></td>
                <td><strong><?= htmlspecialchars($p['ref_no']) ?></strong></td>
                <td><?= date('d-M-Y', strtotime($p['date_'])) ?></td>
                <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($p['request_heading'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['currency_name']) ?></td>
                <td><?= $displayAmount ?></td>
                <td><span class="badge badge-<?= $statusColors[$p['status']] ?>"><?= ucfirst($p['status']) ?></span></td>
                <td>
                    <a href="./?page=preview_purchase_request&id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye mr-1"></i>View</a>
                    <form method="POST" action="./controllers/purchase_request_controller.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this purchase request?')">
                        <input type="hidden" name="purchase_request_id" value="<?= $p['id'] ?>">
                        <button type="submit" name="delete_purchase_request" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach;
            else: ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="fas <?= $statusIcons[$activeStatus] ?> fa-2x mb-2 d-block"></i>
                    No <?= $activeStatus ?> purchase requests found
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
            <tfoot class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Ref No</th>
                    <th>Date</th>
                    <th>Heading</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer text-muted text-right">
        Total: <strong><?= is_array($purchaseRequests) ? count($purchaseRequests) : 0 ?></strong> records
    </div>
</div>