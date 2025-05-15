<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

require_once("../Controllers/product_controller.php");
require_once("../Controllers/bundle_controller.php");

$product_controller = new ProductController();
$bundle_controller = new BundleController();

// Check if bundle ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid bundle ID'];
    header("Location: bundle.php");
    exit;
}

$bundle_id = (int)$_GET['id'];

// Get bundle details
$bundle = $product_controller->get_one_product_ctr($bundle_id);

// Check if bundle exists and is actually a bundle
if (!$bundle || !isset($bundle['is_bundle']) || $bundle['is_bundle'] != 1) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Bundle not found or invalid'];
    header("Location: bundle.php");
    exit;
}

// Get bundle items
$bundle_items = $bundle_controller->get_bundle_items_ctr($bundle_id);

// Get all products for bundle creation
$products = $product_controller->get_all_products_ctr();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bundle - Admin Panel</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/admin-styles.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="icon" href="../Images/logo.png" type="image/png">
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="admin-container">
        <div class="row">
            <div class="col-lg-12">
                <h2>Edit Bundle</h2>

                <!-- Display messages if any -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message']['type'] === 'error' ? 'danger' : $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']['text']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <a href="bundle.php" class="btn btn-secondary mb-3">
                    <i class="fa fa-arrow-left"></i> Back to Bundles
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Bundle: <?php echo $bundle['product_title']; ?></h4>
                    </div>
                    <div class="card-body">
                        <form id="bundleForm" method="POST" action="../Actions/update_bundle.php" enctype="multipart/form-data">
                            <input type="hidden" name="bundle_id" value="<?php echo $bundle_id; ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="bundle_title">Bundle Title</label>
                                        <input type="text" class="form-control" id="bundle_title" name="bundle_title" value="<?php echo htmlspecialchars($bundle['product_title']); ?>" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="bundle_price">Bundle Price</label>
                                        <input type="number" step="0.01" class="form-control" id="bundle_price" name="bundle_price" value="<?php echo $bundle['product_price']; ?>" required>
                                        <small class="form-text text-muted">Set this lower than the sum of individual product prices</small>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="bundle_desc">Bundle Description</label>
                                        <textarea class="form-control" id="bundle_desc" name="bundle_desc" rows="4" required><?php echo htmlspecialchars($bundle['product_desc']); ?></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="bundle_image">Bundle Image</label>
                                        <input type="file" class="form-control" id="bundle_image" name="bundle_image">
                                        <small class="form-text text-muted">Upload a new image or leave blank to keep current image</small>

                                        <?php if (!empty($bundle['product_image'])): ?>
                                            <div class="mt-2">
                                                <p>Current image:</p>
                                                <img src="<?php echo $bundle['product_image']; ?>" alt="Current bundle image" class="img-thumbnail" style="max-width: 150px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="bundle_keywords">Bundle Keywords</label>
                                        <input type="text" class="form-control" id="bundle_keywords" name="bundle_keywords" value="<?php echo htmlspecialchars($bundle['product_keywords']); ?>" required>
                                        <small class="form-text text-muted">Separate keywords with commas</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Select Products for the Bundle</label>
                                        <div class="bundle-products">
                                            <?php if ($products['success'] && !empty($products['data'])): ?>
                                                <?php
                                                // Create an array of current bundle item product IDs for easier checking
                                                $current_bundle_items = array_map(function ($item) {
                                                    return $item['product_id'];
                                                }, $bundle_items);
                                                ?>
                                                <input type="hidden" name="quantities[]" value="1" for each product>

                                                <?php foreach ($products['data'] as $product): ?>
                                                    <?php
                                                    // Skip the current bundle itself and other bundles
                                                    if ($product['product_id'] == $bundle_id || (isset($product['is_bundle']) && $product['is_bundle'] == 1)) {
                                                        continue;
                                                    }
                                                    

                                                    // Check if this product is already in the bundle
                                                    $is_selected = in_array($product['product_id'], $current_bundle_items);
                                                    ?>

                                                    <div class="bundle-product-item">
                                                        <input type="checkbox" id="product_<?php echo $product['product_id']; ?>" name="product_ids[]" value="<?php echo $product['product_id']; ?>" class="product-checkbox" <?php echo $is_selected ? 'checked' : ''; ?>>
                                                        <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>">
                                                        <label for="product_<?php echo $product['product_id']; ?>" class="ml-2">
                                                            <?php echo $product['product_title']; ?>
                                                            <span class="ml-2">$<?php echo number_format($product['product_price'], 2); ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No products available to add to bundle</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="selected-products" id="selectedProducts">
                                        <h5>Selected Products</h5>
                                        <p id="noProductsSelected" style="<?php echo !empty($bundle_items) ? 'display: none;' : ''; ?>">No products selected yet</p>
                                        <div id="selectedProductsList"></div>

                                        <div class="mt-3">
                                            <p><strong>Total Individual Price: </strong><span id="totalOriginalPrice">$0.00</span></p>
                                            <p><strong>Bundle Price: </strong><span id="bundlePrice">$0.00</span></p>
                                            <p><strong>Savings: </strong><span id="savingsAmount">$0.00</span> (<span id="savingsPercent">0</span>%)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Update Bundle</button>
                                <a href="bundle.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
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
    <script src="../JS/bundle_2.js"></script>

    <script>
        // Keep track of selected products and their prices
        let selectedProducts = [
            <?php
            foreach ($bundle_items as $item) {
                echo $item['product_id'] . ', ';
            }
            ?>
        ];

        let productPrices = {};

        <?php if ($products['success'] && !empty($products['data'])): ?>
            <?php foreach ($products['data'] as $product): ?>
                productPrices[<?php echo $product['product_id']; ?>] = <?php echo $product['product_price']; ?>;
            <?php endforeach; ?>
        <?php endif; ?>

        // Handle product selection
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const productId = parseInt(this.value);

                if (this.checked) {
                    // Add to selected products
                    if (!selectedProducts.includes(productId)) {
                        selectedProducts.push(productId);
                    }
                } else {
                    // Remove from selected products
                    selectedProducts = selectedProducts.filter(id => id != productId);
                }

                // Update the selected products display
                updateSelectedProducts();

                // Update pricing
                updatePricing();
            });
        });

        // Update bundle price when input changes
        document.getElementById('bundle_price').addEventListener('input', function() {
            updatePricing();
        });

        
    </script>
</body>

</html>