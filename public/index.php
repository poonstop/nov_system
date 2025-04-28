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

// Fetch real data from database
try {
    // Get violation statistics by municipality
    $violationsQuery = $conn->query("
        SELECT m.municipality_name, COUNT(v.violation_id) as violation_count
        FROM violations v
        JOIN establishments e ON v.establishment_id = e.establishment_id
        JOIN municipalities m ON e.municipality_id = m.municipality_id
        GROUP BY m.municipality_name
        ORDER BY violation_count DESC
    ");
    
    $violationsData = [];
    while ($row = $violationsQuery->fetch_assoc()) {
        $violationsData[$row['municipality_name'] = $row['violation_count']];
    }
    // Get system overview statistics
    $statsQuery = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM violations) as total_violations,
            (SELECT COUNT(DISTINCT municipality_id) FROM establishments) as total_municipalities,
            (SELECT COUNT(*) FROM establishments) as total_establishments,
            (SELECT COUNT(*) FROM users WHERE is_active = 1) as active_users
    ");
    $stats = $statsQuery->fetch_assoc();

} catch (Exception $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    $violationsData = [];
    $stats = [
        'total_violations' => 0,
        'total_municipalities' => 0,
        'total_establishments' => 0,
        'active_users' => 0
    ];
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
                    <span class="badge bg-info text-dark mb-3"><?= htmlspecialchars(ucfirst($_SESSION['role'])) ?></span>
                    <p class="lead text-muted mb-0">Monitoring and Enforcement Tracking System Non - Compliance</p>
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
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        System Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                Total Violations
                            </span>
                            <span class="badge bg-primary rounded-pill"><?= number_format($stats['total_violations']) ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-city me-2 text-info"></i>
                                Municipalities
                            </span>
                            <span class="badge bg-secondary rounded-pill"><?= number_format($stats['total_municipalities']) ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-store me-2 text-success"></i>
                                Establishments
                            </span>
                            <span class="badge bg-success rounded-pill"><?= number_format($stats['total_establishments']) ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-users me-2 text-danger"></i>
                                Active Users
                            </span>
                            <span class="badge bg-danger rounded-pill"><?= number_format($stats['active_users']) ?></span>
                        </div>
                    </div>
                    
                    <!-- Recent Activity Section -->
                    <div class="mt-4">
    <h6 class="fw-semibold mb-3">
        <i class="fas fa-history me-2"></i>
        Recent Activity
    </h6>
    <?php
    try {
        $activitiesQuery = $conn->query("
            SELECT a.action_type, a.description, u.username, a.created_at 
            FROM activities a
            JOIN users u ON a.user_id = u.user_id
            ORDER BY a.created_at DESC LIMIT 3
        ");
        
        while ($activity = $activitiesQuery->fetch_assoc()): ?>
            <div class="d-flex mb-3">
                <div class="flex-shrink-0">
                    <div class="avatar-sm bg-light rounded">
                        <i class="fas fa-user-circle fs-4 text-muted p-2"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-2">
                    <h6 class="mb-0"><?= htmlspecialchars($activity['username']) ?></h6>
                    <p class="small text-muted mb-0"><?= htmlspecialchars($activity['description']) ?></p>
                    <small class="text-muted"><?= date('M j, g:i A', strtotime($activity['created_at'])) ?></small>
                </div>
            </div>
        <?php endwhile;
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
</div>

<!-- Chart.js script with AJAX data loading -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let chart;
    const ctx = document.getElementById('violationsChart').getContext('2d');
    
    // Initial chart setup
    function initChart(labels, data) {
        if (chart) chart.destroy();
        
        chart = new Chart(ctx, {
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

    // Initialize with current data
    initChart(<?= json_encode($labels) ?>, <?= json_encode($values) ?>);

    // Handle time range filter
    document.querySelectorAll('[data-range]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const range = this.getAttribute('data-range');
            
            // Update dropdown text
            document.querySelector('#chartFilter').textContent = this.textContent;
            
            // AJAX call to fetch filtered data
            fetch(`api/get_violations.php?range=${range}`)
                .then(response => response.json())
                .then(data => {
                    initChart(data.labels, data.values);
                })
                .catch(error => console.error('Error:', error));
        });
    });
});
</script>

    <?php include '../templates/footer.php'; ?>