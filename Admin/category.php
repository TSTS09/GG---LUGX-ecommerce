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
$categories = $product_controller->get_all_categories_ctr();

// Check if editing a category
$edit_mode = false;
$category_to_edit = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $cat_id = (int)$_GET['edit'];
    $category_to_edit = $product_controller->get_one_category_ctr($cat_id);

    if (!$category_to_edit) {
        $edit_mode = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Category - Admin Panel</title>

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
                <h2><?php echo $edit_mode ? 'Edit Category' : 'Category Management'; ?></h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="category-form">
                    <h4><?php echo $edit_mode ? 'Update Category' : 'Add New Category'; ?></h4>
                    <form id="categoryForm" method="POST" action="<?php echo $edit_mode ? '../Actions/update_category.php' : '../Actions/add_category.php'; ?>">
                        <?php if ($edit_mode && $category_to_edit): ?>
                            <input type="hidden" name="cat_id" value="<?php echo $category_to_edit['cat_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="cat_name">Category Name</label>
                            <input type="text" class="form-control" id="cat_name" name="cat_name"
                                value="<?php echo $edit_mode && $category_to_edit ? $category_to_edit['cat_name'] : ''; ?>" required>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Category</button>
                            <?php if ($edit_mode): ?>
                                <a href="category.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="category-table">
                    <h4>All Categories</h4>
                    <?php if ($categories['success'] && !empty($categories['data'])): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories['data'] as $category): ?>
                                        <tr>
                                            <td><?php echo $category['cat_id']; ?></td>
                                            <td><?php echo $category['cat_name']; ?></td>
                                            <td>
                                                <a href="category.php?edit=<?php echo $category['cat_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No categories found.</p>
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
    <script src="../JS/validate_category.js"></script>
</body>

</html>