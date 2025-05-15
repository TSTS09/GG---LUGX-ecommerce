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
    public function create_bundle_ctr($title, $price, $description, $image, $keywords, $product_ids, $discounts)
    {
        try {
            // Log method call
            error_log("create_bundle_ctr called with:");
            error_log("Title: $title");
            error_log("Price: $price");
            error_log("Description: $description");
            error_log("Image: $image");
            error_log("Keywords: $keywords");
            error_log("Product IDs: " . implode(",", $product_ids));

            // Sanitize and validate inputs
            $title = htmlspecialchars($title);
            $price = (float)$price;
            $description = htmlspecialchars($description);
            $keywords = htmlspecialchars($keywords);

            // Make sure product_ids is an array
            if (!is_array($product_ids) || empty($product_ids)) {
                error_log("Invalid product_ids: must be a non-empty array");
                return false;
            }

            // Make sure discounts is an array matching product_ids
            if (!is_array($discounts) || count($discounts) != count($product_ids)) {
                error_log("Invalid discounts array: creating default zero discounts");
                $discounts = array_fill(0, count($product_ids), 0);
            }

            // Call the bundle class method
            $result = $this->bundleClass->create_bundle(
                $title,
                $price,
                $description,
                $image,
                $keywords,
                $product_ids,
                $discounts
            );

            if ($result) {
                error_log("Bundle created successfully with ID: $result");
            } else {
                error_log("Bundle creation failed");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Exception in create_bundle_ctr: " . $e->getMessage());
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
