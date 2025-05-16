<?php
session_start();
require_once("../Setting/core.php");
require_once("../Setting/db_class.php");

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$order_id = (int)$_GET['id'];

// Create database connection directly
$db = new db_connection();
$conn = $db->db_conn();

// Get order details without joining customer table
$sql = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_data = $result->fetch_assoc();

// If order not found, redirect to index
if (!$order_data) {
    header("Location: ../index.php");
    exit;
}

// For security check if the user is logged in and the order belongs to them
if (is_logged_in() && $order_data['customer_id'] !== null) {
    $customer_id = $_SESSION['customer_id'];
    if ($order_data['customer_id'] != $customer_id) {
        // This order doesn't belong to the logged-in user
        header("Location: ../index.php");
        exit;
    }
}

// Get customer info if customer_id exists
$customer = null;
if ($order_data['customer_id']) {
    $cust_sql = "SELECT * FROM customer WHERE customer_id = ?";
    $cust_stmt = $conn->prepare($cust_sql);
    $cust_stmt->bind_param("i", $order_data['customer_id']);
    $cust_stmt->execute();
    $cust_result = $cust_stmt->get_result();
    $customer = $cust_result->fetch_assoc();
}

// Get order items
$items_sql = "SELECT od.*, p.product_title, p.product_price, p.product_image, 
               (od.qty * p.product_price) as item_total,
               p.deleted as is_product_deleted
              FROM orderdetails od 
              LEFT JOIN products p ON od.product_id = p.product_id 
              WHERE od.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}

// Get payment info
$payment_sql = "SELECT * FROM payment WHERE order_id = ? LIMIT 1";
$payment_stmt = $conn->prepare($payment_sql);
$payment_stmt->bind_param("i", $order_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$payment_info = $payment_result->fetch_assoc();

// Get exchange rate from the payment info or use a default
$exchange_rate = isset($payment_info['exchange_rate']) ? $payment_info['exchange_rate'] : 12.5;

// Calculate GHS amount if available
$usd_amount = $order_data['order_amount'];
$ghs_amount = isset($payment_info['ghs_amount']) ? $payment_info['ghs_amount'] : ($usd_amount * $exchange_rate);
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
    <div class="container mb-4 no-print">
        <div class="row">
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
                    <?php if ($customer): ?>
                        <div><strong>Name:</strong> <?php echo htmlspecialchars($customer['customer_name']); ?></div>
                        <div><strong>Email:</strong> <?php echo htmlspecialchars($customer['customer_email']); ?></div>
                        <div><strong>Contact:</strong> <?php echo htmlspecialchars($customer['customer_contact']); ?></div>
                        <div><strong>Address:</strong> <?php echo htmlspecialchars($customer['customer_city'] . ', ' . $customer['customer_country']); ?></div>
                    <?php elseif (isset($order_data['guest_email']) && !empty($order_data['guest_email'])): ?>
                        <div><strong>Name:</strong> <?php echo htmlspecialchars($order_data['guest_name'] ?? 'Guest User'); ?></div>
                        <div><strong>Email:</strong> <?php echo htmlspecialchars($order_data['guest_email']); ?></div>
                        <?php if (isset($order_data['guest_phone']) && !empty($order_data['guest_phone'])): ?>
                            <div><strong>Contact:</strong> <?php echo htmlspecialchars($order_data['guest_phone']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($order_data['guest_address']) && !empty($order_data['guest_address'])): ?>
                            <div><strong>Address:</strong> <?php echo htmlspecialchars($order_data['guest_address']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div><strong>Guest Order</strong></div>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6 text-right">
                    <div class="invoice-details-title">Invoice Details</div>
                    <div><strong>Invoice Number:</strong> <?php echo $order_data['invoice_no']; ?></div>
                    <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order_data['order_date'])); ?></div>
                    <div><strong>Status:</strong> <?php echo $order_data['order_status']; ?></div>
                    <?php if (isset($order_data['reference']) && !empty($order_data['reference'])): ?>
                        <div><strong>Reference:</strong> <?php echo $order_data['reference']; ?></div>
                    <?php endif; ?>
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
                if (!empty($order_items)):
                    foreach ($order_items as $item):
                        $item_total = $item['product_price'] * $item['qty'];
                        $subtotal += $item_total;
                ?>
                        <tr <?php echo isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1 ? 'style="background-color: #f2f2f2;"' : ''; ?>>
                            <td>
                                <?php echo $item['product_title']; ?>
                                <?php if (isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1): ?>
                                    <span style="font-size: 0.8em; color: #666; display: block;">(Product no longer available)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['qty']; ?></td>
                            <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                            <td class="text-right">$<?php echo number_format($item_total, 2); ?></td>
                        </tr>
                    <?php
                    endforeach;
                else:
                    ?>
                    <tr>
                        <td colspan="4">No items found for this order.</td>
                    </tr>
                <?php
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
                <?php if ($payment_info): ?>
                    <div><strong>Payment Method:</strong> <?php echo $payment_info['payment_method'] ?? 'Online Payment'; ?></div>
                    <div><strong>Transaction ID:</strong> <?php echo isset($payment_info['transaction_id']) ? $payment_info['transaction_id'] : '-'; ?></div>
                    <div><strong>Payment Date:</strong> <?php echo date('F j, Y', strtotime($payment_info['payment_date'])); ?></div>
                <?php else: ?>
                    <div><strong>Payment Method:</strong> Online Payment</div>
                <?php endif; ?>
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