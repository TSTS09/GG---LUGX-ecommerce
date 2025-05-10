<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

require_once("../Controllers/product_controller.php");
$product_controller = new ProductController();
$products = $product_controller->get_all_products_ctr();
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
                                            <img src="<?php echo $product_to_edit['product_image']; ?>" alt="Current product image" class="img-thumbnail" style="max-width: 150px;">
                                        </div>
                                    <?php endif; ?>
                                    <img id="image-preview" class="img-thumbnail mt-2" alt="Image preview" style="display: none; max-width: 150px;">
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
                    <?php if ($products['success'] && !empty($products['data'])): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products['data'] as $product): ?>
                                        <tr>
                                            <td><?php echo $product['product_id']; ?></td>
                                            <td>
                                                <?php if (!empty($product['product_image'])): ?>
                                                    <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>" style="max-width: 50px; max-height: 50px;">
                                                <?php else: ?>
                                                    <span>No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $product['product_title']; ?></td>
                                            <td><?php echo $product['cat_name']; ?></td>
                                            <td><?php echo $product['brand_name']; ?></td>
                                            <td>$<?php echo number_format($product['product_price'], 2); ?></td>
                                            <td>
                                                <a href="product.php?edit=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="../Actions/delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No products found.</p>
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
</body>

</html>