<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
   exit;
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $conn->prepare("DELETE FROM `users` WHERE id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `orders` WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `messages` WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `cart` WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?")->execute([$delete_id]);
   header('location:users_accounts.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Accounts | Admin</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <!-- Google Font -->
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="../css/user_account.css">
   
</head>
<body>

   <?php include '../components/admin_navbar.php'; ?>

   <section class="accounts">
      <h1 class="heading">User Accounts</h1>

      <div class="box-container">
         <?php
         $select_accounts = $conn->prepare("SELECT * FROM `users`");
         $select_accounts->execute();
         if ($select_accounts->rowCount() > 0) {
            while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {
         ?>
         <div class="box">
            <p><strong>User ID:</strong> <span><?= $fetch_accounts['id']; ?></span></p>
            <p><strong>Username:</strong> <span><?= htmlspecialchars($fetch_accounts['name']); ?></span></p>
            <p><strong>Email:</strong> <span><?= htmlspecialchars($fetch_accounts['email']); ?></span></p>
            <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" class="delete-btn" onclick="return confirm('Delete this account? All associated data will be removed.')">Delete</a>
         </div>
         <?php
            }
         } else {
            echo '<p class="empty">No user accounts has been found.</p>';
         }
         ?>
      </div>
   </section>

   <script src="../js/admin_script.js"></script>

</body>
</html>
