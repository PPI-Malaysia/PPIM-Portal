<?php
//note: this page is only accessible to users with user_type = 6 (super admin)
require_once("../conf.php");

// Set the content type to JSON
header('Content-Type: application/json');

// Check if already logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit();
}

if ($_SESSION['user_type'] != 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['username']);
    $password = $_POST['password'];
    $type = $_POST['usertype'];
    
    // Validate input
    if (empty($name) || empty($password) || empty($type)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit();
    } else {
        // Generate a random salt
        $salt = bin2hex(random_bytes(16)); // 32 character salt
        
        // Hash the password with the salt
        $hashedPassword = hash('sha256', $password . $salt);
        
        // Prepare and execute query using mysqli
        $stmt = $conn->prepare("INSERT INTO user (name, password, salt, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $hashedPassword, $salt, $type);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error creating user: ' . $conn->error
            ]);
        }
        
        $stmt->close();
        exit();
    }
} else {
    // If not a POST request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}
?>