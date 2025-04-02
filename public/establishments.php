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
            'remarks' => $remarks
        ];

        // Redirect to the same page to show the issuer modal
        header("Location: establishments.php?show_issuer_modal=1");
        ob_end_flush();
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_issuer'])) {
    // Retrieve previously stored NOV details
    $novDetails = $_SESSION['nov_details'] ?? null;
    
    if ($novDetails) {
        $issuer_name = htmlspecialchars($_POST['issuer_name']);
        $issued_datetime = htmlspecialchars($_POST['issued_datetime']);
        
        try {
            // Store in database with enhanced error handling
            $stmt = $conn->prepare("INSERT INTO establishments 
                (name, address, owner_representative, nature, products, violations, remarks, nov_files, issued_by, issued_datetime)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt === false) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $violations_str = implode(', ', $novDetails['violations'] ?? []);
            
            $stmt->bind_param("ssssssssss", 
                $novDetails['establishment'],
                $novDetails['address'],
                $novDetails['owner_representative'],
                $novDetails['nature'],
                $novDetails['products'],
                $violations_str,
                $novDetails['remarks'],
                $novDetails['filename'],
                $issuer_name,
                $issued_datetime
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

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
            
            // Redirect to establishments page
            header("Location: establishments.php");
            ob_end_flush();
            exit();
            
        } catch (Exception $e) {
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
    </style>
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">Notice Management</h2>
        
        <!-- NOV Submission Form -->
        <div class="nov-form mb-5 shadow">
            <h4 class="mb-4">Notice of Violation Form</h4>
            <form id="novForm" method="POST">
                <!-- Establishment Section -->
                <div class="form-section mb-4">
                    <h5 class="section-title mb-3">Establishment Details</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="owner_representative" 
                                       name="owner_representative" required 
                                       placeholder="Enter owner/representative name"
                                       data-placeholder="Enter owner/representative name"
                                       value="<?= isset($_SESSION['nov_details']['owner_representative']) ? htmlspecialchars($_SESSION['nov_details']['owner_representative']) : '' ?>">
                                <label for="owner_representative">Owner/Representative Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="establishment" class="form-control" 
                                       placeholder="ABC Trading Corporation" required
                                       data-placeholder="ABC Trading Corporation"
                                       value="<?= isset($_SESSION['nov_details']['establishment']) ? htmlspecialchars($_SESSION['nov_details']['establishment']) : '' ?>">
                                <label>Establishment Name</label>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="form-floating mb-3">
                                <input type="text" name="address" class="form-control" 
                                       placeholder="123 Main Street, City" required
                                       data-placeholder="123 Main Street, City"
                                       value="<?= isset($_SESSION['nov_details']['address']) ? htmlspecialchars($_SESSION['nov_details']['address']) : '' ?>">
                                <label>Business Address</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select name="nature_select" id="natureSelect" class="form-select" required>
                                    <option value="">Select Nature</option>
                                    <option value="Retail Trade" <?= isset($_SESSION['nov_details']['nature_select']) && $_SESSION['nov_details']['nature_select'] === 'Retail Trade' ? 'selected' : '' ?>>Retailer/Wholesaler</option>
                                    <option value="Manufacturing" <?= isset($_SESSION['nov_details']['nature_select']) && $_SESSION['nov_details']['nature_select'] === 'Manufacturing' ? 'selected' : '' ?>>Supermarket/Grocery/Convenience Store</option>
                                    <option value="Manufacturing" <?= isset($_SESSION['nov_details']['nature_select']) && $_SESSION['nov_details']['nature_select'] === 'Manufacturing' ? 'selected' : '' ?>>Service and Repair</option>
                                    <option value="Food Service" <?= isset($_SESSION['nov_details']['nature_select']) && $_SESSION['nov_details']['nature_select'] === 'Food Service' ? 'selected' : '' ?>>Hardware</option>
                                    <option value="Manufacturing" <?= isset($_SESSION['nov_details']['nature_select']) && $_SESSION['nov_details']['nature_select'] === 'Manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                                    <option value="Others" <?= isset($_SESSION['nov_details']['nature_select']) && $_SESSION['nov_details']['nature_select'] === 'Others' ? 'selected' : '' ?>>Others</option>
                                </select>
                                <label for="natureSelect">Nature of Business</label>
                            </div>
                            <input type="text" name="nature_custom" id="natureCustom" 
                                   class="form-control mt-2 custom-nature" 
                                   placeholder="Specify nature of business"
                                   value="<?= isset($_SESSION['nov_details']['nature_custom']) ? htmlspecialchars($_SESSION['nov_details']['nature_custom']) : '' ?>"
                                   style="display: <?= isset($_SESSION['nov_details']['nature_select']) && $_SESSION['nov_details']['nature_select'] === 'Others' ? 'block' : 'none' ?>">
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="form-section mb-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <textarea name="products" class="form-control" 
                                          placeholder="List non-compliant items/services" 
                                          style="height: 100px" required><?= isset($_SESSION['nov_details']['products']) ? htmlspecialchars($_SESSION['nov_details']['products']) : '' ?></textarea>
                                <label>Non-Conforming Products/Goods/Services</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Violations Section -->
                <div class="form-section mb-4">
                    <h5 class="section-title mb-3">Violations Found</h5>
                    <div class="violation-list row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="No PS/ICC Mark"
                                    <?= isset($_SESSION['nov_details']['violations']) && in_array('No PS/ICC Mark', $_SESSION['nov_details']['violations']) ? 'checked' : '' ?>>
                                <label class="form-check-label">No PS/ICC Mark</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Invalid/Expired Accreditation"
                                    <?= isset($_SESSION['nov_details']['violations']) && in_array('Invalid/Expired Accreditation', $_SESSION['nov_details']['violations']) ? 'checked' : '' ?>>
                                <label class="form-check-label">Invalid/Expired Accreditation</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Improper Labeling"
                                    <?= isset($_SESSION['nov_details']['violations']) && in_array('Improper Labeling', $_SESSION['nov_details']['violations']) ? 'checked' : '' ?>>
                                <label class="form-check-label">Improper Labeling</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Price Tag Violations"
                                    <?= isset($_SESSION['nov_details']['violations']) && in_array('Price Tag Violations', $_SESSION['nov_details']['violations']) ? 'checked' : '' ?>>
                                <label class="form-check-label">Price Tag Violations</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remarks Section -->
                <div class="form-section">
                    <h5 class="section-title mb-3">Additional Remarks</h5>
                    <textarea name="remarks" class="form-control" rows="3" 
                              placeholder="Enter additional comments or observations"><?= isset($_SESSION['nov_details']['remarks']) ? htmlspecialchars($_SESSION['nov_details']['remarks']) : '' ?></textarea>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-contract me-2"></i>Submit Form
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
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="issuer_name" class="form-label">Issued By (Name)</label>
                            <input type="text" class="form-control" id="issuer_name" name="issuer_name" required 
                                   placeholder="Enter name of the person issuing the violation">
                        </div>
                        <div class="mb-3">
                            <label for="issued_datetime" class="form-label">Date and Time of Issuance</label>
                            <input type="text" class="form-control" id="issued_datetime" name="issued_datetime" required
                                   placeholder="Select date and time">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="establishments.php" class="btn btn-secondary me-2">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        <?php if (isset($_GET['show_confirmation']) && $_GET['show_confirmation'] == 1): ?>
        document.addEventListener('DOMContentLoaded', function() {
    const confirmationData = <?= json_encode($_SESSION['issuer_confirmation']) ?>;
    
    Swal.fire({
        title: 'Confirm Issuer Details',
        html: `
            <div class="text-start">
                <p><strong>Issuer Name:</strong> ${confirmationData.issuer_name}</p>
                <p><strong>Issuance Date:</strong> ${confirmationData.issued_datetime}</p>
                <p><strong>For Establishment:</strong> ${confirmationData.nov_details.establishment}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10346C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, save details',
        cancelButtonText: 'Review details'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form to save to database
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'save_issuer.php';
            
            const issuerName = document.createElement('input');
            issuerName.type = 'hidden';
            issuerName.name = 'issuer_name';
            issuerName.value = confirmationData.issuer_name;
            
            const issuedDatetime = document.createElement('input');
            issuedDatetime.type = 'hidden';
            issuedDatetime.name = 'issued_datetime';
            issuedDatetime.value = confirmationData.issued_datetime;
            
            const submitBtn = document.createElement('input');
            submitBtn.type = 'hidden';
            submitBtn.name = 'confirmed_submit';
            submitBtn.value = '1';
            
            form.appendChild(issuerName);
            form.appendChild(issuedDatetime);
            form.appendChild(submitBtn);
            document.body.appendChild(form);
            form.submit();
        } else {
            // Go back to issuer details form
            window.location.href = 'establishments.php?show_issuer_modal=1';
        }
    });
});
<?php endif; ?>
    document.addEventListener('DOMContentLoaded', function() {
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
                    maxDate: new Date() // Prevent future dates
                });
                
                // Auto-focus on issuer name field when modal appears
                const issuerNameField = document.getElementById('issuer_name');
                if (issuerNameField) {
                    issuerNameField.focus();
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
        });

        function resetForm() {
    const form = document.getElementById('novForm');
    if (form) {
        form.reset();

           // Reset custom nature field visibility
           const natureSelect = document.getElementById('natureSelect');
        const customInput = document.getElementById('natureCustom');
        if (natureSelect && customInput) {
            customInput.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
        
        // Uncheck all checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
    }
}
          // Function to clear session data
    function clearSessionData() {
        // Client-side session clearing
        sessionStorage.removeItem('novFormData');

        // Server-side session clearing via AJAX
        fetch('clear_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });
    }
     // Create a new clear_session.php file
     const menuItems = document.querySelectorAll('.nav-link, .navbar-brand, .sidebar-link');
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Check if the clicked link is not the current page
            if (!this.classList.contains('active')) {
                clearSessionData();
            }
        });
    });

    // Save form data to sessionStorage
    const novForm = document.getElementById('novForm');
    if (novForm) {
        novForm.addEventListener('input', function() {
            const formData = {};
            const formElements = novForm.elements;
            
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];
                
                if (element.name) {
                    if (element.type === 'checkbox') {
                        if (!formData[element.name]) {
                            formData[element.name] = [];
                        }
                        if (element.checked) {
                            formData[element.name].push(element.value);
                        }
                    } else if (element.type !== 'submit') {
                        formData[element.name] = element.value;
                    }
                }
            }
            
            sessionStorage.setItem('novFormData', JSON.stringify(formData));
        });

        // Restore form data on page load
        const savedFormData = sessionStorage.getItem('novFormData');
        if (savedFormData) {
            const parsedData = JSON.parse(savedFormData);
            
            Object.keys(parsedData).forEach(key => {
                const field = novForm.elements[key];
                
                if (field) {
                    if (field.length && field.type === 'radio') {
                        // Handle radio buttons
                        Array.from(field).forEach(radio => {
                            if (radio.value === parsedData[key]) {
                                radio.checked = true;
                            }
                        });
                    } else if (field.type === 'checkbox') {
                        // Handle checkboxes
                        if (Array.isArray(parsedData[key])) {
                            parsedData[key].forEach(value => {
                                const checkbox = novForm.querySelector(`input[name="${key}[]"][value="${value}"]`);
                                if (checkbox) checkbox.checked = true;
                            });
                        }
                    } else {
                        // Handle other input types
                        field.value = parsedData[key];
                    }
                }
            });
        }
    }

    </script>
    <?php include '../templates/footer.php'; ?>
</body>
</html>
<?php
ob_end_flush();
?>
