<?php
require_once(__DIR__ . "/../Classes/wishlist_class.php");

class WishlistController
{
    private $wishlistClass;

    public function __construct()
    {
        $this->wishlistClass = new WishlistClass();
    }

    // Add to wishlist for logged in user
    public function add_to_wishlist_ctr($product_id, $customer_id)
    {
        try {
            return $this->wishlistClass->add_to_wishlist($product_id, $customer_id);
        } catch (Exception $e) {
            error_log("Error in add_to_wishlist_ctr: " . $e->getMessage());
            return false;
        }
    }

    // Add to wishlist for guest
    public function add_to_guest_wishlist_ctr($product_id, $guest_id)
    {
        try {
            return $this->wishlistClass->add_to_guest_wishlist($product_id, $guest_id);
        } catch (Exception $e) {
            error_log("Error in add_to_guest_wishlist_ctr: " . $e->getMessage());
            return false;
        }
    }

    // Get wishlist items for logged in user
    public function get_wishlist_items_ctr($customer_id)
    {
        try {
            return $this->wishlistClass->get_wishlist_items($customer_id);
        } catch (Exception $e) {
            error_log("Error in get_wishlist_items_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    // Get wishlist items for guest
    public function get_guest_wishlist_items_ctr($guest_id)
    {
        try {
            return $this->wishlistClass->get_guest_wishlist_items($guest_id);
        } catch (Exception $e) {
            error_log("Error in get_guest_wishlist_items_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    // Remove from wishlist
    public function remove_from_wishlist_ctr($wishlist_id, $customer_id)
    {
        try {
            return $this->wishlistClass->remove_from_wishlist($wishlist_id, $customer_id);
        } catch (Exception $e) {
            error_log("Error in remove_from_wishlist_ctr: " . $e->getMessage());
            return false;
        }
    }

    // Remove from guest wishlist
    public function remove_from_guest_wishlist_ctr($wishlist_id, $guest_id)
    {
        try {
            return $this->wishlistClass->remove_from_guest_wishlist($wishlist_id, $guest_id);
        } catch (Exception $e) {
            error_log("Error in remove_from_guest_wishlist_ctr: " . $e->getMessage());
            return false;
        }
    }
    // Get wishlist count for a customer
    public function get_wishlist_count_ctr($customer_id)
    {
        try {
            return $this->wishlistClass->get_wishlist_count($customer_id);
        } catch (Exception $e) {
            error_log("Error in get_wishlist_count_ctr: " . $e->getMessage());
            return 0;
        }
    }

    // Get wishlist count for a guest
    public function get_guest_wishlist_count_ctr($guest_id)
    {
        try {
            return $this->wishlistClass->get_guest_wishlist_count($guest_id);
        } catch (Exception $e) {
            error_log("Error in get_guest_wishlist_count_ctr: " . $e->getMessage());
            return 0;
        }
    }

    // Get wishlist count for display (works for both logged in and guest)
    public function get_wishlist_count_display()
    {
        if (is_logged_in()) {
            return $this->get_wishlist_count_ctr($_SESSION['customer_id']);
        } else if (isset($_SESSION['guest_session_id'])) {
            return $this->get_guest_wishlist_count_ctr($_SESSION['guest_session_id']);
        }
        return 0;
    }
}
