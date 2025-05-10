<?php
/**
 * AJAX endpoint to fetch establishments with pending notices
 * Used for refreshing notifications in real-time
 */

session_start();
include __DIR__ . '/../connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Query to find establishments with pending status beyond 48 hours excluding weekends
    $overdueQuery = $conn->query("
        SELECT e.name, e.establishment_id, ns.issued_datetime  
        FROM establishments e
        JOIN notice_status ns ON e.establishment_id = ns.establishment_id
        WHERE ns.status = 'pending' 
        AND (
            DATEDIFF(NOW(), ns.issued_datetime) > 2
            OR (
                DATEDIFF(NOW(), ns.issued_datetime) = 2
                AND
                HOUR(TIMEDIFF(NOW(), ns.issued_datetime)) >= 0
                AND
                MINUTE(TIMEDIFF(NOW(), ns.issued_datetime)) >= 0
            )
        )
        AND (
            (DAYOFWEEK(ns.issued_datetime) NOT IN (1, 7)) /* Exclude weekends */
            OR
            (DATEDIFF(NOW(), ns.issued_datetime) > 4) /* If more than 4 days, include anyway */
        )
        ORDER BY ns.issued_datetime ASC
    ");

    $overdueEstablishments = [];
    if ($overdueQuery && $overdueQuery->num_rows > 0) {
        while ($row = $overdueQuery->fetch_assoc()) {
            // Calculate the business days elapsed
            $issuedDate = new DateTime($row['issued_datetime']);
            $currentDate = new DateTime();
            $businessDays = getBusinessDays($issuedDate, $currentDate);
            
            if ($businessDays >= 2) { // At least 2 business days (48 hours excl. weekends)
                $row['business_days_elapsed'] = $businessDays;
                $overdueEstablishments[] = $row;
            }
        }
    }

    // Return JSON response
    echo json_encode($overdueEstablishments);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to fetch data', 'message' => $e->getMessage()]);
}

/**
 * Function to calculate business days between two dates
 * @param DateTime $startDate - The start date
 * @param DateTime $endDate - The end date
 * @return int - Number of business days
 */
function getBusinessDays($startDate, $endDate) {
    $days = 0;
    $current = clone $startDate;
    
    while ($current <= $endDate) {
        $dayOfWeek = $current->format('N');
        if ($dayOfWeek < 6) { // 1 (Monday) to 5 (Friday) are business days
            $days++;
        }
        $current->modify('+1 day');
    }
    
    return $days;
}