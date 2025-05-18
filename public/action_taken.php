<?php
// Start output buffering at the very beginning
ob_start();

require_once '../connection.php';
include '../templates/header.php';

// Get establishment ID from URL
$establishment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if establishment exists
$stmt = $conn->prepare("SELECT e.*, nr.notice_type, nr.remarks as action_remarks, nr.date_responded, nr.record_id
                     FROM establishments e 
                     LEFT JOIN notice_records nr ON e.establishment_id = nr.establishment_id
                     WHERE e.establishment_id = ?");
$stmt->bindParam(1, $establishment_id, PDO::PARAM_INT);
$stmt->execute();
$establishment = $stmt->fetch(PDO::FETCH_ASSOC);

// If establishment not found, redirect back with error
if (!$establishment) {
    header("Location: nov_form.php?error=invalid_establishment");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_action'])) {
    // Map abbreviated notice types to full names
    $notice_type_map = [
        'CFO' => 'Certified First Offence',
        'FC' => 'Formal Charge'
    ];
    
    $notice_type = $_POST['notice_type'];
    
    // Replace abbreviated notice types with full names
    if (array_key_exists($notice_type, $notice_type_map)) {
        $notice_type = $notice_type_map[$notice_type];
    }
    
    $date_responded = $_POST['date_responded'];
    $remarks = $_POST['remarks'] ?? '';
    $other_details = $_POST['other_details'] ?? '';
    
    // Combine remarks and other details if applicable
    if (!empty($other_details)) {
        $remarks = "Details: " . $other_details . "\n" . $remarks;
    }
    
    // Check if a record already exists
    $check_record = $conn->prepare("SELECT * FROM notice_records WHERE establishment_id = ?");
    $check_record->bindParam(1, $establishment_id, PDO::PARAM_INT);
    $check_record->execute();
    
    if ($check_record->rowCount() > 0) {
        // Update existing record
        $update_record = $conn->prepare("UPDATE notice_records SET notice_type = ?, date_responded = ?, remarks = ?, status = 'Responded', updated_at = NOW() WHERE establishment_id = ?");
        $update_record->bindParam(1, $notice_type);
        $update_record->bindParam(2, $date_responded);
        $update_record->bindParam(3, $remarks);
        $update_record->bindParam(4, $establishment_id, PDO::PARAM_INT);
        $update_record->execute();
        
        // Get the actual record_id for the file upload
        $get_record_id = $conn->prepare("SELECT record_id FROM notice_records WHERE establishment_id = ?");
        $get_record_id->bindParam(1, $establishment_id, PDO::PARAM_INT);
        $get_record_id->execute();
        $record_data = $get_record_id->fetch(PDO::FETCH_ASSOC);
        $record_id = $record_data['record_id'];
    } else {
        // Insert new record
        $insert_record = $conn->prepare("INSERT INTO notice_records (establishment_id, notice_type, date_responded, status, remarks) VALUES (?, ?, ?, 'Responded', ?)");
        $insert_record->bindParam(1, $establishment_id, PDO::PARAM_INT);
        $insert_record->bindParam(2, $notice_type);
        $insert_record->bindParam(3, $date_responded);
        $insert_record->bindParam(4, $remarks);
        $insert_record->execute();
        
        $record_id = $conn->lastInsertId();
    }
    
    // Update establishment status to "Responded"
    $update_status = $conn->prepare("UPDATE establishments SET notice_status = 'Responded' WHERE establishment_id = ?");
    $update_status->bindParam(1, $establishment_id, PDO::PARAM_INT);
    $update_status->execute();
    
    // Handle file upload if present
    if (isset($_FILES['action_file']) && $_FILES['action_file']['error'] === 0) {
        $file = $_FILES['action_file'];
        $file_name = $file['name'];
        $file_type = $file['type'];
        $file_tmp = $file['tmp_name'];
        
        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/notice_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $new_file_name = uniqid('notice_') . '_' . $file_name;
        $file_path = $upload_dir . $new_file_name;
        
        // Move uploaded file to destination
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Insert file info into notice_images table
            $insert_image = $conn->prepare("INSERT INTO notice_images (record_id, image_path, image_name, image_type) VALUES (?, ?, ?, ?)");
            $relative_path = 'uploads/notice_images/' . $new_file_name;
            $insert_image->bindParam(1, $record_id, PDO::PARAM_INT);
            $insert_image->bindParam(2, $relative_path);
            $insert_image->bindParam(3, $file_name);
            $insert_image->bindParam(4, $file_type);
            $insert_image->execute();
        }
    }
    
    // Log the action
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $action = "Updated action type to $notice_type";
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $details = "Updated establishment ID: $establishment_id action type to: $notice_type";
    
    $log_query = $conn->prepare("INSERT INTO user_logs (user_id, action, user_agent, details, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $log_query->bindParam(1, $user_id, PDO::PARAM_INT);
    $log_query->bindParam(2, $action);
    $log_query->bindParam(3, $user_agent);
    $log_query->bindParam(4, $details);
    $log_query->execute();
    
    // Redirect to prevent form resubmission
    header("Location: nov_form.php?action_updated=1");
    exit();
}

// Check if we have any existing record
$has_existing_record = false;
$existing_notice_type = '';
$existing_date_responded = '';
$existing_remarks = '';
$existing_record_id = null;

if (!empty($establishment['notice_type'])) {
    $has_existing_record = true;
    $existing_notice_type = $establishment['notice_type'];
    $existing_date_responded = $establishment['date_responded'] ? date('Y-m-d', strtotime($establishment['date_responded'])) : '';
    $existing_remarks = $establishment['action_remarks'] ?? '';
    $existing_record_id = $establishment['record_id'] ?? null;
}

// Fetch any existing uploaded images
$existing_images = [];
if ($existing_record_id) {
    $images_query = $conn->prepare("SELECT * FROM notice_images WHERE record_id = ? AND active = 1");
    $images_query->bindParam(1, $existing_record_id, PDO::PARAM_INT);
    $images_query->execute();
    $existing_images = $images_query->fetchAll(PDO::FETCH_ASSOC);
}

// Function to check if notice type matches (handling both short and full versions)
function isNoticeType($existing, $check) {
    $full_map = [
        'CFO' => 'Certified First Offence',
        'FC' => 'Formal Charge'
    ];
    
    // Check direct match
    if ($existing === $check) {
        return true;
    }
    
    // Check if existing is abbreviated and matches full version
    if (isset($full_map[$existing]) && $full_map[$existing] === $check) {
        return true;
    }
    
    // Check if existing is full version and check is abbreviated
    foreach ($full_map as $abbr => $full) {
        if ($existing === $full && $check === $abbr) {
            return true;
        }
    }
    
    return false;
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Actions Taken for <?php echo htmlspecialchars($establishment['name']); ?></h2>
                <a href="nov_form.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
            
            <?php if ($has_existing_record): ?>
            <div class="alert alert-info">
                <strong>Note:</strong> This establishment already has an action recorded. Making changes will update the existing record.
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Action Details</h5>
                </div>
                <form method="POST" enctype="multipart/form-data" id="actionForm">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Action Type</label>
                            <div class="border p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="notice_type" id="cfo" 
                                                value="CFO" <?php echo (isNoticeType($existing_notice_type, 'CFO') || isNoticeType($existing_notice_type, 'Certified First Offence')) ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="cfo">
                                                Certified First Offence (CFO)
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="notice_type" id="compliance" 
                                                value="Compliance" <?php echo (isNoticeType($existing_notice_type, 'Compliance')) ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="compliance">
                                                Complied
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="notice_type" id="fc" 
                                                value="FC" <?php echo (isNoticeType($existing_notice_type, 'FC') || isNoticeType($existing_notice_type, 'Formal Charge')) ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="fc">
                                                Formal Charge (FC)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="notice_type" id="other" 
                                                value="Other" <?php echo (isNoticeType($existing_notice_type, 'Other')) ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="other">
                                                Others
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_responded" class="form-label fw-bold">Date Responded</label>
                            <input type="date" class="form-control" id="date_responded" name="date_responded" 
                                value="<?php echo $existing_date_responded; ?>" required>
                        </div>
                        
                        <div class="mb-3" id="other_details_div" style="display: none;">
                            <label for="other_details" class="form-label fw-bold">Other Details</label>
                            <input type="text" class="form-control" id="other_details" name="other_details" 
                                placeholder="Please specify other action details">
                            <div class="form-text">Provide specific details about the action taken</div>
                        </div>
                        
                        <div class="mb-3" id="file_upload_div" style="display: none;">
                            <label for="action_file" class="form-label fw-bold">Upload File/Image</label>
                            <input type="file" class="form-control" id="action_file" name="action_file">
                            <div class="form-text">Upload supporting documents or images (required for Formal Charge and optional for Others)</div>
                        </div>
                        
                        <?php if (!empty($existing_images)): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Existing Attachments</label>
                            <div class="row">
                                <?php foreach ($existing_images as $image): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title text-truncate"><?php echo htmlspecialchars($image['image_name']); ?></h6>
                                            <?php if (isImageFile($image['image_type'])): ?>
                                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" class="img-fluid mb-2" alt="Uploaded Image">
                                            <?php else: ?>
                                                <div class="text-center py-4 bg-light mb-2">
                                                    <i class="fas fa-file-alt fa-3x text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <a href="../<?php echo htmlspecialchars($image['image_path']); ?>" class="btn btn-sm btn-primary d-block" target="_blank">
                                                <i class="fas fa-eye me-1"></i> View Full
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label fw-bold">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" 
                                placeholder="Optional remarks"><?php echo htmlspecialchars($existing_remarks); ?></textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="nov_form.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="submit_action" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> <?php echo $has_existing_record ? 'Update' : 'Submit'; ?> Action
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to check if file type is an image
function isImageFile($file_type) {
    $image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp', 'image/tiff'];
    return in_array(strtolower($file_type), $image_types);
}
?>

<script>
    // Function to toggle form fields based on selection
    function toggleFields() {
        const selectedAction = document.querySelector('input[name="notice_type"]:checked')?.value;
        const fileUploadDiv = document.getElementById('file_upload_div');
        const otherDetailsDiv = document.getElementById('other_details_div');
        
        // Hide all conditional fields first
        fileUploadDiv.style.display = 'none';
        otherDetailsDiv.style.display = 'none';
        
        // Show relevant fields based on selection
        if (selectedAction === 'FC') {
            fileUploadDiv.style.display = 'block';
        } else if (selectedAction === 'Other') {
            otherDetailsDiv.style.display = 'block';
            fileUploadDiv.style.display = 'block';
        }
    }

    // Add event listeners to all radio buttons
    document.querySelectorAll('input[name="notice_type"]').forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });
    
    // Initialize fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleFields();
        
        // Validate form submission
        document.getElementById('actionForm').addEventListener('submit', function(event) {
            const selectedAction = document.querySelector('input[name="notice_type"]:checked')?.value;
            const fileInput = document.getElementById('action_file');
            
            if (selectedAction === 'FC' && fileInput.files.length === 0 && !<?php echo $has_existing_record ? 'true' : 'false'; ?>) {
                event.preventDefault();
                alert('Please upload a file for Formal Charge.');
            }
        });
    });
</script>

<?php 
include '../templates/footer.php'; 
// Flush the output buffer
ob_end_flush();
?>