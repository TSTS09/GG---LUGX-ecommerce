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
    $cat_id = (int)$_GET['id'];
    
    // Create controller instance
    $product_controller = new ProductController();
    
    // Delete category
    $result = $product_controller->delete_category_ctr($cat_id);
    
    if ($result) {
        // Redirect back to category page with success message
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Category deleted successfully'
        ];
    } else {
        // Redirect back to category page with error message
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to delete category. It may be in use by products.'
        ];
    }
} else {
    // Redirect back to category page with error message
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid category ID'
    ];
}

// Redirect back to category page
header("Location: ../Admin/category.php");
exit;
?>