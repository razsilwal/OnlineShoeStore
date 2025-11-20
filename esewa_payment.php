<?php
// esewa_payment.php
include 'components/connect.php';
session_start();

// Check if user came from checkout with proper data
if(!isset($_SESSION['user_id']) || !isset($_POST['order']) || $_POST['method'] !== 'esewa') {
    header('Location: checkout.php'); 
    exit();
}

$user_id = $_SESSION['user_id'];

// Get data from checkout form
$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$address = filter_var($_POST['state'].' - '.$_POST['pin_code'], FILTER_SANITIZE_STRING);

// Fetch cart items to calculate total
$cart_items = [];
$grand_total = 0;
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id=?");
$select_cart->execute([$user_id]);
while($row = $select_cart->fetch(PDO::FETCH_ASSOC)){
    $cart_items[] = $row['name'].' ('.$row['price'].' x '.$row['quantity'].')';
    $grand_total += $row['price'] * $row['quantity'];
}
$total_products = implode(", ", $cart_items);

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
    
    // Use dynamic URLs
    $base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $esewa_success_url = $base_url . "/esewa_success.php";
    $esewa_failure_url = $base_url . "/esewa_failure.php";
    
    // Payment breakdown
    $amount = $grand_total;
    $tax_amount = 0;
    $product_service_charge = 0;
    $product_delivery_charge = 0;
    $total_amount = $amount + $tax_amount + $product_service_charge + $product_delivery_charge;
    
    // Signature generation
    $signed_field_names = "total_amount,transaction_uuid,product_code";
    $data_to_sign = "total_amount=" . $total_amount . ",transaction_uuid=" . $transaction_uuid . ",product_code=" . $esewa_product_code;
    
    $hash = hash_hmac('sha256', $data_to_sign, $esewa_secret_key, true);
    $signature = base64_encode($hash);

    // Auto-submit eSewa form with test credentials info
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
                max-width: 500px;
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
            .test-info {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 5px;
                padding: 15px;
                margin-top: 20px;
                text-align: left;
            }
            .urgent {
                color: red;
                font-weight: bold;
                background: #ffe6e6;
                padding: 5px;
                border-radius: 3px;
            }
        </style>
    </head>
    <body>
        <div class="loading">
            <div class="spinner"></div>
            <h3>Redirecting to eSewa Payment Gateway...</h3>
            <p>Please wait while we redirect you to secure payment page.</p>
            
            <div class="test-info">
                <h4>🧪 TEST CREDENTIALS:</h4>
                <p><strong>Username:</strong> 9806800000</p>
                <p><strong>MPIN:</strong> 1234</p>
                <p><strong>OTP:</strong> <span class="urgent">987654</span></p>
                <p><em>⚠️ OTP expires in 1-2 seconds! Enter immediately after MPIN</em></p>
                <p><em>💡 Tip: Have the OTP ready before entering MPIN</em></p>
            </div>
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
            console.log("Amount: ' . $amount . '");
            console.log("Transaction UUID: ' . $transaction_uuid . '");
            console.log("IMPORTANT: Use OTP 987654 immediately after MPIN!");
            
            // Auto-submit after short delay
            setTimeout(function() {
                document.getElementById("esewaForm").submit();
            }, 3000);
        </script>
    </body>
    </html>';
    exit();
} else {
    // If cart is empty, redirect back to checkout
    header('Location: checkout.php');
    exit();
}
?>