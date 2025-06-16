<?php
require_once("../user.php");

header('Content-Type: application/json');
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//check if it's superadmin
if ($_SESSION['user_type'] != 999) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Super Admin only']);
    exit();
}
try {
    $main = new User();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $usertype = intval($_POST['usertype']);
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : null;
    
    // Validate input
    if ($user_id <= 0) {
        throw new Exception('Invalid user ID');
    }
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }
    
    if (empty($usertype)) {
        throw new Exception('User type is required');
    }
    
    // Don't allow editing super admin
    if ($user_id == 999) {
        throw new Exception('Cannot edit Super Admin account');
    }
    
    // Validate user type exists and is active
    $userTypes = $main->getUserTypes();
    $validTypes = array_column($userTypes, 'id');
    
    if (!in_array($usertype, $validTypes)) {
        throw new Exception('Invalid user type selected');
    }
    
    // Update user
    $result = $main->updateUser($user_id, $username, $usertype, $new_password);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update user. Username may already exist.');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>