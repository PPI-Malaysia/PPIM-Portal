<?php
// API endpoint for dashboard statistics
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

// Define ROOT_PATH relative to this file's location
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../..') . '/');
}

require_once(ROOT_PATH . "assets/php/dashboard.php");

$dashboard = new Dashboard();

// Check authentication
if (!$dashboard->isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'UNAUTHORIZED',
            'message' => 'Authentication required'
        ]
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // Get all dashboard statistics
            $stats = $dashboard->getAllStats();

            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Failed to fetch dashboard statistics: ' . $e->getMessage()
                ]
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'METHOD_NOT_ALLOWED',
                'message' => 'Only GET method is allowed'
            ]
        ]);
        break;
}
?>
