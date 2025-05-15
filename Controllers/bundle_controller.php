<?php
require_once(__DIR__ . "/../Classes/bundle_class.php");

class BundleController
{
    private $bundleClass;

    public function __construct()
    {
        $this->bundleClass = new BundleClass();
    }

    // Get bundle items
    public function get_bundle_items_ctr($bundle_id)
    {
        try {
            return $this->bundleClass->get_bundle_items($bundle_id);
        } catch (Exception $e) {
            error_log("Error in get_bundle_items_ctr: " . $e->getMessage());
            return [];
        }
    }

    // Get all bundles
    public function get_all_bundles_ctr()
    {
        try {
            return $this->bundleClass->get_all_bundles();
        } catch (Exception $e) {
            error_log("Error in get_all_bundles_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    // Delete bundle items
    public function delete_bundle_items_ctr($bundle_id)
    {
        try {
            return $this->bundleClass->delete_bundle_items($bundle_id);
        } catch (Exception $e) {
            error_log("Error in delete_bundle_items_ctr: " . $e->getMessage());
            return false;
        }
    }
    public function create_bundle_ctr($title, $price, $description, $image, $keywords, $product_ids, $discounts, $quantities = [])
    {
        try {
            // Validate inputs
            if (empty($title)) {
                error_log("Bundle title cannot be empty");
                return false;
            }

            if ($price <= 0) {
                error_log("Bundle price must be greater than zero");
                return false;
            }

            if (empty($product_ids) || !is_array($product_ids)) {
                error_log("Product IDs must be a non-empty array");
                return false;
            }

            // Log method call
            error_log("create_bundle_ctr called with title: $title, price: $price, products: " . count($product_ids));

            // Call the bundle class method
            $result = $this->bundleClass->create_bundle(
                $title,
                $price,
                $description,
                $image,
                $keywords,
                $product_ids,
                $discounts,
                $quantities  // Add this parameter
            );

            return $result;
        } catch (Exception $e) {
            // Capture the full error message
            error_log("Detailed error in create_bundle_ctr: " . $e->getMessage());
            $_SESSION['detailed_error'] = $e->getMessage();
            return false;
        }
    }

    public function add_bundle_items_ctr($bundle_id, $product_ids, $discounts, $quantities = [])
    {
        try {
            return $this->bundleClass->add_bundle_items($bundle_id, $product_ids, $discounts, $quantities);
        } catch (Exception $e) {
            error_log("Error in add_bundle_items_ctr: " . $e->getMessage());
            return false;
        }
    }
}
