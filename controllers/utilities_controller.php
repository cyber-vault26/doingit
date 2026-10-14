<?php
session_start();
if(!isset($_SESSION['userid'])){
    echo "<script>window.history.back();</script>";
    exit();
}
require_once "../functions/utilities_functions.php";
require_once "../functions/notification_functions.php";
require_once "../configs.php";
$conn = openConn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(isset($_POST['addunit'])){
        $name = trim($_POST['name']);
        $short = trim($_POST['short']);
        $created_by = (int) $_SESSION['userid'];
        $newUnit = insertUnit($conn, $name, $short, $created_by);
        if(is_numeric($newUnit) && $newUnit > 0){
            setNotification('success', 'Unit added successfully');
            echo "<script>window.location.href='../?page=unit_list';</script>";
        } else {
            echo $newUnit;
        }
    }

    if(isset($_POST['addcurrency'])){
        $name = trim($_POST['name']);
        $value = floatval($_POST['value']);
        $created_by = (int) $_SESSION['userid'];
        $newCurrency = insertCurrency($conn, $name, $value, $created_by);
        if(is_numeric($newCurrency) && $newCurrency > 0){
            setNotification('success', 'Currency added successfully');
            echo "<script>window.location.href='../?page=currency_list';</script>";
        } else {
            echo $newCurrency;
        }
    }

    if(isset($_POST['editunit'])){
        $id = (int) $_POST['unit_id'];
        $name = trim($_POST['name']);
        $short = trim($_POST['short']);
        $updated_by = (int) $_SESSION['userid'];
        $updateUnit = updateUnit($conn, $id, $name, $short, $updated_by);
        if($updateUnit === true){
            setNotification('success', 'Unit updated successfully');
            echo "<script>window.location.href='../?page=unit_list';</script>";
        } else {
            echo $updateUnit;
        }
    }

    if(isset($_POST['editcurrency'])){
        $id = (int) $_POST['currency_id'];
        $name = trim($_POST['name']);
        $value = floatval($_POST['value']);
        $updated_by = (int) $_SESSION['userid'];
        $updateCurrency = updateCurrency($conn, $id, $name, $value, $updated_by);
        if($updateCurrency === true){
            setNotification('success', 'Currency updated successfully');
            echo "<script>window.location.href='../?page=currency_list';</script>";
        } else {
            echo $updateCurrency;
        }
    }
    
    if(isset($_POST['delete_unit'])){
    $id = (int) $_POST['unit_id'];
    $deleted_by = (int) $_SESSION['userid'];
    $deleteUnit = softDeleteUnit($conn, $id, $deleted_by);
    if($deleteUnit === true){
        setNotification('success', 'Unit deleted successfully');
        echo "<script>window.location.href='../?page=unit_list';</script>";
    } else {
        echo $deleteUnit;
    }
}

if(isset($_POST['delete_currency'])){
    $id = (int) $_POST['currency_id'];
    $deleted_by = (int) $_SESSION['userid'];
    $deleteCurrency = softDeleteCurrency($conn, $id, $deleted_by);
    if($deleteCurrency === true){
        setNotification('success', 'Currency deleted successfully');
        echo "<script>window.location.href='../?page=currency_list';</script>";
    } else {
        echo $deleteCurrency;
    }
}

}
?>