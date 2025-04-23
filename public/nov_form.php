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
        e.nov_files,
        e.nature,
        e.notice_status,
        e.remarks,
        e.issued_by,
        DATE_FORMAT(e.issued_datetime, '%Y-%m-%d %H:%i:%s') AS formatted_issued_datetime,
        DATE_FORMAT(e.created_at, '%Y-%m-%d %H:%i:%s') AS formatted_created_at
    FROM establishments e
    ORDER BY e.created_at DESC
";
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

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-store me-2"></i>Establishment Records
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control" 
                            placeholder="Search for establishments..." onkeyup="filterTable()">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-1"></i>Add New Establishment
                    </button>
                </div>
            </div>
            
            <div id="tableContainer">
                <div class="table-responsive">
                    <table id="recordsTable" class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Owner/Rep</th>
                                <th>Violations</th>
                                <th>Products</th>
                                <th>No. of Violations</th>
                                <th>Date Created</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Records will be loaded here dynamically -->
                            <tr>
                                <td colspan="9" class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading records...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div id="noResults" style="display: none;" class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No matching records found
                </div>
            </div>
        </div>
    </div>
</div>
                 
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

   <!-- View Modal -->
<div id="viewModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Establishment Details</h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeModal('viewModal')"></button>
            </div>
            <div class="modal-body">
                <div id="viewModalContent">
                    <!-- Content will be loaded dynamically -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Establishment</h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeModal('editModal')"></button>
            </div>
            <div class="modal-body">
                <div id="editModalContent">
                    <!-- Form will be loaded dynamically -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading form...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

    <script src="js/form_handler_novform.js"></script>
