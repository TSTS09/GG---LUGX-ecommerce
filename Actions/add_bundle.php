<?php
require_once(__DIR__ . "/../Controllers/product_controller.php");
require_once(__DIR__ . "/../Controllers/bundle_controller.php");
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

    // Create controller instances
    $product_controller = new ProductController();
    $bundle_controller = new BundleController();
    
    // Check if this is a bundle
    $product = $product_controller->get_one_product_ctr($product_id);
    if ($product && isset($product['is_bundle']) && $product['is_bundle'] == 1) {
        // If it's a bundle, delete bundle items first
        $bundle_controller->delete_bundle_items_ctr($product_id);
    }

    // Now delete the product/bundle
    $result = $product_controller->soft_delete_product_ctr($product_id);

    if ($result) {
        // Redirect back to product management page with success message
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Product/Bundle deleted successfully'
        ];
    } else {
        // Redirect back to product management page with error message
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to delete product/bundle'
        ];
    }
} else {
    // Redirect back to product management page with error message
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid product ID'
    ];
}

// Redirect back to appropriate page
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'bundle.php') !== false) {
    header("Location: ../Admin/bundle.php");
} else {
    header("Location: ../Admin/product.php");
}
exit;