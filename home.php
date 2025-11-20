<?php
declare(strict_types=1);

// Database connection and session start
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';

// Keep your existing wishlist/cart handler
include 'components/wishlist_cart.php';

/* =========================
   Enhanced OOP Classes
   ========================= */
class InputSanitizer {
    public static function string($value, string $default = ''): string {
        if ($value === null) return $default;
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
    
    public static function integer($value, int $default = 0): int {
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : $default;
    }
    
    public static function float($value, float $default = 0.0): float {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float)$value : $default;
    }
}

class CSRFProtection {
    private $sessionKey = 'csrf_token';
    
    public function __construct() {
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = bin2hex(random_bytes(32));
        }
    }
    
    public function getToken(): string {
        return $_SESSION[$this->sessionKey];
    }
    
    public function validate($token): bool {
        return hash_equals($this->getToken(), (string)$token);
    }
}

class ProductViewTracker {
    private $sessionKey = 'viewed_products';
    private $maxProducts = 5;
    
    public function addProduct($productId): void {
        if (empty($productId)) return;
        
        if (!isset($_SESSION[$this->sessionKey]) || !is_array($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = array();
        }
        
        $existingKey = array_search($productId, $_SESSION[$this->sessionKey]);
        if ($existingKey !== false) {
            unset($_SESSION[$this->sessionKey][$existingKey]);
        }
        
        array_unshift($_SESSION[$this->sessionKey], $productId);
        $_SESSION[$this->sessionKey] = array_slice($_SESSION[$this->sessionKey], 0, $this->maxProducts);
    }
    
    public function getViewedProducts(): array {
        return $_SESSION[$this->sessionKey] ?? array();
    }
}

class ProductDataRepository {
    private $database;
    
    public function __construct($database) {
        $this->database = $database;
    }
    
    public function fetchFeaturedProducts($limit = 8): array {
        try {
            // Check if products table exists
            $checkTable = $this->database->query("SHOW TABLES LIKE 'products'");
            if ($checkTable->rowCount() === 0) {
                error_log("Products table does not exist");
                return array();
            }
            
            // Try different possible column names for featured status
            $sql = "SELECT * FROM `products` 
                    WHERE (featured = 1 OR is_featured = 1) AND status = 'active' 
                    ORDER BY id DESC 
                    LIMIT :limit";
            
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();
            
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $results ? $results : array();
            
        } catch (PDOException $e) {
            error_log("Database error in fetchFeaturedProducts: " . $e->getMessage());
            return array();
        }
    }
}

class ProductService {
    private $repository;
    
    public function __construct($repository) {
        $this->repository = $repository;
    }
    
    public function getFeaturedProducts($limit = 8): array {
        $productsData = $this->repository->fetchFeaturedProducts($limit);
        $processedProducts = array();
        
        foreach ($productsData as $productData) {
            $processedProducts[] = $this->processProductData($productData);
        }
        
        return $processedProducts;
    }
    
    private function processProductData($productData): array {
        $price = InputSanitizer::float($productData['price'] ?? 0);
        $discount = InputSanitizer::integer($productData['discount'] ?? 0);
        $discountedPrice = $discount > 0 ? $price - ($price * $discount / 100) : $price;
        
        $imageFilename = InputSanitizer::string($productData['image_01'] ?? $productData['image'] ?? '');
        $imagePath = 'uploaded_img/' . $imageFilename;
        
        // Check multiple possible image locations
        if (!empty($imageFilename)) {
            if (file_exists($imagePath) && is_file($imagePath)) {
                $imageUrl = $imagePath;
            } elseif (file_exists('images/' . $imageFilename) && is_file('images/' . $imageFilename)) {
                $imageUrl = 'images/' . $imageFilename;
            } else {
                $imageUrl = 'images/default-product.jpg';
            }
        } else {
            $imageUrl = 'images/default-product.jpg';
        }
        
        return array(
            'id' => InputSanitizer::integer($productData['id']),
            'name' => InputSanitizer::string($productData['name']),
            'description' => InputSanitizer::string($productData['description'] ?? ''),
            'price' => $price,
            'discount' => $discount,
            'discounted_price' => $discountedPrice,
            'image_raw' => $imageFilename,
            'image_url' => $imageUrl,
            'avg_rating' => InputSanitizer::float($productData['avg_rating'] ?? $productData['rating'] ?? 0),
            'review_count' => InputSanitizer::integer($productData['review_count'] ?? $productData['reviews'] ?? 0)
        );
    }
}

/* =========================
   Application Execution
   ========================= */
$csrf = new CSRFProtection();
$viewTracker = new ProductViewTracker();

// Track viewed products via GET pid
if (isset($_GET['pid'])) {
    $productId = InputSanitizer::string($_GET['pid']);
    $viewTracker->addProduct($productId);
}

// Fetch featured products
$featuredProducts = array();
$loadError = false;

try {
    // Check if database connection is working
    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection failed");
    }
    
    $productService = new ProductService(new ProductDataRepository($conn));
    $featuredProducts = $productService->getFeaturedProducts(8);
    
} catch (Throwable $error) {
    $loadError = true;
    error_log("Home page error: " . $error->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kickster - Premium Footwear Collection</title>

   <!-- Favicon -->
   <link rel="icon" href="images/favicon.ico" type="image/x-icon">

   <!-- Swiper CSS -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- Animate.css -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
   
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/home.css">

   <style>
      :root {
         --primary: #4361ee;
         --secondary: #3a0ca3;
         --accent: #f72585;
         --light: #faf8f8ff;
         --dark: #212529;
         --gradient: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
         --gradient-accent: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
      }
      
      /* Hero Section */
      .home-bg {
         background: linear-gradient(135deg, #f0141cff 0%, #e4e8f0 100%);
         padding: 4rem 0;
         overflow: hidden;
         min-height: 80vh;
         display: flex;
         align-items: center;
      }
      
      .home {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 2rem;
         width: 100%;
      }
      
      .home-slider .slide {
         display: flex;
         align-items: center;
         flex-wrap: wrap;
         gap: 2rem;
         padding: 2rem 0;
      }
      
      .home-slider .image {
         flex: 1 1 45rem;
         text-align: center;
      }
      
      .home-slider .image img {
         width: 100%;
         max-width: 500px;
         height: auto;
         object-fit: contain;
         filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
      }
      
      .home-slider .content {
         flex: 1 1 45rem;
      }
      
      .home-slider .content span {
         color: var(--accent);
         font-size: 2.5rem;
         font-weight: 600;
         display: block;
         margin-bottom: 1rem;
      }
      
      .home-slider .content h3 {
         font-size: 4rem;
         color: var(--dark);
         text-transform: uppercase;
         margin-bottom: 1.5rem;
         line-height: 1.1;
      }
      
      .home-slider .content p {
         font-size: 1.6rem;
         color: #666;
         line-height: 1.8;
         margin-bottom: 2rem;
      }
      
      .home-slider .content .btn {
         display: inline-block;
         padding: 1.2rem 3rem;
         background: var(--gradient);
         color: white;
         font-size: 1.6rem;
         border-radius: 50px;
         text-transform: uppercase;
         font-weight: 600;
         transition: all 0.3s ease;
         box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
      }
      
      .home-slider .content .btn:hover {
         transform: translateY(-5px);
         box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
         background: var(--gradient-accent);
      }
      
      /* Categories Section */
      .categories {
         padding: 4rem 0;
         background: white;
      }
      
      .section-container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 2rem;
      }
      
      .section-title {
         text-align: center;
         margin-bottom: 4rem;
      }
      
      .section-title h1 {
         font-size: 3rem;
         color: var(--dark);
         text-transform: uppercase;
         margin-bottom: 1.5rem;
         position: relative;
         display: inline-block;
      }
      
      .section-title h1::after {
         content: '';
         position: absolute;
         bottom: -10px;
         left: 50%;
         transform: translateX(-50%);
         width: 80px;
         height: 4px;
         background: var(--gradient-accent);
         border-radius: 2px;
      }
      
      .categories-grid {
         display: flex;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 3rem;
      }
      
      .category-card {
         background: white;
         border-radius: 15px;
         overflow: hidden;
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
         transition: all 0.3s ease;
         text-align: center;
      }
      
      .category-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 30px rgba(0,0,0,0.15);
      }
      
      .category-card img {
         width: 100%;
         height: 200px;
         object-fit: cover;
      }
      
      .category-card h3 {
         font-size: 2rem;
         color: var(--dark);
         padding: 1.5rem;
         margin: 0;
      }
      
      .category-card .btn {
         display: inline-block;
         margin-bottom: 2rem;
         padding: 0.8rem 2rem;
         background: var(--gradient);
         color: white;
         border-radius: 50px;
         font-size: 1.4rem;
         transition: all 0.3s ease;
      }
      
      .category-card .btn:hover {
         background: var(--gradient-accent);
         transform: scale(1.05);
      }
      
      /* Featured Products */
      .featured-products {
         padding: 5rem 0;
         background: #f9fafc;
      }
      
      .products-container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 2rem;
      }
      
      .products-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 3rem;
      }
      
      .product-card {
         background: white;
         border-radius: 15px;
         overflow: hidden;
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
         transition: all 0.3s ease;
         position: relative;
      }
      
      .product-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 30px rgba(0,0,0,0.15);
      }
      
      .product-badge {
         position: absolute;
         top: 15px;
         left: 15px;
         background: var(--accent);
         color: white;
         padding: 0.5rem 1rem;
         border-radius: 50px;
         font-size: 1.2rem;
         font-weight: 600;
         z-index: 1;
      }
      
      .product-image {
         width: 100%;
         height: 250px;
         object-fit: contain;
         padding: 20px;
         background: #f9f9f9;
      }
      
      .product-info {
         padding: 2rem;
      }
      
      .product-title {
         font-size: 1.8rem;
         color: var(--dark);
         margin-bottom: 1rem;
         height: 54px;
         overflow: hidden;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
      }
      
      .product-price {
         font-size: 2rem;
         color: var(--primary);
         font-weight: 700;
         margin-bottom: 1.5rem;
      }
      
      .product-price .old-price {
         text-decoration: line-through;
         color: #999;
         font-size: 1.6rem;
         margin-left: 0.5rem;
      }
      
      .product-rating {
         color: #ffc107;
         margin-bottom: 1.5rem;
      }
      
      .product-actions {
         display: flex;
         justify-content: space-between;
      }
      
      .add-to-cart {
         flex: 1;
         background: var(--gradient);
         color: white;
         border: none;
         padding: 1rem;
         border-radius: 5px;
         cursor: pointer;
         transition: all 0.3s ease;
         font-weight: 600;
      }
      
      .add-to-cart:hover {
         background: var(--gradient-accent);
         transform: translateY(-2px);
      }
      
      .wishlist-btn {
         width: 45px;
         height: 45px;
         background: white;
         border: 1px solid #ddd;
         border-radius: 50%;
         margin-left: 1rem;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         transition: all 0.3s ease;
      }
      
      .wishlist-btn:hover {
         background: #fee;
         border-color: var(--accent);
         color: var(--accent);
      }
      
      /* Empty State */
      .empty {
         text-align: center;
         padding: 5rem 0;
      }
      
      .empty p {
         font-size: 1.8rem;
         color: #666;
         margin-bottom: 2rem;
      }
      
      /* Responsive Design */
      @media (max-width: 991px) {
         .home-slider .slide {
            flex-direction: column;
            text-align: center;
         }
         
         .home-slider .content {
            margin-top: 3rem;
         }
      }
      
      @media (max-width: 768px) {
         .home-slider .content h3 {
            font-size: 3rem;
         }
         
         .section-title h1 {
            font-size: 2.5rem;
         }
      }
      
      @media (max-width: 450px) {
         .home-slider .content h3 {
            font-size: 2.5rem;
         }
         
         .home-slider .content span {
            font-size: 2rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Hero Slider Section -->
<section class="home-bg">
   <div class="home">
      <div class="swiper home-slider">
         <div class="swiper-wrapper">

            <div class="swiper-slide slide">
               <div class="image animate__animated animate__fadeInLeft">
                  <img src="shoes collection picture/men/nike-removebg.png" alt="Nike shoes">
               </div>
               <div class="content animate__animated animate__fadeInRight">
                  <span>Limited Time Offer</span>
                  <h3>Premium Footwear Collection</h3>
                  <p>Discover our latest arrivals with exclusive designs and unmatched comfort for every step you take.</p>
                  <a href="shop.php" class="btn">Shop Now</a>
               </div>
            </div>

         </div>
         <div class="swiper-pagination"></div>
      </div>
   </div>
</section>

<!-- Categories Section -->
<section class="categories">
   <div class="section-container">
      <div class="section-title">
         <h1 class="animate__animated animate__fadeIn">Shop by Category</h1>
      </div>
      
      <div class="categories-grid">
         <div class="category-card animate__animated animate__fadeInUp">
            <img src="shoes collection picture/men/formal1.jpg" alt="Men's Shoes">
            <h3>Men's Footwear</h3>
            <a href="shop.php?category=men" class="btn">Shop Now</a>
         </div>
         
         <div class="category-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
            <img src="shoes collection picture/women/photo3.jpg" alt="Women's Shoes">
            <h3>Women's Footwear</h3>
            <a href="shop.php?category=women" class="btn">Shop Now</a>
         </div>
         
         <div class="category-card animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
            <img src="shoes collection picture/men/nike.jpg" alt="Sports Shoes">
            <h3>Sports Shoes</h3>
            <a href="shop.php?category=sports" class="btn">Shop Now</a>
         </div>
      </div>
   </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
   <div class="section-title">
      <h1 class="animate__animated animate__fadeIn">Featured Products</h1>
   </div>
   
   <div class="products-container">
      <?php if (!$loadError && !empty($featuredProducts)): ?>
         <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
               <div class="product-card animate__animated animate__fadeInUp">
                  <?php if ($product['discount'] > 0): ?>
                     <div class="product-badge"><?= $product['discount'] ?>% OFF</div>
                  <?php endif; ?>
                  
                  <img src="<?= $product['image_url'] ?>" 
                       alt="<?= $product['name'] ?>" 
                       class="product-image"
                       onerror="this.src='images/default-product.jpg'">
                  
                  <div class="product-info">
                     <h3 class="product-title"><?= $product['name'] ?></h3>
                     
                     <div class="product-rating">
                        <?php
                           $rating = (int)round($product['avg_rating']);
                           for ($i = 1; $i <= 5; $i++) {
                              echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                           }
                        ?>
                        <span>(<?= $product['review_count'] ?>)</span>
                     </div>
                     
                     <div class="product-price">
                        NRs. <?= number_format($product['discounted_price'], 2) ?>
                        <?php if ($product['discount'] > 0): ?>
                           <span class="old-price">NRs. <?= number_format($product['price'], 2) ?></span>
                        <?php endif; ?>
                     </div>
                     
                     <form action="" method="post" class="product-actions">
                        <input type="hidden" name="pid" value="<?= $product['id'] ?>">
                        <input type="hidden" name="name" value="<?= $product['name'] ?>">
                        <input type="hidden" name="price" value="<?= $product['discounted_price'] ?>">
                        <input type="hidden" name="image" value="<?= $product['image_raw'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $csrf->getToken() ?>">
                        <input type="number" name="qty" value="1" min="1" max="99" class="qty" style="display:none;">
                        
                        <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
                        <button type="submit" name="add_to_wishlist" class="wishlist-btn" aria-label="Add to wishlist">
                           <i class="far fa-heart"></i>
                        </button>
                     </form>
                  </div>
               </div>
            <?php endforeach; ?>
         </div>
      <?php elseif ($loadError): ?>
         <div class="empty">
            <p>Error loading products. Please try again later.</p>
            <a href="shop.php" class="btn">Browse Products</a>
         </div>
      <?php else: ?>
         <div class="empty">
            <p>No featured products found at the moment</p>
            <a href="shop.php" class="btn">Browse All Products</a>
         </div>
      <?php endif; ?>
      
      <div style="text-align: center; margin-top: 4rem;">
         <a href="shop.php" class="btn" style="padding: 1.2rem 3rem; font-size: 1.6rem;">View All Products</a>
      </div>
   </div>
</section>

<!-- Footer -->
<?php include 'components/footer.php' ?>

<!-- JavaScript Libraries -->
<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
// Initialize all sliders when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
   // Home slider with autoplay and pagination
   var homeSwiper = new Swiper(".home-slider", {
      loop: true,
      grabCursor: true,
      effect: 'fade',
      speed: 1000,
      autoplay: {
         delay: 5000,
         disableOnInteraction: false,
      },
      pagination: {
         el: ".swiper-pagination",
         clickable: true,
      },
   });

   // Add to cart/wishlist animation
   document.querySelectorAll('.add-to-cart, .wishlist-btn').forEach(button => {
      button.addEventListener('click', function() {
         this.classList.add('animate__animated', 'animate__pulse');
         setTimeout(() => {
            this.classList.remove('animate__animated', 'animate__pulse');
         }, 1000);
      });
   });
});
</script>
</body>
</html>