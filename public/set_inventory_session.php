<?php
session_start();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the combined form data
    $allFormData = json_decode($_POST['all_form_data'], true);
    
    if ($allFormData) {
        // Store in session for later processing
        $_SESSION['nov_details'] = $allFormData;
        
        // Return success response
        echo json_encode(['success' => true]);
        exit;
    } else {
        // Return error response
        echo json_encode(['success' => false, 'message' => 'Invalid form data']);
        exit;
    }
} else {
    // Return error for non-POST requests
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
?>