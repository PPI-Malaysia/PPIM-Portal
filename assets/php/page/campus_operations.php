<?php
// campus_operations.php - Handle AJAX operations for campuses
session_start();
require_once("../../campuses.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$campuses = new Campuses();

if (!$campuses->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'create':
        if (!$campuses->canCreate()) {
            echo json_encode(['success' => false, 'message' => 'No permission to create campuses']);
            exit;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'shortName' => $_POST['shortName'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'country' => $_POST['country'] ?? 'Malaysia',
            'contactEmail' => $_POST['contactEmail'] ?? '',
            'contactPhone' => $_POST['contactPhone'] ?? '',
            'description' => $_POST['description'] ?? '',
            'website' => $_POST['website'] ?? '',
            'studentCount' => $_POST['studentCount'] ?? 0,
            'establishedYear' => $_POST['establishedYear'] ?? null
        ];
        
        // Parse JSON fields
        if (isset($_POST['address'])) {
            $data['address'] = json_decode($_POST['address'], true);
        }
        if (isset($_POST['socialLinks'])) {
            $data['socialLinks'] = json_decode($_POST['socialLinks'], true);
        }
        if (isset($_POST['programs'])) {
            $data['programs'] = json_decode($_POST['programs'], true);
        }
        
        $logo = isset($_FILES['logo']) ? $_FILES['logo'] : null;
        $cover = isset($_FILES['cover']) ? $_FILES['cover'] : null;
        
        $id = $campuses->createCampus($data, $logo, $cover);
        
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Campus created successfully', 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create campus']);
        }
        break;
        
    case 'update':
        if (!$campuses->canEdit()) {
            echo json_encode(['success' => false, 'message' => 'No permission to edit campuses']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        $data = [];
        if (isset($_POST['name'])) $data['name'] = $_POST['name'];
        if (isset($_POST['shortName'])) $data['shortName'] = $_POST['shortName'];
        if (isset($_POST['city'])) $data['city'] = $_POST['city'];
        if (isset($_POST['state'])) $data['state'] = $_POST['state'];
        if (isset($_POST['country'])) $data['country'] = $_POST['country'];
        if (isset($_POST['contactEmail'])) $data['contactEmail'] = $_POST['contactEmail'];
        if (isset($_POST['contactPhone'])) $data['contactPhone'] = $_POST['contactPhone'];
        if (isset($_POST['description'])) $data['description'] = $_POST['description'];
        if (isset($_POST['website'])) $data['website'] = $_POST['website'];
        if (isset($_POST['studentCount'])) $data['studentCount'] = $_POST['studentCount'];
        if (isset($_POST['establishedYear'])) $data['establishedYear'] = $_POST['establishedYear'];
        
        // Parse JSON fields
        if (isset($_POST['address'])) {
            $data['address'] = json_decode($_POST['address'], true);
        }
        if (isset($_POST['socialLinks'])) {
            $data['socialLinks'] = json_decode($_POST['socialLinks'], true);
        }
        if (isset($_POST['programs'])) {
            $data['programs'] = json_decode($_POST['programs'], true);
        }
        
        $logo = isset($_FILES['logo']) ? $_FILES['logo'] : null;
        $cover = isset($_FILES['cover']) ? $_FILES['cover'] : null;
        
        $success = $campuses->updateCampus($id, $data, $logo, $cover);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Campus updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update campus']);
        }
        break;
        
    case 'delete':
        if (!$campuses->canDelete()) {
            echo json_encode(['success' => false, 'message' => 'No permission to delete campuses']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $success = $campuses->deleteCampus($id);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Campus deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete campus']);
        }
        break;
        
    case 'get':
        $id = $_POST['id'] ?? 0;
        $campus = $campuses->getCampus($id);
        
        if ($campus) {
            echo json_encode(['success' => true, 'data' => $campus]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Campus not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

