<?php



              $consgId = (int) $conn->real_escape_string($_GET['consgn_id']);

              ?>
              
                  <div class="  ">

                      <div class=" card card-primary">
                        <div class=" card-header"> <h4>Consignment Details</h4></div>
                        <div class=" card-body">
                         

                            <?php
                                $consignement = selectConsignmentById($conn, $consgId);
                                if($consignement && is_array($consignement)){
                                    // head
                                  ?>  
                                  <div class=" row box  p-3">
                                      <div class=" col-md-2 col-sm-10 border border-dark">
                                          <div class="  border-bottom border-dark ">
                                                <h4 for="" class='text-center'>Status:</h4>
                                          </div>
                                          <div class=" ">
                                                <h5 for="" class="text-center"><?= $consignement['status'] ?></h5>
                                          </div>
                                      </div>
                                      <div class=" col-md-2 col-sm-10 border border-dark">
                                          <div class="  border-bottom border-dark ">
                                                <h4 for="" class='text-center'> Customer:</h4>
                                          </div>
                                          <div class=" ">
                                                <h5 for="" class="text-center"><?= $consignement['mteja'] ?></h5>
                                          </div>
                                      </div>
                                      <div class=" col-md-2 col-sm-10 border border-dark">
                                          <div class="  border-bottom border-dark ">
                                                <h4 for="" class='text-center'> Date :</h4>
                                          </div>
                                          <div class=" ">
                                                <h5 for="" class="text-center"><?= $consignement['date_'] ?></h5>
                                          </div>
                                      </div>
                                      <div class=" col-md-2 col-sm-10 border border-dark">
                                          <div class="  border-bottom border-dark ">
                                                <h4 for="" class='text-center'> Shipping Date :</h4>
                                          </div>
                                          <div class=" ">
                                                <h5 for="" class="text-center"><?= $consignement['ship_date'] ?></h5>
                                          </div>
                                      </div>
                                      <div class=" col-md-2 col-sm-10 border border-dark">
                                          <div class="  border-bottom border-dark ">
                                                <h4 for="" class='text-center'> Return Date :</h4>
                                          </div>
                                          <div class=" ">
                                                <h5 for="" class="text-center"><?= $consignement['return_date'] ?></h5>
                                          </div>
                                      </div>
                                      <div class=" col-md-2 col-sm-10 border border-dark">
                                          <div class="  border-bottom border-dark ">
                                                <h4 for="" class='text-center'> Lood Type :</h4>
                                          </div>
                                          <div class=" ">
                                                <h5 for="" class="text-center"><?= $consignement['load_type'] ?></h5>
                                          </div>
                                      </div>
                                  </div>
                                    <div class=" mt-2 table-info">
                                         <div class=" card-header">
                                          <h4 class='card-title'>Customer Details</h4>
                                          <div class=" card-tools">
                                            <button data-target="#edit_customer_model" data-toggle="modal" class="  btn btn-sm btn-warning"  > <i class=" fas fa-edit"></i> Edit</button>
                                          </div>
                                        </div>
                                    </div>
                                  
                                      <!-- get CUstomer details -->
                                       <?php
                                        $customer = selectSubsidiaryById($conn,$consignement['customer_id']);
                                        if($customer && is_array($customer)){
                                          ?>
                                            <div class=" row px-1 py-2 border-bottom border-dark">
                                                <div class=" col-md-2 border-right border-dark">
                                                  <h6>Name:<?= $customer['name'] ?></h6>
                                                </div>
                                                <div class=" col-md-2 border-right border-dark">
                                                  <h6>phone:<?= $customer['phone'] ?></h6>
                                                </div>
                                                <div class=" col-md-2 border-right border-dark">
                                                  <h6>Address:<?= $customer['address'] ?></h6>
                                                </div>
                                                <div class=" col-md-2 border-right border-dark">
                                                  <h6>Category:<?= $customer['subcategory'] ?></h6>
                                                </div>
                                            </div>
                                          <?php
                                        }
                                       ?>

                                  
                                  <?php
                                }
                            ?>
                              <div class="my-2 table-info p-1">
                                 <div class=" card-header">
                                      <h5 class=" card-title">Consingment Distribution</h5>
                                  <div class=" card-tools">
                                    <button type="button" class=" btn btn-sm btn-info" data-target='#addDistribution' data-toggle="modal"> <i class=" fa fa-plus"></i> Add Distribution</button>
                                    <a href="./?page=consignments_list" class=" btn btn-sm btn-warning"  > <i class=" fas fa-arrow-left"></i> Back</a>
                                    <a href="./" class=" btn btn-sm btn-danger"> <i class=" fas fa-home"></i> Home</a>

                                  </div>
                                </div>
                              </div>
                              <!-- for editing -->
                                   <?php
                                        if($consignement['status'] !== "closed"){
                                              ?>
                                        <div class=" m-3 table-responsive">
                                              <h4>Consignment Summary for editing</h4>

                                                <table class=" table table-sm table-bordered ">
                                                <tr>
                                                  <td>#</td>
                                                  <td>Truck</td>
                                                  <td>trailer</td>
                                                  <td>Driver</td>
                                                  <td>Route</td>
                                                  <td>Distance</td>
                                                  <td>Action </td>
                                                </tr>
                                                <!-- acquire details  -->
                                                <?php
                                                  $body = selectConsignmentBody($conn,$consgId);
                                                   //print_r($body);
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
                                                        echo "<td>
                                                                  <button class=' m-1 btn btn-sm btn-info luch-edit-modal' data-target='#edit_modal' data-toggle='modal' data-id='$b[id]' data-truck-id='$b[truck_id]' data-trailer-id='$b[trailer_id]' data-truck-name='$b[truck]' data-trailer-name='$b[trailer]' data-driver-id='$b[driver_id]' data-driver-name='$b[dereva]' data-distance='$b[distance]' > Edit </button>
                                                                  <a href='./controllers/consignment_controller.php?removeConsgBody&id=$b[id]' class=' m-1 btn btn-sm btn-danger'> remove  </a>
                                                              </td>";

                                                      echo "</tr>";
                                                      $counter++;
                                                    }
                                                  }
                                                ?>
                                              </table>
                                        </div>
                                        <?php
                                        }
                                 
                                   
                                       $body = selectConsignmentBody($conn,$consgId);
                                       // print_r($body);
                                       if($body && is_array($body)){
                                        $co = 1;
                                        foreach($body as $b){
                                          ?>
                                           <div class=" card card-outline card-warning ">
                                            <div class=" card-header">
                                               <h5 class=" card-title"><?= $co . ".". $b['dereva'] ?></h5>
                                               <!--<h5 class=" card-title"><?= $co . ".Driver:". $b['dereva'] . $b["driver_id"]  . "id:" . $b['id'] ?></h5>-->
                                            </div>
                                            <div class=" card-body">
                                                <h6>Truck: <?= $b['truck'] ?></h6>
                                                <h6>Trailer: <?= $b['trailer'] ?></h6>
                                                <h6>Route: <?= $b['route'] ?></h6>
                                                <h6>Route Distance: <?= $b['distance'] ?></h6>
                                                <h6>Cost Distribution</h6>
                                                <?php
                                                    $AmountProvided =0;
                                                    $amountSpent = 0;
                                                    $distributions  = selectConsignmentCost($conn,$consgId);
                                                    // print_r($distributions);
                                                    if($distributions && is_array($distributions)){
                                                      foreach($distributions as $distribution){
                                                        if($b['truck_id'] == $distribution['vehicle_id']){
                                                              $AmountProvided += $distribution['cr_ammount'];
                                                              
                                                        }
                                                        
                                                      }
                                                      ?>
                                                        <div class=" m-1">
                                                          <h5>Amount Provided: <?= number_format($AmountProvided,2)  ?> </h5>
                                                         
                                                        </div>
                                                          <div class=" table-responsive form-group">
                                                              <h4>Cost Allocation</h4>
                                                              <table class=" table table-bordered table-sm">
                                                                <tr>
                                                                  <td>#</td>
                                                                  <td>Date</td>
                                                                
                                                                  <td>Item Name</td>
                                                                  <td>Amount</td>
                                                                  <td>Action</td>
                                                                </tr>
                                                                <?php
                                                                    $costs = selectConsignmentCost($conn, $consgId);
                                                                    if($costs && is_array($costs)){
                                                                      $counter =1;
                                                                      foreach($costs as $cost){
                                                                        if($b['truck_id'] == $cost['vehicle_id']){
                                                                        echo "<tr>";
                                                                            echo "<td>$counter </td>";
                                                                            echo "<td>$cost[date_]</td>";
                                                                             echo "<td>$cost[name]</td>";
                                                                           
                                                                            echo "<td>". number_format( $cost['dr_ammount'],2)."</td>";
                                                                             echo "<td><a class=' btn btn-danger btn-sm' href='./controllers/consignment_controller.php?delete_cost&id=$cost[id]&transaction_id=$cost[id]' onclick='return confirm(\"Are you sure you want to delete this?\")' href='./?page=delete_cost&id=$cost[id]'  >Delete</a></td>";

                                                                        echo "</tr>";
                                                                        $counter++;
                                                                        }
                                                                        // else{
                                                                        //   echo "<pre>";
                                                                        //   echo $cost['vehicle_id'] . " !=" . $b['truck_id'];
                                                                        //   echo "</pre>";
                                                                        // }
                                                                      }
                                                                    }
                                                                ?>
                                                              </table>
                                                          </div>
                                                       
                                                      
                                                      <?php
                                                    }

                                                ?>
                                            </div>
                                           </div>
                                          <?php
                                          $co++;

                                        }
                                       }
                                    ?>
                                      
                                      <table class=" table table-sm table-bordered d-none">
                                        <tr>
                                          <td>#</td>
                                          <td>Truck</td>
                                          <td>trailer</td>
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
                              
                             
                        </div>
                        <div class=" card-footer">
                            <a target='_blank' href="./processes/printings/print_consign.pdf.php?consid=<?=$consgId?>">Print Consignment</a>
                        </div>
                      </div>
                  </div>
               <!-- modal go here -->
                   <div class=" modal fade" id="edit_modal">
                            <div class=" modal-dialog">
                                       <div class=" modal-content">
                                            <div class=" modal-header">
                                                <h4 class=" modal-title">Update Consignment Details</h4>
                                                <button type="button" class=" btn btn-danger" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class=" modal-body">
                                                <form action="./controllers/consignment_controller.php" method="post" class=" was-validated">
                                                  <input type="hidden" name="id" readonly class=" form-control" id="">
                                                    <div class="form-group">
                                                        <label for="">Truck</label>
                                                        <input readonly type="text" name="truck" class=" form-control" id="">

                                                    </div>
                                                    <div class=" form-group">
                                                      <label for="" class=" form-label"> trailer</label>
                                                      <input type="text" name="trailer" class=" form-control" id="" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="">Truck Driver</label>
                                                        <input readonly type="text" name="driver" class=" form-control" id="">

                                                    </div>
                                                    <div class=" form-group">
                                                        <label for="" class=" form-label"> Curret Distance</label>
                                                        <input type="number" required name="distace1" class=" form-control" id="">
                                                    </div>
                                                    <div class=" form-group">
                                                        <label for="" class=" form-label"> New distance Distance</label>
                                                        <input type="number" required name="distance2" class=" form-control" id="">
                                                    </div>
                                                    <div class=" card-footer">
                                                        <button type="submit" class=" bt btn-sm btn-block btn-primary" name="updatedistance">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class=" modal-footer">
                                                <button type="button" class=" btn btn-danger" data-dismiss="modal">Close</button>
                                                
                                            </div>
                                       </div>
                            </div>
                   </div>
                      <div class=" modal fade" id="addDistribution">
                          <div class=" modal-dialog modal-lg modal-dialog-scrollable">
                            <div class=" modal-content">
                              <div class=" modal-header">
                                <h4>Add new Distribution to Consignment</h4>
                                <button type="button" class=" btn btn-sm btn-danger" data-dismiss="modal">&times;</button>
                              </div>
                              <div class=" modal-body">

                                      <form action="./controllers/consignment_controller.php" method="post">
                                        <input type="hidden" name="consid" value="<?=$consgId?>">
                                         <h5>Driver And Vehicle Involved</h5>
                                              <div class=" form-group">
                                                    <div class=" my-2">
                                                        <button type="button" class=" btn btn-sm btn-warning" onclick="addConsignmentDataTableRow()">Add Row</button>
                                                        <button type="button" class=" btn btn-sm btn-danger" onclick="removeTableRow('consignmentFormDataTable')">Remove Row</button>
                                                    </div>
                                                    <div class=" form-group table-responsive">
                                                        <table class=" table table-bordered table-sm" id='consignmentFormDataTable'>
                                                            <tr class=" table-primary">
                                                                <td>Truck</td>
                                                                <td>Trailer</td>
                                                                <td>Driver</td>
                                                                <td>Route</td>
                                                                <td>Distance</td>
                                                            </tr>
                                                            <tr>
                                                              <td>
                                                                  <select name="truck[]" class=" select2-in select2bs4 form-control"  required id="">
                                                                      <option value="">Select below</option>
                                                                      <?php
                                                                            $vehicles = selectMinSubByType($conn,"truck");
                                                                            if($vehicles && is_array($vehicles)){
                                                                              foreach($vehicles as $vehicle){
                                                                                echo "<option value='$vehicle[id]'>$vehicle[name]</option>";
                                                                              }
                                                                            }
                                                                      ?>
                                                                  </select>
                                                              </td>
                                                              <td>
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
                                                              </td>
                                                              <td>
                                                                  <select name="drivers[]" class=" select2-in select2bs4 form-control"  required id="">
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
                                                              </td>
                                                              <td>
                                                                  <select name="routes[]" class=" select2-in select2bs4 form-control"  required id="">
                                                                      <option value="">Select below</option>
                                                                      <?php
                                                                            $routes = selectSubsidiaryBySubCategory($conn, "route", "active");
                                                                            if($routes && is_array($routes)){
                                                                              foreach($routes as $route){
                                                                                echo "<option value='$route[id]'>$route[name]</option>";
                                                                              }
                                                                            }
                                                                      ?>
                                                                  </select>
                                                              </td>
                                                              <td>
                                                                <input type="number" name="distance[]" class=" form-control" required id="">
                                                              </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                              </div>

                                        </div>
                                        <div class=" card-footer">
                                            <button type="submit" name="addConsignItem" class=" btn btn-sm btn-primary">Submit</button>
                                        </div>
                                    </form>


                              </div>
                            </div>
                          </div>
                        </div>
                        
                        
                        
                        <!-- customer edit modal  -->
                         <div class=" modal fade" id="edit_customer_model">
                                  <div class=" modal-dialog">
                                    <div class=" modal-content">
                                        <div class="modal-header">
                                          <h4>Edit Consignment Customer</h4>
                                          <button class=" bnt btn-danger btn-sm" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class=" modal-body">
                                          <form action="./controllers/consignment_controller.php" method="post">
                                            <input type="hidden" name="consignment_id" value="<?= $consgId?>">
                                            <div class="form-group">
                                                <label for="">Customer</label>
                                                <select name="customer_id"  class=" form-control" id="">
                                                  <?php
                                                      $customer = selectSubsidiaryById($conn,$consignement['customer_id']);
                                                      if($customer && is_array($customer)){
                                                        echo "<option value='$customer[id]'>$customer[name]</option>";
                                                      }
                                                      $customers = selectAllSubsidiariesTypeWhereNotId($conn, $consignement['customer_id'], "customer");
                                                      if($customers && is_array($customers)){
                                                        foreach($customers as $customer){
                                                          echo "<option value='$customer[id]'>$customer[name]</option>";
                                                        }
                                                      }
                                                  ?>
                                                </select>
                                            </div>
                                            <div class="modal-footer">
                                              <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"> <i class=" fas fa-close"></i> Cancel</button>
                                              <button type="submit" name="editConsignCustomer" class="btn btn-primary btn-sm"> <i class=" fas fa-save"></i> Save</button>
                                            </div>
                                          </form>
                                        </div>
                                    </div>
                                  </div>
                         </div>

                   <script>
                      document.querySelectorAll(".luch-edit-modal").forEach(
                        button => {
                          button.addEventListener("click", function(){
                              const detailId = this.getAttribute("data-id");
                              const truck = this.getAttribute("data-truck-name");
                              const trailer = this.getAttribute("data-trailer-name");
                              const driver = this.getAttribute("data-driver-name");
                              const distance = this.getAttribute("data-distance");

                              // assign value to input
                              $("input[name='id']").val(detailId);
                              $("input[name='truck']").val(truck);
                              $("input[name='trailer']").val(trailer);
                              $("input[name='driver']").val(driver);
                              $("input[name='distace1']").val(distance);
                              $("input[name='distance2']").val(distance);

                          })
                        }

                            
                      )
                   </script>



























