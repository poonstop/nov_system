<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$current_page = 'user.php';
$is_logged_in = true;

// Database connection
require_once '../connection.php'; // Update this path if needed

// Check if $pdo is set
if (!isset($pdo)) {
    die("Database connection failed: PDO object not available");
}

// Fetch users from database
try {
    $stmt = $pdo->prepare("SELECT id, username, fullname, ulvl, email, status FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database error
    $error_message = "Database error: " . $e->getMessage();
    $users = []; // Initialize as empty array to prevent the foreach error
}

// Process add user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $userLevel = trim($_POST['userLevel']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match";
    
    // Check if username already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $errors[] = "Username already exists";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // Insert new user if no errors
    if (empty($errors)) {
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, ulvl, email, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $result = $stmt->execute([$username, $hashedPassword, $name, $userLevel, $email]);
            
            if ($result) {
                // Set success message
                $_SESSION['success_message'] = "User registered successfully!";
                
                // Redirect to refresh page and see the updated table
                header("Location: user.php");
                exit;
            } else {
                $errors[] = "Failed to add user. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // If there are errors, they will be displayed on the page
}

// Process edit user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $userId = $_POST['userId'];
    $name = trim($_POST['editName']);
    $userLevel = trim($_POST['editUserLevel']);
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];
    $email = isset($_POST['editEmail']) ? trim($_POST['editEmail']) : '';
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (!empty($newPassword) && $newPassword !== $confirmNewPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // Update user if no errors
    if (empty($errors)) {
        try {
            // If password is being updated
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET fullname = ?, ulvl = ?, password = ?, email = ? WHERE id = ?");
                $result = $stmt->execute([$name, $userLevel, $hashedPassword, $email, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET fullname = ?, ulvl = ?, email = ? WHERE id = ?");
                $result = $stmt->execute([$name, $userLevel, $email, $userId]);
            }
            
            if ($result) {
                // Set success message
                $_SESSION['success_message'] = "User updated successfully!";
                
                // Redirect to refresh page
                header("Location: user.php");
                exit;
            } else {
                $errors[] = "Failed to update user. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // If there are errors, they will be displayed on the page
}

// Process activate/deactivate user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $userId = $_POST['userId'];
    $newStatus = $_POST['newStatus'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $userId]);
        
        $_SESSION['success_message'] = "User status updated successfully!";
        header("Location: user.php");
        exit;
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

$pageTitle = "User Management";
include '../templates/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h1 class="display-6 fw-bold mb-3">User Management</h1>
                    <p class="lead text-muted mb-0">
                        Manage system users and assign roles
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['success_message']; 
        unset($_SESSION['success_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- User Management Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-users me-2 text-primary"></i>
                        System Users
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-1"></i> Add User
                    </button>
                </div>
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search users..." id="searchInput">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users && count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['ulvl']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if ($user['status'] == 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                                    data-id="<?php echo $user['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                    data-name="<?php echo htmlspecialchars($user['fullname']); ?>"
                                                    data-role="<?php echo htmlspecialchars($user['ulvl']); ?>"
                                                    data-email="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <?php if ($user['status'] == 'active'): ?>
                                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#toggleStatusModal" 
                                                        data-id="<?php echo $user['id']; ?>" 
                                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                        data-status="inactive">
                                                        <i class="fas fa-ban"></i> Deactivate
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#toggleStatusModal" 
                                                        data-id="<?php echo $user['id']; ?>" 
                                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                        data-status="active">
                                                        <i class="fas fa-check"></i> Activate
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="POST" action="user.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="userLevel" class="form-label">User Level:</label>
                        <select class="form-select" id="userLevel" name="userLevel" required>
                            <option value="admin">Admin</option>
                            <option value="inspector">Inspector</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password:</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <input type="hidden" name="register" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addUserForm" class="btn btn-primary">Register</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST" action="user.php">
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="editUsername" name="editUsername" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name:</label>
                        <input type="text" class="form-control" id="editName" name="editName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUserLevel" class="form-label">User Level:</label>
                        <select class="form-select" id="editUserLevel" name="editUserLevel" required>
                            <option value="admin">Admin</option>
                            <option value="inspector">Inspector</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="editEmail" name="editEmail">
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password (leave blank to keep current):</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirmNewPassword" class="form-label">Confirm New Password:</label>
                        <input type="password" class="form-control" id="confirmNewPassword" name="confirmNewPassword">
                    </div>
                    <input type="hidden" name="userId" id="editUserId">
                    <input type="hidden" name="update" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editUserForm" class="btn btn-primary">Update Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Status User Modal -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toggleStatusModalLabel">Change User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="statusAction"></span> user <strong id="statusUserName"></strong>?</p>
                <form id="toggleStatusForm" method="POST" action="user.php">
                    <input type="hidden" name="userId" id="statusUserId">
                    <input type="hidden" name="newStatus" id="newStatus">
                    <input type="hidden" name="toggle_status" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="toggleStatusForm" class="btn btn-warning" id="confirmStatusBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
        background-image: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
    }
    .card {
        border-radius: 0.5rem;
        border: none;
    }
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .modal-content {
        border-radius: 0.5rem;
        border: none;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const tbody = document.querySelector('table tbody');
            const rows = tbody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const username = row.cells[0].textContent.toLowerCase();
                const name = row.cells[1].textContent.toLowerCase();
                const role = row.cells[2].textContent.toLowerCase();
                const email = row.cells[3].textContent.toLowerCase();
                
                if (username.includes(filter) || name.includes(filter) || 
                    role.includes(filter) || email.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Password confirmation validation for Add User form
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirmPassword');
        
        // Real-time password validation
        confirmPasswordField.addEventListener('input', function() {
            if (this.value && this.value !== passwordField.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Submit validation
        addUserForm.addEventListener('submit', function(event) {
            const password = passwordField.value;
            const confirmPassword = confirmPasswordField.value;
            
            if (password !== confirmPassword) {
                event.preventDefault();
                confirmPasswordField.setCustomValidity('Passwords do not match');
                alert('Passwords do not match!');
            }
        });
    }

    // Edit user modal
    const editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            const username = button.getAttribute('data-username');
            const name = button.getAttribute('data-name');
            const role = button.getAttribute('data-role');
            const email = button.getAttribute('data-email');
            
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editName').value = name;
            document.getElementById('editUserLevel').value = role.toLowerCase();
            document.getElementById('editEmail').value = email;
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmNewPassword').value = '';
        });
    }

    // Password confirmation validation for Edit User form
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        const newPasswordField = document.getElementById('newPassword');
        const confirmNewPasswordField = document.getElementById('confirmNewPassword');
        
        // Real-time password validation
        confirmNewPasswordField.addEventListener('input', function() {
            if (this.value && this.value !== newPasswordField.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        editUserForm.addEventListener('submit', function(event) {
            const newPassword = newPasswordField.value;
            const confirmNewPassword = confirmNewPasswordField.value;
            
            if (newPassword && newPassword !== confirmNewPassword) {
                event.preventDefault();
                confirmNewPasswordField.setCustomValidity('Passwords do not match');
                alert('New passwords do not match!');
            }
        });
    }
    
    // Toggle status modal
    const toggleStatusModal = document.getElementById('toggleStatusModal');
    if (toggleStatusModal) {
        toggleStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            const username = button.getAttribute('data-username');
            const newStatus = button.getAttribute('data-status');
            const action = newStatus === 'active' ? 'activate' : 'deactivate';
            
            document.getElementById('statusUserId').value = userId;
            document.getElementById('statusUserName').textContent = username;
            document.getElementById('newStatus').value = newStatus;
            document.getElementById('statusAction').textContent = action;
            
            const confirmBtn = document.getElementById('confirmStatusBtn');
            confirmBtn.className = `btn btn-${newStatus === 'active' ? 'success' : 'warning'}`;
            confirmBtn.textContent = newStatus === 'active' ? 'Activate' : 'Deactivate';
        });
    }
    
    // Auto hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });

    // Clear the form when the modal is closed
    const addUserModal = document.getElementById('addUserModal');
    if (addUserModal) {
        addUserModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('addUserForm').reset();
            const fields = document.getElementById('addUserForm').querySelectorAll('input');
            fields.forEach(field => field.setCustomValidity(''));
        });
    }
});
</script>

<?php include '../templates/footer.php'; ?>