<?php
include 'components/connect.php';

session_start();
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

// Handle add to cart functionality
if(isset($_POST['add_to_cart'])){
   // Sanitize inputs
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
   $size = filter_var($_POST['size'], FILTER_SANITIZE_STRING);
   
   // Check if product already exists in cart for this user with same size
   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND pid = ? AND size = ?");
   $check_cart->execute([$user_id, $pid, $size]);
   
   if($check_cart->rowCount() > 0){
      // Update quantity if product exists
      $update_qty = $conn->prepare("UPDATE `cart` SET quantity = quantity + 1 WHERE user_id = ? AND pid = ? AND size = ?");
      $update_qty->execute([$user_id, $pid, $size]);
      $message[] = 'Product quantity updated in cart!';
   } else {
      // Insert new item if it doesn't exist
      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, image, quantity, size) VALUES(?,?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $name, $price, $image, 1, $size]);
      $message[] = 'Product added to cart!';
   }
   
   // Redirect to prevent form resubmission
   header('Location: ' . $_SERVER['PHP_SELF']);
   exit();
}

// Handle add to wishlist functionality
if(isset($_POST['add_to_wishlist'])){
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
   
   // Check if product is already in wishlist
   $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND pid = ?");
   $check_wishlist->execute([$user_id, $pid]);
   
   if($check_wishlist->rowCount() > 0){
      $message[] = 'Product is already in wishlist!';
   } else {
      $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
      $message[] = 'Product added to wishlist!';
   }
   
   header('Location: ' . $_SERVER['PHP_SELF']);
   exit();
}

include 'components/wishlist_cart.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shop | Modern </title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
   /* Enhanced Shop Styles */
   .products {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 1rem;
   }
   
   .heading {
      text-align: center;
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 2rem;
      position: relative;
   }
   
   .heading:after {
      content: '';
      display: block;
      width: 80px;
      height: 4px;
      background: linear-gradient(135deg, #ff6b35, #f7931e);
      margin: 1rem auto;
      border-radius: 2px;
   }
   
   .box-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
   }
   
   .box {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      position: relative;
      border: none;
      display: flex;
      flex-direction: column;
   }
   
   .box:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.15);
   }
   
   .box img {
      width: 100%;
      height: 280px;
      object-fit: cover;
      transition: transform 0.3s ease;
   }
   
   .box:hover img {
      transform: scale(1.05);
   }
   
   .product-badge {
      position: absolute;
      top: 15px;
      left: 15px;
      padding: 8px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      z-index: 2;
   }
   
   .product-badge.sale {
      background: #ff4757;
      color: white;
   }
   
   .product-badge.new {
      background: #2ed573;
      color: white;
   }
   
   .product-badge.hot {
      background: #ff9f43;
      color: white;
   }
   
   .action-buttons {
      position: absolute;
      top: 15px;
      right: 15px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      z-index: 2;
   }
   
   .action-buttons button,
   .action-buttons a {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: rgba(255,255,255,0.95);
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      color: #666;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      cursor: pointer;
   }
   
   .action-buttons button:hover,
   .action-buttons a:hover {
      background: #ff6b35;
      color: white;
      transform: scale(1.1);
   }
   
   .wishlist-btn.active {
      color: #ff4757;
      background: #fff5f5;
   }
   
   .wishlist-btn.active:hover {
      background: #ff4757;
      color: white;
   }
   
   .product-info {
      padding: 1.5rem;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
   }
   
   .category {
      font-size: 12px;
      color: #ff6b35;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
   }
   
   .name {
      font-size: 18px;
      font-weight: 600;
      color: #2c3e50;
      text-decoration: none;
      margin-bottom: 12px;
      line-height: 1.4;
      transition: color 0.3s ease;
   }
   
   .name:hover {
      color: #ff6b35;
   }
   
   .rating {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 15px;
   }
   
   .rating i {
      font-size: 14px;
      color: #ffc107;
   }
   
   .rating i.far {
      color: #ddd;
   }
   
   .rating span {
      font-size: 13px;
      color: #7f8c8d;
   }
   
   .flex {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      gap: 15px;
   }
   
   .price {
      font-size: 20px;
      font-weight: 700;
      color: #ff6b35;
   }
   
   .price span.old-price {
      font-size: 16px;
      color: #95a5a6;
      text-decoration: line-through;
      margin-right: 8px;
      font-weight: 400;
   }
   
   .size-select {
      padding: 10px 12px;
      border: 2px solid #e8e8e8;
      border-radius: 8px;
      font-size: 14px;
      min-width: 100px;
      background: white;
      transition: border-color 0.3s ease;
   }
   
   .size-select:focus {
      border-color: #ff6b35;
      outline: none;
   }
   
   .btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #ff6b35, #f7931e);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
   }
   
   .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255,107,53,0.4);
   }
   
   .btn:active {
      transform: translateY(0);
   }
   
   .empty {
      text-align: center;
      padding: 4rem 2rem;
      color: #7f8c8d;
   }
   
   .empty img {
      width: 180px;
      opacity: 0.5;
      margin-bottom: 2rem;
   }
   
   .empty h3 {
      font-size: 24px;
      margin-bottom: 1rem;
      color: #2c3e50;
   }
   
   .empty p {
      font-size: 16px;
      margin-bottom: 2rem;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
   }
   
   /* Notification Styles */
   .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #00c851;
      color: white;
      padding: 15px 20px;
      border-radius: 10px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      z-index: 1000;
      display: flex;
      align-items: center;
      gap: 10px;
      transform: translateX(400px);
      transition: transform 0.3s ease;
   }
   
   .notification.show {
      transform: translateX(0);
   }
   
   .notification.error {
      background: #ff4757;
   }
   
   /* Responsive Design */
   @media (max-width: 768px) {
      .box-container {
         grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
         gap: 1.5rem;
      }
      
      .heading {
         font-size: 2rem;
      }
      
      .product-info {
         padding: 1.2rem;
      }
      
      .flex {
         flex-direction: column;
         align-items: stretch;
      }
      
      .size-select {
         width: 100%;
      }
   }
   
   @media (max-width: 480px) {
      .box-container {
         grid-template-columns: 1fr;
      }
      
      .products {
         padding: 1rem;
      }
      
      .action-buttons {
         flex-direction: row;
         top: auto;
         bottom: 15px;
         right: 15px;
      }
      
      .action-buttons button,
      .action-buttons a {
         width: 40px;
         height: 40px;
      }
   }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Notification Message -->
<?php if(isset($message)): ?>
   <div class="notification <?php echo (strpos($message[0], 'added') !== false || strpos($message[0], 'updated') !== false) ? '' : 'error' ?>" id="notification">
      <i class="fas fa-<?php echo (strpos($message[0], 'added') !== false || strpos($message[0], 'updated') !== false) ? 'check-circle' : 'exclamation-circle' ?>"></i>
      <span><?php echo $message[0]; ?></span>
   </div>
<?php endif; ?>

<!-- Products Section -->
<section class="products">
   <h1 class="heading">Discover Our Collection</h1>

   <div class="box-container">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 12"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
        while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
           // Check if product is in wishlist
           $in_wishlist = false;
           if($user_id != ''){
              $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND pid = ?");
              $check_wishlist->execute([$user_id, $fetch_product['id']]);
              $in_wishlist = $check_wishlist->rowCount() > 0;
           }
           
           // Generate random badge for demo
           $badge_type = rand(1, 4);
           $badge = '';
           $original_price = '';
           
           if($badge_type == 1) {
              $badge = '<span class="product-badge sale">SALE</span>';
              $original_price = number_format($fetch_product['price'] * 1.2, 2);
           } elseif($badge_type == 2) {
              $badge = '<span class="product-badge new">NEW</span>';
           } elseif($badge_type == 3) {
              $badge = '<span class="product-badge hot">HOT</span>';
           }
           
           // Generate random rating
           $rating = rand(3, 5);
           $rating_stars = str_repeat('<i class="fas fa-star"></i>', $rating);
           if($rating < 5) {
              $rating_stars .= str_repeat('<i class="far fa-star"></i>', 5 - $rating);
           }
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      
      <?= $badge ?>
      
      <div class="action-buttons">
         <button type="submit" name="add_to_wishlist" class="wishlist-btn <?= $in_wishlist ? 'active' : '' ?>" title="<?= $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
            <i class="fas fa-heart"></i>
         </button>
         <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye" title="Quick View"></a>
      </div>
      
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="<?= $fetch_product['name']; ?>">
      
      <div class="product-info">
         
         <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="name"><?= $fetch_product['name']; ?></a>
         
         <div class="rating">
            <?= $rating_stars ?> 
            <span>(<?= rand(15, 200); ?> reviews)</span>
         </div>
         
         <div class="flex">
            <div class="price">
               
               NRs. <?= number_format($fetch_product['price'], 2); ?>
            </div>
            <select name="size" class="size-select" required>
               <option value="">Select Size</option>
               <option value="5">5</option>
               <option value="6">6</option>
               <option value="7">7</option>
               <option value="8">8</option>
               <option value="9">9</option>
               <option value="10">10</option>
               <option value="11">11</option>
               <option value="12">12</option>
            </select>
         </div>
      </div>
      
      <button type="submit" class="btn" name="add_to_cart">
         <i class="fas fa-shopping-cart"></i> Add to Cart
      </button>
   </form>
   <?php
        }
     }else{
        echo '
        <div class="empty">
           <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" alt="Empty shop">
           <h3>Our Shop is Currently Empty</h3>
           <p>We\'re preparing something amazing for you! Check back soon for our new collection.</p>
        </div>';
     }
   ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Notification animation
   const notification = document.getElementById('notification');
   if(notification) {
      setTimeout(() => {
         notification.classList.add('show');
      }, 100);
      
      setTimeout(() => {
         notification.classList.remove('show');
         setTimeout(() => {
            notification.remove();
         }, 300);
      }, 3000);
   }
   
   // Form validation
   const forms = document.querySelectorAll('form.box');
   forms.forEach(form => {
      form.addEventListener('submit', function(e) {
         const sizeSelect = this.querySelector('select[name="size"]');
         if(!sizeSelect.value) {
            e.preventDefault();
            alert('Please select a shoe size');
            sizeSelect.focus();
         }
      });
   });
   
   // Wishlist button animation
   const wishlistButtons = document.querySelectorAll('.wishlist-btn');
   wishlistButtons.forEach(button => {
      button.addEventListener('click', function() {
         this.classList.toggle('active');
      });
   });
});
</script>

</body>
</html>