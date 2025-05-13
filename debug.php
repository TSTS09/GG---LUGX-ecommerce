<?php
// Diagnostic script to directly check your database
// Save this as debug_products.php in your root directory

// Include database configuration
require_once("Setting/db_cred.php");

// Set up error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to connect to database
function connectDB() {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

// Get connection
$conn = connectDB();
echo "<h1>Database Diagnostic Results</h1>";

// Check products table
echo "<h2>Products Table</h2>";
$sql = "SELECT COUNT(*) as count FROM products";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
echo "Total products in database: " . $row['count'] . "<br>";

// Get all products directly, without JOINs
echo "<h3>Direct Product Query (No JOINs)</h3>";
$sql = "SELECT * FROM products ORDER BY product_id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Error: " . mysqli_error($conn) . "<br>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>Category ID</th><th>Brand ID</th><th>Price</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['product_title'] . "</td>";
        echo "<td>" . $row['product_cat'] . "</td>";
        echo "<td>" . $row['product_brand'] . "</td>";
        echo "<td>$" . $row['product_price'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Check products with JOINs
echo "<h3>Product Query With JOINs</h3>";
$sql = "SELECT p.*, c.cat_name, b.brand_name 
        FROM products p 
        LEFT JOIN categories c ON p.product_cat = c.cat_id 
        LEFT JOIN brands b ON p.product_brand = b.brand_id
        ORDER BY p.product_id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Error: " . mysqli_error($conn) . "<br>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>Category</th><th>Brand</th><th>Price</th></tr>";
    
    $count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $count++;
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['product_title'] . "</td>";
        echo "<td>" . $row['cat_name'] . "</td>";
        echo "<td>" . $row['brand_name'] . "</td>";
        echo "<td>$" . $row['product_price'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "Total products with JOINs: " . $count . "<br>";
}

// Check for any product_status references in the code
$files = [
    'Classes/product_class.php',
    'Controllers/product_controller.php',
    'View/all_product.php',
    'Admin/product.php'
];

echo "<h2>Code Check for 'product_status'</h2>";
echo "<ul>";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $matches = [];
        preg_match_all('/product_status/i', $content, $matches);
        
        echo "<li>$file: " . count($matches[0]) . " instances of 'product_status'</li>";
        
        if (count($matches[0]) > 0) {
            echo "<ul>";
            // Get context for each match (10 chars before and after)
            preg_match_all('/.{0,30}product_status.{0,30}/i', $content, $contexts);
            foreach ($contexts[0] as $context) {
                echo "<li><code>" . htmlspecialchars($context) . "</code></li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<li>$file: File not found</li>";
    }
}

echo "</ul>";

// Close connection
mysqli_close($conn);
?>