<?php
header('Content-Type: application/json');
error_reporting(0); // Disable error display
ob_start(); // Start output buffering

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

    // Improved date handling with timezone support
    date_default_timezone_set('Asia/Manila');
    $dateUpdated = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $dateUpdatedStr = $dateUpdated->format('Y-m-d H:i:s');

    // Prepare and execute SQL - FIXED bind_param order and parameter count
    $stmt = $conn->prepare("
        UPDATE establishments 
        SET 
            name = ?,
            address = ?,
            owner_representative = ?,
            violations = ?,
            date_updated = ?
            WHERE id = ?
    ");

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    // Fixed bind_param types and parameters
    $stmt->bind_param(
        "sssssi", // String, String, String, String, String, Integer
        $name,
        $address,   
        $ownerRep,
        $violations,
        $dateUpdatedStr,
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        throw new Exception('No records updated - ID may not exist');
    }

    // Success response with updated timestamp
    $response = [
        'success' => true,
        'message' => 'Record updated successfully',
        'updated_id' => $id,
        'date_updated' => $dateUpdatedStr,
        'formatted_date' => $dateUpdated->format('F j, Y, g:i a') // Human-readable format
    ];

} catch(Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
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
    exit();
}
?>