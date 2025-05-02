<?php
// get_violations.php - This file handles AJAX requests for chart data filtering
session_start();
include __DIR__ . '/../connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Initialize response array
$response = [
    'labels' => [],
    'values' => []
];

// Get time range filter
$range = isset($_GET['range']) ? $_GET['range'] : 'year';

// Current date
$now = new DateTime();
$whereClause = "";

// Set date range filter
switch($range) {
    case 'week':
        $startDate = new DateTime();
        $startDate->modify('-7 days');
        $whereClause = " WHERE e.date_created >= '" . $startDate->format('Y-m-d') . "'";
        break;
    case 'month':
        $startDate = new DateTime();
        $startDate->modify('-1 month');
        $whereClause = " WHERE e.date_created >= '" . $startDate->format('Y-m-d') . "'";
        break;
    case 'year':
    default:
        $startDate = new DateTime();
        $startDate->modify('-1 year');
        $whereClause = " WHERE e.date_created >= '" . $startDate->format('Y-m-d') . "'";
        break;
}

try {
    // Query for violation data with time range filter
    $query = "
        SELECT a.municipality, COUNT(e.establishment_id) as violation_count
        FROM establishments e
        JOIN addresses a ON e.establishment_id = a.establishment_id
        $whereClause
        GROUP BY a.municipality
        ORDER BY violation_count DESC
        LIMIT 10
    ";
    
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = $row['municipality'];
            $response['values'][] = (int)$row['violation_count'];
        }
    }
    
    // If no data, provide some fallback data
    if (empty($response['labels'])) {
        $response['labels'] = ['No Data Available'];
        $response['values'] = [0];
    }
    
} catch (Exception $e) {
    error_log("AJAX Chart Data Error: " . $e->getMessage());
    $response['error'] = 'Failed to fetch data';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

?>