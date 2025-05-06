<?php
require_once(__DIR__ . "/../Classes/cart_class.php");

class CartController {
    private $cartClass;
    
    public function __construct() {
        $this->cartClass = new CartClass();
    }
    
    /**
     * Add product to cart
     * @param int $product_id - Product ID
     * @param string $ip_address - Client IP address
     * @param int $customer_id - Customer ID
     * @param int $quantity - Quantity (default: 1)
     * @return bool - True if successful, false otherwise
     */
    public function add_to_cart_ctr($product_id, $ip_address, $customer_id, $quantity = 1) {
        try {
            return $this->cartClass->add_to_cart($product_id, $ip_address, $customer_id, $quantity);
        } catch (Exception $e) {
            error_log("Error in add_to_cart_ctr: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if product is already in cart
     * @param int $product_id - Product ID
     * @param int $customer_id - Customer ID
     * @return bool - True if product is in cart, false otherwise
     */
    public function check_product_in_cart_ctr($product_id, $customer_id) {
        try {
            return $this->cartClass->check_product_in_cart($product_id, $customer_id);
        } catch (Exception $e) {
            error_log("Error in check_product_in_cart_ctr: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all products in cart for a customer
     * @param int $customer_id - Customer ID
     * @return array - Array of products in cart and their details
     */
    public function get_cart_items_ctr($customer_id) {
        try {
            $items = $this->cartClass->get_cart_items($customer_id);
            
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            error_log("Error in get_cart_items_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Update quantity of a product in cart
     * @param int $product_id - Product ID
     * @param int $customer_id - Customer ID
     * @param int $quantity - New quantity
     * @return bool - True if successful, false otherwise
     */
    public function update_cart_quantity_ctr($product_id, $customer_id, $quantity) {
        try {
            return $this->cartClass->update_cart_quantity($product_id, $customer_id, $quantity);
        } catch (Exception $e) {
            error_log("Error in update_cart_quantity_ctr: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a product from cart
     * @param int $product_id - Product ID
     * @param int $customer_id - Customer ID
     * @return bool - True if successful, false otherwise
     */
    public function remove_from_cart_ctr($product_id, $customer_id) {
        try {
            return $this->cartClass->remove_from_cart($product_id, $customer_id);
        } catch (Exception $e) {
            error_log("Error in remove_from_cart_ctr: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cart total for a customer
     * @param int $customer_id - Customer ID
     * @return float - Cart total
     */
    public function get_cart_total_ctr($customer_id) {
        try {
            return $this->cartClass->get_cart_total($customer_id);
        } catch (Exception $e) {
            error_log("Error in get_cart_total_ctr: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get cart count for a customer
     * @param int $customer_id - Customer ID
     * @return int - Number of items in cart
     */
    public function get_cart_count_ctr($customer_id) {
        try {
            return $this->cartClass->get_cart_count($customer_id);
        } catch (Exception $e) {
            error_log("Error in get_cart_count_ctr: " . $e->getMessage());
            return 0;
        }
    }
}
?>