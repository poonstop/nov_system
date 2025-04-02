<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['timeout'])) {
    $_SESSION['timeout'] = time(); // Reset timeout
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>