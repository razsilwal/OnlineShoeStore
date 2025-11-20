<?php
class CartHandler {
    private $conn;
    private $user_id;

    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function deleteItem($cart_id) {
        // Add user_id check for security
        $delete = $this->conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
        return $delete->execute([$cart_id, $this->user_id]);
    }

    public function deleteAllItems() {
        $delete = $this->conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        return $delete->execute([$this->user_id]);
    }

    public function updateQuantity($cart_id, $qty) {
        // Better quantity validation
        $qty = filter_var($qty, FILTER_VALIDATE_INT, array(
            "options" => array("min_range" => 1, "max_range" => 99)
        ));
        
        if (!$qty) {
            return false; // Invalid quantity
        }
        
        // Add user_id check for security
        $update = $this->conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
        return $update->execute([$qty, $cart_id, $this->user_id]);
    }

    public function getUserCart() {
        $select = $this->conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $select->execute([$this->user_id]);
        return $select;
    }
}
?>