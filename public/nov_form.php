<?php
include __DIR__ . '/../db_config.php';
include '../templates/header.php';

// Helper function to capitalize first letter of each word
function capitalizeWords($string) {
    return ucwords(strtolower($string));
}

// Query with additional fields for improved functionality
$query = "
    SELECT 
        e.id,
        e.name, 
        e.address, 
        IFNULL(e.owner_representative, 'Not specified') AS owner_rep,
        e.violations AS all_violations,
        e.products AS inventory_products,
        e.num_violations,
        e.nature,
        e.remarks,
        e.created_at,
        e.updated_at,
        e.nov_files
    FROM establishments e
    ORDER BY e.updated_at DESC
";
$result = $conn->query($query);
?>

<style>
    body {
        background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
        min-height: 100vh;
    }
    .container {
        margin-top: 20px;
        padding-bottom: 40px;
    }
    .table-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .search-bar {
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .search-bar:focus {
        box-shadow: 0 0 10px rgba(16, 52, 108, 0.3);
        border-color: #10346C;
    }
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 800px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    .close-btn {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s;
    }
    .close-btn:hover {
        color: #000;
    }
    .btn-primary, .btn-secondary, .btn-danger, .btn-success {
        margin: 5px;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn-primary {
        background-color: #10346C;
        color: white;
        border: none;
    }
    .btn-primary:hover {
        background-color: #0c2755;
        transform: translateY(-2px);
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }
    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }
    .btn-danger {
        background-color: #dc3545;
        color: white;
        border: none;
    }
    .btn-danger:hover {
        background-color: #c82333;
        transform: translateY(-2px);
    }
    .btn-success {
        background-color: #28a745;
        color: white;
        border: none;
    }
    .btn-success:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }
    
    /* Badge Styles */
    .badge {
        padding: 5px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    .bg-danger {
        background-color: #dc3545;
        color: white;
    }
    .bg-warning {
        background-color: #ffc107;
        color: #212529;
    }
    .bg-success {
        background-color: #28a745;
        color: white;
    }
    
    /* Status Indicator */
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-resolved {
        background-color: #28a745;
    }
    .status-pending {
        background-color: #ffc107;
    }
    .status-urgent {
        background-color: #dc3545;
    }
    
    /* Responsive Table Styles */
    @media (max-width: 992px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
        }
        
        .table td, .table th {
            white-space: nowrap;
        }
        
        .modal-content {
            width: 95%;
            margin: 10% auto;
        }
    }
    
    /* Search Result Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    #recordsTable tbody tr {
        transition: all 0.3s ease;
    }
    
    #recordsTable tbody tr.search-match {
        animation: fadeIn 0.5s ease;
        background-color: rgba(16, 52, 108, 0.05);
    }
    
    #recordsTable tbody tr:hover {
        background-color: rgba(16, 52, 108, 0.1);
        transform: scale(1.01);
    }
    
    /* Empty Search Result Styling */
    .no-results {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
    
    /* Inventory items styles */
    .inventory-container {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        background-color: #f8f9fa;
    }
    
    .inventory-item {
        background-color: #fff;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #eee;
        border-radius: 5px;
        position: relative;
    }
    
    .remove-item {
        position: absolute;
        top: 10px;
        right: 10px;
        color: #dc3545;
        cursor: pointer;
    }
    
    /* Filter controls */
    .filter-controls {
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f0f4f8;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .filter-item {
        flex: 1;
        min-width: 200px;
    }
    
    /* Info tooltip */
    .tooltip-icon {
        cursor: help;
        color: #10346C;
        margin-left: 5px;
    }
    
    /* Status badge */
    .status-badge {
        display: inline-block;
        padding: 3px 8px;a
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        margin-left: 5px;
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Establishment Management</h4>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add New Establishment
        </button>
    </div>
    
    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-row">
            <div class="filter-item">
                <div class="input-group">
                    <span class="input-group-text" style="background-color: #10346C; color: white;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="form-control search-bar" 
                        placeholder="Search establishment, address, or violations"
                        onkeyup="filterTable()"
                    >
                </div>
            </div>
            <div class="filter-item">
                <select id="violationFilter" class="form-select" onchange="filterTable()">
                    <option value="">All Violations</option>
                    <option value="PS/ICC">No PS/ICC Mark</option>
                    <option value="Invalid/Expired">Invalid/Expired Accreditation</option>
                    <option value="Improper Labeling">Improper Labeling</option>
                    <option value="Price Tag">Price Tag Violations</option>
                </select>
            </div>
            <div class="filter-item">
                <select id="natureFilter" class="form-select" onchange="filterTable()">
                    <option value="">All Business Types</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Retail Trade">Retail Trade</option>
                    <option value="Food Service">Food Service</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        <div class="filter-row">
            <div class="filter-item">
                <select id="sortBy" class="form-select" onchange="sortTable()">
                    <option value="dateDesc">Newest First</option>
                    <option value="dateAsc">Oldest First</option>
                    <option value="nameAsc">Name (A-Z)</option>
                    <option value="nameDesc">Name (Z-A)</option>
                    <option value="violationsDesc">Most Violations</option>
                </select>
            </div>
            <div class="filter-item d-flex align-items-center">
                <label class="me-2">Status:</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="showUrgent" checked onchange="filterTable()">
                    <label class="form-check-label" for="showUrgent">
                        <span class="status-indicator status-urgent"></span> Urgent
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="showPending" checked onchange="filterTable()">
                    <label class="form-check-label" for="showPending">
                        <span class="status-indicator status-pending"></span> Pending
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="showResolved" checked onchange="filterTable()">
                    <label class="form-check-label" for="showResolved">
                        <span class="status-indicator status-resolved"></span> Resolved
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="recordsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Establishment</th>
                        <th>Address</th>
                        <th>Owner/Representative</th>  
                        <th>Nature</th>
                        <th>Violations</th>
                        <th>Products</th>
                        <th>Last Updated</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Determine status based on remarks
                        $status = "pending";
                        $statusBadge = "bg-warning";
                        $statusText = "Pending";
                        
                        if (!empty($row['remarks'])) {
                            $remarks = strtolower($row['remarks']);
                            if (strpos($remarks, 'urgent') !== false) {
                                $status = "urgent";
                                $statusBadge = "bg-danger";
                                $statusText = "Urgent";
                            } elseif (strpos($remarks, 'resolved') !== false || strpos($remarks, 'complete') !== false) {
                                $status = "resolved";
                                $statusBadge = "bg-success";
                                $statusText = "Resolved";
                            }
                        }
                    ?>
                        <tr data-id="<?= $row['id'] ?>" data-status="<?= $status ?>" data-nature="<?= $row['nature'] ?>">
                            <td><?= capitalizeWords(htmlspecialchars($row['name'])) ?></td>
                            <td><?= capitalizeWords(htmlspecialchars($row['address'])) ?></td>
                            <td><?= capitalizeWords(htmlspecialchars($row['owner_rep'])) ?></td>
                            <td><?= htmlspecialchars($row['nature']) ?></td>
                            <td class="violations-cell">
                                <?php 
                                if (!empty($row['all_violations'])) {
                                    $violations = array_map('trim', explode(',', $row['all_violations']));
                                    $formattedViolations = array_map(function($v) {
                                        if (strpos(strtolower($v), 'ps/icc') !== false) return '<span class="badge bg-danger">No PS/ICC Mark</span>';
                                        if (strpos(strtolower($v), 'invalid/expired') !== false) return '<span class="badge bg-warning">Invalid/Expired Accreditation</span>';
                                        if (strpos(strtolower($v), 'improper labeling') !== false) return '<span class="badge bg-info">Improper Labeling</span>';
                                        if (strpos(strtolower($v), 'price tag') !== false) return '<span class="badge bg-secondary">Price Tag Violations</span>';
                                        return '<span class="badge bg-secondary">' . ucwords(strtolower($v)) . '</span>';
                                    }, $violations);
                                    echo implode(' ', array_unique($formattedViolations));
                                } else {
                                    echo '<span class="text-muted">No violations</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (!empty($row['inventory_products'])) {
                                    $products = json_decode($row['inventory_products'], true);
                                    if (is_array($products)) {
                                        $productNames = array_column($products, 'product_name');
                                        echo htmlspecialchars(implode(', ', $productNames));
                                        echo ' <span class="badge bg-primary">' . count($productNames) . '</span>';
                                    } else {
                                        echo htmlspecialchars($row['inventory_products']);
                                    }
                                } else {
                                    echo '<span class="text-muted">No inventory</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (!empty($row['date_updated']) && $row['date_updated'] != '0000-00-00 00:00:00') {
                                    try {
                                        $date = new DateTime($row['date_updated'], new DateTimeZone('UTC'));
                                        $date->setTimezone(new DateTimeZone('Asia/Manila'));
                                        echo $date->format('M d, Y h:i A');
                                        
                                        // Calculate days since last update
                                        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                                        $diff = $now->diff($date);
                                        $days = $diff->days;
                                        
                                        // Display age indicator
                                        if ($days == 0) {
                                            echo ' <span class="badge bg-success">Today</span>';
                                        } elseif ($days == 1) {
                                            echo ' <span class="badge bg-success">Yesterday</span>';
                                        } elseif ($days <= 7) {
                                            echo ' <span class="badge bg-success">' . $days . ' days ago</span>';
                                        } elseif ($days <= 30) {
                                            echo ' <span class="badge bg-warning">' . $days . ' days ago</span>';
                                        } else {
                                            echo ' <span class="badge bg-danger">' . $days . ' days ago</span>';
                                        }
                                    } catch (Exception $e) {
                                        echo 'Invalid date';
                                    }
                                } else {
                                    echo 'No date';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-indicator status-<?= $status ?>"></span>
                                <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="openViewModal(<?= $row['id'] ?>)" data-bs-toggle="tooltip" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="openEditModal(<?= $row['id'] ?>)" data-bs-toggle="tooltip" title="Edit Record">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['id'] ?>)" data-bs-toggle="tooltip" title="Delete Record">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="noResults" class="no-results" style="display: none;">
                <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                <h5>No establishments found</h5>
                <p>Try adjusting your search criteria or filters</p>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Establishment Details</h3>
            <span class="close-btn" onclick="closeModal('viewModal')">&times;</span>
        </div>
        <div id="viewModalContent">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
            <button class="btn btn-primary" id="editFromViewBtn">Edit Details</button>
            <button class="btn btn-success" id="generateNovBtn">Generate NOV</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Establishment Details</h3>
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
        </div>
        <div id="editModalContent">
            <form id="editForm">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_name">Establishment Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="edit_owner_rep">Owner/Representative</label>
                        <input type="text" class="form-control" id="edit_owner_rep" name="owner_rep">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="edit_address">Address</label>
                    <input type="text" class="form-control" id="edit_address" name="address">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_nature">Nature of Business</label>
                        <select class="form-select" id="edit_nature" name="nature">
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Retail Trade">Retail Trade</option>
                            <option value="Food Service">Food Service</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Violations</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="violation_psicc" name="violations[]" value="No PS/ICC Mark">
                            <label class="form-check-label" for="violation_psicc">No PS/ICC Mark</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="violation_expired" name="violations[]" value="Invalid/Expired Accreditation">
                            <label class="form-check-label" for="violation_expired">Invalid/Expired Accreditation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="violation_labeling" name="violations[]" value="Improper Labeling">
                            <label class="form-check-label" for="violation_labeling">Improper Labeling</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="violation_price" name="violations[]" value="Price Tag Violations">
                            <label class="form-check-label" for="violation_price">Price Tag Violations</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="edit_remarks">Remarks</label>
                    <textarea class="form-control" id="edit_remarks" name="remarks" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="edit_status">Status</label>
                    <select class="form-select" id="edit_status" name="status">
                        <option value="pending">Pending</option>
                        <option value="urgent">Urgent</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                
                <h4>Products Inventory</h4>
                <div id="inventory_items" class="inventory-container">
                    <!-- Inventory items will be added here -->
                </div>
                
                <button type="button" class="btn btn-success" onclick="addInventoryItem()">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </form>
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
        </div>
    </div>
</div>

<!-- Add Establishment Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Establishment</h3>
            <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
        </div>
        <div id="addModalContent">
            <form id="addForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="add_name">Establishment Name</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="add_owner_rep">Owner/Representative</label>
                        <input type="text" class="form-control" id="add_owner_rep" name="owner_rep">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="add_address">Address</label>
                    <input type="text" class="form-control" id="add_address" name="address" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="add_nature">Nature of Business</label>
                        <select class="form-select" id="add_nature" name="nature" required>
                            <option value="">Select business type</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Retail Trade">Retail Trade</option>
                            <option value="Food Service">Food Service</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Violations</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_violation_psicc" name="violations[]" value="No PS/ICC Mark">
                            <label class="form-check-label" for="add_violation_psicc">No PS/ICC Mark</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_violation_expired" name="violations[]" value="Invalid/Expired Accreditation">
                            <label class="form-check-label" for="add_violation_expired">Invalid/Expired Accreditation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_violation_labeling" name="violations[]" value="Improper Labeling">
                            <label class="form-check-label" for="add_violation_labeling">Improper Labeling</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_violation_price" name="violations[]" value="Price Tag Violations">
                            <label class="form-check-label" for="add_violation_price">Price Tag Violations</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="add_remarks">Remarks</label>
                    <textarea class="form-control" id="add_remarks" name="remarks" rows="3"></textarea>
                </div>
                
                <h4>Products Inventory</h4>
                <div id="add_inventory_items" class="inventory-container">
                    <div class="inventory-item">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label>Product Name</label>
                                <input type="text" class="form-control" name="product_name[]" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="product_price[]" min="0" step="0.01" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Quantity</label>
                                <input type="number" class="form-control" name="product_quantity[]" min="0" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="product_sealed[]" value="1">
                                    <label class="form-check-label">Sealed</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="product_withdrawn[]" value="1">
                                    <label class="form-check-label">Withdrawn</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-success" onclick="addInventoryItemToAdd()">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </form>
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="saveNewEstablishment()">Save Establishment</button>
            <button class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Confirm Deletion</h3>
            <span class="close-btn" onclick="closeModal('deleteModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this establishment? This action cannot be undone.</p>
            <input type="hidden" id="delete_id">
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-danger" onclick="deleteEstablishment()">Delete</button>
            <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
        </div>
    </div>
</div>

<script>
// Global variables
let currentEstablishmentId = null;

// Modal functions
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}
function fetchWithTimeout(url, options, timeout = 10000) {
    return Promise.race([
        fetch(url, options),
        new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Request timeout')), timeout)
        )
    ]);
}

function openViewModal(id) {
    currentEstablishmentId = id;
    document.getElementById('viewModal').style.display = 'block';
    document.getElementById('viewModalContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Improved AJAX call to get establishment details
    fetchWithTimeout(`get_establishment.php?id=${id}`, {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayEstablishmentDetails(data.establishment);
            
            // Set up the edit button to open edit modal
            document.getElementById('editFromViewBtn').onclick = function() {
                closeModal('viewModal');
                openEditModal(id);
            };
            
            // Set up the NOV button with error handling
            document.getElementById('generateNovBtn').onclick = function() {
                try {
                    // Use a more reliable navigation method
                    const novUrl = `generate_nov.php?id=${id}`;
                    const a = document.createElement('a');
                    a.href = novUrl;
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                } catch (err) {
                    console.error('Navigation error:', err);
                    alert('Error generating NOV. Please try again.');
                }
            };
        } else {
            document.getElementById('viewModalContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading establishment details: ${data.message || 'Unknown error'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error fetching establishment details:', error);
        document.getElementById('viewModalContent').innerHTML = `
            <div class="alert alert-danger">
                ${error.message || 'Network error. Please check your connection and try again.'}
                <button class="btn btn-sm btn-primary mt-2" onclick="openViewModal(${id})">
                    <i class="fas fa-sync"></i> Retry
                </button>
            </div>
        `;
    });
}

function displayEstablishmentDetails(establishment) {
    // Format the violations
    let violationsBadges = '';
    if (establishment.all_violations) {
        const violations = establishment.all_violations.split(',').map(v => v.trim());
        violationsBadges = violations.map(v => {
            let className = 'bg-secondary';
            if (v.toLowerCase().includes('ps/icc')) className = 'bg-danger';
            if (v.toLowerCase().includes('invalid/expired')) className = 'bg-warning';
            if (v.toLowerCase().includes('improper labeling')) className = 'bg-info';
            if (v.toLowerCase().includes('price tag')) className = 'bg-secondary';
            
            return `<span class="badge ${className} me-1">${v}</span>`;
        }).join(' ');
    } else {
        violationsBadges = '<span class="text-muted">No violations recorded</span>';
    }
    
    // Format the products
    let productsHTML = '';
    if (establishment.inventory_products) {
        try {
            const products = JSON.parse(establishment.inventory_products);
            if (Array.isArray(products) && products.length > 0) {
                productsHTML = `
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                products.forEach(product => {
                    const status = [];
                    if (product.sealed == 1) status.push('<span class="badge bg-info">Sealed</span>');
                    if (product.withdrawn == 1) status.push('<span class="badge bg-warning">Withdrawn</span>');
                    
                    productsHTML += `
                        <tr>
                            <td>${product.product_name}</td>
                            <td>₱${parseFloat(product.price).toFixed(2)}</td>
                            <td>${product.quantity}</td>
                            <td>${status.length ? status.join(' ') : '<span class="text-muted">-</span>'}</td>
                        </tr>
                    `;
                });
                
                productsHTML += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                productsHTML = '<p class="text-muted">No products in inventory</p>';
            }
        } catch (e) {
            productsHTML = `<p class="text-muted">${establishment.inventory_products}</p>`;
        }
    } else {
        productsHTML = '<p class="text-muted">No products in inventory</p>';
    }
    
    // Determine status badge
    let statusBadge = '<span class="badge bg-warning">Pending</span>';
    if (establishment.remarks) {
        const remarks = establishment.remarks.toLowerCase();
        if (remarks.includes('urgent')) {
            statusBadge = '<span class="badge bg-danger">Urgent</span>';
        } else if (remarks.includes('resolved') || remarks.includes('complete')) {
            statusBadge = '<span class="badge bg-success">Resolved</span>';
        }
    }
    
    // Format dates
    let createdDate = 'Not available';
    let updatedDate = 'Not available';
    
    if (establishment.date_created && establishment.date_created !== '0000-00-00 00:00:00') {
        const created = new Date(establishment.date_created);
        createdDate = created.toLocaleString('en-PH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    if (establishment.date_updated && establishment.date_updated !== '0000-00-00 00:00:00') {
        const updated = new Date(establishment.date_updated);
        updatedDate = updated.toLocaleString('en-PH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // NOV files section
    let novFilesHTML = '<p class="text-muted">No NOV files uploaded</p>';
    if (establishment.nov_files) {
        try {
            const novFiles = JSON.parse(establishment.nov_files);
            if (Array.isArray(novFiles) && novFiles.length > 0) {
                novFilesHTML = `
                    <ul class="list-group">
                        ${novFiles.map(file => `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-file-pdf text-danger me-2"></i>${file.name}</span>
                                <a href="uploads/nov/${file.filename}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                `;
            }
        } catch (e) {
            novFilesHTML = '<p class="text-muted">Error loading NOV files</p>';
        }
    }
    
    // Build the complete details HTML
    document.getElementById('viewModalContent').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h4>${establishment.name}</h4>
                <p><i class="fas fa-map-marker-alt text-danger"></i> ${establishment.address || 'No address provided'}</p>
                <p><i class="fas fa-user text-primary"></i> ${establishment.owner_rep || 'No representative specified'}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p><strong>Status:</strong> ${statusBadge}</p>
                <p><strong>Business Type:</strong> ${establishment.nature || 'Not specified'}</p>
                <p><strong>Last Updated:</strong> ${updatedDate}</p>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Violations</h5>
            </div>
            <div class="card-body">
                ${violationsBadges}
                <div class="mt-2">
                    <small class="text-muted">Total violations: ${establishment.num_violations || '0'}</small>
                </div>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-box"></i> Products Inventory</h5>
            </div>
            <div class="card-body">
                ${productsHTML}
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt"></i> NOV Documents</h5>
            </div>
            <div class="card-body">
                ${novFilesHTML}
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-comment"></i> Remarks</h5>
            </div>
            <div class="card-body">
                ${establishment.remarks ? `<p>${establishment.remarks}</p>` : '<p class="text-muted">No remarks provided</p>'}
            </div>
        </div>
        
        <div class="text-muted mt-3">
            <small>Record created on: ${createdDate}</small>
        </div>
    `;
}

function openEditModal(id) {
    currentEstablishmentId = id;
    document.getElementById('editModal').style.display = 'block';
    document.getElementById('edit_id').value = id;
    
    // Improved AJAX call with error handling
    fetchWithTimeout(`get_establishment.php?id=${id}`, {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            populateEditForm(data.establishment);
        } else {
            throw new Error(data.message || 'Error loading establishment details');
        }
    })
    .catch(error => {
        console.error('Error fetching establishment data:', error);
        alert(error.message || 'Network error. Please check your connection and try again.');
        closeModal('editModal');
    });
}

function populateEditForm(establishment) {
    // Basic info
    document.getElementById('edit_name').value = establishment.name || '';
    document.getElementById('edit_address').value = establishment.address || '';
    document.getElementById('edit_owner_rep').value = establishment.owner_rep || '';
    document.getElementById('edit_nature').value = establishment.nature || 'Retail Trade';
    document.getElementById('edit_remarks').value = establishment.remarks || '';
    
    // Status
    let status = 'pending';
    if (establishment.remarks) {
        const remarks = establishment.remarks.toLowerCase();
        if (remarks.includes('urgent')) {
            status = 'urgent';
        } else if (remarks.includes('resolved') || remarks.includes('complete')) {
            status = 'resolved';
        }
    }
    document.getElementById('edit_status').value = status;
    
    // Clear violation checkboxes
    document.getElementById('violation_psicc').checked = false;
    document.getElementById('violation_expired').checked = false;
    document.getElementById('violation_labeling').checked = false;
    document.getElementById('violation_price').checked = false;
    
    // Set violation checkboxes
    if (establishment.all_violations) {
        const violations = establishment.all_violations.split(',').map(v => v.trim().toLowerCase());
        if (violations.some(v => v.includes('ps/icc'))) {
            document.getElementById('violation_psicc').checked = true;
        }
        if (violations.some(v => v.includes('invalid/expired'))) {
            document.getElementById('violation_expired').checked = true;
        }
        if (violations.some(v => v.includes('improper labeling'))) {
            document.getElementById('violation_labeling').checked = true;
        }
        if (violations.some(v => v.includes('price tag'))) {
            document.getElementById('violation_price').checked = true;
        }
    }
    
    // Clear existing inventory items
    document.getElementById('inventory_items').innerHTML = '';
    
    // Add inventory items
    if (establishment.inventory_products) {
        try {
            const products = JSON.parse(establishment.inventory_products);
            if (Array.isArray(products) && products.length > 0) {
                products.forEach(product => {
                    addInventoryItemWithData(product);
                });
            } else {
                // Add one empty item if no products
                addInventoryItem();
            }
        } catch (e) {
            console.error('Error parsing inventory products:', e);
            // Add one empty item if error
            addInventoryItem();
        }
    } else {
        // Add one empty item if no inventory
        addInventoryItem();
    }
}

function addInventoryItem() {
    const container = document.getElementById('inventory_items');
    const itemDiv = document.createElement('div');
    itemDiv.classList.add('inventory-item');
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Product Name</label>
                <input type="text" class="form-control" name="product_name[]">
            </div>
            <div class="col-md-3 mb-2">
                <label>Price</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" name="product_price[]" min="0" step="0.01" value="0.00">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label>Quantity</label>
                <input type="number" class="form-control" name="product_quantity[]" min="0" value="0">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_sealed[]" value="1">
                    <label class="form-check-label">Sealed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_withdrawn[]" value="1">
                    <label class="form-check-label">Withdrawn</label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-item" onclick="removeInventoryItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

function addInventoryItemWithData(product) {
    const container = document.getElementById('inventory_items');
    const itemDiv = document.createElement('div');
    itemDiv.classList.add('inventory-item');
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Product Name</label>
                <input type="text" class="form-control" name="product_name[]" value="${product.product_name || ''}">
            </div>
            <div class="col-md-3 mb-2">
                <label>Price</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" name="product_price[]" min="0" step="0.01" value="${parseFloat(product.price || 0).toFixed(2)}">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label>Quantity</label>
                <input type="number" class="form-control" name="product_quantity[]" min="0" value="${product.quantity || 0}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_sealed[]" value="1" ${product.sealed == 1 ? 'checked' : ''}>
                    <label class="form-check-label">Sealed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_withdrawn[]" value="1" ${product.withdrawn == 1 ? 'checked' : ''}>
                    <label class="form-check-label">Withdrawn</label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-item" onclick="removeInventoryItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

function removeInventoryItem(button) {
    const item = button.closest('.inventory-item');
    item.remove();
}

function addInventoryItemToAdd() {
    const container = document.getElementById('add_inventory_items');
    const itemDiv = document.createElement('div');
    itemDiv.classList.add('inventory-item');
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Product Name</label>
                <input type="text" class="form-control" name="product_name[]">
            </div>
            <div class="col-md-3 mb-2">
                <label>Price</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" name="product_price[]" min="0" step="0.01" value="0.00">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label>Quantity</label>
                <input type="number" class="form-control" name="product_quantity[]" min="0" value="0">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_sealed[]" value="1">
                    <label class="form-check-label">Sealed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_withdrawn[]" value="1">
                    <label class="form-check-label">Withdrawn</label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-item" onclick="removeInventoryItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function confirmDelete(id) {
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteModal').style.display = 'block';
}

function deleteEstablishment() {
    const id = document.getElementById('delete_id').value;
    
    // Improved AJAX call to delete establishment
    fetchWithTimeout('delete_establishment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cache-Control': 'no-cache'
        },
        body: `id=${id}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove the row from the table
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
            }
            
            // Close the modal and show success message
            closeModal('deleteModal');
            alert('Establishment successfully deleted');
            
            // Check if table is empty
            const tbody = document.querySelector('#recordsTable tbody');
            if (tbody.children.length === 0) {
                document.getElementById('noResults').style.display = 'block';
            }
        } else {
            throw new Error(data.message || 'Unknown error deleting establishment');
        }
    })
    .catch(error => {
        console.error('Error deleting establishment:', error);
        alert(error.message || 'Network error. Please check your connection and try again.');
    });
}

function saveChanges() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    
    // Add status to remarks if needed
    const status = document.getElementById('edit_status').value;
    let remarks = formData.get('remarks') || '';
    
    if (status === 'urgent' && !remarks.toLowerCase().includes('urgent')) {
        remarks = '[URGENT] ' + remarks;
    } else if (status === 'resolved' && !remarks.toLowerCase().includes('resolved')) {
        remarks = '[RESOLVED] ' + remarks;
    }
    
    formData.set('remarks', remarks);
    
    // AJAX call to update establishment with improved error handling
    fetchWithTimeout('update_establishment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message before reloading
            alert('Establishment successfully updated');
            location.reload();
        } else {
            throw new Error(data.message || 'Unknown error updating establishment');
        }
    })
    .catch(error => {
        console.error('Error updating establishment:', error);
        alert(error.message || 'Network error. Please check your connection and try again.');
    });
}

// Table filtering and sorting functions
function filterTable() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const violationFilter = document.getElementById('violationFilter').value.toLowerCase();
    const natureFilter = document.getElementById('natureFilter').value;
    const showUrgent = document.getElementById('showUrgent').checked;
    const showPending = document.getElementById('showPending').checked;
    const showResolved = document.getElementById('showResolved').checked;
    
    const rows = document.querySelectorAll('#recordsTable tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const address = row.cells[1].textContent.toLowerCase();
        const representative = row.cells[2].textContent.toLowerCase();
        const nature = row.cells[3].textContent;
        const violations = row.cells[4].textContent.toLowerCase();
        const products = row.cells[5].textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        
        // Apply search filter
        const matchesSearch = 
            searchText === '' || 
            name.includes(searchText) || 
            address.includes(searchText) || 
            representative.includes(searchText) || 
            violations.includes(searchText) ||
            products.includes(searchText);
        
        // Apply violation filter
        const matchesViolation = 
            violationFilter === '' || 
            violations.includes(violationFilter);
        
        // Apply nature filter
        const matchesNature = 
            natureFilter === '' || 
            nature === natureFilter;
        
        // Apply status filter
        const matchesStatus = 
            (status === 'urgent' && showUrgent) || 
            (status === 'pending' && showPending) || 
            (status === 'resolved' && showResolved);
        
        // Combine all filters
        const isVisible = matchesSearch && matchesViolation && matchesNature && matchesStatus;
        
        // Show or hide the row
        row.style.display = isVisible ? '' : 'none';
        
        // Count visible rows
        if (isVisible) {
            visibleCount++;
            
            // Add search match class for animation
            if (searchText !== '') {
                row.classList.add('search-match');
            } else {
                row.classList.remove('search-match');
            }
        }
    });
    
    // Show or hide the "no results" message
    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}

function sortTable() {
    const sortBy = document.getElementById('sortBy').value;
    const tbody = document.querySelector('#recordsTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Sort the rows based on the selected option
    rows.sort((a, b) => {
        if (sortBy === 'dateDesc') {
            // Sort by date updated (newest first)
            const dateA = a.cells[6].textContent.trim();
            const dateB = b.cells[6].textContent.trim();
            return new Date(dateB) - new Date(dateA);
        } else if (sortBy === 'dateAsc') {
            // Sort by date updated (oldest first)
            const dateA = a.cells[6].textContent.trim();
            const dateB = b.cells[6].textContent.trim();
            return new Date(dateA) - new Date(dateB);
        } else if (sortBy === 'nameAsc') {
            // Sort by name (A-Z)
            const nameA = a.cells[0].textContent.toLowerCase();
            const nameB = b.cells[0].textContent.toLowerCase();
            return nameA.localeCompare(nameB);
        } else if (sortBy === 'nameDesc') {
            // Sort by name (Z-A)
            const nameA = a.cells[0].textContent.toLowerCase();
            const nameB = b.cells[0].textContent.toLowerCase();
            return nameB.localeCompare(nameA);
        } else if (sortBy === 'violationsDesc') {
            // Sort by number of violations (most first)
            const violationsA = a.cells[4].querySelectorAll('.badge').length;
            const violationsB = b.cells[4].querySelectorAll('.badge').length;
            return violationsB - violationsA;
        }
        
        return 0;
    });
    
    // Re-append the sorted rows to the table
    rows.forEach(row => tbody.appendChild(row));
}

// Initialize event listeners when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Close modals when clicking outside of them
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
    
    // Add escape key listener for modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
});
</script>

<?php include '../templates/footer.php'; ?>