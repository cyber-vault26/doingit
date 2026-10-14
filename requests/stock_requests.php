<?php
    require_once "../configs.php";
    require_once "../functions/subsidiary_functions.php";
    require_once "../functions/stock_functions.php";
    require_once "../functions/min_sub_functions.php";
    require_once "../functions/utilities_functions.php";
    $conn = openConn();
if($_SERVER['REQUEST_METHOD'] === 'GET'){
 if(isset($_GET['get_stock_movement'])){
    $type   = $conn->real_escape_string($_GET['type']);
    $date1  = isset($_GET['date1'])  && $_GET['date1']  !== '' ? $conn->real_escape_string($_GET['date1'])  : null;
    $date2  = isset($_GET['date2'])  && $_GET['date2']  !== '' ? $conn->real_escape_string($_GET['date2'])  : null;
    $store  = isset($_GET['store'])  && $_GET['store']  !== '' ? (int) $_GET['store']  : null;
    $status = isset($_GET['status']) && $_GET['status'] !== '' ? $conn->real_escape_string($_GET['status']) : null;
    $stocks = selectStockBatchesByType($conn, $type, $date1, $date2, $store, $status);
    $label  = $type === 'in' ? 'Supplier' : 'Customer';
    ?>

     

    <div class="bulk-actions mb-2" style="display:none;">
        <?php if($status == 'pending'): ?>
        <button class="btn btn-sm btn-info"  onclick="bulkStockAction('review_stock')">Review Selected</button>
        <button class="btn btn-sm btn-danger" onclick="bulkStockAction('delete_stock')">Delete Selected</button>
        <?php endif; ?>
        <?php if($status == 'reviewed'): ?>
        <button class="btn btn-sm btn-warning" onclick="bulkStockAction('approve_stock')">Approve Selected</button>
        <button class="btn btn-sm btn-danger"  onclick="$('#bulkRejectModal').modal('show')">Reject Selected</button>
        <button class="btn btn-sm btn-danger"  onclick="bulkStockAction('delete_stock')">Delete Selected</button>
        <?php endif; ?>
        <?php if($status == 'rejected'): ?>
        <button class="btn btn-sm btn-danger"  onclick="bulkStockAction('delete_stock')">Delete Selected</button>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
    <table class="table table-sm table-search table-striped table-hover">
        <thead class="bg-primary text-white">
            <tr>
                <?php if($status != 'approved'): ?>
                <td style="cursor:pointer;"><input type="checkbox" class="select-all" onclick="toggleAll(this)" style="cursor:pointer;"></td>
                <?php endif; ?>
                <th style="width:40px;">#</th>
                <th>Ref No</th>
                <th>Date</th>
                <th><?= $label ?></th>
                <th>Store</th>
                <th>Status</th>
                <th style="width:110px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if($stocks && is_array($stocks)){
            $num = 0;
            $colspan = $status != 'approved' ? 8 : 7;
            foreach($stocks as $b){
                $num++;
                $party     = selectSubsidiaryById($conn, $b['supplier_id']);
                $partyName = $party ? htmlspecialchars($party['name']) : '-';
                $storeData = selectStockStoreById($conn, $b['store_id']);
                $storeName = $storeData ? htmlspecialchars($storeData['name']) : '-';
                $badgeClass = match($b['status']) {
                    'approved' => 'badge-success',
                    'rejected' => 'badge-danger',
                    'reviewed' => 'badge-info',
                    default    => 'badge-warning'
                };
                if($b['type'] === 'in'){
                    $editPage = 'edit_grn';
                } elseif($b['type'] === 'out'){
                    $editPage = 'edit_gdn';
                } else {
                    $editPage = 'edit_tranfered_stocks';
                }

                $checkboxCol = $status != 'approved'
                    ? "<td style='cursor:pointer;'><input type='checkbox' class='row-select' value='{$b['batch']}' onchange='toggleRow(this)' style='cursor:pointer;'></td>"
                    : '';

                $actionCol = "<a class='btn btn-outline-info btn-sm' href='../?page=preview_stock&batch={$b['batch']}&type={$b['type']}'><i class='fas fa-eye'></i></a>";

                if($status == 'pending' || $status == 'reviewed' || $status == 'approved'){
                    $actionCol .= " <a class='btn btn-outline-warning btn-sm' href='../?page={$editPage}&batch={$b['batch']}&type={$b['type']}'><i class='fas fa-edit'></i></a>";
                }

                echo "<tr>
                    {$checkboxCol}
                    <td>{$num}</td>
                    <td><strong>" . htmlspecialchars($b['ref_no']) . "</strong></td>
                    <td>" . htmlspecialchars($b['date_']) . "</td>
                    <td>{$partyName}</td>
                    <td>{$storeName}</td>
                    <td><span class='badge {$badgeClass}'>" . ucfirst(htmlspecialchars($b['status'])) . "</span></td>
                    <td>{$actionCol}</td>
                </tr>";
            }
        } else {
            $colspan = $status != 'approved' ? 8 : 7;
            echo "<tr><td colspan='{$colspan}' class='text-center text-muted py-3'>No records found</td></tr>";
        }
        ?>
        </tbody>
        <tfoot class="bg-primary text-white">
            <tr>
                <?php if($status != 'approved'): ?> 
                <th></th>
                <?php endif; ?>
                <th>#</th>
                <th>Ref No</th>
                <th>Date</th>
                <th><?= $label ?></th>
                <th>Store</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </tfoot>
    </table>
    </div>

     
    <?php
}
if(isset($_GET['get_manufacturing_order_items'])){
    $order_id = (int) $_GET['order_id'];
    $items    = selectManufacturingOrderItems($conn, $order_id);
    $minSubs  = selectAllMinSub($conn);
    $units    = selectAllUnit($conn);
    $original_ids = [];
    if($items && is_array($items)){
        foreach($items as $item){
            $original_ids[] = $item['id'];
            echo "<tr class='data-row'>";
            echo "<input type='hidden' name='item_row_id[]' value='{$item['id']}'>";
            echo "<td><select name='item[]' class='form-control' required><option value=''>Select below</option>";
            foreach($minSubs as $ms){
                $sel = ($ms['id'] == $item['item_id']) ? 'selected' : '';
                echo "<option value='{$ms['id']}' {$sel}>{$ms['name']}</option>";
            }
            echo "</select></td>";
            echo "<td><select name='unit[]' class='form-control' required><option value=''>Select below</option>";
            foreach($units as $u){
                $sel = ($u['id'] == $item['unit_id']) ? 'selected' : '';
                echo "<option value='{$u['id']}' {$sel}>{$u['name']}</option>";
            }
            echo "</select></td>";
            echo "<td><input type='number' name='quantity[]' value='{$item['quantity']}' min='0.01' step='any' class='form-control' required></td>";
            echo "</tr>";
        }
        echo "<tr id='original-ids-row' style='display:none;'>";
        echo "<td><input type='hidden' name='original_item_row_ids' value='".implode(',', $original_ids)."'></td>";
        echo "</tr>";
    } else {
        echo "<tr><td colspan='3' class='text-center'>No items found</td></tr>";
    }
    exit;
}

if(isset($_GET['get_manufacturing_order_preview'])){
    $order_id = (int) $_GET['order_id'];
    $order    = selectManufacturingOrderById($conn, $order_id);
    $items    = selectManufacturingOrderItems($conn, $order_id);
    if($order){
        echo "<table class='table table-sm table-bordered'>";
        echo "<tr><th style='width:30%'>Reference No</th><td>{$order['reference_no']}</td></tr>";
        echo "<tr><th>Customer</th><td>{$order['customer_name']}</td></tr>";
        echo "<tr><th>Date</th><td>{$order['date_']}</td></tr>";
        echo "<tr><th>Status</th><td><span class='badge badge-warning'>".ucfirst($order['status'])."</span></td></tr>";
        echo "</table>";
        echo "<h6 class='mt-3'>Items Required</h6>";
        echo "<table class='table table-sm table-bordered table-striped'>";
        echo "<thead class='bg-primary'><tr><th>#</th><th>Item</th><th>Unit</th><th>Quantity</th></tr></thead><tbody>";
        if($items && is_array($items)){
            $n = 1;
            foreach($items as $item){
                echo "<tr>
                    <td>{$n}</td>
                    <td>{$item['item_name']}</td>
                    <td>{$item['unit_name']}</td>
                    <td>".number_format($item['quantity'], 2)."</td>
                </tr>";
                $n++;
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No items</td></tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-danger'>Order not found.</div>";
    }
    exit;
}

if(isset($_GET['get_order_info'])){
    $order = selectManufacturingOrderById($conn, (int)$_GET['order_id']);
    header('Content-Type: application/json');
    echo json_encode($order ?: null);
    exit;
}


if (isset($_GET['get_item_units']) && isset($_GET['item_id'])) {
    $item_id  = (int) $_GET['item_id'];
    $store_id = isset($_GET['store_id']) && $_GET['store_id'] !== '' ? (int) $_GET['store_id'] : null;
    $item     = selectMinSubById($conn, $item_id);
    $base_unit_row = selectUnitById($conn, (int)$item['base_unit']);
    $balance       = sumStockMovementByItemId($conn, $item_id, null, null, $store_id);
    $conversions = selectItemUnitConversionByItemId($conn, $item_id);
    $alt_units   = [];
    if ($conversions && is_array($conversions)) {
        foreach ($conversions as $conv) {
            $unit_row    = selectUnitById($conn, (int)$conv['unit_two_id']);
            $unit_balance = $conv['quantity'] > 0 ? round($balance / $conv['quantity'], 4) : 0;
            $alt_units[] = [
                'id'             => (int)   $conv['unit_two_id'],
                'name'           => $unit_row['name'],
                'factor'         => (float) $conv['quantity'],
                'balance_in_unit'=> $unit_balance
            ];
        }
    }
   echo json_encode([
        'base_unit'         => (int)   $item['base_unit'],
        'base_unit_name'    => $base_unit_row['name'],
        'base_unit_balance' => $balance,
        'alt_units'         => $alt_units,
        'selling_price'     => (float) ($item['price'] ?? 0),
        'image_url'         => $item['image_url'] ?? ''     
    ]);
    exit();
}

}