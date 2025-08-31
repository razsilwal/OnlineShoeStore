<?php
include '../components/connect.php';

session_start();

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
   $select_admin->execute([$name, $pass]);
   $row = $select_admin->fetch(PDO::FETCH_ASSOC);

   if($select_admin->rowCount() > 0){
      $_SESSION['admin_id'] = $row['id'];
      header('location:dashboard.php');
   }else{
      $message[] = 'incorrect username or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Login</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_login.css">

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<section class="form-container">
  

   <form action="" method="post">
      <h3><b>Admin Login</b></h3>
      
      
      <div class="input-group">
         <i class="fas fa-user"></i>
         
         <input type="text" name="name" required placeholder="Enter the username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>
      
      <div class="input-group">
         <i class="fas fa-lock"></i>
         
         <input type="password" name="pass" required placeholder="Enter the password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>
      
      <input type="submit" value="Login Now" class="btn" name="submit">
   </form>
</section>

<script>
   // Simple animation for the login form
   document.addEventListener('DOMContentLoaded', function() {
      const formContainer = document.querySelector('.form-container');
      formContainer.style.opacity = '0';
      formContainer.style.transform = 'translateY(20px)';
      formContainer.style.transition = 'all 0.4s ease-out';
      
      setTimeout(() => {
         formContainer.style.opacity = '1';
         formContainer.style.transform = 'translateY(0)';
      }, 100);
   });
</script>
   
</body>
</html>