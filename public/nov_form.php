<?php
require_once '../connection.php';
include '../templates/header.php';

// Initialize search and filter variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

// Handle status update (settlement)
if (isset($_POST['settle_establishment'])) {
    $establishment_id = $_POST['establishment_id'];
    $new_status = $_POST['new_status'];
    $issued_by = $_POST['issued_by'];
    $position = $_POST['position'];
    $witnessed_by = $_POST['witnessed_by'];
    
    // Update establishment notice status
    $update_estab = $conn->prepare("UPDATE establishments SET notice_status = ? WHERE establishment_id = ?");
    $update_estab->bind_param("si", $new_status, $establishment_id);
    $update_estab->execute();
    
    // Update or insert notice_status record
    $check_notice = $conn->prepare("SELECT * FROM notice_status WHERE establishment_id = ?");
    $check_notice->bind_param("i", $establishment_id);
    $check_notice->execute();
    $result = $check_notice->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $update_notice = $conn->prepare("UPDATE notice_status SET status = ?, issued_by = ?, position = ?, issued_datetime = NOW(), witnessed_by = ?, updated_at = NOW() WHERE establishment_id = ?");
        $update_notice->bind_param("ssssi", $new_status, $issued_by, $position, $witnessed_by, $establishment_id);
        $update_notice->execute();
    } else {
        // Insert new record
        $insert_notice = $conn->prepare("INSERT INTO notice_status (establishment_id, status, issued_by, position, issued_datetime, witnessed_by) VALUES (?, ?, ?, ?, NOW(), ?)");
        $insert_notice->bind_param("issss", $establishment_id, $new_status, $issued_by, $position, $witnessed_by);
        $insert_notice->execute();
    }
    
    // Log the action
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Assuming you have user sessions
    $action = "Updated establishment status to $new_status";
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $details = "Updated establishment ID: $establishment_id to status: $new_status";
    
    $log_query = $conn->prepare("INSERT INTO user_logs (user_id, action, user_agent, details, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $log_query->bind_param("isss", $user_id, $action, $user_agent, $details);
    $log_query->execute();
    
    // Redirect to prevent form resubmission
    header("Location: nov_form.php?status_updated=1");
    exit();
}

// Build the SQL query with search and filters
$sql = "SELECT e.establishment_id, e.name, e.violations, e.notice_status, 
               ns.issued_datetime, e.date_created, e.date_updated
        FROM establishments e
        LEFT JOIN notice_status ns ON e.establishment_id = ns.establishment_id
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (e.name LIKE ? OR e.violations LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($filter_status)) {
    $sql .= " AND e.notice_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_date)) {
    $sql .= " AND DATE(e.date_created) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

$sql .= " ORDER BY e.date_created DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get unique status values for filter dropdown
$status_query = $conn->query("SELECT DISTINCT notice_status FROM establishments WHERE notice_status IS NOT NULL");
$statuses = [];
while ($status_row = $status_query->fetch_assoc()) {
    if (!empty($status_row['notice_status'])) {
        $statuses[] = $status_row['notice_status'];
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <h2>Establishment Management</h2>
            
            <?php if (isset($_GET['status_updated']) && $_GET['status_updated'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Status updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Search and Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by name or violations" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filter_status" class="form-label">Filter by Status</label>
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
                    <div class="col-md-3">
                        <label for="filter_date" class="form-label">Filter by Date</label>
                        <input type="date" class="form-control" id="filter_date" name="filter_date" 
                               value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </div>
            </form>
            
            <!-- Establishments Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Establishment Name</th>
                            <th>Violations</th>
                            <th>Status</th>
                            <th>Issued Date</th>
                            <th>Date Created</th>
                            <th>Date Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['violations']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($row['notice_status']); ?>">
                                            <?php echo htmlspecialchars($row['notice_status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['issued_datetime'] ? date('M d, Y h:i A', strtotime($row['issued_datetime'])) : 'N/A'; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['date_created'])); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['date_updated'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view_establishment.php?id=<?php echo $row['establishment_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="edit_establishment.php?id=<?php echo $row['establishment_id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#settleModal<?php echo $row['establishment_id']; ?>">
                                                <i class="fas fa-check-circle"></i> Settle
                                            </button>
                                        </div>
                                        
                                        <!-- Settle Modal -->
                                        <div class="modal fade" id="settleModal<?php echo $row['establishment_id']; ?>" 
                                             tabindex="-1" aria-labelledby="settleModalLabel<?php echo $row['establishment_id']; ?>" 
                                             aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="settleModalLabel<?php echo $row['establishment_id']; ?>">
                                                            Update Status for <?php echo htmlspecialchars($row['name']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="establishment_id" value="<?php echo $row['establishment_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="new_status" class="form-label">New Status</label>
                                                                <select class="form-select" id="new_status" name="new_status" required>
                                                                    <option value="">Select Status</option>
                                                                    <option value="Pending">Pending</option>
                                                                    <option value="In Progress">In Progress</option>
                                                                    <option value="Complied">Complied</option>
                                                                    <option value="Non-Compliant">Non-Compliant</option>
                                                                    <option value="Closed">Closed</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="issued_by" class="form-label">Issued By</label>
                                                                <input type="text" class="form-control" id="issued_by" name="issued_by" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="position" class="form-label">Position</label>
                                                                <input type="text" class="form-control" id="position" name="position" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="witnessed_by" class="form-label">Witnessed By</label>
                                                                <input type="text" class="form-control" id="witnessed_by" name="witnessed_by">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="settle_establishment" class="btn btn-primary">Update Status</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No establishments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
        case 'Pending':
        default:
            return 'bg-info';
    }
}
?>

<?php include '../templates/footer.php'; ?>