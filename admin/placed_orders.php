<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'Payment status updated successfully!';
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

// Add search functionality
$search = '';
if(isset($_GET['search'])){
   $search = $_GET['search'];
   $search = filter_var($search, FILTER_SANITIZE_STRING);
}

// Get order statistics
$total_orders = $conn->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
$pending_orders = $conn->query("SELECT COUNT(*) FROM `orders` WHERE payment_status = 'pending'")->fetchColumn();
$completed_orders = $conn->query("SELECT COUNT(*) FROM `orders` WHERE payment_status = 'completed'")->fetchColumn();
$total_revenue = $conn->query("SELECT COALESCE(SUM(total_price), 0) FROM `orders` WHERE payment_status = 'completed'")->fetchColumn();

?>
<?php include '../components/admin_header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders | Admin Panel</title>

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

      /* Orders Section */
      .orders {
         padding: 30px;
         max-width: 1400px;
         margin: 0 auto;
      }

      /* Header Section */
      .page-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 30px;
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

      /* Stats Cards */
      .stats-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 20px;
         margin-bottom: 30px;
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
         background: var(--primary);
      }

      .stat-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      }

      .stat-card.total::before { background: var(--primary); }
      .stat-card.pending::before { background: var(--accent); }
      .stat-card.completed::before { background: var(--secondary); }
      .stat-card.revenue::before { background: #4299E1; }

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

      .stat-card.total .stat-icon { background: var(--primary); }
      .stat-card.pending .stat-icon { background: var(--accent); }
      .stat-card.completed .stat-icon { background: var(--secondary); }
      .stat-card.revenue .stat-icon { background: #4299E1; }

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

      /* Orders Table Container */
      .orders-container {
         background: white;
         border-radius: 16px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
         overflow: hidden;
      }

      .table-header {
         padding: 25px;
         border-bottom: 1px solid var(--border);
         background: var(--light);
      }

      .table-title {
         font-size: 1.5rem;
         font-weight: 600;
         color: var(--dark);
         margin: 0;
      }

      /* Table Styling */
      .custom-table {
         width: 100%;
         border-collapse: collapse;
      }

      .custom-table thead {
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      }

      .custom-table th {
         padding: 16px 20px;
         text-align: left;
         color: white;
         font-weight: 600;
         font-size: 0.9rem;
         text-transform: uppercase;
         letter-spacing: 0.5px;
      }

      .custom-table tbody tr {
         border-bottom: 1px solid var(--border);
         transition: var(--transition);
      }

      .custom-table tbody tr:hover {
         background: var(--light);
         transform: translateX(5px);
      }

      .custom-table td {
         padding: 20px;
         color: var(--dark);
         font-weight: 500;
      }

      /* Status Badges */
      .status-badge {
         padding: 8px 16px;
         border-radius: 20px;
         font-size: 0.8rem;
         font-weight: 600;
         text-transform: uppercase;
         letter-spacing: 0.5px;
      }

      .status-pending {
         background: rgba(255, 107, 107, 0.1);
         color: var(--accent);
         border: 1px solid rgba(255, 107, 107, 0.2);
      }

      .status-completed {
         background: rgba(6, 214, 160, 0.1);
         color: var(--secondary);
         border: 1px solid rgba(6, 214, 160, 0.2);
      }

      /* Action Buttons */
      .action-buttons {
         display: flex;
         gap: 10px;
         align-items: center;
      }

      .status-select {
         padding: 8px 12px;
         border: 1px solid var(--border);
         border-radius: 8px;
         background: white;
         color: var(--dark);
         font-weight: 500;
         cursor: pointer;
         transition: var(--transition);
         min-width: 120px;
      }

      .status-select:focus {
         outline: none;
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(139, 95, 191, 0.1);
      }

      .delete-btn {
         padding: 8px 12px;
         background: rgba(255, 107, 107, 0.1);
         color: var(--accent);
         border: 1px solid rgba(255, 107, 107, 0.2);
         border-radius: 8px;
         cursor: pointer;
         transition: var(--transition);
         text-decoration: none;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .delete-btn:hover {
         background: var(--accent);
         color: white;
         transform: scale(1.05);
      }

      /* Customer Info */
      .customer-info {
         display: flex;
         flex-direction: column;
         gap: 4px;
      }

      .customer-name {
         font-weight: 600;
         color: var(--dark);
      }

      .customer-detail {
         font-size: 0.85rem;
         color: var(--gray);
      }

      /* Empty State */
      .empty-state {
         text-align: center;
         padding: 60px 20px;
         color: var(--gray);
      }

      .empty-icon {
         font-size: 4rem;
         color: var(--border);
         margin-bottom: 20px;
      }

      .empty-text {
         font-size: 1.2rem;
         margin-bottom: 10px;
      }

      /* Responsive Design */
      @media (max-width: 1024px) {
         .orders {
            padding: 20px;
         }

         .page-title {
            font-size: 2rem;
         }

         .stats-grid {
            grid-template-columns: repeat(2, 1fr);
         }
      }

      @media (max-width: 768px) {
         .orders {
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

         .custom-table {
            display: block;
            overflow-x: auto;
         }

         .action-buttons {
            flex-direction: column;
            gap: 8px;
         }

         .status-select {
            min-width: 100px;
         }
      }

      @media (max-width: 480px) {
         .page-title {
            font-size: 1.5rem;
         }

         .stat-card {
            padding: 20px;
         }

         .stat-number {
            font-size: 1.5rem;
         }

         .custom-table th,
         .custom-table td {
            padding: 12px 8px;
            font-size: 0.8rem;
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

<section class="orders">
   <!-- Page Header -->
   <div class="page-header fade-in">
      <h1 class="page-title">Order Management</h1>
      <div class="header-actions">
         <!-- Add any additional header buttons here -->
      </div>
   </div>

   <!-- Statistics Cards -->
   <div class="stats-grid fade-in">
      <div class="stat-card total">
         <div class="stat-icon">
            <i class="fas fa-shopping-bag"></i>
         </div>
         <div class="stat-number"><?= $total_orders ?></div>
         <div class="stat-label">Total Orders</div>
      </div>

      <div class="stat-card pending">
         <div class="stat-icon">
            <i class="fas fa-clock"></i>
         </div>
         <div class="stat-number"><?= $pending_orders ?></div>
         <div class="stat-label">Pending Orders</div>
      </div>

      <div class="stat-card completed">
         <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
         </div>
         <div class="stat-number"><?= $completed_orders ?></div>
         <div class="stat-label">Completed Orders</div>
      </div>

      <div class="stat-card revenue">
         <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
         </div>
         <div class="stat-number">NRs. <?= number_format($total_revenue, 2) ?></div>
         <div class="stat-label">Total Revenue</div>
      </div>
   </div>

   <!-- Orders Table -->
   <div class="orders-container fade-in">
      <div class="table-header">
         <h2 class="table-title">All Orders</h2>
      </div>

      <div class="table-responsive">
         <table class="custom-table">
            <thead>
               <tr>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Customer</th>
                  <th>Amount</th>
                  <th>Products</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th>Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php
                  $select_orders = $conn->prepare("SELECT * FROM `orders` 
                                                   WHERE id LIKE ? OR name LIKE ? OR number LIKE ? OR address LIKE ?
                                                   ORDER BY placed_on DESC");
                  $search_param = "%$search%";
                  $select_orders->execute([$search_param, $search_param, $search_param, $search_param]);
                  
                  if($select_orders->rowCount() > 0){
                     while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
                        $status_class = $fetch_orders['payment_status'] == 'completed' ? 'status-completed' : 'status-pending';
               ?>
               <tr>
                  <td><strong>#<?= $fetch_orders['id'] ?></strong></td>
                  <td><?= date('M d, Y', strtotime($fetch_orders['placed_on'])) ?></td>
                  <td>
                     <div class="customer-info">
                        <span class="customer-name"><?= $fetch_orders['name'] ?></span>
                        <span class="customer-detail"><?= $fetch_orders['number'] ?></span>
                        <span class="customer-detail"><?= $fetch_orders['address'] ?></span>
                     </div>
                  </td>
                  <td><strong>NRs. <?= number_format($fetch_orders['total_price'], 2) ?></strong></td>
                  <td>
                     <span class="customer-detail" title="<?= $fetch_orders['total_products'] ?>">
                        <?= strlen($fetch_orders['total_products']) > 30 ? substr($fetch_orders['total_products'], 0, 30) . '...' : $fetch_orders['total_products'] ?>
                     </span>
                  </td>
                  <td>
                     <span class="status-badge">
                        <?= ucfirst($fetch_orders['method']) ?>
                     </span>
                  </td>
                  <td>
                     <span class="status-badge <?= $status_class ?>">
                        <?= ucfirst($fetch_orders['payment_status']) ?>
                     </span>
                  </td>
                  <td>
                     <div class="action-buttons">
                        <form action="" method="post" class="d-inline">
                           <input type="hidden" name="order_id" value="<?= $fetch_orders['id'] ?>">
                           <input type="hidden" name="update_payment" value="1">
                           <select name="payment_status" class="status-select" onchange="this.form.submit()">
                              <option value="pending" <?= $fetch_orders['payment_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                              <option value="completed" <?= $fetch_orders['payment_status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                           </select>
                        </form>
                        <a href="placed_orders.php?delete=<?= $fetch_orders['id'] ?>" 
                           class="delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this order?');"
                           title="Delete Order">
                           <i class="fas fa-trash"></i>
                        </a>
                     </div>
                  </td>
               </tr>
               <?php
                     }
                  } else {
               ?>
               <tr>
                  <td colspan="8">
                     <div class="empty-state">
                        <div class="empty-icon">
                           <i class="fas fa-box-open"></i>
                        </div>
                        <div class="empty-text">No orders found</div>
                        <p class="customer-detail">There are no orders matching your criteria.</p>
                     </div>
                  </td>
               </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
   </div>
</section>

<script>
   // Add hover effects and animations
   document.addEventListener('DOMContentLoaded', function() {
      const tableRows = document.querySelectorAll('.custom-table tbody tr');
      
      tableRows.forEach((row, index) => {
         // Add staggered animation
         row.style.animationDelay = `${index * 0.1}s`;
         row.classList.add('fade-in');
         
         // Add click effect
         row.addEventListener('click', function(e) {
            if (!e.target.closest('.action-buttons')) {
               this.style.transform = 'translateX(10px)';
               setTimeout(() => {
                  this.style.transform = 'translateX(5px)';
               }, 150);
            }
         });
      });

      // Auto-hide messages after 5 seconds
      const messages = document.querySelectorAll('.message');
      messages.forEach(message => {
         setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
         }, 5000);
      });
   });

   // Add search functionality
   function searchOrders() {
      const searchTerm = document.getElementById('searchInput').value;
      window.location.href = `placed_orders.php?search=${encodeURIComponent(searchTerm)}`;
   }
</script>

</body>
</html>