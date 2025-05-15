<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class BundleClass extends db_connection
{
    // Create new bundle
    public function create_bundle($title, $price, $description, $image, $keywords, $product_ids, $discounts)
    {
        try {
            $conn = $this->db_conn();

            // Start transaction
            $conn->begin_transaction();

            // Use direct query for the product insertion
            $sql = "INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords, is_bundle) 
               VALUES (1, 1, ?, ?, ?, ?, ?, 1)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdsss", $title, $price, $description, $image, $keywords);

            if (!$stmt->execute()) {
                throw new Exception("Failed to create bundle: " . $stmt->error);
            }

            $bundle_id = $conn->insert_id;

            // Add bundle items
            foreach ($product_ids as $key => $product_id) {
                $discount = isset($discounts[$key]) ? $discounts[$key] : 0;

                $insert_sql = "INSERT INTO bundle_items (bundle_id, product_id, discount_percent) VALUES (?, ?, ?)";
                $item_stmt = $conn->prepare($insert_sql);
                $item_stmt->bind_param("iid", $bundle_id, $product_id, $discount);

                if (!$item_stmt->execute()) {
                    $conn->rollback();
                    throw new Exception("Failed to add bundle item: " . $item_stmt->error);
                }
            }

            $conn->commit();
            return $bundle_id;
        } catch (Exception $e) {
            if (isset($conn) && $conn->ping()) {
                $conn->rollback();
            }
            error_log("Error creating bundle: " . $e->getMessage());
            return false;
        }
    }

    // Get bundle items
    public function get_bundle_items($bundle_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT bi.*, p.product_title, p.product_price, p.product_image 
                   FROM bundle_items bi
                   JOIN products p ON bi.product_id = p.product_id
                   WHERE bi.bundle_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $bundle_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }

            return $items;
        } catch (Exception $e) {
            error_log("Error getting bundle items: " . $e->getMessage());
            return [];
        }
    }

    // Get all bundles
    public function get_all_bundles()
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT * FROM products WHERE is_bundle = 1 AND (deleted = 0 OR deleted IS NULL)";
            $result = $this->db_query($sql);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => "Failed to fetch bundles",
                    'data' => []
                ];
            }

            $bundles = $this->db_fetch_all($sql);

            // Get items for each bundle
            foreach ($bundles as &$bundle) {
                $bundle['items'] = $this->get_bundle_items($bundle['product_id']);
            }

            return [
                'success' => true,
                'data' => $bundles
            ];
        } catch (Exception $e) {
            error_log("Error getting all bundles: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    // Delete bundle items
    public function delete_bundle_items($bundle_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "DELETE FROM bundle_items WHERE bundle_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $bundle_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting bundle items: " . $e->getMessage());
            return false;
        }
    }

    // Add bundle items
    public function add_bundle_items($bundle_id, $product_ids, $discounts)
    {
        try {
            $conn = $this->db_conn();

            // Begin transaction
            $conn->begin_transaction();

            $sql = "INSERT INTO bundle_items (bundle_id, product_id, discount_percent) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            foreach ($product_ids as $key => $product_id) {
                $discount = $discounts[$key] ?? 0;
                $stmt->bind_param("iid", $bundle_id, $product_id, $discount);

                if (!$stmt->execute()) {
                    // Rollback if any item fails
                    $conn->rollback();
                    throw new Exception("Failed to add bundle item: " . $stmt->error);
                }
            }

            // Commit the transaction
            $conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback on error
            if (isset($conn) && $conn->ping()) {
                $conn->rollback();
            }

            error_log("Error adding bundle items: " . $e->getMessage());
            return false;
        }
    }
}
