<?php
$stores    = selectAllStockStores($conn);
$today     = date('Y-m-d');
$first_day = date('Y-m-01');
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group mb-0">
                                    <label>From Date</label>
                                    <input type="date" id="dash_date1" class="form-control" value="<?= $first_day ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group mb-0">
                                    <label>To Date</label>
                                    <input type="date" id="dash_date2" class="form-control" value="<?= $today ?>">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group mb-0">
                                    <label>Store</label>
                                    <select id="dash_store" class="form-control" <?= $cost_center ? 'disabled' : '' ?>>
                                        <option value="">All Stores</option>
                                        <?php if($stores && is_array($stores)) foreach($stores as $store): ?>
                                            <option value="<?= $store['id'] ?>" <?= isset($cost_center) && $store['id'] == $cost_center ? 'selected' : '' ?>><?= htmlspecialchars($store['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" id="dashFilterBtn" class="btn btn-primary btn-block d-block">Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="dashboardContent">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
                <div class="mt-3 text-muted"><strong>Loading...</strong></div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function(){
    function loadDashboard(){
        $('#dashboardContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div><div class="mt-3 text-muted"><strong>Loading...</strong></div></div>');
        var params = 'stock_summaries=1&date1='+$('#dash_date1').val()+'&date2='+$('#dash_date2').val()+'&store_id='+$('#dash_store').val();
        $('#dashboardContent').load('requests/dashboard_requests.php?'+params);
    }
    $('#dashFilterBtn').on('click', loadDashboard);
    loadDashboard();
});
</script>