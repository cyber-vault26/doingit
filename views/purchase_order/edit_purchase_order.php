<?php
if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "<script>window.location.href='./?page=pending_purchase_order_list';</script>";
    exit();
}

$purchase_order_id = (int) $_GET['id'];
$purchase_order = selectPurchaseOrderById($conn, $purchase_order_id);
$purchase_order_items = selectPurchaseOrderItemsByPurchaseOrderId($conn, $purchase_order_id);

if(!$purchase_order){
    echo "<script>window.location.href='./?page=pending_purchase_order_list';</script>";
    exit();
}
?>

<div class="card card-outline card-info">
  <div class="card-header">
    <h5 class="card-title">Edit Purchase Order - <?= htmlspecialchars($purchase_order['ref_no']) ?></h5>
  </div>
  <div class="card-body">   
    <form action="./controllers/purchase_order_controller.php" method="post" class="was-validated"> 
      <input type="hidden" name="purchase_order_id" value="<?= $purchase_order_id ?>">

      <div class="row m-1">
        <div class="col-md-4 form-group">
          <label>Date</label>
          <input type="date" name="date" required class="form-control" max="<?= $purchase_order['date_'] ?>" value="<?= $purchase_order['date_'] ?>">
        </div>
        <div class="col-md-4 form-group">
          <label>Expiry Date</label>
          <input type="date" name="expiry_date" required class="form-control" min="<?= $purchase_order['date_'] ?>" value="<?= $purchase_order['expiry_date'] ?>">
        </div>
        <div class="col-md-4 form-group">
          <label>Ref No</label>
          <input type="text" class="form-control" name="ref_no" value="<?= htmlspecialchars($purchase_order['ref_no']) ?>" required>
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
                $selected = $currency['id'] == $purchase_order['currency'] ? 'selected' : '';
                echo "<option value='{$currency['id']}' $selected>{$currency['name']}</option>";
              }
            ?>
          </select>
        </div>
        <div class="col-md-6 form-group">
          <label>Exchange Rate</label>
          <div id="currencyx">
            <input type="text" id="curr_value" name="curr" class="form-control format-number" value="<?= number_format($purchase_order['currency_rate'], 2) ?>" readonly>
          </div>
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
                $selected = $supplier['id'] == $purchase_order['supplier_id'] ? 'selected' : '';
                echo "<option value='{$supplier['id']}' $selected>{$supplier['name']}</option>";
              }
            ?>
          </select>
        </div>
        <div class="col-md-6 form-group">
          <label>VAT Tax Rate</label>
          <select name="tax_rate" id="tax_rate" required class="form-control" onchange="calculatePurchaseOrderTotal()">
            <option value="">Select below</option>
            <option value="18" <?= $purchase_order['tax_rate'] == 18 ? 'selected' : '' ?>>18%</option>
            <option value="0" <?= $purchase_order['tax_rate'] == 0 ? 'selected' : '' ?>>0%</option>
          </select>
        </div>
      </div>

      <div class="row m-1">
        <div class="col-md-6 form-group">
          <label for="project_id">Project</label>
          <select name="project_id" id="project_id" required class="select2-form select2bs4-form form-control">
            <option value="">Select Project</option>
            <option value="0" <?= $purchase_order['project_id'] == 0 ? 'selected' : '' ?>>Default Project</option>
            <?php
              $projects = listProjectsByStatus($conn, 'active');
              if ($projects && is_array($projects)) {
                foreach ($projects as $project) {
                  $selected = $project['id'] == $purchase_order['project_id'] ? 'selected' : '';
                  echo "<option value='".$project['id']."' $selected>".$project['name']."</option>";
                }
              }
            ?>
          </select>
        </div>
      </div>

      <div class="row m-1">
        <div class="col-md-12 form-group">
          <label for="invoice_heading">Purchase Order Heading</label>
          <textarea name="invoice_heading" id="invoice_heading" class="form-control" placeholder="Enter purchase order heading (displayed on printed document)" required rows="3" style="resize: vertical;"><?= htmlspecialchars($purchase_order['invoice_heading']) ?></textarea>
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
              <th style="width: 5%;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if(!empty($purchase_order_items) && is_array($purchase_order_items)){
                foreach($purchase_order_items as $index => $item){
            ?>
            <tr class="data-row">
              <td>
                <input type="hidden" name="existing_item_id[]" value="<?= $item['id'] ?>">
                <textArea name="description[]" class="form-control" required  >
                    <?= htmlspecialchars($item['item_name']) ?>
                </textArea>
                
              </td>
              <td>
                <input type="text" name="quantity[]" class="form-control format-number" oninput="calculatePurchaseOrderTotal()" required value="<?= number_format($item['quantity'], 2) ?>">
              </td>
              <td>
                <select name="unit[]" required class="form-control select2-form select2bs4-form">
                  <option value="">Select below</option>
                  <?php
                    $units = selectAllUnit($conn);
                    if ($units && is_array($units)) {
                      foreach ($units as $unit) {
                        $selected = $unit['id'] == $item['unit'] ? 'selected' : '';
                        echo "<option value='$unit[id]' $selected>$unit[name]</option>";
                      }
                    }
                  ?>
                </select>
              </td>
              <td>
                <input type="text" name="price[]" class="form-control format-number" oninput="calculatePurchaseOrderTotal()" required value="<?= number_format($item['price'], 2) ?>">
              </td>
              <td>
                <input type="text" name="amt[]" class="amount form-control" readonly value="<?= number_format($item['amount'], 2) ?>">
              </td>
              <td>
                <input type="text" name="amount[]" class="total form-control" readonly value="<?= number_format($item['eqv_amount'], 2) ?>">
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeThisRow(this)">×</button>
              </td>
            </tr>
            <?php
                }
            } else {
            ?>
            <tr class="data-row">
              <td>
                <input type="hidden" name="existing_item_id[]" value="">
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
              <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeThisRow(this)">×</button>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>

        <table class="table table-sm table-bordered" style="max-width: 400px; margin-left: auto;">
          <tr>
            <td><label>Sub Total</label></td>
            <td><input name="subtotal" id="subtotal" class="form-control" required readonly value="<?= number_format($purchase_order['subtotal'], 2) ?>"></td>
          </tr>
          <tr>
            <td><label>Tax</label></td>
            <td><input name="tax_amount" id="tax" class="form-control" required readonly value="<?= number_format($purchase_order['tax_amount'], 2) ?>"></td>
          </tr>
          <tr>
            <td><strong>Grand Total</strong></td>
            <td><input name="totalAmount" id="total" class="form-control font-weight-bold" required readonly value="<?= number_format($purchase_order['total_amount'], 2) ?>"></td>
          </tr>
        </table>
      </div>

      <div class="row m-1 mt-3">
        <div class="col-md-12 form-group">
          <label for="payment_terms"> Terms & Conditions</label>
          <textarea name="payment_terms" id="payment_terms" class="form-control" placeholder="Enter terms and conditions" required rows="3" style="resize: vertical;"><?= htmlspecialchars($purchase_order['payment_terms']) ?></textarea>
        </div>
      </div>

      <div class="card-footer">
        <button type="submit" class="btn btn-warning btn-block" name="update_purchase_order">Update Purchase Order</button>
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

function removeThisRow(button) {
    var row = button.closest('tr');
    var tbody = row.parentNode;
    if (tbody.children.length > 1) {
        row.remove();
        calculatePurchaseOrderTotal();
    }
}

function addTableRow(tableId) {
    var table = document.getElementById(tableId);
    var tbody = table.getElementsByTagName('tbody')[0];
    var newRow = tbody.children[0].cloneNode(true);
    
    // Clear input values
    var inputs = newRow.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].type === 'text' || inputs[i].type === 'hidden') {
            inputs[i].value = '';
        }
    }
    
    // Reset select to first option
    var selects = newRow.getElementsByTagName('select');
    for (var i = 0; i < selects.length; i++) {
        selects[i].selectedIndex = 0;
    }
    
    tbody.appendChild(newRow);
    
    // Reinitialize select2 for new elements
    $('.select2-form').select2();
    $('.select2bs4-form').select2({
        theme: 'bootstrap4'
    });
}

function removeTableRow(tableId) {
    var table = document.getElementById(tableId);
    var tbody = table.getElementsByTagName('tbody')[0];
    if (tbody.children.length > 1) {
        tbody.removeChild(tbody.lastChild);
        calculatePurchaseOrderTotal();
    }
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    calculatePurchaseOrderTotal();
    
    // Initialize select2
    $('.select2-form').select2();
    $('.select2bs4-form').select2({
        theme: 'bootstrap4'
    });
});
</script>