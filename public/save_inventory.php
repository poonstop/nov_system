<?php
session_start();
include __DIR__ . '/../connection.php';

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$response = ['success' => false, 'message' => ''];

// Store products in session for later use
if (isset($_POST['products']) && is_array($_POST['products'])) {
    $_SESSION['inventory'] = $_POST['products'];
    
    // If this is an Ajax request, return a success response
    if ($isAjax) {
        $response['success'] = true;
        $response['message'] = 'Products stored in session successfully.';
        echo json_encode($response);
        exit();
    }
    
    // For non-Ajax requests, redirect to show the status modal
    header("Location: establishments.php?show_status_modal=1");
    exit();
} else {
    $response['message'] = 'No products were submitted. Please add at least one product.';
    
    if ($isAjax) {
        echo json_encode($response);
        exit();
    }
    
    $_SESSION['error'] = $response['message'];
    header("Location: establishments.php");
    exit();
}