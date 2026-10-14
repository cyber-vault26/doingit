
                 <div class=" card card-info">
                      <div class="card-header"><h4>Create Consigniment</h4></div>
                      <form action="./controllers/consignment_controller.php" class="was-validated" method="post">
                          <div class=" card-body">
                          <div class=" form-group">
                                      <label for="" class=" form-label">Ref No</label>
                                      <?php 
                                        $count = countConsignment($conn, date("Y"));
                                        $ref = "CFO/" . ($count && is_array($count) ? $count['jumla'] + 1 : 1);
                                      ?>
                                        <input type="text" name="ref_no" class=" form-control" required readonly value="<?= $ref  ?>"  id="">
                                  </div>
                              
                         
                          <div class=" form-group">
                                <label for="">Select Customer</label>
                                <select name="customer"  required class=" select2-in select2bs4 form-control" id="">
                                    <option value="">Select Below</option>
                                    <?php
                                      $customers = selectSubsidiaries($conn, 'active');
                                      if($customers && is_array($customers)){
                                        foreach($customers as $customer){
                                          echo "<option value='$customer[id]'>$customer[name]</option>";
                                        }
                                      }
                                    ?>
                                </select>
                          </div>
                          <div class=" form-group">
                                      <label for="">Date of Creation</label>
                                      <input type="date" name="date" id="" class=" form-control" required>
                          </div>
                          <div class=" form-group">
                                      <label for="" class=" ">Load Type</label>
                                      <input type="text" name="loadtype" required class=" form-control" id="">
                          </div>
                          <div class=" form-group">
                                      <label for="" class=" ">Load Weight (in tons) </label>
                                      <input type="number" step='any' min='1.0' name="loadweight" required class=" form-control" id="">
                          </div>
                              <div class=" form-group">
                                      <label for="">Load Description</label>
                                      <textarea name="description"  required  id="" rows="3" class=" form-control"></textarea>
                              </div>
                              <div class=" form-group">
                                      <label for="">Shipment Date</label>
                                      <input type="date" name="shipdate" class=" form-control" required  id="">
                              </div>
                              <div class=" form-group">
                                      <label for="">return  Date</label>
                                      <input type="date" name="returndate" class=" form-control" required id="">
                              </div>
                              <h5>Drive And Vehicle Involved</h5>
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
                              <button type="submit" name="createConsign" class=" btn btn-sm btn-primary">Submit</button>
                          </div>
                      </form>
                 </div>
                 <script>
                   $(document).ready(function(){
                  $(function () {
                      $(".select2-in").select2({
                                    placeholder: "Select an option",
                                          });
                          $('.select2bs4').select2({
                    theme: 'bootstrap4'
                  })
                      });

                      
              });
                 </script>
              <?php
          