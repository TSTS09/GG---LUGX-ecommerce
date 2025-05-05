<?php
require_once("../Classes/user_class.php");
class UserController {
    protected $userModel;
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    // Get customer details by ID
    public function get_customer_by_id($customer_id) {
        $sql = "SELECT * FROM customer WHERE customer_id = $customer_id";
        return $this->userModel->db_fetch_one($sql);
    }
    
    // Parameters in exact database column order
    public function add_user($customer_name, $customer_email, $customer_pass, $customer_country, $customer_city, $customer_contact, $customer_image, $user_role) {
        try {
            // First check if email exists
            if ($this->check_email_exists($customer_email)) {
                return "Email already exists";
            }
            
            // Pass parameters in exact database column order
            $result = $this->userModel->add_user(
                $customer_name,
                $customer_email,
                $customer_pass,
                $customer_country,
                $customer_city,
                $customer_contact,
                $customer_image,
                $user_role
            );
            
            return $result;
        } catch (Exception $e) {
            error_log("Add user error: " . $e->getMessage());
            return $e->getMessage(); // Return error message instead of false
        }
    }
    
    public function check_email_exists($customer_email) {
        return $this->userModel->check_email_exists($customer_email);
    }
    
    public function login_user_ctr($customer_email, $customer_pass) {
        return $this->userModel->login_user($customer_email, $customer_pass);
    }
}
?>