<?php
// document_operations.php - Handle AJAX operations for documents
// Flag this script as API mode to prevent HTML redirects in constructors
define('IS_API', true);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
// Ensure JSON header is sent before any output and suppress HTML errors in API mode
header('Content-Type: application/json; charset=UTF-8');
if (function_exists('ini_set')) { @ini_set('display_errors', '0'); }
// Buffer and discard any accidental output from includes
ob_start();
// Ensure fatal errors still return JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (ob_get_length()) { ob_clean(); }
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => 'Server error',
            'error' => $error['message']
        ]);
    }
});
require_once(__DIR__ . "/../documents.php");
if (ob_get_length()) { ob_clean(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$documents = new Documents();

if (!$documents->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'create':
        if (!$documents->canCreate()) {
            echo json_encode(['success' => false, 'message' => 'No permission to create documents']);
            exit;
        }
        
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? ''
        ];
        
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;
        
        $id = $documents->createDocument($data, $file);
        
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Document created successfully', 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create document']);
        }
        break;
        
    case 'update':
        if (!$documents->canEdit()) {
            echo json_encode(['success' => false, 'message' => 'No permission to edit documents']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        $data = [];
        if (isset($_POST['title'])) $data['title'] = $_POST['title'];
        if (isset($_POST['description'])) $data['description'] = $_POST['description'];
        if (isset($_POST['category'])) $data['category'] = $_POST['category'];
        
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;
        
        $success = $documents->updateDocument($id, $data, $file);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Document updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update document']);
        }
        break;
        
    case 'delete':
        if (!$documents->canDelete()) {
            echo json_encode(['success' => false, 'message' => 'No permission to delete documents']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $success = $documents->deleteDocument($id);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete document']);
        }
        break;
        
    case 'get':
        $id = $_POST['id'] ?? 0;
        $document = $documents->getDocument($id);
        
        if ($document) {
            echo json_encode(['success' => true, 'data' => $document]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Document not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

