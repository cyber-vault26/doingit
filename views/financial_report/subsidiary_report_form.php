<div class="row">
  <!-- Subsidiary Report (Normal) -->
  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">Subsidiary Report</h4>
      </div>
      <form action="./?page=view_subsidiary_report" method="post" class="was-validated">
        <div class="card-body">
          <div class="form-group mb-3">
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
          <div class="form-group mb-3">
            <label for="project_id_normal" class="form-label">Project <span class="text-muted small">(Optional)</span></label>
            <select class="form-control select2-form select2bs4-form" name="project_id" id="project_id_normal">
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
        <div class="card-footer text-center">
          <button type="submit" name="report" class="btn btn-success w-100">Get Report</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Subsidiary Report by Date Range -->
  <div class="col-md-6">
    <div class="card card-info my-3">
      <div class="card-header">
        <h4 class="card-title mb-0">Subsidiary Report by Date Range</h4>
      </div>
      <form action="./?page=view_subsidiary_report_by_date_range" method="post" class="was-validated">
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
            <label for="project_id_date" class="form-label">Project <span class="text-muted small">(Optional)</span></label>
            <select class="form-control select2-form select2bs4-form" name="project_id" id="project_id_date">
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
          <button type="submit" name="report" class="btn btn-success w-100">Get Report</button>
        </div>
      </form>
    </div>
  </div>
</div>