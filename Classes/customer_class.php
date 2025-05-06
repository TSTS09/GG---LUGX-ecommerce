<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class CustomerModel extends db_connection
{

    public function add_customer($customer_name, $customer_email, $customer_pass, $customer_country, $customer_city, $customer_contact, $customer_image, $user_role)
    {
        // Get database connection
        $conn = $this->db_conn();

        // Sanitize inputs
        $customer_name = mysqli_real_escape_string($conn, $customer_name);
        $customer_email = mysqli_real_escape_string($conn, $customer_email);
        $customer_pass = mysqli_real_escape_string($conn, $customer_pass);
        $customer_country = mysqli_real_escape_string($conn, $customer_country);
        $customer_city = mysqli_real_escape_string($conn, $customer_city);
        $customer_contact = mysqli_real_escape_string($conn, $customer_contact);
        if ($customer_image) {
            $customer_image = mysqli_real_escape_string($conn, $customer_image);
        }
        $user_role = (int)$user_role;

        // Hash the password
        $hashed_password = password_hash($customer_pass, PASSWORD_BCRYPT);

        $sql = "INSERT INTO customer (
            customer_name, 
            customer_email, 
            customer_pass, 
            customer_country, 
            customer_city, 
            customer_contact, 
            customer_image,
            user_role
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssi",
            $customer_name,
            $customer_email,
            $hashed_password,
            $customer_country,
            $customer_city,
            $customer_contact,
            $customer_image,
            $user_role
        );

        return $stmt->execute();
    }

    public function get_one_customer($customer_id)
    {
        $conn = $this->db_conn();
        $customer_id = mysqli_real_escape_string($conn, $customer_id);

        $sql = "SELECT * FROM customer WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }


    public function update_customer($customer_id, $customer_name, $customer_email, $customer_country, $customer_city, $customer_contact, $customer_image)
    {
        $conn = $this->db_conn();

        // Sanitize inputs
        $customer_id = mysqli_real_escape_string($conn, $customer_id);
        $customer_name = mysqli_real_escape_string($conn, $customer_name);
        $customer_email = mysqli_real_escape_string($conn, $customer_email);
        $customer_country = mysqli_real_escape_string($conn, $customer_country);
        $customer_city = mysqli_real_escape_string($conn, $customer_city);
        $customer_contact = mysqli_real_escape_string($conn, $customer_contact);
        if ($customer_image) {
            $customer_image = mysqli_real_escape_string($conn, $customer_image);
        }

        $sql = "UPDATE customer SET 
                customer_name = ?,
                customer_email = ?,
                customer_country = ?,
                customer_city = ?,
                customer_contact = ?";

        // Add image to update if provided
        $params = [$customer_name, $customer_email, $customer_country, $customer_city, $customer_contact];
        $types = "sssss";

        if ($customer_image) {
            $sql .= ", customer_image = ?";
            $params[] = $customer_image;
            $types .= "s";
        }

        $sql .= " WHERE customer_id = ?";
        $params[] = $customer_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    public function delete_customer($customer_id)
    {
        $conn = $this->db_conn();
        $customer_id = mysqli_real_escape_string($conn, $customer_id);

        $sql = "DELETE FROM customer WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("i", $customer_id);
        return $stmt->execute();
    }

    public function all_customers()
    {
        $sql = "SELECT * FROM customer ORDER BY customer_name";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function one_customer($customer_id)
    {
        $sql = "SELECT * FROM customer WHERE customer_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function get_all_customers($search = '', $limit = 10, $offset = 0)
    {
        $conn = $this->db_conn();
        $search = mysqli_real_escape_string($conn, $search);
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT * FROM customer 
                WHERE customer_name LIKE ? 
                OR customer_email LIKE ? 
                LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $search_param = "%$search%";
        $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function get_customers_count($search = '')
    {
        $conn = $this->db_conn();
        $search = mysqli_real_escape_string($conn, $search);

        $sql = "SELECT COUNT(*) as count FROM customer 
                WHERE customer_name LIKE ? 
                OR customer_email LIKE ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $search_param = "%$search%";
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function check_email_exists($customer_email)
    {
        $conn = $this->db_conn();
        $customer_email = mysqli_real_escape_string($conn, $customer_email);
        $sql = "SELECT * FROM customer WHERE customer_email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("s", $customer_email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function login_customer($customer_email, $customer_pass)
    {
        $conn = $this->db_conn();
        $customer_email = mysqli_real_escape_string($conn, $customer_email);

        $sql = "SELECT * FROM customer WHERE customer_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $customer_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();

        if ($customer && password_verify($customer_pass, $customer['customer_pass'])) {
            return $customer;
        }
        return false;
    }

    // public function update_password($customer_email, $new_password) {
    //     $conn = $this->db_conn();

    //     // Sanitize inputs
    //     $customer_email = mysqli_real_escape_string($conn, $customer_email);

    //     // First verify the email exists
    //     $sql = "SELECT customer_id FROM customer WHERE customer_email = ?";
    //     $stmt = $conn->prepare($sql);
    //     if (!$stmt) {
    //         throw new Exception("Prepare statement failed: " . $conn->error);
    //     }

    //     $stmt->bind_param("s", $customer_email);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $customer = $result->fetch_assoc();

    //     if (!$customer) {
    //         return false;
    //     }

    //     // Hash the new password
    //     $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    //     // Update the password
    //     $sql = "UPDATE customer SET customer_pass = ? WHERE customer_email = ?";
    //     $stmt = $conn->prepare($sql);
    //     if (!$stmt) {
    //         throw new Exception("Prepare statement failed: " . $conn->error);
    //     }

    //     $stmt->bind_param("ss", $hashed_password, $customer_email);
    //     return $stmt->execute();
    // }

    /**
     * Retrieve customer by email
     * @param string $customer_email
     * @return array|bool Customer data or false if not found
     */
    public function get_customer_by_email_ctr($customer_email)
    {
        $conn = $this->db_conn();
        $customer_email = mysqli_real_escape_string($conn, $customer_email);

        $sql = "SELECT * FROM customer WHERE customer_email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("s", $customer_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();

        return $customer ?: false;
    }

    /**
     * Update customer password
     * @param string $email - Customer email
     * @param string $password - New password (plain text)
     * @return bool - True if successful, false otherwise
     */
    public function update_password($email, $password)
    {
        try {
            $conn = $this->db_conn();

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update password
            $sql = "UPDATE customer SET customer_pass = ? WHERE customer_email = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("ss", $hashed_password, $email);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }
}
