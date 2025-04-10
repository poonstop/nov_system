<?php
session_start();

// Get POST data
$establishment = $_POST['establishment'] ?? '';
$owner_rep = $_POST['owner_representative'] ?? '';
$address = $_POST['address'] ?? '';
$nature_select = $_POST['nature_select'] ?? '';
$nature_custom = $_POST['nature_custom'] ?? '';
$products = $_POST['products'] ?? '';
$violations = $_POST['violations'] ?? [];
$remarks = $_POST['remarks'] ?? '';

// Determine nature value
$nature = ($nature_select === 'Others') ? $nature_custom : $nature_select;

// Store in session
$_SESSION['nov_details'] = [
    'establishment' => htmlspecialchars($establishment),
    'address' => htmlspecialchars($address),
    'nature' => htmlspecialchars($nature),
    'nature_select' => htmlspecialchars($nature_select),
    'nature_custom' => htmlspecialchars($nature_custom),
    'owner_representative' => htmlspecialchars($owner_rep),
    'products' => htmlspecialchars($products),
    'violations' => $violations,
    'remarks' => htmlspecialchars($remarks)
];

// Take a snapshot of form data
$_SESSION['form_snapshot'] = $_POST;

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;