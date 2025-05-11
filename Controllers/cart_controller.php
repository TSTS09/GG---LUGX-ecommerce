<?php
require_once(__DIR__ . "/../Classes/cart_class.php");

class CartController
{
    private $cartClass;

    public function __construct()
    {
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
    public function add_to_cart_ctr($product_id, $ip_address, $customer_id, $quantity = 1)
    {
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
    public function check_product_in_cart_ctr($product_id, $customer_id)
    {
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
    public function get_cart_items_ctr($customer_id)
    {
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
    public function update_cart_quantity_ctr($product_id, $customer_id, $quantity)
    {
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
    public function remove_from_cart_ctr($product_id, $customer_id)
    {
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
    public function get_cart_total_ctr($customer_id)
    {
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
    public function get_cart_count_ctr($customer_id)
    {
        try {
            return $this->cartClass->get_cart_count($customer_id);
        } catch (Exception $e) {
            error_log("Error in get_cart_count_ctr: " . $e->getMessage());
            return 0;
        }
    }
    /**
     * Get order details
     * @param int $order_id - Order ID
     * @return array|bool - Order details if successful, false otherwise
     */
    public function get_order_details_ctr($order_id)
    {
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
    public function get_order_items_ctr($order_id)
    {
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
    /**
     * Create a new order from cart items
     * @param int $customer_id - Customer ID
     * @param float $order_amount - Order amount
     * @param string $invoice_no - Invoice number
     * @param string $order_status - Order status
     * @param string $reference - Transaction reference (optional)
     * @return int|bool - Order ID if successful, false otherwise
     */
    public function create_order_ctr($customer_id, $order_amount, $invoice_no, $order_status = 'Pending', $reference = '')
    {
        try {
            return $this->cartClass->create_order($customer_id, $order_amount, $invoice_no, $order_status, $reference);
        } catch (Exception $e) {
            error_log("Error in create_order_ctr: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get orders by reference
     * @param string $reference - Transaction reference
     * @return array - Orders with the given reference
     */
    public function get_orders_by_reference_ctr($reference)
    {
        try {
            return $this->cartClass->get_orders_by_reference($reference);
        } catch (Exception $e) {
            error_log("Error in get_orders_by_reference_ctr: " . $e->getMessage());
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
     * @param float $ghs_amount - Payment amount in GHS (optional)
     * @param float $exchange_rate - Exchange rate used (optional)
     * @return bool - True if successful, false otherwise
     */
    public function record_payment_ctr($order_id, $payment_method, $amount, $currency, $transaction_id, $ghs_amount = 0, $exchange_rate = 0)
    {
        try {
            return $this->cartClass->record_payment($order_id, $payment_method, $amount, $currency, $transaction_id, $ghs_amount, $exchange_rate);
        } catch (Exception $e) {
            error_log("Error in record_payment_ctr: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Get all orders for admin
     * @return array - Array of all orders
     */
    public function get_all_orders_admin_ctr()
    {
        try {
            return $this->cartClass->get_all_orders_admin();
        } catch (Exception $e) {
            error_log("Error in get_all_orders_admin_ctr: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get orders by status
     * @param string $status - Order status
     * @return array - Orders with the given status
     */
    public function get_orders_by_status_ctr($status)
    {
        try {
            return $this->cartClass->get_orders_by_status($status);
        } catch (Exception $e) {
            error_log("Error in get_orders_by_status_ctr: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update order status
     * @param int $order_id - Order ID
     * @param string $status - New status
     * @return bool - True if successful, false otherwise
     */
    public function update_order_status_ctr($order_id, $status)
    {
        try {
            return $this->cartClass->update_order_status($order_id, $status);
        } catch (Exception $e) {
            error_log("Error in update_order_status_ctr: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment information for an order
     * @param int $order_id - Order ID
     * @return array|null - Payment information or null if not found
     */
    public function get_payment_info_ctr($order_id)
    {
        try {
            return $this->cartClass->get_payment_info($order_id);
        } catch (Exception $e) {
            error_log("Error in get_payment_info_ctr: " . $e->getMessage());
            return null;
        }
    }
}
