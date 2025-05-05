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

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .invoice-header {
            border-bottom: 2px solid #ee626b;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        .invoice-header img {
            max-height: 60px;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #ee626b;
        }

        .invoice-details {
            margin-bottom: 40px;
        }

        .invoice-details-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .invoice-table th {
            background-color: #f5f5f5;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }

        .invoice-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .invoice-footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #777;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            font-size: 16px;
        }

        .btn-download-pdf {
            background-color: #ee626b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .btn-download-pdf:hover {
            background-color: #dc3545;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }

            .invoice-container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
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

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th class="text-right">Total</th>
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
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-right">$<?php echo number_format($order_data['order_amount'], 2); ?></td>
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
            <p>© 2025 GG-LUGX. All rights reserved.</p>
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