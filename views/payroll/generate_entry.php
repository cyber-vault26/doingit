<div class=" card card-primary">
    <div class=" card-header"> <h4 class=" card-title ">Generate Payroll Entries</h4> </div>
    <?php
                             $totalBasicSalary = 0;
                            $totalGrossSalary = 0;
                            $totalAllowance = 0;
                            $totalNssf = 0;
                            $totalTaxableAmount = 0;
                            $totalPayee = 0;
                            $totalSalaryAdvance = 0;
                            
                            $totalDeduction = 0;
                            $totalNetPay = 0;
                            $totalSdl = 0;
                            $totalWcf = 0;
                            $totalMchango = 0;
                        $items = selectPayrollDetails($conn, (int) $_GET['payroll_id']);
                        if($items && is_array($items)){
                            foreach($items as $item){
                                $totalBasicSalary += $item['basic_salary'];
                              
                                $totalAllowance += $item['allowance'];
                                $totalNssf += $item['nssf'];
                                $totalTaxableAmount += $item['taxable_amount'];
                                $totalPayee += $item['payee'];
                                $totalSalaryAdvance += $item['advanced_salary'];
                                // $totalDeduction += $item['deduction'];
                                // $totalNetPay += $item['net_pay'];
                                $totalSdl += $item['sdl'];
                                $totalWcf += $item['wcf'];
                                $totalMchango += 5000;
                                
                            }
                            $totalGrossSalary += $totalBasicSalary + $totalAllowance;
                            $totalDeduction += $totalPayee + $totalSalaryAdvance;
                            $totalNetPay += $totalTaxableAmount - $totalPayee - $totalSalaryAdvance - $totalMchango;
                        }

    ?>
    <div class=" card-body">
        <div class=" card-footer"> <h5>Entries Distibution</h5> </div>
        <form action="./controllers/payroll_controller.php" class=" was-validated" method="post">
            <input type="hidden" name="payroll_id" value="<?= $_GET['payroll_id'] ?>">
            <div class=' col-md-6 col-sm-10 '>
                      <label>Select Date</label>
            <input name='date' type='date' class='form-control' required max='<?= date('Y-m-d') ?>' />
            </div>
          
            <table class=" table-bordered table">
                <tr>
                    <td>#</td>
                    <td>Ref No</td>
                    <td>Debit Account</td>
                    <td>Amount</td>
                    <td>Credit Account</td>
                    <td>Description</td>   
                </tr>
                <tr>
                    <td>1</td>
                    <td> <input type="text" name="ref_no"  readonly class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Salaries Expenses" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="842">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalNetPay,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Salary Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="502">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value="Salary " id=""> </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td> <input type="text" name="ref_no" readonly   class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Salaries Expenses" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="842">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalNssf,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="NSSF Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="427">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value="NSSF " id=""> </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td> <input type="text" name="ref_no" readonly   class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Salaries Expenses" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="842">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalPayee,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="PAYEE Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="845">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value="Payee " id=""> </td>
                </tr>
                <tr>
                    <td>4</td>
                    <td> <input type="text" name="ref_no"  readonly  class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="SDL Expenses" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="846">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalSdl,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="SDL Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="845">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value="SDL " id=""> </td>
                </tr>
                
                <tr>
                    <td>5</td>
                    <td> <input type="text" name="ref_no"  readonly  class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="WCF Expenses" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="847">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalWcf,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="WCF Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="428">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value="WCF " id=""> </td>
                </tr>
                <tr>
                    <td>6</td>
                    <td> <input type="text" name="ref_no" readonly   class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="NSSF Expenses" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="499">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalNssf,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="NSSF Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="427">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value="WCF " id=""> </td>
                </tr>
                <tr>
                    <td>7</td>
                    <td> <input type="text" name="ref_no"  readonly  class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Salaries Expenses" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="842">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalSalaryAdvance,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Salary Advance Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="859">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value="Salary Advance " id=""> </td>
                </tr>
                <tr>
                    <td>8</td>
                    <td> <input type="text" name="ref_no"  readonly  class=" form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Employee Fund" readonly class=" form-control"  id="">
                            <input type="hidden" name="debit_account[]"  id="" value="836">    
                    </td>
                    <td> <input type="text" name="amount[]"  class=" form-control" value="<?= number_format($totalMchango,2) ?>" id=""> </td>
                    <td> 
                            <input type="text" name="" value="Employee  Payable" readonly class=" form-control" id="">
                            <input type="hidden" name="credit_account[]" id="" value="849">    
                    </td>
                    <td> <input type="text" name="description[]"  class=" form-control" value=" Employee Fund " id=""> </td>
                </tr>
                
            </table>
            <div class=" card-footer">
                <button type="submit" class="btn btn-primary btn-sm btn-block" name="post_payroll_entry">POST ENTRY</button>
            </div>
        </form>
    </div>
</div>