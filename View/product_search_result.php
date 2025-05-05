<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/product_controller.php");

$product_controller = new ProductController();

// Get search term
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Search products
if (!empty($search_term)) {
    $products = $product_controller->search_products_ctr($search_term);
} else {
    // If no search term, redirect to all products
    header("Location: all_product.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - GG - LUGX</title>

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
        .product-container {
            padding: 30px;
            max-width: 1200px;
            margin: 100px auto;
        }

        .product-card {
            margin-bottom: 30px;
            transition: transform 0.3s;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-top: 10px;
            height: 50px;
            overflow: hidden;
        }

        .product-category {
            color: #777;
            font-size: 14px;
        }

        .product-price {
            font-size: 16px;
            font-weight: 700;
            color: #ee626b;
        }

        .btn-add-to-cart {
            background-color: #ee626b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .btn-add-to-cart:hover {
            background-color: #dc3545;
            color: white;
        }

        .search-bar {
            margin-bottom: 30px;
        }

        .search-input {
            border-radius: 25px;
            padding-left: 20px;
        }

        .btn-search {
            background-color: #ee626b;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php
    // Include the header
    include_once('../includes/header.php');
    ?>

    <div class="product-container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="mb-4">Search Results for: "<?php echo htmlspecialchars($search_term); ?>"</h2>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row search-bar">
            <div class="col-lg-6 offset-lg-3">
                <form action="product_search_result.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" name="search" placeholder="Search for products..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-search" type="submit">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products -->
        <div class="row">
            <?php
            if ($products['success'] && !empty($products['data'])) {
                foreach ($products['data'] as $product) {
            ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card product-card h-100">
                            <img src="<?php echo $product['product_image']; ?>" class="card-img-top product-image" alt="<?php echo $product['product_title']; ?>">
                            <div class="card-body">
                                <h5 class="product-title"><?php echo $product['product_title']; ?></h5>
                                <p class="product-category"><?php echo $product['cat_name']; ?> / <?php echo $product['brand_name']; ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="product-price">$<?php echo number_format($product['product_price'], 2); ?></span>
                                    <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 text-center">
                                <a href="../Actions/add_to_cart.php?id=<?php echo $product['product_id']; ?>" class="btn btn-add-to-cart">
                                    <i class="fa fa-shopping-cart"></i> Add to Cart
                                </a>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <div class="col-lg-12 text-center">
                    <p>No products found matching your search. Please try a different search term.</p>
                    <a href="all_product.php" class="btn btn-primary mt-3">View All Products</a>
                </div>
            <?php
            }
            ?>
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