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
    
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = intval($input['user_id']);
    
    // Validate input
    if ($user_id <= 0) {
        throw new Exception('Invalid user ID');
    }
    
    // Don't allow deleting super admin
    if ($user_id == 999) {
        throw new Exception('Cannot delete Super Admin account');
    }
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }
    
    $result = $main->deleteUser($user_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete user');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>