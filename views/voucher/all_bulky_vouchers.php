<?php
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>
<div class="card card-info">
    <div class="card-header"><h4 class="card-title">Bulky Vouchers</h4></div>
    <div class="card-body">
        <div class="btn-group mb-3 flex-wrap d-flex w-100 justify-content-start" id="bulky-tab-buttons">
            <button class="btn btn-outline-primary btn-sm active" data-target="#bv-all" data-type="all">All</button>
            <button class="btn btn-outline-info btn-sm" data-target="#bv-payment" data-type="bulky-payment">Bulky Payment</button>
            <button class="btn btn-outline-warning btn-sm" data-target="#bv-receipt" data-type="bulky-receipt">Bulky Receipt</button>
            <button class="btn btn-outline-success btn-sm" data-target="#bv-journal" data-type="bulky-journal">Bulky Journal</button>
        </div>
    </div>
    <div class="card-footer">
        <div class="tab-content">

            <div class="tab-pane active" id="bv-all">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control all-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control all-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchBulkyVoucherByType('all');">Filter</button>
                        </div>
                    </div>
                    <div id="all-bulky-voucher"></div>
                </div>
            </div>

            <div class="tab-pane" id="bv-payment">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control bulky-payment-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control bulky-payment-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchBulkyVoucherByType('bulky-payment');">Filter</button>
                        </div>
                    </div>
                    <div id="bulky-payment-voucher"></div>
                </div>
            </div>

            <div class="tab-pane" id="bv-receipt">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control bulky-receipt-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control bulky-receipt-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchBulkyVoucherByType('bulky-receipt');">Filter</button>
                        </div>
                    </div>
                    <div id="bulky-receipt-voucher"></div>
                </div>
            </div>

            <div class="tab-pane" id="bv-journal">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control bulky-journal-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control bulky-journal-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchBulkyVoucherByType('bulky-journal');">Filter</button>
                        </div>
                    </div>
                    <div id="bulky-journal-voucher"></div>
                </div>
            </div>

        </div>

        <div id="spinner-overlay">
            <div class="spinner"></div>
            <p style="margin-top:15px; margin-left:15px; font-weight:bold;">Processing...</p>
        </div>
    </div>
</div>

<script>
var bulkyVoucherStatus = '<?php echo $status; ?>';

$(document).ready(function() {
    $('#bulky-tab-buttons button').click(function() {
        $('#bulky-tab-buttons button').removeClass('active');
        $(this).addClass('active');
        $('.tab-pane').removeClass('active');
        $($(this).data('target')).addClass('active');
        sessionStorage.setItem('selectedBulkyVoucherType', $(this).data('type'));
        fetchBulkyVoucherByType($(this).data('type'));
    });

    var savedType = sessionStorage.getItem('selectedBulkyVoucherType') || 'all';
    $('#bulky-tab-buttons button[data-type="' + savedType + '"]').addClass('active').siblings().removeClass('active');
    $('.tab-pane').removeClass('active');
    $('#bulky-tab-buttons button[data-type="' + savedType + '"]').each(function(){
        $($(this).data('target')).addClass('active');
    });
    fetchBulkyVoucherByType(savedType);
});

function fetchBulkyVoucherByType(type) {
    var date1 = $('.' + type + '-d1').val();
    var date2 = $('.' + type + '-d2').val();
    if(!date1 || !date2) return;
    var result = '#' + type + '-voucher';
    if(type === 'all') result = '#all-bulky-voucher';
    showSpinner();
    $.ajax({
        url: "./requests/voucher_requests.php?select_bulky_voucher_by_type",
        type: "GET",
        data: { type: type, date1: date1, date2: date2, status: bulkyVoucherStatus },
        success: function(response) {
            $(result).html(response);
            reinitializeTableSearch();
        },
        error: function(error) {
            console.error(error);
        },
        complete: function() {
            hideSpinner();
        }
    });
}
</script>