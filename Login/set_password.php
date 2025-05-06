<?php
require_once("../Controllers/customer_controller.php");

if (!isset($_GET['email'])) {
    die('Email parameter is missing');
}

$customer_email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email format');
}

// Check if email exists and get customer details
$customerController = new CustomerController();
$customer = $customerController->get_customer_by_email_ctr($customer_email);

if (!$customer) {
    die('Email not found');
}

// Get customer name
$customer_name = $customer['customer_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/setpassword.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="Images/logo.png" type="image/png">    
    <title>Reset Password</title>
    <style>
        .customer-greeting {
            margin-bottom: 20px;
            font-size: 16px;
            color: #666;
        }
        .customer-name {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="set-password-container">
        <h2 class="set-password-heading">Set New Password</h2>
        
        <div class="customer-greeting">
            Hello <span class="customer-name"><?php echo htmlspecialchars($customer_name); ?></span>, please set your new password below:
        </div>
        
        <div class="set-password-message" id="password-message"></div>
        <form id="setPasswordForm">
            <input type="hidden" name="customer_email" id="customer_email" value="<?php echo htmlspecialchars($customer_email); ?>">
            <div class="set-password-field-container">
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required class="set-password-input">
                <span toggle="#new_password" class="fa fa-fw fa-eye set-password-toggle"></span>
            </div>
            <div class="set-password-field-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required class="set-password-input">
                <span toggle="#confirm_password" class="fa fa-fw fa-eye set-password-toggle"></span>
            </div>
            <button type="submit" class="set-password-submit">Set Password</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/setpassword.js"></script>
</body>
</html>