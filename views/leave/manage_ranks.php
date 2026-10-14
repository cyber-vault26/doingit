<?php
// Include necessary files
require_once __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/user_functions.php";

// Get database connection
$conn = openConn();

// Check if user has permission to manage ranks (only HR/Admin)
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'Manager') {
    echo "<script>alert('Access denied. You do not have permission to manage staff ranks.'); window.location.href='./';</script>";
    exit;
}

// Handle form submissions
$message = '';
$error = '';


// Get all ranks
$ranks = [];
$sql = "SELECT * FROM staff_ranks ORDER BY sort_order";
$result = $conn->query($sql);
if ($result) {
    $ranks = $result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-tag me-2"></i>Manage Staff Ranks</h2>
                    <div class="action-buttons">
                        <a href="./?page=leave_policies_list" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Leave
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Create New Rank Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Create New Rank
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="./controllers/rank_controller.php" id="createRankForm">
                            <input type="hidden" name="action" value="create_rank">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rank_name" class="form-label">Rank Name *</label>
                                        <input type="text" class="form-control" id="rank_name" name="rank_name" required
                                               placeholder="e.g., Manager, Supervisor, Officer">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">Sort Order *</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" required
                                               min="1" placeholder="e.g., 10, 20, 30">
                                        <small class="form-text text-muted">Lower numbers appear first in lists</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="1"
                                                  placeholder="Brief description of the rank role"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Create Rank
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existing Ranks List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Existing Staff Ranks
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ranks)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No staff ranks found. Create your first rank above.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="60">#</th>
                                            <th>Rank Name</th>
                                            <th>Description</th>
                                            <th width="100">Sort Order</th>
                                            <th width="100">Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ranks as $index => $rank): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($rank['rank_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($rank['description'] ?: 'No description'); ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $rank['sort_order']; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($rank['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-primary" 
                                                                onclick="editRank(<?php echo $rank['id']; ?>, '<?php echo htmlspecialchars($rank['rank_name']); ?>', '<?php echo htmlspecialchars($rank['description']); ?>', <?php echo $rank['sort_order']; ?>)"
                                                                title="Edit Rank">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-<?php echo $rank['is_active'] ? 'warning' : 'success'; ?>" 
                                                                onclick="toggleStatus(<?php echo $rank['id']; ?>)"
                                                                title="<?php echo $rank['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                            <i class="fas fa-<?php echo $rank['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count($ranks); ?></h4>
                                <p class="mb-0">Total Ranks</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($ranks, fn($r) => $r['is_active'])); ?></h4>
                                <p class="mb-0">Active Ranks</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($ranks, fn($r) => !$r['is_active'])); ?></h4>
                                <p class="mb-0">Inactive Ranks</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Rank Modal -->
    <div class="modal fade" id="editRankModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Staff Rank
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editRankForm">
                        <input type="hidden" name="action" value="edit_rank">
                        <input type="hidden" id="edit_rank_id" name="rank_id">
                        
                        <div class="mb-3">
                            <label for="edit_rank_name" class="form-label">Rank Name *</label>
                            <input type="text" class="form-control" id="edit_rank_name" name="rank_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_sort_order" class="form-label">Sort Order *</label>
                            <input type="number" class="form-control" id="edit_sort_order" name="sort_order" required min="1">
                            <small class="form-text text-muted">Lower numbers appear first in lists</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editRankForm" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Rank
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit rank function
        function editRank(id, name, description, sortOrder) {
            document.getElementById('edit_rank_id').value = id;
            document.getElementById('edit_rank_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_sort_order').value = sortOrder;
            
            var modal = new bootstrap.Modal(document.getElementById('editRankModal'));
            modal.show();
        }
        
        // Toggle status function
        function toggleStatus(rankId) {
            if (confirm('Are you sure you want to toggle the status of this rank?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="toggle_status"><input type="hidden" name="rank_id" value="' + rankId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Form validation
        document.getElementById('createRankForm').addEventListener('submit', function(e) {
            var rankName = document.getElementById('rank_name').value.trim();
            var sortOrder = document.getElementById('sort_order').value;
            
            if (rankName === '') {
                e.preventDefault();
                alert('Please enter a rank name');
                return false;
            }
            
            if (sortOrder === '' || sortOrder < 1) {
                e.preventDefault();
                alert('Please enter a valid sort order (minimum 1)');
                return false;
            }
            
            return true;
        });
        
        document.getElementById('editRankForm').addEventListener('submit', function(e) {
            var rankName = document.getElementById('edit_rank_name').value.trim();
            var sortOrder = document.getElementById('edit_sort_order').value;
            
            if (rankName === '') {
                e.preventDefault();
                alert('Please enter a rank name');
                return false;
            }
            
            if (sortOrder === '' || sortOrder < 1) {
                e.preventDefault();
                alert('Please enter a valid sort order (minimum 1)');
                return false;
            }
            
            return true;
        });
    </script>
