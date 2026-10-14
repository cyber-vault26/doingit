
        <?php
            $totlalAdvRequests =0;
            $approvedRequest =0;
            $totalAmountRequested =0;
            $totalAmountAproved =0;
            $totalAmountPending =0;
            
            $requests = getAllAdvanceCashRequest($conn);
            if($requests && is_array($requests)){
                foreach($requests as $request){
                    $totlalAdvRequests +=1;
                    if($request['status'] ==='approved'){
                        $totalAmountAproved += $request['amount'];
                    }
                    if($request['status'] === 'pending'){
                        $totalAmountPending += $request['amount'];
                    }
                }
            }
        
        ?>
<div class=" card card-secondary">
        <div class=" card-header">
            <h4 class=" card-title">Advance Cash Request</h4>

        </div>
        <div class=" card-body">
            <div class=" row">
                    <div class=" card card-outline col-md-3 card-primary m-1">
                        <div class=" card-header ">
                            <h4 class=" card-title">Total Advanced cash Request</h4>
                        </div>
                        <div class=" card-body">
                            <h4 class=" text-center"><?= number_format($totlalAdvRequests) ?></h4>
                        </div>
                    </div>
                    <div class=" card card-outline col-md-3 card-info m-1">
                        <div class=" card-header ">
                            <h4 class=" card-title">Total Amount Approved</h4>
                        </div>
                        <div class=" card-body">
                            <h4 class=" text-center"><?= number_format($totalAmountAproved) ?></h4>
                        </div>
                    </div>
                    <div class=" card card-outline col-md-3 card-success m-1">
                        <div class=" card-header ">
                            <h4 class=" card-title">Total Pending  Amount </h4>
                        </div>
                        <div class=" card-body">
                            <h4 class=" text-center"><?= number_format($totalAmountPending) ?></h4>
                        </div>
                    </div>
                    <div class=" card card-outline col-md-3 card-success m-1">
                        <div class=" card-header ">
                            <h4 class=" card-title">Total Pending  Amount </h4>
                        </div>
                        <div class=" card-body">
                            <h4 class=" text-center"><?= number_format($totalAmountPending) ?></h4>
                        </div>
                    </div>

            </div>
            <div class=" m-1 p-2 ">
                    <h4 class=" text-center "> Advanced Cash Distribution</h4>

                    <div class=" row"></div>
                    <div class=" col-md-5 card card-outline card-warning"> <h3></h3> </div>
            </div>
        </div>
        <div class=" card-footer"> -- end --</div>
</div>