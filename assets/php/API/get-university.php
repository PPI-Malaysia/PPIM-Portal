<?php
/**
 * API endpoint to get universities
 * Returns JSON formatted list of universities
 */

// Define API mode to prevent redirects in StudentDatabase constructor
define('API_MODE', true);

// Suppress warnings for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Load the StudentDatabase class
require_once(__DIR__ . "/../student-database.php");

try {
    // Initialize database connection
    $studentDB = new StudentDatabase();

    // Check authentication
    if (!$studentDB->hasFullAccess() && !$studentDB->isPPICampus()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Access denied. Insufficient permissions.'
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Get search parameter if provided
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;

    // Get universities using the StudentDatabase method
    $universities = $studentDB->getUniversities($search, $limit);

    // Return success response
    echo json_encode([
        'success' => true,
        'count' => count($universities),
        'data' => $universities
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Failed to fetch universities: ' . $e->getMessage()
        ]
    ], JSON_PRETTY_PRINT);
}
