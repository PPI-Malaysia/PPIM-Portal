<?php
// Set the content type to JSON - MUST be before any output
header('Content-Type: application/json');

require_once("../user-ppi.php");
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if user has permission for PPI account creation
if (!isset($_SESSION['user_permissions']) || !in_array('student_db_add', $_SESSION['user_permissions'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - student_db_add permission required']);
    exit();
}

try {
    $main = new UserPPI();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get form data
    $name = trim($_POST['username']);
    $password = $_POST['password'];
    $type = 1000;
    $university_details = $_POST['university-details'];

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

    // Create user using the UserPPI class method
    $result = $main->addUser($name, $password);

    if ($result) {
        //connect the $name with $university-details on university_user table
        $connectUser = $main->connectUniUser($name, $university_details);
        echo json_encode([
            'success' => true,
            'message' => 'PPI campus user created successfully'
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