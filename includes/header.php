<?php
// If session hasn't been started, start it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core file if not already included
if (!function_exists('is_logged_in')) {
    // Adjust the path based on where the file is being included from
    $core_path = '';

    // Different paths depending on the directory level
    if (strpos($_SERVER['PHP_SELF'], '/Admin/') !== false) {
        $core_path = "../Setting/core.php";
    } elseif (
        strpos($_SERVER['PHP_SELF'], '/View/') !== false ||
        strpos($_SERVER['PHP_SELF'], '/Login/') !== false ||
        strpos($_SERVER['PHP_SELF'], '/Actions/') !== false
    ) {
        $core_path = "../Setting/core.php";
    } else {
        $core_path = "Setting/core.php";
    }

    if (file_exists($core_path)) {
        require_once($core_path);
    } else {
        // Try absolute path as fallback
        $fallback_path = $_SERVER['DOCUMENT_ROOT'] . "/Setting/core.php";
        if (file_exists($fallback_path)) {
            require_once($fallback_path);
        } else {
            echo "Error: core.php file not found";
        }
    }
}


// Determine base URL for links and image paths
$base_url = '';
if (strpos($_SERVER['PHP_SELF'], '/Admin/') !== false) {
    $base_url = "../";
} elseif (
    strpos($_SERVER['PHP_SELF'], '/View/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/Login/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/Actions/') !== false
) {
    $base_url = "../";
}

// Get current page for highlighting active menu item
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Function to check if a menu item should be marked as active
function isActive($page_name)
{
    global $current_page;
    if ($current_page === $page_name) {
        return ' class="active"';
    }
    return '';
}
require_once(__DIR__ . "/../Controllers/wishlist_controller.php");
$wishlist_controller = new WishlistController();
$wishlist_count = $wishlist_controller->get_wishlist_count_display();

// Get cart count for display
require_once(__DIR__ . "/../Controllers/cart_controller.php");
$cart_controller = new CartController();
$cart_count = $cart_controller->get_cart_count_display();
?>

<!-- Header Area Start -->
<header class="header-area header-sticky">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="main-nav">
                    <!-- Logo -->
                    <a href="<?php echo $base_url; ?>index.php" class="logo">
                        <img src="<?php echo $base_url; ?>Images/logo.png" alt="GG-LUGX" style="width: 158px;">
                    </a>

                    <!-- Main Menu -->
                    <ul class="nav">
                        <?php if (is_logged_in() && is_admin()): ?>
                            <!-- Admin Navigation Menu -->
                            <li><a href="<?php echo $base_url; ?>Admin/brand.php" <?php echo isActive('brand'); ?>>Brands</a></li>
                            <li><a href="<?php echo $base_url; ?>Admin/category.php" <?php echo isActive('category'); ?>>Categories</a></li>
                            <li><a href="<?php echo $base_url; ?>Admin/product.php" <?php echo isActive('product'); ?>>Manage Products</a></li>
                            <li><a href="<?php echo $base_url; ?>Admin/orders.php" <?php echo isActive('orders'); ?>>Orders</a></li>
                            <li><a href="<?php echo $base_url; ?>Admin/bundle.php" <?php echo isActive('bundle'); ?>>Bundle</a></li>
                            <li><a href="<?php echo $base_url; ?>Admin/wishlist.php" <?php echo isActive('wislist'); ?>>Wishlist</a></li>
                            <li><a href="<?php echo $base_url; ?>Actions/logout.php">Logout</a></li>
                        <?php elseif (is_logged_in()): ?>
                            <!-- Regular User Navigation Menu -->
                            <li><a href="<?php echo $base_url; ?>index.php" <?php echo isActive('index'); ?>>Home</a></li>
                            <li><a href="<?php echo $base_url; ?>View/all_product.php" <?php echo isActive('all_product'); ?>>Shop</a></li>
                            <li><a href="<?php echo $base_url; ?>View/orders.php" <?php echo isActive('orders'); ?>>Orders</a></li>
                            <li><a href="<?php echo $base_url; ?>View/bundles.php" <?php echo isActive('bundles'); ?>>Deals</a></li>
                            <li><a href="<?php echo $base_url; ?>View/contact.php" <?php echo isActive('contact'); ?>>Contact</a></li>
                            <li><a href="<?php echo $base_url; ?>View/cart.php" <?php echo isActive('cart'); ?>>
                                    <i class="fa fa-shopping-cart <?php echo ($cart_count > 0) ? 'filled' : 'empty'; ?>"></i>
                                    <?php if ($cart_count > 0): ?>
                                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                                    <?php endif; ?>
                                </a></li>
                            <li><a href="<?php echo $base_url; ?>View/wishlist.php" <?php echo isActive('wishlist'); ?>>
                                    <i class="fa fa-heart <?php echo ($wishlist_count > 0) ? 'text-danger' : ''; ?>"></i>
                                    <?php if ($wishlist_count > 0): ?>
                                        <span class="wishlist-badge"><?php echo $wishlist_count; ?></span>
                                    <?php endif; ?>
                                </a></li>
                            <li><a href="<?php echo $base_url; ?>Actions/logout.php">Logout</a></li>

                        <?php else: ?>
                            <!-- Not Logged In Navigation Menu -->
                            <li><a href="<?php echo $base_url; ?>index.php" <?php echo isActive('index'); ?>>Home</a></li>
                            <li><a href="<?php echo $base_url; ?>View/all_product.php" <?php echo isActive('all_product'); ?>>Shop</a></li>
                            <li><a href="<?php echo $base_url; ?>View/bundles.php" <?php echo isActive('bundles'); ?>>Deals</a></li>
                            <li><a href="<?php echo $base_url; ?>View/track_order.php">Track Order</a></li>
                            <li><a href="<?php echo $base_url; ?>View/contact.php" <?php echo isActive('contact'); ?>>Contact</a></li>
                            <li><a href="<?php echo $base_url; ?>View/cart.php" <?php echo isActive('cart'); ?>>
                                    <i class="fa fa-shopping-cart <?php echo ($cart_count > 0) ? 'filled' : 'empty'; ?>"></i>
                                    <?php if ($cart_count > 0): ?>
                                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                                    <?php endif; ?>
                                </a></li>
                            <li><a href="<?php echo $base_url; ?>View/wishlist.php" <?php echo isActive('wishlist'); ?>>
                                    <i class="fa fa-heart <?php echo ($wishlist_count > 0) ? 'text-danger' : ''; ?>"></i>
                                    <?php if ($wishlist_count > 0): ?>
                                        <span class="wishlist-badge"><?php echo $wishlist_count; ?></span>
                                    <?php endif; ?>
                                </a></li>
                            <li><a href="<?php echo $base_url; ?>Login/login.php" <?php echo isActive('login'); ?>>Sign In/Register</a></li>

                        <?php endif; ?>

                        <!-- Username display if logged in -->
                        <?php if (is_logged_in()): ?>
                            <li>
                                <span class="user-greeting">
                                    Hello, <?php echo isset($_SESSION['customer_name']) ? htmlspecialchars($_SESSION['customer_name']) : 'User'; ?>
                                </span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <a class='menu-trigger'>
                        <span>Menu</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</header>
<!-- Header Area End -->

<!-- Add CSS to fix header text visibility -->
<style>
    /* Fix header text visibility */
    .header-area .main-nav .nav li a {
        color: #333333 !important;
        font-weight: 500;
    }

    .header-area .main-nav .nav li a.active {
        color: #ee626b !important;
        font-weight: 600;
    }

    .header-area {
        background-color: #f8f8f8;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .user-greeting {
        color: #333333;
        font-weight: 600;
        margin-right: 15px;
    }
</style>