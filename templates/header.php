<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Set session timeout to 10 minutes (600 seconds)
$inactive = 600;
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
}
$_SESSION['timeout'] = time();

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Favicon (tab logo) -->
    <link rel="icon" href="../images/dti-logo.ico" type="../images/dti-logo.ico">
    <link rel="shortcut icon" href="../images/dti-logo.ico" type="../images/dti-logo.ico">

    <!-- For modern browsers (PNG format) -->
    <link rel="icon" type="../images/dti-logo1.png" href="../images/dti-logo1.png">
    <style>
        :root {
            --sidebar-expanded-width: 250px;
            --sidebar-collapsed-width: 70px;
            --sidebar-bg: #343a40;
            --sidebar-hover: #4e5962;
            --sidebar-active: #007bff;
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            background-color: #f8f9fa;
        }
        
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        
        #sidebar {
            width: var(--sidebar-collapsed-width);
            background-color: var(--sidebar-bg);
            color: #fff;
            transition: all var(--transition-speed) ease;
            position: fixed;
            height: 100vh;
            z-index: 999;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        #sidebar:hover {
            width: var(--sidebar-expanded-width);
        }
        
        #sidebar .sidebar-header {
            padding: 15px 10px;
            background: #212529;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
        }
        
        #sidebar .sidebar-header h4 {
            opacity: 0;
            transition: opacity var(--transition-speed);
            margin-left: 30px;
        }
        
        #sidebar:hover .sidebar-header h4 {
            opacity: 1;
        }
        
        #sidebar .logo-small {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 1.5rem;
            font-weight: bold;
            transition: all var(--transition-speed);
        }
        
        #sidebar:hover .logo-small {
            opacity: 0;
        }
        
        #sidebar .nav-text, 
        #sidebar .user-info, 
        #sidebar .btn-text {
            opacity: 0;
            transition: opacity var(--transition-speed);
            white-space: nowrap;
        }
        
        #sidebar:hover .nav-text, 
        #sidebar:hover .user-info, 
        #sidebar:hover .btn-text {
            opacity: 1;
        }
        
        #sidebar ul.components {
            padding: 20px 0;
            margin: 0;
        }
        
        #sidebar ul li {
            position: relative;
        }
        
        #sidebar ul li a {
            padding: 15px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }
        
        #sidebar ul li a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 0;
            background-color: #fff;
            transition: width var(--transition-speed) ease;
        }
        
        #sidebar ul li a:hover {
            color: #fff;
            background: var(--sidebar-hover);
        }
        
        #sidebar ul li a:hover::after {
            width: 100%;
        }
        
        #sidebar ul li a.active {
            color: #fff;
            background: var(--sidebar-active);
            border-right: 4px solid #fff;
        }
        
        #sidebar .icon-container {
            width: 30px;
            text-align: center;
            margin-right: 10px;
            flex-shrink: 0;
        }
        
        #content {
            width: calc(100% - var(--sidebar-collapsed-width));
            min-height: 100vh;
            transition: all var(--transition-speed);
            margin-left: var(--sidebar-collapsed-width);
            padding: 20px;
        }
        
        .user-profile {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
            overflow: hidden;
        }
        
        .logout-section {
            padding: 15px;
            position: absolute;
            bottom: 0;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            #sidebar {
                width: 0;
                overflow: hidden;
            }
            
            #sidebar.mobile-active {
                width: var(--sidebar-expanded-width);
            }
            
            #content {
                width: 100%;
                margin-left: 0;
            }
            
            #mobile-toggle {
                display: block !important;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1000;
                background-color: var(--sidebar-bg);
                color: white;
                border: none;
                border-radius: 4px;
                padding: 8px 12px;
                cursor: pointer;
            }
            
            .overlay {
                display: none;
                position: fixed;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 998;
                opacity: 0;
                transition: all var(--transition-speed);
            }
            
            .overlay.active {
                display: block;
                opacity: 1;
            }
        }
        
        #mobile-toggle {
            display: none;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Mobile Toggle Button -->
        <button id="mobile-toggle" class="d-none">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Overlay for mobile -->
        <div class="overlay"></div>
        
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header d-flex align-items-center">
                <div class="logo-small">N</div>
                <h4 class="mb-0">NOVM System</h4>
            </div>

            <?php if ($is_logged_in): ?>
            <div class="user-profile">
                <div class="d-flex align-items-center">
                    <div class="icon-container">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </div>
                    <div class="user-info">
                        <strong>Welcome,</strong>
                        <div><?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'User')); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <ul class="list-unstyled components">
                <li>
                    <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        <div class="icon-container">
                            <i class="fas fa-home"></i>
                        </div>
                        <span class="nav-text">Home</span>
                    </a>
                </li>
                <li>
                    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                        <div class="icon-container">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="establishments.php" class="<?php echo ($current_page == 'establishments.php') ? 'active' : ''; ?>">
                        <div class="icon-container">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <span class="nav-text">Notice Management</span>
                    </a>
                </li>
                <li>
                    <a href="nov_form.php" class="<?php echo ($current_page == 'nov_form.php') ? 'active' : ''; ?>">
                        <div class="icon-container">
                            <i class="fas fa-building"></i>
                        </div>
                        <span class="nav-text">Establishments</span>
                    </a>
                </li>
            </ul>
            <?php if ($is_logged_in): ?>
            <div class="logout-section">
                <button id="logoutBtn" class="btn btn-danger w-100 d-flex align-items-center">
                    <div class="icon-container">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="btn-text">Logout</span>
                </button>
            </div>
            <?php else: ?>
            <div class="logout-section">
                <a href="login.php" class="btn btn-primary w-100 d-flex align-items-center">
                    <div class="icon-container">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <span class="btn-text">Login</span>
                </a>
            </div>
            <?php endif; ?>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Main content goes here -->
            <div class="container-fluid">