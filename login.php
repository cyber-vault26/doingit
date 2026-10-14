<?php
session_start();
include("./configs.php");
require_once "./functions/user_functions.php";
$conn = openConn();
session_unset();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tellicerp</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="./plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="./dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="../../index2.html" class="h1"><b>TELLIC</b>ERP</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Sign in to start your session</p>
      <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
        <div class="input-group mb-3">
          <input type="email" name='email' required class="form-control" placeholder="Email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name='password' required class="form-control" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-4">
            <button type="submit" name='login' class="btn btn-primary btn-block">Sign In</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['login'])) {
        $email    = htmlspecialchars($conn->real_escape_string($_POST['email']));
        $password = trim($_POST['password']);
        $user     = selectUserByEmail($conn, $email);
        $data = [
            "email"      => $email,
            "session_id" => session_id(),
            "ip_address" => $_SERVER['REMOTE_ADDR'],
            "user_agent" => $_SERVER['HTTP_USER_AGENT'],
        ];
        if ($user && count($user) > 0) {
            $data['user_id'] = $user['id'];
            if ($user['status'] === 'active') {
                if (password_verify($password, $user['password'])) {
                    $data['status'] = 'success';
                    recordUserLogin($conn, $data);
                    $_SESSION['userid']      = $user['id'];
                    $_SESSION['usertype']    = $user['usertype'];
                    $_SESSION['username']    = $user['names'];
                    $_SESSION['userlevel']   = $user['user_level'];
                    $_SESSION['Level']       = $user['user_level'];
                    $_SESSION['level']       = $user['user_level'];
                    $_SESSION['cost_center'] = $user['store_id'];
                     $_SESSION['department_id'] = $user['department_id'];
                    $_SESSION['rank_id'] = $user['rank_id'];
                    if ($user['usertype'] === 'normal') {
                        echo "<script>window.location.href='./customer/'</script>";
                    } else {
                        echo "<script>window.location.href='./'</script>";
                    }
                } else {
                    $data['status']         = 'failed';
                    $data['failure_reason'] = 'wrong_password';
                    recordUserLogin($conn, $data);
                    echo "<script>alert('Wrong password'); window.location.href='./login.php'</script>";
                }
            } elseif ($user['status'] === 'pending') {
                $data['status']         = 'failed';
                $data['failure_reason'] = 'pending_verification';
                recordUserLogin($conn, $data);
                echo "<p class='text-center text-danger'>Your Account of membership is not verified please contact your readers || Akaount ya uanachama haijadhibitishwa tafadhari wasiliana na uongozi</p>";
            } elseif (in_array($user['status'], ['deleted', 'inactive', 'quit'])) {
                $data['status']         = 'failed';
                $data['failure_reason'] = 'account_suspended';
                recordUserLogin($conn, $data);
                echo "<p class='text-center text-danger'>Your Account of membership has been suspended please contact your readers || Akaount ya uanachama imesimamishwa tafadhari wasiliana na uongozi</p>";
            }
        } else {
            $data['user_id']        = NULL;
            $data['status']         = 'failed';
            $data['failure_reason'] = 'user_not_found';
            recordUserLogin($conn, $data);
            echo "<script>alert('No User found'); window.location.href='./login.php'</script>";
        }
    }
}
?>
<script src="./plugins/jquery/jquery.min.js"></script>
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="./dist/js/adminlte.min.js"></script>
</body>
</html>  

<!--<!DOCTYPE html>-->
<!--<html lang="en">-->
<!--<head>-->
<!--<meta charset="UTF-8">-->
<!--<meta name="viewport" content="width=device-width, initial-scale=1.0">-->
<!--<title>Session Expired</title>-->
<!--<style>-->
<!--  body {-->
<!--    font-family: Arial, Helvetica, sans-serif;-->
<!--    background-color: #f4f4f4;-->
<!--    margin: 0;-->
<!--    padding: 0;-->
<!--    display: flex;-->
<!--    justify-content: center;-->
<!--    align-items: center;-->
<!--    min-height: 100vh;-->
<!--  }-->
<!--  .box {-->
<!--    background-color: #ffffff;-->
<!--    border: 1px solid #cccccc;-->
<!--    border-radius: 6px;-->
<!--    padding: 30px 25px;-->
<!--    max-width: 420px;-->
<!--    width: 90%;-->
<!--    text-align: center;-->
<!--    box-shadow: 0 0 8px rgba(0,0,0,0.1);-->
<!--  }-->
<!--  h1 {-->
<!--    color: #b30000;-->
<!--    font-size: 22px;-->
<!--    margin-bottom: 15px;-->
<!--  }-->
<!--  p {-->
<!--    color: #333333;-->
<!--    font-size: 15px;-->
<!--    line-height: 1.5;-->
<!--    margin: 10px 0;-->
<!--  }-->
<!--  .contact {-->
<!--    margin-top: 20px;-->
<!--    padding-top: 15px;-->
<!--    border-top: 1px solid #e0e0e0;-->
<!--    font-size: 14px;-->
<!--    color: #444444;-->
<!--  }-->
<!--  .contact a {-->
<!--    color: #0056b3;-->
<!--    text-decoration: none;-->
<!--  }-->
<!--  .contact a:hover {-->
<!--    text-decoration: underline;-->
<!--  }-->
<!--</style>-->
<!--</head>-->
<!--<body>-->

<!--<div class="box">-->
<!--  <h1>Session Expired</h1>-->
<!--  <p>Please make payment to continue using this system.</p>-->
<!--  <p>This may appear due to a payment issue. Kindly contact Tellic ERP for help.</p>-->

<!--  <div class="contact">-->
<!--    <p>Website: <a href="https://tellicerp.co.tz" target="_blank">tellicerp.co.tz</a></p>-->
<!--    <p>Phone: <a href="tel:+255756882617">+255 756 882 617</a></p>-->
<!--    <p>Email: info@tellicerp.co.tz</p>-->
<!--  </div>-->
<!--</div>-->

<!--</body>-->
<!--</html>-->