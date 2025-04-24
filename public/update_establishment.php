<?php
header('Content-Type: application/json');
include __DIR__ . '/../db_config.php';

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users
ini_set('log_errors', 1);

// Enhanced debug logging with rotation
function debug_log($message) {
    $log_file = __DIR__ . '/update_debug.log';
    
    // Rotate log if larger than 5MB
    if (file_exists($log_file) && filesize($log_file) > 5 * 1024 * 1024) {
        rename($log_file, __DIR__ . '/update_debug_' . date('Y-m-d_His') . '.log');
    }
    
    $time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$time] $message\n", FILE_APPEND);
}

debug_log("===== New Update Request =====");

// Initialize response
$response = [
    'success' => false, 
    'message' => 'An unknown error occurred',
    'validation_errors' => []
];

try {
    // Verify CSRF token for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
            throw new Exception('CSRF token validation failed');
        }
    }

    // Get and validate JSON input
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception('No data received');
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    // Validate required fields with detailed error messages
    $requiredFields = [
        'id' => 'Establishment ID',
        'name' => 'Establishment Name',
        'address' => 'Address',
        'owner_rep' => 'Owner/Representative'
    ];
    
    foreach ($requiredFields as $field => $fieldName) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $response['validation_errors'][$field] = "$fieldName is required";
        }
    }
    
    if (!empty($response['validation_errors'])) {
        throw new Exception('Validation failed');
    }

    // Begin transaction
    $pdo->beginTransaction();

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

    // Handle notice information with validation
    $noticeStatus = null;
    if (isset($data['notice_status'])) {
        $validStatuses = ['Received', 'Refused', 'Pending', 'Complied'];
        if (in_array($data['notice_status'], $validStatuses)) {
            $noticeStatus = $data['notice_status'];
        } else {
            debug_log("Invalid notice status: " . $data['notice_status']);
        }
    }
    
    // Validate and format datetime with Manila timezone
    $issuedDateTime = null;
    if (!empty($data['issued_datetime'])) {
        try {
            $dateObj = new DateTime($data['issued_datetime'], new DateTimeZone('Asia/Manila'));
            $issuedDateTime = $dateObj->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            debug_log("Invalid issued_datetime format: " . $data['issued_datetime']);
        }
    }
    
    // Sanitize other fields
    $issuedBy = isset($data['issued_by']) ? trim($data['issued_by']) : null;
    $position = isset($data['position']) ? trim($data['position']) : null;
    $witnessedBy = isset($data['witnessed_by']) ? trim($data['witnessed_by']) : null;
    $contactNumber = isset($data['contact_number']) ? 
        preg_replace('/[^0-9]/', '', $data['contact_number']) : null;

    // Update establishment record using PDO
    $stmt = $pdo->prepare("
        UPDATE establishments 
        SET 
            name = :name,
            address = :address,
            owner_representative = :owner_rep,
            contact_number = :contact_number,
            violations = :violations,
            num_violations = :num_violations,
            notice_status = :notice_status,
            issued_datetime = :issued_datetime,
            issued_by = :issued_by,
            position = :position,
            witnessed_by = :witnessed_by,
            updated_at = NOW(),
            updated_by = :updated_by
        WHERE id = :id
    ");
    
    $stmt->execute([
        ':name' => $name,
        ':address' => $address,
        ':owner_rep' => $ownerRep,
        ':contact_number' => $contactNumber,
        ':violations' => $violations,
        ':num_violations' => $numViolations,
        ':notice_status' => $noticeStatus,
        ':issued_datetime' => $issuedDateTime,
        ':issued_by' => $issuedBy,
        ':position' => $position,
        ':witnessed_by' => $witnessedBy,
        ':updated_by' => $_SESSION['user_id'],
        ':id' => $id
    ]);

    // Handle inventory data if provided
    if (isset($data['inventory']) && is_array($data['inventory'])) {
        // First delete existing inventory
        $pdo->prepare("DELETE FROM establishment_inventory WHERE establishment_id = ?")
            ->execute([$id]);

        // Insert new inventory items with batch processing
        $inventoryStmt = $pdo->prepare("
            INSERT INTO establishment_inventory (
                establishment_id, product_name, sealed, withdrawn, description,
                price, pieces, dao_violation, other_violation, remarks, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        // Batch processing for performance
        $pdo->beginTransaction();
        try {
            foreach ($data['inventory'] as $item) {
                $inventoryStmt->execute([
                    $id,
                    $item['name'] ?? '',
                    isset($item['sealed']) ? 1 : 0,
                    isset($item['withdrawn']) ? 1 : 0,
                    $item['description'] ?? '',
                    filter_var($item['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                    filter_var($item['pieces'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
                    isset($item['dao_violation']) ? 1 : 0,
                    isset($item['other_violation']) ? 1 : 0,
                    $item['remarks'] ?? ''
                ]);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception('Failed to insert inventory items: ' . $e->getMessage());
        }
    }

    // Log the update action
    $logStmt = $pdo->prepare("
        INSERT INTO system_logs (user_id, action_type, description, ip_address)
        VALUES (?, 'establishment_update', ?, ?)
    ");
    $logStmt->execute([
        $_SESSION['user_id'],
        "Updated establishment #$id",
        $_SERVER['REMOTE_ADDR']
    ]);

    // Commit transaction
    $pdo->commit();
    
    $response = [
        'success' => true,
        'message' => 'Record updated successfully',
        'updated_id' => $id,
        'changes' => [
            'name' => $name,
            'address' => $address,
            'violations_count' => $numViolations
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Database error',
        'error_code' => $e->getCode(),
        'error_info' => $e->errorInfo ?? null
    ];
    debug_log("PDO Error: " . $e->getMessage() . " [Code: " . $e->getCode() . "]");
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    debug_log("Error: " . $e->getMessage());
    
} finally {
    // Close any remaining connections
    $pdo = null;
    
    // Send response
    echo json_encode($response);
    debug_log("Response: " . json_encode($response));
    exit();
}