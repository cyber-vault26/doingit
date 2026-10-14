<?php
// Include necessary files
require_once __DIR__ . "/../../configs.php";
require_once __DIR__ . "/../../functions/department_functions.php";
require_once __DIR__ . "/../../functions/user_functions.php";
require_once __DIR__ . "/../../functions/360_evaluation_functions.php";

// Get database connection
$conn = openConn();

// Check if user has permission to view review questions
if ($_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'system_admin' && $_SESSION['usertype'] !== 'hr_admin') {
    echo "<script>alert('Access denied. You do not have permission to view review questions.'); window.location.href='./';</script>";
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

// Get all departments for filter
$departments = listdepartments($conn);

// Get ALL review questions (no PHP filtering - will be done in JavaScript)
$questions = getAllReviewQuestions($conn);

$conn->close();
?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            
            <div class="col-12">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-list me-2"></i>Review Questions List</h2>
                    <div class="action-buttons">
                        <a href="./?page=add_review_questions" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add New Question
                        </a>
                        <a href="./?page=360_perfomance" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4 id="totalQuestions"><?php echo count($questions); ?></h4>
                                <p class="mb-0">Total Questions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4 id="activeQuestions"><?php echo count(array_filter($questions, fn($q) => $q['is_active'])); ?></h4>
                                <p class="mb-0">Active</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h4 id="deptSpecificQuestions"><?php echo count(array_filter($questions, fn($q) => $q['department_id'])); ?></h4>
                                <p class="mb-0">Department Specific</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4 id="generalQuestions"><?php echo count(array_filter($questions, fn($q) => !$q['department_id'])); ?></h4>
                                <p class="mb-0">General</p>
                            </div>
                        </div>
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

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category" onchange="filterQuestions()">
                                    <option value="">All Categories</option>
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
                            <div class="col-md-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-control" id="department" name="department" onchange="filterQuestions()">
                                    <option value="">All Departments</option>
                                    <option value="general">General Questions</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" onchange="filterQuestions()">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-none">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" onclick="filterQuestions()">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Questions Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Review Questions
                            <span class="badge bg-primary ms-2" id="questionCount"><?php echo count($questions); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($questions)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No review questions found. Add your first question above.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="60">#</th>
                                            <th>Category</th>
                                            <th>Question</th>
                                            <th>Type</th>
                                            <th>Department</th>
                                            <th>Weight</th>
                                            <th>Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="questionsTableBody">
                                        <?php foreach ($questions as $question): 
                                            $counter = 1;
                                            ?>
                                            <tr data-category="<?php echo htmlspecialchars($question['category']); ?>" 
                                                data-department="<?php echo $question['department_id'] ?: 'general'; ?>" 
                                                data-status="<?php echo $question['is_active']; ?>">
                                                <td><?php echo $counter; ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($question['category']); ?></span>
                                                </td>
                                                <td>
                                                    <div style="max-width: 350px; overflow: hidden; text-overflow: ellipsis;">
                                                        <?php echo htmlspecialchars($question['question_text']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $typeIcons = [
                                                        'rating' => 'fa-star text-warning',
                                                        'yes_no' => 'fa-check-circle text-success',
                                                        'text' => 'fa-comment text-primary'
                                                    ];
                                                    $typeLabels = [
                                                        'rating' => 'Rating',
                                                        'yes_no' => 'Yes/No',
                                                        'text' => 'Text'
                                                    ];
                                                    ?>
                                                    <i class="fas <?php echo $typeIcons[$question['question_type']]; ?> me-1"></i>
                                                    <?php echo $typeLabels[$question['question_type']]; ?>
                                                </td>
                                                <td>
                                                    <?php if ($question['department_id']): ?>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($question['department_name']); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary">General</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $question['weight']; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($question['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info" 
                                                                onclick="viewQuestion(<?php echo $question['id']; ?>)"
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning" 
                                                                onclick="editQuestion(<?php echo $question['id']; ?>)"
                                                                title="Edit Question">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-<?php echo $question['is_active'] ? 'secondary' : 'success'; ?>" 
                                                                onclick="toggleQuestion(<?php echo $question['id']; ?>, <?php echo $question['is_active'] ? 0 : 1; ?>)"
                                                                title="<?php echo $question['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                            <i class="fas fa-<?php echo $question['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deleteQuestion(<?php echo $question['id']; ?>)"
                                                                title="Delete Question">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php 
                                        $counter++;
                                     endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
            </div>
        </div>
    </div>

    <!-- View Question Modal -->
    <div class="modal fade" id="viewQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Question Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="questionDetails">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store all questions data
        const allQuestions = {
            <?php foreach ($questions as $question): ?>
                <?php echo $question['id']; ?>: 
                {
                    id: <?php echo $question['id']; ?>,
                    category: '<?php echo addslashes($question['category']); ?>',
                    question_text: '<?php echo addslashes($question['question_text']); ?>',
                    question_type: '<?php echo $question['question_type']; ?>',
                    department_name: '<?php echo addslashes($question['department_name'] ?? 'General'); ?>',
                    department_id: <?php echo $question['department_id'] ?: 'null'; ?>,
                    weight: '<?php echo $question['weight']; ?>',
                    is_active: <?php echo $question['is_active']; ?>,
                    created_at: '<?php echo $question['created_at']; ?>'
                }
                <?php endforeach; ?>
        };
        
        // Filter questions function
        function filterQuestions() {
            const category = document.getElementById('category').value;
            const department = document.getElementById('department').value;
            const status = document.getElementById('status').value;
            
            const rows = document.querySelectorAll('#questionsTableBody tr');
            let visibleCount = 0;
            let activeCount = 0;
            let deptSpecificCount = 0;
            let generalCount = 0;
            
            rows.forEach(row => {
                const rowCategory = row.getAttribute('data-category');
                const rowDepartment = row.getAttribute('data-department');
                const rowStatus = row.getAttribute('data-status');
                
                let showRow = true;
                
                // Category filter
                if (category && rowCategory !== category) {
                    showRow = false;
                }
                
                // Department filter
                if (department) {
                    if (department === 'general' && rowDepartment !== 'general') {
                        showRow = false;
                    } else if (department !== 'general' && rowDepartment !== department) {
                        showRow = false;
                    }
                }
                
                // Status filter
                if (status !== '' && rowStatus !== status) {
                    showRow = false;
                }
                
                if (showRow) {
                    row.style.display = '';
                    visibleCount++;
                    
                    // Update statistics for visible rows
                    if (rowStatus === '1') activeCount++;
                    if (rowDepartment !== 'general') deptSpecificCount++;
                    else generalCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update counts
            updateStatistics(visibleCount, activeCount, deptSpecificCount, generalCount);
        }
        
        // Clear filters function
        function clearFilters() {
            document.getElementById('category').value = '';
            document.getElementById('department').value = '';
            document.getElementById('status').value = '';
            
            // Show all rows
            const rows = document.querySelectorAll('#questionsTableBody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            
            // Reset statistics to original values
            const totalQuestions = Object.keys(allQuestions).length;
            const activeCount = Object.values(allQuestions).filter(q => q.is_active).length;
            const deptSpecificCount = Object.values(allQuestions).filter(q => q.department_id !== null).length;
            const generalCount = Object.values(allQuestions).filter(q => q.department_id === null).length;
            
            updateStatistics(totalQuestions, activeCount, deptSpecificCount, generalCount);
        }
        
        // Update statistics function
        function updateStatistics(total, active, deptSpecific, general) {
            document.getElementById('questionCount').textContent = total;
            document.getElementById('totalQuestions').textContent = total;
            document.getElementById('activeQuestions').textContent = active;
            document.getElementById('deptSpecificQuestions').textContent = deptSpecific;
            document.getElementById('generalQuestions').textContent = general;
        }
        
        // View question details
        function viewQuestion(questionId) {
            const question = allQuestions[questionId];
            if (question) {
                const typeLabels = {
                    'rating': 'Rating Scale (1-5)',
                    'yes_no': 'Yes/No',
                    'text': 'Text Response'
                };
                
                let content = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Category:</strong> ${question.category}<br>
                            <strong>Type:</strong> ${typeLabels[question.question_type]}<br>
                            <strong>Department:</strong> ${question.department_name}<br>
                            <strong>Weight:</strong> ${question.weight}<br>
                            <strong>Status:</strong> <span class="badge bg-${question.is_active ? 'success' : 'secondary'}">${question.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Question ID:</strong> ${question.id}<br>
                            <strong>Created:</strong> ${new Date(question.created_at).toLocaleString()}
                        </div>
                        <div class="col-12 mt-3">
                            <strong>Question:</strong><br>
                            <div class="alert alert-light">
                                ${question.question_text}
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('questionDetails').innerHTML = content;
                var modal = new bootstrap.Modal(document.getElementById('viewQuestionModal'));
                modal.show();
            }
        }
        
        // Edit question
        function editQuestion(questionId) {
            window.location.href = `./?page=add_review_questions&edit_id=${questionId}`;
        }
        
        // Toggle question status
        function toggleQuestion(questionId, newStatus) {
            if (confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this question?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = './controllers/360_evaluation_controller.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_question">
                    <input type="hidden" name="question_id" value="${questionId}">
                    <input type="hidden" name="is_active" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Delete question
        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = './controllers/360_evaluation_controller.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_question">
                    <input type="hidden" name="question_id" value="${questionId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial statistics
            const totalQuestions = Object.keys(allQuestions).length;
            const activeCount = Object.values(allQuestions).filter(q => q.is_active).length;
            const deptSpecificCount = Object.values(allQuestions).filter(q => q.department_id !== null).length;
            const generalCount = Object.values(allQuestions).filter(q => q.department_id === null).length;
            
            updateStatistics(totalQuestions, activeCount, deptSpecificCount, generalCount);
        });
    </script>
