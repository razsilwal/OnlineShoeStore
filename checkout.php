<?php
include 'components/connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
    header('location:user_login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_items = [];
$grand_total = 0;
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id=?");
$select_cart->execute([$user_id]);
while($row = $select_cart->fetch(PDO::FETCH_ASSOC)){
    $cart_items[] = $row['name'].' ('.$row['price'].' x '.$row['quantity'].')';
    $grand_total += $row['price'] * $row['quantity'];
}
$total_products = implode(", ", $cart_items);

$message = [];

// Handle Cash on Delivery order
if(isset($_POST['order']) && $_POST['method']=='cod'){
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $address = filter_var($_POST['state'].' - '.$_POST['pin_code'], FILTER_SANITIZE_STRING);
    $method = 'cod';

    if($grand_total > 0){
        $insert_order = $conn->prepare("INSERT INTO `orders`(user_id,name,number,email,method,address,total_products,total_price,placed_on,payment_status) VALUES(?,?,?,?,?,?,?,?,NOW(),'completed')");
        $insert_order->execute([$user_id,$name,$number,$email,$method,$address,$total_products,$grand_total]);

        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id=?");
        $delete_cart->execute([$user_id]);

        $message[] = "Order placed successfully with Cash on Delivery!";
        header('refresh:2;url=orders.php');
    } else {
        $message[] = "Your cart is empty!";
    }
}

// Handle eSewa payment
if(isset($_POST['order']) && $_POST['method']=='esewa'){
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $address = filter_var($_POST['state'].' - '.$_POST['pin_code'], FILTER_SANITIZE_STRING);

    if($grand_total > 0){
        // Save order as PENDING first
        $transaction_uuid = uniqid();
        $insert_order = $conn->prepare("INSERT INTO `orders`(user_id,name,number,email,method,address,total_products,total_price,placed_on,payment_status,transaction_id) VALUES(?,?,?,?,?,?,?,?,NOW(),'pending',?)");
        $insert_order->execute([$user_id,$name,$number,$email,'esewa',$address,$total_products,$grand_total,$transaction_uuid]);
        
        $order_id = $conn->lastInsertId();
        $_SESSION['current_order_id'] = $order_id;
        $_SESSION['order_transaction_id'] = $transaction_uuid;

        // eSewa configuration - UAT Environment
        $esewa_secret_key = "8gBm/:&EnhH.1/q";
        $esewa_product_code = "EPAYTEST";
        $esewa_success_url = "http://" . $_SERVER['HTTP_HOST'] . "/shoestore/esewa_success.php";
        $esewa_failure_url = "http://" . $_SERVER['HTTP_HOST'] . "/shoestore/esewa_failure.php";
        
        // Payment breakdown as per eSewa docs
        $amount = $grand_total; // Product amount
        $tax_amount = 0; // No tax
        $product_service_charge = 0; // No service charge
        $product_delivery_charge = 0; // No delivery charge
        $total_amount = $amount + $tax_amount + $product_service_charge + $product_delivery_charge;
        
        // Signature generation - EXACT format as eSewa documentation
        $signed_field_names = "total_amount,transaction_uuid,product_code";
        $data_to_sign = "total_amount=" . $total_amount . ",transaction_uuid=" . $transaction_uuid . ",product_code=" . $esewa_product_code;
        
        $hash = hash_hmac('sha256', $data_to_sign, $esewa_secret_key, true);
        $signature = base64_encode($hash);

        // Log for debugging
        error_log("eSewa Payment Request:");
        error_log("Amount: " . $amount);
        error_log("Total Amount: " . $total_amount);
        error_log("Transaction UUID: " . $transaction_uuid);
        error_log("Data to sign: " . $data_to_sign);
        error_log("Signature: " . $signature);

        // Auto-submit eSewa form with ALL required fields
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Redirecting to eSewa...</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background: #f5f5f5; 
                    display: flex; 
                    justify-content: center; 
                    align-items: center; 
                    height: 100vh; 
                    margin: 0; 
                }
                .loading { 
                    text-align: center; 
                    background: white; 
                    padding: 40px; 
                    border-radius: 10px; 
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
                }
                .spinner { 
                    border: 4px solid #f3f3f3; 
                    border-top: 4px solid #f57224; 
                    border-radius: 50%; 
                    width: 40px; 
                    height: 40px; 
                    animation: spin 1s linear infinite; 
                    margin: 0 auto 20px; 
                }
                @keyframes spin { 
                    0% { transform: rotate(0deg); } 
                    100% { transform: rotate(360deg); } 
                }
            </style>
        </head>
        <body>
            <div class="loading">
                <div class="spinner"></div>
                <h3>Redirecting to eSewa Payment Gateway...</h3>
                <p>Please wait while we redirect you to secure payment page.</p>
            </div>
            
            <form id="esewaForm" method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
                <input type="hidden" name="amount" value="' . $amount . '">
                <input type="hidden" name="tax_amount" value="' . $tax_amount . '">
                <input type="hidden" name="total_amount" value="' . $total_amount . '">
                <input type="hidden" name="transaction_uuid" value="' . $transaction_uuid . '">
                <input type="hidden" name="product_code" value="' . $esewa_product_code . '">
                <input type="hidden" name="product_service_charge" value="' . $product_service_charge . '">
                <input type="hidden" name="product_delivery_charge" value="' . $product_delivery_charge . '">
                <input type="hidden" name="success_url" value="' . $esewa_success_url . '">
                <input type="hidden" name="failure_url" value="' . $esewa_failure_url . '">
                <input type="hidden" name="signed_field_names" value="' . $signed_field_names . '">
                <input type="hidden" name="signature" value="' . $signature . '">
            </form>
            <script>
                console.log("eSewa Payment Details:");
                console.log("Transaction UUID: ' . $transaction_uuid . '");
                console.log("Amount: ' . $amount . '");
                console.log("Total Amount: ' . $total_amount . '");
                console.log("Success URL: ' . $esewa_success_url . '");
                
                // Auto-submit after short delay
                setTimeout(function() {
                    document.getElementById("esewaForm").submit();
                }, 2000);
            </script>
        </body>
        </html>';
        exit();
    } else {
        $message[] = "Your cart is empty!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Daraz Style</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<style>
:root {
    --primary:#f57224; --primary-dark:#e0611a; --dark:#212121; --light:#fff; --border:#e0e0e0; --text:#212121;
    --success:#4CAF50; --error:#f44336; --shadow:0 2px 10px rgba(0,0,0,0.1);
}
body { font-family:'Roboto',sans-serif; background:#f7f7f7; margin:0; padding:0; color:var(--dark);}
.checkout-container { max-width:1200px; margin:30px auto; display:grid; grid-template-columns:1fr; gap:30px; padding:0 15px;}
@media(min-width:992px){ .checkout-container { grid-template-columns:2fr 1fr; } }
.checkout-card { background:var(--light); padding:25px; border-radius:8px; box-shadow:var(--shadow);}
.checkout-header { display:flex; align-items:center; gap:12px; margin-bottom:20px; border-bottom:1px solid var(--border); color:var(--dark);}
.checkout-header i{ color:var(--primary); font-size:1.3rem;}
.checkout-header h2{ margin:0; font-size:1.4rem;}
.form-group { margin-bottom:15px; }
.form-label{ display:block; font-weight:600; margin-bottom:5px; color:var(--dark);}
.form-control{ width:100%; padding:10px; border:1px solid var(--border); border-radius:4px; font-size:1rem; color:var(--dark);}
.form-control::placeholder{ color:#aaa;}
.payment-options{ display:flex; flex-direction:column; gap:10px;}
.payment-option{ display:flex; align-items:center; gap:10px; padding:10px; border:2px solid #e0e0e0; border-radius:8px; cursor:pointer; transition:all 0.3s; background:#fff;}
.payment-option.active{ border-color:var(--primary); background:rgba(245,114,36,0.05);}
.payment-option input[type="radio"]{ display:none;}
.selection-indicator{ width:20px;height:20px;border:2px solid #ccc;border-radius:50%; display:flex; align-items:center; justify-content:center;}
.payment-option.active .selection-indicator{ border-color:var(--primary);}
.checkmark{ width:12px;height:12px;background:var(--primary); border-radius:50%; opacity:0;}
.payment-option.active .checkmark{ opacity:1;}
.btn-checkout{ background:var(--primary); color:#fff; border:none; padding:12px; border-radius:4px; width:100%; cursor:pointer; font-size:1rem; display:flex; align-items:center; justify-content:center; gap:8px;}
.btn-checkout:hover{ background:var(--primary-dark);}
.order-summary{ position:sticky; top:20px;}
.order-item{ display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed var(--border);}
.price-row{ display:flex; justify-content:space-between; margin-bottom:8px;}
.total-price{ font-weight:600; font-size:1.2rem;}
.message{ padding:10px; margin-bottom:15px; border-radius:4px; display:flex; justify-content:space-between;}
.success{ background:rgba(76,175,80,0.1); border-left:4px solid var(--success); color:var(--success);}
.error{ background:rgba(244,67,54,0.1); border-left:4px solid var(--error); color:var(--error);}
</style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="checkout-container">

<?php if(!empty($message)): ?>
    <?php foreach($message as $msg): ?>
        <div class="message success"><?= $msg ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="checkout-main">
<div class="checkout-card">
<div class="checkout-header"><i class="fas fa-map-marker-alt"></i><h2>Delivery Address</h2></div>
<form method="POST" id="checkoutForm">
<div class="form-group">
<label class="form-label">Full Name</label>
<input type="text" name="name" class="form-control" placeholder="Enter full name" required>
</div>
<div class="form-group">
<label class="form-label">Phone Number</label>
<input type="tel" name="number" class="form-control" placeholder="Enter phone number" required>
</div>
<div class="form-group">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" placeholder="Enter email" required>
</div>
<div class="form-group">
<label class="form-label">State</label>
<input type="text" name="state" class="form-control" placeholder="State" required>
</div>
<div class="form-group">
<label class="form-label">Postal Code</label>
<input type="text" name="pin_code" class="form-control" placeholder="Postal code" required>
</div>
</div>

<div class="checkout-card">
  <div class="checkout-header"><i class="fas fa-credit-card"></i><h2>Payment Method</h2></div>
  <div class="payment-options">
    <label class="payment-option active">
      <input type="radio" name="method" value="cod" checked>
      <div class="option-details">
        <span style="font-weight:600; font-size:1rem; color:#212121;">Cash on Delivery</span>
        <small style="color:#555;">Pay when you receive your order</small>
      </div>
      <div class="selection-indicator"><div class="checkmark"></div></div>
    </label>

    <label class="payment-option">
      <input type="radio" name="method" value="esewa">
      <div class="option-details">
        <span style="font-weight:600; font-size:1rem; color:#212121;">eSewa</span>
        <small style="color:#555;">Pay securely with your eSewa account</small>
      </div>
      <div class="selection-indicator"><div class="checkmark"></div></div>
    </label>
  </div>
</div>


</div>

<div class="order-summary">
<div class="checkout-card">
<div class="checkout-header"><i class="fas fa-shopping-bag"></i><h2>Order Summary</h2></div>
<?php if($grand_total>0): ?>
<?php foreach($cart_items as $item): ?>
<div class="order-item">
<span><?= $item; ?></span>
</div>
<?php endforeach; ?>
<div class="price-row total-price">
<span>Total:</span><span>Rs. <?= $grand_total; ?></span>
</div>
<input type="hidden" name="total_price" value="<?= $grand_total; ?>">
<button type="submit" name="order" class="btn-checkout">Place Order</button>
<?php else: ?>
<p>Your cart is empty!</p>
<?php endif; ?>
</form>
</div>
</div>

</div>

<script>
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function(){
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
    });
});
</script>

<?php include 'components/footer.php'; ?>
</body>
</html>