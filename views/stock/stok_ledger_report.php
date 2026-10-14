<div id="spinner-overlay">
    <div class="spinner"></div>
    <p style="margin-top: 15px; margin-left:15px; font-weight: bold;">Processing...</p>
</div>

<div class="card card-primary">
  <div class="card-header">
    <h3 class="card-title">Stock Movement: Ledger Report</h3>
  </div>
  <div class="card-body">

    <div class="card-footer">
      <div class="row align-items-end">

        <div class="col-md-2 col-6">
          <label class="form-label font-weight-bold">From</label>
          <input type="date" id="filter_date1" class="form-control">
        </div>

        <div class="col-md-2 col-6">
          <label class="form-label font-weight-bold">To</label>
          <input type="date" id="filter_date2" class="form-control">
        </div>

        <div class="col-md-3 col-12 mt-2 mt-md-0">
          <label class="form-label font-weight-bold">Store</label>
         <select id="filter_store" class="form-control select2-form select2bs4-form" <?= $cost_center ? 'disabled' : '' ?>>
            <option value="">All Stores</option>
            <?php
                $stores = selectAllStockStores($conn);
                if($stores && is_array($stores)){
                    foreach($stores as $store){
                        echo "<option value='$store[id]' " . ($store['id'] == $cost_center ? 'selected' : '') . ">$store[name]</option>";
                    }
                }
            ?>
        </select>
        <?php if($cost_center): ?>
            <input type="hidden" name="store_id" value="<?= $cost_center ?>">
        <?php endif; ?>
        </div>

        <div class="col-md-3 col-12 mt-2 mt-md-0">
          <label class="form-label font-weight-bold">Ledger</label>
          <select id="filter_ledger" class="form-control select2-form select2bs4-form">
            <option value="">All Ledgers</option>
            <?php
              $ledgers = selectAllSubsidiaries($conn);
              if($ledgers && is_array($ledgers)){
                foreach($ledgers as $ledger){
                  echo "<option value='$ledger[id]'>$ledger[name]</option>";
                }
              }
            ?>
          </select>
        </div>

        <div class="col-md-2 col-12 mt-2 mt-md-0 d-flex align-items-end">
          <button class="btn btn-primary btn-block" onclick="generateLedgerReport()">
            <i class="fas fa-cog"></i> Generate
          </button>
        </div>

      </div>
    </div>

    <div id="data-container-item"></div>

  </div>
</div>

<script>
function getFilters() {
    return {
        date1:  document.getElementById('filter_date1').value,
        date2:  document.getElementById('filter_date2').value,
        store:  document.getElementById('filter_store').value,
        ledger: document.getElementById('filter_ledger').value
    };
}

function generateLedgerReport() {
    var filters = getFilters();
    showSpinner();
    $.get("./requests/stock_requests/stock_requests.php", {
        get_ledger_report: 1,
        date1:  filters.date1,
        date2:  filters.date2,
        store:  filters.store,
        ledger: filters.ledger
    }, function(response) {
        document.getElementById('data-container-item').innerHTML = response;
        reinitializeTableSearch();
    }).fail(function(xhr, status, error) {
        document.getElementById('data-container-item').innerHTML =
            "<div class='alert alert-danger'>" +
            "<strong>AJAX Error:</strong><br>" +
            "Status: " + xhr.status + "<br>" +
            "Error: " + error + "<br>" +
            "Response: " + xhr.responseText +
            "</div>";
    }).always(hideSpinner);
}
</script>
<script>
function printStockLedgerReport(contentId) {
    var base = window.location.origin + '/';
    var el = document.getElementById(contentId);
    if(!el){ alert('Report not loaded yet.'); return; }
    var content = el.outerHTML;
    var win = window.open('', '_blank');
    if(!win){ alert('Please allow popups for printing.'); return; }
    win.document.write('<html><head><title>Stock Ledger Report</title>');
    win.document.write('<link rel="stylesheet" href="' + base + 'plugins/fontawesome-free/css/all.min.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/primary-color.css">');
    win.document.write('<link rel="stylesheet" href="' + base + 'dist/css/stock-report-ledger.css">');
    win.document.write('</head><body>');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function(){ win.print(); win.close(); }, 1200);
}
</script>