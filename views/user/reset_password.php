<div class="card card-info">
  <div class="card-header">
    <h4 class="card-title">Reset User Password</h4>
  </div>
  <div class="card-body">
    <div class="mb-3">
    <?php
      $myid = $_SESSION['userid'];
      $users = $conn->query("SELECT id, names FROM users WHERE id = $myid");
      if (isset($_POST['restp'])) {
        $uid = $_POST['userid'];
        $newpass = password_hash($_POST['newpass'], PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$newpass' WHERE id=$uid");
         echo "<script>alert('SUCCESS'); window.location.href='./?page=logout'</script>"; 
      }
    ?>
    <form method="post" onsubmit="return confirm('Are you sure you want to reset your password to: ' + document.getElementsByName('newpass')[0].value + ' ?')">
      <select name="userid" class="form-control mb-2">
        <?php while($row = $users->fetch_assoc()) echo "<option value='{$row['id']}'>{$row['names']}</option>"; ?>
      </select>
      <input type="password" name="newpass" class="form-control mb-2" placeholder="New Password" required>
      <div class="d-flex">
        <button type="submit" name="restp" class="btn btn-info">Reset Password</button>
      </div>
    </form>
    </div>
  </div>
</div>