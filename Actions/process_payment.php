<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");
require_once("../Controllers/customer_controller.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php?redirect=cart");
    exit;
}

// Get customer ID from session
$customer_id = $_SESSION['customer_id'];

// Create controllers
$cart_controller = new CartController();
$customer_controller = new CustomerController();

// Get customer details
$customer = $customer_controller->get_one_customer_ctr($customer_id);

// Verify that required parameters are present
if (!isset($_GET['reference']) || !isset($_GET['transaction_id']) || !isset($_GET['status'])) {
    $_SESSION['payment_error'] = "Invalid payment data received";
    header("Location: ../View/payment_failed.php");
    exit;
}

// Get parameters
$reference = $_GET['reference'];
$transaction_id = $_GET['transaction_id'];
$status = $_GET['status'];

// Verify payment with PayStack API
$paystack_secret_key = "sk_test_yourtestkeyhere"; // Replace with your PayStack test secret key
$verification_url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verification_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $paystack_secret_key,
    "Cache-Control: no-cache"
]);

// Send request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    $_SESSION['payment_error'] = "Payment verification failed: " . curl_error($ch);
    header("Location: ../View/payment_failed.php");
    exit;
}

curl_close($ch);

// Decode response
$result = json_decode($response);

// Check if verification was successful
if (!$result->status || $result->data->status !== 'success') {
    $_SESSION['payment_error'] = "Payment failed or was not completed";
    header("Location: ../View/payment_failed.php");
    exit;
}

// If verification is successful
// Extract payment data
$amount = $result->data->amount / 100; // Convert from kobo back to main currency unit
$currency = $result->data->currency;
$payment_method = "Paystack"; 

// Generate invoice number
$invoice_no = "INV-" . mt_rand(1000000, 9999999);

// Create order
$order_id = $cart_controller->create_order_ctr($customer_id, $amount, $invoice_no);

if (!$order_id) {
    $_SESSION['payment_error'] = "Failed to create order in the database";
    header("Location: ../View/payment_failed.php");
    exit;
}

// Record payment
$payment_recorded = $cart_controller->record_payment_ctr($order_id, $payment_method, $amount, $currency, $transaction_id);

if (!$payment_recorded) {
    $_SESSION['payment_error'] = "Failed to record payment in the database";
    header("Location: ../View/payment_failed.php");
    exit;
}

// Send order confirmation email to customer
$to = $customer['customer_email'];
$subject = "Order Confirmation - " . $invoice_no;

// Get order items
$order_items = $cart_controller->get_order_items_ctr($order_id);

// Build email body
$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; }
        .header { background-color: #f8f8f8; padding: 20px; border-bottom: 3px solid #ee626b; }
        .content { padding: 20px; }
        .footer { background-color: #f8f8f8; padding: 20px; font-size: 12px; text-align: center; }
        h1 { color: #ee626b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f8f8; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Order Confirmation</h1>
            <p>Thank you for your order!</p>
        </div>
        <div class='content'>
            <p>Dear " . $customer['customer_name'] . ",</p>
            <p>Your order has been received and is now being processed. Here are your order details:</p>
            
            <p><strong>Order Number:</strong> " . $invoice_no . "</p>
            <p><strong>Order Date:</strong> " . date('F j, Y') . "</p>
            <p><strong>Payment Method:</strong> " . $payment_method . "</p>
            <p><strong>Transaction ID:</strong> " . $transaction_id . "</p>
            
            <h3>Order Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>";

// Add order items
if ($order_items['success'] && !empty($order_items['data'])) {
    foreach ($order_items['data'] as $item) {
        $email_body .= "
            <tr>
                <td>" . $item['product_title'] . "</td>
                <td>" . $item['qty'] . "</td>
                <td>$" . number_format($item['product_price'], 2) . "</td>
                <td>$" . number_format($item['item_total'], 2) . "</td>
            </tr>";
    }
}

$email_body .= "
                </tbody>
                <tfoot>
                    <tr class='total'>
                        <td colspan='3'>Total:</td>
                        <td>$" . number_format($amount, 2) . "</td>
                    </tr>
                </tfoot>
            </table>
            
            <p>If you have any questions, please contact our customer support.</p>
            <p>Thank you for shopping with us!</p>
        </div>
        <div class='footer'>
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; " . date('Y') . " GG-LUGX. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";

// Set email headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: GG-LUGX <noreply@gg-lugx.com>" . "\r\n";

// Attempt to send email
$email_sent = mail($to, $subject, $email_body, $headers);

// Store order details in session for success page
$_SESSION['order_id'] = $order_id;
$_SESSION['invoice_no'] = $invoice_no;
$_SESSION['amount'] = $amount;
$_SESSION['payment_date'] = date('Y-m-d H:i:s');

// Redirect to success page
header("Location: ../View/payment_success.php");
exit;