<div class=" card card-info">
    <div class=" card-header"> <h4 class=" card-title"> Payroll Voucher</h4></div>
    <form action="./?page=payroll_voucher" method="post" class=" was-validated">
        <div class=" card-body">
            <div class=" form-group">
                <label for="">Select Month</label>
                <input type="month" name="month" class=" form-control" required max="<?= date("Y-m")?>" id="">
                
            </div>
             
        </div>
        <div class=" card-footer">
            <button type="submit" class=" btn btn-sm btn-info btn-block">Generate Voucher</button>
        </div>
    </form>
</div>