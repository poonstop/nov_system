<?php
session_start();

// Clear specific session variables related to NOV form
unset($_SESSION['nov_details']);
unset($_SESSION['error']);
unset($_SESSION['success']);

// Optional: You can add a response if needed
echo json_encode(['status' => 'success']);
?>