<div class="card card-outline card-info">
  <div class="card-header">
    <h5 class="card-title">Create Profoma</h5>
  </div>
  <div class="card-body">
    <form action="./controllers/profoma_controller.php" method="post" class="was-validated">
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
          <input type="text" class="form-control" name="ref_no" value="<?= generateRefNo($conn, 'profomas', 'PL/' . date('Y/')) ?>" readonly>
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
          <label>Customer</label>
          <select name="customer_id" required class="form-control select2-form select2bs4-form">
            <option value="">Select Below</option>
            <?php
              $suppliers = selectAllSubsidiaries($conn);
              foreach ($suppliers as $supplier) {
                echo "<option value='{$supplier['id']}'>{$supplier['name']}</option>";
              }
            ?>
          </select>
        </div>
      </div>
      <div class="row m-1">
        <div class="col-md-12 form-group">
          <label for="invoice_heading">Profoma Heading</label>
          <textarea name="invoice_heading" id="invoice_heading" class="form-control" placeholder="Enter invoice heading (displayed on printed invoice)" required rows="3" style="resize: vertical;"></textarea>
          <small class="text-muted">This will be displayed on the printed invoice</small>
        </div>
      </div>
      <div class="row m-1">
        <div class="col-md-12 my-2">
          <button type="button" class="btn btn-primary" onclick="addTableRow('profoma-table')">Add Row</button>
          <button type="button" class="btn btn-danger" onclick="removeTableRow('profoma-table'); calculateProfomaTotal();">Remove Row</button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered" id="profoma-table">
          <thead class="bg-primary text-white">
            <tr>
              <th style="width: 20%;">Item</th>
              <th style="width: 10%;">Quantity</th>
              <th style="width: 10%;">Unit</th>
              <th style="width: 12%;">Price</th>
              <th style="width: 12%;">Amount</th>
              <th style="width: 12%;">Equiv Amount</th>
              <th style="width: 8%;">VAT %</th>
              <th style="width: 12%;">VAT Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr class="data-row">
              <td>
                <select name="item[]" class="form-control select2-form select2bs4-form grn-item-select" required>
                    <option value="">Select below</option>
                    <?php
                    $minSubs = selectAllMinSub($conn);
                    if($minSubs && is_array($minSubs))
                       foreach($minSubs as $minSub):
                    ?>
                        <option value="<?= $minSub['id'] ?>"><?= $minSub['name'] ?></option>
                    <?php endforeach; ?>
                </select>
              </td>
              <td>
                <input type="text" name="quantity[]" class="form-control format-number" oninput="calculateProfomaTotal()" required>
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
                <input type="text" name="price[]" class="form-control format-number" oninput="calculateProfomaTotal()" required>
              </td>
              <td>
                <input type="text" name="amt[]" class="amount form-control" readonly>
              </td>
              <td>
                <input type="text" name="amount[]" class="total form-control" readonly>
              </td>
              <td>
                <select name="tax_rate[]" class="form-control vat-rate" onchange="calculateProfomaTotal()" required>
                  <option value="">Select Below</option>
                  <option value="0">0%</option>
                  <option value="18">18%</option>
                </select>
              </td>
              <td>
                <input type="text" name="vat_amount[]" class="vat-amount form-control" readonly>
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
            <td><label>Total VAT</label></td>
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
          <label for="payment_terms">Payment Terms</label>
          <textarea name="payment_terms" id="payment_terms" class="form-control" placeholder="Enter payment terms" required rows="3" style="resize: vertical;"></textarea>
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-success btn-block" name="create_profoma">POST</button>
      </div>
    </form>
  </div>
</div>
<script>
function calculateProfomaTotal() {
    var prices = document.getElementsByName("price[]");
    var quantities = document.getElementsByName("quantity[]");
    var amounts = document.getElementsByName("amount[]");
    var amts = document.getElementsByName("amt[]");
    var vatAmounts = document.getElementsByName("vat_amount[]");
    var vatRates = document.getElementsByName("tax_rate[]");
    var currencyField = document.getElementById("curr_value");
    var currency = currencyField ? (parseFloat(currencyField.value.replace(/,/g, '')) || 1) : 1;
    var totalVat = 0;
    var totalEquiv = 0;
    for (var i = 0; i < prices.length; i++) {
        var quantity = parseFloat(quantities[i].value.replace(/,/g, '')) || 0;
        var price = parseFloat(prices[i].value.replace(/,/g, '')) || 0;
        var localAmount = quantity * price;
        var finalAmount = localAmount * currency;
        var vatRate = parseFloat(vatRates[i].value) || 0;
        var vatAmt = (vatRate / 100) * finalAmount;
        if (amts[i]) amts[i].value = localAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (amounts[i]) amounts[i].value = finalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (vatAmounts[i]) vatAmounts[i].value = vatAmt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        totalVat += vatAmt;
        totalEquiv += finalAmount;
    }
    var grandTotal = totalEquiv + totalVat;
    document.getElementById("subtotal").value = totalEquiv.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById("tax").value = totalVat.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById("total").value = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
var currDiv = document.getElementById('currencyx');
if (currDiv) {
    new MutationObserver(function() {
        setTimeout(function() { calculateProfomaTotal(); }, 100);
    }).observe(currDiv, {childList: true, subtree: true});
}
// Fix 1: recalc when exchange rate is typed
$(document).on('input', '#curr_value', function() {
    calculateProfomaTotal();
});
</script>