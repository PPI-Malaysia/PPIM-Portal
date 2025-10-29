<?php
// documents.php - Documents management class
require_once(__DIR__."/content-management.php");

class Documents extends ContentManagement {
    
    private $lastError = null;
    
    /**
     * Get last error message
     * @return string|null
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * Get all documents with pagination and filters
     * @param int $page
     * @param int $limit
     * @param string|null $category
     * @param string|null $search
     * @return array
     */
    public function getDocuments($page = 1, $limit = 10, $category = null, $search = null) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT d.*, u.name as uploader_name 
                FROM documents d 
                LEFT JOIN user u ON d.uploaded_by = u.id";
        
        $where = [];
        $params = [];
        $types = "";
        
        if ($category) {
            $where[] = "d.category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($search) {
            $where[] = "(d.title LIKE ? OR d.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY d.uploaded_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $documents = [];
        while ($row = $result->fetch_assoc()) {
            $documents[] = $this->formatDocument($row);
        }
        
        return $documents;
    }
    
    /**
     * Get total count of documents with filters
     * @param string|null $category
     * @param string|null $search
     * @return int
     */
    public function getTotalCount($category = null, $search = null) {
        $sql = "SELECT COUNT(*) as total FROM documents";
        
        $where = [];
        $params = [];
        $types = "";
        
        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($search) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return (int)$result['total'];
    }
    
    /**
     * Get document by ID
     * @param int $id
     * @return array|null
     */
    public function getDocument($id) {
        $sql = "SELECT d.*, u.name as uploader_name 
                FROM documents d 
                LEFT JOIN user u ON d.uploaded_by = u.id 
                WHERE d.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->formatDocument($row);
        }
        
        return null;
    }
    
    /**
     * Create a new document
     * @param array $data
     * @param array|null $file - The uploaded file from $_FILES
     * @return int|false - Document ID or false
     */
    public function createDocument($data, $file = null) {
        if (!$this->canCreate()) {
            $this->lastError = 'No permission to create documents';
            return false;
        }
        
        $fileUrl = null;
        $fileSize = null;
        $fileType = null;
        $metadataArray = $this->normalizeMetadata($data['metadata'] ?? []);
		
        if ($file && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
            $allowedTypes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];
			
            $uploadResult = $this->handleFileUpload($file, 'documents', $allowedTypes, 20971520);
			
            if (!$uploadResult['success']) {
                $this->lastError = $uploadResult['error'] ?? 'File upload failed';
                error_log("Document upload failed: " . $this->lastError);
                return false;
            }
			
            $fileUrl = $uploadResult['path'];
            $fileSize = $this->formatFileSize($uploadResult['sizeBytes'] ?? ($file['size'] ?? 0));
            $fileType = strtolower(pathinfo($uploadResult['filename'] ?? ($file['name'] ?? ''), PATHINFO_EXTENSION));
			
            $fileMeta = [
                'original_name' => $uploadResult['originalName'] ?? ($file['name'] ?? null),
                'size_bytes' => $uploadResult['sizeBytes'] ?? ($file['size'] ?? null),
                'mime_type' => $uploadResult['mimeType'] ?? null,
                'relative_path' => $uploadResult['relativePath'] ?? null,
                'uploaded_at' => date('c')
            ];
            $metadataArray = array_merge($metadataArray, array_filter($fileMeta, function ($value) {
                return $value !== null;
            }));
        } elseif (isset($data['file_url'])) {
            $fileUrl = $data['file_url'];
            $fileSize = $data['file_size'] ?? null;
            $fileType = $data['file_type'] ?? null;
            $metadataArray = array_merge($metadataArray, array_filter([
                'original_name' => $data['file_name'] ?? null,
                'mime_type' => $data['file_mime'] ?? null,
                'size_bytes' => isset($data['file_size_bytes']) ? (int)$data['file_size_bytes'] : null
            ]));
        }
		
        $metadataJson = json_encode($metadataArray, JSON_UNESCAPED_SLASHES);
        if ($metadataJson === false) {
            $metadataJson = '{}';
        }
        
        $sql = "INSERT INTO documents (title, description, category, file_url, file_size, file_type, 
                thumbnail_url, uploaded_by, metadata) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        // Bind only variables (no expressions) due to mysqli bind_param by-reference semantics
        $title = isset($data['title']) ? $data['title'] : '';
        $description = isset($data['description']) ? $data['description'] : '';
        $category = isset($data['category']) ? $data['category'] : '';
        $thumbnailUrl = isset($data['thumbnail_url']) ? $data['thumbnail_url'] : null;
        $uploadedBy = $this->user_id;
        $metadataParam = $metadataJson;
        // Types: title(s), description(s), category(s), file_url(s), file_size(s), file_type(s), thumbnail_url(s), uploaded_by(i), metadata(s)
        $stmt->bind_param(
            "sssssssis",
            $title,
            $description,
            $category,
            $fileUrl,
            $fileSize,
            $fileType,
            $thumbnailUrl,
            $uploadedBy,
            $metadataParam
        );
        
        if ($stmt->execute()) {
            $this->lastError = null;
            return $stmt->insert_id;
        }
        
        $this->lastError = 'Database insert failed: ' . $this->conn->error;
        error_log("Document creation failed: " . $this->lastError);
        return false;
    }
    
    /**
     * Update a document
     * @param int $id
     * @param array $data
     * @param array|null $file - The uploaded file from $_FILES (optional)
     * @return boolean
     */
    public function updateDocument($id, $data, $file = null) {
        if (!$this->canEdit()) {
            return false;
        }
        
        $existingDocument = $this->getDocument($id);
        if (!$existingDocument) {
            return false;
        }
		
        $updates = [];
        $params = [];
        $types = "";
        $metadataArray = is_array($existingDocument['metadata']) ? $existingDocument['metadata'] : [];
        $metadataShouldUpdate = false;
		
        if ($file && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
            $allowedTypes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];
			
            $uploadResult = $this->handleFileUpload($file, 'documents', $allowedTypes, 20971520);
			
            if ($uploadResult['success']) {
                if (!empty($existingDocument['fileUrl'])) {
                    $this->deleteFile($existingDocument['fileUrl']);
                }
				
                $updates[] = "file_url = ?";
                $params[] = $uploadResult['path'];
                $types .= "s";
				
                $updates[] = "file_size = ?";
                $fileSize = $this->formatFileSize($uploadResult['sizeBytes'] ?? ($file['size'] ?? 0));
                $params[] = $fileSize;
                $types .= "s";
				
                $updates[] = "file_type = ?";
                $extension = strtolower(pathinfo($uploadResult['filename'] ?? ($file['name'] ?? ''), PATHINFO_EXTENSION));
                $params[] = $extension;
                $types .= "s";
				
                $fileMeta = [
                    'original_name' => $uploadResult['originalName'] ?? ($file['name'] ?? null),
                    'size_bytes' => $uploadResult['sizeBytes'] ?? ($file['size'] ?? null),
                    'mime_type' => $uploadResult['mimeType'] ?? null,
                    'relative_path' => $uploadResult['relativePath'] ?? null,
                    'updated_at' => date('c')
                ];
                $metadataArray = array_merge($metadataArray, array_filter($fileMeta, function ($value) {
                    return $value !== null;
                }));
                $metadataShouldUpdate = true;
            }
        }
		
        if (isset($data['title'])) {
            $updates[] = "title = ?";
            $params[] = $data['title'];
            $types .= "s";
        }
        
        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
            $types .= "s";
        }
        
        if (isset($data['category'])) {
            $updates[] = "category = ?";
            $params[] = $data['category'];
            $types .= "s";
        }
        
        if (isset($data['thumbnail_url'])) {
            $updates[] = "thumbnail_url = ?";
            $params[] = $data['thumbnail_url'];
            $types .= "s";
        }
        
        if (isset($data['metadata'])) {
            $incomingMetadata = $this->normalizeMetadata($data['metadata']);
            if (!empty($incomingMetadata)) {
                $metadataArray = array_merge($metadataArray, $incomingMetadata);
                $metadataShouldUpdate = true;
            }
        }
		
        if ($metadataShouldUpdate) {
            $metadataJson = json_encode($metadataArray, JSON_UNESCAPED_SLASHES);
            if ($metadataJson === false) {
                $metadataJson = '{}';
            }
            $updates[] = "metadata = ?";
            $params[] = $metadataJson;
            $types .= "s";
        }
        
        if (empty($updates)) {
            return true; // Nothing to update
        }
        
        $sql = "UPDATE documents SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a document
     * @param int $id
     * @return boolean
     */
    public function deleteDocument($id) {
        if (!$this->canDelete()) {
            return false;
        }
        
        // Get document to delete file
        $document = $this->getDocument($id);
        if ($document && !empty($document['fileUrl'])) {
            $this->deleteFile($document['fileUrl']);
        }
        
        if ($document && !empty($document['thumbnail'])) {
            $this->deleteFile($document['thumbnail']);
        }
        
        $stmt = $this->conn->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Increment download count
     * @param int $id
     * @return boolean
     */
    public function incrementDownloadCount($id) {
        $stmt = $this->conn->prepare("UPDATE documents SET download_count = download_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Format document data for output
     * @param array $row
     * @return array
     */
    private function formatDocument($row) {
        $metadata = $this->normalizeMetadata($row['metadata'] ?? null);
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category'],
            'fileUrl' => $row['file_url'],
            'downloadUrl' => 'download.php?type=document&id=' . $row['id'],
            'fileSize' => $row['file_size'],
            'fileSizeBytes' => isset($metadata['size_bytes']) ? (int)$metadata['size_bytes'] : null,
            'fileType' => $row['file_type'],
            'fileMimeType' => $metadata['mime_type'] ?? null,
            'originalFileName' => $metadata['original_name'] ?? null,
            'storagePath' => $metadata['relative_path'] ?? null,
            'thumbnail' => $row['thumbnail_url'],
            'uploadedAt' => $row['uploaded_at'],
            'updatedAt' => $row['updated_at'],
            'downloadCount' => $row['download_count'],
            'metadata' => $metadata
        ];
    }

    private function normalizeMetadata($metadata) {
        if (empty($metadata)) {
            return [];
        }
		
        if (is_array($metadata)) {
            return $metadata;
        }
		
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
		
        return [];
    }

    public function resolveFilePath($path) {
        return $this->resolveStoragePath($path);
    }
    
    /**
     * Get all categories
     * @return array
     */
    public function getCategories() {
        $stmt = $this->conn->query("SELECT DISTINCT category FROM documents ORDER BY category");
        $categories = [];
        while ($row = $stmt->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        return $categories;
    }
}
?>