<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/product_controller.php");

$product_controller = new ProductController();

/**
 * Helper function to add or update a query parameter
 * @param string $param - Parameter name
 * @param string $value - Parameter value
 * @return string - URL with updated parameter
 */
function add_query_param($param, $value)
{
    $params = $_GET;
    $params[$param] = $value;

    return '?' . http_build_query($params);
}

/**
 * Helper function to update multiple query parameters
 * @param array $new_params - Associative array of parameters to update
 * @return string - URL with updated parameters
 */
function update_query_params($new_params)
{
    $params = $_GET;

    foreach ($new_params as $key => $value) {
        $params[$key] = $value;
    }

    return '?' . http_build_query($params);
}

// Handle search or category filter
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Get products - using your existing controller methods without pagination parameters
if (!empty($search_term)) {
    $products = $product_controller->search_products_ctr($search_term);
} elseif (!empty($category_filter)) {
    // Check if category filter is numeric (ID) or string (name)
    if (is_numeric($category_filter)) {
        $products = $product_controller->get_products_by_category_ctr($category_filter);
        // Get category name for display
        $category_info = $product_controller->get_one_category_ctr($category_filter);
        $category_name = $category_info ? $category_info['cat_name'] : 'Category';
    } else {
        // Get category ID from name 
        $category = $product_controller->get_category_by_name_ctr($category_filter);
        if ($category) {
            $products = $product_controller->get_products_by_category_ctr($category['cat_id']);
            $category_name = $category_filter;
        } else {
            $products = $product_controller->get_all_products_ctr();
            $category_name = 'All Products';
        }
    }
} else {
    $products = $product_controller->get_all_products_ctr();
    $category_name = 'All Products';
}

// Get all categories for filtering
$all_categories = $product_controller->get_all_categories_ctr();

// Simple client-side pagination
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

// Apply client-side pagination if products exist
$total_products = 0;
$paginated_products = ['success' => false, 'data' => []];

if ($products['success'] && !empty($products['data'])) {
    $total_products = count($products['data']);

    // Calculate starting and ending indices for current page
    $start_index = ($current_page - 1) * $limit;
    $end_index = min($start_index + $limit, $total_products);

    // Create a subset of products for the current page
    $paginated_data = array_slice($products['data'], $start_index, $limit);

    $paginated_products = [
        'success' => true,
        'data' => $paginated_data,
        'total_count' => $total_products,
        'total_pages' => ceil($total_products / $limit)
    ];
} else {
    // Keep the original products result
    $paginated_products = $products;
}
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
        /* Add these styles for consistent image dimensions and pagination */
        .product-card .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            color: #ee626b;
            border-color: #dee2e6;
        }

        .page-item.active .page-link {
            background-color: #ee626b;
            border-color: #ee626b;
        }

        .page-link:hover {
            color: #fff;
            background-color: #e04147;
            border-color: #e04147;
        }

        .form-inline {
            display: flex;
            align-items: center;
        }

        .form-inline label {
            margin-right: 10px;
            margin-bottom: 0;
        }

        .dropdown-item.active {
            background-color: #ee626b;
            color: #fff;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
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
        <?php if ($paginated_products['success'] && !empty($paginated_products['data'])): ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-inline">
                        <label class="mr-2">Show:</label>
                        <select id="show-entries" class="form-control" onchange="changeDisplayCount(this.value)">
                            <option value="12" <?php echo $limit == 12 ? 'selected' : ''; ?>>12</option>
                            <option value="24" <?php echo $limit == 24 ? 'selected' : ''; ?>>24</option>
                            <option value="48" <?php echo $limit == 48 ? 'selected' : ''; ?>>48</option>
                        </select>
                    </div>
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
            if ($paginated_products['success'] && !empty($paginated_products['data'])) {
                foreach ($paginated_products['data'] as $product) {
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

        <!-- Pagination Controls -->
        <?php if ($paginated_products['success'] && !empty($paginated_products['data']) && $paginated_products['total_count'] > $limit): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <p>Showing <?php echo count($paginated_products['data']); ?> of <?php echo $paginated_products['total_count']; ?> products</p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end">
                            <?php
                            // Calculate total pages
                            $total_pages = $paginated_products['total_pages'];

                            // Previous page link
                            if ($current_page > 1):
                            ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo update_query_params(['page' => $current_page - 1]); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            // Page number links
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo update_query_params(['page' => $i]); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php
                            // Next page link
                            if ($current_page < $total_pages):
                            ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo update_query_params(['page' => $current_page + 1]); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
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

    <script>
        function changeDisplayCount(limit) {
            // Get current URL
            let url = new URL(window.location.href);

            // Set the limit parameter
            url.searchParams.set('limit', limit);

            // Reset to first page when changing limit
            url.searchParams.set('page', 1);

            // Redirect to new URL
            window.location.href = url.toString();
        }
    </script>
</body>

</html>