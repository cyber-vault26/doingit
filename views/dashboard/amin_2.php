<div class=" card card-primary">
    <div class=" card-header">
    <h4 class=" card-title ">Welcome  <?= $_SESSION['username'] ?></h4>
    </div>
    <div class=" card-body">
        <div class="card-footer">
            <h4>System Summaries</h4>
        </div>
        <div class=" row m-1">
            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-info">
              <span class="info-box-icon"><i class="far fa-bookmark"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Shared Capital</span>
                <span class="info-box-number">
                    <?php
                    $balance = getLastBalanceByMaster($conn,2, date("Y-m-d"));
                   // print_r($balance);
                    if($balance && is_numeric($balance)){
                        echo number_format($balance,2);
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                  Shared Capital of the company
                </span>
                
                <a href="./?page=master_last_balance&master_id=2&name=capital" class="btn btn-warning btn-sm btn-block text-light"> View More </a>
              
              </div>
              <!-- /.info-box-content -->
              
            </div>
          

            
            <!-- /.info-box -->
          </div>
            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-warning">
              <span class="info-box-icon"><i class="far fa-flag"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Pending Bills</span>
                <span class="info-box-number">
                    <?php
                    $balance = getlastSubLastBalanceByLedger($conn, 57, date("Y-m-d"));
                    if($balance && is_numeric($balance)){
                      if($balance >= 1){
                        echo number_format($balance,2);
                      } else{
                        echo number_format(($balance*-1),2);
                      }
                       
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                  Pending Bills (Invoices) From Customers
                </span>
                <a href="./?page=ledger_report_summary&ledger_id=57&ledger_name=Debitors" class="btn btn-primary btn-sm btn-block text-light"> View More </a>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
      
            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-danger">
              <span class="info-box-icon"><i class="fa fa-times"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Pending Creditors' Bills </span>
                <span class="info-box-number">
                    <?php
                    $balance = getlastSubLastBalanceByLedger($conn, 39, date("Y-m-d"));
                    if($balance && is_numeric($balance)){
                      if($balance >= 0){
                        echo number_format($balance,2);
                      } else{
                        echo number_format(($balance*-1),2);
                      }
                        
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                  Pending Bills (Payments) to suppliers
                </span>
                <a href="./?page=ledger_report_summary&ledger_id=39&ledger_name=Creditors" class="btn btn-primary btn-sm btn-block text-light"> View More </a>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>

            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-dark">
              <span class="info-box-icon"><i class="fa fa-plus"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Cash Receipt </span>
                <span class="info-box-number">
                    <?php
                    $balance = getlastSubLastBalanceByLedger($conn, 32, date("Y-m-d"));
                    if($balance && is_numeric($balance)){
                      if($balance >= 0){
                        echo number_format($balance,2);
                      } else{
                        echo number_format(0,2);
                      }
                        
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                  Balance On Cashes
                </span>
                <a href="./?page=ledger_report_summary&ledger_id=32&ledger_name=Cash Ledger" class="btn btn-primary btn-sm btn-block text-light"> View More </a>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-default">
              <span class="info-box-icon"><i class="fa fa-bacon"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Inter company transaction value </span>
                <span class="info-box-number">
                    <?php
                    $balance = getSubCLosingBalance($conn, 655, date("Y-m-d"));
                    if($balance && is_numeric($balance)){
                      if($balance >= 0){
                        echo number_format($balance,2);
                      } else{
                        echo number_format(0,2);
                      }
                        
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                 Debtor Balance
                </span>
                <a href="./?page=sub_report_summary&sub_id=655&sub_name=Inter company credito" class="btn btn-default btn-sm btn-block text-dark"> View More </a>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-primary">
              <span class="info-box-icon"><i class="fa fa-bacon"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Inter compant transaction value </span>
                <span class="info-box-number">
                    <?php
                    $balance = getSubCLosingBalance($conn, 656, date("Y-m-d"));
                    if($balance && is_numeric($balance)){
                      if($balance >= 0){
                        echo number_format($balance,2);
                      } else{
                        echo number_format(0,2);
                      }
                        
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                 Creditor Balance
                </span>
                <a href="./?page=sub_report_summary&sub_id=656&sub_name=InterCompany Creditor" class="btn btn-default btn-sm btn-block text-dark"> View More </a>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-secondary">
              <span class="info-box-icon"><i class="fa fa-chart-line"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Sales / Turn Over </span>
                <span class="info-box-number">
                    <?php
                    $balance = getLastBalanceByMaster($conn, 7, date("Y-m-d"));
                    if($balance && is_numeric($balance)){
                      if($balance >= 0){
                        echo number_format($balance,2);
                      } else{
                        echo number_format(($balance*-1),2);
                      }
                        
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                 Sales
                </span>
                <a href="./?page=master_last_balance&master_id=7&name=Sales" class="btn btn-warning btn-sm btn-block text-light"> View More </a>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
            <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-warning ">
              <span class="info-box-icon"><i class="fa fa-chart-bar"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Expenses  </span>
                <span class="info-box-number">
                    <?php
                    $balance = getLastBalanceByMaster($conn, 8, date("Y-m-d"));
                    if($balance && is_numeric($balance)){
                      if($balance >= 0){
                        echo number_format($balance,2);
                      } else{
                        echo number_format(0,2);
                      }
                        
                    } else{
                      echo  0.00;
                    }
                    ?>
                </span>

                <div class="progress">
                  <div class="progress-bar" style="width: 60%"></div>
                </div>
                <span class="progress-description">
                 Expenses Balance
                </span>
                <a href="./?page=master_last_balance&master_id=8&name=Expenses" class="btn btn-secondary btn-sm btn-block text-light"> View More </a>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
        </div> <!-- endo row -->
        
        
        
        
        
         <div class="card-footer">
            <h4>Project Summaries</h4>
        </div>
        
        <div class="row">
           
           
  <!-- Total Consignments -->
  <div class="col-md-3 col-sm-6 col-12">
    <div class="info-box bg-success">
      <span class="info-box-icon"><i class="fas fa-truck"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Projects</span>
        <span class="info-box-number">
            <?php
                $totalConsignmentsQuery = "SELECT COUNT(id) as jumla FROM `projects` WHERE deleted_at is null";
                    $totalConsignmentsResult = mysqli_query($conn, $totalConsignmentsQuery);
                    $totalConsignments = 0;
                    if($totalConsignmentsResult && mysqli_num_rows($totalConsignmentsResult) > 0) {
                        $row = mysqli_fetch_assoc($totalConsignmentsResult);
                        $totalConsignments = $row['jumla'];
                }
    
                echo number_format($totalConsignments); 
                            
             ?>
            
        </span>
        <div class="progress">
          <div class="progress-bar" style="width: 80%"></div>
        </div>
        <span class="progress-description">Active</span>
        <a href="?page=all_consignment" class="btn btn-light btn-sm btn-block text-dark">View More</a>
      </div>
    </div>
  </div>

  <!-- Total Cost Generated -->
  <div class="col-md-3 col-sm-6 col-12">
    <div class="info-box bg-warning">
      <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Cost Generated</span>
        <span class="info-box-number">
            TZS <?php echo number_format(getTotalProjectCosts($conn), 2); ?>
        </span>

        <div class="progress">
          <div class="progress-bar" style="width: 65%"></div>
        </div>
        <span class="progress-description">Total Cost</span>
        <a href="?page=consignment_cost_report" class="btn btn-light btn-sm btn-block text-dark">View More</a>
      </div>
    </div>
  </div>

  <!-- Total Income Generated -->
  <div class="col-md-3 col-sm-6 col-12">
    <div class="info-box bg-info">
      <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Budget Amount</span>
        <span class="info-box-number">TZS <?php echo number_format(getTotalProjectBudget($conn) ?: 0, 2); ?></span>
        <div class="progress">
          <div class="progress-bar" style="width: 75%"></div>
        </div>
        <span class="progress-description">Total Budget</span>
        <a href="?page=consignment_income_report" class="btn btn-light btn-sm btn-block text-dark">View More</a>
      </div>
    </div>
  </div>

  <!-- Net Profit -->
  <div class="col-md-3 col-sm-6 col-12">
    <div class="info-box bg-primary">
      <span class="info-box-icon"><i class="fas fa-piggy-bank"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Budget Tracking</span>
        <span class="info-box-number">TZS 
        <?php 
    
$totalBudget = getTotalProjectBudget($conn);
$totalCosts = getTotalProjectCosts($conn);
$balance = $totalBudget - $totalCosts;
 
 
      echo number_format($balance ?: 0, 2); 
        ?>
        </span>
        <div class="progress">
          <div class="progress-bar" style="width: 90%"></div>
        </div>
        <span class="progress-description">Budget Vs Cost</span>
        <a href="?page=consignment_profit_report" class="btn btn-light btn-sm btn-block text-dark">View More</a>
      </div>
    </div>
  </div>
        </div>
        
        </div>
    </div>
