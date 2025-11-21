<?php
session_start();

if(isset($_SESSION['success'])){
    $success = $_SESSION['success'];
    unset($_SESSION['success']); // remove message so it shows only once
}
include 'components/connect.php';

// Include the same custom_hash function from your registration page
// Include central hashing function
include 'components/hashing.php';

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = custom_hash($_POST['pass']); // Use custom_hash instead of sha1
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
   $select_user->execute([$email, $pass]);
   $row = $select_user->fetch(PDO::FETCH_ASSOC);

   if($select_user->rowCount() > 0){
      $_SESSION['user_id'] = $row['id'];
      header('location:home.php');
      exit();
   }else{
      $message[] = 'Incorrect username or password!';

      
   // DEBUG CODE - ADD THIS
   echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border-radius: 5px;'>";
   echo "<strong>DEBUG INFO:</strong><br>";
   echo "Email: " . $email . "<br>";
   echo "Input Password: " . $_POST['pass'] . "<br>";
   echo "Hashed Input: " . custom_hash($_POST['pass']) . "<br>";
   echo "Hash Length: " . strlen(custom_hash($_POST['pass'])) . "<br>";
   
   // Check what's actually in database
   $debug_stmt = $conn->prepare("SELECT password, LENGTH(password) as pass_length FROM users WHERE email = ?");
   $debug_stmt->execute([$email]);
   $debug_user = $debug_stmt->fetch(PDO::FETCH_ASSOC);
   
   if($debug_user){
       echo "DB Password: " . $debug_user['password'] . "<br>";
       echo "DB Password Length: " . $debug_user['pass_length'] . "<br>";
       echo "Hashes Match: " . (custom_hash($_POST['pass']) === $debug_user['password'] ? 'YES' : 'NO') . "<br>";
   } else {
       echo "User not found in database<br>";
   }
   echo "</div>";
}
   }
?>

<!-- The rest of your HTML remains exactly the same -->
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kickster - Login Page</title>
   
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
    max-width: 420px;
    color: #fff;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

/* ===== FORM HEADER ===== */
.form-header h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

/* ===== MESSAGES ===== */
.message {
    background: rgba(255, 99, 71, 0.15);
    border: 1px solid rgba(255, 99, 71, 0.5);
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 8px;
    font-size: 14px;
}

.message.success {
    background: rgba(46, 204, 113, 0.15);
    border-color: rgba(46, 204, 113, 0.5);
}

/* ===== FORM GROUPS ===== */
.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
}

/* ===== INPUT WITH ICON ===== */
.input-with-icon {
    position: relative;
}

.input-with-icon i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #bbb;
}

/* ===== INPUT FIELDS ===== */
.form-control {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border-radius: 8px;
    border: none;
    outline: none;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 14px;
    transition: 0.3s;
}

.form-control::placeholder {
    color: rgba(255,255,255,0.6);
}

.form-control:focus {
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 0 5px rgba(255,255,255,0.5);
}

/* ===== PASSWORD TOGGLE ===== */
.password-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    cursor: pointer;
    color: #bbb;
    font-size: 14px;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #fff;
}

/* ===== BUTTON ===== */
.btn {
    width: 100%;
    padding: 12px;
    background: #2ecc71;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.3s ease;
}

.btn:hover {
    background: #27ae60;
}

/* ===== FOOTER TEXT ===== */
.form-footer {
    margin-top: 15px;
    text-align: center;
    font-size: 14px;
}

.form-footer a {
    color: #2ecc71;
    text-decoration: none;
    font-weight: 500;
}

.form-footer a:hover {
    text-decoration: underline;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
    .form-container {
        padding: 20px 15px;
    }
}

   


</style>
    
</header>

<main>
    <section class="form-container">
        <div class="form-header">
            <h2>Welcome back!</h2>
            <p>Sign in to access your account</p>
        </div>
        
        <?php
        if(isset($message)){
            foreach($message as $msg){
                echo '<div class="message">'.$msg.'</div>';
            }
        }
        ?>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" class="form-control" oninput="this.value = this.value.replace(/\s/g, '')">
                </div>
            </div>
            
            <div class="form-group">
    <label for="password">Password</label>
    <div class="input-with-icon" style="position: relative;">
        <i class="fas fa-lock"></i>
        <input type="password" id="password" name="pass" required placeholder="Enter your password" class="form-control" oninput="this.value = this.value.replace(/\s/g, '')">
        <button type="button" class="password-toggle" id="togglePassword">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    <div class="form-footer" style="text-align: right; margin-top: 0.5rem;">
        <a href="forget_password.php">Forgot password?</a>
    </div>
</div>

            
            <button type="submit" class="btn" name="submit">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </button>
            
            <div class="divider">
                <span>or continue with</span>
            </div>
            
            <!-- <div class="social-login">
                <button type="button" class="social-btn google">
                    <i class="fab fa-google"></i>
                    <span>Google</span>
                </button>
                <button type="button" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                    <span>Facebook</span>
                </button>
                <button type="button" class="social-btn apple">
                    <i class="fab fa-apple"></i>
                    <span>Apple</span>
                </button>
            </div> -->
            
            <div class="form-footer">
                Don't have an account? <a href="user_register.php">Sign up</a>
            </div>
        </form>
    </section>
</main>



<script src="js/script.js"></script>

<script>
   // Password toggle functionality
   const togglePassword = document.querySelector('#togglePassword');
   const password = document.querySelector('#password');
   
   togglePassword.addEventListener('click', function (e) {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
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

   // Add animation to social buttons on hover
   const socialButtons = document.querySelectorAll('.social-btn');
   socialButtons.forEach(button => {
      button.addEventListener('mouseenter', () => {
         const icon = button.querySelector('i');
         icon.style.transform = 'scale(1.2)';
         setTimeout(() => {
            icon.style.transform = 'scale(1)';
         }, 300);
      });
   });
</script>

</body>
</html>