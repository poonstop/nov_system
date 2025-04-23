<?php
session_start();
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

// Sample data for the chart
$data = [
    "San Fernando" => 15,
    "Bauang" => 8,
    "Agoo" => 12,
    "Naguilian" => 5,
    "Luna" => 4
];
$labels = array_keys($data);
$values = array_values($data);

$pageTitle = "Dashboard - Violations Chart";
include '../templates/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h1 class="display-6 fw-bold mb-3">Welcome, <span class="text-primary"><?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?></span></h1>
                    <p class="lead text-muted mb-0">
                        Monitoring and Enforcement Tracking System Non-Compliance
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Violations Chart Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Violations by Municipality in La Union
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="violationsChart"></canvas>
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
                            <span class="text-muted">Total Violations</span>
                            <span class="badge bg-primary rounded-pill"><?php echo array_sum($values); ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Municipalities</span>
                            <span class="badge bg-secondary rounded-pill"><?php echo count($labels); ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Highest Violations</span>
                            <span class="badge bg-danger rounded-pill"><?php echo max($values); ?></span>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Lowest Violations</span>
                            <span class="badge bg-success rounded-pill"><?php echo min($values); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
        background-image: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
    }
    .card {
        border-radius: 0.5rem;
        border: none;
    }
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .list-group-item {
        background-color: transparent;
    }
</style>

<!-- Chart.js script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = <?= json_encode($labels); ?>;
        const data = {
            labels: labels,
            datasets: [{
                label: 'Number of Violations',
                data: <?= json_encode($values); ?>,
                backgroundColor: [
                    'rgba(16, 52, 108, 0.7)',
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderColor: [
                    'rgba(16, 52, 108, 1)',
                    'rgba(13, 110, 253, 1)',
                    'rgba(25, 135, 84, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
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
            }
        };

        const ctx = document.getElementById('violationsChart').getContext('2d');
        new Chart(ctx, config);
    });
</script>

<?php include '../templates/footer.php'; ?>