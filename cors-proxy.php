<?php
/**
 * Image/File Proxy with CORS Support
 * Allows ppimalaysia.id to access uploaded files from portal.ppimalaysia.id
 * 
 * Usage: https://portal.ppimalaysia.id/cors-proxy.php?file=/assets/uploads/publications/images/2025/10/abc.jpg
 */

// Allow specific origins (most secure)
$allowedOrigins = [
    'https://ppimalaysia.id',
    'https://www.ppimalaysia.id',
    'http://localhost:3000', // For development
];

// Get the origin of the request
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Check if origin is allowed
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Max-Age: 3600");
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Get requested file path
$file = $_GET['file'] ?? '';

// Security: Validate file path
if (empty($file)) {
    http_response_code(400);
    die('No file specified');
}

// Security: Prevent directory traversal
$file = str_replace(['../', '..\\'], '', $file);

// Resolve absolute path
$rootPath = __DIR__;
$filePath = $rootPath . $file;

// Check if file exists
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die('File not found');
}

// Security: Only allow files from uploads directory
if (strpos(realpath($filePath), realpath($rootPath . '/assets/uploads')) !== 0) {
    http_response_code(403);
    die('Access denied');
}

// Get mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Output file
readfile($filePath);
exit;
?>
