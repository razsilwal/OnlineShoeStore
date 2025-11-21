<?php
include 'components/connect.php';
include 'components/CartHandler.php';
session_start();

if(!isset($_SESSION['user_id'])){
   header('location:user_login.php');
   exit;
}

$user_id = $_SESSION['user_id'];

// Initialize OOP Cart Handler
$cartHandler = new CartHandler($conn, $user_id);

// Delete single cart item
if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $cartHandler->deleteItem($cart_id);
   header('location:cart.php'); // Prevent form resubmission
   exit;
}

// Delete all cart items
if(isset($_GET['delete_all'])){
   $cartHandler->deleteAllItems();
   header('location:cart.php');
   exit;
}

// Update quantity
if(isset($_POST['update_qty'])){
   $cartHandler->updateQuantity($_POST['cart_id'], $_POST['qty']);
   header('location:cart.php'); // Prevent form resubmission
   exit;
}

// Get cart count for display
$select_cart = $cartHandler->getUserCart();
$item_count = $select_cart->rowCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart | YourStore</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* Cart Page Styles */
      .shopping-cart {
         max-width: 1200px;
         margin: 2rem auto;
         padding: 0 1.5rem;
      }
      
      .cart-header {
         text-align: center;
         margin-bottom: 3rem;
         padding: 2rem;
         background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
         border-radius: 12px;
         box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      }
      
      .cart-header h3 {
         font-size: 2.8rem;
         color: #2c3e50;
         margin-bottom: 0.5rem;
         font-weight: 700;
      }
      
      .item-count {
         font-size: 1.3rem;
         color: #7f8c8d;
         font-weight: 500;
      }
      
      .cart-container {
         display: grid;
         grid-template-columns: 1fr 400px;
         gap: 3rem;
         align-items: start;
      }
      
      .cart-items {
         display: flex;
         flex-direction: column;
         gap: 1.5rem;
      }
      
      .cart-item {
         display: grid;
         grid-template-columns: 150px 1fr auto;
         gap: 1.5rem;
         background: #fff;
         padding: 1.5rem;
         border-radius: 12px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.08);
         transition: transform 0.3s ease, box-shadow 0.3s ease;
         border: 1px solid #e9ecef;
      }
      
      .cart-item:hover {
         transform: translateY(-3px);
         box-shadow: 0 8px 25px rgba(0,0,0,0.12);
      }
      
      .cart-item-img {
         width: 150px;
         height: 150px;
         object-fit: cover;
         border-radius: 8px;
         border: 1px solid #e9ecef;
      }
      
      .cart-item-details {
         display: flex;
         flex-direction: column;
         gap: 0.8rem;
      }
      
      .cart-item-title {
         font-size: 1.5rem;
         font-weight: 600;
         color: #2c3e50;
         text-decoration: none;
         line-height: 1.3;
         transition: color 0.3s ease;
      }
      
      .cart-item-title:hover {
         color: #3498db;
      }
      
      .cart-item-price {
         font-size: 1.4rem;
         font-weight: 700;
         color: #27ae60;
      }
      
      .stock-status {
         font-size: 1rem;
         color: #27ae60;
         font-weight: 500;
      }
      
      .shipping-info {
         font-size: 1rem;
         color: #3498db;
         font-weight: 500;
      }
      
      .cart-item-actions {
         display: flex;
         flex-direction: column;
         gap: 1rem;
         min-width: 200px;
      }
      
      .qty-control {
         display: flex;
         align-items: center;
         gap: 0.8rem;
         margin-bottom: 1rem;
      }
      
      .qty-control label {
         font-size: 1.1rem;
         color: #2c3e50;
         font-weight: 500;
      }
      
      .qty-control input {
         width: 80px;
         padding: 10px 12px;
         border: 2px solid #e9ecef;
         border-radius: 6px;
         font-size: 1.1rem;
         text-align: center;
         transition: border-color 0.3s ease;
      }
      
      .qty-control input:focus {
         border-color: #3498db;
         outline: none;
      }
      
      .update-btn {
         background: #3498db;
         color: white;
         border: none;
         border-radius: 6px;
         width: 40px;
         height: 40px;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         transition: background 0.3s ease;
      }
      
      .update-btn:hover {
         background: #2980b9;
      }
      
      .sub-total {
         font-size: 1.3rem;
         font-weight: 600;
         color: #2c3e50;
         margin: 0.5rem 0;
      }
      
      .delete-btn {
         background: #e74c3c;
         color: white;
         border: none;
         border-radius: 6px;
         padding: 12px 20px;
         font-size: 1.1rem;
         font-weight: 600;
         cursor: pointer;
         transition: background 0.3s ease;
         display: flex;
         align-items: center;
         gap: 0.5rem;
         justify-content: center;
      }
      
      .delete-btn:hover {
         background: #c0392b;
      }
      
      .cart-summary {
         background: #fff;
         padding: 2rem;
         border-radius: 12px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.08);
         border: 1px solid #e9ecef;
         position: sticky;
         top: 2rem;
      }
      
      .summary-title {
         font-size: 1.8rem;
         color: #2c3e50;
         margin-bottom: 1.5rem;
         font-weight: 700;
         text-align: center;
      }
      
      .summary-details {
         margin-bottom: 1.5rem;
         padding-bottom: 1.5rem;
         border-bottom: 2px solid #e9ecef;
      }
      
      .summary-row {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 1rem;
      }
      
      .summary-label {
         font-size: 1.2rem;
         color: #7f8c8d;
         font-weight: 500;
      }
      
      .summary-value {
         font-size: 1.2rem;
         color: #2c3e50;
         font-weight: 600;
      }
      
      .grand-total {
         margin-bottom: 2rem;
         padding-top: 1rem;
         border-top: 2px solid #e9ecef;
      }
      
      .grand-total .summary-row {
         font-size: 1.5rem;
         font-weight: 700;
         color: #2c3e50;
      }
      
      .checkout-btn {
         display: block;
         width: 100%;
         background: #27ae60;
         color: white;
         text-align: center;
         padding: 16px;
         border-radius: 8px;
         font-size: 1.3rem;
         font-weight: 600;
         text-decoration: none;
         transition: background 0.3s ease;
         margin-bottom: 1rem;
      }
      
      .checkout-btn:hover {
         background: #219a52;
      }
      
      .checkout-btn.disabled {
         background: #bdc3c7;
         cursor: not-allowed;
      }
      
      .secondary-btn {
         display: block;
         width: 100%;
         background: #3498db;
         color: white;
         text-align: center;
         padding: 14px;
         border-radius: 8px;
         font-size: 1.2rem;
         font-weight: 600;
         text-decoration: none;
         transition: background 0.3s ease;
         margin-bottom: 1rem;
      }
      
      .secondary-btn:hover {
         background: #2980b9;
      }
      
      .empty-cart {
         text-align: center;
         padding: 4rem 2rem;
         background: #fff;
         border-radius: 12px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.08);
         grid-column: 1 / -1;
      }
      
      .empty-cart-icon {
         font-size: 5rem;
         color: #bdc3c7;
         margin-bottom: 2rem;
      }
      
      .empty-cart h3 {
         font-size: 2.2rem;
         color: #2c3e50;
         margin-bottom: 1rem;
         font-weight: 600;
      }
      
      .empty-cart-text {
         font-size: 1.3rem;
         color: #7f8c8d;
         margin-bottom: 2.5rem;
      }
      
      .shop-now-btn {
         display: inline-block;
         background: #3498db;
         color: white;
         padding: 14px 35px;
         border-radius: 8px;
         font-size: 1.3rem;
         font-weight: 600;
         text-decoration: none;
         transition: background 0.3s ease;
      }
      
      .shop-now-btn:hover {
         background: #2980b9;
      }
      
      /* Responsive Design */
      @media (max-width: 1024px) {
         .cart-container {
            grid-template-columns: 1fr;
            gap: 2rem;
         }
         
         .cart-summary {
            position: static;
         }
      }
      
      @media (max-width: 768px) {
         .cart-item {
            grid-template-columns: 1fr;
            text-align: center;
         }
         
         .cart-item-img {
            width: 100%;
            max-width: 200px;
            margin: 0 auto;
         }
         
         .cart-header h3 {
            font-size: 2.2rem;
         }
         
         .cart-item-title {
            font-size: 1.3rem;
         }
         
         .cart-item-price {
            font-size: 1.2rem;
         }
      }
      
      @media (max-width: 480px) {
         .shopping-cart {
            padding: 0 1rem;
         }
         
         .cart-header {
            padding: 1.5rem;
         }
         
         .cart-header h3 {
            font-size: 1.8rem;
         }
         
         .cart-summary {
            padding: 1.5rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="shopping-cart">
   <div class="cart-header">
      <h3>Shopping Cart</h3>
      <div class="item-count"><?= $item_count ?> item<?= $item_count != 1 ? 's' : '' ?></div>
   </div>

   <div class="cart-container">
      <div class="cart-items">
      <?php
         $grand_total = 0;
         $select_cart = $cartHandler->getUserCart();
         
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total += $sub_total;
      ?>
         <form action="" method="post" class="cart-item">
            <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
            <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="<?= $fetch_cart['name']; ?>" class="cart-item-img">
            
            <div class="cart-item-details">
               <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="cart-item-title"><?= $fetch_cart['name']; ?></a>
               <div class="cart-item-price">Rs. <?= number_format($fetch_cart['price'], 2); ?></div>
               <div class="stock-status">In stock</div>
               <div class="shipping-info">FREE Shipping</div>
            </div>
            
            <div class="cart-item-actions">
               <div class="qty-control">
                  <label for="qty">Qty:</label>
                  <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>">
                  <button type="submit" class="update-btn" name="update_qty" title="Update Quantity">
                     <i class="fas fa-sync-alt"></i>
                  </button>
               </div>
               
               <div class="sub-total">Subtotal: Rs. <?= number_format($sub_total, 2); ?></div>
               
               <button type="submit" onclick="return confirm('Delete this item from cart?');" class="delete-btn" name="delete">
                  <i class="fas fa-trash"></i> Remove
               </button>
            </div>
         </form>
      <?php
            }
         } else {
      ?>
         <div class="empty-cart">
            <div class="empty-cart-icon">
               <i class="fas fa-shopping-cart"></i>
            </div>
            <h3>Your Cart is Empty</h3>
            <p class="empty-cart-text">Looks like you haven't added anything to your cart yet</p>
            <a href="shop.php" class="shop-now-btn">Shop Now</a>
         </div>
      <?php
         }
      ?>
      </div>

      <?php if($item_count > 0) { ?>
      <div class="cart-summary">
         <h3 class="summary-title">Order Summary</h3>
         
         <div class="summary-details">
            <div class="summary-row">
               <span class="summary-label">Subtotal (<?= $item_count ?> item<?= $item_count != 1 ? 's' : '' ?>):</span>
               <span class="summary-value">Rs. <?= number_format($grand_total, 2); ?></span>
            </div>
            <div class="summary-row">
               <span class="summary-label">Delivery:</span>
               <span class="summary-value">FREE</span>
            </div>
         </div>
         
         <div class="grand-total">
            <div class="summary-row">
               <span>Total:</span>
               <span>Rs. <?= number_format($grand_total, 2); ?></span>
            </div>
         </div>
         
         <a href="checkout.php" class="checkout-btn <?= ($grand_total > 1)?'':'disabled'; ?>">
            Proceed to Checkout
         </a>
         
         <a href="shop.php" class="secondary-btn">
            Continue Shopping
         </a>
         
         <a href="cart.php?delete_all" class="delete-btn"
            onclick="return confirm('Delete all items from cart?');">
            <i class="fas fa-trash"></i> Empty Cart
         </a>
      </div>
      <?php } ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>