<?php
require_once(__DIR__ . "/../Classes/order_class.php");

class OrderController {
    private $orderClass;
    
    public function __construct() {
        $this->orderClass = new OrderClass();
    }
    
    /**
     * Get all orders for a customer
     * @param int $customer_id - Customer ID
     * @return array - Array of orders and success status
     */
    public function get_customer_orders_ctr($customer_id) {
        try {
            $orders = $this->orderClass->get_customer_orders($customer_id);
            
            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (Exception $e) {
            error_log("Error in get_customer_orders_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Get order details
     * @param int $order_id - Order ID
     * @return array - Order details and success status
     */
    public function get_order_details_ctr($order_id) {
        try {
            $order = $this->orderClass->get_order_details($order_id);
            
            return [
                'success' => true,
                'data' => $order
            ];
        } catch (Exception $e) {
            error_log("Error in get_order_details_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Get order items
     * @param int $order_id - Order ID
     * @return array - Order items and success status
     */
    public function get_order_items_ctr($order_id) {
        try {
            $items = $this->orderClass->get_order_items($order_id);
            
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