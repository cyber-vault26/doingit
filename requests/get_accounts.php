<?php
require_once '../configs.php';
$conn=openConn();

$query = "SELECT id, bank_name, account_number FROM bank_accounts";
$result = mysqli_query($conn, $query);

echo '<select name="bank_id" class="form-control" required>';
echo '<option value="">Select Bank Account</option>';

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['id'] . '">' . $row['bank_name'] . ' - ' . $row['account_number'] . '</option>';
    }
} else {
    echo '<option value="">No accounts found</option>';
}

echo '</select>';

mysqli_close($conn);
?>