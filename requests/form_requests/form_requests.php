<?php
    require_once "../../configs.php";
    require_once "../../functions/min_sub_functions.php";
     require_once "../../functions/retail_functions.php";
    require_once "../../functions/subsidiary_functions.php";
       require_once "../../functions/utilities_functions.php";
           require_once "../../functions/cost_center_functions.php";
              require_once "../../functions/voucher_functions.php";
    $conn = openConn();
    
    
    session_start();

    
    
    

    if($_SERVER['REQUEST_METHOD'] === "GET"){
         if(isset($_GET['select_subs'])){
                                        $data = [];
                                        $items  = selectAllSubsidiaries($conn);
                                         if($items && is_array($items)){
                                           foreach($items as $item){
                                             $data[] = [
                                                "id" => $item['id'], 
                                              "name" => $item['name']
                                            ];
                                           }
                                         }
                          echo json_encode($data);
              }
             
 
            if(isset($_GET['consignment_vehicle'])){
                $itemtype = $_GET['consignment_vehicle'];
                ?>
                    <select name="truck[]" class=" select2-in select2bs4 form-control"  required id="">
                                                          <option value="">Select below</option>
                                                          <?php
                                                                $vehicles = selectMinSubByType($conn,$itemtype);
                                                                if($vehicles && is_array($vehicles)){
                                                                  foreach($vehicles as $vehicle){
                                                                    echo "<option value='$vehicle[id]'>$vehicle[name]</option>";
                                                                  }
                                                                }
                                                          ?>
                                                      </select>
                <?php
              }
                if(isset($_GET['form_driver_array'])){
               
                ?>
                  <select name="drivers[]" class=" select2-in select2bs4 form-control"  required id="">
                                                          <option value="">Select below</option>
                                                          <?php
                                                                $drivers =  selectSubsidiaryBySubCategory($conn, "driver", "active");
                                                                if($drivers && is_array($drivers)){
                                                                  foreach($drivers as $driver){
                                                                    echo "<option value='$driver[id]'>$driver[name]</option>";
                                                                  }
                                                                }
                                                          ?>
                                                      </select>
                <?php
              }
               if(isset($_GET['form_vehicle_array'])){

                ?>
                    <select name="truck[]" class=" select2-form select2bs4-form"  required id="">
                                                          <option value="">Select below</option>
                                                          <?php
                                                                $vehicles = selectAllMinSub($conn);
                                                                if($vehicles && is_array($vehicles)){
                                                                  foreach($vehicles as $vehicle){
                                                                    echo "<option value='$vehicle[id]'>$vehicle[name]</option>";
                                                                  }
                                                                }
                                                          ?>
                                                      </select>
                <?php
              }
              if(isset($_GET['form_trailer_array'])){
                
                ?>
                    <select name="trailer[]" class=" select2-in select2bs4 form-control"  required id="">
                                                          <option value="">Select below</option>
                                                          <?php
                                                                $vehicles = selectMinSubByType($conn,"trailer");
                                                                if($vehicles && is_array($vehicles)){
                                                                  foreach($vehicles as $vehicle){
                                                                    echo "<option value='$vehicle[id]'>$vehicle[name]</option>";
                                                                  }
                                                                }
                                                          ?>
                                                      </select>
                <?php
              }
            if(isset($_GET['consignment_vehicle1'])){
                $itemtype = $_GET['consignment_vehicle1'];
                ?>
                    <select name="trailer[]" class=" select2-in select2bs4 form-control"  required id="">
                                                          <option value="">Select below</option>
                                                          <?php
                                                                $vehicles = selectMinSubByType($conn,$itemtype);
                                                                if($vehicles && is_array($vehicles)){
                                                                  foreach($vehicles as $vehicle){
                                                                    echo "<option value='$vehicle[id]'>$vehicle[name]</option>";
                                                                  }
                                                                }
                                                          ?>
                                                      </select>
                <?php
              }
                
              if(isset($_GET['get_voucher_get_minsub_items'])){
                ?>
                      <select name="item[]" id="" class=" select2 select2bs4 form-control w3-border" required>
                                                                    <option value="">Select below</option>
                                                                        <?php
                                                                            $items  = selectAllMinSub($conn);
                                                                            if($items && is_array($items)){
                                                                              foreach($items as $item){
                                                                                echo "<option value='$item[id]'> $item[name] </option>";
                                                                              }
                                                                            }
                                                                            ?>
                                                                    </select>
                <?php
              }
                if(isset($_GET['select_opening_pump'])){
                $id = $_GET['select_opening_pump'];
                ?>

                  <label for="" class=" form-label">Select Pump</label>
                  <select name="pump_id" class=" form-control" required id="">
                    <option value="">Select Below</option>
                    <?php
                        $pumps = selectPumpFromStation($conn, $id);
                        if($pumps && is_array($pumps)){
                          foreach($pumps as $pump){
                            echo "<option value='$pump[id]'>$pump[pump_name]</option>";
                          }
                        }
                    ?>
                  </select>
                <?php

              }
              if(isset($_GET['consignment_route'])){
                $itemtype = $_GET['consignment_route'];
                ?>
                    <select name="routes[]" class=" select2-in select2bs4 form-control"  required id="">
                                                          <option value="">Select below</option>
                                                          <?php
                                                                $routes = selectSubsidiaryByType($conn, "route", "active");
                                                                if($routes && is_array($routes)){
                                                                  foreach($routes as $route){
                                                                    echo "<option value='$route[id]'>$route[name]</option>";
                                                                  }
                                                                }
                                                          ?>
                                                      </select>
                <?php
              }
              if(isset($_GET['consignId_driver'])){
                $cosingmentId = (int) $conn->real_escape_string($_GET['consignId_driver']);
                ?>
                  <select name="drivers[]" class=" select2-in select2bs4 form-control"  required id="">
                                                          <option value="">Select below</option>
                                                          <?php
                                                                $drivers =  selectConsignmentDriver($conn, $cosingmentId);
                                                                if($drivers && is_array($drivers)){
                                                                  foreach($drivers as $driver){
                                                                    echo "<option value='$driver[id]'>$driver[name]</option>";
                                                                  }
                                                                }
                                                          ?>
                                                      </select>
                <?php
              }
            if(isset($_GET['select_sub_cost_center_by_cost_center_id'])){
                  $costCenterId = (int) $_GET['select_sub_cost_center_by_cost_center_id'] ;

                ?>
                    <select name="sub_cost_center_id" class=" form-control" required id="">
                      <option value="">Select Below</option>
                      <?php
                          $subcostCenters = selectSubCostCentersByCostCenterId($conn,$costCenterId);
                          if($subcostCenters && is_array($subcostCenters)){
                            foreach($subcostCenters as $subcostCenter){
                              echo "<option value='$subcostCenter[id]'>$subcostCenter[name]</option>";
                            }
                          }
                      ?>
                    </select>
                <?php

              }
               
               if(isset($_GET['get_voucher_get_subs_items'])){
                ?>
                      <select name="item[]" id="" class=" select2-form select2bs4-form" required>
                                                                    <option value="">Select below</option>
                                                                        <?php
                                                                            $items  = selectAllSubsidiaries($conn);
                                                                            if($items && is_array($items)){
                                                                              foreach($items as $item){
                                                                                echo "<option value='$item[id]'> $item[name] </option>";
                                                                              }
                                                                            }
                                                                            ?>
                                                                    </select>
                <?php
              }
              if(isset($_GET['get_form_unit_select'])){
                ?>
                    <select name="unit[]" id="" class=" form-control select2-form select2bs4-form " required>
                                                        <option value="">Select below</option>
                                                                <?php
                                                                    $units = selectAllUnit($conn);
                                                                    if($units && is_array($units)){
                                                                        foreach($units as $unit){
                                                                            echo "<option value='$unit[id]'>$unit[name]</option>";
                                                                        }
                                                                    }
                                                                ?>      

                                                    </select>
                <?php
              }
              if(isset($_GET['select_voucher_by_type'])){
//print_r($_GET);
            $date1 = $conn->real_escape_string($_GET['date1']);
            $date2 = $conn->real_escape_string($_GET['date2']);
            $type = $conn->real_escape_string($_GET['type']);
            $transaction = [];
            if($type == "all"){
              $transactions = selectVoucherByAndDateRange($conn, $date1, $date2);
            } else{
              $transactions = selectVoucherByRefTypeAndDateRange($conn, $type, $date1, $date2);
            }
            //$transactions = selectVoucherByRefTypeAndDateRange($conn, $type, $date1, $date2);
            // print_r($transactions);
            ?>
            <style>
                  .action-btn{
                    display: none;
                 
                      margin-bottom: 10px !important;
                      /* background-color: red !important; */
                      color: white !important;
                      padding: 5px 10px !important;
                      border: none !important;
                      cursor: pointer !important;
                }
                
                </style>
                    <button class=" action-btn btn btn-danger" onclick="handleAction('delete_voucher')">Delete</button>
             
            <div class=" table-responsive">
                <table class="table table-bordered table-sm">
                    <tr class="table-primary">
                        <td></td>
                        <td>#</td>
                        <td>Date</td>
                        <td>Ref No</td>
                        <td>Debit account</td>
                        <td>Amount</td>
                        <td>Credit Account</td>
                        <td>Narration</td>
                        <td>Status</td>
                        <td>Action</td>
                    </tr>
                    <?php
                    $usertype = $_SESSION['usertype'] ?? '';
                    $userlevel = $_SESSION['userlevel'] ?? '';
                
                    if ($transactions && is_array($transactions)) {
                        $counter = 1;
                        foreach ($transactions as $transaction) {
                            echo "<tr>";
                
                            if (
                                ($usertype === 'accountant' && $userlevel == 5) ||
                                $usertype === 'Admin'
                            ) {
                                echo "<td><input type='checkbox' name='id[]' class='checkbox' onchange='showActionButton()' value='{$transaction['id']}'></td>";
                            } else {
                                echo "<td>-</td>";
                            }
                
                            echo "
                                <td>$counter</td>
                                <td>{$transaction['date_']}</td>
                                <td>{$transaction['reference_no']}</td>
                                <td>{$transaction['debit_acc']}</td>
                                <td>" . number_format($transaction['dr_ammount'], 2) . "</td>
                                <td>{$transaction['credit_acc']}</td>
                                <td>{$transaction['description']}</td>
                                <td>{$transaction['status']}</td>
                                <td>
                                    <a class='btn btn-info btn-sm' href='./?page=transaction_pdf&id={$transaction['id']}'>Print Voucher</a>
                                    <a class='btn btn-warning btn-sm m-1' href='./?page=edit_transaction&id={$transaction['id']}'>Edit</a>
                                </td>
                            </tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='10'>No Record Found</td></tr>";
                    }
                    ?>
                </table>

              </div>
            <?php
            
          }
          if(isset($_GET['select_purchase_voucher'])){
            $date1 = $conn->real_escape_string($_GET['date1']);
            $date2 = $conn->real_escape_string($_GET['date2']);
           
            ?>
              <div class=" table-responsive">
                 <h5>Purches Voucher</h5>
                   <table class=" table table-sm table-striped">
                            <thead>
                                <tr class=" table-danger">
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Details</th>
                                    <th>type</th>
                                    <th>Debit Amount</th>
                                    <th>Credit Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                     $purchases = selectPurchasesByDateRange($conn, $date1, $date2);
                                    //  print_r($purchases);
                                     if($purchases && is_array($purchases)){
                                       $i = 1;
                                       foreach($purchases as $purchase){
                                         echo "<tr>
                                            <td>$i</td>
                                            <td>$purchase[date_]</td>
                                            <td>$purchase[reference]</td>
                                            <td>$purchase[name]</td>
                                            <td>purchases</td>
                                            <td>$purchase[cr_ammount]</td>
                                            <td>$purchase[cr_ammount]</td>
                                            </tr>";
                                            $i++;
                                       }
                                     }
                                ?>
                            </tbody>
                        </table>
              </div>
            <?php
          } 
          
     if(isset($_GET['select_sales_voucher'])){
    $date1 = $conn->real_escape_string($_GET['date1']);
    $date2 = $conn->real_escape_string($_GET['date2']);
    ?>
    <div class="table-responsive">
        <h5>Sales Voucher</h5>
        <table class="table table-sm table-striped table-bordered table-search">
            <thead>
                <tr class='table-primary'>
                    <td>#</td>
                    <td>Date</td>
                    <td>ref No</td>
                    <td>Particular</td>
                    <td>type</td>
                    <td>Debit Amount</td>
                    <td>Credit Amount</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
                <?php
                $sales = selectAllInvoicesByDateRange($conn, $date1, $date2);
                if($sales && is_array($sales)){
                    $counter = 1;
                    foreach($sales as $sale){
                        echo "<tr>";
                        echo "<td>$counter</td>";  
                        echo "<td>$sale[date_]</td>";  
                        echo "<td>$sale[reference]</td>";  
                        echo "<td>$sale[name]</td>";  
                        echo "<td>sales</td>";  
                        echo "<td>". number_format($sale['dr_amount'],2)."</td>"; 
                        echo "<td>". number_format($sale['dr_amount'],2)."</td>"; 
                        echo "<td>";
                        
                        echo "<button class='btn btn-info btn-sm select-account-btn' 
                                data-toggle='modal' 
                                data-target='#selectAccountModal' 
                                data-invoice-id='{$sale['id']}'>
                                Print Invoice
                              </button> ";
                        
                      
                        echo "<a href='./?page=edit_invoice&invoice_id={$sale['id']}' 
                                class='btn btn-warning btn-sm'>
                                Edit
                              </a>";
                        echo "</td>";

                        echo "</tr>";
                        $counter++;
                    }
                }
                ?>
            </tbody>
        </table>
        
        <!-- Account Selection Modal -->
        <div class='modal fade' id='selectAccountModal'>
            <div class='modal-dialog'>
                <div class="modal-content">
                    <div class='modal-header bg-info'>
                        <h4>Select Bank Account</h4>
                        <button type="button" data-dismiss='modal' class='btn btn-danger'>&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="./processes/printings/print_invoice.pdf.php" method="POST" target="_blank">
                            <input type="hidden" name="id" id="invoiceId">
                            
                            <div id="accounts"></div>
                            
                            <button type="submit" class='btn btn-info'>OK</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        document.querySelectorAll('.select-account-btn').forEach(btn => {
            btn.onclick = function() {
                document.getElementById('invoiceId').value = this.dataset.invoiceId;
                loadAccounts(); // This function is imported from another JS file
            }
        });
        </script>
    </div>
    <?php
}
           if(isset($_GET['select_all_subsidiaries'])){
            // print_r($_GET);
            ?>
              <div class=" table-responsive">
                   <table class=" table table-bordered table-striped table-sm table-search">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th> Name</th>
                                <th>Type</th>
                                <th>CostCenter</th>
                          
                                <th>Ledger</th>
                                <td>Action</td>
                            </tr>
                        </thead>
                       <tbody>
                        <?php
                            $subs = getAllSubsidiaries($conn);
                            if($subs && is_array($subs)){
                                $counter = 1;
                                foreach($subs as $sub){
                                    $costCenterId = (int) $sub['cost_center'];
                                    echo "<tr>";
                                        echo "<td>$counter</td>";
                                        echo "<td>$sub[name]</td>";
                                        echo "<td>$sub[type]</td>";
                                        $costCenter = selectCostCenterById($conn, $costCenterId);
                                        if($costCenter && is_array($costCenter)){
                                            echo "<td>$costCenter[name]</td>";
                                        } else {
                                            echo "<td>Not Set</td>";
                                        }
                    
                                        echo "<td>$sub[ledger]</td>";
                                        echo "<td>
                                            <a href='./?page=edit_subsidiary&id=$sub[id]' class='btn btn-sm btn-primary my-1'>Edit</a>
                                            <a href='./?page=view_subsidiary_report&id=$sub[id]' class='btn btn-sm btn-secondary my-1'>View Report</a>";
                    
                                         
                                        if (
                                            (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'accountant' && $_SESSION['userlevel'] == 5)
                                            || 
                                            (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'Admin')
                                        ) {
                                            echo "<a href='./?page=delete_subsidiary&sub={$sub['id']}' 
                                                class='btn btn-sm btn-danger my-1' 
                                                onclick=\"return confirm('Are you sure you want to delete this item?');\">
                                                Delete
                                              </a>";

                                        }
                    
                                        echo "</td>";
                                    echo "</tr>";
                                    $counter++;
                                }
                            }
                        ?>
                    </tbody>

                    </table>

              </div>
            <?php
          }
          if(isset($_GET['select_subsidiaries_by_type'])){
          //  print_r($_GET);
            $type = $conn->real_escape_string($_GET['type']);
            $subsidiaries = [];
            if($type === 'deleted'){
              $subsidiaries = selectDeletedSubsidiaries($conn);
            } else{
              $subsidiaries = selectSubsidiaryByType($conn, $type);
            }
                  ?>
                <div class=" table-responsive">
                        <table class=" table table-bordered table-striped table-sm table-search">
                              <thead>
                                  <tr>
                                      <th>SN</th>
                                      <th> Name</th>
                                      <th>Type</th>
                                      <th>CostCenter</th>
                                
                                      <th>Ledger</th>
                                      <td>Action</td>
                                  </tr>
                              </thead>
                              <tbody>
                                <?php
                                    $usertype = $_SESSION['usertype'] ?? '';
                                    $userlevel = $_SESSION['userlevel'] ?? '';
                                
                                    if ($subsidiaries && is_array($subsidiaries)) {
                                        $counter = 1;
                                        foreach ($subsidiaries as $sub) {
                                            $costCenterId = (int) $sub['cost_center'];
                                            echo "<tr>";
                                            echo "<td>$counter</td>";
                                            echo "<td>$sub[name]</td>";
                                            echo "<td>$sub[type]</td>";
                                            $costCenter = selectCostCenterById($conn, $costCenterId);
                                            if ($costCenter && is_array($costCenter)) {
                                                echo "<td>$costCenter[name]</td>";
                                            } else {
                                                echo "<td>Not Set</td>";
                                            }
                                            echo "<td>$sub[ledger]</td>";
                                
                                            if ($type !== 'deleted') {
                                                echo "<td>";
                                                echo "<a href='./?page=edit_subsidiary&id=$sub[id]' class='btn btn-sm btn-primary my-1'>Edit</a> ";
                                                echo "<a href='./?page=view_subsidiary_report&id=$sub[id]' class='btn btn-sm btn-secondary my-1'>View Report</a> ";
                                
                                                // Only show delete if user is accountant level 5 OR admin
                                                if (($usertype === 'accountant' && $userlevel == 5) || $usertype === 'admin') {
                                                   echo "<a href='./?page=delete_subsidiary&sub={$sub['id']}' 
                                                    class='btn btn-sm btn-danger my-1' 
                                                    onclick=\"return confirm('Are you sure you want to delete this item?');\">
                                                    Delete
                                                  </a>";

                                                }
                                
                                                echo "</td>";
                                            } else {
                                                echo "<td>
                                                    <a href='./?page=restore_subsidiary&sub=$sub[id]' class='btn btn-sm btn-primary my-1'>Restore</a>
                                                </td>";
                                            }
                                
                                            echo "</tr>";
                                            $counter++;
                                        }
                                    }
                                ?>
                                </tbody>

                          </table>

                    </div>
            <?php
            
          }
          
          
          if (isset($_GET['project_sub_cost_item'])) {
            $items = selectCOAByMasterId($conn, 8,1);
            echo '<option value="">Select Below</option>';
            if($items && is_array($items)){
                foreach($items as $item){
                    echo "<option value='{$item['sub_id']}'>" . htmlspecialchars($item['subs']) . "</option>";
                }
            } else {
                echo '<option value="">No items found</option>';
            }
        }
  
 
    }

?>