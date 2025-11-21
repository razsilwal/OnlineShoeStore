<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

include 'components/wishlist_cart.php';

if(isset($_POST['delete'])){
   $wishlist_id = $_POST['wishlist_id'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$wishlist_id]);
}

if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Wishlist</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* Additional styles for wishlist page */
      .wishlist-header {
         text-align: center;
         margin-bottom: 2rem;
         padding: 1.5rem;
         background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
         border-radius: 10px;
         box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      }
      
      .wishlist-header h3 {
         font-size: 2.5rem;
         color: #333;
         margin-bottom: 0.5rem;
         font-weight: 700;
      }
      
      .wishlist-count {
         font-size: 1.2rem;
         color: #666;
      }
      
      .wishlist-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 2rem;
         margin-bottom: 3rem;
      }
      
      .wishlist-item {
         background: #fff;
         border-radius: 12px;
         overflow: hidden;
         box-shadow: 0 5px 15px rgba(0,0,0,0.08);
         transition: transform 0.3s ease, box-shadow 0.3s ease;
         position: relative;
      }
      
      .wishlist-item:hover {
         transform: translateY(-5px);
         box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      }
      
      .wishlist-item img {
         width: 100%;
         height: 220px;
         object-fit: cover;
         border-bottom: 1px solid #eee;
      }
      
      .item-actions {
         position: absolute;
         top: 10px;
         right: 10px;
         display: flex;
         gap: 8px;
      }
      
      .item-actions a, 
      .item-actions button {
         width: 36px;
         height: 36px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         background: rgba(255,255,255,0.9);
         color: #333;
         border: none;
         cursor: pointer;
         transition: all 0.3s ease;
         box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
      
      .item-actions a:hover, 
      .item-actions button:hover {
         background: #fff;
         transform: scale(1.1);
      }
      
      .item-actions .delete-btn {
         color: #e74c3c;
      }
      
      .item-details {
         padding: 1.5rem;
      }
      
      .item-name {
         font-size: 1.4rem;
         font-weight: 600;
         color: #333;
         margin-bottom: 0.8rem;
         line-height: 1.3;
      }
      
      .item-price {
         font-size: 1.5rem;
         font-weight: 700;
         color: #2c3e50;
         margin-bottom: 1rem;
      }
      
      .quantity-controls {
         display: flex;
         align-items: center;
         margin-bottom: 1.2rem;
         gap: 10px;
      }
      
      .quantity-controls label {
         font-size: 1rem;
         color: #666;
         font-weight: 500;
      }
      
      .quantity-controls input {
         width: 70px;
         padding: 8px 12px;
         border: 1px solid #ddd;
         border-radius: 6px;
         font-size: 1rem;
         text-align: center;
      }
      
      .add-to-cart-btn {
         width: 100%;
         padding: 12px;
         background: #3498db;
         color: white;
         border: none;
         border-radius: 6px;
         font-size: 1.1rem;
         font-weight: 600;
         cursor: pointer;
         transition: background 0.3s ease;
      }
      
      .add-to-cart-btn:hover {
         background: #2980b9;
      }
      
      .wishlist-summary {
         background: #fff;
         border-radius: 12px;
         padding: 2rem;
         box-shadow: 0 5px 15px rgba(0,0,0,0.08);
         margin-top: 2rem;
      }
      
      .summary-row {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 1.5rem;
         padding-bottom: 1rem;
         border-bottom: 1px solid #eee;
      }
      
      .summary-label {
         font-size: 1.3rem;
         color: #333;
         font-weight: 600;
      }
      
      .summary-value {
         font-size: 1.8rem;
         font-weight: 700;
         color: #2c3e50;
      }
      
      .summary-actions {
         display: flex;
         justify-content: space-between;
         gap: 1rem;
         flex-wrap: wrap;
      }
      
      .summary-actions a {
         flex: 1;
         text-align: center;
         padding: 14px 20px;
         border-radius: 8px;
         font-size: 1.1rem;
         font-weight: 600;
         transition: all 0.3s ease;
         min-width: 200px;
      }
      
      .continue-shopping {
         background: #f8f9fa;
         color: #333;
         border: 1px solid #ddd;
      }
      
      .continue-shopping:hover {
         background: #e9ecef;
      }
      
      .delete-all {
         background: #e74c3c;
         color: white;
      }
      
      .delete-all:hover {
         background: #c0392b;
      }
      
      .delete-all.disabled {
         background: #bdc3c7;
         cursor: not-allowed;
      }
      
      .empty-wishlist {
         text-align: center;
         padding: 4rem 2rem;
         background: #fff;
         border-radius: 12px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      }
      
      .empty-wishlist i {
         font-size: 5rem;
         color: #bdc3c7;
         margin-bottom: 1.5rem;
      }
      
      .empty-wishlist p {
         font-size: 1.5rem;
         color: #7f8c8d;
         margin-bottom: 2rem;
      }
      
      .empty-wishlist a {
         display: inline-block;
         padding: 12px 30px;
         background: #3498db;
         color: white;
         border-radius: 6px;
         font-size: 1.1rem;
         font-weight: 600;
         transition: background 0.3s ease;
      }
      
      .empty-wishlist a:hover {
         background: #2980b9;
      }
      
      @media (max-width: 768px) {
         .wishlist-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
         }
         
         .summary-actions {
            flex-direction: column;
         }
         
         .summary-actions a {
            min-width: 100%;
         }
         
         .wishlist-header h3 {
            font-size: 2rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <div class="wishlist-header">
      <h3>My Wishlist</h3>
      <?php
         $count_wishlist = $conn->prepare("SELECT COUNT(*) FROM `wishlist` WHERE user_id = ?");
         $count_wishlist->execute([$user_id]);
         $total_items = $count_wishlist->fetchColumn();
      ?>
      <p class="wishlist-count"><?= $total_items; ?> item<?= $total_items != 1 ? 's' : '' ?> saved</p>
   </div>

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
      $select_wishlist->execute([$user_id]);
      if($select_wishlist->rowCount() > 0){
   ?>
   
   <div class="wishlist-container">
      <?php
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){
            $grand_total += $fetch_wishlist['price'];  
      ?>
      <form action="" method="post" class="wishlist-item">
         <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
         <input type="hidden" name="wishlist_id" value="<?= $fetch_wishlist['id']; ?>">
         <input type="hidden" name="name" value="<?= $fetch_wishlist['name']; ?>">
         <input type="hidden" name="price" value="<?= $fetch_wishlist['price']; ?>">
         <input type="hidden" name="image" value="<?= $fetch_wishlist['image']; ?>">
         
         <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="<?= $fetch_wishlist['name']; ?>">
         
         <div class="item-actions">
            <a href="quick_view.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
            <button type="submit" name="delete" class="delete-btn fas fa-trash" onclick="return confirm('Delete this from wishlist?');"></button>
         </div>
         
         <div class="item-details">
            <div class="item-name"><?= $fetch_wishlist['name']; ?></div>
            <div class="item-price">NRs. <?= number_format($fetch_wishlist['price']); ?>/-</div>
            
            <div class="quantity-controls">
               <label for="qty_<?= $fetch_wishlist['id']; ?>">Qty:</label>
               <input type="number" id="qty_<?= $fetch_wishlist['id']; ?>" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            </div>
            
            <input type="submit" value="Add to Cart" class="add-to-cart-btn" name="add_to_cart">
         </div>
      </form>
      <?php
         }
      ?>
   </div>

   <div class="wishlist-summary">
      <div class="summary-row">
         <span class="summary-label">Grand Total:</span>
         <span class="summary-value">NRs. <?= number_format($grand_total); ?>/-</span>
      </div>
      
      <div class="summary-actions">
         <a href="shop.php" class="continue-shopping">Continue Shopping</a>
         <a href="wishlist.php?delete_all" class="delete-all <?= ($grand_total > 1)?'':'disabled'; ?>" onclick="return confirm('Delete all from wishlist?');">Delete All Items</a>
      </div>
   </div>

   <?php
      }else{
   ?>
   
   <div class="empty-wishlist">
      <i class="far fa-heart"></i>
      <p>Your wishlist is empty</p>
      <a href="shop.php">Start Shopping</a>
   </div>
   
   <?php
      }
   ?>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>