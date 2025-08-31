<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$cart_id]);
}

if(isset($_GET['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'cart quantity updated';
}
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
<style>
   /* Shopping Cart Section */
.shopping-cart {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.cart-header h3 {
    font-size: 1.8rem;
    color: #2c3e50;
    font-weight: 600;
}

.item-count {
    background: #f8f9fa;
    color: #6c757d;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.cart-container {
    display: flex;
    gap: 2rem;
    flex-direction: column;
}

@media (min-width: 992px) {
    .cart-container {
        flex-direction: row;
    }
}

.cart-items {
    flex: 2;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
}

.cart-item {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-img {
    width: 120px;
    height: 120px;
    object-fit: contain;
    border-radius: 5px;
    background: #f8f9fa;
    padding: 0.5rem;
}

.cart-item-details {
    flex: 1;
    min-width: 200px;
}

.cart-item-title {
    font-size: 1.1rem;
    color: #2c3e50;
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: block;
    text-decoration: none;
}

.cart-item-title:hover {
    color: #3498db;
}

.cart-item-price {
    font-size: 1.1rem;
    color: #27ae60;
    font-weight: 600;
    margin-bottom: 0.5rem;
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
    gap: 0.5rem;
}

.qty-control label {
    color: #6c757d;
    font-size: 0.9rem;
}

.qty {
    width: 60px;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-align: center;
}

.update-btn {
    background: #f8f9fa;
    border: none;
    color: #6c757d;
    padding: 0.5rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.update-btn:hover {
    background: #e9ecef;
    color: #2c3e50;
}

.sub-total {
    font-weight: 600;
    color: #2c3e50;
}

.delete-btn {
    background: #fff5f5;
    color: #e74c3c;
    border: 1px solid #ffdddd;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.delete-btn:hover {
    background: #ffecec;
}

/* Empty Cart Styles */
.empty-cart {
    text-align: center;
    padding: 3rem 0;
}

.empty-cart-icon {
    font-size: 3rem;
    color: #bdc3c7;
    margin-bottom: 1rem;
}

.empty-cart h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-cart-text {
    color: #7f8c8d;
    margin-bottom: 1.5rem;
}

.shop-now-btn {
    background: #3498db;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.shop-now-btn:hover {
    background: #2980b9;
}

/* Cart Summary Styles */
.cart-summary {
    flex: 1;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
    height: fit-content;
}

.summary-title {
    font-size: 1.3rem;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.summary-details {
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.summary-label {
    color: #6c757d;
}

.summary-value {
    font-weight: 500;
    color: #2c3e50;
}

.grand-total {
    padding: 1rem 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 1.5rem;
}

.grand-total .summary-row {
    font-size: 1.1rem;
    font-weight: 600;
    color: #27ae60;
}

.checkout-btn {
    background: #27ae60;
    color: white;
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: block;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.checkout-btn:hover {
    background: #219653;
}

.checkout-btn.disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

.secondary-btn {
    background: #3498db;
    color: white;
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: block;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.secondary-btn:hover {
    background: #2980b9;
}
</style>
   
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="shopping-cart">
   <div class="cart-header">
      <h3>Shopping Cart</h3>
      <?php
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         $item_count = $select_cart->rowCount();
      ?>
      <div class="item-count"><?= $item_count ?> item<?= $item_count != 1 ? 's' : '' ?></div>
   </div>

   <div class="cart-container">
      <div class="cart-items">
      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         
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
               <div>In stock</div>
               <div>FREE Shipping</div>
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

      <?php if($select_cart->rowCount() > 0) { ?>
      <div class="cart-summary">
         <h3 class="summary-title">Order Summary</h3>
         
         <div class="summary-details">
            <div class="summary-row">
               <span class="summary-label">Subtotal (<?= $item_count ?> item<?= $item_count != 1 ? 's' : '' ?>):</span>
               <span class="summary-value">NRs. <?= number_format($grand_total, 2); ?></span>
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
            Add other product
         </a>
         
         <a href="cart.php?delete_all" class="delete-btn" onclick="return confirm('Delete all items from cart?');" style="justify-content: center; margin-top: 1rem;">
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