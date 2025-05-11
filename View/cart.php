<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/cart_controller.php");

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../Login/login.php?redirect=cart");
    exit;
}

// Get customer ID from session
$customer_id = $_SESSION['customer_id'];

// Create cart controller instance
$cart_controller = new CartController();

// Get cart items
$cart_items = $cart_controller->get_cart_items_ctr($customer_id);

// Get cart total
$cart_total = $cart_controller->get_cart_total_ctr($customer_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - GG - LUGX</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="stylesheet" href="../CSS/cart.css">
    <link rel="icon" href="../Images/logo.png" type="image/png">
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="main-content">
        <div class="cart-container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="mb-4">Your Shopping Cart</h2>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message']['text']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($cart_items['success'] && !empty($cart_items['data'])): ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="cart-table">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Product</th>
                                        <th width="120">Price</th>
                                        <th width="150">Quantity</th>
                                        <th width="120">Subtotal</th>
                                        <th width="50">Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items['data'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" class="cart-img mr-3">
                                                    <a href="single_product.php?id=<?php echo $item['p_id']; ?>" class="cart-product-title">
                                                        <?php echo $item['product_title']; ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="cart-product-price">$<?php echo number_format($item['product_price'], 2); ?></td>
                                            <td>
                                                <form action="../Actions/manage_quantity_cart.php" method="POST" class="d-flex align-items-center">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['p_id']; ?>">
                                                    <input type="number" name="quantity" class="form-control cart-quantity" value="<?php echo $item['qty']; ?>" min="1" max="10">
                                                    <button type="submit" class="btn btn-update-qty ml-2">
                                                        <i class="fa fa-refresh"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="cart-product-price">$<?php echo number_format($item['item_total'], 2); ?></td>
                                            <td class="text-center">
                                                <a href="../Actions/remove_from_cart.php?id=<?php echo $item['p_id']; ?>" class="btn-remove" onclick="return confirm('Are you sure you want to remove this item?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="cart-total-section">
                            <h4 class="cart-total-title">Cart Summary</h4>

                            <div class="cart-total-row">
                                <span class="cart-total-label">Subtotal</span>
                                <span>$<?php echo number_format($cart_total, 2); ?></span>
                            </div>

                            <div class="cart-total-row">
                                <span class="cart-total-label">Shipping</span>
                                <span>Free</span>
                            </div>

                            <div class="cart-total-row">
                                <span class="cart-total-label">Grand Total</span>
                                <span class="cart-grand-total">$<?php echo number_format($cart_total, 2); ?></span>
                            </div>

                            <div class="text-center">
                                <a href="all_product.php" class="btn btn-secondary btn-continue-shopping">
                                    <i class="fa fa-arrow-left"></i> Continue Shopping
                                </a>
                                <a href="payment.php" class="btn btn-checkout">
                                    Proceed to Checkout <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="empty-cart">
                            <i class="fa fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Looks like you haven't added any items to your cart yet.</p>
                            <a href="all_product.php" class="btn btn-primary mt-3">
                                <i class="fa fa-shopping-bag"></i> Start Shopping
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
    <script src="../JS/isotope.min.js"></script>
    <script src="../JS/owl-carousel.js"></script>
    <script src="../JS/counter.js"></script>
    <script src="../JS/custom.js"></script>
</body>

</html>