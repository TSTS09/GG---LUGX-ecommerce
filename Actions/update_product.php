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
    $required_fields = ['product_id', 'product_title', 'product_cat', 'product_brand', 'product_price', 'product_desc', 'product_keywords'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode([
                'status' => 'error',
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
            ]);
            exit;
        }
    }

    // Get form data
    $product_id = (int)$_POST['product_id'];
    $product_title = trim($_POST['product_title']);
    $product_cat = (int)$_POST['product_cat'];
    $product_brand = (int)$_POST['product_brand'];
    $product_price = (float)$_POST['product_price'];
    $product_desc = trim($_POST['product_desc']);
    $product_keywords = trim($_POST['product_keywords']);

    // Validate data types and lengths
    if (strlen($product_title) > 100) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product title is too long (maximum 100 characters)'
        ]);
        exit;
    }

    if ($product_price <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product price must be greater than zero'
        ]);
        exit;
    }

    // Create controller instance
    $product_controller = new ProductController();

    // Get current product data
    $current_product = $product_controller->get_one_product_ctr($product_id);

    // Handle image update if a new image is uploaded
    $image_path = $current_product['product_image'];

    if (!empty($_FILES['product_image']['name'])) {
        $target_dir = "../Images/product/";
        $timestamp = time();
        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $new_file_name = $timestamp . '_' . rand(1000, 9999) . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        $image_path = '../Images/product/' . $new_file_name;

        // Check if file is an actual image
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if ($check === false) {
            echo json_encode([
                'status' => 'error',
                'message' => 'File is not an image'
            ]);
            exit;
        }

        // Check file size (max 5MB)
        if ($_FILES["product_image"]["size"] > 5000000) {
            echo json_encode([
                'status' => 'error',
                'message' => 'File is too large (max 5MB)'
            ]);
            exit;
        }

        // Allow only certain file formats
        $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'
            ]);
            exit;
        }

        // Create upload directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Try to upload file
        if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to upload image'
            ]);
            exit;
        }

        // Delete old image if not the default
        if ($current_product['product_image'] && $current_product['product_image'] != '../Images/product/default.jpg' && file_exists($current_product['product_image'])) {
            unlink($current_product['product_image']);
        }
    }

    // Update product
    $result = $product_controller->update_product_ctr(
        $product_id,
        $product_cat,
        $product_brand,
        $product_title,
        $product_price,
        $product_desc,
        $image_path,
        $product_keywords
    );

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Product updated successfully'
        ]);
    } else {
        // If update fails but new image was uploaded, delete it
        if (!empty($_FILES['product_image']['name']) && file_exists($target_file)) {
            unlink($target_file);
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update product'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
