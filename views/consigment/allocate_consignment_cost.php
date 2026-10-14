<?php
              $consgId = (int) $conn->real_escape_string($_GET['consgn_id']);
              

              ?>
                  <div class="  ">

                      <div class=" card card-primary">
                        <div class=" card-header"> <h4>Consignment Details</h4></div>
                        <div class=" card-body">
             <?php      
             $consignRef = "";
                        $consignement = selectConsignmentById($conn, $consgId);
          if($consignement && is_array($consignement)){
            $consignRef =$consignement['ref_no'];
              // head
            ?>  
            <div class=" row box  p-3">
                <div class=" col-2  border border-dark">
                    <div class="  border-bottom border-dark ">
                          <h4 for="" class='text-center'>Status:</h4>
                    </div>
                    <div class=" ">
                          <h5 for="" class="text-center"><?= $consignement['status'] ?></h5>
                    </div>
                </div>
                <div class=" col-2  border border-dark">
                    <div class="  border-bottom border-dark ">
                          <h4 for="" class='text-center'> Customer:</h4>
                    </div>
                    <div class=" ">
                          <h5 for="" class="text-center"><?= $consignement['mteja'] ?></h5>
                    </div>
                </div>
                <div class=" col-2 border border-dark">
                    <div class="  border-bottom border-dark ">
                          <h4 for="" class='text-center'> Date :</h4>
                    </div>
                    <div class=" ">
                          <h5 for="" class="text-center"><?= $consignement['date_'] ?></h5>
                    </div>
                </div>
                <div class=" col-2 border border-dark">
                    <div class="  border-bottom border-dark ">
                          <h4 for="" class='text-center'> Shipping Date :</h4>
                    </div>
                    <div class=" ">
                          <h5 for="" class="text-center"><?= $consignement['ship_date'] ?></h5>
                    </div>
                </div>
                <div class=" col-2  border border-dark">
                    <div class="  border-bottom border-dark ">
                          <h4 for="" class='text-center'> Return Date :</h4>
                    </div>
                    <div class=" ">
                          <h5 for="" class="text-center"><?= $consignement['return_date'] ?></h5>
                    </div>
                </div>
                <div class=" col-2 border border-dark">
                    <div class="  border-bottom border-dark ">
                          <h4 for="" class='text-center'> Lood Type :</h4>
                    </div>
                    <div class=" ">
                          <h5 for="" class="text-center"><?= $consignement['load_type'] ?></h5>
                    </div>
                </div>
            </div>
            <?php
          }
      ?>
   
        <div class="my-2">
            <h5>Consingment Distribution</h5>
            <div class=" table-responsive">
                <table class=" table table-sm table-bordered">
                  <tr>
                    <td>#</td>
                    <td>Truck</td>
                    <td>Trailer</td>
                    <td>Driver</td>
                    <td>Route</td>
                    <td>Distance</td>
                  </tr>
                  <!-- acquire details  -->
                   <?php
                    $body = selectConsignmentBody($conn,$consgId);
                    // print_r($body);
                    if($body && is_array($body)){
                      $counter = 1;
                      foreach($body as $b){
                        echo "<tr>";
                          echo "<td>$counter</td>";
                          echo "<td>$b[truck]</td>";
                          echo "<td>$b[trailer]</td>";
                          echo "<td>$b[dereva]</td>";
                          echo "<td>$b[route]</td>";
                          echo "<td>$b[distance]</td>";
                        echo "</tr>";
                        $counter++;
                      }
                    }
                   ?>
                </table>
            </div>
        
                        <div></div>

                            <div class=" my-3 mx-3">
                                <h5>Expenses Items</h5>
                                <form action="./controllers/consignment_controller.php" class=" was-validated" method="post">
                                    <div class="form-group">
                                          <input type="hidden" name="consign_id" value='<?=$consgId ?>'>
                                          <input type="hidden" name="consign_ref" value='<?=$consignRef ?>'>
                                        <label for="exampleInputPassword1">Involved Shipment Cost</label>
                                        <div class=" form-group col-sm-10 col-md-5">
                                            <label for="">Select Payment Mode</label>
                                            <select name="payment_method" required class=" form-control select2-form select2bs4-form" id="">
                                              <option value="">Select Option </option>
                                              <?php
                                                    $cashes = selectSubsidiariesBYledgerId($conn,32);
                                                    if($cashes && is_array($cashes)){
                                                      foreach($cashes as $cash){
                                                        echo "<option value='$cash[id]'> $cash[name] </option>";
                                                      }
                                                    }
                                                    $banks = selectSubsidiariesBYledgerId($conn,33);
                                                    if($banks && is_array($banks)){
                                                      foreach($banks as $bank){
                                                        echo "<option value='$bank[id]'> $bank[name] </option>";
                                                      }
                                                    }
                                              ?>
                                            </select>
                                        </div>
                                          <div class=" form-group col-md-5 col-sm-10">
                                                <label for="" class=" form-label">Current used</label>
                                                <select name="currency" id="" class=" form-control select2-form select2bs4-form" onchange="selectCurrency(this.value)" required>

                                        <option value="">Select below</option>
                                            <?php
                                                $sql = "SELECT * FROM currency";
                                                $query = mysqli_query($conn,$sql);
                                                while($rows = mysqli_fetch_assoc($query)){
                                                    ?>
                                                    <option value="<?php echo $rows['id']?>"><?php echo $rows['name'] ?></option>
                                                    <?php
                                                }
                                            ?>


                                        </select>

                                              </div>
                                              <div class=" form-group col-md-6 col-sm-10">
                                                <label for="" class=" form-label">Exchange Rate</label>
                                                        <div id="currency1">

                                                        </div>
                                              </div>

                                        <div class='my-3'>
                                          <button type="button" onclick="addShipmentExpItem()"; class=' btn btn-sm btn-outline-info text-dark'>Add row</button>
                                          <button type="button" onclick="removeTableRow('expenseTable')" class=' btn btn-sm btn-outline-danger text-dark' >remove row</button>
                                        </div>
                                        <div class=" my-2">
                                          <table class=' table table-sm' id='expenseTable'>
                                            <tr class=' table-danger'>
                                              <td>Expense Item</td>
                                              <td>Driver</td>

                                              <td>Amount</td>
                                              <td>Eqv</td>
                                            </tr>
                                            <tr>
                                              <td>
                                              <div class="form-group">
                                                        <select  name='cr_account[]' required  class="form-control select2-form select2bs4-form">
                                                      <option value="">Select Below</option>
                                                    <?php
                                                        $costs =  selectSUbsidiariesBySubmainId($conn,8);
                                                        if($costs && is_array($costs)){
                                                          foreach($costs as $cost){
                                                            echo "<option value='$cost[id]'>$cost[name]  </option>";
                                                          }
                                                        }
                                                    ?>
                                                    </select>
                                                </div>
                                              </td>
                                              <td>
                                                  
                                              <div class="form-group">

                                              <select name="drivers[]" class=" select2-form select2bs4-form form-control"  required id="">
                                                        <option value="">Select below</option>
                                                        <?php
                                                              $drivers = selectSubsidiaryBySubCategory($conn, "driver", "active");
                                                              if($drivers && is_array($drivers)){
                                                                foreach($drivers as $driver){
                                                                  echo "<option value='$driver[id]'>$driver[name]</option>";
                                                                }
                                                              }
                                                        ?>
                                                    </select>
                                              </div>
                                               </td>
                                               <td>
                                            

                                                 <input type="text" step='any'  required  name="" class="form-control debt_amount" oninput="this.value = this.value.replace(/[^0-9.,]/g, ''); numberFormat(this); calculateEqv()"  id="exampleInputPassword1" placeholder="Enter amount">
                                              
                                              </td>
                                                <td>
                                        

                                                <input type="text" step='any'  required  name="amount[]" class="form-control equiv_dr"  id="exampleInputPassword1" readonly placeholder="Eqv">
                                           
                                              </td>
                                            </tr>
                                          </table>
                                        </div>
                                                      </div>
                                                      </div>
                                                      </div>

                                                      <div class=" card-footer">
                                                         <button type="submit" name='allocate_consgn' class=' btn btn-sm btn-success'>Allocate Cost</button>
                                                       </div>
                                </form>

                                <?php
                                 
                                ?>

                      </div>
                  </div>
              <?php
            