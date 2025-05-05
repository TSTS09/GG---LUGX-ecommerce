<?php
// Start with clean output buffer
ob_start();
// Set error handling
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
// Start session
session_start();
// Function to log errors instead of displaying them
function exception_error_handler($severity, $message, $file, $line) {
    error_log("Error ($severity): $message in $file on line $line");
    return true;
}
set_error_handler("exception_error_handler");
// Always send JSON response
header('Content-Type: application/json');
try {
    // Include controllers
    require_once("../Controllers/user_controllers.php");
    require_once("../Controllers/customer_controller.php");
   
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Invalid request method. Only POST requests are accepted.');
    }
   
    // Get login credentials
    $email = $_POST['customer_email'] ?? '';
    $password = $_POST['customer_pass'] ?? '';
    if (empty($email) || empty($password)) {
        throw new Exception("Email and password are required");
    }
    $userController = new UserController();
    $customerController = new CustomerController();
    // Check if it's a user (handled by UserController)
    if ($userController->check_email_exists($email)) {
        $user = $userController->login_user_ctr($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['customer_id'];
            $_SESSION['customer_id'] = $user['customer_id']; // Also set customer_id for consistency
            $_SESSION['user_name'] = $user['customer_name'];
            $_SESSION['customer_name'] = $user['customer_name']; // Also set customer_name for consistency
            $_SESSION['user_email'] = $user['customer_email'];
            $_SESSION['customer_email'] = $user['customer_email']; // Also set customer_email for consistency
            $_SESSION['user_image'] = $user['customer_image'] ?? null;
            $_SESSION['user_role'] = $user['user_role'];
           
            // Clear output buffer
            ob_end_clean();
            
            // Determine redirect based on user role
            $redirect = "../index.php"; // Default redirect
            if ($user['user_role'] == 1) { // Admin
                $redirect = "../Admin/product.php"; // Admin panel redirect
            } else { // Regular user
                $redirect = "../View/all_product.php"; // Product listing redirect
            }
           
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "redirect" => $redirect
            ]);
            exit;
        }
    }
   
    // Check if it's a customer (handled by CustomerController)
    if ($customerController->check_email_exists($email)) {
        $customer = $customerController->login_customer_ctr($email, $password);
        if ($customer) {
            $_SESSION['customer_id'] = $customer['customer_id'];
            $_SESSION['user_id'] = $customer['customer_id']; // Also set user_id for consistency
            $_SESSION['customer_name'] = $customer['customer_name'];
            $_SESSION['user_name'] = $customer['customer_name']; // Also set user_name for consistency
            $_SESSION['customer_email'] = $customer['customer_email'];
            $_SESSION['user_email'] = $customer['customer_email']; // Also set user_email for consistency
            $_SESSION['customer_image'] = $customer['customer_image'] ?? null;
            $_SESSION['user_role'] = $customer['user_role'];
           
            // Clear output buffer
            ob_end_clean();
            
            // Determine redirect based on user role
            $redirect = "../index.php"; // Default redirect
            if ($customer['user_role'] == 1) { // Admin
                $redirect = "../Admin/product.php"; // Admin panel redirect
            } else { // Regular customer
                $redirect = "../View/all_product.php"; // Product listing redirect
            }
           
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "redirect" => $redirect
            ]);
            exit;
        }
    }
   
    // If we reached here, authentication failed
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
   
} catch (Exception $e) {
    // Log the full exception for debugging
    error_log("Login error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
   
    // Clear any buffered output
    ob_end_clean();
   
    // Send clean error response
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>