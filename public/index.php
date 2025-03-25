<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    // Destroy the session and redirect to login
    session_destroy();
    header("Location: login.php");
    exit;
}

include '../templates/header.php';
?>

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h1 class="text-center">Welcome, <span class="text-primary"><?php echo htmlspecialchars($_SESSION['username']); ?></span>!</h1>
        <p class="text-center text-muted">You are now logged into the Notice of Violation System. Manage your records effectively and efficiently.</p>
        <div class="mt-4 text-center">
            <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
        </div>
        <div class="mt-3 text-center">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</button>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="">
                    <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
