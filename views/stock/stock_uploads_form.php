<div class="card card-primary">
    <div class="card-header">
        <h4 class="card-title">Stock Upload Form</h4>
    </div>
    <form action="./?page=processes_stock_upload_data" method="post" enctype="multipart/form-data" class="was-validated">
        <div class="card-body">

            <div class="form-group">
                <label class="font-weight-bold">Transaction Type</label>
                <select name="type" class="form-control select2-form select2bs4-form" required>
                    <option value="">— Select Below —</option>
                    <option value="grn">GRN — Goods Received Note</option>
                    <option value="gdn">GDN — Goods Delivery Note</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>

             

            <div class="form-group">
                <label class="font-weight-bold">Select File</label>
                <input type="file" name="file" class="form-control" required accept=".csv, .xls, .xlsx">
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-block btn-sm">
                <i class="fas fa-upload"></i> Upload & Process
            </button>
        </div>
    </form>
</div>


<!-- Format Instructions -->
<div class="alert mt-3" style="background-color: #162171; color: #fff;">
    <h5><i class="fas fa-info-circle"></i> Excel File Format Required</h5>
    <p><strong>Please ensure your Excel file follows this exact format:</strong></p>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Column A</th>
                    <th>Column B</th>
                    <th>Column C</th>
                    <th>Column D</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-warning">
                    <td><strong>#</strong></td>
                    <td><strong>Name</strong></td>
                    <td><strong>Quantity</strong></td>
                    <td><strong>Date</strong></td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Cement 50kg</td>
                    <td>100</td>
                    <td>2025-04-01</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Steel Rod 12mm</td>
                    <td>250</td>
                    <td>2025-04-01</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <h6><strong>Column Descriptions:</strong></h6>
            <ul class="list-unstyled">
                <li><i class="fas fa-angle-right mr-1"></i><strong>Column A (#):</strong> Row number — will be ignored</li>
                <li><i class="fas fa-angle-right mr-1"></i><strong>Column B (Name):</strong> Item name — must match system records exactly</li>
                <li><i class="fas fa-angle-right mr-1"></i><strong>Column C (Quantity):</strong> Quantity in numbers only</li>
                <li><i class="fas fa-angle-right mr-1"></i><strong>Column D (Date):</strong> Date in YYYY-MM-DD format</li>
            </ul>
        </div>
    </div>

    <div class="alert alert-success mt-3">
        <h6><strong>Important Notes:</strong></h6>
        <ul class="mb-0">
            <li>Row 1 should contain headers — it will be skipped during processing</li>
            <li>Column A (#) will be ignored during processing</li>
            <li>Item names must exactly match existing records in the system</li>
            <li>Quantity must be numbers only — no commas or units</li>
            <li>Date must be in <strong>YYYY-MM-DD</strong> format e.g. 2025-04-01</li>
            <li>Supported file formats: .xlsx, .xls, .csv</li>
        </ul>
    </div>

    <div class="col-md-12 mt-3">
        <ul class="list-unstyled">
            <li style="color: #fff; font-weight: bold;">
                Not sure about the format? 
                <a href="./processes/printings/stock_uploading_template.php" style="color: #20c997; text-decoration: underline;" target="_blank">
                    Click here
                </a> 
                to download an example Excel file showing the correct format.
            </li>
        </ul>
    </div>
</div>