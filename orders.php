<?php
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';

// ======= OOP CLASS FOR ORDERS =======
class OrderHandler {
    private $conn;
    private $user_id;

    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    // Get all orders for the user
    public function getOrders() {
        $stmt = $this->conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY placed_on DESC");
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get order count by status
    public function getOrderCounts() {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM `orders` 
            WHERE user_id = ?
        ");
        $stmt->execute([$this->user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete order
    public function deleteOrder($order_id) {
        $stmt = $this->conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $this->user_id]);
        if($stmt->rowCount() > 0) {
            $del = $this->conn->prepare("DELETE FROM `orders` WHERE id = ?");
            $del->execute([$order_id]);
            return ['status'=>'success', 'message'=>'Order deleted successfully!'];
        } else {
            return ['status'=>'error', 'message'=>'Order not found!'];
        }
    }
}

// ======= HANDLE ORDER OPERATIONS =======
$orderHandler = new OrderHandler($conn, $user_id);
$message = [];

if(isset($_POST['delete_order'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_STRING);
    $result = $orderHandler->deleteOrder($order_id);
    $message[] = $result['message'];
}

// Check for payment success message
if(isset($_GET['payment']) && $_GET['payment'] == 'success') {
    $message[] = "Payment completed successfully! Your order has been confirmed.";
}

// Fetch orders and counts
$orders = $user_id ? $orderHandler->getOrders() : [];
$orderCounts = $user_id ? $orderHandler->getOrderCounts() : ['total' => 0, 'completed' => 0, 'pending' => 0, 'failed' => 0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders | Kickster</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
        :root {
            --primary: #f57224;
            --primary-dark: #e0611a;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --border: #dee2e6;
            --shadow: 0 2px 15px rgba(0,0,0,0.1);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Messages */
        .messages-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .message {
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.3s ease;
            box-shadow: var(--shadow);
        }

        .message.success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 4px solid var(--success);
            color: #155724;
        }

        .message.error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 4px solid var(--danger);
            color: #721c24;
        }

        .message i {
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .message i:hover {
            opacity: 1;
        }

        /* Orders Section */
        .orders-section {
            padding: 120px 20px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .orders-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary), #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .orders-subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .stat-card.total { border-top: 4px solid var(--primary); }
        .stat-card.completed { border-top: 4px solid var(--success); }
        .stat-card.pending { border-top: 4px solid var(--warning); }
        .stat-card.failed { border-top: 4px solid var(--danger); }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-card.total .stat-icon { color: var(--primary); }
        .stat-card.completed .stat-icon { color: var(--success); }
        .stat-card.pending .stat-icon { color: var(--warning); }
        .stat-card.failed .stat-icon { color: var(--danger); }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: black; 
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Orders Container */
        .orders-container {
            display: grid;
            gap: 25px;
        }

        /* Order Card */
        .order-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 25px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--light);
    gap: 20px;
}

.order-info {
    flex: 1;
}

.order-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.order-title i {
    color: var(--primary);
}

.order-meta-info {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.order-items, .order-date {
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.order-amount {
    text-align: right;
    min-width: 150px;
}

.amount-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.amount-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

/* Remove the old order-price class since we moved it to header */
.order-price {
    display: none;
}

/* Responsive */
@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .order-amount {
        text-align: left;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .amount-label {
        margin-bottom: 0;
    }
}

        .order-id {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
        }

        .order-date {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .order-details {
            margin-bottom: 20px;
        }

        .order-product {
            padding: 8px 12px;
            background: var(--light);
            border-radius: 6px;
            margin-bottom: 8px;
            border-left: 3px solid var(--primary);
            color: black;
        }

        .order-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
            text-align: right;
        }

        .order-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-status {
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .status-completed { background: #d4edda; color: var(--success); }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-failed { background: #f8d7da; color: var(--danger); }

        .order-payment {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .order-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .order-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .order-btn-primary {
            background: var(--primary);
            color: white;
        }

        .order-btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .order-btn-secondary {
            background: #6c757d;
            color: white;
        }

        .order-btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .order-btn-danger {
            background: var(--danger);
            color: white;
        }

        .order-btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-orders, .login-prompt {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .empty-icon, .login-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .empty-title, .login-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .empty-description, .login-prompt p {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .empty-action, .login-link {
            display: inline-block;
            padding: 12px 30px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-action:hover, .login-link:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .orders-title {
                font-size: 2rem;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-meta {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-actions {
                width: 100%;
                justify-content: center;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
   </style>
</head>
<body>
    <?php include 'components/user_header.php'; ?>

    <!-- Display messages -->
    <?php if(isset($message) && !empty($message)): ?>
        <div class="messages-container">
            <?php foreach($message as $msg): ?>
                <div class="message <?= strpos($msg, 'successfully') !== false ? 'success' : 'error' ?>">
                    <span><?= htmlspecialchars($msg) ?></span>
                    <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <section class="orders-section">
       <div class="container">
          <h1 class="orders-title">My Orders</h1>
          <p class="orders-subtitle">Track and manage all your orders in one place</p>

          <?php if($user_id && !empty($orders)): ?>
          <!-- Stats Cards -->
          <div class="stats-container">
             <div class="stat-card total">
                <div class="stat-icon">
                   <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-number"><?= $orderCounts['total'] ?></div>
                <div class="stat-label">Total Orders</div>
             </div>
             
             <div class="stat-card completed">
                <div class="stat-icon">
                   <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?= $orderCounts['completed'] ?></div>
                <div class="stat-label">Completed</div>
             </div>
             
             <div class="stat-card pending">
                <div class="stat-icon">
                   <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?= $orderCounts['pending'] ?></div>
                <div class="stat-label">Pending</div>
             </div>
             
             <div class="stat-card failed">
                <div class="stat-icon">
                   <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?= $orderCounts['failed'] ?></div>
                <div class="stat-label">Failed</div>
             </div>
          </div>
          <?php endif; ?>

          <div class="orders-container">
          <?php
             if($user_id == '') {
                echo '
                <div class="login-prompt">
                   <div class="login-icon"><i class="fas fa-user-lock"></i></div>
                   <h2 class="login-title">Please <a href="user_login.php" class="login-link">login</a> to view your orders</h2>
                   <p>Access your order history, track shipments, and manage your purchases</p>
                </div>
                ';
             } elseif(empty($orders)) {
                echo '
                <div class="empty-orders">
                   <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                   <h2 class="empty-title">No Orders Yet</h2>
                   <p class="empty-description">Start your shopping journey with us! Discover amazing products and great deals.</p>
                   <a href="shop.php" class="empty-action">
                      <i class="fas fa-shopping-cart"></i> Start Shopping
                   </a>
                </div>
                ';
             } else {
                foreach($orders as $fetch_orders):
                    $status = strtolower($fetch_orders['payment_status']);
                    $status_class = 'status-' . $status;
                    $products = explode(', ', $fetch_orders['total_products']);
                    $order_date = date('M d, Y - h:i A', strtotime($fetch_orders['placed_on']));
                    $payment_method = ucfirst($fetch_orders['method']);
          ?>
          <div class="order-card">
             <div class="order-header">
    <div class="order-info">
        <div class="order-title">
            <i class="fas fa-receipt"></i>
            Your Order
        </div>
        <div class="order-meta-info">
            <span class="order-items">
                <i class="fas fa-cube"></i>
                <?= count(array_filter($products)) ?> item<?= count(array_filter($products)) != 1 ? 's' : '' ?>
            </span>
            <span class="order-date">
                <i class="fas fa-calendar"></i>
                <?= $order_date ?>
            </span>
        </div>
    </div>
    <div class="order-amount">
        <div class="amount-label">Total Amount</div>
        <div class="amount-value">Rs. <?= number_format($fetch_orders['total_price'], 2) ?></div>
    </div>
</div>

             <div class="order-details">
                <?php foreach($products as $product): ?>
                    <?php if(!empty(trim($product))): ?>
                        <div class="order-product">
                           <i class="fas fa-cube"></i> <?= htmlspecialchars($product) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
             </div>

             <div class="order-price">Total: Rs. <?= number_format($fetch_orders['total_price'], 2) ?></div>

             <div class="order-meta">
                <div class="order-status <?= $status_class ?>">
                   <i class="fas fa-<?= 
                      $status == 'completed' ? 'check-circle' : 
                      ($status == 'pending' ? 'clock' : 
                      ($status == 'failed' ? 'times-circle' : 'info-circle'))
                   ?>"></i>
                   <?= ucfirst($status) ?>
                </div>
                <div class="order-payment">
                   <i class="fas fa-credit-card"></i> Paid via <?= $payment_method ?>
                </div>
             </div>

             <div class="order-actions">
                <a href="shop.php" class="order-btn order-btn-primary">
                    <i class="fas fa-shopping-cart"></i> Shop Again
                </a>
                <a href="contact.php" class="order-btn order-btn-secondary">
                    <i class="fas fa-headset"></i> Support
                </a>
                <?php if($status == 'pending'): ?>
                <form method="POST" style="display: inline;">
                   <input type="hidden" name="order_id" value="<?= $fetch_orders['id'] ?>">
                   <button type="submit" name="delete_order" class="order-btn order-btn-danger" onclick="return confirm('Are you sure you want to cancel this order?');">
                       <i class="fas fa-trash"></i> Cancel Order
                   </button>
                </form>
                <?php endif; ?>
             </div>
          </div>
          <?php
                endforeach;
             }
          ?>
          </div>
       </div>
    </section>

    <script>
        // Auto-close messages
        const messages = document.querySelectorAll('.message');
        messages.forEach(msg => {
            setTimeout(() => { 
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-20px)';
                setTimeout(() => msg.remove(), 300);
            }, 5000);
        });

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const orderCards = document.querySelectorAll('.order-card');
            orderCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'slideIn 0.5s ease forwards';
                card.style.opacity = '0';
            });
        });
    </script>
</body>
</html>