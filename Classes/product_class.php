<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class ProductClass extends db_connection
{

    //=============== CATEGORY FUNCTIONS ===============//

    /**
     * Add a new category
     * @param string $cat_name - The name of the category
     * @return bool - True if successful, false otherwise
     */
    public function add_category($cat_name)
    {
        try {
            $conn = $this->db_conn();

            // Sanitize inputs
            $cat_name = mysqli_real_escape_string($conn, $cat_name);

            // Check if category already exists
            $check_sql = "SELECT cat_id FROM categories WHERE cat_name = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $check_stmt->bind_param("s", $cat_name);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Category already exists");
            }

            // Insert category
            $sql = "INSERT INTO categories (cat_name) VALUES (?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("s", $cat_name);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error adding category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all categories
     * @return array - Array of categories
     */
    public function get_all_categories()
    {
        try {
            $sql = "SELECT * FROM categories ORDER BY cat_name ASC";
            $result = $this->db_query($sql);

            if (!$result) {
                throw new Exception("Failed to fetch categories");
            }

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single category by ID
     * @param int $cat_id - The category ID
     * @return array|bool - Category data or false if not found
     */
    public function get_one_category($cat_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT * FROM categories WHERE cat_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $cat_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a category
     * @param int $cat_id - The category ID
     * @param string $cat_name - The new category name
     * @return bool - True if successful, false otherwise
     */
    public function update_category($cat_id, $cat_name)
    {
        try {
            $conn = $this->db_conn();

            // Sanitize inputs
            $cat_name = mysqli_real_escape_string($conn, $cat_name);

            // Check if category exists
            $check_sql = "SELECT cat_id FROM categories WHERE cat_name = ? AND cat_id != ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $check_stmt->bind_param("si", $cat_name, $cat_id);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Category name already exists");
            }

            // Update category
            $sql = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("si", $cat_name, $cat_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a category
     * @param int $cat_id - The category ID
     * @return bool - True if successful, false otherwise
     */
    public function delete_category($cat_id)
    {
        try {
            $conn = $this->db_conn();

            // Check if category is in use by products
            $check_sql = "SELECT product_id FROM product WHERE product_cat = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $check_stmt->bind_param("i", $cat_id);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Cannot delete category because it is used by products");
            }

            // Delete category
            $sql = "DELETE FROM categories WHERE cat_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $cat_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }

    //=============== BRAND FUNCTIONS ===============//

    /**
     * Add a new brand
     * @param string $brand_name - The name of the brand
     * @return bool - True if successful, false otherwise
     */
    public function add_brand($brand_name)
    {
        try {
            $conn = $this->db_conn();

            // Sanitize inputs
            $brand_name = mysqli_real_escape_string($conn, $brand_name);

            // Check if brand already exists
            $check_sql = "SELECT brand_id FROM brands WHERE brand_name = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $check_stmt->bind_param("s", $brand_name);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Brand already exists");
            }

            // Insert brand
            $sql = "INSERT INTO brands (brand_name) VALUES (?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("s", $brand_name);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error adding brand: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all brands
     * @return array - Array of brands
     */
    public function get_all_brands()
    {
        try {
            $sql = "SELECT * FROM brands ORDER BY brand_name ASC";
            $result = $this->db_query($sql);

            if (!$result) {
                throw new Exception("Failed to fetch brands");
            }

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error fetching brands: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single brand by ID
     * @param int $brand_id - The brand ID
     * @return array|bool - Brand data or false if not found
     */
    public function get_one_brand($brand_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT * FROM brands WHERE brand_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $brand_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching brand: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a brand
     * @param int $brand_id - The brand ID
     * @param string $brand_name - The new brand name
     * @return bool - True if successful, false otherwise
     */
    public function update_brand($brand_id, $brand_name)
    {
        try {
            $conn = $this->db_conn();

            // Sanitize inputs
            $brand_name = mysqli_real_escape_string($conn, $brand_name);

            // Check if brand exists
            $check_sql = "SELECT brand_id FROM brands WHERE brand_name = ? AND brand_id != ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $check_stmt->bind_param("si", $brand_name, $brand_id);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Brand name already exists");
            }

            // Update brand
            $sql = "UPDATE brands SET brand_name = ? WHERE brand_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("si", $brand_name, $brand_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error updating brand: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a brand
     * @param int $brand_id - The brand ID
     * @return bool - True if successful, false otherwise
     */
    public function delete_brand($brand_id)
    {
        try {
            $conn = $this->db_conn();

            // Check if brand is in use by products
            $check_sql = "SELECT product_id FROM product WHERE product_brand = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $check_stmt->bind_param("i", $brand_id);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Cannot delete brand because it is used by products");
            }

            // Delete brand
            $sql = "DELETE FROM brands WHERE brand_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $brand_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error deleting brand: " . $e->getMessage());
            return false;
        }
    }

    //=============== PRODUCT FUNCTIONS ===============//

    /**
     * Add a new product
     * @param int $product_cat - Category ID
     * @param int $product_brand - Brand ID
     * @param string $product_title - Product title
     * @param double $product_price - Product price
     * @param string $product_desc - Product description
     * @param string $product_image - Product image path
     * @param string $product_keywords - Product keywords
     * @return bool - True if successful, false otherwise
     */
    public function add_product($product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords)
    {
        try {
            $conn = $this->db_conn();

            // Sanitize inputs
            $product_cat = (int)$product_cat;
            $product_brand = (int)$product_brand;
            $product_title = mysqli_real_escape_string($conn, $product_title);
            $product_price = (float)$product_price;
            $product_desc = mysqli_real_escape_string($conn, $product_desc);
            $product_keywords = mysqli_real_escape_string($conn, $product_keywords);

            // Insert product
            $sql = "INSERT INTO product (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("iisdssss", $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error adding product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all products
     * @param string $search - Optional search term
     * @param int $limit - Optional result limit
     * @return array - Array of products
     */
    public function get_all_products($search = '', $limit = 0)
    {
        try {
            $conn = $this->db_conn();

            // Base query
            $sql = "SELECT p.*, c.cat_name, b.brand_name 
                   FROM product p 
                   LEFT JOIN categories c ON p.product_cat = c.cat_id 
                   LEFT JOIN brands b ON p.product_brand = b.brand_id";

            $params = [];
            $types = "";

            // Add search condition if search term exists
            if (!empty($search)) {
                $search = mysqli_real_escape_string($conn, $search);
                $sql .= " WHERE p.product_title LIKE ? OR p.product_keywords LIKE ? OR p.product_desc LIKE ?";
                $search_param = "%$search%";
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
                $types .= "sss";
            }

            // Add ordering
            $sql .= " ORDER BY p.product_id DESC";

            // Add limit if specified
            if ($limit > 0) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
                $types .= "i";
            }

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            return $products;
        } catch (Exception $e) {
            error_log("Error fetching products: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single product by ID
     * @param int $product_id - The product ID
     * @return array|bool - Product data or false if not found
     */
    public function get_one_product($product_id)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT p.*, c.cat_name, b.brand_name 
                   FROM product p 
                   LEFT JOIN categories c ON p.product_cat = c.cat_id 
                   LEFT JOIN brands b ON p.product_brand = b.brand_id 
                   WHERE p.product_id = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a product
     * @param int $product_id - Product ID
     * @param int $product_cat - Category ID
     * @param int $product_brand - Brand ID
     * @param string $product_title - Product title
     * @param double $product_price - Product price
     * @param string $product_desc - Product description
     * @param string $product_image - Product image path (optional)
     * @param string $product_keywords - Product keywords
     * @return bool - True if successful, false otherwise
     */
    public function update_product($product_id, $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords)
    {
        try {
            $conn = $this->db_conn();

            // Sanitize inputs
            $product_id = (int)$product_id;
            $product_cat = (int)$product_cat;
            $product_brand = (int)$product_brand;
            $product_title = mysqli_real_escape_string($conn, $product_title);
            $product_price = (float)$product_price;
            $product_desc = mysqli_real_escape_string($conn, $product_desc);
            $product_keywords = mysqli_real_escape_string($conn, $product_keywords);

            // Prepare SQL based on whether image is being updated
            if ($product_image) {
                $sql = "UPDATE product SET 
                        product_cat = ?, 
                        product_brand = ?, 
                        product_title = ?, 
                        product_price = ?, 
                        product_desc = ?, 
                        product_image = ?, 
                        product_keywords = ? 
                        WHERE product_id = ?";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                $stmt->bind_param("iisdsssi", $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_image, $product_keywords, $product_id);
            } else {
                $sql = "UPDATE product SET 
                        product_cat = ?, 
                        product_brand = ?, 
                        product_title = ?, 
                        product_price = ?, 
                        product_desc = ?, 
                        product_keywords = ? 
                        WHERE product_id = ?";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                $stmt->bind_param("iisdssi", $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_id);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error updating product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a product
     * @param int $product_id - The product ID
     * @return bool - True if successful, false otherwise
     */
    public function delete_product($product_id)
    {
        try {
            $conn = $this->db_conn();

            // Get product image for deletion
            $img_sql = "SELECT product_image FROM product WHERE product_id = ?";
            $img_stmt = $conn->prepare($img_sql);
            if (!$img_stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $img_stmt->bind_param("i", $product_id);
            if (!$img_stmt->execute()) {
                throw new Exception("Execute statement failed: " . $img_stmt->error);
            }

            $result = $img_stmt->get_result();
            $product = $result->fetch_assoc();

            // Delete product
            $sql = "DELETE FROM product WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            // Delete product image file if it exists
            if ($product && $product['product_image']) {
                $image_path = "../Images/product_images/" . $product['product_image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get products by category
     * @param int $cat_id - Category ID
     * @param int $limit - Optional result limit
     * @return array - Array of products
     */
    public function get_products_by_category($cat_id, $limit = 0)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT p.*, c.cat_name, b.brand_name 
                   FROM product p 
                   LEFT JOIN categories c ON p.product_cat = c.cat_id 
                   LEFT JOIN brands b ON p.product_brand = b.brand_id 
                   WHERE p.product_cat = ?";

            // Add ordering
            $sql .= " ORDER BY p.product_id DESC";

            // Add limit if specified
            if ($limit > 0) {
                $sql .= " LIMIT ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $cat_id, $limit);
            } else {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $cat_id);
            }

            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            return $products;
        } catch (Exception $e) {
            error_log("Error fetching products by category: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get products by brand
     * @param int $brand_id - Brand ID
     * @param int $limit - Optional result limit
     * @return array - Array of products
     */
    public function get_products_by_brand($brand_id, $limit = 0)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT p.*, c.cat_name, b.brand_name 
                   FROM product p 
                   LEFT JOIN categories c ON p.product_cat = c.cat_id 
                   LEFT JOIN brands b ON p.product_brand = b.brand_id 
                   WHERE p.product_brand = ?";

            // Add ordering
            $sql .= " ORDER BY p.product_id DESC";

            // Add limit if specified
            if ($limit > 0) {
                $sql .= " LIMIT ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $brand_id, $limit);
            } else {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $brand_id);
            }

            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            return $products;
        } catch (Exception $e) {
            error_log("Error fetching products by brand: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search products
     * @param string $search_term - Search term
     * @return array - Array of products matching search
     */
    public function search_products($search_term)
    {
        try {
            $conn = $this->db_conn();

            $search_term = mysqli_real_escape_string($conn, $search_term);
            $search_param = "%$search_term%";

            $sql = "SELECT p.*, c.cat_name, b.brand_name 
                   FROM product p 
                   LEFT JOIN categories c ON p.product_cat = c.cat_id 
                   LEFT JOIN brands b ON p.product_brand = b.brand_id 
                   WHERE p.product_title LIKE ? 
                   OR p.product_keywords LIKE ? 
                   OR p.product_desc LIKE ?
                   ORDER BY p.product_id DESC";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("sss", $search_param, $search_param, $search_param);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            return $products;
        } catch (Exception $e) {
            error_log("Error searching products: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured products (newest products)
     * @param int $limit - Number of products to return
     * @return array - Array of featured products
     */
    public function get_featured_products($limit = 8)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT p.*, c.cat_name, b.brand_name 
                   FROM product p 
                   LEFT JOIN categories c ON p.product_cat = c.cat_id 
                   LEFT JOIN brands b ON p.product_brand = b.brand_id 
                   ORDER BY p.product_id DESC 
                   LIMIT ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $limit);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            return $products;
        } catch (Exception $e) {
            error_log("Error fetching featured products: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Get bestselling products based on order data
     * @param int $limit - Number of products to return
     * @return array - Array of bestselling products
     */
    public function get_bestselling_products($limit = 6)
    {
        try {
            $conn = $this->db_conn();

            // This is a sample implementation assuming you have an order_details table
            // with product_id and quantity columns
            $sql = "SELECT p.*, c.cat_name, b.brand_name, SUM(od.qty) as total_sold 
               FROM product p
               LEFT JOIN categories c ON p.product_cat = c.cat_id 
               LEFT JOIN brands b ON p.product_brand = b.brand_id
               LEFT JOIN orderdetails od ON p.product_id = od.product_id
               GROUP BY p.product_id
               ORDER BY total_sold DESC
               LIMIT ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $limit);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            // If no products found with sales data, fall back to featured products
            if (empty($products) && $limit > 0) {
                return $this->get_featured_products($limit);
            }

            return $products;
        } catch (Exception $e) {
            error_log("Error fetching bestselling products: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Get category by name
     * @param string $category_name - Category name
     * @return array|false - Category data or false if not found
     */
    public function get_category_by_name($category_name)
    {
        try {
            $conn = $this->db_conn();

            // Sanitize input
            $category_name = mysqli_real_escape_string($conn, $category_name);

            // Get category by name
            $sql = "SELECT * FROM categories WHERE cat_name = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("s", $category_name);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error getting category by name: " . $e->getMessage());
            return false;
        }
    }
}
