
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

// Enhanced PHP backup function with improved INSERT statement generation
function createPHPBackup($db_connection, $db_name, $backup_file) {
    try {
        // Set a higher time limit for large databases
        set_time_limit(1800); // 30 minutes
        ini_set('memory_limit', '1024M'); // Increase memory limit further
        
        // Open file handle at the start with write mode to create a fresh file
        $backup_file_handle = fopen($backup_file, 'wb'); // Write mode, binary safe
        if (!$backup_file_handle) {
            error_log("Failed to open backup file for writing: " . $backup_file);
            return false;
        }
        
        // Initialize backup content
        $header = "-- Database Backup for {$db_name}\n";
        $header .= "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";
        
        // Add SET statements for better compatibility
        $header .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $header .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $header .= "SET AUTOCOMMIT = 0;\n";
        $header .= "START TRANSACTION;\n"; // Explicit transaction start
        $header .= "SET time_zone = \"+00:00\";\n";
        $header .= "SET CHARACTER_SET_CLIENT=utf8mb4;\n";
        $header .= "SET CHARACTER_SET_RESULTS=utf8mb4;\n";
        $header .= "SET NAMES utf8mb4;\n\n";
        
        // Write the header to file
        fwrite($backup_file_handle, $header);
        
        // Get all table names
        $tables_result = $db_connection->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        if (!$tables_result) {
            error_log("Error fetching tables: " . $db_connection->error);
            fclose($backup_file_handle);
            return false;
        }
        
        $tables = [];
        while ($table_row = $tables_result->fetch_row()) {
            $tables[] = $table_row[0];
        }
        
        error_log("Found " . count($tables) . " tables to backup");
        
        // Process users table first to ensure it's complete
        $prioritized_tables = array_merge(
            array_filter($tables, function($table) { return strtolower($table) === 'users'; }),
            array_filter($tables, function($table) { return strtolower($table) !== 'users'; })
        );
        
        // First pass: Create DROP TABLE and CREATE TABLE statements
        foreach ($prioritized_tables as $table_name) {
            $structure_content = "-- Table structure for table `{$table_name}`\n";
            
            // Add DROP TABLE statement
            $structure_content .= "DROP TABLE IF EXISTS `{$table_name}`;\n";
            
            // Get table creation SQL
            $structure_result = $db_connection->query("SHOW CREATE TABLE `{$table_name}`");
            if (!$structure_result) {
                error_log("Error getting structure for table {$table_name}: " . $db_connection->error);
                continue;
            }
            
            $structure_row = $structure_result->fetch_row();
            // Use the CREATE TABLE statement exactly as returned by MySQL
            $structure_content .= $structure_row[1] . ";\n\n";
            
            // Write structure to file immediately
            fwrite($backup_file_handle, $structure_content);
            fflush($backup_file_handle); // Ensure it's written to disk
            
            // Free result set
            $structure_result->free();
        }
        
        // Second pass: Insert data table by table with better binary data handling
        foreach ($prioritized_tables as $table_name) {
            // Flag for users table special handling
            $isUsersTable = (strtolower($table_name) === 'users');
            
            // Get column names and types first
            $fields_result = $db_connection->query("SHOW COLUMNS FROM `{$table_name}`");
            if (!$fields_result) {
                error_log("Error getting columns for table {$table_name}: " . $db_connection->error);
                continue;
            }
            
            $fields = [];
            $field_types = [];
            $field_nulls = [];
            
            while ($field = $fields_result->fetch_assoc()) {
                $fields[] = $field['Field'];
                // Store field type information to handle binary/blob data properly
                $field_types[$field['Field']] = $field['Type'];
                // Store if field is nullable
                $field_nulls[$field['Field']] = ($field['Null'] === 'YES');
            }
            
            // Field names for INSERT statement
            $field_list = "`" . implode("`, `", $fields) . "`";
            
            // Get row count first
            $count_result = $db_connection->query("SELECT COUNT(*) FROM `{$table_name}`");
            if (!$count_result) {
                error_log("Error counting rows in {$table_name}: " . $db_connection->error);
                continue;
            }
            
            $count_row = $count_result->fetch_row();
            $row_count = (int)$count_row[0];
            $count_result->free();
            
            if ($row_count > 0) {
                $table_header = "-- Data for table `{$table_name}`\n";
                $table_header .= "-- {$row_count} rows\n";
                fwrite($backup_file_handle, $table_header);
                fflush($backup_file_handle);
                
                // Process in smaller batches to avoid memory issues
                $batch_size = 50; // Even smaller batch size for better memory handling
                
                // Determine appropriate ordering for consistent backups
                $orderBy = '';
                if (in_array('id', $fields)) {
                    $orderBy = " ORDER BY `id` ASC";
                } elseif (in_array('user_id', $fields)) {
                    $orderBy = " ORDER BY `user_id` ASC";
                } elseif ($primary_key = getPrimaryKey($db_connection, $table_name)) {
                    $orderBy = " ORDER BY `{$primary_key}` ASC";
                }
                
                // FIXED: Use a more reliable method to ensure we process ALL rows
                // Use a direct query with LIMIT and OFFSET for more reliable pagination
                for ($offset = 0; $offset < $row_count; $offset += $batch_size) {
                    // Get a batch of rows
                    $data_query = "SELECT * FROM `{$table_name}`{$orderBy} LIMIT {$offset}, {$batch_size}";
                    $data_result = $db_connection->query($data_query);
                    
                    if (!$data_result) {
                        error_log("Error fetching data from {$table_name}: " . $db_connection->error);
                        error_log("Failed query: " . $data_query);
                        break;
                    }
                    
                    $processed_rows = $data_result->num_rows;
                    if ($processed_rows == 0) {
                        // No more rows to process
                        error_log("No more rows found for {$table_name} at offset {$offset}");
                        break;
                    }
                    
                    // Process this batch's rows
                    $row_values = [];
                    
                    while ($row = $data_result->fetch_assoc()) {
                        $values = [];
                        
                        foreach ($fields as $field) {
                            if (is_null($row[$field])) {
                                $values[] = "NULL";
                            } else {
                                // Handle different field types
                                $field_type_lower = strtolower($field_types[$field]);
                                
                                // Binary data - use hex representation
                                if (strpos($field_type_lower, 'binary') !== false || 
                                    strpos($field_type_lower, 'blob') !== false || 
                                    strpos($field_type_lower, 'varbinary') !== false) {
                                    $values[] = "0x" . bin2hex($row[$field]);
                                } 
                                // Handle bit fields
                                else if (strpos($field_type_lower, 'bit') === 0) {
                                    // If it's a single bit, bin2hex should work well
                                    $bit_val = $row[$field];
                                    // Handle empty bit values
                                    if ($bit_val === '') {
                                        $values[] = "b'0'";
                                    } else {
                                        // Convert bit value to binary string representation
                                        $bit_str = '';
                                        for ($i = 0; $i < strlen($bit_val); $i++) {
                                            $bit_str .= str_pad(decbin(ord($bit_val[$i])), 8, '0', STR_PAD_LEFT);
                                        }
                                        // Trim leading zeros for bit fields
                                        $bit_str = ltrim($bit_str, '0');
                                        if ($bit_str === '') $bit_str = '0'; // Handle case of all zeros
                                        $values[] = "b'" . $bit_str . "'";
                                    }
                                }
                                // Handle numeric fields without quotes
                                else if (strpos($field_type_lower, 'int') !== false ||
                                         strpos($field_type_lower, 'float') !== false ||
                                         strpos($field_type_lower, 'double') !== false ||
                                         strpos($field_type_lower, 'decimal') !== false) {
                                    $values[] = $row[$field];
                                }
                                // Handle date/time fields with correct quoting
                                else if (strpos($field_type_lower, 'date') !== false ||
                                         strpos($field_type_lower, 'time') !== false) {
                                    // Ensure valid date/time format or NULL for invalid dates
                                    if ($row[$field] == '0000-00-00' || $row[$field] == '0000-00-00 00:00:00') {
                                        // Use NULL for invalid dates when possible
                                        if ($field_nulls[$field]) {
                                            $values[] = "NULL";
                                        } else {
                                            $values[] = "'0000-00-00 00:00:00'";
                                        }
                                    } else {
                                        $values[] = "'" . $db_connection->real_escape_string($row[$field]) . "'";
                                    }
                                }
                                // All other types - escape properly
                                else {
                                    $values[] = "'" . $db_connection->real_escape_string($row[$field]) . "'";
                                }
                            }
                        }
                        
                        $row_values[] = "(" . implode(", ", $values) . ")";
                    }
                    
                    // Create and write complete INSERT statement for this batch
                    if (!empty($row_values)) {
                        // For users table, use INSERT IGNORE to prevent errors on duplicate keys during restore
                        $insert_prefix = $isUsersTable 
                            ? "INSERT IGNORE INTO `{$table_name}` ({$field_list}) VALUES\n" 
                            : "INSERT INTO `{$table_name}` ({$field_list}) VALUES\n";
                        
                        fwrite($backup_file_handle, $insert_prefix);
                        fwrite($backup_file_handle, implode(",\n", $row_values) . ";\n\n");
                        fflush($backup_file_handle); // Force flush after each batch
                        
                        // VERIFICATION: Log the number of rows processed in this batch
                        error_log("Wrote {$processed_rows} rows for table {$table_name} at offset {$offset}");
                        
                        // Clear row values array to free memory
                        $row_values = [];
                    } else {
                        error_log("WARNING: No values generated for table {$table_name} at offset {$offset}");
                    }
                    
                    // Free result set to conserve memory
                    $data_result->free();
                    
                    // Log progress for large tables
                    if ($row_count > 1000 && ($offset + $processed_rows) % 1000 < $batch_size) {
                        error_log("Backup progress for {$table_name}: " . ($offset + $processed_rows) . "/{$row_count} rows processed");
                    }
                }
                
                // Log completion for each table
                error_log("Completed backup of table {$table_name}: {$row_count} rows should be processed");
            }
            
            // Free field result to conserve memory
            $fields_result->free();
        }
        
        // Add views
        $views_result = $db_connection->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
        if ($views_result && $views_result->num_rows > 0) {
            fwrite($backup_file_handle, "\n-- Views\n\n");
            
            while ($view_row = $views_result->fetch_row()) {
                $view_name = $view_row[0];
                
                // Add DROP VIEW statement
                fwrite($backup_file_handle, "DROP VIEW IF EXISTS `{$view_name}`;\n");
                
                // Get view creation SQL
                $view_result = $db_connection->query("SHOW CREATE VIEW `{$view_name}`");
                if ($view_result) {
                    $view_row = $view_result->fetch_row();
                    fwrite($backup_file_handle, $view_row[1] . ";\n\n");
                    $view_result->free();
                }
            }
            $views_result->free();
        }
        
        // Add proper DELIMITER commands for triggers, procedures and functions
        fwrite($backup_file_handle, "\n-- Set proper delimiter for routines\n");
        fwrite($backup_file_handle, "DELIMITER $$\n\n");
        fflush($backup_file_handle);
        
        // Add triggers
        $triggers_result = $db_connection->query("SHOW TRIGGERS");
        if ($triggers_result && $triggers_result->num_rows > 0) {
            fwrite($backup_file_handle, "\n-- Triggers\n\n");
            
            while ($trigger_row = $triggers_result->fetch_assoc()) {
                $trigger_name = $trigger_row['Trigger'];
                
                // Add DROP TRIGGER statement
                fwrite($backup_file_handle, "DROP TRIGGER IF EXISTS `{$trigger_name}`$$\n");
                
                // Get trigger creation SQL
                $trigger_result = $db_connection->query("SHOW CREATE TRIGGER `{$trigger_name}`");
                if ($trigger_result) {
                    $trigger_row = $trigger_result->fetch_row();
                    // Ensure correct termination with our delimiter
                    $trigger_sql = rtrim($trigger_row[2], ';') . "$$\n\n";
                    fwrite($backup_file_handle, $trigger_sql);
                    $trigger_result->free();
                }
            }
            $triggers_result->free();
        }
        
        // Add procedures
        $routines_result = $db_connection->query("SHOW PROCEDURE STATUS WHERE Db = '{$db_name}'");
        if ($routines_result && $routines_result->num_rows > 0) {
            fwrite($backup_file_handle, "\n-- Procedures\n\n");
            
            while ($routine_row = $routines_result->fetch_assoc()) {
                $routine_name = $routine_row['Name'];
                
                // Add DROP PROCEDURE statement
                fwrite($backup_file_handle, "DROP PROCEDURE IF EXISTS `{$routine_name}`$$\n");
                
                // Get procedure creation SQL
                $procedure_result = $db_connection->query("SHOW CREATE PROCEDURE `{$routine_name}`");
                if ($procedure_result) {
                    $procedure_row = $procedure_result->fetch_row();
                    // Ensure correct termination with our delimiter
                    $procedure_sql = rtrim($procedure_row[2], ';') . "$$\n\n";
                    fwrite($backup_file_handle, $procedure_sql);
                    $procedure_result->free();
                }
            }
            $routines_result->free();
        }
        
        // Add functions
        $functions_result = $db_connection->query("SHOW FUNCTION STATUS WHERE Db = '{$db_name}'");
        if ($functions_result && $functions_result->num_rows > 0) {
            fwrite($backup_file_handle, "\n-- Functions\n\n");
            
            while ($function_row = $functions_result->fetch_assoc()) {
                $function_name = $function_row['Name'];
                
                // Add DROP FUNCTION statement
                fwrite($backup_file_handle, "DROP FUNCTION IF EXISTS `{$function_name}`$$\n");
                
                // Get function creation SQL
                $function_result = $db_connection->query("SHOW CREATE FUNCTION `{$function_name}`");
                if ($function_result) {
                    $function_row = $function_result->fetch_row();
                    // Ensure correct termination with our delimiter
                    $function_sql = rtrim($function_row[2], ';') . "$$\n\n";
                    fwrite($backup_file_handle, $function_sql);
                    $function_result->free();
                }
            }
            $functions_result->free();
        }
        
        // Reset delimiter
        fwrite($backup_file_handle, "\n-- Reset delimiter\n");
        fwrite($backup_file_handle, "DELIMITER ;\n\n");
        
        // Add re-enable foreign key checks and commit transaction at the end
        fwrite($backup_file_handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($backup_file_handle, "COMMIT;\n");
        fwrite($backup_file_handle, "\n-- End of backup\n");
        fflush($backup_file_handle);
        
        // Close file handle
        fclose($backup_file_handle);
        
        // Verify the backup file was written correctly
        clearstatcache();
        if (file_exists($backup_file) && filesize($backup_file) > 0) {
            error_log("Backup completed successfully: " . $backup_file . " (Size: " . filesize($backup_file) . " bytes)");
            return true;
        } else {
            error_log("Backup file verification failed. File may be empty or not created properly.");
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception during backup: " . $e->getMessage() . " at line " . $e->getLine() . " in " . $e->getFile());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Helper function to get primary key column name for a table
 * 
 * @param mysqli $db_connection Database connection
 * @param string $table_name Table to check for primary key
 * @return string|null Primary key column name or null if not found
 */
function getPrimaryKey($db_connection, $table_name) {
    $result = $db_connection->query("SHOW KEYS FROM `{$table_name}` WHERE Key_name = 'PRIMARY'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row['Column_name'];
    }
    return null;
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