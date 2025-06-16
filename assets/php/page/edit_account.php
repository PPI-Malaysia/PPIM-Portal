<?php
// assets/php/page/account.php - Backend handler for account operations
header('Content-Type: application/json');

// Include the credentials class
require_once("../credentials.php");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }

    // Check if action is specified
    if (!isset($_POST['todo'])) {
        throw new Exception('No action specified');
    }

    // Instantiate credentials class
    $credentials = new Credentials();

    $response = ['success' => false, 'message' => ''];

    switch ($_POST['todo']) {
        case 'change_password':
            // Validate required fields
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Call the changePassword method
            $response = $credentials->changePassword($currentPassword, $newPassword, $confirmPassword);
            break;

        default:
            throw new Exception('Invalid action specified');
    }

    // Return JSON response
    echo json_encode($response);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>