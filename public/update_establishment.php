<?php 
header('Content-Type: application/json'); 
error_reporting(E_ALL); // Enable error reporting for debugging
ini_set('display_errors', 1);

// Create debug log
function debug_log($message) {
    $log_file = __DIR__ . '/update_debug.log';
    $time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$time] $message\n", FILE_APPEND);
}

debug_log("===== New Update Request =====");

$response = ['success' => false, 'message' => 'An unknown error occurred']; // Default response  

try {     
    // Get and validate JSON input     
    $json = file_get_contents('php://input');     
    if (empty($json)) {         
        throw new Exception('No data received');     
    }      
    
    $data = json_decode($json, true);     
    if (json_last_error() !== JSON_ERROR_NONE) {         
        throw new Exception('Invalid JSON: ' . json_last_error_msg());     
    }      
    
    // Validate required fields     
    $requiredFields = ['id', 'name', 'address', 'owner_rep', 'violations', 'num_violations'];     
    foreach ($requiredFields as $field) {         
        if (!isset($data[$field])) {             
            throw new Exception("Missing required field: $field");         
        }     
    }     
    
    require_once __DIR__ . '/../connection.php';      
    
    // Sanitize and validate data     
    $id = filter_var($data['id'], FILTER_VALIDATE_INT);     
    if ($id === false || $id <= 0) {         
        throw new Exception('Invalid establishment ID');     
    }      
    
    $name = trim($data['name']);     
    if (empty($name)) {         
        throw new Exception('Establishment name cannot be empty');     
    }      
    
    $address = trim($data['address']);     
    $ownerRep = trim($data['owner_rep']);     
    $violations = trim($data['violations']);     
    $numViolations = filter_var($data['num_violations'], FILTER_VALIDATE_INT, [         
        'options' => ['min_range' => 0]     
    ]);     
    
    if ($numViolations === false) {         
        throw new Exception('Invalid number of violations');     
    }      
    
    // Use date_updated from input if provided, otherwise use current time
    if (isset($data['date_updated']) && !empty($data['date_updated'])) {
        try {
            // Validate the date format
            $dateObj = new DateTime($data['date_updated']);
            $dateUpdatedStr = $dateObj->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // If date parsing fails, fall back to current time
            date_default_timezone_set('Asia/Manila');
            $dateUpdated = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $dateUpdatedStr = $dateUpdated->format('Y-m-d H:i:s');
        }
    } else {
        // Default to current Manila time
        date_default_timezone_set('Asia/Manila');
        $dateUpdated = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $dateUpdatedStr = $dateUpdated->format('Y-m-d H:i:s');
    }

    // Handle inventory data
    $productsJson = '';
    if (isset($data['inventory']) && is_array($data['inventory'])) {
        $productsJson = json_encode($data['inventory']);
        debug_log("Inventory data: " . print_r($data['inventory'], true));
    }

    // Log the data for debugging
    debug_log("Updating establishment ID: $id");
    debug_log("Violations: $violations");
    debug_log("Number of violations: $numViolations");
    debug_log("Date updated: $dateUpdatedStr");
    
    // Prepare and execute 
    $stmt = $conn->prepare("
        UPDATE establishments 
        SET 
            name = ?,
            address = ?,
            owner_representative = ?,
            violations = ?,
            num_violations = ?,
            products = ?,
            date_updated = ?
        WHERE id = ?
    ");  
    
    if (!$stmt) {         
        throw new Exception('Database prepare error: ' . $conn->error);     
    }      
    
    // Bind parameters
    $stmt->bind_param(
        "ssssissi", // String, String, String, String, Integer, String, String, Integer
        $name,
        $address,
        $ownerRep,
        $violations,
        $numViolations,
        $productsJson,
        $dateUpdatedStr,
        $id
    );      
    
    if (!$stmt->execute()) {         
        throw new Exception('Database execute error: ' . $stmt->error);     
    }      
    
    // Check if any rows were affected     
    if ($stmt->affected_rows === 0) {         
        // This is not necessarily an error - could be no changes were made
        $response = [
            'success' => true,
            'message' => 'No changes detected or ID may not exist',
            'updated_id' => $id,
            'date_updated' => $dateUpdatedStr,
            'num_violations' => $numViolations
        ];
    } else {
        // Success response with updated timestamp
        $dateUpdated = new DateTime($dateUpdatedStr);     
        $response = [         
            'success' => true,         
            'message' => 'Record updated successfully',         
            'updated_id' => $id,         
            'date_updated' => $dateUpdatedStr,         
            'formatted_date' => $dateUpdated->format('F j, Y, g:i a'), // Human-readable format
            'num_violations' => $numViolations,
            'violations' => $violations
        ];
    }
} catch(Exception $e) {     
    http_response_code(500);     
    $response = [         
        'success' => false,         
        'message' => 'Error: ' . $e->getMessage()     
    ]; 
    debug_log("Error: " . $e->getMessage());
} finally {     
    // Clean up resources     
    if (isset($stmt) && $stmt) {         
        $stmt->close();     
    }     
    if (isset($conn) && $conn) {         
        $conn->close();     
    }
          
    // End output buffering and send the response     
    ob_end_clean();     
    echo json_encode($response);     
    debug_log("Response: " . json_encode($response));
    exit(); 
} 
?>