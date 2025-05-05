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
$brands = $product_controller->get_all_brands_ctr();

// Check if editing a brand
$edit_mode = false;
$brand_to_edit = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $brand_id = (int)$_GET['edit'];
    $brand_to_edit = $product_controller->get_one_brand_ctr($brand_id);

    if (!$brand_to_edit) {
        $edit_mode = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Brand - Admin Panel</title>

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
                <h2><?php echo $edit_mode ? 'Edit Brand' : 'Brand Management'; ?></h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="brand-form">
                    <h4><?php echo $edit_mode ? 'Update Brand' : 'Add New Brand'; ?></h4>
                    <form id="brandForm" method="POST" action="<?php echo $edit_mode ? '../Actions/update_brand.php' : '../Actions/add_brand.php'; ?>">
                        <?php if ($edit_mode && $brand_to_edit): ?>
                            <input type="hidden" name="brand_id" value="<?php echo $brand_to_edit['brand_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="brand_name">Brand Name</label>
                            <input type="text" class="form-control" id="brand_name" name="brand_name"
                                value="<?php echo $edit_mode && $brand_to_edit ? $brand_to_edit['brand_name'] : ''; ?>" required>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Brand</button>
                            <?php if ($edit_mode): ?>
                                <a href="brand.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="brand-table">
                    <h4>All Brands</h4>
                    <?php if ($brands['success'] && !empty($brands['data'])): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Brand Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($brands['data'] as $brand): ?>
                                        <tr>
                                            <td><?php echo $brand['brand_id']; ?></td>
                                            <td><?php echo $brand['brand_name']; ?></td>
                                            <td>
                                                <a href="brand.php?edit=<?php echo $brand['brand_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No brands found.</p>
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
    <script src="../JS/validate_brand.js"></script>
</body>

</html>