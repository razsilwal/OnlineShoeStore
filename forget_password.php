<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // ✅ ADD THIS LINE

// Include Composer autoload (make sure you have run `composer require phpmailer/phpmailer`)
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'components/connect.php';

include 'components/hashing.php';

$message = [];

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Check if email exists in the database
    $check_email = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $check_email->execute([$email]);

    if ($check_email->rowCount() > 0) {
        // Generate 6-digit OTP
        $otp = '';
        for ($i = 0; $i < 6; $i++) {
            $otp .= mt_rand(0, 9);
        }

        $expiry_time = time() + 120; // 120 seconds expiry

        // Store OTP and expiry in DB
        $update_otp = $conn->prepare("UPDATE `users` SET otp = ?, otp_expiry = ? WHERE email = ?");
        $update_otp->execute([$otp, $expiry_time, $email]);

        // ✅ ADD THESE TWO LINES - Store session variables for verify_otp.php
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_sent'] = true;

        // Send Email via PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sabalsilwal51@gmail.com';
            $mail->Password   = 'rfyfgxcsbxczerzi';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Sender & recipient
            $mail->setFrom('sabalsilwal51@gmail.com', 'Kickster Support');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP Code';
            $mail->Body    = "Hello,<br><br>Your OTP for password reset is: <strong>$otp</strong><br><br>This OTP will expire in 2 minutes.<br><br>Regards,<br>Kickster";

            $mail->send();
            header("Location: verify_otp.php");
            exit();
        } catch (Exception $e) {
            $message[] = "❌ Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $message[] = "❌ Email not found in our system.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Kickster</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary: #f72585;
            --accent: #fca311;
            --light: #f8f9fa;
            --dark: #14213d;
            --gray: #6c757d;
            --danger: #ef233c;
            --success: #2ec4b6;
            --border: #e2e8f0;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: var(--dark);
        }
        
        .container {
            background: white;
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 28rem;
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 12rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            z-index: -1;
            clip-path: ellipse(100% 80% at 50% 0%);
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            padding: 2.5rem 0 1.5rem;
        }
        
        .logo {
            width: 5rem;
            height: 5rem;
            background: white;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .logo i {
            font-size: 2.5rem;
            color: var(--primary);
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .content {
            padding: 0 2.5rem 2.5rem;
        }
        
        h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .subtitle {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 1;
        }
        
        .card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            animation: slideIn 0.4s ease-out;
            border: 1px solid transparent;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-danger {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger);
            border-color: rgba(239, 35, 60, 0.2);
        }
        
        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .input-group {
            position: relative;
        }
        
        input[type="email"] {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }
        
        input[type="email"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: white;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.25rem;
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .btn:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .footer-text a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .footer-text a:hover {
            text-decoration: underline;
            color: var(--primary-dark);
        }
        
        .illustration {
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .illustration svg {
            width: 12rem;
            height: auto;
            opacity: 0.9;
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .container {
                border-radius: 1rem;
            }
            
            .content {
                padding: 0 1.5rem 1.5rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-shoe-prints"></i>
            </div>
        </div>
        
        <div class="content">
            <h1>Forgot Password?</h1>
            <p class="subtitle">Enter your email to receive a password reset link</p>
            
            <div class="card">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $message[0]; ?>
                    </div>
                <?php endif; ?>
                
                <div class="illustration">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" placeholder="your@email.com" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit" class="btn">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>
            </div>
            
            <p class="footer-text">
                Remember your password? <a href="user_login.php">Sign in here</a>
            </p>
        </div>
    </div>
</body>
</html>