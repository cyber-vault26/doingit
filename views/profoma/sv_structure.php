case "sv_report":
    $id = $_GET['sv_id'] ?? 0;
    $sql ="SELECT subsidiaries.name, voucher.name as voucher, sales_voucher.* FROM subsidiaries, sales_voucher, voucher WHERE subsidiaries.id= sales_voucher.dr_account AND voucher.id= sales_voucher.voucher_type AND sales_voucher.id='$id'";

    $result = $conn->query($sql);
    $rows = $result->fetch_assoc();
    $customer_id = $rows['dr_account'];
    
    //customer details
    $sqlc = "SELECT * FROM subsidiaries WHERE id= '$customer_id'";
    $resultc = $conn->query($sqlc);
    $rowc = $resultc->fetch_assoc();
    
    $sqld = "SELECT * FROM company_details";
    $resultd = $conn->query($sqld);
    $rowd = $resultd->fetch_assoc();
    ?>
    <div class="card card-info">
        <div class="card-header">
            <h4 class="card-title text-center">Sales Voucher Report</h4>
        </div>
        <div class="card-body">
 <style>
        .tax-invoice-container {
            font-family: 'Times New Roman', serif;
            /*max-width: 900px;*/
            /*margin: 0 auto;*/
            /*background: white;*/
            /*box-shadow: 0 0 20px rgba(0,0,0,0.1);*/
            /*padding: 0;*/
        }
        
        .tax-invoice-header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #666;

            margin-bottom: 0;
        }
        
        .tax-invoice-details-section {
            display: flex;
            justify-content: space-between;
            padding: 20px;
           border-bottom: 1px solid #666;
        }
        
        .customer-details-section {
            width: 48%;
        }
        
        .company-details-section {
            width: 48%;
            text-align: right;
        }
        
        .details-heading {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        
        .details-content {
            font-size: 14px;
            line-height: 1.6;
        }
        
        .tax-invoice-ref-section {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #666;
        }
        
        .ref-details-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ref-item {
            font-size: 14px;
            font-weight: bold;
        }
        
        .invoice-items-section {
            padding: 20px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-family: 'Times New Roman', serif;
        }
        
        .items-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 14px;
        }
        
        .items-table td {
            padding: 10px 12px;
            border: 1px solid #000;
            font-size: 14px;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .total-row {
            background: #f8f9fa !important;
            font-weight: bold;
        }
        
        .vat-row {
            background: #fff3cd !important;
        }
        
        .grand-total-row {
            background: #d4edda !important;
            font-weight: bold;
            font-size: 16px;
        }
        
        .bank-details-section {
            padding: 20px;
            background: #f8f9fa;
            border-top: 2px solid #000;
        }
        
        .bank-details-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .bank-info {
            width: 60%;
        }
        
        .signature-section {
            width: 35%;
            text-align: center;
        }
        
        .bank-details-heading {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .bank-details-text {
            font-size: 14px;
            line-height: 1.6;
        }
        
        .signature-text {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .signature-year {
            font-size: 12px;
            color: #666;
        }
        
        .declaration-section {
            padding: 15px 20px;
            background: #fff;
            border-top: 1px solid #ddd;
            font-size: 12px;
            line-height: 1.4;
            color: #666;
        }
        
        .declaration-heading {
            font-weight: bold;
            color: #000;
        }
        
       
    @media print {
        body * {
            visibility: hidden;
        }

        .tax-invoice-container, .tax-invoice-container * {
            visibility: visible;
        }

        .tax-invoice-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .print-button-section {
            display: none ;
        }
    }
 

        
        .amount-cell {
            text-align: right;
        }
        
        .center-cell {
            text-align: center;
        }
    </style>
    
    <div class="tax-invoice-container">
        <div class="print-button-section">
            <button onclick="window.print()">Print</button>
        </div>
        
        <div class="tax-invoice-header">
            Tax Invoice
        </div>
        
        <div class="tax-invoice-details-section">
            <div class="customer-details-section">
                <div class="details-heading">To</div>
                <div class="details-content">
                    <?= $rowc['name'] ?><br>
                    <?= $rowc['address'] ?><br>
                    TIN: <?= $rowc['tin'] ?><br>
                    VRN: <?= $rowc['vrn'] ?><br>
                     
                </div>
            </div>
            
            <div class="company-details-section">
                <div class="details-heading">From</div>
                <div class="details-content">
                    <?= $rowd['title'] ?><br>
                    <?= $rowd['address'] ?><br>
                    Phone: <?= $rowd['phone'] ?><br>
                    Email: <?= $rowd['email'] ?><br>
                    TIN: 107-686-886 <br>
                    VRN: 40-004368-G
                </div>
            </div>
        </div>
        
        <div class="tax-invoice-ref-section">
            <div class="details-heading">Tax Invoice Details</div>
            <div class="ref-details-flex">
                <div class="ref-item">REF No: <?= $rows['reference'] ?></div>
                <div class="ref-item">Date: <?= $rows['date_'] ?></div>
            </div>
            <div class="ref-details-flex" style="margin-top: 10px;">
                <div class="ref-item">Customer: <?= $rows['name'] ?></div>
                <div class="ref-item">Voucher: <?= $rows['voucher'] ?></div>
            </div>
        </div>
        
        <div class="invoice-items-section">
            <div class="details-heading">Tax Invoice Details</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 50%;">Item (description)</th>
                        <th style="width: 15%;" class="center-cell">Quantity</th>
                        <th style="width: 15%;" class="center-cell">Price</th>
                        <th style="width: 15%;" class="center-cell">Amount(Tsh)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT subsidiaries.name, sales_voucher_details.* 
                            FROM subsidiaries, sales_voucher_details 
                            WHERE subsidiaries.id = sales_voucher_details.cr_account 
                            AND sales_voucher_details.sv_id='$id'";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $total = 0;
                        $num = 0;
                        while ($rows = $result->fetch_assoc()) {
                            $num++;
                            $total += $rows['amount'];
                            ?>
                            <tr>
                                <td class="center-cell"><?= $num ?></td>
                                <td><?= $rows['name'] ?></td>
                                <td class="center-cell"><?= $rows['quantity'] ?></td>
                                <td class="amount-cell"><?= number_format($rows['price'], 2) ?></td>
                                <td class="amount-cell"><?= number_format($rows['amount'], 2) ?></td>
                            </tr>
                            <?php
                        }
                        
                        // Calculate VAT (18%)
                        $vat_amount = $total * 0.18;
                        $grand_total = $total + $vat_amount;
                        ?>
                        <tr class="vat-row">
                            <td colspan="4" style="text-align: right; font-weight: bold;">VAT (18%)</td>
                            <td class="amount-cell"><?= number_format($vat_amount, 2) ?></td>
                        </tr>
                        <tr class="grand-total-row">
                            <td colspan="4" style="text-align: right; font-weight: bold;">Grand Total</td>
                            <td class="amount-cell"><?= number_format($grand_total, 2) ?></td>
                        </tr>
                        <?php
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; color: red;'>No data found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="bank-details-section">
            <div class="bank-details-content">
                <div class="bank-info">
                    <div class="bank-details-heading">Bank Details</div>
                    <div class="bank-details-text">
                        <strong>Account Name:</strong> Buildscape Engineering and Construction Limited<br>
                        <strong>Bank Name:</strong> Tanzania Commercial Bank (TCB)<br>
                        <strong>Bank Account No:</strong> 170400000076
                    </div>
                </div>
                <div class="signature-section">
                    <div class="signature-text">Authorized Signature and Stamp</div>
                    <div class="signature-year">(2025)</div>
                </div>
            </div>
        </div>
        
        <div class="declaration-section">
            <span class="declaration-heading">Declaration:</span> We declare that this invoice shows the actual price of the goods/services described and that all particulars are true and correct.
        </div>
    </div>

        </div>
    </div>
    <?php
    break;
 


          