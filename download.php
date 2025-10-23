<?php
// download.php - Stream stored files with correct headers

declare(strict_types=1);

define('IS_API', true);

// Define ROOT_PATH before any includes to ensure consistent path resolution
if (!defined('ROOT_PATH')) {
	define('ROOT_PATH', __DIR__ . '/');
}

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Include only what we need - bypass ContentManagement constructor
require_once __DIR__ . '/assets/php/main.php';

$type = isset($_GET['type']) ? strtolower((string)$_GET['type']) : 'document';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($type !== 'document') {
	http_response_code(400);
	echo 'Unsupported download type';
	exit;
}

if ($id <= 0) {
	http_response_code(400);
	echo 'Invalid or missing document ID';
	exit;
}

// Create a database connection directly without going through Documents class
$main = new ppim();

// Check for API key authentication (for server-to-server)
$apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : null;
$validApiKey = getenv('PPIM_API_KEY') ?: 'your-secure-api-key-here'; // Change this or set in environment

$isAuthenticated = false;

if ($apiKey && $apiKey === $validApiKey) {
	// API key authentication - bypass session check
	$isAuthenticated = true;
	error_log("Document download authenticated via API key");
} elseif ($main->isLoggedIn()) {
	// Session-based authentication
	$isAuthenticated = true;
	error_log("Document download authenticated via session");
}

if (!$isAuthenticated) {
	http_response_code(401);
	echo 'Unauthorized';
	exit;
}

// Fetch document directly from database
require_once __DIR__ . '/assets/php/conf.php';
$stmt = $conn->prepare("SELECT d.*, u.name as uploader_name FROM documents d LEFT JOIN user u ON d.uploaded_by = u.id WHERE d.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if (!$document) {
	http_response_code(404);
	echo json_encode([
		'success' => false,
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => 'Document not found'
		]
	]);
	exit;
}

$fileUrl = $document['file_url'] ?? '';
if (!$fileUrl) {
	http_response_code(404);
	echo json_encode([
		'success' => false,
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => 'File reference missing'
		]
	]);
	exit;
}

// Handle external URLs
if (preg_match('#^https?://#i', $fileUrl)) {
	// Increment download count
	$updateStmt = $conn->prepare("UPDATE documents SET download_count = download_count + 1 WHERE id = ?");
	$updateStmt->bind_param("i", $id);
	$updateStmt->execute();
	
	header('Location: ' . $fileUrl);
	exit;
}

// Resolve file path
function resolveFilePath($path) {
	if (empty($path)) {
		return null;
	}
	
	if (preg_match('#^https?://#i', $path)) {
		return $path;
	}
	
	$normalizedPath = str_replace('\\', '/', $path);
	$normalizedPath = ltrim($normalizedPath, '/');
	$fullPath = ROOT_PATH . $normalizedPath;
	
	if (file_exists($fullPath)) {
		return $fullPath;
	}
	
	$realPath = realpath($path);
	return $realPath !== false ? $realPath : null;
}

$fullPath = resolveFilePath($fileUrl);

// Enhanced debug logging
error_log("=== DOWNLOAD DEBUG ===");
error_log("Document ID: $id");
error_log("Document title: " . ($document['title'] ?? 'N/A'));
error_log("File URL from DB: $fileUrl");
error_log("ROOT_PATH: " . ROOT_PATH);
error_log("Resolved path: " . ($fullPath ?: 'NULL'));
if ($fullPath) {
	error_log("File exists: " . (file_exists($fullPath) ? 'YES' : 'NO'));
	error_log("Is regular file: " . (is_file($fullPath) ? 'YES' : 'NO'));
	error_log("Is readable: " . (is_readable($fullPath) ? 'YES' : 'NO'));
}
error_log("=====================");

if (!$fullPath || !is_file($fullPath)) {
	http_response_code(404);
	error_log("File not found at resolved path: " . ($fullPath ?: 'NULL'));
	echo json_encode([
		'success' => false,
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => 'Document file not found'
		]
	]);
	exit;
}

// Increment download count
$updateStmt = $conn->prepare("UPDATE documents SET download_count = download_count + 1 WHERE id = ?");
$updateStmt->bind_param("i", $id);
$updateStmt->execute();

// Get original filename from metadata
$metadata = isset($document['metadata']) ? json_decode($document['metadata'], true) : [];
$downloadName = $metadata['original_name'] ?? $document['title'] ?? basename($fullPath);
$downloadName = $downloadName ?: basename($fullPath);
$mimeType = $metadata['mime_type'] ?? mime_content_type($fullPath) ?: 'application/octet-stream';
$fileSize = filesize($fullPath);

if (ob_get_length()) {
	@ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
$encodedFileName = rawurlencode($downloadName);
$escapedFileName = addcslashes($downloadName, "\\\"");
header('Content-Disposition: attachment; filename="' . $escapedFileName . '"; filename*=UTF-8\'\'' . $encodedFileName);
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . $fileSize);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$chunkSize = 8192;
$handle = fopen($fullPath, 'rb');
if ($handle === false) {
	http_response_code(500);
	echo 'Unable to read file';
	exit;
}

while (!feof($handle)) {
	echo fread($handle, $chunkSize);
	flush();
}

fclose($handle);
exit;
