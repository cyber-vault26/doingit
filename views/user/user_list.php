<div class=" card card-info">
    <div class=" card-header">
        <h4 class=" card-title"> List of user</h4>

    </div>
    <div class=" card-body">
        <table class=" table table-sm table-bordered">
            <tr  class="  table-secondary">
                <td>#</td>
                <td>Names</td>
                <td>Phone</td>
                <td>Email</td>
                <td>type</td>
                <td>Action</td>
            </tr>
            <?php
                $users = selectAllUsers($conn);
                if($users && is_array($users)){
                    $counter=1;
                    foreach($users as $user){
                        echo "<tr>";
                                echo "<td>$counter</td>";
                                echo "<td>$user[names]</td>";
                                echo "<td>$user[phone]</td>";
                                echo "<td>$user[email]</td>";
                                
                                echo "<td>$user[usertype]</td>";
                                echo "<td>";
                                        if($user['usertype'] !=='Admin' && $user['usertype'] !=='manager'){
                                                  echo "<a href='./?page=edit_user&user_id=$user[id]' class=' btn btn-primary btn-sm m-1'>Edit</a>";
                                                  echo "<a href='./controllers/user_controller.php?softdelete_user&id=$user[id]' class=' btn btn-sm btn-warning'  >delete</a>";
                                         }
                                echo "</td>";

                        echo "</td>";
                        $counter++;
                    }
                }
            ?>
        </table>
    </div>
</div>