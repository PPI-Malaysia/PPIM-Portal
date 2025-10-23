<?php
// API endpoint for team members management
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

// Define ROOT_PATH relative to this file's location
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../..') . '/');
}

require_once(ROOT_PATH . "assets/php/team-members.php");

$team = new TeamMembers();

// Check authentication
if (!$team->isLoggedIn()) {
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
if (count($segments) >= 3 && $segments[1] == 'team') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int)$segments[2] : null;
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single team member
            $member = $team->getTeamMember($id);
            
            if ($member) {
                echo json_encode([
                    'success' => true,
                    'data' => $member
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Team member not found'
                    ]
                ]);
            }
        } else {
            // Get list of team members
            $department = isset($_GET['department']) ? $_GET['department'] : null;
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'order';
            
            $data = $team->getTeamMembers($department, $sort);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'members' => $data
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new team member
        if (!$team->canCreate()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to create team members'
                ]
            ]);
            exit;
        }
        
        // Check if this is a file upload or JSON data
        $data = [];
        $image = null;
        
        if (isset($_FILES['image'])) {
            // Multipart form data with file
            $data = $_POST;
            $image = $_FILES['image'];
            
            // Parse JSON fields if they are strings
            if (isset($data['socialLinks']) && is_string($data['socialLinks'])) {
                $data['socialLinks'] = json_decode($data['socialLinks'], true);
            }
            if (isset($data['achievements']) && is_string($data['achievements'])) {
                $data['achievements'] = json_decode($data['achievements'], true);
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
        $missing = $team->validateRequiredFields($data, ['name', 'position']);
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
        
        $id = $team->createTeamMember($data, $image);
        
        if ($id) {
            http_response_code(201);
            $member = $team->getTeamMember($id);
            echo json_encode([
                'success' => true,
                'data' => $member,
                'message' => 'Team member created successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create team member'
                ]
            ]);
        }
        break;
        
    case 'PUT':
        // Update team member
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Team member ID required'
                ]
            ]);
            exit;
        }
        
        if (!$team->canEdit()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to edit team members'
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
        
        $success = $team->updateTeamMember($id, $data);
        
        if ($success) {
            $member = $team->getTeamMember($id);
            echo json_encode([
                'success' => true,
                'data' => $member,
                'message' => 'Team member updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update team member'
                ]
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete team member
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_ID',
                    'message' => 'Team member ID required'
                ]
            ]);
            exit;
        }
        
        if (!$team->canDelete()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions to delete team members'
                ]
            ]);
            exit;
        }
        
        $success = $team->deleteTeamMember($id);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Team member deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete team member'
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

