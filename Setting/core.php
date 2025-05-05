<?php
//start session
session_start();

//for header redirection
ob_start();

// Session configuration
// Set session timeout to 30 minutes (1800 seconds)
ini_set('session.gc_maxlifetime', 1800);

// Set session cookie to expire after 30 minutes
session_set_cookie_params(1800);

// Session timeout check
function check_session_timeout() {
    // If there's no last activity timestamp, set it now
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return;
    }
    
    // Calculate time since last activity
    $inactive_time = time() - $_SESSION['last_activity'];
    
    // If inactive for more than 30 minutes (1800 seconds)
    if ($inactive_time > 1800) {
        // Destroy session
        session_unset();
        session_destroy();
        
        // Remove session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        
        // Redirect to login page with timeout parameter
        header("Location: ../Login/login.php?timeout=1");
        exit;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

//function to check for login
function is_logged_in(){
    // Check for session timeout before checking login status
    check_session_timeout();
    
    if(isset($_SESSION['customer_id'])){
        return true;
    }else{
        return false;
    }
}

//function to check for admin
function is_admin(){
    if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1){
        return true;
    }else{
        return false;
    }
}

//function to check for customer
function is_customer(){
    if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2){
        return true;
    }else{
        return false;
    }
}

//function to check for role (admin, customer, etc)
function get_role(){
    if(isset($_SESSION['user_role'])){
        return $_SESSION['user_role'];
    }else{
        return false;
    }
}
?>