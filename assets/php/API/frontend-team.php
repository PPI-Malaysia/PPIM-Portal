<?php
/**
 * Frontend Public API for PPIM Website
 * 
 * This API provides team data for the PPIM Malaysia Website (ppimalaysia.id)
 * Routes:
 *   GET ?route=active-period     - Get active period team data
 *   GET ?route=periods           - Get all periods (for navigation/selector)
 *   GET ?route=period&slug=xxx   - Get specific period team data by slug
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once(__DIR__ . '/../team-management-v2.php');

// Portal base URL for images - CHANGE THIS FOR PRODUCTION
define('PORTAL_BASE_URL', 'https://portal.ppimalaysia.id');

/**
 * Convert relative path to full URL
 */
function toFullImageUrl($path) {
    if (empty($path)) return null;
    if (preg_match('#^https?://#i', $path)) return $path;
    $base = rtrim(PORTAL_BASE_URL, '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Format member for frontend output
 */
function formatMemberForFrontend($member) {
    if (!$member) return null;
    
    return [
        'id' => $member['id'],
        'name' => $member['name'],
        'position' => $member['position'],
        'positionLevel' => $member['positionLevel'],
        'positionLevelLabel' => $member['positionLevelLabel'],
        'imageUrl' => toFullImageUrl($member['imageUrl']),
        'bio' => $member['bio'],
        'email' => $member['email'],
        'phone' => $member['phone'],
        'linkedin' => $member['linkedin'],
        'instagram' => $member['instagram'],
        'university' => $member['university'],
        'major' => $member['major'],
        'displayOrder' => $member['displayOrder']
    ];
}

/**
 * Format period for frontend output
 */
function formatPeriodForFrontend($period) {
    return [
        'id' => $period['id'],
        'name' => $period['name'],
        'slug' => $period['slug'],
        'startDate' => $period['startDate'],
        'endDate' => $period['endDate'],
        'theme' => $period['theme'],
        'description' => $period['description'],
        'isActive' => $period['isActive']
    ];
}

/**
 * Build complete team structure response
 * This matches what PPIM Website expects
 */
function buildTeamResponse($team, $periodId) {
    global $team;
    
    // Initialize response structure as expected by PPIM Website
    $response = [
        'period' => null,
        'ketuaUmum' => null,
        'wakilKetua' => null,
        'sekretarisUmum' => null,
        'wakilSekretaris' => null,
        'bendaharaUmum' => null,
        'wakilBendahara' => null,
        'bpiList' => [],
        'departments' => []
    ];
    
    return $response;
}

/**
 * Main handler
 */
try {
    $team = new TeamManagementV2();
    
    $route = $_GET['route'] ?? 'active-period';
    
    switch ($route) {
        case 'active-period':
            $period = $team->getActivePeriod();
            if (!$period) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'No active period found']);
                exit;
            }
            echo json_encode([
                'success' => true,
                'data' => getTeamDataForPeriod($team, $period)
            ]);
            break;
            
        case 'periods':
            $periods = $team->getPeriods();
            $formatted = array_map('formatPeriodForFrontend', $periods);
            echo json_encode([
                'success' => true,
                'data' => $formatted
            ]);
            break;
            
        case 'period':
            $slug = $_GET['slug'] ?? null;
            if (!$slug) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing slug parameter']);
                exit;
            }
            
            $period = $team->getPeriod($slug);
            if (!$period) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Period not found']);
                exit;
            }
            echo json_encode([
                'success' => true,
                'data' => getTeamDataForPeriod($team, $period)
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown route: ' . $route]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

/**
 * Get complete team data for a period
 * Structures data as expected by PPIM Website
 */
function getTeamDataForPeriod($team, $period) {
    $periodId = $period['id'];
    
    // Get all members for this period
    $members = $team->getMembers(['periodId' => $periodId, 'isActive' => true]);
    
    // Get all departments
    $departments = $team->getDepartments(true); // active only
    
    // Initialize response structure
    $response = [
        'period' => formatPeriodForFrontend($period),
        'ketuaUmum' => null,
        'wakilKetua' => null,
        'sekretarisUmum' => null,
        'wakilSekretaris' => null,
        'bendaharaUmum' => null,
        'wakilBendahara' => null,
        'bpiList' => [],
        'departments' => []
    ];
    
    // Separate members into groups
    $biroMembers = []; // department_id IS NOT NULL and is_biro = true
    $deptMembers = []; // department_id IS NOT NULL and is_biro = false
    
    foreach ($members as $member) {
        // Core team (no department)
        if ($member['departmentId'] === null) {
            switch ($member['positionLevel']) {
                case 'ketua_umum':
                    $response['ketuaUmum'] = formatMemberForFrontend($member);
                    break;
                case 'wakil_ketua':
                    $response['wakilKetua'] = formatMemberForFrontend($member);
                    break;
                case 'sekretaris':
                    $response['sekretarisUmum'] = formatMemberForFrontend($member);
                    break;
                case 'wakil_sekretaris':
                    $response['wakilSekretaris'] = formatMemberForFrontend($member);
                    break;
                case 'bendahara':
                    $response['bendaharaUmum'] = formatMemberForFrontend($member);
                    break;
                case 'wakil_bendahara':
                    $response['wakilBendahara'] = formatMemberForFrontend($member);
                    break;
            }
        } else {
            // Department member
            $deptId = $member['departmentId'];
            if ($member['isBiro']) {
                if (!isset($biroMembers[$deptId])) {
                    $biroMembers[$deptId] = [];
                }
                $biroMembers[$deptId][] = $member;
            } else {
                if (!isset($deptMembers[$deptId])) {
                    $deptMembers[$deptId] = [];
                }
                $deptMembers[$deptId][] = $member;
            }
        }
    }
    
    // Process Biro members into bpiList
    foreach ($departments as $dept) {
        if (!$dept['isBiro']) continue;
        
        $deptId = $dept['id'];
        $deptMembersList = $biroMembers[$deptId] ?? [];
        
        // Find kepala biro, wakil kepala biro, and staff
        $kepalaBiro = null;
        $wakilKepalaBiro = null;
        $staffList = [];
        
        foreach ($deptMembersList as $m) {
            switch ($m['positionLevel']) {
                case 'kepala_biro':
                    $kepalaBiro = formatMemberForFrontend($m);
                    break;
                case 'wakil_kepala_biro':
                    $wakilKepalaBiro = formatMemberForFrontend($m);
                    break;
                case 'staff':
                    $staffList[] = formatMemberForFrontend($m);
                    break;
            }
        }
        
        // Sort staff by display order
        usort($staffList, fn($a, $b) => $a['displayOrder'] <=> $b['displayOrder']);
        
        $response['bpiList'][] = [
            'id' => $dept['id'],
            'name' => $dept['name'],
            'shortName' => $dept['shortName'],
            'slug' => $dept['slug'],
            'description' => $dept['description'],
            'icon' => $dept['icon'],
            'color' => $dept['color'],
            'displayOrder' => $dept['displayOrder'],
            'kepalaBiro' => $kepalaBiro,
            'wakilKepalaBiro' => $wakilKepalaBiro,
            'staffList' => $staffList
        ];
    }
    
    // Sort bpiList by display order
    usort($response['bpiList'], fn($a, $b) => $a['displayOrder'] <=> $b['displayOrder']);
    
    // Process Departemen members
    foreach ($departments as $dept) {
        if ($dept['isBiro']) continue;
        
        $deptId = $dept['id'];
        $deptMembersList = $deptMembers[$deptId] ?? [];
        
        // Find kepala dept, wakil kepala dept, and staff
        $kepalaDepartemen = null;
        $wakilKepalaDepartemen = null;
        $staffList = [];
        
        foreach ($deptMembersList as $m) {
            switch ($m['positionLevel']) {
                case 'kepala_dept':
                    $kepalaDepartemen = formatMemberForFrontend($m);
                    break;
                case 'wakil_kepala_dept':
                    $wakilKepalaDepartemen = formatMemberForFrontend($m);
                    break;
                case 'staff':
                    $staffList[] = formatMemberForFrontend($m);
                    break;
            }
        }
        
        // Sort staff by display order
        usort($staffList, fn($a, $b) => $a['displayOrder'] <=> $b['displayOrder']);
        
        // Get department period info
        $deptInfo = $team->getDepartmentPeriodInfo($deptId, $periodId);
        
        $response['departments'][] = [
            'id' => $dept['id'],
            'name' => $dept['name'],
            'shortName' => $dept['shortName'],
            'slug' => $dept['slug'],
            'description' => $dept['description'],
            'icon' => $dept['icon'],
            'color' => $dept['color'],
            'displayOrder' => $dept['displayOrder'],
            'kepalaDepartemen' => $kepalaDepartemen,
            'wakilKepalaDepartemen' => $wakilKepalaDepartemen,
            'staffList' => $staffList,
            'info' => $deptInfo ? [
                'vision' => $deptInfo['vision'],
                'mission' => $deptInfo['mission'],
                'programs' => $deptInfo['programs'],
                'achievements' => $deptInfo['achievements']
            ] : null
        ];
    }
    
    // Sort departments by display order
    usort($response['departments'], fn($a, $b) => $a['displayOrder'] <=> $b['displayOrder']);
    
    return $response;
}
