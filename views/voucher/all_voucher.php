<?php
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>
<div class="card card-info">
    <div class="card-header"><h4 class="card-title">All Vouchers</h4></div>
    <div class="card-body">
        <div class="btn-group mb-3 flex-wrap d-flex w-100 justify-content-start" id="tab-buttons">
            <button class="btn btn-outline-primary btn-sm active" data-target="#all" data-type="all">All</button>
            <button class="btn btn-outline-danger btn-sm" data-target="#sales" data-type="sales">Sales</button>
            <button class="btn btn-outline-secondary btn-sm" data-target="#purchases" data-type="purchases">Purchases</button>
            <button class="btn btn-outline-warning btn-sm" data-target="#receipt" data-type="receipt">Receipts</button>
            <button class="btn btn-outline-info btn-sm" data-target="#payment" data-type="payment">Payments</button>
            <button class="btn btn-outline-success btn-sm" data-target="#journal" data-type="journal">Journals</button>
        </div>
    </div>
    <div class="card-footer">
        <div class="tab-content">
            <div class="tab-pane active" id="all">
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
                            <button class="btn btn-info btn-block mt-3" onclick="fetchVoucherByType('all');">Filter</button>
                        </div>
                    </div>
                    <div id="all-voucher"></div>
                </div>
            </div>

            <div class="tab-pane" id="sales">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control sales-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control sales-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchVoucherByType('sales');">Filter</button>
                        </div>
                    </div>
                    <div id="sales-voucher"></div>
                </div>
            </div>

            <div class="tab-pane" id="purchases">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control purchases-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control purchases-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchVoucherByType('purchases');">Filter</button>
                        </div>
                    </div>
                    <div id="purchase-voucher"></div>
                </div>
            </div>

            <div class="tab-pane" id="receipt">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control receipt-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control receipt-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchVoucherByType('receipt');">Filter</button>
                        </div>
                    </div>
                </div>
                <div id="receipt-voucher"></div>
            </div>

            <div class="tab-pane" id="payment">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control payment-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control payment-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchVoucherByType('payment');">Filter</button>
                        </div>
                    </div>
                </div>
                <div id="payment-voucher"></div>
            </div>

            <div class="tab-pane" id="journal">
                <div class="card-body">
                    <h5>Apply Filter</h5>
                    <div class="row">
                        <div class="col-md-4 col-sm-10">
                            <label>Start Date</label>
                            <input type="date" class="form-control journal-d1" value="<?= date("Y-01-01") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10">
                            <label>End Date</label>
                            <input type="date" class="form-control journal-d2" value="<?= date("Y-m-d") ?>">
                        </div>
                        <div class="col-md-4 col-sm-10 p-3">
                            <button class="btn btn-info btn-block mt-3" onclick="fetchVoucherByType('journal');">Filter</button>
                        </div>
                    </div>
                </div>
                <div id="journal-voucher"></div>
            </div>
        </div>

        <div id="spinner-overlay">
            <div class="spinner"></div>
            <p style="margin-top:15px; margin-left:15px; font-weight:bold;">Processing...</p>
        </div>
    </div>
</div>

<script>
var voucherStatus = '<?php echo $status; ?>';

$(document).ready(function() {
    $('#tab-buttons button').click(function() {
        $('#tab-buttons button').removeClass('active');
        $(this).addClass('active');
        $('.tab-pane').removeClass('active');
        $($(this).data('target')).addClass('active');
        sessionStorage.setItem('selectedVoucherType', $(this).data('type'));
        fetchVoucherByType($(this).data('type'));
    });

    var savedType = sessionStorage.getItem('selectedVoucherType') || 'all';
    $('#tab-buttons button[data-type="' + savedType + '"]').addClass('active').siblings().removeClass('active');
    $('.tab-pane').removeClass('active');
    $('#tab-buttons button[data-type="' + savedType + '"]').each(function(){
        $($(this).data('target')).addClass('active');
    });
    fetchVoucherByType(savedType);
});

function fetchVoucherByType(type) {
    var date1 = $('.' + type + '-d1').val();
    var date2 = $('.' + type + '-d2').val();
    if(!date1 || !date2) return;
    var result = type === 'purchases' ? '#purchase-voucher' : '#' + type + '-voucher';
    showSpinner();
    $.ajax({
        url: "./requests/voucher_requests.php?select_voucher_by_type",
        type: "GET",
        data: { type: type, date1: date1, date2: date2, status: voucherStatus },
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