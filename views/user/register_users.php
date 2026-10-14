<?php
    require_once __DIR__ . "/../../functions/department_functions.php";
    require_once __DIR__ . "/../../functions/rank_functions.php";
    require_once __DIR__ . "/../../functions/stock_functions.php";  

    $conn = openConn();
    $ranks = listRanks($conn);
    $departments = listdepartments($conn);
    $ledgers = selectLedgerBySubMainId($conn, 13);
    $selectedLedgerId = 58;
    $stockStores = selectAllStockStores($conn);  
?>

<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">Register System User</h4>
    </div>
    <form action="./controllers/user_controller.php" method="post" class="was-validated">
        <div class="card-body">

            <div class="form-group">
                <label class="form-label">User ledger</label>
                <select name="ledger_id" class="form-control" required>
                    <option value="">Select below</option>
                    <?php if($ledgers && is_array($ledgers)): ?>
                        <?php foreach($ledgers as $ledger): ?>
                            <option value="<?= $ledger['id'] ?>" <?= ($ledger['id'] == $selectedLedgerId ? 'selected' : '') ?>>
                                <?= $ledger['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Names</label>
                <input type="text" name="names" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Staff rank</label>
                <select name="rank_id" class="form-control" required>
                    <option value="">Select below</option>
                    <?php if($ranks && is_array($ranks)): ?>
                        <?php foreach($ranks as $rank): ?>
                            <option value="<?= $rank['id'] ?>"><?= $rank['rank_name'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">Select below</option>
                    <?php if($departments && is_array($departments)): ?>
                        <?php foreach($departments as $department): ?>
                            <option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" maxlength="13" class="form-control" required>
            </div>

          
            <div class="form-group">
                <label class="form-label">Cost centre / Store</label>
                <select name="store_id" class="form-control" required>
                    <option value="">Select cost centre / store</option>
                    <?php if($stockStores && is_array($stockStores)): ?>
                        <?php foreach($stockStores as $store): ?>
                            <option value="<?= $store['id'] ?>"><?= $store['name'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">User group</label>
                <select name="usergroup" class="form-control" required>
                    <option value="staff">Normal staff</option>
                    <option value="shop-keeper">Shop keeper</option>
                    <option value="manufacturing">Manufacturing</option>
                    <option value="manager">Manager</option>
                    <option value="accountant">Accountant</option>
                    <option value="director">Director</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">User role</label>
                <select name="level" class="form-control" required>
                    <option value="1">Level 1 – data entry, creation only</option>
                    <option value="2">Level 2 – data entry, creation, sending requests</option>
                    <option value="3">Level 3 – accountant, HR, approval of some requests</option>
                    <option value="4">Level 4 – operational managers</option>
                    <option value="5">Level 5 – general managers</option>
                    <option value="6">Level 6 – directors</option>
                    <option value="7">Level 7 – system admin (technical)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Default password</label>
                <input type="password" name="password" value="12345" class="form-control" required>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" name="reg_user" class="btn btn-info btn-sm">Register User</button>
        </div>
    </form>
</div>