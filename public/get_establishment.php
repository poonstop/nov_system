<?php
// get_establishment.php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection.php';

$response = ['success' => false];

try {
    // Validate ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid establishment ID');
    }
    
    $id = (int)$_GET['id'];
    
    // Get establishment details
    $stmt = $conn->prepare("
        SELECT id, name, address, owner_representative as owner_rep, violations, products
        FROM establishments 
        WHERE id = ?
    ");
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Establishment not found');
    }
    
    $establishment = $result->fetch_assoc();
    
    // Parse inventory from products field
    $inventory = [];
    if (!empty($establishment['products'])) {
        $inventory = json_decode($establishment['products'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If not valid JSON, try parsing as comma-separated list
            $products = explode(',', $establishment['products']);
            foreach ($products as $product) {
                $inventory[] = [
                    'product_name' => trim($product),
                    'quantity' => '0',
                    'price' => '0.00'
                ];
            }
        }
    }
    
    $response = [
        'success' => true,
        'establishment' => $establishment,
        'inventory' => $inventory
    ];
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    
    echo json_encode($response);
}