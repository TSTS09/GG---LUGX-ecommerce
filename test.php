
<?php
// test_db.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

require_once(__DIR__ . "/Setting/db_class.php");

try {
    $db = new db_connection();
    $conn = $db->db_conn();
    
    if (!$conn) {
        echo "<p style='color:red'>Database connection failed!</p>";
        exit;
    }
    
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Test a simple query
    $query = "SELECT 1 as test";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo "<p style='color:red'>Simple query failed: " . mysqli_error($conn) . "</p>";
        exit;
    }
    
    $row = mysqli_fetch_assoc($result);
    echo "<p>Simple query result: " . $row['test'] . "</p>";
    
    // Check if product table exists
    echo "<h2>Product Table Check</h2>";
    $tables_result = mysqli_query($conn, "SHOW TABLES LIKE 'product'");
    if (mysqli_num_rows($tables_result) == 0) {
        echo "<p style='color:red'>Product table does not exist in the database!</p>";
        
        // Show all tables
        echo "<h3>Available Tables:</h3>";
        $all_tables = mysqli_query($conn, "SHOW TABLES");
        echo "<ul>";
        while ($table = mysqli_fetch_array($all_tables)) {
            echo "<li>" . $table[0] . "</li>";
        }
        echo "</ul>";
        exit;
    }
    
    echo "<p style='color:green'>Product table exists!</p>";
    
    // Get product table structure
    $structure_result = mysqli_query($conn, "DESCRIBE product");
    echo "<h3>Product Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($field = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . $field['Key'] . "</td>";
        echo "<td>" . $field['Default'] . "</td>";
        echo "<td>" . $field['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check categories and brands (foreign keys)
    echo "<h2>Foreign Key Tables Check</h2>";
    
    // Check categories
    $cat_result = mysqli_query($conn, "SHOW TABLES LIKE 'categories'");
    if (mysqli_num_rows($cat_result) == 0) {
        echo "<p style='color:red'>Categories table does not exist!</p>";
    } else {
        echo "<p style='color:green'>Categories table exists!</p>";
        
        // Count categories
        $cat_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
        $count = mysqli_fetch_assoc($cat_count)['count'];
        echo "<p>Number of categories: " . $count . "</p>";
        
        if ($count > 0) {
            // Show first few categories
            $cats = mysqli_query($conn, "SELECT * FROM categories LIMIT 5");
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Name</th></tr>";
            while ($cat = mysqli_fetch_assoc($cats)) {
                echo "<tr>";
                echo "<td>" . $cat['cat_id'] . "</td>";
                echo "<td>" . $cat['cat_name'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Check brands
    $brand_result = mysqli_query($conn, "SHOW TABLES LIKE 'brands'");
    if (mysqli_num_rows($brand_result) == 0) {
        echo "<p style='color:red'>Brands table does not exist!</p>";
    } else {
        echo "<p style='color:green'>Brands table exists!</p>";
        
        // Count brands
        $brand_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM brands");
        $count = mysqli_fetch_assoc($brand_count)['count'];
        echo "<p>Number of brands: " . $count . "</p>";
        
        if ($count > 0) {
            // Show first few brands
            $brands = mysqli_query($conn, "SELECT * FROM brands LIMIT 5");
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Name</th></tr>";
            while ($brand = mysqli_fetch_assoc($brands)) {
                echo "<tr>";
                echo "<td>" . $brand['brand_id'] . "</td>";
                echo "<td>" . $brand['brand_name'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Test product insertion
    echo "<h2>Direct Product Insertion Test</h2>";
    
    // Get first category ID
    $first_cat_id = 0;
    $cat_query = mysqli_query($conn, "SELECT cat_id FROM categories LIMIT 1");
    if ($cat_query && mysqli_num_rows($cat_query) > 0) {
        $first_cat_id = mysqli_fetch_assoc($cat_query)['cat_id'];
    }
    
    // Get first brand ID
    $first_brand_id = 0;
    $brand_query = mysqli_query($conn, "SELECT brand_id FROM brands LIMIT 1");
    if ($brand_query && mysqli_num_rows($brand_query) > 0) {
        $first_brand_id = mysqli_fetch_assoc($brand_query)['brand_id'];
    }
    
    echo "<p>Using category ID: " . $first_cat_id . "</p>";
    echo "<p>Using brand ID: " . $first_brand_id . "</p>";
    
    if ($first_cat_id > 0 && $first_brand_id > 0) {
        // Create test product data
        $test_title = "Test Product " . time();
        $test_price = 19.99;
        $test_desc = "This is a test product";
        $test_image = "../Images/product/default.jpg";
        $test_keywords = "test, product";
        
        // Build and show INSERT query
        $insert_query = "INSERT INTO product (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords) 
                         VALUES ($first_cat_id, $first_brand_id, '$test_title', $test_price, '$test_desc', '$test_image', '$test_keywords')";
        
        echo "<p>Test query: " . htmlspecialchars($insert_query) . "</p>";
        
        // Try executing the query
        $insert_result = mysqli_query($conn, $insert_query);
        if ($insert_result) {
            $new_id = mysqli_insert_id($conn);
            echo "<p style='color:green'>Product inserted successfully with ID: " . $new_id . "</p>";
        } else {
            echo "<p style='color:red'>Product insertion failed: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color:red'>Cannot test product insertion - no categories or brands found!</p>";
        echo "<p>Please add at least one category and one brand first.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>