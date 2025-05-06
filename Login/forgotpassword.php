<?php
require_once("../Controllers/customer_controller.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['email'])) {
        $error = "Email is required.";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check if email exists
            $customerController = new CustomerController();
            if ($customerController->check_email_exists($email)) {
                // Redirect to set_password.php with email parameter
                header("Location: set_password.php?email=" . urlencode($email));
                exit;
            } else {
                $error = "Email not found.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="Images/logo.png" type="image/png"> 
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .forgot-password-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .forgot-password-heading {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            color: #333;
        }
        .forgot-password-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            margin-bottom: 1rem;
        }
        .forgot-password-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
        .forgot-password-submit {
            width: 100%;
            padding: 0.8rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        .forgot-password-submit:hover {
            background-color: #0056b3;
        }
        .forgot-password-error {
            color: #dc3545;
            margin-bottom: 1rem;
            min-height: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h2 class="forgot-password-heading">Forgot Password</h2>
        <div class="forgot-password-error"><?php echo isset($error) ? htmlspecialchars($error) : ''; ?></div>
        <form method="POST">
            <input type="email" name="email" class="forgot-password-input" placeholder="Enter your email" required>
            <button type="submit" class="forgot-password-submit">Reset Password</button>
            <p class="text-center">
            or did you remember your password? <a href="login.php">Login</a>
        </p>
        </form>
    </div>
    
</body>
</html>