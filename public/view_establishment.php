<?php
require_once '../connection.php';
include '../templates/header.php';

// Check if establishment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect back to establishments list if no ID provided
    header("Location: nov_form.php");
    exit();
}

$establishment_id = intval($_GET['id']);

// Fetch establishment details
$estab_query = $conn->prepare("
    SELECT e.*, ns.issued_by, ns.position, ns.issued_datetime, ns.witnessed_by 
    FROM establishments e
    LEFT JOIN notice_status ns ON e.establishment_id = ns.establishment_id 
    WHERE e.establishment_id = ?
");
$estab_query->bind_param("i", $establishment_id);
$estab_query->execute();
$establishment = $estab_query->get_result()->fetch_assoc();

// If establishment doesn't exist, redirect back
if (!$establishment) {
    header("Location: nov_form.php");
    exit();
}

// Fetch address details
$address_query = $conn->prepare("
    SELECT * FROM addresses 
    WHERE establishment_id = ?
");
$address_query->bind_param("i", $establishment_id);
$address_query->execute();
$address = $address_query->get_result()->fetch_assoc();

// Fetch inventory items
$inventory_query = $conn->prepare("
    SELECT * FROM inventory 
    WHERE establishment_id = ? 
    ORDER BY date_created DESC
");
$inventory_query->bind_param("i", $establishment_id);
$inventory_query->execute();
$inventory_result = $inventory_query->get_result();

// Fetch notice status history
$notice_history_query = $conn->prepare("
    SELECT * FROM notice_status 
    WHERE establishment_id = ? 
    ORDER BY issued_datetime DESC
");
$notice_history_query->bind_param("i", $establishment_id);
$notice_history_query->execute();
$notice_history_result = $notice_history_query->get_result();

// Log the view action
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$action = "Viewed establishment";
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$details = "Viewed establishment ID: $establishment_id - " . $establishment['name'];

$log_query = $conn->prepare("INSERT INTO user_logs (user_id, action, user_agent, details, timestamp) VALUES (?, ?, ?, ?, NOW())");
$log_query->bind_param("isss", $user_id, $action, $user_agent, $details);
$log_query->execute();

// Helper function to get the appropriate Bootstrap badge class based on status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Complied':
            return 'bg-success';
        case 'Non-Compliant':
            return 'bg-danger';
        case 'In Progress':
            return 'bg-warning';
        case 'Closed':
            return 'bg-secondary';
        case 'Pending':
        default:
            return 'bg-info';
    }
}

// Format a full address from components
function formatFullAddress($address) {
    if (!$address) return 'No address information available';
    
    $components = [];
    if (!empty($address['street'])) $components[] = $address['street'];
    if (!empty($address['barangay'])) $components[] = 'Barangay ' . $address['barangay'];
    if (!empty($address['municipality'])) $components[] = $address['municipality'];
    if (!empty($address['province'])) $components[] = $address['province'];
    if (!empty($address['region'])) $components[] = $address['region'];
    
    return implode(', ', $components);
}
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="nov_form.php">Establishments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Establishment</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2><?php echo htmlspecialchars($establishment['name']); ?></h2>
                <div>
                    <a href="nov_form.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="edit_establishment.php?id=<?php echo $establishment_id; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Establishment Details Card -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>Establishment Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($establishment['name']); ?></p>
                            <p><strong>Owner/Representative:</strong> <?php echo htmlspecialchars($establishment['owner_representative'] ?? 'Not specified'); ?></p>
                            <p><strong>Nature of Business:</strong> <?php echo htmlspecialchars($establishment['nature']); ?></p>
                            <p><strong>Products:</strong> <?php echo htmlspecialchars($establishment['products']); ?></p>
                            <p>
                                <strong>Status:</strong> 
                                <span class="badge <?php echo getStatusBadgeClass($establishment['notice_status']); ?>">
                                    <?php echo htmlspecialchars($establishment['notice_status'] ?? 'Pending'); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($establishment['date_created'])); ?></p>
                            <p><strong>Last Updated:</strong> <?php echo date('M d, Y h:i A', strtotime($establishment['date_updated'])); ?></p>
                            <p><strong>Notice Status Updated:</strong> 
                                <?php echo $establishment['issued_datetime'] ? date('M d, Y h:i A', strtotime($establishment['issued_datetime'])) : 'N/A'; ?>
                            </p>
                            <p><strong>Number of Violations:</strong> <?php echo htmlspecialchars($establishment['num_violations'] ?? 'Not specified'); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars(formatFullAddress($address)); ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($establishment['nov_files'])): ?>
                    <div class="mt-3">
                        <strong>Notice of Violation Files:</strong>
                        <p>
                            <a href="../uploads/<?php echo htmlspecialchars($establishment['nov_files']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-pdf"></i> View NOV Document
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Violations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="violations-container">
                        <?php if (!empty($establishment['violations'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($establishment['violations'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">No violations recorded.</p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($establishment['remarks'])): ?>
                        <div class="mt-3">
                            <h6>Remarks:</h6>
                            <p><?php echo nl2br(htmlspecialchars($establishment['remarks'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Current Notice Status Details -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>Current Notice Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Status:</strong> 
                                <span class="badge <?php echo getStatusBadgeClass($establishment['notice_status']); ?>">
                                    <?php echo htmlspecialchars($establishment['notice_status'] ?? 'Pending'); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Issued By:</strong> <?php echo htmlspecialchars($establishment['issued_by'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Position:</strong> <?php echo htmlspecialchars($establishment['position'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Witnessed By:</strong> <?php echo htmlspecialchars($establishment['witnessed_by'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inventory Items -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-boxes me-2"></i>Inventory Items
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($inventory_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Pieces</th>
                                        <th>Status</th>
                                        <th>Violations</th>
                                        <th>Remarks</th>
                                        <th>Date Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $inventory_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['description'] ?? 'N/A'); ?></td>
                                            <td>₱<?php echo number_format($item['price'] ?? 0, 2); ?></td>
                                            <td><?php echo htmlspecialchars($item['pieces'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($item['sealed']): ?>
                                                    <span class="badge bg-danger">Sealed</span>
                                                <?php endif; ?>
                                                <?php if ($item['withdrawn']): ?>
                                                    <span class="badge bg-warning">Withdrawn</span>
                                                <?php endif; ?>
                                                <?php if (!$item['sealed'] && !$item['withdrawn']): ?>
                                                    <span class="badge bg-success">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item['dao_violation']): ?>
                                                    <span class="badge bg-danger">DAO Violation</span>
                                                <?php endif; ?>
                                                <?php if ($item['other_violation']): ?>
                                                    <span class="badge bg-warning">Other Violation</span>
                                                <?php endif; ?>
                                                <?php if (!$item['dao_violation'] && !$item['other_violation']): ?>
                                                    <span class="badge bg-secondary">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['inv_remarks'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($item['date_created'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No inventory items recorded for this establishment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notice Status History -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Notice Status History
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($notice_history_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Status</th>
                                        <th>Issued By</th>
                                        <th>Position</th>
                                        <th>Witnessed By</th>
                                        <th>Date Issued</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($history = $notice_history_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?php echo getStatusBadgeClass($history['status']); ?>">
                                                    <?php echo htmlspecialchars($history['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($history['issued_by'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($history['position'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($history['witnessed_by'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($history['issued_datetime'])); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($history['updated_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No notice status history available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>