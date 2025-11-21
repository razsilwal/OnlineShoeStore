<?php
session_start();
include 'components/connect.php';
include 'components/hashing.php';
// Test database connection
try {
    $test_conn = $conn->query("SELECT 1");
    echo "Database connection: OK<br>";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}

// Test if users table exists
try {
    $test_table = $conn->query("SELECT COUNT(*) FROM users");
    echo "Users table: OK<br>";
} catch (PDOException $e) {
    echo "Users table error: " . $e->getMessage() . "<br>";
}


// Check if OTP is verified
if(!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])){
    header('Location: forgot_password.php');
    exit();
}

$email = $_SESSION['reset_email'];
$success = false;
$message = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if(empty($new_password) || empty($confirm_password)){
        $message[] = 'All password fields are required!';
    } elseif($new_password !== $confirm_password){
        $message[] = 'Passwords do not match!';
    } elseif(strlen($new_password) < 8){
        $message[] = 'Password must be at least 8 characters!';
    } else {
        // Hash the new password using the SAME algorithm
        $hashed_password = custom_hash($new_password);
        
        // Update password in database
        $update_user = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_user->execute([$hashed_password, $email]);
        
        if($update_user->rowCount() > 0){
            $success = true;
            // Clear session data
            unset($_SESSION['reset_email'], $_SESSION['otp_sent'], $_SESSION['otp_verified']);
            
            // Clear OTP data from database
            $clear_otp = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE email = ?");
            $clear_otp->execute([$email]);
        } else {
    // Debug information
    $message[] = 'Failed to reset password. Please try again.';
    
    // Add debug info to see what's happening
    error_log("Password reset failed for email: " . $email);
    error_log("New password length: " . strlen($new_password));
    error_log("Hashed password length: " . strlen($hashed_password));
    
    // Check if user exists
    $check_user = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $check_user->execute([$email]);
    $user = $check_user->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $message[] = 'Debug: User not found with this email.';
    } else {
        $message[] = 'Debug: User exists. Hash update failed.';
        $message[] = 'Debug: Old hash length: ' . strlen($user['password']);
        $message[] = 'Debug: New hash length: ' . strlen($hashed_password);
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
    <title>Reset Password | Kickster</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 10px;
        }

        .logo h1 {
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .success-box i {
            font-size: 2.5rem;
            color: #28a745;
            margin-bottom: 15px;
        }

        .success-box h3 {
            color: #155724;
            margin-bottom: 10px;
        }

        .redirect-message {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }

        .message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }

        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            text-align: left;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .login-link {
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 25px;
                margin: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-shoe-prints"></i>
            <h1>Kickster</h1>
        </div>

        <h2>Create New Password</h2>
        <p class="subtitle">Enter your new password to secure your account</p>
        
        <?php if ($success): ?>
            <div class="success-box">
                <i class="fas fa-check-circle"></i>
                <h3>Password Updated Successfully!</h3>
                <p>Your password has been reset. You can now login with your new password.</p>
            </div>
            
            <p class="redirect-message">
                <i class="fas fa-spinner fa-spin"></i> Redirecting to login page...
            </p>
            <script>
                setTimeout(function() {
                    window.location.href = 'user_login.php';
                }, 3000);
            </script>
        <?php else: ?>
        
        <?php
        if(isset($message)){
            foreach($message as $msg){
                echo '<div class="message">'.$msg.'</div>';
            }
        }
        ?>
        
        <form method="POST" id="passwordForm">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div class="form-group">
                <label for="newPassword">New Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="new_password" id="newPassword" required placeholder="Enter new password" minlength="8" autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-hint">Must be at least 8 characters</div>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" id="confirmPassword" required placeholder="Confirm new password" minlength="8" autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-key"></i> Reset Password
            </button>
        </form>
        <?php endif; ?>
        
        <p class="login-link">
            Remember your password? <a href="user_login.php">Login here</a>
        </p>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const icon = passwordField.parentElement.querySelector('.password-toggle i');
            
            if(passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Validate password match on form submit
        document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                const alertDiv = document.createElement('div');
                alertDiv.className = 'message';
                alertDiv.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match!';
                
                const container = document.querySelector('.container');
                const form = document.getElementById('passwordForm');
                container.insertBefore(alertDiv, form);
                
                // Remove alert after 3 seconds
                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    setTimeout(() => alertDiv.remove(), 300);
                }, 3000);
            }
        });

        // Auto-focus first password field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('newPassword').focus();
        });
    </script>
</body>
</html>