<?php
// API endpoint for documents management
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

// Define ROOT_PATH relative to this file's location
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../..') . '/');
}

require_once(ROOT_PATH . "assets/php/documents.php");

$documents = new Documents();

// Check authentication
if (!$documents->isLoggedIn()) {
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
if (count($segments) >= 3 && $segments[1] == 'documents') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int)$segments[2] : null;
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single document
            $document = $documents->getDocument($id);
            
            if ($document) {
                echo json_encode([
                    'success' => true,
                    'data' => $document
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Document not found'
                    ]
                ]);
            }
        } else {
            // Get list of documents
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            
            $data = $documents->getDocuments($page, $limit, $category, $search);
            $total = $documents->getTotalCount($category, $search);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'documents' => $data,
                    'pagination' => $documents->buildPagination($page, $limit, $total)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new document
        // Note: For file uploads, use multipart/form-data, not JSON
        if (!$documents->canCreate()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to create documents'
                ]
            ]);
            exit;
        }
        
        // Check if this is a file upload or JSON data
        $data = [];
        $file = null;
        
        if (isset($_FILES['file'])) {
            // Multipart form data with file
            $data = $_POST;
            $file = $_FILES['file'];
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
        $missing = $documents->validateRequiredFields($data, ['title', 'description', 'category']);
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
        
        $id = $documents->createDocument($data, $file);
        
        if ($id) {
            http_response_code(201);
            $document = $documents->getDocument($id);
            echo json_encode([
                'success' => true,
                'data' => $document,
                'message' => 'Document created successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create document'
                ]
            ]);
        }
        break;
        
    case 'PUT':
        // Update document
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Document ID required'
                ]
            ]);
            exit;
        }
        
        if (!$documents->canEdit()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to edit documents'
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
        
        $success = $documents->updateDocument($id, $data);
        
        if ($success) {
            $document = $documents->getDocument($id);
            echo json_encode([
                'success' => true,
                'data' => $document,
                'message' => 'Document updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update document'
                ]
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete document
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Document ID required'
                ]
            ]);
            exit;
        }
        
        if (!$documents->canDelete()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to delete documents'
                ]
            ]);
            exit;
        }
        
        $success = $documents->deleteDocument($id);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete document'
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

