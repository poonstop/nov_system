<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nov_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set database connection to use UTC
$conn->query("SET time_zone = '+00:00'");

// Set PHP to use Manila timezone (matches php.ini)
date_default_timezone_set('Asia/Manila');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>