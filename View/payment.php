<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");
require_once("../Controllers/customer_controller.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php?redirect=payment");
    exit;
}

// Get customer ID from session
$customer_id = $_SESSION['customer_id'];

// Create cart controller instance
$cart_controller = new CartController();
$customer_controller = new CustomerController();

// Get cart items
$cart_items = $cart_controller->get_cart_items_ctr($customer_id);

// Get cart total
$cart_total = $cart_controller->get_cart_total_ctr($customer_id);

// Get customer details
$customer = $customer_controller->get_one_customer_ctr($customer_id);

// Check if cart is empty
if (!$cart_items['success'] || empty($cart_items['data'])) {
    header("Location: cart.php");
    exit;
}

// Initialize PayStack integration
$paystack_public_key = "pk_test_942e4174c8bdc335aed436d07ba8c9ee1eda6831"; 
$email = $customer['customer_email'];
$reference = 'ORD_' . time() . '_' . mt_rand(1000, 9999);

// Currency conversion from USD to GHS
// First, try to get the current exchange rate from an API
function get_exchange_rate() {
    // Default exchange rate in case API fails (1 USD = 12.5 GHS as an example)
    $default_rate = 12.5;
    
    try {
        // Try with ExchangeRate-API (free tier)
        $api_url = "https://open.er-api.com/v6/latest/USD";
        $response = @file_get_contents($api_url);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['rates']['GHS'])) {
                error_log("Exchange rate from API: 1 USD = " . $data['rates']['GHS'] . " GHS");
                return $data['rates']['GHS'];
            }
        }
        
        // Try with alternative API if first one fails
        $backup_api = "https://api.exchangerate.host/latest?base=USD&symbols=GHS";
        $backup_response = @file_get_contents($backup_api);
        
        if ($backup_response !== false) {
            $backup_data = json_decode($backup_response, true);
            if (isset($backup_data['rates']['GHS'])) {
                error_log("Exchange rate from backup API: 1 USD = " . $backup_data['rates']['GHS'] . " GHS");
                return $backup_data['rates']['GHS'];
            }
        }
        
        // If APIs fail, log the error and return default rate
        error_log("Could not get exchange rate from APIs, using default rate: 1 USD = " . $default_rate . " GHS");
        return $default_rate;
    } catch (Exception $e) {
        error_log("Error getting exchange rate: " . $e->getMessage());
        return $default_rate;
    }
}

// Get the exchange rate
$exchange_rate = get_exchange_rate();

// Convert the USD amount to GHS
$usd_total = $cart_total;
$ghs_total = $usd_total * $exchange_rate;

// Store these in session for use in process_payment.php
$_SESSION['usd_total'] = $usd_total;
$_SESSION['ghs_total'] = $ghs_total;
$_SESSION['exchange_rate'] = $exchange_rate;

// Amount in pesewas for Paystack (smallest currency unit)
$amount = round($ghs_total * 100);

// Currency should be GHS for Ghana-based Paystack accounts
$currency = "GHS";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GG - LUGX</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" href="../Images/logo.png" type="image/png">
    <link rel="stylesheet" href="../CSS/payment.css">
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="main-content">
        <div class="payment-container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="mb-4">Checkout</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price (USD)</th>
                                        <th>Total (USD)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items['data'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border-radius: 5px;">
                                                    <span><?php echo $item['product_title']; ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo $item['qty']; ?></td>
                                            <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                            <td>$<?php echo number_format($item['item_total'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Subtotal (USD):</strong></td>
                                        <td>$<?php echo number_format($usd_total, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                                        <td>$0.00</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Total (USD):</strong></td>
                                        <td><strong>$<?php echo number_format($usd_total, 2); ?></strong></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right"><strong>Total (GHS):</strong></td>
                                        <td><strong>GH₵<?php echo number_format($ghs_total, 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="currency-info">
                            <p><i class="fa fa-info-circle"></i> <strong>Currency Conversion:</strong> Your payment will be processed in Ghanaian Cedis (GHS). Current exchange rate: 1 USD = <?php echo number_format($exchange_rate, 2); ?> GHS</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="payment-details">
                        <h4>Customer Details</h4>
                        
                        <div class="mb-3">
                            <p><strong>Name:</strong> <?php echo $customer['customer_name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $customer['customer_email']; ?></p>
                            <p><strong>Contact:</strong> <?php echo $customer['customer_contact']; ?></p>
                            <p><strong>Country:</strong> <?php echo $customer['customer_country']; ?></p>
                            <p><strong>City:</strong> <?php echo $customer['customer_city']; ?></p>
                        </div>
                        
                        <div class="divider"></div>
                        
                        <div class="payment-method">
                            <h4>Payment Method</h4>
                            <p>Pay securely via PayStack</p>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-pay" onclick="payWithPaystack()">Pay Now GH₵<?php echo number_format($ghs_total, 2); ?></button>
                            </div>
                            
                            <div class="mt-3">
                                <a href="cart.php" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once('../includes/footer.php'); ?>

    <!-- PayStack Integration -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        function payWithPaystack(){
            var handler = PaystackPop.setup({
                key: '<?php echo $paystack_public_key; ?>',
                email: '<?php echo $email; ?>',
                amount: <?php echo $amount; ?>,
                currency: '<?php echo $currency; ?>',
                ref: '<?php echo $reference; ?>',
                callback: function(response){
                    // Redirect to process payment
                    window.location.href = "../Actions/process_payment.php?reference=" + response.reference + "&transaction_id=" + response.transaction + "&status=" + response.status;
                },
                onClose: function(){
                    alert('Transaction was not completed, window closed.');
                },
            });
            handler.openIframe();
        }
    </script>

    <!-- Scripts -->
    <script src="../JS/jquery/jquery.min.js"></script>
    <script src="../JS/bootstrap/js/bootstrap.min.js"></script>
    <script src="../JS/isotope.min.js"></script>
    <script src="../JS/owl-carousel.js"></script>
    <script src="../JS/counter.js"></script>
    <script src="../JS/custom.js"></script>
</body>

</html>