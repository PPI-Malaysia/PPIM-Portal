<?php
// content-management.php - Base class for content management system
require_once(__DIR__."/main.php");

class ContentManagement extends ppim {
    
    /**
     * Constructor - Initialize with access control
     */
    public function __construct() {
        parent::__construct();
        
        if (!$this->hasContentAccess()) {
            // In API mode, send error response instead of redirect
            if (defined('IS_API') && IS_API === true) {
                $this->sendError('ACCESS_DENIED', 'You do not have permission to access content management', 403);
            }
            header('Location: /index.php');
            exit();
        }
    }
    
    /**
     * Check if user has access to content management
     * @return boolean
     */
    protected function hasContentAccess() {
        return $this->hasPermission("content_view");
    }
    
    /**
     * Check if user can create content
     * @return boolean
     */
    public function canCreate() {
        return $this->hasPermission("content_create");
    }
    
    /**
     * Check if user can edit content
     * @return boolean
     */
    public function canEdit() {
        return $this->hasPermission("content_edit");
    }
    
    /**
     * Check if user can delete content
     * @return boolean
     */
    public function canDelete() {
        return $this->hasPermission("content_delete");
    }
    
    /**
     * Generate a URL-friendly slug from a string
     * @param string $text
     * @return string
     */
    protected function generateSlug($text) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace spaces with hyphens
        $text = str_replace(' ', '-', $text);
        
        // Remove special characters
        $text = preg_replace('/[^a-z0-9\-]/', '', $text);
        
        // Remove multiple consecutive hyphens
        $text = preg_replace('/-+/', '-', $text);
        
        // Trim hyphens from ends
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Ensure slug is unique by appending number if needed
     * @param string $slug
     * @param string $table
     * @param int|null $excludeId
     * @return string
     */
    protected function ensureUniqueSlug($slug, $table, $excludeId = null) {
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT COUNT(*) as count FROM $table WHERE slug = ?";
            $params = [$slug];
            $types = "s";
            
            if ($excludeId !== null) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
                $types .= "i";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['count'] == 0) {
                return $slug;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }
    
    /**
     * Handle file upload
     * @param array $file - The $_FILES array element
     * @param string $uploadDir - Directory to upload to (relative to assets/uploads/)
     * @param array $allowedTypes - Allowed MIME types
     * @param int $maxSize - Maximum file size in bytes (default: 10MB)
     * @return array - ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public function handleFileUpload($file, $uploadDir, $allowedTypes = [], $maxSize = 10485760) {
        $result = [
            'success' => false,
            'path' => null,
            'relativePath' => null,
            'filename' => null,
            'originalName' => null,
            'mimeType' => null,
            'sizeBytes' => null,
            'error' => null
        ];
		
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $result['error'] = 'No file uploaded';
            return $result;
        }
		
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'File upload error: ' . $file['error'];
            return $result;
        }
		
        if ($file['size'] > $maxSize) {
            $result['error'] = 'File too large. Maximum size: ' . $this->formatFileSize($maxSize);
            return $result;
        }
		
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }
		
        if (!$mimeType) {
            $result['error'] = 'Unable to determine file type';
            return $result;
        }
		
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            $result['error'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes);
            return $result;
        }
		
        $uploadsRoot = rtrim(ROOT_PATH, '/\\') . '/assets/uploads';
        if (!file_exists($uploadsRoot)) {
            if (!@mkdir($uploadsRoot, 0755, true)) {
                $result['error'] = 'Failed to create uploads directory: ' . $uploadsRoot;
                error_log($result['error']);
                return $result;
            }
        }
		
        if (!is_writable($uploadsRoot)) {
            $result['error'] = 'Uploads directory is not writable: ' . $uploadsRoot;
            error_log($result['error']);
            return $result;
        }
		
        $normalizedDir = trim($uploadDir, '/');
        $dateSegment = date('Y/m');
        $relativeDirectory = $normalizedDir ? $normalizedDir . '/' . $dateSegment : $dateSegment;
        $fullUploadDir = $uploadsRoot . '/' . $relativeDirectory;
        if (!file_exists($fullUploadDir)) {
            if (!@mkdir($fullUploadDir, 0755, true)) {
                $result['error'] = 'Failed to create upload subdirectory: ' . $fullUploadDir;
                error_log($result['error']);
                return $result;
            }
        }
		
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        try {
            $randomName = bin2hex(random_bytes(16));
        } catch (Exception $exception) {
            $randomName = uniqid('', true);
        }
        $filename = $randomName . ($extension ? '.' . $extension : '');
        $targetPath = $fullUploadDir . '/' . $filename;
		
        // Verify tmp file exists before moving
        if (!file_exists($file['tmp_name'])) {
            $result['error'] = 'Temporary file not found: ' . $file['tmp_name'];
            error_log($result['error']);
            return $result;
        }
		
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $result['success'] = true;
            $result['path'] = '/assets/uploads/' . $relativeDirectory . '/' . $filename;
            $result['relativePath'] = $relativeDirectory . '/' . $filename;
            $result['filename'] = $filename;
            $result['originalName'] = $file['name'];
            $result['mimeType'] = $mimeType;
            $result['sizeBytes'] = (int)$file['size'];
            error_log("File uploaded successfully: " . $targetPath);
        } else {
            $result['error'] = 'Failed to move uploaded file from ' . $file['tmp_name'] . ' to ' . $targetPath . ' (check permissions)';
            error_log($result['error']);
        }
		
        return $result;
    }

    protected function resolveStoragePath($path) {
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
    
    /**
     * Delete a file from the server
     * @param string $filePath - Relative path from root
     * @return boolean
     */
    protected function deleteFile($filePath) {
        if (empty($filePath)) {
            return false;
        }
        
        $fullPath = ROOT_PATH . ltrim($filePath, '/');
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Format file size to human-readable format
     * @param int $bytes
     * @return string
     */
    protected function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Calculate reading time from HTML content
     * @param string $content - HTML content
     * @return int - Reading time in minutes
     */
    protected function calculateReadingTime($content) {
        // Strip HTML tags
        $text = strip_tags($content);
        
        // Count words
        $wordCount = str_word_count($text);
        
        // Average reading speed: 200 words per minute
        $readingTime = ceil($wordCount / 200);
        
        return max(1, $readingTime); // Minimum 1 minute
    }
    
    /**
     * Sanitize HTML content
     * @param string $html
     * @return string
     */
    protected function sanitizeHTML($html) {
        // Allow common HTML tags for rich text
        $allowed_tags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre><table><tr><td><th><thead><tbody>';
        return strip_tags($html, $allowed_tags);
    }
    
    /**
     * Build pagination array
     * @param int $page
     * @param int $limit
     * @param int $total
     * @return array
     */
    protected function buildPagination($page, $limit, $total) {
        $totalPages = ceil($total / $limit);
        
        return [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $totalPages
        ];
    }
    
    /**
     * Send JSON response
     * @param array $data
     * @param int $statusCode
     */
    protected function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send success response
     * @param mixed $data
     * @param string $message
     */
    protected function sendSuccess($data = null, $message = '') {
        $response = ['success' => true];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if (!empty($message)) {
            $response['message'] = $message;
        }
        
        $this->sendJsonResponse($response);
    }
    
    /**
     * Send error response
     * @param string $code
     * @param string $message
     * @param int $statusCode
     */
    protected function sendError($code, $message, $statusCode = 400) {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
        
        $this->sendJsonResponse($response, $statusCode);
    }
    
    /**
     * Validate required fields
     * @param array $data
     * @param array $requiredFields
     * @return array|null - Returns array with missing fields or null if all present
     */
    protected function validateRequiredFields($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        return empty($missing) ? null : $missing;
    }
}
?>