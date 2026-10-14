<?php
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';
$advanceCashRequests = selectAdvanceCashRequestsByStatus($conn, $status);

$statusLabels = [
    'pending'  => 'Pending',
    'approved' => 'Approved',
    'paid'     => 'Paid',
    'rejected' => 'Rejected'
];

$statusColors = [
    'pending'  => 'card-warning',
    'approved' => 'card-success',
    'paid'     => 'card-info',
    'rejected' => 'card-danger'
];

$cardColor = $statusColors[$status] ?? 'card-info';
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
?>

<div class="card <?= $cardColor ?>">
    <div class="card-header">
        <h4><?= $statusLabel ?> Advance Cash Request List</h4>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered table-search">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Request Date</th>
                    <th>Ref No</th>
                    <th>Requester</th>
                    <th>Project</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if(!empty($advanceCashRequests) && is_array($advanceCashRequests)){
                $counter = 1;
                foreach($advanceCashRequests as $p){
                    $displayAmount = $p['currency_name'] !== 'TSH'
                        ? number_format($p['total_amount'] / $p['currency_rate'], 2)
                        : number_format($p['total_amount'], 2);
            ?>
            <tr>
                <td><?= $counter++ ?></td>
                <td><?= date('d-M-Y', strtotime($p['date_'])) ?></td>
                <td><?= htmlspecialchars($p['ref_no']) ?></td>
                <td><?= htmlspecialchars($p['requester_name']) ?></td>
                <td><?= !empty($p['project_name']) ? htmlspecialchars($p['project_name']) : '-' ?></td>
                <td><?= htmlspecialchars($p['currency_name']) ?></td>
                <td><?= $displayAmount ?></td>
                <td><?= ucfirst($p['type']) ?></td>
                <td><?= ucfirst($p['status']) ?></td>
                <td>
                    <a href="./?page=preview_advance_cash_request&id=<?= $p['id'] ?>" class="btn btn-outline-info btn-sm mb-1"><i class="fas fa-eye"></i> Preview</a>
                    <?php if($status === 'pending'): ?>
                    <form method="POST" action="./controllers/procurement_controller.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this advance cash request?')">
                        <input type="hidden" name="advance_cash_request_id" value="<?= $p['id'] ?>">
                        <button type="submit" name="delete_advance_cash_request" class="btn btn-danger btn-sm mb-1">Delete</button>
                    </form>
                    <a href="./?page=edit_advance_cash_request&id=<?= $p['id'] ?>" class="btn btn-warning btn-sm mb-1"><i class="fas fa-edit"></i> Amend</a>
                    <?php endif; ?>
                    <?php if($status === 'rejected'): ?>
                    <a href="./?page=edit_advance_cash_request&id=<?= $p['id'] ?>" class="btn btn-warning btn-sm mb-1"><i class="fas fa-edit"></i> Amend</a>
                    <?php endif; ?>
                    <?php if($status === 'paid'): ?>
                    <a href="./?page=create_retirement&id=<?= $p['id'] ?>" class="btn btn-success btn-sm mb-1"><i class="fas fa-undo mr-1"></i> Retire</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='10' class='text-center'>No {$statusLabel} advance cash requests found</td></tr>";
            }
            ?>
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Request Date</th>
                    <th>Ref No</th>
                    <th>Requester</th>
                    <th>Project</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer">-- END --</div>
</div>