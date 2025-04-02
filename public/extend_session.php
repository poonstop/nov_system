<?php
session_start();
$_SESSION['timeout'] = time(); // Reset the timeout
echo json_encode(['success' => true]);
?>