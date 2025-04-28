<?php
// Start output buffering at the VERY TOP
ob_start();
session_start();
include __DIR__ . '/../connection.php';

// If form submission from violations page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_violations'])) {
    // Process form data from both establishment form and violations
    
    // Retrieve form data from session
    $formData = $_SESSION['form_data'] ?? [];
    
    // Combine with violations data
    $violations = $_POST['violations'] ?? [];
    $additional_notes = htmlspecialchars($_POST['additional_notes'] ?? '');
    
    // Now proceed with creating NOV and saving to database
    // [Your existing processing code would go here]
    
    // Redirect to issuer modal or completion
    header("Location: establishments.php?show_issuer_modal=1");
    ob_end_flush();
    exit();
}

// Include header
include '../templates/header.php';

// Retrieve form data from session if available
$formData = $_SESSION['form_data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Violation Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
        }
        .violations-form { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            margin-top: 20px;
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
        }
        .section-title {
            color: #10346C;
            border-bottom: 2px solid #10346C;
            padding-bottom: 0.5rem;
            font-weight: 600;
        }
        .establishment-summary {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .violation-category {
            margin-bottom: 1.5rem;
        }
        .violation-item {
            padding: 10px;
            border-radius: 5px;
            transition: all 0.2s ease;
        }
        .violation-item:hover {
            background-color: #f0f0f0;
        }
        .form-check-input:checked + .form-check-label {
            font-weight: bold;
            color: #10346C;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">Select Violations</h2>
        
        <!-- Establishment Summary -->
        <div class="establishment-summary">
            <h5>Establishment Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?= htmlspecialchars($formData['establishment'] ?? 'N/A') ?></p>
                    <p><strong>Representative:</strong> <?= htmlspecialchars($formData['owner_representative'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Address:</strong> <?= htmlspecialchars($formData['address'] ?? 'N/A') ?></p>
                    <p><strong>Nature:</strong> <?= htmlspecialchars($formData['nature'] ?? $formData['nature_select'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>
        
        <!-- Violations Form -->
        <div class="violations-form shadow">
            <h4 class="mb-4">Identify Violations</h4>
            <form method="POST" id="violationsForm">
                <!-- Violations Selection -->
                <div class="form-section">
                    <h5 class="section-title mb-3">Product Standards Violations</h5>
                    
                    <div class="violation-category">
                        <h6>Documentation</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="No PS/ICC Mark" id="violation1">
                                        <label class="form-check-label" for="violation1">
                                            No PS/ICC Mark
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="Invalid/Expired PS Certificate" id="violation2">
                                        <label class="form-check-label" for="violation2">
                                            Invalid/Expired PS Certificate
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="violation-category">
                        <h6>Labeling Issues</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="Improper Labeling" id="violation3">
                                        <label class="form-check-label" for="violation3">
                                            Improper Labeling
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="Missing Product Information" id="violation4">
                                        <label class="form-check-label" for="violation4">
                                            Missing Product Information
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="section-title mb-3">Consumer Protection Violations</h5>
                    
                    <div class="violation-category">
                        <h6>Pricing and Tags</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="No Price Tag" id="violation5">
                                        <label class="form-check-label" for="violation5">
                                            No Price Tag
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="Price Tag Violations" id="violation6">
                                        <label class="form-check-label" for="violation6">
                                            Price Tag Violations
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="violation-category">
                        <h6>Sales Practices</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="Unfair Sales Act Violation" id="violation7">
                                        <label class="form-check-label" for="violation7">
                                            Unfair Sales Act Violation
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="violation-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="violations[]" 
                                               value="Misleading Advertisement" id="violation8">
                                        <label class="form-check-label" for="violation8">
                                            Misleading Advertisement
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Notes -->
                <div class="form-section">
                    <h5 class="section-title mb-3">Additional Notes</h5>
                    <textarea name="additional_notes" class="form-control" rows="4" 
                              placeholder="Enter any additional details about the violations"></textarea>
                </div>
                
                <div class="mt-4 d-flex justify-content-between">
                    <a href="establishments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Establishment Form
                    </a>
                    <button type="submit" name="submit_violations" class="btn btn-primary btn-lg">
                        <i class="fas fa-check me-2"></i>Submit Violations
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validate that at least one violation is selected before submission
            const violationsForm = document.getElementById('violationsForm');
            
            if (violationsForm) {
                violationsForm.addEventListener('submit', function(e) {
                    const checkboxes = document.querySelectorAll('input[name="violations[]"]:checked');
                    
                    if (checkboxes.length === 0) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'No Violations Selected',
                            text: 'Please select at least one violation before submitting.',
                            icon: 'warning',
                            confirmButtonColor: '#10346C'
                        });
                    }
                });
            }
            
            // Optional: Highlight selected violations
            const violationCheckboxes = document.querySelectorAll('input[name="violations[]"]');
            violationCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const parentItem = this.closest('.violation-item');
                    if (this.checked) {
                        parentItem.style.backgroundColor = '#e8f4ff';
                    } else {
                        parentItem.style.backgroundColor = '';
                    }
                });
            });
        });
    </script>
</body>
</html>