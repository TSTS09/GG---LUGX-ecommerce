<?php
// Place this at the top of your orders.php file in the admin panel

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../Setting/core.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

require_once("../Controllers/cart_controller.php");
require_once("../Controllers/customer_controller.php");

// Create controller instances
$cart_controller = new CartController();
$customer_controller = new CustomerController();

// Debug status updates - uncomment this when testing
// error_log("Order status update handler running");

// Handle status update if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    // Capture the form data
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';

    // Debug information
    // error_log("Update Status Form Submitted - Order ID: $order_id, New Status: $new_status");

    // Validate inputs
    if ($order_id <= 0) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Invalid order ID'
        ];
    } else if (empty($new_status)) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Please select a status'
        ];
    } else {
        // Validate the status value
        $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Completed', 'Cancelled'];
        if (!in_array($new_status, $valid_statuses)) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Invalid status selected'
            ];
        } else {
            // Try to update the status
            $result = $cart_controller->update_order_status_ctr($order_id, $new_status);

            if ($result) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => "Order #$order_id status updated to $new_status successfully"
                ];
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Failed to update order status. Please try again.'
                ];
            }
        }
    }

    // Redirect to avoid form resubmission - maintain the current tab
    $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
    header("Location: orders.php?tab=" . $current_tab);
    exit;
}

// The rest of your orders.php code follows...
?>

// Get all orders for admin
$all_orders = $cart_controller->get_all_orders_admin_ctr();
$completed_orders = $cart_controller->get_orders_by_status_ctr('Completed');
$pending_orders = $cart_controller->get_orders_by_status_ctr('Pending');
$processing_orders = $cart_controller->get_orders_by_status_ctr('Processing');
$shipped_orders = $cart_controller->get_orders_by_status_ctr('Shipped');
$delivered_orders = $cart_controller->get_orders_by_status_ctr('Delivered');
$cancelled_orders = $cart_controller->get_orders_by_status_ctr('Cancelled');

// Get current tab from GET or default to 'all'
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Panel</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/admin-styles.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="icon" href="../Images/logo.png" type="image/png">
    <link rel="stylesheet" href="../CSS/admin-orders.css">
</head>

<body>
    <!-- Header -->
    <?php
    // Include the header
    include_once('../includes/header.php');
    ?>

    <div class="admin-container">
        <div class="row">
            <div class="col-lg-12">
                <h2>Order Management</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message']['type'] === 'error' ? 'danger' : $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']['text']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="orders-table">
                    <h4>Order Management</h4>

                    <!-- Export Orders Button -->
                    <div class="mb-3">
                        <a href="../View/export_all_orders.php?tab=<?php echo $current_tab; ?>" class="btn btn-primary" target="_blank">
                            <i class="fa fa-file-pdf-o"></i> Export <?php echo ucfirst($current_tab); ?> Orders to PDF
                        </a>
                    </div>

                    <!-- Navigation tabs -->
                    <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab === 'all' ? 'active' : ''; ?>" href="?tab=all">
                                All Orders <span class="badge badge-pill"><?php echo count($all_orders); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab === 'completed' ? 'active' : ''; ?>" href="?tab=completed">
                                Completed <span class="badge badge-pill"><?php echo count($completed_orders); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab === 'pending' ? 'active' : ''; ?>" href="?tab=pending">
                                Pending <span class="badge badge-pill"><?php echo count($pending_orders); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab === 'processing' ? 'active' : ''; ?>" href="?tab=processing">
                                Processing <span class="badge badge-pill"><?php echo count($processing_orders); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab === 'shipped' ? 'active' : ''; ?>" href="?tab=shipped">
                                Shipped <span class="badge badge-pill"><?php echo count($shipped_orders); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab === 'delivered' ? 'active' : ''; ?>" href="?tab=delivered">
                                Delivered <span class="badge badge-pill"><?php echo count($delivered_orders); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab === 'cancelled' ? 'active' : ''; ?>" href="?tab=cancelled">
                                Cancelled <span class="badge badge-pill"><?php echo count($cancelled_orders); ?></span>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content" id="orderTabsContent">
                        <!-- Display orders based on selected tab -->
                        <?php
                        // Determine which orders to display
                        $orders_to_display = [];
                        switch ($current_tab) {
                            case 'completed':
                                $orders_to_display = $completed_orders;
                                break;
                            case 'pending':
                                $orders_to_display = $pending_orders;
                                break;
                            case 'processing':
                                $orders_to_display = $processing_orders;
                                break;
                            case 'shipped':
                                $orders_to_display = $shipped_orders;
                                break;
                            case 'delivered':
                                $orders_to_display = $delivered_orders;
                                break;
                            case 'cancelled':
                                $orders_to_display = $cancelled_orders;
                                break;
                            default:
                                $orders_to_display = $all_orders;
                                break;
                        }

                        if (!empty($orders_to_display)):
                        ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Invoice No</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders_to_display as $order): ?>
                                            <?php
                                            // Get customer info
                                            $customer = $customer_controller->get_one_customer_ctr($order['customer_id']);

                                            // Get payment info
                                            $payment_info = $cart_controller->get_payment_info_ctr($order['order_id']);

                                            // Determine status class
                                            $status_class = '';
                                            switch ($order['order_status']) {
                                                case 'Pending':
                                                    $status_class = 'status-pending';
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
                                                case 'Completed':
                                                    $status_class = 'status-completed';
                                                    break;
                                                case 'Cancelled':
                                                    $status_class = 'status-cancelled';
                                                    break;
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo $order['order_id']; ?></td>
                                                <td><?php echo $order['invoice_no']; ?></td>
                                                <td>
                                                    <?php if ($customer): ?>
                                                        <?php echo htmlspecialchars($customer['customer_name']); ?><br>
                                                        <small><?php echo htmlspecialchars($customer['customer_email']); ?></small>
                                                    <?php else: ?>
                                                        ID: <?php echo $order['customer_id']; ?><br>
                                                        <small class="text-muted">Customer details not found</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                <td>
                                                    $<?php echo number_format($order['order_amount'], 2); ?>
                                                    <?php if (isset($payment_info) && !empty($payment_info) && isset($payment_info['ghs_amount']) && $payment_info['ghs_amount'] > 0): ?>
                                                        <br><small class="text-muted">(GH₵<?php echo number_format($payment_info['ghs_amount'], 2); ?>)</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="order-status <?php echo $status_class; ?>">
                                                        <?php echo $order['order_status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#statusModal<?php echo $order['order_id']; ?>">
                                                        Update Status
                                                    </button>
                                                    <button class="btn btn-sm btn-info btn-view-details" data-order-id="<?php echo $order['order_id']; ?>">
                                                        View Details
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Order Details Row -->
                                            <tr class="order-details-row" id="orderDetails<?php echo $order['order_id']; ?>">
                                                <td colspan="7">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5 class="mb-0">Order Details #<?php echo $order['order_id']; ?></h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h6>Order Information</h6>
                                                                    <p><strong>Invoice:</strong> <?php echo $order['invoice_no']; ?></p>
                                                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                                                    <p><strong>Status:</strong> <span class="order-status <?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span></p>
                                                                    <?php if (isset($order['reference']) && !empty($order['reference'])): ?>
                                                                        <p><strong>Reference:</strong> <?php echo $order['reference']; ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>Customer Information</h6>
                                                                    <?php if ($customer): ?>
                                                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['customer_name']); ?></p>
                                                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['customer_email']); ?></p>
                                                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['customer_contact']); ?></p>
                                                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($customer['customer_city'] . ', ' . $customer['customer_country']); ?></p>
                                                                    <?php else: ?>
                                                                        <p>Customer details not available</p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <!-- Payment Information -->
                                                            <?php if (isset($payment_info) && !empty($payment_info)): ?>
                                                                <div class="payment-info mt-3">
                                                                    <h6>Payment Information</h6>
                                                                    <p><strong>Amount:</strong> $<?php echo number_format($payment_info['amt'], 2); ?> <?php echo $payment_info['currency']; ?></p>
                                                                    <?php if (isset($payment_info['ghs_amount']) && $payment_info['ghs_amount'] > 0): ?>
                                                                        <p><strong>Amount (GHS):</strong> GH₵<?php echo number_format($payment_info['ghs_amount'], 2); ?></p>
                                                                    <?php endif; ?>
                                                                    <?php if (isset($payment_info['exchange_rate']) && $payment_info['exchange_rate'] > 0): ?>
                                                                        <p class="currency-info"><small>Exchange Rate: 1 USD = <?php echo number_format($payment_info['exchange_rate'], 2); ?> GHS</small></p>
                                                                    <?php endif; ?>
                                                                    <p><strong>Payment Date:</strong> <?php echo date('F j, Y', strtotime($payment_info['payment_date'])); ?></p>
                                                                </div>
                                                            <?php endif; ?>

                                                            <!-- Order Items -->
                                                            <div class="mt-3">
                                                                <h6>Order Items</h6>
                                                                <?php
                                                                $order_items = $cart_controller->get_order_items_ctr($order['order_id']);
                                                                if ($order_items['success'] && !empty($order_items['data'])):
                                                                ?>
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
                                                                                <?php foreach ($order_items['data'] as $item): ?>
                                                                                    <tr>
                                                                                        <td>
                                                                                            <?php if (!empty($item['product_image'])): ?>
                                                                                                <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                                                                            <?php endif; ?>
                                                                                            <?php echo $item['product_title']; ?>
                                                                                        </td>
                                                                                        <td><?php echo $item['qty']; ?></td>
                                                                                        <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                                                                        <td>$<?php echo number_format($item['product_price'] * $item['qty'], 2); ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                            <tfoot>
                                                                                <tr>
                                                                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                                                                    <td><strong>$<?php echo number_format($order['order_amount'], 2); ?></strong></td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <p>No items found for this order.</p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Update Status Modal -->
                                            <div class="modal fade" id="statusModal<?php echo $order['order_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel<?php echo $order['order_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="statusModalLabel<?php echo $order['order_id']; ?>">Update Order Status</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>

                                                        <!-- Leave action empty to post to the current page -->
                                                        <form action="" method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">

                                                                <div class="form-group">
                                                                    <label for="new_status<?php echo $order['order_id']; ?>">Current Status: <span class="order-status <?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span></label>
                                                                    <select class="form-control" id="new_status<?php echo $order['order_id']; ?>" name="new_status" required>
                                                                        <option value="">Select New Status</option>
                                                                        <option value="Pending" <?php echo $order['order_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="Processing" <?php echo $order['order_status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                                        <option value="Shipped" <?php echo $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                                        <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                                        <option value="Completed" <?php echo $order['order_status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                                        <option value="Cancelled" <?php echo $order['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No orders found in this category.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php
    // Include the footer
    include_once('../includes/footer.php');
    ?>

    <!-- Scripts -->
    <script src="../JS/jquery/jquery.min.js"></script>
    <script src="../JS/bootstrap/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Hide all order details rows initially
            $('.order-details-row').hide();

            // Toggle order details
            $('.btn-view-details').on('click', function() {
                const orderId = $(this).data('order-id');
                $('#orderDetails' + orderId).toggle();
            });
        });
    </script>
</body>

</html>