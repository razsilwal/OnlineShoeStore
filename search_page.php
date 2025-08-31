<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

// Pagination setup
$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Price range filter
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;

// Sort options
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Get all products from database
$all_products = [];
$total_results = 0;
$search_results = [];
$search_term = isset($_GET['search_box']) ? trim($_GET['search_box']) : '';
$search_message = '';

try {
    $stmt = $conn->query("SELECT * FROM `products`");
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo '<div class="no-results">
            <i class="fas fa-exclamation-triangle"></i>
            <p class="empty">Unable to load products</p>
          </div>';
}

if(!empty($search_term) && !empty($all_products)){
    // Perform linear search on the product array
    $matched_products = [];
    
    foreach($all_products as $product) {
        // Check if product matches search term and price range
        $matches_search = stripos($product['name'], $search_term) !== false || 
                         stripos($product['details'], $search_term) !== false;
        $matches_price = $product['price'] >= $min_price && $product['price'] <= $max_price;
        
        if($matches_search && $matches_price) {
            $matched_products[] = $product;
        }
    }
    
    $total_results = count($matched_products);
    
    // Apply sorting
    switch($sort) {
        case 'name_desc': 
            usort($matched_products, function($a, $b) {
                return strcmp($b['name'], $a['name']);
            });
            break;
        case 'price_asc': 
            usort($matched_products, function($a, $b) {
                return $a['price'] <=> $b['price'];
            });
            break;
        case 'price_desc': 
            usort($matched_products, function($a, $b) {
                return $b['price'] <=> $a['price'];
            });
            break;
        default: // name_asc
            usort($matched_products, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
    }
    
    // Apply pagination
    $search_results = array_slice($matched_products, $offset, $per_page);
    
    $search_message = "Search results for:";
}

// Calculate total pages for pagination
$total_pages = ceil($total_results / $per_page);

// Get price range for filter
$prices = array_column($all_products, 'price');
$global_min_price = !empty($prices) ? floor(min($prices)) : 0;
$global_max_price = !empty($prices) ? ceil(max($prices)) : 10000;
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Products - Discover Amazing Items</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }
      
      body {
         font-family: 'Inter', sans-serif;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         min-height: 100vh;
         color: #333;
      }
      
      .search-container {
         max-width: 1400px;
         margin: 0 auto;
         padding: 20px;
      }
      
      .search-hero {
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-radius: 24px;
         padding: 40px;
         margin-bottom: 30px;
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
      }
      
      .hero-title {
         text-align: center;
         font-size: 2.5rem;
         font-weight: 700;
         background: linear-gradient(135deg, #667eea, #764ba2);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin-bottom: 10px;
      }
      
      .hero-subtitle {
         text-align: center;
         color: #64748b;
         font-size: 1.1rem;
         margin-bottom: 30px;
      }
      
      .search-form {
         max-width: 700px;
         margin: 0 auto;
         position: relative;
      }
      
      .search-form form {
         display: flex;
         background: #fff;
         border-radius: 60px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
         border: 2px solid transparent;
         transition: all 0.3s ease;
      }
      
      .search-form form:focus-within {
         border-color: #667eea;
         box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
      }
      
      .search-form .box {
         flex: 1;
         padding: 18px 25px;
         font-size: 16px;
         border: none;
         outline: none;
         background: transparent;
      }
      
      .search-form .btn-search {
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         border: none;
         padding: 18px 30px;
         cursor: pointer;
         transition: all 0.3s ease;
         font-size: 16px;
         display: flex;
         align-items: center;
         gap: 8px;
      }
      
      .search-form .btn-search:hover {
         background: linear-gradient(135deg, #5a6fd8, #6b42a0);
         transform: translateX(-2px);
      }
      
      .filter-toggle {
         display: none;
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         border: none;
         padding: 12px 20px;
         border-radius: 12px;
         cursor: pointer;
         font-weight: 500;
         box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
         transition: all 0.3s ease;
      }
      
      .filter-toggle:hover {
         transform: translateY(-2px);
         box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
      }
      
      .search-content {
         display: flex;
         gap: 30px;
         align-items: flex-start;
      }
      
      .filter-sidebar {
         flex: 0 0 320px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-radius: 20px;
         padding: 30px;
         box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
         position: sticky;
         top: 20px;
      }
      
      .filter-title {
         font-size: 1.3rem;
         font-weight: 600;
         margin-bottom: 25px;
         color: #1e293b;
         display: flex;
         align-items: center;
         gap: 10px;
      }
      
      .filter-group {
         margin-bottom: 25px;
         padding-bottom: 25px;
         border-bottom: 1px solid rgba(0, 0, 0, 0.1);
      }
      
      .filter-group:last-of-type {
         border-bottom: none;
         margin-bottom: 0;
      }
      
      .filter-group h3 {
         font-size: 1.1rem;
         font-weight: 600;
         color: #374151;
         margin-bottom: 15px;
         display: flex;
         align-items: center;
         gap: 8px;
      }
      
      .price-inputs {
         display: flex;
         gap: 15px;
      }
      
      .price-input-group {
         flex: 1;
         position: relative;
      }
      
      .price-input-group label {
         display: block;
         font-size: 0.9rem;
         font-weight: 500;
         color: #64748b;
         margin-bottom: 5px;
      }
      
      .price-inputs input {
         width: 100%;
         padding: 12px 15px;
         border: 2px solid #e5e7eb;
         border-radius: 12px;
         font-size: 14px;
         transition: all 0.3s ease;
         background: #fff;
      }
      
      .price-inputs input:focus {
         border-color: #667eea;
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
         outline: none;
      }
      
      .filter-btn {
         width: 100%;
         padding: 14px;
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         border: none;
         border-radius: 12px;
         cursor: pointer;
         font-weight: 600;
         font-size: 16px;
         transition: all 0.3s ease;
         box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      }
      
      .filter-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
      }
      
      .products-section {
         flex: 1;
         min-width: 0;
      }
      
      .section-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 25px;
         flex-wrap: wrap;
         gap: 15px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         padding: 20px 25px;
         border-radius: 16px;
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
      }
      
      .results-info {
         font-weight: 500;
         color: #64748b;
      }
      
      .sort-options select {
         padding: 10px 15px;
         border: 2px solid #e5e7eb;
         border-radius: 10px;
         background: #fff;
         font-size: 14px;
         font-weight: 500;
         color: #374151;
         cursor: pointer;
         transition: all 0.3s ease;
      }
      
      .sort-options select:focus {
         border-color: #667eea;
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
         outline: none;
      }
      
      .products .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 25px;
      }
      
      .products .box {
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-radius: 20px;
         overflow: hidden;
         box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
         transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
         position: relative;
         padding: 0;
      }
      
      .products .box:hover {
         transform: translateY(-8px) scale(1.02);
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      }
      
      .products .box img {
         width: 100%;
         height: 220px;
         object-fit: cover;
         border-radius: 16px 16px 0 0;
      }
      
      .product-content {
         padding: 20px;
      }
      
      .products .box .name {
         font-size: 1.1rem;
         font-weight: 600;
         color: #1e293b;
         margin-bottom: 12px;
         line-height: 1.4;
      }
      
      .products .box .flex {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 15px;
      }
      
      .products .box .price {
         font-size: 1.3rem;
         font-weight: 700;
         background: linear-gradient(135deg, #667eea, #764ba2);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }
      
      .products .box .qty {
         width: 60px;
         padding: 8px;
         border: 2px solid #e5e7eb;
         border-radius: 8px;
         text-align: center;
         font-weight: 500;
         transition: all 0.3s ease;
      }
      
      .products .box .qty:focus {
         border-color: #667eea;
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
         outline: none;
      }
      
      .product-actions {
         display: flex;
         gap: 10px;
         margin-bottom: 15px;
      }
      
      .products .box .fas {
         position: absolute;
         top: 15px;
         right: 15px;
         width: 45px;
         height: 45px;
         display: flex;
         align-items: center;
         justify-content: center;
         background: rgba(255, 255, 255, 0.9);
         border: none;
         border-radius: 50%;
         cursor: pointer;
         transition: all 0.3s ease;
         box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      }
      
      .products .box .fas:hover {
         background: #667eea;
         color: white;
         transform: scale(1.1);
      }
      
      .products .box .fa-eye {
         right: 70px;
      }
      
      .products .box .btn {
         width: 100%;
         padding: 12px;
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         border: none;
         border-radius: 12px;
         font-weight: 600;
         font-size: 14px;
         cursor: pointer;
         transition: all 0.3s ease;
         text-transform: uppercase;
         letter-spacing: 0.5px;
      }
      
      .products .box .btn:hover {
         background: linear-gradient(135deg, #5a6fd8, #6b42a0);
         transform: translateY(-2px);
         box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      }
      
      .no-results {
         text-align: center;
         padding: 60px 40px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-radius: 20px;
         box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
         grid-column: 1 / -1;
      }
      
      .no-results i {
         font-size: 4rem;
         color: #cbd5e1;
         margin-bottom: 20px;
         display: block;
      }
      
      .no-results .empty {
         font-size: 1.5rem;
         font-weight: 600;
         color: #64748b;
         margin-bottom: 10px;
      }
      
      .search-info {
         text-align: center;
         margin: 30px 0;
         padding: 20px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-radius: 16px;
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
         grid-column: 1 / -1;
         color: #64748b;
         font-weight: 500;
      }
      
      .search-info strong {
         color: #667eea;
         font-weight: 700;
      }
      
      .pagination {
         display: flex;
         justify-content: center;
         margin: 40px 0;
         flex-wrap: wrap;
         gap: 8px;
      }
      
      .pagination a,
      .pagination span {
         padding: 12px 16px;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border: 2px solid transparent;
         border-radius: 12px;
         color: #64748b;
         text-decoration: none;
         font-weight: 500;
         transition: all 0.3s ease;
         box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      }
      
      .pagination a:hover {
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         transform: translateY(-2px);
         box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      }
      
      .pagination a.active {
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      }
      
      @media (max-width: 1024px) {
         .search-content {
            flex-direction: column;
            gap: 20px;
         }
         
         .filter-sidebar {
            flex: none;
            position: relative;
            top: 0;
         }
      }
      
      @media (max-width: 768px) {
         .search-container {
            padding: 15px;
         }
         
         .search-hero {
            padding: 30px 20px;
            border-radius: 16px;
         }
         
         .hero-title {
            font-size: 2rem;
         }
         
         .filter-sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            width: 90%;
            max-width: 350px;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            border-radius: 0;
         }
         
         .filter-sidebar.active {
            left: 0;
         }
         
         .filter-toggle {
            display: block;
            margin-bottom: 20px;
         }
         
         .products .box-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
         }
         
         .section-header {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
         }
         
         .pagination {
            gap: 5px;
         }
         
         .pagination a,
         .pagination span {
            padding: 10px 12px;
            font-size: 14px;
         }
      }
      
      @media (max-width: 480px) {
         .hero-title {
            font-size: 1.8rem;
         }
         
         .products .box-container {
            grid-template-columns: 1fr;
         }
         
         .search-form .box {
            padding: 16px 20px;
            font-size: 15px;
         }
         
         .search-form .btn-search {
            padding: 16px 25px;
         }
      }
      
      /* Backdrop for mobile filter */
      .filter-backdrop {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0, 0, 0, 0.5);
         backdrop-filter: blur(4px);
         z-index: 999;
      }
      
      .filter-backdrop.active {
         display: block;
      }
      
      /* Loading animation */
      .loading {
         display: inline-block;
         width: 20px;
         height: 20px;
         border: 2px solid rgba(255, 255, 255, 0.3);
         border-radius: 50%;
         border-top-color: #fff;
         animation: spin 1s ease-in-out infinite;
      }
      
      @keyframes spin {
         to { transform: rotate(360deg); }
      }
      
      /* Smooth scrolling */
      html {
         scroll-behavior: smooth;
      }
      
      /* Custom scrollbar */
      ::-webkit-scrollbar {
         width: 8px;
      }
      
      ::-webkit-scrollbar-track {
         background: rgba(0, 0, 0, 0.1);
         border-radius: 4px;
      }
      
      ::-webkit-scrollbar-thumb {
         background: linear-gradient(135deg, #667eea, #764ba2);
         border-radius: 4px;
      }
      
      ::-webkit-scrollbar-thumb:hover {
         background: linear-gradient(135deg, #5a6fd8, #6b42a0);
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="search-container">
   <!-- Search Hero Section -->
   <div class="search-hero">
      <h1 class="hero-title">Discover Amazing Products</h1>
      <p class="hero-subtitle">Find exactly what you're looking for with our advanced search</p>
      
      <div class="search-form">
         <form action="" method="get">
            <input type="text" name="search_box" placeholder="Search for products, brands, categories..." class="box" 
                   value="<?= htmlspecialchars($search_term) ?>" required>
            <button type="submit" class="btn-search">
               <i class="fas fa-search"></i>
               Search
            </button>
         </form>
      </div>
   </div>

   <button class="filter-toggle" id="filterToggle">
      <i class="fas fa-filter"></i> Filters & Sort
   </button>

   <div class="search-content">
      <!-- Filter Sidebar -->
      <aside class="filter-sidebar" id="filterSidebar">
         <div class="filter-title">
            <i class="fas fa-sliders-h"></i>
            Filter Products
         </div>
         
         <div class="filter-group">
            <h3><i class="fas fa-tags"></i> Price Range</h3>
            <div class="price-inputs">
               <div class="price-input-group">
                  <label>Min Price</label>
                  <input type="number" id="min-price" name="min_price" placeholder="₹0" 
                         value="<?= $min_price ?>" min="<?= $global_min_price ?>" max="<?= $global_max_price ?>">
               </div>
               <div class="price-input-group">
                  <label>Max Price</label>
                  <input type="number" id="max-price" name="max_price" placeholder="₹10000" 
                         value="<?= $max_price ?>" min="<?= $global_min_price ?>" max="<?= $global_max_price ?>">
               </div>
            </div>
         </div>
         
         <button type="button" class="filter-btn" id="applyFilters">
            <i class="fas fa-check"></i> Apply Filters
         </button>
      </aside>
      
      <!-- Products Section -->
      <section class="products-section">
         <?php if(!empty($search_term)): ?>
         <div class="section-header">
            <div class="results-info">
               <?php if(!empty($search_results)): ?>
                  Showing <?= count($search_results) ?> of <?= $total_results ?> results
               <?php else: ?>
                  No results found
               <?php endif; ?>
            </div>
            <div class="sort-options">
               <select id="sortBy">
                  <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                  <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                  <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                  <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
               </select>
            </div>
         </div>
         <?php endif; ?>
         
         <section class="products">
            <div class="box-container">
            <?php
            if(!empty($search_term)){
               echo '<div class="search-info">'.$search_message.' <strong>"'.htmlspecialchars($search_term).'"</strong></div>';
               
               if(!empty($search_results)){
                  foreach($search_results as $product){
            ?>
            <form action="" method="post" class="box">
               <input type="hidden" name="pid" value="<?= $product['id'] ?>">
               <input type="hidden" name="name" value="<?= $product['name'] ?>">
               <input type="hidden" name="price" value="<?= $product['price'] ?>">
               <input type="hidden" name="image" value="<?= $product['image_01'] ?>">
               
               <button class="fas fa-heart" type="submit" name="add_to_wishlist" title="Add to Wishlist"></button>
               <a href="quick_view.php?pid=<?= $product['id'] ?>" class="fas fa-eye" title="Quick View"></a>
               
               <img src="uploaded_img/<?= $product['image_01'] ?>" alt="<?= $product['name'] ?>" loading="lazy">
               
               <div class="product-content">
                  <div class="name"><?= $product['name'] ?></div>
                  <div class="flex">
                     <div class="price"><span>₹</span><?= $product['price'] ?><span>/-</span></div>
                     <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
                  </div>
                  <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
               </div>
            </form>
            <?php
                  }
               } else {
                  echo '<div class="no-results">
                           <i class="fas fa-search-minus"></i>
                           <p class="empty">No products found matching your search!</p>
                           <p>Try adjusting your filters or search terms.</p>
                        </div>';
               }
               
               // Pagination
               if($total_pages > 1){
                  echo '<div class="pagination">';
                  if($page > 1){
                     echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.($page-1).'">&laquo; Previous</a>';
                  }
                  
                  // Show page numbers
                  $start_page = max(1, $page - 2);
                  $end_page = min($total_pages, $page + 2);
                  
                  if($start_page > 1) echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page=1">1</a>';
                  if($start_page > 2) echo '<span>...</span>';
                  
                  for($i = $start_page; $i <= $end_page; $i++){
                     if($i == $page){
                        echo '<a class="active">'.$i.'</a>';
                     } else {
                        echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.$i.'">'.$i.'</a>';
                     }
                  }
                  
                  if($end_page < $total_pages - 1) echo '<span>...</span>';
                  if($end_page < $total_pages) echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.$total_pages.'">'.$total_pages.'</a>';
                  
                  if($page < $total_pages){
                     echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.($page+1).'">Next &raquo;</a>';
                  }
                  echo '</div>';
               }
            } else {
               echo '<div class="no-results">
                        <i class="fas fa-search"></i>
                        <p class="empty">Start your product search</p>
                        <p>Use the search bar above to find amazing products</p>
                     </div>';
            }
            ?>
            </div>
         </section>
      </section>
   </div>
</div>

<!-- Filter Backdrop for Mobile -->
<div class="filter-backdrop" id="filterBackdrop"></div>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile filter toggle
    const filterToggle = document.getElementById('filterToggle');
    const filterSidebar = document.getElementById('filterSidebar');
    const filterBackdrop = document.getElementById('filterBackdrop');
    
    if(filterToggle && filterSidebar) {
        filterToggle.addEventListener('click', function() {
            filterSidebar.classList.toggle('active');
            filterBackdrop.classList.toggle('active');
            document.body.style.overflow = filterSidebar.classList.contains('active') ? 'hidden' : '';
        });
        
        // Close filter when clicking backdrop
        if(filterBackdrop) {
            filterBackdrop.addEventListener('click', function() {
                filterSidebar.classList.remove('active');
                filterBackdrop.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    }
    
    // Apply filters with loading state
    const applyFilters = document.getElementById('applyFilters');
    if(applyFilters) {
        applyFilters.addEventListener('click', function() {
            const searchTerm = document.querySelector('input[name="search_box"]').value;
            const minPrice = document.getElementById('min-price').value || <?= $global_min_price ?>;
            const maxPrice = document.getElementById('max-price').value || <?= $global_max_price ?>;
            const sortBy = document.getElementById('sortBy').value;
            
            // Show loading state
            this.innerHTML = '<div class="loading"></div> Applying...';
            this.disabled = true;
            
            window.location.href = `?search_box=${encodeURIComponent(searchTerm)}&min_price=${minPrice}&max_price=${maxPrice}&sort=${sortBy}`;
        });
    }
    
    // Sort options with loading feedback
    const sortBy = document.getElementById('sortBy');
    if(sortBy) {
        sortBy.addEventListener('change', function() {
            const searchTerm = document.querySelector('input[name="search_box"]').value;
            const minPrice = document.getElementById('min-price').value || <?= $global_min_price ?>;
            const maxPrice = document.getElementById('max-price').value || <?= $global_max_price ?>;
            const sortValue = this.value;
            
            // Show loading state
            this.style.opacity = '0.7';
            this.disabled = true;
            
            window.location.href = `?search_box=${encodeURIComponent(searchTerm)}&min_price=${minPrice}&max_price=${maxPrice}&sort=${sortValue}`;
        });
    }
    
    // Price input validation with better UX
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    
    if(minPriceInput && maxPriceInput) {
        minPriceInput.addEventListener('input', function() {
            if(parseFloat(this.value) > parseFloat(maxPriceInput.value) && maxPriceInput.value) {
                maxPriceInput.value = this.value;
                maxPriceInput.style.borderColor = '#fbbf24';
                setTimeout(() => {
                    maxPriceInput.style.borderColor = '#e5e7eb';
                }, 1000);
            }
        });
        
        maxPriceInput.addEventListener('input', function() {
            if(parseFloat(this.value) < parseFloat(minPriceInput.value) && minPriceInput.value) {
                minPriceInput.value = this.value;
                minPriceInput.style.borderColor = '#fbbf24';
                setTimeout(() => {
                    minPriceInput.style.borderColor = '#e5e7eb';
                }, 1000);
            }
        });
    }
    
    // Enhanced search form submission
    const searchForm = document.querySelector('.search-form form');
    if(searchForm) {
        searchForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.btn-search');
            submitBtn.innerHTML = '<div class="loading"></div> Searching...';
            submitBtn.disabled = true;
        });
    }
    
    // Smooth scroll to results after search
    if(window.location.search.includes('search_box=')) {
        setTimeout(() => {
            const results = document.querySelector('.products-section');
            if(results) {
                results.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }
    
    // Add to cart with feedback
    const addToCartBtns = document.querySelectorAll('input[name="add_to_cart"]');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const originalText = this.value;
            this.value = 'Adding...';
            this.style.opacity = '0.7';
            this.disabled = true;
            
            // Re-enable after form submission
            setTimeout(() => {
                this.value = originalText;
                this.style.opacity = '1';
                this.disabled = false;
            }, 2000);
        });
    });
    
    // Wishlist heart animation
    const wishlistBtns = document.querySelectorAll('.fa-heart');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            this.style.transform = 'scale(1.2)';
            this.style.color = '#ef4444';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);
        });
    });
    
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe product boxes for scroll animation
    const productBoxes = document.querySelectorAll('.products .box');
    productBoxes.forEach((box, index) => {
        box.style.opacity = '0';
        box.style.transform = 'translateY(20px)';
        box.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(box);
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="search_box"]');
            if(searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape to close mobile filter
        if(e.key === 'Escape' && filterSidebar && filterSidebar.classList.contains('active')) {
            filterSidebar.classList.remove('active');
            filterBackdrop.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Add search shortcut hint
    const searchInput = document.querySelector('input[name="search_box"]');
    if(searchInput && !searchInput.value) {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        const shortcut = isMac ? '⌘K' : 'Ctrl+K';
        searchInput.placeholder += ` (Press ${shortcut} to focus)`;
    }
});

// Service Worker for offline functionality (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('SW registered: ', registration);
        }).catch(function(registrationError) {
            console.log('SW registration failed: ', registrationError);
        });
    });
}
</script>
</body>
</html>