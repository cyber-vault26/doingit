<div class="container">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Warning Letters for 
                <?php
                $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
                $user_result = $conn->query("SELECT name FROM subsidiaries WHERE id = $user_id");
                echo ($user_result->num_rows > 0) ? $user_result->fetch_assoc()['name'] : "Unknown User";
                ?>
            </h3>
        </div>
        <div class="card-body">
            <?php
            $warnings_result = $conn->query("SELECT * FROM warnings WHERE user_id = $user_id ORDER BY warning_date DESC");
            if ($warnings_result->num_rows > 0):
            ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; ?>
                    <?php while ($row = $warnings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td>WRN-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['warning_date'])); ?></td>
                        <td>
                            <a href="./pdfs/print_warning.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View Letter
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-info">No warning letters found for this user.</div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="index.php?page=warnings_letter" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Staff List
                </a>
            </div>
        </div>
    </div>
</div>