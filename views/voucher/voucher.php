    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <?php
                            $transaction = countTransactionByVoucherRefNo($conn, "RV/");
                            if($transaction && is_array($transaction)){
                                echo "<h3>$transaction[jumla]</h3>";
                            }
                        ?>
                        <p>Receipt Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="./?page=receipt_voucher" class="small-box-footer">Create Receipt Voucher <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <?php
                            $transaction = countTransactionByVoucherRefNo($conn, "PV/");
                            if($transaction && is_array($transaction)){
                                echo "<h3>$transaction[jumla]</h3>";
                            }
                        ?>
                        <p>Payment Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="./?page=payment_voucher" class="small-box-footer">Make Payment <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-dark">
                    <div class="inner">
                        <?php
                            $transaction = countTransactionByVoucherRefNo($conn, "JV/");
                            if($transaction && is_array($transaction)){
                                echo "<h3>$transaction[jumla]</h3>";
                            }
                        ?>
                        <p>Journal Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="./?page=journal_voucher" class="small-box-footer">Generate Journal <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <?php
                            $transaction = countTransactionByVoucherRefNo($conn, "PJV/");
                            if($transaction && is_array($transaction)){
                                echo "<h3>$transaction[jumla]</h3>";
                            }
                        ?>
                        <p>Purchases</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="./?page=purchase_voucher" class="small-box-footer">Record Purchases <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <?php
                            $transaction = countTransactionByVoucherRefNo($conn, "SV/");
                            if($transaction && is_array($transaction)){
                                echo "<h3>$transaction[jumla]</h3>";
                            }
                        ?>
                        <p>Sales Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="./?page=sales_voucher" class="small-box-footer">Add Sales Transaction<i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>