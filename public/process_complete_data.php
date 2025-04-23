<?php
session_start();
require_once 'connection.php'; // Make sure this file exists with your database connection

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_issuer'])) {
    // Extract establishment data
    $establishment = mysqli_real_escape_string($conn, $_POST['establishment']);
    $owner_representative = mysqli_real_escape_string($conn, $_POST['owner_representative']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $nature_select = mysqli_real_escape_string($conn, $_POST['nature_select']);
    $nature_custom = mysqli_real_escape_string($conn, $_POST['nature_custom']);
    $nature = ($nature_select === 'Others') ? $nature_custom : $nature_select;
    $products = mysqli_real_escape_string($conn, $_POST['products']);
    
    // Extract notice status information
    $notice_status = mysqli_real_escape_string($conn, $_POST['notice_status']);
    $issued_datetime = mysqli_real_escape_string($conn, $_POST['issued_datetime']);
    $issued_by = mysqli_real_escape_string($conn, $_POST['issued_by']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $witnessed_by = mysqli_real_escape_string($conn, $_POST['witnessed_by']);
    
    // Current date and time for record creation
    $datetime_now = date('Y-m-d H:i:s');
    
    // Insert into establishments table
    $check_sql = "SELECT id FROM establishments WHERE establishment_name = '$establishment' AND owner_representative = '$owner_representative'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Establishment exists, get its ID
        $row = mysqli_fetch_assoc($check_result);
        $establishment_id = $row['id'];
        
        // Update last_updated timestamp
        $update_sql = "UPDATE establishments SET 
                      last_updated = '$datetime_now'
                      WHERE id = $establishment_id";
        mysqli_query($conn, $update_sql);
    } else {
        // Insert new establishment
        $insert_sql = "INSERT INTO establishments (
                       establishment_name, 
                       address, 
                       owner_representative, 
                       business_nature,
                       non_conforming_products,
                       date_created,
                       last_updated,
                       violations_count
                   ) VALUES (
                       '$establishment', 
                       '$address', 
                       '$owner_representative', 
                       '$nature',
                       '$products',
                       '$datetime_now',
                       '$datetime_now',
                       0
                   )";
        
        if (mysqli_query($conn, $insert_sql)) {
            $establishment_id = mysqli_insert_id($conn);
        } else {
            // Error handling
            $_SESSION['error_message'] = "Database Error: " . mysqli_error($conn);
            header("Location: nov_form.php");
            exit();
        }
    }
    
    // Process violations
    if (isset($_POST['violations']) && is_array($_POST['violations'])) {
        $violations = $_POST['violations'];
        $violations_count = count($violations);
        
        // Update violations count
        $update_count_sql = "UPDATE establishments SET 
                          violations_count = violations_count + $violations_count, 
                          last_updated = '$datetime_now'
                          WHERE id = $establishment_id";
        mysqli_query($conn, $update_count_sql);
        
        // Insert each violation
        foreach ($violations as $violation) {
            $violation = mysqli_real_escape_string($conn, $violation);
            
            $violation_sql = "INSERT INTO violations (
                           establishment_id,
                           violation_type,
                           date_recorded
                       ) VALUES (
                           $establishment_id,
                           '$violation',
                           '$datetime_now'
                       )";
            mysqli_query($conn, $violation_sql);
        }
    }
    
    // Process inventory items
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        foreach ($_POST['products'] as $product) {
            $name = mysqli_real_escape_string($conn, $product['name']);
            $sealed = isset($product['sealed']) ? 1 : 0;
            $withdrawn = isset($product['withdrawn']) ? 1 : 0;
            $description = mysqli_real_escape_string($conn, $product['description']);
            $price = floatval($product['price']);
            $pieces = intval($product['pieces']);
            $dao_violation = isset($product['dao_violation']) ? 1 : 0;
            $other_violation = isset($product['other_violation']) ? 1 : 0;
            $remarks = mysqli_real_escape_string($conn, $product['remarks']);
            
            $inventory_sql = "INSERT INTO inventory (
                           establishment_id,
                           product_name,
                           description,
                           price,
                           pieces,
                           sealed,
                           withdrawn,
                           dao_violation,
                           other_violation,
                           remarks,
                           date_added
                       ) VALUES (
                           $establishment_id,
                           '$name',
                           '$description',
                           $price,
                           $pieces,
                           $sealed,
                           $withdrawn,
                           $dao_violation,
                           $other_violation,
                           '$remarks',
                           '$datetime_now'
                       )";
            mysqli_query($conn, $inventory_sql);
        }
    }
    
    // Insert notice information
    $notice_sql = "INSERT INTO notices (
                establishment_id,
                notice_status,
                issued_datetime,
                issued_by,
                position,
                witnessed_by,
                date_recorded
            ) VALUES (
                $establishment_id,
                '$notice_status',
                '$issued_datetime',
                '$issued_by',
                '$position',
                '$witnessed_by',
                '$datetime_now'
            )";
    mysqli_query($conn, $notice_sql);
    
    // Set success message and redirect
    $_SESSION['success_message'] = "Notice of Violation for $establishment has been successfully recorded.";
    header("Location: establishments.php?success=1");
    exit();
} else {
    // Handle direct access to this script
    header("Location: nov_form.php");
    exit();
}
?>