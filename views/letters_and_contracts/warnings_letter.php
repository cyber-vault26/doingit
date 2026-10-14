<div class="container">
        <br>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Number of Warnings</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all staff
                $sql = "SELECT * FROM subsidiaries WHERE type = 'staff'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $user_id = $row['id'];
                        $user_name = $row['name'];

                        // Count warnings for each staff
                        $count_sql = "SELECT COUNT(*) as count FROM warnings WHERE user_id = $user_id";
                        $count_result = $conn->query($count_sql);
                        $count = 0;
                        if ($count_result->num_rows > 0) {
                            $count_row = $count_result->fetch_assoc();
                            $count = $count_row['count'];
                        }

                        echo "<tr>";
                        echo "<td>$user_name</td>";
                        echo "<td>$count</td>";
                        echo "<td>";
                        echo "<a href='./pdfs/generate_warning_letter.php?user_id=$user_id' class='btn btn-warning'>Send Warning Letter</a>";
                        
                        // Add View Warnings button if count > 0
                        if ($count > 0) {
                            echo " <a href='index.php?page=view_warnings&user_id=$user_id' class='btn btn-info'>View Warnings</a>";
                        }
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No staff found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>