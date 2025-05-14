<?php
require_once(__DIR__ . "/../Controllers/product_controller.php");
require_once(__DIR__ . "/../Setting/core.php");

// Check if user is logged in and is admin
session_start();
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

// Check if ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    // Create controller instance
    $product_controller = new ProductController();

    // Soft delete product (instead of hard delete)
    $result = $product_controller->soft_delete_product_ctr($product_id);

    if ($result) {
        // Redirect back to product management page with success message
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Product deleted successfully'
        ];
    } else {
        // Redirect back to product management page with error message
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to delete product'
        ];
    }
} else {
    // Redirect back to product management page with error message
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid product ID'
    ];
}

// Redirect back to product management page
header("Location: ../Admin/product.php");
exit;
