<?php
require_once '../connection.php';


// Get establishment ID from URL
$establishment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_establishment'])) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Fetch existing establishment data
        $current_data_sql = "SELECT * FROM establishments WHERE establishment_id = :establishment_id";
        $current_stmt = $conn->prepare($current_data_sql);
        $current_stmt->bindParam(':establishment_id', $establishment_id);
        $current_stmt->execute();
        $current_data = $current_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Build the update query dynamically based on what was submitted
        $update_fields = [];
        $params = [':establishment_id' => $establishment_id];
        
        // Check each field and only update if it's different from current value
        if (isset($_POST['name']) && $_POST['name'] !== $current_data['name']) {
            $update_fields[] = "name = :name";
            $params[':name'] = $_POST['name'];
        }
        
        if (isset($_POST['owner_representative']) && $_POST['owner_representative'] !== $current_data['owner_representative']) {
            $update_fields[] = "owner_representative = :owner_representative";
            $params[':owner_representative'] = $_POST['owner_representative'];
        }
        
        if (isset($_POST['nature']) && $_POST['nature'] !== $current_data['nature']) {
            $update_fields[] = "nature = :nature";
            $params[':nature'] = $_POST['nature'];
        }
        
        if (isset($_POST['products']) && $_POST['products'] !== $current_data['products']) {
            $update_fields[] = "products = :products";
            $params[':products'] = $_POST['products'];
        }
        
        if (isset($_POST['violations']) && $_POST['violations'] !== $current_data['violations']) {
            $update_fields[] = "violations = :violations";
            $params[':violations'] = $_POST['violations'];
        }
        
        if (isset($_POST['remarks']) && $_POST['remarks'] !== $current_data['remarks']) {
            $update_fields[] = "remarks = :remarks";
            $params[':remarks'] = $_POST['remarks'];
        }
        
        // Only update if there are changes
        if (!empty($update_fields)) {
            $update_fields[] = "date_updated = NOW()";
            
            $update_sql = "UPDATE establishments SET " . implode(", ", $update_fields) . " WHERE establishment_id = :establishment_id";
            
            $update_stmt = $conn->prepare($update_sql);
            foreach ($params as $key => $value) {
                $update_stmt->bindValue($key, $value);
            }
            $update_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect with success message
            header("Location: edit_establishment.php?id=$establishment_id&updated=1");
            exit;
        } else {
            // No changes were made
            $conn->commit();
            header("Location: edit_establishment.php?id=$establishment_id&no_changes=1");
            exit;
        }
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error_message = "Database error: " . $e->getMessage();
    }
}
include '../templates/header.php';
// Handle inventory form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_inventory'])) {
    try {
        // Insert new inventory item
        $inventory_sql = "INSERT INTO inventory 
                          (establishment_id, product_name, sealed, withdrawn, description, 
                           price, pieces, dao_violation, other_violation, product_remarks) 
                          VALUES 
                          (:establishment_id, :product_name, :sealed, :withdrawn, :description, 
                           :price, :pieces, :dao_violation, :other_violation, :product_remarks)";
        
        $inventory_stmt = $conn->prepare($inventory_sql);
        $inventory_stmt->bindParam(':establishment_id', $establishment_id);
        $inventory_stmt->bindParam(':product_name', $_POST['product_name']);
        
        $sealed = isset($_POST['sealed']) ? 1 : 0;
        $withdrawn = isset($_POST['withdrawn']) ? 1 : 0;
        $dao_violation = isset($_POST['dao_violation']) ? 1 : 0;
        $other_violation = isset($_POST['other_violation']) ? 1 : 0;
        
        $inventory_stmt->bindParam(':sealed', $sealed, PDO::PARAM_INT);
        $inventory_stmt->bindParam(':withdrawn', $withdrawn, PDO::PARAM_INT);
        $inventory_stmt->bindParam(':description', $_POST['description']);
        $inventory_stmt->bindParam(':price', $_POST['price']);
        $inventory_stmt->bindParam(':pieces', $_POST['pieces']);
        $inventory_stmt->bindParam(':dao_violation', $dao_violation, PDO::PARAM_INT);
        $inventory_stmt->bindParam(':other_violation', $other_violation, PDO::PARAM_INT);
        $inventory_stmt->bindParam(':product_remarks', $_POST['product_remarks']);
        $inventory_stmt->execute();
        
        // Redirect with success message
        header("Location: edit_establishment.php?id=$establishment_id&inventory_added=1");
        exit;
        
    } catch (PDOException $e) {
        $inventory_error = "Database error: " . $e->getMessage();
    }
}

// Handle add issuer form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_issuer'])) {
    try {
        // Insert new issuer
        $issuer_sql = "INSERT INTO notice_issuers 
                       (establishment_id, issuer_name, issuer_position) 
                       VALUES 
                       (:establishment_id, :issuer_name, :issuer_position)";
        
        $issuer_stmt = $conn->prepare($issuer_sql);
        $issuer_stmt->bindParam(':establishment_id', $establishment_id);
        $issuer_stmt->bindParam(':issuer_name', $_POST['issuer_name']);
        $issuer_stmt->bindParam(':issuer_position', $_POST['issuer_position']);
        $issuer_stmt->execute();
        
        // Redirect with success message
        header("Location: edit_establishment.php?id=$establishment_id&issuer_added=1");
        exit;
        
    } catch (PDOException $e) {
        $issuer_error = "Database error: " . $e->getMessage();
    }
}

// Handle add penalty form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_penalty'])) {
    try {
        // Insert new penalty
        $penalty_sql = "INSERT INTO penalties 
                       (establishment_id, amount, description, reference_number, status, issued_by) 
                       VALUES 
                       (:establishment_id, :amount, :description, :reference_number, :status, :issued_by)";
        
        $penalty_stmt = $conn->prepare($penalty_sql);
        $penalty_stmt->bindParam(':establishment_id', $establishment_id);
        $penalty_stmt->bindParam(':amount', $_POST['amount']);
        $penalty_stmt->bindParam(':description', $_POST['penalty_description']);
        $penalty_stmt->bindParam(':reference_number', $_POST['reference_number']);
        $penalty_stmt->bindParam(':status', $_POST['status']);
        $penalty_stmt->bindParam(':issued_by', $_POST['issued_by']);
        $penalty_stmt->execute();
        
        // Redirect with success message
        header("Location: edit_establishment.php?id=$establishment_id&penalty_added=1");
        exit;
        
    } catch (PDOException $e) {
        $penalty_error = "Database error: " . $e->getMessage();
    }
}

// Fetch establishment data
$fetch_sql = "SELECT * FROM establishments WHERE establishment_id = :establishment_id";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bindParam(':establishment_id', $establishment_id);
$fetch_stmt->execute();
$establishment = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

// If establishment not found, redirect to list page
if (!$establishment) {
    header('Location: index.php?error=not_found');
    exit;
}

// Fetch address data
$address_sql = "SELECT * FROM addresses WHERE establishment_id = :establishment_id";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bindParam(':establishment_id', $establishment_id);
$address_stmt->execute();
$address = $address_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch inventory items
$inventory_sql = "SELECT * FROM inventory WHERE establishment_id = :establishment_id ORDER BY date_created DESC";
$inventory_stmt = $conn->prepare($inventory_sql);
$inventory_stmt->bindParam(':establishment_id', $establishment_id);
$inventory_stmt->execute();
$inventory_items = $inventory_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch issuers
$issuers_sql = "SELECT * FROM notice_issuers WHERE establishment_id = :establishment_id ORDER BY created_at DESC";
$issuers_stmt = $conn->prepare($issuers_sql);
$issuers_stmt->bindParam(':establishment_id', $establishment_id);
$issuers_stmt->execute();
$issuers = $issuers_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch penalties
$penalties_sql = "SELECT * FROM penalties WHERE establishment_id = :establishment_id ORDER BY created_at DESC";
$penalties_stmt = $conn->prepare($penalties_sql);
$penalties_stmt->bindParam(':establishment_id', $establishment_id);
$penalties_stmt->execute();
$penalties = $penalties_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Establishments</a></li>
                    <li class="breadcrumb-item active">Edit Establishment</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-edit me-2"></i> Edit Establishment</h2>
                <div>
                    <a href="view_establishment.php?id=<?php echo $establishment_id; ?>" class="btn btn-info me-2">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            
            <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Establishment details updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['no_changes']) && $_GET['no_changes'] == 1): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i> No changes were detected. The establishment details remain the same.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['inventory_added']) && $_GET['inventory_added'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Inventory item added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['issuer_added']) && $_GET['issuer_added'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Notice issuer added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['penalty_added']) && $_GET['penalty_added'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Penalty added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Edit Tabs -->
            <ul class="nav nav-tabs mb-3" id="editTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" 
                            type="button" role="tab" aria-controls="details" aria-selected="true">
                        <i class="fas fa-building me-1"></i> Establishment Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" 
                            type="button" role="tab" aria-controls="inventory" aria-selected="false">
                        <i class="fas fa-boxes me-1"></i> Inventory (<?php echo count($inventory_items); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="issuers-tab" data-bs-toggle="tab" data-bs-target="#issuers" 
                            type="button" role="tab" aria-controls="issuers" aria-selected="false">
                        <i class="fas fa-user-tie me-1"></i> Notice Issuers (<?php echo count($issuers); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="penalties-tab" data-bs-toggle="tab" data-bs-target="#penalties" 
                            type="button" role="tab" aria-controls="penalties" aria-selected="false">
                        <i class="fas fa-money-bill-wave me-1"></i> Penalties (<?php echo count($penalties); ?>)
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="editTabsContent">
                <!-- Establishment Details Tab -->
                <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Establishment Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Establishment Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                            value="<?php echo htmlspecialchars($establishment['name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="owner_representative" class="form-label">Owner/Representative</label>
                                        <input type="text" class="form-control" id="owner_representative" name="owner_representative" 
                                               value="<?php echo htmlspecialchars($establishment['owner_representative'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nature" class="form-label">Nature of Business <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nature" name="nature" 
                                            value="<?php echo htmlspecialchars($establishment['nature']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="notice_status" class="form-label">Current Status</label>
                                        <input type="text" class="form-control" id="notice_status" 
                                               value="<?php echo htmlspecialchars($establishment['notice_status'] ?? 'Pending'); ?>" readonly>
                                        <small class="text-muted">Status can be updated from the main establishments page</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="products" class="form-label">Products</label>
                                    <textarea class="form-control" id="products" name="products" rows="2"><?php echo htmlspecialchars($establishment['products']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="violations" class="form-label">Violations <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="violations" name="violations" rows="4"><?php echo htmlspecialchars($establishment['violations']); ?></textarea>
                                    <small class="text-muted">Describe all violations in detail. You can add more violations here.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($establishment['remarks']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>Address Information</h6>
                                    <p>
                                        <?php if ($address): ?>
                                            <?php echo htmlspecialchars($address['street'] . ', ' . $address['barangay'] . ', ' . 
                                                   $address['municipality'] . ', ' . $address['province'] . ', ' . $address['region']); ?>
                                            <a href="edit_address.php?id=<?php echo $establishment_id; ?>" class="btn btn-sm btn-outline-secondary ms-2">
                                                <i class="fas fa-map-marker-alt me-1"></i> Edit Address
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No address information available.</span>
                                            <a href="add_address.php?id=<?php echo $establishment_id; ?>" class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="fas fa-plus-circle me-1"></i> Add Address
                                            </a>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="update_establishment" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Establishment Details
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Inventory Tab -->
                <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New Inventory Item</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($inventory_error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $inventory_error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="price" class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="pieces" class="form-label">Pieces/Quantity</label>
                                        <input type="number" class="form-control" id="pieces" name="pieces" min="0">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="sealed" name="sealed">
                                            <label class="form-check-label" for="sealed">Sealed</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="withdrawn" name="withdrawn">
                                            <label class="form-check-label" for="withdrawn">Withdrawn</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Violation Type</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="dao_violation" name="dao_violation">
                                            <label class="form-check-label" for="dao_violation">DAO Violation</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="other_violation" name="other_violation">
                                            <label class="form-check-label" for="other_violation">Other Violation</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="product_remarks" class="form-label">Product Remarks</label>
                                        <textarea class="form-control" id="product_remarks" name="product_remarks" rows="2"></textarea>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="add_inventory" class="btn btn-success">
                                        <i class="fas fa-plus-circle me-1"></i> Add Inventory Item
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Inventory List -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Current Inventory Items</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Violation Type</th>
                                            <th>Date Added</th>
                                          
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($inventory_items) > 0): ?>
                                            <?php foreach ($inventory_items as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo htmlspecialchars($item['pieces'] ?? '0'); ?></td>
                                                    <td>
                                                        <?php if ($item['sealed'] == 1): ?>
                                                            <span class="badge bg-warning text-dark">Sealed</span>
                                                        <?php endif; ?>
                                                        <?php if ($item['withdrawn'] == 1): ?>
                                                            <span class="badge bg-danger">Withdrawn</span>
                                                        <?php endif; ?>
                                                        <?php if ($item['sealed'] == 0 && $item['withdrawn'] == 0): ?>
                                                            <span class="badge bg-secondary">Regular</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($item['dao_violation'] == 1): ?>
                                                            <span class="badge bg-info">DAO</span>
                                                        <?php endif; ?>
                                                        <?php if ($item['other_violation'] == 1): ?>
                                                            <span class="badge bg-secondary">Other</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($item['date_created'])); ?></td>
                                                    <td>
                                                        <a href="edit_inventory.php?id=<?php echo $item['inventory_id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-3">No inventory items found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Issuers Tab -->
                <div class="tab-pane fade" id="issuers" role="tabpanel" aria-labelledby="issuers-tab">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> Add Notice Issuer</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($issuer_error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $issuer_error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="issuer_name" class="form-label">Issuer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="issuer_name" name="issuer_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="issuer_position" class="form-label">Position <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="issuer_position" name="issuer_position" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="add_issuer" class="btn btn-info">
                                        <i class="fas fa-plus-circle me-1"></i> Add Notice Issuer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Issuers List -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i> Current Notice Issuers</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Date Added</th>
                                       
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($issuers) > 0): ?>
                                            <?php foreach ($issuers as $issuer): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($issuer['issuer_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($issuer['issuer_position']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($issuer['created_at'])); ?></td>
                                                    <td>
                                                        <a href="edit_issuer.php?id=<?php echo $issuer['issuer_id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3">No notice issuers found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Penalties Tab -->
                <div class="tab-pane fade" id="penalties" role="tabpanel" aria-labelledby="penalties-tab">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Add Penalty</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($penalty_error)): ?>
                                <div class="alert alert-danger">
                                    <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $penalty_error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="amount" class="form-label">Penalty Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reference_number" class="form-label">Reference Number</label>
                                        <input type="text" class="form-control" id="reference_number" name="reference_number">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Payment Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Unpaid">Unpaid</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Partial">Partially Paid</option>
                                            <option value="Contested">Contested</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="issued_by" class="form-label">Issued By <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="issued_by" name="issued_by" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="penalty_description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="penalty_description" name="penalty_description" rows="3" required></textarea>
                                    <small class="text-muted">Describe the reason for the penalty in detail.</small>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="add_penalty" class="btn btn-danger">
                                        <i class="fas fa-plus-circle me-1"></i> Add Penalty
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Penalties List -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i> Current Penalties</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Amount</th>
                                            <th>Reference #</th>
                                            <th>Status</th>
                                            <th>Issued By</th>
                                            <th>Date Issued</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($penalties) > 0): ?>
                                            <?php foreach ($penalties as $penalty): ?>
                                                <tr>
                                                    <td>₱<?php echo number_format($penalty['amount'], 2); ?></td>
                                                    <td><?php echo htmlspecialchars($penalty['reference_number'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <?php
                                                        $status_badge = 'bg-secondary';
                                                        if ($penalty['status'] == 'Paid') {
                                                            $status_badge = 'bg-success';
                                                        } elseif ($penalty['status'] == 'Unpaid') {
                                                            $status_badge = 'bg-danger';
                                                        } elseif ($penalty['status'] == 'Partial') {
                                                            $status_badge = 'bg-warning text-dark';
                                                        } elseif ($penalty['status'] == 'Contested') {
                                                            $status_badge = 'bg-info text-dark';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $status_badge; ?>">
                                                            <?php echo htmlspecialchars($penalty['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($penalty['issued_by']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($penalty['created_at'])); ?></td>
                                                    <td>
                                                        <a href="edit_penalty.php?id=<?php echo $penalty['penalty_id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#penaltyModal<?php echo $penalty['penalty_id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Penalty Details Modal -->
                                                <div class="modal fade" id="penaltyModal<?php echo $penalty['penalty_id']; ?>" tabindex="-1" aria-labelledby="penaltyModalLabel<?php echo $penalty['penalty_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-info text-white">
                                                                <h5 class="modal-title" id="penaltyModalLabel<?php echo $penalty['penalty_id']; ?>">
                                                                    <i class="fas fa-money-bill-wave me-2"></i> Penalty Details
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <h6>Amount:</h6>
                                                                    <p class="fw-bold">₱<?php echo number_format($penalty['amount'], 2); ?></p>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6>Reference Number:</h6>
                                                                    <p><?php echo htmlspecialchars($penalty['reference_number'] ?? 'N/A'); ?></p>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6>Description:</h6>
                                                                    <p><?php echo nl2br(htmlspecialchars($penalty['description'])); ?></p>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6>Status:</h6>
                                                                    <p>
                                                                        <span class="badge <?php echo $status_badge; ?>">
                                                                            <?php echo htmlspecialchars($penalty['status']); ?>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6>Issued By:</h6>
                                                                    <p><?php echo htmlspecialchars($penalty['issued_by']); ?></p>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6>Date Issued:</h6>
                                                                    <p><?php echo date('F d, Y', strtotime($penalty['created_at'])); ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <a href="edit_penalty.php?id=<?php echo $penalty['penalty_id']; ?>" class="btn btn-warning">
                                                                    <i class="fas fa-edit me-1"></i> Edit
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-3">No penalties found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix for tab navigation - ensure tabs work independently of form validation
    const tabLinks = document.querySelectorAll('.nav-link');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Prevent default behavior of Bootstrap if needed
            // e.preventDefault();
            
            // Get the target tab from the data attribute
            const target = this.getAttribute('data-bs-target');
            
            // Deactivate all tabs and contents
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
                tab.setAttribute('aria-selected', 'false');
            });
            
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Activate the clicked tab and its content
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            document.querySelector(target).classList.add('show', 'active');
        });
    });
    
    // Display a message if there were no changes
    <?php if (isset($_GET['no_changes']) && $_GET['no_changes'] == 1): ?>
    const alertContainer = document.querySelector('.col');
    const noChangesAlert = document.createElement('div');
    noChangesAlert.className = 'alert alert-info alert-dismissible fade show';
    noChangesAlert.setAttribute('role', 'alert');
    noChangesAlert.innerHTML = `
        <i class="fas fa-info-circle me-2"></i> No changes were detected. Nothing was updated.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Insert after breadcrumbs
    const breadcrumb = document.querySelector('.breadcrumb');
    breadcrumb.parentNode.insertBefore(noChangesAlert, breadcrumb.nextSibling);
    <?php endif; ?>
});
</script>

<?php include '../templates/footer.php'; ?>