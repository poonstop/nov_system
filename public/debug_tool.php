<?php
// Include the database connection
require_once '../connection.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Test database connection
if ($conn->ping()) {
    echo "<p style='color:green'>Database connection is working!</p>";
} else {
    echo "<p style='color:red'>Database connection failed: " . $conn->error . "</p>";
}

// Get database information
echo "<h2>Database Information</h2>";
echo "<p>Host: $db_host</p>";
echo "<p>Database: $db_name</p>";
echo "<p>User: $db_user</p>";
echo "<p>Character Set: " . $conn->character_set_name() . "</p>";

// List tables
echo "<h2>Tables in Database</h2>";
$tables_result = $conn->query("SHOW TABLES");

if ($tables_result->num_rows > 0) {
    echo "<ul>";
    while ($table = $tables_result->fetch_row()) {
        echo "<li>" . $table[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No tables found in database.</p>";
}

// Check user_logs table for logging
echo "<h2>User Logs Test</h2>";
$logs_table_exists = $conn->query("SHOW TABLES LIKE 'user_logs'")->num_rows > 0;

if ($logs_table_exists) {
    echo "<p style='color:green'>user_logs table exists.</p>";
    
    // Test log function
    try {
        logAction(1, 'debug_test', $_SERVER['HTTP_USER_AGENT'], 'Testing logging function');
        echo "<p style='color:green'>Log function executed successfully!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error using log function: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>user_logs table does not exist. Logging may not work.</p>";
}

// Test file permissions
echo "<h2>File Permissions</h2>";
$backup_dir = '../backups/';

if (file_exists($backup_dir)) {
    echo "<p>Backup directory exists.</p>";
    if (is_writable($backup_dir)) {
        echo "<p style='color:green'>Backup directory is writable.</p>";
    } else {
        echo "<p style='color:red'>Backup directory is not writable! Please fix permissions.</p>";
    }
} else {
    echo "<p style='color:red'>Backup directory does not exist!</p>";
    
    // Try to create it
    if (mkdir($backup_dir, 0755, true)) {
        echo "<p style='color:green'>Successfully created backup directory.</p>";
    } else {
        echo "<p style='color:red'>Failed to create backup directory. Check permissions.</p>";
    }
}

// Show PHP configuration
echo "<h2>PHP Configuration</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Max Upload Size: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>Max Post Size: " . ini_get('post_max_size') . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . " seconds</p>";
?>