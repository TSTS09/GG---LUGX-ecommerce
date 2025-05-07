<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php");
    exit;
}

// Get error message if available
$error_message = isset($_SESSION['payment_error']) ? $_SESSION['payment_error'] : "There was an issue processing your payment.";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - GG - LUGX</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" href="../Images/logo.png" type="image/png">

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1 0 auto;
            padding-bottom: 30px;
        }
        
        footer {
            flex-shrink: 0;
            width: 100%;
            margin-top: auto;
        }
        
        .failed-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
        }
        
        .failed-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .failed-icon {
            color: #dc3545;
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .btn-retry {
            background-color: #ee626b;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .btn-retry:hover {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-cart {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
            margin-top: 20px;
            margin-left: 10px;
        }
        
        .btn-cart:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="main-content">
        <div class="failed-container">
            <div class="failed-card">
                <div class="failed-icon">
                    <i class="fa fa-times-circle"></i>
                </div>
                <h2>Payment Failed</h2>
                <p>Unfortunately, your payment could not be processed.</p>
                <p><?php echo $error_message; ?></p>
                
                <div class="mt-4">
                    <a href="payment.php" class="btn btn-retry">Try Again</a>
                    <a href="cart.php" class="btn btn-cart">Back to Cart</a>
                </div>
                
                <div class="mt-4">
                    <p>If you continue to experience issues, please contact our customer support.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once('../includes/footer.php'); ?>

    <!-- Scripts -->
    <script src="../JS/jquery/jquery.min.js"></script>
    <script src="../JS/bootstrap/js/bootstrap.min.js"></script>
    <script src="../JS/isotope.min.js"></script>
    <script src="../JS/owl-carousel.js"></script>
    <script src="../JS/counter.js"></script>
    <script src="../JS/custom.js"></script>
</body>

</html>