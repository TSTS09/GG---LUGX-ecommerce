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
$order_controller = new OrderController();

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
    <link rel="icon" href="../Images/logo.png" type="image/png">

    <title><?php echo $page_title; ?></title>

    <style>
        html,
        body {
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

        .header-area .main-nav .nav li a {
            color: #333333 !important;
            font-weight: 500;
        }

        .header-area .main-nav .nav li a.active {
            color: #ee626b !important;
            font-weight: 600;
        }

        .header-area {
            background-color: #f8f8f8;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .user-greeting {
            color: #333333;
            font-weight: 600;
            margin-right: 15px;
        }

        .order-container {
            padding: 30px;
            max-width: 1200px;
            margin: 100px auto;
        }

        .order-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .order-date {
            color: #6c757d;
            font-size: 14px;
        }

        .order-status {
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-processing {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-canceled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn-print {
            background-color: #ee626b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-print:hover {
            background-color: #dc3545;
            color: white;
        }

        .no-orders {
            text-align: center;
            padding: 50px 0;
        }

        .no-orders i {
            font-size: 60px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .no-orders h3 {
            margin-bottom: 20px;
        }

        .btn-shop-now {
            background-color: #ee626b;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-shop-now:hover {
            background-color: #dc3545;
            color: white;
            text-decoration: none;
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
                                            case 'Canceled':
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
                                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">View Details</a>
                                        <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-print" target="_blank">
                                            <i class="fa fa-print"></i> Print Invoice
                                        </a>
                                    </div>
                                </div>

                                <div class="order-items">
                                    <h6>Order Items</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
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
                                                $order_items = $order_controller->get_order_items_ctr($order['order_id']);
                                                if ($order_items['success'] && !empty($order_items['data'])):
                                                    foreach ($order_items['data'] as $item):
                                                ?>
                                                        <tr>
                                                            <td><?php echo $item['product_title']; ?></td>
                                                            <td><?php echo $item['qty']; ?></td>
                                                            <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                                            <td>$<?php echo number_format($item['product_price'] * $item['qty'], 2); ?></td>
                                                        </tr>
                                                    <?php
                                                    endforeach;
                                                else:
                                                    ?>
                                                    <tr>
                                                        <td colspan="4">No items found for this order.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
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
        // PDF Print Button Functionality
        document.querySelectorAll('.btn-print').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                window.open(url, '_blank');
            });
        });
    </script>
</body>

</html>