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
    
    /**
     * Create a new order from cart items
     * @param int $customer_id - Customer ID
     * @param float $order_amount - Order amount
     * @param string $invoice_no - Invoice number
     * @param string $order_status - Order status
     * @return int|bool - Order ID if successful, false otherwise
     */
    public function create_order_ctr($customer_id, $order_amount, $invoice_no, $order_status = 'Pending') {
        try {
            return $this->cartClass->create_order($customer_id, $order_amount, $invoice_no, $order_status);
        } catch (Exception $e) {
            error_log("Error in create_order_ctr: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record payment
     * @param int $order_id - Order ID
     * @param string $payment_method - Payment method
     * @param float $amount - Payment amount
     * @param string $currency - Currency code
     * @param string $transaction_id - Transaction ID
     * @return bool - True if successful, false otherwise
     */
    public function record_payment_ctr($order_id, $payment_method, $amount, $currency, $transaction_id) {
        try {
            return $this->cartClass->record_payment($order_id, $payment_method, $amount, $currency, $transaction_id);
        } catch (Exception $e) {
            error_log("Error in record_payment_ctr: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get order details
     * @param int $order_id - Order ID
     * @return array|bool - Order details if successful, false otherwise
     */
    public function get_order_details_ctr($order_id) {
        try {
            $order = $this->cartClass->get_order_details($order_id);
            
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found',
                    'data' => null
                ];
            }
            
            return [
                'success' => true,
                'data' => $order
            ];
        } catch (Exception $e) {
            error_log("Error in get_order_details_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Get order items
     * @param int $order_id - Order ID
     * @return array - Array of order items and their details
     */
    public function get_order_items_ctr($order_id) {
        try {
            $items = $this->cartClass->get_order_items($order_id);
            
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            error_log("Error in get_order_items_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
}
?>