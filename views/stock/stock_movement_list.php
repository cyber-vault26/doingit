<?php $status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : 'pending'; ?>

<div class="modal fade no-print" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Selected Records</h5>
                <button type="button" class="close text-white" onclick="$('#bulkRejectModal').modal('hide')">&times;</button>
            </div>
            <div class="modal-body">
                <label>Reason for Rejection <span class="text-danger">*</span></label>
                <textarea id="bulk_rejection_reason" class="form-control" rows="4" placeholder="Enter reason for rejection..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="$('#bulkRejectModal').modal('hide')">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkStockAction('reject_stock')">Reject</button>
            </div>
        </div>
    </div>
</div>

<div id="spinner-overlay">
    <div class="spinner"></div>
    <p style="margin-top: 15px; margin-left:15px; font-weight: bold;">Processing...</p>
</div>

<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="stock-movement-tabs" role="tablist">
            <li class="pt-2 px-3"><h3 class="card-title">Stock Movement:</h3></li>
            <li class="nav-item">
                <a class="nav-link" id="tab-grn" data-toggle="pill" href="#tab-content-grn" role="tab" onclick="fetchStockMovement('in', 'data-container-grn')">Received (GRN)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-gdn" data-toggle="pill" href="#tab-content-gdn" role="tab" onclick="fetchStockMovement('out', 'data-container-gdn')">Issued (GDN)</a>
            </li>
            <!--<li class="nav-item">-->
            <!--    <a class="nav-link" id="tab-grn" data-toggle="pill" href="#tab-content-grn" role="tab" onclick="fetchStockMovement('transfer-in', 'data-container-grn')">Transfer In</a>-->
            <!--</li>-->
            <li class="nav-item">
                <a class="nav-link" id="tab-gdn" data-toggle="pill" href="#tab-content-gdn" role="tab" onclick="fetchStockMovement('transfer-out', 'data-container-gdn')">Transfer</a>
            </li>
        </ul>
        
    </div>
    <div class="card-body">
        <div class="row align-items-end mb-3">
            <div class="col-md-3 col-6">
                <label class="form-label font-weight-bold">Date From</label>
                <input type="date" id="filter_date1" class="form-control">
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label font-weight-bold">Date To</label>
                <input type="date" id="filter_date2" class="form-control">
            </div>
            <div class="col-md-3 col-12 mt-2 mt-md-0">
                <label class="form-label font-weight-bold">Store</label>
                <select id="filter_store" class="form-control form-control-sm select2-form select2bs4-form" <?= $cost_center ? 'disabled' : '' ?>>
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
            <div class="col-md-3 col-12 mt-2 mt-md-0 d-flex align-items-end">
                <button class="btn btn-primary btn-block" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane fade" id="tab-content-grn" role="tabpanel">
                <div id="data-container-grn"></div>
            </div>
            <div class="tab-pane fade" id="tab-content-gdn" role="tabpanel">
                <div id="data-container-gdn"></div>
            </div>
        </div>
    </div>
</div>

<script>
var currentStatus   = '<?= $status ?>';
var activeTabType   = 'in';
var activeContainer = 'data-container-grn';

function getFilters(){
    return {
        date1: document.getElementById('filter_date1').value,
        date2: document.getElementById('filter_date2').value,
        store: document.getElementById('filter_store').value
    };
}

function activateTab(type){
    activeTabType   = type;
    activeContainer = type === 'in' ? 'data-container-grn' : 'data-container-gdn';
    var tabId  = type === 'in' ? 'tab-grn' : 'tab-gdn';
    var paneId = type === 'in' ? 'tab-content-grn' : 'tab-content-gdn';
    $('#stock-movement-tabs .nav-link').removeClass('active');
    $('#' + tabId).addClass('active');
    $('.tab-pane').removeClass('show active');
    $('#' + paneId).addClass('show active');
    window.location.hash = type;
}

function fetchStockMovement(type, containerId){
    activateTab(type);
    showSpinner();
    var filters = getFilters();
    $.get('./requests/stock_requests.php', {
        get_stock_movement: 1,
        type:   type,
        status: currentStatus,
        date1:  filters.date1,
        date2:  filters.date2,
        store:  filters.store
    }, function(response){
        document.getElementById(containerId).innerHTML = response;
        reinitializeTableSearch();
    }).fail(function(){
        document.getElementById(containerId).innerHTML = "<div class='alert alert-danger'>Failed to load data.</div>";
    }).always(hideSpinner);
}

function applyFilters(){
    fetchStockMovement(activeTabType, activeContainer);
}

function getActiveContainer(){
    return '#' + activeContainer;
}

function bulkStockAction(action){
    var reason = '';
    if(action === 'reject_stock'){
        reason = $('#bulk_rejection_reason').val().trim();
        if(!reason){ alert('Please enter a reason for rejection.'); return; }
    }
    var selected = $(getActiveContainer() + ' .row-select:checked').map(function(){ return this.value; }).get();
    console.log('ACTION:', action);
    console.log('ACTIVE CONTAINER:', getActiveContainer());
    console.log('SELECTED BATCHES:', selected);
    if(!selected.length || !confirm('Are you sure?')) return;
    var formData = selected.map(function(b){ return 'selected_batches[]=' + encodeURIComponent(b); }).join('&') + '&' + action + '=1';
    if(reason) formData += '&rejection_reason=' + encodeURIComponent(reason);
    console.log('FORM DATA:', formData);
    showSpinner();
    $.ajax({
        url: './controllers/stock_controllers.php',
        type: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        data: formData,
        success: function(response){
            console.log('RAW RESPONSE:', JSON.stringify(response));
            console.log('TRIMMED:', JSON.stringify(response.trim()));
            console.log('EQUALS SUCCESS:', response.trim() == 'SUCCESS');
            if(response.trim() == 'SUCCESS'){
                if(action === 'reject_stock') $('#bulkRejectModal').modal('hide');
                applyFilters();
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr){
            console.log('AJAX ERROR STATUS:', xhr.status);
            console.log('AJAX ERROR RESPONSE:', xhr.responseText);
            alert('Request failed.');
        },
        complete: function(){
            hideSpinner();
        }
    });
}

function toggleAll(checkbox){
    $(getActiveContainer() + ' .row-select').prop('checked', checkbox.checked).each(function(){ toggleRow(this); });
}

function toggleRow(checkbox){
    var row = $(checkbox).closest('tr');
    $(checkbox).is(':checked') ? row.addClass('table-danger') : row.removeClass('table-danger');
    $('.bulk-actions').toggle($(getActiveContainer() + ' .row-select:checked').length > 0);
}

window.addEventListener('DOMContentLoaded', function(){
    var hash = window.location.hash.replace('#', '');
    var type = hash === 'out' ? 'out' : 'in';
    var cont = type === 'in' ? 'data-container-grn' : 'data-container-gdn';
    fetchStockMovement(type, cont);
});
</script>