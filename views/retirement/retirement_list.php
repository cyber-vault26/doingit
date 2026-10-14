<?php
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';
$retirements = selectRetirementsByStatus($conn, $status);

$statusLabels = [
    'pending'  => 'Pending',
    'approved' => 'Approved',
    'rejected' => 'Rejected'
];

$statusColors = [
    'pending'  => 'card-warning',
    'approved' => 'card-success',
    'rejected' => 'card-danger'
];

$cardColor = $statusColors[$status] ?? 'card-warning';
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
?>

<div class="card <?= $cardColor ?>">
    <div class="card-header">
        <h4><?= $statusLabel ?> Retirement List</h4>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered table-search">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Retirement Date</th>
                    <th>Ref No</th>
                    <th>Advance Ref No</th>
                    <th>Requester</th>
                    <th>Project</th>
                    <th>Currency</th>
                    <th>Total Spent</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if(!empty($retirements) && is_array($retirements)){
                $counter = 1;
                foreach($retirements as $r){
                    $displayAmount = $r['currency_name'] !== 'TSH'
                        ? number_format($r['total_spent'] / $r['currency_rate'], 2)
                        : number_format($r['total_spent'], 2);
            ?>
            <tr>
                <td><?= $counter++ ?></td>
                <td><?= date('d-M-Y', strtotime($r['date_'])) ?></td>
                <td><?= htmlspecialchars($r['ref_no']) ?></td>
                <td><?= htmlspecialchars($r['advance_ref_no']) ?></td>
                <td><?= htmlspecialchars($r['requester_name']) ?></td>
                <td><?= !empty($r['project_name']) ? htmlspecialchars($r['project_name']) : '-' ?></td>
                <td><?= htmlspecialchars($r['currency_name']) ?></td>
                <td><?= $displayAmount ?></td>
                <td><?= ucfirst($r['status']) ?></td>
                <td>
                    <a href="./?page=preview_retirement&id=<?= $r['id'] ?>" class="btn btn-outline-info btn-sm mb-1"><i class="fas fa-eye"></i> Preview</a>
                    <?php if($status === 'pending'): ?>
                    <form method="POST" action="./controllers/retirement_controller.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this retirement?')">
                        <input type="hidden" name="retirement_id" value="<?= $r['id'] ?>">
                        <button type="submit" name="delete_retirement" class="btn btn-danger btn-sm mb-1">Delete</button>
                    </form>
                    <?php endif; ?>
                    <?php if($status === 'rejected'): ?>
                    <a href="./?page=edit_retirement&id=<?= $r['id'] ?>" class="btn btn-warning btn-sm mb-1"><i class="fas fa-edit"></i> Amend</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='10' class='text-center'>No {$statusLabel} retirements found</td></tr>";
            }
            ?>
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Retirement Date</th>
                    <th>Ref No</th>
                    <th>Advance Ref No</th>
                    <th>Requester</th>
                    <th>Project</th>
                    <th>Currency</th>
                    <th>Total Spent</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer">-- END --</div>
</div>