<?php
require_once(__DIR__ ."/../Classes/customer_class.php");

class CustomerController {
    private $customerModel;

    public function __construct() {
        $this->customerModel = new CustomerModel();
    }

    public function add_customer_ctr($customer_name, $customer_email, $customer_pass, $customer_country, $customer_city, $customer_contact, $customer_image, $user_role) {
        return $this->customerModel->add_customer($customer_name, $customer_email, $customer_pass, $customer_country, $customer_city, $customer_contact, $customer_image, $user_role);
    }

    public function check_email_exists($customer_email) {
        return $this->customerModel->check_email_exists($customer_email);
    }

    public function login_customer_ctr($customer_email, $customer_pass) {
        return $this->customerModel->login_customer($customer_email, $customer_pass);
    }

    public function update_password_ctr($customer_email, $new_password) {
        return $this->customerModel->update_password($customer_email, $new_password);
    }

    public function get_all_customers_ctr($search = '', $limit = 10, $offset = 0) {
        return $this->customerModel->get_all_customers($search, $limit, $offset);
    }

    public function get_customers_count_ctr($search = '') {
        return $this->customerModel->get_customers_count($search);
    }

    public function get_one_customer_ctr($customer_id) {
        return $this->customerModel->get_one_customer($customer_id);
    }

    public function update_customer_ctr($customer_id, $customer_name, $customer_email, $customer_country, $customer_city, $customer_contact, $customer_image) {
        return $this->customerModel->update_customer($customer_id, $customer_name, $customer_email, $customer_country, $customer_city, $customer_contact, $customer_image);
    }

    public function delete_customer_ctr($customer_id) {
        return $this->customerModel->delete_customer($customer_id);
    }

    /**
     * Get customer by email
     * @param string $email
     * @return array|bool Customer data or false if not found
     */
    public function get_customer_by_email_ctr($email) {
        try {
            return $this->customerModel->get_customer_by_email_ctr($email);
        } catch (Exception $e) {
            error_log("Error in get_customer_by_email_ctr: " . $e->getMessage());
            return false;
        }
    }
}
?>