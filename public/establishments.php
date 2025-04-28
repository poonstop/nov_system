<?php
// Basic setup
ob_start();
session_start();
include __DIR__ . '/../connection.php';

// Simple error handler
function showError($message) {
    $_SESSION['error'] = $message;
    header("Location: establishments.php");
    exit();
}

// Create upload directory if needed
$uploadDir = __DIR__ . '/nov_files/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Process main form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle establishment form submission
    if (isset($_POST['submit_establishment'])) {
        // Validate required fields
        $requiredFields = ['establishment', 'owner_representative', 'street', 'barangay', 
                          'municipality', 'province', 'region', 'nature_select', 'products'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                showError("Missing required field: $field");
            }
        }
        
        // Process nature of business
        $nature = ($_POST['nature_select'] === 'Others') 
            ? trim($_POST['nature_custom']) 
            : $_POST['nature_select'];
        
        if ($_POST['nature_select'] === 'Others' && empty(trim($_POST['nature_custom']))) {
            showError("Please specify the nature of business");
        }
        
        // Save form data to session and proceed to violations
        $_SESSION['form_data'] = [
            'establishment' => $_POST['establishment'],
            'owner_representative' => $_POST['owner_representative'],
            'street' => $_POST['street'],
            'barangay' => $_POST['barangay'],
            'municipality' => $_POST['municipality'],
            'province' => $_POST['province'],
            'region' => $_POST['region'],
            'nature_select' => $_POST['nature_select'],
            'nature_custom' => $_POST['nature_custom'] ?? '',
            'nature' => $nature,
            'products' => $_POST['products']
        ];
        
        header("Location: establishments.php?show_violations_modal=1");
        exit();
    }
    
    // Handle violations form submission
    else if (isset($_POST['submit_violations'])) {
        if (!isset($_SESSION['form_data'])) {
            showError("Missing establishment data. Please complete the form again.");
        }
        
        $formData = $_SESSION['form_data'];
        $violations = isset($_POST['violations']) ? implode(', ', $_POST['violations']) : '';
        $remarks = $_POST['remarks'] ?? '';
        
        // Generate NOV file
        $filename = preg_replace('/[^A-Za-z0-9\-]/', '', $formData['establishment']) . '_' . time() . '.txt';
        $fileContent = "NOTICE OF VIOLATION\n\n" .
                      "Establishment: {$formData['establishment']}\n" .
                      "Address: {$formData['street']}, {$formData['barangay']}, {$formData['municipality']}, {$formData['province']}, {$formData['region']}\n" .
                      "Nature of Business: {$formData['nature']}\n" .
                      "Non-Conforming Products: {$formData['products']}\n" .
                      "Violations: $violations\n" .
                      "Remarks: $remarks\n\n" .
                      "Date: " . date('Y-m-d H:i:s');
                      
        file_put_contents($uploadDir . $filename, $fileContent);
        
        // Save session data for next step
        $_SESSION['nov_details'] = [
            'filename' => $filename,
            'establishment' => $formData['establishment'],
            'street' => $formData['street'],
            'barangay' => $formData['barangay'],
            'municipality' => $formData['municipality'],
            'province' => $formData['province'],
            'region' => $formData['region'],
            'nature' => $formData['nature'],
            'owner_representative' => $formData['owner_representative'],
            'products' => $formData['products'],
            'violations' => $violations,
            'remarks' => $remarks
        ];
        
        // Proceed to inventory or notice status
        header("Location: establishments.php?show_inventory_modal=1");
        exit();
    }
    
    // Handle inventory submission
    else if (isset($_POST['save_inventory']) || isset($_POST['skip_inventory'])) {
        if (isset($_POST['products']) && is_array($_POST['products']) && !isset($_POST['skip_inventory'])) {
            $validProducts = false;
            
            // Check if at least one product has a name
            foreach ($_POST['products'] as $product) {
                if (!empty($product['name'])) {
                    $validProducts = true;
                    break;
                }
            }
            
            if (!$validProducts) {
                showError("Please add at least one valid product with a name.");
            }
            
            // Store products
            $sanitizedProducts = [];
            foreach ($_POST['products'] as $index => $product) {
                if (!empty($product['name'])) {
                    $sanitizedProducts[$index] = [
                        'name' => $product['name'],
                        'description' => $product['description'] ?? '',
                        'price' => filter_var($product['price'] ?? 0, FILTER_VALIDATE_FLOAT),
                        'pieces' => filter_var($product['pieces'] ?? 0, FILTER_VALIDATE_INT),
                        'sealed' => isset($product['sealed']) ? 1 : 0,
                        'withdrawn' => isset($product['withdrawn']) ? 1 : 0,
                        'dao_violation' => isset($product['dao_violation']) ? 1 : 0,
                        'other_violation' => isset($product['other_violation']) ? 1 : 0,
                        'remarks' => $product['remarks'] ?? ''
                    ];
                }
            }
            
            $_SESSION['inventory'] = $sanitizedProducts;
        } else {
            // Skip inventory or no products added
            $_SESSION['inventory'] = [];
        }
        
        header("Location: establishments.php?show_status_modal=1");
        exit();
    }
    
    // Handle final notice status submission
    else if (isset($_POST['submit_status'])) {
        $novDetails = $_SESSION['nov_details'] ?? null;
        
        if (!$novDetails) {
            showError("Missing establishment data. Please complete the form again.");
        }
        
        // Process status data
        $notice_status = $_POST['notice_status'] ?? 'Not specified';
        $issuer_name = $_POST['issued_by'] ?? '';
        $position = $_POST['position'] ?? '';
        $issued_datetime = $_POST['issued_datetime'] ?? date('Y-m-d H:i:s');
        $witnessed_by = $_POST['witnessed_by'] ?? '';
        $remarks = $_POST['remarks'] ?? $novDetails['remarks'];

        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // 1. Insert establishment first (as the main table)
            $stmt_establishment = $conn->prepare("
                INSERT INTO establishments (
                    name, 
                    owner_representative, 
                    nature, 
                    products, 
                    violations, 
                    notice_status, 
                    remarks, 
                    nov_files, 
                    issued_by, 
                    issued_datetime,
                    created_at
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt_establishment->bind_param("ssssssssss", 
                $novDetails['establishment'],
                $novDetails['owner_representative'],
                $novDetails['nature'],
                $novDetails['products'],
                $novDetails['violations'],
                $notice_status,
                $remarks,
                $novDetails['filename'],
                $issuer_name,
                $issued_datetime
            );
            $stmt_establishment->execute();
            $establishment_id = $conn->insert_id;
            
            // 2. Insert address with foreign key to establishment
            $stmt_address = $conn->prepare("
                INSERT INTO addresses (
                    establishment_id,
                    street, 
                    barangay, 
                    municipality, 
                    province, 
                    region
                ) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $street = $novDetails['street'];
            $barangay = $novDetails['barangay'];
            $municipality = $novDetails['municipality'];
            $province = $novDetails['province'];
            $region = $novDetails['region'];
            
            $stmt_address->bind_param("isssss", 
                $establishment_id,
                $street,
                $barangay,
                $municipality,
                $province,
                $region
            );
            $stmt_address->execute();
            
            // 3. Insert notice status with foreign key to establishment
            $stmt_status = $conn->prepare("
                INSERT INTO notice_status (
                    establishment_id, 
                    status, 
                    issued_by, 
                    position, 
                    issued_datetime, 
                    witnessed_by
                ) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            // Fix: Ensure all variables are properly defined as variables
            $status = $notice_status;
            $issuer = $issuer_name;
            $pos = $position;
            $datetime = $issued_datetime;
            $witness = $witnessed_by;
            
            $stmt_status->bind_param("isssss", 
                $establishment_id,
                $status,
                $issuer,
                $pos,
                $datetime,
                $witness
            );
            $stmt_status->execute();
            
            // 4. Insert inventory products if available
            if (isset($_SESSION['inventory']) && is_array($_SESSION['inventory']) && !empty($_SESSION['inventory'])) {
                foreach ($_SESSION['inventory'] as $product) {
                    if (empty($product['name'])) continue;
                    
                    $stmt_product = $conn->prepare("
                        INSERT INTO inventory (
                            establishment_id, 
                            product_name, 
                            sealed, 
                            withdrawn, 
                            description, 
                            price, 
                            pieces, 
                            dao_violation, 
                            other_violation, 
                            inv_remarks
                        ) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    // Fix: Use variables for all bind_param parameters
                    $prod_name = $product['name'];
                    $sealed = $product['sealed'];
                    $withdrawn = $product['withdrawn'];
                    $description = $product['description'] ?? '';
                    $price = $product['price'];
                    $pieces = $product['pieces'];
                    $dao_violation = $product['dao_violation'];
                    $other_violation = $product['other_violation'];
                    $inv_remarks = $product['remarks'] ?? '';
                    
                    $stmt_product->bind_param("isiiisdiss", 
                        $establishment_id,
                        $prod_name,
                        $sealed,
                        $withdrawn,
                        $description,
                        $price,
                        $pieces,
                        $dao_violation,
                        $other_violation,
                        $inv_remarks
                    );
                    $stmt_product->execute();
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            // Success message
            $_SESSION['success'] = json_encode([
                'title' => 'Notice of Violation Saved',
                'text' => "NOV for {$novDetails['establishment']} has been successfully recorded.",
                'establishment' => $novDetails['establishment'],
                'issuer' => $issuer_name,
                'datetime' => $issued_datetime
            ]);
            
            // Clear session data
            unset($_SESSION['nov_details'], $_SESSION['form_data'], $_SESSION['inventory']);
            
            header("Location: establishments.php?success=1");
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $_SESSION['error'] = "Failed to save Notice of Violation: " . $e->getMessage();
            header("Location: establishments.php");
            exit();
        }
    }
}

// Include header
include '../templates/header.php';

// Add this section to display success/error messages
$errorMsg = $_SESSION['error'] ?? null;
$successMsg = $_SESSION['success'] ?? null;

// Clear messages after displaying
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Establishment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/nov-styles.css">
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">Notice Management</h2>
        
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        
        <?php if ($successMsg): ?>
            <div id="success-message" style="display: none;"><?php echo $successMsg; ?></div>
        <?php endif; ?>
        
        <!-- NOV Submission Form -->
        <div class="nov-form mb-5 shadow p-4 bg-white">
            <form id="novForm" method="POST">
                <!-- Establishment Details -->
                <div class="form-section mb-4">
                    <h5 class="section-title mb-3">Establishment Details</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="owner_representative">Owner/Representative:</label>
                            <input type="text" class="form-control" id="owner_representative" name="owner_representative" required>
                        </div>
                        <div class="col-12">
                            <label for="establishment">Name of Establishment:</label>
                            <input type="text" name="establishment" class="form-control" id="establishment" required>
                        </div>
                        
                        <!-- Address Fields -->
                        <div class="col-md-4">
                            <label for="region">Region:</label>
                            <input type="text" name="region" id="region" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="province">Province:</label>
                            <input type="text" name="province" id="province" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="municipality">Municipality:</label>
                            <input type="text" name="municipality" id="municipality" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="barangay">Barangay:</label>
                            <input type="text" name="barangay" id="barangay" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="street">Street and house no.:</label>
                            <input type="text" name="street" id="street" class="form-control" required>
                        </div>
                        
                        <!-- Nature of Business -->
                        <div class="col-12">
                            <label for="nature_select">Nature of business:</label>
                            <select name="nature_select" id="nature_select" class="form-select" required>
                                <option value="">Select Nature of business</option>
                                <option value="Retail/Wholesaler">Retailer/Wholesaler</option>
                                <option value="Supermarket/Grocery/Convenience Store">Supermarket/Grocery/Convenience Store</option>
                                <option value="Service and Repair">Service and Repair</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Manufacturing">Manufacturing</option>
                                <option value="Others">Others</option>
                            </select>
                            <input type="text" name="nature_custom" id="nature_custom" class="form-control mt-2" 
                                placeholder="Specify nature of business" style="display: none;">
                        </div>
                        
                        <div class="col-12">
                            <label for="products">Non-Conforming Product and services:</label>
                            <input type="text" name="products" id="products" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary" name="submit_establishment">Proceed</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Violations Modal -->
    <div class="modal fade" id="violationsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Select Violations</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <h5>Violations Found:</h5>
                        
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
                                        <input class="form-check-input" type="checkbox" name="violations[]" value="Invalid/suspended or cancelled BPS license" id="invalidLicense">
                                        <label class="form-check-label" for="invalidLicense">Invalid/suspended/cancelled BPS license</label>
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
                                        <input class="form-check-input" type="checkbox" name="violations[]" value="No Batch Number/Lot Code" id="noBatchNumber">
                                        <label class="form-check-label" for="noBatchNumber">No Batch Number/Lot Code</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" value="No Product Contents/Ingredients" id="noContents">
                                        <label class="form-check-label" for="noContents">No Product Contents/Ingredients</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Remarks field -->
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks:</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                            <button type="submit" class="btn btn-primary" name="submit_violations">Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Modal -->
    <div class="modal fade" id="inventoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Inventory of Non-Conforming Products</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div id="productsContainer">
                            <!-- Product template -->
                            <div class="product-item border p-3 mb-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <label for="product_name_0">Product:</label>
                                        <input type="text" class="form-control" id="product_name_0" name="products[0][name]">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex mt-4">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" id="product_sealed_0" name="products[0][sealed]" value="1">
                                                <label class="form-check-label" for="product_sealed_0">Sealed</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="product_withdrawn_0" name="products[0][withdrawn]" value="1">
                                                <label class="form-check-label" for="product_withdrawn_0">Withdrawn</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <label for="product_description_0">Description:</label>
                                    <textarea class="form-control" id="product_description_0" name="products[0][description]" rows="2"></textarea>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label for="product_price_0">Price:</label>
                                        <input type="number" class="form-control" id="product_price_0" name="products[0][price]" step="0.01">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="product_pieces_0">No. of Pieces:</label>
                                        <input type="number" class="form-control" id="product_pieces_0" name="products[0][pieces]">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex mt-4">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" id="product_dao_0" name="products[0][dao_violation]" value="1">
                                                <label class="form-check-label" for="product_dao_0">DAO</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="product_other_0" name="products[0][other_violation]" value="1">
                                                <label class="form-check-label" for="product_other_0">Other</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <label for="product_remarks_0">Remarks:</label>
                                    <input type="text" class="form-control" id="product_remarks_0" name="products[0][remarks]">
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary" id="addProductBtn">
                                <i class="bi bi-plus-circle"></i> Add Another Product
                            </button>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                            <button type="submit" class="btn btn-outline-primary" name="skip_inventory">Skip</button>
                            <button type="submit" class="btn btn-primary" name="save_inventory">Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Notice Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Notice Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Notice Status:</label>
                        <div class="border p-3 rounded">
                            <div class="d-flex justify-content-around">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input status-radio" type="radio" name="notice_status" id="statusReceived" value="Received" required>
                                    <label class="form-check-label" for="statusReceived">Received</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input status-radio" type="radio" name="notice_status" id="statusRefused" value="Refused" required>
                                    <label class="form-check-label" for="statusRefused">Refused</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="issued_datetime" class="form-label">Date Issued:</label>
                        <input type="text" class="form-control datepicker" id="issued_datetime" name="issued_datetime" value="<?= date('Y-m-d H:i:s') ?>" required>
                    </div>
                    
                    <!-- Fields shown only when "Received" is selected -->
                    <div class="received-only-fields">
                        <div class="mb-3">
                            <label for="issued_by" class="form-label">Issued By:</label>
                            <input type="text" class="form-control" id="issued_by" name="issued_by">
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Position:</label>
                            <input type="text" class="form-control" id="position" name="position">
                        </div>
                    </div>
                    
                    <!-- Fields shown only when "Refused" is selected -->
                    <div class="refused-only-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="witnessed_by" class="form-label">Witnessed By:</label>
                            <input type="text" class="form-control" id="witnessed_by" name="witnessed_by">
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                        <button type="submit" class="btn btn-primary" name="submit_status">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="js/form_handler_establishments.js"></script>
    <script>