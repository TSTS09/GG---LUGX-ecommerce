<?php
// Start with clean output buffer
ob_start();

// Set error handling
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Function to log errors instead of displaying them
function exception_error_handler($severity, $message, $file, $line) {
    error_log("Error ($severity): $message in $file on line $line");
    
    // Don't execute PHP's internal error handler
    return true;
}
set_error_handler("exception_error_handler");

// Always send JSON response
header('Content-Type: application/json');

try {
    // Include controller
    require_once("../Controllers/user_controllers.php");
    
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Invalid request method. Only POST requests are accepted.');
    }
    
    // Validate required fields
    $required_fields = ['customer_name', 'customer_email', 'customer_pass', 
                        'customer_country', 'customer_city', 'customer_contact'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Get form data
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_pass = $_POST['customer_pass'];
    $customer_country = $_POST['customer_country'];
    $customer_city = $_POST['customer_city'];
    $customer_contact = $_POST['customer_contact'];
    
    // Handle the optional image upload
    $customer_image = null;
    if (isset($_FILES['customer_image']) && $_FILES['customer_image']['error'] == 0) {
        $upload_dir = "../Images/customer_images/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }
        
        // Generate a unique filename
        $file_extension = pathinfo($_FILES['customer_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['customer_image']['tmp_name'], $upload_dir . $filename)) {
            $customer_image = $filename;
        } else {
            throw new Exception("Failed to upload image");
        }
    }
    
    // Set default user role for regular customers
    $user_role = 2;
    
    // Instantiate controller
    $userController = new UserController();
    
    // Attempt to add user
    $response = $userController->add_user(
        $customer_name,
        $customer_email,
        $customer_pass,
        $customer_country,
        $customer_city,
        $customer_contact,
        $customer_image,
        $user_role
    );
    
    // Process response
    if ($response === true) {
        // Clear any buffered output
        ob_end_clean();
        echo json_encode([
            'success' => true, 
            'message' => 'Your account has been created successfully.'
        ]);
    } else {
        // Clear any buffered output
        ob_end_clean();
        echo json_encode([
            'success' => false, 
            'message' => is_string($response) ? $response : 'Registration failed.'
        ]);
    }
    
} catch (Exception $e) {
    // Log the full exception for debugging
    error_log("Registration error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // Clear any buffered output
    ob_end_clean();
    
    // Send clean error response
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>