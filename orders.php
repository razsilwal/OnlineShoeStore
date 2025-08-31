<?php
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';

// Handle order deletion
if(isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    $order_id = filter_var($order_id, FILTER_SANITIZE_STRING);
    
    // Verify the order belongs to the user before deleting
    $verify_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ?");
    $verify_order->execute([$order_id, $user_id]);
    
    if($verify_order->rowCount() > 0) {
        $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
        $delete_order->execute([$order_id]);
        $message[] = 'Order deleted successfully!';
    } else {
        $message[] = 'Order not found!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders | Kickster</title>
   
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
   
</head>
<body>
   
<!-- Header Section -->
<?php
// messages.php - Reusable message component

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize message array
$messages = [];

// Check for session messages
if (isset($_SESSION['messages']) && !empty($_SESSION['messages'])) {
    $messages = (array)$_SESSION['messages'];
    unset($_SESSION['messages']); // Clear after displaying
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kickster</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/order.css">

</head>
<body>
    <?php if (!empty($messages)): ?>
        <div class="messages-container">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= htmlspecialchars($msg['type'], ENT_QUOTES) ?>">
                    <span><?= htmlspecialchars($msg['text'], ENT_QUOTES) ?></span>
                    <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php include 'components/user_header.php'; ?>


    <style>
    /* Message Styles */
    .messages-container {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .message {
        padding: 15px 25px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(0);
        opacity: 1;
        transition: all 0.5s ease;
        max-width: 300px;
        word-wrap: break-word;
        animation: slide-in 0.5s forwards;
    }
    
    .message.info {
        background: #3498db;
        color: white;
    }
    
    .message.success {
        background: #2ecc71;
        color: white;
    }
    
    .message.error {
        background: #e74c3c;
        color: white;
    }
    
    .message.warning {
        background: #f4c90c;
        color: #333;
    }
    
    .message span {
        margin-right: 15px;
    }
    
    .message i {
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .message i:hover {
        transform: scale(1.2);
    }
    
    @keyframes slide-in {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @media (max-width: 768px) {
        .messages-container {
            width: calc(100% - 40px);
            right: 20px;
            left: 20px;
            top: 70px;
        }
        
        .message {
            max-width: none;
            width: 100%;
        }
    }
    
    /* Header Styles */
    .header {
        background: linear-gradient(135deg, rgb(128, 172, 185), rgb(75, 119, 139));
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        transition: all 0.3s ease;
    }
    
    .logo {
        font-size: 1.8rem;
        color: white;
        position: relative;
        text-decoration: none;
    }
    
    .logo span {
        color: #f39c12;
    }
    
    .logo-pulse {
        position: absolute;
        width: 8px;
        height: 8px;
        background: #f39c12;
        border-radius: 50%;
        top: -5px;
        right: -5px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        70% { transform: scale(1.3); opacity: 0.2; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
    
    .nav-link {
        color: white;
        margin: 0 15px;
        position: relative;
        text-transform: uppercase;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .nav-link:hover {
        color: #ffffff;
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    .icons {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .icon-link {
        color: white;
        font-size: 1.2rem;
        position: relative;
        transition: transform 0.3s;
    }
    
    .icon-link:hover {
        transform: translateY(-3px);
        color: #f39c12;
    }
    
    .badge {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }
    
    .pulse {
        animation: pulse 1.5s infinite;
    }
    
    .user-icon {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .user-icon:hover {
        color: #f39c12;
        transform: scale(1.1);
    }
    
    .profile-card {
        position: absolute;
        right: 2rem;
        top: 100%;
        background: white;
        border-radius: 10px;
        padding: 20px;
        width: 250px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s;
        z-index: 1001;
    }
    
    .profile-card.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .avatar {
        width: 40px;
        height: 40px;
        background: #3498db;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-weight: bold;
    }
    
    .username {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .profile-btn, .logout-btn {
        display: block;
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border-radius: 5px;
        text-align: left;
        transition: all 0.3s;
    }
    
    .profile-btn {
        background: #f8f9fa;
        color: #2c3e50;
    }
    
    .profile-btn:hover {
        background: #e9ecef;
    }
    
    .logout-btn {
        background: #f8f9fa;
        color: #e74c3c;
    }
    
    .logout-btn:hover {
        background: #fdecea;
    }
    
    .option-btn {
        display: inline-block;
        width: 48%;
        padding: 8px;
        margin: 5px 0;
        border-radius: 5px;
        text-align: center;
        transition: all 0.3s;
    }
    
    .gradient-btn {
        background: linear-gradient(to right, rgb(153, 188, 212), #2ecc71);
        color: white;
    }
    
    .gradient-btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
    
    .login-prompt {
        text-align: center;
        color: #7f8c8d;
        margin-bottom: 15px;
    }
    
    .hamburger {
        display: none;
        cursor: pointer;
        font-size: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .navbar {
            position: fixed;
            top: 70px;
            left: -100%;
            background: rgb(11, 129, 247);
            width: 80%;
            height: calc(100vh - 70px);
            flex-direction: column;
            padding: 20px;
            transition: all 0.5s;
        }
        
        .navbar.active {
            left: 0;
        }
        
        .nav-link {
            margin: 15px 0;
            font-size: 1.1rem;
        }
        
        .hamburger {
            display: block;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Message handling
        const messages = document.querySelectorAll('.message');
        
        messages.forEach(message => {
            // Auto-close after 4 seconds
            const autoCloseTimer = setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }, 4000);
            
            // Manual close
            const closeBtn = message.querySelector('.fa-times');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    clearTimeout(autoCloseTimer);
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 500);
                });
            }
        });
        
        // Header functionality
        const userBtn = document.getElementById('user-btn');
        const profileCard = document.querySelector('.profile-card');
        const menuBtn = document.getElementById('menu-btn');
        const navbar = document.querySelector('.navbar');
        
        // Toggle profile dropdown
        if (userBtn && profileCard) {
            userBtn.addEventListener('click', function() {
                profileCard.classList.toggle('active');
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-icon') && !e.target.closest('.profile-card')) {
                if (profileCard) profileCard.classList.remove('active');
            }
        });
        
        // Mobile menu toggle
        if (menuBtn && navbar) {
            menuBtn.addEventListener('click', function() {
                navbar.classList.toggle('active');
                menuBtn.classList.toggle('fa-times');
            });
        }
    });
    </script>
</body>
</html>

<!-- Display messages -->
<?php
if(isset($message)) {
   foreach($message as $msg) {
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<!-- Main Content -->
<section class="orders-section">
   <div class="container">
      <div class="orders-header">
         <h1 class="orders-title">My Orders</h1>
      </div>

      <div class="orders-container">
      <?php
         if($user_id == '') {
            echo '
            <div class="login-prompt">
               <h2 class="login-title">Please <a href="user_login.php" class="login-link">login</a> to view your orders</h2>
               <p>View and manage all your orders in one place</p>
            </div>
            ';
         } else {
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY placed_on DESC");
            $select_orders->execute([$user_id]);
            
            if($select_orders->rowCount() > 0) {
               while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                  $status = strtolower($fetch_orders['payment_status']);
                  $status_class = 'status-' . $status;
                  $products = explode(' - ', $fetch_orders['total_products']);
                  $order_date = date('M d, Y', strtotime($fetch_orders['placed_on']));
      ?>
      <div class="order-card">
         <div class="order-header">
            <div class="order-id">Order Product <?= $fetch_orders['id'] ?></div>
            <div class="order-date"><?= $order_date ?></div>
         </div>
         
         <div class="order-details" style="color: #55cb4fff;">
            <?php foreach($products as $product): ?>
               <?php if(!empty(trim($product))): ?>
                  <div class="order-product"><?= htmlspecialchars($product) ?></div>
               <?php endif; ?>
            <?php endforeach; ?>
         </div>
         
         <div class="order-price">Total: NRs. <?= number_format($fetch_orders['total_price'], 2) ?></div>
         
         <div class="order-status <?= $status_class ?>">
            <i class="fas fa-<?= 
               $status == 'pending' ? 'clock' : 
               ($status == 'processing' ? 'cog' : 
               ($status == 'shipped' ? 'truck' : 
               ($status == 'delivered' ? 'check-circle' : 'times-circle')))
            ?>"></i>
            <?= ucfirst($status) ?>
         </div>
         
         <div class="order-actions">
           <button class="order-btn order-btn-primary" onclick="window.location.href='shop.php'">
    <i class="fas fa-cart"></i> Shop
</button>


            <form method="POST" style="display: inline;">
               <input type="hidden" name="order_id" value="<?= $fetch_orders['id'] ?>">
               <button type="submit" name="delete_order" class="order-btn order-btn-danger" onclick="return confirm('Are you sure you want to cancel this order?');">
                  <i class="fas fa-trash"></i> Cancel
               </button>
            </form>
         </div>
      </div>
      <?php
               }
            } else {
               echo '
               <div class="empty-orders">
                  <div class="empty-icon">
                     <i class="fas fa-box-open"></i>
                  </div>
                  <h2 class="empty-title">No Orders Yet</h2>
                  <p class="empty-description">You haven\'t placed any orders with us yet. Start shopping to discover amazing products!</p>
                  <a href="shop.php" class="empty-action">Start Shopping</a>
               </div>
               ';
            }
         }
      ?>
      </div>
   </div>
</section>

<script>
// Newsletter Functionality
document.addEventListener('DOMContentLoaded', function() {
   const newsletter = document.querySelector('.newsletter-corner.top-left');
   const toggle = newsletter.querySelector('.newsletter-toggle');
   const closeBtn = newsletter.querySelector('.close-btn');
   const form = newsletter.querySelector('.newsletter-form');

   // Toggle newsletter box
   toggle.addEventListener('click', () => {
      newsletter.classList.toggle('active');
   });

   closeBtn.addEventListener('click', () => {
      newsletter.classList.remove('active');
   });

   // Form submission
   form.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = form.querySelector('input').value;
      
      // Simulate successful submission
      form.innerHTML = `
        <div style="text-align: center; padding: 10px 0;">
          <i class="fas fa-check-circle" style="font-size: 40px; color: #4cc9f0;"></i>
          <h3 style="margin: 10px 0; color: #4361ee;">Thank You!</h3>
          <p>Check your email for your discount code</p>
        </div>
      `;
      
      // Hide after 3 seconds
      setTimeout(() => {
        newsletter.classList.remove('active');
        // Reset form after animation
        setTimeout(() => {
          form.innerHTML = `
            <input type="email" placeholder="Enter your email" required>
            <button type="submit" class="btn">
              <i class="fas fa-paper-plane"></i> Claim Discount
            </button>
          `;
        }, 300);
      }, 3000);
   });
   
   // Footer animations
   const footerBoxes = document.querySelectorAll('.footer .box');
   footerBoxes.forEach((box, index) => {
      box.style.animationDelay = `${index * 0.1}s`;
   });
});
</script>
<script>
// Mobile menu toggle
document.getElementById('menu-btn').addEventListener('click', function() {
   document.getElementById('navbar').classList.toggle('active');
});

// User account box toggle
document.getElementById('user-btn').addEventListener('click', function() {
   document.querySelector('.account-box').classList.toggle('active');
});

// Close account box when clicking outside
document.addEventListener('click', function(e) {
   if(!e.target.closest('.account-box') && !e.target.closest('#user-btn')) {
      document.querySelector('.account-box').classList.remove('active');
   }
});

// Auto-close messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
   const messages = document.querySelectorAll('.message');
   messages.forEach(message => {
      setTimeout(() => {
         message.style.opacity = '0';
         setTimeout(() => message.remove(), 300);
      }, 5000);
   });
});
</script>

</body>
</html>