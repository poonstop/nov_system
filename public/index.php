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
        <div class="d-flex flex-column justify-content-center align-items-center" style="min-height: 25vh;">
        <h1 class="text-center">Welcome, <span class="text-primary"><?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?></span>!</h1>
            <p class="text-center text-muted">You are now logged into the Notice of Violation System. Manage your records effectively and efficiently.</p>
            </div>
        </div>
    </div>
</div>


<?php include '../templates/footer.php'; ?>
