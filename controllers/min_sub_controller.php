<?php
session_start();
if (!$_SESSION) {
    echo "<script>window.history.back();</script>";
}
require_once "../configs.php";
require_once "../functions/notification_functions.php";
require_once "../functions/min_sub_functions.php";
$conn = openConn();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $userId    = (int) $_SESSION['userid'];
    $uploadDir = '../uploads/items/';

    function handleImageUpload($fileInput, $uploadDir) {
        if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK && !empty($_FILES[$fileInput]['name'])) {
            $originalFileName = basename($_FILES[$fileInput]['name']);
            $fileName         = time() . '_' . $originalFileName;
            $targetPath       = $uploadDir . $fileName;
            if (!move_uploaded_file($_FILES[$fileInput]['tmp_name'], $targetPath)) {
                throw new Exception("Failed to upload image.");
            }
            return $fileName;
        }
        return null;
    }

    if (isset($_POST['create_minsub'])) {
        $name           = $conn->real_escape_string(trim($_POST['name']));
        $code           = !empty($_POST['code'])           ? $conn->real_escape_string(trim($_POST['code']))           : null;
        $price          = isset($_POST['price'])           && $_POST['price'] !== ''           ? (float) $_POST['price']          : null;
        $sub_id         = (int) $_POST['sub_id'];
        $subcategory    = $conn->real_escape_string(trim($_POST['subcategory']));
        $type           = $conn->real_escape_string(trim($_POST['type']));
        $base_unit      = !empty($_POST['base_unit'])      ? (int) $_POST['base_unit']                                            : null;
        $packaging_size = !empty($_POST['packaging_size']) ? $conn->real_escape_string(trim($_POST['packaging_size']))             : null;
        $reorder_level  = isset($_POST['reorder_level'])   && $_POST['reorder_level'] !== ''   ? (float) $_POST['reorder_level']   : null;

        $conn->begin_transaction();
        try {
            $image_url = handleImageUpload('image', $uploadDir);
            $check = $conn->query("SELECT id FROM min_subs WHERE code = '$code'  and deleted_at is null");
            if ($check && $check->num_rows > 0) {
                throw new Exception("Code already exists");
            }
            $result = insertMinSub($conn, $name, $code, $image_url, $price, $sub_id, $subcategory, $type, $userId, $base_unit, $packaging_size, $reorder_level);
            if (!is_int($result) || $result <= 0) throw new Exception("Failed to create Min Sub.");
            $conn->commit();
            setNotification('success', 'Min Sub created successfully');
            echo "<script>window.location.href='../?page=min_sub_list';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if (isset($_POST['update_minsub'])) {
        $id             = (int) $_POST['id'];
        $name           = $conn->real_escape_string(trim($_POST['name']));
        $code           = !empty($_POST['code'])           ? $conn->real_escape_string(trim($_POST['code']))           : null;
        $price          = isset($_POST['price'])           && $_POST['price'] !== ''           ? (float) $_POST['price']          : null;
        $sub_id         = (int) $_POST['sub_id'];
        $subcategory    = $conn->real_escape_string(trim($_POST['subcategory']));
        $type           = $conn->real_escape_string(trim($_POST['type']));
        $base_unit      = !empty($_POST['base_unit'])      ? (int) $_POST['base_unit']                                            : null;
        $packaging_size = !empty($_POST['packaging_size']) ? $conn->real_escape_string(trim($_POST['packaging_size']))             : null;
        $reorder_level  = isset($_POST['reorder_level'])   && $_POST['reorder_level'] !== ''   ? (float) $_POST['reorder_level']   : null;

        $conn->begin_transaction();
        try {
            $image_url = handleImageUpload('image', $uploadDir);
            if ($image_url === null) {
                $image_url = !empty($_POST['existing_image_url']) ? $conn->real_escape_string($_POST['existing_image_url']) : null;
            }
            //$check = $conn->query("SELECT id FROM min_subs WHERE code = '$code'  and deleted_at is null");
            // if ($check && $check->num_rows > 0) {
            //     throw new Exception("Code already exists");
            // }
            $result = updateMinSub($conn, $id, $name, $code, $image_url, $price, $sub_id, $subcategory, $type, $userId, $base_unit, $packaging_size, $reorder_level);
            if ($result !== true) throw new Exception("Failed to update Min Sub.");
            $conn->commit();
            setNotification('success', 'Min Sub updated successfully');
            echo "<script>window.location.href='../?page=min_sub_list';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
    }

    if (isset($_POST['delete_minsub'])) {
        $id = (int) $_POST['id'];
        $conn->begin_transaction();
        try {
            $result = softDeleteMinSub($conn, $id, $userId);
            if ($result !== true) throw new Exception("Failed to delete Min Sub.");
            $conn->commit();
            setNotification('success', 'Min Sub deleted successfully');
            echo "<script>window.location.href='../?page=min_sub_list';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
        }
    }
}
?>