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
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }
    
    $user_id = intval($_GET['id']);
    
    // Validate input
    if ($user_id <= 0) {
        throw new Exception('Invalid user ID');
    }
    
    // Don't allow editing super admin
    if ($user_id == 999) {
        throw new Exception('Cannot edit Super Admin account');
    }
    
    $user = $main->getUserById($user_id);
    
    if ($user) {
        echo json_encode([
            'success' => true, 
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'User not found'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>