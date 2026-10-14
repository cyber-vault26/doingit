<?php
        require_once __DIR__ . "/../../functions/department_functions.php";
        require_once __DIR__ . "/../../functions/rank_functions.php";
        require_once __DIR__ . "/../../functions/user_functions.php";

        $conn = openConn();
        $ranks = listRanks($conn);
        $departments = listdepartments($conn);
        $ledgers = selectLedgerBySubMainId($conn, 13);
        $selectedLedgerId = 58; 
        $user_id = $_GET['user_id'];
        $userDetails = selectUserById($conn, $user_id);
        $selectedRankId = $userDetails['rank_id'];
        $selectedDepartment = $userDetails['department_id'];
        $selectedUserGroup = $userDetails['usertype'];
        $selectedUserLevel = $userDetails['user_level'];

?>

<div class=" card card-info">
    <div class=" card-header">
        <h4 class=" card-title">Edit user</h4>
        </div>
        <form action="./controllers/user_controller.php" method="post" class=" was-validated">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
            <div class=" card-body">
                <div class=" form-group">
                    <label for="" class=" form-label">User ledger</label>
                    <select name="ledger_id"  class=" form-control" required id="">
                        <option value="">select Below</option>
                        <?php
                            if($ledgers && is_array($ledgers)){
                                foreach($ledgers as $ledger){

                                
                                echo "<option value='$ledger[id]' " . ($ledger['id'] == $selectedLedgerId ? 'selected' : '') . ">$ledger[name]</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class=" form-group">
                    <label for=""> Names </label>
                    <input type="text" name="names" class=" form-control" value="<?= $userDetails['names'] ?>" required id="">

                </div>
                <div class=" form-group">
                    <label for="">Email</label>
                    <input type="text"  required value="<?= $userDetails['email'] ?>" class=" form-control" name="email" id="">
                </div>
                <div class=" form-group">
                    <label for="">staff Rank</label>
                    <select name="rank_id" class=" form-control" id="" required>
                        <option value="">select Below</option>
                        <?php
                            if($ranks && is_array($ranks)){
                                foreach($ranks as $rank){
                                    echo "<option value='$rank[id]' " . ($rank['id'] == $selectedRankId ? 'selected' : '') . ">$rank[rank_name]</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class=" form-group">
                    <label for="">Department</label>
                    <select name="department_id" class=" form-control" id="" required>
                        <option value="">select Below</option>
                        <?php
                            if($departments && is_array($departments)){
                                foreach($departments as $department){
                                    echo "<option value='$department[id]' " . ($department['id'] == $selectedDepartment ? 'selected' : '') . ">$department[name]</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class=" form-group">
                    <label for="" class=" form-label"> Phone</label>
                    <input type="tel" name="phone" value="<?= $userDetails['phone'] ?>" max="13" required id="" class=" form-control">
                </div>
                <div class=" form-group">
                    <label for="" class=" form-label">User group</label>
                    <select name="usergroup" class=" form-control" id="" required>
                        
                        <option value="staff" <?php echo ($selectedUserGroup == 'staff') ? 'selected' : ''; ?>>Normal staff</option>
                        <option value="manager" <?php echo ($selectedUserGroup == 'manager') ? 'selected' : ''; ?>>Manager</option>
                        <option value="accountant" <?php echo ($selectedUserGroup == 'accountant') ? 'selected' : ''; ?>>Accountant</option>
                        <option value="manager" <?php echo ($selectedUserGroup == 'Director') ? 'selected' : ''; ?>>Director</option>
                        <option value="Admin" <?php echo ($selectedUserGroup == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class=" form-group">
                    <label for="" class=" form-label">User role</label>
                    <select name="level" id="" class=" form-control" required>   
                        
                             <option value="1" <?php echo ($selectedUserLevel == '1') ? 'selected' : ''; ?>> level 1: data entry, creation only </option>
                            <option value="2" <?php echo ($selectedUserLevel == '2') ? 'selected' : ''; ?>> level 2: data entry, creation only, sending request</option>
                            <option value="3" <?php echo ($selectedUserLevel == '3') ? 'selected' : ''; ?>> level 3: Accountant, HR, approval of some request,  </option>
                            <option value="4" <?php echo ($selectedUserLevel == '4') ? 'selected' : ''; ?>> level 4: Operational Managers,   </option>
                            <option value="5" <?php echo ($selectedUserLevel == '5') ? 'selected' : ''; ?>> level 5: general managers   </option>
                            <option value="6" <?php echo ($selectedUserLevel == '6') ? 'selected' : ''; ?>> level 6: Directors   </option>
                            <option value="7" <?php echo ($selectedUserLevel == '7') ? 'selected' : ''; ?>> level 7: System admin (technical non managing person)  </option>
                    </select>

                </div>
               
            </div>
            <div class=" card-footer">
                      <button type="submit" name="edit_user" class=" btn btn-info btn-sm">Edit User</button>
            </div>
         
        </form>
  
</div>