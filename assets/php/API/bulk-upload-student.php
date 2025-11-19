<?php
/**
 * Bulk Upload Student API
 * Accepts one student record at a time for bulk upload
 * Only accessible to PPI Campus accounts
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Define API_MODE to prevent POST handling in constructor
define('API_MODE', true);

require_once(__DIR__ . "/../student-database.php");

try {
    $studentDB = new StudentDatabase();

    // Check if user is PPI Campus (not full access)
    if ($studentDB->hasFullAccess()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Bulk upload is only available for PPI Campus accounts']);
        exit;
    }

    if (!$studentDB->isPPICampus()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to upload students']);
        exit;
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
        exit;
    }

    // Use the new insertSingleStudent method
    $result = $studentDB->insertSingleStudent($data);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
