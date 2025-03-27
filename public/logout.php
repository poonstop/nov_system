<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nov_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session
session_start();

// Log logout action (optional, but good for audit trails)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $logout_time = date('Y-m-d H:i:s');
    
    $log_sql = "INSERT INTO user_logs (user_id, action, timestamp) VALUES (?, 'logout', ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("is", $user_id, $logout_time);
    $log_stmt->execute();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Close database connection
$conn->close();

// Check if it's an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // For AJAX requests, just return success
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit();
}

// Redirect to login page
header("Location: login.php");
exit();
?>