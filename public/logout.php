<?php
// Start the session first before attempting any session operations
session_start();

// Include database connection from external file (more secure)
include __DIR__ . '/../connection.php';

// Check if it's an AJAX logout request
if (isset($_POST['action']) && $_POST['action'] == 'logout') {
    // Handle AJAX logout
    
    // Log the logout event if user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
        $fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'unknown';
        
        // Log the logout event with detailed information
        $action = "Logout";
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $details = "User {$username} ({$fullname}) logged out via AJAX";
        $current_time = date("Y-m-d H:i:s");
        
        try {
            $log_stmt = $conn->prepare("INSERT INTO user_logs (user_id, action, user_agent, details, timestamp) VALUES (?, ?, ?, ?, ?)");
            $log_stmt->execute([$user_id, $action, $user_agent, $details, $current_time]);
        } catch (Exception $e) {
            // Log error but continue with logout
            error_log("Error logging logout: " . $e->getMessage());
        }
    }
    
    // Thorough session cleanup - first unset all session variables
    $_SESSION = array();

    // Delete the session cookie if cookies are used
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
    
    // Close database connection if needed
    $conn = null;
    
    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'You have been successfully logged out.']);
    exit;
}

// For regular (non-AJAX) requests, continue with standard logout process

// Check if it's a timeout scenario
$timeout = isset($_GET['timeout']) ? true : false;

// Log the logout event if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
    $fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'unknown';
    
    // Log the logout event with detailed information
    $action = "Logout";
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $details = $timeout ? 
        "User {$username} ({$fullname}) session timed out" : 
        "User {$username} ({$fullname}) logged out";
    $current_time = date("Y-m-d H:i:s");
    
    try {
        $log_stmt = $conn->prepare("INSERT INTO user_logs (user_id, action, user_agent, details, timestamp) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->execute([$user_id, $action, $user_agent, $details, $current_time]);
    } catch (Exception $e) {
        // Log error but continue with logout
        error_log("Error logging logout: " . $e->getMessage());
    }
    
    // Set logout message (will be used before redirecting)
    if ($timeout) {
        $_SESSION['logout_message'] = "Your session has timed out due to inactivity.";
    } else {
        $_SESSION['logout_message'] = "You have been successfully logged out.";
    }
}

// Store the logout message temporarily
$logout_message = isset($_SESSION['logout_message']) ? $_SESSION['logout_message'] : null;

// Thorough session cleanup - first unset all session variables
$_SESSION = array();

// Delete the session cookie if cookies are used
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Close database connection
$conn = null;

// Check if it's an AJAX request (additional check beyond the action parameter)
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // For AJAX requests, return success as JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => $logout_message]);
    exit();
}

// Redirect to login page with appropriate parameters
if ($timeout) {
    header("Location: login.php?timeout=1");
} else if ($logout_message) {
    // Pass logout message as URL parameter (properly encoded)
    header("Location: login.php?message=" . urlencode($logout_message));
} else {
    header("Location: login.php");
}
exit();
?>