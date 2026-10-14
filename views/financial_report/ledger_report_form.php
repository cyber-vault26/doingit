<div class="row">
  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">Ledger Report (All Time)</h4>
      </div>
      <form action="./" method="get" class="was-validated">
        <input type="hidden" name="page" value="view_ledger_report">
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="ledger_id_all" class="form-label">Select Ledger Account:</label>
              <select name="ledger_id" id="ledger_id_all" class="form-control select2-form select2bs4-form" required>
                <option value="">-- Select Ledger --</option>
                <?php
                $ledgers = selectAllActiveLedgers($conn);
                foreach ($ledgers as $row) {
                  echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label for="store_id_all" class="form-label">Project <span class="text-muted small">(Optional)</span></label>
              <select class="form-control select2-form select2bs4-form" name="store_id" id="store_id_all">
                <option value="">All Projects</option>
                <?php
                $projects = listProjectsByStatus($conn, 'active');
                if ($projects && is_array($projects)) {
                  foreach ($projects as $project) {
                    echo "<option value='{$project['id']}'>" . htmlspecialchars($project['name']) . "</option>";
                  }
                }
                ?>
              </select>
            </div>
          </div>
        </div>
        <div class="card-footer text-center">
          <button type="submit" name="report" class="btn btn-success w-100">Get Report</button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">Ledger Report (By Date Range)</h4>
      </div>
      <form action="./" method="get" class="was-validated">
        <input type="hidden" name="page" value="view_ledger_report_by_date_range">
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="ledger_id_range" class="form-label">Select Ledger Account:</label>
              <select name="ledger_id" id="ledger_id_range" class="form-control select2-form select2bs4-form" required>
                <option value="">-- Select Ledger --</option>
                <?php
                $ledgers = selectAllActiveLedgers($conn);
                foreach ($ledgers as $row) {
                  echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label for="store_id_range" class="form-label">Project <span class="text-muted small">(Optional)</span></label>
              <select class="form-control select2-form select2bs4-form" name="store_id" id="store_id_range">
                <option value="">All Projects</option>
                <?php
                $projects = listProjectsByStatus($conn, 'active');
                if ($projects && is_array($projects)) {
                  foreach ($projects as $project) {
                    echo "<option value='{$project['id']}'>" . htmlspecialchars($project['name']) . "</option>";
                  }
                }
                ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="date1_sub" class="form-label">Starting Date:</label>
              <input type="date" name="date1" id="date1_sub" class="form-control" value="<?php echo date('Y-01-01'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="date2_sub" class="form-label">Ending Date:</label>
              <input type="date" name="date2" id="date2_sub" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
          </div>
        </div>
        <div class="card-footer text-center">
          <button type="submit" name="report" class="btn btn-success w-100">Get Report</button>
        </div>
      </form>
    </div>
  </div>
</div>