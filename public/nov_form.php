<?php
include __DIR__ . '/../connection.php';
include '../templates/header.php';

// Helper function to capitalize first letter of each word
function capitalizeWords($string) {
    return ucwords(strtolower($string));
}
// Previous query remains the same
$query = "
    SELECT 
        e.id,
        e.name, 
        e.address, 
        IFNULL(e.owner_representative, 'Not specified') AS owner_rep,
        e.violations AS all_violations,
        e.products AS inventory_products,
        e.num_violations,
        e.date_created,
        e.date_updated,
        e.nov_files
    FROM establishments e
    ORDER BY e.date_updated DESC
";
$result = $conn->query($query);
?>

<style>
    body {
        background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
    }
    .container {
        margin-top: 20px;
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
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 10px;
    }
    .close-btn {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close-btn:hover {
        color: #000;
    }
    .btn-primary, .btn-secondary {
        margin: 5px;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary {
        background-color: #10346C;
        color: white;
        border: none;
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }
    
    /* Responsive Table Styles */
    @media (max-width: 768px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
        }
        
        .table td, .table th {
            white-space: nowrap;
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
  
</style>

<div class="container">
    <h4 class="mb-3 text-center">Establishment Management</h4>

    <!-- Search Bar with Icon -->
    <div class="input-group mb-3">
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

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="recordsTable">
                <!-- Previous table structure remains the same -->
                <thead>
    <tr>
        <th>Establishment</th>
        <th>Address</th>
        <th>Owner/Representative</th>  
        <th>Violations</th>
        <th>Products (Inventory)</th>
        <th>No. of Violations</th>
        <th>Date Created</th>
        <th>Last Updated</th>
        <th>Actions</th>
    </tr>
</thead>

<tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr data-id="<?= $row['id'] ?>">
            <td><?= ucwords(strtolower(htmlspecialchars($row['name']))) ?></td>
            <td><?= ucwords(strtolower(htmlspecialchars($row['address']))) ?></td>
            <td><?= ucwords(strtolower(htmlspecialchars($row['owner_rep']))) ?></td>
            <td>
            <?php 
    if (!empty($row['all_violations'])) {
        $violations = array_map('trim', explode(',', $row['all_violations']));
        $formattedViolations = array_map(function($v) {
            if (strpos(strtolower($v), 'ps/icc') !== false) return 'No PS/ICC Mark';
            if (strpos(strtolower($v), 'invalid/expired') !== false) return 'Invalid/Expired Accreditation';
            return ucwords(strtolower($v));
        }, $violations);
        echo htmlspecialchars(implode(', ', array_unique($formattedViolations)));
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
            <td><?= $row['num_violations'] ?></td>
            <td>
                <?php 
                if (!empty($row['date_created']) && $row['date_created'] != '0000-00-00 00:00:00') {
                    try {
                        $date = new DateTime($row['date_created']);
                        echo $date->format('M d, Y h:i A');
                    } catch (Exception $e) {
                        echo 'Invalid date';
                    }
                } else {
                    echo 'No date';
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
                    } catch (Exception $e) {
                        echo 'Invalid date';
                    }
                } else {
                    echo 'No date';
                }
                ?>
            </td>
            <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="openViewModal(<?= $row['id'] ?>)">View NOV</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="openEditModal(<?= $row['id'] ?>)">Edit</button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
</table>
        </div>
    </div>
</div>

<!-- View NOV Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>NOV Details</h3>
            <span class="close-btn" onclick="closeModal('viewModal')">&times;</span>
        </div>
        <div id="viewModalContent"></div>
        <div class="text-center mt-3">
            <button class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
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
                
                <div class="mb-3">
                    <label for="edit_name">Establishment Name</label>
                    <input type="text" class="form-control" id="edit_name" name="name" required>
                </div>
                
                <div class="mb-3">
                    <label for="edit_address">Address</label>
                    <input type="text" class="form-control" id="edit_address" name="address">
                </div>
                
                <div class="mb-3">
                    <label for="edit_owner_rep">Owner/Representative</label>
                    <input type="text" class="form-control" id="edit_owner_rep" name="owner_rep">
                </div>
                
                <div class="mb-3">
                    <label for="edit_violations">Violations</label>
                    <input type="text" class="form-control" id="edit_violations" name="violations">
                </div>
                
                <h4>Products Inventory</h4>
                <div id="inventory_items">
                    <!-- Inventory items will be added here -->
                </div>
                
                <button type="button" class="btn btn-success" onclick="addInventoryItem()">Add Product</button>
            </form>
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
        </div>
    </div>
</div>

<script src="js/form_handler_novform.js"></script>
