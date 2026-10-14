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
                            }
                            $totalGrossSalary += $totalBasicSalary + $totalAllowance;
                            $totalDeduction += $totalPayee + $totalSalaryAdvance;
                            $totalNetPay += $totalTaxableAmount - $totalPayee - $totalSalaryAdvance;
                        }

    ?>
    <div class=" card-body">
        <div class=" card-footer"> <h5>Entries Distibution</h5> </div>
        <form action="./controllers/payroll_controller.php" class=" was-validated" method="post">
            <input type="hidden" name="payroll_id" value="<?= $_GET['payroll_id'] ?>">
            <div class="row mb-3">
            <div class="col-md-4">
                <label for="entry_date" class="form-label">Entry Date:</label>
                <input type="date" name="entry_date" id="entry_date" class="form-control" required value="">
            </div>
        </div>
       
           <table class="table-bordered table">
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
        <td> <input type="text" name="ref_no" readonly class="form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="Salaries Expenses" readonly class="form-control" id="">
            <input type="hidden" name="debit_account[]" id="" value="23">    
        </td>
        <td> <input type="text" name="amount[]" class="form-control" value="<?= number_format($totalNetPay,2) ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="Salary Payable" readonly class="form-control" id="">
            <input type="hidden" name="credit_account[]" id="" value="24">    
        </td>
        <td> <input type="text" name="description[]" class="form-control" value="Salary " id=""> </td>
    </tr>
    <tr>
        <td>2</td>
        <td> <input type="text" name="ref_no" readonly class="form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="Salaries Expenses" readonly class="form-control" id="">
            <input type="hidden" name="debit_account[]" id="" value="23">    
        </td>
        <td> <input type="text" name="amount[]" class="form-control" value="<?= number_format($totalNssf,2) ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="NSSF Payable" readonly class="form-control" id="">
            <input type="hidden" name="credit_account[]" id="" value="21">    
        </td>
        <td> <input type="text" name="description[]" class="form-control" value="NSSF " id=""> </td>
    </tr>
    <tr>
        <td>3</td>
        <td> <input type="text" name="ref_no" readonly class="form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="Salaries Expenses" readonly class="form-control" id="">
            <input type="hidden" name="debit_account[]" id="" value="23">    
        </td>
        <td> <input type="text" name="amount[]" class="form-control" value="<?= number_format($totalPayee,2) ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="PAYEE Payable" readonly class="form-control" id="">
            <input type="hidden" name="credit_account[]" id="" value="25">    
        </td>
        <td> <input type="text" name="description[]" class="form-control" value="Payee " id=""> </td>
    </tr>
    <tr>
        <td>4</td>
        <td> <input type="text" name="ref_no" readonly class="form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="SDL Expenses" readonly class="form-control" id="">
            <input type="hidden" name="debit_account[]" id="" value="26">    
        </td>
        <td> <input type="text" name="amount[]" class="form-control" value="<?= number_format($totalSdl,2) ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="SDL Payable" readonly class="form-control" id="">
            <input type="hidden" name="credit_account[]" id="" value="27">    
        </td>
        <td> <input type="text" name="description[]" class="form-control" value="SDL " id=""> </td>
    </tr>
    <tr>
        <td>5</td>
        <td> <input type="text" name="ref_no" readonly class="form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="WCF Expenses" readonly class="form-control" id="">
            <input type="hidden" name="debit_account[]" id="" value="20">    
        </td>
        <td> <input type="text" name="amount[]" class="form-control" value="<?= number_format($totalWcf,2) ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="WCF Payable" readonly class="form-control" id="">
            <input type="hidden" name="credit_account[]" id="" value="22">    
        </td>
        <td> <input type="text" name="description[]" class="form-control" value="WCF " id=""> </td>
    </tr>
    <tr>
        <td>6</td>
        <td> <input type="text" name="ref_no" readonly class="form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="NSSF Expenses" readonly class="form-control" id="">
            <input type="hidden" name="debit_account[]" id="" value="19">    
        </td>
        <td> <input type="text" name="amount[]" class="form-control" value="<?= number_format($totalNssf,2) ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="NSSF Payable" readonly class="form-control" id="">
            <input type="hidden" name="credit_account[]" id="" value="21">    
        </td>
        <td> <input type="text" name="description[]" class="form-control" value="NSSF " id=""> </td>
    </tr>
    <tr>
        <td>7</td>
        <td> <input type="text" name="ref_no" readonly class="form-control" value="PYJV/<?= $_GET['payroll_id'] ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="Salaries Expenses" readonly class="form-control" id="">
            <input type="hidden" name="debit_account[]" id="" value="23">    
        </td>
        <td> <input type="text" name="amount[]" class="form-control" value="<?= number_format($totalSalaryAdvance,2) ?>" id=""> </td>
        <td> 
            <input type="text" name="" value="Salary Advance Payable" readonly class="form-control" id="">
            <input type="hidden" name="credit_account[]" id="" value="28">    
        </td>
        <td> <input type="text" name="description[]" class="form-control" value="Salary Advance " id=""> </td>
    </tr>
</table>
            <div class=" card-footer">
                <button type="submit" class="btn btn-primary btn-sm btn-block" name="post_payroll_entry">POST ENTRY</button>
            </div>
        </form>
    </div>
</div>