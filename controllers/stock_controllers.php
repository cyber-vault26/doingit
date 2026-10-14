<?php
session_start();
if(!$_SESSION){
    echo "<script>window.history.back();</script>";
}
require_once "../configs.php";
require_once "../functions/transaction_functions.php";
require_once "../functions/stock_functions.php"; 
require_once "../functions/notification_functions.php";
require_once "../functions/voucher_functions.php";
require_once "../functions/subsidiary_functions.php";
require_once "../functions/min_sub_functions.php";
$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === "POST"){
    
    
if (isset($_POST['upload_items'])) {
    
     error_log('POST DATA: ' . json_encode($_POST));

    $itemNames    = $_POST['item_name']    ?? [];
    $packSizes    = $_POST['pack_size']    ?? [];
    $categories   = $_POST['category']    ?? [];
    $subCategories= $_POST['sub_category']?? [];
    $ledgers      = $_POST['ledger']       ?? [];

    $insertedCount = 0;
    $errorCount    = 0;

    for ($i = 0; $i < count($itemNames); $i++) {

        $itemName    = $conn->real_escape_string(trim($itemNames[$i]));
        $packSize    = $conn->real_escape_string(trim($packSizes[$i]    ?? ''));
        $category    = $conn->real_escape_string(trim($categories[$i]   ?? ''));
        $subCategory = $conn->real_escape_string(trim($subCategories[$i]?? ''));
        $ledgerName  = $conn->real_escape_string(trim($ledgers[$i]      ?? ''));

        if (empty($itemName)) continue;

        // -----------------------------------------------------------
        // STEP 1: Insert or get existing subsidiary (ledger)
        // subcategory = 'other', type = 'stock', ledger_id = 33
        // -----------------------------------------------------------
        $subId = null;

        $checkSql = "SELECT id FROM subsidiaries 
                     WHERE name = '$ledgerName' 
                     AND type = 'stock' 
                     AND deleted_at IS NULL 
                     LIMIT 1";
        $checkResult = $conn->query($checkSql);

        if ($checkResult && $checkResult->num_rows > 0) {
            // Already exists — reuse its id
            $subId = (int)$checkResult->fetch_assoc()['id'];
        } else {
            // Insert new subsidiary
            $insertSub = "INSERT INTO subsidiaries (ledger_id, name, subcategory, type, created_at)
                          VALUES (33, '$ledgerName', 'others', 'stock', NOW())";
            if ($conn->query($insertSub)) {
                $subId = (int)$conn->insert_id;
            }
        }

        if (!$subId) {
            $errorCount++;
            continue;
        }

        // -----------------------------------------------------------
        // STEP 2: Insert into min_subs linked to subsidiary
        // -----------------------------------------------------------
        $insertItem = "INSERT INTO min_subs 
                            (name, packaging_size, category, subcategory, sub_id, type, created_at)
                       VALUES 
                            ('$itemName', '$packSize', '$category', '$subCategory', $subId, 'stock', NOW())";

        if ($conn->query($insertItem)) {
            $insertedCount++;
        } else {
            $errorCount++;
        }
    }

    // -----------------------------------------------------------
    // Redirect with result message
    // -----------------------------------------------------------
    if ($errorCount === 0) {
        $_SESSION['success'] = "$insertedCount item(s) inserted successfully.";
    } else {
        $_SESSION['warning'] = "$insertedCount item(s) inserted. $errorCount failed.";
    }

    //header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
 
    
if(isset($_POST['add_manufacturing_order'])){
    $reference_no = $conn->real_escape_string(trim($_POST['reference_no']));
    $customer_id  = (int) $_POST['customer_id'];
    $date_        = $conn->real_escape_string(trim($_POST['date_']));
    $created_by   = (int) $_SESSION['userid'];
    $items        = $_POST['item'];
    $units        = $_POST['unit'];
    $quantities   = $_POST['quantity'];
    try {
        $conn->begin_transaction();
        $newOrder = insertManufacturingOrder($conn, $reference_no, $customer_id, $date_, $created_by);
        if(!is_numeric($newOrder) || $newOrder <= 0){
            throw new Exception($newOrder);
        }
        for($i = 0; $i < count($items); $i++){
            $item_id  = (int) $items[$i];
            $unit_id  = (int) $units[$i];
            $qty      = floatval(str_replace(',', '', $quantities[$i]));
            $insertItem = insertManufacturingOrderItem($conn, (int)$newOrder, $item_id, $unit_id, $qty, $created_by);
            if(!is_numeric($insertItem)){
                throw new Exception($insertItem);
            }
        }
        $conn->commit();
        setNotification('success', 'Manufacturing order created successfully');
        echo "<script>window.location.href='../?page=stock_manufacturing_orders';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }
}


if(isset($_POST['edit_manufacturing_order'])){
    $order_id     = (int) $_POST['order_id'];
    $customer_id  = (int) $_POST['customer_id'];
    $date_        = $conn->real_escape_string(trim($_POST['date_']));
    $updated_by   = (int) $_SESSION['userid'];
    $item_row_ids = $_POST['item_row_id'];
    $items        = $_POST['item'];
    $units        = $_POST['unit'];
    $quantities   = $_POST['quantity'];
    $original_ids = !empty($_POST['original_item_row_ids']) ? array_map('intval', explode(',', $_POST['original_item_row_ids'])) : [];
    $submitted_ids = array_map('intval', $item_row_ids);
    $deleted_ids   = array_diff($original_ids, array_filter($submitted_ids));
    try {
        $conn->begin_transaction();
        $updateOrder = updateManufacturingOrder($conn, $order_id, $customer_id, $date_, $updated_by);
        if($updateOrder !== true){
            throw new Exception($updateOrder);
        }
        foreach($deleted_ids as $deleted_id){
            $deleteItem = softDeleteManufacturingOrderItem($conn, $deleted_id, $updated_by);
            if($deleteItem !== true){
                throw new Exception($deleteItem);
            }
        }
        for($i = 0; $i < count($items); $i++){
            $item_id      = (int) $items[$i];
            $unit_id      = (int) $units[$i];
            $qty          = floatval(str_replace(',', '', $quantities[$i]));
            $item_row_id  = (int) $item_row_ids[$i];
            if($item_row_id > 0){
                $result = updateManufacturingOrderItem($conn, $item_row_id, $item_id, $unit_id, $qty, $updated_by);
            } else {
                $result = insertManufacturingOrderItem($conn, $order_id, $item_id, $unit_id, $qty, $updated_by);
            }
            if($result !== true && !is_numeric($result)){
                throw new Exception($result);
            }
        }
        $conn->commit();
        setNotification('success', 'Manufacturing order updated successfully');
        echo "<script>window.location.href='../?page=stock_manufacturing_orders';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }
}

if(isset($_POST['delete_manufacturing_order'])){
    $order_id   = (int) $_POST['order_id'];
    $deleted_by = (int) $_SESSION['userid'];
    $delete = softDeleteManufacturingOrder($conn, $order_id, $deleted_by);
    if($delete === true){
        setNotification('success', 'Manufacturing order deleted successfully');
        echo "<script>window.location.href='../?page=stock_manufacturing_orders';</script>";
    } else {
        echo $delete;
    }
}


if(isset($_POST['save_manufacturing_entry'])){
    $userid           = (int) $_SESSION['userid'];
    $batch_ref        = $conn->real_escape_string(trim($_POST['batch_ref']));
    $manufacture_date = $conn->real_escape_string(trim($_POST['manufacture_date']));
    $notes            = $conn->real_escape_string(trim($_POST['notes']));
    $order_ids        = isset($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : [];
    $batch            = time();
    $store_id         = 1;
    $supplier_id      = 0;
    $raw_items        = $_POST['raw_item'];
    $raw_units        = $_POST['raw_unit'];
    $raw_qtys         = $_POST['raw_qty'];
    $raw_base_qtys    = $_POST['raw_base_qty'];
    $raw_wastes       = $_POST['raw_waste'];
    $prod_items       = $_POST['prod_item'];
    $prod_units       = $_POST['prod_unit'];
    $prod_qtys        = $_POST['prod_qty'];
    $prod_base_qtys   = $_POST['prod_base_qty'];
    $prod_expiries    = $_POST['prod_expiry_date'];

    try {
        $conn->begin_transaction();

        for($i = 0; $i < count($raw_items); $i++){
            $item_id  = (int) $raw_items[$i];
            $unit_id  = (int) $raw_units[$i];
            $qty      = floatval(str_replace(',', '', $raw_qtys[$i]));
            $base_qty = floatval(str_replace(',', '', $raw_base_qtys[$i]));
            $wastage  = floatval(str_replace(',', '', $raw_wastes[$i]));
            $rawMat   = insertManufacturingRawMaterial($conn, $batch, $item_id, $unit_id, $qty, $base_qty, $wastage, $userid);
            if(!is_numeric($rawMat)){
                throw new Exception($rawMat);
            }
            
            $rawStockOut = insertStockMovement($conn, $batch_ref, null, null, $manufacture_date, $item_id, $qty, $base_qty, $unit_id, "out", $store_id, $supplier_id, $batch, null, $userid);
            if(!is_numeric($rawStockOut)){
                throw new Exception("Raw material stock-out failed: " . $rawStockOut);
            }
        }

        for($i = 0; $i < count($prod_items); $i++){
            $item_id    = (int) $prod_items[$i];
            $unit_id    = (int) $prod_units[$i];
            $qty        = floatval(str_replace(',', '', $prod_qtys[$i]));
            $base_qty   = floatval(str_replace(',', '', $prod_base_qtys[$i]));
            $expiry     = !empty($prod_expiries[$i]) ? $conn->real_escape_string($prod_expiries[$i]) : null;
            $stockMovement = insertStockMovement($conn, $batch_ref, null, null, $manufacture_date, $item_id, $qty, $base_qty, $unit_id, "in", $store_id, $supplier_id, $batch, $expiry, $userid);
            if(!is_numeric($stockMovement)){
                throw new Exception($stockMovement);
            }
        }

        foreach($order_ids as $order_id){
            $markDone = completeManufacturingOrder($conn, $order_id, $userid);
            if($markDone !== true){
                throw new Exception($markDone);
            }
        }

        $conn->commit();
        setNotification('success', 'Manufacturing entry saved successfully');
        echo "<script>window.location.href='../?page=stock_manufacturing_orders';</script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }
}


if(isset($_POST['add_stock_store'])){
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $created_by = (int) $_SESSION['userid'];
    $newStore = insertStockStore($conn, $name, $address, $created_by);
    if(is_numeric($newStore) && $newStore > 0){
        setNotification('success', 'Stock store added successfully');
        echo "<script>window.location.href='../?page=stock_store_list';</script>";
    } else {
        echo $newStore;
    }
}

if(isset($_POST['edit_stock_store'])){
    $id = (int) $_POST['store_id'];
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $updated_by = (int) $_SESSION['userid'];
    $updateStore = updateStockStore($conn, $id, $name, $address, $updated_by);
    if($updateStore === true){
        setNotification('success', 'Stock store updated successfully');
        echo "<script>window.location.href='../?page=stock_store_list';</script>";
    } else {
        echo $updateStore;
    }
}

if(isset($_POST['delete_stock_store'])){
    $id = (int) $_POST['store_id'];
    $deleted_by = (int) $_SESSION['userid'];
    $deleteStore = softDeleteStockStore($conn, $id, $deleted_by);
    if($deleteStore === true){
        setNotification('success', 'Stock store deleted successfully');
        echo "<script>window.location.href='../?page=stock_store_list';</script>";
    } else {
        echo $deleteStore;
    }
}    

if (isset($_POST['add_unit_conversion'])) {
    $item_id     = (int)   $_POST['item_id'];
    $unit_two_id = (int)   $_POST['unit_two_id'];
    $quantity    = (float) $_POST['quantity'];
    $created_by  = (int)   $_SESSION['userid'];
    $result = insertItemUnitConversion($conn, $item_id, $unit_two_id, $quantity, $created_by);
    if (is_numeric($result) && $result > 0) {
        setNotification('success', 'Unit conversion added successfully');
        echo "<script>window.location.href='../?page=stock_unit_conversions';</script>";
    } else {
        echo $result;
    }
}

if (isset($_POST['edit_unit_conversion'])) {
    $id          = (int)   $_POST['conversion_id'];
    $item_id     = (int)   $_POST['item_id'];
    $unit_two_id = (int)   $_POST['unit_two_id'];
    $quantity    = (float) $_POST['quantity'];
    $updated_by  = (int)   $_SESSION['userid'];
    $result = updateItemUnitConversion($conn, $id, $item_id, $unit_two_id, $quantity, $updated_by);
    if ($result === true) {
        setNotification('success', 'Unit conversion updated successfully');
        echo "<script>window.location.href='../?page=stock_unit_conversions';</script>";
    } else {
        echo $result;
    }
}

if(isset($_POST['delete_unit_conversion'])){
    $id         = (int) $_POST['conversion_id'];
    $deleted_by = (int) $_SESSION['userid'];

    $result = softDeleteItemUnitConversion($conn, $id, $deleted_by);
    if($result === true){
        setNotification('success', 'Unit conversion deleted successfully');
        echo "<script>window.location.href='../?page=stock_unit_conversions';</script>";
    } else {
        echo $result;
    }
}

if(isset($_POST['addgrn'])){
    $userid          = (int) $_SESSION['userid'];
    $ref             = $conn->real_escape_string($_POST['voucher_ref']);
    $po_id           = !empty($_POST['purchase_order_id']) ? (int) $_POST['purchase_order_id'] : null;
    $date            = $conn->real_escape_string($_POST['date']);
    $supplier        = (int) $_POST['supplier'];
    $store_id        = (int) $_POST['store_id'];
    $currency        = (int) $_POST['currency'];
    $curr_rate       = (float) str_replace(',', '', $_POST['curr']);
    $tax             = (float) str_replace(',', '', $_POST['tax']);
    $subtotal        = (float) str_replace(',', '', $_POST['subtotal']);
    $total           = (float) str_replace(',', '', $_POST['total']);
    $batch           = time();
    $items           = $_POST['item'];
    $quantities      = $_POST['quantity'];
    $base_quantities = $_POST['base_quantity'];
    $units           = $_POST['unit'];
    $prices          = $_POST['price'];
    $amounts_foreign = $_POST['eqv'];
    $amounts_base    = $_POST['amount'];
    $vat_rates       = $_POST['rates'];
    $vat_amounts     = $_POST['vat_amount'];
    try {
        $conn->begin_transaction();
        $combined_description = "";
        for($i = 0; $i < count($items); $i++){
            $item_id  = (int) $items[$i];
            $unit     = (int) $units[$i];
            $base_qty = (float) str_replace(',', '', $base_quantities[$i]);
            $price    = (float) str_replace(',', '', $prices[$i]);
            if($unit <= 0)     throw new Exception("Row " . ($i+1) . ": unit is required.");
            if($base_qty <= 0) throw new Exception("Row " . ($i+1) . ": base quantity must be greater than 0.");
            if($price <= 0)    throw new Exception("Row " . ($i+1) . ": price must be greater than 0.");
            $item_data = selectMinSubById($conn, $item_id);
            if(!$item_data || !is_array($item_data)) throw new Exception("Row " . ($i+1) . ": item not found.");
            if($i > 0) $combined_description .= ", ";
            $combined_description .= ($i+1) . ". " . $item_data['name'];
        }

        // DR: Stock/Inventory account, CR: Supplier (AP) account
        $dr_account = 1430;  
        $cr_account =  $supplier;  

        $transactionId = createTransaction($conn, $ref, 'purchases', $dr_account, $subtotal, $cr_account, $date, $combined_description, $currency, $curr_rate, 'draft', $userid, null, null, $store_id);
        if(!is_numeric($transactionId)) throw new Exception("Error creating transaction: " . $transactionId);

        $taxTransactionId = 0;
        if($tax > 0){
            $taxRef           = $ref . '/TAX';
            $taxTransactionId = createTransaction($conn, $taxRef, 'purchases', $cr_account, $tax, 18, $date, "VAT Input - " . $combined_description, $currency, $curr_rate, 'draft', $userid, null, null, $store_id);
            if(!is_numeric($taxTransactionId)) throw new Exception("Error creating tax transaction: " . $taxTransactionId);
        }

        $pvId = createPurchaseVoucher($conn, $date, $ref, $currency, $curr_rate, $total, $supplier, $transactionId, $taxTransactionId, $store_id, $dr_account, $userid);   
        if(!is_numeric($pvId)) throw new Exception("Error creating purchase voucher: " . $pvId);

        for($i = 0; $i < count($items); $i++){
            $item_id     = (int) $items[$i];
            $qty         = (float) str_replace(',', '', $quantities[$i]);
            $base_qty    = (float) str_replace(',', '', $base_quantities[$i]);
            $unit        = (int) $units[$i];
            $price       = (float) str_replace(',', '', $prices[$i]);
            $amt_foreign = (float) str_replace(',', '', $amounts_foreign[$i]);
            $amt_base    = (float) str_replace(',', '', $amounts_base[$i]);
            $vat_rate    = (float) $vat_rates[$i];
            $vat_amount  = (float) str_replace(',', '', $vat_amounts[$i]);

            $det = createPurchaseVoucherDetail($conn, $pvId, $transactionId, $qty, $unit, $price, $amt_foreign, $amt_base, 'Manunuzi Ya Bidhaa', $item_id, $vat_rate, $vat_amount, $userid);
            if(!is_numeric($det)) throw new Exception("Error creating detail row " . ($i+1) . ": " . $det);

            $stockMovement = insertStockMovement($conn, $ref, $pvId, $po_id, $date, $item_id, $qty, $price, $base_qty, $unit, "in", $store_id, $supplier, $batch, null, $userid, $currency, $curr_rate, $vat_rate);
            if(!is_numeric($stockMovement)) throw new Exception("Error creating stock movement row " . ($i+1) . ": " . $stockMovement);
        }

        $conn->commit();
        setNotification('success', 'Stock received successfully');
        echo "<script>window.location.href='../?page=record_stock_movement';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }
} 

if(isset($_POST['addstocktransfer'])){
    $userid          = (int) $_SESSION['userid'];
    $ref             = $conn->real_escape_string($_POST['voucher_ref']);
    $date            = $conn->real_escape_string($_POST['date']);
    $from_store_id   = (int) $_POST['from_store_id'];
    $to_store_id     = (int) $_POST['to_store_id'];
    $batch           = time();
    $items           = $_POST['item'];
    $quantities      = $_POST['quantity'];
    $base_quantities = $_POST['base_quantity'];
    $units           = $_POST['unit'];
    $prices          = $_POST['price'];
    try {
        $conn->begin_transaction();
        if($from_store_id === $to_store_id){
            throw new Exception("Transfer From and Transfer To cannot be the same store.");
        }
        for($i = 0; $i < count($items); $i++){
            $item     = (int) $items[$i];
            $qty      = floatval(str_replace(',', '', $quantities[$i]));
            $base_qty = floatval(str_replace(',', '', $base_quantities[$i]));
            $unit     = (int) $units[$i];
            $price    = floatval(str_replace(',', '', $prices[$i]));
            $outMovement = insertStockMovement($conn, $ref, null, null, $date, $item, $qty, $price, $base_qty, $unit, "transfer-out", $from_store_id, 0, $batch, null, $userid, 4, 1.00, 0, $to_store_id);
            if(!is_numeric($outMovement)){
                throw new Exception($outMovement);
            }
        }
        $conn->commit();
        setNotification('success', 'Stock transfer created and pending approval.');
        echo "<script>window.location.href='../?page=record_stock_movement';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }
}

if(isset($_POST['addgdn'])){
    $userid          = (int) $_SESSION['userid'];
    $ref             = $conn->real_escape_string($_POST['voucher_ref']);
    $date            = $conn->real_escape_string($_POST['date']);
    $customer        = 1394;
    $store_id        = (int) $_POST['store_id'];
    $currency        = (int) $_POST['currency'];
    $curr_rate       = (float) str_replace(',', '', $_POST['curr']);
    $tax             = (float) str_replace(',', '', $_POST['tax']);
    $subtotal        = (float) str_replace(',', '', $_POST['subtotal']);
    $total           = (float) str_replace(',', '', $_POST['total']);
    $batch           = time();
    $due_date        = date('Y-m-d', strtotime($date . ' + 15 days'));
    $items           = $_POST['item'];
    $quantities      = $_POST['quantity'];
    $base_quantities = $_POST['base_quantity'];
    $units           = $_POST['unit'];
    $prices          = $_POST['price'];
    $amounts_foreign = $_POST['eqv'];
    $amounts_base    = $_POST['amount'];
    $vat_rates       = $_POST['rates'];
    $vat_amounts     = $_POST['vat_amount'];
    try {
        $conn->begin_transaction();
        $combined_description = "";
        for($i = 0; $i < count($items); $i++){
            $item_id  = (int) $items[$i];
            $unit     = (int) $units[$i];
            $base_qty = (float) str_replace(',', '', $base_quantities[$i]);
            $price    = (float) str_replace(',', '', $prices[$i]);
            if($unit <= 0)     throw new Exception("Row " . ($i + 1) . ": unit is required.");
            if($base_qty <= 0) throw new Exception("Row " . ($i + 1) . ": base quantity must be greater than 0.");
            if($price <= 0)    throw new Exception("Row " . ($i + 1) . ": price must be greater than 0.");
            $item_data = selectMinSubById($conn, $item_id);
            if(!$item_data || !is_array($item_data)) throw new Exception("Row " . ($i + 1) . ": item not found.");
            if($i > 0) $combined_description .= ", ";
            $combined_description .= ($i + 1) . ". " . $item_data['name'];
        }
        $transactionId = createTransaction($conn, $ref, 'sales', $customer, $subtotal, 1395, $date, $combined_description, $currency, $curr_rate, 'draft', $userid, null, null, $store_id);
        if(!is_numeric($transactionId)) throw new Exception("Error creating transaction: " . $transactionId);
        $taxTransactionId = 0;
        if($tax > 0){
            $taxRef           = $conn->real_escape_string($ref . '/TAX');
            $taxTransactionId = createTransaction($conn, $taxRef, 'sales', $customer, $tax, 17, $date, "VAT Output - " . $combined_description, $currency, $curr_rate, 'draft', $userid, null, null, $store_id);
            if(!is_numeric($taxTransactionId)) throw new Exception("Error creating tax transaction: " . $taxTransactionId);
        }
        $svId = createSalesVoucher($conn, $date, $ref, $currency, $curr_rate, $total, $customer, $transactionId, $taxTransactionId, 1395, $store_id, $userid, "Mauzo Ya Bidhaa", $due_date);
        if(!is_numeric($svId)) throw new Exception("Error creating sales voucher: " . $svId);
        for($i = 0; $i < count($items); $i++){
            $item_id     = (int) $items[$i];
            $item_desc   = "Mauzo Ya Bidhaa";
            $qty         = (float) str_replace(',', '', $quantities[$i]);
            $base_qty    = (float) str_replace(',', '', $base_quantities[$i]);
            $unit        = (int) $units[$i];
            $price       = (float) str_replace(',', '', $prices[$i]);
            $amt_foreign = (float) str_replace(',', '', $amounts_foreign[$i]);
            $amt_base    = (float) str_replace(',', '', $amounts_base[$i]);
            $vat_rate    = (float) $vat_rates[$i];
            $vat_amount  = (float) str_replace(',', '', $vat_amounts[$i]);
            $det = createSalesVoucherDetail($conn, $svId, $transactionId, $qty, $unit, $price, $amt_foreign, $amt_base, $item_desc, $item_id, $vat_rate, $vat_amount, $userid);
            if(!is_numeric($det)) throw new Exception("Error creating detail row " . ($i + 1) . ": " . $det);
            $stockMovement = insertStockMovement($conn, $ref, $svId, null, $date, $item_id, $qty, $price, $base_qty, $unit, "out", $store_id, $customer, $batch, null, $userid, $currency, $curr_rate, $vat_rate);
            if(!is_numeric($stockMovement)) throw new Exception("Error creating stock movement row " . ($i + 1) . ": " . $stockMovement);
        }
        $conn->commit();
        setNotification('success', 'Stock issued successfully');
        echo "<script>window.location.href='../?page=record_stock_movement';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }
}
 
if(isset($_POST['upload_stock'])){
    $userid = (int) $_SESSION['userid'];
    $type   = $_POST['type'] ?? '';
    $batch  = time();
    $ref    = 'UPLOAD-' . strtoupper($type) . '-' . $batch;
    $price=0.00;
    $item_ids   = $_POST['item_id']   ?? [];
    $base_units = $_POST['base_unit'] ?? [];
    $quantities = $_POST['quantity']  ?? [];
    $dates      = $_POST['date']      ?? [];

    try {
        $conn->begin_transaction();

        if($type === 'grn'){
            $store_id = (int) $_POST['store'];
            for($i = 0; $i < count($item_ids); $i++){
                $item     = (int) $item_ids[$i];
                $unit     = (int) $base_units[$i];
                $qty      = (float) $quantities[$i];
                $date     = $conn->real_escape_string($dates[$i]);
                $movement = insertStockMovement($conn, $ref, null, null, $date, $item, $qty, $price, $qty,$unit, "in", $store_id, 0, $batch, null, $userid);
                 
                if(!is_numeric($movement)) throw new Exception("GRN row " . ($i + 1) . ": " . $movement);
            }
        } elseif($type === 'gdn'){
            $store_id = (int) $_POST['store'];
            for($i = 0; $i < count($item_ids); $i++){
                $item     = (int) $item_ids[$i];
                $unit     = (int) $base_units[$i];
                $qty      = (float) $quantities[$i];
                $date     = $conn->real_escape_string($dates[$i]);
                $movement = insertStockMovement($conn, $ref, null, null, $date, $item, $qty,$price, $qty, $unit, "out", $store_id, 0, $batch, null, $userid);
                if(!is_numeric($movement)) throw new Exception("GDN row " . ($i + 1) . ": " . $movement);
            }
        } elseif($type === 'transfer'){
            $from_store_id = (int) $_POST['from_store'];
            $to_store_id   = (int) $_POST['to_store'];
            if($from_store_id === $to_store_id) throw new Exception("From Store and To Store cannot be the same.");
            for($i = 0; $i < count($item_ids); $i++){
                $item = (int) $item_ids[$i];
                $unit = (int) $base_units[$i];
                $qty  = (float) $quantities[$i];
                $date = $conn->real_escape_string($dates[$i]);
                $out  = insertStockMovement($conn, $ref, null, null, $date, $item, $qty, $qty, $unit, "out", $from_store_id, 0, $batch, null, $userid);
                if(!is_numeric($out)) throw new Exception("Transfer OUT row " . ($i + 1) . ": " . $out);
                $in   = insertStockMovement($conn, $ref, null, null, $date, $item, $qty, $qty, $unit, "in", $to_store_id, 0, $batch, null, $userid);
                if(!is_numeric($in)) throw new Exception("Transfer IN row " . ($i + 1) . ": " . $in);
            }
        } else {
            throw new Exception("Unknown transaction type: " . htmlspecialchars($type));
        }

        $conn->commit();
        setNotification('success', 'Stock uploaded successfully');
        echo "<script>window.location.href='../?page=stock_uploads_form';</script>";

    } catch(Exception $e){
        $conn->rollback();
        echo "<div class='alert alert-danger'><strong>Error:</strong> " . $e->getMessage() . "</div>";
    }
}

if(isset($_POST['editgrn'])){
    $userid     = (int) $_SESSION['userid'];
    $batch      = (int) $_POST['batch'];
    $type       = $conn->real_escape_string($_POST['type']);
    $ref        = $conn->real_escape_string($_POST['voucher_ref']);
    $date       = $conn->real_escape_string($_POST['date']);
    $supplier   = (int) $_POST['supplier'];
    $store_id   = (int) $_POST['store_id_select'];
    $po_id      = isset($_POST['purchase_order_id']) && $_POST['purchase_order_id'] !== '' ? (int) $_POST['purchase_order_id'] : null;
    $stock_ids  = $_POST['stock_id'];
    $is_new     = $_POST['is_new'];
    $items      = $_POST['item'];
    $units      = $_POST['unit'];
    $quantities = $_POST['quantity'];
    $base_qtys  = $_POST['base_quantity'];
    $prices     = $_POST['price'];
    $delete_ids = !empty($_POST['delete_ids']) ? array_filter(explode(',', $_POST['delete_ids'])) : [];
    $redirect   = "edit_grn&batch=$batch&type=$type";

    try {
        $conn->begin_transaction();

        for($i = 0; $i < count($stock_ids); $i++){
            $unit     = (int) $units[$i];
            $base_qty = floatval(str_replace(',', '', $base_qtys[$i]));
            if($unit <= 0){
                throw new Exception("Row " . ($i + 1) . ": unit is required.");
            }
            if($base_qty <= 0){
                throw new Exception("Row " . ($i + 1) . ": base quantity must be greater than 0.");
            }
        }

        if(!empty($delete_ids)){
            foreach($delete_ids as $del_id){
                $del_id = (int) trim($del_id);
                if($del_id <= 0) continue;
                $result = softDeleteStock($conn, $del_id, $userid);
                if($result !== true){
                    throw new Exception("Delete failed: " . $result);
                }
            }
        }

        for($i = 0; $i < count($stock_ids); $i++){
            $stock_id = (int) $stock_ids[$i];
            $item     = (int) $items[$i];
            $unit     = (int) $units[$i];
            $qty      = floatval(str_replace(',', '', $quantities[$i]));
            $base_qty = floatval(str_replace(',', '', $base_qtys[$i]));
            $price    = floatval(str_replace(',', '', $prices[$i]));

            if((int) $is_new[$i] === 1 || $stock_id === 0){
                $result = insertStockMovement($conn, $ref, null, $po_id, $date, $item, $qty, $price, $base_qty, $unit, $type, $store_id, $supplier, $batch, null, $userid);
                if(!is_numeric($result)){
                    throw new Exception("Insert failed: " . $result);
                }
            } else {
                $result = updateStockMovement($conn, $stock_id, $po_id, $date, $item, $qty, $base_qty, $price, $unit, $store_id, $supplier, $userid);
                if($result !== true){
                    throw new Exception("Update failed: " . $result);
                }
            }
        }

        $conn->commit();
        setNotification('success', 'Stock updated successfully');
        echo "<script>window.location.href='../?page=$redirect';</script>";

    } catch(Exception $e){
        $conn->rollback();
        echo $e->getMessage();
    }
}


if(isset($_POST['editgdn'])){
    $userid          = (int) $_SESSION['userid'];
    $svId            = (int) $_POST['voucher_id'];
    $batch           = (int) $_POST['batch'];
    $ref             = $conn->real_escape_string($_POST['voucher_ref']);
    $date            = $conn->real_escape_string($_POST['date']);
    $due_date        = date('Y-m-d', strtotime($date . ' + 15 days'));
    $customer        = 1394;
    $store_id        = (int) $_POST['store_id_select'];
    $currency        = (int) $_POST['currency'];
    $curr_rate       = (float) str_replace(',', '', $_POST['curr']);
    $tax             = (float) str_replace(',', '', $_POST['tax']);
    $subtotal        = (float) str_replace(',', '', $_POST['subtotal']);
    $total           = (float) str_replace(',', '', $_POST['total']);
    $items           = $_POST['item'];
    $quantities      = $_POST['quantity'];
    $base_quantities = $_POST['base_quantity'];
    $units           = $_POST['unit'];
    $prices          = $_POST['price'];
    $amounts_foreign = $_POST['eqv'];
    $amounts_base    = $_POST['amount'];
    $vat_rates       = $_POST['rates'];
    $vat_amounts     = $_POST['vat_amount'];
    $stock_ids       = $_POST['stock_id'];
    $is_new          = $_POST['is_new'];
    $delete_ids      = !empty($_POST['delete_ids']) ? array_filter(explode(',', $_POST['delete_ids'])) : [];
    $voucher         = selectSalesVoucherById($conn, $svId);
    if(!$voucher || !is_array($voucher)) die("Voucher not found.");
    $transactionId    = (int) $voucher['transaction_id'];
    $taxTransactionId = (int) $voucher['tax_transaction_id'];
    try {
        $conn->begin_transaction();
        $combined_description = "";
        $item_cache           = [];
        for($i = 0; $i < count($items); $i++){
            $unit     = (int) $units[$i];
            $base_qty = (float) str_replace(',', '', $base_quantities[$i]);
            $price    = (float) str_replace(',', '', $prices[$i]);
            if($unit <= 0)     throw new Exception("Row " . ($i + 1) . ": unit is required.");
            if($base_qty <= 0) throw new Exception("Row " . ($i + 1) . ": base quantity must be greater than 0.");
            if($price <= 0)    throw new Exception("Row " . ($i + 1) . ": price must be greater than 0.");
            $item_id   = (int) $items[$i];
            $item_data = selectMinSubById($conn, $item_id);
            if(!$item_data || !is_array($item_data)) throw new Exception("Row " . ($i + 1) . ": item not found.");
            $item_cache[$i] = $item_data;
            if($i > 0) $combined_description .= ", ";
            $combined_description .= ($i + 1) . ". " . $item_data['name'];
        }
        if($tax > 0){
            $taxRef         = $conn->real_escape_string($ref . '/TAX/' . $svId);
            $taxDescription = "VAT Output-Sales - " . $combined_description;
            if($taxTransactionId > 0){
                $updatedTax = updateTransaction($conn, $taxTransactionId, $taxRef, 'sales', $customer, $tax, 17, $date, '', $taxDescription, $currency, $curr_rate, $store_id, $userid, null);
                if(!$updatedTax) throw new Exception("Failed to update tax transaction.");
            } else {
                $newTaxId = createTransaction($conn, $taxRef, 'sales', $customer, $tax, 17, $date, $taxDescription, $currency, $curr_rate, 'draft', $userid, null, null, $store_id);
                if(!is_numeric($newTaxId)) throw new Exception("Error creating tax transaction: " . $newTaxId);
                $taxTransactionId = $newTaxId;
            }
        } else {
            if($taxTransactionId > 0){
                $deleted = softDeleteTransaction($conn, $taxTransactionId, $userid);
                if($deleted !== true) throw new Exception("Failed to delete tax transaction: " . $deleted);
                $taxTransactionId = 0;
            }
        }
        $updated = updateSalesVoucher($conn, $svId, $date, $ref, $currency, $curr_rate, $total, $customer, $transactionId, $taxTransactionId, 1395, $store_id, $userid, 'Mauzo ya Bidhaa', $due_date);
        if(!$updated) throw new Exception("Failed to update sales voucher.");
        $updatedTxn = updateTransaction($conn, $transactionId, $ref, 'sales', $customer, $subtotal, 1395, $date, '', $combined_description, $currency, $curr_rate, $store_id, $userid, null);
        if(!$updatedTxn) throw new Exception("Failed to update transaction.");
        $deletedDetails = softDeleteSalesVoucherDetailsBySvId($conn, $svId, $userid);
        if($deletedDetails !== true) throw new Exception("Failed to delete voucher details: " . $deletedDetails);
        if(!empty($delete_ids)){
            foreach($delete_ids as $del_id){
                $del_id = (int) trim($del_id);
                if($del_id <= 0) continue;
                $result = softDeleteStock($conn, $del_id, $userid);
                if($result !== true) throw new Exception("Failed to delete stock row: " . $result);
            }
        }
        for($i = 0; $i < count($items); $i++){
            $item_id     = (int) $items[$i];
            $item_desc   = ($i + 1) . ". " . $item_cache[$i]['name'];
            $qty         = (float) str_replace(',', '', $quantities[$i]);
            $base_qty    = (float) str_replace(',', '', $base_quantities[$i]);
            $unit        = (int) $units[$i];
            $price       = (float) str_replace(',', '', $prices[$i]);
            $amt_foreign = (float) str_replace(',', '', $amounts_foreign[$i]);
            $amt_base    = (float) str_replace(',', '', $amounts_base[$i]);
            $vat_rate    = (float) $vat_rates[$i];
            $vat_amount  = (float) str_replace(',', '', $vat_amounts[$i]);
            $stock_id    = (int) $stock_ids[$i];
            $det = createSalesVoucherDetail($conn, $svId, $transactionId, $qty, $unit, $price, $amt_foreign, $amt_base, $item_desc, $item_id, $vat_rate, $vat_amount, $userid);
            if(!is_numeric($det)) throw new Exception("Error creating detail row " . ($i + 1) . ": " . $det);
            if((int) $is_new[$i] === 1 || $stock_id === 0){
                $result = insertStockMovement($conn, $ref, $svId, null, $date, $item_id, $qty, $price, $base_qty, $unit, 'out', $store_id, $customer, $batch, null, $userid, $currency, $curr_rate, $vat_rate);
                if(!is_numeric($result)) throw new Exception("Error inserting stock row " . ($i + 1) . ": " . $result);
            } else {
                $result = updateStockMovement($conn, $stock_id, null, $date, $item_id, $qty, $base_qty, $price, $unit, $store_id, $customer, $userid, $currency, $curr_rate, $vat_rate);
                if($result !== true) throw new Exception("Error updating stock row " . ($i + 1) . ".");
            }
        }
        $conn->commit();
        setNotification('success', 'GDN updated successfully');
        echo "<script>window.location.href='../?page=edit_gdn&batch=$batch&type=out';</script>";
    } catch(Exception $e){
        $conn->rollback();
        echo $e->getMessage();
    }
}

if(isset($_POST['delete_stock']) && isset($_POST['selected_batches'])){
    $userid  = (int) $_SESSION['userid'];
    $batches = $_POST['selected_batches'];
    $conn->begin_transaction();
    try {
        foreach($batches as $batch){
            $batch = (int) $batch;
            $meta  = selectStockBatchMeta($conn, $batch);
            if(!$meta) throw new Exception("Batch $batch not found.");
            if($meta['type'] === 'out'){
                $voucherId = (int) $meta['voucher_id'];
                if($voucherId <= 0) throw new Exception("Batch $batch has no linked invoice. Cannot delete.");
                $result = softDeleteSalesVoucher($conn, $voucherId, $userid);
                if($result !== true) throw new Exception("Failed to delete invoice for batch $batch: " . $result);
            }
            $result = softDeleteStockByBatch($conn, $batch, $userid);
            if($result !== true) throw new Exception("Failed to delete stock batch $batch: " . $result);
        }
        $conn->commit();
        echo "SUCCESS";
    } catch(Exception $e){
        $conn->rollback();
        echo $e->getMessage();
    }
}

if(isset($_POST['review_stock']) && isset($_POST['selected_batches'])){
    $batches = $_POST['selected_batches'];
    foreach($batches as $batch){
        $batch  = (int) $batch;
        $result = reviewStock($conn, $batch, $_SESSION['userid']);
        if($result !== true){ echo $result; exit; }
    }
    echo "SUCCESS";
}

if(isset($_POST['approve_stock']) && isset($_POST['selected_batches'])){
    $batches = $_POST['selected_batches'];
    foreach($batches as $batch){
        $batch  = (int) $batch;
        $result = approveStock($conn, $batch, $_SESSION['userid']);
        if($result !== true){ echo $result; exit; }
    }
    echo "SUCCESS";
}

if(isset($_POST['reject_stock']) && isset($_POST['selected_batches'])){
    $reason  = $conn->real_escape_string($_POST['rejection_reason']);
    $batches = $_POST['selected_batches'];
    foreach($batches as $batch){
        $batch  = (int) $batch;
        $result = rejectStock($conn, $batch, $_SESSION['userid'], $reason);
        if($result !== true){ echo $result; exit; }
    }
    echo "SUCCESS";
}


 
   }
  
?>