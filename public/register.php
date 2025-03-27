<?php
/*
session_start();
include __DIR__ . '/../connection.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "Username and password are required!";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already taken!";
        } else {
            try {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user with fullname
                $insert = $conn->prepare("INSERT INTO users (username, password, fullname) VALUES (?, ?, ?)");
                $insert->bind_param("sss", $username, $hashed_password, $username);
                
                if ($insert->execute()) {
                    $_SESSION['success'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed: " . $insert->error;
                }
            } catch (mysqli_sql_exception $e) {
                // Improved error handling
                $error = "Registration failed: " . $e->getMessage();
                error_log("Registration Error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
        }
        .register-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .register-box {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
        }
        .register-header {
            background-color: #2196F3;
            color: white;
            text-align: center;
            padding: 15px;
            margin: -20px -20px 20px;
            border-radius: 8px 8px 0 0;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.5);
        }
        .form-control::placeholder {
            color: #888;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        .form-control:focus::placeholder {
            opacity: 0.4;
        }
        .register-btn, .confirm-btn {
            width: 100%;
            padding: 10px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }
        .register-btn:hover, .confirm-btn:hover {
            background-color: #1976D2;
        }
        .register-btn:active, .confirm-btn:active {
            transform: scale(0.98);
        }
        .error-message {
            color: #d32f2f;
            font-size: 12px;
            margin-bottom: 10px;
            display: none;
            align-items: center;
        }
        .error-message::before {
            content: "ⓘ";
            margin-right: 5px;
            font-size: 14px;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .login-link a {
            color: #2196F3;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .login-link a:hover {
            color: #1976D2;
        }
        label {
            display: none;
        }
        #confirmationModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .confirmation-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        #successPrompt {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        @media (max-width: 480px) {
            .register-container {
                padding: 10px;
            }
            .register-box {
                padding: 15px;
            }
            .form-control {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h2>Create Account</h2>
            </div>
            <?php if (!empty($error)): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px; display: block;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form id="registrationForm" method="POST" action="">
                <input type="text" id="username" name="username" placeholder="Create a username" class="form-control" required 
                       pattern="[a-zA-Z0-9]{4,20}" 
                       title="4-20 characters, letters and numbers only">
                <div class="error-message">Username must be 4-20 characters (letters and numbers only)</div>
                
                <input type="password" id="password" name="password" placeholder="Choose a secure password" class="form-control" required 
                       minlength="8"
                       pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$"
                       title="At least 8 characters with letters and numbers">
                <div class="error-message">Password must be at least 8 characters with letters and numbers</div>
                
                <button type="button" class="register-btn" onclick="showConfirmation()">Create Account</button>
            </form>
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal">
        <div class="confirmation-content">
            <h3>Confirm Registration</h3>
            <p>Are you sure you want to create this account?</p>
            <button class="confirm-btn" onclick="submitForm()">Confirm</button>
            <button class="register-btn" onclick="hideConfirmation()" style="background-color: #f44336; margin-top: 10px;">Cancel</button>
        </div>
    </div>

    <!-- Success Prompt -->
    <div id="successPrompt">
        Account created successfully!
    </div>

    <script>
        // Form validation and dynamic error message display
        const inputs = document.querySelectorAll('.form-control');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const errorMessage = this.nextElementSibling;
                if (!this.validity.valid) {
                    errorMessage.style.display = 'flex';
                } else {
                    errorMessage.style.display = 'none';
                }
            });
        });

        function showConfirmation() {
            // Validate form before showing confirmation
            const form = document.getElementById('registrationForm');
            if (form.checkValidity()) {
                document.getElementById('confirmationModal').style.display = 'flex';
            } else {
                form.reportValidity();
            }
        }

        function hideConfirmation() {
            document.getElementById('confirmationModal').style.display = 'none';
        }

        function submitForm() {
            // Simulate a delay and show success prompt
            hideConfirmation();
            
            // Show success prompt
            const successPrompt = document.getElementById('successPrompt');
            successPrompt.style.display = 'block';

            // Submit form after a short delay
            setTimeout(() => {
                document.getElementById('registrationForm').submit();
            }, 1500);
        }
    </script>
</body>
</html>
*/