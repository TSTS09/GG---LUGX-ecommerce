<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/bundle_controller.php");
require_once("../Controllers/product_controller.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty($_POST['bundle_id']) || empty($_POST['bundle_title']) || empty($_POST['bundle_price']) || empty($_POST['bundle_desc'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Please fill in all required fields'];
        header("Location: ../Admin/bundle.php");
        exit;
    }

    if (empty($_POST['product_ids']) || !is_array($_POST['product_ids'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Please select at least one product for the bundle'];
        header("Location: ../Admin/edit_bundle.php?id=" . $_POST['bundle_id']);
        exit;
    }

    // Get form data
    $bundle_id = (int)$_POST['bundle_id'];
    $bundle_title = trim($_POST['bundle_title']);
    $bundle_price = (float)$_POST['bundle_price'];
    $bundle_desc = trim($_POST['bundle_desc']);
    $bundle_keywords = trim($_POST['bundle_keywords']);
    $product_ids = $_POST['product_ids'];
    $discounts = isset($_POST['discounts']) ? $_POST['discounts'] : array_fill(0, count($product_ids), 0);
    $quantities = isset($_POST['quantities']) && is_array($_POST['quantities']) ? $_POST['quantities'] : array_fill(0, count($product_ids), 1);

    // Calculate the total price of all selected products
    $total_original_price = 0;
    foreach ($product_ids as $product_id) {
        // Get product details
        $product = $product_controller->get_one_product_ctr($product_id);
        if ($product) {
            $total_original_price += $product['product_price'];
        }
    }

    // Check if bundle price is higher than total original price
    if ($bundle_price >= $total_original_price) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Bundle price must be lower than the total price of individual products ($' . number_format($total_original_price, 2) . ')'];
        header("Location: ../Admin/edit_bundle.php?id=" . $bundle_id);
        exit;
    }

    // Get current bundle info
    $product_controller = new ProductController();
    $bundle = $product_controller->get_one_product_ctr($bundle_id);

    if (!$bundle || !isset($bundle['is_bundle']) || $bundle['is_bundle'] != 1) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Bundle not found or invalid'];
        header("Location: ../Admin/bundle.php");
        exit;
    }

    // Handle image upload
    $image_path = $bundle['product_image']; // Keep current image by default

    if (isset($_FILES['bundle_image']) && $_FILES['bundle_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $target_dir = "../Images/product/";
        $timestamp = time();
        $file_extension = strtolower(pathinfo($_FILES["bundle_image"]["name"], PATHINFO_EXTENSION));
        $new_file_name = $timestamp . '_' . rand(1000, 9999) . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        $new_image_path = '../Images/product/' . $new_file_name;

        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Only JPG, JPEG, PNG & GIF files are allowed'];
            header("Location: ../Admin/edit_bundle.php?id=" . $bundle_id);
            exit;
        }

        // Check file size (max 5MB)
        if ($_FILES["bundle_image"]["size"] > 5000000) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'File is too large (max 5MB)'];
            header("Location: ../Admin/edit_bundle.php?id=" . $bundle_id);
            exit;
        }

        // Upload file
        if (move_uploaded_file($_FILES["bundle_image"]["tmp_name"], $target_file)) {
            // Delete old image if it's not the default
            if ($image_path != '../Images/product/default.jpg' && file_exists($image_path)) {
                unlink($image_path);
            }

            $image_path = $new_image_path;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to upload image'];
            header("Location: ../Admin/edit_bundle.php?id=" . $bundle_id);
            exit;
        }
    }

    // Update bundle
    $bundle_controller = new BundleController();

    // First update the product details
    $product_controller = new ProductController();
    $update_product_result = $product_controller->update_product_ctr(
        $bundle_id,
        $bundle['product_cat'], // Keep original category
        $bundle['product_brand'], // Keep original brand
        $bundle_title,
        $bundle_price,
        $bundle_desc,
        $image_path,
        $bundle_keywords,
    );

    if ($update_product_result) {
        // Now update bundle items - first delete existing items
        $delete_items_result = $bundle_controller->delete_bundle_items_ctr($bundle_id);

        // Then add new items
        $add_items_result = $bundle_controller->add_bundle_items_ctr($bundle_id, $product_ids, $discounts, $quantities);

        if ($add_items_result) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Bundle updated successfully'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to update bundle items'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to update bundle details'];
    }

    header("Location: ../Admin/bundle.php");
    exit;
} else {
    header("Location: ../Admin/bundle.php");
    exit;
}
