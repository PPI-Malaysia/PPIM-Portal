<?php
require_once("assets/php/conf.php");


$message = '';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $type = $_POST['type'];
    
    // Validate input
    if (empty($name) || empty($password) || empty($type)) {
        $message = 'All fields are required';
    } else {
        // Generate a random salt
        $salt = bin2hex(random_bytes(16)); // 32 character salt
        
        // Hash the password with the salt
        $hashedPassword = hash('sha256', $password . $salt);
        
        // Prepare and execute query using mysqli
        $stmt = $conn->prepare("INSERT INTO user (name, password, salt, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $hashedPassword, $salt, $type);
        
        if ($stmt->execute()) {
            $message = 'User created successfully';
        } else {
            $message = 'Error creating user: ' . $conn->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel - Create User</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    .container {
        max-width: 500px;
        margin: 0 auto;
    }

    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }

    form {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input,
    select {
        width: 100%;
        padding: 8px;
        margin-bottom: 15px;
        box-sizing: border-box;
    }

    button {
        background: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
    }

    button:hover {
        background: #45a049;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Create New User</h1>

        <?php if (!empty($message)): ?>
        <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form method="post">
            <div>
                <label for="name">Username:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div>
                <label for="type">User Type:</label>
                <select id="type" name="type" required>
                    <option value="1">type 1</option>
                    <option value="2">type 2</option>
                    <option value="3">type 3</option>
                    <option value="4">type 4</option>
                    <option value="5">type 5</option>
                    <option value="6">Super Admin</option>
                </select>
            </div>

            <button type="submit">Create User</button>
        </form>

        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>