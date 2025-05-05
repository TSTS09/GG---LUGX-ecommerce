<?php
// Get base URL for correct path to assets
$base_url = '';
if (strpos($_SERVER['PHP_SELF'], '/Admin/') !== false) {
    $base_url = "../";
} elseif (strpos($_SERVER['PHP_SELF'], '/View/') !== false || 
          strpos($_SERVER['PHP_SELF'], '/Login/') !== false || 
          strpos($_SERVER['PHP_SELF'], '/Actions/') !== false) {
    $base_url = "../";
}
?>


<!-- Footer Start -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <p>Copyright © 2025 GG - LUGX. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<!-- Footer End -->

<!-- Scripts -->
<script src="<?php echo $base_url; ?>JS/jquery/jquery.min.js"></script>
<script src="<?php echo $base_url; ?>JS/bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo $base_url; ?>JS/isotope.min.js"></script>
<script src="<?php echo $base_url; ?>JS/owl-carousel.js"></script>
<script src="<?php echo $base_url; ?>JS/counter.js"></script>
<script src="<?php echo $base_url; ?>JS/custom.js"></script>

</body>
</html>