<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

// Function to place an order
function placeOrder($conn, $user_id, $name, $number, $email, $method, $address, $total_products, $total_price) {
    $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $check_cart->execute([$user_id]);

    if($check_cart->rowCount() > 0){
        $cart_items = [];
        
        while($fetch_cart = $check_cart->fetch(PDO::FETCH_ASSOC)){
            // Check if product_id exists in the cart item
            $product_id = isset($fetch_cart['product_id']) ? $fetch_cart['product_id'] : '';
            $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
        }
        
        // Modified to match your database schema (removed product_ids column)
        $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES(?,?,?,?,?,?,?,?,NOW(),'pending')");
        $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);

        return true;
    }
    return false;
}

// Handle regular order submission
if(isset($_POST['order'])){
    $name = $_POST['name'] ?? '';
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $number = $_POST['number'] ?? '';
    $number = filter_var($number, FILTER_SANITIZE_STRING);
    $email = $_POST['email'] ?? '';
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $method = $_POST['method'] ?? 'cash on delivery';
    $method = filter_var($method, FILTER_SANITIZE_STRING);
    $address = ($_POST['state'] ?? '') . ' - '. ($_POST['pin_code'] ?? '');
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    $total_products = $_POST['total_products'] ?? '';
    $total_price = $_POST['total_price'] ?? 0;

    if(placeOrder($conn, $user_id, $name, $number, $email, $method, $address, $total_products, $total_price)){
        $message[] = 'Order placed successfully!';
        header('location:orders.php');
        exit();
    }else{
        $message[] = 'Your cart is empty';
    }
}

// Handle Esewa payment
if(isset($_POST['esewa_payment'])){
    $esewa_username = $_POST['esewa_username'] ?? '';
    $esewa_password = $_POST['esewa_password'] ?? '';
    
    $valid_username = 'esewa_user';
    $valid_password = 'esewa123';
    
    if($esewa_username === $valid_username && $esewa_password === $valid_password){
        // Get all form data from hidden inputs
        $name = $_POST['name'] ?? '';
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $number = $_POST['number'] ?? '';
        $number = filter_var($number, FILTER_SANITIZE_STRING);
        $email = $_POST['email'] ?? '';
        $email = filter_var($email, FILTER_SANITIZE_STRING);
        $address = ($_POST['state'] ?? '') . ' - '. ($_POST['pin_code'] ?? '');
        $address = filter_var($address, FILTER_SANITIZE_STRING);
        $total_products = $_POST['total_products'] ?? '';
        $total_price = $_POST['total_price'] ?? 0;
        $method = 'esewa';
        
        if(placeOrder($conn, $user_id, $name, $number, $email, $method, $address, $total_products, $total_price)){
            $message[] = 'Order placed successfully with Esewa!';
            header('location:orders.php');
            exit();
        }else{
            $message[] = 'Your cart is empty';
        }
    } else {
        $message[] = 'Invalid Esewa credentials. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout - Daraz Style</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
      :root {
         --primary: #f57224;
         --primary-dark: #e0611a;
         --secondary: #2874f0;
         --dark: #212121;
         --light: #ffffff;
         --gray: #f5f5f5;
         --border: #e0e0e0;
         --text: #555555;
         --success: #4CAF50;
         --error: #f44336;
         --shadow: 0 2px 10px rgba(0,0,0,0.1);
      }
      
      body {
         background-color: #f7f7f7;
         font-family: 'Roboto', sans-serif;
         color: var(--dark);
         line-height: 1.6;
      }
      
      .checkout-container {
         max-width: 1200px;
         margin: 30px auto;
         padding: 0 20px;
         display: grid;
         grid-template-columns: 1fr;
         gap: 30px;
      }
      
      @media (min-width: 992px) {
         .checkout-container {
            grid-template-columns: 2fr 1fr;
         }
      }
      
      /* Checkout Cards */
      .checkout-card {
         background: var(--light);
         border-radius: 8px;
         box-shadow: var(--shadow);
         padding: 25px;
         margin-bottom: 25px;
      }
      
      .checkout-header {
         display: flex;
         align-items: center;
         padding-bottom: 15px;
         margin-bottom: 20px;
         border-bottom: 1px solid var(--border);
         color: var(--dark);
      }
      
      .checkout-header i {
         margin-right: 12px;
         color: var(--primary);
         font-size: 1.2rem;
      }
      
      .checkout-header h2 {
         font-size: 1.4rem;
         font-weight: 600;
         margin: 0;
      }
      
      /* Form Styles */
      .form-group {
         margin-bottom: 20px;
      }
      
      .form-label {
         display: block;
         margin-bottom: 8px;
         font-size: 0.95rem;
         font-weight: 500;
         color: var(--dark);
      }
      
      .form-control {
         width: 100%;
         padding: 12px 15px;
         border: 1px solid var(--border);
         border-radius: 4px;
         font-size: 1rem;
         transition: all 0.3s ease;
      }
      
      .form-control:focus {
         border-color: var(--primary);
         outline: none;
         box-shadow: 0 0 0 2px rgba(245, 114, 36, 0.2);
      }
      
      .address-fields {
         display: grid;
         grid-template-columns: 1fr;
         gap: 15px;
      }
      
      @media (min-width: 768px) {
         .address-fields {
            grid-template-columns: 1fr 1fr;
         }
      }
      
      /* Payment Methods */
      .payment-options {
         display: flex;
         flex-direction: column;
         gap: 12px;
      }
      
      .payment-option {
         display: flex;
         align-items: center;
         padding: 15px;
         border: 1px solid var(--border);
         border-radius: 6px;
         cursor: pointer;
         transition: all 0.3s ease;
      }
      
      .payment-option:hover {
         border-color: var(--primary);
      }
      
      .payment-option.active {
         border-color: var(--primary);
         background-color: rgba(245, 114, 36, 0.05);
      }
      
      .payment-option input {
         margin-right: 12px;
         accent-color: var(--primary);
      }
      
      .payment-icon {
         margin-right: 10px;
         font-size: 1.2rem;
         color: var(--primary);
      }
      
      /* Order Summary */
      .order-summary {
         position: sticky;
         top: 20px;
      }
      
      .order-items {
         max-height: 250px;
         overflow-y: auto;
         margin-bottom: 20px;
         padding-bottom: 15px;
         border-bottom: 1px solid var(--border);
      }
      
      .order-item {
         display: flex;
         justify-content: space-between;
         padding: 12px 0;
         border-bottom: 1px dashed var(--border);
      }
      
      .order-item:last-child {
         border-bottom: none;
      }
      
      .price-summary {
         margin-top: 20px;
      }
      
      .price-row {
         display: flex;
         justify-content: space-between;
         margin-bottom: 12px;
         font-size: 0.95rem;
      }
      
      .total-price {
         font-size: 1.2rem;
         font-weight: 600;
         padding-top: 15px;
         margin-top: 15px;
         border-top: 1px solid var(--border);
      }
      
      /* Buttons */
      .btn-checkout {
         width: 100%;
         background-color: var(--primary);
         color: white;
         border: none;
         border-radius: 4px;
         padding: 14px;
         font-size: 1rem;
         font-weight: 500;
         cursor: pointer;
         margin-top: 20px;
         transition: all 0.3s ease;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
      }
      
      .btn-checkout:hover {
         background-color: var(--primary-dark);
         transform: translateY(-2px);
      }
      
      .btn-checkout.disabled {
         background-color: #cccccc;
         cursor: not-allowed;
         transform: none;
      }
      
      /* Modal Styles */
      .modal {
         display: none;
         position: fixed;
         z-index: 1000;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0,0,0,0.5);
         backdrop-filter: blur(3px);
      }
      
      .modal-content {
         background-color: var(--light);
         margin: 10% auto;
         padding: 30px;
         width: 100%;
         max-width: 450px;
         border-radius: 8px;
         box-shadow: 0 5px 20px rgba(0,0,0,0.2);
         animation: modalFadeIn 0.3s ease-out;
      }
      
      @keyframes modalFadeIn {
         from { opacity: 0; transform: translateY(-20px); }
         to { opacity: 1; transform: translateY(0); }
      }
      
      .modal-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 20px;
         padding-bottom: 15px;
         border-bottom: 1px solid var(--border);
      }
      
      .modal-header h3 {
         margin: 0;
         color: var(--primary);
         font-size: 1.3rem;
      }
      
      .close {
         color: #aaa;
         font-size: 1.5rem;
         font-weight: bold;
         cursor: pointer;
         transition: all 0.2s;
      }
      
      .close:hover {
         color: var(--dark);
      }
      
      .esewa-form .form-group {
         margin-bottom: 15px;
      }
      
      .btn-esewa {
         width: 100%;
         background-color: #5cb85c;
         color: white;
         border: none;
         padding: 14px;
         border-radius: 4px;
         font-size: 1rem;
         font-weight: 500;
         cursor: pointer;
         margin-top: 15px;
         transition: all 0.3s;
      }
      
      .btn-esewa:hover {
         background-color: #4cae4c;
      }
      
      .esewa-logo {
         text-align: center;
         margin-bottom: 20px;
      }
      
      .esewa-logo img {
         height: 40px;
      }
      
      .demo-credentials {
         margin-top: 20px;
         padding: 15px;
         background-color: var(--gray);
         border-radius: 6px;
         font-size: 0.9rem;
         text-align: center;
      }
      
      /* Messages */
      .message {
         padding: 15px;
         margin-bottom: 20px;
         border-radius: 4px;
         display: flex;
         align-items: center;
         justify-content: space-between;
         animation: slideDown 0.3s ease-out;
      }
      
      @keyframes slideDown {
         from { opacity: 0; transform: translateY(-20px); }
         to { opacity: 1; transform: translateY(0); }
      }
      
      .message.success {
         background-color: rgba(76, 175, 80, 0.1);
         color: var(--success);
         border-left: 4px solid var(--success);
      }
      
      .message.error {
         background-color: rgba(244, 67, 54, 0.1);
         color: var(--error);
         border-left: 4px solid var(--error);
      }
      
      .message i {
         cursor: pointer;
         margin-left: 10px;
      }
      
      /* Responsive Adjustments */
      @media (max-width: 768px) {
         .checkout-container {
            padding: 0 15px;
         }
         
         .checkout-card {
            padding: 20px;
         }
         
         .modal-content {
            margin: 20% auto;
            padding: 20px;
         }
      }

      /* another  */
      /* Payment Method Section */
.payment-method {
   background: #ffffff;
   border-radius: 12px;
   box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
   padding: 1.5rem;
   margin-bottom: 2rem;
}

.checkout-header {
   display: flex;
   align-items: center;
   gap: 1rem;
   margin-bottom: 1.5rem;
}

.checkout-header i {
   font-size: 1.5rem;
   color: #3498db;
}

.checkout-header h2 {
   font-size: 1.5rem;
   color: #2c3e50;
   margin: 0;
}

.payment-options {
   display: flex;
   flex-direction: column;
   gap: 1rem;
}

.payment-option {
   display: block;
   position: relative;
   cursor: pointer;
   border: 2px solid #e0e0e0;
   border-radius: 10px;
   padding: 1rem;
   transition: all 0.3s ease;
}

.payment-option:hover {
   border-color: #bdc3c7;
}

.payment-option.active {
   border-color: #3498db;
   background-color: #f8fafc;
}

.option-content {
   display: flex;
   align-items: center;
   gap: 1rem;
}

.option-icon {
   width: 50px;
   height: 50px;
   background: #f1f5f9;
   border-radius: 8px;
   display: flex;
   align-items: center;
   justify-content: center;
}

.option-icon i {
   font-size: 1.5rem;
   color: #3498db;
}

.option-details {
   flex: 1;
}

.option-details h4 {
   margin: 0 0 0.25rem 0;
   color: #2c3e50;
   font-size: 1.1rem;
}

.option-details p {
   margin: 0;
   color: #7f8c8d;
   font-size: 0.9rem;
}

.selection-indicator {
   width: 24px;
   height: 24px;
   border: 2px solid #bdc3c7;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   transition: all 0.3s ease;
}

.payment-option.active .selection-indicator {
   border-color: #3498db;
}

.checkmark {
   width: 12px;
   height: 12px;
   background: #3498db;
   border-radius: 50%;
   opacity: 0;
   transition: opacity 0.3s ease;
}

.payment-option.active .checkmark {
   opacity: 1;
}

/* Hide the default radio button */
.payment-option input[type="radio"] {
   position: absolute;
   opacity: 0;
   width: 0;
   height: 0;
}
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-form">
   <?php
      if(isset($message)){
         foreach($message as $msg){
            $isSuccess = strpos($msg, 'successfully') !== false;
            $class = $isSuccess ? 'message success' : 'message error';
            echo '
            <div class="'.$class.'">
               <span>'.$msg.'</span>
               <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
            </div>
            ';
         }
      }
   ?>
</section>

<div class="checkout-container">
   <div class="checkout-main">
      <div class="checkout-card delivery-address">
         <div class="checkout-header">
            <i class="fas fa-map-marker-alt"></i>
            <h2>Delivery Address</h2>
         </div>
         <form action="" method="POST" id="checkoutForm">
            <div class="form-group">
               <label class="form-label">Full Name</label>
               <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group">
               <label class="form-label">Phone Number</label>
               <input type="tel" name="number" class="form-control" placeholder="Enter your phone number" required>
            </div>
            
            <div class="form-group">
               <label class="form-label">Email Address</label>
               <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            
            <div class="address-fields">
               <div class="form-group">
                  <label class="form-label">State</label>
                  <input type="text" name="state" class="form-control" placeholder="Your state" required>
               </div>
               
               <div class="form-group">
                  <label class="form-label">Postal Code</label>
                  <input type="text" name="pin_code" class="form-control" placeholder="Postal code" required>
               </div>
            </div>
      </div>
      
      <div class="checkout-card payment-method">
         <div class="checkout-header">
            <i class="fas fa-credit-card"></i>
            <h2>Payment Method</h2>
         </div>
         
         <div class="payment-options">
            <label class="payment-option active">
               <input type="radio" name="method" value="cash on delivery" checked>
               <div class="option-content">
                  <div class="option-icon">
                     <i class="fas fa-money-bill-wave"></i>
                  </div>
                  <div class="option-details">
                     <h4>Cash on Delivery</h4>
                     <p>Pay when you receive your order</p>
                  </div>
                  <div class="selection-indicator">
                     <div class="checkmark"></div>
                  </div>
               </div>
            </label>
            
            <label class="payment-option">
               <input type="radio" name="method" value="esewa">
               <div class="option-content">
                  <div class="option-icon">
                     <i class="fas fa-wallet"></i>
                  </div>
                  <div class="option-details">
                     <h4>Esewa</h4>
                     <p>Pay securely with your Esewa account</p>
                  </div>
                  <div class="selection-indicator">
                     <div class="checkmark"></div>
                  </div>
               </div>
            </label>
         </div>
      </div>
   </div>
   
   <div class="order-summary">
      <div class="checkout-card">
         <div class="checkout-header">
            <i class="fas fa-shopping-bag"></i>
            <h2>Order Summary</h2>
         </div>
         
         <div class="order-items">
         <?php
            $grand_total = 0;
            $cart_items = [];
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
                  $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
         ?>
            <div class="order-item">
               <span><?= $fetch_cart['name']; ?> × <?= $fetch_cart['quantity']; ?></span>
               <span>₹<?= $fetch_cart['price'] * $fetch_cart['quantity']; ?></span>
            </div>
         <?php
               }
               $total_products = implode($cart_items);
            }else{
               echo '<p class="empty">Your cart is empty!</p>';
            }
         ?>
         </div>
         
         <input type="hidden" name="total_products" value="<?= isset($total_products) ? $total_products : ''; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         
         <div class="price-summary">
            <div class="price-row">
               <span>Subtotal</span>
               <span>Nrs. <?= $grand_total; ?></span>
            </div>
            <div class="price-row">
               <span>Delivery Fee</span>
               <span>NRs. 0</span>
            </div>
            <div class="price-row total-price">
               <span>Total</span>
               <span>NRs. <?= $grand_total; ?></span>
            </div>
         </div>
         
         <button type="submit" name="order" class="btn-checkout <?= ($grand_total > 1)?'':'disabled'; ?>">
            <i class="fas fa-shopping-bag"></i>
            PLACE ORDER
         </button>
         </form>
      </div>
   </div>
</div>

<!-- Esewa Payment Modal -->
<div id="esewaModal" class="modal">
   <div class="modal-content">
      <div class="modal-header">
         <h3>Esewa Payment</h3>
         <span class="close">&times;</span>
      </div>
      
      <div class="esewa-logo">
         <img src="https://esewa.com.np/common/images/esewa_logo.png" alt="Esewa Logo">
      </div>
      
      <form class="esewa-form" method="POST">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         <input type="hidden" name="total_products" value="<?= isset($total_products) ? $total_products : ''; ?>">
         <input type="hidden" name="name" value="<?= isset($_POST['name']) ? $_POST['name'] : ''; ?>">
         <input type="hidden" name="number" value="<?= isset($_POST['number']) ? $_POST['number'] : ''; ?>">
         <input type="hidden" name="email" value="<?= isset($_POST['email']) ? $_POST['email'] : ''; ?>">
         <input type="hidden" name="state" value="<?= isset($_POST['state']) ? $_POST['state'] : ''; ?>">
         <input type="hidden" name="pin_code" value="<?= isset($_POST['pin_code']) ? $_POST['pin_code'] : ''; ?>">
         
         <div class="form-group">
            <label class="form-label">Esewa Username</label>
            <input type="text" name="esewa_username" class="form-control" placeholder="Enter your Esewa username" required>
         </div>
         
         <div class="form-group">
            <label class="form-label">Esewa Password</label>
            <input type="password" name="esewa_password" class="form-control" placeholder="Enter your Esewa password" required>
         </div>
         
         <div class="form-group">
            <label class="form-label">Amount to Pay</label>
            <input type="text" class="form-control" value="NRs. <?= $grand_total; ?>" readonly>
         </div>
         
         <button type="submit" name="esewa_payment" class="btn-esewa">
            <i class="fas fa-lock"></i> PAY WITH ESEWA
         </button>
         
         <div class="demo-credentials">
            <p><strong>Demo Credentials:</strong></p>
            <p>Username: <strong>esewa_user</strong></p>
            <p>Password: <strong>esewa123</strong></p>
         </div>
      </form>
   </div>
</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>


<script>
   document.addEventListener('DOMContentLoaded', function() {
      // Payment method selection
      document.querySelectorAll('.payment-option').forEach(option => {
         option.addEventListener('click', function() {
            document.querySelectorAll('.payment-option').forEach(opt => {
               opt.classList.remove('active');
            });
            this.classList.add('active');
            this.querySelector('input').checked = true;
         });
      });
      
      // Esewa modal functionality
      const esewaRadio = document.querySelector('input[value="esewa"]');
      const esewaModal = document.getElementById('esewaModal');
      const closeBtn = document.querySelector('.close');
      const placeOrderBtn = document.querySelector('button[name="order"]');
      
      placeOrderBtn.addEventListener('click', function(e) {
         if(esewaRadio.checked) {
            e.preventDefault();
            
            // Collect all form data and populate hidden fields in modal
            document.querySelector('#esewaModal input[name="name"]').value = 
               document.querySelector('input[name="name"]').value;
            document.querySelector('#esewaModal input[name="number"]').value = 
               document.querySelector('input[name="number"]').value;
            document.querySelector('#esewaModal input[name="email"]').value = 
               document.querySelector('input[name="email"]').value;
            document.querySelector('#esewaModal input[name="state"]').value = 
               document.querySelector('input[name="state"]').value;
            document.querySelector('#esewaModal input[name="pin_code"]').value = 
               document.querySelector('input[name="pin_code"]').value;
            
            esewaModal.style.display = 'block';
         }
      });
      
      closeBtn.addEventListener('click', function() {
         esewaModal.style.display = 'none';
      });
      
      window.addEventListener('click', function(e) {
         if(e.target === esewaModal) {
            esewaModal.style.display = 'none';
         }
      });
      
      // Form validation on submit
      const form = document.getElementById('checkoutForm');
      form.addEventListener('submit', function(e) {
         if(!esewaRadio.checked) {
            // Validate regular form submission
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
               if(!field.value.trim()) {
                  field.style.borderColor = 'var(--error)';
                  isValid = false;
               } else {
                  field.style.borderColor = 'var(--border)';
               }
            });
            
            if(!isValid) {
               e.preventDefault();
               alert('Please fill in all required fields');
            }
         }
      });
   });
</script>
</body>
</html>