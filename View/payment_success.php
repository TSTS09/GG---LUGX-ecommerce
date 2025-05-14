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
$amount = $_SESSION['amount']; // USD amount
$ghs_amount = isset($_SESSION['ghs_amount']) ? $_SESSION['ghs_amount'] : ($amount * $_SESSION['exchange_rate']);
$exchange_rate = isset($_SESSION['exchange_rate']) ? $_SESSION['exchange_rate'] : 12.5; // Default if not set
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
    <link rel="stylesheet" href="../CSS/payment_success.css">

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

                <div class="currency-info">
                    <p><i class="fa fa-info-circle"></i> <strong>Currency Information:</strong></p>
                    <p>Your payment of <strong>GH₵<?php echo number_format($ghs_amount, 2); ?></strong> was processed in Ghanaian Cedis (GHS).</p>
                    <p>Exchange rate applied: <strong>1 USD = <?php echo number_format($exchange_rate, 2); ?> GHS</strong></p>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price (USD)</th>
                                <th>Total (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($order_items['success'] && !empty($order_items['data'])): ?>
                                <?php foreach ($order_items['data'] as $item): ?>
                                    <tr <?php echo isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1 ? 'class="table-secondary"' : ''; ?>>
                                        <td>
                                            <?php echo $item['product_title']; ?>
                                            <?php if (isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1): ?>
                                                <span class="badge badge-secondary">Product no longer available</span>
                                            <?php endif; ?>
                                        </td>
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
                                <td colspan="3" class="text-right"><strong>Total (USD):</strong></td>
                                <td><strong>$<?php echo number_format($amount, 2); ?></strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total (GHS):</strong></td>
                                <td><strong>GH₵<?php echo number_format($ghs_amount, 2); ?></strong></td>
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