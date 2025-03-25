<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nov_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "User not found.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice of Violation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
            color: #333;
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-title {
            text-align: center;
            font-size: 1.5rem;
            color: #10346C;
            margin-bottom: 15px;
            font-weight: bold;
        }

        h1 {
            font-size: 1.8rem;
            color: #10346C;
            margin-bottom: 20px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-img {
            width: 80px; /* Adjust the size of logos */
            margin: 0 10px;
        }

        .time-display {
            text-align: center;
            color: #10346C;
            font-size: 1.5rem; /* Increased font size */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Minimalistic and readable font */
            font-weight: 600;
            margin-bottom: 15px;
        }

        .btn-primary {
            background-color: #10346C !important;
            border-color: #10346C !important;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #0d2b58 !important;
            border-color: #0d2b58 !important;
        }

        .alert {
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        input.form-control {
            border-radius: 5px;
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 1rem;
        }

        input.form-control:focus {
            border-color: #10346C;
            box-shadow: 0 0 5px rgba(16, 52, 108, 0.5);
            outline: none;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo-container">
        <img src="../images/dti-logo.png" alt="DTI Logo" class="logo-img">
        <img src="../images/Bagong-Pilipinas-Logo-1200x1250.png" alt="Bagong Pilipinas Logo" class="logo-img">
    </div>
    <div class="header-title">Notice of Violation Monitoring System</div>
    <div class="time-display" id="current-time">Loading time...</div>
    <h1 class="text-center">Login</h1>
    <?php if (isset($login_error)): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($login_error) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
</div>
<script>
    // Function to update the time display
    function updateTime() {
        const now = new Date();
        const formattedTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('current-time').textContent = formattedTime;
    }
    // Update time every second
    setInterval(updateTime, 1000);
    updateTime();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
