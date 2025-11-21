<?php
include '../components/connect.php';
include '../components/hashing.php';

session_start();

// Debug: Check if hashing.php is loaded
if (!function_exists('custom_hash')) {
    die("Error: custom_hash function not found. Check hashing.php file.");
}

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = $_POST['pass'];

   // ======= VISIBLE DEBUG CODE =======
   echo "<div style='background: #fff3cd; padding: 15px; margin: 10px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
   echo "<h3>🔍 DEBUG INFORMATION</h3>";
   echo "<strong>Username entered:</strong> " . htmlspecialchars($name) . "<br>";
   echo "<strong>Password entered:</strong> " . htmlspecialchars($pass) . "<br>";
   
   $hashed_pass = custom_hash($pass);
   echo "<strong>Hashed password:</strong> " . $hashed_pass . "<br>";
   
   // Check if admin exists with that username
   $check_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
   $check_admin->execute([$name]);
   
   if($check_admin->rowCount() > 0){
      $admin = $check_admin->fetch(PDO::FETCH_ASSOC);
      echo "<strong>Admin found in database:</strong> YES<br>";
      echo "<strong>Database password hash:</strong> " . $admin['password'] . "<br>";
      echo "<strong>Password match:</strong> " . ($admin['password'] === $hashed_pass ? '✅ YES' : '❌ NO') . "<br>";
      echo "<strong>Admin ID:</strong> " . $admin['id'] . "<br>";
   } else {
      echo "<strong>Admin found in database:</strong> ❌ NO<br>";
      echo "No admin found with username: " . htmlspecialchars($name) . "<br>";
   }
   echo "</div>";
   // ======= END DEBUG CODE =======

   // Use your custom hashing function
   $hashed_pass = custom_hash($pass);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
   $select_admin->execute([$name, $hashed_pass]);
   $row = $select_admin->fetch(PDO::FETCH_ASSOC);

   if($select_admin->rowCount() > 0){
      $_SESSION['admin_id'] = $row['id'];
      echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
      echo "✅ Login successful! Redirecting to dashboard...";
      echo "</div>";
      header('location:dashboard.php');
      exit();
   }else{
      $message[] = 'Incorrect username or password!';
   }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Login | Kickster</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <style>
      :root {
         --primary: #667eea;
         --primary-dark: #5a6fd8;
         --secondary: #764ba2;
         --success: #27ae60;
         --error: #e74c3c;
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
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 20px;
      }

      /* Messages */
      .message {
         position: fixed;
         top: 20px;
         left: 50%;
         transform: translateX(-50%);
         z-index: 1000;
         padding: 15px 25px;
         margin-bottom: 15px;
         border-radius: 10px;
         display: flex;
         justify-content: space-between;
         align-items: center;
         animation: slideDown 0.3s ease;
         box-shadow: 0 5px 15px rgba(0,0,0,0.2);
         max-width: 400px;
         width: 90%;
      }

      .message.error {
         background: rgba(231, 76, 60, 0.95);
         color: white;
         border-left: 4px solid #c0392b;
      }

      .message i {
         cursor: pointer;
         opacity: 0.8;
         transition: opacity 0.3s;
      }

      .message i:hover {
         opacity: 1;
      }

      /* Form Container */
      .form-container {
         width: 100%;
         max-width: 450px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(10px);
         padding: 3rem 2.5rem;
         border-radius: 20px;
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
         opacity: 0;
         transform: translateY(20px);
         transition: all 0.4s ease-out;
      }

      .form-container.loaded {
         opacity: 1;
         transform: translateY(0);
      }

      .form-container h3 {
         text-align: center;
         color: var(--dark);
         font-size: 2.2rem;
         margin-bottom: 2.5rem;
         font-weight: 700;
         background: linear-gradient(135deg, var(--primary), var(--secondary));
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
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

      /* Additional Links */
      .form-footer {
         text-align: center;
         margin-top: 2rem;
         padding-top: 1.5rem;
         border-top: 1px solid #e0e0e0;
      }

      .form-footer a {
         color: var(--primary);
         text-decoration: none;
         font-weight: 500;
         transition: color 0.3s ease;
      }

      .form-footer a:hover {
         color: var(--secondary);
         text-decoration: underline;
      }

      /* Logo/Brand */
      .brand {
         text-align: center;
         margin-bottom: 1.5rem;
      }

      .brand h1 {
         font-size: 2.5rem;
         font-weight: 700;
         background: linear-gradient(135deg, var(--primary), var(--secondary));
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin-bottom: 0.5rem;
      }

      .brand p {
         color: var(--text-light);
         font-size: 1rem;
      }

      /* Animations */
      @keyframes slideDown {
         from {
            opacity: 0;
            transform: translate(-50%, -20px);
         }
         to {
            opacity: 1;
            transform: translate(-50%, 0);
         }
      }

      /* Responsive */
      @media (max-width: 768px) {
         body {
            padding: 15px;
         }

         .form-container {
            padding: 2.5rem 2rem;
            margin: 1rem;
         }

         .form-container h3 {
            font-size: 1.8rem;
         }

         .brand h1 {
            font-size: 2rem;
         }
      }

      @media (max-width: 480px) {
         .form-container {
            padding: 2rem 1.5rem;
         }

         .form-container h3 {
            font-size: 1.5rem;
         }

         .input-group .box {
            padding: 1rem 1rem 1rem 2.8rem;
            font-size: 1rem;
         }

         .btn {
            padding: 1rem;
            font-size: 1.1rem;
         }

         .brand h1 {
            font-size: 1.8rem;
         }
      }
   </style>

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message error">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<section class="form-container">
   <div class="brand">
      <h1>Kickster</h1>
      <p>Admin Panel</p>
   </div>

   <form action="" method="post">
      <h3>Admin Login</h3>
      
      <div class="input-group">
         <i class="fas fa-user"></i>
         <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>
      
      <div class="input-group">
         <i class="fas fa-lock"></i>
         <input type="password" name="pass" required placeholder="Enter your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>
      
      <input type="submit" value="Login Now" class="btn" name="submit" id="submitBtn">

      <div class="form-footer">
         <a href="./dashboard.php"><i class="fas fa-home"></i> Back to Website</a>
      </div>
   </form>
</section>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      const formContainer = document.querySelector('.form-container');
      setTimeout(() => {
         formContainer.classList.add('loaded');
      }, 100);

      setTimeout(() => {
         const messages = document.querySelectorAll('.message');
         messages.forEach(msg => {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 300);
         });
      }, 5000);
   });
</script>
   
</body>
</html>