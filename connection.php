<?php
// Set timezone for Manila (Philippines)
date_default_timezone_set('Asia/Manila');

// Database configuration
$db_host = "127.0.0.1"; // Changed from "localhost" to IP address
$db_port = 3306; // Explicitly defining the MySQL port
$db_user = "root"; // Change this to your database username
$db_pass = ""; // Change this to your database password
$db_name = "nov_system7";

// Create database connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set character set
    $conn->set_charset("utf8mb4");

    // You can also set the session timezone for MySQL if needed
    $conn->query("SET time_zone = '+08:00'");
    
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage() . " (Error code: " . $e->getCode() . ")");
}

/**
 * Log system actions
 * @param int $user_id User ID
 * @param string $action_type Type of action
 * @param string $user_agent User agent
 * @param string $description Action description
 * @return void
 */
function logAction($user_id, $action_type, $user_agent, $description) {
    global $conn;
    
    $user_id = (int)$user_id;
    $action_type = $conn->real_escape_string($action_type);
    $user_agent = $conn->real_escape_string($user_agent);
    $description = $conn->real_escape_string($description);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO user_logs (user_id, action_type, user_agent, ip_address, description, created_at) 
            VALUES ($user_id, '$action_type', '$user_agent', '$ip_address', '$description', NOW())";
    
    $conn->query($sql);
}
?>