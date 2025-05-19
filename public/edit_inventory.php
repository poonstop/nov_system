<?php
ob_start();
require_once '../connection.php';
include '../templates/header.php';

// Get inventory ID from URL
$inventory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize success message
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_inventory'])) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Build the update query dynamically
        $update_fields = [];
        $params = [':inventory_id' => $inventory_id];
        
        // Add all fields to the update query
        if (isset($_POST['product_name'])) {
            $update_fields[] = "product_name = :product_name";
            $params[':product_name'] = $_POST['product_name'];
        }
        
        if (isset($_POST['price'])) {
            $update_fields[] = "price = :price";
            $params[':price'] = $_POST['price'];
        }
        
        if (isset($_POST['pieces'])) {
            $update_fields[] = "pieces = :pieces";
            $params[':pieces'] = $_POST['pieces'];
        }
        
        if (isset($_POST['description'])) {
            $update_fields[] = "description = :description";
            $params[':description'] = $_POST['description'];
        }
        
        if (isset($_POST['product_remarks'])) {
            $update_fields[] = "product_remarks = :product_remarks";
            $params[':product_remarks'] = $_POST['product_remarks'];
        }
        
        // Handle checkboxes
        $sealed = isset($_POST['sealed']) ? 1 : 0;
        $update_fields[] = "sealed = :sealed";
        $params[':sealed'] = $sealed;
        
        $withdrawn = isset($_POST['withdrawn']) ? 1 : 0;
        $update_fields[] = "withdrawn = :withdrawn";
        $params[':withdrawn'] = $withdrawn;
        
        $dao_violation = isset($_POST['dao_violation']) ? 1 : 0;
        $update_fields[] = "dao_violation = :dao_violation";
        $params[':dao_violation'] = $dao_violation;
        
        $other_violation = isset($_POST['other_violation']) ? 1 : 0;
        $update_fields[] = "other_violation = :other_violation";
        $params[':other_violation'] = $other_violation;
        
        // Update the inventory item
        $update_sql = "UPDATE inventory SET " . implode(", ", $update_fields) . " WHERE inventory_id = :inventory_id";
        $update_stmt = $conn->prepare($update_sql);
        
        foreach ($params as $key => $value) {
            $update_stmt->bindValue($key, $value);
        }
        
        $update_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        $success_message = "Inventory item updated successfully!";
        
        // Additional option: If you want to redirect with a success parameter instead of showing message on same page
        // Get establishment ID for redirect
        $fetch_est_sql = "SELECT establishment_id FROM inventory WHERE inventory_id = :inventory_id";
        $fetch_est_stmt = $conn->prepare($fetch_est_sql);
        $fetch_est_stmt->bindParam(':inventory_id', $inventory_id);
        $fetch_est_stmt->execute();
        $establishment_id = $fetch_est_stmt->fetchColumn();
        
        // Uncomment the following two lines if you want to redirect instead of showing in-page message
        // header("Location: edit_establishment.php?id=$establishment_id&inventory_updated=1#inventory");
        // exit;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Fetch inventory item data
$fetch_sql = "SELECT i.*, e.name as establishment_name, e.establishment_id 
              FROM inventory i 
              JOIN establishments e ON i.establishment_id = e.establishment_id 
              WHERE i.inventory_id = :inventory_id";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bindParam(':inventory_id', $inventory_id);
$fetch_stmt->execute();
$inventory = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

// If inventory item not found, redirect to list page
if (!$inventory) {
    header('Location: index.php?error=not_found');
    exit;
}

$establishment_id = $inventory['establishment_id'];
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Establishments</a></li>
                    <li class="breadcrumb-item"><a href="edit_establishment.php?id=<?php echo $establishment_id; ?>">Edit Establishment</a></li>
                    <li class="breadcrumb-item active">Edit Inventory Item</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-edit me-2"></i> Edit Inventory Item</h2>
                <div>
                    <a href="edit_establishment.php?id=<?php echo $establishment_id; ?>#inventory" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Establishment
                    </a>
                </div>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i> Edit Inventory Item for <?php echo htmlspecialchars($inventory['establishment_name']); ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_name" name="product_name" 
                                       value="<?php echo htmlspecialchars($inventory['product_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($inventory['price']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="pieces" class="form-label">Pieces/Quantity</label>
                                <input type="number" class="form-control" id="pieces" name="pieces" min="0"
                                       value="<?php echo htmlspecialchars($inventory['pieces']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sealed" name="sealed"
                                           <?php echo ($inventory['sealed'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="sealed">Sealed</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="withdrawn" name="withdrawn"
                                           <?php echo ($inventory['withdrawn'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="withdrawn">Withdrawn</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?php echo htmlspecialchars($inventory['description']); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Violation Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dao_violation" name="dao_violation"
                                           <?php echo ($inventory['dao_violation'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="dao_violation">DAO Violation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="other_violation" name="other_violation"
                                           <?php echo ($inventory['other_violation'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="other_violation">Other Violation</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="product_remarks" class="form-label">Product Remarks</label>
                                <textarea class="form-control" id="product_remarks" name="product_remarks" rows="2"><?php echo htmlspecialchars($inventory['product_remarks']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Date Added</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($inventory['date_created'])); ?>" readonly>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="update_inventory" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Inventory Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>