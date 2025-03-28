<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../connection.php';

$rawInput = file_get_contents('php://input');
file_put_contents('debug_input.log', $rawInput);

$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid JSON: ' . json_last_error_msg(),
        'raw_input' => $rawInput
    ]);
    exit;
}

if (!isset($data['id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'No ID provided',
        'received_data' => $data
    ]);
    exit;
}

try {
    // Count violations from the comma-separated string
    $violationCount = !empty($data['violations']) ? 
        count(explode(',', $data['violations'])) : 
        0;

    $stmt = $conn->prepare("UPDATE establishments SET 
        name = ?, 
        address = ?, 
        owner_representative = ?, 
        violations = ?, 
        created_at = ? 
        WHERE id = ?");

    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $date = date('Y-m-d'); // Default to current date

if (!empty($data['date'])) {
    try {
        // Handle ISO format (YYYY-MM-DD)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
            $date = $data['date'];
        } 
        // Handle other formats if needed
        else {
            $dateObj = new DateTime($data['date']);
            $date = $dateObj->format('Y-m-d');
        }
    } catch (Exception $e) {
        error_log('Date parse error: ' . $e->getMessage());
    }
}

    $stmt->bind_param(
        "ssssis",
        $data['name'], 
        $data['address'], 
        $data['owner_rep'], 
        $data['violations'], 
        $data['date'], 
        $data['id']
    );

    $result = $stmt->execute();

    if ($result) {
        echo json_encode([
            'success' => true,
            'violation_count' => $violationCount
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Update failed: ' . $stmt->error
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Exception: ' . $e->getMessage()
    ]);
}

$conn->close();
?>