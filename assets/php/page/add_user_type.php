<?php
require_once("../user.php");

header('Content-Type: application/json');
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
try {
    $main = new User();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $usertype_name = trim($_POST['usertype_name']);
    $usertype_description = trim($_POST['usertype_description']);
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    // Validate input
    if (empty($usertype_name)) {
        throw new Exception('User type name is required');
    }
    
    // Convert permission IDs to integers
    $permissions = array_map('intval', $permissions);
    
    $result = $main->createUserType($usertype_name, $usertype_description, $permissions);
    
    if ($result && is_array($result) && $result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'User type created successfully',
            'id' => $result['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create user type. All IDs (1-998) may be taken.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>