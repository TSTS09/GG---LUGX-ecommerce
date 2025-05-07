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
    $brand_id = (int)$_GET['id'];
    
    // Create controller instance
    $product_controller = new ProductController();
    
    // Delete brand
    $result = $product_controller->delete_brand_ctr($brand_id);
    
    if ($result) {
        // Redirect back to brand page with success message
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Brand deleted successfully'
        ];
    } else {
        // Redirect back to brand page with error message
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to delete brand. It may be in use by products.'
        ];
    }
} else {
    // Redirect back to brand page with error message
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid brand ID'
    ];
}

// Redirect back to brand page
header("Location: ../Admin/brand.php");
exit;
?>