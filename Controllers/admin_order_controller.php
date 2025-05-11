<?php
session_start();
require_once("../Setting/core.php");

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: ../Login/login.php");
    exit;
}

require_once("../Setting/db_class.php");

// Simple controller for order management
class SimpleOrderController extends db_connection {
    /**
     * Get all orders
     * @return array - Array of all orders with customer info
     */
    public function get_all_orders() {
        try {
            $sql = "SELECT o.*, c.customer_email 
                    FROM orders o
                    LEFT JOIN customer c ON o.customer_id = c.customer_id
                    ORDER BY o.order_date DESC";
            
            $result = $this->db_query($sql);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch orders',
                    'data' => []
                ];
            }
            
            $orders = $this->db_fetch_all($sql);
            
            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (Exception $e) {
            error_log("Error in get_all_orders: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Update order status
     * @param int $order_id - Order ID
     * @param string $status - New status
     * @return bool - True if successful, false otherwise
     */
    public function update_order_status($order_id, $status) {
        try {
            $conn = $this->db_conn();
            
            // Validate status
            $valid_statuses = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];
            if (!in_array($status, $valid_statuses)) {
                return false;
            }
            
            $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("si", $status, $order_id);
            
            if (!$stmt->execute()) {
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
}

$order_controller = new SimpleOrderController();

// Handle status update if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $result = $order_controller->update_order_status($order_id, $new_status);
    
    if ($result) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Order status updated successfully'
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Failed to update order status'
        ];
    }
    
    // Redirect to avoid form resubmission
    header("Location: orders.php");
    exit;
}

// Get all orders
$orders = $order_controller->get_all_orders();
?>