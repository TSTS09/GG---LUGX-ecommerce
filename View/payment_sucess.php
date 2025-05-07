<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php");
    exit;
}

// Check if order data is available
if (!isset($_SESSION['order_id']) || !isset($_SESSION['invoice_no']) || !isset($_SESSION['amount'])) {
    header("Location: ../index.php");
    exit;
}

// Get order data from session
$order_id = $_SESSION['order_id'];
$invoice_no = $_SESSION['invoice_no'];
$amount = $_SESSION['amount'];
$payment_date = $_SESSION['payment_date'];

// Create cart controller instance
$cart_controller = new CartController();

// Get order items
$order_items = $cart_controller->get_order_items_ctr($order_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - GG - LUGX</title>

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
        
        .success-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 30px;
        }
        
        .success-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .success-icon {
            color: #28a745;
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .invoice-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .invoice-title {
            font-size: 24px;
            color: #ee626b;
        }
        
        .btn-continue {
            background-color: #ee626b;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
            margin-top: a0px;
        }
        
        .btn-continue:hover {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-print {
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
        
        .btn-print:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="main-content">
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon">
                    <i class="fa fa-check-circle"></i>
                </div>
                <h2>Payment Successful!</h2>
                <p>Your order has been placed successfully. Thank you for shopping with us!</p>
                <p>An email has been sent to your registered email address with the order details.</p>
                
                <div class="mt-4">
                    <a href="../index.php" class="btn btn-continue">Continue Shopping</a>
                    <a href="print_invoice.php?id=<?php echo $order_id; ?>" target="_blank" class="btn btn-print">
                        <i class="fa fa-print"></i> Print Invoice
                    </a>
                </div>
            </div>
            
            <div class="invoice-container">
                <div class="invoice-header">
                    <div>
                        <div class="invoice-title">ORDER INVOICE</div>
                        <p>Invoice Number: <?php echo $invoice_no; ?></p>
                        <p>Date: <?php echo date('F j, Y', strtotime($payment_date)); ?></p>
                    </div>
                    <div>
                        <img src="../Images/logo.png" alt="Logo" style="max-height: 50px;">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($order_items['success'] && !empty($order_items['data'])): ?>
                                <?php foreach ($order_items['data'] as $item): ?>
                                    <tr>
                                        <td><?php echo $item['product_title']; ?></td>
                                        <td><?php echo $item['qty']; ?></td>
                                        <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['item_total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No items found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($amount, 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-4">
                    <p><strong>Note:</strong> This is a receipt of your purchase. Please keep it for your records.</p>
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