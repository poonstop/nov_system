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
        if (!headers_sent()) {
            header("Location: login.php?timeout=1");
        }
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
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Tracking System'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
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
            overflow: visible;
        }
        
        #sidebar .sidebar-header {
            padding: 15px 10px;
            background: #343a40;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            display: flex;
            align-items: center;
            height: 85px;
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
            opacity: 0;
            width: 0;
            transition: all var(--transition-speed);
            white-space: normal;
            text-overflow: clip;
        }
        
        #sidebar:hover .sidebar-header .title-container {
            width: auto;
            opacity: 1;
            white-space: normal;
        }
        
        #sidebar .sidebar-title {
            font-size: 0.95rem;
            line-height: 1.2;
            text-align: left;
            margin: 0;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            word-break: break-word;
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
            margin-left: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            color: rgba(255, 255, 255, 0.9);
            text-transform: capitalize;
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: calc(var(--sidebar-expanded-width) - 70px);
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
            margin-right: 5px;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* This is the key change - content now transitions with the sidebar */
        #content {
            width: calc(100% - var(--sidebar-collapsed-width));
            min-height: 100vh;
            transition: all var(--transition-speed);
            margin-left: var(--sidebar-collapsed-width);
            padding: 20px;
        }
        
        /* When sidebar is hovered, adjust the content margin */
        #sidebar:hover ~ #content {
            margin-left: var(--sidebar-expanded-width);
            width: calc(100% - var(--sidebar-expanded-width));
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
            min-height: auto;
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
                transform: translateX(-100%);
            }
            
            #sidebar.mobile-active {
                width: var(--sidebar-expanded-width);
                transform: translateX(0);
            }
            
            #sidebar.mobile-active .sidebar-header .title-container,
            #sidebar.mobile-active .nav-text, 
            #sidebar.mobile-active .user-info, 
            #sidebar.mobile-active .btn-text {
                opacity: 1;
                width: auto;
            }
            
            #content {
                width: 100%;
                margin-left: 0;
                transition: all var(--transition-speed);
            }
            
            /* When mobile sidebar is active, push content */
            #sidebar.mobile-active ~ #content {
                margin-left: var(--sidebar-expanded-width);
                transform: translateX(0);
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
    </style>
</head>
<body>
    <script>
        / Session timeout handling
let timeoutWarning;
const sessionTimeout = <?php echo $inactive; ?> * 1000; // Convert to milliseconds
const warningTime = 60000; // 1 minute before timeout

function startSessionTimer() {
    // Clear any existing timers
    if (timeoutWarning) clearTimeout(timeoutWarning);
    
    // Set warning timer (1 minute before timeout)
    timeoutWarning = setTimeout(() => {
        Swal.fire({
            title: 'Session About to Expire',
            text: `Your session will expire in 1 minute. Would you like to continue?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Continue Session',
            cancelButtonText: 'Logout Now',
            timer: 60000, // Auto-close after 1 minute
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Extend session via AJAX
                fetch('extend_session.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            startSessionTimer(); // Reset the timer
                            Swal.fire('Session Extended', 'Your session has been extended.', 'success');
                        }
                    });
            } else if (result.dismiss === Swal.DismissReason.timer) {
                // Timer expired - session ended
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has ended due to inactivity.',
                    icon: 'info'
                }).then(() => {
                    window.location.href = 'login.php?timeout=1';
                });
            }
        });
    }, sessionTimeout - warningTime);
}

// Start the timer when page loads
startSessionTimer();

// Reset timer on user activity
['click', 'mousemove', 'keypress'].forEach(event => {
    document.addEventListener(event, startSessionTimer);
});
</script>

    <div id="wrapper">
        <!-- Mobile Toggle Button -->
        <button id="mobile-toggle" class="d-none">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Overlay for mobile -->
        <div class="overlay"></div>
        
        <?php include 'sidebar.php'; ?>
        
        <!-- Page Content -->
        <div id="content">