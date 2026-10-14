<?php    // Query to get all distinct staff members with leave requests and count their requests
    $query = "SELECT 
                lr.staff_id, 
                COALESCE(lr.staff_type, 'users') as staff_type,
                CASE 
                    WHEN lr.staff_type = 'SUBSIDIARIES' THEN s.name
                    ELSE u.names 
                END as staff_name, 
                COUNT(lr.id) as request_count 
            FROM leave_request lr
            LEFT JOIN users u ON (lr.staff_id = u.id AND (lr.staff_type IS NULL OR lr.staff_type = 'users'))
            LEFT JOIN subsidiaries s ON (lr.staff_id = s.id AND lr.staff_type = 'SUBSIDIARIES')
            GROUP BY lr.staff_id, lr.staff_type, staff_name
            ORDER BY staff_name ASC";
    $result = mysqli_query($conn, $query);
?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Leave Reports - Staff Summary</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Staff Name</th>
                        <th>Total Leave Requests</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(mysqli_num_rows($result) > 0) {
                        $counter = 1;
                        while($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>'.$counter.'</td>';
                            echo '<td>'.$row['staff_name'].'</td>';
                            echo '<td>'.$row['request_count'].'</td>';
                            echo '<td>
                                    <a href="?page=staff_profile&staff_id='.$row['staff_id'].'&staff_type='.$row['staff_type'].'" class="btn btn-sm btn-primary">
                                        <i class="fas fa-user mr-1"></i> View Profile
                                    </a>
                                </td>';
                            echo '</tr>';
                            $counter++;
                        }
                    } else {
                        echo '<tr><td colspan="4" class="text-center">No staff with leave requests found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>