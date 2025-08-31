<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'Payment status updated successfully!';
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

// Add search functionality
$search = '';
if(isset($_GET['search'])){
   $search = $_GET['search'];
   $search = filter_var($search, FILTER_SANITIZE_STRING);
}

?>
<?php include '../components/admin_navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders | Admin Panel</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
   <link rel="stylesheet" href="../css/admin_order.css">

</head>
<body>



<section class="orders pt-5">

   <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h1 class="heading">Placed Orders</h1>
      </div>

      <div class="card shadow-sm">
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-hover" id="ordersTable">
                  <thead class="table-dark">
                     <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Products</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                        $select_orders = $conn->prepare("SELECT * FROM `orders` 
                                                         WHERE id LIKE ? OR name LIKE ? OR number LIKE ? OR address LIKE ?
                                                         ORDER BY placed_on DESC");
                        $search_param = "%$search%";
                        $select_orders->execute([$search_param, $search_param, $search_param, $search_param]);
                        
                        if($select_orders->rowCount() > 0){
                           while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
                              $status_class = $fetch_orders['payment_status'] == 'completed' ? 'success' : 'warning';
                     ?>
                     <tr>
                        <td>#<?= $fetch_orders['id'] ?></td>
                        <td><?= date('M d, Y', strtotime($fetch_orders['placed_on'])) ?></td>
                        <td>
                           <div class="d-flex flex-column">
                              <strong><?= $fetch_orders['name'] ?></strong>
                              <small class="text-muted"><?= $fetch_orders['number'] ?></small>
                              <small><?= $fetch_orders['address'] ?></small>
                           </div>
                        </td>
                        <td>NRs. <?= number_format($fetch_orders['total_price'], 2) ?></td>
                        <td><?= $fetch_orders['total_products'] ?></td>
                        <td><?= ucfirst($fetch_orders['method']) ?></td>
                        <td>
                           <span class="badge bg-<?= $status_class ?>">
                              <?= ucfirst($fetch_orders['payment_status']) ?>
                           </span>
                        </td>
                        <td>
                           <div class="d-flex gap-2">
                              <form action="" method="post" class="d-inline">
                                 <input type="hidden" name="order_id" value="<?= $fetch_orders['id'] ?>">
                                 <input type="hidden" name="update_payment" value="1">
                                 <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pending" <?= $fetch_orders['payment_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="completed" <?= $fetch_orders['payment_status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                 </select>
                              </form>
                              <a href="placed_orders.php?delete=<?= $fetch_orders['id'] ?>" 
                                 class="btn btn-sm btn-danger" 
                                 onclick="return confirm('Are you sure you want to delete this order?');">
                                 <i class="fas fa-trash"></i>
                              </a>
                           </div>
                        </td>
                     </tr>
                     <?php
                           }
                        } else {
                     ?>
                     <tr>
                        <td colspan="8" class="text-center py-4">No orders found!</td>
                     </tr>
                     <?php } ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>

</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
   $(document).ready(function() {
      $('#ordersTable').DataTable({
         "order": [[0, "desc"]],
         "responsive": true,
         "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search orders...",
            "emptyTable": "No orders available",
            "info": "Showing _START_ to _END_ of _TOTAL_ orders",
            "infoEmpty": "Showing 0 to 0 of 0 orders",
            "lengthMenu": "Show _MENU_ orders per page"
         }
      });
   });
</script>
<script src="../js/admin_script.js"></script>
   
</body>
</html>