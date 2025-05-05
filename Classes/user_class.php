<?php
require_once(__DIR__ . "/../Setting/db_class.php");

class UserModel extends db_connection
{
    public function add_user($customer_name, $customer_email, $customer_pass, $customer_country, $customer_city, $customer_contact, $customer_image, $user_role)
    {
        // Get database connection
        $conn = $this->db_conn();

        // Sanitize the inputs
        $customer_name = $conn->real_escape_string($customer_name);
        $customer_email = $conn->real_escape_string($customer_email);
        $customer_pass = $conn->real_escape_string($customer_pass);
        $customer_country = $conn->real_escape_string($customer_country);
        $customer_city = $conn->real_escape_string($customer_city);
        $customer_contact = $conn->real_escape_string($customer_contact);
        // $customer_image can be NULL
        if ($customer_image) {
            $customer_image = $conn->real_escape_string($customer_image);
        }
        $user_role = intval($user_role);

        // Hash the password using bcrypt
        $hashed_password = password_hash($customer_pass, PASSWORD_BCRYPT);

        try {
            // SQL fields in exact database column order
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

            // Bind parameters in exact database column order
            $stmt->bind_param(
                "sssssssi",
                $customer_name,
                $customer_email,
                $hashed_password,    // Hashed password in password column
                $customer_country,
                $customer_city,
                $customer_contact,
                $customer_image,
                $user_role
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Customer registration failed: " . $e->getMessage());
        }
    }

    public function check_email_exists($customer_email)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT COUNT(*) as count FROM customer WHERE customer_email = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("s", $customer_email);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log($e->getMessage()); // Log the error message
            throw new Exception("Email check failed: " . $e->getMessage());
        }
    }

    public function login_user($customer_email, $customer_pass)
    {
        try {
            $conn = $this->db_conn();

            $sql = "SELECT * FROM customer WHERE customer_email = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("s", $customer_email);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();

            if ($customer && password_verify($customer_pass, $customer['customer_pass'])) {
                return $customer;
            } else {
                throw new Exception("Invalid email or password");
            }
        } catch (Exception $e) {
            error_log($e->getMessage()); // Log the error message
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }
}
