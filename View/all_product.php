<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/product_controller.php");

$product_controller = new ProductController();

// Handle search or category filter
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Get products based on filters
if (!empty($search_term)) {
    // For search
    $products = $product_controller->search_products_ctr($search_term);
} elseif (!empty($category_filter)) {
    // Using category filter
    if (is_numeric($category_filter)) {
        $category_products = $product_controller->get_products_by_category_ctr($category_filter);
        // Get category name for display
        $category_info = $product_controller->get_one_category_ctr($category_filter);
        $category_name = $category_info ? $category_info['cat_name'] : 'Category';
        $products = $category_products;
    } else {
        // Get category ID from name 
        $category = $product_controller->get_category_by_name_ctr($category_filter);
        if ($category) {
            $category_products = $product_controller->get_products_by_category_ctr($category['cat_id']);
            $category_name = $category_filter;
            $products = $category_products;
        } else {
            // Get all products without pagination
            $products = $product_controller->get_all_products_ctr('', 1000, 1);
            $category_name = 'All Products';
        }
    }
} else {
    // Get all products without pagination
    $products = $product_controller->get_all_products_ctr('', 1000, 1);
    $category_name = 'All Products';
}

// Get all categories for filtering
$all_categories = $product_controller->get_all_categories_ctr();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - GG - LUGX</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="../CSS/all_product.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" href="../Images/logo.png" type="image/png">

    <style>
        /* Add these styles for consistent image dimensions */
        .product-card .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .category-badge {
            margin-bottom: 5px;
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
                <h2 class="mb-4">
                    <?php
                    if (!empty($search_term)) {
                        echo 'Search Results for: "' . htmlspecialchars($search_term) . '"';
                    } elseif (!empty($category_filter)) {
                        echo 'Products in Category: ' . htmlspecialchars($category_name);
                    } else {
                        echo 'All Products';
                    }
                    ?>
                </h2>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row search-bar">
            <div class="col-lg-6 offset-lg-3">
                <form action="all_product.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" name="search" placeholder="Search for products..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-search" type="submit">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="row category-filters">
            <div class="col-lg-12 text-center">
                <?php
                if ($all_categories['success'] && !empty($all_categories['data'])) {
                    echo '<a href="all_product.php" class="category-badge ' . (empty($category_filter) ? 'active-filter' : '') . '">All</a>';

                    foreach ($all_categories['data'] as $category) {
                        // Count products in this category
                        $category_products = $product_controller->get_products_by_category_ctr($category['cat_id']);
                        $product_count = isset($category_products['data']) ? count($category_products['data']) : 0;

                        $active_class = ($category_filter == $category['cat_id'] || $category_filter == $category['cat_name']) ? 'active-filter' : '';
                        echo '<a href="all_product.php?category=' . $category['cat_id'] . '" class="category-badge ' . $active_class . '">' .
                            $category['cat_name'] . ' (' . $product_count . ')</a>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Display Options & Pagination Controls -->
        <?php if (isset($products['success']) && $products['success'] && !empty($products['data'])): ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <p>Displaying all products</p>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <?php if (!empty($category_filter) || !empty($search_term)): ?>
                            <a href="all_product.php" class="btn btn-secondary mr-2">
                                <i class="fa fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Products -->
        <div class="row">
            <?php
            if (isset($products['success']) && $products['success'] && !empty($products['data'])) {
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
                            <?php if (!is_admin()): ?>
                                <div class="card-footer bg-white border-top-0 text-center">
                                    <a href="../Actions/add_to_cart.php?id=<?php echo $product['product_id']; ?>" class="btn btn-add-to-cart">
                                        <i class="fa fa-shopping-cart"></i> Add to Cart
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <div class="col-lg-12 text-center">
                    <p>No products found. Please try a different search or category.</p>
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