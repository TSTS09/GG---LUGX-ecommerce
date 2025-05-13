<?php

// Add debugging code
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log some debug info
$debug_file = "../Error/product_debug.log";
file_put_contents($debug_file, "Starting product.php debug\n", FILE_APPEND);

session_start();
require_once("../Setting/core.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

require_once("../Controllers/product_controller.php");
$product_controller = new ProductController();

// Get pagination parameters
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get products with pagination
$products = $product_controller->get_all_products_ctr($search, $limit, $current_page);

// Get categories and brands for the form
$categories = $product_controller->get_all_categories_ctr();
$brands = $product_controller->get_all_brands_ctr();

// Check if editing a product
$edit_mode = false;
$product_to_edit = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $product_id = (int)$_GET['edit'];
    $product_to_edit = $product_controller->get_one_product_ctr($product_id);

    if (!$product_to_edit) {
        $edit_mode = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Product - Admin Panel</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/admin-styles.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="icon" href="../Images/logo.png" type="image/png">

    <!-- Custom CSS for admin product tables -->
    <style>
        .product-table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .pagination {
            margin-bottom: 0;
        }

        .form-inline {
            display: flex;
            align-items: center;
        }

        .form-inline label {
            margin-right: 10px;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .page-info {
            padding-top: 10px;
        }

        .form-control {
            height: auto;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php
    // Include the header
    include_once('../includes/header.php');
    ?>

    <div class="admin-container">
        <div class="row">
            <div class="col-lg-12">
                <h2><?php echo $edit_mode ? 'Edit Product' : 'Product Management'; ?></h2>

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
                <div class="product-form">
                    <h4><?php echo $edit_mode ? 'Update Product' : 'Add New Product'; ?></h4>
                    <form id="productForm" method="POST" action="<?php echo $edit_mode ? '../Actions/update_product.php' : '../Actions/add_product.php'; ?>" enctype="multipart/form-data">
                        <?php if ($edit_mode && $product_to_edit): ?>
                            <input type="hidden" name="product_id" value="<?php echo $product_to_edit['product_id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="product_title">Product Title</label>
                                    <input type="text" class="form-control" id="product_title" name="product_title"
                                        value="<?php echo $edit_mode && $product_to_edit ? $product_to_edit['product_title'] : ''; ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="product_cat">Product Category</label>
                                    <select class="form-control" id="product_cat" name="product_cat" required>
                                        <option value="">Select Category</option>
                                        <?php
                                        if ($categories['success'] && !empty($categories['data'])) {
                                            foreach ($categories['data'] as $category) {
                                                $selected = ($edit_mode && $product_to_edit && $product_to_edit['product_cat'] == $category['cat_id']) ? 'selected' : '';
                                                echo "<option value=\"{$category['cat_id']}\" {$selected}>{$category['cat_name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="product_brand">Product Brand</label>
                                    <select class="form-control" id="product_brand" name="product_brand" required>
                                        <option value="">Select Brand</option>
                                        <?php
                                        if ($brands['success'] && !empty($brands['data'])) {
                                            foreach ($brands['data'] as $brand) {
                                                $selected = ($edit_mode && $product_to_edit && $product_to_edit['product_brand'] == $brand['brand_id']) ? 'selected' : '';
                                                echo "<option value=\"{$brand['brand_id']}\" {$selected}>{$brand['brand_name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="product_price">Product Price</label>
                                    <input type="number" step="0.01" class="form-control" id="product_price" name="product_price"
                                        value="<?php echo $edit_mode && $product_to_edit ? $product_to_edit['product_price'] : ''; ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="product_desc">Product Description</label>
                                    <textarea class="form-control" id="product_desc" name="product_desc" rows="4" required><?php echo $edit_mode && $product_to_edit ? $product_to_edit['product_desc'] : ''; ?></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="product_keywords">Product Keywords</label>
                                    <input type="text" class="form-control" id="product_keywords" name="product_keywords"
                                        value="<?php echo $edit_mode && $product_to_edit ? $product_to_edit['product_keywords'] : ''; ?>" required>
                                    <small class="form-text text-muted">Separate keywords with commas</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="product_image">Product Image (Optional)</label>
                                    <input type="file" class="form-control" id="product_image" name="product_image">
                                    <small class="form-text text-muted">Upload an image (JPG, JPEG, PNG, or GIF, max 5MB) or leave blank to use a default image.</small>
                                    <?php if ($edit_mode && $product_to_edit && !empty($product_to_edit['product_image'])): ?>
                                        <div class="mt-2">
                                            <p>Current image:</p>
                                            <img src="<?php echo $product_to_edit['product_image']; ?>" alt="Current product image" class="img-thumbnail" style="max-width: 150px; height: 100px; object-fit: cover;">
                                        </div>
                                    <?php endif; ?>
                                    <img id="image-preview" class="img-thumbnail mt-2" alt="Image preview" style="display: none; max-width: 150px; height: 100px; object-fit: cover;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Product</button>
                            <?php if ($edit_mode): ?>
                                <a href="product.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="product-table">
                    <h4>All Products</h4>

                    <!-- Display Options & Pagination Controls -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-inline">
                                <label for="show-entries">Show entries: </label>
                                <select id="show-entries" class="form-control" onchange="changeDisplayCount(this.value)">
                                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inline float-right">
                                <input type="text" id="search-products" class="form-control mr-2" placeholder="Search products..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                                <button onclick="searchProducts()" class="btn btn-primary">Search</button>
                                <?php if (!empty($search)): ?>
                                    <a href="product.php" class="btn btn-secondary ml-2">Clear</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($products['success'] && !empty($products['data'])): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Initialize counter for row numbering
                                    $counter = ($current_page - 1) * $limit + 1;
                                    foreach ($products['data'] as $product):
                                    ?>
                                        <tr>
                                            <td><?php echo $counter; ?></td>
                                            <td>
                                                <?php if (!empty($product['product_image'])): ?>
                                                    <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                <?php else: ?>
                                                    <span>No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $product['product_title']; ?></td>
                                            <td><?php echo $product['cat_name']; ?></td>
                                            <td><?php echo $product['brand_name']; ?></td>
                                            <td>$<?php echo number_format($product['product_price'], 2); ?></td>
                                            <td>
                                                <a href="product.php?edit=<?php echo $product['product_id']; ?>&page=<?php echo $current_page; ?>&limit=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="../Actions/delete_product.php?id=<?php echo $product['product_id']; ?>&return=<?php echo urlencode("product.php?page={$current_page}&limit={$limit}" . (!empty($search) ? "&search=" . urlencode($search) : "")); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php
                                        // Increment counter for next row
                                        $counter++;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="row">
                            <div class="col-md-6">
                                <p class="page-info">Showing <?php echo count($products['data']); ?> of <?php echo $products['total_count']; ?> entries</p>
                            </div>
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end">
                                        <?php
                                        // Calculate total pages
                                        $total_pages = $products['total_pages'];

                                        // Previous page link
                                        if ($current_page > 1):
                                        ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&limit=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
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
                                                <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php
                                        // Next page link
                                        if ($current_page < $total_pages):
                                        ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&limit=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No products found<?php echo !empty($search) ? ' matching your search criteria' : ''; ?>.</p>
                        <?php if (!empty($search)): ?>
                            <a href="product.php" class="btn btn-primary">Show All Products</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
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
    <script src="../JS/validate_product.js"></script>
    <script src="../JS/pagination-admin.js"></script>
   
</body>

</html>