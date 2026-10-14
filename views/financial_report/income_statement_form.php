<?php $stores = selectAllStockStores($conn); ?>
<style media="print">
  @page { size: A4; margin: 15mm; }
  html { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
  thead.bg-info, thead.bg-info th { background-color: #0dcaf0 !important; color: #000 !important; }
  .row.mb-2, .breadcrumb, .card-header, .nav-tabs, .card-footer .row,
  .main-footer, input, button, .no-print { display: none !important; }
  .report-header { border-bottom: 2px solid #000; }
</style>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Income Statement</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="./?page=homepage">Home</a></li>
            <li class="breadcrumb-item"><a href="./?page=stock_summary_report">Financial reports</a></li>
            <li class="breadcrumb-item active">Income Statement</li>
        </ol>
    </div>
</div>
<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
            <li class="pt-2 px-3"><h3 class="card-title">Level:</h3></li>
            <li class="nav-item"><a class="nav-link" id="custom-tab-master" data-toggle="pill" href="#custom-tabs-two-master" role="tab" aria-controls="custom-tabs-two-master" aria-selected="false" onclick="fetchIncomeStatement('master')">Master Level</a></li>
            <li class="nav-item"><a class="nav-link" id="custom-tab-all" data-toggle="pill" href="#custom-tabs-two-submaster" role="tab" aria-controls="custom-tabs-two-submaster" aria-selected="false" onclick="fetchIncomeStatement('submaster')">SubMaster Level</a></li>
            <li class="nav-item"><a class="nav-link" id="custom-tab-ledger" data-toggle="pill" href="#custom-tabs-two-ledger" role="tab" aria-controls="custom-tabs-two-ledger" aria-selected="false" onclick="fetchIncomeStatement('ledger')">Ledger Level</a></li>
            <li class="nav-item"><a class="nav-link active" id="custom-tab-subsidiary" data-toggle="pill" href="#custom-tabs-two-subsidiary" role="tab" aria-controls="custom-tabs-two-subsidiary" aria-selected="true" onclick="fetchIncomeStatement('subsidiary')">Subsidiary Level</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade" id="custom-tabs-two-master" role="tabpanel" aria-labelledby="custom-tab-master">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-3 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control master-date1 global-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control master-date2 global-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>Store</label>
                            <select class="form-control select2-form select2bs4-form master-store global-store" <?= $cost_center ? 'disabled' : '' ?>>
                                <option value="">All Stores</option>
                                <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                                    <option value="<?= $store['id'] ?>" <?= $store['id'] == $cost_center ? 'selected' : '' ?>><?= $store['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($cost_center): ?>
                                <input type="hidden" class="global-store-hidden" value="<?= $cost_center ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchIncomeStatement('master')">Filter</button>
                        </div>
                    </div>
                </div>
                <div class="master-data"></div>
            </div>
            <div class="tab-pane fade" id="custom-tabs-two-submaster" role="tabpanel" aria-labelledby="custom-tab-submaster">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-3 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control submaster-date1 global-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control submaster-date2 global-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>Store</label>
                            <select class="form-control select2-form select2bs4-form submaster-store global-store" <?= $cost_center ? 'disabled' : '' ?>>
                                <option value="">All Stores</option>
                                <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                                    <option value="<?= $store['id'] ?>" <?= $store['id'] == $cost_center ? 'selected' : '' ?>><?= $store['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($cost_center): ?>
                                <input type="hidden" class="global-store-hidden" value="<?= $cost_center ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchIncomeStatement('submaster')">Filter</button>
                        </div>
                    </div>
                </div>
                <div class="submaster-data"></div>
            </div>
            <div class="tab-pane fade" id="custom-tabs-two-ledger" role="tabpanel" aria-labelledby="custom-tab-ledger">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-3 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control ledger-date1 global-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control ledger-date2 global-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>Store</label>
                            <select class="form-control select2-form select2bs4-form ledger-store global-store" <?= $cost_center ? 'disabled' : '' ?>>
                                <option value="">All Stores</option>
                                <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                                    <option value="<?= $store['id'] ?>" <?= $store['id'] == $cost_center ? 'selected' : '' ?>><?= $store['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($cost_center): ?>
                                <input type="hidden" class="global-store-hidden" value="<?= $cost_center ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchIncomeStatement('ledger')">Filter</button>
                        </div>
                    </div>
                </div>
                <div class="ledger-data"></div>
            </div>
            <div class="tab-pane fade show active" id="custom-tabs-two-subsidiary" role="tabpanel" aria-labelledby="custom-tab-subsidiary">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-3 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control subsidiary-date1 global-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control subsidiary-date2 global-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-3 col-sm-10">
                            <label>Store</label>
                            <select class="form-control select2-form select2bs4-form subsidiary-store global-store" <?= $cost_center ? 'disabled' : '' ?>>
                                <option value="">All Stores</option>
                                <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                                    <option value="<?= $store['id'] ?>" <?= $store['id'] == $cost_center ? 'selected' : '' ?>><?= $store['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($cost_center): ?>
                                <input type="hidden" class="global-store-hidden" value="<?= $cost_center ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchIncomeStatement('subsidiary')">Filter</button>
                        </div>
                    </div>
                </div>
                <div class="subsidiary-data"></div>
            </div>
        </div>
    </div>
</div>
<script>
  var activeLevel = 'subsidiary';

  $(document).ready(function(){
    fetchIncomeStatement('subsidiary');

    $(document).on('click', '#exportExcelBtn', function(){
      var table = $('.'+activeLevel+'-data #incomeStatementTable')[0];
      exportTableToExcel(table, 'Income Statement', 'Income_Statement_'+activeLevel);
    });

    $('.global-date1').on('change', function(){
      var val = $(this).val();
      $('.global-date1').val(val);
    });

    $('.global-date2').on('change', function(){
      var val = $(this).val();
      $('.global-date2').val(val);
    });

    $('.global-store').on('change', function(){
      var val = $(this).val();
      $('.global-store').val(val);
    });
  });

  function syncDates() {
    var date1 = $('.global-date1').first().val();
    var date2 = $('.global-date2').first().val();
    var store = $('.global-store').first().val();
    $('.global-date1').val(date1);
    $('.global-date2').val(date2);
    $('.global-store').val(store);
  }

  function getStoreId(level) {
    <?php if($cost_center): ?>
      return '<?= $cost_center ?>';
    <?php else: ?>
      return $('.'+level+'-store').val() || '';
    <?php endif; ?>
  }

  function fetchIncomeStatement(level){
    activeLevel = level;
    syncDates();
    var date1   = $('.'+level+'-date1').val();
    var date2   = $('.'+level+'-date2').val();
    var storeId = getStoreId(level);
    $('.'+level+'-data').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $.ajax({
      url:  './requests/financial_reports/income_statement_requests.php',
      data: { level: level, date1: date1, date2: date2, store_id: storeId },
      type: 'GET',
      success: function(response){
        $('.'+level+'-data').html(response);
      },
      error: function(error){
        $('.'+level+'-data').html('<div class="text-center py-4">Error loading data.</div>');
      }
    });
  }

  function exportToPDF() {
    window.print();
  }
</script>