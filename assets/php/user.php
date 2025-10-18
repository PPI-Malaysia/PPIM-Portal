<?php
// user.php - User management functionality
require_once(__DIR__."/main.php");

class User extends ppim {
    private $users = [];
    
    /**
     * Constructor - initializes user management functionality
     */
    public function __construct() {
        // Call parent constructor to initialize base functionality
        parent::__construct();
        
        // Check if user has admin privileges (type 999)
        if ($this->user_type != 999) {
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
        // Fetch users from database with user type names
        $sql = "SELECT u.id, u.name, u.type, ut.name as type_name 
                FROM user u 
                LEFT JOIN user_types ut ON u.type = ut.id";
        $result = $this->conn->query($sql);
        
        $this->users = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $this->users[] = $row;
            }
        }
    }
    
    private function validateSuperAdmin() {
        if ($this->user_type != 999) {
            http_response_code(403);
            die('Access denied');
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
     * Get all available user types
     * @return array
     */
    public function getUserTypes() {
        $this->validateSuperAdmin();
        $sql = "SELECT id, name, description FROM user_types WHERE is_active = 1 AND id != 999 ORDER BY id";
        $result = $this->conn->query($sql);
        
        $userTypes = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $userTypes[] = $row;
            }
        }
        return $userTypes;
    }
    
    /**
     * Get all permissions grouped by category
     * @return array
     */
    public function getPermissions() {
        $this->validateSuperAdmin();
        $sql = "SELECT id, name, description, category FROM permissions ORDER BY category, name";
        $result = $this->conn->query($sql);
        
        $permissions = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $permissions[$row['category']][] = $row;
            }
        }
        return $permissions;
    }
    
    /**
     * Get permissions for a specific user type
     * @param int $userTypeId
     * @return array
     */
    public function getUserTypePermissions($userTypeId) {
        $this->validateSuperAdmin();
        $sql = "SELECT p.id, p.name FROM permissions p 
                JOIN user_type_permissions utp ON p.id = utp.permission_id 
                WHERE utp.user_type_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userTypeId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $permissions = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $permissions[] = $row['id'];
            }
        }
        return $permissions;
    }
    
    /**
     * Get the next available user type ID
     * @return int|false
     */
    public function getNextAvailableUserTypeId() {
        $this->validateSuperAdmin();
        // Get all existing IDs in range 1-998, excluding 999 (super admin)
        $sql = "SELECT id FROM user_types WHERE id BETWEEN 1 AND 998 ORDER BY id";
        $result = $this->conn->query($sql);
        
        $existingIds = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $existingIds[] = $row['id'];
            }
        }
        
        // Find the first gap or next number
        for ($i = 1; $i <= 998; $i++) {
            if (!in_array($i, $existingIds)) {
                return $i;
            }
        }
        
        return false; // No available IDs
    }
    
    /**
     * Create a new user type
     * @param string $name
     * @param string $description
     * @param array $permissions
     * @return array|false Returns array with success status and new ID, or false
     */
    public function createUserType($name, $description, $permissions = []) {
        $this->validateSuperAdmin();
        // Get next available ID
        $id = $this->getNextAvailableUserTypeId();
        if ($id === false) {
            return false; // No available IDs
        }
        
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Insert user type
            $sql = "INSERT INTO user_types (id, name, description, created_by) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("issi", $id, $name, $description, $this->user_id);
            $stmt->execute();
            
            // Insert permissions
            if (!empty($permissions)) {
                $permSql = "INSERT INTO user_type_permissions (user_type_id, permission_id) VALUES (?, ?)";
                $permStmt = $this->conn->prepare($permSql);
                
                foreach ($permissions as $permissionId) {
                    $permStmt->bind_param("ii", $id, $permissionId);
                    $permStmt->execute();
                }
            }
            
            $this->conn->commit();
            return ['success' => true, 'id' => $id];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    /**
     * Update user type permissions
     * @param int $userTypeId
     * @param array $permissions
     * @return boolean
     */
    public function updateUserTypePermissions($userTypeId, $permissions = []) {
        $this->validateSuperAdmin();
        // Don't allow editing super admin
        if ($userTypeId == 999) {
            return false;
        }

        // don't allow editing ppi_campus data
        if ($userTypeId == 1000) {
            return false;
        }
        
        $this->conn->begin_transaction();
        
        try {
            // Delete existing permissions
            $deleteSql = "DELETE FROM user_type_permissions WHERE user_type_id = ?";
            $deleteStmt = $this->conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $userTypeId);
            $deleteStmt->execute();
            
            // Insert new permissions
            if (!empty($permissions)) {
                $insertSql = "INSERT INTO user_type_permissions (user_type_id, permission_id) VALUES (?, ?)";
                $insertStmt = $this->conn->prepare($insertSql);
                
                foreach ($permissions as $permissionId) {
                    $insertStmt->bind_param("ii", $userTypeId, $permissionId);
                    $insertStmt->execute();
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    /**
     * Delete a user type (actually delete from database)
     * @param int $userTypeId
     * @return boolean
     */
    public function deleteUserType($userTypeId) {
        $this->validateSuperAdmin();
        // Don't allow deleting super admin
        if ($userTypeId == 999) {
            return false;
        }

        // don't allow deleting ppi_campus data
        if ($userTypeId == 1000) {
            return false;
        }
        
        // Check if any users are using this type
        $checkSql = "SELECT COUNT(*) as count FROM user WHERE type = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("i", $userTypeId);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            return false; // Cannot delete user type with active users
        }
        
        // Start transaction to delete user type and its permissions
        $this->conn->begin_transaction();
        
        try {
            // Delete permissions first (foreign key constraint)
            $deletePerm = "DELETE FROM user_type_permissions WHERE user_type_id = ?";
            $stmtPerm = $this->conn->prepare($deletePerm);
            $stmtPerm->bind_param("i", $userTypeId);
            $stmtPerm->execute();
            
            // Delete user type
            $deleteType = "DELETE FROM user_types WHERE id = ?";
            $stmtType = $this->conn->prepare($deleteType);
            $stmtType->bind_param("i", $userTypeId);
            $result = $stmtType->execute();
            
            $this->conn->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Connect user id from username with ppi_campus id
     * @param String $name
     * @param string $university_id
     * @return boolean
     */
    public function connectUniUser($name, $university_id) {
        $this->validateSuperAdmin();
        // get the user ID by username
        $userID = $this->getIdByUser($name);
        if ($userID) {
            // connect the user id with ppi_campus id
            $sql = "INSERT INTO university_user (user_id, university_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $userID, $university_id);
            $result = $stmt->execute();
            if ($result) {
                // Reload users after adding
                $this->loadUsers();
            }
            
            return $result;
        }
        return false;
    }
    
    /**
     * Add a new user
     * @param string $name
     * @param string $password
     * @param int $type
     * @return boolean
     */
    public function addUser($name, $password, $type) {
        $this->validateSuperAdmin();
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
        
        if ($result) {
            // Reload users after adding
            $this->loadUsers();
        }
        
        return $result;
    }
    
    /**
     * Update a user (with optional password reset)
     * @param int $id
     * @param string $name
     * @param int $type
     * @param string|null $newPassword
     * @return boolean
     */
    public function updateUser($id, $name, $type, $newPassword = null) {
        $this->validateSuperAdmin();
        // Check if username already exists (excluding current user)
        $checkSql = "SELECT COUNT(*) as count FROM user WHERE name = ? AND id != ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("si", $name, $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            return false; // Username already exists
        }
        
        if ($newPassword) {
            // Update with new password
            $salt = bin2hex(random_bytes(16)); // 32 character salt
            $hashedPassword = hash('sha256', $newPassword . $salt);
            
            $sql = "UPDATE user SET name = ?, password = ?, salt = ?, type = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssii", $name, $hashedPassword, $salt, $type, $id);
        } else {
            // Update without changing password
            $sql = "UPDATE user SET name = ?, type = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sii", $name, $type, $id);
        }
        
        $result = $stmt->execute();
        
        if ($result) {
            // Reload users after updating
            $this->loadUsers();
        }
        
        return $result;
    }
    
    /**
     * Get a specific user by ID
     * @param int $id
     * @return array|false
     */
    public function getUserById($id) {
        $this->validateSuperAdmin();
        $sql = "SELECT u.id, u.name, u.type, ut.name as type_name 
                FROM user u 
                LEFT JOIN user_types ut ON u.type = ut.id 
                WHERE u.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get a specific user ID by user name
     * @param string $name
     * @return int|false
     */
    public function getIdByUser($user) {
        $this->validateSuperAdmin();
        $sql = "SELECT id, name FROM user
                WHERE name = ?";
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
        $this->validateSuperAdmin();

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
     * Delete a user
     * @param int $id
     * @return boolean
     */
    public function deleteUser($id) {
        $this->validateSuperAdmin();
        $sql = "DELETE FROM user WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Reload users after deleting
            $this->loadUsers();
        }
        
        return $result;
    }

    /**
     * Delete a PPI user
     * @param int $id
     * @return boolean
     */
    public function deletePPIUser($id) {
        $this->validateSuperAdmin();

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
            $this->loadUsers();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    } 
}