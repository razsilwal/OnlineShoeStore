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
   <link rel="stylesheet" href="css/cart.css">

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
            onclick="return confirm('Delete all items from cart?');"
            style="justify-content: center; margin-top: 1rem;">
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