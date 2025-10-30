<?php
/**
 * API Headers and Authentication Helper
 * Include this file at the top of all API endpoints
 */

// Set JSON content type
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Check if request is authenticated via API key or session
 * @param object $instance - Instance of the class (e.g., Documents, Publications)
 * @param bool $publicReadAllowed - Allow public GET requests without authentication
 * @return bool
 */
function checkApiAuthentication($instance, $publicReadAllowed = false) {
    $isAuthenticated = false;
    
    // For public read access on GET requests
    if ($publicReadAllowed && $_SERVER['REQUEST_METHOD'] === 'GET') {
        return true;
    }
    
    // Check for API key in header
    $apiKey = null;
    if (isset($_SERVER['HTTP_X_API_KEY'])) {
        $apiKey = $_SERVER['HTTP_X_API_KEY'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $apiKey = $matches[1];
        }
    }
    
    // Validate API key if provided
    if ($apiKey && defined('API_KEY') && $apiKey === API_KEY) {
        $isAuthenticated = true;
    } elseif (method_exists($instance, 'isLoggedIn') && $instance->isLoggedIn()) {
        $isAuthenticated = true;
    }
    
    if (!$isAuthenticated) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Authentication required. Please provide X-API-Key header or valid session.'
            ]
        ]);
        exit;
    }
    
    return true;
}

/**
 * Send JSON error response
 * @param int $statusCode - HTTP status code
 * @param string $errorCode - Error code identifier
 * @param string $message - Error message
 */
function sendApiError($statusCode, $errorCode, $message) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $errorCode,
            'message' => $message
        ]
    ]);
    exit;
}

/**
 * Send JSON success response
 * @param mixed $data - Data to return
 * @param string|null $message - Optional success message
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendApiSuccess($data, $message = null, $statusCode = 200) {
    http_response_code($statusCode);
    $response = [
        'success' => true,
        'data' => $data
    ];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    echo json_encode($response);
    exit;
}
?>
