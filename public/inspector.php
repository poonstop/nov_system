<?php
session_start();

// Check if user is logged in and is an inspector
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'inspector') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Inspector Dashboard";
include '../templates/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h1 class="display-6 fw-bold mb-3">Hello World!</h1>
                    <p class="lead text-muted mb-0">
                        Welcome, Inspector <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Inspector Content -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-clipboard-check me-2 text-primary"></i>
                        Inspector Dashboard
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4 class="alert-heading">Inspector View</h4>
                        <p>This is a specialized view for inspectors. You can add inspector-specific functionality here.</p>
                        <hr>
                        <p class="mb-0">Current user level: <strong><?php echo htmlspecialchars($_SESSION['user_level']); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>