<?php
// Replace these values with your database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nov_system";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate a hashed password
$newPassword = "admin123"; // Replace with your desired password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the password in the database
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashedPassword);

if ($stmt->execute()) {
    echo "Password updated successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
