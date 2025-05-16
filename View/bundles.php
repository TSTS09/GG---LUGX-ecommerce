<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/bundle_controller.php");

// Create bundle controller instance
$bundle_controller = new BundleController();

// Get all bundles
$bundles = $bundle_controller->get_all_bundles_ctr();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Bundles - GG - LUGX</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" href="../Images/logo.png" type="image/png">
    <link rel="stylesheet" href="../CSS/user_bundles.css">
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="bundle-container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="mb-4">Game Bundles</h2>
                <p class="mb-5">Save big with our carefully curated game bundles! Get multiple games at a discounted price.</p>

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

        <?php if ($bundles['success'] && !empty($bundles['data'])): ?>
            <div class="row">
                <?php foreach ($bundles['data'] as $bundle): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="bundle-card">
                            <img src="<?php echo $bundle['product_image']; ?>" alt="<?php echo $bundle['product_title']; ?>" class="bundle-image">

                            <div class="bundle-body">
                                <h3><?php echo $bundle['product_title']; ?></h3>
                                <p><?php echo $bundle['product_desc']; ?></p>

                                <div class="bundle-price">
                                    $<?php echo number_format($bundle['product_price'], 2); ?>
                                </div>

                                <?php if (!empty($bundle['items'])): ?>
                                    <div class="bundle-items">
                                        <h5>Bundle Includes:</h5>

                                        <?php
                                        $total_original = 0;
                                        $unavailable_items = [];

                                        foreach ($bundle['items'] as $item):
                                            // Check if product is available
                                            $is_unavailable = isset($item['is_product_deleted']) && $item['is_product_deleted'] == 1;

                                            // Only add to total price if product is available
                                            if (!$is_unavailable) {
                                                $total_original += $item['product_price'] * ($item['quantity'] ?? 1);
                                            } else {
                                                $unavailable_items[] = $item;
                                            }
                                        ?>
                                            <div class="bundle-item <?php echo $is_unavailable ? 'unavailable-item' : ''; ?>">
                                                <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" <?php echo $is_unavailable ? 'style="opacity: 0.5;"' : ''; ?>>
                                                <div>
                                                    <h6><?php echo $item['product_title']; ?></h6>
                                                    <?php if ($is_unavailable): ?>
                                                        <span class="unavailable-badge">No longer available</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="bundle-item-price">
                                                    $<?php echo number_format($item['product_price'], 2); ?>
                                                    <?php if (isset($item['quantity']) && $item['quantity'] > 1): ?>
                                                        <span class="quantity-badge">×<?php echo $item['quantity']; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if (!empty($unavailable_items)): ?>
                                            <div class="bundle-unavailable-notice">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                <?php echo count($unavailable_items); ?> product(s) in this bundle are no longer available.
                                                Bundle price has been adjusted accordingly.
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        // Adjusted savings calculation based on available products only
                                        $savings = $total_original - $bundle['product_price'];
                                        $savings_percent = ($total_original > 0) ? ($savings / $total_original) * 100 : 0;
                                        ?>

                                        <div class="bundle-savings">
                                            Save $<?php echo number_format($savings, 2); ?> (<?php echo round($savings_percent); ?>%)
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($item['quantity'] > 1): ?>
                                    <span class="badge badge-info"><?php echo $item['quantity']; ?>×</span>
                                <?php endif; ?>
                                <div class="mt-4">
                                    <a href="../Actions/add_to_cart.php?id=<?php echo $bundle['product_id']; ?>" class="btn btn-primary btn-lg">
                                        <i class="fa fa-shopping-cart"></i> Add Bundle to Cart
                                    </a>

                                    <?php if (is_logged_in() || !isset($bundle['is_preorder']) || $bundle['is_preorder'] != 1): ?>
                                        <a href="../Actions/add_to_wishlist.php?id=<?php echo $bundle['product_id']; ?>" class="btn btn-outline-secondary ml-2">
                                            <i class="fa fa-heart"></i> Add to Wishlist
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <h3>No bundles available at the moment</h3>
                <p>Check back soon for exciting game bundles!</p>
                <a href="all_product.php" class="btn btn-primary mt-3">
                    <i class="fa fa-gamepad"></i> Browse Individual Games
                </a>
            </div>
        <?php endif; ?>
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