<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php?redirect=cart");
    exit;
}

// Check if product ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    // Get customer ID from session
    $customer_id = $_SESSION['customer_id'];
    
    // Create cart controller instance
    $cart_controller = new CartController();
    
    // Remove item from cart
    $result = $cart_controller->remove_from_cart_ctr($product_id, $customer_id);
    
    if ($result) {
        // Success
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Item removed from cart successfully'
        ];
    } else {
        // Error
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to remove item from cart'
        ];
    }
} else {
    // Invalid product ID
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid product ID'
    ];
}

// Redirect back to cart page
header("Location: ../View/cart.php");
exit;
?>