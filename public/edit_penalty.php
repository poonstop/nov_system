<?php
ob_start();
require_once '../connection.php';
include '../templates/header.php';

// Get penalty ID from URL
$penalty_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_penalty'])) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Update the penalty
        $update_sql = "UPDATE penalties 
                       SET amount = :amount, 
                           description = :description,
                           reference_number = :reference_number,
                           status = :status,
                           issued_by = :issued_by
                       WHERE penalty_id = :penalty_id";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':amount', $_POST['amount']);
        $update_stmt->bindParam(':description', $_POST['penalty_description']);
        $update_stmt->bindParam(':reference_number', $_POST['reference_number']);
        $update_stmt->bindParam(':status', $_POST['status']);
        $update_stmt->bindParam(':issued_by', $_POST['issued_by']);
        $update_stmt->bindParam(':penalty_id', $penalty_id);
        $update_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Get establishment ID for redirect
        $fetch_est_sql = "SELECT establishment_id FROM penalties WHERE penalty_id = :penalty_id";
        $fetch_est_stmt = $conn->prepare($fetch_est_sql);
        $fetch_est_stmt->bindParam(':penalty_id', $penalty_id);
        $fetch_est_stmt->execute();
        $establishment_id = $fetch_est_stmt->fetchColumn();
        
        // Redirect back to edit establishment page
        header("Location: edit_establishment.php?id=$establishment_id&penalty_updated=1#penalties");
        exit;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Fetch penalty data
$fetch_sql = "SELECT p.*, e.name as establishment_name, e.establishment_id 
              FROM penalties p 
              JOIN establishments e ON p.establishment_id = e.establishment_id 
              WHERE p.penalty_id = :penalty_id";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bindParam(':penalty_id', $penalty_id);
$fetch_stmt->execute();
$penalty = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

// If penalty not found, redirect to list page
if (!$penalty) {
    header('Location: index.php?error=not_found');
    exit;
}

$establishment_id = $penalty['establishment_id'];
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Establishments</a></li>
                    <li class="breadcrumb-item"><a href="edit_establishment.php?id=<?php echo $establishment_id; ?>">Edit Establishment</a></li>
                    <li class="breadcrumb-item active">Edit Penalty</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-money-bill-wave me-2"></i> Edit Penalty</h2>
                <div>
                    <a href="edit_establishment.php?id=<?php echo $establishment_id; ?>#penalties" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Establishment
                    </a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Edit Penalty for <?php echo htmlspecialchars($penalty['establishment_name']); ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Penalty Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" 
                                           value="<?php echo htmlspecialchars($penalty['amount']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" 
                                       value="<?php echo htmlspecialchars($penalty['reference_number']); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Payment Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Unpaid" <?php echo ($penalty['status'] == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                    <option value="Paid" <?php echo ($penalty['status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                    <option value="Partial" <?php echo ($penalty['status'] == 'Partial') ? 'selected' : ''; ?>>Partially Paid</option>
                                    <option value="Contested" <?php echo ($penalty['status'] == 'Contested') ? 'selected' : ''; ?>>Contested</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="issued_by" class="form-label">Issued By <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="issued_by" name="issued_by" 
                                       value="<?php echo htmlspecialchars($penalty['issued_by']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="penalty_description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="penalty_description" name="penalty_description" rows="3" required><?php echo htmlspecialchars($penalty['description']); ?></textarea>
                            <small class="text-muted">Describe the reason for the penalty in detail.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Date Issued</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($penalty['created_at'])); ?>" readonly>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="update_penalty" class="btn btn-danger">
                                <i class="fas fa-save me-1"></i> Update Penalty
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>