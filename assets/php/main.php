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
            define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
        }
        
        require_once(ROOT_PATH . "assets/php/conf.php");
        $this->conn = $conn; // Store connection as property
        
        //check if user already logged in
        $this->checkLogin();
    }
    
    /**
     * Check if user is logged in and set properties
     * @return boolean
     */
    protected function checkLogin() {
        if (isset($_SESSION['user_name']) && isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->user_name = $_SESSION['user_name'];
            $this->user_type = $_SESSION['user_type'];
            $this->isLoggedIn = true;
            return true;
        } else {
            header('Location: ' . (defined('ROOT_PATH') ? ROOT_PATH : '') . 'login.php');
            exit();
        }
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
}
?>