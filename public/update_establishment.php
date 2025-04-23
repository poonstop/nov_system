<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

// Connect to database
require_once 'connection.php';

// Log received data for debugging
$input = file_get_contents('php://input');
error_log('Received data: ' . $input);

try {
    // Decode JSON input
    $data = json_decode($input, true);
    
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    // Extract data
    $id = isset($data['id']) ? intval($data['id']) : 0;
    $name = isset($data['name']) ? mysqli_real_escape_string($conn, $data['name']) : '';
    $address = isset($data['address']) ? mysqli_real_escape_string($conn, $data['address']) : '';
    $owner_rep = isset($data['owner_rep']) ? mysqli_real_escape_string($conn, $data['owner_rep']) : '';
    $violations = isset($data['violations']) ? mysqli_real_escape_string($conn, $data['violations']) : '';
    $num_violations = isset($data['num_violations']) ? intval($data['num_violations']) : 0;
    $date_updated = isset($data['date_updated']) ? mysqli_real_escape_string($conn, $data['date_updated']) : date('Y-m-d H:i:s');
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Update establishment
    $update_sql = "UPDATE establishments SET 
                  establishment_name = '$name',
                  address = '$address', 
                  owner_representative = '$owner_rep',
                  violations = '$violations',
                  violations_count = $num_violations,
                  last_updated = '$date_updated'
                WHERE id = $id";
    
    $result = mysqli_query($conn, $update_sql);
    
    if (!$result) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }
    
    // Handle inventory items if they exist
    if (isset($data['inventory']) && is_array($data['inventory'])) {
        // First, get existing inventory items to determine which ones to update vs insert
        $existing_items = [];
        $query = "SELECT id FROM inventory WHERE establishment_id = $id";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $existing_items[] = $row['id'];
        }
        
        foreach ($data['inventory'] as $item) {
            $item_id = isset($item['id']) && !empty($item['id']) ? intval($item['id']) : 0;
            $product_name = isset($item['product_name']) ? mysqli_real_escape_string($conn, $item['product_name']) : '';
            $price = isset($item['price']) ? floatval($item['price']) : 0.00;
            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
            $sealed = isset($item['sealed']) ? intval($item['sealed']) : 0;
            $withdrawn = isset($item['withdrawn']) ? intval($item['withdrawn']) : 0;
            
            if ($item_id > 0 && in_array($item_id, $existing_items)) {
                // Update existing item
                $update_item_sql = "UPDATE inventory SET 
                                  product_name = '$product_name',
                                  price = $price,
                                  pieces = $quantity,
                                  sealed = $sealed,
                                  withdrawn = $withdrawn,
                                  date_updated = '$date_updated'
                                WHERE id = $item_id AND establishment_id = $id";
                                
                if (!mysqli_query($conn, $update_item_sql)) {
                    throw new Exception("Failed to update inventory item: " . mysqli_error($conn));
                }
                
                // Remove from existing items array
                $key = array_search($item_id, $existing_items);
                if ($key !== false) {
                    unset($existing_items[$key]);
                }
            } else {
                // Insert new item
                $insert_item_sql = "INSERT INTO inventory (
                                  establishment_id,
                                  product_name,
                                  price,
                                  pieces,
                                  sealed,
                                  withdrawn,
                                  date_added
                              ) VALUES (
                                  $id,
                                  '$product_name',
                                  $price,
                                  $quantity,
                                  $sealed,
                                  $withdrawn,
                                  '$date_updated'
                              )";
                              
                if (!mysqli_query($conn, $insert_item_sql)) {
                    throw new Exception("Failed to insert inventory item: " . mysqli_error($conn));
                }
            }
        }
        
        // Delete items that weren't updated or are no longer present
        if (!empty($existing_items)) {
            $ids_to_delete = implode(',', $existing_items);
            $delete_sql = "DELETE FROM inventory WHERE id IN ($ids_to_delete) AND establishment_id = $id";
            
            if (!mysqli_query($conn, $delete_sql)) {
                throw new Exception("Failed to delete old inventory items: " . mysqli_error($conn));
            }
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Establishment and inventory updated successfully'
    ]);
    
} catch (Exception $e) {
    // Roll back transaction on error
    mysqli_rollback($conn);
    
    // Log error
    error_log("Error in update_establishment.php: " . $e->getMessage());
    
    // Return error
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>