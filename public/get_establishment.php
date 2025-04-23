<?php
// get_establishments.php
header('Content-Type: application/json');

// Database connection
$servername = "127.0.0.1"; // Use the server name from the UI
$username = "root"; // Default username, adjust as needed
$password = ""; // Default password, adjust as needed
$dbname = "nov_system"; // Your database name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all establishments
    $stmt = $conn->prepare("SELECT * FROM establishments ORDER BY created_at DESC");
    $stmt->execute();
    $establishments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // For each establishment, get its inventory products
    foreach ($establishments as &$establishment) {
        $stmt = $conn->prepare("SELECT * FROM inventory WHERE establishment_id = ?");
        $stmt->execute([$establishment['id']]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $establishment['products'] = $products;
    }
    
    echo json_encode(['success' => true, 'establishments' => $establishments]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Database error: " . $e->getMessage()]);
}
$conn = null;
?>