<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../templates/header.php';

// Example data (replace with actual database queries)
$data = [
    "San Fernando" => 15,
    "Bauang" => 8,
    "Agoo" => 12,
    "Naguilian" => 5,
    "Luna" => 4
];
$labels = array_keys($data);
$values = array_values($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Violations Chart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
         background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
        }
        </Style>
</head>
<body>
<div class="container mt-5">
    <h1>Dashboard</h1>
    <span class="navbar-text me-3">
    Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'User')); ?>!
    </span>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Violations by Municipality in La Union</h5>
            <canvas id="violationsChart"></canvas>
        </div>
    </div>
</div>

<script>
    // Data for the chart
    const labels = <?= json_encode($labels); ?>;
    const data = {
        labels: labels,
        datasets: [{
            label: 'Number of Violations',
            data: <?= json_encode($values); ?>,
            backgroundColor: [
                'rgba(75, 192, 192, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 99, 132, 0.2)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    };

    // Configuration for the chart
    const config = {
        type: 'bar', // Change to 'pie' or 'line' for different chart types
        data: data,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    // Render the chart
    const ctx = document.getElementById('violationsChart').getContext('2d');
    new Chart(ctx, config);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include '../templates/footer.php'; ?>
