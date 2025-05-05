<?php
session_start();
require_once("../Controllers/customer_controller.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Validate inputs
    if (empty($_POST['customer_email']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
        throw new Exception('All fields are required');
    }

    $email = filter_var($_POST['customer_email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password
    if (strlen($new_password) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }

    if ($new_password !== $confirm_password) {
        throw new Exception('Passwords do not match');
    }

    // Update password
    $customerController = new CustomerController();
    
    // Verify the email exists
    if (!$customerController->check_email_exists($email)) {
        throw new Exception('Email not found');
    }
    
    $result = $customerController->update_password_ctr($email, $new_password);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        throw new Exception('Failed to update password');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>