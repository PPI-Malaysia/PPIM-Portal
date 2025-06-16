<?php
// credentials.php - User credential management functionality
require_once("main.php");

class Credentials extends ppim {
    /**
     * Constructor - initializes the credentials functionality
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Login a user with username and password
     * @param string $username
     * @param string $password
     * @return array Result of login attempt with success status and message
     */
    public function login($username, $password) {
        $username = trim($username);
        $result = ['success' => false, 'message' => ''];
        
        if (empty($username) || empty($password)) {
            $result['message'] = 'Please enter both username and password';
            return $result;
        }
        
        // Prepare and execute query using mysqli
        $stmt = $this->conn->prepare("SELECT id, name, password, salt, type FROM user WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $query_result = $stmt->get_result();
        
        if ($query_result->num_rows === 1) {
            $user = $query_result->fetch_assoc();
            // Verify password with salt
            $hashedPassword = hash('sha256', $password . $user['salt']);
            if ($hashedPassword === $user['password']) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['type'];
                
                $result['success'] = true;
                $result['message'] = 'Login successful';
                $result['user_id'] = $user['id'];
                $result['user_name'] = $user['name'];
                $result['user_type'] = $user['type'];
            } else {
                $result['message'] = 'Invalid username or password';
            }
        } else {
            $result['message'] = 'Invalid username or password';
        }
        
        $stmt->close();
        return $result;
    }
    
    /**
     * Change user password
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @return array Result of password change attempt with success status and message
     */
    public function changePassword($currentPassword, $newPassword, $confirmPassword) {
        $result = ['success' => false, 'message' => ''];
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $result['message'] = 'User not logged in';
            return $result;
        }
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $result['message'] = 'All fields are required';
            return $result;
        }
        
        // Check if new password and confirm password match
        if ($newPassword !== $confirmPassword) {
            $result['message'] = 'New password and confirm password do not match';
            return $result;
        }
        
        // Check if new password meets minimum length requirement
        if (strlen($newPassword) < 8) {
            $result['message'] = 'Password must be at least 8 characters long';
            return $result;
        }
        
        // Get user's current password and salt from database
        $stmt = $this->conn->prepare("SELECT password, salt FROM user WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $query_result = $stmt->get_result();
        
        if ($query_result->num_rows === 1) {
            $user = $query_result->fetch_assoc();
            
            // Verify current password with salt
            $hashedCurrentPassword = hash('sha256', $currentPassword . $user['salt']);
            
            if ($hashedCurrentPassword === $user['password']) {
                // Current password is correct, now update with new password
                // Generate a new salt for better security
                $newSalt = bin2hex(random_bytes(16));
                
                // Hash the new password with the new salt
                $hashedNewPassword = hash('sha256', $newPassword . $newSalt);
                
                // Update user password in database
                $updateStmt = $this->conn->prepare("UPDATE user SET password = ?, salt = ? WHERE id = ?");
                $updateStmt->bind_param("ssi", $hashedNewPassword, $newSalt, $this->user_id);
                
                if ($updateStmt->execute()) {
                    $result['success'] = true;
                    $result['message'] = 'Password updated successfully';
                } else {
                    $result['message'] = 'Error updating password: ' . $this->conn->error;
                }
                
                $updateStmt->close();
            } else {
                $result['message'] = 'Current password is incorrect';
            }
        } else {
            $result['message'] = 'User not found';
        }
        
        $stmt->close();
        return $result;
    }
    
    /**
     * Logout current user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Reset user properties
        $this->user_id = null;
        $this->user_name = null;
        $this->user_type = null;
        $this->isLoggedIn = false;
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Render the change password form
     */
    public function renderChangePasswordForm() {
        // Only render if user is logged in
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit();
        }
        
        include(ROOT_PATH . 'views/change_password.php');
    }
}
?>