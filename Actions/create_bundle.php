<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/bundle_controller.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty($_POST['bundle_title']) || empty($_POST['bundle_price']) || empty($_POST['bundle_desc'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Please fill in all required fields'];
        header("Location: ../Admin/bundle.php");
        exit;
    }
    
    if (empty($_POST['product_ids']) || !is_array($_POST['product_ids'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Please select at least one product for the bundle'];
        header("Location: ../Admin/bundle.php");
        exit;
    }
    
    // Get form data
    $bundle_title = trim($_POST['bundle_title']);
    $bundle_price = (float)$_POST['bundle_price'];
    $bundle_desc = trim($_POST['bundle_desc']);
    $bundle_keywords = trim($_POST['bundle_keywords']);
    $product_ids = $_POST['product_ids'];
    $discounts = isset($_POST['discounts']) ? $_POST['discounts'] : array_fill(0, count($product_ids), 0);
    
    // Handle image upload
    $image_path = '../Images/product/default.jpg'; // Default image
    
    if (isset($_FILES['bundle_image']) && $_FILES['bundle_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $target_dir = "../Images/product/";
        $timestamp = time();
        $file_extension = strtolower(pathinfo($_FILES["bundle_image"]["name"], PATHINFO_EXTENSION));
        $new_file_name = $timestamp . '_' . rand(1000, 9999) . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        $image_path = '../Images/product/' . $new_file_name;
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Only JPG, JPEG, PNG & GIF files are allowed'];
            header("Location: ../Admin/bundle.php");
            exit;
        }
        
        // Check file size (max 5MB)
        if ($_FILES["bundle_image"]["size"] > 5000000) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'File is too large (max 5MB)'];
            header("Location: ../Admin/bundle.php");
            exit;
        }
        
        // Upload file
        if (!move_uploaded_file($_FILES["bundle_image"]["tmp_name"], $target_file)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to upload image'];
            header("Location: ../Admin/bundle.php");
            exit;
        }
    }
    
    // Create bundle
    $bundle_controller = new BundleController();
    $result = $bundle_controller->create_bundle_ctr(
        $bundle_title,
        $bundle_price,
        $bundle_desc,
        $image_path,
        $bundle_keywords,
        $product_ids,
        $discounts
    );
    
    if ($result) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Bundle created successfully'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to create bundle'];
    }
    
    header("Location: ../Admin/bundle.php");
    exit;
} else {
    header("Location: ../Admin/bundle.php");
    exit;
}
?>