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

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/admin_navbar.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">



<header class="navbar">
   <section class="header-flex">

      <a href="dashboard.php" class="dashboard-logo">
         <i class="admin class"></i> Admin
      </a>

      <nav class="dashboard-nav" id="dashboardNav">
         <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
         <a href="products.php"><i class="fas fa-product"></i> Products</a>
         <a href="placed_orders.php"><i class="fas-order"></i> Orders</a>
         
         <a href="users_accounts.php"><i class="fas fa-users"></i> Users</a>
         
      </nav>

      <div class="dashboard-icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="dashboard-profile" id="dashboardProfile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            
            $name_parts = explode(' ', $fetch_profile['name']);
            $initials = '';
            foreach($name_parts as $part) {
               $initials .= strtoupper(substr($part, 0, 1));
            }
         ?>
         <div class="profile-header">
            <div class="profile-img"><?= $initials ?></div>
            <div class="profile-info">
               <h4><?= $fetch_profile['name'] ?></h4>
               <p>Administrator</p>
            </div>
         </div>
         
         <div class="profile-links">
            <a href="update_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a>
            <a href="register_admin.php"><i class="fas fa-user-plus"></i> Register Admin</a>
         </div>
         
         <div class="flex-btn">
            <a href="admin_login.php" class="dashboard-btn btn-primary">
               <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="../components/admin_logout.php" 
               class="dashboard-btn btn-danger" 
               onclick="return confirm('Are you sure you want to logout?');">
               <i class="fas fa-sign-out-alt"></i> Logout
            </a>
         </div>
      </div>

   </section>
</header>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      const userBtn = document.getElementById('user-btn');
      const profile = document.getElementById('dashboardProfile');
      const menuBtn = document.getElementById('menu-btn');
      const navbar = document.getElementById('dashboardNav');
      
      // Toggle profile dropdown
      userBtn.addEventListener('click', () => {
         profile.classList.toggle('active');
      });
      
      // Toggle mobile menu
      menuBtn.addEventListener('click', () => {
         navbar.classList.toggle('active');
         menuBtn.classList.toggle('fa-times');
      });
      
      // Close dropdowns when clicking outside
      document.addEventListener('click', (e) => {
         if (!e.target.closest('#dashboardProfile') && !e.target.matches('#user-btn')) {
            profile.classList.remove('active');
         }
         if (!e.target.closest('#dashboardNav') && !e.target.matches('#menu-btn')) {
            navbar.classList.remove('active');
            menuBtn.classList.remove('fa-times');
         }
      });
      
      // Auto-remove messages after 5 seconds
      const messages = document.querySelectorAll('.message');
      messages.forEach(message => {
         setTimeout(() => {
            message.style.animation = 'slideOut 0.5s ease-out forwards';
            setTimeout(() => message.remove(), 500);
         }, 5000);
      });

      // Force consistent header height
      const header = document.querySelector('.dashboard-header');
      if(header) {
         header.style.height = '70px';
         header.style.minHeight = '70px';
      }
   });
</script>