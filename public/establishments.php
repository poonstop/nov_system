<?php
    // Start output buffering at the VERY TOP
    ob_start();
    session_start();
    include __DIR__ . '/../db_config.php';

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
        $region = htmlspecialchars($_POST['region']);
        $province = htmlspecialchars($_POST['province']);
        $municipality = htmlspecialchars($_POST['municipality']);
        $barangay = htmlspecialchars($_POST['barangay']);
        $street = htmlspecialchars($_POST['street']);

        $address = "$street, Brgy. $barangay, $municipality, $province, $region";

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

    // Process issuer form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_issuer'])) {
        // Debug logging
        error_log("Received form data: " . print_r($_POST, true));
        
        // Retrieve previously stored NOV details
        $novDetails = $_SESSION['nov_details'] ?? null;
        
        if (!$novDetails) {
            // If no session data, try to get directly from POST
            $novDetails = [
                'establishment' => $_POST['establishment'] ?? '',
                'address' => $_POST['address'] ?? '',
                'owner_representative' => $_POST['owner_representative'] ?? '',
                'nature' => $_POST['nature'] ?? '',
                'products' => $_POST['products'] ?? '',
                'violations' => $_POST['violations'] ?? [],
                'filename' => $_POST['filename'] ?? '' // Add this line
            ];
            
            // If still empty, report error
            if (empty($novDetails['establishment'])) {
                $_SESSION['error'] = "Missing establishment data. Please complete the form again.";
                header("Location: establishments.php");
                exit();
            }
        }
        
        // Process and sanitize input data
        // FIX 1: Check if 'issued_by' exists in $_POST, otherwise use a default value
        $issuer_name = !empty($_POST['issued_by']) ? htmlspecialchars($_POST['issued_by']) : '';
        $position = !empty($_POST['position']) ? htmlspecialchars($_POST['position']) : '';
        $issued_datetime = !empty($_POST['issued_datetime']) ? htmlspecialchars($_POST['issued_datetime']) : date('Y-m-d H:i:s');
        $notice_status = !empty($_POST['notice_status']) ? htmlspecialchars($_POST['notice_status']) : ($novDetails['notice_status'] ?? 'Not specified');
        $witnessed_by = !empty($_POST['witnessed_by']) ? htmlspecialchars($_POST['witnessed_by']) : '';
        $remarks = !empty($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : ($novDetails['remarks'] ?? '');

        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Format violations data
            $violations_str = is_array($novDetails['violations']) ? implode(', ', $novDetails['violations']) : $novDetails['violations'];
            
            // Store in establishment table
            $stmt = $conn->prepare("INSERT INTO establishments 
                (name, address, owner_representative, nature, products, violations, notice_status, remarks, nov_files, issued_by, issued_datetime)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt === false) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            // Make sure all variables are properly defined before binding
            $establishment = $novDetails['establishment'];
            $address = $novDetails['address'];
            $owner_rep = $novDetails['owner_representative'];
            $nature = $novDetails['nature'];
            $products = $novDetails['products'];
            $filename = $novDetails['filename'] ?? '';
            
            $stmt->bind_param("sssssssssss", 
                $establishment,
                $address,
                $owner_rep,
                $nature,
                $products,
                $violations_str,
                $notice_status,
                $remarks,
                $filename,
                $issuer_name,
                $issued_datetime
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // Get the inserted establishment ID
            $establishment_id = $conn->insert_id;
            
            // Insert notice status details
            $stmt_status = $conn->prepare("INSERT INTO notice_stat 
                (id, status, issued_by, position, issued_datetime, witnessed_by, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
                
            if ($stmt_status === false) {
                throw new Exception("Prepare status statement failed: " . $conn->error);
            }
            
            $stmt_status->bind_param("issssss", 
                $establishment_id,
                $notice_status,
                $issuer_name,
                $position,
                $issued_datetime,
                $witnessed_by,
                $remarks
            );
            
            if (!$stmt_status->execute()) {
                throw new Exception("Execute status insert failed: " . $stmt_status->error);
            }
            
            // Insert inventory products if available
            if (isset($_SESSION['inventory']) && is_array($_SESSION['inventory'])) {
                foreach ($_SESSION['inventory'] as $product) {
                    $stmt_product = $conn->prepare("INSERT INTO inventory 
                        (id, product_name, sealed, withdrawn, description, price, pieces, 
                        dao_violation, other_violation, remarks)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    if ($stmt_product === false) {
                        throw new Exception("Prepare product statement failed: " . $conn->error);
                    }
                    
                    $sealed = isset($product['sealed']) ? 1 : 0;
                    $withdrawn = isset($product['withdrawn']) ? 1 : 0;
                    $dao_violation = isset($product['dao_violation']) ? 1 : 0;
                    $other_violation = isset($product['other_violation']) ? 1 : 0;
                    
                    // FIX 3: Create temporary variables for all values to ensure they can be passed by reference
                    $product_name = $product['name'] ?? '';
                    $description = $product['description'] ?? '';
                    $price = $product['price'] ?? 0;
                    $pieces = $product['pieces'] ?? 0;
                    $product_remarks = $product['remarks'] ?? '';
                    
                    $stmt_product->bind_param("isssdiiiss", 
                        $establishment_id,
                        $product_name,
                        $sealed,
                        $withdrawn,
                        $description,
                        $price,
                        $pieces,
                        $dao_violation,
                        $other_violation,
                        $product_remarks
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
            unset($_SESSION['inventory_products']);
            unset($_SESSION['inventory']);  // Added this to ensure complete cleanup
            
            // Redirect to establishments page
            header("Location: establishments.php?success=1");
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
    }

    // Process inventory form submission 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_inventory'])) {
        // Store inventory products in session
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            $_SESSION['inventory'] = $_POST['products'];
            
            // Redirect to violations page or show status modal
            header("Location: establishments.php?show_status_modal=1");
            exit();
        } else {
            $_SESSION['error'] = "No products were submitted. Please add at least one product.";
            header("Location: establishments.php");
            exit();
        }
    }

    // Process proceed to violations
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
                <link rel="stylesheet" href="css/nov-styles.css">
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

                                
                                <div class="mb-3">
                                    <label for="issued_datetime" class="form-label">Date Issued:</label>
                                        <input type="text" class="form-control" id="issued_datetime" name="issued_datetime" 
                                            placeholder="" required>
                                </div>
                                
                                <!-- Received By fields - initially hidden -->
                                <div id="receivedByFields" style="display: none;">
                                    <div class="mb-3">
                                    <label for="received_by" class="form-label">Issued By:</label>
                                    <input type="text" class="form-control" id="received_by" name="issued_by" placeholder="Enter name of issuer">
                                    </div>
                                    <div class="mb-3">
                                        <label for="position" class="form-label">Position:</label>
                                        <input type="text" class="form-control" id="position" name="position" placeholder="Enter position of issuer">
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
                                    <button type="button" class="btn btn-primary" id="submitStatusBtn">Save</button>
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
                            <!-- Hidden field for establishment data -->
                            <input type="hidden" name="establishment" value="">
                            <input type="hidden" name="owner_representative" value="">
                            <input type="hidden" name="address" value="">
                            <input type="hidden" name="nature_select" value="">
                            <input type="hidden" name="nature_custom" value="">
                            <input type="hidden" name="products" value="">
                            
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

                                <!-- Add Button to add more products -->
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="addProductBtn">
                                        <i class="bi bi-plus-circle"></i> Add Another Product
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Add Back and Save buttons -->
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-secondary" id="backFromInventoryBtn">Back</button>
                                <button type="button" class="btn btn-outline-primary" id="skipInventoryBtn">Skip</button>
                                <button type="button" class="btn btn-primary" id="saveInventoryBtn">Save Products</button>
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

                    <!-- Issuer Modal -->   
    <div class="modal fade" id="issuerModal" tabindex="-1" aria-labelledby="issuerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="issuerModalLabel">Notice Issuer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="issuerForm" method="POST">
                        <input type="hidden" name="submit_issuer" value="1">
                        <input type="hidden" name="notice_status" id="issuerNoticeStatus">
                        <input type="hidden" name="witnessed_by" id="issuerWitnessedBy">
                        
                        <div class="mb-3">
                            <label for="issued_by" class="form-label">Issued By:</label>
                            <input type="text" class="form-control" id="issued_by" name="issued_by" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="issuer_position" class="form-label">Position:</label>
                            <input type="text" class="form-control" id="issuer_position" name="position">
                        </div>
                        
                        <div class="mb-3">
                            <label for="issuer_datetime" class="form-label">Date and Time:</label>
                            <input type="text" class="form-control" id="issuer_datetime" name="issued_datetime" required>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Notice</button>
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
            <script src="js/form_handler_establishments.js"></script>
            </body>
            </html>