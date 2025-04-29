<?php
include __DIR__ . '/../connection.php';
include '../templates/header.php';

// Helper function to capitalize first letter of each word
function capitalizeWords($string) {
    return ucwords(strtolower($string));
}

// Query with additional fields for improved functionality
$query = "
    SELECT 
        e.establishment_id,
        e.name, 
        e.owner_representative AS owner_rep,
        e.violations AS all_violations,
        e.products AS inventory_products,
        e.num_violations,
        e.nature,
        e.remarks,
        e.created_at,
        e.date_updated,
        e.nov_files,
        CONCAT(IFNULL(a.street, ''), IF(a.street IS NOT NULL AND a.street != '', ', ', ''),
               IFNULL(a.barangay, ''), IF(a.barangay IS NOT NULL AND a.barangay != '', ', ', ''),
               IFNULL(a.municipality, ''), IF(a.municipality IS NOT NULL AND a.municipality != '', ', ', ''),
               IFNULL(a.province, ''), IF(a.province IS NOT NULL AND a.province != '', ', ', ''),
               IFNULL(a.region, '')) AS address
    FROM establishments e
    LEFT JOIN addresses a ON e.establishment_id = a.establishment_id
    ORDER BY e.date_updated DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Establishment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/nov-form.css">
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">Establishment Management</h2>
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
    .btn-group .btn {
    padding: 0.25rem 0.5rem;
    margin-right: 2px;
}

/* Status indicators */
.status-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 5px;
}

.status-urgent {
    background-color: #dc3545;
}

.status-pending {
    background-color: #ffc107;
}

.status-resolved {
    background-color: #198754;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    border-radius: 5px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.close-btn {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-btn:hover {
    color: black;
}

/* Form styles */
.inventory-container {
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.inventory-item {
    margin-bottom: 8px;
}

/* Toast notification styles */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

/* No results message */
.no-results {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

/* Violations badge spacing */
.violations-cell .badge {
    margin-right: 3px;
    margin-bottom: 3px;
}

/* Details container in view modal */
.details-container {
    padding: 10px;
}

/* Form improvements */
.form-control:focus, .form-select:focus {
    border-color: #10346C;
    box-shadow: 0 0 0 0.25rem rgba(16, 52, 108, 0.25);
}

/* Button hover effects */
.btn-primary:hover {
    background-color: #0d2c5d;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-danger:hover {
    background-color: #bd2130;
}

.btn-success:hover {
    background-color: #1e7e34;
}
.violation-count {
        font-weight: bold;
    }
    .violation-count-badge {
        font-size: 1rem;
        padding: 0.35em 0.65em;
    }
</style>


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
                <option value="Overpricing">Overpricing</option>
                <option value="Business Name">Business Name Violation</option>
                <option value="Sales Promotion">Unauthorized Sales Promotion</option>
                <option value="Other">Other Violations</option>
            </select>
        </div>
        <div class="filter-item">
            <select id="natureFilter" class="form-select" onchange="filterTable()">
                <option value="">All Business Types</option>
                <option value="Manufacturing">Manufacturing</option>
                <option value="Retail Trade">Retail Trade</option>
                <option value="Food Service">Food Service</option>
                <option value="Service and Repair">Service and Repair</option>
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
                        <th>Violation Count</th>
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

                         <tr data-id="<?= isset($row['establishment_id']) ? $row['establishment_id'] : '' ?>" data-status="<?= $status ?>" data-nature="<?= isset($row['nature']) ? $row['nature'] : '' ?>">
                            <td><?= capitalizeWords(htmlspecialchars($row['name'])) ?></td>
                            <td><?= isset($row['address']) ? capitalizeWords(htmlspecialchars(trim($row['address'], ', '))) : '<span class="text-muted">No address available</span>' ?></td>
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
            if (strpos(strtolower($v), 'overpricing') !== false) return '<span class="badge bg-danger">Overpricing</span>';
            if (strpos(strtolower($v), 'business name') !== false) return '<span class="badge bg-primary">Business Name Violation</span>';
            if (strpos(strtolower($v), 'sales promotion') !== false) return '<span class="badge bg-info">Unauthorized Sales Promotion</span>';
            return '<span class="badge bg-secondary">' . ucwords(strtolower($v)) . '</span>';
        }, $violations);
        echo implode(' ', array_unique($formattedViolations));
    } else {
        echo '<span class="text-muted">No violations</span>';
    }
    ?>
</td>
                                <td class="violation-count text-center">
                                <?php 
                                if (!empty($row['all_violations'])) {
                                    $violations = array_map('trim', explode(',', $row['all_violations']));
                                    $uniqueViolations = array_unique($violations);
                                    $count = count($uniqueViolations);
                                    $badgeClass = ($count > 2) ? 'bg-danger' : (($count > 0) ? 'bg-warning' : 'bg-secondary');
                                    echo '<span class="badge ' . $badgeClass . ' violation-count-badge">' . $count . '</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">0</span>';
                                }
                                ?>
                            </td>

                            <td> 
                            <?php 
if (!empty($row['date_updated']) && $row['date_updated'] != '0000-00-00 00:00:00') {
    try {
        // Store the original UTC timestamp in a data attribute
        $rawTimestamp = htmlspecialchars($row['date_updated']);
        echo '<span data-timestamp="' . $rawTimestamp . '">';
        
        // Convert database time (assumed to be UTC) to Asia/Manila timezone
        $dateUtc = new DateTime($row['date_updated'], new DateTimeZone('UTC'));
        $dateManila = clone $dateUtc;
        $dateManila->setTimezone(new DateTimeZone('Asia/Manila'));
        
        // Output formatted date and time in Asia/Manila
        echo $dateManila->format('M d, Y h:i A');
        
        // Get the current time in Asia/Manila
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        
        // Calculate badge based on date difference
        $diff = $now->diff($dateManila);
        $days = (int)$diff->format('%a');
        $hours = (int)$diff->format('%h');
        $mins = (int)$diff->format('%i');
        $totalHours = $days * 24 + $hours;
        
        // Only compare dates if they're on different days
        $nowDate = $now->format('Y-m-d');
        $updatedDate = $dateManila->format('Y-m-d');
        
        if ($nowDate === $updatedDate && $totalHours < 24) {
            if ($mins < 5) {
                echo ' <span class="badge bg-success">Just now</span>';
            } else if ($hours < 1) {
                echo ' <span class="badge bg-success">' . $mins . ' min' . ($mins !== 1 ? 's' : '') . ' ago</span>';
            } else {
                echo ' <span class="badge bg-success">' . $hours . ' hour' . ($hours !== 1 ? 's' : '') . ' ago</span>';
            }
        } elseif ($nowDate === $updatedDate) {
            echo ' <span class="badge bg-success">Today</span>';
        } elseif ($days === 1) {
            echo ' <span class="badge bg-success">Yesterday</span>';
        } elseif ($days <= 7) {
            echo ' <span class="badge bg-success">' . $days . ' days ago</span>';
        } elseif ($days <= 30) {
            echo ' <span class="badge bg-warning">' . $days . ' days ago</span>';
        } else {
            echo ' <span class="badge bg-danger">' . $days . ' days ago</span>';
        }
        
        echo '</span>';
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
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
            <script src="js/action-button-fix.js"></script>
            <script src="js/form_handler_novform.js"></script>
            </body>
            </html>


<?php include '../templates/footer.php'; ?>