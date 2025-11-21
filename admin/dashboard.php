<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

// Detect the correct date column
$check_columns = $conn->query("SHOW COLUMNS FROM `orders` LIKE '%date%'");
$date_column = 'placed_on'; // default fallback
if($check_columns->rowCount() > 0) {
    $column_info = $check_columns->fetch(PDO::FETCH_ASSOC);
    $date_column = $column_info['Field'];
}

// Get monthly data
$monthly_data = array();
for ($i = 1; $i <= 12; $i++) {
    $month = date('Y-m', strtotime(date('Y') . "-" . $i . "-01"));
    $start_date = $month . "-01";
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $select_monthly_orders = $conn->prepare("SELECT COUNT(*) as count FROM `orders` WHERE $date_column BETWEEN ? AND ?");
    $select_monthly_orders->execute([$start_date, $end_date]);
    $monthly_orders = $select_monthly_orders->fetch(PDO::FETCH_ASSOC);
    
    $select_monthly_revenue = $conn->prepare("SELECT SUM(total_price) as total FROM `orders` WHERE payment_status = 'completed' AND $date_column BETWEEN ? AND ?");
    $select_monthly_revenue->execute([$start_date, $end_date]);
    $monthly_revenue = $select_monthly_revenue->fetch(PDO::FETCH_ASSOC);
    
    $monthly_data[$i] = array(
        'month' => date('M', strtotime($start_date)),
        'orders' => $monthly_orders['count'] ?: 0,
        'revenue' => $monthly_revenue['total'] ?: 0
    );
}

// Get dashboard stats
$total_pendings = 0;
$select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
$select_pendings->execute(['pending']);
while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
    $total_pendings += $fetch_pendings['total_price'];
}

$total_completes = 0;
$select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
$select_completes->execute(['completed']);
while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
    $total_completes += $fetch_completes['total_price'];
}

$select_orders = $conn->prepare("SELECT * FROM `orders`");
$select_orders->execute();
$number_of_orders = $select_orders->rowCount();

$select_products = $conn->prepare("SELECT * FROM `products`");
$select_products->execute();
$number_of_products = $select_products->rowCount();

$select_users = $conn->prepare("SELECT * FROM `users`");
$select_users->execute();
$number_of_users = $select_users->rowCount();

$select_admins = $conn->prepare("SELECT * FROM `admins`");
$select_admins->execute();
$number_of_admins = $select_admins->rowCount();

$select_messages = $conn->prepare("SELECT * FROM `messages`");
$select_messages->execute();
$number_of_messages = $select_messages->rowCount();

$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard | Admin Panel</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js">

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

      .welcome-section {
         display: flex;
         align-items: center;
         gap: 15px;
         color: var(--gray);
         font-size: 1.1rem;
      }

      .welcome-section i {
         color: var(--primary);
      }

      /* Stats Grid */
      .stats-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
         gap: 25px;
         margin-bottom: 40px;
      }

      .stat-card {
         background: white;
         padding: 30px;
         border-radius: 20px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
         transition: var(--transition);
         position: relative;
         overflow: hidden;
         text-align: center;
      }

      .stat-card::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 4px;
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      }

      .stat-card:hover {
         transform: translateY(-8px);
         box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      }

      .stat-icon {
         width: 70px;
         height: 70px;
         border-radius: 20px;
         display: flex;
         align-items: center;
         justify-content: center;
         margin: 0 auto 20px;
         font-size: 1.8rem;
         color: white;
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      }

      .stat-value {
         font-size: 2.5rem;
         font-weight: 700;
         color: var(--dark);
         margin-bottom: 10px;
         line-height: 1;
      }

      .stat-value span {
         font-size: 1.5rem;
         color: var(--primary);
      }

      .stat-label {
         color: var(--gray);
         font-weight: 500;
         font-size: 1rem;
         margin-bottom: 20px;
      }

      .stat-btn {
         display: inline-block;
         padding: 12px 24px;
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
         color: white;
         text-decoration: none;
         border-radius: 12px;
         font-weight: 600;
         transition: var(--transition);
         font-size: 0.9rem;
      }

      .stat-btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 10px 25px rgba(139, 95, 191, 0.4);
      }

      /* Welcome Card Special Styling */
      .welcome-card {
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
         color: white;
         grid-column: 1 / -1;
      }

      .welcome-card::before {
         display: none;
      }

      .welcome-card .stat-icon {
         background: rgba(255, 255, 255, 0.2);
         backdrop-filter: blur(10px);
      }

      .welcome-card .stat-value {
         color: white;
         font-size: 2rem;
      }

      .welcome-card .stat-label {
         color: rgba(255, 255, 255, 0.8);
      }

      .welcome-card .stat-btn {
         background: rgba(255, 255, 255, 0.2);
         backdrop-filter: blur(10px);
         border: 1px solid rgba(255, 255, 255, 0.3);
      }

      .welcome-card .stat-btn:hover {
         background: white;
         color: var(--primary);
      }

      /* Charts Section */
      .charts-section {
         display: grid;
         grid-template-columns: 2fr 1fr;
         gap: 30px;
         margin-bottom: 40px;
      }

      .chart-container {
         background: white;
         padding: 30px;
         border-radius: 20px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
      }

      .chart-title {
         font-size: 1.5rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 25px;
         display: flex;
         align-items: center;
         gap: 12px;
      }

      .chart-title i {
         color: var(--primary);
      }

      .chart-controls {
         display: flex;
         gap: 10px;
         margin-bottom: 20px;
      }

      .chart-controls button {
         padding: 10px 20px;
         border: 1px solid var(--border);
         background: white;
         color: var(--gray);
         border-radius: 10px;
         cursor: pointer;
         transition: var(--transition);
         font-weight: 500;
         font-size: 0.9rem;
      }

      .chart-controls button.active,
      .chart-controls button:hover {
         background: var(--primary);
         color: white;
         border-color: var(--primary);
      }

      .chart-holder {
         height: 300px;
         position: relative;
      }

      /* Live Clock */
      .live-clock {
         background: white;
         padding: 25px;
         border-radius: 20px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
         text-align: center;
         margin-bottom: 30px;
      }

      .clock-time {
         font-size: 3rem;
         font-weight: 700;
         color: var(--primary);
         margin-bottom: 10px;
         font-family: 'Inter', monospace;
      }

      .clock-date {
         font-size: 1.2rem;
         color: var(--gray);
         font-weight: 500;
      }

      /* Responsive Design */
      @media (max-width: 1200px) {
         .charts-section {
            grid-template-columns: 1fr;
         }
         
         .stats-grid {
            grid-template-columns: repeat(2, 1fr);
         }
      }

      @media (max-width: 768px) {
         .main-content {
            padding: 20px;
            margin-top: 70px;
         }

         .page-title {
            font-size: 2rem;
         }

         .stats-grid {
            grid-template-columns: 1fr;
         }

         .welcome-card {
            grid-column: 1;
         }

         .chart-container {
            padding: 20px;
         }

         .clock-time {
            font-size: 2.5rem;
         }
      }

      @media (max-width: 480px) {
         .main-content {
            padding: 15px;
         }

         .page-title {
            font-size: 1.5rem;
         }

         .stat-card {
            padding: 25px;
         }

         .stat-value {
            font-size: 2rem;
         }

         .clock-time {
            font-size: 2rem;
         }

         .chart-controls {
            flex-direction: column;
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
      <h1 class="page-title">Dashboard Overview</h1>
      <div class="welcome-section">
         <i class="fas fa-user-shield"></i>
         Welcome back, <?= htmlspecialchars($fetch_profile['name']); ?>
      </div>
   </div>

   <!-- Live Clock -->
   <div class="live-clock fade-in">
      <div class="clock-time" id="live-clock">--:--:--</div>
      <div class="clock-date" id="live-date">Loading...</div>
   </div>

   <!-- Statistics Grid -->
   <div class="stats-grid">
      <!-- Welcome Card -->
      <div class="stat-card welcome-card fade-in">
         <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
         </div>
         <div class="stat-value">Welcome to Kickster Admin</div>
         <div class="stat-label">Manage your store efficiently with real-time analytics</div>
         <a href="update_profile.php" class="stat-btn">
            <i class="fas fa-user-edit"></i>
            Update Profile
         </a>
      </div>

      <!-- Pending Orders -->
      <div class="stat-card fade-in">
         <div class="stat-icon">
            <i class="fas fa-clock"></i>
         </div>
         <div class="stat-value"><span>Rs </span><?= number_format($total_pendings); ?></div>
         <div class="stat-label">Total Pending Amount</div>
         <a href="placed_orders.php" class="stat-btn">
            <i class="fas fa-shopping-bag"></i>
            View Orders
         </a>
      </div>

      <!-- Completed Orders -->
      <div class="stat-card fade-in">
         <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
         </div>
         <div class="stat-value"><span>Rs </span><?= number_format($total_completes); ?></div>
         <div class="stat-label">Completed Orders Revenue</div>
         <a href="placed_orders.php" class="stat-btn">
            <i class="fas fa-shopping-bag"></i>
            View Orders
         </a>
      </div>

      <!-- Total Orders -->
      <div class="stat-card fade-in">
         <div class="stat-icon">
            <i class="fas fa-clipboard-list"></i>
         </div>
         <div class="stat-value"><?= number_format($number_of_orders); ?></div>
         <div class="stat-label">Total Orders Placed</div>
         <a href="placed_orders.php" class="stat-btn">
            <i class="fas fa-eye"></i>
            See Details
         </a>
      </div>

      <!-- Products -->
      <div class="stat-card fade-in">
         <div class="stat-icon">
            <i class="fas fa-cube"></i>
         </div>
         <div class="stat-value"><?= number_format($number_of_products); ?></div>
         <div class="stat-label">Products in Store</div>
         <a href="products.php" class="stat-btn">
            <i class="fas fa-boxes"></i>
            Manage Products
         </a>
      </div>

      <!-- Users -->
      <div class="stat-card fade-in">
         <div class="stat-icon">
            <i class="fas fa-users"></i>
         </div>
         <div class="stat-value"><?= number_format($number_of_users); ?></div>
         <div class="stat-label">Registered Users</div>
         <a href="users_accounts.php" class="stat-btn">
            <i class="fas fa-user-cog"></i>
            Manage Users
         </a>
      </div>
   </div>

   <!-- Charts Section -->
   <div class="charts-section">
      <!-- Line Chart -->
      <div class="chart-container fade-in">
         <h2 class="chart-title">
            <i class="fas fa-chart-line"></i>
            Monthly Analytics
         </h2>
         <div class="chart-controls">
            <button class="active" data-type="orders">Orders</button>
            <button data-type="revenue">Revenue</button>
            <button data-type="both">Both</button>
         </div>
         <div class="chart-holder">
            <canvas id="lineChart"></canvas>
         </div>
      </div>

      <!-- Pie Chart -->
      <div class="chart-container fade-in">
         <h2 class="chart-title">
            <i class="fas fa-chart-pie"></i>
            Store Distribution
         </h2>
         <div class="chart-holder">
            <canvas id="pieChart"></canvas>
         </div>
      </div>
   </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
      // Live clock
      function updateClock() {
         const now = new Date();
         const timeString = now.toLocaleTimeString('en-US', { 
            hour12: true,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
         });
         const dateString = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
         });
         document.getElementById('live-clock').textContent = timeString;
         document.getElementById('live-date').textContent = dateString;
      }
      updateClock();
      setInterval(updateClock, 1000);

      // Monthly data
      const monthlyData = {
         labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
         orders: <?php echo json_encode(array_column($monthly_data, 'orders')); ?>,
         revenue: <?php echo json_encode(array_column($monthly_data, 'revenue')); ?>
      };

      // Line Chart
      const lineCtx = document.getElementById('lineChart').getContext('2d');
      const lineChart = new Chart(lineCtx, {
         type: 'line',
         data: {
            labels: monthlyData.labels,
            datasets: [{
               label: 'Orders',
               data: monthlyData.orders,
               borderColor: 'rgba(139, 95, 191, 1)',
               backgroundColor: 'rgba(139, 95, 191, 0.1)',
               borderWidth: 3,
               tension: 0.4,
               fill: true
            }, {
               label: 'Revenue',
               data: monthlyData.revenue,
               borderColor: 'rgba(6, 214, 160, 1)',
               backgroundColor: 'rgba(6, 214, 160, 0.1)',
               borderWidth: 3,
               tension: 0.4,
               fill: true
            }]
         },
         options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
               legend: {
                  position: 'top',
                  labels: {
                     usePointStyle: true,
                     font: {
                        size: 12
                     }
                  }
               },
               tooltip: {
                  callbacks: {
                     label: function(context) {
                        let label = context.dataset.label || '';
                        if (label.includes('Revenue')) {
                           return label + ': Rs ' + context.parsed.y.toLocaleString();
                        } else {
                           return label + ': ' + context.parsed.y.toLocaleString();
                        }
                     }
                  }
               }
            },
            scales: {
               y: {
                  beginAtZero: true,
                  grid: {
                     color: 'rgba(0, 0, 0, 0.05)'
                  }
               },
               x: {
                  grid: {
                     color: 'rgba(0, 0, 0, 0.05)'
                  }
               }
            }
         }
      });

      // Chart filter buttons
      document.querySelectorAll('.chart-controls button').forEach(button => {
         button.addEventListener('click', function() {
            document.querySelectorAll('.chart-controls button').forEach(btn => {
               btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const type = this.getAttribute('data-type');
            lineChart.data.datasets.forEach((dataset, i) => {
               dataset.hidden = !(type === 'both' || 
                                 (type === 'orders' && i === 0) || 
                                 (type === 'revenue' && i === 1));
            });
            lineChart.update();
         });
      });

      // Pie Chart
      const pieCtx = document.getElementById('pieChart').getContext('2d');
      const pieChart = new Chart(pieCtx, {
         type: 'pie',
         data: {
            labels: ['Pending Amount', 'Completed Revenue', 'Products', 'Users', 'Admins'],
            datasets: [{
               data: [
                  <?= $total_pendings; ?>,
                  <?= $total_completes; ?>,
                  <?= $number_of_products; ?>,
                  <?= $number_of_users; ?>,
                  <?= $number_of_admins; ?>
               ],
               backgroundColor: [
                  'rgba(255, 107, 107, 0.8)',
                  'rgba(6, 214, 160, 0.8)',
                  'rgba(139, 95, 191, 0.8)',
                  'rgba(66, 153, 225, 0.8)',
                  'rgba(237, 137, 54, 0.8)'
               ],
               borderWidth: 2,
               borderColor: 'white'
            }]
         },
         options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
               legend: {
                  position: 'right',
                  labels: {
                     usePointStyle: true,
                     padding: 20,
                     font: {
                        size: 11
                     }
                  }
               },
               tooltip: {
                  callbacks: {
                     label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        if (label.includes('Amount') || label.includes('Revenue')) {
                           return `${label}: Rs ${value.toLocaleString()} (${percentage}%)`;
                        }
                        return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                     }
                  }
               }
            }
         }
      });
   });
</script>
</body>
</html>