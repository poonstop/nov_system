<?php
header('Content-Type: application/json');
include __DIR__ . '/../connection.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone to match your application needs
date_default_timezone_set('Asia/Manila'); // Adjust to your preferred timezone

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
    $dateUpdated = new DateTime('now');
    if (!empty($data['date_updated'])) {
        try {
            $inputDate = new DateTime($data['date_updated']);
            $dateUpdated = $inputDate;
        } catch (Exception $e) {
            // Log the error but continue with current time
            error_log("Date parsing error: " . $e->getMessage());
        }
    }
    $dateUpdatedStr = $dateUpdated->format('Y-m-d H:i:s');

    // Prepare and execute SQL
    $stmt = $conn->prepare("
        UPDATE establishments 
        SET 
            name = ?,
            address = ?,
            owner_representative = ?,
            violations = ?,
            num_violations = ?,
            date_updated = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param(
        "ssssisi",
        $name,
        $address,
        $ownerRep,
        $violations,
        $numViolations,
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
    echo json_encode([
        'success' => true,
        'message' => 'Record updated successfully',
        'updated_id' => $id,
        'date_updated' => $dateUpdatedStr,
        'formatted_date' => $dateUpdated->format('F j, Y, g:i a') // Human-readable format
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getFile() . ':' . $e->getLine()
    ]);
} finally {
    // Clean up resources
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>