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

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
   :root {
   --primary: #8B5FBF;
   --primary-dark: #6B46C1;
   --primary-light: #9F7AEA;
   --secondary: #06D6A0;
   --accent: #FF6B6B;
   --dark: #1A202C;
   --light: #F7FAFC;
   --gray: #718096;
   --border: #E2E8F0;
   --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
   --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
   box-sizing: border-box;
   margin: 0;
   padding: 0;
   font-family: 'Inter', sans-serif;
}

/* ===== Message Styling ===== */
.message {
   position: fixed;
   top: 20px;
   right: 20px;
   background: linear-gradient(135deg, var(--secondary), #05C78C);
   color: white;
   padding: 16px 24px;
   border-radius: 16px;
   box-shadow: var(--shadow);
   display: flex;
   align-items: center;
   justify-content: space-between;
   max-width: 380px;
   z-index: 9999;
   animation: slideInRight 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
   font-size: 0.95rem;
   font-weight: 500;
   border: 1px solid rgba(255, 255, 255, 0.2);
   backdrop-filter: blur(10px);
}

@keyframes slideInRight {
   from { 
      transform: translateX(100%) scale(0.9); 
      opacity: 0; 
   }
   to { 
      transform: translateX(0) scale(1); 
      opacity: 1; 
   }
}

.message i {
   margin-left: 16px;
   cursor: pointer;
   transition: var(--transition);
   font-size: 1.1rem;
   opacity: 0.8;
   padding: 4px;
   border-radius: 50%;
}

.message i:hover {
   opacity: 1;
   background: rgba(255, 255, 255, 0.2);
   transform: rotate(90deg);
}

/* ===== Modern Header Styling ===== */
.admin-header {
   background: linear-gradient(135deg, var(--dark), #2D3748);
   backdrop-filter: blur(20px);
   border-bottom: 1px solid rgba(255, 255, 255, 0.1);
   position: sticky;
   top: 0;
   z-index: 1000;
   box-shadow: var(--shadow);
   height: 80px;
   display: flex;
   align-items: center;
   width: 100%;
}

.header-container {
   display: flex;
   justify-content: space-between;
   align-items: center;
   width: 100%;
   max-width: 1400px;
   margin: 0 auto;
   padding: 0 30px;
   height: 100%;
}

/* ===== Logo Styling ===== */
.brand {
   display: flex;
   align-items: center;
   gap: 12px;
   text-decoration: none;
   transition: var(--transition);
   padding: 8px 16px;
   border-radius: 12px;
   background: rgba(139, 95, 191, 0.1);
   border: 1px solid rgba(139, 95, 191, 0.2);
}

.brand-logo {
   width: 40px;
   height: 40px;
   background: linear-gradient(135deg, var(--primary), var(--primary-dark));
   border-radius: 10px;
   display: flex;
   align-items: center;
   justify-content: center;
   color: white;
   font-size: 1.2rem;
   font-weight: 700;
   transition: var(--transition);
}

.brand-text {
   display: flex;
   flex-direction: column;
   line-height: 1.2;
}

.brand-name {
   font-size: 1.4rem;
   font-weight: 800;
   background: linear-gradient(135deg, var(--primary-light), white);
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   letter-spacing: -0.5px;
}

.brand-subtitle {
   font-size: 0.75rem;
   color: var(--primary-light);
   font-weight: 500;
   letter-spacing: 0.5px;
}

.brand:hover {
   transform: translateY(-2px);
   background: rgba(139, 95, 191, 0.2);
   box-shadow: 0 8px 25px rgba(139, 95, 191, 0.3);
}

.brand:hover .brand-logo {
   transform: rotate(-10deg) scale(1.1);
}

/* ===== Navigation ===== */
.nav-menu {
   display: flex;
   align-items: center;
   gap: 8px;
   height: 100%;
}

.nav-item {
   position: relative;
   text-decoration: none;
   color: rgba(255, 255, 255, 0.8);
   font-weight: 500;
   padding: 12px 20px;
   border-radius: 12px;
   transition: var(--transition);
   display: flex;
   align-items: center;
   gap: 10px;
   font-size: 0.95rem;
   height: 48px;
}

.nav-item i {
   font-size: 1.1rem;
   transition: var(--transition);
   opacity: 0.7;
}

.nav-item:hover {
   color: white;
   background: rgba(139, 95, 191, 0.15);
   transform: translateY(-2px);
}

.nav-item:hover i {
   opacity: 1;
   transform: scale(1.1);
}

.nav-item.active {
   color: white;
   background: rgba(139, 95, 191, 0.2);
   box-shadow: inset 0 0 0 1px rgba(139, 95, 191, 0.4);
}

.nav-item.active::before {
   content: '';
   position: absolute;
   bottom: -1px;
   left: 50%;
   transform: translateX(-50%);
   width: 4px;
   height: 4px;
   background: var(--secondary);
   border-radius: 50%;
}

/* ===== Header Actions ===== */
.header-actions {
   display: flex;
   align-items: center;
   gap: 16px;
   height: 100%;
}

.action-btn {
   width: 48px;
   height: 48px;
   border-radius: 12px;
   display: flex;
   align-items: center;
   justify-content: center;
   background: rgba(255, 255, 255, 0.05);
   border: 1px solid rgba(255, 255, 255, 0.1);
   color: rgba(255, 255, 255, 0.8);
   cursor: pointer;
   transition: var(--transition);
   position: relative;
   font-size: 1.2rem;
}

.action-btn:hover {
   background: rgba(139, 95, 191, 0.2);
   color: white;
   transform: translateY(-2px);
   border-color: rgba(139, 95, 191, 0.4);
}

.action-btn.notification::after {
   content: '';
   position: absolute;
   top: 10px;
   right: 10px;
   width: 8px;
   height: 8px;
   background: var(--accent);
   border-radius: 50%;
   border: 2px solid var(--dark);
}

/* ===== Profile Dropdown ===== */
.profile-dropdown {
   position: relative;
   height: 100%;
   display: flex;
   align-items: center;
}

.profile-trigger {
   display: flex;
   align-items: center;
   gap: 12px;
   padding: 8px 16px;
   border-radius: 12px;
   background: rgba(255, 255, 255, 0.05);
   border: 1px solid rgba(255, 255, 255, 0.1);
   cursor: pointer;
   transition: var(--transition);
   height: 48px;
}

.profile-trigger:hover {
   background: rgba(139, 95, 191, 0.2);
   border-color: rgba(139, 95, 191, 0.4);
}

.profile-avatar {
   width: 36px;
   height: 36px;
   border-radius: 10px;
   background: linear-gradient(135deg, var(--primary), var(--primary-dark));
   display: flex;
   align-items: center;
   justify-content: center;
   color: white;
   font-weight: 700;
   font-size: 0.9rem;
   transition: var(--transition);
}

.profile-info {
   display: flex;
   flex-direction: column;
   line-height: 1.2;
}

.profile-name {
   font-size: 0.9rem;
   font-weight: 600;
   color: white;
}

.profile-role {
   font-size: 0.75rem;
   color: var(--primary-light);
   font-weight: 500;
}

.profile-arrow {
   color: rgba(255, 255, 255, 0.6);
   transition: var(--transition);
   font-size: 0.9rem;
}

.profile-trigger:hover .profile-arrow {
   color: white;
   transform: translateY(1px);
}

.dropdown-menu {
   position: absolute;
   top: 100%;
   right: 0;
   margin-top: 8px;
   background: white;
   border-radius: 16px;
   box-shadow: var(--shadow);
   padding: 20px;
   width: 280px;
   opacity: 0;
   visibility: hidden;
   transform: translateY(-10px);
   transition: var(--transition);
   z-index: 1001;
   border: 1px solid var(--border);
}

.dropdown-menu.active {
   opacity: 1;
   visibility: visible;
   transform: translateY(0);
}

.dropdown-header {
   display: flex;
   align-items: center;
   gap: 12px;
   padding-bottom: 16px;
   margin-bottom: 16px;
   border-bottom: 1px solid var(--border);
}

.dropdown-avatar {
   width: 48px;
   height: 48px;
   border-radius: 12px;
   background: linear-gradient(135deg, var(--primary), var(--primary-dark));
   display: flex;
   align-items: center;
   justify-content: center;
   color: white;
   font-weight: 700;
   font-size: 1.1rem;
}

.dropdown-info h4 {
   color: var(--dark);
   font-size: 1rem;
   font-weight: 600;
   margin-bottom: 4px;
}

.dropdown-info p {
   color: var(--gray);
   font-size: 0.85rem;
}

.dropdown-links {
   display: flex;
   flex-direction: column;
   gap: 8px;
   margin-bottom: 16px;
}

.dropdown-link {
   display: flex;
   align-items: center;
   gap: 12px;
   padding: 12px 16px;
   border-radius: 10px;
   text-decoration: none;
   color: var(--dark);
   transition: var(--transition);
   font-weight: 500;
   font-size: 0.9rem;
}

.dropdown-link i {
   color: var(--primary);
   font-size: 1rem;
   width: 20px;
   transition: var(--transition);
}

.dropdown-link:hover {
   background: var(--light);
   color: var(--primary-dark);
   transform: translateX(4px);
}

.dropdown-link:hover i {
   transform: scale(1.1);
}

.dropdown-actions {
   display: flex;
   gap: 12px;
   padding-top: 16px;
   border-top: 1px solid var(--border);
}

.action-button {
   flex: 1;
   padding: 10px 16px;
   border-radius: 10px;
   text-decoration: none;
   font-weight: 600;
   font-size: 0.85rem;
   text-align: center;
   transition: var(--transition);
   display: flex;
   align-items: center;
   justify-content: center;
   gap: 6px;
}

.action-button.primary {
   background: linear-gradient(135deg, var(--primary), var(--primary-dark));
   color: white;
}

.action-button.primary:hover {
   transform: translateY(-2px);
   box-shadow: 0 8px 20px rgba(139, 95, 191, 0.4);
}

.action-button.danger {
   background: rgba(255, 107, 107, 0.1);
   color: var(--accent);
   border: 1px solid rgba(255, 107, 107, 0.2);
}

.action-button.danger:hover {
   background: var(--accent);
   color: white;
   transform: translateY(-2px);
}

/* ===== Mobile Menu ===== */
.mobile-toggle {
   display: none;
   background: none;
   border: none;
   color: rgba(255, 255, 255, 0.8);
   font-size: 1.4rem;
   cursor: pointer;
   transition: var(--transition);
   padding: 8px;
   border-radius: 10px;
}

.mobile-toggle:hover {
   color: white;
   background: rgba(139, 95, 191, 0.2);
}

/* ===== Responsive Design ===== */
@media (max-width: 1200px) {
   .header-container {
      padding: 0 20px;
   }
   
   .nav-item {
      padding: 12px 16px;
      font-size: 0.9rem;
   }
}

@media (max-width: 992px) {
   .admin-header {
      height: 70px;
   }
   
   .nav-menu {
      position: fixed;
      top: 70px;
      left: 0;
      width: 100%;
      background: white;
      flex-direction: column;
      align-items: stretch;
      padding: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transform: translateY(-100%);
      opacity: 0;
      transition: var(--transition);
      z-index: 999;
      gap: 8px;
   }
   
   .nav-menu.active {
      transform: translateY(0);
      opacity: 1;
   }
   
   .nav-item {
      color: var(--dark);
      padding: 16px 20px;
      border-radius: 12px;
      height: auto;
      justify-content: flex-start;
   }
   
   .nav-item:hover {
      background: var(--light);
      color: var(--primary-dark);
   }
   
   .nav-item.active {
      background: var(--light);
      color: var(--primary-dark);
      box-shadow: none;
   }
   
   .nav-item.active::before {
      display: none;
   }
   
   .mobile-toggle {
      display: block;
   }
   
   .brand-text {
      display: none;
   }
   
   .profile-info {
      display: none;
   }
   
   .profile-trigger {
      padding: 8px;
   }
}

@media (max-width: 768px) {
   .header-container {
      padding: 0 16px;
   }
   
   .dropdown-menu {
      width: 260px;
      right: -10px;
   }
}

/* ===== Animation Keyframes ===== */
@keyframes fadeInUp {
   from {
      opacity: 0;
      transform: translateY(20px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}

@keyframes pulse {
   0%, 100% {
      transform: scale(1);
   }
   50% {
      transform: scale(1.05);
   }
}
</style>

<header class="admin-header">
   <div class="header-container">
      <!-- Brand Logo -->
      <a href="dashboard.php" class="brand">
         <div class="brand-logo">K</div>
         <div class="brand-text">
            <div class="brand-name">Kickster</div>
            <div class="brand-subtitle">ADMIN PANEL</div>
         </div>
      </a>

      <!-- Navigation Menu -->
      <nav class="nav-menu" id="navMenu">
         <a href="dashboard.php" class="nav-item">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
         </a>
         <a href="products.php" class="nav-item">
            <i class="fas fa-cube"></i>
            <span>Products</span>
         </a>
         <a href="placed_orders.php" class="nav-item">
            <i class="fas fa-shopping-bag"></i>
            <span>Orders</span>
         </a>
         <a href="admin_accounts.php" class="nav-item">
            <i class="fas fa-user-shield"></i>
            <span>Admins</span>
         </a>
         <a href="users_accounts.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Users</span>
         </a>
      </nav>

      <!-- Header Actions -->
      <div class="header-actions">
         <!-- Mobile Toggle -->
         <button class="mobile-toggle" id="mobileToggle">
            <i class="fas fa-bars"></i>
         </button>

         
         <!-- Profile Dropdown -->
         <div class="profile-dropdown">
            <div class="profile-trigger" id="profileTrigger">
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
               <div class="profile-avatar"><?= $initials ?></div>
               <div class="profile-info">
                  <div class="profile-name"><?= $fetch_profile['name'] ?></div>
                  <div class="profile-role">Administrator</div>
               </div>
               <i class="fas fa-chevron-down profile-arrow"></i>
            </div>

            <div class="dropdown-menu" id="dropdownMenu">
               <div class="dropdown-header">
                  <div class="dropdown-avatar"><?= $initials ?></div>
                  <div class="dropdown-info">
                     <h4><?= $fetch_profile['name'] ?></h4>
                     <p>Administrator</p>
                  </div>
               </div>

               <div class="dropdown-links">
                  <a href="update_profile.php" class="dropdown-link">
                     <i class="fas fa-user-edit"></i>
                     <span>Update Profile</span>
                  </a>
                  <a href="register_admin.php" class="dropdown-link">
                     <i class="fas fa-user-plus"></i>
                     <span>Register Admin</span>
                  </a>
               </div>

               <div class="dropdown-actions">
                  <a href="admin_login.php" class="action-button primary">
                     <i class="fas fa-sign-in-alt"></i>
                     <span>Login</span>
                  </a>
                  <a href="../components/admin_logout.php" 
                     class="action-button danger"
                     onclick="return confirm('Are you sure you want to logout?');">
                     <i class="fas fa-sign-out-alt"></i>
                     <span>Logout</span>
                  </a>
               </div>
            </div>
         </div>
      </div>
   </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
   const mobileToggle = document.getElementById('mobileToggle');
   const navMenu = document.getElementById('navMenu');
   const profileTrigger = document.getElementById('profileTrigger');
   const dropdownMenu = document.getElementById('dropdownMenu');

   // Mobile menu toggle
   mobileToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
      mobileToggle.querySelector('i').classList.toggle('fa-bars');
      mobileToggle.querySelector('i').classList.toggle('fa-times');
   });

   // Profile dropdown toggle
   profileTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle('active');
   });

   // Close dropdowns when clicking outside
   document.addEventListener('click', (e) => {
      if (!e.target.closest('.profile-dropdown')) {
         dropdownMenu.classList.remove('active');
      }
      if (!e.target.closest('.nav-menu') && !e.target.closest('.mobile-toggle')) {
         navMenu.classList.remove('active');
         mobileToggle.querySelector('i').classList.remove('fa-times');
         mobileToggle.querySelector('i').classList.add('fa-bars');
      }
   });

   // Auto-remove messages
   const messages = document.querySelectorAll('.message');
   messages.forEach(message => {
      setTimeout(() => {
         message.style.animation = 'slideInRight 0.5s ease-out reverse forwards';
         setTimeout(() => message.remove(), 500);
      }, 5000);
   });

   // Add active class to current page
   const currentPage = window.location.pathname.split('/').pop();
   const navItems = document.querySelectorAll('.nav-item');
   navItems.forEach(item => {
      if (item.getAttribute('href') === currentPage) {
         item.classList.add('active');
      }
   });
});
</script>