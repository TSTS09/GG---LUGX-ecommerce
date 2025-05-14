<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Create cart controller instance
$cart_controller = new CartController();

// Check if product ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    // Handle based on user type
    if (is_logged_in()) {
        // For logged in users
        $customer_id = $_SESSION['customer_id'];
        $result = $cart_controller->remove_from_cart_ctr($product_id, $customer_id);
    } else {
        // For guest users
        if (!isset($_SESSION['guest_session_id'])) {
            $_SESSION['guest_session_id'] = uniqid('guest_', true);
        }
        $guest_id = $_SESSION['guest_session_id'];
        
        // Use a similar function for guests (you'll need to implement this)
        $result = $cart_controller->remove_from_guest_cart_ctr($product_id, $guest_id);
    }
    
    if ($result) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Item removed from cart successfully'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to remove item from cart'];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid product ID'];
}

// Redirect back to cart page
header("Location: ../View/cart.php");
exit;
?>