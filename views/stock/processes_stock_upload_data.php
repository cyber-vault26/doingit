<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$type     = $_POST['type']     ?? '';
$file     = $_FILES['file']    ?? null;
$fileName = ($file && $file['error'] === 0) ? $file['name'] : 'N/A';
$data = [];
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK){
        $inputFileName = $_FILES['file']['tmp_name'];
        try{
            $spreadSheet = IOFactory::load($inputFileName);
            $workSheet   = $spreadSheet->getActiveSheet();
            $highestRow  = $workSheet->getHighestRow();
            $highestColumn = $workSheet->getHighestColumn();
            for($row = 1; $row <= $highestRow; $row++){
                $rowData = $workSheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                $data[]  = $rowData[0];
            }
        } catch(Exception $e){
            echo "<div class='alert alert-danger'>Error reading file: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<div class="card card-primary">
    <div class="card-header">
        <h4 class="card-title">Stock Upload — Review & Process</h4>
    </div>
    <div class="card-body">
        <h5>Transaction Type: <strong><?= strtoupper(htmlspecialchars($type)) ?></strong></h5>
        <h5>Uploaded File: <strong><?= htmlspecialchars($fileName) ?></strong></h5>
        <form action="./controllers/stock_controllers.php" method="post" class="was-validated">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <div class="row mt-3">
                <?php if($type === 'transfer'): ?>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">From Store</label>
                        <select name="from_store" class="form-control select2-form select2bs4-form" required>
                            <option value="">— Select Store —</option>
                            <?php
                                $stores = selectAllStockStores($conn);
                                if($stores && is_array($stores)){
                                    foreach($stores as $store){
                                        echo "<option value='{$store['id']}'>{$store['name']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">To Store</label>
                        <select name="to_store" class="form-control select2-form select2bs4-form" required>
                            <option value="">— Select Store —</option>
                            <?php
                                if($stores && is_array($stores)){
                                    foreach($stores as $store){
                                        echo "<option value='{$store['id']}'>{$store['name']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <?php else: ?>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Store</label>
                        <select name="store" class="form-control select2-form select2bs4-form" required>
                            <option value="">— Select Store —</option>
                            <?php
                                $stores = selectAllStockStores($conn);
                                if($stores && is_array($stores)){
                                    foreach($stores as $store){
                                        echo "<option value='{$store['id']}'>{$store['name']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr class="bg-primary">
                            <th class="text-white">#</th>
                            <th class="text-white">Item Name</th>
                            <th class="text-white">Base Unit</th>
                            <th class="text-white">Quantity</th>
                            <th class="text-white">Date</th>
                            <th class="text-white">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $counter  = 1;
                        $firstRow = true;
                        foreach($data as $row){
                            if($firstRow){ $firstRow = false; continue; }
                            if(empty($row[1])) break;
                            $rawName  = $row[1];
                            // Remove bracket section e.g. "Cement 50kg (KG)" -> "Cement 50kg"
                            $rawName  = preg_replace('/\(.*?\)/', '', $rawName);
                            // Remove non-printable and hidden characters
                            $rawName  = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $rawName);
                            // Collapse multiple spaces
                            $rawName  = trim(preg_replace('/\s+/', ' ', $rawName));
                            // Remove any remaining non-ASCII hidden chars
                            $rawName  = preg_replace('/[^\x20-\x7E]/', '', $rawName);
                            $cleanName = trim($rawName);
                            $quantity = (float) $row[2];
                            $rawDate  = trim($row[3] ?? '');
                            if($quantity <= 0) continue;
                            // Parse Excel date serial or string
                            $parsedDate = '';
                            if(is_numeric($rawDate)){
                                $unixDate   = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp((float)$rawDate);
                                $parsedDate = date('Y-m-d', $unixDate);
                            } elseif(!empty($rawDate)){
                                $ts = strtotime($rawDate);
                                $parsedDate = $ts ? date('Y-m-d', $ts) : '';
                            }
                            // Simple search — no join, just min_subs
                            $searchName = $conn->real_escape_string($cleanName);
                            $sql        = "SELECT * FROM min_subs WHERE deleted_at IS NULL AND name LIKE '%$searchName%' LIMIT 1";
                            $result     = $conn->query($sql);
                            $itemData   = $result ? $result->fetch_assoc() : null;
                            // Get base unit separately
                            $baseUnitName = '-';
                            if($itemData){
                                $unitResult   = selectUnitById($conn, (int)$itemData['base_unit']);
                                $baseUnitName = $unitResult['name'] ?? '-';
                            }
                            echo "<tr>";
                            echo "<td>$counter</td>";
                            if($itemData){
                                echo "<td><input type='hidden' name='item_id[]' value='{$itemData['id']}'><input type='hidden' name='base_unit[]' value='{$itemData['base_unit']}'>" . htmlspecialchars($itemData['name']) . "</td>";
                                echo "<td><span class='badge badge-primary'>" . htmlspecialchars($baseUnitName) . "</span></td>";
                                echo "<td><input type='number' name='quantity[]' value='$quantity' step='0.01' min='0.01' required class='form-control form-control-sm' style='width:120px;'></td>";
                                echo "<td><input type='date' name='date[]' value='$parsedDate' required class='form-control form-control-sm' style='width:150px;'></td>";
                                echo "<td><span class='badge badge-success'>Found</span></td>";
                            } else {
                                echo "<td>" . htmlspecialchars($cleanName) . "</td>";
                                echo "<td>—</td>";
                                echo "<td><input type='number' value='$quantity' step='0.01' disabled class='form-control form-control-sm' style='width:120px;'></td>";
                                echo "<td><input type='date' value='$parsedDate' disabled class='form-control form-control-sm' style='width:150px;'></td>";
                                echo "<td><span class='badge badge-danger'>Not Found</span></td>";
                            }
                            echo "</tr>";
                            $counter++;
                        }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary">
                            <th class="text-white">#</th>
                            <th class="text-white">Item Name</th>
                            <th class="text-white">Base Unit</th>
                            <th class="text-white">Quantity</th>
                            <th class="text-white">Date</th>
                            <th class="text-white">Status</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer">
                <button type="submit" name="upload_stock" class="btn btn-primary btn-block btn-sm">
                    <i class="fas fa-check"></i> Process Stock Data
                </button>
            </div>
        </form>
    </div>
</div>