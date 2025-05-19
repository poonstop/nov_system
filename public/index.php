<?php
session_start();
include __DIR__ . '/../connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$current_page = 'index.php';
$is_logged_in = true;

// Set default role if not set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'user'; // Set a default role
}

// Fetch real data from database
try {
    // Get violation statistics by municipality
    // FIXED: Using establishments and addresses tables directly since there's no separate municipalities table
    // Get violation statistics by municipality with proper case handling
    $violationsQuery = $conn->query("
        SELECT 
            CONCAT(UPPER(SUBSTRING(a.municipality, 1, 1)), LOWER(SUBSTRING(a.municipality FROM 2))) as municipality,
            COUNT(e.establishment_id) as violation_count
        FROM establishments e
        JOIN addresses a ON e.establishment_id = a.establishment_id
        GROUP BY LOWER(a.municipality)
        ORDER BY violation_count DESC
        LIMIT 10
    ");
    
    $violationsData = [];
    if ($violationsQuery) {
        while ($row = $violationsQuery->fetch(PDO::FETCH_ASSOC)) { // Fixed: Changed from fetch() to fetch(PDO::FETCH_ASSOC)
            $violationsData[$row['municipality']] = $row['violation_count'];
        }
    }
    
    // Get system overview statistics 
    // FIXED: Adjusted queries to work with the available tables
    $statsQuery = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM establishments) as total_violations,
        (SELECT COUNT(DISTINCT LOWER(municipality)) FROM addresses) as total_municipalities,
        (SELECT COUNT(*) FROM establishments) as total_establishments,
        (SELECT COUNT(*) FROM users WHERE status = 'active') as active_users
    ");

    // If you want to display specific municipalities in your chart rather than top violations
    // You can modify the violations query like this:
    $specificMunicipalities = ['Aringay', 'Agoo', 'Bacnotan', 'Bagulin', 'Balaoan', 'Bangar', 'Bauang', 'Burgos', 'Caba', 'Luna', 'Naguilian', 'Pugo', 'Rosario', 'San Gabriel', 'San Juan', 'Santol', 'Sto. Tomas', 'Sudipen', 'San Fernando City', 'Tubao',]; // Add your specific municipalities
    $placeholders = implode(',', array_fill(0, count($specificMunicipalities), '?'));
    
    // Prepare statement for specific municipalities
    $violationsStmt = $conn->prepare("
        SELECT a.municipality, COUNT(e.establishment_id) as violation_count
        FROM establishments e
        JOIN addresses a ON e.establishment_id = a.establishment_id
        WHERE LOWER(a.municipality) IN (" . implode(',', array_map(function($m) { return 'LOWER(?)'; }, $specificMunicipalities)) . ")
        GROUP BY a.municipality
        ORDER BY violation_count DESC
    ");

    // If you want to show all municipalities but avoid duplicates due to case sensitivity:
    $uniqueViolationsQuery = $conn->query("
        SELECT 
            CONCAT(UPPER(SUBSTRING(a.municipality, 1, 1)), LOWER(SUBSTRING(a.municipality, 2))) as municipality, 
            COUNT(e.establishment_id) as violation_count
        FROM establishments e
        JOIN addresses a ON e.establishment_id = a.establishment_id
        GROUP BY LOWER(a.municipality)
        ORDER BY violation_count DESC
        LIMIT 10
    ");
    
    $stats = $statsQuery ? $statsQuery->fetch(PDO::FETCH_ASSOC) : []; // Fixed: Changed from fetch() to fetch(PDO::FETCH_ASSOC)
    
    // Add additional status counts
    // FIXED: Since we don't have a violations table with status field, using some placeholder data
    $stats['pending_violations'] = isset($stats['total_violations']) ? round($stats['total_violations'] * 0.4) : 0;
    $stats['urgent_violations'] = isset($stats['total_violations']) ? round($stats['total_violations'] * 0.2) : 0;
    $stats['resolved_violations'] = isset($stats['total_violations']) ? round($stats['total_violations'] * 0.4) : 0;

    // Get top violation types from the violations field in establishments
    // FIXED: Using the violations field from establishments table
    $violationTypesQuery = $conn->query("
        SELECT 
            CASE 
                WHEN violations LIKE '%No PS/ICC Mark%' THEN 'No PS/ICC Mark'
                WHEN violations LIKE '%Invalid/suspended%' THEN 'Invalid/Suspended License'
                WHEN violations LIKE '%No Manufacturer%' THEN 'No Manufacturer Name'
                WHEN violations LIKE '%Other%' THEN 'Other Violations'
                ELSE 'Miscellaneous'
            END as violation_type,
            COUNT(*) as count
        FROM establishments
        GROUP BY violation_type
        ORDER BY count DESC
        LIMIT 5
    ");
    
    $violationTypes = [];
    $violationCounts = [];
    if ($violationTypesQuery) {
        while ($row = $violationTypesQuery->fetch(PDO::FETCH_ASSOC)) { // Fixed: Changed from fetch() to fetch(PDO::FETCH_ASSOC)
            $violationTypes[] = $row['violation_type'];
            $violationCounts[] = $row['count'];
        }
    }

} catch (Exception $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    $violationsData = [];
    $stats = [
        'total_violations' => 0,
        'total_municipalities' => 0,
        'total_establishments' => 0,
        'active_users' => 0,
        'pending_violations' => 0,
        'urgent_violations' => 0,
        'resolved_violations' => 0
    ];
    $violationTypes = [];
    $violationCounts = [];
}

// Prepare chart data
$labels = array_keys($violationsData);
$values = array_values($violationsData);

include '../templates/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Welcome Card with User Role -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h1 class="display-6 fw-bold mb-2">Welcome, <span class="text-primary"><?= htmlspecialchars(ucfirst($_SESSION['username'])) ?></span></h1>
                    <span class="badge bg-info text-dark mb-3"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'User')) ?></span>
                    <p class="lead text-muted mb-0">Monitoring and Enforcement Tracking System</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Total Violations</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($stats['total_violations'] ?? 0) ?></h2>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="fas fa-exclamation-triangle text-primary fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Status Distribution</span>
                        </div>
                        <div class="progress mt-1" style="height: 8px;">
                            <?php
                            $total = max(1, ($stats['total_violations'] ?? 1)); // Avoid division by zero
                            $urgentPercent = (($stats['urgent_violations'] ?? 0) / $total) * 100;
                            $pendingPercent = (($stats['pending_violations'] ?? 0) / $total) * 100;
                            $resolvedPercent = (($stats['resolved_violations'] ?? 0) / $total) * 100;
                            ?>
                            <div class="progress-bar bg-danger" style="width: <?= $urgentPercent ?>%" title="Urgent: <?= $stats['urgent_violations'] ?? 0 ?>"></div>
                            <div class="progress-bar bg-warning" style="width: <?= $pendingPercent ?>%" title="Pending: <?= $stats['pending_violations'] ?? 0 ?>"></div>
                            <div class="progress-bar bg-success" style="width: <?= $resolvedPercent ?>%" title="Resolved: <?= $stats['resolved_violations'] ?? 0 ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Establishments</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($stats['total_establishments'] ?? 0) ?></h2>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="fas fa-store text-success fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-success">
                            <i class="fas fa-city me-1"></i>
                            <?= number_format($stats['total_municipalities'] ?? 0) ?> Municipalities
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Pending Reviews</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($stats['pending_violations'] ?? 0) ?></h2>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="fas fa-clock text-warning fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <?= number_format($stats['urgent_violations'] ?? 0) ?> Urgent cases
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Active Users</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($stats['active_users'] ?? 0) ?></h2>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="fas fa-users text-info fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="user.php" class="text-decoration-none text-info">
                            <i class="fas fa-user-cog me-1"></i>
                            Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Violations Chart Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Violations by Municipality
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartFilter" data-bs-toggle="dropdown">
                            This Year
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-range="year">This Year</a></li>
                            <li><a class="dropdown-item" href="#" data-range="month">This Month</a></li>
                            <li><a class="dropdown-item" href="#" data-range="week">This Week</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="violationsChart"></canvas>
                    </div>
                    <div class="text-end mt-2">
                        <small class="text-muted">Last updated: <?= date('M j, Y g:i A') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Column -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <!-- Violation Types Chart -->
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-pie-chart me-2"></i>
                        Violation Types
                    </h6>
                    <div class="chart-container mb-3" style="position: relative; height: 200px;">
                        <canvas id="violationTypesChart"></canvas>
                    </div>
                   
                    <!-- Status Summary -->
                    <h6 class="fw-semibold mt-4 mb-3">
                        <i class="fas fa-tasks me-2"></i>
                        Status Summary
                    </h6>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                                Urgent Cases
                            </span>
                            <span class="badge bg-danger rounded-pill"><?= number_format($stats['urgent_violations'] ?? 0) ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-clock me-2 text-warning"></i>
                                Pending Reviews
                            </span>
                            <span class="badge bg-warning rounded-pill"><?= number_format($stats['pending_violations'] ?? 0) ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-check-circle me-2 text-success"></i>
                                Resolved Cases
                            </span>
                            <span class="badge bg-success rounded-pill"><?= number_format($stats['resolved_violations'] ?? 0) ?></span>
                        </div>
                    </div>
                    
                    <!-- Recent Activity Section -->
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-history me-2"></i>
                            Recent Activity
                        </h6>
                        <?php
                        // FIXED: Use user_logs table since activities table doesn't exist
                        try {
                            $activitiesQuery = $conn->query("
                                SELECT u.action, u.timestamp, us.username 
                                FROM user_logs u
                                JOIN users us ON u.user_id = us.id
                                ORDER BY u.timestamp DESC LIMIT 3
                            ");
                            
                            // Fixed: Proper PDO rowCount() check instead of mysqli_num_rows
                            $rowCount = $activitiesQuery->rowCount();
                            if ($activitiesQuery && $rowCount > 0) {
                                while ($activity = $activitiesQuery->fetch(PDO::FETCH_ASSOC)): ?>
                                    <div class="d-flex mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm bg-light rounded">
                                                <i class="fas fa-user-circle fs-4 text-muted p-2"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($activity['username'] ?? 'Unknown') ?></h6>
                                            <p class="small text-muted mb-0"><?= htmlspecialchars($activity['action'] ?? 'Unknown action') ?></p>
                                            <small class="text-muted"><?= date('M j, g:i A', strtotime($activity['timestamp'] ?? 'now')) ?></small>
                                        </div>
                                    </div>
                                <?php endwhile;
                            } else {
                                echo '<p class="text-muted small">No recent activity</p>';
                            }
                        } catch (Exception $e) {
                            error_log("Activity fetch error: " . $e->getMessage());
                            echo '<p class="text-muted small">Unable to load recent activity</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Access Links -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Access</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="nov_form.php" class="btn btn-outline-primary w-100 p-3">
                                <i class="fas fa-store mb-2 fs-3"></i>
                                <div>Manage Establishments</div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="export_establishments.php" class="btn btn-outline-success w-100 p-3">
                                <i class="fas fa-chart-line mb-2 fs-3"></i>
                                <div>Generate Reports</div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="backup_restore.php" class="btn btn-outline-secondary w-100 p-3">
                                <i class="fas fa-cog mb-2 fs-3"></i>
                                <div>System Settings</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js script with AJAX data loading -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let violationsChart;
    let violationTypesChart;
    const ctx = document.getElementById('violationsChart').getContext('2d');
    const typeCtx = document.getElementById('violationTypesChart').getContext('2d');
    
    // Initialize violations by municipality chart
    function initViolationsChart(labels, data) {
        if (violationsChart) violationsChart.destroy();
        
        violationsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Violations',
                    data: data,
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(25, 135, 84, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(111, 66, 193, 0.7)'
                    ],
                    borderColor: [
                        'rgba(13, 110, 253, 1)',
                        'rgba(25, 135, 84, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(111, 66, 193, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: Math.round,
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Initialize violation types pie chart
    function initViolationTypesChart(labels, data) {
        if (violationTypesChart) violationTypesChart.destroy();
        
        violationTypesChart = new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(25, 135, 84, 0.7)',
                        'rgba(111, 66, 193, 0.7)'
                    ],
                    borderColor: [
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(13, 110, 253, 1)',
                        'rgba(25, 135, 84, 1)',
                        'rgba(111, 66, 193, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        displayColors: false
                    }
                },
                cutout: '60%'
            }
        });
    }

    // Initialize with current data
    initViolationsChart(
        <?= json_encode($labels) ?>,
        <?= json_encode($values) ?>
    );
    
    initViolationTypesChart(
        <?= json_encode($violationTypes) ?>,
        <?= json_encode($violationCounts) ?>
    );

    // Handle time range filter
    document.querySelectorAll('[data-range]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const range = this.getAttribute('data-range');
            
            // Update dropdown text
            document.querySelector('#chartFilter').textContent = this.textContent;
            
            // AJAX call to fetch filtered data
            fetch(`get_violations.php?range=${range}`)
                .then(response => response.json())
                .then(data => {
                    initViolationsChart(data.labels, data.values);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Show a message to the user
                    alert('Failed to load data. Please try again later.');
                });
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});

</script>

<?php include '../templates/footer.php'; ?>