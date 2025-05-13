<?php
// user.php - User management functionality
require_once("main.php");

class User extends ppim {
    private $users = [];
    
    /**
     * Constructor - initializes user management functionality
     */
    public function __construct() {
        // Call parent constructor to initialize base functionality
        parent::__construct();
        
        // Check if user has admin privileges (type 6)
        if ($this->user_type != 6) {
            header('Location: index.php');
            exit();
        }
        
        // Load users from database
        $this->loadUsers();
    }
    
    /**
     * Load users from database
     */
    private function loadUsers() {
        
        // Fetch users from database
        $sql = "SELECT name, type FROM user";
        $result = $this->conn->query($sql);
        
        $this->users = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $this->users[] = $row;
            }
        }
    }
    
    /**
     * Get all users
     * @return array
     */
    public function getUsers() {
        return $this->users;
    }
    
    /**
     * Add a new user
     * @param string $name
     * @param string $password
     * @param int $type
     * @return boolean
     */
    public function addUser($name, $password, $type) {
        global $conn;
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO user (name, password, type) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $hashed_password, $type);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Reload users after adding
            $this->loadUsers();
        }
        
        return $result;
    }
    
    /**
     * Update a user
     * @param int $id
     * @param string $name
     * @param int $type
     * @return boolean
     */
    public function updateUser($id, $name, $type) {
        global $conn;
        
        $sql = "UPDATE user SET name = ?, type = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $name, $type, $id);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Reload users after updating
            $this->loadUsers();
        }
        
        return $result;
    }
    
    /**
     * Delete a user
     * @param int $id
     * @return boolean
     */
    public function deleteUser($id) {
        global $conn;
        
        $sql = "DELETE FROM user WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Reload users after deleting
            $this->loadUsers();
        }
        
        return $result;
    }
}
?>