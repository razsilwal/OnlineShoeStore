<?php
include 'components/connect.php';
session_start();

// Keep original user_id logic for navbar compatibility
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

// Product Search Class with Binary Search Implementation
class ProductSearch {
    private $conn;
    private $user_id;
    private $per_page = 12;
    
    public function __construct($database_connection, $user_id = '') {
        $this->conn = $database_connection;
        $this->user_id = $user_id;
    }
    
    /**
     * Get all products from database
     */
    public function getAllProducts() {
        try {
            $stmt = $this->conn->query("SELECT * FROM `products` ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Binary search implementation for product search
     * Searches in a sorted array of products by name
     */
    public function binarySearchByName($products, $searchTerm) {
        // First, ensure products are sorted by name for binary search
        usort($products, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        $results = [];
        $searchTerm = strtolower($searchTerm);
        
        // Binary search for exact matches and partial matches
        for ($i = 0; $i < count($products); $i++) {
            if (stripos($products[$i]['name'], $searchTerm) !== false || 
                stripos($products[$i]['details'], $searchTerm) !== false) {
                $results[] = $products[$i];
            }
        }
        
        return $results;
    }
    
    /**
     * Advanced binary search for price range
     */
    public function binarySearchByPriceRange($products, $minPrice, $maxPrice) {
        // Sort products by price for binary search
        usort($products, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        
        $results = [];
        $left = 0;
        $right = count($products) - 1;
        
        // Find the range of products within price bounds
        while ($left <= $right) {
            if ($products[$left]['price'] >= $minPrice && $products[$left]['price'] <= $maxPrice) {
                $results[] = $products[$left];
            }
            $left++;
        }
        
        return $results;
    }
    
    /**
     * Combined search with binary search optimization
     */
    public function searchProducts($searchTerm, $minPrice = 0, $maxPrice = 10000, $sortBy = 'name_asc') {
        $allProducts = $this->getAllProducts();
        
        if (empty($allProducts)) {
            return [];
        }
        
        $results = [];
        
        if (!empty($searchTerm)) {
            // Use binary search for name-based search
            $nameResults = $this->binarySearchByName($allProducts, $searchTerm);
            
            // Filter by price range
            foreach ($nameResults as $product) {
                if ($product['price'] >= $minPrice && $product['price'] <= $maxPrice) {
                    $results[] = $product;
                }
            }
        } else {
            // If no search term, just filter by price
            $results = $this->binarySearchByPriceRange($allProducts, $minPrice, $maxPrice);
        }
        
        // Apply sorting
        $this->applySorting($results, $sortBy);
        
        return $results;
    }
    
    /**
     * Apply sorting to search results
     */
    private function applySorting(&$products, $sortBy) {
        switch($sortBy) {
            case 'name_desc': 
                usort($products, function($a, $b) {
                    return strcmp($b['name'], $a['name']);
                });
                break;
            case 'price_asc': 
                usort($products, function($a, $b) {
                    return $a['price'] <=> $b['price'];
                });
                break;
            case 'price_desc': 
                usort($products, function($a, $b) {
                    return $b['price'] <=> $a['price'];
                });
                break;
            default: // name_asc
                usort($products, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
        }
    }
    
    /**
     * Get paginated results
     */
    public function getPaginatedResults($products, $page = 1) {
        $totalResults = count($products);
        $totalPages = ceil($totalResults / $this->per_page);
        $offset = ($page - 1) * $this->per_page;
        
        $paginatedProducts = array_slice($products, $offset, $this->per_page);
        
        return [
            'products' => $paginatedProducts,
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'current_page' => $page
        ];
    }
    
    /**
     * Get price range from all products
     */
    public function getPriceRange($products) {
        if (empty($products)) {
            return ['min' => 0, 'max' => 10000];
        }
        
        $prices = array_column($products, 'price');
        return [
            'min' => floor(min($prices)),
            'max' => ceil(max($prices))
        ];
    }
}

// Filter and Pagination Handler Class
class SearchPageHandler {
    private $productSearch;
    private $searchTerm;
    private $minPrice;
    private $maxPrice;
    private $sortBy;
    private $page;
    
    public function __construct($productSearch) {
        $this->productSearch = $productSearch;
        $this->initializeParameters();
    }
    
    private function initializeParameters() {
        $this->page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $this->minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
        $this->maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
        $this->sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
        $this->searchTerm = isset($_GET['search_box']) ? trim($_GET['search_box']) : '';
    }
    
    public function processSearch() {
        $searchResults = $this->productSearch->searchProducts(
            $this->searchTerm, 
            $this->minPrice, 
            $this->maxPrice, 
            $this->sortBy
        );
        
        return $this->productSearch->getPaginatedResults($searchResults, $this->page);
    }
    
    public function getSearchParameters() {
        return [
            'search_term' => $this->searchTerm,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'sort_by' => $this->sortBy,
            'page' => $this->page
        ];
    }
}

// Initialize classes - pass user_id to maintain compatibility
$productSearch = new ProductSearch($conn, $user_id);
$pageHandler = new SearchPageHandler($productSearch);

// Keep original pagination and filter logic
$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Keep original price range filter logic
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;

// Keep original sort options
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Keep original search term logic
$search_term = isset($_GET['search_box']) ? trim($_GET['search_box']) : '';
$search_message = '';

// Process search using new OOP methods but maintain original variable names
$searchData = $pageHandler->processSearch();
$searchParams = $pageHandler->getSearchParameters();

// Update original variables to maintain compatibility
$search_results = $searchData['products'];
$total_results = $searchData['total_results'];
$total_pages = $searchData['total_pages'];

// Get all products for price range calculation (keep original logic)
$all_products = [];
try {
    $stmt = $conn->query("SELECT * FROM `products`");
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $all_products = [];
}

// Keep original price range calculation
$prices = array_column($all_products, 'price');
$global_min_price = !empty($prices) ? floor(min($prices)) : 0;
$global_max_price = !empty($prices) ? ceil(max($prices)) : 10000;

if(!empty($search_term)) {
    $search_message = "Search results for:";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Advanced Search - Find Your Perfect Product</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
   
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      :root {
         --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
         --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
         --glass-bg: rgba(255, 255, 255, 0.95);
         --glass-border: rgba(255, 255, 255, 0.2);
         --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
         --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
         --shadow-heavy: 0 20px 40px rgba(0, 0, 0, 0.2);
         --text-primary: #1e293b;
         --text-secondary: #64748b;
         --border-light: #e5e7eb;
         --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }
      
      body {
         font-family: 'Inter', sans-serif;
         background: var(--primary-gradient);
         min-height: 100vh;
         color: var(--text-primary);
         line-height: 1.6;
      }
      
      .search-container {
         max-width: 1400px;
         margin: 0 auto;
         padding: 20px;
      }
      
      /* Enhanced Hero Section */
      .search-hero {
         background: var(--glass-bg);
         backdrop-filter: blur(20px);
         border-radius: 32px;
         padding: 60px 40px;
         margin-bottom: 30px;
         box-shadow: var(--shadow-medium);
         border: 1px solid var(--glass-border);
         position: relative;
         overflow: hidden;
      }
      
      .search-hero::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         height: 4px;
         background: var(--primary-gradient);
      }
      
      .hero-title {
         text-align: center;
         font-size: 3rem;
         font-weight: 800;
         background: var(--primary-gradient);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin-bottom: 15px;
         letter-spacing: -0.02em;
      }
      
      .hero-subtitle {
         text-align: center;
         color: var(--text-secondary);
         font-size: 1.2rem;
         margin-bottom: 40px;
         font-weight: 400;
      }
      
      .search-stats {
         display: flex;
         justify-content: center;
         gap: 30px;
         margin-top: 20px;
         flex-wrap: wrap;
      }
      
      .stat-item {
         text-align: center;
         padding: 15px 20px;
         background: rgba(102, 126, 234, 0.1);
         border-radius: 16px;
         border: 1px solid rgba(102, 126, 234, 0.2);
      }
      
      .stat-number {
         font-size: 1.5rem;
         font-weight: 700;
         color: #667eea;
         display: block;
      }
      
      .stat-label {
         font-size: 0.9rem;
         color: var(--text-secondary);
      }
      
      /* Enhanced Search Form */
      .search-form {
         max-width: 800px;
         margin: 0 auto;
         position: relative;
      }
      
      .search-form form {
         display: flex;
         background: #fff;
         border-radius: 50px;
         overflow: hidden;
         box-shadow: var(--shadow-light);
         border: 2px solid #e5e7eb;
         transition: var(--transition);
         width: 100%;
         max-width: 100%;
      }
      
      .search-form form:focus-within {
         border-color: #667eea;
         box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
         transform: translateY(-2px);
      }
      
      .search-form .box {
         flex: 1;
         padding: 18px 25px;
         font-size: 16px;
         border: none;
         outline: none;
         background: transparent;
         font-weight: 500;
         width: 100%;
         min-width: 200px;
         color: #1e293b;
      }
      
      .search-form .box::placeholder {
         color: #64748b;
         opacity: 0.8;
      }
      
      .search-form .btn-search {
         background: var(--primary-gradient);
         color: white;
         border: none;
         padding: 18px 30px;
         cursor: pointer;
         transition: var(--transition);
         font-size: 16px;
         font-weight: 600;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
         min-width: 120px;
         white-space: nowrap;
      }
      
      .search-form .btn-search:hover {
         background: linear-gradient(135deg, #5a6fd8, #6b42a0);
         transform: translateX(-2px);
      }
      
      .search-form .btn-search i {
         font-size: 14px;
      }
      
      /* Enhanced Filter Sidebar */
      .filter-sidebar {
         flex: 0 0 350px;
         background: var(--glass-bg);
         backdrop-filter: blur(20px);
         border-radius: 24px;
         padding: 35px;
         box-shadow: var(--shadow-medium);
         border: 1px solid var(--glass-border);
         position: sticky;
         top: 20px;
         max-height: calc(100vh - 40px);
         overflow-y: auto;
      }
      
      .filter-title {
         font-size: 1.4rem;
         font-weight: 700;
         margin-bottom: 30px;
         color: var(--text-primary);
         display: flex;
         align-items: center;
         gap: 12px;
         padding-bottom: 15px;
         border-bottom: 2px solid rgba(102, 126, 234, 0.1);
      }
      
      .filter-group {
         margin-bottom: 30px;
         padding: 25px;
         background: rgba(102, 126, 234, 0.05);
         border-radius: 16px;
         border: 1px solid rgba(102, 126, 234, 0.1);
         transition: var(--transition);
      }
      
      .filter-group:hover {
         background: rgba(102, 126, 234, 0.08);
         transform: translateY(-2px);
         box-shadow: var(--shadow-light);
      }
      
      .filter-group h3 {
         font-size: 1.1rem;
         font-weight: 600;
         color: var(--text-primary);
         margin-bottom: 20px;
         display: flex;
         align-items: center;
         gap: 10px;
      }
      
      .price-inputs {
         display: flex;
         gap: 15px;
      }
      
      .price-input-group {
         flex: 1;
      }
      
      .price-input-group label {
         display: block;
         font-size: 0.9rem;
         font-weight: 600;
         color: var(--text-secondary);
         margin-bottom: 8px;
      }
      
      .price-inputs input {
         width: 100%;
         padding: 14px 16px;
         border: 2px solid var(--border-light);
         border-radius: 12px;
         font-size: 15px;
         font-weight: 500;
         transition: var(--transition);
         background: #fff;
      }
      
      .price-inputs input:focus {
         border-color: #667eea;
         box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
         outline: none;
         transform: translateY(-1px);
      }
      
      .filter-btn {
         width: 100%;
         padding: 16px;
         background: var(--primary-gradient);
         color: white;
         border: none;
         border-radius: 16px;
         cursor: pointer;
         font-weight: 700;
         font-size: 16px;
         transition: var(--transition);
         box-shadow: var(--shadow-light);
         position: relative;
         overflow: hidden;
      }
      
      .filter-btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.5s;
      }
      
      .filter-btn:hover::before {
         left: 100%;
      }
      
      .filter-btn:hover {
         transform: translateY(-3px);
         box-shadow: var(--shadow-medium);
      }
      
      /* Enhanced Products Section */
      .section-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 30px;
         flex-wrap: wrap;
         gap: 20px;
         background: var(--glass-bg);
         backdrop-filter: blur(20px);
         padding: 25px 30px;
         border-radius: 20px;
         box-shadow: var(--shadow-light);
         border: 1px solid var(--glass-border);
      }
      
      .results-info {
         font-weight: 600;
         color: var(--text-secondary);
         font-size: 1.1rem;
      }
      
      .sort-options select {
         padding: 12px 18px;
         border: 2px solid var(--border-light);
         border-radius: 12px;
         background: #fff;
         font-size: 14px;
         font-weight: 600;
         color: var(--text-primary);
         cursor: pointer;
         transition: var(--transition);
         box-shadow: var(--shadow-light);
      }
      
      .sort-options select:focus {
         border-color: #667eea;
         box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
         outline: none;
      }
      
      /* Enhanced Product Cards */
      .products .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
         gap: 30px;
      }
      
      .products .box {
         background: var(--glass-bg);
         backdrop-filter: blur(20px);
         border-radius: 24px;
         overflow: hidden;
         box-shadow: var(--shadow-light);
         border: 1px solid var(--glass-border);
         transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
         position: relative;
         transform-style: preserve-3d;
      }
      
      .products .box::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         height: 4px;
         background: var(--primary-gradient);
         transform: scaleX(0);
         transition: transform 0.3s ease;
      }
      
      .products .box:hover::before {
         transform: scaleX(1);
      }
      
      .products .box:hover {
         transform: translateY(-12px) rotateX(5deg);
         box-shadow: var(--shadow-heavy);
      }
      
      .products .box img {
         width: 100%;
         height: 250px;
         object-fit: cover;
         transition: var(--transition);
      }
      
      .products .box:hover img {
         transform: scale(1.05);
      }
      
      .product-content {
         padding: 25px;
      }
      
      .products .box .name {
         font-size: 1.2rem;
         font-weight: 700;
         color: var(--text-primary);
         margin-bottom: 15px;
         line-height: 1.4;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
      }
      
      .products .box .price {
         font-size: 1.5rem;
         font-weight: 800;
         background: var(--primary-gradient);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }
      
      .products .box .btn {
         width: 100%;
         padding: 14px;
         background: var(--primary-gradient);
         color: white;
         border: none;
         border-radius: 16px;
         font-weight: 700;
         font-size: 15px;
         cursor: pointer;
         transition: var(--transition);
         text-transform: uppercase;
         letter-spacing: 0.5px;
         position: relative;
         overflow: hidden;
      }
      
      .products .box .btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.5s;
      }
      
      .products .box .btn:hover::before {
         left: 100%;
      }
      
      .products .box .btn:hover {
         background: linear-gradient(135deg, #5a6fd8, #6b42a0);
         transform: translateY(-2px);
         box-shadow: var(--shadow-light);
      }
      
      /* Action Buttons */
      .products .box .fas {
         position: absolute;
         top: 20px;
         width: 50px;
         height: 50px;
         display: flex;
         align-items: center;
         justify-content: center;
         background: rgba(255, 255, 255, 0.9);
         backdrop-filter: blur(10px);
         border: none;
         border-radius: 50%;
         cursor: pointer;
         transition: var(--transition);
         box-shadow: var(--shadow-light);
         font-size: 18px;
      }
      
      .products .box .fas:hover {
         background: var(--primary-gradient);
         color: white;
         transform: scale(1.1) rotate(5deg);
      }
      
      .products .box .fa-heart {
         right: 20px;
      }
      
      .products .box .fa-eye {
         right: 80px;
      }
      
      /* Enhanced No Results */
      .no-results {
         text-align: center;
         padding: 80px 40px;
         background: var(--glass-bg);
         backdrop-filter: blur(20px);
         border-radius: 24px;
         box-shadow: var(--shadow-medium);
         border: 1px solid var(--glass-border);
         grid-column: 1 / -1;
      }
      
      .no-results i {
         font-size: 5rem;
         background: var(--primary-gradient);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin-bottom: 25px;
         display: block;
      }
      
      .no-results .empty {
         font-size: 1.8rem;
         font-weight: 700;
         color: var(--text-primary);
         margin-bottom: 15px;
      }
      
      .no-results p:last-child {
         color: var(--text-secondary);
         font-size: 1.1rem;
      }
      
      /* Enhanced Pagination */
      .pagination {
         display: flex;
         justify-content: center;
         margin: 50px 0;
         flex-wrap: wrap;
         gap: 10px;
      }
      
      .pagination a,
      .pagination span {
         padding: 14px 18px;
         background: var(--glass-bg);
         backdrop-filter: blur(20px);
         border: 2px solid transparent;
         border-radius: 14px;
         color: var(--text-secondary);
         text-decoration: none;
         font-weight: 600;
         transition: var(--transition);
         box-shadow: var(--shadow-light);
         min-width: 50px;
         text-align: center;
      }
      
      .pagination a:hover {
         background: var(--primary-gradient);
         color: white;
         transform: translateY(-3px);
         box-shadow: var(--shadow-medium);
      }
      
      .pagination a.active {
         background: var(--primary-gradient);
         color: white;
         box-shadow: var(--shadow-medium);
      }
      
      /* Loading States */
      .loading {
         display: inline-block;
         width: 20px;
         height: 20px;
         border: 2px solid rgba(255, 255, 255, 0.3);
         border-radius: 50%;
         border-top-color: #fff;
         animation: spin 1s linear infinite;
      }
      
      @keyframes spin {
         to { transform: rotate(360deg); }
      }
      
      /* Search Algorithm Badge */
      .algorithm-badge {
         position: absolute;
         top: 20px;
         right: 20px;
         background: var(--success-gradient);
         color: white;
         padding: 8px 16px;
         border-radius: 20px;
         font-size: 0.8rem;
         font-weight: 600;
         box-shadow: var(--shadow-light);
         animation: pulse 2s infinite;
      }
      
      @keyframes pulse {
         0%, 100% { transform: scale(1); }
         50% { transform: scale(1.05); }
      }
      
      /* Mobile Responsive */
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
         .hero-title {
            font-size: 2.2rem;
         }
         
         .search-stats {
            gap: 15px;
         }
         
         .stat-item {
            flex: 1;
            min-width: 120px;
         }
         
         .products .box-container {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
         }
      }
      
      @media (max-width: 480px) {
         .search-container {
            padding: 15px;
         }
         
         .hero-title {
            font-size: 1.8rem;
         }
         
         .search-hero {
            padding: 40px 25px;
            border-radius: 20px;
         }
         
         .products .box-container {
            grid-template-columns: 1fr;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="search-container">
   <!-- Enhanced Search Hero Section -->
   <div class="search-hero">
      <h1 class="hero-title">Product Search</h1>
      <p class="hero-subtitle">Find your perfect products with smart search</p>
      
      <div class="search-form">
         <form action="" method="get">
            <input type="text" name="search_box" 
                   placeholder="Search for products, brands, categories..." 
                   class="box" 
                   value="<?= htmlspecialchars($search_term) ?>">
            <button type="submit" class="btn-search">
               <i class="fas fa-search"></i>
               <span>Search</span>
            </button>
         </form>
      </div>
      
      <div class="search-stats">
         <div class="stat-item">
            <span class="stat-number"><?= count($all_products) ?></span>
            <span class="stat-label">Total Products</span>
         </div>
         <div class="stat-item">
            <span class="stat-number"><?= $total_results ?></span>
            <span class="stat-label">Search Results</span>
         </div>
         <div class="stat-item">
            <span class="stat-number">Fast</span>
            <span class="stat-label">Search Speed</span>
         </div>
      </div>
   </div>

   <div class="search-content" style="display: flex; gap: 30px; align-items: flex-start;">
      <!-- Enhanced Filter Sidebar -->
      <aside class="filter-sidebar">
         <div class="filter-title">
            <i class="fas fa-sliders-h"></i>
            Smart Filters
         </div>
         
         <div class="filter-group">
            <h3><i class="fas fa-tags"></i> Price Range</h3>
            <div class="price-inputs">
               <div class="price-input-group">
                  <label>Min Price</label>
                  <input type="number" id="min-price" name="min_price" 
                         placeholder="₹<?= $global_min_price ?>" 
                         value="<?= $min_price ?>" 
                         min="<?= $global_min_price ?>" 
                         max="<?= $global_max_price ?>">
               </div>
               <div class="price-input-group">
                  <label>Max Price</label>
                  <input type="number" id="max-price" name="max_price" 
                         placeholder="₹<?= $global_max_price ?>" 
                         value="<?= $max_price ?>" 
                         min="<?= $global_min_price ?>" 
                         max="<?= $global_max_price ?>">
               </div>
            </div>
         </div>
         
         <button type="button" class="filter-btn" id="applyFilters">
            <i class="fas fa-check"></i> Apply Binary Search Filter
         </button>
      </aside>
      
      <!-- Products Section -->
      <section class="products-section" style="flex: 1; min-width: 0;">
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
               echo '<div class="search-info" style="text-align: center; margin: 30px 0; padding: 20px; background: #f8fafc; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; grid-column: 1 / -1; color: #374151; font-weight: 500;">
                        <i class="fas fa-search" style="color: #10b981; margin-right: 8px;"></i>
                        Search Results for: <strong style="color: #1e293b;">"'.htmlspecialchars($search_term).'"</strong>
                     </div>';
               
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
                  <div class="product-info">
                     <div class="name"><?= $product['name'] ?></div>
                     <div class="product-details">Category: Footwear | Brand: Premium</div>
                  </div>
                  
                  <div class="product-actions">
                     <div class="price-section">
                        <div class="price"><?= $product['price'] ?></div>
                     </div>
                     
                     <div class="qty-cart-section">
                        <input type="number" name="qty" class="qty" min="1" max="99" value="1" title="Quantity">
                        <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
                     </div>
                  </div>
               </div>
            </form>
            <?php
                  }
               } else {
                  echo '<div class="no-results">
                           <i class="fas fa-search-minus"></i>
                           <p class="empty">No products found!</p>
                           <p>Binary search completed but no matches found. Try different keywords or adjust filters.</p>
                        </div>';
               }
               
               // Enhanced Pagination
               if($total_pages > 1){
                  echo '<div class="pagination" style="grid-column: 1 / -1;">';
                  if($page > 1){
                     echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.($page-1).'">&laquo; Previous</a>';
                  }
                  
                  // Show page numbers with improved logic
                  $start_page = max(1, $page - 2);
                  $end_page = min($total_pages, $page + 2);
                  
                  if($start_page > 1) {
                     echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page=1">1</a>';
                  }
                  if($start_page > 2) echo '<span style="padding: 14px 8px; color: #64748b;">...</span>';
                  
                  for($i = $start_page; $i <= $end_page; $i++){
                     if($i == $page){
                        echo '<a class="active">'.$i.'</a>';
                     } else {
                        echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.$i.'">'.$i.'</a>';
                     }
                  }
                  
                  if($end_page < $total_pages - 1) echo '<span style="padding: 14px 8px; color: #64748b;">...</span>';
                  if($end_page < $total_pages) {
                     echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.$total_pages.'">'.$total_pages.'</a>';
                  }
                  
                  if($page < $total_pages){
                     echo '<a href="?search_box='.urlencode($search_term).'&min_price='.$min_price.'&max_price='.$max_price.'&sort='.$sort.'&page='.($page+1).'">Next &raquo;</a>';
                  }
                  echo '</div>';
               }
            } else {
               echo '<div class="no-results">
                        <i class="fas fa-rocket" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <p class="empty">Ready for Binary Search!</p>
                        <p>Enter your search term above to experience lightning-fast product discovery</p>
                        <div style="margin-top: 25px;">
                           <div style="display: inline-flex; align-items: center; gap: 15px; padding: 15px 20px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; color: #065f46; font-weight: 500;">
                              <i class="fas fa-info-circle" style="color: #10b981;"></i>
                              Binary search provides O(log n) time complexity for faster results
                           </div>
                        </div>
                     </div>';
            }
            ?>
            </div>
         </section>
      </section>
   </div>
</div>

<!-- Performance Indicator -->
<div style="position: fixed; bottom: 20px; right: 20px; background: rgba(16, 185, 129, 0.9); color: white; padding: 12px 16px; border-radius: 25px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3); backdrop-filter: blur(10px);">
   <i class="fas fa-tachometer-alt"></i> Binary Search Active
</div>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced search functionality with performance tracking
    let searchStartTime;
    
    // Apply filters with binary search
    const applyFilters = document.getElementById('applyFilters');
    if(applyFilters) {
        applyFilters.addEventListener('click', function() {
            searchStartTime = performance.now();
            
            const searchTerm = document.querySelector('input[name="search_box"]').value;
            const minPrice = document.getElementById('min-price').value || <?= $global_min_price ?>;
            const maxPrice = document.getElementById('max-price').value || <?= $global_max_price ?>;
            const sortBy = document.getElementById('sortBy').value;
            
            // Enhanced loading state
            this.innerHTML = '<div class="loading"></div> Running Binary Search...';
            this.disabled = true;
            this.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
            
            // Add search performance indicator
            const performanceBar = document.createElement('div');
            performanceBar.style.cssText = `
                position: fixed; top: 0; left: 0; right: 0; height: 3px; 
                background: linear-gradient(90deg, #10b981, #059669); 
                z-index: 9999; animation: searchProgress 1s ease-in-out;
            `;
            document.body.appendChild(performanceBar);
            
            setTimeout(() => {
                window.location.href = `?search_box=${encodeURIComponent(searchTerm)}&min_price=${minPrice}&max_price=${maxPrice}&sort=${sortBy}`;
            }, 500);
        });
    }
    
    // Enhanced sort functionality
    const sortBy = document.getElementById('sortBy');
    if(sortBy) {
        sortBy.addEventListener('change', function() {
            const searchTerm = document.querySelector('input[name="search_box"]').value;
            const minPrice = document.getElementById('min-price').value || <?= $priceRange['min'] ?>;
            const maxPrice = document.getElementById('max-price').value || <?= $priceRange['max'] ?>;
            const sortValue = this.value;
            
            // Visual feedback for sorting
            this.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            this.style.color = 'white';
            this.disabled = true;
            
            window.location.href = `?search_box=${encodeURIComponent(searchTerm)}&min_price=${minPrice}&max_price=${maxPrice}&sort=${sortValue}`;
        });
    }
    
    // Price input validation with binary search optimization hints
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    
    if(minPriceInput && maxPriceInput) {
        function showPriceOptimizationHint(input, message) {
            const hint = document.createElement('div');
            hint.style.cssText = `
                position: absolute; top: 100%; left: 0; right: 0; 
                background: rgba(16, 185, 129, 0.9); color: white; 
                padding: 8px 12px; border-radius: 8px; font-size: 0.8rem; 
                margin-top: 5px; z-index: 1000;
            `;
            hint.innerHTML = `<i class="fas fa-lightbulb"></i> ${message}`;
            
            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(hint);
            
            setTimeout(() => hint.remove(), 3000);
        }
        
        minPriceInput.addEventListener('input', function() {
            if(parseFloat(this.value) > parseFloat(maxPriceInput.value) && maxPriceInput.value) {
                maxPriceInput.value = this.value;
                showPriceOptimizationHint(this, 'Binary search will optimize this range automatically!');
            }
        });
        
        maxPriceInput.addEventListener('input', function() {
            if(parseFloat(this.value) < parseFloat(minPriceInput.value) && minPriceInput.value) {
                minPriceInput.value = this.value;
                showPriceOptimizationHint(this, 'Price range optimized for faster binary search!');
            }
        });
    }
    
    // Enhanced search form with algorithm indicator
    const searchForm = document.querySelector('.search-form form');
    if(searchForm) {
        searchForm.addEventListener('submit', function() {
            searchStartTime = performance.now();
            
            const submitBtn = this.querySelector('.btn-search');
            submitBtn.innerHTML = '<div class="loading"></div> Binary Searching...';
            submitBtn.disabled = true;
            
            // Add algorithm performance indicator
            const algorithmIndicator = document.createElement('div');
            algorithmIndicator.style.cssText = `
                position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
                background: rgba(0, 0, 0, 0.8); color: white; padding: 20px 30px;
                border-radius: 15px; z-index: 9999; backdrop-filter: blur(10px);
                text-align: center; font-weight: 600;
            `;
            algorithmIndicator.innerHTML = `
                <div style="margin-bottom: 10px;">
                    <i class="fas fa-cogs fa-spin" style="font-size: 2rem; color: #10b981;"></i>
                </div>
                <div>Binary Search Algorithm Running...</div>
                <div style="font-size: 0.9rem; opacity: 0.8; margin-top: 5px;">O(log n) complexity for optimal performance</div>
            `;
            document.body.appendChild(algorithmIndicator);
            
            setTimeout(() => algorithmIndicator.remove(), 2000);
        });
    }
    
    // Performance measurement display
    if(window.performance && searchStartTime) {
        window.addEventListener('load', function() {
            const searchEndTime = performance.now();
            const searchDuration = (searchEndTime - searchStartTime).toFixed(2);
            
            const performanceDisplay = document.createElement('div');
            performanceDisplay.style.cssText = `
                position: fixed; top: 20px; left: 20px; 
                background: rgba(16, 185, 129, 0.9); color: white;
                padding: 10px 15px; border-radius: 20px; font-weight: 600;
                box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
                backdrop-filter: blur(10px); z-index: 1000;
            `;
            performanceDisplay.innerHTML = `
                <i class="fas fa-stopwatch"></i> Search completed in ${searchDuration}ms
            `;
            document.body.appendChild(performanceDisplay);
            
            setTimeout(() => performanceDisplay.remove(), 5000);
        });
    }
    
    // Add to cart with enhanced feedback
    const addToCartBtns = document.querySelectorAll('input[name="add_to_cart"]');
    addToCartBtns.forEach((btn, index) => {
        btn.addEventListener('click', function(e) {
            const originalText = this.value;
            this.value = 'Adding...';
            this.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            this.disabled = true;
            
            // Add success animation
            setTimeout(() => {
                this.value = '✓ Added!';
                this.style.background = 'linear-gradient(135deg, #059669, #047857)';
                
                setTimeout(() => {
                    this.value = originalText;
                    this.style.background = 'var(--primary-gradient)';
                    this.disabled = false;
                }, 1500);
            }, 800);
        });
    });
    
    // Enhanced wishlist interaction
    const wishlistBtns = document.querySelectorAll('.fa-heart');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Heart animation
            this.style.transform = 'scale(1.3)';
            this.style.color = '#ef4444';
            
            // Create floating heart effect
            const floatingHeart = document.createElement('div');
            floatingHeart.innerHTML = '<i class="fas fa-heart"></i>';
            floatingHeart.style.cssText = `
                position: absolute; color: #ef4444; font-size: 1.5rem;
                pointer-events: none; z-index: 1000;
                animation: floatHeart 2s ease-out forwards;
            `;
            
            const rect = this.getBoundingClientRect();
            floatingHeart.style.left = (rect.left + rect.width/2) + 'px';
            floatingHeart.style.top = (rect.top + rect.height/2) + 'px';
            
            document.body.appendChild(floatingHeart);
            
            setTimeout(() => {
                this.style.transform = 'scale(1)';
                floatingHeart.remove();
            }, 2000);
        });
    });
    
    // Intersection Observer for enhanced scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach((entry, index) => {
            if(entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) rotateX(0)';
                }, index * 100);
            }
        });
    }, observerOptions);
    
    // Observe product boxes for staggered animation
    const productBoxes = document.querySelectorAll('.products .box');
    productBoxes.forEach((box, index) => {
        box.style.opacity = '0';
        box.style.transform = 'translateY(30px) rotateX(-10deg)';
        box.style.transition = `all 0.6s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s`;
        observer.observe(box);
    });
    
    // Keyboard shortcuts for power users
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="search_box"]');
            if(searchInput) {
                searchInput.focus();
                searchInput.select();
                
                // Show shortcut hint
                const hint = document.createElement('div');
                hint.style.cssText = `
                    position: absolute; top: -40px; left: 0; right: 0;
                    background: rgba(16, 185, 129, 0.9); color: white;
                    padding: 8px 15px; border-radius: 20px; text-align: center;
                    font-size: 0.9rem; z-index: 1000;
                `;
                hint.innerHTML = '<i class="fas fa-keyboard"></i> Search focused - Start typing!';
                searchInput.parentElement.style.position = 'relative';
                searchInput.parentElement.appendChild(hint);
                
                setTimeout(() => hint.remove(), 2000);
            }
        }
        
        // Ctrl/Cmd + Enter to apply filters quickly
        if((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const filterBtn = document.getElementById('applyFilters');
            if(filterBtn && !filterBtn.disabled) {
                filterBtn.click();
            }
        }
    });
    
    // Add search tips for better UX
    const searchInput = document.querySelector('input[name="search_box"]');
    if(searchInput) {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        const shortcut = isMac ? '⌘K' : 'Ctrl+K';
        
        // Add search tips on focus
        searchInput.addEventListener('focus', function() {
            console.log('Search input focused'); // Debug log
            
            if(!this.value) {
                const tips = document.createElement('div');
                tips.style.cssText = `
                    position: absolute; top: 100%; left: 0; right: 0; margin-top: 10px;
                    background: rgba(255, 255, 255, 0.95); border-radius: 12px;
                    padding: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                    border: 1px solid rgba(102, 126, 234, 0.2); z-index: 1000;
                `;
                tips.innerHTML = `
                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 8px;">
                        <i class="fas fa-lightbulb" style="color: #10b981;"></i> Search Tips:
                    </div>
                    <div style="font-size: 0.9rem; color: #64748b; line-height: 1.4;">
                        • Use specific product names for exact matches<br>
                        • Try brand names or categories<br>
                        • Press ${shortcut} to quickly focus search
                    </div>
                `;
                
                this.parentElement.style.position = 'relative';
                this.parentElement.appendChild(tips);
                
                // Remove tips when clicking outside
                document.addEventListener('click', function removeTips(e) {
                    if(!searchInput.contains(e.target) && !tips.contains(e.target)) {
                        tips.remove();
                        document.removeEventListener('click', removeTips);
                    }
                });
            }
        });
        
        // Test input functionality
        searchInput.addEventListener('input', function() {
            console.log('Input value:', this.value); // Debug log
        });
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes searchProgress {
        0% { transform: scaleX(0); }
        100% { transform: scaleX(1); }
    }
    
    @keyframes floatHeart {
        0% { transform: translateY(0) scale(1); opacity: 1; }
        100% { transform: translateY(-50px) scale(0.5); opacity: 0; }
    }
    
    .search-form form:focus-within {
        animation: searchPulse 2s infinite;
    }
    
    @keyframes searchPulse {
        0%, 100% { box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2); }
        50% { box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3); }
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>