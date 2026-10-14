 
<div class=" card card-secondary">
                      <div class=" card-header">
                          <h4 class=" card-title">My Request List</h4>
                      </div>
                      <div class=" card-body table-responsive">
                          <table class=" table table-sm table-bordered">
                              <tr class=" table-primary">
                                  <td>#</td>
                                  <td>Requester</td>
                                  <td>Amount</td>
                                  <td>Request Date</td>
                                  <td>Status</td>
                                  <td>Action</td>
                              </tr>
                              <?php
                            //   print_r($_SESSION);
                                  $user_id= $_SESSION['userid'];
                                  $requests = getAllAdvanceCashRequestBySessionId($conn, $user_id);
                                  if($requests && is_array($requests)){
                                    $counter =1;
                                    foreach($requests as $request){
                                      echo "<tr>";
                                          echo "<td>$counter</td>";
                                          echo "<td>$request[requester]</td>";
                                          echo "<td>$request[amount]</td>";
                                          echo "<td>$request[date_]</td>";
                                          echo "<td>$request[status] By manager</td>";
                                          echo "<td>";
                                              echo "<button data-toggle='modal'  data-target='#adv-preview-modal' data-adv-reason='$request[reason]' class=' preview-adv-req-btn btn btn-info btn-sm  m-1' data-adv-date='$request[date_]' data-adv-refno='$request[ref_no]' data-adv-id='$request[id]' data-adv-requester='$request[requester]' data-adv-amount='$request[amount]'>Preview</button>";
                                            //   if(($_SESSION['usertype'] ==='Manager' || $_SESSION['usertype'] ==='Admin') && $_SESSION['userlevel'] >5  ){
                                            //     echo "<a class=' btn btn-primary btn-sm m-1'  href='./?page=approve_adv&id=$request[id]&status=director approved' >Approve</a>";
                                            //      echo "<a class=' btn btn-danger btn-sm m-1'  href='./?page=approve_adv&id=$request[id]&status=director rejected' >Reject Request</a>";
                                            //  }
                                          echo "</td>";
                                      echo "</tr>";
                                      $counter++;
                                    }
                                  }
                              ?>
                              <tr class=" table-primary">
                                  <td>#</td>
                                  <td>Requester</td>
                                  <td>Amount</td>
                                  <td>Request Date</td>
                                  <td>Status</td>
                                  <td>Action</td>
                              </tr>
                          </table>
                      </div>

                  </div>
                  <div class=" modal fade" id='adv-preview-modal'>
                          <div class=" modal-dialog modal-lg">
                                <div class=" modal-content">
                                    <div class=" modal-header bg-info">
                                        <h4>Adv Cash Request Preview</h4>
                                        <button type="button" class=" btn  btn-danger btn-sm" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class=" modal-body">
                                     
                                        <div class=" row">
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">Ref No:</label>
                                                <input class=" form-control" type="text" name="" readonly id="refNo">
                                               

                                            </div>
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">Requested BY:</label>
                                                <input class=" form-control" type="text" name="" readonly id="requester">

                                            </div>
                                             <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">Reason:</label>
                                                <input class=" form-control" type="text" name="" readonly id="reason">

                                            </div>
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">date:</label>
                                                <input class=" form-control" type="text" name="" readonly id="date">

                                            </div>
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">Amount:</label>
                                                <input class=" form-control" type="text" name="" readonly id="amount">

                                            </div>
                                        </div>
                                        <div class=" form-group table-responsive" id='adv_items_preview'></div>
                                    
                                   
                                    </div>

                                </div>
                          </div>
                 </div>
                  <div class=" modal fade" id='adv-preview-modal'>
                          <div class=" modal-dialog modal-lg">
                                <div class=" modal-content">
                                    <div class=" modal-header bg-info">
                                        <h4>Adv Cash Request Edit</h4>
                                        <button type="button" class=" btn  btn-danger btn-sm" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class=" modal-body">
                                     <form action=" <?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                                        <div class=" row">
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">Ref No:</label>
                                                <input class=" form-control" type="text" name="" readonly id="refNo1">
                                               

                                            </div>
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">Requested BY:</label>
                                                <input class=" form-control" type="text" name="" readonly id="requester1">

                                            </div>
                                            
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">date:</label>
                                                <input class=" form-control" type="text" name="" readonly id="date1">

                                            </div>
                                            <div class=" col-md-5  col-sm-10 form-group">
                                                <label for="">Amount:</label>
                                                <input class=" form-control" type="text" name="" readonly id="amount1">

                                            </div>
                                        </div>
                                        <div class=" form-group table-responsive" id='adv_items_preview1'></div>
                                    
                                   
                                    </div>
                                    <div class=" ">
                                        <button type="submit" class=" btn btn-info btn-sm" name="approveadvcash">Submit</button>
                                    </div>
                                  </form>
                                </div>
                          </div>
                 </div>
                 <script>
                          document.querySelectorAll(".preview-adv-req-btn").forEach(
                            button =>{
                              button.addEventListener("click", function(){
                                  var lpo_id = this.getAttribute("data-adv-id");
                                  // var supplier = this.getAttribute("data-adv-suppier");
                                  var  amount = this.getAttribute("data-adv-amount");
                                  var requester = this.getAttribute("data-adv-requester");
                                  var date_ = this.getAttribute("data-adv-date");
                                  var reason = this.getAttribute("data-adv-reason");
                                  var refNo = this.getAttribute("data-adv-refno");
                                    // assign value
                                  document.getElementById('refNo').value=refNo;
                                  document.getElementById('requester').value= requester;
                              
                                  document.getElementById("amount").value = amount;
                                  document.getElementById("date").value = date_;
                                   document.getElementById('reason').value= reason;

                                  $.ajax({
                                        type:"GET",
                                        url:"./processes/form_data_fetch/form_data_api.php",
                                        data:{lpo_adv_cash_request:lpo_id},
                                        success:function(response){
                                          $("#adv_items_preview").html(response);
                                        }
                                  });

                              

                              });
                            }
                          );

                          document.querySelectorAll(".preview-adv-req-btn").forEach(
                            button =>{
                              button.addEventListener("click", function(){
                                  var lpo_id = this.getAttribute("data-adv-id");
                                  // var supplier = this.getAttribute("data-adv-suppier");
                                  var  amount = this.getAttribute("data-adv-amount");
                                  var requester = this.getAttribute("data-adv-requester");
                                  var date_ = this.getAttribute("data-adv-date");
                                  var refNo = this.getAttribute("data-adv-refno");
                                    // assign value
                                  document.getElementById('refNo1').value=refNo;
                                  document.getElementById('requester1').value= requester;
                              
                                  document.getElementById("amount1").value = amount;
                                  document.getElementById("date1").value = date_;

                                  $.ajax({
                                        type:"GET",
                                        url:"./processes/form_data_fetch/form_data_api.php",
                                        data:{lpo_adv_cash_request:lpo_id},
                                        success:function(response){
                                          $("#adv_items_preview1").html(response);
                                        }
                                  });

                              

                              });
                            }
                          );
                    </script>
            