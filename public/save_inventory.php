<?php
// Start session to access session variables
session_start();
include __DIR__ . '/../connection.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Get the establishment ID if it exists in session
        $establishment_id = $_SESSION['establishment_id'] ?? null;
        
        // If no establishment ID exists, we need to create one first
        if (!$establishment_id) {
            // Extract establishment data from form
            $establishment = $_POST['establishment'] ?? '';
            $owner_rep = $_POST['owner_representative'] ?? '';
            $address = $_POST['address'] ?? '';
            $nature = $_POST['nature_select'] === 'Others' ? $_POST['nature_custom'] : $_POST['nature_select'];
            $products_general = $_POST['products'] ?? '';
            
            // Insert establishment record
            $stmt = $conn->prepare("INSERT INTO establishments 
                (name, address, owner_representative, nature, products)
                VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt === false) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssss", 
                $establishment,
                $address,
                $owner_rep,
                $nature,
                $products_general
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $establishment_id = $conn->insert_id;
            
            // Store establishment ID in session for future use
            $_SESSION['establishment_id'] = $establishment_id;
        }
        
        // Now handle the inventory products
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $index => $product) {
                // Skip products without a name
                if (empty($product['name'])) continue;
                
                $stmt_product = $conn->prepare("INSERT INTO inventory_products 
                    (establishment_id, product_name, sealed, withdrawn, description, price, pieces, 
                    dao_violation, other_violation, remarks)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt_product === false) {
                    throw new Exception("Prepare product statement failed: " . $conn->error);
                }
                
                $name = htmlspecialchars($product['name']);
                $sealed = isset($product['sealed']) ? 1 : 0;
                $withdrawn = isset($product['withdrawn']) ? 1 : 0;
                $description = htmlspecialchars($product['description'] ?? '');
                $price = floatval($product['price'] ?? 0);
                $pieces = intval($product['pieces'] ?? 0);
                $dao_violation = isset($product['dao_violation']) ? 1 : 0;
                $other_violation = isset($product['other_violation']) ? 1 : 0;
                $remarks = htmlspecialchars($product['remarks'] ?? '');
                
                $stmt_product->bind_param("issiisdiss", 
                    $establishment_id,
                    $name,
                    $sealed,
                    $withdrawn,
                    $description,
                    $price,
                    $pieces,
                    $dao_violation,
                    $other_violation,
                    $remarks
                );
                
                if (!$stmt_product->execute()) {
                    throw new Exception("Execute product insert failed: " . $stmt_product->error);
                }
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        // Return success response
        echo json_encode(['success' => true, 'message' => 'Inventory saved successfully', 'establishment_id' => $establishment_id]);
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        
        // Log the error
        error_log("Database Error: " . $e->getMessage());
        
        // Return error response
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    // Not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>