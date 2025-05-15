<?php
require_once(__DIR__ . "/../Classes/bundle_class.php");

class BundleController
{
    private $bundleClass;

    public function __construct()
    {
        $this->bundleClass = new BundleClass();
    }

    // Create new bundle
    // In bundle_controller.php
    public function create_bundle_ctr($title, $price, $description, $image, $keywords, $product_ids, $discounts)
    {
        try {
            // Log method call
            error_log("create_bundle_ctr called");

            // Call the bundle class method
            return $this->bundleClass->create_bundle(
                $title,
                $price,
                $description,
                $image,
                $keywords,
                $product_ids,
                $discounts
            );
        } catch (Exception $e) {
            error_log("Error in create_bundle_ctr: " . $e->getMessage());
            return false;
        }
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

    // Add bundle items
    public function add_bundle_items_ctr($bundle_id, $product_ids, $discounts)
    {
        try {
            return $this->bundleClass->add_bundle_items($bundle_id, $product_ids, $discounts);
        } catch (Exception $e) {
            error_log("Error in add_bundle_items_ctr: " . $e->getMessage());
            return false;
        }
    }
}
