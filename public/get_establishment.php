<?php // get_establishment.php
header('Content-Type: application/json');
include __DIR__ . '/../connection.php';

// Initialize response
$response = ['success' => false];

try {
    // Validate ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid establishment ID');
    }
    
    $id = (int)$_GET['id'];
    
    // Get establishment details
    $stmt = $conn->prepare("
        SELECT 
            e.id, 
            e.name, 
            e.address, 
            e.owner_representative as owner_rep, 
            e.nature,
            e.violations, 
            e.products,
            e.nov_files,
            e.notice_status,
            e.issued_by,
            e.position,
            e.issued_datetime,
            e.witnessed_by,
            e.remarks
        FROM establishments e
        WHERE e.id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Establishment not found');
    }
    
    $establishment = $result->fetch_assoc();
    
    // Get inventory items from the inventory table
    $inventory = [];
    $inv_stmt = $conn->prepare("
        SELECT 
            inventory_id,
            product_name,
            sealed,
            withdrawn,
            description,
            price,
            pieces,
            dao_violation,
            other_violation,
            remarks
        FROM inventory
        WHERE id = ?
    ");
    
    if ($inv_stmt) {
        $inv_stmt->bind_param("i", $id);
        $inv_stmt->execute();
        $inv_result = $inv_stmt->get_result();
        
        while ($item = $inv_result->fetch_assoc()) {
            // Convert numeric strings to appropriate types
            $item['price'] = (float)$item['price']; 
            $item['pieces'] = (int)$item['pieces'];
            
            // Convert numeric booleans to actual booleans for JSON
            $item['sealed'] = (bool)$item['sealed'];
            $item['withdrawn'] = (bool)$item['withdrawn'];
            $item['dao_violation'] = (bool)$item['dao_violation'];
            $item['other_violation'] = (bool)$item['other_violation'];
            
            $inventory[] = $item;
        }
        $inv_stmt->close();
    }
    
    // If no inventory items found in separate table, try parsing from products field
    if (empty($inventory) && !empty($establishment['products'])) {
        $products_array = json_decode($establishment['products'], true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($products_array)) {
            // Valid JSON found
            $inventory = $products_array;
        } else {
            // If not valid JSON, try parsing as comma-separated list
            $products = explode(',', $establishment['products']);
            foreach ($products as $product) {
                if (trim($product) !== '') {
                    $inventory[] = [
                        'product_name' => trim($product),
                        'pieces' => 0,
                        'price' => 0.00,
                        'sealed' => false,
                        'withdrawn' => false
                    ];
                }
            }
        }
    }
    
    // Format violations as array if it's a comma-separated string
    if (!empty($establishment['violations']) && is_string($establishment['violations'])) {
        $establishment['violations_array'] = array_map('trim', explode(',', $establishment['violations']));
    } else {
        $establishment['violations_array'] = [];
    }
    
    $response = [
        'success' => true,
        'establishment' => $establishment,
        'inventory' => $inventory
    ];

} catch (Exception $e) {
    error_log("Error in get_establishment.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
} finally {
    // Close statements if they exist
    if (isset($stmt)) $stmt->close();
    if (isset($inv_stmt)) $inv_stmt->close();
    
    // Output JSON response
    echo json_encode($response);
    
    // Close connection
    if (isset($conn)) $conn->close();
}