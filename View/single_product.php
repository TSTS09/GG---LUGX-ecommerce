<?php
session_start();
require_once("../Setting/core.php");
require_once("../Controllers/product_controller.php");

$product_controller = new ProductController();

// Check if product ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $product = $product_controller->get_one_product_ctr($product_id);

    // If product not found, redirect to all products
    if (!$product) {
        header("Location: all_product.php");
        exit;
    }
} else {
    // If no ID provided, redirect to all products
    header("Location: all_product.php");
    exit;
}

// Get related products (products in the same category)
$related_products = $product_controller->get_products_by_category_ctr($product['product_cat'], 4);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_title']; ?> - GG - LUGX</title>

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

        .product-image {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        .product-meta {
            display: flex;
            margin-bottom: 15px;
        }

        .product-category,
        .product-brand {
            margin-right: 20px;
            color: #777;
        }

        .product-category span,
        .product-brand span {
            font-weight: 600;
            color: #333;
        }

        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #ee626b;
            margin-bottom: 20px;
        }

        .product-description {
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .product-keywords {
            margin-bottom: 30px;
        }

        .keyword-tag {
            display: inline-block;
            background-color: #f8f9fa;
            padding: 5px 15px;
            border-radius: 25px;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .btn-add-to-cart {
            background-color: #ee626b;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-add-to-cart:hover {
            background-color: #dc3545;
            color: white;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .quantity-label {
            margin-right: 15px;
            font-weight: 600;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 5px;
        }

        .related-title {
            margin-top: 50px;
            margin-bottom: 30px;
            font-weight: 700;
            color: #333;
        }

        .related-product-card {
            margin-bottom: 30px;
            transition: transform 0.3s;
            height: 100%;
        }

        .related-product-card:hover {
            transform: translateY(-5px);
        }

        .related-product-image {
            height: 150px;
            object-fit: cover;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .related-product-title {
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            height: 40px;
            overflow: hidden;
        }

        .related-product-price {
            font-size: 14px;
            font-weight: 700;
            color: #ee626b;
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
            <!-- Product Image -->
            <div class="col-lg-6">
                <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>" class="product-image">
            </div>

            <!-- Product Info -->
            <div class="col-lg-6 product-info">
                <h1 class="product-title"><?php echo $product['product_title']; ?></h1>

                <div class="product-meta">
                    <div class="product-category">
                        Category: <span><?php echo $product['cat_name']; ?></span>
                    </div>
                    <div class="product-brand">
                        Brand: <span><?php echo $product['brand_name']; ?></span>
                    </div>
                </div>

                <div class="product-price">
                    $<?php echo number_format($product['product_price'], 2); ?>
                </div>

                <div class="product-description">
                    <h5>Product Description:</h5>
                    <p><?php echo $product['product_desc']; ?></p>
                </div>

                <div class="product-keywords">
                    <?php
                    $keywords = explode(',', $product['product_keywords']);
                    foreach ($keywords as $keyword) {
                        $keyword = trim($keyword);
                        if (!empty($keyword)) {
                            echo '<span class="keyword-tag">' . $keyword . '</span>';
                        }
                    }
                    ?>
                </div>

                <form action="../Actions/add_to_cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

                    <div class="quantity-selector">
                        <label for="quantity" class="quantity-label">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" class="quantity-input" value="1" min="1" max="10">
                    </div>

                    <button type="submit" class="btn btn-add-to-cart">
                        <i class="fa fa-shopping-cart"></i> Add to Cart
                    </button>
                </form>

                <div class="mt-4">
                    <a href="all_product.php" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Shop
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div class="row">
            <div class="col-lg-12">
                <h3 class="related-title">Related Products</h3>
            </div>

            <?php
            if ($related_products['success'] && !empty($related_products['data'])) {
                foreach ($related_products['data'] as $related) {
                    // Skip the current product in related products
                    if ($related['product_id'] == $product_id) {
                        continue;
                    }
            ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card related-product-card h-100">
                            <img src="<?php echo $related['product_image']; ?>" class="card-img-top related-product-image" alt="<?php echo $related['product_title']; ?>">
                            <div class="card-body">
                                <h5 class="related-product-title"><?php echo $related['product_title']; ?></h5>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="related-product-price">$<?php echo number_format($related['product_price'], 2); ?></span>
                                    <a href="single_product.php?id=<?php echo $related['product_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <div class="col-lg-12 text-center">
                    <p>No related products found.</p>
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