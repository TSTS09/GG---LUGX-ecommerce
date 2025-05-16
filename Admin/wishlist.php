<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

require_once("../Controllers/wishlist_controller.php");
require_once("../Controllers/product_controller.php");
require_once("../Controllers/customer_controller.php");

// Create controller instances
$wishlist_controller = new WishlistController();
$product_controller = new ProductController();
$customer_controller = new CustomerController();

/**
 * Get previous period wishlist counts for comparison
 * @param object $conn Database connection
 * @param int $days Number of days to look back
 * @return array Product IDs with previous counts
 */
function getPreviousWishlistCounts($conn, $days = 7) {
    $previous_counts = [];
    
    // Get date from X days ago
    $date = date('Y-m-d', strtotime("-$days days"));
    
    // Query wishlist items from before that date
    $sql = "SELECT p_id, COUNT(*) as count 
            FROM wishlist 
            WHERE date_added < '$date' 
            GROUP BY p_id";
            
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $previous_counts[$row['p_id']] = $row['count'];
        }
    }
    
    return $previous_counts;
}

// Helper function to calculate wishlist analytics
function getWishlistAnalytics($conn) {
    $analytics = [
        'total_wishlists' => 0,
        'products_in_wishlists' => 0,
        'most_popular_products' => [],
        'most_popular_categories' => [],
        'most_popular_brands' => [],
        'guest_vs_registered' => ['guest' => 0, 'registered' => 0],
        'wishlist_by_date' => [],
        'conversion_rate' => 0, // Products added to wishlist that were later purchased
        'recent_wishlisted_products' => [],
    ];

    // Total wishlists (count unique users with at least one wishlist item)
    $sql = "SELECT 
                COUNT(DISTINCT c_id) AS registered_users, 
                COUNT(DISTINCT guest_session_id) AS guest_users 
            FROM wishlist 
            WHERE c_id IS NOT NULL OR guest_session_id IS NOT NULL";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $analytics['guest_vs_registered']['registered'] = $row['registered_users'];
        $analytics['guest_vs_registered']['guest'] = $row['guest_users'];
        $analytics['total_wishlists'] = $row['registered_users'] + $row['guest_users'];
    }

    // Total products in wishlists
    $sql = "SELECT COUNT(*) AS total FROM wishlist";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $analytics['products_in_wishlists'] = $row['total'];
    }

    // Most popular products in wishlists - excluding deleted products
    $sql = "SELECT w.p_id, p.product_title, p.product_image, p.product_price, COUNT(*) AS count
            FROM wishlist w
            JOIN products p ON w.p_id = p.product_id
            WHERE p.deleted = 0 OR p.deleted IS NULL
            GROUP BY w.p_id
            ORDER BY count DESC
            LIMIT 10";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $analytics['most_popular_products'][] = $row;
    }

    // Get previous counts for comparison
    $previous_counts = getPreviousWishlistCounts($conn);

    // Calculate percentage changes for popular products
    foreach ($analytics['most_popular_products'] as &$product) {
        $product_id = $product['p_id'];
        $current_count = $product['count'];
        $previous_count = isset($previous_counts[$product_id]) ? $previous_counts[$product_id] : 0;
        
        // Calculate percentage change
        if ($previous_count > 0) {
            $change = (($current_count - $previous_count) / $previous_count) * 100;
        } else {
            // If no previous data, consider it 100% new
            $change = $current_count > 0 ? 100 : 0;
        }
        
        $product['percentage_change'] = round($change, 1);
    }

    // Most popular categories in wishlists
    $sql = "SELECT c.cat_id, c.cat_name, COUNT(*) AS count
            FROM wishlist w
            JOIN products p ON w.p_id = p.product_id
            JOIN categories c ON p.product_cat = c.cat_id
            GROUP BY c.cat_id
            ORDER BY count DESC
            LIMIT 5";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $analytics['most_popular_categories'][] = $row;
    }

    // Most popular brands in wishlists
    $sql = "SELECT b.brand_id, b.brand_name, COUNT(*) AS count
            FROM wishlist w
            JOIN products p ON w.p_id = p.product_id
            JOIN brands b ON p.product_brand = b.brand_id
            GROUP BY b.brand_id
            ORDER BY count DESC
            LIMIT 5";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $analytics['most_popular_brands'][] = $row;
    }

    // Wishlists by date (for trend chart)
    $sql = "SELECT DATE(date_added) AS date, COUNT(*) AS count
            FROM wishlist
            GROUP BY DATE(date_added)
            ORDER BY date
            LIMIT 30";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $analytics['wishlist_by_date'][] = $row;
    }

    // Recently added wishlist items
    $sql = "SELECT w.*, p.product_title, p.product_image, p.product_price, c.customer_name
            FROM wishlist w
            LEFT JOIN products p ON w.p_id = p.product_id
            LEFT JOIN customer c ON w.c_id = c.customer_id
            ORDER BY w.date_added DESC
            LIMIT 10";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $analytics['recent_wishlisted_products'][] = $row;
    }

    // Calculate conversion rate (products in wishlists that were purchased)
    // This is a more complex query that joins wishlist items with order details
    $sql = "SELECT COUNT(DISTINCT od.product_id) AS purchased_count
            FROM orderdetails od
            JOIN wishlist w ON od.product_id = w.p_id
            WHERE 
                (w.c_id IS NOT NULL AND w.c_id IN (SELECT customer_id FROM orders o WHERE o.order_id = od.order_id))
                OR (w.date_added < (SELECT o.order_date FROM orders o WHERE o.order_id = od.order_id))";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $purchased_count = $row['purchased_count'];
        if ($analytics['products_in_wishlists'] > 0) {
            $analytics['conversion_rate'] = round(($purchased_count / $analytics['products_in_wishlists']) * 100, 2);
        }
    }

    return $analytics;
}

// Get database connection
$db = new db_connection();
$conn = $db->db_conn();

// Get wishlist analytics
$analytics = getWishlistAnalytics($conn);

// Prepare data for charts
$categoryLabels = [];
$categoryData = [];
foreach ($analytics['most_popular_categories'] as $category) {
    $categoryLabels[] = $category['cat_name'];
    $categoryData[] = $category['count'];
}

$brandLabels = [];
$brandData = [];
foreach ($analytics['most_popular_brands'] as $brand) {
    $brandLabels[] = $brand['brand_name'];
    $brandData[] = $brand['count'];
}

$dateLabels = [];
$dateData = [];
foreach ($analytics['wishlist_by_date'] as $date) {
    $dateLabels[] = date('M d', strtotime($date['date']));
    $dateData[] = $date['count'];
}

// JSON encode data for JavaScript charts
$categoryChartData = json_encode([
    'labels' => $categoryLabels,
    'data' => $categoryData
]);

$brandChartData = json_encode([
    'labels' => $brandLabels,
    'data' => $brandData
]);

$dateChartData = json_encode([
    'labels' => $dateLabels,
    'data' => $dateData
]);

$userTypeData = json_encode([
    'labels' => ['Registered Users', 'Guest Users'],
    'data' => [
        $analytics['guest_vs_registered']['registered'],
        $analytics['guest_vs_registered']['guest']
    ]
]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Analytics - Admin Panel</title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/admin-styles.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="../CSS/wishlist.css">
    <link rel="icon" href="../Images/logo.png" type="image/png">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .trend-indicator {
            font-size: 12px;
            font-weight: bold;
            padding-left: 5px;
        }
        .trend-up {
            color: #28a745;
        }
        .trend-down {
            color: #dc3545;
        }
        .trend-neutral {
            color: #6c757d;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include_once('../includes/header.php'); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Wishlist Analytics Dashboard</h2>
            <p>Gain insights into customer preferences and wishlist behaviors</p>
        </div>

        <!-- Summary Statistics -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <i class="fa fa-heart card-icon"></i>
                    <h3>Total Wishlists</h3>
                    <div class="stats-number"><?php echo $analytics['total_wishlists']; ?></div>
                    <div class="stats-label">Unique users with wishlists</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <i class="fa fa-gamepad card-icon"></i>
                    <h3>Wishlisted Products</h3>
                    <div class="stats-number"><?php echo $analytics['products_in_wishlists']; ?></div>
                    <div class="stats-label">Total products in wishlists</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <i class="fa fa-shopping-cart card-icon"></i>
                    <h3>Conversion Rate</h3>
                    <div class="stats-number"><?php echo $analytics['conversion_rate']; ?>%</div>
                    <div class="stats-label">Wishlisted products purchased</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <i class="fa fa-user card-icon"></i>
                    <h3>Registered vs Guest</h3>
                    <div class="stats-number"><?php echo $analytics['guest_vs_registered']['registered']; ?> / <?php echo $analytics['guest_vs_registered']['guest']; ?></div>
                    <div class="stats-label">Registered users vs Guest users</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-lg-6">
                <div class="stats-card">
                    <h3>Most Popular Categories in Wishlists</h3>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="stats-card">
                    <h3>Most Popular Brands in Wishlists</h3>
                    <div class="chart-container">
                        <canvas id="brandChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="stats-card">
                    <h3>Wishlist Trends (Last 30 Days)</h3>
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stats-card">
                    <h3>User Type Distribution</h3>
                    <div class="chart-container">
                        <canvas id="userTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Products Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="stats-card">
                    <h3>Most Popular Products in Wishlists</h3>
                    <div class="table-responsive">
                        <table class="table table-striped products-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Wishlist Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($analytics['most_popular_products'] as $product): 
                                ?>
                                <tr>
                                    <td>#<?php echo $rank++; ?></td>
                                    <td><img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>"></td>
                                    <td><?php echo $product['product_title']; ?></td>
                                    <td>$<?php echo number_format($product['product_price'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $product['count']; ?></span>
                                        <?php if (isset($product['percentage_change'])): ?>
                                            <?php if ($product['percentage_change'] > 0): ?>
                                                <span class="trend-indicator trend-up">+<?php echo $product['percentage_change']; ?>%</span>
                                            <?php elseif ($product['percentage_change'] < 0): ?>
                                                <span class="trend-indicator trend-down"><?php echo $product['percentage_change']; ?>%</span>
                                            <?php else: ?>
                                                <span class="trend-indicator trend-neutral">0%</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../View/single_product.php?id=<?php echo $product['p_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        <a href="#" class="btn btn-sm btn-secondary">Promote</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Wishlist Activity -->
        <div class="row">
            <div class="col-lg-12">
                <div class="stats-card">
                    <h3>Recent Wishlist Activity</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>User</th>
                                    <th>Date Added</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analytics['recent_wishlisted_products'] as $item): ?>
                                <tr>
                                    <td><img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_title']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;"></td>
                                    <td><?php echo $item['product_title']; ?></td>
                                    <td>
                                        <?php if ($item['c_id']): ?>
                                            <?php echo $item['customer_name']; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Guest User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($item['date_added'])); ?></td>
                                    <td>
                                        <?php if ($item['c_id']): ?>
                                            <span class="badge badge-success">Registered</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Guest</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Category Chart
            const categoryData = <?php echo $categoryChartData; ?>;
            const ctxCategory = document.getElementById('categoryChart').getContext('2d');
            new Chart(ctxCategory, {
                type: 'bar',
                data: {
                    labels: categoryData.labels,
                    datasets: [{
                        label: 'Wishlist Count',
                        data: categoryData.data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Brand Chart
            const brandData = <?php echo $brandChartData; ?>;
            const ctxBrand = document.getElementById('brandChart').getContext('2d');
            new Chart(ctxBrand, {
                type: 'doughnut',
                data: {
                    labels: brandData.labels,
                    datasets: [{
                        label: 'Wishlist Count',
                        data: brandData.data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });

            // Trend Chart
            const trendData = <?php echo $dateChartData; ?>;
            const ctxTrend = document.getElementById('trendChart').getContext('2d');
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [{
                        label: 'Daily Wishlist Additions',
                        data: trendData.data,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // User Type Chart
            const userTypeData = <?php echo $userTypeData; ?>;
            const ctxUserType = document.getElementById('userTypeChart').getContext('2d');
            new Chart(ctxUserType, {
                type: 'pie',
                data: {
                    labels: userTypeData.labels,
                    datasets: [{
                        label: 'Users',
                        data: userTypeData.data,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>