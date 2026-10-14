<div class="card card-outline card-info">
  <div class="card-header">
    <h5 class="card-title">Create Purchase Order</h5>
  </div>
  <div class="card-body">   
    <form action="./controllers/purchase_order_controller.php" method="post" class="was-validated"> 

      <div class="row m-1">
        <div class="col-md-4 form-group">
          <label>Date</label>
          <input type="date" name="date" required class="form-control" max="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-4 form-group">
          <label>Expiry Date</label>
          <input type="date" name="expiry_date" required class="form-control" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-4 form-group">
          <label>Ref No</label>
          <input type="text" class="form-control" name="ref_no" value="<?= generateRefNo($conn, 'purchase_orders', 'PO/' . date('Y/')) ?>">
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
        <div class="col-md-6 form-group">
          <label>Supplier</label>
          <select name="supplier_id" required class="form-control select2-form select2bs4-form">
            <option value="">Select Below</option>
            <?php
              $suppliers = selectAllSubsidiaries($conn);
              foreach ($suppliers as $supplier) {
                echo "<option value='{$supplier['id']}'>{$supplier['name']}</option>";
              }
            ?>
          </select>
        </div>
        <div class="col-md-6 form-group">
          <label>VAT Tax Rate</label>
          <select name="tax_rate" id="tax_rate" required class="form-control" onchange="calculatePurchaseOrderTotal()">
            <option value="">Select below</option>
            <option value="18">18%</option>
            <option value="0">0%</option>
          </select>
        </div>
      </div>

      <div class="row m-1">
        <div class="col-md-6 form-group">
          <label for="project_id">Project</label>
          <select name="project_id" id="project_id" required class="select2-form select2bs4-form form-control">
            <option value="">Select Project</option>
            <option value="0" selected>Default Project</option>
            <?php
              $projects = listProjectsByStatus($conn, 'active');
              if ($projects && is_array($projects)) {
                foreach ($projects as $project) {
                  echo "<option value='".$project['id']."'>".$project['name']."</option>";
                }
              }
            ?>
          </select>
        </div>
      </div>

      <div class="row m-1">
        <div class="col-md-12 form-group">
          <label for="invoice_heading">Purchase Order Heading</label>
          <textarea name="invoice_heading" id="invoice_heading" class="form-control" placeholder="Enter purchase order heading (displayed on printed document)" required rows="3" style="resize: vertical;"></textarea>
          <small class="text-muted">This will be displayed on the printed purchase order</small>
        </div>
      </div>

      <div class="row m-1">
        <div class="col-md-12 my-2">
          <button type="button" class="btn btn-primary" onclick="addTableRow('purchase-order-table')">Add Row</button>
          <button type="button" class="btn btn-danger" onclick="removeTableRow('purchase-order-table')">Remove Row</button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-bordered" id="purchase-order-table">
          <thead class="bg-primary text-white">
            <tr>
              <th style="width: 22%;">Item Description</th>
              <th style="width: 13%;">Quantity</th>
              <th style="width: 12%;">Unit</th>
              <th style="width: 15%;">Price</th>
              <th style="width: 15%;">Amount</th>
              <th style="width: 13%;">Equiv Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr class="data-row">
              <td>
                <input type="text" name="description[]" class="form-control" required>
              </td>
              <td>
                <input type="text" name="quantity[]" class="form-control format-number" oninput="calculatePurchaseOrderTotal()" required>
              </td>
              <td>
                <select name="unit[]" required class="form-control select2-form select2bs4-form">
                  <option value="">Select below</option>
                  <?php
                    $units = selectAllUnit($conn);
                    if ($units && is_array($units)) {
                      foreach ($units as $unit) {
                        echo "<option value='$unit[id]'>$unit[name]</option>";
                      }
                    }
                  ?>
                </select>
              </td>
              <td>
                <input type="text" name="price[]" class="form-control format-number" oninput="calculatePurchaseOrderTotal()" required>
              </td>
              <td>
                <input type="text" name="amt[]" class="amount form-control" readonly>
              </td>
              <td>
                <input type="text" name="amount[]" class="total form-control" readonly>
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
            <td><label>Tax</label></td>
            <td><input name="tax_amount" id="tax" class="form-control" required readonly></td>
          </tr>
          <tr>
            <td><strong>Grand Total</strong></td>
            <td><input name="totalAmount" id="total" class="form-control font-weight-bold" required readonly></td>
          </tr>
        </table>
      </div>

      <div class="row m-1 mt-3">
        <div class="col-md-12 form-group">
          <label for="payment_terms">Terms & Conditions</label>
          <textarea name="payment_terms" id="payment_terms" class="form-control" placeholder="Enter payment terms" required rows="3" style="resize: vertical;"></textarea>
        </div>
      </div>

      <div class="card-footer">
        <button type="submit" class="btn btn-success btn-block" name="create_purchase_order">POST</button>
      </div>

    </form>
  </div>
</div>

<script>
function calculatePurchaseOrderTotal() {
    var prices = document.getElementsByName("price[]");
    var quantities = document.getElementsByName("quantity[]");
    var amounts = document.getElementsByName("amount[]");
    var amts = document.getElementsByName("amt[]");
    var currencyField = document.getElementById("curr_value");
    var currency = currencyField ? (parseFloat(currencyField.value.replace(/,/g, '')) || 1) : 1;
    for (var i = 0; i < prices.length; i++) {
        var quantity = parseFloat(quantities[i].value.replace(/,/g, '')) || 0;
        var price = parseFloat(prices[i].value.replace(/,/g, '')) || 0;
        var localAmount = quantity * price;
        var finalAmount = localAmount * currency;
        if (amts[i]) amts[i].value = localAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (amounts[i]) amounts[i].value = finalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    var inputs = document.getElementsByClassName("total");
    var rates = parseFloat(document.getElementById("tax_rate").value) || 0;
    var sum = 0;
    for (var i = 0; i < inputs.length; i++) {
        sum += parseFloat(inputs[i].value.replace(/,/g, '')) || 0;
    }
    var tax = (rates / 100) * sum;
    var jumla = sum + tax;
    document.getElementById("subtotal").value = sum.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById("tax").value = tax.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById("total").value = jumla.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>