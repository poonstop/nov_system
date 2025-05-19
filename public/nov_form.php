<?php
require_once '../connection.php';
include '../templates/header.php';

// Initialize search and filter variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filter_action = isset($_GET['filter_action']) ? $_GET['filter_action'] : '';

// Build the SQL query with search and filters
// Modified JOIN to ensure we get the most recent notice record for each establishment
$sql = "SELECT e.establishment_id, e.name, e.violations, e.notice_status, 
               e.expiry_date, ns.witnessed_by, e.date_created, e.date_updated,
               nr.notice_type as action_type, nr.remarks as action_remarks,
               nr.date_responded
        FROM establishments e
        LEFT JOIN notice_status ns ON e.establishment_id = ns.establishment_id
        LEFT JOIN (
            SELECT * FROM notice_records 
            WHERE (establishment_id, created_at) IN (
                SELECT establishment_id, MAX(created_at) 
                FROM notice_records 
                GROUP BY establishment_id
            )
        ) nr ON e.establishment_id = nr.establishment_id
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (e.name LIKE ? OR e.violations LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($filter_status)) {
    $sql .= " AND e.notice_status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_date)) {
    $sql .= " AND DATE(e.date_created) = ?";
    $params[] = $filter_date;
}

if (!empty($filter_action)) {
    $sql .= " AND nr.notice_type = ?";
    $params[] = $filter_action;
}

$sql .= " ORDER BY e.date_created DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);

// Bind parameters if any
for ($i = 0; $i < count($params); $i++) {
    $stmt->bindParam($i+1, $params[$i]);
}

$stmt->execute();

// Get unique status values for filter dropdown
$status_query = $conn->query("SELECT DISTINCT notice_status FROM establishments WHERE notice_status IS NOT NULL");
$statuses = [];
while ($status_row = $status_query->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($status_row['notice_status'])) {
        $statuses[] = $status_row['notice_status'];
    }
}

// Get unique action types for filter dropdown - include all possible values from the enum
$action_query = $conn->query("SELECT DISTINCT notice_type FROM notice_records WHERE notice_type IS NOT NULL");
$action_types = [];
while ($action_row = $action_query->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($action_row['notice_type'])) {
        $action_types[] = $action_row['notice_type'];
    }
}

// Add all possible enum values if they don't exist in the results
$all_action_types = ['Certified First Offence', 'Formal Charge', 'Compliance', 'Other'];
foreach ($all_action_types as $type) {
    if (!in_array($type, $action_types)) {
        $action_types[] = $type;
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-building me-2"></i> Establishment Management</h2>
               
                    
                </a>
            </div>
            
            <?php if (isset($_GET['status_updated']) && $_GET['status_updated'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Status updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['action_updated']) && $_GET['action_updated'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Action type updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Search and Filter Form -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Search & Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Search by name or violations" value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-select" id="filter_status" name="filter_status">
                                    <option value="">All Statuses</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo htmlspecialchars($status); ?>" 
                                                <?php echo ($filter_status === $status) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filter_action" class="form-label">Action Type</label>
                                <select class="form-select" id="filter_action" name="filter_action">
                                    <option value="">All Actions</option>
                                    <?php foreach ($action_types as $action): ?>
                                        <option value="<?php echo htmlspecialchars($action); ?>" 
                                                <?php echo ($filter_action === $action) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($action); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_date" class="form-label">Date Created</label>
                                <input type="date" class="form-control" id="filter_date" name="filter_date" 
                                    value="<?php echo htmlspecialchars($filter_date); ?>">
                            </div>
                            <div class="col-md-2">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
 <!-- Establishments Table -->
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Establishments List</h5>
            <a href="export_establishments.php" class="btn btn-sm btn-light">
                <i class="fas fa-download me-1"></i> Export Data
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Establishment Name</th>
                        <th>Violations</th>
                        <th>Status</th>
                        <th>Action Type</th>
                        <th>Date Responded</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    // Limit violations text length
                                    $violations = htmlspecialchars($row['violations']);
                                    echo (strlen($violations) > 50) ? substr($violations, 0, 50) . '...' : $violations;
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusBadgeClass($row['notice_status']); ?>">
                                        <?php echo htmlspecialchars($row['notice_status'] ?? 'Pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['action_type'])): ?>
                                        <span class="badge <?php echo getActionTypeBadgeClass($row['action_type']); ?>">
                                            <?php echo htmlspecialchars($row['action_type']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted"><em>Not Set</em></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['date_responded'])): ?>
                                        <span class="text-secondary">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo date('M d, Y', strtotime($row['date_responded'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted"><em>Not Responded</em></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['expiry_date'])): ?>
                                        <?php 
                                            echo date('M d, Y', strtotime($row['expiry_date']));
                                            
                                            // Check if already expired
                                            if (strtotime($row['expiry_date']) < time()) {
                                                echo ' <span class="badge bg-danger">Expired</span>';
                                            } elseif (strtotime($row['expiry_date']) < strtotime('+7 days')) {
                                                echo ' <span class="badge bg-warning text-dark">Soon</span>';
                                            }
                                        ?>
                                    <?php else: ?>
                                        <span class="text-muted"><em>Not Set</em></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="view_establishment.php?id=<?php echo $row['establishment_id']; ?>" 
                                            class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_establishment.php?id=<?php echo $row['establishment_id']; ?>" 
                                            class="btn btn-sm btn-warning" title="Edit Establishment">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="action_taken.php?id=<?php echo $row['establishment_id']; ?>" 
                                            class="btn btn-sm btn-primary" title="Manage Actions Taken">
                                            <i class="fas fa-tasks"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i> No establishments found matching your criteria.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light">
        <span>Total Records: <strong><?php echo $stmt->rowCount(); ?></strong></span>
    </div>
</div>

<!-- Legend Section -->
<div class="card mt-3 shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Status & Action Legend</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Status Types:</h6>
                <div class="d-flex flex-wrap">
                    <div class="me-3 mb-2">
                        <span class="badge bg-info">Pending</span> - Initial stage
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-warning">In Progress</span> - Being processed
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-success">Complied</span> - Requirements met
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-danger">Non-Compliant</span> - Failed requirements
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-secondary">Closed</span> - Case closed
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-info">Responded</span> - Action has been taken
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h6>Action Types:</h6>
                <div class="d-flex flex-wrap">
                    <div class="me-3 mb-2">
                        <span class="badge bg-primary">CFO</span> - Certificate of First Offence
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-info">FC</span> - Formal Charge
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-success">Compliance</span> - Compliance fulfilled
                    </div>
                    <div class="me-3 mb-2">
                        <span class="badge bg-secondary">Other</span> - Other action types
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<?php
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
        case 'Responded':
            return 'bg-info';
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

<!-- Add custom script for hover tooltips, interactions, and auto-refresh to ensure display updates -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add hover effect to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseout', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // If returning from action_taken.php with updated action, refresh the page to ensure data is current
    if (window.location.search.includes('action_updated=1')) {
        // Don't redirect to prevent infinite loop - just display the alert
        // The page will refresh on its own when the alert is closed or after a timeout
        setTimeout(function() {
            // Force reload all data by clearing search params but keep the action_updated param
            const currentUrl = new URL(window.location.href);
            const actionUpdated = currentUrl.searchParams.get('action_updated');
            
            // Clear all search params
            currentUrl.search = '';
            
            // Add back only the action_updated param
            if (actionUpdated) {
                currentUrl.searchParams.set('action_updated', actionUpdated);
            }
            
            // Reload with cleaned URL after 3 seconds
            setTimeout(function() {
                window.location.href = currentUrl.toString();
            }, 3000);
        }, 1000);
    }
});
</script>

<?php include '../templates/footer.php'; ?>