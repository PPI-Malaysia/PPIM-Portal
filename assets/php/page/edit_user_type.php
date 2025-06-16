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
    
    $usertype_id = intval($_POST['usertype_id']);
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    // Validate input - allow 1-998, block 999 (super admin)
    if ($usertype_id < 1 || $usertype_id > 998) {
        throw new Exception('Invalid user type ID');
    }
    
    if ($usertype_id == 999) {
        throw new Exception('Cannot edit Super Admin permissions');
    }
    
    // Convert permission IDs to integers
    $permissions = array_map('intval', $permissions);
    
    $result = $main->updateUserTypePermissions($usertype_id, $permissions);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User type updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user type']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>