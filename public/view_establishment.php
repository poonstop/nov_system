<?php
require_once '../connection.php';
include '../templates/header.php';

// Check if ID is provided in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to the main page if no ID is provided
    header("Location: nov_form.php");
    exit();
}

// Get establishment ID
$establishment_id = $_GET['id'];

// Fetch establishment details
$estab_query = $conn->prepare("
    SELECT e.*, a.street, a.barangay, a.municipality, a.province, a.region 
    FROM establishments e
    LEFT JOIN addresses a ON e.establishment_id = a.establishment_id
    WHERE e.establishment_id = ?
");
$estab_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$estab_query->execute();

if ($estab_query->rowCount() == 0) {
    // Establishment not found, redirect back
    header("Location: nov_form.php?error=not_found");
    exit();
}

// Get establishment data
$establishment = $estab_query->fetch(PDO::FETCH_ASSOC);

// Fetch inventory items
$inventory_query = $conn->prepare("
    SELECT * FROM inventory 
    WHERE establishment_id = ?
    ORDER BY date_created DESC
");
$inventory_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$inventory_query->execute();
$inventory_items = $inventory_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch notice status
$status_query = $conn->prepare("
    SELECT ns.*, ni.issuer_name, ni.issuer_position 
    FROM notice_status ns
    LEFT JOIN notice_issuers ni ON ns.establishment_id = ni.establishment_id
    WHERE ns.establishment_id = ?
    ORDER BY ni.created_at DESC
");
$status_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$status_query->execute();
$status_records = $status_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch notice records (action types)
$records_query = $conn->prepare("
    SELECT * FROM notice_records
    WHERE establishment_id = ?
    ORDER BY created_at DESC
");
$records_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$records_query->execute();
$notice_records = $records_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch images related to this establishment
$images_query = $conn->prepare("
    SELECT ni.* 
    FROM notice_images ni
    JOIN notice_records nr ON ni.record_id = nr.record_id
    WHERE nr.establishment_id = ?
    ORDER BY ni.upload_date DESC
");
$images_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$images_query->execute();
$images = $images_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch penalties
$penalties_query = $conn->prepare("
    SELECT * FROM penalties
    WHERE establishment_id = ?
    ORDER BY created_at DESC
");
$penalties_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$penalties_query->execute();
$penalties = $penalties_query->fetchAll(PDO::FETCH_ASSOC);

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

// Helper function to get the appropriate Bootstrap badge class based on action type
function getActionTypeBadgeClass($action_type) {
    switch ($action_type) {
        case 'CFO':
            return 'bg-primary';
        case 'FC':
            return 'bg-info';
        case 'Compliance':
            return 'bg-success';
        case 'Other':
        default:
            return 'bg-secondary';
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="nov_form.php">Establishments</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($establishment['name']); ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2>Establishment Details</h2>
                <div>
                    <a href="edit_establishment.php?id=<?php echo $establishment_id; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="nov_form.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Establishment information updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Establishment Information Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-building me-2"></i>Establishment Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Establishment Name:</th>
                            <td><strong><?php echo htmlspecialchars($establishment['name']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Owner/Representative:</th>
                            <td><?php echo htmlspecialchars($establishment['owner_representative'] ?? 'Not specified'); ?></td>
                        </tr>
                        <tr>
                            <th>Nature of Business:</th>
                            <td><?php echo htmlspecialchars($establishment['nature'] ?? 'Not specified'); ?></td>
                        </tr>
                        <tr>
                            <th>Products:</th>
                            <td><?php echo nl2br(htmlspecialchars($establishment['products'] ?? 'Not specified')); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Address:</th>
                            <td>
                                <?php
                                $address_parts = [];
                                if (!empty($establishment['street'])) $address_parts[] = $establishment['street'];
                                if (!empty($establishment['barangay'])) $address_parts[] = 'Brgy. ' . $establishment['barangay'];
                                if (!empty($establishment['municipality'])) $address_parts[] = $establishment['municipality'];
                                if (!empty($establishment['province'])) $address_parts[] = $establishment['province'];
                                if (!empty($establishment['region'])) $address_parts[] = $establishment['region'];
                                
                                echo !empty($address_parts) ? htmlspecialchars(implode(', ', $address_parts)) : 'No address on record';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Notice Status:</th>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($establishment['notice_status']); ?> fs-6">
                                    <?php echo htmlspecialchars($establishment['notice_status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?php echo date('F d, Y h:i A', strtotime($establishment['date_created'])); ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td><?php echo date('F d, Y h:i A', strtotime($establishment['date_updated'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Violations and Inventory Section -->
    <div class="row">
        <!-- Violations Section -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Violations</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($establishment['violations'])): ?>
                        <div class="p-3 border rounded">
                            <?php echo nl2br(htmlspecialchars($establishment['violations'])); ?>
                        </div>
                        
                        <?php if (!empty($establishment['remarks'])): ?>
                            <div class="mt-3">
                                <h5>Additional Remarks:</h5>
                                <div class="p-3 border rounded">
                                    <?php echo nl2br(htmlspecialchars($establishment['remarks'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($establishment['issued_datetime'])): ?>
                            <div class="mt-3">
                                <p><strong>Issued Date:</strong> <?php echo date('F d, Y h:i A', strtotime($establishment['issued_datetime'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($establishment['expiry_date'])): ?>
                            <div class="mt-1">
                                <p>
                                    <strong>Expiry Date:</strong> 
                                    <?php echo date('F d, Y', strtotime($establishment['expiry_date'])); ?>
                                    <?php if (strtotime($establishment['expiry_date']) < time()): ?>
                                        <span class="badge bg-danger">Expired</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">No violations recorded for this establishment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

       <!-- Inventory Items Section -->
<div class="col-md-6">
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Inventory Items</h4>
        </div>
        <div class="card-body">
            <?php if (count($inventory_items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['pieces'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($item['price']): ?>
                                            ₱<?php echo number_format($item['price'], 2); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['sealed']): ?>
                                            <span class="badge bg-warning">Sealed</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['withdrawn']): ?>
                                            <span class="badge bg-danger">Withdrawn</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!$item['sealed'] && !$item['withdrawn']): ?>
                                            <span class="badge bg-light text-dark">Normal</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['dao_violation']): ?>
                                            <span class="badge bg-info">DAO Violation</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['other_violation']): ?>
                                            <span class="badge bg-secondary">Other Violation</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Display product remarks if available
                                        if (!empty($item['product_remarks'])) {
                                            echo '<div class="mb-1"><strong>Product:</strong> ' . htmlspecialchars($item['product_remarks']) . '</div>';
                                        }
                                        
                                        // Display inventory remarks if available
                                        if (!empty($item['inv_remarks'])) {
                                            echo '<div' . (empty($item['product_remarks']) ? '' : ' class="mt-1 pt-1 border-top"') . '>';
                                            echo '<strong>Inventory:</strong> ' . htmlspecialchars($item['inv_remarks']);
                                            echo '</div>';
                                        }
                                        
                                        // If neither is available
                                        if (empty($item['product_remarks']) && empty($item['inv_remarks'])) {
                                            echo 'No remarks';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No inventory items recorded for this establishment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

    <!-- Notice Status and Issuers Section -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Status History and Issuers</h4>
        </div>
        <div class="card-body">
            <?php if (count($status_records) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Updated On</th>
                                <th>Issued By</th>
                                <th>Position</th>
                                <th>Witnessed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status_records as $status): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($status['status']); ?>">
                                            <?php echo htmlspecialchars($status['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('F d, Y h:i A', strtotime($status['updated_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($status['issuer_name'] ?? 'Not specified'); ?></td>
                                    <td><?php echo htmlspecialchars($status['issuer_position'] ?? 'Not specified'); ?></td>
                                    <td><?php echo htmlspecialchars($status['witnessed_by'] ?? 'None'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No status history or issuers recorded for this establishment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions Taken Section -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-tasks me-2"></i>Actions Taken</h4>
        </div>
        <div class="card-body">
            <?php if (count($notice_records) > 0): ?>
                <div class="accordion" id="actionsAccordion">
                    <?php foreach ($notice_records as $index => $record): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button <?php echo ($index > 0) ? 'collapsed' : ''; ?>" type="button" 
                                       data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" 
                                       aria-expanded="<?php echo ($index === 0) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <span class="badge <?php echo getActionTypeBadgeClass($record['notice_type']); ?> me-2">
                                                <?php echo htmlspecialchars($record['notice_type']); ?>
                                            </span>
                                            <span class="badge <?php echo getStatusBadgeClass($record['status']); ?>">
                                                <?php echo htmlspecialchars($record['status']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted me-3">
                                            <?php echo date('F d, Y', strtotime($record['created_at'])); ?>
                                        </small>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo ($index === 0) ? 'show' : ''; ?>" 
                                 aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#actionsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Record Details</h5>
                                            <table class="table table-bordered table-striped">
                                                <tr>
                                                    <th width="40%">Action Type:</th>
                                                    <td><?php echo htmlspecialchars($record['notice_type']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Date Created:</th>
                                                    <td><?php echo date('F d, Y h:i A', strtotime($record['created_at'])); ?></td>
                                                </tr>
                                                <?php if ($record['date_responded']): ?>
                                                <tr>
                                                    <th>Date Responded:</th>
                                                    <td><?php echo date('F d, Y h:i A', strtotime($record['date_responded'])); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <th>Status:</th>
                                                    <td><?php echo htmlspecialchars($record['status']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Remarks:</th>
                                                    <td><?php echo nl2br(htmlspecialchars($record['remarks'] ?? 'No remarks')); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Last Updated:</th>
                                                    <td><?php echo date('F d, Y h:i A', strtotime($record['updated_at'])); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <?php
                                            // Filter images belonging to this record
                                            $record_images = array_filter($images, function($img) use ($record) {
                                                return $img['record_id'] == $record['record_id'];
                                            });
                                            
                                            // Filter penalties belonging to around this record's creation time
                                            // (Since there's no direct foreign key relationship)
                                            $record_penalties = array_filter($penalties, function($penalty) use ($record) {
                                                $penalty_time = strtotime($penalty['created_at']);
                                                $record_time = strtotime($record['created_at']);
                                                // Check if penalty was created within 24 hours of the record
                                                return abs($penalty_time - $record_time) < 86400; // 24 hours in seconds
                                            });
                                            ?>
                                            
                                            <!-- Related Images Section -->
                                            <?php if (!empty($record_images)): ?>
                                                <h5>Related Images</h5>
                                                <div class="row">
                                                    <?php foreach ($record_images as $image): ?>
                                                        <div class="col-md-6 mb-3">
                                                            <div class="card">
                                                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                                     class="card-img-top img-thumbnail" 
                                                                     alt="<?php echo htmlspecialchars($image['image_name']); ?>">
                                                                <div class="card-body py-2">
                                                                    <p class="card-text small mb-0"><?php echo htmlspecialchars($image['image_name']); ?></p>
                                                                    <p class="card-text small text-muted">
                                                                        <?php echo htmlspecialchars($image['image_type']); ?> - 
                                                                        <?php echo date('M d, Y', strtotime($image['upload_date'])); ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Related Penalties Section -->
                                            <?php if (!empty($record_penalties)): ?>
                                                <h5 class="mt-3">Related Penalties</h5>
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Reference #</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($record_penalties as $penalty): ?>
                                                            <tr>
                                                                <td>₱<?php echo number_format($penalty['amount'], 2); ?></td>
                                                                <td>
                                                                    <span class="badge <?php echo ($penalty['status'] == 'Paid') ? 'bg-success' : 'bg-warning'; ?>">
                                                                        <?php echo htmlspecialchars($penalty['status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($penalty['reference_number'] ?? 'N/A'); ?></td>
                                                                <td><?php echo date('M d, Y', strtotime($penalty['issued_date'])); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <div class="mt-2">
                                                    <strong>Issued By:</strong> <?php echo htmlspecialchars($record_penalties[0]['issued_by'] ?? 'Not specified'); ?>
                                                </div>
                                                <?php if (!empty($record_penalties[0]['description'])): ?>
                                                    <div class="mt-2">
                                                        <strong>Description:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($record_penalties[0]['description'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if (empty($record_images) && empty($record_penalties)): ?>
                                                <div class="alert alert-secondary">
                                                    <p class="mb-0">No images or penalties associated with this record.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">No action records found for this establishment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Add any custom JavaScript functionality here
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>

<?php include '../templates/footer.php'; ?>