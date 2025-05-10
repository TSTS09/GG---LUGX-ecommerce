<?php
// Start output buffering to capture any unexpected output
ob_start();

require_once(__DIR__ . "/../Controllers/product_controller.php");
require_once(__DIR__ . "/../Setting/core.php");

// Enable error logging, but disable error display for clean JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Check if session is already started before starting it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log PHP configuration
error_log("PHP Upload Configuration:");
error_log("file_uploads: " . ini_get('file_uploads'));
error_log("upload_max_filesize: " . ini_get('upload_max_filesize'));
error_log("post_max_size: " . ini_get('post_max_size'));
error_log("upload_tmp_dir: " . ini_get('upload_tmp_dir'));
error_log("max_file_uploads: " . ini_get('max_file_uploads'));

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    // Clean any buffered output
    ob_end_clean();

    // Send clean JSON
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Log form submission
        error_log("Processing product form submission");

        // Validate the input
        $required_fields = ['product_title', 'product_cat', 'product_brand', 'product_price', 'product_desc', 'product_keywords'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                error_log("Missing required field: $field");
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                ]);
                exit;
            }
        }

        // Log form data
        error_log("Form data: " . json_encode($_POST));

        // Get form data
        $product_title = trim($_POST['product_title']);
        $product_cat = (int)$_POST['product_cat'];
        $product_brand = (int)$_POST['product_brand'];
        $product_price = (float)$_POST['product_price'];
        $product_desc = trim($_POST['product_desc']);
        $product_keywords = trim($_POST['product_keywords']);

        // Validate data types and lengths
        if (strlen($product_title) > 100) {
            error_log("Product title too long: " . strlen($product_title));
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Product title is too long (maximum 100 characters)'
            ]);
            exit;
        }

        if ($product_price <= 0) {
            error_log("Invalid product price: " . $product_price);
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Product price must be greater than zero'
            ]);
            exit;
        }

        // Handle image upload - make it optional
        $image_path = '../Images/product/default.jpg'; // Default image path

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != UPLOAD_ERR_NO_FILE) {
            error_log("Processing image upload: " . $_FILES['product_image']['name']);

            // A file was uploaded, process it
            $target_dir = "../Images/product/";
            $timestamp = time();
            $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
            $new_file_name = $timestamp . '_' . rand(1000, 9999) . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;
            $image_path = '../Images/product/' . $new_file_name;

            error_log("Target directory: $target_dir");
            error_log("Target file: $target_file");

            // Create upload directory if it doesn't exist
            if (!file_exists($target_dir)) {
                error_log("Creating target directory: $target_dir");
                if (!mkdir($target_dir, 0777, true)) {
                    $mkdir_error = error_get_last();
                    error_log("Failed to create directory: " . ($mkdir_error ? $mkdir_error['message'] : 'Unknown error'));
                    ob_end_clean();
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to create upload directory: ' . ($mkdir_error ? $mkdir_error['message'] : 'Unknown error')
                    ]);
                    exit;
                }
            }

            // Ensure directory is writable
            if (!is_writable($target_dir)) {
                error_log("Directory not writable: $target_dir");
                chmod($target_dir, 0777);
                if (!is_writable($target_dir)) {
                    error_log("Still not writable after chmod: $target_dir");
                    ob_end_clean();
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Upload directory is not writable'
                    ]);
                    exit;
                }
            }

            // Check if file is an actual image
            $check = getimagesize($_FILES["product_image"]["tmp_name"]);
            if ($check === false) {
                error_log("File is not an image");
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'File is not an image'
                ]);
                exit;
            }

            // Check file size (max 5MB)
            if ($_FILES["product_image"]["size"] > 5000000) {
                error_log("File too large: " . $_FILES["product_image"]["size"]);
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'File is too large (max 5MB)'
                ]);
                exit;
            }

            // Allow only certain file formats
            $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
            if (!in_array($file_extension, $allowed_extensions)) {
                error_log("Invalid file type: $file_extension");
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'
                ]);
                exit;
            }

            // Try to upload file
            error_log("Moving uploaded file: " . $_FILES["product_image"]["tmp_name"] . " to $target_file");

            if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $error_message = "Failed to upload image";
                $upload_error = error_get_last();
                if ($upload_error) {
                    $error_message .= ": " . $upload_error['message'];
                }

                error_log($error_message);
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => $error_message,
                    'details' => [
                        'file_error' => $_FILES["product_image"]["error"],
                        'tmp_file_exists' => file_exists($_FILES["product_image"]["tmp_name"]) ? 'Yes' : 'No',
                        'target_exists' => file_exists($target_dir) ? 'Yes' : 'No',
                        'target_writable' => is_writable($target_dir) ? 'Yes' : 'No'
                    ]
                ]);
                exit;
            }

            error_log("File uploaded successfully to: $target_file");
        }

        // Create controller instance
        error_log("Creating product controller");
        $product_controller = new ProductController();

        // Add product
        error_log("Calling add_product_ctr with parameters: Cat=$product_cat, Brand=$product_brand, Title=$product_title, Price=$product_price, Image=$image_path");
        $result = $product_controller->add_product_ctr(
            $product_cat,
            $product_brand,
            $product_title,
            $product_price,
            $product_desc,
            $image_path,
            $product_keywords
        );

        if ($result) {
            error_log("Product added successfully");
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Product added successfully'
            ]);
        } else {
            error_log("Failed to add product through controller");

            // Remove uploaded image if product insertion fails
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                if (file_exists($target_file)) {
                    unlink($target_file);
                    error_log("Removed uploaded image due to product insertion failure");
                }
            }

            // Check for database tables
            try {
                $db = new db_connection();
                $conn = $db->db_conn();

                // Check product table
                $tables_result = mysqli_query($conn, "SHOW TABLES");
                $tables = [];
                while ($table = mysqli_fetch_array($tables_result)) {
                    $tables[] = $table[0];
                }
                error_log("Available tables: " . implode(", ", $tables));

                // Check if product table exists
                if (in_array('product', $tables)) {
                    // Check product table structure
                    $columns_result = mysqli_query($conn, "DESCRIBE product");
                    $columns = [];
                    while ($column = mysqli_fetch_assoc($columns_result)) {
                        $columns[] = $column['Field'] . " (" . $column['Type'] . ")";
                    }
                    error_log("Product table columns: " . implode(", ", $columns));

                    // Check if category and brand exist
                    if ($product_cat > 0) {
                        $cat_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories WHERE cat_id = $product_cat");
                        $cat_count = mysqli_fetch_assoc($cat_result)['count'];
                        error_log("Category ID $product_cat exists: " . ($cat_count > 0 ? 'Yes' : 'No'));
                    }

                    if ($product_brand > 0) {
                        $brand_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM brands WHERE brand_id = $product_brand");
                        $brand_count = mysqli_fetch_assoc($brand_result)['count'];
                        error_log("Brand ID $product_brand exists: " . ($brand_count > 0 ? 'Yes' : 'No'));
                    }
                } else {
                    error_log("Product table does not exist!");
                }
            } catch (Exception $e) {
                error_log("Database check error: " . $e->getMessage());
            }

            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to add product. Check the database connection and product controller.'
            ]);
        }
    } catch (Exception $e) {
        error_log("Exception in add_product.php: " . $e->getMessage());
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'An unexpected error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
