 <?php
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    ?>
    <div class="container">
        <div class="row">
            <?php 
            $contracts_result = $conn->query("SELECT * FROM contracts WHERE user_id = $user_id ORDER BY contract_date DESC");
            if ($contracts_result && $contracts_result->num_rows > 0):
            ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while($contract = $contracts_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td>CTR-<?php echo str_pad($contract['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d M Y', strtotime($contract['contract_date'])); ?></td>
                                <td>
                                    <a href="./pdfs/print_contracts.php?id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View Contract
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    No contracts found for this user.
                </div>
            <?php endif; ?>
            
            <div class="mt-3">
                <a href="?page=staff_list" class="btn btn-secondary">Back to Staff List</a>
            </div>
        </div>
    </div>