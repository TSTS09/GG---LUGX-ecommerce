<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php?redirect=cart");
    exit;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    
    // Validate inputs
    if ($product_id <= 0) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Invalid product ID'
        ];
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
    
    // Get customer ID from session
    $customer_id = $_SESSION['customer_id'];
    
    // Create cart controller instance
    $cart_controller = new CartController();
    
    // Update cart quantity
    $result = $cart_controller->update_cart_quantity_ctr($product_id, $customer_id, $quantity);
    
    if ($result) {
        // Success
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Cart updated successfully'
        ];
    } else {
        // Error
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to update cart'
        ];
    }
} else {
    // Invalid request method
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid request method'
    ];
}

// Redirect back to cart page
header("Location: ../View/cart.php");
exit;
?>