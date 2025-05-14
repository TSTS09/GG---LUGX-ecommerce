<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Redirect logged in users to payment page
if (is_logged_in()) {
    header("Location: payment.php");
    exit;
}

// Check for guest session
if (!isset($_SESSION['guest_session_id'])) {
    header("Location: cart.php");
    exit;
}

$guest_id = $_SESSION['guest_session_id'];
$cart_controller = new CartController();

// Get guest cart items
$cart_items = $cart_controller->get_guest_cart_items_ctr($guest_id);
$cart_total = $cart_controller->get_guest_cart_total_ctr($guest_id);

// Check if cart is empty
if (!$cart_items['success'] || empty($cart_items['data'])) {
    header("Location: cart.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    $required = ['guest_email', 'guest_name', 'guest_phone', 'guest_address'];
    $errors = [];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('guest_', '', $field)) . ' is required';
        }
    }
    
    // Validate email format
    if (!empty($_POST['guest_email']) && !filter_var($_POST['guest_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($errors)) {
        // Save guest information to session for payment processing
        $_SESSION['guest_checkout'] = [
            'email' => $_POST['guest_email'],
            'name' => $_POST['guest_name'],
            'phone' => $_POST['guest_phone'],
            'address' => $_POST['guest_address']
        ];
        
        // Redirect to payment
        header("Location: payment.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Checkout - GG - LUGX</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/guest_checkout.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" href="../Images/logo.png" type="image/png">
    <link rel="stylesheet" href="../CSS/guest-checkout.css">
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="main-content">
        <div class="checkout-container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="mb-4">Guest Checkout</h2>
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout-form">
                        <h4>Your Information</h4>
                        <p>Please provide your details to complete your purchase. You can checkout as a guest without creating an account.</p>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="guest_email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="guest_email" name="guest_email" 
                                    value="<?php echo isset($_POST['guest_email']) ? htmlspecialchars($_POST['guest_email']) : ''; ?>" required>
                                <small class="form-text text-muted">We'll send your order confirmation to this email</small>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="guest_name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guest_name" name="guest_name" 
                                    value="<?php echo isset($_POST['guest_name']) ? htmlspecialchars($_POST['guest_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="guest_phone">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="guest_phone" name="guest_phone" 
                                    value="<?php echo isset($_POST['guest_phone']) ? htmlspecialchars($_POST['guest_phone']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="guest_address">Shipping Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="guest_address" name="guest_address" rows="3" required><?php echo isset($_POST['guest_address']) ? htmlspecialchars($_POST['guest_address']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-checkout">Continue to Payment</button>
                                <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items['data'] as $item): ?>
                                        <tr>
                                            <td>
                                                <?php echo $item['product_title']; ?>
                                            </td>
                                            <td><?php echo $item['qty']; ?></td>
                                            <td>$<?php echo number_format($item['item_total'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Subtotal:</strong></td>
                                        <td>$<?php echo number_format($cart_total, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Shipping:</strong></td>
                                        <td>$0.00</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Total:</strong></td>
                                        <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once('../includes/footer.php'); ?>

    <!-- Scripts -->
    <script src="../JS/jquery/jquery.min.js"></script>
    <script src="../JS/bootstrap/js/bootstrap.min.js"></script>
    <script src="../JS/isotope.min.js"></script>
    <script src="../JS/owl-carousel.js"></script>
    <script src="../JS/counter.js"></script>
    <script src="../JS/custom.js"></script>
</body>

</html>