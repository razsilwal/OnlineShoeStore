<?php

include '../components/connect.php';
include '../components/hashing.php'; // Include your custom hashing

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
   exit;
}

// Fetch current admin profile
$fetch_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$fetch_profile->execute([$admin_id]);
$fetch_profile = $fetch_profile->fetch(PDO::FETCH_ASSOC);

$messages = [];

if (isset($_POST['submit'])) {

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

   // Update name
   $update_profile_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
   $update_profile_name->execute([$name, $admin_id]);
   $messages[] = 'Username updated successfully!';

   $empty_pass = custom_hash(''); // Use custom hashing for empty password
   $prev_pass = $_POST['prev_pass'];
   $old_pass = filter_var(custom_hash($_POST['old_pass']), FILTER_SANITIZE_STRING); // Use custom hashing
   $new_pass = filter_var(custom_hash($_POST['new_pass']), FILTER_SANITIZE_STRING); // Use custom hashing
   $confirm_pass = filter_var(custom_hash($_POST['confirm_pass']), FILTER_SANITIZE_STRING); // Use custom hashing

   if ($_POST['old_pass'] != '') {
      if ($old_pass != $prev_pass) {
         $messages[] = 'Old password not matched!';
      } elseif ($_POST['new_pass'] == '') {
         $messages[] = 'Please enter a new password!';
      } elseif ($new_pass != $confirm_pass) {
         $messages[] = 'Confirm password not matched!';
      } else {
         $update_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
         $update_pass->execute([$confirm_pass, $admin_id]);
         $messages[] = 'Password updated successfully!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Update Profile | Admin</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Google Font -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <style>
      :root {
         --primary: #667eea;
         --primary-dark: #5a6fd8;
         --secondary: #764ba2;
         --success: #27ae60;
         --error: #e74c3c;
         --warning: #f39c12;
         --dark: #2c3e50;
         --light: #f8f9fa;
         --text: #333;
         --text-light: #6c757d;
      }

      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         font-family: 'Poppins', sans-serif;
         background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
         min-height: 100vh;
         padding: 20px;
      }

      /* Form Container */
      .form-container {
         max-width: 500px;
         margin: 80px auto 40px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(10px);
         padding: 3rem 2.5rem;
         border-radius: 20px;
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .form-container h3 {
         text-align: center;
         color: var(--dark);
         font-size: 2.2rem;
         margin-bottom: 2rem;
         font-weight: 700;
         background: linear-gradient(135deg, var(--primary), var(--secondary));
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      /* Messages */
      .message {
         padding: 1rem 1.5rem;
         margin: 1rem 0;
         border-radius: 10px;
         font-weight: 500;
         display: flex;
         align-items: center;
         gap: 0.8rem;
         animation: slideIn 0.3s ease;
      }

      .message.success {
         background: rgba(39, 174, 96, 0.1);
         border-left: 4px solid var(--success);
         color: var(--success);
      }

      .message.error {
         background: rgba(231, 76, 60, 0.1);
         border-left: 4px solid var(--error);
         color: var(--error);
      }

      .message.warning {
         background: rgba(243, 156, 18, 0.1);
         border-left: 4px solid var(--warning);
         color: var(--warning);
      }

      /* Input Groups */
      .input-group {
         position: relative;
         margin-bottom: 1.5rem;
      }

      .input-group i {
         position: absolute;
         left: 1.2rem;
         top: 50%;
         transform: translateY(-50%);
         color: var(--text-light);
         font-size: 1.1rem;
         z-index: 2;
      }

      .input-group .box {
         width: 100%;
         padding: 1.2rem 1.2rem 1.2rem 3rem;
         border: 2px solid #e0e0e0;
         border-radius: 12px;
         font-size: 1.1rem;
         color: var(--text);
         background: #fff;
         transition: all 0.3s ease;
         font-family: 'Poppins', sans-serif;
      }

      .input-group .box:focus {
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
         outline: none;
         transform: translateY(-2px);
      }

      .input-group .box::placeholder {
         color: #aaa;
      }

      /* Password Toggle */
      .password-toggle {
         position: relative;
         margin-bottom: 1.5rem;
      }

      .password-toggle .box {
         width: 100%;
         padding: 1.2rem 3rem 1.2rem 3rem;
         border: 2px solid #e0e0e0;
         border-radius: 12px;
         font-size: 1.1rem;
         color: var(--text);
         background: #fff;
         transition: all 0.3s ease;
         font-family: 'Poppins', sans-serif;
      }

      .password-toggle .box:focus {
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
         outline: none;
         transform: translateY(-2px);
      }

      .password-toggle .fa-lock {
         position: absolute;
         left: 1.2rem;
         top: 50%;
         transform: translateY(-50%);
         color: var(--text-light);
         font-size: 1.1rem;
      }

      .password-toggle .fa-eye,
      .password-toggle .fa-eye-slash {
         position: absolute;
         right: 1.2rem;
         top: 50%;
         transform: translateY(-50%);
         color: var(--text-light);
         font-size: 1.1rem;
         cursor: pointer;
         transition: color 0.3s ease;
      }

      .password-toggle .fa-eye:hover,
      .password-toggle .fa-eye-slash:hover {
         color: var(--primary);
      }

      /* Submit Button */
      .btn {
         width: 100%;
         padding: 1.2rem;
         margin: 2rem 0 1rem;
         background: linear-gradient(135deg, var(--primary), var(--secondary));
         color: #fff;
         border: none;
         border-radius: 12px;
         font-size: 1.2rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
         font-family: 'Poppins', sans-serif;
         text-transform: uppercase;
         letter-spacing: 1px;
      }

      .btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
      }

      .btn:active {
         transform: translateY(-1px);
      }

      /* Profile Info */
      .profile-info {
         text-align: center;
         margin-bottom: 2rem;
         padding: 1.5rem;
         background: rgba(102, 126, 234, 0.05);
         border-radius: 12px;
         border: 1px solid rgba(102, 126, 234, 0.1);
      }

      .profile-info .admin-id {
         font-size: 0.9rem;
         color: var(--text-light);
         margin-bottom: 0.5rem;
      }

      .profile-info .admin-name {
         font-size: 1.3rem;
         font-weight: 600;
         color: var(--primary);
      }

      /* Password Strength */
      .password-strength {
         margin: 0.5rem 0;
         font-size: 0.9rem;
         color: #666;
      }

      .strength-weak { color: var(--error); }
      .strength-medium { color: var(--warning); }
      .strength-strong { color: var(--success); }

      /* Animations */
      @keyframes slideIn {
         from {
            opacity: 0;
            transform: translateY(-10px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }

      /* Responsive */
      @media (max-width: 768px) {
         .form-container {
            padding: 2.5rem 2rem;
            margin: 60px 1rem 20px;
         }

         .form-container h3 {
            font-size: 1.8rem;
         }
      }

      @media (max-width: 480px) {
         .form-container {
            padding: 2rem 1.5rem;
         }

         .form-container h3 {
            font-size: 1.5rem;
         }

         .input-group .box,
         .password-toggle .box {
            padding: 1rem 1rem 1rem 2.8rem;
            font-size: 1rem;
         }

         .btn {
            padding: 1rem;
            font-size: 1.1rem;
         }
      }
   </style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

   <form action="" method="post" onsubmit="return validateForm();">
      <h3>Update Profile</h3>

      <div class="profile-info">
         <div class="admin-id">Admin ID: <?= $admin_id ?></div>
         <div class="admin-name">Welcome, <?= htmlspecialchars($fetch_profile['name']) ?></div>
      </div>

      <?php
      if (!empty($messages)) {
         foreach ($messages as $msg) {
            $message_class = 'message ';
            if (strpos($msg, 'successfully') !== false) {
               $message_class .= 'success';
            } elseif (strpos($msg, 'not matched') !== false || strpos($msg, 'Please enter') !== false) {
               $message_class .= 'error';
            } else {
               $message_class .= 'warning';
            }
            
            echo '<div class="' . $message_class . '">';
            echo '<i class="fas fa-' . (strpos($msg, 'successfully') !== false ? 'check-circle' : 'exclamation-circle') . '"></i>';
            echo '<span>' . htmlspecialchars($msg) . '</span>';
            echo '</div>';
         }
      }
      ?>

      <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">

      <div class="input-group">
         <i class="fas fa-user"></i>
         <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name']); ?>" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>

      <div class="password-toggle">
         <i class="fas fa-lock"></i>
         <input type="password" name="old_pass" placeholder="Enter old password" maxlength="20" class="box" id="old_pass">
         <i class="fas fa-eye" onclick="toggleVisibility('old_pass', this)"></i>
      </div>

      <div class="password-toggle">
         <i class="fas fa-lock"></i>
         <input type="password" name="new_pass" placeholder="Enter new password" maxlength="20" class="box" id="new_pass" onkeyup="checkPasswordStrength(this.value)">
         <i class="fas fa-eye" onclick="toggleVisibility('new_pass', this)"></i>
      </div>
      <div id="password-strength" class="password-strength"></div>

      <div class="password-toggle">
         <i class="fas fa-lock"></i>
         <input type="password" name="confirm_pass" placeholder="Confirm the password" maxlength="20" class="box" id="confirm_pass" onkeyup="checkPasswordMatch()">
         <i class="fas fa-eye" onclick="toggleVisibility('confirm_pass', this)"></i>
      </div>
      <div id="password-match" class="password-strength"></div>

      <input type="submit" value="Update Profile" class="btn" name="submit">
   </form>

</section>

<script>
// Password toggle
function toggleVisibility(fieldId, icon) {
   const field = document.getElementById(fieldId);
   if (field.type === "password") {
      field.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
   } else {
      field.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
   }
}

// Password strength checker
function checkPasswordStrength(password) {
   const strengthText = document.getElementById('password-strength');
   let strength = 0;
   
   if (password.length >= 6) strength++;
   if (password.match(/[a-z]+/)) strength++;
   if (password.match(/[A-Z]+/)) strength++;
   if (password.match(/[0-9]+/)) strength++;
   if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;
   
   const strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
   const strengthClasses = ['', 'strength-weak', 'strength-weak', 'strength-medium', 'strength-strong', 'strength-strong'];
   
   if (password.length > 0) {
      strengthText.textContent = `Password strength: ${strengthLabels[strength]}`;
      strengthText.className = `password-strength ${strengthClasses[strength]}`;
   } else {
      strengthText.textContent = '';
   }
}

// Password match checker
function checkPasswordMatch() {
   const password = document.querySelector('input[name="new_pass"]').value;
   const confirmPassword = document.querySelector('input[name="confirm_pass"]').value;
   const matchText = document.getElementById('password-match');
   
   if (confirmPassword.length > 0) {
      if (password === confirmPassword) {
         matchText.textContent = '✓ Passwords match';
         matchText.className = 'password-strength strength-strong';
      } else {
         matchText.textContent = '✗ Passwords do not match';
         matchText.className = 'password-strength strength-weak';
      }
   } else {
      matchText.textContent = '';
   }
}

// Form validation
function validateForm() {
   const newPass = document.getElementById("new_pass").value;
   const confirmPass = document.getElementById("confirm_pass").value;
   const oldPass = document.getElementById("old_pass").value;

   // If old password is entered, new password must also be entered
   if (oldPass && !newPass) {
      alert("Please enter a new password if you want to change your password!");
      return false;
   }

   if (newPass !== confirmPass) {
      alert("New password and confirm password do not match!");
      return false;
   }

   if (newPass.length > 0 && newPass.length < 6) {
      alert("New password must be at least 6 characters long!");
      return false;
   }

   return true;
}

// Auto-remove messages after 5 seconds
setTimeout(() => {
   const messages = document.querySelectorAll('.message');
   messages.forEach(msg => {
      msg.style.opacity = '0';
      setTimeout(() => msg.remove(), 300);
   });
}, 5000);
</script>

</body>
</html>