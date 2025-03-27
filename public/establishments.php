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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        // Store in database
        $stmt = $conn->prepare("INSERT INTO establishments 
            (name, address, owner_representative, nature, products, violations, remarks, nov_files)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", 
            $establishment,
            $address,
            $owner_rep,
            $nature,
            $products,
            $violations,
            $remarks,
            $filename
        );
        $stmt->execute();
        $_SESSION['success'] = "Notice of Violation submitted successfully!";
        header("Location: establishments.php");
        ob_end_flush();
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
        <h2 class="mb-4">File NOV</h2>
        
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
                                       data-placeholder="Enter owner/representative name">
                                <label for="owner_representative">Owner/Representative Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="establishment" class="form-control" 
                                       placeholder="ABC Trading Corporation" required
                                       data-placeholder="ABC Trading Corporation">
                                <label>Establishment Name</label>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="form-floating mb-3">
                                <input type="text" name="address" class="form-control" 
                                       placeholder="123 Main Street, City" required
                                       data-placeholder="123 Main Street, City">
                                <label>Business Address</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select name="nature_select" id="natureSelect" class="form-select" required>
                                    <option value="">Select Nature</option>
                                    <option value="Retail Trade">Retailer/Wholesaler</option>
                                    <option value="Manufacturing">Supermarket/Grocery/Convenience Store</option>
                                    <option value="Manufacturing">Service and Repair</option>
                                    <option value="Food Service">Hardware</option>
                                    <option value="Manufacturing">Manufacturing</option>
                                    <option value="Others">Others</option>
                                </select>
                                <label for="natureSelect">Nature of Business</label>
                            </div>
                            <input type="text" name="nature_custom" id="natureCustom" 
                                   class="form-control mt-2 custom-nature" 
                                   placeholder="Specify nature of business">
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
                                          style="height: 100px" required></textarea>
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
                                <input class="form-check-input" type="checkbox" name="violations[]" value="No PS/ICC Mark">
                                <label class="form-check-label">No PS/ICC Mark</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Invalid/Expired Accreditation">
                                <label class="form-check-label">Invalid/Expired Accreditation</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Improper Labeling">
                                <label class="form-check-label">Improper Labeling</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="violations[]" value="Price Tag Violations">
                                <label class="form-check-label">Price Tag Violations</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remarks Section -->
                <div class="form-section">
                    <h5 class="section-title mb-3">Additional Remarks</h5>
                    <textarea name="remarks" class="form-control" rows="3" 
                              placeholder="Enter additional comments or observations"></textarea>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-contract me-2"></i>Submit Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Enhanced Placeholder Interaction
    document.addEventListener('DOMContentLoaded', function() {
        // Nature of Business Custom Input Toggle
        const natureSelect = document.getElementById('natureSelect');
        const customInput = document.getElementById('natureCustom');
        
        natureSelect.addEventListener('change', function() {
            if (this.value === 'Others') {
                customInput.style.display = 'block';
                customInput.required = true;
                customInput.focus(); // Auto-focus on custom input
            } else {
                customInput.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
            }
        });

        // Placeholder Interaction
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            const originalPlaceholder = input.getAttribute('data-placeholder') || input.placeholder;
            
            input.addEventListener('focus', function() {
                this.setAttribute('placeholder', originalPlaceholder);
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.setAttribute('placeholder', originalPlaceholder);
                }
            });
        });
    });
// Form submission with confirmation
document.getElementById('novForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // First validate the form
        if (!validateForm()) return;
        
        Swal.fire({
            title: 'Confirm Submission',
            text: 'Are you sure you want to submit this Notice of Violation?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10346C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, submit the form programmatically
                this.submit();
            }
        });
    });

    function validateForm() {
        const natureSelect = document.getElementById('natureSelect');
        const customInput = document.getElementById('natureCustom');
        
        if (natureSelect.value === 'Others' && customInput.value.trim() === '') {
            Swal.fire({
                title: 'Error',
                text: 'Please specify the nature of business',
                icon: 'error'
            });
            customInput.focus();
            return false;
        }
        return true;
    }

    // Show success/error messages
    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= $_SESSION['success'] ?>',
            icon: 'success',
            confirmButtonColor: '#10346C'
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?= $_SESSION['error'] ?>',
            icon: 'error',
            confirmButtonColor: '#10346C'
        });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    </script>
</body>
</html>
<?php
ob_end_flush();
?>