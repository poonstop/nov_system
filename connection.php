<?php
// Set timezone for Manila (Philippines)
date_default_timezone_set('Asia/Manila');

// Database configuration
$db_host = "localhost";
$db_user = "root"; // Change this to your database username
$db_pass = ""; // Change this to your database password
$db_name = "nov_system7";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// You can also set the session timezone for MySQL if needed
$conn->query("SET time_zone = '+08:00'");
?>