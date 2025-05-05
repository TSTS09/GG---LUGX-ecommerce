<?php
// Include core file for session management and authentication functions
require_once("../Setting/core.php");

// Set page title
$page_title = "GG - LUGX Gaming | Contact Us";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title><?php echo $page_title; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="../JS/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="../CSS/fontawesome.css">
    <link rel="stylesheet" href="../CSS/templatemo-lugx-gaming.css">
    <link rel="stylesheet" href="../CSS/owl.css">
    <link rel="stylesheet" href="../CSS/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" href="../Images/logo.png" type="image/png">
    
    <!-- Custom CSS for alerts -->
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .alert ul {
            margin-bottom: 0;
            padding-left: 20px;
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
    <?php
    // Include the header
    include_once('../includes/header.php');
    ?>
    <!-- ***** Header Area End ***** -->

    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>Contact Us</h3>
                    <span class="breadcrumb"><a href="../index.php">Home</a> > Contact Us</span>
                </div>
            </div>
        </div>
    </div>

    <div class="contact-page section">
        <div class="container">
            <?php 
            // Display success message if set
            if (isset($_SESSION['contact_success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['contact_success'] . '</div>';
                unset($_SESSION['contact_success']);
            }
            
            // Display error message if set
            if (isset($_SESSION['contact_error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['contact_error'] . '</div>';
                unset($_SESSION['contact_error']);
            }
            
            // Display validation errors if any
            if (isset($_SESSION['contact_errors']) && is_array($_SESSION['contact_errors'])) {
                echo '<div class="alert alert-danger"><ul>';
                foreach ($_SESSION['contact_errors'] as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ul></div>';
                unset($_SESSION['contact_errors']);
            }
            ?>
            <div class="row">
                <div class="col-lg-6 align-self-center">
                    <div class="left-text">
                        <div class="section-heading">
                            <h6>Contact Us</h6>
                            <h2>We're Here For You!</h2>
                        </div>
                        <p>GG-LUGX is your ultimate gaming destination at Ashesi University. We offer the latest and greatest games, gaming equipment, and accessories to level up your gaming experience. Our knowledgeable staff is ready to help you find exactly what you need!</p>
                        <ul>
                            <li><span>Address</span> 1 University Avenue, Berekuso, Eastern Region, Ghana</li>
                            <li><span>Phone</span> +233 302 610 330</li>
                            <li><span>Email</span> info@gg-lugx.com</li>
                            <li><span>Store Hours</span> Monday-Friday: 9am to 6pm | Saturday: 10am to 4pm</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="right-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="map">
                                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.6082452361807!2d-0.21956032421655893!3d5.7598289302832105!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdf7eddcf6c5b6b%3A0x1449516882e0e8e9!2sAshesi%20University!5e0!3m2!1sen!2sus!4v1683560374909!5m2!1sen!2sus" width="100%" height="325px" frameborder="0" style="border:0; border-radius: 23px;" allowfullscreen></iframe>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <form id="contact-form" action="../Actions/process_contact.php" method="post">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <fieldset>
                                                <input type="name" name="name" id="name" placeholder="Your Name..." autocomplete="on" required>
                                            </fieldset>
                                        </div>
                                        <div class="col-lg-6">
                                            <fieldset>
                                                <input type="surname" name="surname" id="surname" placeholder="Your Surname..." autocomplete="on" required>
                                            </fieldset>
                                        </div>
                                        <div class="col-lg-6">
                                            <fieldset>
                                                <input type="text" name="email" id="email" pattern="[^ @]*@[^ @]*" placeholder="Your E-mail..." required>
                                            </fieldset>
                                        </div>
                                        <div class="col-lg-6">
                                            <fieldset>
                                                <input type="subject" name="subject" id="subject" placeholder="Subject..." autocomplete="on" required>
                                            </fieldset>
                                        </div>
                                        <div class="col-lg-12">
                                            <fieldset>
                                                <textarea name="message" id="message" placeholder="Your Message" required></textarea>
                                            </fieldset>
                                        </div>
                                        <div class="col-lg-12">
                                            <fieldset>
                                                <button type="submit" id="form-submit" class="orange-button">Send Message Now</button>
                                            </fieldset>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Include the footer
    include_once('../includes/footer.php');
    ?>

    <!-- Scripts -->
    <script src="../JS/jquery/jquery.min.js"></script>
    <script src="../JS/bootstrap/js/bootstrap.min.js"></script>
    <script src="../JS/isotope.min.js"></script>
    <script src="../JS/owl-carousel.js"></script>
    <script src="../JS/counter.js"></script>
    <script src="../JS/custom.js"></script>
</body>
</html>