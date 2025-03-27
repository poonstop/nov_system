<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']) ? true : false;

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NOVM System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Favicon (tab logo) -->
    <link rel="icon" href="../images/dti-logo.ico" type="../images/dti-logo.ico">
    <link rel="shortcut icon" href="../images/dti-logo.ico" type="../images/dti-logo.ico">

    <!-- For modern browsers (PNG format) -->
    <link rel="icon" type="../images/dti-logo1.png" href="../images/dti-logo1.png">
    <style>
        .navbar {
            transition: all 0.3s ease;
        }
        .navbar-nav .nav-item {
            margin: 0 10px;
        }
        .navbar-nav .nav-link {
            position: relative;
            font-weight: 500;
        }
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #007bff;
            transition: width 0.3s ease;
        }
        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }
        @media (max-width: 991px) {
            .navbar-nav {
                text-align: center;
                background-color: #f8f9fa;
                padding: 15px 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="index.php">Notice of Violation Monitoring System</a>
            
            <!-- Responsive Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'establishments.php') ? 'active' : ''; ?>" href="establishments.php">
                            File NOV
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'nov_form.php') ? 'active' : ''; ?>" href="nov_form.php">
                            Establishments Management
                        </a>
                </ul>
                
                <!-- User Info and Logout - MODIFIED SECTION -->
            <div class="d-flex align-items-center">
                <?php if ($is_logged_in): ?>
                    <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'User')); ?>!
                    </span>
                    <!-- Changed from <a> to <button> to trigger modal -->
                    <button class="btn btn-outline-danger" id="logoutBtn">
                        Logout
                    </button>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <!-- Add SweetAlert JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('logoutBtn').addEventListener('click', function() {
    Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to log out of the NOVM System?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, logout!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show logout progress with countdown
            let timerInterval;
            Swal.fire({
                title: 'Logging out...',
                html: 'You will be redirected in <b></b> seconds.',
                timer: 2000, // 2 seconds delay
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        b.textContent = (Swal.getTimerLeft() / 1000).toFixed(1);
                    }, 100);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            }).then((result) => {
                // Perform the actual logout when timer completes
                if (result.dismiss === Swal.DismissReason.timer) {
                    window.location.href = 'logout.php';
                }
            });
        }
    });
});
</script>


    <!-- Your existing scripts remain the same -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
</body>
</html>