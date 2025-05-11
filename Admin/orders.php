<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}
require_once("../Setting/db_class.php");
require_once("../Controllers/admin_order_controller.php");
$order_controller = new SimpleOrderController();
// Get all orders
$orders = $order_controller->get_all_orders();  
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
    <link rel="stylesheet" href="../CSS/admin.css">
    
    <style>
        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-processing {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
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
                    <h4>All Orders</h4>
                    <?php if ($orders['success'] && !empty($orders['data'])): ?>
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
                                    <?php foreach ($orders['data'] as $order): ?>
                                        <tr>
                                            <td><?php echo $order['order_id']; ?></td>
                                            <td><?php echo $order['invoice_no']; ?></td>
                                            <td>
                                                ID: <?php echo $order['customer_id']; ?><br>
                                                <?php echo $order['customer_email']; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td>$<?php echo number_format($order['order_amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($order['order_status']) {
                                                    case 'Processing':
                                                        $status_class = 'status-processing';
                                                        break;
                                                    case 'Shipped':
                                                        $status_class = 'status-shipped';
                                                        break;
                                                    case 'Delivered':
                                                        $status_class = 'status-delivered';
                                                        break;
                                                    case 'Cancelled':
                                                        $status_class = 'status-cancelled';
                                                        break;
                                                }
                                                ?>
                                                <span class="order-status <?php echo $status_class; ?>">
                                                    <?php echo $order['order_status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#statusModal<?php echo $order['order_id']; ?>">
                                                    Update Status
                                                </button>
                                                <a href="../View/print_invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                    View Invoice
                                                </a>
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
                                                    <form action="orders.php" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                            
                                                            <div class="form-group">
                                                                <label for="new_status<?php echo $order['order_id']; ?>">Current Status: <span class="order-status <?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span></label>
                                                                <select class="form-control" id="new_status<?php echo $order['order_id']; ?>" name="new_status" required>
                                                                    <option value="">Select New Status</option>
                                                                    <option value="Processing" <?php echo $order['order_status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                                    <option value="Shipped" <?php echo $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                                    <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
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
                        <p>No orders found.</p>
                    <?php endif; ?>
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
</body>

</html>