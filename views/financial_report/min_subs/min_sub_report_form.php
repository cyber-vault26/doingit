<div class="row">
  <!-- Subsidiary Report (Normal) -->
  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">MinSub Report</h4>
      </div>
      <form action="./" method="get" class="was-validated">
        <input type="hidden" name="page" value="view_min_sub_report">
        <div class="card-body">
          <div class="form-group">
            <label for="sub_id_normal" class="form-label">Select MinSub:</label>
            <select class="form-control select2-form select2bs4-form" name="id" id="sub_id_normal" required>
              <option value="">Select Below</option>
              <?php
              $minSubs = selectAllMinSub($conn);
              if ($minSubs && count($minSubs) > 0) {
                foreach ($minSubs as $row) {
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

  <!-- Subsidiary Report by Date Range -->
  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">MinSub Report by Date Range</h4>
      </div>
      <form action="./" method="get" class="was-validated">
        <input type="hidden" name="page" value="view_min_sub_report_by_date_range">
        <div class="card-body">
          <div class="form-group mb-3">
            <label for="sub_id_date" class="form-label">Select MinSub:</label>
            <select class="form-control select2-form select2bs4-form" name="id" id="sub_id_date" required>
              <option value="">Select Below</option>
              <?php
              $minSubs = selectAllMinSub($conn);
              if ($minSubs && count($minSubs) > 0) {
                foreach ($minSubs as $row) {
                  echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                }
              }
              ?>
            </select>
          </div>
          <div class="form-group mb-3">
            <label for="date1_sub_range" class="form-label">Start Date:</label>
            <input type="date" name="date1" id="date1_sub_range" class="form-control" 
                   value="<?php echo date('Y') . '-01-01'; ?>" 
                   max="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="form-group mb-3">
            <label for="date2_sub_range" class="form-label">End Date:</label>
            <input type="date" name="date2" id="date2_sub_range" class="form-control" 
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