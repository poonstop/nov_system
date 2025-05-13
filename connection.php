<?php
// Set timezone for Manila (Philippines)
date_default_timezone_set('Asia/Manila');

// Database configuration
$db_host = "127.0.0.1"; // Changed from "localhost" to IP address
$db_port = 3306;        // Explicitly defining the MySQL port
$db_user = "root";      // Change this to your database username
$db_pass = "";          // Change this to your database password
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
 * @param string|int $user_id User ID (not the database connection)
 * @param string $action_type Type of action
 * @param string $description Action description
 * @return void
 */
function logAction($user_id, $action_type, $description) {
    global $conn;
    
    // Check if user_id is numeric
    if (is_numeric($user_id)) {
        $user_id = (int)$user_id;
    } else {
        // For cases when user_id is a string (like 'System')
        $user_id = $conn->real_escape_string($user_id);
        // Use quotes for string user_id in the SQL query below
    }
    
    $action_type = $conn->real_escape_string($action_type);
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? 
                  $conn->real_escape_string($_SERVER['HTTP_USER_AGENT']) : 
                  'System Process';
    $description = $conn->real_escape_string($description);
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    
    // Adjust SQL based on user_id type
    if (is_int($user_id)) {
        $sql = "INSERT INTO user_logs (user_id, action, user_agent, details, timestamp)
                VALUES ($user_id, '$action_type', '$user_agent', '$description', NOW())";
    } else {
        $sql = "INSERT INTO user_logs (user_id, action, user_agent, details, timestamp)
                VALUES ('$user_id', '$action_type', '$user_agent', '$description', NOW())";
    }
    
    $conn->query($sql);
}
?>