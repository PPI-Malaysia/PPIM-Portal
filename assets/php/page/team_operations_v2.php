<?php
// team_operations_v2.php - Direct operations handler for team management
// This file provides a fallback for environments without URL rewriting

header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../..') . '/');
}

require_once(ROOT_PATH . "assets/php/team-management-v2.php");

$team = new TeamManagementV2();

// Check authentication
if (!$team->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_POST['id'] ?? $_GET['id'] ?? null;

try {
    switch ($action) {
        // Period operations
        case 'get_periods':
            $periods = $team->getPeriods();
            jsonResponse(['success' => true, 'data' => $periods]);
            break;

        case 'get_period':
            if (!$id) badRequest('Period ID required');
            $period = $team->getPeriod($id);
            if ($period) {
                jsonResponse(['success' => true, 'data' => $period]);
            } else {
                notFound('Period not found');
            }
            break;

        case 'create_period':
            if (!$team->canCreate()) forbidden('Permission denied');
            $data = getPostData();
            $periodId = $team->createPeriod($data);
            if ($periodId) {
                jsonResponse(['success' => true, 'data' => $team->getPeriod($periodId), 'message' => 'Period created']);
            } else {
                serverError('Failed to create period');
            }
            break;

        case 'update_period':
            if (!$id) badRequest('Period ID required');
            if (!$team->canEdit()) forbidden('Permission denied');
            $data = getPostData();
            $success = $team->updatePeriod($id, $data);
            if ($success) {
                jsonResponse(['success' => true, 'data' => $team->getPeriod($id), 'message' => 'Period updated']);
            } else {
                serverError('Failed to update period');
            }
            break;

        case 'activate_period':
            if (!$id) badRequest('Period ID required');
            if (!$team->canEdit()) forbidden('Permission denied');
            $success = $team->activatePeriod($id);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Period activated']);
            } else {
                serverError('Failed to activate period');
            }
            break;

        case 'delete_period':
            if (!$id) badRequest('Period ID required');
            if (!$team->canDelete()) forbidden('Permission denied');
            $success = $team->deletePeriod($id);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Period deleted']);
            } else {
                serverError('Failed to delete period');
            }
            break;

        // Department operations
        case 'get_departments':
            $activeOnly = !isset($_GET['all']) || $_GET['all'] !== 'true';
            $departments = $team->getDepartments($activeOnly);
            jsonResponse(['success' => true, 'data' => $departments]);
            break;

        case 'get_department':
            if (!$id) badRequest('Department ID required');
            $department = $team->getDepartment($id);
            if ($department) {
                jsonResponse(['success' => true, 'data' => $department]);
            } else {
                notFound('Department not found');
            }
            break;

        case 'create_department':
            if (!$team->canCreate()) forbidden('Permission denied');
            $data = getPostData();
            $deptId = $team->createDepartment($data);
            if ($deptId) {
                jsonResponse(['success' => true, 'data' => $team->getDepartment($deptId), 'message' => 'Department created']);
            } else {
                serverError('Failed to create department');
            }
            break;

        case 'update_department':
            if (!$id) badRequest('Department ID required');
            if (!$team->canEdit()) forbidden('Permission denied');
            $data = getPostData();
            $success = $team->updateDepartment($id, $data);
            if ($success) {
                jsonResponse(['success' => true, 'data' => $team->getDepartment($id), 'message' => 'Department updated']);
            } else {
                serverError('Failed to update department');
            }
            break;

        case 'reorder_departments':
            if (!$team->canEdit()) forbidden('Permission denied');
            $data = getPostData();
            if (empty($data['order'])) badRequest('Order map required');
            $success = $team->reorderDepartments($data['order']);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Departments reordered']);
            } else {
                serverError('Failed to reorder departments');
            }
            break;

        case 'delete_department':
            if (!$id) badRequest('Department ID required');
            if (!$team->canDelete()) forbidden('Permission denied');
            $success = $team->deleteDepartment($id);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Department deleted']);
            } else {
                serverError('Failed to delete department');
            }
            break;

        // Member operations
        case 'get_members':
            $filters = [];
            if (isset($_GET['periodId'])) $filters['periodId'] = $_GET['periodId'];
            if (isset($_GET['departmentId'])) $filters['departmentId'] = $_GET['departmentId'];
            if (isset($_GET['positionLevel'])) $filters['positionLevel'] = $_GET['positionLevel'];
            if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
            $members = $team->getMembers($filters);
            jsonResponse(['success' => true, 'data' => $members]);
            break;

        case 'get_member':
            if (!$id) badRequest('Member ID required');
            $member = $team->getMember($id);
            if ($member) {
                jsonResponse(['success' => true, 'data' => $member]);
            } else {
                notFound('Member not found');
            }
            break;

        case 'create_member':
            if (!$team->canCreate()) forbidden('Permission denied');
            $data = $_POST;
            $image = isset($_FILES['image']) ? $_FILES['image'] : null;
            $memberId = $team->createMember($data, $image);
            if ($memberId) {
                jsonResponse(['success' => true, 'data' => $team->getMember($memberId), 'message' => 'Member created']);
            } else {
                serverError('Failed to create member');
            }
            break;

        case 'update_member':
            if (!$id) badRequest('Member ID required');
            if (!$team->canEdit()) forbidden('Permission denied');
            $data = $_POST;
            $image = isset($_FILES['image']) ? $_FILES['image'] : null;
            $success = $team->updateMember($id, $data, $image);
            if ($success) {
                jsonResponse(['success' => true, 'data' => $team->getMember($id), 'message' => 'Member updated']);
            } else {
                serverError('Failed to update member');
            }
            break;

        case 'reorder_members':
            if (!$team->canEdit()) forbidden('Permission denied');
            $data = getPostData();
            if (empty($data['order'])) badRequest('Order map required');
            $success = $team->reorderMembers($data['order']);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Members reordered']);
            } else {
                serverError('Failed to reorder members');
            }
            break;

        case 'delete_member':
            if (!$id) badRequest('Member ID required');
            if (!$team->canDelete()) forbidden('Permission denied');
            $success = $team->deleteMember($id);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Member deleted']);
            } else {
                serverError('Failed to delete member');
            }
            break;

        // Utility operations
        case 'get_position_levels':
            $type = isset($_GET['type']) ? $_GET['type'] : null;
            $levels = $team->getPositionLevels($type);
            jsonResponse(['success' => true, 'data' => $levels]);
            break;

        case 'get_icons':
            $icons = $team->getAvailableIcons();
            jsonResponse(['success' => true, 'data' => $icons]);
            break;

        case 'get_team_structure':
            $periodId = $_GET['periodId'] ?? $_POST['periodId'] ?? null;
            if (!$periodId) badRequest('Period ID required');
            $structure = $team->getFullTeamStructure($periodId);
            if ($structure) {
                jsonResponse(['success' => true, 'data' => $structure]);
            } else {
                notFound('Period not found');
            }
            break;

        default:
            badRequest('Invalid action');
    }
} catch (Exception $e) {
    serverError($e->getMessage());
}

// Helper functions
function jsonResponse($data) {
    echo json_encode($data);
    exit;
}

function getPostData() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    return $_POST;
}

function badRequest($message) {
    http_response_code(400);
    jsonResponse(['success' => false, 'message' => $message]);
}

function forbidden($message) {
    http_response_code(403);
    jsonResponse(['success' => false, 'message' => $message]);
}

function notFound($message) {
    http_response_code(404);
    jsonResponse(['success' => false, 'message' => $message]);
}

function serverError($message) {
    http_response_code(500);
    jsonResponse(['success' => false, 'message' => $message]);
}
?>
