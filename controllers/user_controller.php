<?php
session_start();
if(!$_SESSION){
    echo "<script> window.history.back() </script>";
    exit();
}
require_once "../configs.php";
require_once "../functions/user_functions.php";
require_once "../functions/subsidiary_functions.php";
require_once __DIR__ . "/../functions/notification_functions.php";
$conn = openConn();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['reg_user'])){
        $names         = $conn->real_escape_string(trim($_POST['names']));
        $email         = $conn->real_escape_string(trim($_POST['email']));
        $phone         = $conn->real_escape_string(trim($_POST['phone']));
        $ledger_id     = (int) $_POST['ledger_id'];
        $usertype      = $conn->real_escape_string($_POST['usergroup']);
        $userlevel     = (int) $_POST['level'];
        $rank_id       = (int) $_POST['rank_id'];
        $department_id = (int) $_POST['department_id'];
        $store_id      = (int) $_POST['store_id'];
        $password      = $_POST['password'];
        $has_pass      = password_hash($password, PASSWORD_DEFAULT);
        try {
            $conn->begin_transaction();
            $newUser = createUser($conn, [
                'names'         => $names,
                'email'         => $email,
                'password'      => $has_pass,
                'phone'         => $phone,
                'usertype'      => $usertype,
                'userlevel'     => $userlevel,
                'rank_id'       => $rank_id,
                'department_id' => $department_id,
                'store_id'      => $store_id,
                'status'        => 'active'
            ]);
            if(!$newUser['success']){
                throw new Exception($newUser['message']);
            }
            $userid = $newUser['user_id'];
            $newSubsidiary = createSubsidiary($conn, $names, $ledger_id, $_SESSION['userid'], 'staff', 'person', $phone, $email, null, null, null, $userid);
            if(!$newSubsidiary){
                throw new Exception("Failed to create subsidiary record for user.");
            }
            $addEmployee = addUserToEmployee($conn, [
                'user_id'       => $userid,
                'employee_code' => "MP$userid",
                'shift_start'   => '08:00:00',
                'shift_end'     => '17:00:00',
                'department'    => 'General'
            ]);
            if(!$addEmployee){
                throw new Exception("Failed to add user to employee records.");
            }
            $conn->commit();
            setNotification('success', 'User created successfully');
            echo "<script>window.location.href='../?page=register_user';</script>";
        } catch(Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
    }
    if(isset($_POST['edit_user'])){
        $data = [
            'names'         => $conn->real_escape_string(trim($_POST['names'])),
            'phone'         => $conn->real_escape_string(trim($_POST['phone'])),
            'email'         => $conn->real_escape_string(trim($_POST['email'])),
            'usertype'      => $conn->real_escape_string($_POST['usergroup']),
            'user_level'    => (int) $_POST['level'],
            'rank_id'       => (int) $_POST['rank_id'],
            'department_id' => (int) $_POST['department_id'],
            'user_id'       => (int) $_POST['user_id']
        ];
        try {
            $conn->begin_transaction();
            $updateUser = updateUserDetails($conn, $data);
            if(!$updateUser){
                throw new Exception("Failed to update user details.");
            }
            $conn->commit();
            setNotification('success', 'User updated successfully');
            echo "<script>window.location.href='../?page=user_list';</script>";
        } catch(Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
        exit();
    }
}
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    if(isset($_GET['softdelete_user'])){
        $userid = (int) $_GET['id'];
        $deleteUser = softDeleteUser($conn, $userid);
        if(!$deleteUser){
            echo $deleteUser;
            return;
        }
        setNotification('success', 'User deactivated successfully');
        echo "<script>window.history.back();</script>";
    }
}
?>