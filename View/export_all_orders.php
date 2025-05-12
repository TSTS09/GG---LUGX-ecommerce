<?php
session_start();
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

// Get tab parameter to determine which orders to export
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

// Get appropriate orders based on tab
switch ($tab) {
    case 'completed':
        $orders = $cart_controller->get_orders_by_status_ctr('Completed');
        $title = "Completed Orders";
        break;
    case 'pending':
        $orders = $cart_controller->get_orders_by_status_ctr('Pending');
        $title = "Pending Orders";
        break;
    case 'processing':
        $orders = $cart_controller->get_orders_by_status_ctr('Processing');
        $title = "Processing Orders";
        break;
    case 'shipped':
        $orders = $cart_controller->get_orders_by_status_ctr('Shipped');
        $title = "Shipped Orders";
        break;
    case 'delivered':
        $orders = $cart_controller->get_orders_by_status_ctr('Delivered');
        $title = "Delivered Orders";
        break;
    case 'cancelled':
        $orders = $cart_controller->get_orders_by_status_ctr('Cancelled');
        $title = "Cancelled Orders";
        break;
    default:
        $orders = $cart_controller->get_all_orders_admin_ctr();
        $title = "All Orders";
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Export</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- PDF Generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .invoice-header {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .company-info {
            text-align: right;
        }
        .order-status {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .page-break { page-break-after: always; }
        table { width: 100%; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .mt-4 { margin-top: 20px; }
        .mb-4 { margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container mb-4 no-print">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1><?php echo $title; ?></h1>
                <button id="downloadPDF" class="btn btn-primary">Download PDF</button>
                <button onclick="window.print()" class="btn btn-secondary">Print</button>
                <button onclick="window.close()" class="btn btn-dark">Close</button>
            </div>
        </div>
    </div>

    <div id="pdf-content">
        <div class="invoice-header">
            <div class="row">
                <div class="col-6">
                    <h2>GG-LUGX Gaming</h2>
                    <p>Order Report: <?php echo $title; ?></p>
                    <p>Generated: <?php echo date('F j, Y g:i a'); ?></p>
                </div>
                <div class="col-6 company-info">
                    <p>GG-LUGX Gaming Store</p>
                    <p>1 University Avenue</p>
                    <p>Berekuso, Ghana</p>
                    <p>Email: info@gg-lugx.com</p>
                </div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_orders = 0;
                $total_amount = 0;
                
                if (!empty($orders)):
                    foreach ($orders as $order):
                        // Get customer info
                        $customer = $customer_controller->get_one_customer_ctr($order['customer_id']);
                        
                        // Get payment info
                        $payment_info = $cart_controller->get_payment_info_ctr($order['order_id']);
                        
                        // Determine status class
                        $status_class = '';
                        switch ($order['order_status']) {
                            case 'Pending': $status_class = 'status-pending'; break;
                            case 'Processing': $status_class = 'status-processing'; break;
                            case 'Shipped': $status_class = 'status-shipped'; break;
                            case 'Delivered': $status_class = 'status-delivered'; break;
                            case 'Completed': $status_class = 'status-completed'; break;
                            case 'Cancelled': $status_class = 'status-cancelled'; break;
                        }
                        
                        // Track totals
                        $total_orders++;
                        $total_amount += $order['order_amount'];
                ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['invoice_no']; ?></td>
                    <td>
                        <?php if ($customer): ?>
                            <?php echo htmlspecialchars($customer['customer_name']); ?><br>
                            <small><?php echo htmlspecialchars($customer['customer_email']); ?></small>
                        <?php else: ?>
                            <small>Customer ID: <?php echo $order['customer_id']; ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td>$<?php echo number_format($order['order_amount'], 2); ?></td>
                    <td><span class="order-status <?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span></td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="6" class="text-center">No orders found</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total Orders:</strong></td>
                    <td colspan="2"><?php echo $total_orders; ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total Amount:</strong></td>
                    <td colspan="2">$<?php echo number_format($total_amount, 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-4">
            <p><strong>Summary:</strong> This report contains <?php echo $total_orders; ?> <?php echo strtolower($title); ?> with a total value of $<?php echo number_format($total_amount, 2); ?>.</p>
        </div>
    </div>

    <script>
        // PDF generation
        document.getElementById('downloadPDF').addEventListener('click', function() {
            const element = document.getElementById('pdf-content');
            const opt = {
                margin: 0.5,
                filename: '<?php echo str_replace(' ', '_', $title); ?>_<?php echo date('Y-m-d'); ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>
</html>