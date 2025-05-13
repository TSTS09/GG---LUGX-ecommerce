<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate the input
    if (empty($_POST['order_id']) || empty($_POST['order_status'])) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Order ID and status are required'
        ];
        header("Location: ../Admin/orders.php");
        exit;
    }
    
    $order_id = (int)$_POST['order_id'];
    $order_status = $_POST['order_status'];
    
    // Create controller instance
    $cart_controller = new CartController();
    
    // Update order status
    $result = $cart_controller->update_order_status_ctr($order_id, $order_status);
    
    if ($result) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Order status updated successfully'
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to update order status'
        ];
    }
    
    // Redirect back to orders page
    header("Location: ../Admin/orders.php");
    exit;
} else {
    // If not POST request, redirect to orders page
    header("Location: ../Admin/orders.php");
    exit;
}
