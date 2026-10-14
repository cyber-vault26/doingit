<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file     = $_FILES['file']    ?? null;
$fileName = ($file && $file['error'] === 0) ? $file['name'] : null;
$data     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $inputFileName = $_FILES['file']['tmp_name'];
    try {
        $spreadSheet   = IOFactory::load($inputFileName);
        $workSheet     = $spreadSheet->getActiveSheet();
        $highestRow    = $workSheet->getHighestRow();
        $highestColumn = $workSheet->getHighestColumn();
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = $workSheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $data[]  = $rowData[0];
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error reading file: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

function cleanCell($val) {
    $val = preg_replace('/\(.*?\)/', '', (string)$val);
    $val = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $val);
    $val = preg_replace('/[^\x20-\x7E]/', '', $val);
    return trim(preg_replace('/\s+/', ' ', $val));
}
?>

<div class="card card-primary">
    <div class="card-header">
        <h4 class="card-title">Items Upload — Review &amp; Process</h4>
    </div>

    <div class="card-body">

        <!-- TOP: FILE UPLOAD FORM -->
        <div class="card card-outline card-secondary mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-upload mr-1"></i> Upload Excel File</h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Excel File</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-eye mr-1"></i> Preview
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Columns used:
                        <strong>C</strong> Item Name &nbsp;|&nbsp;
                        <strong>D</strong> Pack Size &nbsp;|&nbsp;
                        <strong>E</strong> Category &nbsp;|&nbsp;
                        <strong>F</strong> Sub-Category &nbsp;|&nbsp;
                        <strong>G</strong> Ledger
                    </small>
                </form>
            </div>
        </div>

        <?php if (!empty($data)): ?>
        <?php
            $subsidiaries = [];
            $subResult = $conn->query("SELECT * FROM subsidiaries WHERE deleted_at IS NULL ORDER BY name ASC");
            if ($subResult) {
                while ($sub = $subResult->fetch_assoc()) {
                    $subsidiaries[] = $sub;
                }
            }
        ?>

        <h5>Uploaded File: <strong><?= htmlspecialchars($fileName) ?></strong></h5>

        <!-- MAIN PROCESS FORM -->
        <form action="./controllers/stock_controllers.php" method="post">

            <div class="row mt-3 mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Subsidiary</label>
                        <select name="subsidiary_id" class="form-control select2-form select2bs4-form" required>
                            <option value="">— Select Subsidiary —</option>
                            <?php foreach ($subsidiaries as $sub): ?>
                                <option value="<?= (int)$sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-2">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr class="bg-primary">
                            <th class="text-white">#</th>
                            <th class="text-white">Item Name</th>
                            <th class="text-white">Pack Size</th>
                            <th class="text-white">Category</th>
                            <th class="text-white">Sub-Category</th>
                            <th class="text-white">Ledger</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $counter  = 1;
                        $firstRow = true;
                        foreach ($data as $row):
                            if ($firstRow) { $firstRow = false; continue; }
                            if (empty($row[2])) continue;

                            // A=0  B=1  C=2        D=3        E=4          F=5            G=6
                            //skip  skip  itemName  packSize   category     subCategory    ledger
                            $itemName    = cleanCell($row[2]);
                            $packSize    = cleanCell($row[3] ?? '');
                            $category    = cleanCell($row[4] ?? '');
                            $subCategory = cleanCell($row[5] ?? '');
                            $ledger      = cleanCell($row[6] ?? '');
                    ?>
                        <tr>
                            <td><?= (int)$counter ?></td>
                            <td>
                                <input type="hidden" name="item_name[]"    value="<?= htmlspecialchars($itemName) ?>">
                                <input type="hidden" name="pack_size[]"    value="<?= htmlspecialchars($packSize) ?>">
                                <input type="hidden" name="category[]"     value="<?= htmlspecialchars($category) ?>">
                                <input type="hidden" name="sub_category[]" value="<?= htmlspecialchars($subCategory) ?>">
                                <input type="hidden" name="ledger[]"       value="<?= htmlspecialchars($ledger) ?>">
                                <?= htmlspecialchars($itemName) ?>
                            </td>
                            <td><?= htmlspecialchars($packSize)    ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= htmlspecialchars($category)    ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= htmlspecialchars($subCategory) ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= htmlspecialchars($ledger)      ?: '<span class="text-muted">—</span>' ?></td>
                        </tr>
                    <?php
                            $counter++;
                        endforeach;
                    ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary">
                            <th class="text-white">#</th>
                            <th class="text-white">Item Name</th>
                            <th class="text-white">Pack Size</th>  
                            <th class="text-white">Category</th>
                            <th class="text-white">Sub-Category</th> 
                            <th class="text-white">Ledger</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="card-footer px-0">
                <button type="submit" name="upload_items" class="btn btn-primary btn-block btn-sm">
                    <i class="fas fa-check mr-1"></i> Process Items Data
                </button>
            </div>

        </form>
        <?php endif; ?>

    </div>
</div>