<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Create cart controller instance
$cart_controller = new CartController();

// Check if product ID is provided
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Validate inputs
    if ($product_id <= 0) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid product ID'];
        header("Location: ../View/cart.php");
        exit;
    }
    
    if ($quantity <= 0) {
        // If quantity is 0 or negative, remove item from cart
        header("Location: remove_from_cart.php?id=$product_id");
        exit;
    }
    
    if ($quantity > 10) {
        $quantity = 10; // Limit to maximum 10 items
    }
    
    // Handle based on user type
    if (is_logged_in()) {
        // For logged in users
        $customer_id = $_SESSION['customer_id'];
        $result = $cart_controller->update_cart_quantity_ctr($product_id, $customer_id, $quantity);
    } else {
        // For guest users
        if (!isset($_SESSION['guest_session_id'])) {
            $_SESSION['guest_session_id'] = uniqid('guest_', true);
        }
        $guest_id = $_SESSION['guest_session_id'];
        
        // Use a similar function for guests (you'll need to implement this)
        $ip_address = $_SERVER['REMOTE_ADDR'];
        // Remove the old item and add with new quantity
        $result = $cart_controller->remove_from_guest_cart_ctr($product_id, $guest_id) && 
                  $cart_controller->add_to_guest_cart_ctr($product_id, $ip_address, $guest_id, $quantity);
    }
    
    if ($result) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Cart updated successfully'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to update cart'];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid request method'];
}

header("Location: ../View/cart.php");
exit;
?>