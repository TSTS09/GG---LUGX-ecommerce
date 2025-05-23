<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class CartClass extends db_connection
{

    /**
     * Add product to cart
     * @param int $product_id - Product ID
     * @param string $ip_address - Client IP address
     * @param int $customer_id - Customer ID
     * @param int $quantity - Quantity (default: 1)
     * @return bool - True if successful, false otherwise
     */
    public function add_to_cart($product_id, $ip_address, $customer_id, $quantity = 1)
    {
        try {
            $conn = $this->db_conn();

            // Check if product exists
            $check_sql = "SELECT product_id FROM products WHERE product_id = ?";
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
                // Log the error with more detailed information
                error_log("Cart insertion error: " . $stmt->error . " | Product ID: " . $product_id .
                    " | Customer ID: " . $customer_id . " | Quantity: " . $quantity);
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
    public function check_product_in_cart($product_id, $customer_id)
    {
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
    public function get_cart_items($customer_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT c.p_id, c.qty, p.product_title, p.product_price, p.product_image, 
                           (c.qty * p.product_price) as item_total
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
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
    public function update_cart_quantity($product_id, $customer_id, $quantity)
    {
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
    public function remove_from_cart($product_id, $customer_id)
    {
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
    public function get_cart_total($customer_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT SUM(c.qty * p.product_price) as total
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
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
    public function get_cart_count($customer_id)
    {
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
    /**
     * Move cart items to order details
     * @param int $customer_id - Customer ID
     * @param int $order_id - Order ID
     * @return bool - True if successful, false otherwise
     */
    public function move_cart_to_order_details($customer_id, $order_id)
    {
        try {
            $conn = $this->db_conn();

            // Get cart items
            $cart_items = $this->get_cart_items($customer_id);

            if (empty($cart_items)) {
                throw new Exception("No items in cart");
            }

            // Begin transaction
            $conn->begin_transaction();

            // Insert each cart item into order details
            foreach ($cart_items as $item) {
                $sql = "INSERT INTO orderdetails (order_id, product_id, qty) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                $stmt->bind_param("iii", $order_id, $item['p_id'], $item['qty']);
                if (!$stmt->execute()) {
                    throw new Exception("Execute statement failed: " . $stmt->error);
                }
            }

            // Clear cart
            $sql = "DELETE FROM cart WHERE c_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $customer_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            // Commit transaction
            $conn->commit();

            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            if (isset($conn) && $conn->ping()) {
                $conn->rollback();
            }

            error_log("Error moving cart to order details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order details
     * @param int $order_id - Order ID
     * @return array|bool - Order details if successful, false otherwise
     */
    public function get_order_details($order_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT o.*, c.customer_name, c.customer_email, c.customer_contact 
                    FROM orders o 
                    JOIN customer c ON o.customer_id = c.customer_id 
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
     * Create a new order from cart items
     * @param int $customer_id - Customer ID
     * @param float $order_amount - Order amount
     * @param string $invoice_no - Invoice number
     * @param string $order_status - Order status
     * @param string $reference - Transaction reference (optional)
     * @return int|bool - Order ID if successful, false otherwise
     */
    public function create_order($customer_id, $order_amount, $invoice_no, $order_status = 'Pending', $reference = '')
    {
        try {
            $conn = $this->db_conn();

            // Log all parameters for debugging
            error_log("Creating order with: customer_id=$customer_id, amount=$order_amount, invoice=$invoice_no, status=$order_status, ref=$reference");

            // Create order with all required fields
            $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status, order_amount, reference) 
                VALUES (?, ?, NOW(), ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Prepare statement failed: " . $conn->error);
                return false;
            }

            // Include order_amount in the parameter binding
            $stmt->bind_param("iisds", $customer_id, $invoice_no, $order_status, $order_amount, $reference);

            if (!$stmt->execute()) {
                error_log("Execute statement failed: " . $stmt->error);
                return false;
            }

            // Get the order ID
            $order_id = $conn->insert_id;
            error_log("Order created successfully with ID: $order_id");

            // Move cart items to order details
            $result = $this->move_cart_to_order_details($customer_id, $order_id);
            if (!$result) {
                error_log("Failed to move cart items to order details");
                return false;
            }

            return $order_id;
        } catch (Exception $e) {
            error_log("Exception in create_order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get orders by reference
     * @param string $reference - Transaction reference
     * @return array - Orders with the given reference
     */
    public function get_orders_by_reference($reference)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT * FROM orders WHERE reference = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("s", $reference);
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
            error_log("Error getting orders by reference: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Record payment
     * @param int $order_id - Order ID
     * @param string $payment_method - Payment method
     * @param float $amount - Payment amount (USD)
     * @param string $currency - Currency code
     * @param string $transaction_id - Transaction ID
     * @param float $ghs_amount - Payment amount in GHS
     * @param float $exchange_rate - Exchange rate used
     * @return bool - True if successful, false otherwise
     */
    public function record_payment($order_id, $payment_method, $amount, $currency, $transaction_id, $ghs_amount = 0, $exchange_rate = 0)
    {
        try {
            // Establish database connection
            $conn = $this->db_conn();

            // Get order info to check if it's a guest order
            $order_sql = "SELECT customer_id, guest_email FROM orders WHERE order_id = ?";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("i", $order_id);
            $order_stmt->execute();
            $order_result = $order_stmt->get_result();
            $order = $order_result->fetch_assoc();

            // For guest orders, use 0 as customer_id
            $customer_id = $order['customer_id'];

            // Insert payment with customer_id (can be 0 for guests)
            if ($customer_id === NULL) {
                $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date, ghs_amount, exchange_rate) 
                VALUES (?, NULL, ?, ?, NOW(), ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("disdd", $amount, $order_id, $currency, $ghs_amount, $exchange_rate);
            } else {
                $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date, ghs_amount, exchange_rate) 
                VALUES (?, ?, ?, ?, NOW(), ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("diisdd", $amount, $customer_id, $order_id, $currency, $ghs_amount, $exchange_rate);
            }            

            if (!$stmt->execute()) {
                error_log("Payment insert failed: " . $stmt->error);
                return false;
            }

            // Update order status
            $update_sql = "UPDATE orders SET order_status = 'Completed' WHERE order_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $order_id);

            if (!$update_stmt->execute()) {
                error_log("Order status update failed: " . $update_stmt->error);
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Exception in record_payment: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Get all orders for admin
     * @return array - Array of all orders
     */
    public function get_all_orders_admin()
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT o.*, c.customer_email 
                FROM orders o
                LEFT JOIN customer c ON o.customer_id = c.customer_id
                ORDER BY o.order_date DESC";

            $result = $this->db_query($sql);

            if (!$result) {
                return [];
            }

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error in get_all_orders_admin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get orders by status
     * @param string $status - Order status
     * @return array - Orders with the given status
     */
    public function get_orders_by_status($status)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT o.*, c.customer_email 
                FROM orders o
                LEFT JOIN customer c ON o.customer_id = c.customer_id
                WHERE o.order_status = ?
                ORDER BY o.order_date DESC";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("s", $status);
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
            error_log("Error getting orders by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payment information for an order
     * @param int $order_id - Order ID
     * @return array|null - Payment information or null if not found
     */
    public function get_payment_info($order_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT * FROM payment WHERE order_id = ? LIMIT 1";
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
            error_log("Error getting payment info: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Update order status
     * @param int $order_id - Order ID
     * @param string $status - New status
     * @return bool - True if successful, false otherwise
     */
    public function update_order_status($order_id, $status)
    {
        try {
            $conn = $this->db_conn();

            // Validate status
            $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Completed', 'Cancelled'];
            if (!in_array($status, $valid_statuses)) {
                throw new Exception("Invalid status: $status");
            }

            $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("si", $status, $order_id);

            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Get order items with product info, including deleted products
     * @param int $order_id - Order ID
     * @return array - Array of order items and their details
     */
    public function get_order_items($order_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT od.*, p.product_title, p.product_price, p.product_image, 
                    (od.qty * p.product_price) as item_total,
                    p.deleted as is_product_deleted
                FROM orderdetails od 
                LEFT JOIN products p ON od.product_id = p.product_id 
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
                // If product is deleted, add a note to the title
                if ($row['is_product_deleted'] == 1) {
                    $row['product_title'] = $row['product_title'] . ' (Product no longer available)';
                }
                $items[] = $row;
            }

            return $items;
        } catch (Exception $e) {
            error_log("Error getting order items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add product to guest cart
     * @param int $product_id - Product ID
     * @param string $ip_address - Client IP address
     * @param string $guest_id - Guest session ID
     * @param int $quantity - Quantity
     * @return bool - True if successful, false otherwise
     */
    public function add_to_guest_cart($product_id, $ip_address, $guest_id, $quantity = 1)
    {
        try {
            $conn = $this->db_conn();

            // Check if product exists
            $check_sql = "SELECT product_id FROM products WHERE product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows == 0) {
                throw new Exception("Product does not exist");
            }

            // Check if product is already in guest cart
            $check_cart_sql = "SELECT p_id FROM cart WHERE p_id = ? AND guest_session_id = ?";
            $check_cart_stmt = $conn->prepare($check_cart_sql);
            $check_cart_stmt->bind_param("is", $product_id, $guest_id);
            $check_cart_stmt->execute();
            $cart_result = $check_cart_stmt->get_result();

            if ($cart_result->num_rows > 0) {
                // Update quantity instead
                $update_sql = "UPDATE cart SET qty = qty + ? WHERE p_id = ? AND guest_session_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("iis", $quantity, $product_id, $guest_id);
                return $update_stmt->execute();
            }

            // Insert into cart
            $sql = "INSERT INTO cart (p_id, ip_add, guest_session_id, qty) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issi", $product_id, $ip_address, $guest_id, $quantity);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding to guest cart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if product is already in guest cart
     * @param int $product_id - Product ID
     * @param string $guest_id - Guest session ID
     * @return bool - True if product is in cart, false otherwise
     */
    public function check_product_in_guest_cart($product_id, $guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT p_id FROM cart WHERE p_id = ? AND guest_session_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $product_id, $guest_id);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error checking product in guest cart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all products in guest cart
     * @param string $guest_id - Guest session ID
     * @return array - Array of products in cart and their details
     */
    public function get_guest_cart_items($guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT c.p_id, c.qty, p.product_title, p.product_price, p.product_image, 
                           (c.qty * p.product_price) as item_total
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    WHERE c.guest_session_id = ?
                    ORDER BY c.p_id DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $guest_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];

            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }

            return $items;
        } catch (Exception $e) {
            error_log("Error getting guest cart items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get guest cart total
     * @param string $guest_id - Guest session ID
     * @return float - Cart total
     */
    public function get_guest_cart_total($guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT SUM(c.qty * p.product_price) as total
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    WHERE c.guest_session_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $guest_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting guest cart total: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get guest cart count
     * @param string $guest_id - Guest session ID
     * @return int - Number of items in cart
     */
    public function get_guest_cart_count($guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT COUNT(*) as count FROM cart WHERE guest_session_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $guest_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting guest cart count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create order from guest cart
     * @param float $order_amount - Order amount
     * @param string $invoice_no - Invoice number
     * @param string $order_status - Order status
     * @param string $reference - Transaction reference
     * @param string $guest_email - Guest email
     * @param string $guest_name - Guest name
     * @param string $guest_id - Guest session ID
     * @return int|bool - Order ID if successful, false otherwise
     */
    public function create_guest_order($order_amount, $invoice_no, $order_status, $reference, $guest_email, $guest_name, $guest_id, $guest_phone = null, $guest_address = null)
    {
        try {
            $conn = $this->db_conn();

            $sql = "INSERT INTO orders (customer_id, guest_email, guest_name, guest_phone, guest_address, invoice_no, order_date, order_status, order_amount, reference) 
               VALUES (NULL, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiss", $guest_email, $guest_name, $guest_phone, $guest_address, $invoice_no, $order_status, $order_amount, $reference);


            if (!$stmt->execute()) {
                error_log("Failed to create guest order: " . $stmt->error);
                return false;
            }

            $order_id = $conn->insert_id;

            // Move guest cart items to order details
            $this->move_guest_cart_to_order_details($guest_id, $order_id);

            return $order_id;
        } catch (Exception $e) {
            error_log("Exception in create_guest_order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Move guest cart items to order details
     * @param string $guest_id - Guest session ID
     * @param int $order_id - Order ID
     * @return bool - True if successful, false otherwise
     */
    public function move_guest_cart_to_order_details($guest_id, $order_id)
    {
        try {
            $conn = $this->db_conn();

            // Get guest cart items
            $cart_items = $this->get_guest_cart_items($guest_id);

            if (empty($cart_items)) {
                throw new Exception("No items in guest cart");
            }

            // Begin transaction
            $conn->begin_transaction();

            // Insert each cart item into order details
            foreach ($cart_items as $item) {
                $sql = "INSERT INTO orderdetails (order_id, product_id, qty) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $order_id, $item['p_id'], $item['qty']);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert order detail: " . $stmt->error);
                }
            }

            // Clear guest cart
            $sql = "DELETE FROM cart WHERE guest_session_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $guest_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to clear guest cart: " . $stmt->error);
            }

            // Commit transaction
            $conn->commit();

            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            if (isset($conn) && $conn->ping()) {
                $conn->rollback();
            }

            error_log("Error moving guest cart to order details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get guest order by email and order ID
     * @param string $email - Guest email
     * @param int $order_id - Order ID or invoice number
     * @return array|bool - Order details if found, false otherwise
     */
    public function get_guest_order($email, $order_id)
    {
        try {
            $conn = $this->db_conn();

            // Try to find by order ID first
            $sql = "SELECT * FROM orders WHERE guest_email = ? AND (order_id = ? OR invoice_no = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $email, $order_id, $order_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }

            return false;
        } catch (Exception $e) {
            error_log("Error getting guest order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all guest orders by email
     * @param string $email - Guest email
     * @return array - Array of orders
     */
    public function get_guest_orders_by_email($email)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT * FROM orders WHERE guest_email = ? ORDER BY order_date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $orders = [];

            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }

            return $orders;
        } catch (Exception $e) {
            error_log("Error getting guest orders: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Remove product from guest cart
     * @param int $product_id - Product ID
     * @param string $guest_id - Guest session ID
     * @return bool - True if successful, false otherwise
     */
    public function remove_from_guest_cart($product_id, $guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "DELETE FROM cart WHERE p_id = ? AND guest_session_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("is", $product_id, $guest_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error removing from guest cart: " . $e->getMessage());
            return false;
        }
    }
}
