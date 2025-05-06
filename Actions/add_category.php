<?php
require_once(__DIR__ . "/../Controllers/product_controller.php");
require_once(__DIR__ . "/../Setting/core.php");

// Check if user is logged in and is admin
session_start();
if (!is_logged_in() || !is_admin()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate the input
    if (empty($_POST['cat_name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category name is required'
        ]);
        exit;
    }
    
    $cat_name = trim($_POST['cat_name']);
    
    // Maximum length check (assuming cat_name field in DB is VARCHAR(100))
    if (strlen($cat_name) > 100) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category name is too long (maximum 100 characters)'
        ]);
        exit;
    }
    
    // Create controller instance
    $product_controller = new ProductController();
    
    // Add category
    $result = $product_controller->add_category_ctr($cat_name);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Category added successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add category. It may already exist.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>