<?php
// Include database connection
require_once '../connection.php';

// Initialize response array
$response = array('status' => 'error', 'message' => '');
$redirect_to_form = true;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if form data is submitted
    if (isset($_POST['form_submit']) && $_POST['form_submit'] === 'save_notice') {
        try {
            // Start transaction
            $conn->beginTransaction();

            // 1. Process Establishment Details
            $name = $_POST['name'] ?? '';
            $owner_representative = $_POST['owner_representative'] ?? '';
            $nature = $_POST['nature'] ?? '';
            $products = $_POST['products'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            $issue_date = $_POST['issue_date'] ?? date('Y-m-d');
            
            // Calculate expiry date (48 hours from issue date)
            $expiry_date = date('Y-m-d H:i:s', strtotime($issue_date . ' + 48 hours'));
            
            // Get violations as JSON array
            $violations = isset($_POST['violations']) ? json_encode($_POST['violations']) : '[]';
            $num_violations = isset($_POST['violations']) ? count($_POST['violations']) : 0;
            
            // Insert establishment record
            $stmt = $conn->prepare("INSERT INTO establishments 
                (name, owner_representative, nature, products, violations, remarks, issued_datetime, expiry_date, num_violations) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            $stmt->execute([
                $name, 
                $owner_representative, 
                $nature, 
                $products, 
                $violations, 
                $remarks, 
                $issue_date . ' 00:00:00', 
                $expiry_date,
                $num_violations
            ]);
            
            // Get the establishment ID
            $establishment_id = $conn->lastInsertId();
            
            // 2. Process Address
            $region = $_POST['region'] ?? '';
            $province = $_POST['province'] ?? '';
            $municipality = $_POST['municipality'] ?? '';
            $barangay = $_POST['barangay'] ?? '';
            $street = $_POST['street'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO addresses 
                (establishment_id, street, barangay, municipality, province, region) 
                VALUES (?, ?, ?, ?, ?, ?)");
                
            $stmt->execute([
                $establishment_id,
                $street,
                $barangay,
                $municipality,
                $province,
                $region
            ]);
            
            // 3. Process Inventory (only if not skipped)
            if (isset($_POST['inventory_skipped']) && $_POST['inventory_skipped'] !== 'true') {
                // Check if inventory data exists
                if (isset($_POST['product_name']) && is_array($_POST['product_name'])) {
                    $product_names = $_POST['product_name'];
                    $sealed_items = $_POST['sealed'] ?? [];
                    $withdrawn_items = $_POST['withdrawn'] ?? [];
                    $descriptions = $_POST['description'] ?? [];
                    $prices = $_POST['price'] ?? [];
                    $pieces = $_POST['pieces'] ?? [];
                    $dao_violations = $_POST['dao_violation'] ?? [];
                    $other_violations = $_POST['other_violation'] ?? [];
                    $product_remarks = $_POST['product_remarks'] ?? [];
                    
                    // Inventory remarks
                    $inv_remarks = [];
                    if (isset($_POST['sealed_products_left']) && $_POST['sealed_products_left'] == '1') {
                        $inv_remarks[] = "The sealed products were left";
                    }
                    if (isset($_POST['withdrawn_products_to_dti']) && $_POST['withdrawn_products_to_dti'] == '1') {
                        $inv_remarks[] = "The withdrawn products were brought to the DTI";
                    }
                    $inv_remarks_text = implode(", ", $inv_remarks);
                    
                    // Insert each inventory item
                    $stmt = $conn->prepare("INSERT INTO inventory 
                        (establishment_id, product_name, sealed, withdrawn, description, price, pieces, 
                        dao_violation, other_violation, product_remarks, inv_remarks) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    foreach ($product_names as $index => $product_name) {
                        if (!empty($product_name)) {
                            $sealed = isset($sealed_items[$index]) ? 1 : 0;
                            $withdrawn = isset($withdrawn_items[$index]) ? 1 : 0;
                            $dao_violation = isset($dao_violations[$index]) ? 1 : 0;
                            $other_violation = isset($other_violations[$index]) ? 1 : 0;
                            
                            $stmt->execute([
                                $establishment_id,
                                $product_name,
                                $sealed,
                                $withdrawn,
                                $descriptions[$index] ?? '',
                                $prices[$index] ?? 0,
                                $pieces[$index] ?? 0,
                                $dao_violation,
                                $other_violation,
                                $product_remarks[$index] ?? '',
                                $inv_remarks_text
                            ]);
                        }
                    }
                }
            }
            
            // 4. Process Issuers
            if (isset($_POST['issuer_name']) && is_array($_POST['issuer_name'])) {
                $issuer_names = $_POST['issuer_name'];
                $issuer_positions = $_POST['issuer_position'] ?? [];
                
                $stmt = $conn->prepare("INSERT INTO notice_issuers 
                    (establishment_id, issuer_name, issuer_position) 
                    VALUES (?, ?, ?)");
                
                foreach ($issuer_names as $index => $issuer_name) {
                    if (!empty($issuer_name)) {
                        $stmt->execute([
                            $establishment_id,
                            $issuer_name,
                            $issuer_positions[$index] ?? ''
                        ]);
                    }
                }
            }
            
            // 5. Process Notice Status
            $notice_status = $_POST['notice_status'] ?? 'Received';
            $witnessed_by = ($notice_status === 'Refused') ? ($_POST['witnessed_by'] ?? '') : null;
            
            $stmt = $conn->prepare("INSERT INTO notice_status 
                (establishment_id, status, witnessed_by) 
                VALUES (?, ?, ?)");
                
            $stmt->execute([
                $establishment_id,
                $notice_status,
                $witnessed_by
            ]);
            
            // Update notice status in establishments table
            $stmt = $conn->prepare("UPDATE establishments SET notice_status = ? WHERE establishment_id = ?");
            $stmt->execute([$notice_status, $establishment_id]);
            
            // Commit transaction
            $conn->commit();
            
            // Set success response
            $response['status'] = 'success';
            $response['message'] = 'Notice record successfully saved!';
            $response['establishment_id'] = $establishment_id;
            
            // For AJAX requests, we'll output JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            // For regular form submissions, we'll redirect to avoid resubmission
            $redirect_to_form = false;
            
        } catch (PDOException $e) {
            // Rollback transaction
            $conn->rollBack();
            
            // Set error response
            $response['status'] = 'error';
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            
            // For AJAX requests, we'll output JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        }
    }
}

// Only include the HTML if we're not responding to an AJAX request
if ($redirect_to_form) {
    include '../templates/header.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .modal-lg {
            max-width: 900px;
        }
        .modal-xl {
            max-width: 1140px;
        }
        .violation-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .btn-proceed {
            background-color: #007bff;
            color: white;
        }
        .btn-skip {
            background-color: #6c757d;
            color: white;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Notice Management</h1>
        
        <!-- Establishment Details Form -->
        <form id="establishmentForm" class="form-container">
            <h3>Establishment Details</h3>
            
            <div class="mb-3">
                <label for="owner_representative" class="form-label">Owner/Representative:</label>
                <input type="text" class="form-control" id="owner_representative" name="owner_representative" required>
            </div>
            
            <div class="mb-3">
                <label for="name" class="form-label">Name of Establishment:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
           <!-- Replace the existing address input fields in the form with these dynamic dropdowns -->

<div class="row mb-3">
    <div class="col-md-6">
        <label for="region" class="form-label">Region:</label>
        <select class="form-select" id="region" name="region" required>
            <option value="">Select region</option>
            <!-- Will be populated by JavaScript -->
        </select>
    </div>
    <div class="col-md-6">
        <label for="province" class="form-label">Province:</label>
        <select class="form-select" id="province" name="province" required>
            <option value="">Select province</option>
            <!-- Will be populated based on region selection -->
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="municipality" class="form-label">Municipality:</label>
        <select class="form-select" id="municipality" name="municipality" required>
            <option value="">Select municipality</option>
            <!-- Will be populated based on province selection -->
        </select>
    </div>
    <div class="col-md-6">
        <label for="barangay" class="form-label">Barangay:</label>
        <select class="form-select" id="barangay" name="barangay" required>
            <option value="">Select barangay</option>
            <!-- Will be populated based on municipality selection -->
        </select>
    </div>
</div>

<div class="mb-3">
    <label for="street" class="form-label">Street and house no.:</label>
    <input type="text" class="form-control" id="street" name="street" required>
</div>  
            
            <div class="mb-3">
                <label for="nature" class="form-label">Nature of business:</label>
                <select class="form-select" id="nature" name="nature" required>
                    <option value="">Select business nature</option>
                    <option value="Retail">Retail</option>
                    <option value="Wholesale">Wholesale</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Service and Repair">Service and Repair</option>
                    <option value="Food Service">Food Service</option>
                    <option value="Freight Forwarding">Freight Forwarding</option>
                    <option value="Others">Others</option>
                </select>
                <div class="mb-3" id="other_nature_container" style="display: none;">
    <label for="other_nature" class="form-label">Specify other business nature:</label>
    <input type="text" class="form-control" id="other_nature" name="other_nature">
</div>
            </div>
            <div class="mb-3">
                <label for="issue_date" class="form-label">Issue Date:</label>
                <input type="date" class="form-control" id="issue_date" name="issue_date" required>
            </div>
                        
            <div class="mb-3">
                <label for="expiry_date" class="form-label">Expiry Date (Calculated):</label>
                <input type="date" class="form-control" id="expiry_date" name="expiry_date" readonly>
            </div>
                        
            <div class="mb-3">
                <label for="products" class="form-label">Non-Conforming Product and services:</label>
                <textarea class="form-control" id="products" name="products" rows="3"></textarea>
            </div>
            
            <div class="d-grid">
                <button type="button" id="establishmentProceedBtn" class="btn btn-proceed">Proceed</button>
            </div>
        </form>
    </div>

   <!-- Violations Modal -->
    <div class="modal fade" id="violationsModal" tabindex="-1" aria-labelledby="violationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="violationsModalLabel">Violations Found:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="violationsForm">
                        <div class="violation-section">
                            <h6 class="mb-3">Product Standards Violation:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_ps_mark" name="violations[]" value="No PS/ICC Mark">
                                        <label class="form-check-label" for="viol_ps_mark">No PS/ICC Mark</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_bps_license" name="violations[]" value="Invalid/suspended/cancelled BPS license">
                                        <label class="form-check-label" for="viol_bps_license">Invalid/suspended/cancelled BPS license or permit. In violation of DAO 2:2007 pursuant to Republic Act 4109 or Product Standards Law and its Implementing Rules and Regulations</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="violation-section">
    <h6 class="mb-3">DAO Violation:</h6>
    <div class="row">
        <div class="col-md-4">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="viol_manufacturer_name" name="violations[]" value="No Manufacturer's Name">
                <label class="form-check-label" for="viol_manufacturer_name">No Manufacturer's Name</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="viol_manufacturer_address" name="violations[]" value="No Manufacturer's Address">
                <label class="form-check-label" for="viol_manufacturer_address">No Manufacturer's Address</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="viol_date_manufactured" name="violations[]" value="No Date Manufactured">
                <label class="form-check-label" for="viol_date_manufactured">No Date Manufactured</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="viol_country_origin" name="violations[]" value="No Country of Origin">
                <label class="form-check-label" for="viol_country_origin">No Country of Origin</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="viol_others" name="violations[]" value="Others" onclick="toggleOthersInput()">
                <label class="form-check-label" for="viol_others">Others</label>
            </div>
        </div>
    </div>
    <div id="others_input_container" class="row mt-2 d-none">
        <div class="col-md-8">
            <div class="form-group">
                <label for="others_details">Please specify other violation:</label>
                <input type="text" class="form-control" id="others_details" name="others_details" placeholder="Enter details">
            </div>
        </div>
    </div>
</div>

                        <div class="violation-section">
                            <h6 class="mb-3">Accreditation Violation:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_no_accreditation" name="violations[]" value="No Accreditation Certification">
                                        <label class="form-check-label" for="viol_no_accreditation">No Accreditation Certification</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_expired_accreditation" name="violations[]" value="Expired Accreditation Certificate">
                                        <label class="form-check-label" for="viol_expired_accreditation">Expired Accreditation Certificate</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_display_accreditation" name="violations[]" value="Failure to Display valid copy of Accreditation Certificate in conspicuous place in the establishment">
                                        <label class="form-check-label" for="viol_display_accreditation">Failure to Display Accreditation Certificate in conspicuous place in the establishment, in violation of PD 1572 or the Accreditation Law in relation to Article 128 of RA 7394 (Consumer Act of the Philippines) and its omplementing rules and regulation</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_accreditation_others" name="violations_checkbox_others" value="Others">
                                        <label class="form-check-label" for="viol_accreditation_others">Others:</label>
                                    </div>
                                    <div id="accreditation_others_container" class="mb-2 ms-4" style="display: none;">
                                        <input type="text" class="form-control" id="viol_accreditation_others_text" name="violations_others" placeholder="Specify other accreditation violation">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="violation-section">
                            <h6 class="mb-3">Freight Forwarding Services Violation:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_freight_no_accreditation" name="violations[]" value="Freight Business with No Accreditation Certification">
                                        <label class="form-check-label" for="viol_freight_no_accreditation">Freight Business with No Accreditation Certification</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_freight_expired_accreditation" name="violations[]" value="Freight Business with Expired Accreditation Certificate">
                                        <label class="form-check-label" for="viol_freight_expired_accreditation">Freight Business with Expired Accreditation Certificate</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_freight_display_accreditation" name="violations[]" value="Freight Business with Failure to Display valid copy of Accreditation Certificate in conspicuous place in the establishment">
                                        <label class="form-check-label" for="viol_freight_display_accreditation">Freight Business with Failure to Display Accreditation Certificate in conspicuous place in the establishment</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="violation-section">
                            <h6 class="mb-3">Price Tag Violation:</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="viol_price_tag" name="violations[]" value="Products with no/ or inappropriate Price Tag">
                                <label class="form-check-label" for="viol_price_tag">Products with no/ or inappropriate Price Tag</label>
                            </div>
                        </div>
                        
                        <div class="violation-section">
                            <h6 class="mb-3">Pricing Violation:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_price_excess" name="violations[]" value="Price grossly in excess of its/their true worth">
                                        <label class="form-check-label" for="viol_price_excess">Price grossly in excess of its/their true worth</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="viol_price_ceiling" name="violations[]" value="Price is beyond the price ceiling">
                                        <label class="form-check-label" for="viol_price_ceiling">Price is beyond the price ceiling</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="violation-section">
                            <h6 class="mb-3">Business Name Violation:</h6>
                                <div class="form-check mb-2">
                                 <input class="form-check-input" type="checkbox" id="viol_business_name_1" name="violations[]" value="Operating without Business Name Registration">
                                     <label class="form-check-label" for="viol_business_name_1">Operating without Business Name Registration</label>
                                     </div>
                                    <div class="form-check mb-2">
                                     <input class="form-check-input" type="checkbox" id="viol_business_name_2" name="violations[]" value="Using a trade name other than his true name">
                                         <label class="form-check-label" for="viol_business_name_2">Using a trade name other than his true name</label>
                                    </div>
                                            <div class="form-check mb-2">
                                                 <input class="form-check-input" type="checkbox" id="viol_business_name_3" name="violations[]" value="Using a trade name on signages and/or documents (e.g receipts) without prior DTI registration">
                                                     <label class="form-check-label" for="viol_business_name_3">Using a trade name on signages and/or documents (e.g receipts) without prior DTI registration</label>
                                    </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="viol_business_name_4" name="violations[]" value="Failure to display Business Name Registration Certificate">
                                                                <label class="form-check-label" for="viol_business_name_4">Failure to display Business Name Registration Certificate</label>
                                                            </div>
                                                        </div>
                        
                        <div class="violation-section">
                            <h6 class="mb-3">Other Violation:</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="viol_other_violations" name="violations_checkbox_others" value="Other Violations">
                                <label class="form-check-label" for="viol_other_violations">Other Violations:</label>
                            </div>
                            <div id="other_violations_container" class="mb-2 ms-4" style="display: none;">
                                <input type="text" class="form-control" id="other_violations_text" name="violations_others" placeholder="Specify other violations">
                            </div>
                        </div>

                      
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-back" id="violationsBackBtn">Back</button>
                    <button type="button" class="btn btn-proceed" id="violationsProceedBtn">Proceed</button>
                </div>
            </div>
        </div>
    </div>

   <!-- Inventory Modal -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inventoryModalLabel">Inventory of Non-Conforming Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inventoryForm">
                    <div id="inventoryItems">
                        <div class="inventory-item mb-4 pb-3 border-bottom">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product:</label>
                                <input type="text" class="form-control product_name" name="product_name[]">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input sealed" type="checkbox" name="sealed[]" value="1">
                                        <label class="form-check-label">Sealed</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input withdrawn" type="checkbox" name="withdrawn[]" value="1">
                                        <label class="form-check-label">Withdrawn</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Brand/ Description (model,size,color etc):</label>
                                <textarea class="form-control description" name="description[]" rows="3"></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Price:</label>
                                    <input type="number" class="form-control price" name="price[]" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. of Pieces:</label>
                                    <input type="number" class="form-control pieces" name="pieces[]">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input dao_violation" type="checkbox" name="dao_violation[]" value="1">
                                        <label class="form-check-label">Violation of DAO</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input other_violation" type="checkbox" name="other_violation[]" value="1">
                                        <label class="form-check-label">Other Violation/s</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Product Remarks:</label>
                                <textarea class="form-control product_remarks" name="product_remarks[]" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary mb-3" id="addProductBtn">Add Another Product</button>
                </form>
            </div>
            <div class="modal-footer flex-column align-items-stretch">
                <!-- Inventory Remarks Checkboxes -->
                <div class="inventory-remarks-section mb-3 border-top pt-3">
                    <label class="form-label fw-bold">Inventory Remarks:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="sealed_products_left" id="sealedProductsLeft" value="1">
                        <label class="form-check-label" for="sealedProductsLeft">The sealed products were left</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="withdrawn_products_to_dti" id="withdrawnProductsToDTI" value="1">
                        <label class="form-check-label" for="withdrawnProductsToDTI">The withdrawn products were brought to the DTI</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-back me-2" id="inventoryBackBtn">Back</button>
                    <button type="button" class="btn btn-skip me-2" id="inventorySkipBtn">Skip</button>
                    <button type="button" class="btn btn-proceed" id="inventoryProceedBtn">Proceed</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notice Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Status of Notice Violation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <!-- Notice Status Section -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h6 class="mb-3">Status of Notice:</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notice_status" id="status_received" value="Received" checked>
                                <label class="form-check-label" for="status_received">Received</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notice_status" id="status_refused" value="Refused">
                                <label class="form-check-label" for="status_refused">Refused</label>
                            </div>
                        </div>
                        
                        <!-- Witnessed By (only shows when Refused is selected) -->
                        <div class="mb-3" id="witnessed_by_section" style="display: none;">
                            <label for="witnessed_by" class="form-label">Witnessed By:</label>
                            <input type="text" class="form-control" id="witnessed_by" name="witnessed_by">
                        </div>
                    </div>
                    
                    <!-- Issuers Section (Moved from Issuers Modal) -->
                    <div class="mb-4">
                        <h6 class="mb-3">Issued By:</h6>
                        <div id="issuersList">
                            <div class="issuer-item mb-4 pb-3 border-bottom">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Name:</label>
                                        <input type="text" class="form-control issuer_name" name="issuer_name[]" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Position:</label>
                                        <select class="form-select issuer_position" name="issuer_position[]" required>
                                            <option value="Team Leader">Team Leader</option>
                                            <option value="Team Member">Team Member</option>
                                            <option value="Inspector">Inspector</option>
                                            <option value="Supervisor">Supervisor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-primary mb-3" id="addIssuerBtn">Add Another Issuer</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-back" id="statusBackBtn">Back</button>
                <button type="button" class="btn btn-proceed" id="statusSaveBtn">Save and Submit</button>
            </div>
        </div>
    </div>
</div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                        <p class="mt-3">Notice record has been successfully saved!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="window.location.reload()">Add New Notice</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/form_handler_establishments.js"></script>
    <script src="js/address_dropdown.js"></script>
    <script>
// Show/hide "Other violations" text field based on checkbox
$(document).ready(function() {
    $('#viol_other_violations').on('change', function() {
        if ($(this).is(':checked')) {
            $('#other_violations_container').show();
        } else {
            $('#other_violations_container').hide();
        }
    });
    
    // Show/hide "Other nature" field based on select value
    $('#nature').on('change', function() {
        if ($(this).val() === 'Others') {
            $('#other_nature_container').show();
        } else {
            $('#other_nature_container').hide();
        }
    });
});
</script>

</body>
</html>