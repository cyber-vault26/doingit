<!-- Modal -->
<div class="modal fade" id="profomaPreviewModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Profoma Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="profomaPreviewBody">
        <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>
      </div>
    </div>
  </div>
</div>

<?php
$acceptedProfomas = selectProfomasByStatus($conn, 'accepted');
?>
<div class="card card-info">
    <div class="card-header">
        <h4>Approved Profoma List</h4>
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
            if(!empty($acceptedProfomas) && is_array($acceptedProfomas)){
                $counter = 1;
                foreach($acceptedProfomas as $p){
                    $displayAmount = strtoupper($p['currency_name']) !== 'TSH'
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
                    <button class="btn btn-outline-primary btn-sm" onclick="previewProfoma(<?= $p['id'] ?>)"><i class="fas fa-eye"></i> Preview</button>
                </td>
            </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>No approved profoma found</td></tr>";
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

<script>
function previewProfoma(id) {
    $('#profomaPreviewBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
    $('#profomaPreviewModal').modal('show');
    $.ajax({
        url: './requests/profoma_requests.php',
        type: 'GET',
        data: {action: 'preview', id: id},
        success: function(data) {
            $('#profomaPreviewBody').html(data);
        },
        error: function(xhr, status, error) {
            $('#profomaPreviewBody').html('<div class="alert alert-danger">' + xhr.responseText + '</div>');
        }
    });
}
</script>