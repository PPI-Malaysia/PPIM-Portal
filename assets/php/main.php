<?php
// ppim.php - Core functionality for all pages

class ppim {
    protected $user_id = null;
    protected $user_name = null;
    protected $user_type = null;
    protected $isLoggedIn = false;
    protected $conn = null;
    
    /**
     * Constructor - initializes the base functionality
     */
    public function __construct() {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Connect to database - using absolute path resolution
        if (!defined('ROOT_PATH')) {
            $projectRoot = realpath(__DIR__ . '/../../');
            if ($projectRoot === false) {
                $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
                $projectRoot = !empty($documentRoot) ? $documentRoot : getcwd();
            }
            $projectRoot = str_replace('\\', '/', $projectRoot);
            $projectRoot = rtrim($projectRoot, '/') . '/';
            define('ROOT_PATH', $projectRoot);
        }
        
        require(ROOT_PATH . "assets/php/conf.php");
        $this->conn = $conn; // Store connection as property
        
        //check if user already logged in
        $this->checkLogin();
    }
    
    /**
     * Check if user is logged in and set properties
     * @return boolean
     */
    protected function checkLogin() {
        // If DEV_MODE is on, seed a dev session automatically
        if (defined('DEV_MODE') && DEV_MODE === true) {
            $_SESSION['user_id'] = defined('DEV_USER_ID') ? DEV_USER_ID : 1;
            $_SESSION['user_name'] = defined('DEV_USER_NAME') ? DEV_USER_NAME : 'Dev User';
            $_SESSION['user_type'] = defined('DEV_USER_TYPE') ? DEV_USER_TYPE : 999; // Super Admin
        }

        if (isset($_SESSION['user_name']) && isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->user_name = $_SESSION['user_name'];
            $this->user_type = $_SESSION['user_type'];
            $this->isLoggedIn = true;
            return true;
        } else {
            // In API mode, don't redirect. Let caller handle auth failure with JSON.
            if (defined('IS_API') && IS_API === true) {
                $this->isLoggedIn = false;
                return false;
            }
            // Use a relative path or absolute URL instead of filesystem path
            header('Location: /login.php');
            exit();
        }
    }

	/**
	 * Check if a table exists in the current database
	 * @param string $tableName
	 * @return bool
	 */
	protected function tableExists($tableName) {
		$sql = "SELECT COUNT(*) AS cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $tableName);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();
		return isset($result['cnt']) && (int)$result['cnt'] > 0;
	}

    /**
     * Check if current user has a specific permission
     * @param string $permission
     * @return boolean
     */
    public function hasPermission($permission) {
        // In development, optionally bypass permission checks entirely
        if (defined('DEV_MODE') && DEV_MODE === true) return true;
        // Super admin (type 999) has all permissions
        if ($this->user_type == 999) return true;

        // Gracefully handle missing tables
        if (!$this->tableExists('permissions') || !$this->tableExists('user_type_permissions')) {
            return false;
        }

        $query = "
            SELECT COUNT(*) as count
            FROM permissions p
            JOIN user_type_permissions utp ON p.id = utp.permission_id
            WHERE utp.user_type_id = ? AND p.name = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $this->user_type, $permission);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return isset($result['count']) && (int)$result['count'] > 0;
    }

    /**
     * Get all permissions for current user
     * @return array
     */
    public function getUserPermissions() {
        // In development, optionally return an empty array or all permissions; returning empty is safe
        if (defined('DEV_MODE') && DEV_MODE === true) {
            return [];
        }
        // Super admin (type 999) has all permissions
        if ($this->user_type == 999) {
            if (!$this->tableExists('permissions')) {
                return [];
            }
            $query = "SELECT name FROM permissions";
            $result = $this->conn->query($query);
            $permissions = [];
            while ($row = $result->fetch_assoc()) {
                $permissions[] = $row['name'];
            }
            return $permissions;
        }

        // Gracefully handle missing tables
        if (!$this->tableExists('permissions') || !$this->tableExists('user_type_permissions')) {
            return [];
        }

        $query = "
            SELECT p.name 
            FROM permissions p
            JOIN user_type_permissions utp ON p.id = utp.permission_id
            WHERE utp.user_type_id = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['name'];
        }
        return $permissions;
    }

    /**
     * Get username of logged in user
     * @return string|null
     */
    public function getUserName() {
        return $this->user_name;
    }
    
    /**
     * Get user ID of logged in user
     * @return int|null
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * Get user type of logged in user
     * @return int|null
     */
    public function getUserType() {
        return $this->user_type;
    }
    
    /**
     * Check if user is logged in
     * @return boolean
     */
    public function isLoggedIn() {
        return $this->isLoggedIn;
    }

    /**
     * Check user type
     * @param int $type
     * @return boolean
     */
    public function isUserType($type) {
        return $this->user_type == $type;
    }
    
    /**
     * Check user type if more than >$type1 and less than $type2
     * @param int $type1, $type2
     * @return boolean
     */
    public function isUserTypeRange($type1, $type2) {
        return $this->user_type >= $type1 && $this->user_type < $type2;
    }

    /**
     * Render the header part of the page
     */
    public function renderNavbar() {
        $main = $this;
        include(ROOT_PATH . 'views/navbar.php');
    }
    
    /**
     * Render the footer part of the page
     */
    public function renderTheme() {
        $main = $this;
        include(ROOT_PATH . 'views/theme.php');
    }

	    /**
	     * Store an alert message in session for later display
	     * @param string $message
	     * @param string $type One of: primary, secondary, success, danger, warning, info, light, dark
	     */
	    protected function showAlert($message, $type = 'info') {
	        if (session_status() == PHP_SESSION_NONE) {
	            session_start();
	        }
	        if (!isset($_SESSION['flash_messages']) || !is_array($_SESSION['flash_messages'])) {
	            $_SESSION['flash_messages'] = [];
	        }
	        $_SESSION['flash_messages'][] = [
	            'message' => (string)$message,
	            'type' => (string)$type,
	        ];
	    }
}
?>