<?php
include 'components/connect.php';

session_start();
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
}

// Small OOP wrappers that preserve original backend logic and queries
class CartHandler {
    private $conn;
    private $user_id;
    public $message = [];

    public function __construct(PDO $conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function addToCart(array $post) {
        // Sanitize inputs (same as original)
        $pid = filter_var($post['pid'] ?? '', FILTER_SANITIZE_STRING);
        $name = filter_var($post['name'] ?? '', FILTER_SANITIZE_STRING);
        $price = filter_var($post['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $image = filter_var($post['image'] ?? '', FILTER_SANITIZE_STRING);
        $size = filter_var($post['size'] ?? '', FILTER_SANITIZE_STRING);

        // Check if product already exists in cart for this user with same size
        $check_cart = $this->conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND pid = ? AND size = ?");
        $check_cart->execute([$this->user_id, $pid, $size]);

        if($check_cart->rowCount() > 0){
            // Update quantity if product exists
            $update_qty = $this->conn->prepare("UPDATE `cart` SET quantity = quantity + 1 WHERE user_id = ? AND pid = ? AND size = ?");
            $update_qty->execute([$this->user_id, $pid, $size]);
            $this->message[] = 'Product quantity updated in cart!';
        } else {
            // Insert new item if it doesn't exist
            $insert_cart = $this->conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, image, quantity, size) VALUES(?,?,?,?,?,?,?)");
            $insert_cart->execute([$this->user_id, $pid, $name, $price, $image, 1, $size]);
            $this->message[] = 'Product added to cart!';
        }

        return $this->message;
    }
}

class WishlistHandler {
    private $conn;
    private $user_id;
    public $message = [];

    public function __construct(PDO $conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function addToWishlist(array $post) {
        $pid = filter_var($post['pid'] ?? '', FILTER_SANITIZE_STRING);
        $name = filter_var($post['name'] ?? '', FILTER_SANITIZE_STRING);
        $price = filter_var($post['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $image = filter_var($post['image'] ?? '', FILTER_SANITIZE_STRING);

        // Check if product is already in wishlist
        $check_wishlist = $this->conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND pid = ?");
        $check_wishlist->execute([$this->user_id, $pid]);

        if($check_wishlist->rowCount() > 0){
            $this->message[] = 'Product is already in wishlist!';
        } else {
            $insert_wishlist = $this->conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
            $insert_wishlist->execute([$this->user_id, $pid, $name, $price, $image]);
            $this->message[] = 'Product added to wishlist!';
        }

        return $this->message;
    }
}

class ProductRenderer {
    private $conn;
    private $user_id;

    public function __construct(PDO $conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    // This echoes the same HTML forms/markup for products as the original page,
    // including wishlist check, badges, rating, etc.
    public function renderProducts($limit = 12) {
        $select_products = $this->conn->prepare("SELECT * FROM `products` LIMIT ?");
        // PDO with LIMIT needs integer binding
        $select_products->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $select_products->execute();

        if($select_products->rowCount() > 0){
            while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
                // Check if product is in wishlist
                $in_wishlist = false;
                if($this->user_id != ''){
                   $check_wishlist = $this->conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND pid = ?");
                   $check_wishlist->execute([$this->user_id, $fetch_product['id']]);
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

                // Output the form/box (same structure as original)
                echo '<form action="" method="post" class="box">';
                echo '<input type="hidden" name="pid" value="' . htmlspecialchars($fetch_product['id']) . '">';
                echo '<input type="hidden" name="name" value="' . htmlspecialchars($fetch_product['name']) . '">';
                echo '<input type="hidden" name="price" value="' . htmlspecialchars($fetch_product['price']) . '">';
                echo '<input type="hidden" name="image" value="' . htmlspecialchars($fetch_product['image_01']) . '">';

                echo $badge;

                echo '<div class="action-buttons">';
                echo '<button type="submit" name="add_to_wishlist" class="wishlist-btn ' . ($in_wishlist ? 'active' : '') . '" title="' . ($in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist') . '">';
                echo '<i class="fas fa-heart"></i>';
                echo '</button>';
                echo '<a href="quick_view.php?pid=' . htmlspecialchars($fetch_product['id']) . '" class="fas fa-eye" title="Quick View"></a>';
                echo '</div>';

                echo '<img src="uploaded_img/' . htmlspecialchars($fetch_product['image_01']) . '" alt="' . htmlspecialchars($fetch_product['name']) . '">';

                echo '<div class="product-info">';
                echo '<a href="quick_view.php?pid=' . htmlspecialchars($fetch_product['id']) . '" class="name">' . htmlspecialchars($fetch_product['name']) . '</a>';

                echo '<div class="rating">';
                echo $rating_stars;
                echo '<span>(' . rand(15, 200) . ' reviews)</span>';
                echo '</div>';

                echo '<div class="flex">';
                echo '<div class="price">NRs. ' . number_format($fetch_product['price'], 2) . '</div>';
                echo '<select name="size" class="size-select" required>';
                echo '<option value="">Select Size</option>';
                for($s = 5; $s <= 12; $s++){
                    echo '<option value="' . $s . '">' . $s . '</option>';
                }
                echo '</select>';
                echo '</div>'; // .flex

                echo '</div>'; // .product-info

                echo '<button type="submit" class="btn" name="add_to_cart">';
                echo '<i class="fas fa-shopping-cart"></i> Add to Cart';
                echo '</button>';

                echo '</form>';
            }
        } else {
            echo '
            <div class="empty">
               <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" alt="Empty shop">
               <h3>Our Shop is Currently Empty</h3>
               <p>We\'re preparing something amazing for you! Check back soon for our new collection.</p>
            </div>';
        }
    }
}

// Instantiate handlers
$cartHandler = new CartHandler($conn, $user_id);
$wishlistHandler = new WishlistHandler($conn, $user_id);
$productRenderer = new ProductRenderer($conn, $user_id);

// Handle POST actions (preserve same redirect behavior to prevent resubmission)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add_to_cart'])) {
        $message = $cartHandler->addToCart($_POST);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    if(isset($_POST['add_to_wishlist'])) {
        $message = $wishlistHandler->addToWishlist($_POST);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
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
   
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
   /* (kept identical) */
   /* ... all your original CSS here ... */
   /* For brevity in this snippet I left it unchanged — paste original CSS block here exactly as in your file */
   /* (In your actual file, keep the CSS you posted previously) */
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Notification Message -->
<?php if(isset($message) && is_array($message) && count($message) > 0): ?>
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
      // Render products (same output as original)
      $productRenderer->renderProducts(12);
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
