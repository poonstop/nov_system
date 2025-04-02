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
    <title>Tracking System for Monitoring and Enforcement Non – Compliance</title>
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
        width: var(--sidebar-collapsed-width); /* Collapsed width */
        background-color: var(--sidebar-bg);
        color: #fff;
        transition: all var(--transition-speed) ease;
        position: fixed;
        height: 100vh;
        z-index: 999;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        overflow: hidden; /* Prevent overflow when collapsed */
        }
        
        #sidebar:hover {
        width: var(--sidebar-expanded-width); /* Expanded width */
        overflow: visible; /* Show content on hover */
        
        }
        
        #sidebar .sidebar-header {
            padding: 15px 10px;
            background: #212529;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            display: flex;
            align-items: center;
            height: 85px; /* Fixed height for header */
        }
        
        #sidebar .sidebar-logo {
            height: 35px;
            width: 35px;
            min-width: 35px;
            object-fit: contain;
            margin-right: 10px;
            transition: all var(--transition-speed);
            flex-shrink: 0;
            display: block;
        }
        
        #sidebar .sidebar-header .title-container {
        opacity: 0; /* Hide text initially */
        width: 0; /* Collapse text container */
        transition: all var(--transition-speed);
        white-space: normal; /* Allow text to wrap */
        text-overflow: clip; /* Don't truncate text */
        }
        
        #sidebar:hover .sidebar-header .title-container {
        width: auto;
        opacity: 1;
        white-space: normal; /* Ensure text wraps when visible */
        }
        
        #sidebar .sidebar-title {
        font-size: 0.95rem;
        line-height: 1.2;
        text-align: left;
        margin: 0;
        white-space: normal; /* Allow text to wrap */
        overflow: visible; /* Show all text */
        text-overflow: clip; /* Don't truncate text */
        word-break: break-word; /* Break long words if needed */
        }
        
        #sidebar .logo-small {
            position: absolute;
            top: 15px;
            left: 50px;
            font-size: 1.5rem;
            font-weight: bold;
            transition: all var(--transition-speed);
        }
        
        #sidebar:hover .logo-small {
            opacity: 0;
        }
        #sidebar .nav-text {
        opacity: 0;
        transition: opacity var(--transition-speed);
        white-space: nowrap;
        margin-left: 10px; /* Add spacing between icon and text */
        font-size: 0.9rem; /* Slightly smaller font size */
        font-weight: 500; /* Medium weight for better readability */
        letter-spacing: 0.3px; /* Slight letter spacing */
        color: rgba(255, 255, 255, 0.9); /* Brighter text color */
        text-transform: capitalize; /* Capitalize first letters */
        flex-grow: 1; /* Allow text to take available space */
        overflow: hidden;
        text-overflow: ellipsis; /* Show ellipsis if text is too long */
        max-width: calc(var(--sidebar-expanded-width) - 70px); /* Limit width */
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
        margin-right: 5px; /* Reduced margin */
        flex-shrink: 0;
        display: flex;
        justify-content: center;
        align-items: center;
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
            transition: all var(--transition-speed);
        }
        .user-profile .d-flex {
            min-height: auto; /* Ensure consistent height */
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
            
            #sidebar.mobile-active .sidebar-header .title-container {
                width: auto;
                opacity: 1;
            }
            
            #sidebar.mobile-active .nav-text, 
            #sidebar.mobile-active .user-info, 
            #sidebar.mobile-active .btn-text {
                opacity: 1;
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
        
        /* Small screen adjustments */
        @media (min-width: 769px) and (max-width: 992px) {
            #sidebar .sidebar-title {
                font-size: 0.85rem;
            }
        }
        
        /* Large screens */
        @media (min-width: 993px) {
            #sidebar:hover .sidebar-header {
                padding: 15px 10px;
            }
            
            #sidebar:hover .sidebar-title {
                font-size: 0.95rem;
            }
        }
        
        #mobile-toggle {
            display: none;
        }

        /* Add smooth animation for mobile sidebar */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-expanded-width);
                transition: transform var(--transition-speed) ease;
            }
            
            #sidebar.mobile-active {
                transform: translateX(0);
            }
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
            <div class="sidebar-header">
                <img src="../images/dti-logo1.png" alt="DTI Logo" class="sidebar-logo">
                <div class="title-container">
                    <h5 class="sidebar-title mb-0">Tracking System for Monitoring and Enforcement System Non - Compliance</h5>
                </div>
            </div>


            <?php if ($is_logged_in): ?>
            <div class="user-profile">
                <div class="d-flex align-items-center">
                    <div class="icon-container">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </div>
                    <div class="user-info">
                        <strong>Welcome,</strong>
                        <?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'User')); ?>
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
                        <span class="nav-text">Establishments Management</span>
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
            
            <!-- JavaScript for Mobile Toggle -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Mobile sidebar toggle
                    const mobileToggle = document.getElementById('mobile-toggle');
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.querySelector('.overlay');
                    
                    if (mobileToggle) {
                        mobileToggle.addEventListener('click', function() {
                            sidebar.classList.toggle('mobile-active');
                            overlay.classList.toggle('active');
                        });
                    }
                    
                    if (overlay) {
                        overlay.addEventListener('click', function() {
                            sidebar.classList.remove('mobile-active');
                            overlay.classList.remove('active');
                        });
                    }
                    
                    // Make mobile toggle visible
                    mobileToggle.classList.remove('d-none');
                    
                    // Logout button functionality
                    const logoutBtn = document.getElementById('logoutBtn');
                    if (logoutBtn) {
                        logoutBtn.addEventListener('click', function() {
                            Swal.fire({
                                title: 'Logout',
                                text: 'Are you sure you want to logout?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, logout'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'logout.php';
                                }
                            });
                        });
                    }
                });
            </script>