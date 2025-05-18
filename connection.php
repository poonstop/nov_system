<?php
// Set timezone for Manila (Philippines)
date_default_timezone_set('Asia/Manila');

// Database configuration
$db_host = "localhost"; // Using standard "localhost" - common for XAMPP/WAMP setups
$db_user = "root";      // Default MySQL username for local development
$db_pass = "";          // Default blank password for local development
$db_name = "nov_system7";

// Create database connection with PDO
try {
    // Create PDO connection with correct DSN format
    $conn = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Set timezone for MySQL
    $conn->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    // Log the error for admin debugging
    error_log("Database connection failed: " . $e->getMessage() . " (Error code: " . $e->getCode() . ")");

    // Provide clearer error message if in development environment
    if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1')) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        // Generic message for production
        die("Database connection failed. Please contact the administrator.");
    }
}

/**
 * Log system actions
 * @param string|int $id User ID
 * @param string $action_type Type of action
 * @param string $description Action description
 * @return void
 */
function logAction($id, $action_type, $description) {
    global $conn;
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'System Process';
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    
    try {
        $stmt = $conn->prepare("INSERT INTO user_logs (user_id, action_type, ip_address, user_agent, description, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$id, $action_type, $ip_address, $user_agent, $description]);
    } catch (PDOException $e) {
        error_log("Failed to log action: " . $e->getMessage());
        // Don't stop execution if logging fails
    }
}
?>