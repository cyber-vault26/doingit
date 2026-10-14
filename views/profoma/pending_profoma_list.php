<?php
$pendingProfomas = selectProfomasByStatus($conn, 'pending');
?>
<div class="card card-info">
    <div class="card-header">
        <h4>Profoma Request List</h4>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered table-search">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Request Date</th>
                    <th>Ref No</th>
                    <th>Customer</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if(!empty($pendingProfomas) && is_array($pendingProfomas)){
                $counter = 1;
                foreach($pendingProfomas as $p){
                    $displayAmount = $p['currency_name'] !== 'TSH'
                        ? number_format($p['total_amount'] / $p['currency_rate'], 2)
                        : number_format($p['total_amount'], 2);
            ?>
            <tr>
                <td><?= $counter++ ?></td>
                <td><?= date('d-M-Y', strtotime($p['date_'])) ?></td>
                <td><?= htmlspecialchars($p['ref_no']) ?></td>
                <td><?= htmlspecialchars($p['customer_name']) ?></td>
                <td><?= htmlspecialchars($p['currency_name']) ?></td>
                <td><?= $displayAmount ?></td>
                <td><?= ucfirst($p['status']) ?></td>
                <td>
                    <a href="./?page=preview_profoma&id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm mb-1"><i class="fas fa-eye"></i> Preview</a>
                    <form method="POST" action="./controllers/profoma_controller.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this profoma?')">
                        <input type="hidden" name="profoma_id" value="<?= $p['id'] ?>">
                        <button type="submit" name="delete_profoma" class="btn btn-danger btn-sm mb-1">Delete</button>
                    </form>
                </td>
            </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>No pending profoma requests found</td></tr>";
            }
            ?>
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Request Date</th>
                    <th>Ref No</th>
                    <th>Customer</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer">-- END --</div>
</div>