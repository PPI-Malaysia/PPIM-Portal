<?php
// publication_operations.php - Handle AJAX operations for publications
header('Content-Type: application/json');
session_start();

// Define ROOT_PATH relative to this file's location (assets/php/page/publication_operations.php)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../..') . '/');
}

try {
    require_once(ROOT_PATH . 'assets/php/publications.php');

    // Debug logging
    error_log("=== Publication Operation Request ===");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Action: " . ($_POST['action'] ?? 'none'));
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES: " . print_r(array_keys($_FILES), true));
    error_log("Session ID: " . session_id());
    error_log("Session data: " . print_r($_SESSION, true));

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $publications = new Publications();

    if (!$publications->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action) {
        case 'create':
            if (!$publications->canCreate()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No permission to create publications']);
                exit;
            }

            // Basic server-side validation
            $required = ['title','excerpt','category','authorName'];
            foreach ($required as $field) {
                if (!isset($_POST[$field]) || $_POST[$field] === '') {
                    http_response_code(422);
                    echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
                    exit;
                }
            }

            $data = [
                'title' => $_POST['title'] ?? '',
                'excerpt' => $_POST['excerpt'] ?? '',
                'content' => $_POST['content'] ?? '',
                'category' => $_POST['category'] ?? '',
                'authorName' => $_POST['authorName'] ?? '',
                'authorAffiliation' => $_POST['authorAffiliation'] ?? null,
                'authorId' => $_POST['authorId'] ?? $publications->getUserId(),
                'publishedAt' => $_POST['publishedAt'] ?? date('Y-m-d H:i:s'),
                'featuredImage' => [
                    'url' => $_POST['featuredImageUrl'] ?? null,
                    'alt' => $_POST['featuredImageAlt'] ?? null
                ],
                'banner' => [
                    'url' => $_POST['bannerUrl'] ?? null,
                    'alt' => $_POST['bannerAlt'] ?? null
                ],
                'tags' => isset($_POST['tags']) ? explode(',', $_POST['tags']) : []
            ];
            
            // Handle featured image file upload
            if (isset($_FILES['featuredImageFile']) && $_FILES['featuredImageFile']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $uploadResult = $publications->handleFileUpload($_FILES['featuredImageFile'], 'publications/images', $allowedTypes, 5242880); // 5MB limit
                
                if ($uploadResult['success']) {
                    $data['featuredImage']['url'] = $uploadResult['path'];
                    $data['featuredImage']['alt'] = $uploadResult['originalName'];
                }
            }
            
            // Handle banner file upload
            if (isset($_FILES['bannerFile']) && $_FILES['bannerFile']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $uploadResult = $publications->handleFileUpload($_FILES['bannerFile'], 'publications/banners', $allowedTypes, 10485760); // 10MB limit
                
                if ($uploadResult['success']) {
                    $data['banner']['url'] = $uploadResult['path'];
                    $data['banner']['alt'] = $uploadResult['originalName'];
                }
            }

            try {
                $id = $publications->createPublication($data);
                // Save attachments if any
                if ($id && isset($_FILES['attachments'])) {
                    $publications->addAttachments($id, $_FILES['attachments']);
                }
            } catch (Throwable $e) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Create failed', 'error' => $e->getMessage()]);
                exit;
            }

            if ($id) {
                echo json_encode(['success' => true, 'message' => 'Publication created successfully', 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create publication']);
            }
            break;

        case 'update':
            if (!$publications->canEdit()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No permission to edit publications']);
                exit;
            }

            $id = $_POST['id'] ?? 0;

            $data = [];
            if (isset($_POST['title'])) $data['title'] = $_POST['title'];
            if (isset($_POST['excerpt'])) $data['excerpt'] = $_POST['excerpt'];
            if (isset($_POST['content'])) $data['content'] = $_POST['content'];
            if (isset($_POST['category'])) $data['category'] = $_POST['category'];
            if (isset($_POST['authorName'])) $data['authorName'] = $_POST['authorName'];
            if (isset($_POST['authorAffiliation'])) $data['authorAffiliation'] = $_POST['authorAffiliation'];
            if (isset($_POST['publishedAt'])) $data['publishedAt'] = $_POST['publishedAt'];

            // Handle featured image file upload
            if (isset($_FILES['featuredImageFile']) && $_FILES['featuredImageFile']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $uploadResult = $publications->handleFileUpload($_FILES['featuredImageFile'], 'publications/images', $allowedTypes, 5242880);
                
                if ($uploadResult['success']) {
                    $data['featuredImage'] = [
                        'url' => $uploadResult['path'],
                        'alt' => $uploadResult['originalName']
                    ];
                }
            } elseif (isset($_POST['featuredImageUrl']) || isset($_POST['featuredImageAlt'])) {
                $data['featuredImage'] = [
                    'url' => $_POST['featuredImageUrl'] ?? null,
                    'alt' => $_POST['featuredImageAlt'] ?? null
                ];
            }

            // Handle banner file upload
            if (isset($_FILES['bannerFile']) && $_FILES['bannerFile']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $uploadResult = $publications->handleFileUpload($_FILES['bannerFile'], 'publications/banners', $allowedTypes, 10485760);
                
                if ($uploadResult['success']) {
                    $data['banner'] = [
                        'url' => $uploadResult['path'],
                        'alt' => $uploadResult['originalName']
                    ];
                }
            } elseif (isset($_POST['bannerUrl']) || isset($_POST['bannerAlt'])) {
                $data['banner'] = [
                    'url' => $_POST['bannerUrl'] ?? null,
                    'alt' => $_POST['bannerAlt'] ?? null
                ];
            }

            if (isset($_POST['tags'])) {
                $data['tags'] = explode(',', $_POST['tags']);
            }

            $success = $publications->updatePublication($id, $data);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Publication updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update publication']);
            }
            break;

        case 'delete':
            if (!$publications->canDelete()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No permission to delete publications']);
                exit;
            }

            $id = $_POST['id'] ?? 0;
            $success = $publications->deletePublication($id);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Publication deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete publication']);
            }
            break;

        case 'get':
            $id = $_POST['id'] ?? 0;
            $publication = $publications->getPublication($id);

            if ($publication) {
                echo json_encode(['success' => true, 'data' => $publication]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Publication not found']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Throwable $e) {
    // Log the error for debugging
    error_log("Publication operation error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString() // Remove this in production
    ]);
}
?>

