<?php
// API endpoint for enhanced team management (periods, departments, members)
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

// Define ROOT_PATH
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../..') . '/');
}

require_once(ROOT_PATH . "assets/php/team-management-v2.php");

$team = new TeamManagementV2();

// Check authentication
if (!$team->isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required']
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Get the path info - handle both clean URLs, direct file access, and query parameters
$pathInfo = '';

// First, check for query parameter fallback (e.g., ?resource=members&id=1)
if (isset($_GET['resource'])) {
    $pathInfo = '/' . $_GET['resource'];
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $pathInfo .= '/' . $_GET['id'];
    }
    if (isset($_GET['subresource']) && !empty($_GET['subresource'])) {
        $pathInfo .= '/' . $_GET['subresource'];
    }
    if (isset($_GET['subid']) && !empty($_GET['subid'])) {
        $pathInfo .= '/' . $_GET['subid'];
    }
} elseif (isset($_SERVER['PATH_INFO'])) {
    $pathInfo = $_SERVER['PATH_INFO'];
} elseif (isset($_SERVER['REQUEST_URI'])) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Check for clean URL pattern /api/team-v2/...
    if (preg_match('#/api/team-v2/(.*)$#', $uri, $matches)) {
        $pathInfo = '/' . $matches[1];
    } elseif (preg_match('#/api/team/(.*)$#', $uri, $matches)) {
        $pathInfo = '/' . $matches[1];
    } elseif (preg_match('#team-v2\.php/(.*)$#', $uri, $matches)) {
        // Handle direct file access with path info: team-v2.php/members
        $pathInfo = '/' . $matches[1];
    }
}

$segments = array_values(array_filter(explode('/', trim($pathInfo, '/'))));

// Parse the endpoint structure
// Expected: /{resource}/{id?}/{subresource?}/{subid?}
$resource = isset($segments[0]) ? $segments[0] : null;
$id = isset($segments[1]) && !empty($segments[1]) ? $segments[1] : null;
$subResource = isset($segments[2]) ? $segments[2] : null;
$subId = isset($segments[3]) ? $segments[3] : null;

try {
    switch ($resource) {
        case 'periods':
            handlePeriods($team, $method, $id, $subResource, $subId);
            break;
        case 'departments':
            handleDepartments($team, $method, $id, $subResource);
            break;
        case 'members':
            handleMembers($team, $method, $id);
            break;
        case 'position-levels':
            handlePositionLevels($team);
            break;
        case 'icons':
            handleIcons($team);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]]);
}

// ============================================================
// PERIOD HANDLERS
// ============================================================

function handlePeriods($team, $method, $id, $subResource, $subId) {
    switch ($method) {
        case 'GET':
            if ($id) {
                if ($subResource === 'departments') {
                    // GET /api/team-v2/periods/{slug}/departments
                    if ($subId) {
                        // GET /api/team-v2/periods/{slug}/departments/{deptSlug}
                        $period = $team->getPeriod($id);
                        $dept = $team->getDepartment($subId);
                        if ($period && $dept) {
                            $dept['members'] = $team->getMembersByDepartment($period['id'], $dept['id']);
                            $dept['periodInfo'] = $team->getDepartmentPeriodInfo($dept['id'], $period['id']);
                            jsonResponse(['success' => true, 'data' => $dept]);
                        } else {
                            notFoundResponse('Department not found');
                        }
                    } else {
                        $departments = $team->getDepartmentsForPeriod($id);
                        jsonResponse(['success' => true, 'data' => ['departments' => $departments]]);
                    }
                } elseif ($subResource === 'structure') {
                    // GET /api/team-v2/periods/{slug}/structure
                    $structure = $team->getFullTeamStructure($id);
                    if ($structure) {
                        jsonResponse(['success' => true, 'data' => $structure]);
                    } else {
                        notFoundResponse('Period not found');
                    }
                } else {
                    // GET /api/team-v2/periods/{id}
                    $period = $team->getPeriod($id);
                    if ($period) {
                        jsonResponse(['success' => true, 'data' => $period]);
                    } else {
                        notFoundResponse('Period not found');
                    }
                }
            } else {
                // GET /api/team-v2/periods
                $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
                $periods = $team->getPeriods($activeOnly);
                jsonResponse(['success' => true, 'data' => ['periods' => $periods]]);
            }
            break;

        case 'POST':
            if (!$team->canCreate()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            $data = getJsonInput();
            $missing = validateRequired($data, ['name', 'startDate', 'endDate']);
            if ($missing) {
                validationErrorResponse($missing);
                return;
            }
            $periodId = $team->createPeriod($data);
            if ($periodId) {
                http_response_code(201);
                jsonResponse([
                    'success' => true,
                    'data' => $team->getPeriod($periodId),
                    'message' => 'Period created successfully'
                ]);
            } else {
                serverErrorResponse('Failed to create period');
            }
            break;

        case 'PUT':
            if (!$id) {
                badRequestResponse('Period ID required');
                return;
            }
            if (!$team->canEdit()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            
            if ($subResource === 'activate') {
                // PUT /api/team-v2/periods/{id}/activate
                $success = $team->activatePeriod($id);
                if ($success) {
                    jsonResponse(['success' => true, 'message' => 'Period activated']);
                } else {
                    serverErrorResponse('Failed to activate period');
                }
            } else {
                // PUT /api/team-v2/periods/{id}
                $data = getJsonInput();
                $success = $team->updatePeriod($id, $data);
                if ($success) {
                    jsonResponse(['success' => true, 'data' => $team->getPeriod($id), 'message' => 'Period updated']);
                } else {
                    serverErrorResponse('Failed to update period');
                }
            }
            break;

        case 'DELETE':
            if (!$id) {
                badRequestResponse('Period ID required');
                return;
            }
            if (!$team->canDelete()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            $success = $team->deletePeriod($id);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Period deleted']);
            } else {
                serverErrorResponse('Failed to delete period. Make sure no members are assigned to this period.');
            }
            break;

        default:
            methodNotAllowedResponse();
    }
}

// ============================================================
// DEPARTMENT HANDLERS
// ============================================================

function handleDepartments($team, $method, $id, $subResource) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $dept = $team->getDepartment($id);
                if ($dept) {
                    jsonResponse(['success' => true, 'data' => $dept]);
                } else {
                    notFoundResponse('Department not found');
                }
            } else {
                $activeOnly = !isset($_GET['all']) || $_GET['all'] !== 'true';
                $departments = $team->getDepartments($activeOnly);
                jsonResponse(['success' => true, 'data' => ['departments' => $departments]]);
            }
            break;

        case 'POST':
            if (!$team->canCreate()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            $data = getJsonInput();
            $missing = validateRequired($data, ['name']);
            if ($missing) {
                validationErrorResponse($missing);
                return;
            }
            $deptId = $team->createDepartment($data);
            if ($deptId) {
                http_response_code(201);
                jsonResponse([
                    'success' => true,
                    'data' => $team->getDepartment($deptId),
                    'message' => 'Department created successfully'
                ]);
            } else {
                serverErrorResponse('Failed to create department');
            }
            break;

        case 'PUT':
            if (!$team->canEdit()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            
            if ($subResource === 'reorder') {
                // PUT /api/team-v2/departments/reorder
                $data = getJsonInput();
                if (empty($data['order'])) {
                    badRequestResponse('Order map required');
                    return;
                }
                $success = $team->reorderDepartments($data['order']);
                if ($success) {
                    jsonResponse(['success' => true, 'message' => 'Departments reordered']);
                } else {
                    serverErrorResponse('Failed to reorder departments');
                }
            } elseif ($id) {
                // PUT /api/team-v2/departments/{id}
                $data = getJsonInput();
                $success = $team->updateDepartment($id, $data);
                if ($success) {
                    jsonResponse(['success' => true, 'data' => $team->getDepartment($id), 'message' => 'Department updated']);
                } else {
                    serverErrorResponse('Failed to update department');
                }
            } else {
                badRequestResponse('Department ID required');
            }
            break;

        case 'DELETE':
            if (!$id) {
                badRequestResponse('Department ID required');
                return;
            }
            if (!$team->canDelete()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            $success = $team->deleteDepartment($id);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Department deleted']);
            } else {
                serverErrorResponse('Failed to delete department');
            }
            break;

        default:
            methodNotAllowedResponse();
    }
}

// ============================================================
// MEMBER HANDLERS
// ============================================================

function handleMembers($team, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                if ($id === 'reorder') {
                    methodNotAllowedResponse();
                    return;
                }
                $member = $team->getMember($id);
                if ($member) {
                    jsonResponse(['success' => true, 'data' => $member]);
                } else {
                    notFoundResponse('Member not found');
                }
            } else {
                // Build filters from query params
                $filters = [];
                if (isset($_GET['periodId'])) $filters['periodId'] = $_GET['periodId'];
                if (isset($_GET['departmentId'])) $filters['departmentId'] = $_GET['departmentId'];
                if (isset($_GET['positionLevel'])) $filters['positionLevel'] = $_GET['positionLevel'];
                if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
                if (isset($_GET['active'])) $filters['isActive'] = $_GET['active'] === 'true';
                
                $members = $team->getMembers($filters);
                jsonResponse(['success' => true, 'data' => ['members' => $members]]);
            }
            break;

        case 'POST':
            // Check for _method override (for PUT with file uploads)
            if (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
                // Handle as PUT with file upload
                if (!$team->canEdit()) {
                    forbiddenResponse('Insufficient permissions');
                    return;
                }
                
                if (!$id) {
                    badRequestResponse('Member ID required for update');
                    return;
                }
                
                $data = $_POST;
                $image = isset($_FILES['image']) ? $_FILES['image'] : null;
                unset($data['_method']); // Remove method override from data
                
                $success = $team->updateMember($id, $data, $image);
                if ($success) {
                    jsonResponse(['success' => true, 'data' => $team->getMember($id), 'message' => 'Member updated successfully']);
                } else {
                    serverErrorResponse('Failed to update member');
                }
                return;
            }
            
            if (!$team->canCreate()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            
            // Handle both JSON and multipart form data
            $data = [];
            $image = null;
            
            if (isset($_FILES['image'])) {
                $data = $_POST;
                $image = $_FILES['image'];
            } else {
                $data = getJsonInput();
            }
            
            $missing = validateRequired($data, ['periodId', 'name', 'position', 'positionLevel']);
            if ($missing) {
                validationErrorResponse($missing);
                return;
            }
            
            $memberId = $team->createMember($data, $image);
            if ($memberId) {
                http_response_code(201);
                jsonResponse([
                    'success' => true,
                    'data' => $team->getMember($memberId),
                    'message' => 'Member created successfully'
                ]);
            } else {
                serverErrorResponse('Failed to create member');
            }
            break;

        case 'PUT':
            if (!$team->canEdit()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            
            if ($id === 'reorder') {
                // PUT /api/team-v2/members/reorder
                $data = getJsonInput();
                if (empty($data['order'])) {
                    badRequestResponse('Order map required');
                    return;
                }
                $success = $team->reorderMembers($data['order']);
                if ($success) {
                    jsonResponse(['success' => true, 'message' => 'Members reordered']);
                } else {
                    serverErrorResponse('Failed to reorder members');
                }
            } elseif ($id) {
                // PUT /api/team-v2/members/{id}
                $data = getJsonInput();
                $success = $team->updateMember($id, $data);
                if ($success) {
                    jsonResponse(['success' => true, 'data' => $team->getMember($id), 'message' => 'Member updated']);
                } else {
                    serverErrorResponse('Failed to update member');
                }
            } else {
                badRequestResponse('Member ID required');
            }
            break;

        case 'DELETE':
            if (!$id) {
                badRequestResponse('Member ID required');
                return;
            }
            if (!$team->canDelete()) {
                forbiddenResponse('Insufficient permissions');
                return;
            }
            $success = $team->deleteMember($id);
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Member deleted']);
            } else {
                serverErrorResponse('Failed to delete member');
            }
            break;

        default:
            methodNotAllowedResponse();
    }
}

// ============================================================
// UTILITY HANDLERS
// ============================================================

function handlePositionLevels($team) {
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $levels = $team->getPositionLevels($type);
    jsonResponse(['success' => true, 'data' => $levels]);
}

function handleIcons($team) {
    $icons = $team->getAvailableIcons();
    jsonResponse(['success' => true, 'data' => $icons]);
}

// ============================================================
// RESPONSE HELPERS
// ============================================================

function jsonResponse($data) {
    echo json_encode($data);
    exit;
}

function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE && !empty($input)) {
        http_response_code(400);
        jsonResponse(['success' => false, 'error' => ['code' => 'INVALID_JSON', 'message' => 'Invalid JSON data']]);
    }
    return $data ?? [];
}

function validateRequired($data, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missing[] = $field;
        }
    }
    return empty($missing) ? null : $missing;
}

function badRequestResponse($message) {
    http_response_code(400);
    jsonResponse(['success' => false, 'error' => ['code' => 'BAD_REQUEST', 'message' => $message]]);
}

function forbiddenResponse($message) {
    http_response_code(403);
    jsonResponse(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $message]]);
}

function notFoundResponse($message) {
    http_response_code(404);
    jsonResponse(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $message]]);
}

function validationErrorResponse($missing) {
    http_response_code(422);
    jsonResponse([
        'success' => false,
        'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Missing required fields: ' . implode(', ', $missing)]
    ]);
}

function serverErrorResponse($message) {
    http_response_code(500);
    jsonResponse(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $message]]);
}

function methodNotAllowedResponse() {
    http_response_code(405);
    jsonResponse(['success' => false, 'error' => ['code' => 'METHOD_NOT_ALLOWED', 'message' => 'Method not allowed']]);
}
?>
