<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class OrderClass extends db_connection {
    
    /**
     * Get all orders for a customer
     * @param int $customer_id - Customer ID
     * @return array - Array of orders
     */
    public function get_customer_orders($customer_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "SELECT o.*, p.payment_date, p.amt as payment_amount 
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE o.customer_id = ?
                    ORDER BY o.order_date DESC";
                    
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $customer_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $orders = [];
            
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            
            return $orders;
        } catch (Exception $e) {
            error_log("Error getting customer orders: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get order details
     * @param int $order_id - Order ID
     * @return array|bool - Order details array or false if not found
     */
    public function get_order_details($order_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "SELECT o.*, p.payment_date, p.amt as payment_amount, p.currency, p.pay_id 
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE o.order_id = ?";
                    
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $order_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get order items
     * @param int $order_id - Order ID
     * @return array - Array of order items
     */
    public function get_order_items($order_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "SELECT od.*, p.product_title, p.product_price, p.product_image 
                    FROM orderdetails od
                    JOIN product p ON od.product_id = p.product_id
                    WHERE od.order_id = ?";
                    
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $order_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $items = [];
            
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            return $items;
        } catch (Exception $e) {
            error_log("Error getting order items: " . $e->getMessage());
            return [];
        }
    }
}
?>