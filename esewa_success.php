<?php
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';
$order_id = $_SESSION['current_order_id'] ?? '';

if (!$user_id) {
    header('Location: user_login.php');
    exit();
}

// V2 API returns data as base64 encoded JSON in 'data' parameter
if(isset($_GET['data'])) {
    $encoded_data = $_GET['data'];
    $json_data = base64_decode($encoded_data);
    $data = json_decode($json_data, true);
    
    if($data && isset($data['status']) && $data['status'] === 'COMPLETE') {
        // Payment successful
        if($order_id) {
            $update_order = $conn->prepare("UPDATE `orders` SET payment_status = 'completed' WHERE id = ? AND user_id = ?");
            $update_order->execute([$order_id, $user_id]);
            
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);
        }
        
        unset($_SESSION['current_order_id']);
        unset($_SESSION['order_transaction_id']);
        
        header('Location: orders.php?payment=success');
        exit();
    }
}

// If anything fails
header('Location: esewa_failure.php?reason=payment_failed');
exit();
?>