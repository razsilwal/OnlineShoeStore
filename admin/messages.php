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
   $delete_message = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
   $delete_message->execute([$delete_id]);
   header('location:messages.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages | Admin Panel</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Google Font -->
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">

   <!-- Custom Inline Style (Replace with admin_style.css if needed) -->
   <style>
      * {
         font-family: 'Roboto', sans-serif;
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         background-color: #f9fafb;
         color: #333;
      }

      .heading {
         background-color: #2563eb;
         color: #fff;
         padding: 1.5rem;
         text-align: center;
         font-size: 2rem;
         text-transform: uppercase;
         letter-spacing: 1px;
      }

      .contacts {
         padding: 2rem;
      }

      .box-container {
         display: flex;
         flex-wrap: wrap;
         gap: 1.5rem;
         justify-content: center;
      }

      .box {
         background-color: #fff;
         border-radius: 10px;
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
         padding: 1.5rem;
         width: 330px;
         transition: all 0.3s ease;
      }

      .box:hover {
         transform: translateY(-5px);
         box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
      }

      .box p {
         margin-bottom: 0.8rem;
         font-size: 1rem;
      }

      .box span {
         color: #1e3a8a;
         font-weight: bold;
      }

      .delete-btn {
         display: inline-block;
         margin-top: 1rem;
         padding: 0.6rem 1.3rem;
         background-color: #dc2626;
         color: white;
         border-radius: 5px;
         text-decoration: none;
         font-weight: bold;
         transition: background 0.3s;
      }

      .delete-btn:hover {
         background-color: #b91c1c;
      }

      .empty {
         text-align: center;
         font-size: 1.2rem;
         color: #666;
         padding: 2rem;
      }

      @media (max-width: 768px) {
         .box {
            width: 90%;
         }
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="contacts">
   <h1 class="heading">Messages</h1>

   <div class="box-container">
      <?php
      $select_messages = $conn->prepare("SELECT * FROM `messages` ORDER BY id DESC");
      $select_messages->execute();

      if ($select_messages->rowCount() > 0) {
         while ($fetch_message = $select_messages->fetch(PDO::FETCH_ASSOC)) {
      ?>
         <div class="box">
            <p><strong>User ID:</strong> <span><?= htmlspecialchars($fetch_message['user_id']); ?></span></p>
            <p><strong>Name:</strong> <span><?= htmlspecialchars($fetch_message['name']); ?></span></p>
            <p><strong>Email:</strong> <span><?= htmlspecialchars($fetch_message['email']); ?></span></p>
            <p><strong>Phone:</strong> <span><?= htmlspecialchars($fetch_message['number']); ?></span></p>
            <p><strong>Message:</strong> <br><span><?= nl2br(htmlspecialchars($fetch_message['message'])); ?></span></p>
            <a href="messages.php?delete=<?= $fetch_message['id']; ?>" onclick="return confirm('Are you sure you want to delete this message?');" class="delete-btn">Delete</a>
         </div>
      <?php
         }
      } else {
         echo '<p class="empty">You have no messages.</p>';
      }
      ?>
   </div>
</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
