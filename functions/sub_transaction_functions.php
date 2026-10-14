<?php

        function createSubTransaction (
            mysqli $conn, 
            string $reference_no,
            int $dr_account,
            float $dr_amount,
            string $date,
            int $cr_account,
            float $cr_amount,
            string $description,
            string $status
        ){
        if($conn === false){
            exit;
        }
        $sql = "INSERT INTO `sub_transactions`(`id`, `reference_no`, `voucher_type`, `category_type`, `currency`, `dr_account`, `dr_ammount`, `cr_account`, `cr_ammount`, `date_`, `description`,  `status`) 
                                        VALUES (NULL,'$reference_no',NULL,NULL,NULL,'$dr_account','$dr_amount','$cr_account','$cr_amount','$date','$description','$status')";
        if($conn->query($sql) === true){
            return true;
        } else{
            return $sql;
        }
        // return $sql;
        }
function generateSubRef(mysqli $conn, string $initial){
        if($conn === false){
            exit;
        }
        $sql = "SELECT COUNT(id) as number FROM sub_transactions WHERE reference_no LIKE ' $initial%'";
        if($result = $conn->query($sql)){
            $count = $result->fetch_assoc();
            $result->free();
            return $count;
        } else{
            return $conn->error;
        }

    }
    
    function selectAllSubTransactionByMinSubId(mysqli $conn, int $minSubId){
        if($conn === false){
            exit();

        }
         $sql = "SELECT * FROM sub_transactions WHERE cr_account ='$minSubId' OR dr_account='$minSubId'";
         if($result = $conn->query($sql)){
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
            return $data;
         } else{
            return $conn->error;
         }
    }
    function selectMinSubLastBalance(mysqli $conn, int $minSubId, string $lastDate){
            if($conn === false){
                exit();
            }
            $sql = "SELECT * FROM sub_transactions  WHERE 
                    (cr_account = '$minSubId' OR dr_account = '$minSubId') 
                    AND date_<='$lastDate'";
            if($result = $conn->query($sql)){
                $balance =0;
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();

                if($data && count($data)>0){
                    foreach($data as $transaction){
                        if($transaction['cr_account'] == $minSubId ){
                            $balance -= $transaction['cr_ammount'];

                        }elseif($transaction['dr_account'] == $minSubId){
                            $balance += $transaction['dr_ammount'];
                        }
                    }
                } else{
                    $balance =0;
                }


                return $balance;

            }else{
                return $conn->error;
            }
    }
    ?>