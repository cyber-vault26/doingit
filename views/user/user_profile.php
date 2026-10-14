 

<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">User Profile</h4>
    </div>

 
<div class="card-body">
    <?php
    $myid = $_SESSION['userid'];
    $user = $conn->query("SELECT * FROM users WHERE id = $myid")->fetch_assoc();

    if (isset($_POST['restp'])) {
        $uid = $_POST['userid'];
        $newpass = password_hash($_POST['newpass'], PASSWORD_DEFAULT);

        $conn->query("UPDATE users SET password='$newpass' WHERE id=$uid");

        echo "<script>
                alert('SUCCESS');
                window.location.href='./?page=logout';
              </script>";
    }

    if (isset($_POST['update_info'])) {

        $id    = $_POST['id'];
        $name  = $_POST['names'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $conn->query("
            UPDATE users SET
                names='$name',
                phone='$phone',
                email='$email'
            WHERE id=$id
        ");

        echo "<script>
                alert('SUCCESS');
                window.location.href='./?page=user_profile';
              </script>";

        $user = $conn->query("SELECT * FROM users WHERE id = $myid")->fetch_assoc();
    }
    ?>

    <div class="row">
        <div class="form-group col-md-6">
            <label>Full Name</label>
            <input type="text" class="form-control" value="<?= $user['names'] ?>" readonly>
        </div>

        <div class="form-group col-md-6">
            <label>Phone</label>
            <input type="text" class="form-control" value="<?= $user['phone'] ?>" readonly>
        </div>

        <div class="form-group col-md-6">
            <label>Email</label>
            <input type="text" class="form-control" value="<?= $user['email'] ?>" readonly>
        </div>

        <div class="form-group col-md-6">
            <label>User Type</label>
            <input type="text" class="form-control" value="<?= $user['usertype'] ?>" readonly>
        </div>

        <div class="form-group col-md-4">
            <label>User Level</label>
            <input type="text" class="form-control" value="<?= $user['user_level'] ?>" readonly>
        </div>

        <div class="form-group col-md-4">
            <label>Status</label>
            <input type="text" class="form-control" value="<?= $user['status'] ?>" readonly>
        </div>

        <!--<div class="form-group col-md-4">-->
        <!--    <label>Cost Center</label>-->
        <!--    <input type="text" class="form-control" value="<?= $user['cost_center'] ?>" readonly>-->
        <!--</div>-->
    </div>

    <button type="button"
            class="btn btn-warning mt-3"
            data-bs-toggle="modal"
            data-bs-target="#editModal">
        Edit Info
    </button>

    <hr class="my-4">

    <h5 class="mt-4">Reset Password</h5>

    <form method="post" onsubmit="return confirm('Are you sure you want to reset your password to: ' + document.getElementsByName('newpass')[0].value + ' ?')">

        <input type="hidden" name="userid" value="<?= $user['id'] ?>">

        <div class="row">
            <div class="form-group col-md-8">
                <input type="password"
                       name="newpass"
                       class="form-control"
                       placeholder="New Password"
                       required>
            </div>

            <div class="form-group col-md-4">
                <button type="submit"
                        name="restp"
                        class="btn btn-info w-100">
                    Reset Password
                </button>
            </div>
        </div>
    </form>
</div>
 

</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

 
        <form method="post">

            <div class="modal-header">
                <h5 class="modal-title">Edit User Info</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <input type="hidden"
                       name="id"
                       value="<?= $user['id'] ?>">

                <div class="row">

                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text"
                               name="names"
                               class="form-control"
                               value="<?= $user['names'] ?>"
                               required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="<?= $user['phone'] ?>"
                               required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="<?= $user['email'] ?>"
                               required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>User Type</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $user['usertype'] ?>"
                               readonly>
                    </div>

                    <div class="form-group col-md-4">
                        <label>User Level</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $user['user_level'] ?>"
                               readonly>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Status</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $user['status'] ?>"
                               readonly>
                    </div>

                    <!--<div class="form-group col-md-4">-->
                    <!--    <label>Cost Center</label>-->
                    <!--    <input type="text"-->
                    <!--           class="form-control"-->
                    <!--           value="<?= $user['cost_center'] ?>"-->
                    <!--           readonly>-->
                    <!--</div>-->

                </div>
            </div>

            <div class="modal-footer">
                <button type="submit"
                        name="update_info"
                        class="btn btn-primary">
                    Update Info
                </button>

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>

        </form>

    </div>
</div>
 

</div>

 
