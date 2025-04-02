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

// Define current page for active navigation highlighting
$current_page = 'index.php';

// Define if user is logged in for the header template
$is_logged_in = true;

// Include the header template
include '../templates/header.php';
?>

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex flex-column justify-content-center align-items-center" style="min-height: 25vh;">
            <h1 class="text-center">Welcome, <span class="text-primary"><?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?></span>!</h1>
            <p class="text-center text-muted">You are now logged into the Notice of Violation System. Manage your records effectively and efficiently.</p>
        </div>
    </div>
</div>

<style>
    body {
        background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
    }
</style>

<?php include '../templates/footer.php'; ?>