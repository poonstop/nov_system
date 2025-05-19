<?php
ob_start();
require_once '../connection.php';
include '../templates/header.php';

// Get issuer ID from URL
$issuer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_issuer'])) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Update the issuer
        $update_sql = "UPDATE notice_issuers 
                       SET issuer_name = :issuer_name, 
                           issuer_position = :issuer_position 
                       WHERE issuer_id = :issuer_id";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':issuer_name', $_POST['issuer_name']);
        $update_stmt->bindParam(':issuer_position', $_POST['issuer_position']);
        $update_stmt->bindParam(':issuer_id', $issuer_id);
        $update_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Get establishment ID for redirect
        $fetch_est_sql = "SELECT establishment_id FROM notice_issuers WHERE issuer_id = :issuer_id";
        $fetch_est_stmt = $conn->prepare($fetch_est_sql);
        $fetch_est_stmt->bindParam(':issuer_id', $issuer_id);
        $fetch_est_stmt->execute();
        $establishment_id = $fetch_est_stmt->fetchColumn();
        
        // Redirect back to edit establishment page
        header("Location: edit_establishment.php?id=$establishment_id&issuer_updated=1#issuers");
        exit;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Fetch issuer data
$fetch_sql = "SELECT i.*, e.name as establishment_name, e.establishment_id 
              FROM notice_issuers i 
              JOIN establishments e ON i.establishment_id = e.establishment_id 
              WHERE i.issuer_id = :issuer_id";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bindParam(':issuer_id', $issuer_id);
$fetch_stmt->execute();
$issuer = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

// If issuer not found, redirect to list page
if (!$issuer) {
    header('Location: index.php?error=not_found');
    exit;
}

$establishment_id = $issuer['establishment_id'];
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Establishments</a></li>
                    <li class="breadcrumb-item"><a href="edit_establishment.php?id=<?php echo $establishment_id; ?>">Edit Establishment</a></li>
                    <li class="breadcrumb-item active">Edit Notice Issuer</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-user-edit me-2"></i> Edit Notice Issuer</h2>
                <div>
                    <a href="edit_establishment.php?id=<?php echo $establishment_id; ?>#issuers" class="btn btn-secondary">
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
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Edit Notice Issuer for <?php echo htmlspecialchars($issuer['establishment_name']); ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="issuer_name" class="form-label">Issuer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="issuer_name" name="issuer_name" 
                                       value="<?php echo htmlspecialchars($issuer['issuer_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="issuer_position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="issuer_position" name="issuer_position" 
                                       value="<?php echo htmlspecialchars($issuer['issuer_position']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Date Added</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($issuer['created_at'])); ?>" readonly>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="update_issuer" class="btn btn-info">
                                <i class="fas fa-save me-1"></i> Update Notice Issuer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>