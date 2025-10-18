<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../conf.php';
$error = '';
// Check if already logged in
if (isset($_SESSION['user_id'])) {
    echo "You are already logged in. Redirecting...";
    header('Location: ../../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Prepare and execute query using mysqli
        $stmt = $conn->prepare("SELECT id, name, password, salt, type FROM user WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            $isValid = false;
            
            // 1) Preferred: salted SHA-256 (current system)
            if (!empty($user['salt'])) {
                $hashedPassword = hash('sha256', $password . $user['salt']);
                if (hash_equals($user['password'], $hashedPassword)) {
                    $isValid = true;
                }
            }
            
            // 2) Backward compatibility: password_hash (bcrypt/argon2) if salt empty or not matching
            if (!$isValid && (empty($user['salt']) || preg_match('/^\$2y\$|^\$2a\$|^\$2b\$|^\$argon2/', (string)$user['password']))) {
                if (function_exists('password_verify') && password_verify($password, (string)$user['password'])) {
                    $isValid = true;
                }
            }
            
            if ($isValid) {
                //Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['type'];
                
                // Redirect based on user type
                echo "Hello ".$user['name'].$user['type']."! Login successful. Redirecting...";
                header('Location: ../../../index.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
        
        $stmt->close();
    }
    
    // Only redirect to login page if there's an error
    if (!empty($error)) {
        echo $error;
        header("Location:../../../login.php?error=$error");
        exit();
    }
}
?>