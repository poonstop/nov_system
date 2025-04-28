<?php
// Include database connection
include __DIR__ . '/../connection.php';

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Check if it's an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Prepare response array
    $response = ['success' => false, 'message' => ''];
    
    // Get establishment ID or name
    $establishment_name = sanitize($_POST['establishment'] ?? '');
    $establishment_id = 0;
    
    // Find the establishment ID if name is provided
    if (!empty($establishment_name)) {
        $query = "SELECT id FROM establishments WHERE name = '$establishment_name' ORDER BY id DESC LIMIT 1";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $establishment_id = $row['id'];
        } else {
            // If establishment doesn't exist yet, we'll create it
            $address = sanitize($_POST['address'] ?? '');
            $owner_representative = sanitize($_POST['owner_representative'] ?? '');
            $nature = sanitize($_POST['nature_select'] ?? '');
            if ($nature == 'Others') {
                $nature = sanitize($_POST['nature_custom'] ?? '');
            }
            $products = sanitize($_POST['products'] ?? '');
            
            // Generate a unique filename for NOV files
            $nov_filename = strtolower(str_replace(' ', '', $establishment_name)) . '_' . time() . '.txt';
            
            // Create SQL query for establishments table
            $sql = "INSERT INTO establishments (name, address, owner_representative, nature, products, violations, remarks, nov_files) 
                    VALUES ('$establishment_name', '$address', '$owner_representative', '$nature', '$products', '', '', '$nov_filename')";
            
            if ($conn->query($sql) === TRUE) {
                $establishment_id = $conn->insert_id;
            } else {
                $response['message'] = "Error creating establishment: " . $conn->error;
                echo json_encode($response);
                exit;
            }
        }
    }
    
    // Check if we have a valid establishment ID
    if ($establishment_id <= 0) {
        $response['message'] = "Invalid establishment information";
        echo json_encode($response);
        exit;
    }
    
    // Process inventory products
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        $success = true;
        
        foreach ($_POST['products'] as $product) {
            $product_name = sanitize($product['name'] ?? '');
            
            // Skip empty product names
            if (empty($product_name)) continue;
            
            $sealed = isset($product['sealed']) ? 1 : 0;
            $withdrawn = isset($product['withdrawn']) ? 1 : 0;
            $description = sanitize($product['description'] ?? '');
            $price = sanitize($product['price'] ?? 0);
            $pieces = sanitize($product['pieces'] ?? 0);
            $dao_violation = isset($product['dao_violation']) ? 1 : 0;
            $other_violation = isset($product['other_violation']) ? 1 : 0;
            $product_remarks = sanitize($product['remarks'] ?? '');
            
            // Create SQL query for inventory table
            $inventory_sql = "INSERT INTO inventory (id, product_name, sealed, withdrawn, description, price, pieces, dao_violation, other_violation, remarks) 
                             VALUES ($establishment_id, '$product_name', $sealed, $withdrawn, '$description', '$price', '$pieces', $dao_violation, $other_violation, '$product_remarks')";
            
            // Execute the inventory query
            if (!$conn->query($inventory_sql)) {
                $success = false;
                $response['message'] = "Error saving inventory product '$product_name': " . $conn->error;
                break;
            }
        }
        
        if ($success) {
            // Update products JSON in the establishments table
            $products_json = json_encode($_POST['products']);
            $update_sql = "UPDATE establishments SET products = '$products_json' WHERE id = $establishment_id";
            $conn->query($update_sql);
            
            $response['success'] = true;
            $response['message'] = "Products have been saved successfully";
        }
    } else {
        $response['message'] = "No product data received";
    }
    
    // Send JSON response
    echo json_encode($response);
    exit;
} else {
    // If not an AJAX request, redirect to the main page
    header("Location: establishments.php");
    exit;
}
?>