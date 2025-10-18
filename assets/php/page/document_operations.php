<?php
// document_operations.php - Handle AJAX operations for documents
session_start();
require_once("../../documents.php");

header('Content-Type: application/json');

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

