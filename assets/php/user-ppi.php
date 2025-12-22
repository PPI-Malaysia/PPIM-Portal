<?php
// user-ppi.php - PPI Campus user management functionality
require_once(__DIR__."/main.php");

class UserPPI extends ppim {
    /**
     * Constructor - initializes PPI campus user management functionality
     */
    public function __construct() {
        // Call parent constructor to initialize base functionality
        parent::__construct();

        // Check if user has PPI campus related permissions
        if ($this->hasPermission("student_db_add") == false) {
            echo "Access denied: insufficient permissions for PPI campus account management.";
            exit();
        }
    }

    /**
     * Add a new user
     * @param string $name
     * @param string $password
     * @return boolean
     */
    public function addUser($name, $password) {
        // Check if user has permission for this operation
        if ($this->hasPermission("student_db_add") == false) {
            return false;
        }
        $type = 1000; // PPI Campus account type

        // Check if username already exists
        $checkSql = "SELECT COUNT(*) as count FROM user WHERE name = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            return false; // Username already exists
        }

        // Generate a random salt (matching your existing system)
        $salt = bin2hex(random_bytes(16)); // 32 character salt

        // Hash the password with the salt (matching your existing system)
        $hashedPassword = hash('sha256', $password . $salt);

        // Insert new user
        $sql = "INSERT INTO user (name, password, salt, type) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $hashedPassword, $salt, $type);

        $result = $stmt->execute();

        return $result;
    }

    /**
     * Connect user id from username with ppi_campus id
     * @param String $name
     * @param string $university_id
     * @return boolean
     */
    public function connectUniUser($name, $university_id) {
        // Check if user has permission for this operation
        if ($this->hasPermission("student_db_add") == false) {
            return false;
        }

        // get the user ID by username
        $userID = $this->getIdByUser($name);
        if ($userID) {
            // connect the user id with ppi_campus id
            $sql = "INSERT INTO university_user (user_id, university_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $userID, $university_id);
            $result = $stmt->execute();

            return $result;
        }
        return false;
    }

    /**
     * Get a specific user ID by user name
     * @param string $name
     * @return int|false
     */
    public function getIdByUser($user) {
        $sql = "SELECT id, name FROM user WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result && $result->num_rows > 0){
            return $result->fetch_assoc()['id'];
        }
        return false;
    }

    /**
     * Get users by university ID
     *
     * @param int $university_id
     * @return array|false
     */
    public function getUniUserByUniId($university_id) {
        $sql = "SELECT u.id, u.name
                FROM user u
                JOIN university_user uu ON uu.user_id = u.id
                WHERE uu.university_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $university_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_all();
        }

        return false;
    }

    /**
     * Delete a PPI user
     * @param int $id
     * @return boolean
     */
    public function deletePPIUser($id) {
        // Check if user has permission for this operation
        if ($this->hasPermission("student_db_add") == false) {
            return false;
        }

        // Begin transaction
        $this->conn->begin_transaction();

        try {
            // Delete from university_user first
            $sql = "DELETE FROM university_user WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete from university_user");
            }

            // delete from user
            $sql = "DELETE FROM user WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete from user");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}