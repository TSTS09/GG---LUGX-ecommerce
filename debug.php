<?php
// Place this file in your root directory and name it troubleshoot.php

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once("Setting/db_class.php");
require_once("Classes/product_class.php");
require_once("Controllers/product_controller.php");

// Create direct database connection
$db = new db_connection();
$conn = $db->db_conn();

echo "<h1>Product Display Troubleshooting</h1>";

// Test 1: Direct SQL query without any filters
echo "<h2>Test 1: Direct SQL Query (Raw Results)</h2>";
$sql = "SELECT product_id, product_title, product_price FROM products ORDER BY product_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "<p>Error in direct query: " . mysqli_error($conn) . "</p>";
} else {
    echo "<p>Total products found: " . mysqli_num_rows($result) . "</p>";
    echo "<table border='1'><tr><th>ID</th><th>Title</th><th>Price</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['product_title'] . "</td>";
        echo "<td>$" . $row['product_price'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test 2: Testing ProductClass directly
echo "<h2>Test 2: ProductClass Methods</h2>";
$productClass = new ProductClass();

// Check get_products_count
$count = $productClass->get_products_count();
echo "<p>get_products_count() returns: $count</p>";

// Check get_all_products with no limits
$all_products = $productClass->get_all_products();
echo "<p>get_all_products() with no limits returns: " . count($all_products) . " products</p>";

// Check get_all_products with limit=5
$limited_products = $productClass->get_all_products('', 5);
echo "<p>get_all_products() with limit=5 returns: " . count($limited_products) . " products</p>";

// Check get_all_products with limit=5, offset=5
$paginated_products = $productClass->get_all_products('', 5, 5);
echo "<p>get_all_products() with limit=5, offset=5 returns: " . count($paginated_products) . " products</p>";

// Test 3: Testing ProductController
echo "<h2>Test 3: ProductController Methods</h2>";
$productController = new ProductController();

// Check get_all_products_ctr
$controller_results = $productController->get_all_products_ctr();
echo "<p>get_all_products_ctr() with default parameters returns: ";
echo "<br>success: " . ($controller_results['success'] ? 'true' : 'false');
echo "<br>data count: " . count($controller_results['data']);
echo "<br>total_count: " . $controller_results['total_count'];
echo "<br>total_pages: " . $controller_results['total_pages'];
echo "</p>";

// Check get_all_products_ctr with different page and limit
$controller_results_paginated = $productController->get_all_products_ctr('', 5, 2);
echo "<p>get_all_products_ctr('', 5, 2) returns: ";
echo "<br>success: " . ($controller_results_paginated['success'] ? 'true' : 'false');
echo "<br>data count: " . count($controller_results_paginated['data']);
echo "<br>total_count: " . $controller_results_paginated['total_count'];
echo "<br>total_pages: " . $controller_results_paginated['total_pages'];
echo "</p>";

// Show actual products returned by controller
echo "<h3>Products Returned by Controller (First Page)</h3>";
echo "<table border='1'><tr><th>ID</th><th>Title</th><th>Price</th></tr>";
if ($controller_results['success'] && !empty($controller_results['data'])) {
    foreach ($controller_results['data'] as $product) {
        echo "<tr>";
        echo "<td>" . $product['product_id'] . "</td>";
        echo "<td>" . $product['product_title'] . "</td>";
        echo "<td>$" . $product['product_price'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No products returned</td></tr>";
}
echo "</table>";

// Check database structure
echo "<h2>Test 4: Database Structure Check</h2>";
$tables_result = mysqli_query($conn, "SHOW TABLES");
echo "<p>Tables in database:</p><ul>";
while ($table = mysqli_fetch_array($tables_result)) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul>";

// Check products table structure
$columns_result = mysqli_query($conn, "DESCRIBE products");
echo "<p>Columns in products table:</p><ul>";
while ($column = mysqli_fetch_assoc($columns_result)) {
    echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
}
echo "</ul>";
?>