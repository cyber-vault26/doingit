<?php
    require_once "../../configs.php";
    require_once "../../functions/subsidiary_functions.php";
    require_once "../../functions/stock_functions.php";
    require_once "../../functions/min_sub_functions.php";
    require_once "../../functions/utilities_functions.php";
    $conn = openConn();

function renderReportFooter($co){
    $phones = [];
    foreach(['phone','phone2','phone3'] as $col){
        if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
    }
    $phone_str = implode(' / ', $phones);
    ?>
    <div class="v-footer">
        <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
        <?= htmlspecialchars($co['address'] ?? '') ?><br>
        Tel: <?= $phone_str ?>
    </div>
    <?php
}

 
$co = selectAllCompanyDetails($conn);

if($_SERVER['REQUEST_METHOD'] === 'GET'){

if(isset($_GET['get_item_movement_report'])){
    $itemId  = (int) $_GET['item_id'];
    $date1   = isset($_GET['date1']) && $_GET['date1'] !== '' ? $conn->real_escape_string($_GET['date1']) : null;
    $date2   = isset($_GET['date2']) && $_GET['date2'] !== '' ? $conn->real_escape_string($_GET['date2']) : null;
    $storeId = isset($_GET['store']) && $_GET['store'] !== '' ? (int) $_GET['store'] : null;

    $itemDetails = selectMinSubById($conn, $itemId);

    if(!$itemDetails){
        echo "<div class='alert alert-danger'>Item not found.</div>";
        exit;
    }

    $movements    = selectStockMovementByItemId($conn, $itemId, $date1, $date2, $storeId);
    $baseUnitData = selectUnitById($conn, (int)$itemDetails['base_unit']);
    $baseUnitName = $baseUnitData['name'] ?? '-';
    $storeData    = $storeId ? selectStockStoreById($conn, $storeId) : null;
    $storeName    = $storeData ? htmlspecialchars($storeData['name']) : 'All Stores';
    $imgPath      = '../../uploads/items/' . ($itemDetails['image_url'] ?? '');
    $hasImage     = !empty($itemDetails['image_url']) && file_exists($imgPath);
    $quantityBalance = 0;
    $counter         = 1;
    ?>

    <div id="stock-item-movement-report-<?= $itemId ?>" class="stock-item-movement-report">
        <div class="stock-item-movement-report-header">
            <div class="stock-item-movement-report-logo"><img src="/dist/img/logo.png" alt="Logo"></div>
            <div class="stock-item-movement-report-title">Stock Movement Report</div>
        </div>

        <div class="stock-item-movement-report-item-row">
            <div class="stock-item-movement-report-item-img">
                <?php if($hasImage): ?>
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($itemDetails['name']) ?>">
                <?php else: ?>
                    <div class="stock-item-movement-report-item-img-none">No Image</div>
                <?php endif; ?>
            </div>
            <div>
                <div class="stock-item-movement-report-item-name"><?= htmlspecialchars($itemDetails['name']) ?></div>
                <div class="stock-item-movement-report-item-meta">Base Unit: <?= htmlspecialchars($baseUnitName) ?></div>
            </div>
        </div>

        <div class="stock-item-movement-report-info-row">
            <div class="stock-item-movement-report-party">
                <div class="stock-item-movement-report-party-label">Store:</div>
                <div class="stock-item-movement-report-party-value"><strong><?= $storeName ?></strong></div>
            </div>
            <div class="stock-item-movement-report-meta">
                <?php if($date1 || $date2): ?>
                <div class="stock-item-movement-report-party-label">Period:</div>
                <div class="stock-item-movement-report-party-value">
                    <strong>
                        <?= $date1 ? date('d M Y', strtotime($date1)) : 'Beginning' ?>
                        &nbsp;to&nbsp;
                        <?= $date2 ? date('d M Y', strtotime($date2)) : 'Today' ?>
                    </strong>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <table class="stock-item-movement-report-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:15%;">Date</th>
                    <th style="width:20%;">Ref No</th>
                    <th style="width:10%;" class="stock-item-movement-report-text-center">Type</th>
                    <th style="width:15%;" class="stock-item-movement-report-text-right">Quantity</th>
                    <th style="width:15%;">Unit</th>
                    <th style="width:20%;" class="stock-item-movement-report-text-right">Base Qty (<?= htmlspecialchars($baseUnitName) ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php if($movements && is_array($movements) && count($movements) > 0):
                    foreach($movements as $movement):
                        $qty      = (float) $movement['quantity'];
                        $baseQty  = (float) $movement['base_quantity'];
                        $unitData = selectUnitById($conn, (int)$movement['unit']);
                        $unitName = $unitData['name'] ?? '-';
                        if($movement['type'] === 'in'){ $quantityBalance += $baseQty; } else { $quantityBalance -= $baseQty; }
                ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= date('d-M-Y', strtotime($movement['date_'])) ?></td>
                    <td><?= htmlspecialchars($movement['ref_no']) ?></td>
                    <td class="stock-item-movement-report-text-center">
                        <span class="<?= $movement['type'] === 'in' ? 'stock-item-movement-report-badge-in' : 'stock-item-movement-report-badge-out' ?>">
                            <?= strtoupper($movement['type']) ?>
                        </span>
                    </td>
                    <td class="stock-item-movement-report-text-right"><?= number_format($qty, 2) ?></td>
                    <td><?= htmlspecialchars($unitName) ?></td>
                    <td class="stock-item-movement-report-text-right"><?= number_format($baseQty, 2) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" class="stock-item-movement-report-text-center" style="color:#999;padding:30px;">No movements found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="stock-item-movement-report-balance-row">
                    <td colspan="6" class="stock-item-movement-report-text-right">CLOSING BALANCE (<?= htmlspecialchars($baseUnitName) ?>):</td>
                    <td class="stock-item-movement-report-text-right"><?= number_format($quantityBalance, 2) ?></td>
                </tr>
                <tr class="stock-item-movement-report-header-row">
                    <th>#</th>
                    <th>Date</th>
                    <th>Ref No</th>
                    <th class="stock-item-movement-report-text-center">Type</th>
                    <th class="stock-item-movement-report-text-right">Quantity</th>
                    <th>Unit</th>
                    <th class="stock-item-movement-report-text-right">Base Qty (<?= htmlspecialchars($baseUnitName) ?>)</th>
                </tr>
            </tfoot>
        </table>

        <?php
        $phones = [];
        foreach(['phone','phone2','phone3'] as $col){
            if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
        }
        $phone_str = implode(' / ', $phones);
        ?>
        <div class="stock-item-movement-report-footer">
            <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
            <?= htmlspecialchars($co['address'] ?? '') ?><br>
            Tel: <?= $phone_str ?>
        </div>
    </div>

    <div class="card-footer card-footer-actions no-print">
        <button class="btn btn-primary btn-sm" onclick="printStockItemMovementReport('stock-item-movement-report-<?= $itemId ?>')">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
    <?php
}


if(isset($_GET['get_ledger_report'])){

    $ledgerId = isset($_GET['ledger']) && $_GET['ledger'] !== '' ? (int)$_GET['ledger'] : null;
    $date1    = isset($_GET['date1'])  && $_GET['date1']  !== '' ? $conn->real_escape_string($_GET['date1'])  : null;
    $date2    = isset($_GET['date2'])  && $_GET['date2']  !== '' ? $conn->real_escape_string($_GET['date2'])  : null;
    $storeId  = isset($_GET['store'])  && $_GET['store']  !== '' ? (int)$_GET['store'] : null;

    if($ledgerId){
        $items = selectMinSubsByParentSubsidiary($conn, $ledgerId);
    } else {
        $items = selectAllMinSub($conn);
    }

    if(!$items || !is_array($items) || count($items) === 0){
        echo "<div class='alert alert-warning'>No items found.</div>";
        exit;
    }

    $storeData = $storeId ? selectStockStoreById($conn, $storeId) : null;
    $storeName = $storeData ? htmlspecialchars($storeData['name']) : 'All Stores';

    $phones = [];
    foreach(['phone','phone2','phone3'] as $col){
        if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
    }
    $phone_str = implode(' / ', $phones);
    ?>

    <div id="stock-ledger-report" class="stock-ledger-report">
        <div class="stock-ledger-report-header">
            <div class="stock-ledger-report-logo"><img src="/dist/img/logo.png" alt="Logo"></div>
            <div class="stock-ledger-report-title">Stock Ledger Report</div>
        </div>

        <div class="stock-ledger-report-info-row">
            <div class="stock-ledger-report-party">
                <div class="stock-ledger-report-party-label">Store:</div>
                <div class="stock-ledger-report-party-value"><strong><?= $storeName ?></strong></div>
                <?php if($ledgerId): ?>
                <div class="stock-ledger-report-party-label">Ledger:</div>
                <div class="stock-ledger-report-party-value"><strong><?= htmlspecialchars($items[0]['subsidiary_name'] ?? '-') ?></strong></div>
                <?php endif; ?>
            </div>
            <div class="stock-ledger-report-meta">
                <?php if($date1 || $date2): ?>
                <div class="stock-ledger-report-party-label">Period:</div>
                <div class="stock-ledger-report-party-value">
                    <strong><?= $date1 ? date('d M Y', strtotime($date1)) : 'Beginning' ?> &nbsp;to&nbsp; <?= $date2 ? date('d M Y', strtotime($date2)) : 'Today' ?></strong>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <table class="stock-ledger-report-table">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:6%;">Image</th>
                    <th style="width:24%;">Item Name</th>
                    <th style="width:11%;" class="stock-ledger-report-text-right">Qty In</th>
                    <th style="width:11%;" class="stock-ledger-report-text-right">Qty Out</th>
                    <th style="width:11%;" class="stock-ledger-report-text-right">Transfer In</th>
                    <th style="width:11%;" class="stock-ledger-report-text-right">Transfer Out</th>
                    <th style="width:12%;" class="stock-ledger-report-text-right">Balance</th>
                    <th style="width:10%;" class="stock-ledger-report-text-right">Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter             = 1;
                $hasItemsWithBalance = false;
                $grandTotalIn        = 0;
                $grandTotalOut       = 0;
                $grandTotalTrIn      = 0;
                $grandTotalTrOut     = 0;
                $grandTotalBal       = 0;

                foreach($items as $item){
                    $itemId       = (int)$item['id'];
                    $itemName     = htmlspecialchars($item['name']);
                    $baseUnitData = selectUnitById($conn, (int)$item['base_unit']);
                    $baseUnitName = $baseUnitData['name'] ?? '-';
                    $movements    = selectStockMovementByItemId($conn, $itemId, $date1, $date2, $storeId);

                    if(!$movements || !is_array($movements) || count($movements) === 0) continue;

                    $totalIn = $totalOut = $totalTrIn = $totalTrOut = 0;
                    foreach($movements as $m){
                        $qty = (float)$m['base_quantity'];
                        if($m['type'] === 'in')               $totalIn    += $qty;
                        elseif($m['type'] === 'out')          $totalOut   += $qty;
                        elseif($m['type'] === 'transfer-in')  $totalTrIn  += $qty;
                        elseif($m['type'] === 'transfer-out') $totalTrOut += $qty;
                    }

                    $balance = ($totalIn + $totalTrIn) - ($totalOut + $totalTrOut);
                    $hasItemsWithBalance = true;
                    $grandTotalIn    += $totalIn;
                    $grandTotalOut   += $totalOut;
                    $grandTotalTrIn  += $totalTrIn;
                    $grandTotalTrOut += $totalTrOut;
                    $grandTotalBal   += $balance;
                    $balClass = $balance < 0 ? 'stock-ledger-report-qty-neg' : 'stock-ledger-report-qty-bal';

                    $imgPath  = '../../uploads/items/' . ($item['image_url'] ?? '');
                    $hasImage = !empty($item['image_url']) && file_exists($imgPath);
                ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td>
                        <?php if($hasImage): ?>
                            <img class="stock-ledger-report-row-img" src="<?= htmlspecialchars($imgPath) ?>" alt="<?= $itemName ?>">
                        <?php else: ?>
                            <div class="stock-ledger-report-row-noimg">N/A</div>
                        <?php endif; ?>
                    </td>
                    <td><?= $itemName ?></td>
                    <td class="stock-ledger-report-text-right stock-ledger-report-qty-in"><?= number_format($totalIn, 2) ?></td>
                    <td class="stock-ledger-report-text-right stock-ledger-report-qty-out"><?= number_format($totalOut, 2) ?></td>
                    <td class="stock-ledger-report-text-right stock-ledger-report-qty-in"><?= number_format($totalTrIn, 2) ?></td>
                    <td class="stock-ledger-report-text-right stock-ledger-report-qty-out"><?= number_format($totalTrOut, 2) ?></td>
                    <td class="stock-ledger-report-text-right <?= $balClass ?>"><?= number_format($balance, 2) ?></td>
                    <td class="stock-ledger-report-text-right"><?= htmlspecialchars($baseUnitName) ?></td>
                </tr>
                <?php } if(!$hasItemsWithBalance){ echo '<tr><td colspan="9" class="stock-ledger-report-text-center">No stock transactions found.</td></tr>'; } ?>
            </tbody>
            <tfoot>
                <?php if($hasItemsWithBalance): ?>
                <tr class="stock-ledger-report-totals-row">
                    <td colspan="3" class="stock-ledger-report-text-right">TOTAL</td>
                    <td class="stock-ledger-report-text-right" style="color:#90ee90;"><?= number_format($grandTotalIn, 2) ?></td>
                    <td class="stock-ledger-report-text-right" style="color:#ffb3b3;"><?= number_format($grandTotalOut, 2) ?></td>
                    <td class="stock-ledger-report-text-right" style="color:#90ee90;"><?= number_format($grandTotalTrIn, 2) ?></td>
                    <td class="stock-ledger-report-text-right" style="color:#ffb3b3;"><?= number_format($grandTotalTrOut, 2) ?></td>
                    <td class="stock-ledger-report-text-right" style="color:#90ee90;"><?= number_format($grandTotalBal, 2) ?></td>
                    <td></td>
                </tr>
                <?php endif; ?>
                <tr class="stock-ledger-report-header-row">
                    <th>#</th>
                    <th>Image</th>
                    <th>Item Name</th>
                    <th class="stock-ledger-report-text-right">Qty In</th>
                    <th class="stock-ledger-report-text-right">Qty Out</th>
                    <th class="stock-ledger-report-text-right">Transfer In</th>
                    <th class="stock-ledger-report-text-right">Transfer Out</th>
                    <th class="stock-ledger-report-text-right">Balance</th>
                    <th class="stock-ledger-report-text-right">Unit</th>
                </tr>
            </tfoot>
        </table>

        <div class="stock-ledger-report-footer">
            <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
            <?= htmlspecialchars($co['address'] ?? '') ?><br>
            Tel: <?= $phone_str ?>
        </div>
    </div>

    <div class="card-footer card-footer-actions no-print">
        <button class="btn btn-primary btn-sm" onclick="printStockLedgerReport('stock-ledger-report')">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
   

    <?php
}

if(isset($_GET['get_stock_selling_ledger_report'])){

    $ledgerId = isset($_GET['ledger']) && $_GET['ledger'] !== '' ? (int)$_GET['ledger'] : null;
    $date1    = isset($_GET['date1'])  && $_GET['date1']  !== '' ? $conn->real_escape_string($_GET['date1'])  : null;
    $date2    = isset($_GET['date2'])  && $_GET['date2']  !== '' ? $conn->real_escape_string($_GET['date2'])  : null;
    $storeId  = isset($_GET['store'])  && $_GET['store']  !== '' ? (int)$_GET['store'] : null;

    if($ledgerId){
        $items = selectMinSubsByParentSubsidiary($conn, $ledgerId);
    } else {
        $items = selectAllMinSub($conn);
    }

    if(!$items || !is_array($items) || count($items) === 0){
        echo "<div class='alert alert-warning'>No items found.</div>";
        exit;
    }

    $storeData = $storeId ? selectStockStoreById($conn, $storeId) : null;
    $storeName = $storeData ? htmlspecialchars($storeData['name']) : 'All Stores';

    $rows = [];

    foreach($items as $item){
        $itemId   = (int)$item['id'];
        $sales    = selectStockMovementByItemIdAndType($conn, $itemId, 'out', $date1, $date2, $storeId);

        if(!$sales || !is_array($sales) || count($sales) === 0) continue;

        $imgPath  = '../../uploads/items/' . ($item['image_url'] ?? '');
        $hasImage = !empty($item['image_url']) && file_exists($imgPath);

        foreach($sales as $line){
            $price    = (float)$line['price'];
            $qty      = (float)$line['quantity'];
            $unitData = selectUnitById($conn, (int)$line['unit']);
            $unitName = $unitData['name'] ?? '-';
            $key      = $itemId . '|' . $unitName . '|' . number_format($price, 4);

            if(!isset($rows[$key])){
                $rows[$key] = [
                    'name'     => htmlspecialchars($item['name']),
                    'img'      => $imgPath,
                    'hasImage' => $hasImage,
                    'quantity' => 0,
                    'unit'     => $unitName,
                    'price'    => $price,
                    'amount'   => 0,
                ];
            }
            $rows[$key]['quantity'] += $qty;
            $rows[$key]['amount']   += $qty * $price;
        }
    }

    $grandTotalQty    = array_sum(array_column($rows, 'quantity'));
    $grandTotalAmount = array_sum(array_column($rows, 'amount'));

    $phones = [];
    foreach(['phone','phone2','phone3'] as $col){
        if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
    }
    $phone_str = implode(' / ', $phones);
    ?>

    <div id="stock-selling-ledger-report" class="stock-selling-ledger-report">
        <div class="stock-selling-ledger-report-header">
            <div class="stock-selling-ledger-report-logo"><img src="/dist/img/logo.png" alt="Logo"></div>
            <div class="stock-selling-ledger-report-title">Sales Ledger Report</div>
        </div>

        <div class="stock-selling-ledger-report-info-row">
            <div class="stock-selling-ledger-report-party">
                <div class="stock-selling-ledger-report-party-label">Store:</div>
                <div class="stock-selling-ledger-report-party-value"><strong><?= $storeName ?></strong></div>
                <?php if($ledgerId): ?>
                <div class="stock-selling-ledger-report-party-label">Ledger:</div>
                <div class="stock-selling-ledger-report-party-value"><strong><?= htmlspecialchars($items[0]['subsidiary_name'] ?? '-') ?></strong></div>
                <?php endif; ?>
            </div>
            <div class="stock-selling-ledger-report-meta">
                <?php if($date1 || $date2): ?>
                <div class="stock-selling-ledger-report-party-label">Period:</div>
                <div class="stock-selling-ledger-report-party-value">
                    <strong><?= $date1 ? date('d M Y', strtotime($date1)) : 'Beginning' ?> &nbsp;to&nbsp; <?= $date2 ? date('d M Y', strtotime($date2)) : 'Today' ?></strong>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <table class="stock-selling-ledger-report-table">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:6%;">Image</th>
                    <th style="width:30%;">Item Name</th>
                    <th style="width:12%;" class="stock-selling-ledger-report-text-right">Quantity</th>
                    <th style="width:9%;">Unit</th>
                    <th style="width:17%;" class="stock-selling-ledger-report-text-right">Price</th>
                    <th style="width:18%;" class="stock-selling-ledger-report-text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($rows) > 0): ?>
                    <?php $counter = 1; foreach($rows as $row): ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td>
                            <?php if($row['hasImage']): ?>
                                <img class="stock-selling-ledger-report-row-img" src="<?= htmlspecialchars($row['img']) ?>" alt="<?= $row['name'] ?>">
                            <?php else: ?>
                                <div class="stock-selling-ledger-report-row-noimg">N/A</div>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['name'] ?></td>
                        <td class="stock-selling-ledger-report-text-right"><?= number_format($row['quantity'], 2) ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td class="stock-selling-ledger-report-text-right"><?= number_format($row['price'], 2) ?></td>
                        <td class="stock-selling-ledger-report-text-right"><?= number_format($row['amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="stock-selling-ledger-report-text-center">No sales found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="stock-selling-ledger-report-totals-row">
                    <td colspan="3" class="stock-selling-ledger-report-text-right">TOTAL</td>
                    <td class="stock-selling-ledger-report-text-right"><?= number_format($grandTotalQty, 2) ?></td>
                    <td></td>
                    <td></td>
                    <td class="stock-selling-ledger-report-text-right"><?= number_format($grandTotalAmount, 2) ?></td>
                </tr>
                <tr class="stock-selling-ledger-report-header-row">
                    <th>#</th>
                    <th>Image</th>
                    <th>Item Name</th>
                    <th class="stock-selling-ledger-report-text-right">Quantity</th>
                    <th>Unit</th>
                    <th class="stock-selling-ledger-report-text-right">Price</th>
                    <th class="stock-selling-ledger-report-text-right">Amount</th>
                </tr>
            </tfoot>
        </table>

        <div class="stock-selling-ledger-report-footer">
            <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
            <?= htmlspecialchars($co['address'] ?? '') ?><br>
            Tel: <?= $phone_str ?>
        </div>
    </div>

    <div class="card-footer card-footer-actions no-print">
        <button class="btn btn-primary btn-sm" onclick="printStockSellingLedgerReport('stock-selling-ledger-report')">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <?php
}

if(isset($_GET['stock_item_selling_report'])){

    $itemId  = isset($_GET['item_id']) && $_GET['item_id'] !== '' ? (int)$_GET['item_id'] : null;
    $date1   = isset($_GET['date1'])   && $_GET['date1']   !== '' ? $conn->real_escape_string($_GET['date1'])  : null;
    $date2   = isset($_GET['date2'])   && $_GET['date2']   !== '' ? $conn->real_escape_string($_GET['date2'])  : null;
    $storeId = isset($_GET['store'])   && $_GET['store']   !== '' ? (int)$_GET['store'] : null;

    if(!$itemId){
        echo "<div class='alert alert-warning'>No item selected.</div>";
        exit;
    }

    $itemData = selectMinSubById($conn, $itemId);
    if(!$itemData){
        echo "<div class='alert alert-warning'>Item not found.</div>";
        exit;
    }

    $storeData = $storeId ? selectStockStoreById($conn, $storeId) : null;
    $storeName = $storeData ? htmlspecialchars($storeData['name']) : 'All Stores';
    $itemName  = htmlspecialchars($itemData['name']);

    $imgPath  = '../../uploads/items/' . ($itemData['image_url'] ?? '');
    $hasImage = !empty($itemData['image_url']) && file_exists($imgPath);

    $sales = selectStockMovementByItemIdAndType($conn, $itemId, 'out', $date1, $date2, $storeId);

    $grandTotalQty    = 0;
    $grandTotalAmount = 0;

    $phones = [];
    foreach(['phone','phone2','phone3'] as $col){
        if(!empty($co[$col])) $phones[] = htmlspecialchars($co[$col]);
    }
    $phone_str = implode(' / ', $phones);
    ?>

    <div id="stock-item-selling-report-<?= $itemId ?>" class="stock-item-selling-report">
        <div class="stock-item-selling-report-header">
            <div class="stock-item-selling-report-logo"><img src="/dist/img/logo.png" alt="Logo"></div>
            <div class="stock-item-selling-report-title">Item Sales Report</div>
        </div>

        <div class="stock-item-selling-report-item-row">
            <div class="stock-item-selling-report-item-img">
                <?php if($hasImage): ?>
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= $itemName ?>">
                <?php else: ?>
                    <div class="stock-item-selling-report-no-img">No Image</div>
                <?php endif; ?>
            </div>
            <div class="stock-item-selling-report-item-info">
                <div class="stock-item-selling-report-item-name"><?= $itemName ?></div>
                <div class="stock-item-selling-report-item-meta">Store: <?= $storeName ?></div>
            </div>
        </div>

        <?php if($date1 || $date2): ?>
        <div class="stock-item-selling-report-info-row">
            <div class="stock-item-selling-report-party"></div>
            <div class="stock-item-selling-report-meta">
                <div class="stock-item-selling-report-party-label">Period:</div>
                <div class="stock-item-selling-report-party-value">
                    <strong><?= $date1 ? date('d M Y', strtotime($date1)) : 'Beginning' ?> &nbsp;to&nbsp; <?= $date2 ? date('d M Y', strtotime($date2)) : 'Today' ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <table class="stock-item-selling-report-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:15%;">Date</th>
                    <th style="width:15%;">Ref No</th>
                    <th style="width:13%;" class="stock-item-selling-report-text-right">Quantity</th>
                    <th style="width:10%;">Unit</th>
                    <th style="width:17%;" class="stock-item-selling-report-text-right">Price</th>
                    <th style="width:20%;" class="stock-item-selling-report-text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if($sales && is_array($sales) && count($sales) > 0): ?>
                    <?php $counter = 1; foreach($sales as $line): ?>
                    <?php
                        $qty      = (float)$line['quantity'];
                        $price    = (float)$line['price'];
                        $amount   = $qty * $price;
                        $unitData = selectUnitById($conn, (int)$line['unit']);
                        $unitName = $unitData['name'] ?? '-';
                        $grandTotalQty    += $qty;
                        $grandTotalAmount += $amount;
                    ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= date('d M Y', strtotime($line['date_'])) ?></td>
                        <td><?= htmlspecialchars($line['ref_no']) ?></td>
                        <td class="stock-item-selling-report-text-right"><?= number_format($qty, 2) ?></td>
                        <td><?= htmlspecialchars($unitName) ?></td>
                        <td class="stock-item-selling-report-text-right"><?= number_format($price, 2) ?></td>
                        <td class="stock-item-selling-report-text-right"><?= number_format($amount, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="stock-item-selling-report-text-center">No sales found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="stock-item-selling-report-totals-row">
                    <td colspan="3" class="stock-item-selling-report-text-right">TOTAL</td>
                    <td class="stock-item-selling-report-text-right"><?= number_format($grandTotalQty, 2) ?></td>
                    <td></td>
                    <td></td>
                    <td class="stock-item-selling-report-text-right"><?= number_format($grandTotalAmount, 2) ?></td>
                </tr>
                <tr class="stock-item-selling-report-header-row">
                    <th>#</th>
                    <th>Date</th>
                    <th>Ref No</th>
                    <th class="stock-item-selling-report-text-right">Quantity</th>
                    <th>Unit</th>
                    <th class="stock-item-selling-report-text-right">Price</th>
                    <th class="stock-item-selling-report-text-right">Amount</th>
                </tr>
            </tfoot>
        </table>

        <div class="stock-item-selling-report-footer">
            <strong><?= htmlspecialchars($co['title'] ?? '') ?></strong><br>
            <?= htmlspecialchars($co['address'] ?? '') ?><br>
            Tel: <?= $phone_str ?>
        </div>
    </div>

    <div class="card-footer card-footer-actions no-print">
        <button class="btn btn-primary btn-sm" onclick="printStockItemSellingReport('stock-item-selling-report-<?= $itemId ?>')">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <?php
}

}