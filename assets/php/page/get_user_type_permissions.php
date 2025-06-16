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
    
    $usertype_id = intval($_GET['id']);
    
    // Validate input - allow 999 for super admin viewing
    if ($usertype_id < 1 || $usertype_id > 999) {
        throw new Exception('Invalid user type ID');
    }
    
    // Don't allow editing super admin permissions
    if ($usertype_id == 999) {
        throw new Exception('Cannot edit Super Admin permissions');
    }
    
    $allPermissions = $main->getPermissions();
    $userPermissions = $main->getUserTypePermissions($usertype_id);
    
    echo json_encode([
        'success' => true, 
        'permissions' => $allPermissions,
        'userPermissions' => $userPermissions
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>