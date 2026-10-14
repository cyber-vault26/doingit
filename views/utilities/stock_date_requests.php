<?php
$usertype = $_SESSION['usertype'] ?? '';
$userid   = (int)($_SESSION['userid'] ?? 0);
$isAdmin  = in_array($usertype, ['Admin', 'manager']);

if($isAdmin){
    $pending  = selectAllStockDateRequestsByStatus($conn, 'pending');
    $approved = selectAllStockDateRequestsByStatus($conn, 'approved');
} else {
    $pending  = selectUserStockDateRequestsByStatus($conn, $userid, 'pending');
    $approved = selectUserStockDateRequestsByStatus($conn, $userid, 'approved');
}
?>
<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" role="tablist">
            <li class="pt-2 px-3"><h3 class="card-title">Stock Date Requests:</h3></li>
            <li class="nav-item">
                <a class="nav-link active" id="tab-pending" data-toggle="pill" href="#tab-content-pending" role="tab">
                    Pending <span class="badge badge-warning"><?= count($pending) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-approved" data-toggle="pill" href="#tab-content-approved" role="tab">
                    Approved <span class="badge badge-success"><?= count($approved) ?></span>
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <!-- PENDING TAB -->
            <div class="tab-pane fade show active" id="tab-content-pending" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <?php if($isAdmin): ?><th>User</th><?php endif; ?>
                                <th>Requested Date</th>
                                <th>Transaction Type</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <?php if($isAdmin): ?><th>Action</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($pending && is_array($pending) && count($pending) > 0): ?>
                                <?php foreach($pending as $i => $req): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <?php if($isAdmin): ?><td><?= htmlspecialchars($req['user_name'] ?? '') ?></td><?php endif; ?>
                                    <td><?= htmlspecialchars($req['requested_date']) ?></td>
                                    <td><span class="badge badge-info text-uppercase"><?= htmlspecialchars($req['transaction_type']) ?></span></td>
                                    <td><?= htmlspecialchars($req['reason']) ?></td>
                                    <td><span class="badge badge-warning text-uppercase"><?= htmlspecialchars($req['status']) ?></span></td>
                                    <?php if($isAdmin): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#reviewModal<?= $req['id'] ?>">
                                            <i class="fas fa-edit mr-1"></i> Review
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>

                                <?php if($isAdmin): ?>
                                <!-- Review Modal -->
                                <div class="modal fade" id="reviewModal<?= $req['id'] ?>" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="../controllers/stock_date_request_controller.php" method="POST">
                                                <div class="modal-header bg-primary">
                                                    <h5 class="modal-title">Review Request #<?= $req['id'] ?></h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>
                                                        <strong>User:</strong> <?= htmlspecialchars($req['user_name'] ?? '') ?><br>
                                                        <strong>Requested Date:</strong> <?= htmlspecialchars($req['requested_date']) ?><br>
                                                        <strong>Transaction Type:</strong> <?= htmlspecialchars($req['transaction_type']) ?><br>
                                                        <strong>Reason:</strong> <?= htmlspecialchars($req['reason']) ?>
                                                    </p>
                                                    <div class="form-group">
                                                        <label class="form-label">Decision</label>
                                                        <select name="decision" class="form-control" required>
                                                            <option value="">Select below</option>
                                                            <option value="approve">Approve</option>
                                                            <option value="reject">Reject</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Admin Note / Reason for Update</label>
                                                        <textarea name="admin_note" class="form-control" rows="3" required placeholder="Explain your decision..."></textarea>
                                                    </div>
                                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="review_stock_date_request" class="btn btn-primary">
                                                        <i class="fas fa-paper-plane mr-1"></i> Submit
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? 6 : 5 ?>" class="text-center text-muted">No pending requests</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- APPROVED TAB -->
            <div class="tab-pane fade" id="tab-content-approved" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <?php if($isAdmin): ?><th>User</th><?php endif; ?>
                                <th>Requested Date</th>
                                <th>Transaction Type</th>
                                <th>Reason</th>
                                <th>Admin Note</th>
                                <th>Approved At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($approved && is_array($approved) && count($approved) > 0): ?>
                                <?php foreach($approved as $i => $req): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <?php if($isAdmin): ?><td><?= htmlspecialchars($req['user_name'] ?? '') ?></td><?php endif; ?>
                                    <td><?= htmlspecialchars($req['requested_date']) ?></td>
                                    <td><span class="badge badge-info text-uppercase"><?= htmlspecialchars($req['transaction_type']) ?></span></td>
                                    <td><?= htmlspecialchars($req['reason']) ?></td>
                                    <td><?= htmlspecialchars($req['admin_note'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($req['approved_at'] ?? '') ?></td>
                                    <td><span class="badge badge-success text-uppercase"><?= htmlspecialchars($req['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? 7 : 6 ?>" class="text-center text-muted">No approved requests</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>