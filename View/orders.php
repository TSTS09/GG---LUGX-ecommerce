<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php?redirect=orders");
    exit;
}

// Get customer ID from session
$customer_id = $_SESSION['customer_id'];

// Include order controller
require_once("../Controllers/order_controller.php");
require_once("../Controllers/cart_controller.php");
$order_controller = new OrderController();
$cart_controller = new CartController();

// Get customer orders
$orders = $order_controller->get_customer_orders_ctr($customer_id);

$page_title = "My Orders - Track Your Purchases";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="stylesheet" href="../CSS/orders_cust.css">
    <link rel="icon" href="../Images/logo.png" type="image/png">

    <title><?php echo $page_title; ?></title>

    <style>
        .order-details {
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }

        .product-image-small {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }

        .view-details-btn {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Header Area Start -->
    <?php
    // Include the header
    include_once('../includes/header.php');
    ?>
    <!-- Header Area End -->

    <!-- Main Content Container -->
    <div class="main-content">
        <div class="order-container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="mb-4">My Orders</h2>
                    <p>Track your purchase history and order status below.</p>
                </div>
            </div>

            <?php if ($orders['success'] && !empty($orders['data'])): ?>
                <div class="row">
                    <?php foreach ($orders['data'] as $order): ?>
                        <div class="col-lg-12">
                            <div class="order-card">
                                <div class="order-header">
                                    <div>
                                        <h5>Order #<?php echo $order['order_id']; ?></h5>
                                        <span class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
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

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>Invoice Number:</strong> <?php echo $order['invoice_no']; ?></p>
                                        <p><strong>Total Amount:</strong> $<?php echo number_format($order['order_amount'], 2); ?></p>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> View Details
                                        </a>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-secondary view-details-btn" data-order="<?php echo $order['order_id']; ?>">
                                            <i class="fa fa-list"></i> Quick View
                                        </a>
                                        <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-print" target="_blank">
                                            <i class="fa fa-print"></i> Print Invoice
                                        </a>
                                    </div>
                                </div>

                                <!-- Order Details Section (hidden by default) -->
                                <div class="order-details" id="details-<?php echo $order['order_id']; ?>">
                                    <h6>Order Items</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Use cart controller to get order items for better compatibility
                                                $order_items = $cart_controller->get_order_items_ctr($order['order_id']);
                                                if ($order_items['success'] && !empty($order_items['data'])):
                                                    foreach ($order_items['data'] as $item):
                                                ?>
                                                        <tr <?php echo isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1 ? 'class="table-secondary"' : ''; ?>>
                                                            <td>
                                                                <?php if (!empty($item['product_image'])): ?>
                                                                    <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" class="product-image-small">
                                                                <?php endif; ?>
                                                                <?php echo $item['product_title']; ?>
                                                                <?php if (isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1): ?>
                                                                    <span class="badge badge-secondary">Unavailable</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo $item['qty']; ?></td>
                                                            <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                                            <td>$<?php echo number_format($item['product_price'] * $item['qty'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4">No items found for this order.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                                    <td><strong>$<?php echo number_format($order['order_amount'], 2); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="no-orders">
                            <i class="fa fa-shopping-bag"></i>
                            <h3>You don't have any orders yet</h3>
                            <p>Looks like you haven't made any purchases. Start shopping to see your orders here!</p>
                            <a href="all_product.php" class="btn-shop-now">Shop Now</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer Start -->
    <?php
    // Include the footer
    include_once('../includes/footer.php');
    ?>
    <!-- Footer End -->

    <!-- Scripts -->
    <script src="../JS/jquery/jquery.min.js"></script>
    <script src="../JS/bootstrap/js/bootstrap.min.js"></script>
    <script src="../JS/isotope.min.js"></script>
    <script src="../JS/owl-carousel.js"></script>
    <script src="../JS/counter.js"></script>
    <script src="../JS/custom.js"></script>

    <script>
        // View Details Button Functionality
        $(document).ready(function() {
            $('.view-details-btn').on('click', function() {
                const orderId = $(this).data('order');
                $('#details-' + orderId).slideToggle();
            });
        });
    </script>
</body>

</html>