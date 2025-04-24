<?php
// Include database connection
include __DIR__ . '/../db_config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Notice of Violation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #10346C;
            color: white;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Create Notice of Violation</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Main Form -->
                <form id="novForm" method="POST" action="save_establishment.php">
                    <div class="section-title">Establishment Information</div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="establishment" class="form-label">Establishment Name:</label>
                            <input type="text" class="form-control" id="establishment" name="establishment" required>
                        </div>
                        <div class="col-md-6">
                            <label for="owner_representative" class="form-label">Owner/Representative:</label>
                            <input type="text" class="form-control" id="owner_representative" name="owner_representative">
                        </div>
                    </div>
                    
                    <div class="section-title">Address Information</div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="region" class="form-label">Region:</label>
                            <input type="text" class="form-control" id="region" name="region" required>
                        </div>
                        <div class="col-md-6">
                            <label for="province" class="form-label">Province:</label>
                            <input type="text" class="form-control" id="province" name="province" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="municipality" class="form-label">Municipality:</label>
                            <input type="text" class="form-control" id="municipality" name="municipality" required>
                        </div>
                        <div class="col-md-4">
                            <label for="barangay" class="form-label">Barangay:</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" required>
                        </div>
                        <div class="col-md-4">
                            <label for="street" class="form-label">Street:</label>
                            <input type="text" class="form-control" id="street" name="street" required>
                        </div>
                    </div>
                    
                    <div class="section-title">Business Information</div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="natureSelect" class="form-label">Nature of Business:</label>
                            <select class="form-select" id="natureSelect" name="nature_select" required>
                                <option value="">Select Nature of Business</option>
                                <option value="Manufacturing">Manufacturing</option>
                                <option value="Retail Trade">Retail Trade</option>
                                <option value="Food Service">Food Service</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6 hidden" id="natureCustom">
                            <label for="natureCustomInput" class="form-label">Specify Nature of Business:</label>
                            <input type="text" class="form-control" id="natureCustomInput" name="nature_custom">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="products" class="form-label">Products:</label>
                            <input type="text" class="form-control" id="products" name="products" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="remarks" class="form-label">Remarks:</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="proceedToViolationsBtn">Proceed to Violations</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Violations Modal -->
    <div class="modal fade" id="violationsModal" tabindex="-1" aria-labelledby="violationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="violationsModalLabel">Select Violations</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="violationsForm">
                        <!-- Hidden fields to store establishment data -->
                        <input type="hidden" id="hiddenEstablishment" name="establishment">
                        <input type="hidden" id="hiddenOwnerRep" name="owner_representative">
                        <input type="hidden" id="hiddenAddress" name="address">
                        <input type="hidden" id="hiddenNatureSelect" name="nature_select">
                        <input type="hidden" id="hiddenNatureCustom" name="nature_custom">
                        <input type="hidden" id="hiddenProducts" name="products">
                        <input type="hidden" id="hiddenRemarks" name="remarks">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select all applicable violations:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="No PS/ICC Mark" id="vio1">
                                <label class="form-check-label" for="vio1">No PS/ICC Mark</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Invalid/Expired Accreditation" id="vio2">
                                <label class="form-check-label" for="vio2">Invalid/Expired Accreditation</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Improper Labeling" id="vio3">
                                <label class="form-check-label" for="vio3">Improper Labeling</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Price Tag Violations" id="vio4">
                                <label class="form-check-label" for="vio4">Price Tag Violations</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Uncalibrated Weighing Scale" id="vio5">
                                <label class="form-check-label" for="vio5">Uncalibrated Weighing Scale</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Unfair Trade Practice" id="vio6">
                                <label class="form-check-label" for="vio6">Unfair Trade Practice</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="button" class="btn btn-primary" id="submitViolationsBtn">Proceed to Inventory</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Modal -->
    <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="inventoryModalLabel">Product Inventory</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="inventoryForm">
                        <!-- Hidden fields to store establishment data -->
                        <input type="hidden" name="establishment">
                        <input type="hidden" name="owner_representative">
                        <input type="hidden" name="address">
                        <input type="hidden" name="nature_select">
                        <input type="hidden" name="nature_custom">
                        <input type="hidden" name="products">
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-success" id="addProductBtn">
                                <i class="bi bi-plus-circle"></i> Add Product
                            </button>
                        </div>
                        
                        <div id="productsContainer">
                            <!-- Products will be added here -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="backFromInventoryBtn">Back</button>
                    <button type="button" class="btn btn-success" id="saveInventoryBtn">Save Products</button>
                    <button type="button" class="btn btn-primary" id="skipInventoryBtn">Skip & Proceed</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Received/Refused Modal -->
    <div class="modal fade" id="receivedRefusedModal" tabindex="-1" aria-labelledby="receivedRefusedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="receivedRefusedModalLabel">Notice Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="noticeStatusForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notice Status:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notice_status" id="statusReceived" value="Received">
                                <label class="form-check-label" for="statusReceived">Received</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notice_status" id="statusRefused" value="Refused">
                                <label class="form-check-label" for="statusRefused">Refused</label>
                            </div>
                        </div>
                        
                        <div id="receivedByFields" class="hidden">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="received_by" class="form-label">Received By:</label>
                                    <input type="text" class="form-control" id="received_by" name="received_by">
                                </div>
                                <div class="col-md-6">
                                    <label for="position" class="form-label">Position:</label>
                                    <input type="text" class="form-control" id="position" name="position">
                                </div>
                            </div>
                        </div>
                        
                        <div id="refusedByFields" class="hidden">
                            <div class="mb-3">
                                <label for="witnessed_by" class="form-label">Witnessed By:</label>
                                <input type="text" class="form-control" id="witnessed_by" name="witnessed_by">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="issued_datetime" class="form-label">Date and Time Issued:</label>
                            <input type="text" class="form-control" id="issued_datetime" name="issued_datetime">
                        </div>
                        
                        <div class="mb-3">
                            <label for="status_remarks" class="form-label">Remarks:</label>
                            <textarea class="form-control" id="status_remarks" name="status_remarks" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="button" class="btn btn-primary" id="submitStatusBtn">Submit NOV</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Checking for success message...");
            console.log("URL params:", window.location.search);
            console.log("Session storage:", sessionStorage.getItem('successMessage'));

            // Initialize status radio buttons and fields
            const statusReceived = document.getElementById('statusReceived');
            const statusRefused = document.getElementById('statusRefused');
            const receivedByFields = document.getElementById('receivedByFields');
            const refusedByFields = document.getElementById('refusedByFields');

            // Status radio button handlers
            if (statusReceived) {
                statusReceived.addEventListener('change', function() {
                    if (this.checked && receivedByFields) {
                        receivedByFields.style.display = 'block';
                        if (refusedByFields) refusedByFields.style.display = 'none';
                        
                        if (document.getElementById('received_by')) {
                            document.getElementById('received_by').setAttribute('required', 'required');
                        }
                        if (document.getElementById('position')) {
                            document.getElementById('position').setAttribute('required', 'required');
                        }
                        if (document.getElementById('witnessed_by')) {
                            document.getElementById('witnessed_by').removeAttribute('required');
                        }
                    }
                });
            }

            if (statusRefused) {
                statusRefused.addEventListener('change', function() {
                    if (this.checked && refusedByFields) {
                        if (receivedByFields) receivedByFields.style.display = 'none';
                        refusedByFields.style.display = 'block';
                        
                        if (document.getElementById('witnessed_by')) {
                            document.getElementById('witnessed_by').setAttribute('required', 'required');
                        }
                        if (document.getElementById('received_by')) {
                            document.getElementById('received_by').removeAttribute('required');
                        }
                        if (document.getElementById('position')) {
                            document.getElementById('position').removeAttribute('required');
                        }
                    }
                });
            }

            // Show success message if URL has success parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                const successData = JSON.parse(sessionStorage.getItem('successMessage'));
                if (successData) {
                    Swal.fire({
                        icon: 'success',
                        title: successData.title || 'Success',
                        text: successData.text || 'Operation completed successfully.',
                        confirmButtonColor: '#10346C'
                    });
                    sessionStorage.removeItem('successMessage');
                }
            }

            // Initialize date/time picker
            if (document.getElementById('issued_datetime')) {
                flatpickr("#issued_datetime", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    defaultDate: new Date()
                });
            }

            // Handle nature of business select
            const natureSelect = document.getElementById('natureSelect');
            const natureCustom = document.getElementById('natureCustom');
            
            if (natureSelect) {
                natureSelect.addEventListener('change', function() {
                    if (this.value === 'Others') {
                        natureCustom.classList.remove('hidden');
                        document.getElementById('natureCustomInput').setAttribute('required', 'required');
                    } else {
                        natureCustom.classList.add('hidden');
                        document.getElementById('natureCustomInput').removeAttribute('required');
                    }
                });
            }

            // Initialize modals
            const violationsModal = new bootstrap.Modal(document.getElementById('violationsModal'));
            const inventoryModal = new bootstrap.Modal(document.getElementById('inventoryModal'));
            const receivedRefusedModal = new bootstrap.Modal(document.getElementById('receivedRefusedModal'));
            
            // Proceed to Violations button
            document.getElementById('proceedToViolationsBtn').addEventListener('click', function() {
                // Validate establishment form
                const form = document.getElementById('novForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                // Transfer data to hidden fields in violations modal
                document.getElementById('hiddenEstablishment').value = document.getElementById('establishment').value;
                document.getElementById('hiddenOwnerRep').value = document.getElementById('owner_representative').value;
                
                // Combine address fields
                const address = [
                    document.getElementById('street').value,
                    document.getElementById('barangay').value,
                    document.getElementById('municipality').value,
                    document.getElementById('province').value,
                    document.getElementById('region').value
                ].filter(Boolean).join(', ');
                document.getElementById('hiddenAddress').value = address;
                
                // Transfer nature of business
                document.getElementById('hiddenNatureSelect').value = document.getElementById('natureSelect').value;
                if (document.getElementById('natureSelect').value === 'Others') {
                    document.getElementById('hiddenNatureCustom').value = document.getElementById('natureCustomInput').value;
                }
                
                document.getElementById('hiddenProducts').value = document.getElementById('products').value;
                document.getElementById('hiddenRemarks').value = document.getElementById('remarks').value;
                
                // Show violations modal
                violationsModal.show();
            });
            
            // Proceed to Inventory button
            document.getElementById('submitViolationsBtn').addEventListener('click', function() {
                // Validate at least one violation is selected
                const selectedViolations = document.querySelectorAll('input[name="violations[]"]:checked');
                if (selectedViolations.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select at least one violation',
                        confirmButtonColor: '#10346C'
                    });
                    return;
                }
                
                // Transfer form data to inventory modal
                const inventoryFormInputs = document.getElementById('inventoryForm').querySelectorAll('input[type="hidden"]');
                inventoryFormInputs[0].value = document.getElementById('hiddenEstablishment').value;
                inventoryFormInputs[1].value = document.getElementById('hiddenOwnerRep').value;
                inventoryFormInputs[2].value = document.getElementById('hiddenAddress').value;
                inventoryFormInputs[3].value = document.getElementById('hiddenNatureSelect').value;
                inventoryFormInputs[4].value = document.getElementById('hiddenNatureCustom').value;
                inventoryFormInputs[5].value = document.getElementById('hiddenProducts').value;
                
                // Hide violations modal and show inventory modal
                violationsModal.hide();
                inventoryModal.show();
            });
            
            // Add product button
            document.getElementById('addProductBtn').addEventListener('click', function() {
                const productCount = document.querySelectorAll('.product-row').length;
                const productHtml = `
                    <div class="card mb-3 product-row">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Product #${productCount + 1}</span>
                            <button type="button" class="btn btn-danger btn-sm remove-product">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Product Name:</label>
                                    <input type="text" class="form-control" name="product_name[]" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Brand:</label>
                                    <input type="text" class="form-control" name="product_brand[]" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Quantity:</label>
                                    <input type="number" class="form-control" name="product_quantity[]" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Unit:</label>
                                    <select class="form-select" name="product_unit[]" required>
                                        <option value="">Select Unit</option>
                                        <option value="pieces">Pieces</option>
                                        <option value="boxes">Boxes</option>
                                        <option value="packs">Packs</option>
                                        <option value="cases">Cases</option>
                                        <option value="kilograms">Kilograms</option>
                                        <option value="liters">Liters</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Unit Price:</label>
                                    <input type="number" class="form-control" name="product_price[]" step="0.01" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">Remarks:</label>
                                    <textarea class="form-control" name="product_remarks[]" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('productsContainer').insertAdjacentHTML('beforeend', productHtml);
                
                // Add event listeners to the new remove buttons
                const removeButtons = document.querySelectorAll('.remove-product');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        this.closest('.product-row').remove();
                    });
                });
            });
            
            // Back from inventory button
            document.getElementById('backFromInventoryBtn').addEventListener('click', function() {
                inventoryModal.hide();
                violationsModal.show();
            });
            
            // Skip inventory button
            document.getElementById('skipInventoryBtn').addEventListener('click', function() {
                inventoryModal.hide();
                receivedRefusedModal.show();
            });
            
            // Save inventory button
            document.getElementById('saveInventoryBtn').addEventListener('click', function() {
                const inventoryForm = document.getElementById('inventoryForm');
                if (!inventoryForm.checkValidity()) {
                    inventoryForm.reportValidity();
                    return;
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Products Saved',
                    text: 'Product inventory has been saved successfully.',
                    confirmButtonColor: '#10346C'
                }).then(() => {
                    inventoryModal.hide();
                    receivedRefusedModal.show();
                });
            });
            
            // Submit status button
            document.getElementById('submitStatusBtn').addEventListener('click', function() {
                // Check if status is selected
                const statusReceived = document.getElementById('statusReceived');
                const statusRefused = document.getElementById('statusRefused');
                
                if (!statusReceived.checked && !statusRefused.checked) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select notice status (Received or Refused)',
                        confirmButtonColor: '#10346C'
                    });
                    return;
                }
                
                // Check required fields based on selected status
                if (statusReceived.checked) {
                    if (!document.getElementById('received_by').value || !document.getElementById('position').value) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Please fill in all required fields for received status',
                            confirmButtonColor: '#10346C'
                        });
                        return;
                    }
                } else if (statusRefused.checked) {
                    if (!document.getElementById('witnessed_by').value) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Please enter witnessed by for refused status',
                            confirmButtonColor: '#10346C'
                        });
                        return;
                    }
                }
                
                // Check date issued
                if (!document.getElementById('issued_datetime').value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select date and time issued',
                        confirmButtonColor: '#10346C'
                    });
                    return;
                }
                
                // Submit the form - collect all data and send to server
                submitCompleteForm();
            });
            
            function submitCompleteForm() {
                // Collect all form data
                const formData = new FormData();
                
                // Establishment data
                formData.append('establishment', document.getElementById('establishment').value);
                formData.append('owner_representative', document.getElementById('owner_representative').value);
                formData.append('region', document.getElementById('region').value);
                formData.append('province', document.getElementById('province').value);
                formData.append('municipality', document.getElementById('municipality').value);
                formData.append('barangay', document.getElementById('barangay').value);
                formData.append('street', document.getElementById('street').value);
                
                // Nature of business
                formData.append('nature_select', document.getElementById('natureSelect').value);
                if (document.getElementById('natureSelect').value === 'Others') {
                    formData.append('nature_custom', document.getElementById('natureCustomInput').value);
                }
                
                formData.append('products', document.getElementById('products').value);
                formData.append('remarks', document.getElementById('remarks').value);
                
                // Violations
                const violations = [];
                document.querySelectorAll('input[name="violations[]"]:checked').forEach(checkbox => {
                    violations.push(checkbox.value);
                });
                formData.append('violations', JSON.stringify(violations));
                
                // Inventory items (if any)
                const productData = [];
                document.querySelectorAll('.product-row').forEach(product => {
                    const name = product.querySelector('input[name="product_name[]"]').value;
                    const brand = product.querySelector('input[name="product_brand[]"]').value;
                    const quantity = product.querySelector('input[name="product_quantity[]"]').value;
                    const unit = product.querySelector('select[name="product_unit[]"]').value;
                    const price = product.querySelector('input[name="product_price[]"]').value;
                    const remarks = product.querySelector('textarea[name="product_remarks[]"]').value;
                    
                    productData.push({
                        name,
                        brand,
                        quantity,
                        unit,
                        price,
                        remarks
                    });
                });
                formData.append('products_inventory', JSON.stringify(productData));
                
                // Status information
                const noticeStatus = document.querySelector('input[name="notice_status"]:checked').value;
                formData.append('notice_status', noticeStatus);
                
                if (noticeStatus === 'Received') {
                    formData.append('received_by', document.getElementById('received_by').value);
                    formData.append('position', document.getElementById('position').value);
                } else {
                    formData.append('witnessed_by', document.getElementById('witnessed_by').value);
                }
                
                formData.append('issued_datetime', document.getElementById('issued_datetime').value);
                formData.append('status_remarks', document.getElementById('status_remarks').value);
                
                // Send form data to server using fetch
                fetch('save_nov.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Store success message in session storage for retrieval after redirect
                        sessionStorage.setItem('successMessage', JSON.stringify({
                            title: 'Notice of Violation Created',
                            text: 'The Notice of Violation has been created successfully!'
                        }));
                        
                        // Redirect to success page or NOV list
                        window.location.href = 'nov_list.php?success=true';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'There was an error saving the Notice of Violation.',
                            confirmButtonColor: '#10346C'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'There was an error processing your request. Please try again.',
                        confirmButtonColor: '#10346C'
                    });
                });
            }
        });
    </script>
</body>
</html>