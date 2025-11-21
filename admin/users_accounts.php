<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
   exit;
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $conn->prepare("DELETE FROM `users` WHERE id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `orders` WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `messages` WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `cart` WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?")->execute([$delete_id]);
   header('location:users_accounts.php');
   exit;
}

// Get user statistics
$total_users = $conn->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
$total_orders = $conn->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
$total_messages = $conn->query("SELECT COUNT(*) FROM `messages`")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Accounts | Admin Panel</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
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
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Inter', sans-serif;
      }

      body {
         background: linear-gradient(135deg, #F7FAFC 0%, #EDF2F7 100%);
         min-height: 100vh;
         color: var(--dark);
      }

      /* Main Content */
      .main-content {
         padding: 30px;
         max-width: 1400px;
         margin: 0 auto;
         margin-top: 80px;
      }

      /* Page Header */
      .page-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 40px;
         padding-bottom: 20px;
         border-bottom: 2px solid var(--border);
      }

      .page-title {
         font-size: 2.5rem;
         font-weight: 700;
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin: 0;
      }

      /* Statistics Cards */
      .stats-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 20px;
         margin-bottom: 40px;
      }

      .stat-card {
         background: white;
         padding: 25px;
         border-radius: 16px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
         transition: var(--transition);
         position: relative;
         overflow: hidden;
      }

      .stat-card::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 4px;
         height: 100%;
      }

      .stat-card.users::before { background: var(--primary); }
      .stat-card.orders::before { background: var(--secondary); }
      .stat-card.messages::before { background: #4299E1; }

      .stat-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      }

      .stat-icon {
         width: 50px;
         height: 50px;
         border-radius: 12px;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-bottom: 15px;
         font-size: 1.5rem;
         color: white;
      }

      .stat-card.users .stat-icon { background: var(--primary); }
      .stat-card.orders .stat-icon { background: var(--secondary); }
      .stat-card.messages .stat-icon { background: #4299E1; }

      .stat-number {
         font-size: 2rem;
         font-weight: 700;
         color: var(--dark);
         margin-bottom: 5px;
      }

      .stat-label {
         color: var(--gray);
         font-weight: 500;
         font-size: 0.9rem;
      }

      /* User Accounts Section */
      .accounts-section {
         background: white;
         padding: 40px;
         border-radius: 20px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
      }

      .section-heading {
         font-size: 1.8rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 30px;
         display: flex;
         align-items: center;
         gap: 12px;
      }

      .section-heading i {
         color: var(--primary);
         font-size: 1.5rem;
      }

      /* Users Grid */
      .users-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
         gap: 25px;
      }

      .user-card {
         background: white;
         border: 1px solid var(--border);
         border-radius: 16px;
         padding: 25px;
         transition: var(--transition);
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
         position: relative;
         overflow: hidden;
      }

      .user-card::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 4px;
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      }

      .user-card:hover {
         transform: translateY(-8px);
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
         border-color: var(--primary-light);
      }

      .user-header {
         display: flex;
         align-items: center;
         gap: 15px;
         margin-bottom: 20px;
      }

      .user-avatar {
         width: 60px;
         height: 60px;
         border-radius: 50%;
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
         display: flex;
         align-items: center;
         justify-content: center;
         color: white;
         font-weight: 700;
         font-size: 1.2rem;
         flex-shrink: 0;
      }

      .user-info {
         flex: 1;
      }

      .user-name {
         font-size: 1.2rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 4px;
      }

      .user-id {
         font-size: 0.85rem;
         color: var(--gray);
         font-weight: 500;
      }

      .user-details {
         display: flex;
         flex-direction: column;
         gap: 12px;
         margin-bottom: 25px;
      }

      .detail-item {
         display: flex;
         align-items: center;
         gap: 10px;
         color: var(--gray);
         font-size: 0.9rem;
      }

      .detail-item i {
         color: var(--primary);
         width: 16px;
         font-size: 0.9rem;
      }

      .user-actions {
         display: flex;
         gap: 12px;
      }

      .action-btn {
         flex: 1;
         padding: 10px 16px;
         border-radius: 10px;
         text-decoration: none;
         font-weight: 600;
         font-size: 0.9rem;
         text-align: center;
         transition: var(--transition);
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 6px;
      }

      .delete-btn {
         background: rgba(255, 107, 107, 0.1);
         color: var(--accent);
         border: 1px solid rgba(255, 107, 107, 0.2);
      }

      .delete-btn:hover {
         background: var(--accent);
         color: white;
         transform: translateY(-2px);
      }

      /* Empty State */
      .empty-state {
         text-align: center;
         padding: 60px 20px;
         color: var(--gray);
         grid-column: 1 / -1;
      }

      .empty-icon {
         font-size: 4rem;
         color: var(--border);
         margin-bottom: 20px;
      }

      .empty-text {
         font-size: 1.2rem;
         margin-bottom: 10px;
         color: var(--dark);
      }

      /* Responsive Design */
      @media (max-width: 1024px) {
         .main-content {
            padding: 20px;
            margin-top: 70px;
         }

         .page-title {
            font-size: 2rem;
         }

         .users-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
         }
      }

      @media (max-width: 768px) {
         .main-content {
            padding: 15px;
         }

         .page-header {
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
         }

         .stats-grid {
            grid-template-columns: 1fr;
         }

         .accounts-section {
            padding: 25px;
         }

         .users-grid {
            grid-template-columns: 1fr;
         }

         .user-header {
            flex-direction: column;
            text-align: center;
            gap: 12px;
         }

         .user-actions {
            flex-direction: column;
         }
      }

      @media (max-width: 480px) {
         .page-title {
            font-size: 1.5rem;
         }

         .section-heading {
            font-size: 1.4rem;
         }

         .accounts-section {
            padding: 20px;
         }

         .user-card {
            padding: 20px;
         }

         .stat-card {
            padding: 20px;
         }

         .stat-number {
            font-size: 1.5rem;
         }
      }

      /* Animation */
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

      .fade-in {
         animation: fadeInUp 0.6s ease-out;
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="main-content">
   <!-- Page Header -->
   <div class="page-header fade-in">
      <h1 class="page-title">User Management</h1>
   </div>

   <!-- Statistics Cards -->
   <div class="stats-grid fade-in">
      <div class="stat-card users">
         <div class="stat-icon">
            <i class="fas fa-users"></i>
         </div>
         <div class="stat-number"><?= $total_users ?></div>
         <div class="stat-label">Total Users</div>
      </div>

      <div class="stat-card orders">
         <div class="stat-icon">
            <i class="fas fa-shopping-bag"></i>
         </div>
         <div class="stat-number"><?= $total_orders ?></div>
         <div class="stat-label">Total Orders</div>
      </div>

   </div>

   <!-- User Accounts Section -->
   <section class="accounts-section fade-in">
      <h2 class="section-heading">
         <i class="fas fa-user-cog"></i>
         User Accounts
      </h2>

      <div class="users-grid">
         <?php
         $select_accounts = $conn->prepare("SELECT * FROM `users` ORDER BY id DESC");
         $select_accounts->execute();
         if ($select_accounts->rowCount() > 0) {
            while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {
               $name_parts = explode(' ', $fetch_accounts['name']);
               $initials = '';
               foreach($name_parts as $part) {
                  $initials .= strtoupper(substr($part, 0, 1));
               }
         ?>
         <div class="user-card">
            <div class="user-header">
               <div class="user-avatar"><?= $initials ?></div>
               <div class="user-info">
                  <div class="user-name"><?= htmlspecialchars($fetch_accounts['name']) ?></div>
                  <div class="user-id">ID: #<?= $fetch_accounts['id'] ?></div>
               </div>
            </div>

            <div class="user-details">
               <div class="detail-item">
                  <i class="fas fa-envelope"></i>
                  <span><?= htmlspecialchars($fetch_accounts['email']) ?></span>
               </div>
            </div>

            <div class="user-actions">
               <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" 
                  class="action-btn delete-btn" 
                  onclick="return confirm('Are you sure you want to delete this user account? All associated orders, messages, cart items, and wishlist items will be permanently removed.')">
                  <i class="fas fa-trash"></i>
                  Delete Account
               </a>
            </div>
         </div>
         <?php
            }
         } else {
            echo '
            <div class="empty-state">
               <div class="empty-icon">
                  <i class="fas fa-user-slash"></i>
               </div>
               <div class="empty-text">No User Accounts Found</div>
               <p>There are no registered users in the system yet.</p>
            </div>
            ';
         }
         ?>
      </div>
   </section>
</div>

<script>
   // Add animation to user cards
   document.addEventListener('DOMContentLoaded', function() {
      const userCards = document.querySelectorAll('.user-card');
      userCards.forEach((card, index) => {
         card.style.animationDelay = `${index * 0.1}s`;
         card.classList.add('fade-in');
      });
   });
</script>

</body>
</html>