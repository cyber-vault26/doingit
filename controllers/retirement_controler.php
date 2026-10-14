<?php
        session_start();
        if(!$_SESSION){
          //print_r("hello");
        //   echo "<script> window.location.href='./login.php'</script>";
        }
        require_once "../functions/retirement_functions.php";
        require_once "../configs.php";
        $conn = openConn();
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['send_retirement'])){
               // print_r($_POST);
               // print_r($_FILES);
                $userid = (int) $_POST['cr_account'];
                $refNo = $conn->real_escape_string( $_POST['ref_no']);
                $date = $conn->real_escape_string($_POST['date_']);
                // arrays
                $items = $_POST['item'];
                $narrations = $_POST['narration'];
                
                // NUMERIC VALUES - REMOVE COMMAS
                $cureency = $conn->real_escape_string(str_replace(',', '', $_POST['curr']));
                $totalAmount  = $conn->real_escape_string(str_replace(',', '', $_POST['totalamount']));
                $amounts = $_POST['eqv'];
                $actual_amount = $_POST['amount'];
                
                // Remove commas from arrays
                foreach($amounts as $key => $value) {
                    $amounts[$key] = str_replace(',', '', $value);
                }
                //amount withou currency mulptiplication
                foreach($actual_amount as $key => $value) {
                    $actual_amount[$key] = str_replace(',', '', $value);
                }

                $newRetirement  = createRetirement($conn,$refNo,$userid,$date,$totalAmount,$cureency);
                if($newRetirement){
                  $retirementId = $newRetirement;
                  
                    $count = count($_FILES['receipt']['name']);
                    for($i =0; $i<$count;$i++){
                      $item = (int) $items[$i];
                      $amount = (float) $amounts[$i];
                      $narration = $conn->real_escape_string($narrations[$i]);
                      $tmpName = $_FILES['receipt']['tmp_name'][$i];
                       $originalName = basename($_FILES['receipt']['name'][$i]);
                        $newName = time() . '_' . $originalName;
                      $targetPath = "../resources/attachments/retirements/" . $newName;
                      print_r($originalName);
                      if(move_uploaded_file($_FILES['receipt']['tmp_name'][$i], $targetPath)){
                          $newDetails = insertRetirementItems($conn, $retirementId,$item,$amount,$newName,$narration);
                          if(!$newDetails){
                            echo $newDetails;
                            return;
                          }
                      } else{
                        echo "failed";
                      }
                    }
                  
                } else{
                  echo $newRetirement;
                  return;
                }
               

                echo "<script> alert('Retirement Created Successfully'); window.history.back();</script>";
            }

            if(isset($_POST['reject_retirement'])){
              print_r($_POST);
              $id = (int) $_POST['retirement_id'];
              $reason = $conn->real_escape_string($_POST['reason']);
              $rejectRetirement = rejectRetirementRequest($conn, $id, $reason);
              if(!$rejectRetirement){
                echo $rejectRetirement;
                return;
              }
              echo "<script> alert('Retirement Rejected Successfully'); window.history.back();</script>";
            }
        }
?>