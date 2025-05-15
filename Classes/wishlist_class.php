<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class WishlistClass extends db_connection
{
    // Add to wishlist for logged in user
    public function add_to_wishlist($product_id, $customer_id)
    {
        try {
            $conn = $this->db_conn();

            // Check if product already in wishlist
            $check_sql = "SELECT id FROM wishlist WHERE p_id = ? AND c_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $product_id, $customer_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                return true; // Already in wishlist
            }

            // Add to wishlist
            $sql = "INSERT INTO wishlist (p_id, c_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $product_id, $customer_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding to wishlist: " . $e->getMessage());
            return false;
        }
    }

    // Add to wishlist for guest
    public function add_to_guest_wishlist($product_id, $guest_id)
    {
        try {
            $conn = $this->db_conn();

            // Check if product already in wishlist
            $check_sql = "SELECT id FROM wishlist WHERE p_id = ? AND guest_session_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("is", $product_id, $guest_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                return true; // Already in wishlist
            }

            // Add to wishlist
            $sql = "INSERT INTO wishlist (p_id, guest_session_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $product_id, $guest_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding to guest wishlist: " . $e->getMessage());
            return false;
        }
    }

    // Get wishlist items for logged in user
    public function get_wishlist_items($customer_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT w.id, w.p_id, p.product_title, p.product_price, p.product_image, p.is_bundle, p.is_preorder, p.release_date 
                   FROM wishlist w
                   JOIN products p ON w.p_id = p.product_id
                   WHERE w.c_id = ?
                   ORDER BY w.date_added DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }

            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            error_log("Error getting wishlist items: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    // Get wishlist items for guest
    public function get_guest_wishlist_items($guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT w.id, w.p_id, p.product_title, p.product_price, p.product_image, p.is_bundle, p.is_preorder, p.release_date 
                   FROM wishlist w
                   JOIN products p ON w.p_id = p.product_id
                   WHERE w.guest_session_id = ?
                   ORDER BY w.date_added DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $guest_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }

            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            error_log("Error getting guest wishlist items: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    // Remove from wishlist
    public function remove_from_wishlist($wishlist_id, $customer_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "DELETE FROM wishlist WHERE id = ? AND c_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $wishlist_id, $customer_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error removing from wishlist: " . $e->getMessage());
            return false;
        }
    }

    // Remove from guest wishlist
    public function remove_from_guest_wishlist($wishlist_id, $guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "DELETE FROM wishlist WHERE id = ? AND guest_session_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $wishlist_id, $guest_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error removing from guest wishlist: " . $e->getMessage());
            return false;
        }
    }
    // Get wishlist count for a customer
    public function get_wishlist_count($customer_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT COUNT(*) as count FROM wishlist WHERE c_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting wishlist count: " . $e->getMessage());
            return 0;
        }
    }

    // Get wishlist count for a guest
    public function get_guest_wishlist_count($guest_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT COUNT(*) as count FROM wishlist WHERE guest_session_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $guest_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting guest wishlist count: " . $e->getMessage());
            return 0;
        }
    }
}
