<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Check if user is logged in
if (!is_logged_in()) {
    // Redirect to login page with a return URL
    header("Location: ../Login/login.php?redirect=cart");
    exit;
}

// Check if user is admin - admins should not be able to add to cart
if (is_admin()) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Admin accounts cannot add products to cart'
    ];

    // Redirect back to previous page
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "../View/all_product.php"));
    exit;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['id'])) {
    // Get product ID from POST or GET
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    // Get quantity (default to 1 if not specified)
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Validate inputs
    if ($product_id <= 0) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Invalid product ID'
        ];
        header("Location: ../View/all_product.php");
        exit;
    }

    if ($quantity <= 0 || $quantity > 10) {
        $quantity = 1; // Set to default if invalid
    }

    // Get customer ID from session
    $customer_id = $_SESSION['customer_id'];

    // Get client IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Create cart controller instance
    $cart_controller = new CartController();

    // Check if product is already in cart
    $existing_item = $cart_controller->check_product_in_cart_ctr($product_id, $customer_id);

    if ($existing_item) {
        // Product already in cart, redirect to cart page
        $_SESSION['message'] = [
            'type' => 'info',
            'text' => 'This product is already in your cart. You can adjust the quantity there.'
        ];
        header("Location: ../View/cart.php");
        exit;
    }

    // Add product to cart
    $result = $cart_controller->add_to_cart_ctr($product_id, $ip_address, $customer_id, $quantity);

    if ($result) {
        // Success, redirect to cart page
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Product added to cart successfully'
        ];
        header("Location: ../View/cart.php");
    } else {
        // Error, redirect back to product page
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to add product to cart'
        ];

        // Redirect back to the referring page
        if ($product_id) {
            header("Location: ../View/single_product.php?id=$product_id");
        } else {
            header("Location: ../View/all_product.php");
        }
    }
} else {
    // Invalid request method, redirect to home
    header("Location: ../index.php");
}
exit;
