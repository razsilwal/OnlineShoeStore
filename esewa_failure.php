<?php
session_start();
$order_id = $_SESSION['current_order_id'] ?? '';

if($order_id){
    include 'components/connect.php';
    $update_order = $conn->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
    $update_order->execute([$order_id]);
}

unset($_SESSION['current_order_id']);
unset($_SESSION['order_transaction_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed</title>
</head>
<body>
    <div style="text-align: center; padding: 50px;">
        <h1>Payment Failed</h1>
        <p>Please try again.</p>
        <a href="checkout.php">Try Again</a>
    </div>
</body>
</html>