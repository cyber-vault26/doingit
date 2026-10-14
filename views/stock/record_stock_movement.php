<div id="spinner-overlay">
    <div class="spinner"></div>
    <p style="margin-top: 15px; margin-left:15px; font-weight: bold;">Processing...</p>
</div>
<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" role="tablist">
            <li class="pt-2 px-3"><h3 class="card-title">Record Stock Movement:</h3></li>
            <li class="nav-item">
                <a class="nav-link active" id="tab-grn" data-toggle="pill" href="#tab-content-grn" role="tab" onclick="loadGrnForm()">
                    Receive Stock (GRN)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-gdn" data-toggle="pill" href="#tab-content-gdn" role="tab" onclick="loadGdnForm()">
                    Issue Stock (GDN)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-transfer" data-toggle="pill" href="#tab-content-transfer" role="tab" onclick="loadTransferForm()">
                    Stock Transfer
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-content-grn" role="tabpanel">
                <div id="form-container-grn"></div>
            </div>
            <div class="tab-pane fade" id="tab-content-gdn" role="tabpanel">
                <div id="form-container-gdn"></div>
            </div>
            <div class="tab-pane fade" id="tab-content-transfer" role="tabpanel">
                <div id="form-container-transfer"></div>
            </div>
        </div>
    </div>
</div>
 
 

<!-- Stock Date Request Modal -->
<div class="modal fade" id="stockDateRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-calendar-times mr-2"></i>Past Date — Request Required</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    You selected <strong id="modal-selected-date-display"></strong>.
                    Past dates are locked. Please explain why you need to record on this date.
                </p>
                <div class="form-group">
                    <label>Transaction Type</label>
                    <input type="text" id="modal-txn-type-display" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Reason <span class="text-danger">*</span></label>
                    <textarea id="modal-reason-input" class="form-control" rows="3" placeholder="Explain why you are recording on a past date..."></textarea>
                    <small class="text-danger d-none" id="modal-reason-error">Please enter a reason.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="modal-submit-btn">
                    <i class="fas fa-paper-plane mr-1"></i> Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form that does the real POST -->
<form id="stockDateRequestForm" action="../../controllers/stock_date_request_controller.php" method="POST" style="display:none;">
    <input type="hidden" name="send_stock_date_request" value="1">
    <input type="hidden" name="requested_date" id="form-requested-date">
    <input type="hidden" name="transaction_type" id="form-transaction-type">
    <input type="hidden" name="reason" id="form-reason">
</form>


<script>
function stockLoadUnits(item_id, unitSel, baseQty, store_id, priceEl) {
    if (!item_id) return;
    unitSel = $(unitSel);
    baseQty = $(baseQty);
    unitSel.html('<option value="">Loading...</option>').prop('disabled', true).removeAttr('data-balance');
    baseQty.val('');

    // Remove image from current block while loading
    unitSel.closest('.grn-item-block, .gdn-item-block, .str-item-block, tr').find('.item-img-preview').remove();

    $.get('./requests/stock_requests.php', {get_item_units: 1, item_id: item_id, store_id: store_id || ''}, function(data) {
        if (!data || typeof data !== 'object') {
            unitSel.html('<option value="">Error loading units</option>').prop('disabled', false);
            return;
        }
        var balance = parseFloat(data.base_unit_balance) || 0;
        if (store_id) {
            unitSel.attr('data-balance', balance);
        } else {
            unitSel.attr('data-balance', 'Infinity');
        }
        unitSel.html('');
        unitSel.append('<option value="' + data.base_unit + '" data-factor="1">' + data.base_unit_name + ' (' + balance.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' left)</option>');
        $.each(data.alt_units || [], function(i, u) {
            unitSel.append('<option value="' + u.id + '" data-factor="' + u.factor + '">' + u.name + ' (' + parseFloat(u.balance_in_unit).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' left)</option>');
        });
        unitSel.prop('disabled', false);
        unitSel.closest('.gdn-item-block, .str-item-block').find('.base-unit-label').text('Base Quantity (' + data.base_unit_name + ')');

        // ── GDN ONLY: auto-fill selling price ──────────────────────────
        if (priceEl && data.selling_price !== undefined) {
            var sp = parseFloat(data.selling_price) || 0;
            $(priceEl).val(sp > 0 ? sp.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '');
            if (typeof window.gdnCalculateRow === 'function') {
                window.gdnCalculateRow($(priceEl).get(0));
            }
        }
        // ───────────────────────────────────────────────────────────────

        // ── ITEM IMAGE ─────────────────────────────────────────────────
        var block = unitSel.closest('.grn-item-block, .gdn-item-block, .str-item-block, tr');
        block.find('.item-img-preview').remove();
        if (data.image_url) {
            var imgPath = '../uploads/items/' + data.image_url;
            var imgHtml =
                '<div class="item-img-preview mb-2">' +
                '<a href="' + imgPath + '" target="_blank">' +
                '<img src="' + imgPath + '" style="width:120px;height:120px;object-fit:cover;cursor:pointer;border:2px solid #ddd;">' +
                '</a>' +
                '</div>';
            block.prepend(imgHtml);
        }
        // ───────────────────────────────────────────────────────────────

    }, 'json').fail(function() {
        unitSel.html('<option value="">Failed to load</option>').prop('disabled', false);
    });
}



function stockAddTableRow(tableId){
    console.log('[stockAddTableRow] tableId:',tableId);
    var tbody=$('#'+tableId).find('tbody');
    var newRow=tbody.find('tr.data-row:first').clone();
    newRow.find('select').each(function(){
        try{$(this).select2('destroy');}catch(e){}
        $(this).next('.select2-container').remove();
        $(this).removeClass('select2-hidden-accessible').show();
        this.selectedIndex=0;
    });
    newRow.find('input').val('');
    newRow.find('.grn-unit-select,.str-unit-select').html('<option value="">Select item first</option>').prop('disabled',true);
    tbody.append(newRow);
    newRow.find('.select2-form').select2({placeholder:"Select an option"});
    newRow.find('.select2bs4-form').select2({theme:'bootstrap4'});
    formatNumber();
    console.log('[stockAddTableRow] Row added, total:',tbody.find('tr').length);
}
function stockRemoveTableRow(tableId){
    var table=$('#'+tableId);
    if(table.find('tbody tr').length>1){
        table.find('tbody tr:last').remove();
        console.log('[stockRemoveTableRow] Row removed from:',tableId);
    } else {
        console.warn('[stockRemoveTableRow] Cannot remove last row');
    }
}
function loadGrnForm(){
    console.log('[loadGrnForm] Loading');
    showSpinner();
    $.get('./requests/stock_requests/stock_form_requests.php',{grn_form_input:1},function(response){
        console.log('[loadGrnForm] Response length:',response.length);
        $('#form-container-grn').html(response);
        $('#form-container-grn script').each(function(){
            var s=document.createElement('script');
            s.textContent=$(this).text();
            document.body.appendChild(s);
        });
    }).fail(function(xhr,status,error){
        console.error('[loadGrnForm] Failed:',status,error,xhr.responseText);
        $('#form-container-grn').html("<div class='alert alert-danger'>Failed to load form.</div>");
    }).always(hideSpinner);
}
function loadGdnForm(){
    console.log('[loadGdnForm] Loading');
    showSpinner();
    $.get('./requests/stock_requests/stock_form_requests.php',{gdn_form_input:1},function(response){
        console.log('[loadGdnForm] Response length:',response.length);
        $('#form-container-gdn').html(response);
        $('#form-container-gdn script').each(function(){
            var s=document.createElement('script');
            s.textContent=$(this).text();
            document.body.appendChild(s);
        });
    }).fail(function(xhr,status,error){
        console.error('[loadGdnForm] Failed:',status,error,xhr.responseText);
        $('#form-container-gdn').html("<div class='alert alert-danger'>Failed to load form.</div>");
    }).always(hideSpinner);
}
function loadTransferForm(){
    console.log('[loadTransferForm] Loading');
    showSpinner();
    $.get('./requests/stock_requests/stock_form_requests.php',{stock_transfer_input:1},function(response){
        console.log('[loadTransferForm] Response length:',response.length);
        $('#form-container-transfer').html(response);
        $('#form-container-transfer script').each(function(){
            var s=document.createElement('script');
            s.textContent=$(this).text();
            document.body.appendChild(s);
        });
    }).fail(function(xhr,status,error){
        console.error('[loadTransferForm] Failed:',status,error,xhr.responseText);
        $('#form-container-transfer').html("<div class='alert alert-danger'>Failed to load form.</div>");
    }).always(hideSpinner);
}
window.addEventListener('DOMContentLoaded',function(){
    console.log('[DOMContentLoaded] Loading initial GRN form');
    loadGrnForm();
});

(function(){
    var today = new Date().toISOString().slice(0,10);

    function isApproved(date, type){
        return (window.approvedStockDates || []).some(function(r){
            return r.date === date && r.type === type;
        });
    }

    $(document).on('change', '.stock-date-input', function(){
        var selected = $(this).val();
        if(!selected) return;
        var txnType = $(this).data('type');

        if(selected > today){
            $(this).val(today);
            alert('Future dates are not allowed.');
            return;
        }

        if(selected < today){
            if(isApproved(selected, txnType)){
                return; // approved for today, allow it - don't lock/reset
            }

            $('#modal-selected-date-display').text(selected);
            $('#modal-txn-type-display').val(txnType);
            $('#modal-reason-input').val('');
            $('#modal-reason-error').addClass('d-none');
            $('#form-requested-date').val(selected);
            $('#form-transaction-type').val(txnType);
            $(this).val(today);
            $('#stockDateRequestModal').modal('show');
        }
    });

    $('#modal-submit-btn').on('click', function(){
        var reason = $('#modal-reason-input').val().trim();
        if(!reason){
            $('#modal-reason-error').removeClass('d-none');
            return;
        }
        $('#form-reason').val(reason);
        $('#stockDateRequestForm').submit();
    });

    $(document).on('click', '#stockDateRequestModal [data-dismiss="modal"]', function(){
        $('#stockDateRequestModal').modal('hide');
    });
})();
</script>