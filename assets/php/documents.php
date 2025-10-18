<?php
// documents.php - Documents management class
require_once("content-management.php");

class Documents extends ContentManagement {
    
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
            return false;
        }
        
        $fileUrl = null;
        $fileSize = null;
        $fileType = null;
        
        // Handle file upload if provided
        if ($file && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
            $allowedTypes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];
            
            $uploadResult = $this->handleFileUpload($file, 'documents', $allowedTypes, 20971520); // 20MB max
            
            if (!$uploadResult['success']) {
                return false;
            }
            
            $fileUrl = $uploadResult['path'];
            $fileSize = $this->formatFileSize($file['size']);
            
            // Get file type
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileType = $extension;
        } elseif (isset($data['file_url'])) {
            // File URL provided directly (for external files)
            $fileUrl = $data['file_url'];
            $fileSize = $data['file_size'] ?? null;
            $fileType = $data['file_type'] ?? null;
        }
        
        // Parse metadata if JSON string
        $metadata = null;
        if (isset($data['metadata'])) {
            $metadata = is_string($data['metadata']) ? $data['metadata'] : json_encode($data['metadata']);
        }
        
        $sql = "INSERT INTO documents (title, description, category, file_url, file_size, file_type, 
                thumbnail_url, uploaded_by, metadata) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssis",
            $data['title'],
            $data['description'],
            $data['category'],
            $fileUrl,
            $fileSize,
            $fileType,
            $data['thumbnail_url'] ?? null,
            $this->user_id,
            $metadata
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        
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
        
        $updates = [];
        $params = [];
        $types = "";
        
        // Handle file upload if provided
        if ($file && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
            // Get old document to delete old file
            $oldDoc = $this->getDocument($id);
            
            $allowedTypes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];
            
            $uploadResult = $this->handleFileUpload($file, 'documents', $allowedTypes, 20971520);
            
            if ($uploadResult['success']) {
                // Delete old file
                if ($oldDoc && !empty($oldDoc['fileUrl'])) {
                    $this->deleteFile($oldDoc['fileUrl']);
                }
                
                $updates[] = "file_url = ?";
                $params[] = $uploadResult['path'];
                $types .= "s";
                
                $updates[] = "file_size = ?";
                $fileSize = $this->formatFileSize($file['size']);
                $params[] = $fileSize;
                $types .= "s";
                
                $updates[] = "file_type = ?";
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $params[] = $extension;
                $types .= "s";
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
            $updates[] = "metadata = ?";
            $metadata = is_string($data['metadata']) ? $data['metadata'] : json_encode($data['metadata']);
            $params[] = $metadata;
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
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category'],
            'fileUrl' => $row['file_url'],
            'fileSize' => $row['file_size'],
            'fileType' => $row['file_type'],
            'thumbnail' => $row['thumbnail_url'],
            'uploadedAt' => $row['uploaded_at'],
            'updatedAt' => $row['updated_at'],
            'downloadCount' => $row['download_count'],
            'metadata' => $row['metadata'] ? json_decode($row['metadata'], true) : null
        ];
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

