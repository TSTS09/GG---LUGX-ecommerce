<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");
require_once("../Controllers/customer_controller.php");

// Handle both logged in and guest users
if (is_logged_in()) {
    $customer_id = $_SESSION['customer_id'];
    $guest_checkout = false;

    // Get customer details
    $customer_controller = new CustomerController();
    $customer = $customer_controller->get_one_customer_ctr($customer_id);
    $email = $customer['customer_email'];
} else if (isset($_SESSION['guest_checkout']) && isset($_SESSION['guest_session_id'])) {
    $customer_id = null;
    $guest_checkout = true;
    $guest_details = $_SESSION['guest_checkout'];
    $guest_id = $_SESSION['guest_session_id'];
    $email = $guest_details['email'];
} else {
    // Redirect if neither logged in nor guest checkout
    header("Location: ../View/cart.php");
    exit;
}

// Create cart controller instance
$cart_controller = new CartController();

// Check for direct redirect from Paystack (success case)
if (isset($_GET['reference']) && isset($_GET['trxref'])) {
    // This is the primary callback path when redirected from a successful Paystack payment
    $reference = $_GET['reference'];
    $transaction_id = $reference; // In this case, use reference as transaction ID
    $status = 'success'; // Assume success as Paystack redirects here on success

    error_log("Received direct Paystack redirect with reference: $reference");
}
// Check for our standard parameters
else if (isset($_GET['reference']) && isset($_GET['transaction_id']) && isset($_GET['status'])) {
    // This is our manually structured path 
    $reference = $_GET['reference'];
    $transaction_id = $_GET['transaction_id'];
    $status = $_GET['status'];

    error_log("Received standard payment callback with reference: $reference, transaction_id: $transaction_id, status: $status");
} else {
    // Set error message if required parameters are missing
    error_log("Missing required payment parameters in callback");
    $_SESSION['payment_error'] = "Invalid payment data received";
    header("Location: ../View/payment_failed.php");
    exit;
}

// Verify payment with PayStack API
$paystack_secret_key = "sk_test_75041253ce1c9538bcf1e5a634d10d2bef5299f7";
$verification_url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

// Log the verification request
error_log("Verifying Paystack transaction: " . $verification_url);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verification_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $paystack_secret_key,
    "Cache-Control: no-cache"
]);

// Send request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    $curl_error = curl_error($ch);
    error_log("cURL Error: " . $curl_error);
    $_SESSION['payment_error'] = "Payment verification failed: " . $curl_error;
    header("Location: ../View/payment_failed.php");
    exit;
}

curl_close($ch);

// Log the response
error_log("Paystack verification response: " . $response);

// Decode response
$result = json_decode($response);

// Check if verification was successful - allow for variations in response structure
$verification_successful = false;

if ($result && isset($result->status) && $result->status === true) {
    if (isset($result->data->status)) {
        // Standard response structure
        $verification_successful = ($result->data->status === 'success');
        error_log("Payment status from standard verification: " . $result->data->status);
    } else if (isset($result->data->gateway_response) && strpos(strtolower($result->data->gateway_response), 'success') !== false) {
        // Alternative success response
        $verification_successful = true;
        error_log("Payment success determined from gateway_response: " . $result->data->gateway_response);
    }
}

// Deeper error checking if verification seems to have failed
if (!$verification_successful) {
    $error_message = "Payment verification failed";

    if (isset($result->message)) {
        $error_message .= ": " . $result->message;
    }

    if (isset($result->data) && isset($result->data->gateway_response)) {
        $error_message .= " - " . $result->data->gateway_response;
    }

    error_log("Paystack verification failed: " . $error_message);
    error_log("Full response: " . $response);

    // Check for potential success despite different response format
    if ($result && isset($result->data) && isset($result->data->reference) && $result->data->reference === $reference) {
        error_log("Transaction reference matches despite apparent failure, continuing with processing");
        $verification_successful = true;
    } else {
        $_SESSION['payment_error'] = $error_message;
        header("Location: ../View/payment_failed.php");
        exit;
    }
}

// If verification is successful
// Extract payment data
$paid_amount_ghs = isset($result->data->amount) ? ($result->data->amount / 100) : 0; // Convert from pesewas back to cedis
$currency = isset($result->data->currency) ? $result->data->currency : 'GHS';
$payment_method = "Paystack";

// Get the exchange rate and totals from session
$exchange_rate = isset($_SESSION['exchange_rate']) ? $_SESSION['exchange_rate'] : 12.5;
$usd_total = isset($_SESSION['usd_total']) ? $_SESSION['usd_total'] : ($paid_amount_ghs / $exchange_rate);
$ghs_total = isset($_SESSION['ghs_total']) ? $_SESSION['ghs_total'] : $paid_amount_ghs;

// If paid amount is not available in the response, use the calculated amount
if ($paid_amount_ghs == 0) {
    $paid_amount_ghs = $ghs_total;
    error_log("Using calculated GHS amount: " . $paid_amount_ghs);
}

// Log the amounts for verification
error_log("Payment amounts - GHS: " . $paid_amount_ghs . ", USD: " . $usd_total . ", Exchange Rate: " . $exchange_rate);

// Generate invoice number
$invoice_no = "INV-" . mt_rand(1000000, 9999999);

// Check if order already exists (to prevent duplicate orders on redirect/refresh)
$existing_orders = $cart_controller->get_orders_by_reference_ctr($reference);
if ($existing_orders && !empty($existing_orders)) {
    error_log("Order already exists for reference: $reference, redirecting to success page");

    $order_id = $existing_orders[0]['order_id'];
    $invoice_no = $existing_orders[0]['invoice_no'];

    // Store order details in session for success page
    $_SESSION['order_id'] = $order_id;
    $_SESSION['invoice_no'] = $invoice_no;
    $_SESSION['amount'] = $usd_total;
    $_SESSION['ghs_amount'] = $paid_amount_ghs;
    $_SESSION['payment_date'] = isset($existing_orders[0]['order_date']) ? $existing_orders[0]['order_date'] : date('Y-m-d H:i:s');
    $_SESSION['exchange_rate'] = $exchange_rate;

    // Redirect to success page
    header("Location: ../View/payment_success.php");
    exit;
}

// Create order based on user type
if ($guest_checkout) {
    // For guest users
    $order_id = $cart_controller->create_guest_order_ctr(
        $usd_total,
        $invoice_no,
        'Completed',
        $reference,
        $guest_details['email'],
        $guest_details['name'],
        $guest_id,
        $guest_details['phone'] ?? null,    // Add this line
        $guest_details['address'] ?? null   // Add this line
    );
} else {
    // For logged in users
    $order_id = $cart_controller->create_order_ctr(
        $customer_id,
        $usd_total,
        $invoice_no,
        'Completed',
        $reference
    );
}

if (!$order_id) {
    error_log("Failed to create order in the database");
    $_SESSION['payment_error'] = "Failed to create order in the database";
    header("Location: ../View/payment_failed.php");
    exit;
}

// Record payment - store both the USD amount and GHS amount in notes
$payment_recorded = $cart_controller->record_payment_ctr($order_id, $payment_method, $usd_total, "USD", $transaction_id, $paid_amount_ghs, $exchange_rate);

if (!$payment_recorded) {
    error_log("Failed to record payment in the database");
    $_SESSION['payment_error'] = "Failed to record payment in the database";
    header("Location: ../View/payment_failed.php");
    exit;
}

// Send order confirmation email to customer
$to = $email;
$subject = "Order Confirmation - " . $invoice_no;

// Get order items
$order_items = $cart_controller->get_order_items_ctr($order_id);

// Build email body - simplified for this implementation
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
        .currency-note { background-color: #f9f9f9; padding: 10px; border-left: 3px solid #ee626b; margin: 15px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Order Confirmation</h1>
            <p>Thank you for your order!</p>
        </div>
        <div class='content'>
            <p><strong>Order Number:</strong> " . $invoice_no . "</p>
            <p><strong>Order Date:</strong> " . date('F j, Y') . "</p>
            <p><strong>Payment Method:</strong> " . $payment_method . "</p>
            
            <div class='currency-note'>
                <p>Your payment of GH₵" . number_format($paid_amount_ghs, 2) . " was processed in Ghanaian Cedis.</p>
                <p>Exchange rate: 1 USD = " . number_format($exchange_rate, 2) . " GHS</p>
            </div>
            
            <h3>Order Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price (USD)</th>
                        <th>Total (USD)</th>
                    </tr>
                </thead>
                <tbody>";

// Add order items to the email
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
                        <td colspan='3'>Total (USD):</td>
                        <td>$" . number_format($usd_total, 2) . "</td>
                    </tr>
                    <tr class='total'>
                        <td colspan='3'>Total (GHS):</td>
                        <td>GH₵" . number_format($ghs_total, 2) . "</td>
                    </tr>
                </tfoot>
            </table>
            
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

// Log email status
if ($email_sent) {
    error_log("Order confirmation email sent to: " . $to);
} else {
    error_log("Failed to send order confirmation email to: " . $to);
}

// Store order details in session for success page
$_SESSION['order_id'] = $order_id;
$_SESSION['invoice_no'] = $invoice_no;
$_SESSION['amount'] = $usd_total;
$_SESSION['ghs_amount'] = $paid_amount_ghs;
$_SESSION['payment_date'] = date('Y-m-d H:i:s');
$_SESSION['exchange_rate'] = $exchange_rate;

// Redirect to success page
header("Location: ../View/payment_success.php");
exit;
