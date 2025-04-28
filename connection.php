<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nov_system7";

// Create connection using mysqli (if you still need this)
$conn = new mysqli($servername, $username, $password, $dbname);

// Set database connection to use UTC
$conn->query("SET time_zone = '+00:00'");

// Set PHP to use Manila timezone (matches php.ini)
date_default_timezone_set('Asia/Manila');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Create PDO instance - using $servername instead of $host
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Set default fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>