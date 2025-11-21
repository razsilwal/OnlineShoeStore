<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exist!';
   }else{

      $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, image_01) VALUES(?,?,?,?)");
      $insert_products->execute([$name, $details, $price, $image_01]);

      if($insert_products){
         if($image_size_01 > 2000000 ){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name_01, $image_folder_01);
            
            $message[] = 'new product added!';
         }

      }

   }  

};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/'.$fetch_delete_image['image_01']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_02']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_03']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:products.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products Management | Admin Panel</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <style>
      :root {
         --primary: #8B5FBF;
         --primary-dark: #6B46C1;
         --primary-light: #9F7AEA;
         --secondary: #06D6A0;
         --accent: #FF6B6B;
         --dark: #1A202C;
         --light: #F7FAFC;
         --gray: #718096;
         --border: #E2E8F0;
         --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
         --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }

      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Inter', sans-serif;
      }

      body {
         background: linear-gradient(135deg, #F7FAFC 0%, #EDF2F7 100%);
         min-height: 100vh;
         color: var(--dark);
      }

      /* Messages */
      .message {
         position: fixed;
         top: 100px;
         right: 30px;
         background: linear-gradient(135deg, var(--secondary), #05C78C);
         color: white;
         padding: 16px 24px;
         border-radius: 16px;
         box-shadow: var(--shadow);
         display: flex;
         align-items: center;
         justify-content: space-between;
         max-width: 380px;
         z-index: 9999;
         animation: slideInRight 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
         font-size: 0.95rem;
         font-weight: 500;
         border: 1px solid rgba(255, 255, 255, 0.2);
         backdrop-filter: blur(10px);
      }

      @keyframes slideInRight {
         from { 
            transform: translateX(100%) scale(0.9); 
            opacity: 0; 
         }
         to { 
            transform: translateX(0) scale(1); 
            opacity: 1; 
         }
      }

      .message i {
         margin-left: 16px;
         cursor: pointer;
         transition: var(--transition);
         font-size: 1.1rem;
         opacity: 0.8;
         padding: 4px;
         border-radius: 50%;
      }

      .message i:hover {
         opacity: 1;
         background: rgba(255, 255, 255, 0.2);
         transform: rotate(90deg);
      }

      /* Main Content */
      .main-content {
         padding: 30px;
         max-width: 1400px;
         margin: 0 auto;
         margin-top: 80px;
      }

      /* Page Header */
      .page-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 40px;
         padding-bottom: 20px;
         border-bottom: 2px solid var(--border);
      }

      .page-title {
         font-size: 2.5rem;
         font-weight: 700;
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin: 0;
      }

      /* Add Products Section */
      .add-products {
         background: white;
         padding: 40px;
         border-radius: 20px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
         margin-bottom: 40px;
      }

      .section-heading {
         font-size: 1.8rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 30px;
         display: flex;
         align-items: center;
         gap: 12px;
      }

      .section-heading i {
         color: var(--primary);
         font-size: 1.5rem;
      }

      /* Form Styling */
      .product-form {
         width: 100%;
      }

      .form-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
         gap: 25px;
         margin-bottom: 30px;
      }

      .form-group {
         display: flex;
         flex-direction: column;
         gap: 8px;
      }

      .form-label {
         font-weight: 600;
         color: var(--dark);
         font-size: 0.95rem;
      }

      .form-input {
         padding: 14px 16px;
         border: 2px solid var(--border);
         border-radius: 12px;
         font-size: 1rem;
         color: var(--dark);
         background: white;
         transition: var(--transition);
         font-family: 'Inter', sans-serif;
      }

      .form-input:focus {
         outline: none;
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(139, 95, 191, 0.1);
         transform: translateY(-2px);
      }

      .form-input::placeholder {
         color: var(--gray);
      }

      textarea.form-input {
         resize: vertical;
         min-height: 120px;
      }

      .file-input {
         padding: 12px 16px;
         border: 2px dashed var(--border);
         border-radius: 12px;
         background: var(--light);
         transition: var(--transition);
         cursor: pointer;
      }

      .file-input:hover {
         border-color: var(--primary);
         background: rgba(139, 95, 191, 0.05);
      }

      .file-input:focus {
         border-style: solid;
      }

      /* Submit Button */
      .submit-btn {
         background: linear-gradient(135deg, var(--primary), var(--primary-dark));
         color: white;
         border: none;
         padding: 16px 32px;
         border-radius: 12px;
         font-size: 1.1rem;
         font-weight: 600;
         cursor: pointer;
         transition: var(--transition);
         font-family: 'Inter', sans-serif;
         text-transform: uppercase;
         letter-spacing: 0.5px;
         width: 100%;
         max-width: 300px;
         margin: 0 auto;
         display: block;
      }

      .submit-btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 15px 30px rgba(139, 95, 191, 0.4);
      }

      /* Show Products Section */
      .show-products {
         background: white;
         padding: 40px;
         border-radius: 20px;
         box-shadow: var(--shadow);
         border: 1px solid var(--border);
      }

      /* Products Grid */
      .products-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
         gap: 25px;
      }

      .product-card {
         background: white;
         border: 1px solid var(--border);
         border-radius: 16px;
         overflow: hidden;
         transition: var(--transition);
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      }

      .product-card:hover {
         transform: translateY(-8px);
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
         border-color: var(--primary-light);
      }

      .product-image {
         width: 100%;
         height: 220px;
         object-fit: cover;
         border-bottom: 1px solid var(--border);
      }

      .product-content {
         padding: 20px;
      }

      .product-name {
         font-size: 1.2rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 10px;
         line-height: 1.4;
      }

      .product-price {
         font-size: 1.4rem;
         font-weight: 700;
         color: var(--primary);
         margin-bottom: 12px;
      }

      .product-details {
         color: var(--gray);
         font-size: 0.9rem;
         line-height: 1.5;
         margin-bottom: 20px;
         display: -webkit-box;
         -webkit-line-clamp: 3;
         -webkit-box-orient: vertical;
         overflow: hidden;
      }

      .product-actions {
         display: flex;
         gap: 10px;
      }

      .action-btn {
         flex: 1;
         padding: 10px 16px;
         border-radius: 10px;
         text-decoration: none;
         font-weight: 600;
         font-size: 0.9rem;
         text-align: center;
         transition: var(--transition);
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 6px;
      }

      .update-btn {
         background: rgba(6, 214, 160, 0.1);
         color: var(--secondary);
         border: 1px solid rgba(6, 214, 160, 0.2);
      }

      .update-btn:hover {
         background: var(--secondary);
         color: white;
         transform: translateY(-2px);
      }

      .delete-btn {
         background: rgba(255, 107, 107, 0.1);
         color: var(--accent);
         border: 1px solid rgba(255, 107, 107, 0.2);
      }

      .delete-btn:hover {
         background: var(--accent);
         color: white;
         transform: translateY(-2px);
      }

      /* Empty State */
      .empty-state {
         text-align: center;
         padding: 60px 20px;
         color: var(--gray);
         grid-column: 1 / -1;
      }

      .empty-icon {
         font-size: 4rem;
         color: var(--border);
         margin-bottom: 20px;
      }

      .empty-text {
         font-size: 1.2rem;
         margin-bottom: 10px;
         color: var(--dark);
      }

      /* Responsive Design */
      @media (max-width: 1024px) {
         .main-content {
            padding: 20px;
            margin-top: 70px;
         }

         .page-title {
            font-size: 2rem;
         }

         .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         }
      }

      @media (max-width: 768px) {
         .main-content {
            padding: 15px;
         }

         .page-header {
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
         }

         .add-products,
         .show-products {
            padding: 25px;
         }

         .form-grid {
            grid-template-columns: 1fr;
         }

         .products-grid {
            grid-template-columns: 1fr;
         }

         .product-actions {
            flex-direction: column;
         }
      }

      @media (max-width: 480px) {
         .page-title {
            font-size: 1.5rem;
         }

         .section-heading {
            font-size: 1.4rem;
         }

         .add-products,
         .show-products {
            padding: 20px;
         }

         .form-input {
            padding: 12px 14px;
         }
      }

      /* Animation */
      @keyframes fadeInUp {
         from {
            opacity: 0;
            transform: translateY(20px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }

      .fade-in {
         animation: fadeInUp 0.6s ease-out;
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<?php
if(isset($message)){
    $messages = (array)$message; // Convert to array if it's a string
    foreach($messages as $msg){
        echo '
        <div class="message">
            <span>'.$msg.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<div class="main-content">
   <!-- Page Header -->
   <div class="page-header fade-in">
      <h1 class="page-title">Product Management</h1>
   </div>

   <!-- Add Product Section -->
   <section class="add-products fade-in">
      <h2 class="section-heading">
         <i class="fas fa-plus-circle"></i>
         Add New Product
      </h2>

      <form action="" method="post" enctype="multipart/form-data" class="product-form">
         <div class="form-grid">
            <div class="form-group">
               <label class="form-label">Product Name</label>
               <input type="text" class="form-input" required maxlength="100" placeholder="Enter product name" name="name">
            </div>
            
            <div class="form-group">
               <label class="form-label">Product Price (NRs.)</label>
               <input type="number" min="0" class="form-input" required max="9999999999" placeholder="Enter product price" onkeypress="if(this.value.length == 10) return false;" name="price">
            </div>
            
            <div class="form-group">
               <label class="form-label">Product Image</label>
               <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="form-input file-input" required>
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
               <label class="form-label">Product Details</label>
               <textarea name="details" placeholder="Enter product description and features..." class="form-input" required maxlength="500" rows="6"></textarea>
            </div>
         </div>
         
         <button type="submit" class="submit-btn" name="add_product">
            <i class="fas fa-plus"></i>
            Add Product
         </button>
      </form>
   </section>

   <!-- Show Products Section -->
   <section class="show-products fade-in">
      <h2 class="section-heading">
         <i class="fas fa-boxes"></i>
         All Products
      </h2>

      <div class="products-grid">
      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
      ?>
         <div class="product-card">
            <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="<?= $fetch_products['name']; ?>" class="product-image">
            <div class="product-content">
               <h3 class="product-name"><?= $fetch_products['name']; ?></h3>
               <div class="product-price">NRs. <?= number_format($fetch_products['price'], 2); ?></div>
               <p class="product-details"><?= $fetch_products['details']; ?></p>
               <div class="product-actions">
                  <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="action-btn update-btn">
                     <i class="fas fa-edit"></i>
                     Update
                  </a>
                  <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this product?');">
                     <i class="fas fa-trash"></i>
                     Delete
                  </a>
               </div>
            </div>
         </div>
      <?php
            }
         }else{
            echo '
            <div class="empty-state">
               <div class="empty-icon">
                  <i class="fas fa-box-open"></i>
               </div>
               <div class="empty-text">No products added yet!</div>
               <p>Start by adding your first product using the form above.</p>
            </div>
            ';
         }
      ?>
      </div>
   </section>
</div>

<script>
   // Auto-remove messages after 5 seconds
   setTimeout(() => {
      const messages = document.querySelectorAll('.message');
      messages.forEach(message => {
         message.style.animation = 'slideInRight 0.5s ease-out reverse forwards';
         setTimeout(() => message.remove(), 500);
      });
   }, 5000);

   // Add animation to product cards
   document.addEventListener('DOMContentLoaded', function() {
      const productCards = document.querySelectorAll('.product-card');
      productCards.forEach((card, index) => {
         card.style.animationDelay = `${index * 0.1}s`;
         card.classList.add('fade-in');
      });

      // File input styling
      const fileInputs = document.querySelectorAll('.file-input');
      fileInputs.forEach(input => {
         input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
               this.style.borderColor = 'var(--secondary)';
               this.style.background = 'rgba(6, 214, 160, 0.05)';
            }
         });
      });
   });

   // Form validation
   const form = document.querySelector('.product-form');
   form.addEventListener('submit', function(e) {
      const price = document.querySelector('input[name="price"]');
      if (price.value < 0) {
         e.preventDefault();
         alert('Price cannot be negative!');
         price.focus();
      }
   });
</script>

</body>
</html>