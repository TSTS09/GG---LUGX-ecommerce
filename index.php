<?php
// Include core file for session management and authentication functions
require_once("Setting/core.php");

// If user is logged in but somehow ends up here without active session, redirect
if (!is_logged_in() && isset($_COOKIE['user_logged_in'])) {
    // Clear cookie and redirect to login
    setcookie('user_logged_in', '', time() - 3600, '/');
    header("Location: Login/login.php");
    exit;
}

// Include necessary controllers
require_once("Controllers/product_controller.php");
$productController = new ProductController();

// Get products data
$featured_products = $productController->get_featured_products_ctr(4);
$bestseller_products = $productController->get_featured_products_ctr(6);
$all_categories = $productController->get_all_categories_ctr();

// Set page title
$page_title = "GG - LUGX Gaming";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <title><?php echo $page_title; ?></title>

  <!-- Bootstrap core CSS -->
  <link href="JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Additional CSS Files -->
  <link rel="stylesheet" href="CSS/fontawesome.css">
  <link rel="stylesheet" href="CSS/templatemo-lugx-gaming.css">
  <link rel="stylesheet" href="CSS/owl.css">
  <link rel="stylesheet" href="CSS/animate.css">
  <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
  <link rel="icon" href="Images/logo.png" type="image/png">

  <style>
    /* Fix for footer positioning */
    html, body {
      height: 100%;
      margin: 0;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-content {
      flex: 1 0 auto;
      padding-bottom: 30px;
    }

    footer {
      flex-shrink: 0;
      width: 100% !important;
      margin-top: auto !important;
    }
    
    /* Fix header text visibility */
    .header-area .main-nav .nav li a {
      color: #333333 !important;
      font-weight: 500;
    }
    
    .header-area .main-nav .nav li a.active {
      color: #ee626b !important;
      font-weight: 600;
    }
    
    .header-area {
      background-color: #f8f8f8;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .user-greeting {
      color: #333333;
      font-weight: 600;
      margin-right: 15px;
    }
  </style>
</head>

<body>
  <!-- ***** Preloader Start ***** -->
  <div id="js-preloader" class="js-preloader">
    <div class="preloader-inner">
      <span class="dot"></span>
      <div class="dots">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </div>
  <!-- ***** Preloader End ***** -->

  <!-- ***** Header Area Start ***** -->
  <header class="header-area header-sticky">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <nav class="main-nav">
            <!-- Logo -->
            <a href="index.php" class="logo">
              <img src="Images/logo.png" alt="GG-LUGX" style="width: 158px;">
            </a>
            
            <!-- Main Menu -->
            <ul class="nav">
              <?php if (is_logged_in() && is_admin()): ?>
                <!-- Admin Navigation Menu -->
                <li><a href="index.php" class= "active">Home</a></li>
                <li><a href="Admin/brand.php">Brands</a></li>
                <li><a href="Admin/category.php">Categories</a></li>
                <li><a href="Admin/product.php">Manage Products</a></li>
                <li><a href="View/orders.php">Orders</a></li>
                <li><a href="Actions/logout.php">Logout</a></li>
              <?php elseif (is_logged_in()): ?>
                <!-- Regular User Navigation Menu -->
                <li><a href="index.php" class= "active">Home</a></li>
                <li><a href="View/all_product.php">Products</a></li>
                <li><a href="View/cart.php">
                  <i class="fa fa-shopping-cart"></i> Cart
                </a></li>
                <li><a href="View/orders.php">My Orders</a></li>
                <li><a href="View/contact.php">Contact Us</a></li>
                <li><a href="Actions/logout.php">Logout</a></li>
              <?php else: ?>
                <!-- Not Logged In Navigation Menu -->
                <li><a href="index.php" class= "active">Home</a></li>
                <li><a href="View/all_product.php">Our Shop</a></li>
                <li><a href="View/contact.php">Contact Us</a></li>
                <li><a href="Login/login.php">Sign In/Register</a></li>
              <?php endif; ?>
              
              <!-- Username display if logged in -->
              <?php if (is_logged_in()): ?>
                <li>
                  <span class="user-greeting">
                    Hello, <?php echo isset($_SESSION['customer_name']) ? htmlspecialchars($_SESSION['customer_name']) : 'User'; ?>
                  </span>
                </li>
              <?php endif; ?>
            </ul>
            
            <a class='menu-trigger'>
              <span>Menu</span>
            </a>
          </nav>
        </div>
      </div>
    </div>
  </header>
  <!-- ***** Header Area End ***** -->

  <div class="main-content">
    <div class="main-banner">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 align-self-center">
            <div class="caption header-text">
              <h6>Welcome to GG - LUGX</h6>
              <h2>YOUR ULTIMATE GAMING DESTINATION!</h2>
              <p>GG - LUGX is your one-stop shop for all gaming needs. From AAA titles to indie gems, plus all the equipment that serious gamers demand. Elevate your gaming experience with our premium selection of games and gear.</p>
              <div class="search-input">
                <?php if (is_logged_in()): ?>
                  <!-- Search form for logged in users -->
                  <form id="search" action="View/product_search_result.php" method="GET">
                    <input type="text" placeholder="Type Something" id='searchText' name="search" />
                    <button type="submit">Search Now</button>
                  </form>
                <?php else: ?>
                  <!-- Search form for non-logged in users with login redirect -->
                  <form id="search" onsubmit="redirectToLogin(event)">
                    <input type="text" placeholder="Type Something" id='searchText' name="searchKeyword" />
                    <button type="submit">Search Now</button>
                  </form>
                  <script>
                    function redirectToLogin(event) {
                      event.preventDefault();
                      window.location.href = 'Login/login.php?redirect=search';
                    }
                  </script>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-lg-4 offset-lg-2">
            <div class="right-image">
              <img src="Images/banner-image.jpg" alt="">
              <span class="price">$22</span>
              <span class="offer">-40%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="features">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6">
            <a href="#">
              <div class="item">
                <div class="image">
                  <img src="Images/featured-01.png" alt="" style="max-width: 44px;">
                </div>
                <h4>Fast Delivery</h4>
              </div>
            </a>
          </div>
          <div class="col-lg-3 col-md-6">
            <a href="#">
              <div class="item">
                <div class="image">
                  <img src="Images/featured-02.png" alt="" style="max-width: 44px;">
                </div>
                <h4>Pre-Order Bonus</h4>
              </div>
            </a>
          </div>
          <div class="col-lg-3 col-md-6">
            <a href="#">
              <div class="item">
                <div class="image">
                  <img src="Images/featured-03.png" alt="" style="max-width: 44px;">
                </div>
                <h4>24/7 Support</h4>
              </div>
            </a>
          </div>
          <div class="col-lg-3 col-md-6">
            <a href="#">
              <div class="item">
                <div class="image">
                  <img src="Images/featured-04.png" alt="" style="max-width: 44px;">
                </div>
                <h4>Rewards Program</h4>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="section trending">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div class="section-heading">
              <h6>New Arrivals</h6>
              <h2>Featured Games</h2>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="main-button">
              <a href="View/all_product.php">View All</a>
            </div>
          </div>
          
          <?php
          // Display featured products
          if ($featured_products['success'] && !empty($featured_products['data'])) {
              foreach ($featured_products['data'] as $product) {
          ?>
              <div class="col-lg-3 col-md-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=<?php echo $product['product_id']; ?>">
                      <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>">
                    </a>
                    <span class="price">$<?php echo number_format($product['product_price'], 2); ?></span>
                  </div>
                  <div class="down-content">
                    <span class="category"><?php echo $product['cat_name']; ?></span>
                    <h4><?php echo $product['product_title']; ?></h4>
                    <a href="Actions/add_to_cart.php?id=<?php echo $product['product_id']; ?>"><i class="fa fa-shopping-bag"></i></a>
                  </div>
                </div>
              </div>
          <?php
              }
          } else {
              // Fallback static content
          ?>
              <div class="col-lg-3 col-md-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=1"><img src="Images/trending-01.jpg" alt=""></a>
                    <span class="price"><em>$28</em>$20</span>
                  </div>
                  <div class="down-content">
                    <span class="category">RPG</span>
                    <h4>Elden Ring</h4>
                    <a href="View/single_product.php?id=1"><i class="fa fa-shopping-bag"></i></a>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=2"><img src="Images/trending-02.jpg" alt=""></a>
                    <span class="price">$44</span>
                  </div>
                  <div class="down-content">
                    <span class="category">Strategy</span>
                    <h4>Civilization VI</h4>
                    <a href="View/single_product.php?id=2"><i class="fa fa-shopping-bag"></i></a>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=3"><img src="Images/trending-03.jpg" alt=""></a>
                    <span class="price"><em>$64</em>$44</span>
                  </div>
                  <div class="down-content">
                    <span class="category">Indie</span>
                    <h4>Hollow Knight</h4>
                    <a href="View/single_product.php?id=3"><i class="fa fa-shopping-bag"></i></a>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=4"><img src="Images/trending-04.jpg" alt=""></a>
                    <span class="price">$32</span>
                  </div>
                  <div class="down-content">
                    <span class="category">Action</span>
                    <h4>God of War</h4>
                    <a href="View/single_product.php?id=4"><i class="fa fa-shopping-bag"></i></a>
                  </div>
                </div>
              </div>
          <?php
          }
          ?>
        </div>
      </div>
    </div>

    <div class="section most-played">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div class="section-heading">
              <h6>BESTSELLERS</h6>
              <h2>Top Picks</h2>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="main-button">
              <a href="View/all_product.php">View All</a>
            </div>
          </div>
          
          <?php
          // Display bestseller products
          if ($bestseller_products['success'] && !empty($bestseller_products['data'])) {
              foreach ($bestseller_products['data'] as $product) {
                  // Only display up to 6 products
                  if (isset($count) && $count >= 6) break;
                  $count = isset($count) ? $count + 1 : 1;
          ?>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=<?php echo $product['product_id']; ?>"><img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_title']; ?>"></a>
                  </div>
                  <div class="down-content">
                    <span class="category"><?php echo $product['cat_name']; ?></span>
                    <h4><?php echo $product['product_title']; ?></h4>
                    <a href="View/single_product.php?id=<?php echo $product['product_id']; ?>">Explore</a>
                  </div>
                </div>
              </div>
          <?php
              }
          } else {
              // Fallback static content
          ?>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=1"><img src="Images/top-game-01.jpg" alt=""></a>
                  </div>
                  <div class="down-content">
                    <span class="category">FPS</span>
                    <h4>Warframe VB</h4>
                    <a href="View/single_product.php?id=1">Explore</a>
                  </div>
                </div>
              </div>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=2"><img src="Images/top-game-02.jpg" alt=""></a>
                  </div>
                  <div class="down-content">
                    <span class="category">Open World</span>
                    <h4>PUBG battleground</h4>
                    <a href="View/single_product.php?id=2">Explore</a>
                  </div>
                </div>
              </div>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=3"><img src="Images/top-game-03.jpg" alt=""></a>
                  </div>
                  <div class="down-content">
                    <span class="category">Open World</span>
                    <h4>Apex Legend</h4>
                    <a href="View/single_product.php?id=3">Explore</a>
                  </div>
                </div>
              </div>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=4"><img src="Images/top-game-04.jpg" alt=""></a>
                  </div>
                  <div class="down-content">
                    <span class="category">Simulation</span>
                    <h4>The Sims 4</h4>
                    <a href="View/single_product.php?id=4">Explore</a>
                  </div>
                </div>
              </div>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=5"><img src="Images/top-game-05.jpg" alt=""></a>
                  </div>
                  <div class="down-content">
                    <span class="category">Open World</span>
                    <h4>Lost Ark</h4>
                    <a href="View/single_product.php?id=5">Explore</a>
                  </div>
                </div>
              </div>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <div class="thumb">
                    <a href="View/single_product.php?id=6"><img src="Images/top-game-06.jpg" alt=""></a>
                  </div>
                  <div class="down-content">
                    <span class="category">Adventure</span>
                    <h4>Destiny 2</h4>
                    <a href="View/single_product.php?id=6">Explore</a>
                  </div>
                </div>
              </div>
          <?php
          }
          ?>
        </div>
      </div>
    </div>

    <div class="section categories">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <div class="section-heading">
              <h6>Browse By</h6>
              <h2>Game Categories</h2>
            </div>
          </div>
          
          <?php
          // Display categories
          if ($all_categories['success'] && !empty($all_categories['data'])) {
              // Use the category layout from the template
              echo '<div class="row">';
              $counter = 0;
              foreach ($all_categories['data'] as $category) {
                  // Only display up to 5 categories
                  if ($counter >= 5) break;
                  $counter++;
                  
                  // Image and CSS class
                  $imagePath = "Images/categories-0" . $counter . ".jpg";
          ?>
              <div class="col-lg col-sm-6 col-xs-12">
                <div class="item">
                  <h4><?php echo $category['cat_name']; ?></h4>
                  <div class="thumb">
                    <a href="View/all_product.php?category=<?php echo $category['cat_id']; ?>">
                      <img src="<?php echo $imagePath; ?>" alt="<?php echo $category['cat_name']; ?>">
                    </a>
                  </div>
                </div>
              </div>
          <?php
              }
              echo '</div>';
          } else {
              // Fallback static content
          ?>
              <div class="col-lg col-sm-6 col-xs-12">
                <div class="item">
                  <h4>Action</h4>
                  <div class="thumb">
                    <a href="View/all_product.php?category=1"><img src="Images/categories-01.jpg" alt=""></a>
                  </div>
                </div>
              </div>
              <div class="col-lg col-sm-6 col-xs-12">
                <div class="item">
                  <h4>Simulation</h4>
                  <div class="thumb">
                    <a href="View/all_product.php?category=2"><img src="Images/categories-05.jpg" alt=""></a>
                  </div>
                </div>
              </div>
              <div class="col-lg col-sm-6 col-xs-12">
                <div class="item">
                  <h4>Strategy</h4>
                  <div class="thumb">
                    <a href="View/all_product.php?category=3"><img src="Images/categories-03.jpg" alt=""></a>
                  </div>
                </div>
              </div>
              <div class="col-lg col-sm-6 col-xs-12">
                <div class="item">
                  <h4>RPG</h4>
                  <div class="thumb">
                    <a href="View/all_product.php?category=4"><img src="Images/categories-04.jpg" alt=""></a>
                  </div>
                </div>
              </div>
              <div class="col-lg col-sm-6 col-xs-12">
                <div class="item">
                  <h4>Sports</h4>
                  <div class="thumb">
                    <a href="View/all_product.php?category=5"><img src="Images/categories-05.jpg" alt=""></a>
                  </div>
                </div>
              </div>
          <?php
          }
          ?>
        </div>
      </div>
    </div>
    
    <div class="section cta">
      <div class="container">
        <div class="row">
          <div class="col-lg-5">
            <div class="shop">
              <div class="row">
                <div class="col-lg-12">
                  <div class="section-heading">
                    <h6>Gaming Gear</h6>
                    <h2>Level Up Your Setup With <em>Premium</em> Gaming Equipment!</h2>
                  </div>
                  <p>Discover our extensive collection of gaming peripherals, accessories, and hardware to enhance your gaming performance.</p>
                  <div class="main-button">
                    <a href="View/all_product.php">Shop Gear</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-5 offset-lg-2 align-self-end">
            <div class="subscribe">
              <div class="row">
                <div class="col-lg-12">
                  <div class="section-heading">
                    <h6>JOIN GG REWARDS</h6>
                    <h2>Get 15% Off Your First Order When You <em>Subscribe</em>!</h2>
                  </div>
                  <div class="search-input">
                    <form id="subscribe" action="#">
                      <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Your email...">
                      <button type="submit">Subscribe Now</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center">
          <p>Copyright © 2025 GG - LUGX. All rights reserved. &nbsp;&nbsp; <a rel="nofollow" href="https://templatemo.com" target="_blank">Design: TemplateMo</a></p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="JS/jquery/jquery.min.js"></script>
  <script src="JS/bootstrap/js/bootstrap.min.js"></script>
  <script src="JS/isotope.min.js"></script>
  <script src="JS/owl-carousel.js"></script>
  <script src="JS/counter.js"></script>
  <script src="JS/custom.js"></script>
</body>
</html>