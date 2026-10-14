<?php
// Include necessary files
require_once __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/department_functions.php";
require_once __DIR__ . "/../../functions/user_functions.php";
require_once __DIR__ . "/../../functions/360_evaluation_functions.php";

// Get database connection
$conn = openConn();

// Check if user has permission to manage review questions
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'system_admin' && $_SESSION['usertype'] !== 'hr_admin') {
    echo "<script>alert('Access denied. You do not have permission to manage review questions.'); window.location.href='./';</script>";
    exit;
}

// Handle form submissions - just display messages from controller
$message = '';
$error = '';

// Get success/error messages from URL parameters
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// Get all departments for the form
$departments = listdepartments($conn);

$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-question-circle me-2"></i>Manage Review Questions</h2>
                    <div class="action-buttons">
                        <a href="./?page=360_dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
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

                <!-- Add Question Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus me-2"></i>Add New Review Question
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="./controllers/360_evaluation_controller.php">
                            <input type="hidden" name="action" value="add_question">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category *</label>
                                        <select class="form-control" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="General">General Competencies</option>
                                            <option value="Technical">Technical Skills</option>
                                            <option value="Leadership">Leadership</option>
                                            <option value="Communication">Communication</option>
                                            <option value="Performance">Performance</option>
                                            <option value="Teamwork">Teamwork</option>
                                            <option value="Initiative">Initiative</option>
                                            <option value="Professionalism">Professionalism</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department_id" class="form-label">Department</label>
                                        <select class="form-control" id="department_id" name="department_id">
                                            <option value="">General (All Departments)</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">Leave empty for general questions (apply to all departments)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="question_text" class="form-label">Question Text *</label>
                                        <textarea class="form-control" id="question_text" name="question_text" rows="3" 
                                                  placeholder="Enter the review question..." required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="question_type" class="form-label">Question Type *</label>
                                        <select class="form-control" id="question_type" name="question_type" required>
                                            <option value="rating">Rating Scale (1-5)</option>
                                            <option value="yes_no">Yes/No</option>
                                            <option value="text">Text Response</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="weight" class="form-label">Weight</label>
                                        <input type="number" class="form-control" id="weight" name="weight" 
                                               min="0.1" max="3.0" step="0.1" value="1.0" required>
                                        <small class="form-text text-muted">Weight for scoring (0.1 - 3.0, default 1.0)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Add Question
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const questionText = document.getElementById('question_text').value.trim();
            if (questionText.length < 10) {
                e.preventDefault();
                alert('Question text must be at least 10 characters long.');
                return false;
            }
        });
    </script>
