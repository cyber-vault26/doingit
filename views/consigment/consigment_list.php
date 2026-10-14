
              <div class=" card card-info">
                  <div class=" card-header"><h4>Consignment List</h4></div>
                  <div class=" card-body">
                  <table class=" table table-sm">
                    <tr class=' table-primary'>

                        <td><label for="">#</label></td>
                        <td><label for="">ref NO</label></td>

                        <!-- <td><label for="">Date</label></td> -->
                        <td><label for="">Route</label></td>
                        <td><label for="">Customer</label></td>
                        <td><label for="">Load Weight</label></td>
                        <td><label for="">Load Type</label></td>
                        <td><label for="">Status</label></td>
                        <td><label for="">Actions</label></td>
                     </tr>
                     <?php
                        $consignements = selectAllConsignment($conn);
                        if($consignements && is_array($consignements)){
                          //print_r($consignements);
                          $counter =1;
                              foreach($consignements as $consignement){
                                    echo "<tr>";
                                        echo "<td>$counter</td>";
                                        echo "<td>$consignement[ref_no]</td>";
                                        echo "<td>$consignement[date_]</td>";
                                        // echo "<td></td>";

                                        echo "<td>$consignement[mteja]</td>";
                                        echo "<td>$consignement[load_weight]</td>";
                                        echo "<td>$consignement[load_type]</td>";
                                        echo "<td>$consignement[status]</td>";
                                        echo "<td>
                                             <a href='./?page=view_csgnment_details&consgn_id=$consignement[id]' class=' btn btn-sm btn-outline-primary'>View details</a>";
                                          if($consignement['status'] !== 'closed'){
                                           echo "  <a href='./?page=allocate_consign_cost&consgn_id=$consignement[id]' class=' btn btn-sm btn-outline-success'>Allocate Cost</a>";
                                          } 
                                          if($consignement['status'] === 'in Transit'){
                                              echo "<button  data-target='#close-consgn-modal' data-toggle='modal' class=' lunch-close-consgn-modal btn btn-sm btn-outline-warning m-1 text-dark' data-consign-id='$consignement[id]'   >Add Consignemnt Cost</button>"; 
                                              echo "<a href='./controllers/consignment_controller.php?close_consignment&cosign_id=$consignement[id]'   class='  btn btn-sm btn-outline-warning m-1 text-dark'   >Close Cosingnment</button>"; 
                                          }
                                          if($consignement['status'] === 'pending' ){
                                            echo "  <a href='./controllers/consignment_controller.php?approve_consign&consgn_id=$consignement[id]' class=' btn btn-sm btn-outline-success'>Approve Consignment </a>";
                                           } 
                                          echo "   <a href='./?page=delete_consign&consgn_id=$consignement[id]' class=' btn btn-sm btn-outline-danger'>Reject</a>";

                                      echo   " </td>";
                                    echo "</tr>";
                                    $counter++;
                              }
                        }else{
                          echo "<tr><td colspan='8'>No data</td></tr>";
                        }
                     ?>
                    <tr class=' table-primary'>

                        <td><label for="">#</label></td>
                        <td><label for="">ref NO</label></td>

                        <td><label for="">Date</label></td>
                        <!-- <td><label for="">Route</label></td> -->
                        <td><label for="">Driver</label></td>
                        <td><label for="">Load Weight</label></td>
                        <td><label for="">Load Type</label></td>
                        <td><label for="">Status</label></td>
                        <td><label for="">Actions</label></td>
                     </tr>
                  </table>
                  </div>
                  <div class=" card-footer"> -- end --</div>
              </div>
              <div class=" modal fade" id="close-consgn-modal">
                        <div class=" modal-dialog modal-lg">
                          <div class=" modal-content">
                              <div class=" modal-header bg-info">
                                  <h5>Add  Consignement Cost Window</h5>
                                  <button class=" btn btn-danger btn-sm " data-dismiss='modal'>&times;</button>

                              </div>
                              <div class=" modal-body">
                                  <form action="./controllers/consignment_controller.php" method="post" class=" was-validated">
                                      <div class=" my-2">
                                          <label for="" class="d-none"> Driver</label>
                                          <input type="hidden" name="" readonly class=" form-control" id="driverName">
                                          <input type="hidden" name="driverId" readonly class=" form-control" id="driverId">
                                          <input type="hidden" name="consignId" readonly class=" form-control" id="consignId">

                                      </div>
                                      <div class=" my-2">
                                          <label for="" class=" form-label">date</label>
                                          <input type="date" name="date" class=" form-control" required max="<?= date("Y-m-d") ?>" id="">
                                      </div>
                                      <div class=" my-2">
                                          <h5>Add cost items</h5>
                                          <div class=" form-group col-sm-10 col-md-5">
                                            <label for="">Select Payment Method</label>
                                            <select name="payment_method" required class=" form-control" id="">
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
                                     
                                          <div class=" my-2">
                                              <button type="button" class=" btn btn-sm btn-info" onclick="addConsignmentCloseTable()">Add Row</button>
                                              <button type="button" class=" btn btn-sm btn-danger" onclick="removeConsignmentClose()" >Remove Row</button>
                                          </div>
                                          <table class=" table table-sm" id="closeconsigntable">
                                            <tr class=" bg-primary">
                                                <td>Item</td>
                                                <td>Driver</td>
                                                <td>Amount</td>
                                                <td>Dstance</td>
                                            </tr>
                                            <tr>
                                              <td>
                                                   <select name="cr_account[]" class=" form-control" required id=""></select>
                                              </td>
                                              <td id='driveData'>
                                                
                                              </td>
                                              <td>
                                                <input type="number" name="amount[]" min='1' class=" form-control" required id="">
                                              </td>
                                             
                                            </tr>

                                          </table>
                                          <button type="submit" class=" btn btn-sm btn-info" name="addassignmentcost">POST</button>
                                      </div>
                                  </form>
                              </div>
                          </div>
                        </div>
              </div>
              <script>
                document.querySelectorAll(".lunch-close-consgn-modal").forEach(
                  button =>{
                    button.addEventListener("click", function(){
                        const driverName = this.getAttribute("data-driver");
                        const driverId = this.getAttribute("data-driver-id");
                        const consignId = this.getAttribute("data-consign-id");

                        document.getElementById("driverName").value = driverName;
                        document.getElementById("driverId").value = driverId;
                        document.getElementById("consignId").value = consignId;

                        searchCreditAccount();
                        // try ajax fetch 
                        $.ajax({
                          url:"./processes/form_data_fetch/form_data_api.php",
                          data:{'consignId_driver': consignId},
                          type:"GET",
                          success:function(response){
                            $("#driveData").html(response);
                          }
                        });

                    });
                  
           
                
                });
              </script>
            <?php
