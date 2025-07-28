<?php
//note: this page is only accessible to users with user_type = 999 (super admin)
require_once("../user.php");
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SESSION['user_type'] != 999) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Super Admin only']);
    exit();
}
// Set the content type to JSON
header('Content-Type: application/json');

try {
    $main = new User();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get form data
    $name = trim($_POST['username']);
    $password = $_POST['password'];
    $type = intval($_POST['usertype']);
    
    // Validate input
    if (empty($name)) {
        throw new Exception('Username is required');
    }
    
    if (empty($password)) {
        throw new Exception('Password is required');
    }
    
    if (empty($type)) {
        throw new Exception('User type is required');
    }
    
    // Validate user type exists and is active
    $userTypes = $main->getUserTypes();
    $validTypes = array_column($userTypes, 'id');
    
    if (!in_array($type, $validTypes)) {
        throw new Exception('Invalid user type selected');
    }
    
    // Create user using the User class method
    $result = $main->addUser($name, $password, $type);
    
    if ($result) {
        //connect the $name with $university-details on ppi_campus_user
        $connectUser = $main->connectUniUser($name, $_POST['university-details']);
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully'
        ]);
    } else {
        throw new Exception('Failed to create user. Username may already exist.');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>