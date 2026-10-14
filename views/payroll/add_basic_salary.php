<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body bg-gradient-primary text-white rounded-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">Add Basic Salary Portal</h3>
                            <p class="mb-0 mt-2 opacity-75">Add salary details for employees</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary">Salary Assignment</h5>
                        <div>
                            <button type="button" class="btn btn-success btn-sm rounded-3 px-3 me-2" onclick="addRow()">Add Row</button>
                            <button type="button" class="btn btn-danger btn-sm rounded-3 px-3" onclick="removeRow()">Remove Row</button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form action="./controllers/payroll_controller.php" method="post" id="salaryForm">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="salaryTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center py-3" style="width: 60%">Employee Name</th>
                                        <th class="text-center py-3" style="width: 40%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <tr>
                                        <td>
                                            <select name="emp_id[]" class="form-control employee-select" required onchange="updateSelects()">
                                                <option value="">Select Employee</option>
                                                <?php
                                                $sql = "SELECT s.id, s.name 
                                                        FROM subsidiaries s 
                                                        LEFT JOIN salary sal ON sal.subsidiary_id = s.id AND sal.deleted_at IS NULL
                                                        WHERE s.type='staff' 
                                                        AND s.deleted_at IS NULL 
                                                        AND sal.id IS NULL 
                                                        ORDER BY s.name";
                                                $result = $conn->query($sql);
                                                if($result->num_rows > 0){
                                                    while($rows = $result->fetch_assoc()){
                                                ?>
                                                <option value="<?php echo $rows['id']?>"><?php echo $rows['name']?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="amount[]" class="form-control amount-input" placeholder="0.00" required onkeyup="updateTotal()">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Total Rows:</span>
                                            <span class="fw-bold" id="rowCount">1</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span>Total Amount:</span>
                                            <span class="fw-bold text-success" id="totalAmount">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <a href="./?page=salary_list" class="btn btn-secondary px-4 py-2 rounded-3 me-2">Cancel</a>
                            <button type="submit" name="addsalary" class="btn btn-primary px-5 py-2 rounded-3">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addRow() {
    var tableBody = document.getElementById('tableBody');
    var firstRow = tableBody.rows[0];
    var newRow = firstRow.cloneNode(true);
    var selects = newRow.getElementsByTagName('select');
    var inputs = newRow.getElementsByTagName('input');
    
    for(var i = 0; i < selects.length; i++) {
        selects[i].value = '';
        selects[i].setAttribute('onchange', 'updateSelects()');
    }
    for(var i = 0; i < inputs.length; i++) {
        inputs[i].value = '';
        inputs[i].setAttribute('onkeyup', 'updateTotal()');
    }
    
    tableBody.appendChild(newRow);
    updateCount();
    updateTotal();
    updateSelects();
}

function removeRow() {
    var tableBody = document.getElementById('tableBody');
    if(tableBody.rows.length > 1) {
        tableBody.deleteRow(tableBody.rows.length - 1);
        updateCount();
        updateTotal();
        updateSelects();
    }
}

function updateCount() {
    var tableBody = document.getElementById('tableBody');
    document.getElementById('rowCount').innerHTML = tableBody.rows.length;
}

function updateTotal() {
    var inputs = document.getElementsByClassName('amount-input');
    var total = 0;
    
    for(var i = 0; i < inputs.length; i++) {
        var val = parseFloat(inputs[i].value) || 0;
        total += val;
    }
    
    document.getElementById('totalAmount').innerHTML = total.toFixed(2);
}

function updateSelects() {
    var selects = document.getElementsByClassName('employee-select');
    var selectedValues = [];
    
    for(var i = 0; i < selects.length; i++) {
        if(selects[i].value) {
            selectedValues.push(selects[i].value);
        }
    }
    
    for(var i = 0; i < selects.length; i++) {
        var currentValue = selects[i].value;
        var options = selects[i].getElementsByTagName('option');
        
        for(var j = 0; j < options.length; j++) {
            var option = options[j];
            if(option.value) {
                if(selectedValues.includes(option.value) && option.value != currentValue) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            }
        }
    }
}

updateCount();
updateTotal();
</script>