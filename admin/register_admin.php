<?php

include '../components/connect.php';
include '../components/hashing.php'; // Add this line

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}


if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = $_POST['pass'];
   $cpass = $_POST['cpass'];

   // Check if username already exists
   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
   $select_admin->execute([$name]);

   if($select_admin->rowCount() > 0){
      $message[] = 'Username already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'Confirm password does not match!';
      }else{
         // Use your custom hashing function
         $hashed_password = custom_hash($pass);
         $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password) VALUES(?,?)");
         $insert_admin->execute([$name, $hashed_password]);
         $message[] = 'New admin registered successfully!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register Admin | Admin Panel</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      .form-container {
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 2rem;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }

      .form-container form {
         width: 100%;
         max-width: 450px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(10px);
         padding: 3rem 2.5rem;
         border-radius: 20px;
         box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .form-container form h3 {
         text-align: center;
         color: #333;
         font-size: 2.2rem;
         margin-bottom: 2rem;
         font-weight: 700;
         background: linear-gradient(135deg, #667eea, #764ba2);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      .form-container form .box {
         width: 100%;
         padding: 1.2rem 1.5rem;
         margin: 1rem 0;
         border: 2px solid #e0e0e0;
         border-radius: 12px;
         font-size: 1.1rem;
         color: #333;
         background: #fff;
         transition: all 0.3s ease;
         font-family: 'Poppins', sans-serif;
      }

      .form-container form .box:focus {
         border-color: #667eea;
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
         outline: none;
         transform: translateY(-2px);
      }

      .form-container form .box::placeholder {
         color: #999;
      }

      .form-container form .btn {
         width: 100%;
         padding: 1.2rem;
         margin: 1.5rem 0 1rem;
         background: linear-gradient(135deg, #667eea, #764ba2);
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

      .form-container form .btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
      }

      .password-strength {
         margin: 0.5rem 0;
         font-size: 0.9rem;
         color: #666;
      }

      .strength-weak { color: #e74c3c; }
      .strength-medium { color: #f39c12; }
      .strength-strong { color: #27ae60; }

      .form-footer {
         text-align: center;
         margin-top: 2rem;
         padding-top: 1.5rem;
         border-top: 1px solid #e0e0e0;
      }

      .form-footer a {
         color: #667eea;
         text-decoration: none;
         font-weight: 500;
         transition: color 0.3s ease;
      }

      .form-footer a:hover {
         color: #764ba2;
         text-decoration: underline;
      }

      .input-group {
         position: relative;
         margin: 1rem 0;
      }

      .input-group i {
         position: absolute;
         left: 1.2rem;
         top: 50%;
         transform: translateY(-50%);
         color: #999;
         font-size: 1.1rem;
      }

      .input-group .box {
         padding-left: 3rem;
      }

      /* Message styling */
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
         border-left: 4px solid #27ae60;
         color: #27ae60;
      }

      .message.error {
         background: rgba(231, 76, 60, 0.1);
         border-left: 4px solid #e74c3c;
         color: #e74c3c;
      }

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
            padding: 1rem;
         }

         .form-container form {
            padding: 2rem 1.5rem;
            margin: 1rem;
         }

         .form-container form h3 {
            font-size: 1.8rem;
         }
      }

      @media (max-width: 480px) {
         .form-container form {
            padding: 1.5rem 1rem;
         }

         .form-container form h3 {
            font-size: 1.5rem;
         }

         .form-container form .box {
            padding: 1rem 1.2rem;
            font-size: 1rem;
         }
      }
   </style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Register New Admin</h3>
      
      <?php
      if(isset($message)){
         foreach($message as $message){
            echo '
            <div class="message '. (strpos($message, 'successfully') !== false ? 'success' : 'error') .'">
               <i class="fas fa-'. (strpos($message, 'successfully') !== false ? 'check-circle' : 'exclamation-circle') .'"></i>
               <span>'.$message.'</span>
            </div>
            ';
         }
      }
      ?>

      <div class="input-group">
         <i class="fas fa-user"></i>
         <input type="text" name="name" required placeholder="Enter username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>

      <div class="input-group">
         <i class="fas fa-lock"></i>
         <input type="password" name="pass" required placeholder="Enter password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" onkeyup="checkPasswordStrength(this.value)">
      </div>
      <div id="password-strength" class="password-strength"></div>

      <div class="input-group">
         <i class="fas fa-lock"></i>
         <input type="password" name="cpass" required placeholder="Confirm password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" onkeyup="checkPasswordMatch()">
      </div>
      <div id="password-match" class="password-strength"></div>

      <input type="submit" value="Register Admin" class="btn" name="submit">

      <div class="form-footer">
         <a href="admin_accounts.php"><i class="fas fa-arrow-left"></i> Back to Admin Accounts</a>
      </div>
   </form>

</section>

<script>
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
         strengthText.textContent = `Strength: ${strengthLabels[strength]}`;
         strengthText.className = `password-strength ${strengthClasses[strength]}`;
      } else {
         strengthText.textContent = '';
      }
   }

   function checkPasswordMatch() {
      const password = document.querySelector('input[name="pass"]').value;
      const confirmPassword = document.querySelector('input[name="cpass"]').value;
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