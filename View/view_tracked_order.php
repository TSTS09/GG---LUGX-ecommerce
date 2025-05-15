<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Check if order is in session
if (!isset($_SESSION['tracked_order']) || !isset($_GET['id'])) {
    header("Location: track_order.php");
    exit;
}

$order = $_SESSION['tracked_order'];
$order_id = $_GET['id'];

// Verify the order ID matches
if ($order['order_id'] != $order_id) {
    header("Location: track_order.php");
    exit;
}

// Create cart controller to get order items
$cart_controller = new CartController();
$order_items = $cart_controller->get_order_items_ctr($order_id);
$payment_info = $cart_controller->get_payment_info_ctr($order_id);

// Get exchange rate from the payment info or use a default
$exchange_rate = isset($payment_info['exchange_rate']) ? $payment_info['exchange_rate'] : 12.5;

// Calculate GHS amount if available
$usd_amount = $order['order_amount'];
$ghs_amount = isset($payment_info['ghs_amount']) ? $payment_info['ghs_amount'] : ($usd_amount * $exchange_rate);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - GG - LUGX</title>

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
        .order-container {
            padding: 30px;
            max-width: 1200px;
            margin: 100px auto;
        }

        .order-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .order-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-canceled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        .currency-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 3px solid #17a2b8;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="order-container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="mb-4">Order Details</h2>
                <a href="track_order.php" class="btn btn-secondary mb-4">
                    <i class="fa fa-arrow-left"></i> Back to Track Order
                </a>
            </div>
        </div>

        <div class="order-card">
            <div class="order-header">
                <div>
                    <h3>Order #<?php echo $order_id; ?></h3>
                    <p>Invoice: <?php echo $order['invoice_no']; ?></p>
                    <p>Order Date: <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                </div>
                <div>
                    <?php
                    $status_class = '';
                    switch ($order['order_status']) {
                        case 'Completed':
                            $status_class = 'status-completed';
                            break;
                        case 'Processing':
                            $status_class = 'status-processing';
                            break;
                        case 'Shipped':
                            $status_class = 'status-shipped';
                            break;
                        case 'Delivered':
                            $status_class = 'status-delivered';
                            break;
                        case 'Pending':
                            $status_class = 'status-pending';
                            break;
                        case 'Cancelled':
                            $status_class = 'status-canceled';
                            break;
                        default:
                            $status_class = 'status-processing';
                    }
                    ?>
                    <span class="order-status <?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="section-title">Customer Information</div>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['guest_email']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['guest_name'] ?? 'Guest User'); ?></p>
                    <?php if (isset($order['guest_phone']) && !empty($order['guest_phone'])): ?>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['guest_phone']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($order['guest_address']) && !empty($order['guest_address'])): ?>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['guest_address']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <div class="section-title">Payment Information</div>
                    <p><strong>Payment Method:</strong> <?php echo $payment_info['payment_method'] ?? 'Online Payment'; ?></p>
                    <?php if (isset($payment_info['transaction_id'])): ?>
                        <p><strong>Transaction ID:</strong> <?php echo $payment_info['transaction_id']; ?></p>
                    <?php endif; ?>
                    <?php if (isset($payment_info['payment_date'])): ?>
                        <p><strong>Payment Date:</strong> <?php echo date('F j, Y', strtotime($payment_info['payment_date'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="currency-info">
                <p><strong>Currency Information:</strong></p>
                <p>Payment processed in Ghanaian Cedis (GHS).</p>
                <p>Exchange rate: 1 USD = <?php echo number_format($exchange_rate, 2); ?> GHS</p>
                <p>Amount in GHS: GH₵<?php echo number_format($ghs_amount, 2); ?></p>
            </div>

            <div class="section-title">Order Items</div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price (USD)</th>
                            <th>Total (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subtotal = 0;
                        if ($order_items['success'] && !empty($order_items['data'])):
                            foreach ($order_items['data'] as $item):
                                $item_total = $item['product_price'] * $item['qty'];
                                $subtotal += $item_total;
                        ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                        <?php endif; ?>
                                        <?php echo $item['product_title']; ?>
                                        <?php if (isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1): ?>
                                            <span class="badge badge-secondary">Product no longer available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item['qty']; ?></td>
                                    <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                    <td>$<?php echo number_format($item_total, 2); ?></td>
                                </tr>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <tr>
                                <td colspan="4" class="text-center">No items found for this order.</td>
                            </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                            <td>$0.00</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total (USD):</strong></td>
                            <td><strong>$<?php echo number_format($order['order_amount'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total (GHS):</strong></td>
                            <td><strong>GH₵<?php echo number_format($ghs_amount, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($order['order_status'] == 'Shipped' || $order['order_status'] == 'Processing'): ?>
                <div class="mt-4 text-center">
                    <h5>Tracking Information</h5>
                    <div class="progress" style="height: 30px;">
                        <?php 
                        $progress = 0;
                        switch ($order['order_status']) {
                            case 'Pending': $progress = 10; break;
                            case 'Processing': $progress = 35; break; 
                            case 'Shipped': $progress = 65; break;
                            case 'Delivered': $progress = 90; break;
                            case 'Completed': $progress = 100; break;
                        }
                        ?>
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" 
                            aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $progress; ?>%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span>Order Placed</span>
                        <span>Processing</span>
                        <span>Shipped</span>
                        <span>Delivered</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <p>Thank you for shopping with GG-LUGX. If you have any questions regarding this order, please contact our customer support.</p>
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