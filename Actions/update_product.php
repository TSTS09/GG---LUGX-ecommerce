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

// Output PHP configuration for debugging
$php_settings = [
    'file_uploads' => ini_get('file_uploads'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'max_file_uploads' => ini_get('max_file_uploads')
];

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
    if (!$current_product) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found'
        ]);
        exit;
    }

    // Handle image update if a new image is uploaded
    $image_path = $current_product['product_image'];

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $target_dir = "../Images/product/";
        $timestamp = time();
        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $new_file_name = $timestamp . '_' . rand(1000, 9999) . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        $image_path = '../Images/product/' . $new_file_name;

        // Create upload directory if it doesn't exist
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create upload directory',
                    'details' => [
                        'target_dir' => $target_dir,
                        'php_settings' => $php_settings,
                        'current_dir' => getcwd(),
                        'parent_dir' => dirname(getcwd())
                    ]
                ]);
                exit;
            }
        }

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

        // Try to upload file
        if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $error_message = "Failed to upload image";

            // Add detailed error information
            $upload_error = error_get_last();
            if ($upload_error) {
                $error_message .= ": " . $upload_error['message'];
            }

            // Add information about the file and permissions
            $error_message .= " | Target dir exists: " . (file_exists($target_dir) ? 'Yes' : 'No');
            $error_message .= " | Target dir writable: " . (is_writable($target_dir) ? 'Yes' : 'No');
            $error_message .= " | PHP upload error code: " . $_FILES["product_image"]["error"];

            // Add temporary file information
            $error_message .= " | Temp file exists: " . (file_exists($_FILES["product_image"]["tmp_name"]) ? 'Yes' : 'No');

            echo json_encode([
                'status' => 'error',
                'message' => $error_message,
                'file_details' => [
                    'name' => $_FILES["product_image"]["name"],
                    'size' => $_FILES["product_image"]["size"],
                    'type' => $_FILES["product_image"]["type"],
                    'tmp_name' => $_FILES["product_image"]["tmp_name"],
                    'error' => $_FILES["product_image"]["error"],
                    'target_file' => $target_file,
                    'php_settings' => $php_settings
                ]
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
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0 && file_exists($target_file)) {
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
