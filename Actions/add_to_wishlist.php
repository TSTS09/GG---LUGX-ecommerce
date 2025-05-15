<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/wishlist_controller.php");

// Create wishlist controller instance
$wishlist_controller = new WishlistController();

// Check if product ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    // Handle based on user type
    if (is_logged_in()) {
        // For logged in users
        $customer_id = $_SESSION['customer_id'];
        $result = $wishlist_controller->add_to_wishlist_ctr($product_id, $customer_id);
    } else {
        // For guest users
        if (!isset($_SESSION['guest_session_id'])) {
            $_SESSION['guest_session_id'] = uniqid('guest_', true);
        }
        $guest_id = $_SESSION['guest_session_id'];
        
        $result = $wishlist_controller->add_to_guest_wishlist_ctr($product_id, $guest_id);
    }
    
    if ($result) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Item added to wishlist successfully'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to add item to wishlist'];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid product ID'];
}

// Redirect back to referring page
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: ../View/wishlist.php");
}
exit;
?>