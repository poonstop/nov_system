<?php
// Include the database connection
require_once '../connection.php';

// Set timezone for Manila (Philippines)
date_default_timezone_set('Asia/Manila');

// Initialize messages
$success_message = '';
$error_message = '';

// Process backup request
if (isset($_POST['create_backup']) || isset($_GET['create_backup'])) {
    try {
        // Create a unique filename with timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        
        // Get all tables in the database
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        // Set headers for direct download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Start output buffering
        ob_start();
        
        // Add header information
        echo "-- Database Backup for {$db_name}\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        echo "-- PHP Version: " . phpversion() . "\n\n";
        
        echo "SET FOREIGN_KEY_CHECKS=0;\n";
        echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        echo "SET AUTOCOMMIT = 0;\n";
        echo "START TRANSACTION;\n";
        echo "SET time_zone = \"+08:00\";\n\n";
        
        // Process each table
        foreach ($tables as $table) {
            // Get create table statement
            $stmt = $conn->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            
            echo "\n\n-- Structure for table `$table`\n\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            echo $row[1] . ";\n\n";
            
            // Get data
            $result = $conn->query("SELECT * FROM `$table`");
            $num_fields = $result->columnCount();
            
            if ($result->rowCount() > 0) {
                echo "-- Dumping data for table `$table`\n";
                
                // Get column names
                $columns = [];
                for ($i = 0; $i < $num_fields; $i++) {
                    $column = $result->getColumnMeta($i);
                    $columns[] = "`" . $column['name'] . "`";
                }
                
                // Insert statements
                $fields = implode(', ', $columns);
                
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $values = array();
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . str_replace("'", "\'", $value) . "'";
                        }
                    }
                    echo "INSERT INTO `$table` ($fields) VALUES (" . implode(', ', $values) . ");\n";
                }
            }
        }
        
        echo "\nCOMMIT;\n";
        echo "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Output the buffer and end script
        echo ob_get_clean();
        exit;
        
    } catch (Exception $e) {
        die("Backup error: " . $e->getMessage());
    }
}

// Process restore request
if (isset($_POST['restore_backup']) && isset($_FILES['backup_file'])) {
    try {
        $file = $_FILES['backup_file'];
        
        // Check for errors
        if ($file['error'] > 0) {
            throw new Exception("File upload error: " . $file['error']);
        }
        
        // Check file type (must be .sql)
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'sql') {
            throw new Exception("Only SQL files are allowed.");
        }
        
        // Read the file content
        $sql_content = file_get_contents($file['tmp_name']);
        if (!$sql_content) {
            throw new Exception("Failed to read the backup file.");
        }
        
        // First, disable foreign key checks to prevent constraint errors
        $conn->exec("SET FOREIGN_KEY_CHECKS=0;");
        
        // Split the SQL content into individual queries
        $sql_queries = splitSqlFile($sql_content);
        
        // Variables to track restore statistics
        $tables_processed = 0;
        $queries_executed = 0;
        $error_queries = [];
        
        // Execute each query individually for better error handling
        foreach ($sql_queries as $query) {
            $query = trim($query);
            if (empty($query)) continue;
            
            try {
                $result = $conn->exec($query);
                if ($result !== false) {
                    $queries_executed++;
                    
                    // Count CREATE TABLE operations to track table processing
                    if (preg_match('/CREATE TABLE/i', $query)) {
                        $tables_processed++;
                    }
                } else {
                    // Log the erroneous query and continue with the next one
                    $error_queries[] = [
                        'query' => substr($query, 0, 200) . '...',
                        'error' => $conn->errorInfo()[2]
                    ];
                }
            } catch (PDOException $query_error) {
                // Log the erroneous query and continue with the next one
                $error_queries[] = [
                    'query' => substr($query, 0, 200) . '...',
                    'error' => $query_error->getMessage()
                ];
            }
        }
        
        // Re-enable foreign key checks
        $conn->exec("SET FOREIGN_KEY_CHECKS=1;");
        
        // Build appropriate success message
        if (count($error_queries) > 0) {
            $success_message = "Database restored with {$tables_processed} tables. {$queries_executed} queries executed successfully, but " . count($error_queries) . " errors occurred.";
            // Append detailed error information for debugging
            $error_detail = "Error details:<br>";
            foreach ($error_queries as $index => $error) {
                if ($index > 5) {
                    $error_detail .= "... and " . (count($error_queries) - 5) . " more errors";
                    break;
                }
                $error_detail .= "- " . htmlspecialchars($error['error']) . "<br>";
            }
            $error_message = $error_detail;
        } else {
            $success_message = "Database restored successfully! Processed {$tables_processed} tables with {$queries_executed} queries.";
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

/**
 * Helper function to split SQL file into individual queries
 * This is more reliable than using multi_query for large files
 */
function splitSqlFile($sql) {
    // Remove BOM if present (can cause issues with parsing)
    $bom = pack('H*','EFBBBF');
    $sql = preg_replace("/^$bom/", '', $sql);
    
    $sql = trim($sql);
    
    // Remove MySQL comments
    $sql = preg_replace("/\n\#[^\n]*/", "\n", $sql);
    $sql = preg_replace("/\n--[^\n]*/", "\n", $sql);
    
    $queries = [];
    $currentQuery = '';
    $inString = false;
    $stringChar = '';
    
    // Process character by character for better accuracy
    $length = strlen($sql);
    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $nextChar = ($i < $length - 1) ? $sql[$i + 1] : '';
        
        // Handle strings (ignore semicolons inside strings)
        if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i-1] !== '\\')) {
            if (!$inString) {
                $inString = true;
                $stringChar = $char;
            } elseif ($char === $stringChar) {
                $inString = false;
            }
        }
        
        // Add character to current query
        $currentQuery .= $char;
        
        // If semicolon outside string, end of query
        if ($char === ';' && !$inString) {
            $queries[] = $currentQuery;
            $currentQuery = '';
        }
    }
    
    // Add any remaining query
    if (trim($currentQuery) !== '') {
        $queries[] = $currentQuery;
    }
    
    return $queries;
}

// Function to verify all tables exist in the database
function verifyAllTablesExist($conn) {
    // Expected tables based on the database schema
    $expected_tables = [
        'addresses',
        'establishments',
        'inventory',
        'notice_images',
        'notice_issuers',
        'notice_records',
        'notice_status',
        'penalties',
        'users',
        'user_logs'
    ];
    
    // Get all tables currently in the database
    $existing_tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $existing_tables[] = $row[0];
    }
    
    // Check for missing tables
    $missing_tables = array_diff($expected_tables, $existing_tables);
    
    return [
        'all_exist' => empty($missing_tables),
        'missing' => $missing_tables,
        'existing' => $existing_tables
    ];
}

include '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup & Restore</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .section-divider {
            border-top: 1px solid #dee2e6;
            margin: 30px 0;
            padding-top: 20px;
        }
        
        .card {
            margin-bottom: 20px;
        }
        
        .card-header {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php
                // Verify all tables exist
                $table_check = verifyAllTablesExist($conn);
                if (!$table_check['all_exist']) {
                    echo '<div class="alert alert-warning">
                        <strong>Warning:</strong> Some expected tables are missing from the database. Missing tables: ' . 
                        implode(', ', $table_check['missing']) . 
                        '</div>';
                }
                ?>
                
                <!-- Backup Section -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Database Backup
                    </div>
                    <div class="card-body">
                        <p>Click the button below to create and download a backup of your database.</p>
                        <p><small>Tables to be backed up: <?php echo implode(', ', $table_check['existing']); ?></small></p>
                        <form method="post">
                            <button type="submit" name="create_backup" class="btn btn-primary">
                                Download Database Backup
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Restore Section -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        Database Restore
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> Restoring a database will overwrite all existing data. Make sure you have a backup of your current data before proceeding.
                        </div>
                        
                        <p>Upload a previously created backup file (.sql format) to restore the database.</p>
                        
                        <form method="post" action="" enctype="multipart/form-data" id="restoreForm">
                            <div class="form-group">
                                <label for="backupFile">Select SQL Backup File</label>
                                <input type="file" class="form-control-file" id="backupFile" name="backup_file" required accept=".sql">
                                <small class="form-text text-muted">Only .sql files are supported</small>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="restore_backup" class="btn btn-warning" id="restoreBtn">
                                    Restore Database
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Confirm restore action
            $("#restoreForm").on("submit", function(e) {
                if (!confirm("WARNING: This will replace ALL current data with data from the backup file. This action cannot be undone! Are you sure you want to proceed?")) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        });
    </script>
</body>
</html>
<?php include '../templates/footer.php'; ?>