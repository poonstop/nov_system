<?php
// Start output buffering at the VERY TOP
ob_start();
session_start();
include __DIR__ . '/../connection.php';

// Create upload directory if not exists
$uploadDir = __DIR__ . '/nov_files/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Process form submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['submit_issuer'])) {
    // Handle nature of business
    $nature = ($_POST['nature_select'] === 'Others') 
        ? htmlspecialchars($_POST['nature_custom']) 
        : htmlspecialchars($_POST['nature_select']);
    $owner_rep = $_POST['owner_representative'] ?? 'Not specified';
    $establishment = htmlspecialchars($_POST['establishment']);
    $address = htmlspecialchars($_POST['address']);
    $products = htmlspecialchars($_POST['products']);
    $violations = implode(', ', $_POST['violations'] ?? []);
    $remarks = htmlspecialchars($_POST['remarks']);
    $notice_status = htmlspecialchars($_POST['notice_status'] ?? 'Not specified');

    // Validate custom nature input
    if ($_POST['nature_select'] === 'Others' && empty(trim($_POST['nature_custom']))) {
        $_SESSION['error'] = "Please specify the nature of business";
        header("Location: establishments.php");
        ob_end_flush();
        exit();
    } else {
        // Generate NOV file
        $filename = preg_replace('/[^A-Za-z0-9\-]/', '', $establishment) . '_' . time() . '.txt';
        $fileContent = "NOTICE OF VIOLATION\n\n";
        $fileContent .= "Establishment: $establishment\n";
        $fileContent .= "Address: $address\n";
        $fileContent .= "Nature of Business: $nature\n";
        $fileContent .= "Non-Conforming Products: $products\n";
        $fileContent .= "Violations: $violations\n";
        $fileContent .= "Notice Status: $notice_status\n";
        $fileContent .= "Remarks: $remarks\n";
        $fileContent .= "\nDate: " . date('Y-m-d H:i:s');

        file_put_contents($uploadDir . $filename, $fileContent);

        // Store details in session
        $_SESSION['nov_details'] = [
            'filename' => $filename,
            'establishment' => $establishment,
            'address' => $address,
            'nature' => $nature,
            'nature_select' => $_POST['nature_select'],
            'nature_custom' => $_POST['nature_custom'] ?? '',
            'owner_representative' => $owner_rep,
            'products' => $products,
            'violations' => $_POST['violations'] ?? [],
            'notice_status' => $notice_status, 
            'remarks' => $remarks
        ];

        // Take a snapshot of form data before redirecting
        $_SESSION['form_snapshot'] = $_POST;

        // Redirect to the same page to show the issuer modal
        header("Location: establishments.php?show_issuer_modal=1");
        ob_end_flush();
        exit();
    }
}

// In the section where you handle submit_issuer POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_issuer'])) {
    // Retrieve previously stored NOV details
    $novDetails = $_SESSION['nov_details'] ?? null;
    
    if ($novDetails) {
        $issuer_name = htmlspecialchars($_POST['issuer_name']);
        $issued_datetime = htmlspecialchars($_POST['issued_datetime']);
        
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Store in establishment table with enhanced error handling
            $stmt = $conn->prepare("INSERT INTO establishments 
                (name, address, owner_representative, nature, products, violations, notice_status, remarks, nov_files, issued_by, issued_datetime)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt === false) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $violations_str = is_array($novDetails['violations']) ? implode(', ', $novDetails['violations']) : $novDetails['violations'];
            
            $stmt->bind_param("sssssssssss", 
                $novDetails['establishment'],
                $novDetails['address'],
                $novDetails['owner_representative'],
                $novDetails['nature'],
                $novDetails['products'],
                $violations_str,
                $novDetails['notice_status'],
                $novDetails['remarks'],
                $novDetails['filename'],
                $issuer_name,
                $issued_datetime
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // Get the inserted establishment ID
            $establishment_id = $conn->insert_id;
            
            // Insert inventory products if available
            if (isset($novDetails['products']) && is_array($novDetails['products'])) {
                foreach ($novDetails['products'] as $product) {
                    $stmt_product = $conn->prepare("INSERT INTO inventory_products 
                        (establishment_id, product_name, sealed, withdrawn, description, price, pieces, 
                         dao_violation, other_violation, remarks)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    if ($stmt_product === false) {
                        throw new Exception("Prepare product statement failed: " . $conn->error);
                    }
                    
                    $sealed = isset($product['sealed']) ? 1 : 0;
                    $withdrawn = isset($product['withdrawn']) ? 1 : 0;
                    $dao_violation = isset($product['dao_violation']) ? 1 : 0;
                    $other_violation = isset($product['other_violation']) ? 1 : 0;
                    
                    $stmt_product->bind_param("issssiiiss", 
                        $establishment_id,
                        $product['name'],
                        $sealed,
                        $withdrawn,
                        $product['description'],
                        $product['price'],
                        $product['pieces'],
                        $dao_violation,
                        $other_violation,
                        $product['remarks']
                    );
                    
                    if (!$stmt_product->execute()) {
                        throw new Exception("Execute product insert failed: " . $stmt_product->error);
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();

            // Set success message
            $_SESSION['success'] = json_encode([
                'title' => 'Notice of Violation Saved',
                'text' => "NOV for {$novDetails['establishment']} has been successfully recorded.",
                'establishment' => $novDetails['establishment'],
                'issuer' => $issuer_name,
                'datetime' => $issued_datetime
            ]);
            
            // Clear the session
            unset($_SESSION['nov_details']);
            unset($_SESSION['form_data']);
            unset($_SESSION['form_snapshot']);
            
            // Redirect to establishments page
            header("Location: establishments.php");
            ob_end_flush();
            exit();
            
        } catch (Exception $e) {
            // Roll back transaction on error
            $conn->rollback();
            
            error_log("Database Error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to save Notice of Violation: " . $e->getMessage();
            header("Location: establishments.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Session data lost. Please complete the form again.";
        header("Location: establishments.php");
        exit();
    }
}
// Add this where you process the form data in establishments.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_to_violations'])) {
    // Store form data in session
    $_SESSION['form_data'] = [
        'establishment' => htmlspecialchars($_POST['establishment']),
        'owner_representative' => htmlspecialchars($_POST['owner_representative']),
        'address' => htmlspecialchars($_POST['address']),
        'nature_select' => htmlspecialchars($_POST['nature_select']),
        'nature_custom' => htmlspecialchars($_POST['nature_custom'] ?? ''),
        'nature' => ($_POST['nature_select'] === 'Others') 
            ? htmlspecialchars($_POST['nature_custom']) 
            : htmlspecialchars($_POST['nature_select']),
        'products' => htmlspecialchars($_POST['products'])
    ];
    
    // Redirect to violations page
    header("Location: violations.php");
    exit();
}

// Now include header and output HTML
include '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Establishment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
        }
        .nov-form { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
        }
        
        /* Enhanced Placeholder Styles */
        .form-control, .form-select {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .form-control::placeholder, .form-select {
            color: #6c757d;
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(16, 52, 108, 0.25);
            border-color: #10346C;
        }
        
        /* Placeholder Animation */
        @keyframes placeholderAnimation {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }
        
        .form-control:focus::placeholder, .form-select:focus {
            animation: placeholderAnimation 0.3s ease;
            color: #10346C;
            opacity: 0.8;
        }
        
        /* Floating Label Effect */
        .form-floating label {
            transition: all 0.3s ease;
            color: #6c757d;
        }
        
        .form-control:focus + label,
        .form-control:not(:placeholder-shown) + label {
            color: #10346C;
            transform: scale(0.85) translateY(-1.5rem) translateX(-0.15rem);
        }
        
        /* Responsive Design for Placeholders */
        @media (max-width: 768px) {
            .form-control::placeholder, .form-select {
                font-size: 0.9rem;
            }
        }
        
        /* Additional existing styles */
        .violation-list { list-style: none; padding-left: 0; }
        .file-link { color: #0d6efd; text-decoration: underline; }
        .custom-nature { 
            display: none; 
            margin-top: 0.5rem;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-section {
            padding: 1.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            background: white;
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            box-shadow: 0 4px 6px rgba(16, 52, 108, 0.1);
            transform: translateY(-5px);
        }
        
        .section-title {
            color: #10346C;
            border-bottom: 2px solid #10346C;
            padding-bottom: 0.5rem;
            font-weight: 600;
        }
        
        /* Form data snapshot styles */
        .form-snapshot {
            background-color: rgba(255, 255, 255, 0.95);
            border: 2px solid #10346C;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .snapshot-title {
            color: #10346C;
            font-weight: 600;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
        
        .snapshot-item {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .snapshot-label {
            font-weight: 600;
            color: #495057;
        }
        
        .snapshot-value {
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container py-5">
    <h2 class="mb-4">Notice Management</h2>
    
    <!-- NOV Submission Form -->
    <div class="nov-form mb-5 shadow p-4 bg-white">
        <form id="novForm" method="POST">
            <!-- Establishment Section -->
            <div class="form-section mb-4">
                <h5 class="section-title mb-3">Establishment Details</h5>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="owner_representative">Owner/Representative:</label>
                        <input type="text" class="form-control bg-light" id="owner_representative" 
                               name="owner_representative" required>
                    </div>
                    <div class="col-12">
                        <label for="establishment">Name of Establishment:</label>
                        <input type="text" name="establishment" class="form-control bg-light" 
                               id="establishment" required>
                    </div>
                    
                    <div>
                    </div>
                    <div class="col-md-4">
                        <label for="region">Region:</label>
                        <input type="text" name="region" id="region" class="form-control bg-light" required
                               oninvalid="this.setCustomValidity('Region is required')" 
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="col-md-4">
                        <label for="province">Province:</label>
                        <input type="text" name="province" id="province" class="form-control bg-light" required
                               oninvalid="this.setCustomValidity('Province is required')" 
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="col-md-4">
                        <label for="municipality">Municipality:</label>
                        <input type="text" name="municipality" id="municipality" class="form-control bg-light" required
                               oninvalid="this.setCustomValidity('Municipality is required')" 
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="col-md-6">
                        <label for="barangay">Barangay:</label>
                        <input type="text" name="barangay" id="barangay" class="form-control bg-light" required
                               oninvalid="this.setCustomValidity('Barangay is required')" 
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="col-md-6">
                        <label for="street">Street and house no.:</label>
                        <input type="text" name="street" id="street" class="form-control bg-light" required
                               oninvalid="this.setCustomValidity('Street and house no. is required')" 
                               oninput="this.setCustomValidity('')">
                    </div>
                    
                    <div class="col-12">
                        <label>Business Address:</label>
                        <div class="input-group">
                            <select name="nature_select" id="natureSelect" class="form-select bg-light" required>
                                <option value="">Select Nature of business</option>
                                <option value="Retail/Wholesaler">Retailer/Wholesaler</option>
                                <option value="Supermarket/Grocery/Convenience Store">Supermarket/Grocery/Convenience Store</option>
                                <option value="Service and Repair">Service and Repair</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Manufacturing">Manufacturing</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <input type="text" name="nature_custom" id="natureCustom" 
                               class="form-control mt-2 custom-nature bg-light" 
                               placeholder="Specify nature of business"
                               style="display: none;">
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="form-section mb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label>Non-Conforming Product and services:</label>
                        <input type="text" name="products" class="form-control bg-light" required>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <button type="button" class="btn btn-primary btn-lg" id="proceedToViolationsBtn">
                    Proceed
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Issuer Modal -->
    <?php if (isset($_GET['show_issuer_modal']) && $_GET['show_issuer_modal'] == 1): ?>
    <div class="modal-backdrop show"></div>
    <div class="modal fade show" id="issuerModal" tabindex="-1" role="dialog" style="display: block; padding-right: 17px;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Issuer Details</h5>
                </div>
                
                <!-- Show Form Snapshot Summary -->
                <?php if(isset($_SESSION['form_snapshot'])): ?>
                <div class="form-snapshot mx-3 mt-3">
                    <h6 class="snapshot-title">Form Details Summary</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="snapshot-item">
                                <span class="snapshot-label">Establishment:</span> 
                                <span class="snapshot-value"><?= htmlspecialchars($_SESSION['form_snapshot']['establishment'] ?? '') ?></span>
                            </div>
                            <div class="snapshot-item">
                                <span class="snapshot-label">Representative:</span> 
                                <span class="snapshot-value"><?= htmlspecialchars($_SESSION['form_snapshot']['owner_representative'] ?? '') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="snapshot-item">
                                <span class="snapshot-label">Nature:</span> 
                                <span class="snapshot-value">
                                    <?php 
                                    $nature = ($_SESSION['form_snapshot']['nature_select'] ?? '') === 'Others' 
                                        ? htmlspecialchars($_SESSION['form_snapshot']['nature_custom'] ?? '') 
                                        : htmlspecialchars($_SESSION['form_snapshot']['nature_select'] ?? '');
                                    echo $nature;
                                    ?>
                                </span>
                            </div>
                            <div class="snapshot-item">
                                <span class="snapshot-label">Violations:</span> 
                                <span class="snapshot-value">
                                    <?php 
                                    if(isset($_SESSION['form_snapshot']['violations']) && is_array($_SESSION['form_snapshot']['violations'])) {
                                        echo count($_SESSION['form_snapshot']['violations']);
                                    } else {
                                        echo "0";
                                    }
                                    ?> found
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="issuer_name" class="form-label">Issued By (Name)</label>
                            <input type="text" class="form-control" id="issuer_name" name="issuer_name" required 
                                   placeholder="Enter name of the person issuing the violation">
                        </div>
                        <div class="mb-3">
                            <label for="issued_datetime" class="form-label">Date and Time of Issuance</label>
                            <input type="text" class="form-control datepicker" id="issued_datetime" name="issued_datetime" 
                                   placeholder="Select date and time">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="establishments.php" class="btn btn-secondary me-2" id="backToFormBtn">
                            <i class="fas fa-arrow-left me-1"></i> Back to Form
                        </a>
                        <button type="submit" name="submit_issuer" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Issuer Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Received/Refused Modal -->
    <div class="modal fade" id="receivedRefusedModal" tabindex="-1" aria-labelledby="receivedRefusedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="receivedRefusedModalLabel">Notice Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="noticeStatusForm">
                    <div class="mb-4">
                        <label class="form-label">Notice Status:</label>
                        <div class="border p-3 rounded">
                            <div class="d-flex justify-content-around">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notice_status" id="statusReceived" value="Received" required>
                                    <label class="form-check-label" for="statusReceived">Received</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notice_status" id="statusRefused" value="Refused" required>
                                    <label class="form-check-label" for="statusRefused">Refused</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                     <!-- Received By fields - initially hidden -->
                     <div id="receivedByFields" style="display: none;">
                        <div class="mb-3">
                            <label for="received_by" class="form-label">Received By:</label>
                            <input type="text" class="form-control" id="received_by" name="received_by" placeholder="Enter name of receiver">
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label">Position:</label>
                            <input type="text" class="form-control" id="position" name="position" placeholder="Enter position of receiver">
                        </div>
                    </div>
                    
                    <!-- Refused/Witnessed By field - initially hidden -->
                    <div id="refusedByFields" style="display: none;">
                        <div class="mb-3">
                            <label for="witnessed_by" class="form-label">Witnessed By:</label>
                            <input type="text" class="form-control" id="witnessed_by" name="witnessed_by" placeholder="Enter name of witness">
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                        <button type="button" class="btn btn-primary" id="submitStatusBtn">Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Modal -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="inventoryModalLabel">Inventory of Non-Conforming Products</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inventoryForm" method="POST">
                    <div class="mb-3">
                        <h5>Inventory of Non-Conforming Products:</h5>
                        
                        <div id="productsContainer">
                            <!-- Product items will be added here dynamically -->
                            <div class="product-item border p-3 mb-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <label for="product_name">Product:</label>
                                        <input type="text" class="form-control" name="products[0][name]" required>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex mt-4">
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="checkbox" name="products[0][sealed]" value="1">
                                                <label class="form-check-label">Sealed</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="products[0][withdrawn]" value="1">
                                                <label class="form-check-label">Withdrawn</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <label for="brand_description">Brand Description:</label>
                                    <textarea class="form-control" name="products[0][description]" rows="3"></textarea>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label for="price">Price:</label>
                                        <input type="number" class="form-control" name="products[0][price]" step="0.01">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="pieces">No. of Pieces:</label>
                                        <input type="number" class="form-control" name="products[0][pieces]">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex mt-4">
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="checkbox" name="products[0][dao_violation]" value="1">
                                                <label class="form-check-label">Violation of DAO</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="products[0][other_violation]" value="1">
                                                <label class="form-check-label">Other Violation</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <label for="remarks">Product Remarks:</label>
                                    <input type="text" class="form-control" name="products[0][remarks]">
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" id="addProductBtn">Add Product</button>
                    </div>
                    
                    <!-- Hidden fields to carry over all previous form data -->
                    <input type="hidden" id="allFormData" name="all_form_data">
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" id="backToStatusBtn">Back</button>
                        <button type="button" class="btn btn-primary" id="submitInventoryBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



    <!-- Violations Modal -->
<div class="modal fade" id="violationsModal" tabindex="-1" aria-labelledby="violationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="violationsModalLabel">Notice Management</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="violationsForm" method="POST">
                    <h5>Violation Found:</h5>
                    
                    <!-- Product Standards Violation -->
                    <div class="violation-category mb-3">
                        <strong>Product Standards Violation:</strong>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No PS/ICC Mark" id="noMark">
                                    <label class="form-check-label" for="noMark">No PS/ICC Mark</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Invalid/suspended or cancelled BPS license or permit" id="invalidLicense">
                                    <label class="form-check-label" for="invalidLicense">Invalid/ suspended or cancelled BPS license or permit</label>
                                </div>
                            </div>
                        </div>
                    </div>


                    
                    <!-- DAO Violation -->
                    <div class="violation-category mb-3">
                        <strong>DAO Violation:</strong>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No Manufacturer's Name" id="noManufacturerName">
                                    <label class="form-check-label" for="noManufacturerName">No Manufacturer's Name</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No Manufacturer's Address" id="noManufacturerAddress">
                                    <label class="form-check-label" for="noManufacturerAddress">No Manufacturer's Address</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No Date Manufactured" id="noDateManufactured">
                                    <label class="form-check-label" for="noDateManufactured">No Date Manufactured</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No Country of Origin" id="noCountryOrigin">
                                    <label class="form-check-label" for="noCountryOrigin">No Country of Origin</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Others" id="othersDAO">
                                    <label class="form-check-label" for="othersDAO">Others</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Accreditation Violation -->
                    <div class="violation-category mb-3">
                        <strong>Accreditation Violation:</strong>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No Accreditation Certificate" id="noAccreditationCert">
                                    <label class="form-check-label" for="noAccreditationCert">No Accreditation Certificate</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Expired Accreditation Certificate" id="expiredAccreditationCert">
                                    <label class="form-check-label" for="expiredAccreditationCert">Expired Accreditation Certificate</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Failure to Display Accreditation in a Conspicuous Place" id="failureToDisplayAccreditation">
                                    <label class="form-check-label" for="failureToDisplayAccreditation">Failure to Display Accreditation in a Conspicuous Place</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Freight Forwarding Violation -->
                    <div class="violation-category mb-3">
                        <strong>Freight Forwarding Violation:</strong>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No Accreditation Certificate (FF)" id="noAccreditationCertFF">
                                    <label class="form-check-label" for="noAccreditationCertFF">No Accreditation Certificate</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Expired Accreditation Certificate (FF)" id="expiredAccreditationCertFF">
                                    <label class="form-check-label" for="expiredAccreditationCertFF">Expired Accreditation Certificate</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Failure to Display Accreditation in a Conspicuous Place (FF)" id="failureToDisplayAccreditationFF">
                                    <label class="form-check-label" for="failureToDisplayAccreditationFF">Failure to Display Accreditation in a Conspicuous Place</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Tag Violation -->
                    <div class="violation-category mb-3">
                        <strong>Price Tag Violation:</strong>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="No/Inappropriate Price Tag" id="noPriceTag">
                                    <label class="form-check-label" for="noPriceTag">No/Inappropriate Price Tag</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Violation -->
                    <div class="violation-category mb-3">
                        <strong>Price Violation:</strong>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Price grossly in excess of its true worth" id="excessivePrice">
                                    <label class="form-check-label" for="excessivePrice">Price grossly in excess of its true worth</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="violations[]" value="Price is beyond the Price Ceiling" id="beyondCeiling">
                                    <label class="form-check-label" for="beyondCeiling">Price is beyond the Price Ceiling</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden fields to carry over data -->
                    <input type="hidden" name="establishment" id="hiddenEstablishment">
                    <input type="hidden" name="owner_representative" id="hiddenOwnerRep">
                    <input type="hidden" name="address" id="hiddenAddress">
                    <input type="hidden" name="nature_select" id="hiddenNatureSelect">
                    <input type="hidden" name="nature_custom" id="hiddenNatureCustom">
                    <input type="hidden" name="products" id="hiddenProducts">
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="submitViolationsBtn">Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const statusReceived = document.getElementById('statusReceived');
    const statusRefused = document.getElementById('statusRefused');
    const receivedByFields = document.getElementById('receivedByFields');
    const refusedByFields = document.getElementById('refusedByFields');

if (statusReceived && statusRefused && receivedByFields && refusedByFields) {
    // Show/hide fields based on selection
    statusReceived.addEventListener('change', function() {
        if (this.checked) {
            receivedByFields.style.display = 'block';
            refusedByFields.style.display = 'none';
            
            // Set required attributes
            document.getElementById('received_by').setAttribute('required', 'required');
            document.getElementById('position').setAttribute('required', 'required');
            document.getElementById('witnessed_by').removeAttribute('required');
            
            // Clear refused fields
            document.getElementById('witnessed_by').value = '';
        }
    });
    
    statusRefused.addEventListener('change', function() {
        if (this.checked) {
            receivedByFields.style.display = 'none';
            refusedByFields.style.display = 'block';
            
            // Set required attributes
            document.getElementById('received_by').removeAttribute('required');
            document.getElementById('position').removeAttribute('required');
            document.getElementById('witnessed_by').setAttribute('required', 'required');
            
            // Clear received fields
            document.getElementById('received_by').value = '';
            document.getElementById('position').value = '';
        }
    });
}
            // Add this to your existing document.addEventListener('DOMContentLoaded', function() { ... }) block
            const proceedBtn = document.getElementById('proceedToViolationsBtn');
            if (proceedBtn) {
             proceedBtn.addEventListener('click', function(e) {
        // Validate required fields before proceeding
        const formElement = document.getElementById('novForm');
        const requiredFields = formElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (isValid) {
            // Capture form data
            const formData = new FormData(formElement);
              
                 // Populate the violations modal with form data
            document.getElementById('hiddenEstablishment').value = formData.get('establishment');
            document.getElementById('hiddenOwnerRep').value = formData.get('owner_representative');
            document.getElementById('hiddenAddress').value = formData.get('address');
            document.getElementById('hiddenNatureSelect').value = formData.get('nature_select');
            document.getElementById('hiddenNatureCustom').value = formData.get('nature_custom') || '';
            document.getElementById('hiddenProducts').value = formData.get('products');
                
                // Show the modal
                const violationsModal = new bootstrap.Modal(document.getElementById('violationsModal'));
                violationsModal.show();
            } else {
                Swal.fire({
                title: 'Missing Information',
                text: 'Please fill in all required fields before proceeding.',
                icon: 'warning',
                confirmButtonColor: '#10346C'
                });
            }
        });
    }

            
             // Handle the violations form submission
const submitViolationsBtn = document.getElementById('submitViolationsBtn');
if (submitViolationsBtn) {
    submitViolationsBtn.addEventListener('click', function() {
        // Get form data from the violations modal
        const violationsForm = document.getElementById('violationsForm');
        const formData = new FormData(violationsForm);
        
        // Check if at least one violation is checked
        const violations = formData.getAll('violations[]');
        if (violations.length === 0) {
            Swal.fire({
                title: 'No Violations Selected',
                text: 'Please select at least one violation before proceeding.',
                icon: 'warning',
                confirmButtonColor: '#10346C'
            });
            return;
        }
        
        // Store the violations form data in session storage
        sessionStorage.setItem('violationsFormData', JSON.stringify(Object.fromEntries(formData)));
        
        // Hide violations modal and show received/refused modal
        const violationsModal = bootstrap.Modal.getInstance(document.getElementById('violationsModal'));
        violationsModal.hide();
        
        // Show the received/refused modal
        const receivedRefusedModal = new bootstrap.Modal(document.getElementById('receivedRefusedModal'));
        receivedRefusedModal.show();
    });
}

// Modify the submitStatusBtn event listener to show inventory modal after status modal
const submitStatusBtn = document.getElementById('submitStatusBtn');
if (submitStatusBtn) {
    submitStatusBtn.addEventListener('click', function() {
        // Get form data
        const statusForm = document.getElementById('noticeStatusForm');
        const statusData = new FormData(statusForm);
        
        // Validate status selection
        if (!statusData.get('notice_status')) {
            Swal.fire({
                title: 'Status Required',
                text: 'Please select whether the notice was received or refused.',
                icon: 'warning',
                confirmButtonColor: '#10346C'
            });
            return;
        }
        
        // Additional validation based on selected status
        if (statusData.get('notice_status') === 'Received') {
            if (!statusData.get('received_by').trim()) {
                Swal.fire({
                    title: 'Receiver Name Required',
                    text: 'Please enter the name of the person who received the notice.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            if (!statusData.get('position').trim()) {
                Swal.fire({
                    title: 'Position Required',
                    text: 'Please enter the position of the person who received the notice.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
        } else if (statusData.get('notice_status') === 'Refused') {
            if (!statusData.get('witnessed_by').trim()) {
                Swal.fire({
                    title: 'Witness Name Required',
                    text: 'Please enter the name of the person who witnessed the refusal.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
        }
        
        // Retrieve the previously stored violations form data
        const violationsFormData = JSON.parse(sessionStorage.getItem('violationsFormData'));
        
        // Store status data in session storage
        const statusFormData = Object.fromEntries(statusData);
        sessionStorage.setItem('statusFormData', JSON.stringify(statusFormData));
        
        // Close status modal
        const statusModal = bootstrap.Modal.getInstance(document.getElementById('receivedRefusedModal'));
        statusModal.hide();
        
        // Show inventory modal
        const inventoryModal = new bootstrap.Modal(document.getElementById('inventoryModal'));
        inventoryModal.show();
    });
}

// Add Product Button Functionality
let productCounter = 1;

document.addEventListener('DOMContentLoaded', function() {
    const addProductBtn = document.getElementById('addProductBtn');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function() {
            const container = document.getElementById('productsContainer');
            const newProductItem = document.createElement('div');
            newProductItem.className = 'product-item border p-3 mb-3 rounded';
            newProductItem.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <h6>Product Item #${productCounter + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-product">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <div class="row mb-2">
                    <div class="col-md-8">
                        <label for="product_name">Product:</label>
                        <input type="text" class="form-control" name="products[${productCounter}][name]" required>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex mt-4">
                            <div class="form-check me-4">
                                <input class="form-check-input" type="checkbox" name="products[${productCounter}][sealed]" value="1">
                                <label class="form-check-label">Sealed</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="products[${productCounter}][withdrawn]" value="1">
                                <label class="form-check-label">Withdrawn</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-2">
                    <label for="brand_description">Brand Description:</label>
                    <textarea class="form-control" name="products[${productCounter}][description]" rows="3"></textarea>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="price">Price:</label>
                        <input type="number" class="form-control" name="products[${productCounter}][price]" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label for="pieces">No. of Pieces:</label>
                        <input type="number" class="form-control" name="products[${productCounter}][pieces]">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex mt-4">
                            <div class="form-check me-4">
                                <input class="form-check-input" type="checkbox" name="products[${productCounter}][dao_violation]" value="1">
                                <label class="form-check-label">Violation of DAO</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="products[${productCounter}][other_violation]" value="1">
                                <label class="form-check-label">Other Violation</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-2">
                    <label for="remarks">Product Remarks:</label>
                    <input type="text" class="form-control" name="products[${productCounter}][remarks]">
                </div>
            `;
            
            container.appendChild(newProductItem);
            productCounter++;
            
            // Add event listener to remove button
            const removeBtn = newProductItem.querySelector('.remove-product');
            removeBtn.addEventListener('click', function() {
                container.removeChild(newProductItem);
            });
        });
    }
    
    // Inventory form submission
    const submitInventoryBtn = document.getElementById('submitInventoryBtn');
    if (submitInventoryBtn) {
        submitInventoryBtn.addEventListener('click', function() {
            // Get all previous form data
            const violationsData = JSON.parse(sessionStorage.getItem('violationsFormData'));
            const statusData = JSON.parse(sessionStorage.getItem('statusFormData'));
            
            // Get inventory form data
            const inventoryForm = document.getElementById('inventoryForm');
            const inventoryFormData = new FormData(inventoryForm);
            const inventoryData = {};
            
            // Extract product data
            const products = [];
            const formEntries = Array.from(inventoryFormData.entries());
            
            // Group product entries
            formEntries.forEach(([key, value]) => {
                if (key.startsWith('products')) {
                    const matches = key.match(/products\[(\d+)\]\[([^\]]+)\]/);
                    if (matches) {
                        const index = parseInt(matches[1]);
                        const field = matches[2];
                        
                        if (!products[index]) {
                            products[index] = {};
                        }
                        
                        products[index][field] = value;
                    }
                }
            });
            
            // Filter out empty products
            const validProducts = products.filter(product => product && product.name);
            
            // Combine all form data
            const allData = {
                ...violationsData,
                ...statusData,
                products: validProducts
            };
            
            // Set the combined data to hidden field
            document.getElementById('allFormData').value = JSON.stringify(allData);
            
            // Submit to server
            fetch('set_inventory_session.php', {
                method: 'POST',
                body: new FormData(inventoryForm)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to show the issuer modal
                    window.location.href = 'establishments.php?show_issuer_modal=1';
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to save inventory data. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#10346C'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#10346C'
                });
            });
        });
    }
    
    // Back button in inventory modal
    const backToStatusBtn = document.getElementById('backToStatusBtn');
    if (backToStatusBtn) {
        backToStatusBtn.addEventListener('click', function() {
            // Hide inventory modal
            const inventoryModal = bootstrap.Modal.getInstance(document.getElementById('inventoryModal'));
            inventoryModal.hide();
            
            // Show status modal again
            const statusModal = new bootstrap.Modal(document.getElementById('receivedRefusedModal'));
            statusModal.show();
        });
    }
});
            // Form data capture before navigation
            const formElement = document.getElementById('novForm');
            const submitBtn = document.getElementById('submitFormBtn');
            
            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    // Capture form data before submission
                    captureFormData();
                });
            }
            
            // Function to capture form data
            function captureFormData() {
                const formData = new FormData(formElement);
                sessionStorage.setItem('novFormBackup', JSON.stringify(Object.fromEntries(formData)));
                
                // Optional: Take screenshot of form
                takeFormSnapshot();
            }
            
            // Function to take a visual snapshot of the form
            function takeFormSnapshot() {
                html2canvas(formElement).then(canvas => {
                    const dataURL = canvas.toDataURL('image/png');
                    sessionStorage.setItem('formSnapshot', dataURL);
                }).catch(err => {
                    console.error('Error taking form snapshot:', err);
                });
            }
            
            // Nature of Business Custom Input Toggle
            const natureSelect = document.getElementById('natureSelect');
            const customInput = document.getElementById('natureCustom');
            
            if (natureSelect) {
                natureSelect.addEventListener('change', function() {
                    if (this.value === 'Others') {
                        customInput.style.display = 'block';
                        customInput.required = true;
                        customInput.focus();
                    } else {
                        customInput.style.display = 'none';
                        customInput.required = false;
                        customInput.value = '';
                    }
                });
            }

            // Initialize date picker for issuer modal
            <?php if (isset($_GET['show_issuer_modal']) && $_GET['show_issuer_modal'] == 1): ?>
            flatpickr("#issued_datetime", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                defaultDate: new Date(),
                maxDate: new Date(), // Prevent future dates
                time_24hr: true,
                minuteIncrement: 1,
                allowInput: true,
                disableMobile: true, // Better experience on mobile
                // Ensure the time is always valid and not in the future
                onClose: function(selectedDates, dateStr, instance) {
                    const now = new Date();
                    const selected = selectedDates[0];
                    // If selected date is today, ensure time is not in the future
                    if (selected && selected.toDateString() === now.toDateString() && selected > now) {
                        // Reset to current time if future time on today was selected
                        instance.setDate(now);
                    }
                }
            });
            
            // Auto-focus on issuer name field when modal appears
            const issuerNameField = document.getElementById('issuer_name');
            if (issuerNameField) {
                issuerNameField.focus();
            }
                
            // Confirm before returning to form
            const backToFormBtn = document.getElementById('backToFormBtn');
            if (backToFormBtn) {
                backToFormBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Return to Form?',
                        text: 'Your current progress will be preserved.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10346C',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, go back',
                        cancelButtonText: 'Stay here'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'establishments.php';
                        }
                    });
                });
            }
            <?php endif; ?>

            // Show success message
            <?php if (isset($_SESSION['success'])): ?>
                <?php $successData = json_decode($_SESSION['success'], true); ?>
                Swal.fire({
                    title: '<?= $successData['title'] ?>',
                    html: `
                        <div class="text-start">
                            <p><strong>Establishment:</strong> <?= $successData['establishment'] ?></p>
                            <p><strong>Issued By:</strong> <?= $successData['issuer'] ?></p>
                            <p><strong>Issued On:</strong> <?= $successData['datetime'] ?></p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#10346C',
                    showCancelButton: true,
                    confirmButtonText: 'View Records',
                    cancelButtonText: 'Close'
                }).then((result) => {
                    // Clear sessionStorage on success
                    sessionStorage.removeItem('novFormData');
                    sessionStorage.removeItem('novFormBackup');
                    sessionStorage.removeItem('formSnapshot');
                    
                    if (result.isConfirmed) {
                        window.location.href = 'nov_form.php';
                    } else {
                        // Optional: Reset the form if user stays on page
                        document.getElementById('novForm').reset();
                    }
                });
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            // Show error message if exists
            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({
                    title: 'Error',
                    text: '<?= $_SESSION['error'] ?>',
                    icon: 'error',
                    confirmButtonColor: '#10346C'
                });
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            // Restore form data on page load from session backup
            const backupData = sessionStorage.getItem('novFormBackup');
            if (backupData && formElement) {
                try {
                    const parsedData = JSON.parse(backupData);
                    
                    // Populate regular inputs
                    for (const [key, value] of Object.entries(parsedData)) {
                        const field = formElement.elements[key];
                        if (field && field.type !== 'checkbox' && field.type !== 'radio') {
                            field.value = value;
                        } else if (field && field.type === 'checkbox') {
                            field.checked = parsedData[key] === 'on';
                        }
                    }
                } catch (error) {
                    console.error('Error restoring form data:', error);
                }
            }
        }); // Close the DOMContentLoaded event listener properly
    </script>
</body>
</html>