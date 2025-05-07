<?php
require_once(__DIR__ . "/../Controllers/product_controller.php");
require_once(__DIR__ . "/../Setting/core.php");

// Debug logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
error_log("Delete brand action called for ID: " . ($_GET['id'] ?? 'none'));

// Check if user is logged in and is admin
session_start();
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

// Check if ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $brand_id = (int)$_GET['id'];

    // Create controller instance
    $product_controller = new ProductController();

    // Delete brand
    $result = $product_controller->delete_brand_ctr($brand_id);

    error_log("Delete brand result: " . ($result ? "Success" : "Failed"));

    if ($result) {
        // Success message
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Brand deleted successfully'
        ];
    } else {
        // Error message
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to delete brand. It may be in use by products.'
        ];
    }
} else {
    // Error for missing ID
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid brand ID'
    ];
}

// Redirect back to brand page
header("Location: ../Admin/brand.php");
exit;
