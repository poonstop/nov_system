<?php
header('Content-Type: application/json');
include __DIR__ . '/../connection.php';

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

// Process date - ensure it's valid
$datetime = date('Y-m-d H:i:s'); // Default to current datetime
if (!empty($data['datetime'])) {
    try {
        $datetimeObj = new DateTime($data['datetime']);
        $datetime = $datetimeObj->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        error_log('Datetime processing error: ' . $e->getMessage());
    }
}

try {
    $stmt = $conn->prepare("UPDATE establishments SET 
        name = ?, 
        address = ?, 
        owner_representative = ?, 
        violations = ?, 
        num_violations = ?, 
        created_at = ?
        WHERE id = ?");
    
    $stmt->bind_param(
        "ssssiss",
        $data['name'],
        $data['address'],
        $data['owner_rep'],
        $data['violations'],
        $data['num_violations'],
        $datetime,
        $data['id']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}

$conn->close();
?>