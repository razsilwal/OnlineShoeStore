<?php
session_start();
include 'components/connect.php';

// Check if user came from forgot password
if(!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_sent'])){
    header('Location: forgot_password.php');
    exit();
}

$email = $_SESSION['reset_email'];
$message = [];

if(isset($_POST['verify_otp'])){
    $entered_otp = $_POST['otp'];
    
    if(empty($entered_otp)){
        $message[] = 'Please enter OTP!';
    } else {
        // Verify OTP from database
        $current_time = time();
        $check_otp = $conn->prepare("SELECT id FROM users WHERE email = ? AND otp = ? AND otp_expiry > ?");
        $check_otp->execute([$email, $entered_otp, $current_time]);
        
        if($check_otp->rowCount() > 0){
            $_SESSION['otp_verified'] = true;
            header('Location: reset_password.php');
            exit();
        } else {
            $message[] = 'Invalid or expired OTP! Please try again.';
        }
    }
}

if(isset($_POST['resend_otp'])){
    // Generate new OTP
    $new_otp = '';
    for ($i = 0; $i < 6; $i++) {
        $new_otp .= mt_rand(0, 9);
    }
    
    $expiry_time = time() + 120; // 2 minutes
    
    // Store new OTP in database
    $update_otp = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
    if($update_otp->execute([$new_otp, $expiry_time, $email])){
        $message[] = "New OTP sent! Demo OTP: <strong>$new_otp</strong> (This would be emailed in production)";
    } else {
        $message[] = 'Failed to resend OTP. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | Kickster</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== RESET ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ===== BODY ===== */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== HEADER ===== */
        .header {
            width: 100%;
            padding: 15px 30px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #fff;
            font-weight: 600;
            font-size: 22px;
        }

        .cube-container {
            margin-right: 8px;
        }

        .rotating-cube {
            animation: spin 4s linear infinite;
            font-size: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .theme-toggle {
            background: transparent;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.2);
        }

        /* ===== MAIN FORM SECTION ===== */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 15px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.08);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 450px;
            color: #fff;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== FORM HEADER ===== */
        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-header h2 {
            margin-bottom: 10px;
            font-weight: 600;
            color: #2ecc71;
        }

        .form-header p {
            color: #bbb;
            font-size: 14px;
        }

        .email-display {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            border-radius: 8px;
            padding: 10px 15px;
            margin: 15px 0;
            font-size: 14px;
            color: #3498db;
            text-align: center;
        }

        /* ===== MESSAGES ===== */
        .message {
            background: rgba(255, 99, 71, 0.15);
            border: 1px solid rgba(255, 99, 71, 0.5);
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }

        .message.success {
            background: rgba(46, 204, 113, 0.15);
            border-color: rgba(46, 204, 113, 0.5);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* ===== OTP CONTAINER ===== */
        .otp-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 25px 0;
        }

        .otp-input {
            width: 55px;
            height: 65px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transition: all 0.3s ease;
            outline: none;
        }

        .otp-input:focus {
            border-color: #2ecc71;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 15px rgba(46, 204, 113, 0.3);
            transform: scale(1.05);
        }

        .otp-input.filled {
            border-color: #3498db;
            background: rgba(52, 152, 219, 0.1);
        }

        /* ===== BUTTON ===== */
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            background: linear-gradient(135deg, #27ae60, #219653);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-resend {
            background: transparent;
            border: 2px solid #3498db;
            color: #3498db;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 auto;
        }

        .btn-resend:hover {
            background: rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        /* ===== RESEND OPTION ===== */
        .resend-option {
            text-align: center;
            margin: 25px 0;
            color: #bbb;
            font-size: 14px;
        }

        .resend-option p {
            margin-bottom: 15px;
        }

        /* ===== FOOTER TEXT ===== */
        .form-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #bbb;
        }

        .form-footer a {
            color: #2ecc71;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: #27ae60;
            text-decoration: underline;
        }

        /* ===== ILLUSTRATION ===== */
        .illustration {
            text-align: center;
            margin: 1.5rem 0;
            color: #f39c12;
        }

        .illustration i {
            font-size: 4rem;
            opacity: 0.8;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .form-container {
                padding: 25px 20px;
                margin: 15px;
            }
            
            .otp-input {
                width: 45px;
                height: 55px;
                font-size: 20px;
            }
            
            .otp-container {
                gap: 8px;
            }
        }
    </style>
</head>
<body>
   
<header class="header">
    <a href="home.php" class="logo">
        <div class="cube-container">
            <i class="fas fa-cube rotating-cube"></i>
        </div>
        <span class="logo-text">Kickster</span>
    </a>
    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </button>
</header>

<main>
    <section class="form-container">
        <div class="form-header">
            <h2>Verify OTP</h2>
            <p>Enter the 6-digit code sent to your email</p>
        </div>
        
        <div class="email-display">
            <i class="fas fa-envelope"></i> 
            <?php echo htmlspecialchars($email); ?>
        </div>
        
        <?php
        if(isset($message)){
            foreach($message as $msg){
                $is_success = strpos($msg, 'New OTP sent') !== false;
                $class = $is_success ? 'message success' : 'message';
                echo '<div class="'.$class.'">'.$msg.'</div>';
            }
        }
        ?>

        <div class="illustration">
            <i class="fas fa-mobile-alt"></i>
        </div>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="otp">6-Digit Verification Code</label>
                <div class="otp-container">
                    <input type="text" name="otp1" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp2')" pattern="[0-9]" required>
                    <input type="text" name="otp2" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp3')" pattern="[0-9]" required>
                    <input type="text" name="otp3" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp4')" pattern="[0-9]" required>
                    <input type="text" name="otp4" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp5')" pattern="[0-9]" required>
                    <input type="text" name="otp5" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp6')" pattern="[0-9]" required>
                    <input type="text" name="otp6" class="otp-input" maxlength="1" pattern="[0-9]" required>
                </div>
                <input type="hidden" name="otp" id="fullOtp">
            </div>
            
            <button type="submit" class="btn" name="verify_otp" onclick="combineOtp()">
                <i class="fas fa-check-circle"></i>
                <span>Verify OTP</span>
            </button>
            
            <div class="resend-option">
                <p>Didn't receive the code?</p>
                <button type="submit" name="resend_otp" class="btn-resend">
                    <i class="fas fa-redo"></i> Resend OTP
                </button>
            </div>
            
            <div class="form-footer">
                <a href="forgot_password.php">← Back to email entry</a>
            </div>
        </form>
    </section>
</main>

<script>
   function moveToNext(current, nextFieldName) {
        if (current.value.length >= current.maxLength) {
            const nextField = document.getElementsByName(nextFieldName)[0];
            if (nextField) {
                nextField.focus();
            }
        }
        
        // Update visual state
        if (current.value.length > 0) {
            current.classList.add('filled');
        } else {
            current.classList.remove('filled');
        }
    }
    
    function combineOtp() {
        const otp1 = document.getElementsByName('otp1')[0].value;
        const otp2 = document.getElementsByName('otp2')[0].value;
        const otp3 = document.getElementsByName('otp3')[0].value;
        const otp4 = document.getElementsByName('otp4')[0].value;
        const otp5 = document.getElementsByName('otp5')[0].value;
        const otp6 = document.getElementsByName('otp6')[0].value;
        
        document.getElementById('fullOtp').value = otp1 + otp2 + otp3 + otp4 + otp5 + otp6;
    }
    
    // Auto-focus first OTP input and handle backspace
    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
        const firstInput = otpInputs[0];
        firstInput.focus();
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    const prevInput = otpInputs[index - 1];
                    prevInput.focus();
                    prevInput.value = '';
                    prevInput.classList.remove('filled');
                }
            });
            
            input.addEventListener('input', function(e) {
                if (this.value.length > 0) {
                    this.classList.add('filled');
                } else {
                    this.classList.remove('filled');
                }
            });
        });
    });

    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Check for saved theme preference or use system preference
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark' || (!currentTheme && prefersDarkScheme.matches)) {
        document.documentElement.setAttribute('data-theme', 'dark');
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
    }
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            document.documentElement.removeAttribute('data-theme');
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
            localStorage.setItem('theme', 'dark');
        }
    });
</script>

</body>
</html>