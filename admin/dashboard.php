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
   <title>Dashboard</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
   <link rel="stylesheet" href="../css/admin_dashboard.css">

</head>
<body>
<?php include '../components/admin_navbar.php'; ?>
<section class="dashboard">
   <h1 class="heading">Dashboard</h1>
   <div class="clock"> <div id="live-clock"></div></div>

   <div class="box-container">
      <div class="box">
         <h3>Welcome</h3>
         <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
        
         <a href="update_profile.php" class="btn">Update Profile</a>
      </div>

      <div class="box">
         <h3><span>Rs-</span><?= number_format($total_pendings); ?></h3>
         <p>Total Pendings</p>
         
         <a href="placed_orders.php" class="btn">See Orders</a>
      </div>

      <div class="box">
         <h3><span>Rs-</span><?= number_format($total_completes); ?></h3>
         <p>Completed Orders</p>
         
         <a href="placed_orders.php" class="btn">See Orders</a>
      </div>

      <div class="box">
         <h3><?= number_format($number_of_orders); ?></h3>
         <p>Orders Placed</p>
         <a href="placed_orders.php" class="btn">See Orders</a>
      </div>

      <div class="box">
         <h3><?= number_format($number_of_products); ?></h3>
         <p>Products Added</p>
         <a href="products.php" class="btn">See Products</a>
      </div>

      <div class="box">
         <h3><?= number_format($number_of_users); ?></h3>
         <p>Users</p>
         <a href="users_accounts.php" class="btn">See Users</a>
      </div>


      
   </div>

   <div class="charts-wrapper">
      <div class="chart-container">
         <div class="chart-title">Monthly Analytics of Kickster</div>
         <div class="chart-controls">
            <button class="active" data-type="orders">Orders</button>
            <button data-type="revenue">Revenue</button>
            <button data-type="both">Both</button>
         </div>
         <div class="chart-holder">
            <canvas id="lineChart"></canvas>
         </div>
      </div>
   </div>
   <div class="chart-container">
         <div class="chart-title">Shoes Distribution</div>
         <div class="chart-holder">
            <canvas id="pieChart"></canvas>
         </div>
      </div>
   </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
      // Live clock
      function updateClock() {
         const now = new Date();
         const timeString = now.toLocaleTimeString();
         const dateString = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
         });
         document.getElementById('live-clock').innerHTML = `
            <i class="fas fa-clock"></i> ${timeString}<br>
            <i class="fas fa-calendar"></i> ${dateString}
         `;
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
            borderColor: 'rgba(29, 5, 241, 1)',
            backgroundColor: 'rgba(31, 253, 238, 0.83)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }, {
            label: 'Revenue',
            data: monthlyData.revenue,
            borderColor: 'rgba(29,5, 241, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            borderWidth: 2,
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
                display: true, // Changed from 'false' to true
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
                            label += ': ' + context.parsed.y.toLocaleString();
                        } else {
                            label += ': ' + context.parsed.y.toLocaleString();
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true
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
            labels: ['Pending Orders', 'Completed Orders', 'Products', 'Users', 'Admins', ],
            datasets: [{
               data: [
                  <?= $total_pendings; ?>,
                  <?= $total_completes; ?>,
                  <?= $number_of_products; ?>,
                  <?= $number_of_users; ?>,
                  <?= $number_of_admins; ?>,
                  
               ],
               backgroundColor: [
                  'rgba(223, 242, 51, 0.7)',
                  'rgba(100, 234, 140, 0.51)',
                  'rgba(41, 58, 245, 0.7)',
                  'rgba(255, 0, 0, 0.7)',
                  'rgba(0, 181, 63, 0.92)',
                  
               ],
               borderWidth: 1
            }]
         },
         options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
               legend: {
                  position: 'right',
               },
               tooltip: {
                  callbacks: {
                     label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
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