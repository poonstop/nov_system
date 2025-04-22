<?php 
header('Content-Type: application/json'); 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create debug log
function debug_log($message) {
    $log_file = __DIR__ . '/update_debug.log';
    $time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$time] $message\n", FILE_APPEND);
}

debug_log("===== New Update Request =====");

$response = ['success' => false, 'message' => 'An unknown error occurred'];

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
    $requiredFields = ['id', 'name', 'address', 'owner_rep'];     
    foreach ($requiredFields as $field) {         
        if (!isset($data[$field]) || empty(trim($data[$field]))) {             
            throw new Exception("Missing required field: $field");         
        }     
    }     
    
    require_once __DIR__ . '/../connection.php';
    
    // Begin transaction
    $conn->begin_transaction();

    // Sanitize and validate data     
    $id = filter_var($data['id'], FILTER_VALIDATE_INT);     
    if ($id === false || $id <= 0) {         
        throw new Exception('Invalid establishment ID');     
    }      
    
    $name = trim($data['name']);     
    $address = trim($data['address']);     
    $ownerRep = trim($data['owner_rep']);     
    $violations = trim($data['violations'] ?? '');     
    $numViolations = filter_var($data['num_violations'] ?? 0, FILTER_VALIDATE_INT, [         
        'options' => ['min_range' => 0]     
    ]);     
    
    if ($numViolations === false) {         
        throw new Exception('Invalid number of violations');     
    }      
    
    // Handle notice information
    $noticeStatus = null;
    if (isset($data['notice_status']) && in_array($data['notice_status'], ['Received', 'Refused'])) {
        $noticeStatus = $data['notice_status'];
    }
    
    $issuedDateTime = null;
    if (!empty($data['issued_datetime'])) {
        try {
            $dateObj = new DateTime($data['issued_datetime']);
            $issuedDateTime = $dateObj->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            debug_log("Invalid issued_datetime format: " . $data['issued_datetime']);
        }
    }
    
    $issuedBy = isset($data['issued_by']) ? trim($data['issued_by']) : null;
    $position = isset($data['position']) ? trim($data['position']) : null;
    $witnessedBy = isset($data['witnessed_by']) ? trim($data['witnessed_by']) : null;

    // Update establishment record
    $stmt = $conn->prepare("
        UPDATE establishments 
        SET 
            name = ?,
            address = ?,
            owner_representative = ?,
            violations = ?,
            num_violations = ?,
            notice_status = ?,
            issued_datetime = ?,
            issued_by = ?,
            position = ?,
            witnessed_by = ?,
            date_updated = CURRENT_TIMESTAMP
        WHERE id = ?
    ");  
    
    if (!$stmt) {         
        throw new Exception('Database prepare error: ' . $conn->error);     
    }      
    
    $stmt->bind_param(
        "ssssisssssi",
        $name,
        $address,
        $ownerRep,
        $violations,
        $numViolations,
        $noticeStatus,
        $issuedDateTime,
        $issuedBy,
        $position,
        $witnessedBy,
        $id
    );      
    
    if (!$stmt->execute()) {         
        throw new Exception('Database execute error: ' . $stmt->error);     
    }
    $stmt->close();

    // Handle inventory data if provided
    if (isset($data['inventory']) && is_array($data['inventory'])) {
        // First delete existing inventory
        $deleteStmt = $conn->prepare("DELETE FROM establishment_inventory WHERE establishment_id = ?");
        $deleteStmt->bind_param("i", $id);
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to clear existing inventory: ' . $deleteStmt->error);
        }
        $deleteStmt->close();

        // Insert new inventory items
        $insertStmt = $conn->prepare("
            INSERT INTO establishment_inventory (
                establishment_id, product_name, sealed, withdrawn, description,
                price, pieces, dao_violation, other_violation, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($data['inventory'] as $item) {
            $insertStmt->bind_param(
                "isiiisdiis",
                $id,
                $item['name'] ?? '',
                isset($item['sealed']) ? 1 : 0,
                isset($item['withdrawn']) ? 1 : 0,
                $item['description'] ?? '',
                $item['price'] ?? 0,
                $item['pieces'] ?? 0,
                isset($item['dao_violation']) ? 1 : 0,
                isset($item['other_violation']) ? 1 : 0,
                $item['remarks'] ?? ''
            );
            if (!$insertStmt->execute()) {
                throw new Exception('Failed to insert inventory item: ' . $insertStmt->error);
            }
        }
        $insertStmt->close();
    }

    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => 'Record updated successfully',
        'updated_id' => $id,
        'num_violations' => $numViolations,
        'violations' => $violations
    ];

} catch(Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(500);     
    $response = [         
        'success' => false,         
        'message' => 'Error: ' . $e->getMessage()     
    ]; 
    debug_log("Error: " . $e->getMessage());
} finally {     
    // Clean up resources     
    if (isset($conn) && $conn) {         
        $conn->close();     
    }
          
    echo json_encode($response);     
    debug_log("Response: " . json_encode($response));
    exit(); 
}