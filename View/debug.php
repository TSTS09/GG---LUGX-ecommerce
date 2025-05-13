<?php
// Create this as a new file called debug_products.php in your View directory

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once("../Setting/core.php");
require_once("../Controllers/product_controller.php");

// Start debug log
$debug_file = "../Error/direct_products_debug.log";
file_put_contents($debug_file, "\n\n--- New Debug Run " . date("Y-m-d H:i:s") . " ---\n", FILE_APPEND);

// Create a new product controller
$product_controller = new ProductController();

// Direct database access for verification
try {
    // Get a database connection directly
    require_once("../Setting/db_class.php");
    $db = new db_connection();
    $conn = $db->db_conn();
    
    // Log connection status
    file_put_contents($debug_file, "DB Connection: " . ($conn ? "Success" : "Failed") . "\n", FILE_APPEND);
    
    // Check if products table exists and how many records it has
    $check_query = "SHOW TABLES LIKE 'products'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        file_put_contents($debug_file, "Products table exists\n", FILE_APPEND);
        
        // Count records
        $count_query = "SELECT COUNT(*) as total FROM products";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        file_put_contents($debug_file, "Total products in database: " . $count_row['total'] . "\n", FILE_APPEND);
        
        // Get first 5 records to verify data
        $sample_query = "SELECT * FROM products LIMIT 5";
        $sample_result = mysqli_query($conn, $sample_query);
        
        file_put_contents($debug_file, "Sample products from direct query:\n", FILE_APPEND);
        while($row = mysqli_fetch_assoc($sample_result)) {
            file_put_contents($debug_file, "ID: " . $row['product_id'] . ", Title: " . $row['product_title'] . "\n", FILE_APPEND);
        }
    } else {
        file_put_contents($debug_file, "Products table does not exist!\n", FILE_APPEND);
        
        // Check what tables do exist
        $tables_query = "SHOW TABLES";
        $tables_result = mysqli_query($conn, $tables_query);
        
        file_put_contents($debug_file, "Available tables:\n", FILE_APPEND);
        while($row = mysqli_fetch_row($tables_result)) {
            file_put_contents($debug_file, $row[0] . "\n", FILE_APPEND);
        }
    }
} catch (Exception $e) {
    file_put_contents($debug_file, "Database error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Now try the controller method
try {
    // Try both get_all_products and search_products
    file_put_contents($debug_file, "\nTesting controller methods:\n", FILE_APPEND);
    
    // Test get_all_products_ctr
    $products = $product_controller->get_all_products_ctr();
    file_put_contents($debug_file, "get_all_products_ctr result: " . 
        (isset($products['success']) ? ($products['success'] ? 'success' : 'failed') : 'unknown status') . "\n", FILE_APPEND);
    file_put_contents($debug_file, "Product count from controller: " . 
        (isset($products['data']) ? count($products['data']) : 'data not available') . "\n", FILE_APPEND);
    
    // Output a sample of the products
    if(isset($products['data']) && !empty($products['data'])) {
        file_put_contents($debug_file, "First product from controller: " . json_encode($products['data'][0]) . "\n", FILE_APPEND);
    } else {
        file_put_contents($debug_file, "No products returned from controller\n", FILE_APPEND);
        
        // Additional error info
        if(isset($products['message'])) {
            file_put_contents($debug_file, "Controller error message: " . $products['message'] . "\n", FILE_APPEND);
        }
    }
} catch (Exception $e) {
    file_put_contents($debug_file, "Controller error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Display output on screen
echo "<h1>Debug Product Data</h1>";
echo "<p>Check the debug log file at: " . $debug_file . "</p>";

// Display direct database query results
echo "<h2>Direct Database Query Results</h2>";
try {
    $direct_query = "SELECT * FROM products LIMIT 20";
    $direct_result = mysqli_query($conn, $direct_query);
    
    if($direct_result && mysqli_num_rows($direct_result) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Title</th><th>Price</th><th>Category</th><th>Brand</th></tr>";
        
        while($row = mysqli_fetch_assoc($direct_result)) {
            echo "<tr>";
            echo "<td>" . $row['product_id'] . "</td>";
            echo "<td>" . $row['product_title'] . "</td>";
            echo "<td>$" . $row['product_price'] . "</td>";
            echo "<td>" . $row['product_cat'] . "</td>";
            echo "<td>" . $row['product_brand'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No products found or query error.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Display controller results
echo "<h2>Controller Results</h2>";
if(isset($products['data']) && !empty($products['data'])) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>Price</th><th>Category</th><th>Brand</th></tr>";
    
    foreach($products['data'] as $product) {
        echo "<tr>";
        echo "<td>" . $product['product_id'] . "</td>";
        echo "<td>" . $product['product_title'] . "</td>";
        echo "<td>$" . $product['product_price'] . "</td>";
        echo "<td>" . $product['cat_name'] . "</td>";
        echo "<td>" . $product['brand_name'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No products found from controller.</p>";
}