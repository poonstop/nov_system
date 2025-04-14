// Create a new file called set_status_session.php
<?php
session_start();

// Check if request contains data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data) {
        // Store the data in session
        $_SESSION['nov_details'] = [
            'establishment' => $data['establishment'] ?? '',
            'address' => $data['address'] ?? '',
            'nature' => $data['nature'] ?? '',
            'nature_select' => $data['nature_select'] ?? '',
            'nature_custom' => $data['nature_custom'] ?? '',
            'owner_representative' => $data['owner_representative'] ?? '',
            'products' => $data['products'] ?? '',
            'violations' => $data['violations[]'] ?? [],
            'notice_status' => $data['notice_status'] ?? '',
            'remarks' => $data['remarks'] ?? ''
        ];
        
        // Generate NOV file for non-inventory case
        $uploadDir = __DIR__ . '/nov_files/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $establishment = preg_replace('/[^A-Za-z0-9\-]/', '', $data['establishment'] ?? 'unknown');
        $filename = $establishment . '_' . time() . '.txt';
        $fileContent = "NOTICE OF VIOLATION\n\n";
        $fileContent .= "Establishment: " . ($data['establishment'] ?? '') . "\n";
        $fileContent .= "Address: " . ($data['address'] ?? '') . "\n";
        $fileContent .= "Nature of Business: " . ($data['nature'] ?? '') . "\n";
        $fileContent .= "Non-Conforming Products: " . ($data['products'] ?? '') . "\n";
        $fileContent .= "Violations: " . implode(', ', (array)($data['violations[]'] ?? [])) . "\n";
        $fileContent .= "Notice Status: " . ($data['notice_status'] ?? '') . "\n";
        $fileContent .= "Remarks: " . ($data['remarks'] ?? '') . "\n";
        $fileContent .= "\nDate: " . date('Y-m-d H:i:s');
        
        file_put_contents($uploadDir . $filename, $fileContent);
        $_SESSION['nov_details']['filename'] = $filename;
        
        // Send success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        // Send error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    }
} else {
    // Send error for invalid request method
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>