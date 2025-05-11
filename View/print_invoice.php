<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php?redirect=orders");
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = (int)$_GET['id'];
$customer_id = $_SESSION['customer_id'];

// Include order controller
require_once("../Controllers/order_controller.php");
$order_controller = new OrderController();

// Verify that the order belongs to this customer
$order = $order_controller->get_order_details_ctr($order_id);

if (!$order['success'] || empty($order['data']) || $order['data']['customer_id'] != $customer_id) {
    header("Location: orders.php");
    exit;
}

// Get order details
$order_data = $order['data'];

// Get order items
$order_items = $order_controller->get_order_items_ctr($order_id);

// Get exchange rate from the order or from a default
$exchange_rate = isset($_SESSION['exchange_rate']) ? $_SESSION['exchange_rate'] : 12.5;

// Calculate GHS amount if available
$usd_amount = $order_data['order_amount'];
$ghs_amount = isset($_SESSION['ghs_amount']) ? $_SESSION['ghs_amount'] : ($usd_amount * $exchange_rate);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_data['invoice_no']; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="../CSS/print_invoice.css">
</head>

<body>
    <div class="container mb-4">
        <div class="row no-print">
            <div class="col-12 text-center mb-4">
                <button id="downloadPDF" class="btn-download-pdf">Download PDF</button>
                <button onclick="window.print()" class="btn btn-secondary">Print</button>
                <button onclick="window.close()" class="btn btn-dark">Close</button>
            </div>
        </div>
    </div>

    <div class="invoice-container" id="invoice">
        <div class="invoice-header">
            <div class="row">
                <div class="col-6">
                    <img src="../Images/logo.png" alt="GG-LUGX Logo">
                </div>
                <div class="col-6 text-right">
                    <div class="invoice-title">INVOICE</div>
                    <div>#<?php echo $order_data['invoice_no']; ?></div>
                </div>
            </div>
        </div>

        <div class="invoice-details">
            <div class="row">
                <div class="col-sm-6">
                    <div class="invoice-details-title">Bill To</div>
                    <div><strong>Name:</strong> <?php echo $_SESSION['customer_name']; ?></div>
                    <div><strong>Email:</strong> <?php echo $_SESSION['customer_email']; ?></div>
                </div>
                <div class="col-sm-6 text-right">
                    <div class="invoice-details-title">Invoice Details</div>
                    <div><strong>Invoice Number:</strong> <?php echo $order_data['invoice_no']; ?></div>
                    <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order_data['order_date'])); ?></div>
                    <div><strong>Status:</strong> <?php echo $order_data['order_status']; ?></div>
                </div>
            </div>
        </div>

        <!-- Currency information section -->
        <div class="currency-info">
            <p><strong>Currency Information:</strong></p>
            <p>Payment processed in Ghanaian Cedis (GHS).</p>
            <p>Exchange rate: 1 USD = <?php echo number_format($exchange_rate, 2); ?> GHS</p>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price (USD)</th>
                    <th class="text-right">Total (USD)</th>
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
                            <td><?php echo $item['product_title']; ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                            <td class="text-right">$<?php echo number_format($item_total, 2); ?></td>
                        </tr>
                <?php
                    endforeach;
                endif;
                ?>
                <tr>
                    <td colspan="3" class="text-right">Subtotal</td>
                    <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Shipping</td>
                    <td class="text-right">$0.00</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total (USD)</td>
                    <td class="text-right">$<?php echo number_format($order_data['order_amount'], 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total (GHS)</td>
                    <td class="text-right">GH₵<?php echo number_format($ghs_amount, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-12">
                <div class="invoice-details-title">Payment Information</div>
                <div><strong>Payment Method:</strong> <?php echo $order_data['payment_method'] ?? 'Online Payment'; ?></div>
                <div><strong>Transaction ID:</strong> <?php echo $order_data['transaction_id'] ?? '-'; ?></div>
            </div>
        </div>

        <div class="invoice-footer">
            <p>Thank you for shopping with GG-LUGX. For any questions regarding this invoice, please contact customer support.</p>
            <p>© <?php echo date('Y'); ?> GG-LUGX. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.getElementById('downloadPDF').addEventListener('click', function() {
            const element = document.getElementById('invoice');
            const opt = {
                margin: 0.5,
                filename: 'Invoice-<?php echo $order_data['invoice_no']; ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>

</html>