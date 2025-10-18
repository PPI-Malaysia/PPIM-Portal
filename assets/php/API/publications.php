<?php
// API endpoint for publications management
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once(ROOT_PATH . "assets/php/publications.php");

$publications = new Publications();

// Check authentication
if (!$publications->isLoggedIn()) {
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

// Get ID or slug from URL if present
$identifier = null;
if (count($segments) >= 3 && $segments[1] == 'publications') {
    $identifier = $segments[2] ?? null;
}

switch ($method) {
    case 'GET':
        if ($identifier) {
            // Get single publication by ID or slug
            $publication = $publications->getPublication($identifier);
            
            if ($publication) {
                echo json_encode([
                    'success' => true,
                    'data' => $publication
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Publication not found'
                    ]
                ]);
            }
        } else {
            // Get list of publications
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $tag = isset($_GET['tag']) ? $_GET['tag'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            
            $data = $publications->getPublications($page, $limit, $category, $tag, $search);
            $total = $publications->getTotalCount($category, $tag, $search);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'publications' => $data,
                    'pagination' => $publications->buildPagination($page, $limit, $total)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new publication
        if (!$publications->canCreate()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to create publications'
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
        
        // Validate required fields
        $missing = $publications->validateRequiredFields($data, ['title', 'excerpt', 'authorId', 'category']);
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
        
        $id = $publications->createPublication($data);
        
        if ($id) {
            http_response_code(201);
            $publication = $publications->getPublication($id);
            echo json_encode([
                'success' => true,
                'data' => $publication,
                'message' => 'Publication created successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create publication'
                ]
            ]);
        }
        break;
        
    case 'PUT':
        // Update publication
        if (!$identifier) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Publication ID required'
                ]
            ]);
            exit;
        }
        
        if (!$publications->canEdit()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to edit publications'
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
        
        $success = $publications->updatePublication($identifier, $data);
        
        if ($success) {
            $publication = $publications->getPublication($identifier);
            echo json_encode([
                'success' => true,
                'data' => $publication,
                'message' => 'Publication updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update publication'
                ]
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete publication
        if (!$identifier) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Publication ID required'
                ]
            ]);
            exit;
        }
        
        if (!$publications->canDelete()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to delete publications'
                ]
            ]);
            exit;
        }
        
        $success = $publications->deletePublication($identifier);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Publication deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete publication'
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

