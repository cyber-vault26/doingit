<?php
session_start();
if(!$_SESSION){
    echo "<script>window.history.back();</script>";
}
require_once "../configs.php";
require_once "../functions/transaction_functions.php";
require_once "../functions/voucher_functions.php";
require_once "../functions/notification_functions.php";
require_once "../functions/subsidiary_functions.php";
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] === "POST"){
    $userId = (int) $_SESSION['userid'];
    $uploadDir = '../uploads/vouchers/';
    if(isset($_POST['addreciptvoucher'])){
        $ref             = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $date            = $conn->real_escape_string(trim($_POST['voucherdate']));
        $currency        = (int) $_POST['currency'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $eqv             = $_POST['eqv'];
        $amounts         = $_POST['amount'];
        $cr_accounts     = $_POST['cr_account'];
        $dr_accounts     = $_POST['deb_account'];
        $desc            = $_POST['desc'];
        $project_id      = (int) $_POST['project_id'];
        $transaction_ref =  null;
        $uploadedFiles   = $_FILES['attachment'] ?? null;
        $conn->begin_transaction();
        try {
            for($i = 0; $i < count($eqv); $i++){
                $eqvVal   = (float) str_replace(',', '', $eqv[$i]);
                $dr       = (int) $dr_accounts[$i];
                $cr       = (int) $cr_accounts[$i];
                $d        = $conn->real_escape_string(trim($desc[$i]));
                $fileName = null;
                if($uploadedFiles && isset($uploadedFiles['name'][$i]) && !empty($uploadedFiles['name'][$i]) && $uploadedFiles['error'][$i] === UPLOAD_ERR_OK){
                    $originalFileName = basename($uploadedFiles['name'][$i]);
                    $fileName         = time() . '_' . $originalFileName;
                    $targetPath       = $uploadDir . $fileName;
                    $fileTmpName      = $uploadedFiles['tmp_name'][$i];
                    if(!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to move uploaded file for row " . ($i + 1) . ".");
                }
                $transactionId = createTransaction($conn, $ref, 'receipt', $dr, $eqvVal, $cr, $date, $d, $currency, $currency_rate, 'draft', $userId, $transaction_ref, $fileName, $project_id);
                if(!is_numeric($transactionId)) throw new Exception($transactionId);
            }
            $conn->commit();
            setNotification('success', 'Receipt voucher created successfully');
            echo "<script>window.location.href='../?page=receipt_voucher';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['editreciptvoucher'])){
        $id              = (int) $_POST['voucher_id'];
        $dr              = (int) $_POST['deb_account'][0];
        $cr              = (int) $_POST['cr_account'][0];
        $desc            = $conn->real_escape_string(trim($_POST['desc'][0]));
        $ref             = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $date            = $conn->real_escape_string(trim($_POST['voucherdate']));
        $currency        = (int) $_POST['currency'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $amount          = (float) str_replace(',', '', $_POST['eqv'][0]);
        $project_id      = (int) $_POST['project_id'];
        $transaction_ref = $userId;
        $uploadedFiles   = $_FILES['attachment'] ?? null;
        $attachment_url  = $conn->real_escape_string(trim($_POST['existing_attachment'] ?? ''));
        $conn->begin_transaction();
        try {
            if($uploadedFiles && isset($uploadedFiles['name'][0]) && !empty($uploadedFiles['name'][0]) && $uploadedFiles['error'][0] === UPLOAD_ERR_OK){
                $originalFileName = basename($uploadedFiles['name'][0]);
                $fileName         = time() . '_' . $originalFileName;
                $targetPath       = $uploadDir . $fileName;
                $fileTmpName      = $uploadedFiles['tmp_name'][0];
                if(!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to upload attachment.");
                $attachment_url = $fileName;
            }
            $updated = updateTransaction($conn, $id, $ref, 'receipt', $dr, $amount, $cr, $date, $transaction_ref, $desc, $currency, $currency_rate, $project_id, $userId, $attachment_url);
            if(!$updated) throw new Exception("Failed to update receipt voucher.");
            $conn->commit();
            setNotification('success', 'Receipt voucher updated successfully');
            echo "<script>window.location.href='../?page=receipt_voucher&id=$id';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if(isset($_POST['addothers'])){
        $created_by = (int) $_SESSION['userid'];
        $name = $conn->real_escape_string(trim($_POST['name']));
        $ledger = (int) $_POST['ledger'];
        $newOther = createSubsidiary($conn, $name, $ledger, $created_by, 'others', 'others', null, null, null, null, null, null);
        if(is_numeric($newOther) && $newOther > 0){
            setNotification('success', 'Other subsidiary added successfully');
            echo "<script>window.location.href='../?page=payment_voucher';</script>";
        } else {
            echo "Error: " . $newOther;
        }
    }


    if(isset($_POST['addpaymentVoucher'])){
        $ref             = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $date            = $conn->real_escape_string(trim($_POST['voucherdate']));
        $currency        = (int) $_POST['currency'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $eqv             = $_POST['equiv_dr'];
        $cr_accounts     = $_POST['cr_account'];
        $dr_accounts     = $_POST['deb_account'];
        $desc            = $_POST['desc'];
        $project_id      = (int) $_POST['project_id'];
        $transaction_ref = $userId;
        $uploadedFiles   = $_FILES['attachment'] ?? null;
        $conn->begin_transaction();
        try {
            for($i = 0; $i < count($eqv); $i++){
                $eqvVal   = (float) str_replace(',', '', $eqv[$i]);
                $dr       = (int) $dr_accounts[$i];
                $cr       = (int) $cr_accounts[$i];
                $d        = $conn->real_escape_string(trim($desc[$i]));
                $fileName = null;
                if($uploadedFiles && isset($uploadedFiles['name'][$i]) && !empty($uploadedFiles['name'][$i]) && $uploadedFiles['error'][$i] === UPLOAD_ERR_OK){
                    $originalFileName = basename($uploadedFiles['name'][$i]);
                    $fileName         = time() . '_' . $originalFileName;
                    $targetPath       = $uploadDir . $fileName;
                    $fileTmpName      = $uploadedFiles['tmp_name'][$i];
                    if(!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to move uploaded file for row " . ($i + 1) . ".");
                }
                $transactionId = createTransaction($conn, $ref, 'payment', $dr, $eqvVal, $cr, $date,  $d, $currency, $currency_rate, 'draft', $userId, $transaction_ref, $fileName, $project_id);
                if(!is_numeric($transactionId)) throw new Exception($transactionId);
            }
            $conn->commit();
            setNotification('success', 'Payment voucher created successfully');
            echo "<script>window.location.href='../?page=payment_voucher';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['editpaymentVoucher'])){
        $id              = (int) $_POST['voucher_id'];
        $dr              = (int) $_POST['deb_account'][0];
        $cr              = (int) $_POST['cr_account'][0];
        $desc            = $conn->real_escape_string(trim($_POST['desc'][0]));
        $ref             = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $date            = $conn->real_escape_string(trim($_POST['voucherdate']));
        $currency        = (int) $_POST['currency'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $amount          = (float) str_replace(',', '', $_POST['equiv_dr'][0]);
        $project_id      = (int) $_POST['project_id'];
        $transaction_ref =  $userId;
        $uploadedFiles   = $_FILES['attachment'] ?? null;
        $attachment_url  = $conn->real_escape_string(trim($_POST['existing_attachment'] ?? ''));
        $conn->begin_transaction();
        try {
            if($uploadedFiles && isset($uploadedFiles['name'][0]) && !empty($uploadedFiles['name'][0]) && $uploadedFiles['error'][0] === UPLOAD_ERR_OK){
                $originalFileName = basename($uploadedFiles['name'][0]);
                $fileName         = time() . '_' . $originalFileName;
                $targetPath       = $uploadDir . $fileName;
                $fileTmpName      = $uploadedFiles['tmp_name'][0];
                if(!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to upload attachment.");
                $attachment_url = $fileName;
            }
            $updated = updateTransaction($conn, $id, $ref, 'payment', $dr, $amount, $cr, $date, $transaction_ref, $desc, $currency, $currency_rate, $project_id, $userId, $attachment_url);
            if(!$updated) throw new Exception("Failed to update payment voucher.");
            $conn->commit();
            setNotification('success', 'Payment voucher updated successfully');
            echo "<script>window.location.href='../?page=payment_voucher&id=$id';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if(isset($_POST['addjv'])){
        $ref             = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $date            = $conn->real_escape_string(trim($_POST['voucherdate']));
        $currency        = (int) $_POST['currency'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $eqv             = $_POST['equiv_dr'];
        $cr_accounts     = $_POST['cr_account'];
        $dr_accounts     = $_POST['deb_account'];
        $desc            = $_POST['desc'];
        $project_id      = (int) $_POST['project_id'];
        $transaction_ref =  $userId;
        $uploadedFiles   = $_FILES['attachment'] ?? null;
        $conn->begin_transaction();
        try {
            for($i = 0; $i < count($eqv); $i++){
                $eqvVal   = (float) str_replace(',', '', $eqv[$i]);
                $dr       = (int) $dr_accounts[$i];
                $cr       = (int) $cr_accounts[$i];
                $d        = $conn->real_escape_string(trim($desc[$i]));
                $fileName = null;
                if($uploadedFiles && isset($uploadedFiles['name'][$i]) && !empty($uploadedFiles['name'][$i]) && $uploadedFiles['error'][$i] === UPLOAD_ERR_OK){
                    $originalFileName = basename($uploadedFiles['name'][$i]);
                    $fileName         = time() . '_' . $originalFileName;
                    $targetPath       = $uploadDir . $fileName;
                    $fileTmpName      = $uploadedFiles['tmp_name'][$i];
                    if(!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to move uploaded file for row " . ($i + 1) . ".");
                }
                $transactionId = createTransaction($conn, $ref, 'journal', $dr, $eqvVal, $cr, $date,  $d, $currency, $currency_rate, 'draft', $userId, $transaction_ref, $fileName, $project_id);
                if(!is_numeric($transactionId)) throw new Exception($transactionId);
            }
            $conn->commit();
            setNotification('success', 'Journal voucher created successfully');
            echo "<script>window.location.href='../?page=journal_voucher';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    if(isset($_POST['editjv'])){
        $id              = (int) $_POST['voucher_id'];
        $dr              = (int) $_POST['deb_account'][0];
        $cr              = (int) $_POST['cr_account'][0];
        $desc            = $conn->real_escape_string(trim($_POST['desc'][0]));
        $ref             = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $date            = $conn->real_escape_string(trim($_POST['voucherdate']));
        $currency        = (int) $_POST['currency'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $amount          = (float) str_replace(',', '', $_POST['equiv_dr'][0]);
        $project_id      = (int) $_POST['project_id'];
        $transaction_ref =  $userId;
        $uploadedFiles   = $_FILES['attachment'] ?? null;
        $attachment_url  = $conn->real_escape_string(trim($_POST['existing_attachment'] ?? ''));
        $conn->begin_transaction();
        try {
            if($uploadedFiles && isset($uploadedFiles['name'][0]) && !empty($uploadedFiles['name'][0]) && $uploadedFiles['error'][0] === UPLOAD_ERR_OK){
                $originalFileName = basename($uploadedFiles['name'][0]);
                $fileName         = time() . '_' . $originalFileName;
                $targetPath       = $uploadDir . $fileName;
                $fileTmpName      = $uploadedFiles['tmp_name'][0];
                if(!move_uploaded_file($fileTmpName, $targetPath)) throw new Exception("Failed to upload attachment.");
                $attachment_url = $fileName;
            }
            $updated = updateTransaction($conn, $id, $ref, 'journal', $dr, $amount, $cr, $date, $transaction_ref, $desc, $currency, $currency_rate, $project_id, $userId, $attachment_url);
            if(!$updated) throw new Exception("Failed to update journal voucher.");
            $conn->commit();
            setNotification('success', 'Journal voucher updated successfully');
            echo "<script>window.location.href='../?page=journal_voucher$id=$id';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if(isset($_POST['addsalesv'])){
        $userId          = (int) $_SESSION['userid'];
        $invoice_heading = $conn->real_escape_string(trim($_POST['invoice_heading']));
        $due_date        = $conn->real_escape_string(trim($_POST['due_date']));
        $voucherdate     = $conn->real_escape_string(trim($_POST['voucherdate']));
        $reference       = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $currency        = (int) $_POST['currency'];
        $dr_account      = (int) $_POST['dr_account'];
        $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
        $tax             = (float) str_replace(',', '', $_POST['tax']);
        $totalAmount     = (float) str_replace(',', '', $_POST['total']);
        $subtotal        = (float) str_replace(',', '', $_POST['subtotal']);
        $cr_account      = (int) $_POST['sub_item'];
        $project_id      = (int) $_POST['project_id'];
        $descriptions    = $_POST['description'];
        $quantities      = $_POST['quantity'];
        $units           = $_POST['unit'];
        $prices          = $_POST['price'];
        $amounts_foreign = $_POST['amt'];
        $amounts_base    = $_POST['amount'];
        $vat_rates       = $_POST['rates'];
        $vat_amounts     = $_POST['vat_amount'];
        $conn->begin_transaction();
        try {
            $combined_description = "";
            $min_item_ids = [];
            for($i = 0; $i < count($descriptions); $i++){
                $item_description = $conn->real_escape_string(trim($descriptions[$i]));
                $check = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
                if($check && $check->num_rows > 0){
                    $min_item_ids[] = (int) $check->fetch_assoc()['id'];
                } else {
                    if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting min_subs: " . $conn->error);
                    $min_item_ids[] = $conn->insert_id;
                }
                if($i > 0) $combined_description .= ", ";
                $combined_description .= ($i + 1) . ". " . $item_description;
            }
            $transactionId = createTransaction($conn, $reference, 'sales', $dr_account, $subtotal, $cr_account, $voucherdate,  $combined_description, $currency, $currency_rate, 'draft', $userId, null,null, $project_id);
            if(!is_numeric($transactionId)) throw new Exception("Error creating transaction: " . $transactionId);
            $taxTransactionId = 0;
            if($tax > 0){
                $taxRef         = $conn->real_escape_string($reference . '/TAX');
                $taxDescription = $conn->real_escape_string("VAT Output-Sales - " . $combined_description);
                $taxTransactionId = createTransaction($conn, $taxRef, 'sales', $dr_account, $tax, 17, $voucherdate,  $taxDescription, $currency, $currency_rate, 'draft', $userId, null,null, $project_id);
                if(!is_numeric($taxTransactionId)) throw new Exception("Error creating tax transaction: " . $taxTransactionId);
            }
            $svId = createSalesVoucher($conn, $voucherdate, $reference, $currency, $currency_rate, $totalAmount, $dr_account, $transactionId, $taxTransactionId, $cr_account, $project_id, $userId, $invoice_heading, $due_date);
            if(!is_numeric($svId)) throw new Exception("Error creating sales voucher: " . $svId);
            for($i = 0; $i < count($descriptions); $i++){
                $item_description = $conn->real_escape_string(trim($descriptions[$i]));
                $quantity         = (float) str_replace(',', '', $quantities[$i]);
                $unit             = (int) $units[$i];
                $price            = (float) str_replace(',', '', $prices[$i]);
                $amt_foreign      = (float) str_replace(',', '', $amounts_foreign[$i]);
                $amt_base         = (float) str_replace(',', '', $amounts_base[$i]);
                $vat_rate         = (float) $vat_rates[$i];
                $vat_amount       = (float) str_replace(',', '', $vat_amounts[$i]);
                $det = createSalesVoucherDetail($conn, $svId, $transactionId, $quantity, $unit, $price, $amt_foreign, $amt_base, $item_description, $min_item_ids[$i], $vat_rate, $vat_amount, $userId);
                if(!is_numeric($det)) throw new Exception("Error creating detail row " . ($i + 1) . ": " . $det);
            }
            $conn->commit();
            setNotification('success', 'Sales voucher created successfully');
            echo "<script>window.location.href='../?page=sales_voucher';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    
    
    
    if(isset($_POST['editsalesv'])){
        print_r($_POST);
        $userId           = (int) $_SESSION['userid'];
        $svId             = (int) $_POST['voucher_id'];
        $transactionId    = (int) $_POST['transaction_id'];
        $taxTransactionId = (int) $_POST['tax_transaction_id'];
        $invoice_heading  = $conn->real_escape_string(trim($_POST['invoice_heading']));
        $due_date         = $conn->real_escape_string(trim($_POST['due_date']));
        $voucherdate      = $conn->real_escape_string(trim($_POST['voucherdate']));
        $reference        = $conn->real_escape_string(trim($_POST['voucher_ref']));
        $currency         = (int) $_POST['currency'];
        $dr_account       = (int) $_POST['dr_account'];
        $currency_rate    = (float) str_replace(',', '', $_POST['curr']);
        $tax              = (float) str_replace(',', '', $_POST['tax']);
        $totalAmount      = (float) str_replace(',', '', $_POST['total']);
        $subtotal         = (float) str_replace(',', '', $_POST['subtotal']);
        $cr_account       = (int) $_POST['sub_item'];
        $project_id       = (int) $_POST['project_id'];
        $descriptions     = $_POST['description'];
        $quantities       = $_POST['quantity'];
        $units            = $_POST['unit'];
        $prices           = $_POST['price'];
        $amounts_foreign  = $_POST['amt'];
        $amounts_base     = $_POST['amount'];
        $vat_rates        = $_POST['rates'];
        $vat_amounts      = $_POST['vat_amount'];
        $item_original_ids= $_POST['item_original_id'];
        $item_min_sub_ids = $_POST['item_min_sub_id'];
        $deleted_ids      = $_POST['deleted_item_ids'] ?? '';
        $conn->begin_transaction();
        try {
            $combined_description = "";
            for($i = 0; $i < count($descriptions); $i++){
                if($i > 0) $combined_description .= ", ";
                $combined_description .= ($i + 1) . ". " . $conn->real_escape_string(trim($descriptions[$i]));
            }
            if($tax > 0){
                $taxRef         = $conn->real_escape_string($reference . '/TAX/' . $svId);
                $taxDescription = $conn->real_escape_string("VAT Output-Sales - " . $combined_description);
                if($taxTransactionId > 0){
                    $updatedTax = updateTransaction($conn, $taxTransactionId, $taxRef, 'sales', $dr_account, $tax, 17, $voucherdate, '', $taxDescription, $currency, $currency_rate, $project_id, $userId, null);
                    if(!$updatedTax) throw new Exception("Failed to update tax transaction.");
                } else {
                    $newTaxId = createTransaction($conn, $taxRef, 'sales', $dr_account, $tax, 17, $voucherdate,  $taxDescription, $currency, $currency_rate, 'draft', $userId, null,null, $project_id);
                    if(!is_numeric($newTaxId)) throw new Exception("Error creating tax transaction: " . $newTaxId);
                    $taxTransactionId = $newTaxId;
                }
            }
            $updated = updateSalesVoucher($conn, $svId, $voucherdate, $reference, $currency, $currency_rate, $totalAmount, $dr_account, $transactionId, $taxTransactionId, $cr_account, $project_id, $userId, $invoice_heading, $due_date);
            if(!$updated) throw new Exception("Failed to update sales voucher.");
            $updatedTxn = updateTransaction($conn, $transactionId, $reference, 'sales', $dr_account, $subtotal, $cr_account, $voucherdate, '', $combined_description, $currency, $currency_rate, $project_id, $userId, null);
            if(!$updatedTxn) throw new Exception("Failed to update transaction.");
            if(!empty($deleted_ids)){
                $ids = explode(',', $deleted_ids);
                foreach($ids as $did){
                    $did = (int) trim($did);
                    if($did > 0) softDeleteSalesVoucherDetails($conn, $did, $userId);
                }
            }
            for($i = 0; $i < count($descriptions); $i++){
                $item_description = $conn->real_escape_string(trim($descriptions[$i]));
                $quantity         = (float) str_replace(',', '', $quantities[$i]);
                $unit             = (int) $units[$i];
                $price            = (float) str_replace(',', '', $prices[$i]);
                $amt_foreign      = (float) str_replace(',', '', $amounts_foreign[$i]);
                $amt_base         = (float) str_replace(',', '', $amounts_base[$i]);
                $vat_rate         = (float) $vat_rates[$i];
                $vat_amount       = (float) str_replace(',', '', $vat_amounts[$i]);
                $original_id      = (int) $item_original_ids[$i];
                $min_sub_id       = (int) $item_min_sub_ids[$i];
                if($original_id > 0){
                    $conn->query("UPDATE min_subs SET name = '$item_description' WHERE id = $min_sub_id");
                    $det = updateSalesVoucherDetail($conn, $original_id, $svId, $transactionId, $quantity, $unit, $price, $amt_foreign, $amt_base, $item_description, $min_sub_id, $vat_rate, $vat_amount, $userId);
                    if(!$det) throw new Exception("Failed to update detail row " . ($i + 1) . ".");
                } else {
                    $check = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
                    if($check && $check->num_rows > 0){
                        $new_min_sub_id = (int) $check->fetch_assoc()['id'];
                    } else {
                        if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting min_subs: " . $conn->error);
                        $new_min_sub_id = $conn->insert_id;
                    }
                    $det = createSalesVoucherDetail($conn, $svId, $transactionId, $quantity, $unit, $price, $amt_foreign, $amt_base, $item_description, $new_min_sub_id, $vat_rate, $vat_amount, $userId);
                    if(!is_numeric($det)) throw new Exception("Error creating new detail row: " . $det);
                }
            }
            $conn->commit();
            setNotification('success', 'Sales voucher updated successfully');
            echo "<script>window.location.href='../?page=edit_sales_voucher&id=$svId';</script>";
        } catch(Exception $e){
            $conn->rollback();
            echo $e->getMessage();
        }
    }

   if(isset($_POST['addpurchvocher'])){
      // print_r($_POST);
    $userId          = (int) $_SESSION['userid'];
    $voucherdate     = $conn->real_escape_string(trim($_POST['voucherdate']));
    $reference       = $conn->real_escape_string(trim($_POST['voucher_ref']));
    $currency        = (int) $_POST['currency'];
    $supplier_id     = (int) $_POST['cr_account'];
    $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
    $tax             = (float) str_replace(',', '', $_POST['tax']);
    $totalAmount     = (float) str_replace(',', '', $_POST['total']);
    $subtotal        = (float) str_replace(',', '', $_POST['subtotal']);
    $project_id      = (int) $_POST['project_id'];
    $dr_account      = (int) $_POST['sub_item'];
    $descriptions    = $_POST['description'];
    $quantities      = $_POST['quantity'];
    $units           = $_POST['unit'];
    $prices          = $_POST['price'];
    $amounts_foreign = $_POST['amt'];
    $amounts_base    = $_POST['amount'];
    $vat_rates       = $_POST['rates'];
    $vat_amounts     = $_POST['vat_amount'];
    $conn->begin_transaction();
    try {
        $combined_description = "";
        $min_item_ids = [];
        for($i = 0; $i < count($descriptions); $i++){
            $item_description = $conn->real_escape_string(trim($descriptions[$i]));
            $check = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
            if($check && $check->num_rows > 0){
                $min_item_ids[] = (int) $check->fetch_assoc()['id'];
            } else {
                if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting min_subs: " . $conn->error);
                $min_item_ids[] = $conn->insert_id;
            }
            if($i > 0) $combined_description .= ", ";
            $combined_description .= ($i + 1) . ". " . $item_description;
        }
        $transactionId = createTransaction($conn, $reference, 'purchases', $dr_account, $subtotal, $supplier_id, $voucherdate, $combined_description, $currency, $currency_rate, 'draft', $userId, null, null, $project_id);
        if(!is_numeric($transactionId)) throw new Exception("Error creating transaction: " . $transactionId);
        $taxTransactionId = 0;
        if($tax > 0){
            $taxRef         = $conn->real_escape_string($reference . '/TAX');
            $taxDescription = $conn->real_escape_string("VAT Input-Purchase - " . $combined_description);
            $taxTransactionId = createTransaction($conn, $taxRef, 'purchases', $supplier_id, $tax, 18, $voucherdate, $taxDescription, $currency, $currency_rate, 'draft', $userId, null, null, $project_id);
            if(!is_numeric($taxTransactionId)) throw new Exception("Error creating tax transaction: " . $taxTransactionId);
        }
        $pvId = createPurchaseVoucher($conn, $voucherdate, $reference, $currency, $currency_rate, $totalAmount, $supplier_id, $transactionId, $taxTransactionId, $project_id, $dr_account, $userId);
        if(!is_numeric($pvId)) throw new Exception("Error creating purchase voucher: " . $pvId);
        for($i = 0; $i < count($descriptions); $i++){
            $item_description = $conn->real_escape_string(trim($descriptions[$i]));
            $quantity         = (float) str_replace(',', '', $quantities[$i]);
            $unit             = (int) $units[$i];
            $price            = (float) str_replace(',', '', $prices[$i]);
            $amt_foreign      = (float) str_replace(',', '', $amounts_foreign[$i]);
            $amt_base         = (float) str_replace(',', '', $amounts_base[$i]);
            $vat_rate         = (float) $vat_rates[$i];
            $vat_amount       = (float) str_replace(',', '', $vat_amounts[$i]);
            $det = createPurchaseVoucherDetail($conn, $pvId, $transactionId, $quantity, $unit, $price, $amt_foreign, $amt_base, $item_description, $min_item_ids[$i], $vat_rate, $vat_amount, $userId);
            if(!is_numeric($det)) throw new Exception("Error creating detail row " . ($i + 1) . ": " . $det);
        }
        $conn->commit();
        setNotification('success', 'Purchase voucher created successfully');
        echo "<script>window.location.href='../?page=purchase_voucher';</script>";
    } catch(Exception $e){
        $conn->rollback();
        echo $e->getMessage();
    }
}

if(isset($_POST['editpurchvocher'])){
    $userId           = (int) $_SESSION['userid'];
    $pvId             = (int) $_POST['voucher_id'];
    $transactionId    = (int) $_POST['transaction_id'];
    $taxTransactionId = (int) $_POST['tax_transaction_id'];
    $voucherdate      = $conn->real_escape_string(trim($_POST['voucherdate']));
    $reference        = $conn->real_escape_string(trim($_POST['voucher_ref']));
    $currency         = (int) $_POST['currency'];
    $supplier_id      = (int) $_POST['dr_account'];
    $currency_rate    = (float) str_replace(',', '', $_POST['curr']);
    $tax              = (float) str_replace(',', '', $_POST['tax']);
    $totalAmount      = (float) str_replace(',', '', $_POST['total']);
    $subtotal         = (float) str_replace(',', '', $_POST['subtotal']);
    $project_id       = (int) $_POST['project_id'];
    $dr_account       = (int) $_POST['sub_item'];
    $descriptions     = $_POST['description'];
    $quantities       = $_POST['quantity'];
    $units            = $_POST['unit'];
    $prices           = $_POST['price'];
    $amounts_foreign  = $_POST['amt'];
    $amounts_base     = $_POST['amount'];
    $vat_rates        = $_POST['rates'];
    $vat_amounts      = $_POST['vat_amount'];
    $item_original_ids= $_POST['item_original_id'];
    $item_min_sub_ids = $_POST['item_min_sub_id'];
    $deleted_ids      = $_POST['deleted_item_ids'] ?? '';
    $conn->begin_transaction();
    try {
        $combined_description = "";
        for($i = 0; $i < count($descriptions); $i++){
            if($i > 0) $combined_description .= ", ";
            $combined_description .= ($i + 1) . ". " . $conn->real_escape_string(trim($descriptions[$i]));
        }
        if($tax > 0){
            $taxRef         = $conn->real_escape_string($reference . '/TAX/' . $pvId);
            $taxDescription = $conn->real_escape_string("VAT Input-Purchase - " . $combined_description);
            if($taxTransactionId > 0){
                $updatedTax = updateTransaction($conn, $taxTransactionId, $taxRef, 'purchases', 18, $tax, $supplier_id, $voucherdate, '', $taxDescription, $currency, $currency_rate, $project_id, $userId, null);
                if(!$updatedTax) throw new Exception("Failed to update tax transaction.");
            } else {
                $newTaxId = createTransaction($conn, $taxRef, 'purchases', 18, $tax, $supplier_id, $voucherdate, $taxDescription, $currency, $currency_rate, 'draft', $userId, null, null, $project_id);
                if(!is_numeric($newTaxId)) throw new Exception("Error creating tax transaction: " . $newTaxId);
                $taxTransactionId = $newTaxId;
            }
        }
        $updated = updatePurchaseVoucher($conn, $pvId, $voucherdate, $reference, $currency, $currency_rate, $totalAmount, $supplier_id, $transactionId, $taxTransactionId, $project_id, $dr_account, $userId);
        if(!$updated) throw new Exception("Failed to update purchase voucher.");
        $updatedTxn = updateTransaction($conn, $transactionId, $reference, 'purchases',$dr_account , $subtotal, $supplier_id, $voucherdate, $userId, $combined_description, $currency, $currency_rate, $project_id, $userId, null);
        if(!$updatedTxn) throw new Exception("Failed to update transaction.");
        if(!empty($deleted_ids)){
            $ids = explode(',', $deleted_ids);
            foreach($ids as $did){
                $did = (int) trim($did);
                if($did > 0) softDeletePurchaseVoucherDetails($conn, $did, $userId);
            }
        }
        for($i = 0; $i < count($descriptions); $i++){
            $item_description = $conn->real_escape_string(trim($descriptions[$i]));
            $quantity         = (float) str_replace(',', '', $quantities[$i]);
            $unit             = (int) $units[$i];
            $price            = (float) str_replace(',', '', $prices[$i]);
            $amt_foreign      = (float) str_replace(',', '', $amounts_foreign[$i]);
            $amt_base         = (float) str_replace(',', '', $amounts_base[$i]);
            $vat_rate         = (float) $vat_rates[$i];
            $vat_amount       = (float) str_replace(',', '', $vat_amounts[$i]);
            $original_id      = (int) $item_original_ids[$i];
            $min_sub_id       = (int) $item_min_sub_ids[$i];
            if($original_id > 0){
                $conn->query("UPDATE min_subs SET name = '$item_description' WHERE id = $min_sub_id");
                $det = updatePurchaseVoucherDetail($conn, $original_id, $pvId, $transactionId, $quantity, $unit, $price, $amt_foreign, $amt_base, $item_description, $min_sub_id, $vat_rate, $vat_amount, $userId);
                if(!$det) throw new Exception("Failed to update detail row " . ($i + 1) . ".");
            } else {
                $check = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
                if($check && $check->num_rows > 0){
                    $new_min_sub_id = (int) $check->fetch_assoc()['id'];
                } else {
                    if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting min_subs: " . $conn->error);
                    $new_min_sub_id = $conn->insert_id;
                }
                $det = createPurchaseVoucherDetail($conn, $pvId, $transactionId, $quantity, $unit, $price, $amt_foreign, $amt_base, $item_description, $new_min_sub_id, $vat_rate, $vat_amount, $userId);
                if(!is_numeric($det)) throw new Exception("Error creating new detail row: " . $det);
            }
        }
        $conn->commit();
        setNotification('success', 'Purchase voucher updated successfully');
        echo "<script>window.location.href='../?page=edit_purchase_voucher&id=$pvId';</script>";
    } catch(Exception $e){
        $conn->rollback();
        echo $e->getMessage();
    }
}

 
     
    if(isset($_POST['add_bulky_payment_voucher'])){
    $userId          = (int) $_SESSION['userid'];
    $ref             = $conn->real_escape_string(trim($_POST['ref_no']));
    $date            = $conn->real_escape_string(trim($_POST['date']));
    $currency        = (int) $_POST['currency'];
    $currency_rate   = (float) str_replace(',', '', $_POST['curr']);
    $cr_account      = (int) $_POST['cr_account'];
    $project_id      = (int) ($_POST['project_id'][0] ?? 0);
    $supplier        = $conn->real_escape_string(trim($_POST['supplier']));
    $payment_heading = $conn->real_escape_string(trim($_POST['over_all_description']));
    $subtotal        = (float) str_replace(',', '', $_POST['subtotal']);
    $tax             = (float) str_replace(',', '', $_POST['tax']);
    $totalAmount     = (float) str_replace(',', '', $_POST['total_equivalent_amount']);
    $descriptions    = $_POST['description'];
    $quantities      = $_POST['quantity'];
    $unit_prices     = $_POST['unit_price'];
    $units           = $_POST['unit'];
    $dr_accounts     = $_POST['dr_account'];
    $amounts         = $_POST['amount'];
    $equiv_amounts   = $_POST['equivalent_amount'];
    $vat_rates       = $_POST['rates'];
    $vat_amounts     = $_POST['vat_amount'];
    $uploadedFiles   = $_FILES['attachment'] ?? null;
    $projectIds      = $_POST['project_id'];

    $conn->begin_transaction();
    try {
        $bvId = createBulkyTransaction($conn, $date, $ref, 'bulky-payment', $subtotal, $tax, $totalAmount, $currency, $currency_rate, $project_id, $userId, $supplier, $payment_heading);
        if(!is_numeric($bvId)) throw new Exception("Error creating bulky transaction: " . $bvId);

        for($i = 0; $i < count($descriptions); $i++){
            $item_description = $conn->real_escape_string(trim($descriptions[$i]));
            $quantity         = (float) str_replace(',', '', $quantities[$i]);
            $unit_price       = (float) str_replace(',', '', $unit_prices[$i]);
            $unit             = (int) $units[$i];
            $dr               = (int) $dr_accounts[$i];
            $amount           = (float) str_replace(',', '', $amounts[$i]);
            $equiv_amount     = (float) str_replace(',', '', $equiv_amounts[$i]);
            $vat_rate         = (float) $vat_rates[$i];
            $vat_amount       = (float) str_replace(',', '', $vat_amounts[$i]);
            $itemProjectId    = (int) $projectIds[$i];
            $rowTotal         = $equiv_amount + $vat_amount;
            $fileName         = null;

            $transactionId = createTransaction($conn, $ref, 'bulky-payment', $dr, $rowTotal, $cr_account, $date, $item_description, $currency, $currency_rate, 'draft', $userId, null, null, $itemProjectId);
            if(!is_numeric($transactionId)) throw new Exception("Error creating transaction for row " . ($i + 1) . ": " . $transactionId);

            $taxTransactionId = 0;
            if($vat_amount > 0){
                $taxRef           = $ref . '/TAX/' . $transactionId;
                $taxTransactionId = createTransaction($conn, $taxRef, 'bulky-payment', 18, $vat_amount, 1326, $date, $item_description, $currency, $currency_rate, 'draft', $userId, $transactionId, null, $itemProjectId);
                if(!is_numeric($taxTransactionId)) throw new Exception("Error creating tax transaction for row " . ($i + 1) . ": " . $taxTransactionId);
            }

            if($uploadedFiles && isset($uploadedFiles['name'][$i]) && !empty($uploadedFiles['name'][$i]) && $uploadedFiles['error'][$i] === UPLOAD_ERR_OK){
                $originalFileName = basename($uploadedFiles['name'][$i]);
                $fileName         = time() . '_' . $originalFileName;
                if(!move_uploaded_file($uploadedFiles['tmp_name'][$i], $uploadDir . $fileName)) throw new Exception("Failed to upload file for row " . ($i + 1) . ".");
            }

            $check = $conn->query("SELECT id FROM min_subs WHERE name = '$item_description'");
            if($check && $check->num_rows > 0){
                $itemId = (int) $check->fetch_assoc()['id'];
            } else {
                if(!$conn->query("INSERT INTO min_subs (name) VALUES ('$item_description')")) throw new Exception("Error inserting min_subs: " . $conn->error);
                $itemId = $conn->insert_id;
            }

            $det = createBulkyTransactionItem($conn, $bvId, $dr, $cr_account, $transactionId, $taxTransactionId, $dr, $itemId, $item_description, $quantity, $unit, $unit_price, $amount, $equiv_amount, $vat_rate, $vat_amount, $fileName, $userId, $itemProjectId);
            if(!is_numeric($det)) throw new Exception("Error creating item row " . ($i + 1) . ": " . $det);
        }

        $conn->commit();
        setNotification('success', 'Bulky payment voucher created successfully');
        echo "<script>window.location.href='../?page=all_bulky_vouchers';</script>";

    } catch(Exception $e){
        $conn->rollback();
        echo $e->getMessage();
    }
}
      

}


?>