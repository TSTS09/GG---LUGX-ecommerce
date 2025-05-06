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
    if (empty($_POST['brand_id']) || empty($_POST['brand_name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Brand ID and name are required'
        ]);
        exit;
    }
    
    $brand_id = (int)$_POST['brand_id'];
    $brand_name = trim($_POST['brand_name']);
    
    // Maximum length check (assuming brand_name field in DB is VARCHAR(100))
    if (strlen($brand_name) > 100) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Brand name is too long (maximum 100 characters)'
        ]);
        exit;
    }
    
    // Create controller instance
    $product_controller = new ProductController();
    
    // Update brand
    $result = $product_controller->update_brand_ctr($brand_id, $brand_name);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Brand updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update brand. It may already exist with that name.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>