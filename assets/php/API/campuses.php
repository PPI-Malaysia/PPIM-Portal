<?php
// API endpoint for campuses management
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

// Define ROOT_PATH relative to this file's location
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../..') . '/');
}

require_once(ROOT_PATH . "assets/php/campuses.php");

$campuses = new Campuses();

// Check authentication
if (!$campuses->isLoggedIn()) {
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
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// Get ID from URL if present
$id = null;
if (count($segments) >= 3 && $segments[1] == 'campuses') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int)$segments[2] : null;
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single campus
            $campus = $campuses->getCampus($id);
            
            if ($campus) {
                echo json_encode([
                    'success' => true,
                    'data' => $campus
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Campus not found'
                    ]
                ]);
            }
        } else {
            // Get list of campuses
            $city = isset($_GET['city']) ? $_GET['city'] : null;
            $state = isset($_GET['state']) ? $_GET['state'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            
            $data = $campuses->getCampuses($city, $state, $search);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'campuses' => $data
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new campus
        if (!$campuses->canCreate()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to create campuses'
                ]
            ]);
            exit;
        }
        
        // Check if this is a file upload or JSON data
        $data = [];
        $logo = null;
        $cover = null;
        
        if (isset($_FILES['logo']) || isset($_FILES['cover'])) {
            // Multipart form data with files
            $data = $_POST;
            $logo = isset($_FILES['logo']) ? $_FILES['logo'] : null;
            $cover = isset($_FILES['cover']) ? $_FILES['cover'] : null;
            
            // Parse JSON fields if they are strings
            if (isset($data['address']) && is_string($data['address'])) {
                $data['address'] = json_decode($data['address'], true);
            }
            if (isset($data['socialLinks']) && is_string($data['socialLinks'])) {
                $data['socialLinks'] = json_decode($data['socialLinks'], true);
            }
            if (isset($data['programs']) && is_string($data['programs'])) {
                $data['programs'] = json_decode($data['programs'], true);
            }
        } else {
            // JSON data
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_JSON',
                        'message' => 'Invalid JSON data'
                    ]
                ]);
                exit;
            }
        }
        
        // Validate required fields
        $missing = $campuses->validateRequiredFields($data, ['name']);
        if ($missing) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ]
            ]);
            exit;
        }
        
        $id = $campuses->createCampus($data, $logo, $cover);
        
        if ($id) {
            http_response_code(201);
            $campus = $campuses->getCampus($id);
            echo json_encode([
                'success' => true,
                'data' => $campus,
                'message' => 'Campus created successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create campus'
                ]
            ]);
        }
        break;
        
    case 'PUT':
        // Update campus
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Campus ID required'
                ]
            ]);
            exit;
        }
        
        if (!$campuses->canEdit()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to edit campuses'
                ]
            ]);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_JSON',
                    'message' => 'Invalid JSON data'
                ]
            ]);
            exit;
        }
        
        $success = $campuses->updateCampus($id, $data);
        
        if ($success) {
            $campus = $campuses->getCampus($id);
            echo json_encode([
                'success' => true,
                'data' => $campus,
                'message' => 'Campus updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update campus'
                ]
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete campus
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Campus ID required'
                ]
            ]);
            exit;
        }
        
        if (!$campuses->canDelete()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to delete campuses'
                ]
            ]);
            exit;
        }
        
        $success = $campuses->deleteCampus($id);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Campus deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete campus'
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
                'message' => 'Invalid request method'
            ]
        ]);
        break;
}
?>

