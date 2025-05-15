<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/wishlist_controller.php");

// Create wishlist controller instance
$wishlist_controller = new WishlistController();

// Handle both logged in and guest users
if (is_logged_in()) {
    $customer_id = $_SESSION['customer_id'];
    
    // Get wishlist items
    $wishlist_items = $wishlist_controller->get_wishlist_items_ctr($customer_id);
} else {
    // For guest users
    if (!isset($_SESSION['guest_session_id'])) {
        $_SESSION['guest_session_id'] = uniqid('guest_', true);
    }
    $guest_id = $_SESSION['guest_session_id'];
    
    // Get guest wishlist items
    $wishlist_items = $wishlist_controller->get_guest_wishlist_items_ctr($guest_id);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - GG - LUGX</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" href="../Images/logo.png" type="image/png">
    
    <style>
        .wishlist-container {
            padding: 30px;
            max-width: 1200px;
            margin: 100px auto;
        }
        
        .wishlist-item {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .wishlist-item:hover {
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        
        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .wishlist-empty {
            text-align: center;
            padding: 50px 20px;
        }
        
        .bundle-badge, .preorder-badge {
            position: absolute;
            top: 10px;
            right: 25px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .bundle-badge {
            background-color: #ee626b;
            color: white;
        }
        
        .preorder-badge {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="wishlist-container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="mb-4">My Wishlist</h2>
                
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

        <?php if ($wishlist_items['success'] && !empty($wishlist_items['data'])): ?>
            <div class="row">
                <?php foreach ($wishlist_items['data'] as $item): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="wishlist-item position-relative">
                            <?php if ($item['is_bundle'] == 1): ?>
                                <span class="bundle-badge">Bundle</span>
                            <?php endif; ?>
                            
                            <?php if ($item['is_preorder'] == 1): ?>
                                <span class="preorder-badge">Pre-order</span>
                            <?php endif; ?>
                            
                            <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" class="mb-3">
                            
                            <h5><?php echo $item['product_title']; ?></h5>
                            <p class="price">$<?php echo number_format($item['product_price'], 2); ?></p>
                            
                            <?php if ($item['is_preorder'] == 1 && $item['release_date']): ?>
                                <p class="release-date">Release Date: <?php echo date('F j, Y', strtotime($item['release_date'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <a href="../Actions/remove_from_wishlist.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i> Remove
                                </a>
                                
                                <?php if (!$item['is_preorder'] || is_logged_in()): ?>
                                    <a href="../Actions/add_to_cart.php?id=<?php echo $item['p_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-shopping-cart"></i> Add to Cart
                                    </a>
                                <?php else: ?>
                                    <a href="../Login/login.php?redirect=wishlist" class="btn btn-sm btn-warning">
                                        <i class="fa fa-user"></i> Login to Pre-order
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="wishlist-empty">
                <h3>Your wishlist is empty</h3>
                <p>Add games to your wishlist to keep track of what you want to buy next!</p>
                <a href="all_product.php" class="btn btn-primary mt-3">
                    <i class="fa fa-gamepad"></i> Browse Games
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