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
    $usertype_id = intval($input['usertype_id']);
    
    // Validate input
    if ($usertype_id < 1 || $usertype_id > 998) {
        throw new Exception('Invalid user type ID');
    }
    
    $result = $main->deleteUserType($usertype_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User type deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user type. Make sure no users are assigned to this type.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>