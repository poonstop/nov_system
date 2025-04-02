<?php
session_start();
include __DIR__ . '/../connection.php';

if (isset($_SESSION['logout_message'])) {
    echo '<script>
    Swal.fire({
        icon: "success",
        title: "Logged Out",
        text: "'.$_SESSION['logout_message'].'",
        showConfirmButton: false,
        timer: 2000
    });
    </script>';
    unset($_SESSION['logout_message']);
}
if (isset($_GET['timeout'])) {
    $timeout_message = "Your session has expired due to inactivity. Please log in again.";
}

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$login_error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $login_error = "Please fill in all fields";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, fullname FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                
                header("Location: index.php");
                exit();
            } else {
                $login_error = "Invalid password";
            }
        } else {
            $login_error = "User not found";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice of Violation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Favicon (tab logo) -->
    <link rel="icon" href="../images/dti-logo.ico" type="../images/dti-logo.ico">
    <link rel="shortcut icon" href="../images/dti-logo.ico" type="../images/dti-logo.ico">

    <!-- For modern browsers (PNG format) -->
    <link rel="icon" type="../images/dti-logo1.png" href="../images/dti-logo1.png">
    <style>
        /* Keep your existing CSS styles */
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
            width: 80px;
            margin: 0 10px;
        }

        .time-display {
            text-align: center;
            color: #10346C;
            font-size: 1.5rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            color: #6c757d;
        }
        .toggle-password:hover {
            color: #10346C;
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
    <?php if ($login_error): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($login_error) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($timeout_message)): ?>
<div class="alert alert-warning">
    <?= htmlspecialchars($timeout_message) ?>
</div>
<?php endif; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" 
                   placeholder="Enter your username" required autocomplete="username">
        </div>
        <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="password-container">
            <input type="password" class="form-control" id="password" name="password" 
                   placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="toggle-password" aria-label="Show password">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Registration link for users who don't have an account -->
<!--<div class="mt-3 text-center">
        Don't have an account? <a href="register.php">Register here</a>    
</div>
</div>-->

<script>
     // Password toggle functionality
     document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    // Time display script
    function updateTime() {
        const now = new Date();
        const options = { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: true
        };
        document.getElementById('current-time').textContent = now.toLocaleTimeString('en-PH', options);
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>