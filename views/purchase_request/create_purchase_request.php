<div class="card card-outline card-warning">
  <div class="card-header">
    <h5 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Create Purchase Request</h5>
  </div>
  <div class="card-body">
    <form action="./controllers/purchase_request_controller.php" method="post" class="was-validated">
      <div class="row m-1">
        <div class="col-md-6 form-group">
          <label>Date</label>
          <input type="date" name="date_" required class="form-control" max="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-6 form-group">
          <label>Ref No</label>
          <input type="text" class="form-control" name="ref_no" value="<?= generateRefNo($conn, 'purchase_requests', 'PR/' . date('Y/')) ?>" readonly>
        </div>
      </div>
      <div class="row m-1">
        <div class="col-md-6 form-group">
          <label>Currency</label>
          <select name="currency" required class="form-control select2bs4-form" onchange="selectCurrency(this.value)">
            <option value="">Select below</option>
            <?php
              $currencies = selectAllCurrency($conn);
              foreach ($currencies as $currency) {
                echo "<option value='{$currency['id']}'>{$currency['name']}</option>";
              }
            ?>
          </select>
        </div>
        <div class="col-md-6 form-group">
          <label>Exchange Rate</label>
          <div id="currencyx"></div>
        </div>
      </div>
      <div class="row m-1">
        <div class="col-md-12 form-group">
          <label for="project_id">Project <span class="text-danger">*</span></label>
          <select name="project_id" id="project_id" required class="select2-form select2bs4-form form-control">
            <option value="">— Select Project —</option>
            <option value="0">Default Project</option>
            <?php
              $projects = listProjectsByStatus($conn, 'active');
              if ($projects && is_array($projects)) {
                foreach ($projects as $project) {
                  echo "<option value='{$project['id']}'>{$project['name']}</option>";
                }
              }
            ?>
          </select>
        </div>
      </div>
      <div class="row m-1">
        <div class="col-md-12 form-group">
          <label for="request_heading">Purchase Request Heading <span class="text-danger">*</span></label>
          <textarea name="request_heading" id="request_heading" class="form-control" placeholder="Enter purchase request heading (displayed on printed document)" required rows="3" style="resize: vertical;"></textarea>
        </div>
      </div>
      <div class="row m-1">
        <div class="col-md-12 my-2">
          <button type="button" class="btn btn-primary" onclick="addTableRow('purchase-request-table')">Add Row</button>
          <button type="button" class="btn btn-danger" onclick="removeTableRow('purchase-request-table')">Remove Row</button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered" id="purchase-request-table">
          <thead class="bg-warning">
            <tr>
              <th style="width: 22%;">Item Description</th>
              <th style="width: 10%;">Project Ref</th>
              <th style="width: 13%;">Quantity</th>
              <th style="width: 10%;">Unit</th>
              <th style="width: 13%;">Price</th>
              <th style="width: 13%;">Amount</th>
              <th style="width: 14%;">Equiv Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr class="data-row">
              <td>
                <textarea name="description[]" class="form-control" placeholder="Material or item name" required rows="2" style="resize: vertical;"></textarea>
              </td>
              
              <td>
                <select name="project_id[]" required class="select2-form select2bs4-form form-control">
                  <option value="">— Select Project —</option>
                  <option value="0">Default Project</option>
                  <?php
                    $projects = listProjectsByStatus($conn, 'active');
                    if ($projects && is_array($projects)) {
                      foreach ($projects as $project) {
                        echo "<option value='{$project['id']}'>{$project['name']}</option>";
                      }
                    }
                  ?>
                </select>
              </td>
              
              <td>
                <input type="text" name="quantity[]" class="form-control format-number" oninput="calculatePurchaseRequestTotal()" placeholder="0" required>
              </td>
              <td>
                <select name="unit[]" required class="form-control select2-form select2bs4-form">
                  <option value="">Select unit</option>
                  <?php
                    $units = selectAllUnit($conn);
                    if ($units && is_array($units)) {
                      foreach ($units as $unit) {
                        echo "<option value='{$unit['id']}'>{$unit['name']}</option>";
                      }
                    }
                  ?>
                </select>
              </td>
              <td>
                <input type="text" name="price[]" class="form-control format-number" oninput="calculatePurchaseRequestTotal()" placeholder="0.00" required>
              </td>
              <td>
                <input type="text" name="amount[]" class="amount form-control" readonly>
              </td>
              <td>
                <input type="text" name="amount_eqv[]" class="total form-control" readonly>
              </td>
            </tr>
          </tbody>
        </table>
        <table class="table table-sm table-bordered" style="max-width: 400px; margin-left: auto;">
          <tr>
            <td><label>Sub Total</label></td>
            <td><input name="subtotal" id="subtotal" class="form-control" required readonly></td>
          </tr>
          <tr>
            <td><strong>Grand Total</strong></td>
            <td><input name="totalAmount" id="total" class="form-control font-weight-bold" required readonly></td>
          </tr>
        </table>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-warning btn-block text-dark font-weight-bold" name="create_purchase_request">
          <i class="fas fa-paper-plane mr-2"></i> SUBMIT REQUEST
        </button>
      </div>
    </form>
  </div>
</div>
<script>
function calculatePurchaseRequestTotal() {
    var prices = document.getElementsByName("price[]");
    var quantities = document.getElementsByName("quantity[]");
    var amounts = document.getElementsByName("amount[]");
    var amounts_eqv = document.getElementsByName("amount_eqv[]");
    var currencyField = document.getElementById("curr_value");
    var currency = currencyField ? (parseFloat(currencyField.value.replace(/,/g, '')) || 1) : 1;
    for (var i = 0; i < prices.length; i++) {
        var quantity = parseFloat(quantities[i].value.replace(/,/g, '')) || 0;
        var price = parseFloat(prices[i].value.replace(/,/g, '')) || 0;
        var localAmount = quantity * price;
        var finalAmount = localAmount * currency;
        if (amounts[i]) amounts[i].value = localAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (amounts_eqv[i]) amounts_eqv[i].value = finalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    var inputs = document.getElementsByClassName("total");
    var sum = 0;
    for (var i = 0; i < inputs.length; i++) {
        sum += parseFloat(inputs[i].value.replace(/,/g, '')) || 0;
    }
    document.getElementById("subtotal").value = sum.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById("total").value = sum.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
var currDiv = document.getElementById('currencyx');
if (currDiv) {
    new MutationObserver(function() {
        setTimeout(function() {
            calculatePurchaseRequestTotal();
        }, 100);
    }).observe(currDiv, {childList: true, subtree: true});
}
</script>