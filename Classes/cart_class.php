<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class CartClass extends db_connection {
    
    /**
     * Add product to cart
     * @param int $product_id - Product ID
     * @param string $ip_address - Client IP address
     * @param int $customer_id - Customer ID
     * @param int $quantity - Quantity (default: 1)
     * @return bool - True if successful, false otherwise
     */
    public function add_to_cart($product_id, $ip_address, $customer_id, $quantity = 1) {
        try {
            $conn = $this->db_conn();
            
            // Check if product exists
            $check_sql = "SELECT product_id FROM product WHERE product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $check_stmt->bind_param("i", $product_id);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_stmt->error);
            }
            
            $result = $check_stmt->get_result();
            if ($result->num_rows == 0) {
                throw new Exception("Product does not exist");
            }
            
            // Check if product is already in cart
            $check_cart_sql = "SELECT p_id FROM cart WHERE p_id = ? AND c_id = ?";
            $check_cart_stmt = $conn->prepare($check_cart_sql);
            if (!$check_cart_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $check_cart_stmt->bind_param("ii", $product_id, $customer_id);
            if (!$check_cart_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_cart_stmt->error);
            }
            
            $cart_result = $check_cart_stmt->get_result();
            if ($cart_result->num_rows > 0) {
                throw new Exception("Product already in cart");
            }
            
            // Insert into cart
            $sql = "INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("isii", $product_id, $ip_address, $customer_id, $quantity);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error adding to cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if product is already in cart
     * @param int $product_id - Product ID
     * @param int $customer_id - Customer ID
     * @return bool - True if product is in cart, false otherwise
     */
    public function check_product_in_cart($product_id, $customer_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "SELECT p_id FROM cart WHERE p_id = ? AND c_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $product_id, $customer_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error checking product in cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all products in cart for a customer
     * @param int $customer_id - Customer ID
     * @return array - Array of products in cart and their details
     */
    public function get_cart_items($customer_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "SELECT c.p_id, c.qty, p.product_title, p.product_price, p.product_image, 
                           (c.qty * p.product_price) as item_total
                    FROM cart c
                    JOIN product p ON c.p_id = p.product_id
                    WHERE c.c_id = ?
                    ORDER BY c.p_id DESC";
                    
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $customer_id);
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
            error_log("Error getting cart items: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update quantity of a product in cart
     * @param int $product_id - Product ID
     * @param int $customer_id - Customer ID
     * @param int $quantity - New quantity
     * @return bool - True if successful, false otherwise
     */
    public function update_cart_quantity($product_id, $customer_id, $quantity) {
        try {
            if ($quantity <= 0) {
                // If quantity is 0 or negative, remove item from cart
                return $this->remove_from_cart($product_id, $customer_id);
            }
            
            $conn = $this->db_conn();
            
            $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND c_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("iii", $quantity, $product_id, $customer_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating cart quantity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a product from cart
     * @param int $product_id - Product ID
     * @param int $customer_id - Customer ID
     * @return bool - True if successful, false otherwise
     */
    public function remove_from_cart($product_id, $customer_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "DELETE FROM cart WHERE p_id = ? AND c_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $product_id, $customer_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error removing from cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cart total for a customer
     * @param int $customer_id - Customer ID
     * @return float - Cart total
     */
    public function get_cart_total($customer_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "SELECT SUM(c.qty * p.product_price) as total
                    FROM cart c
                    JOIN product p ON c.p_id = p.product_id
                    WHERE c.c_id = ?";
                    
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $customer_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting cart total: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get cart count for a customer
     * @param int $customer_id - Customer ID
     * @return int - Number of items in cart
     */
    public function get_cart_count($customer_id) {
        try {
            $conn = $this->db_conn();
            
            $sql = "SELECT COUNT(*) as count FROM cart WHERE c_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $customer_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting cart count: " . $e->getMessage());
            return 0;
        }
    }
}
?>