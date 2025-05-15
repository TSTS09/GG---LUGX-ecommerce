<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/wishlist_controller.php");

// Create wishlist controller instance
$wishlist_controller = new WishlistController();

// Check if wishlist item ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $wishlist_id = (int)$_GET['id'];
    
    // Handle based on user type
    if (is_logged_in()) {
        // For logged in users
        $customer_id = $_SESSION['customer_id'];
        $result = $wishlist_controller->remove_from_wishlist_ctr($wishlist_id, $customer_id);
    } else {
        // For guest users
        if (!isset($_SESSION['guest_session_id'])) {
            $_SESSION['guest_session_id'] = uniqid('guest_', true);
        }
        $guest_id = $_SESSION['guest_session_id'];
        
        $result = $wishlist_controller->remove_from_guest_wishlist_ctr($wishlist_id, $guest_id);
    }
    
    if ($result) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Item removed from wishlist successfully'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to remove item from wishlist'];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid wishlist item ID'];
}

// Redirect back to wishlist page
header("Location: ../View/wishlist.php");
exit;
?>