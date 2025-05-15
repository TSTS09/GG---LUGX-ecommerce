<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/bundle_controller.php");
require_once("../Controllers/product_controller.php");

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("Bundle creation process started");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Unauthorized access'];
    header("Location: ../Login/login.php");
    exit;
}

// Check for required form data
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid request method'];
    header("Location: ../Admin/bundle.php");
    exit;
}

// Validate product selection
if (empty($_POST['product_ids']) || !is_array($_POST['product_ids'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Please select at least one product for the bundle'];
    error_log("No products selected for bundle");
    header("Location: ../Admin/bundle.php");
    exit;
}

$product_ids = $_POST['product_ids'];
// Make sure discounts is an array even if not set
$discounts = isset($_POST['discounts']) && is_array($_POST['discounts']) ? $_POST['discounts'] : array_fill(0, count($product_ids), 0);

// Ensure both arrays are the same length
if (count($discounts) < count($product_ids)) {
    $discounts = array_pad($discounts, count($product_ids), 0);
}
$quantities = isset($_POST['quantities']) && is_array($_POST['quantities']) ? $_POST['quantities'] : array_fill(0, count($product_ids), 1);
// Create controller instances
$bundle_controller = new BundleController();
$product_controller = new ProductController();

try {
    // Validate inputs
    if (empty($_POST['bundle_title']) || empty($_POST['bundle_price']) || empty($_POST['bundle_desc'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Please fill in all required fields'];
        header("Location: ../Admin/bundle.php");
        exit;
    }

    // Get form data
    $bundle_title = trim($_POST['bundle_title']);
    $bundle_price = (float)$_POST['bundle_price'];
    $bundle_desc = trim($_POST['bundle_desc']);
    $bundle_keywords = trim($_POST['bundle_keywords']);

    // Validate bundle price is lower than total
    $total_original_price = 0;
    foreach ($product_ids as $product_id) {
        // Get product details
        $product = $product_controller->get_one_product_ctr($product_id);
        if ($product) {
            $total_original_price += $product['product_price'];
        }
    }

    if ($bundle_price >= $total_original_price) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Bundle price must be lower than the total price of individual products ($' .
                number_format($total_original_price, 2) . ')'
        ];
        header("Location: ../Admin/bundle.php");
        exit;
    }

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
            if (!mkdir($target_dir, 0777, true)) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to create upload directory'];
                header("Location: ../Admin/bundle.php");
                exit;
            }
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
            $error_message = "Failed to upload image";
            $upload_error = error_get_last();
            if ($upload_error) {
                $error_message .= ": " . $upload_error['message'];
            }

            $_SESSION['message'] = ['type' => 'error', 'text' => $error_message];
            header("Location: ../Admin/bundle.php");
            exit;
        }
    }

    // Log parameters being passed to create_bundle_ctr
    error_log("Bundle creation parameters:");
    error_log("Title: " . $bundle_title);
    error_log("Price: " . $bundle_price);
    error_log("Description: " . substr($bundle_desc, 0, 100) . (strlen($bundle_desc) > 100 ? '...' : ''));
    error_log("Image: " . $image_path);
    error_log("Keywords: " . $bundle_keywords);
    error_log("Product IDs: " . implode(',', $product_ids));
    error_log("Discounts: " . implode(',', $discounts));

    // Create bundle
    $result = $bundle_controller->create_bundle_ctr(
        $bundle_title,
        $bundle_price,
        $bundle_desc,
        $image_path,
        $bundle_keywords,
        $product_ids,
        $discounts, 
        $quantities 
    );

    if ($result) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Bundle created successfully! Bundle ID: ' . $result
        ];
    } else {
        // Get the detailed error message if available
        $error_msg = isset($_SESSION['detailed_error']) ? $_SESSION['detailed_error'] : 'Unknown error';
        unset($_SESSION['detailed_error']); // Clear it after use

        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to create bundle. ' . $error_msg
        ];

        error_log("Bundle creation failed: " . $error_msg);
    }
} catch (Exception $e) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Error creating bundle: ' . $e->getMessage()
    ];
    error_log("Exception in bundle creation: " . $e->getMessage());
}

header("Location: ../Admin/bundle.php");
exit;
