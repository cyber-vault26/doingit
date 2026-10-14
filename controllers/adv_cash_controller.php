<?php
    session_start();
    if(!$_SESSION){
    //    echo "<script>window.history.back();</script>";
    }
        require_once "../functions/subsidiary_functions.php";
        require_once "../functions/transaction_functions.php";
        require_once "../functions/procurement_functions.php";
        require_once  "../configs.php";
        $conn = openConn();
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['payadvancecash'])){
            print_r($_POST);
            $id = (int) $_POST['adv_id'];
            $cr_account = (int) $_POST['cr_account'];
            $refNo =$conn->real_escape_string($_POST['ref_no']);
            $amount = (float) $_POST['amount'];
            $date = $conn->real_escape_string($_POST['date']);
            $userId = (int) $_POST['requesterby'];
            $userSub = null;
            $userSub = selectSubsidiaryByUserId($conn, $userId);
            if($userSub && is_array($userSub)){
                $subId = $userSub['id'];
            }
                print_r($subId);
            $newTransaction = createTransaction($conn,$refNo,null,$subId,$amount,$date,$cr_account,$amount,1,'out',"adv Cash approval","active", 1.00);
           
            if(!$newTransaction){
                echo $newTransaction;
                return;
            }
            // $status = $conn->real_escape_string( $_GET['status']);
            $update = approveAdvCashRequest($conn,"paid",$id);
            if(!$update){
              echo $updated;
              return;
            }
            echo "<script>alert('SUCCESS'); window.location.href='../?page=pay_adv_cash_list'</script>";

            

        }
        
    }

?>