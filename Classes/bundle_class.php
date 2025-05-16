<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class BundleClass extends db_connection
{
    // Create new bundle
    public function create_bundle($title, $price, $description, $image, $keywords, $product_ids, $discounts, $quantities = [])
    {
        try {
            $conn = $this->db_conn();
            if (!$conn) {
                error_log("Failed to establish database connection in create_bundle");
                return false;
            }

            // Start transaction
            $conn->begin_transaction();

            // Get a valid category and brand ID
            $cat_query = "SELECT cat_id FROM categories ORDER BY cat_id LIMIT 1";
            $cat_result = $conn->query($cat_query);
            if (!$cat_result || $cat_result->num_rows === 0) {
                throw new Exception("No categories found in database. Please create at least one category.");
            }
            $cat_row = $cat_result->fetch_assoc();
            $cat_id = $cat_row['cat_id'];

            $brand_query = "SELECT brand_id FROM brands ORDER BY brand_id LIMIT 1";
            $brand_result = $conn->query($brand_query);
            if (!$brand_result || $brand_result->num_rows === 0) {
                throw new Exception("No brands found in database. Please create at least one brand.");
            }
            $brand_row = $brand_result->fetch_assoc();
            $brand_id = $brand_row['brand_id'];

            // Log exact values being inserted (for debugging)
            error_log("Attempting to insert with values: cat_id=$cat_id, brand_id=$brand_id, title=$title, price=$price");

            // Validate inputs
            $escaped_title = $conn->real_escape_string($title);
            $escaped_desc = $conn->real_escape_string($description);
            $escaped_image = $conn->real_escape_string($image);
            $escaped_keywords = $conn->real_escape_string($keywords);

            $direct_sql = "INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords, is_bundle, deleted, is_preorder, release_date) 
                     VALUES ($cat_id, $brand_id, '$escaped_title', $price, '$escaped_desc', '$escaped_image', '$escaped_keywords', 1, 0, 0, NULL)";

            $result = $conn->query($direct_sql);

            if (!$result) {
                // Log and throw the actual SQL error
                $error_msg = "SQL Error: " . $conn->error;
                error_log($error_msg);
                throw new Exception($error_msg);
            }

            $bundle_id = $conn->insert_id;
            error_log("Bundle created with ID: " . $bundle_id);

            // Add bundle items
            if (empty($product_ids)) {
                throw new Exception("No products selected for bundle");
            }

            foreach ($product_ids as $key => $product_id) {
                $discount = isset($discounts[$key]) ? $discounts[$key] : 0;
                $quantity = isset($quantities[$key]) ? (int)$quantities[$key] : 1;

                $insert_sql = "INSERT INTO bundle_items (bundle_id, product_id, discount_percent) VALUES (?, ?, ?)";
                $item_stmt = $conn->prepare($insert_sql);
                if (!$item_stmt) {
                    throw new Exception("Prepare statement failed for bundle item: " . $conn->error);
                }

                $item_stmt->bind_param("iid", $bundle_id, $product_id, $discount);

                if (!$item_stmt->execute()) {
                    throw new Exception("Failed to add bundle item: " . $item_stmt->error);
                }

                $item_stmt->close();
            }

            $conn->commit();
            return $bundle_id;
        } catch (Exception $e) {
            if (isset($conn) && $conn->ping()) {
                $conn->rollback();
            }
            // Pass the full exception message up
            error_log("Error creating bundle: " . $e->getMessage());
            throw $e; // Re-throw to capture in controller
        }
    }
    // Get bundle items
    public function get_bundle_items($bundle_id)
    {
        try {
            $conn = $this->db_conn();

            // Updated SQL to include deleted status from products table
            $sql = "SELECT bi.*, bi.quantity, p.product_title, p.product_price, p.product_image, p.deleted as is_product_deleted 
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
    // Add bundle items
    public function add_bundle_items($bundle_id, $product_ids, $discounts, $quantities = [])
    {
        try {
            $conn = $this->db_conn();

            // Begin transaction
            $conn->begin_transaction();

            // Process each product
            foreach ($product_ids as $key => $product_id) {
                // Ensure quantities is an array with default values
                if (empty($quantities) || !is_array($quantities)) {
                    $quantities = array_fill(0, count($product_ids), 1);
                }
                $discount = $discounts[$key] ?? 0;
                $quantity = $quantities[$key] ?? 1; // Default quantity to 1 if not specified

                // Check if product already exists in this bundle
                $check_sql = "SELECT * FROM bundle_items WHERE bundle_id = ? AND product_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $bundle_id, $product_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();

                if ($result->num_rows > 0) {
                    // Product already exists in bundle, update quantity
                    $update_sql = "UPDATE bundle_items SET quantity = ?, discount_percent = ? 
                              WHERE bundle_id = ? AND product_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("idii", $quantity, $discount, $bundle_id, $product_id);

                    if (!$update_stmt->execute()) {
                        // Rollback if update fails
                        $conn->rollback();
                        throw new Exception("Failed to update bundle item: " . $update_stmt->error);
                    }
                } else {
                    // Product not in bundle yet, insert new record
                    $insert_sql = "INSERT INTO bundle_items (bundle_id, product_id, discount_percent, quantity) 
                              VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("iidi", $bundle_id, $product_id, $discount, $quantity);

                    if (!$insert_stmt->execute()) {
                        // Rollback if insert fails
                        $conn->rollback();
                        throw new Exception("Failed to add bundle item: " . $insert_stmt->error);
                    }
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
