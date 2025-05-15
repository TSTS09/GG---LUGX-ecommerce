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

// Get all products for bundle creation
$products = $product_controller->get_all_products_ctr();

// Get all existing bundles
$bundles = $bundle_controller->get_all_bundles_ctr();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bundle Management - Admin Panel</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/admin-styles.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="icon" href="../Images/logo.png" type="image/png">
    
    <style>
        .bundle-products {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        
        .bundle-product-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .bundle-product-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .bundle-product-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 10px;
        }
        
        .selected-products {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .selected-product {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            margin-bottom: 10px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="admin-container">
        <div class="row">
            <div class="col-lg-12">
                <h2>Bundle Management</h2>
                
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
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Create New Bundle</h4>
                    </div>
                    <div class="card-body">
                        <form id="bundleForm" method="POST" action="../Actions/create_bundle.php" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="bundle_title">Bundle Title</label>
                                        <input type="text" class="form-control" id="bundle_title" name="bundle_title" required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="bundle_price">Bundle Price</label>
                                        <input type="number" step="0.01" class="form-control" id="bundle_price" name="bundle_price" required>
                                        <small class="form-text text-muted">Set this lower than the sum of individual product prices</small>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="bundle_desc">Bundle Description</label>
                                        <textarea class="form-control" id="bundle_desc" name="bundle_desc" rows="4" required></textarea>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="bundle_image">Bundle Image</label>
                                        <input type="file" class="form-control" id="bundle_image" name="bundle_image">
                                        <small class="form-text text-muted">Upload an image for the bundle (JPG, JPEG, PNG, or GIF, max 5MB)</small>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="bundle_keywords">Bundle Keywords</label>
                                        <input type="text" class="form-control" id="bundle_keywords" name="bundle_keywords" required>
                                        <small class="form-text text-muted">Separate keywords with commas</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Select Products for the Bundle</label>
                                        <div class="bundle-products">
                                            <?php if ($products['success'] && !empty($products['data'])): ?>
                                                <?php foreach ($products['data'] as $product): ?>
                                                    <?php if ($product['is_bundle'] == 0): // Don't allow bundles within bundles ?>
                                                        <div class="bundle-product-item">
                                                            <input type="checkbox" id="product_<?php echo $product['product_id']; ?>" name="product_ids[]" value="<?php echo $product['product_id']; ?>" class="product-checkbox">
                                                            <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>">
                                                            <label for="product_<?php echo $product['product_id']; ?>" class="ml-2">
                                                                <?php echo $product['product_title']; ?>
                                                                <span class="ml-2">$<?php echo number_format($product['product_price'], 2); ?></span>
                                                            </label>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No products available to add to bundle</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="selected-products" id="selectedProducts">
                                        <h5>Selected Products</h5>
                                        <p id="noProductsSelected">No products selected yet</p>
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
                                <button type="submit" class="btn btn-primary">Create Bundle</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Existing Bundles</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($bundles['success'] && !empty($bundles['data'])): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Price</th>
                                            <th>Products</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bundles['data'] as $bundle): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo $bundle['product_image']; ?>" alt="<?php echo $bundle['product_title']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                </td>
                                                <td><?php echo $bundle['product_title']; ?></td>
                                                <td>$<?php echo number_format($bundle['product_price'], 2); ?></td>
                                                <td>
                                                    <?php if (!empty($bundle['items'])): ?>
                                                        <?php echo count($bundle['items']); ?> products
                                                    <?php else: ?>
                                                        No products
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="../View/single_product.php?id=<?php echo $bundle['product_id']; ?>" class="btn btn-sm btn-info" target="_blank">View</a>
                                                    <a href="edit_bundle.php?id=<?php echo $bundle['product_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <a href="../Actions/delete_product.php?id=<?php echo $bundle['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this bundle?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No bundles created yet</p>
                        <?php endif; ?>
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
    
    <script>
        // Keep track of selected products and their prices
        let selectedProducts = [];
        let productPrices = {};
        
        <?php if ($products['success'] && !empty($products['data'])): ?>
            <?php foreach ($products['data'] as $product): ?>
                productPrices[<?php echo $product['product_id']; ?>] = <?php echo $product['product_price']; ?>;
            <?php endforeach; ?>
        <?php endif; ?>
        
        // Handle product selection
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const productId = this.value;
                
                if (this.checked) {
                    // Add to selected products
                    selectedProducts.push(productId);
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
        
        // Update selected products display
        function updateSelectedProducts() {
            const selectedProductsList = document.getElementById('selectedProductsList');
            const noProductsSelected = document.getElementById('noProductsSelected');
            
            if (selectedProducts.length === 0) {
                selectedProductsList.innerHTML = '';
                noProductsSelected.style.display = 'block';
                return;
            }
            
            noProductsSelected.style.display = 'none';
            selectedProductsList.innerHTML = '';
            
            selectedProducts.forEach(productId => {
                const checkbox = document.getElementById('product_' + productId);
                const label = checkbox.nextElementSibling.nextElementSibling;
                const img = checkbox.nextElementSibling;
                
                const productElement = document.createElement('div');
                productElement.className = 'selected-product';
                
                const productInfo = document.createElement('div');
                productInfo.className = 'd-flex align-items-center';
                
                const productImg = document.createElement('img');
                productImg.src = img.src;
                productImg.alt = label.textContent.trim();
                
                const productName = document.createElement('span');
                productName.textContent = label.textContent.trim();
                productName.className = 'ml-2';
                
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-danger';
                removeButton.innerHTML = '<i class="fa fa-times"></i>';
                removeButton.addEventListener('click', function() {
                    checkbox.checked = false;
                    selectedProducts = selectedProducts.filter(id => id != productId);
                    updateSelectedProducts();
                    updatePricing();
                });
                
                productInfo.appendChild(productImg);
                productInfo.appendChild(productName);
                
                productElement.appendChild(productInfo);
                productElement.appendChild(removeButton);
                
                selectedProductsList.appendChild(productElement);
                
                // Add hidden input for the selected product
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'product_ids[]';
                hiddenInput.value = productId;
                selectedProductsList.appendChild(hiddenInput);
                
                // Add hidden input for the discount
                const discountInput = document.createElement('input');
                discountInput.type = 'hidden';
                discountInput.name = 'discounts[]';
                discountInput.value = '0';
                selectedProductsList.appendChild(discountInput);
            });
        }
        
        // Update pricing information
        function updatePricing() {
            const totalOriginalPrice = document.getElementById('totalOriginalPrice');
            const bundlePrice = document.getElementById('bundlePrice');
            const savingsAmount = document.getElementById('savingsAmount');
            const savingsPercent = document.getElementById('savingsPercent');
            
            // Calculate total original price
            let total = 0;
            selectedProducts.forEach(productId => {
                total += productPrices[productId];
            });
            
            // Get bundle price
            const bundlePriceValue = parseFloat(document.getElementById('bundle_price').value) || 0;
            
            // Calculate savings
            const savings = total - bundlePriceValue;
            const savingsPercentValue = total > 0 ? (savings / total) * 100 : 0;
            
            // Update display
            totalOriginalPrice.textContent = '$' + total.toFixed(2);
            bundlePrice.textContent = '$' + bundlePriceValue.toFixed(2);
            savingsAmount.textContent = '$' + savings.toFixed(2);
            savingsPercent.textContent = Math.round(savingsPercentValue);
        }
    </script>
</body>

</html>