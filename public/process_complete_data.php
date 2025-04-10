<?php
session_start();

// Get JSON data from the request body
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if ($data) {
    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/nov_files/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Extract form data
    $establishment = $data['establishment'] ?? '';
    $address = $data['address'] ?? '';
    $nature = ($data['nature_select'] === 'Others') 
        ? ($data['nature_custom'] ?? '') 
        : ($data['nature_select'] ?? '');
    $owner_rep = $data['owner_representative'] ?? '';
    $products = $data['products'] ?? [];
    $violations = is_array($data['violations']) ? implode(', ', $data['violations']) : ($data['violations'] ?? '');
    $notice_status = $data['notice_status'] ?? '';
    $remarks = $data['remarks'] ?? '';
    
    // Generate NOV file
    $filename = preg_replace('/[^A-Za-z0-9\-]/', '', $establishment) . '_' . time() . '.txt';
    $fileContent = "NOTICE OF VIOLATION\n\n";
    $fileContent .= "Establishment: $establishment\n";
    $fileContent .= "Address: $address\n";
    $fileContent .= "Nature of Business: $nature\n";
    $fileContent .= "Non-Conforming Products: " . json_encode($products) . "\n";
    $fileContent .= "Violations: $violations\n";
    $fileContent .= "Notice Status: $notice_status\n";
    $fileContent .= "Remarks: $remarks\n";
    $fileContent .= "\nDate: " . date('Y-m-d H:i:s');
    
    file_put_contents($uploadDir . $filename, $fileContent);
    
    // Store details in session
    $_SESSION['nov_details'] = [
        'filename' => $filename,
        'establishment' => $establishment,
        'address' => $address,
        'nature' => $nature,
        'nature_select' => $data['nature_select'] ?? '',
        'nature_custom' => $data['nature_custom'] ?? '',
        'owner_representative' => $owner_rep,
        'products' => $products,
        'violations' => $data['violations'] ?? [],
        'notice_status' => $notice_status,
        'remarks' => $remarks
    ];
    
    // Take a snapshot of form data
    $_SESSION['form_snapshot'] = $data;
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
}
?>