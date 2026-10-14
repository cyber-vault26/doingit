<div class="row">
  <!-- Min Ledger Report (Normal) -->
  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">Min Ledger Report</h4>
      </div>
      <form action="./" method="get" class="was-validated">
        <input type="hidden" name="page" value="view_min_ledger_report">
        <div class="card-body">
          <div class="form-group">
            <label for="sub_id_normal" class="form-label">Select Subsidiary:</label>
            <select class="form-control select2-form select2bs4-form" name="sub_id" id="sub_id_normal" required>
              <option value="">Select Below</option>
              <?php
              $subsidiaries = selectAllSubsidiaries($conn);
              if ($subsidiaries && count($subsidiaries) > 0) {
                foreach ($subsidiaries as $row) {
                  echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                }
              }
              ?>
            </select>
          </div>
        </div>
        <div class="card-footer text-center">
          <button type="submit" class="btn btn-success w-100">Get Report</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Min Ledger Report by Date Range -->
  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">Min Ledger Report by Date Range</h4>
      </div>
      <form action="./" method="get" class="was-validated">
        <input type="hidden" name="page" value="view_min_ledger_report_by_date_range">
        <div class="card-body">
          <div class="form-group mb-3">
            <label for="sub_id_date" class="form-label">Select Subsidiary:</label>
            <select class="form-control select2-form select2bs4-form" name="sub_id" id="sub_id_date" required>
              <option value="">Select Below</option>
              <?php
              $subsidiaries = selectAllSubsidiaries($conn);
              if ($subsidiaries && count($subsidiaries) > 0) {
                foreach ($subsidiaries as $row) {
                  echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                }
              }
              ?>
            </select>
          </div>
          <div class="form-group mb-3">
            <label for="date1_ledger_range" class="form-label">Start Date:</label>
            <input type="date" name="date1" id="date1_ledger_range" class="form-control" 
                   value="<?php echo date('Y') . '-01-01'; ?>" 
                   max="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="form-group mb-3">
            <label for="date2_ledger_range" class="form-label">End Date:</label>
            <input type="date" name="date2" id="date2_ledger_range" class="form-control" 
                   value="<?php echo date('Y-m-d'); ?>" 
                   max="<?php echo date('Y-m-d'); ?>" required>
          </div>
        </div>
        <div class="card-footer text-center">
          <button type="submit" class="btn btn-success w-100">Get Report</button>
        </div>
      </form>
    </div>
  </div>
</div>