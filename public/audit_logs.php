<?php
session_start();
include __DIR__ . '/../connection.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Pagination settings
$records_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_action = isset($_GET['action']) ? trim($_GET['action']) : '';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

// Base query
$query = "SELECT l.logs_id, l.user_id, l.action, l.user_agent, l.details, l.timestamp, 
          u.username, u.fullname 
          FROM user_logs l 
          LEFT JOIN users u ON l.user_id = u.id 
          WHERE 1=1";

// Add search conditions
$params = [];

if (!empty($search_query)) {
    $query .= " AND (u.username LIKE :search1 OR u.fullname LIKE :search2 OR l.details LIKE :search3)";
    $search_param = "%{$search_query}%";
    $params[':search1'] = $search_param;
    $params[':search2'] = $search_param;
    $params[':search3'] = $search_param;
}

if (!empty($filter_action)) {
    $query .= " AND l.action = :action";
    $params[':action'] = $filter_action;
}

if (!empty($start_date)) {
    $query .= " AND l.timestamp >= :start_date";
    $params[':start_date'] = $start_date . ' 00:00:00';
}

if (!empty($end_date)) {
    $query .= " AND l.timestamp <= :end_date";
    $params[':end_date'] = $end_date . ' 23:59:59';
}

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM user_logs l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE 1=1";

// Add the same conditions as the main query
if (!empty($search_query)) {
    $count_query .= " AND (u.username LIKE :search1 OR u.fullname LIKE :search2 OR l.details LIKE :search3)";
}

if (!empty($filter_action)) {
    $count_query .= " AND l.action = :action";
}

if (!empty($start_date)) {
    $count_query .= " AND l.timestamp >= :start_date";
}

if (!empty($end_date)) {
    $count_query .= " AND l.timestamp <= :end_date";
}

$stmt_count = $conn->prepare($count_query);

if (!empty($params)) {
    foreach ($params as $param_name => $param_value) {
        $stmt_count->bindValue($param_name, $param_value);
    }
}

$stmt_count->execute();
$total_records = $stmt_count->fetchColumn();

$total_pages = ceil($total_records / $records_per_page);

// Final query with pagination
$query .= " ORDER BY l.timestamp DESC LIMIT :offset, :limit";
$params[':offset'] = $offset;
$params[':limit'] = $records_per_page;

$stmt = $conn->prepare($query);

if (!empty($params)) {
    foreach ($params as $param_name => $param_value) {
        $stmt->bindValue($param_name, $param_value);
    }
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get action types for filter dropdown
$action_query = "SELECT DISTINCT action FROM user_logs ORDER BY action";
$action_stmt = $conn->prepare($action_query);
$action_stmt->execute();
$action_result = $action_stmt->fetchAll(PDO::FETCH_ASSOC);

// Set current page for navigation
$current_page = 'audit_logs.php';
$is_logged_in = true;

// Include header template
include '../templates/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="fw-bold mb-2">
                        <i class="fas fa-history me-2 text-primary"></i>
                        System Audit Logs
                    </h1>
                    <p class="lead text-muted mb-0">Review and monitor all system access and activities</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <div class="col-12">
            <!-- Filters Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-filter me-2 text-primary"></i>
                        Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="audit_logs.php" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search:</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Username, name, or details">
                        </div>
                        <div class="col-md-2">
                            <label for="action" class="form-label">Action:</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">All Actions</option>
                                <?php foreach ($action_result as $action_row): ?>
                                    <option value="<?php echo htmlspecialchars($action_row['action']); ?>" <?php echo ($filter_action == $action_row['action']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($action_row['action']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Start Date:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">End Date:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i> Filter</button>
                            <a href="audit_logs.php" class="btn btn-outline-secondary me-2"><i class="fas fa-redo"></i> Reset</a>
                            <button type="button" id="exportBtn" class="btn btn-success"><i class="fas fa-download"></i> Export</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-table me-2 text-primary"></i>
                        System Access Records
                    </h5>
                    <span class="badge bg-info rounded-pill"><?php echo $total_records; ?> Records Found</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (count($result) > 0) {
                                    foreach ($result as $row) {
                                        // Set badge class based on action type
                                        $badge_class = 'bg-secondary';
                                        
                                        if ($row['action'] == 'Login') {
                                            $badge_class = 'bg-success';
                                        } elseif ($row['action'] == 'Failed Login') {
                                            $badge_class = 'bg-danger';
                                        } elseif ($row['action'] == 'Logout') {
                                            $badge_class = 'bg-warning text-dark';
                                        }
                                ?>
                                    <tr>
                                        <td><?php echo $row['logs_id']; ?></td>
                                        <td>
                                            <?php if ($row['username']): ?>
                                                <strong><?php echo htmlspecialchars($row['username']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['fullname']); ?></small>
                                            <?php else: ?>
                                                <em>Unknown User</em>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['action']); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['details']); ?></td>
                                        <td><?php echo date('M d, Y h:i:s A', strtotime($row['timestamp'])); ?></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center p-4 text-muted">No records found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo !empty($start_date) ? '&start_date=' . urlencode($start_date) : ''; ?><?php echo !empty($end_date) ? '&end_date=' . urlencode($end_date) : ''; ?>" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo !empty($start_date) ? '&start_date=' . urlencode($start_date) : ''; ?><?php echo !empty($end_date) ? '&end_date=' . urlencode($end_date) : ''; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo !empty($start_date) ? '&start_date=' . urlencode($start_date) : ''; ?><?php echo !empty($end_date) ? '&end_date=' . urlencode($end_date) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo !empty($start_date) ? '&start_date=' . urlencode($start_date) : ''; ?><?php echo !empty($end_date) ? '&end_date=' . urlencode($end_date) : ''; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo !empty($start_date) ? '&start_date=' . urlencode($start_date) : ''; ?><?php echo !empty($end_date) ? '&end_date=' . urlencode($end_date) : ''; ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <div class="text-end mt-3">
                        <small class="text-muted">Last updated: <?= date('M j, Y g:i A') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Agent Modal -->
<div class="modal fade" id="userAgentModal" tabindex="-1" aria-labelledby="userAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userAgentModalLabel">User Agent Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre id="userAgentContent" class="p-3 bg-light" style="white-space: pre-wrap;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Audit Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm" action="export_logs.php" method="post">
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">Export Format:</label>
                        <select class="form-select" id="exportFormat" name="format">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exportDateRange" class="form-label">Date Range:</label>
                        <select class="form-select" id="exportDateRange" name="date_range">
                            <option value="all">All Records</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div id="customDateRange" class="row g-3" style="display: none;">
                        <div class="col-md-6">
                            <label for="exportStartDate" class="form-label">Start Date:</label>
                            <input type="date" class="form-control" id="exportStartDate" name="export_start_date">
                        </div>
                        <div class="col-md-6">
                            <label for="exportEndDate" class="form-label">End Date:</label>
                            <input type="date" class="form-control" id="exportEndDate" name="export_end_date">
                        </div>
                    </div>
                    <!-- Pass current filters to export -->
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                    <input type="hidden" name="action" value="<?php echo htmlspecialchars($filter_action); ?>">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmExport">Export</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Show user agent details
    function showUserAgent(userAgent) {
        document.getElementById('userAgentContent').textContent = userAgent;
        var modal = new bootstrap.Modal(document.getElementById('userAgentModal'));
        modal.show();
    }
    
    // Export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('exportModal'));
        modal.show();
    });
    
    document.getElementById('exportDateRange').addEventListener('change', function() {
        if (this.value === 'custom') {
            document.getElementById('customDateRange').style.display = 'flex';
        } else {
            document.getElementById('customDateRange').style.display = 'none';
        }
    });
    
    document.getElementById('confirmExport').addEventListener('click', function() {
        document.getElementById('exportForm').submit();
    });
</script>

<?php include '../templates/footer.php'; ?>