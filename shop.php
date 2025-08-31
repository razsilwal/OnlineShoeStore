<?php
include 'components/connect.php';

session_start();
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

// Handle add to cart functionality
if(isset($_POST['add_to_cart'])){
   // Sanitize inputs
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
   $qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);
   
   // Check if cart already exists
   if(isset($_SESSION['cart'])){
      $product_exists = false;
      
      // Check if this product is already in cart
      foreach($_SESSION['cart'] as &$item){
         if($item['pid'] == $pid){
            $item['qty'] += $qty;
            $product_exists = true;
            $message[] = 'Product quantity updated in cart!';
            break;
         }
      }
      
      // Product doesn't exist - add new item
      if(!$product_exists){
         $cart_item = array(
            'pid' => $pid,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'qty' => $qty
         );
         $_SESSION['cart'][] = $cart_item;
         $message[] = 'Product added to cart!';
      }
   }else{
      // Cart doesn't exist - create it with this item
      $cart_item = array(
         'pid' => $pid,
         'name' => $name,
         'price' => $price,
         'image' => $image,
         'qty' => $qty
      );
      $_SESSION['cart'] = array($cart_item);
      $message[] = 'Product added to cart!';
   }
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
   
   <!-- google fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
   /* Daraz-Style Modern Shopping Page Styles */
   * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
   }
   
   body {
      font-family: 'Poppins', sans-serif;
      background: #f5f5f5;
      color: #333;
   }
   
   /* Notification Styles */
   .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #00c851;
      color: white;
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
      transform: translateX(400px);
      transition: transform 0.3s ease;
      font-weight: 500;
   }
   
   .notification.show {
      transform: translateX(0);
   }
   
   .notification.error {
      background: #ff4757;
   }
   
   .notification i {
      margin-right: 8px;
   }
   
   /* Shop Hero Section */
   .shop-hero {
      background: linear-gradient(135deg, #ff6b35, #f7931e);
      color: white;
      text-align: center;
      padding: 60px 20px;
      margin-bottom: 0;
   }
   
   .shop-hero-content h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 15px;
      font-family: 'Playfair Display', serif;
   }
   
   .shop-hero-content p {
      font-size: 1.1rem;
      opacity: 0.9;
      max-width: 600px;
      margin: 0 auto;
   }
   
   /* Filter Bar */
   .filter-bar {
      background: white;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 30px;
   }
   
   .filter-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      gap: 20px;
      align-items: center;
      flex-wrap: wrap;
   }
   
   .filter-group {
      display: flex;
      align-items: center;
      gap: 10px;
   }
   
   .filter-group label {
      font-weight: 500;
      color: #666;
      font-size: 14px;
   }
   
   .filter-select {
      padding: 8px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      background: white;
      font-size: 14px;
      min-width: 120px;
   }
   
   /* Products Section */
   .products {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px 60px;
   }
   
   .products .heading {
      font-size: 2rem;
      font-weight: 600;
      text-align: center;
      margin-bottom: 40px;
      color: #333;
   }
   
   /* Product Grid */
   .box-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
   }
   
   /* Product Card */
   .box {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      position: relative;
      border: none;
      display: flex;
      flex-direction: column;
   }
   
   .box:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
   }
   
   /* Product Image */
   .box img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      transition: transform 0.3s ease;
   }
   
   .box:hover img {
      transform: scale(1.05);
   }
   
   /* Product Badge */
   .product-badge {
      position: absolute;
      top: 12px;
      left: 12px;
      padding: 5px 12px;
      border-radius: 15px;
      font-size: 11px;
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
   
   /* Action Buttons */
   .action-buttons {
      position: absolute;
      top: 12px;
      right: 12px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      opacity: 0;
      transform: translateX(20px);
      transition: all 0.3s ease;
      z-index: 2;
   }
   
   .box:hover .action-buttons {
      opacity: 1;
      transform: translateX(0);
   }
   
   .action-buttons button,
   .action-buttons a {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255,255,255,0.9);
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      color: #666;
      text-decoration: none;
      transition: all 0.2s ease;
      backdrop-filter: blur(10px);
   }
   
   .action-buttons button:hover,
   .action-buttons a:hover {
      background: #ff6b35;
      color: white;
      transform: scale(1.1);
   }
   
   /* Product Info */
   .product-info {
      padding: 20px;
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
      font-size: 16px;
      font-weight: 500;
      color: #333;
      text-decoration: none;
      margin-bottom: 10px;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
   }
   
   .name:hover {
      color: #ff6b35;
   }
   
   /* Rating */
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
      font-size: 12px;
      color: #666;
   }
   
   /* Price and Quantity */
   .flex {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
   }
   
   .price {
      display: flex;
      align-items: center;
      gap: 8px;
   }
   
   .price {
      font-size: 18px;
      font-weight: 600;
      color: #ff6b35;
   }
   
   .price span {
      font-size: 14px;
      color: #999;
      text-decoration: line-through;
      font-weight: 400;
   }
   
   .qty {
      width: 60px;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 6px;
      text-align: center;
      font-size: 14px;
   }
   
   /* Add to Cart Button */
   .btn {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #ff6b35, #f7931e);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
   }
   
   .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255,107,53,0.3);
   }
   
   .btn i {
      font-size: 14px;
   }
   
   /* Empty State */
   .empty {
      text-align: center;
      padding: 80px 20px;
      color: #666;
   }
   
   .empty img {
      width: 150px;
      opacity: 0.6;
      margin-bottom: 30px;
   }
   
   .empty h3 {
      font-size: 24px;
      margin-bottom: 15px;
      color: #333;
   }
   
   .empty p {
      font-size: 16px;
      margin-bottom: 30px;
      max-width: 400px;
      margin-left: auto;
      margin-right: auto;
   }
   
   /* Responsive Design */
   @media (max-width: 768px) {
      .shop-hero-content h1 {
         font-size: 2.2rem;
      }
      
      .filter-container {
         flex-direction: column;
         align-items: stretch;
      }
      
      .filter-group {
         justify-content: space-between;
      }
      
      .box-container {
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 20px;
      }
      
      .action-buttons {
         opacity: 1;
         transform: translateX(0);
         flex-direction: row;
         top: auto;
         bottom: 12px;
         right: 12px;
      }
   }
   
   @media (max-width: 480px) {
      .box-container {
         grid-template-columns: 1fr;
      }
      
      .shop-hero-content h1 {
         font-size: 1.8rem;
      }
      
      .shop-hero {
         padding: 40px 20px;
      }
   }
   
   /* Loading Animation */
   @keyframes fadeInUp {
      from {
         opacity: 0;
         transform: translateY(30px);
      }
      to {
         opacity: 1;
         transform: translateY(0);
      }
   }
   
   .box {
      animation: fadeInUp 0.6s ease forwards;
   }
   
   .box:nth-child(even) {
      animation-delay: 0.1s;
   }
   
   .box:nth-child(3n) {
      animation-delay: 0.2s;
   }
   </style>

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Notification Message -->
<?php if(isset($message)): ?>
   <div class="notification <?php echo strpos($message[0], 'added') !== false ? '' : 'error' ?>" id="notification">
      <i class="fas fa-<?php echo strpos($message[0], 'added') !== false ? 'check-circle' : 'exclamation-circle' ?>"></i>
      <span><?php echo $message[0]; ?></span>
   </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="shop-hero">
   <div class="shop-hero-content">
      <h1>Shop Our Collection</h1>
      <p>Discover handpicked items that blend style, comfort, and quality for your everyday life</p>
   </div>
</section>

<!-- Filter Bar -->
<section class="filter-bar">
   <div class="filter-container">
      <div class="filter-group">
         <label for="category">Category:</label>
         <select class="filter-select" id="category">
            <option value="">All Categories</option>
            <option value="electronics">Electronics</option>
            <option value="fashion">Fashion</option>
            <option value="home">Home & Living</option>
            <option value="beauty">Beauty</option>
         </select>
      </div>
      
      <div class="filter-group">
         <label for="price">Price Range:</label>
         <select class="filter-select" id="price">
            <option value="">All Prices</option>
            <option value="0-1000">Under NRs. 1,000</option>
            <option value="1000-5000">NRs. 1,000 - 5,000</option>
            <option value="5000-10000">NRs. 5,000 - 10,000</option>
            <option value="10000+">Above NRs. 10,000</option>
         </select>
      </div>
      
      <div class="filter-group">
         <label for="rating">Rating:</label>
         <select class="filter-select" id="rating">
            <option value="">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4+ Stars</option>
            <option value="3">3+ Stars</option>
         </select>
      </div>
      
      <div class="filter-group">
         <label for="sort">Sort By:</label>
         <select class="filter-select" id="sort">
            <option value="featured">Featured</option>
            <option value="price-low">Price: Low to High</option>
            <option value="price-high">Price: High to Low</option>
            <option value="rating">Customer Rating</option>
            <option value="newest">Newest First</option>
         </select>
      </div>
   </div>
</section>

<!-- Products Section -->
<section class="products">
   <h1 class="heading">Our Products</h1>

   <div class="box-container">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 12"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
         
         // Add some product badges for demo purposes
         $badge = '';
         $original_price = '';
         $discount_price = $fetch_product['price'];
         
         // Randomly assign badges for demo
         $random_badge = rand(1, 3);
         if($random_badge == 1) {
            $badge = '<span class="product-badge sale">SALE</span>';
            $original_price = number_format($fetch_product['price'] * 1.3, 2);
            $discount_price = $fetch_product['price'];
         } elseif($random_badge == 2) {
            $badge = '<span class="product-badge new">NEW</span>';
         }
         
         // Generate random rating (for demo)
         $rating = rand(3, 5);
         $rating_stars = str_repeat('<i class="fas fa-star"></i>', $rating);
         if($rating < 5) {
            $rating_stars .= str_repeat('<i class="far fa-star"></i>', 5 - $rating);
         }
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id']); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price']); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01']); ?>">
      
      <?= $badge ?>
      
      <div class="action-buttons">
         <button class="fas fa-heart" type="submit" name="add_to_wishlist" title="Add to Wishlist"></button>
         <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye" title="Quick View"></a>
      </div>
      
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="<?= $fetch_product['name']; ?>">
      
      <div class="product-info">
         <span class="category"><?= ucfirst($fetch_product['category'] ?? 'Fashion'); ?></span>
         <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="name"><?= $fetch_product['name']; ?></a>
         <div class="rating">
            <?= $rating_stars ?> 
            <span>(<?= rand(15, 120); ?> reviews)</span>
         </div>
         
         <div class="flex">
            <div class="price">
               NRs. <?= number_format($discount_price, 2); ?>
               <?php if($original_price): ?>
                  <span>NRs. <?= $original_price; ?></span>
               <?php endif; ?>
            </div>
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
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

<script src="js/script.js"></script>

<script>
   // Notification animation
   document.addEventListener('DOMContentLoaded', function() {
      const notification = document.getElementById('notification');
      if(notification) {
         // Show notification
         setTimeout(() => {
            notification.classList.add('show');
         }, 100);
         
         // Hide after 3 seconds
         setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
               notification.remove();
            }, 300);
         }, 3000);
      }
      
      // Quantity input validation
      const quantityInputs = document.querySelectorAll('.qty');
      quantityInputs.forEach(input => {
         input.addEventListener('change', function() {
            if(this.value < 1) this.value = 1;
            if(this.value > 99) this.value = 99;
         });
      });
      
      // Form submission handling
      const forms = document.querySelectorAll('form.box');
      forms.forEach(form => {
         form.addEventListener('submit', function(e) {
            const qtyInput = this.querySelector('input[name="qty"]');
            if(qtyInput.value < 1 || qtyInput.value > 99) {
               e.preventDefault();
               alert('Please enter a valid quantity (1-99)');
               qtyInput.focus();
               return;
            }
         });
      });
      
      // Filter functionality (basic demonstration)
      const filterSelects = document.querySelectorAll('.filter-select');
      filterSelects.forEach(select => {
         select.addEventListener('change', function() {
            // You can implement actual filtering logic here
            // For now, this is just a visual demonstration
            console.log(`Filter ${this.id} changed to: ${this.value}`);
         });
      });
      
      // Add to cart button loading effect
      const addToCartButtons = document.querySelectorAll('.btn[name="add_to_cart"]');
      addToCartButtons.forEach(button => {
         button.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            this.disabled = true;
            
            // Re-enable after form submission (this will be reset by page reload anyway)
            setTimeout(() => {
               this.innerHTML = originalText;
               this.disabled = false;
            }, 2000);
         });
      });
   });
</script>

</body>
</html>