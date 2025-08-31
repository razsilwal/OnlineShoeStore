<?php
include 'components/connect.php';

$email = $_GET['email'] ?? '';
$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];

    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if (time() <= $user['otp_expiry']) {
            if ($entered_otp == $user['otp']) {
                // OTP is correct and valid
                header("Location: reset_password_form.php?email=" . urlencode($email));
                exit();
            } else {
                $message[] = "Incorrect OTP. Please try again.";
            }
        } else {
            $message[] = "OTP has expired. Please request a new one.";
        }
    } else {
        $message[] = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - GKStore</title>
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
        
        h2 {
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
        
        .email-display {
            font-weight: 600;
            color: white;
            word-break: break-all;
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
        
        .alert-warning {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--secondary);
            border-color: rgba(247, 37, 133, 0.2);
        }
        
        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .otp-form {
            margin-top: 1rem;
        }
        
        .otp-input-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .otp-input {
            width: 3.5rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border: 2px solid var(--border);
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }
        
        .otp-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: white;
        }
        
        .btn-verify {
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
        }
        
        .btn-verify:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-verify:active {
            transform: translateY(0);
        }
        
        .resend-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .resend-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .resend-link a:hover {
            text-decoration: underline;
            color: var(--primary-dark);
        }
        
        .timer {
            color: var(--secondary);
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .container {
                border-radius: 1rem;
            }
            
            .content {
                padding: 0 1.5rem 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .otp-input {
                width: 3rem;
                height: 3rem;
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
        
        <div class="content">
            <h2>Verify Your Identity</h2>
            <p class="subtitle">We've sent a 6-digit code to<br><span class="email-display"><?php echo htmlspecialchars($email); ?></span></p>
            
            <div class="card">
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo strpos($message[0], 'expired') !== false ? 'alert-warning' : 'alert-danger'; ?>">
                        <i class="fas <?php echo strpos($message[0], 'expired') !== false ? 'fa-exclamation-triangle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $message[0]; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="otp-form">
                    <div class="otp-input-container">
                        <input type="text" name="otp1" class="otp-input" maxlength="1" pattern="\d" required autofocus oninput="moveToNext(this, 'otp2')">
                        <input type="text" name="otp2" class="otp-input" maxlength="1" pattern="\d" required oninput="moveToNext(this, 'otp3')">
                        <input type="text" name="otp3" class="otp-input" maxlength="1" pattern="\d" required oninput="moveToNext(this, 'otp4')">
                        <input type="text" name="otp4" class="otp-input" maxlength="1" pattern="\d" required oninput="moveToNext(this, 'otp5')">
                        <input type="text" name="otp5" class="otp-input" maxlength="1" pattern="\d" required oninput="moveToNext(this, 'otp6')">
                        <input type="text" name="otp6" class="otp-input" maxlength="1" pattern="\d" required oninput="moveToNext(this, 'btn-verify')">
                    </div>
                    
                    <input type="hidden" name="otp" id="full-otp">
                    
                    <button type="submit" class="btn-verify" id="btn-verify">
                        <i class="fas fa-check-circle"></i> Verify Code
                    </button>
                </form>
            </div>
            
            <p class="resend-link">
                Didn't receive the code? <a href="forgot_password.php?email=<?php echo urlencode($email); ?>">Resend OTP</a>
                <div class="timer" id="timer">02:00</div>
            </p>
        </div>
    </div>

    <script>
        // Combine OTP digits into one field before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const otp1 = document.querySelector('input[name="otp1"]').value;
            const otp2 = document.querySelector('input[name="otp2"]').value;
            const otp3 = document.querySelector('input[name="otp3"]').value;
            const otp4 = document.querySelector('input[name="otp4"]').value;
            const otp5 = document.querySelector('input[name="otp5"]').value;
            const otp6 = document.querySelector('input[name="otp6"]').value;
            
            document.getElementById('full-otp').value = otp1 + otp2 + otp3 + otp4 + otp5 + otp6;
        });
        
        // Auto move to next OTP field
        function moveToNext(current, nextFieldId) {
            if (current.value.length >= current.maxLength) {
                const nextField = document.getElementsByName(nextFieldId)[0];
                if (nextField) {
                    nextField.focus();
                } else {
                    document.getElementById('btn-verify').focus();
                }
            }
            
            // Auto-backspace on empty field
            if (current.value.length === 0 && current.previousElementSibling) {
                current.previousElementSibling.focus();
            }
        }
        
        // Countdown timer
        function startTimer(duration, display) {
            let timer = duration, minutes, seconds;
            const interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);
                
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                
                display.textContent = minutes + ":" + seconds;
                
                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "00:00";
                    document.querySelector('.resend-link a').style.pointerEvents = 'auto';
                    display.style.color = 'var(--danger)';
                }
            }, 1000);
        }
        
        window.onload = function () {
            const twoMinutes = 120; // 2 minutes in seconds
            const display = document.querySelector('#timer');
            startTimer(twoMinutes, display);
            
            // Disable resend link initially
            document.querySelector('.resend-link a').style.pointerEvents = 'none';
        };
    </script>
</body>
</html>