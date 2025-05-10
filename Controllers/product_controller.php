<?php
require_once(__DIR__ . "/../Classes/product_class.php");

class ProductController
{
    private $productClass;

    public function __construct()
    {
        $this->productClass = new ProductClass();
    }

    //=============== CATEGORY CONTROLLERS ===============//

    public function add_category_ctr($cat_name)
    {
        return $this->productClass->add_category($cat_name);
    }

    public function get_all_categories_ctr()
    {
        try {
            $categories = $this->productClass->get_all_categories();

            return [
                'success' => true,
                'data' => $categories
            ];
        } catch (Exception $e) {
            error_log("Error in get_all_categories_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function get_one_category_ctr($cat_id)
    {
        return $this->productClass->get_one_category($cat_id);
    }

    public function update_category_ctr($cat_id, $cat_name)
    {
        return $this->productClass->update_category($cat_id, $cat_name);
    }

    public function delete_category_ctr($cat_id)
    {
        return $this->productClass->delete_category($cat_id);
    }

    //=============== BRAND CONTROLLERS ===============//

    public function add_brand_ctr($brand_name)
    {
        return $this->productClass->add_brand($brand_name);
    }

    public function get_all_brands_ctr()
    {
        try {
            $brands = $this->productClass->get_all_brands();

            return [
                'success' => true,
                'data' => $brands
            ];
        } catch (Exception $e) {
            error_log("Error in get_all_brands_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function get_one_brand_ctr($brand_id)
    {
        return $this->productClass->get_one_brand($brand_id);
    }

    public function update_brand_ctr($brand_id, $brand_name)
    {
        return $this->productClass->update_brand($brand_id, $brand_name);
    }

    public function delete_brand_ctr($brand_id)
    {
        return $this->productClass->delete_brand($brand_id);
    }

    //=============== PRODUCT CONTROLLERS ===============//

    public function add_product_ctr($product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords)
    {
        try {
            error_log("ProductController: Adding product - Title: $product_title, Category: $product_cat, Brand: $product_brand");

            // Validate inputs
            if (empty($product_title)) {
                error_log("ProductController: Product title is empty");
                return false;
            }

            // Create a database connection to test directly
            $connection_test = $this->productClass->db_conn();
            if (!$connection_test) {
                error_log("ProductController: Database connection failed");
                return false;
            }
            error_log("ProductController: Database connection successful");

            // Add product
            $result = $this->productClass->add_product($product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords);

            error_log("ProductController: add_product result: " . ($result ? "Success" : "Failed"));
            return $result;
        } catch (Exception $e) {
            error_log("Exception in add_product_ctr: " . $e->getMessage());
            return false;
        }
    }

    public function get_all_products_ctr($search = '', $entries = 10)
    {
        try {
            // Safety check for authentication
            if (!isset($_SESSION['user_id']) && !isset($_SESSION['customer_id'])) {
                return [
                    'success' => false,
                    'message' => 'Authentication required',
                    'data' => []
                ];
            }

            $entries = in_array((int)$entries, [10, 25, 50, 100]) ? (int)$entries : 10;

            $products = $this->productClass->get_all_products($search, $entries);

            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            error_log("Error in get_all_products_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function get_one_product_ctr($product_id)
    {
        return $this->productClass->get_one_product($product_id);
    }

    public function update_product_ctr($product_id, $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords)
    {
        return $this->productClass->update_product($product_id, $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords);
    }

    public function delete_product_ctr($product_id)
    {
        return $this->productClass->delete_product($product_id);
    }

    public function get_products_by_category_ctr($cat_id, $limit = 0)
    {
        try {
            $products = $this->productClass->get_products_by_category($cat_id, $limit);

            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            error_log("Error in get_products_by_category_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function get_products_by_brand_ctr($brand_id, $limit = 0)
    {
        try {
            $products = $this->productClass->get_products_by_brand($brand_id, $limit);

            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            error_log("Error in get_products_by_brand_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function search_products_ctr($search_term)
    {
        try {
            $products = $this->productClass->search_products($search_term);

            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            error_log("Error in search_products_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function get_featured_products_ctr($limit = 8)
    {
        try {
            $products = $this->productClass->get_featured_products($limit);

            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            error_log("Error in get_featured_products_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    /**
     * Get bestselling products (implementation depends on your database structure)
     * @param int $limit - Number of products to return
     * @return array - Array of bestselling products
     */
    public function get_bestselling_products_ctr($limit = 6)
    {
        try {
            // If you have an orders table with product IDs and quantities
            // You could join it with products to get the most ordered products
            // For now, I'll just reuse the featured products method as a placeholder
            $products = $this->productClass->get_featured_products($limit);

            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            error_log("Error in get_bestselling_products_ctr: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    /**
     * Get category by name
     * @param string $category_name - Category name
     * @return array|false - Category data or false if not found
     */
    public function get_category_by_name_ctr($category_name)
    {
        try {
            $category = $this->productClass->get_category_by_name($category_name);

            if ($category) {
                return $category;
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("Error in get_category_by_name_ctr: " . $e->getMessage());
            return false;
        }
    }
}
