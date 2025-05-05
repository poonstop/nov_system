
<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if ((!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) && !isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}

// Check if the user is admin, otherwise redirect to home
if (!isset($_SESSION["user_level"]) || $_SESSION["user_level"] !== "admin") {
    $_SESSION['error_message'] = "You don't have permission to access this page. Admin access required.";
    header("location: index.php");
    exit;
}

// Define the absolute path to connection.php
// Since connection.php is in the root directory and backup_restore.php is in public/
$connection_path = dirname(__DIR__) . '/connection.php';

// Include connection file
if (file_exists($connection_path)) {
    require_once $connection_path;
} else {
    die("Database connection file not found: " . $connection_path);
}

// Ensure we have a connection variable
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Connection variable not found"));
}

// Use $conn as the primary database connection
$link = $conn;

// Extract database details from connection
$db_server = $db_host ?? 'localhost';
$db_username = $db_user ?? 'root';
$db_password = $db_pass ?? '';
$db_name = $db_name ?? 'nov_system7';

// Create backup directory with proper path
$backup_dir = __DIR__ . "/backups";
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Function to get the current user ID from session
function getCurrentUserId() {
    if (isset($_SESSION["user_id"])) {
        return $_SESSION["user_id"];
    } else if (isset($_SESSION["id"])) {
        return $_SESSION["id"];
    }
    return 0;
}

// Function to log user actions
function logUserAction($user_id, $action, $details) {
    global $link;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $query = "INSERT INTO user_logs (user_id, action, user_agent, details, timestamp) 
              VALUES (?, ?, ?, ?, NOW())";
    
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $user_agent, $details);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Improved pure PHP backup function
function createPHPBackup($db_connection, $db_name, $backup_file) {
    try {
        $backup_content = "-- Database Backup for {$db_name}\n";
        $backup_content .= "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";
        
        // Add SET statements for better compatibility
        $backup_content .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $backup_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $backup_content .= "SET time_zone = \"+00:00\";\n\n";
        
        // Get all table names
        $tables_result = $db_connection->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        while ($table_row = $tables_result->fetch_row()) {
            $tables[] = $table_row[0];
        }
        
        // First, get all create table statements
        foreach ($tables as $table_name) {
            // Add DROP TABLE statement
            $backup_content .= "DROP TABLE IF EXISTS `{$table_name}`;\n";
            
            // Get table creation SQL
            $structure_result = $db_connection->query("SHOW CREATE TABLE `{$table_name}`");
            $structure_row = $structure_result->fetch_row();
            $backup_content .= $structure_row[1] . ";\n\n";
        }
        
        // Then get all data
        foreach ($tables as $table_name) {
            // Get table data
            $data_result = $db_connection->query("SELECT * FROM `{$table_name}`");
            $row_count = $data_result->num_rows;
            
            if ($row_count > 0) {
                $backup_content .= "-- Data for table `{$table_name}`\n";
                $backup_content .= "-- {$row_count} rows\n";
                
                // Start transaction for faster inserts
                $backup_content .= "START TRANSACTION;\n";
                
                // Use multi-row inserts (batches of 100 rows) for better performance
                $rows = [];
                $counter = 0;
                $fields = [];
                
                // Get column names
                $fields_result = $db_connection->query("SHOW COLUMNS FROM `{$table_name}`");
                while ($field = $fields_result->fetch_assoc()) {
                    $fields[] = $field['Field'];
                }
                
                // Field names for INSERT statement
                $field_list = "`" . implode("`, `", $fields) . "`";
                
                while ($data_row = $data_result->fetch_assoc()) {
                    $values = [];
                    foreach ($fields as $field) {
                        if ($data_row[$field] === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . $db_connection->real_escape_string($data_row[$field]) . "'";
                        }
                    }
                    
                    $rows[] = "(" . implode(", ", $values) . ")";
                    $counter++;
                    
                    // Write in batches of 100 rows
                    if ($counter % 100 === 0 || $counter === $row_count) {
                        $backup_content .= "INSERT INTO `{$table_name}` ({$field_list}) VALUES\n";
                        $backup_content .= implode(",\n", $rows) . ";\n";
                        $rows = [];
                    }
                }
                
                if (!empty($rows)) {
                    $backup_content .= "INSERT INTO `{$table_name}` ({$field_list}) VALUES\n";
                    $backup_content .= implode(",\n", $rows) . ";\n";
                }
                
                $backup_content .= "COMMIT;\n\n";
            }
        }
        
        // Get and add views
        $views_result = $db_connection->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
        while ($view_row = $views_result->fetch_row()) {
            $view_name = $view_row[0];
            
            // Add DROP VIEW statement
            $backup_content .= "DROP VIEW IF EXISTS `{$view_name}`;\n";
            
            // Get view creation SQL
            $view_result = $db_connection->query("SHOW CREATE VIEW `{$view_name}`");
            $view_row = $view_result->fetch_row();
            $backup_content .= $view_row[1] . ";\n\n";
        }
        
        // Get and add triggers
        $triggers_result = $db_connection->query("SHOW TRIGGERS");
        if ($triggers_result->num_rows > 0) {
            $backup_content .= "DELIMITER //\n";
            while ($trigger_row = $triggers_result->fetch_assoc()) {
                $trigger_name = $trigger_row['Trigger'];
                
                // Add DROP TRIGGER statement
                $backup_content .= "DROP TRIGGER IF EXISTS `{$trigger_name}`//\n";
                
                // Get trigger creation SQL
                $trigger_result = $db_connection->query("SHOW CREATE TRIGGER `{$trigger_name}`");
                $trigger_row = $trigger_result->fetch_row();
                $backup_content .= $trigger_row[2] . "//\n\n";
            }
            $backup_content .= "DELIMITER ;\n\n";
        }
        
        // Get and add procedures/functions
        $routines_result = $db_connection->query("SHOW PROCEDURE STATUS WHERE Db = '{$db_name}'");
        if ($routines_result->num_rows > 0) {
            $backup_content .= "DELIMITER //\n";
            while ($routine_row = $routines_result->fetch_assoc()) {
                $routine_name = $routine_row['Name'];
                
                // Add DROP PROCEDURE statement
                $backup_content .= "DROP PROCEDURE IF EXISTS `{$routine_name}`//\n";
                
                // Get procedure creation SQL
                $procedure_result = $db_connection->query("SHOW CREATE PROCEDURE `{$routine_name}`");
                $procedure_row = $procedure_result->fetch_row();
                $backup_content .= $procedure_row[2] . "//\n\n";
            }
            $backup_content .= "DELIMITER ;\n\n";
        }
        
        // Get and add functions
        $functions_result = $db_connection->query("SHOW FUNCTION STATUS WHERE Db = '{$db_name}'");
        if ($functions_result->num_rows > 0) {
            $backup_content .= "DELIMITER //\n";
            while ($function_row = $functions_result->fetch_assoc()) {
                $function_name = $function_row['Name'];
                
                // Add DROP FUNCTION statement
                $backup_content .= "DROP FUNCTION IF EXISTS `{$function_name}`//\n";
                
                // Get function creation SQL
                $function_result = $db_connection->query("SHOW CREATE FUNCTION `{$function_name}`");
                $function_row = $function_result->fetch_row();
                $backup_content .= $function_row[2] . "//\n\n";
            }
            $backup_content .= "DELIMITER ;\n\n";
        }
        
        // Add re-enable foreign key checks
        $backup_content .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Write to file
        return file_put_contents($backup_file, $backup_content) !== false;
    } catch (Exception $e) {
        return false;
    }
}

// Enhanced error logging function
function logDatabaseError($message, $context = []) {
    // First log to PHP error log
    error_log($message);
    
    // Then log to a custom file if possible
    $log_dir = __DIR__ . "/logs";
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $log_file = $log_dir . "/db_errors.log";
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}";
    
    // Add context details if provided
    if (!empty($context)) {
        $log_entry .= " | Context: " . json_encode($context);
    }
    
    // Append to log file
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND);
}

function validateRestore($db_connection, $backup_file) {
    // Extract table names from the backup file
    $sql_content = file_get_contents($backup_file);
    preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`|CREATE TABLE `([^`]+)`/', $sql_content, $matches);
    $expected_tables = array_filter(array_merge($matches[1], $matches[2]));
    
    $results = [
        'success' => false,
        'tables_found' => 0,
        'expected_tables' => count($expected_tables),
        'missing_tables' => [],
        'message' => ''
    ];
    
    // Check if connection is still valid
    if (!$db_connection->ping()) {
        $results['message'] = "Database connection lost during validation";
        error_log("Restore validation failed: Database connection lost");
        return $results;
    }
    
    // Count all tables
    $tables_result = $db_connection->query("SHOW TABLES");
    $restored_tables = [];
    
    if ($tables_result) {
        while ($row = $tables_result->fetch_row()) {
            $restored_tables[] = $row[0];
        }
        $results['tables_found'] = count($restored_tables);
        
        // Log all found tables for debugging
        error_log("Tables found after restore: " . implode(", ", $restored_tables));
    } else {
        $results['message'] = "Failed to query tables: " . $db_connection->error;
        error_log("Restore validation failed: " . $results['message']);
        return $results;
    }
    
    // Find missing tables
    $results['missing_tables'] = array_diff($expected_tables, $restored_tables);
    
    // Check if we have all expected tables
    if (empty($results['missing_tables'])) {
        $results['success'] = true;
        $results['message'] = "Found all {$results['expected_tables']} expected tables after restoration";
    } else {
        $results['message'] = "Missing tables after restoration: " . implode(", ", $results['missing_tables']);
        error_log("Restore validation failed: " . $results['message']);
    }
    
    return $results;
}

// Enhanced PHP restore function with better error handling and verification
function restorePHPBackup($db_connection, $backup_file) {
    try {
        // Check if backup file exists and has content
        if (!file_exists($backup_file)) {
            error_log("Backup file not found: " . $backup_file);
            return ["success" => false, "message" => "Backup file not found"];
        }
        
        $filesize = filesize($backup_file);
        if ($filesize == 0) {
            error_log("Backup file is empty: " . $backup_file);
            return ["success" => false, "message" => "Backup file is empty"];
        }
        
        error_log("Starting restore of backup file: " . $backup_file . " (Size: " . $filesize . " bytes)");
        
        // Read file
        $sql_content = file_get_contents($backup_file);
        if ($sql_content === false) {
            error_log("Failed to read backup file: " . $backup_file);
            return ["success" => false, "message" => "Failed to read backup file"];
        }
        
        // Extract table names from the backup file
        preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`|CREATE TABLE `([^`]+)`/', $sql_content, $matches);
        $expected_tables = array_filter(array_merge($matches[1], $matches[2]));
        error_log("Expected tables in backup: " . implode(", ", $expected_tables));
        
        // Make backup of current database first
        $temp_backup_dir = dirname($backup_file) . "/temp";
        if (!file_exists($temp_backup_dir)) {
            mkdir($temp_backup_dir, 0777, true);
        }
        $temp_backup_file = $temp_backup_dir . "/pre_restore_backup_" . date("Y-m-d_H-i-s") . ".sql";
        
        // Try to create a backup of the current state (for safety)
        $backup_created = false;
        if (function_exists('createPHPBackup')) {
            $backup_created = createPHPBackup($db_connection, $GLOBALS['db_name'], $temp_backup_file);
            if ($backup_created) {
                error_log("Created safety backup before restore: " . $temp_backup_file);
            }
        }
        
        // Verify database connection
        if (!$db_connection->ping()) {
            error_log("Database connection lost. Attempting to reconnect...");
            $db_connection->close();
            $db_connection = new mysqli($GLOBALS['db_server'], $GLOBALS['db_username'], $GLOBALS['db_password'], $GLOBALS['db_name']);
            if ($db_connection->connect_error) {
                error_log("Failed to reconnect to database: " . $db_connection->connect_error);
                return ["success" => false, "message" => "Database connection failed during restore process"];
            }
            error_log("Successfully reconnected to database");
        }
        
        // Reset database - drop all existing tables first
        error_log("Dropping all existing tables before restore");
        $tables_result = $db_connection->query("SHOW TABLES");
        
        if ($tables_result) {
            // Disable foreign key checks before dropping tables
            $db_connection->query("SET FOREIGN_KEY_CHECKS=0");
            
            while ($row = $tables_result->fetch_row()) {
                $table_name = $row[0];
                error_log("Attempting to drop table: " . $table_name);
                
                // Use a more robust DROP method
                $drop_result = $db_connection->query("DROP TABLE IF EXISTS `$table_name`");
                
                if (!$drop_result) {
                    error_log("Error dropping table $table_name: " . $db_connection->error);
                } else {
                    error_log("Successfully dropped table: " . $table_name);
                }
            }
        } else {
            error_log("Failed to get existing tables: " . $db_connection->error);
        }
        
        // Ensure foreign key checks are disabled for import
        $db_connection->query("SET FOREIGN_KEY_CHECKS=0");
        $db_connection->query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
        
        // Split SQL file into individual statements
        $sql_statements = [];
        $current_statement = '';
        $delimiter = ';';
        $in_string = false;
        $string_delimiter = '';
        $in_comment = false;
        
        // Split SQL file into statements more carefully
        $chars = str_split($sql_content);
        $escaped = false;
        
        for ($i = 0; $i < count($chars); $i++) {
            $char = $chars[$i];
            $next_char = ($i < count($chars) - 1) ? $chars[$i + 1] : '';
            
            // Handle string literals
            if (!$in_comment && ($char === "'" || $char === '"') && !$escaped) {
                if (!$in_string) {
                    $in_string = true;
                    $string_delimiter = $char;
                } elseif ($char === $string_delimiter) {
                    $in_string = false;
                }
            }
            
            // Handle comments
            if (!$in_string && !$in_comment && $char === '/' && $next_char === '*') {
                $in_comment = true;
                $current_statement .= $char . $next_char;
                $i++; // Skip next char
                continue;
            } elseif ($in_comment && $char === '*' && $next_char === '/') {
                $in_comment = false;
                $current_statement .= $char . $next_char;
                $i++; // Skip next char
                continue;
            }
            
            // Handle line comments
            if (!$in_string && !$in_comment && $char === '-' && $next_char === '-') {
                // Skip until end of line
                while ($i < count($chars) && $chars[$i] !== "\n") {
                    $i++;
                }
                continue;
            }
            
            // Check for DELIMITER statements
            if (!$in_string && !$in_comment && 
                strtoupper(substr($current_statement . $char, -10)) === 'DELIMITER ' && 
                trim($current_statement) === '') {
                // Extract new delimiter
                $delim_start = $i + 1;
                while ($delim_start < count($chars) && 
                      ($chars[$delim_start] === ' ' || $chars[$delim_start] === "\t")) {
                    $delim_start++;
                }
                
                $delim_end = $delim_start;
                while ($delim_end < count($chars) && 
                      !in_array($chars[$delim_end], [' ', "\t", "\n", "\r"])) {
                    $delim_end++;
                }
                
                if ($delim_end > $delim_start) {
                    $delimiter = implode('', array_slice($chars, $delim_start, $delim_end - $delim_start));
                    $i = $delim_end;
                    $current_statement = '';
                    continue;
                }
            }
            
            // Check if we reached the end of a statement
            if (!$in_string && !$in_comment && 
                substr($current_statement . $char, -strlen($delimiter)) === $delimiter) {
                // Remove delimiter from statement
                $sql_statements[] = substr($current_statement . $char, 0, -strlen($delimiter));
                $current_statement = '';
                continue;
            }
            
            // Track escape character
            $escaped = ($char === '\\' && !$escaped);
            
            // Add current character to statement
            $current_statement .= $char;
        }
        
        // Add any remaining statement
        if (trim($current_statement) !== '') {
            $sql_statements[] = $current_statement;
        }
        
        // Track errors for reporting
        $errors = [];
        $success_count = 0;
        $total_queries = count($sql_statements);
        
        error_log("Parsed " . $total_queries . " SQL statements from backup file");
        
        // Execute each statement individually
        foreach ($sql_statements as $index => $statement) {
            $trimmed_statement = trim($statement);
            if (empty($trimmed_statement)) {
                continue;
            }
            
            // Log progress
            if ($index % 100 === 0) {
                error_log("Restore progress: " . $index . "/" . $total_queries . " statements processed");
            }
            
            // Execute the statement
            try {
                $result = $db_connection->query($trimmed_statement);
                if ($result === false) {
                    $error_info = "Error at statement #" . ($index + 1) . ": " . $db_connection->error;
                    error_log($error_info);
                    $errors[] = $error_info;
                } else {
                    $success_count++;
                    if ($result !== true) {
                        $result->free();
                    }
                }
            } catch (Exception $e) {
                $error_info = "Exception at statement #" . ($index + 1) . ": " . $e->getMessage();
                error_log($error_info);
                $errors[] = $error_info;
            }
        }
        
        // Re-enable foreign key checks
        $db_connection->query("SET FOREIGN_KEY_CHECKS=1");
        
        // Verify all expected tables were restored
        $validate_result = validateRestore($db_connection, $backup_file);
        
        // Generate result report
        $result = [
            "success" => $validate_result['success'],
            "total_queries" => $total_queries,
            "success_count" => $success_count,
            "error_count" => count($errors),
            "success_percentage" => ($total_queries > 0) ? round(($success_count / $total_queries) * 100, 2) : 0,
            "tables_found" => $validate_result['tables_found'],
            "expected_tables" => $validate_result['expected_tables'],
            "missing_tables" => $validate_result['missing_tables'],
            "message" => $validate_result['message']
        ];
        
        // Log summary
        error_log("Restore completed: " . 
                 ($result['success'] ? "SUCCESS" : "FAILED") . " - " .
                 "Success rate: " . $result['success_percentage'] . "% (" . 
                 $result['success_count'] . "/" . $result['total_queries'] . " statements) - " .
                 "Tables: " . $result['tables_found'] . "/" . $result['expected_tables']);
        
        if (count($errors) > 0) {
            // Include first few errors in the result
            $result['errors'] = array_slice($errors, 0, 5);
            error_log("First error: " . $errors[0]);
        }
        
        return $result;
    } catch (Exception $e) {
        $error_message = "Critical restore error: " . $e->getMessage();
        error_log($error_message);
        return ["success" => false, "message" => $error_message];
    }
}

// Add a new function to validate MySQL command restore results
function validateMySQLRestore($db_connection, $backup_file) {
    // Extract expected tables from backup file
    $sql_content = file_get_contents($backup_file);
    preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`|CREATE TABLE `([^`]+)`/', $sql_content, $matches);
    $expected_tables = array_filter(array_merge($matches[1], $matches[2]));
    
    // Check if tables exist in database
    $tables_result = $db_connection->query("SHOW TABLES");
    if (!$tables_result) {
        return [
            'success' => false,
            'message' => "Failed to query tables after restore: " . $db_connection->error
        ];
    }
    
    $restored_tables = [];
    while ($row = $tables_result->fetch_row()) {
        $restored_tables[] = $row[0];
    }
    
    $missing_tables = array_diff($expected_tables, $restored_tables);
    
    if (empty($missing_tables)) {
        return [
            'success' => true,
            'message' => "Database restored successfully using mysql command from: " . basename($backup_file) . 
                        " (" . count($restored_tables) . "/" . count($expected_tables) . " expected tables restored)"
        ];
    } else {
        return [
            'success' => false,
            'message' => "MySQL command completed but validation failed: Missing tables: " . implode(", ", $missing_tables)
        ];
    }
}

function performDatabaseRestore($link, $backup_file, $db_server, $db_username, $db_password, $db_name) {
    global $backup_dir;
    
    error_log("Starting database restore from file: " . basename($backup_file));
    
    if (!file_exists($backup_file)) {
        error_log("Backup file not found: " . $backup_file);
        return [
            'success' => false,
            'message' => "Backup file not found: " . basename($backup_file)
        ];
    }
    
    // Extract table names from the backup file to verify after restore
    $sql_content = file_get_contents($backup_file);
    preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`|CREATE TABLE `([^`]+)`/', $sql_content, $matches);
    $expected_tables = array_filter(array_merge($matches[1], $matches[2]));
    error_log("Expected tables in backup: " . implode(", ", $expected_tables));
    
    // First try using mysql command with detailed error reporting
    $mysql_failed = false;
    $mysql_output = "";
    
    // Check if the mysql command-line utility is available
    $check_mysql = shell_exec("which mysql 2>&1");
    if (empty($check_mysql)) {
        error_log("MySQL command-line client not found. Falling back to PHP method.");
        $mysql_failed = true;
    } else {
        // Construct mysql command with proper escaping
        if (empty($db_password)) {
            $command = "mysql --host=" . escapeshellarg($db_server) . " --user=" . escapeshellarg($db_username) . " " . escapeshellarg($db_name) . " < " . escapeshellarg($backup_file) . " 2>&1";
        } else {
            // Create a temporary file with the password
            $temp_file = tempnam(sys_get_temp_dir(), 'mysqlpwd');
            file_put_contents($temp_file, "[client]\npassword=" . $db_password);
            chmod($temp_file, 0600); // Secure the password file
            $command = "mysql --defaults-extra-file=" . escapeshellarg($temp_file) . " --host=" . escapeshellarg($db_server) . " --user=" . escapeshellarg($db_username) . " " . escapeshellarg($db_name) . " < " . escapeshellarg($backup_file) . " 2>&1";
        }
        
        // Execute the command
        error_log("Attempting MySQL command restore");
        exec($command, $output, $return_var);
        $mysql_output = implode("\n", $output);
        
        // Remove temp file if created
        if (isset($temp_file) && file_exists($temp_file)) {
            unlink($temp_file);
        }
        
        // Check if mysql was successful
        if ($return_var !== 0) {
            $mysql_failed = true;
            error_log("MySQL restore failed with exit code: " . $return_var);
            error_log("MySQL restore output: " . $mysql_output);
        } else {
            error_log("MySQL command restore executed successfully");
        }
    }
    
    // If mysql command failed or not available, use the enhanced PHP restore method
    if ($mysql_failed) {
        // Verify database connection before attempting restore
        if (!$link->ping()) {
            error_log("Database connection lost before PHP restore attempt. Trying to reconnect...");
            // Try to reconnect
            $link->close();
            $link = new mysqli($db_server, $db_username, $db_password, $db_name);
            if ($link->connect_error) {
                error_log("Failed to reconnect to database: " . $link->connect_error);
                return [
                    'success' => false,
                    'message' => "Database connection failed. Unable to restore backup."
                ];
            }
            error_log("Reconnected to database successfully");
        }
        
        error_log("Starting PHP-based restore method for: " . basename($backup_file));
        $restore_result = restorePHPBackup($link, $backup_file);
        
        if ($restore_result['success']) {
            return [
                'success' => true,
                'message' => "Database restored successfully using PHP method from: " . basename($backup_file) . 
                            " (" . $restore_result['tables_found'] . "/" . $restore_result['expected_tables'] . " tables restored, " . 
                            $restore_result['success_percentage'] . "% queries succeeded)"
            ];
        } else {
            $error_detail = "";
            if (isset($restore_result['errors']) && !empty($restore_result['errors'])) {
                $error_detail = " Error: " . $restore_result['errors'][0];
            }
            
            if (!empty($restore_result['missing_tables'])) {
                $error_detail .= " Missing tables: " . implode(", ", array_slice($restore_result['missing_tables'], 0, 5)) . 
                    (count($restore_result['missing_tables']) > 5 ? " (and " . (count($restore_result['missing_tables']) - 5) . " more)" : "");
            }
            
            return [
                'success' => false,
                'message' => "Failed to restore database: " . $restore_result['message'] . $error_detail
            ];
        }
    } else {
        // MySQL command worked, validate the results
        $tables_result = $link->query("SHOW TABLES");
        if (!$tables_result) {
            error_log("Failed to query tables after restore: " . $link->error);
            return [
                'success' => false,
                'message' => "MySQL command completed but failed to verify tables: " . $link->error
            ];
        }
        
        $restored_tables = [];
        while ($row = $tables_result->fetch_row()) {
            $restored_tables[] = $row[0];
        }
        
        $missing_tables = array_diff($expected_tables, $restored_tables);
        
        if (empty($missing_tables)) {
            return [
                'success' => true,
                'message' => "Database restored successfully using mysql command from: " . basename($backup_file) . 
                            " (" . count($restored_tables) . "/" . count($expected_tables) . " expected tables restored)"
            ];
        } else {
            return [
                'success' => false,
                'message' => "MySQL command completed but validation failed: Missing tables: " . implode(", ", $missing_tables)
            ];
        }
    }

}
// Messages to display to the user
$success_message = "";
$error_message = "";

// Handle backup creation
if (isset($_POST["create_backup"])) {
    $timestamp = date("Y-m-d_H-i-s");
    $backup_file = $backup_dir . "/backup_" . $timestamp . ".sql";
    
    // First try using mysqldump command if available
    $mysqldump_failed = false;
    
    // Construct mysqldump command with proper escaping
    if (empty($db_password)) {
        $command = "mysqldump --host=" . escapeshellarg($db_server) . " --user=" . escapeshellarg($db_username) . " " . escapeshellarg($db_name) . " > " . escapeshellarg($backup_file) . " 2>&1";
    } else {
        // Create a temporary file with the password
        $temp_file = tempnam(sys_get_temp_dir(), 'mysqlpwd');
        file_put_contents($temp_file, "[client]\npassword=" . $db_password);
        $command = "mysqldump --defaults-extra-file=" . escapeshellarg($temp_file) . " --host=" . escapeshellarg($db_server) . " --user=" . escapeshellarg($db_username) . " " . escapeshellarg($db_name) . " > " . escapeshellarg($backup_file) . " 2>&1";
    }
    
    // Execute the command
    exec($command, $output, $return_var);
    
    // Remove temp file if created
    if (isset($temp_file) && file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    // Check if mysqldump was successful
    if ($return_var !== 0 || !file_exists($backup_file) || filesize($backup_file) == 0) {
        $mysqldump_failed = true;
    }
    
    // If mysqldump failed, use the PHP backup method
    if ($mysqldump_failed) {
        if (createPHPBackup($link, $db_name, $backup_file)) {
            $success_message = "Backup created successfully using PHP method: " . basename($backup_file);
            logUserAction(getCurrentUserId(), "BACKUP_CREATED", "Created database backup (PHP method): " . basename($backup_file));
        } else {
            $error_message = "Failed to create backup using both methods. Please check permissions and database connection.";
        }
    } else {
        $success_message = "Backup created successfully using mysqldump: " . basename($backup_file);
        logUserAction(getCurrentUserId(), "BACKUP_CREATED", "Created database backup (mysqldump): " . basename($backup_file));
    }
}

// Handle backup restore
if (isset($_POST["restore_backup"]) && isset($_POST["backup_file"])) {
    $backup_file = $backup_dir . "/" . basename($_POST["backup_file"]);
    
    // Use the new enhanced restore function
    $restore_result = performDatabaseRestore($link, $backup_file, $db_server, $db_username, $db_password, $db_name);
    
    if ($restore_result['success']) {
        $success_message = $restore_result['message'];
        logUserAction(getCurrentUserId(), "BACKUP_RESTORED", "Restored database: " . basename($backup_file));
    } else {
        $error_message = $restore_result['message'];
        logUserAction(getCurrentUserId(), "BACKUP_RESTORE_FAILED", "Failed to restore: " . basename($backup_file));
    }
}

// Handle backup deletion
if (isset($_POST["delete_backup"]) && isset($_POST["backup_file"])) {
    $backup_file = $backup_dir . "/" . basename($_POST["backup_file"]);
    
    if (file_exists($backup_file)) {
        if (unlink($backup_file)) {
            $success_message = "Backup file deleted: " . basename($backup_file);
            logUserAction(getCurrentUserId(), "BACKUP_DELETED", "Deleted backup file: " . basename($backup_file));
        } else {
            $error_message = "Error deleting backup file. Please check file permissions.";
            error_log("Failed to delete backup file: " . $backup_file . ". Error: " . error_get_last()['message']);
        }
    } else {
        $error_message = "Backup file not found: " . basename($backup_file);
        error_log("Backup file not found for deletion: " . $backup_file);
    }
}

// Handle backup download
if (isset($_GET["download"]) && !empty($_GET["download"])) {
    $backup_file = $backup_dir . "/" . basename($_GET["download"]);
    
    if (file_exists($backup_file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($backup_file));
        header('Expires:
 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backup_file));
        readfile($backup_file);
        
        logUserAction(getCurrentUserId(), "BACKUP_DOWNLOADED", "Downloaded backup file: " . basename($backup_file));
        exit;
    } else {
        $error_message = "Backup file not found: " . basename($_GET["download"]);
    }
}

// Handle backup upload
if (isset($_POST["upload_backup"]) && isset($_FILES["backup_file"])) {
    $allowed_ext = ['sql'];
    $file_ext = strtolower(pathinfo($_FILES["backup_file"]["name"], PATHINFO_EXTENSION));
    
    if (in_array($file_ext, $allowed_ext)) {
        $timestamp = date("Y-m-d_H-i-s");
        $upload_file = $backup_dir . "/uploaded_" . $timestamp . ".sql";
        
        if (move_uploaded_file($_FILES["backup_file"]["tmp_name"], $upload_file)) {
            $success_message = "Backup file uploaded successfully: " . basename($upload_file);
            logUserAction(getCurrentUserId(), "BACKUP_UPLOADED", "Uploaded backup file: " . basename($upload_file));
        } else {
            $error_message = "Error uploading backup file. Please check file permissions.";
        }
    } else {
        $error_message = "Only SQL files are allowed.";
    }
}

// Get list of backup files
$backup_files = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file != "." && $file != ".." && pathinfo($file, PATHINFO_EXTENSION) == "sql") {
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($backup_dir . "/" . $file),
                'date' => date("Y-m-d H:i:s", filemtime($backup_dir . "/" . $file))
            ];
        }
    }
    
    // Sort backup files by date (newest first)
    usort($backup_files, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Page title
$page_title = "Backup & Restore";
// Include header template
include '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTI METRONC - <?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/styles.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">

    <style>
        .backup-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .backup-actions {
            display: flex;
            gap: 5px;
        }
        .backup-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .backup-size {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .section-card {
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            border-radius: 8px 8px 0 0 !important;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation Bar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="mobile-toggle" class="btn btn-info d-lg-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h3 class="ms-2"><?php echo $page_title; ?></h3>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-align-justify"></i>
                    </button>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Create Backup Section -->
                    <div class="col-md-6">
                        <div class="card section-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-download"></i> Create Database Backup</h5>
                            </div>
                            <div class="card-body">
                                <p>Create a full backup of the database. This will include all tables, data, and structure.</p>
                                <form method="post" action="">
                                    <button type="submit" name="create_backup" class="btn btn-primary">
                                        <i class="fas fa-database"></i> Create Backup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Backup Section -->
                    <div class="col-md-6">
                        <div class="card section-card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-upload"></i> Upload Backup File</h5>
                            </div>
                            <div class="card-body">
                                <p>Upload a previously created backup file (.sql format).</p>
                                <form method="post" action="" enctype="multipart/form-data">
                                    <div class="input-group mb-3">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="backupFile" name="backup_file" required>
                                            <label class="custom-file-label" for="backupFile">Choose file</label>
                                        </div>
                                    </div>
                                    <button type="submit" name="upload_backup" class="btn btn-success">
                                        <i class="fas fa-upload"></i> Upload Backup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Files Section -->
                <div class="card section-card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Available Backup Files</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($backup_files)): ?>
                            <div class="alert alert-warning">
                                No backup files found. Create a backup first.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Date Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backup_files as $file): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($file['name']); ?></td>
                                                <td><?php echo formatFileSize($file['size']); ?></td>
                                                <td><?php echo $file['date']; ?></td>
                                                <td>
                                                    <div class="backup-actions">
                                                        <!-- Download Button -->
                                                        <a href="?download=<?php echo urlencode($file['name']); ?>" class="btn btn-sm btn-primary" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        
                                                        <!-- Restore Button -->
                                                        <form method="post" action="" class="d-inline">
                                                            <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($file['name']); ?>">
                                                            <button type="button" class="btn btn-sm btn-warning restore-btn" title="Restore" 
                                                                    data-filename="<?php echo htmlspecialchars($file['name']); ?>">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Delete Button -->
                                                        <form method="post" action="" class="d-inline delete-form">
                                                            <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($file['name']); ?>">
                                                            <button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete" 
                                                                    data-filename="<?php echo htmlspecialchars($file['name']); ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
       $(document).ready(function() {
    // File input label update
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    
    // Confirm restore action with direct form submission
    $(".restore-btn").on("click", function() {
        var fileName = $(this).data('filename');
        
        Swal.fire({
            title: 'Confirm Restore',
            text: "This will replace ALL current data with data from '" + fileName + "'. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit a form programmatically
                var form = $('<form>', {
                    'method': 'post',
                    'action': ''
                });
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'backup_file',
                    'value': fileName
                }));
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'restore_backup',
                    'value': '1'
                }));
                
                $('body').append(form);
                form.submit();
            }
        });
    });
    
    // Delete confirmation
    $(".delete-btn").on("click", function() {
        var fileName = $(this).data('filename');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to recover this backup file!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit a form programmatically
                var form = $('<form>', {
                    'method': 'post',
                    'action': ''
                });
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'backup_file',
                    'value': fileName
                }));
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'delete_backup',
                    'value': '1'
                }));
                
                $('body').append(form);
                form.submit();
            }
        });
    });
});

    </script>
</body>
</html>
<?php include '../templates/footer.php'; ?>