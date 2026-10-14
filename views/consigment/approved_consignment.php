
<div class=" card card-info">
                  <div class=" card-header"><h4>Consignment List</h4></div>
                  <div class=" card-body">
                  <table class=" table table-sm">
                    <tr class=' table-primary'>

                        <td><label for="">#</label></td>
                        <td><label for="">ref NO</label></td>

                         <td><label for="">Date</label></td> 
                        <!--<td><label for="">Route</label></td>-->
                        <td><label for="">Customer</label></td>
                        <td><label for="">Load Weight</label></td>
                        <td><label for="">Load Type</label></td>
                        <td><label for="">Status</label></td>
                        <td><label for="">Actions</label></td>
                     </tr>
                     <?php
                        $consignements = selectAllConsignmentByStatus($conn, "approved");
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
                                        echo "<td>";
                                            if($_SESSION['userlevel'] !=='1' && $_SESSION['userlevel'] !=='2'){
                                          echo "  <a href='./?page=allocate_consign_cost&consgn_id=$consignement[id]' class=' btn btn-sm btn-outline-warning text-dark'>Allocate Cost</a>";
                                            }
                                           echo "  <a href='./?page=view_csgnment_details&consgn_id=$consignement[id]' class=' btn btn-sm btn-outline-primary'>View details</a>";
                                             echo "<button  data-target='#close-consgn-modal' data-toggle='modal' class=' lunch-close-consgn-modal btn btn-sm btn-outline-warning m-1 text-dark' data-consign-ref='$consignement[ref_no]' data-consign-id='$consignement[id]'  data-type='retirement'    >Add Retirement </button>";
                                             echo "<button  data-target='#close-consgn-modal' data-toggle='modal' class=' lunch-close-consgn-modal btn btn-sm btn-outline-danger m-1 text-dark' data-consign-id='$consignement[id]' data-type='close'   >Close </button>";
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
                        <td><label for="">Customer</label></td>
                        <td><label for="">Load Weight</label></td>
                        <td><label for="">Load Type</label></td>
                        <td><label for="">Status</label></td>
                        <td><label for="">Actions</label></td>
                     </tr>
                  </table>
                  </div>
                  <div class=" card-footer"> -- end --</div>
              </div>
              
              
              <!--retirement modal-->
<div class="modal fade" id="close-consgn-modal">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5>Add Consignment Retirement Window</h5>
                <button class="btn btn-danger btn-sm" data-dismiss='modal'>&times;</button>
            </div>
            <div class="modal-body">
                <form action="./controllers/consignment_controller.php" method="post" class="was-validated">
                    <div class="my-2">
                        <label for="" class="d-none">Driver</label>
                        <input type="hidden" name="" readonly class="form-control" id="driverName">
                        <input type="hidden" name="driverd" readonly class="form-control" id="driverId">
                        <input type="hidden" name="consignId" readonly class="form-control" id="consignId">
                        <input type="hidden" name="type" id="type">
                        <input type="hidden" name="consignref" id="consignref">
                    </div>
                    
                    <div class="my-2">
                        <label for="" class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required max="<?= date('Y-m-d') ?>" id="">
                    </div>
                    
                    <div class="my-2">
                        <h5>Add Retirement Items</h5>
                        
                        <div class="my-2">
                            <button type="button" class="btn btn-sm btn-info" onclick="addConsignmentCloseTable()">Add Row</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeConsignmentClose()">Remove Row</button>
                        </div>
                        
                        <table class="table table-sm" id="closeconsigntable">
                            <tr class="bg-primary">
                                <td>Item</td>
                                <td>Driver</td>
                                <td>Currency</td>
                                <td>Exchange Rate</td>
                                <td>Amount</td>
                                <td>Eqv Amount</td>
                                <td>Comment</td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="cr_account[]" class="sub_item form-control select2-form select2bs4-form" id="" required>
                                        <option value="">Select Item</option>
                                    </select>
                                </td>
                                <td id='driveData'>
                                    
                                </td>
                                <td>
                                    <select name="currency[]" id="" class="form-control" onchange="selectCurrency2(this)" required>
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
                                </td>
                                <td>
                                    <div class="currency-rate">
                                        
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step='any' required name="" class="form-control debt_amount" oninput="calculateEqvs(this)" id="exampleInputPassword1" placeholder="Enter amount">
                                </td>
                                <td>
                                    <input type="number" step='any' required name="amount[]" class="form-control equiv_dr" id="exampleInputPassword1" readonly placeholder="Eqv">
                                </td>
                                <td>
                                    <input type="text" name="comment[]" min='1' class="form-control" id="">
                                </td>
                            </tr>
                        </table>
                        
                        <div class="form-group remarks">
                            <label for="">Any remark</label>
                            <textarea name="remarks" id="" cols="10" rows="3" class="form-control"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-sm btn-info" name="addconsignretirement">POST</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
              <!--end of retirement modsl-->
              <script>  
              $(document).ready(function(){
                    // fetchSubSelectItem();
                    // initializeSelect();
                     fetchAllSubOption();
                  })
            
                document.querySelectorAll(".lunch-close-consgn-modal").forEach(
                  button =>{
                    button.addEventListener("click", function(){
                        const driverName = this.getAttribute("data-driver");
                        const driverId = this.getAttribute("data-driver-id");
                        const consignId = this.getAttribute("data-consign-id");
                        const consignRef = this.getAttribute("data-consign-ref");
                        const type = this.getAttribute("data-type");

                        document.getElementById("driverName").value = driverName;
                        // document.getElementById("driverId").value = driverId;
                        document.getElementById("consignId").value = consignId;
                        document.getElementById("consignref").value = consignRef;
                        document.getElementById("type").value = type;
                        if(type =='retirement'){
                            $('.remarks').hide();
                            console.log(type +"hide");
                        } else{
                          $('.remarks').show();
                          console.log(type + "show");
                        }
                        // 
                        // try ajax fetch 
                        $.ajax({
                          url:"./processes/form_data_fetch/form_data_api.php",
                          data:{'consignId_driver': consignId},
                          type:"GET",
                          success:function(response){
                            $("#driveData").html(response);
                          }
                        });
                            // searchCreditAccount();
                    });
                  
           
                
                });
              </script>
            <?php
