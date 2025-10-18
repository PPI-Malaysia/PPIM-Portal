<?php
// team_operations.php - Handle AJAX operations for team members
session_start();
require_once("../../team-members.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$team = new TeamMembers();

if (!$team->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'create':
        if (!$team->canCreate()) {
            echo json_encode(['success' => false, 'message' => 'No permission to create team members']);
            exit;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'position' => $_POST['position'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'department' => $_POST['department'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'order' => $_POST['order'] ?? 0,
            'joinedAt' => $_POST['joinedAt'] ?? null
        ];
        
        // Parse social links
        if (isset($_POST['socialLinks'])) {
            $data['socialLinks'] = json_decode($_POST['socialLinks'], true);
        }
        
        // Parse achievements
        if (isset($_POST['achievements'])) {
            $data['achievements'] = json_decode($_POST['achievements'], true);
        }
        
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;
        
        $id = $team->createTeamMember($data, $image);
        
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Team member created successfully', 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create team member']);
        }
        break;
        
    case 'update':
        if (!$team->canEdit()) {
            echo json_encode(['success' => false, 'message' => 'No permission to edit team members']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        $data = [];
        if (isset($_POST['name'])) $data['name'] = $_POST['name'];
        if (isset($_POST['position'])) $data['position'] = $_POST['position'];
        if (isset($_POST['bio'])) $data['bio'] = $_POST['bio'];
        if (isset($_POST['department'])) $data['department'] = $_POST['department'];
        if (isset($_POST['email'])) $data['email'] = $_POST['email'];
        if (isset($_POST['phone'])) $data['phone'] = $_POST['phone'];
        if (isset($_POST['order'])) $data['order'] = $_POST['order'];
        if (isset($_POST['joinedAt'])) $data['joinedAt'] = $_POST['joinedAt'];
        
        // Parse social links
        if (isset($_POST['socialLinks'])) {
            $data['socialLinks'] = json_decode($_POST['socialLinks'], true);
        }
        
        // Parse achievements
        if (isset($_POST['achievements'])) {
            $data['achievements'] = json_decode($_POST['achievements'], true);
        }
        
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;
        
        $success = $team->updateTeamMember($id, $data, $image);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Team member updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update team member']);
        }
        break;
        
    case 'delete':
        if (!$team->canDelete()) {
            echo json_encode(['success' => false, 'message' => 'No permission to delete team members']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $success = $team->deleteTeamMember($id);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Team member deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete team member']);
        }
        break;
        
    case 'get':
        $id = $_POST['id'] ?? 0;
        $member = $team->getTeamMember($id);
        
        if ($member) {
            echo json_encode(['success' => true, 'data' => $member]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Team member not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

