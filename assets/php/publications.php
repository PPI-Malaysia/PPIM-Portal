<?php
// publications.php - Publications management class
require_once("content-management.php");

class Publications extends ContentManagement {
    
    /**
     * Get all publications with pagination and filters
     * @param int $page
     * @param int $limit
     * @param string|null $category
     * @param string|null $tag
     * @param string|null $search
     * @return array
     */
    public function getPublications($page = 1, $limit = 10, $category = null, $tag = null, $search = null) {
        $offset = ($page - 1) * $limit;
        
        // Build query
        $sql = "SELECT p.*, u.name as author_name 
                FROM publications p 
                LEFT JOIN user u ON p.author_id = u.id";
        
        $where = [];
        $params = [];
        $types = "";
        
        // Add filters
        if ($category) {
            $where[] = "p.category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($tag) {
            $sql .= " LEFT JOIN publication_tags pt ON p.id = pt.publication_id";
            $where[] = "pt.tag = ?";
            $params[] = $tag;
            $types .= "s";
        }
        
        if ($search) {
            $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.published_at DESC, p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $publications = [];
        while ($row = $result->fetch_assoc()) {
            $publications[] = $this->formatPublication($row, false);
        }
        
        return $publications;
    }
    
    /**
     * Get total count of publications with filters
     * @param string|null $category
     * @param string|null $tag
     * @param string|null $search
     * @return int
     */
    public function getTotalCount($category = null, $tag = null, $search = null) {
        $sql = "SELECT COUNT(DISTINCT p.id) as total FROM publications p";
        
        $where = [];
        $params = [];
        $types = "";
        
        if ($category) {
            $where[] = "p.category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($tag) {
            $sql .= " LEFT JOIN publication_tags pt ON p.id = pt.publication_id";
            $where[] = "pt.tag = ?";
            $params[] = $tag;
            $types .= "s";
        }
        
        if ($search) {
            $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
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
     * Get publication by ID or slug
     * @param string|int $identifier
     * @return array|null
     */
    public function getPublication($identifier) {
        if (is_numeric($identifier)) {
            $sql = "SELECT p.*, u.name as author_name, u.id as author_user_id 
                    FROM publications p 
                    LEFT JOIN user u ON p.author_id = u.id 
                    WHERE p.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $identifier);
        } else {
            $sql = "SELECT p.*, u.name as author_name, u.id as author_user_id 
                    FROM publications p 
                    LEFT JOIN user u ON p.author_id = u.id 
                    WHERE p.slug = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $identifier);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->formatPublication($row, true);
        }
        
        return null;
    }
    
    /**
     * Create a new publication
     * @param array $data
     * @return int|false - Publication ID or false
     */
    public function createPublication($data) {
        if (!$this->canCreate()) {
            return false;
        }
        
        // Generate slug
        $slug = $this->generateSlug($data['title']);
        $slug = $this->ensureUniqueSlug($slug, 'publications');
        
        // Calculate reading time
        $readingTime = isset($data['content']) ? $this->calculateReadingTime($data['content']) : 0;
        
        // Sanitize HTML content
        $content = isset($data['content']) ? $this->sanitizeHTML($data['content']) : '';

        // Normalize publishedAt to MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
        $publishedAt = $data['publishedAt'] ?? null;
        if (!empty($publishedAt)) {
            // Accept ISO 8601 like 2025-01-20T00:00 or with seconds
            $publishedAt = str_replace('T', ' ', $publishedAt);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $publishedAt)) {
                $publishedAt .= ':00';
            }
        } else {
            $publishedAt = null;
        }

        // Author id fallback to current user
        $authorId = isset($data['authorId']) ? (int)$data['authorId'] : (int)$this->user_id;
        
        $sql = "INSERT INTO publications (title, slug, excerpt, content, featured_image_url, featured_image_alt, 
                banner_url, banner_alt, author_id, category, published_at, reading_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Prepare bind variables (must be variables, not expressions)
        $title = $data['title'];
        $excerpt = $data['excerpt'] ?? '';
        $featuredImageUrl = isset($data['featuredImage']['url']) ? $data['featuredImage']['url'] : null;
        $featuredImageAlt = isset($data['featuredImage']['alt']) ? $data['featuredImage']['alt'] : null;
        $bannerUrl = isset($data['banner']['url']) ? $data['banner']['url'] : null;
        $bannerAlt = isset($data['banner']['alt']) ? $data['banner']['alt'] : null;
        $category = $data['category'];

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssissi",
            $title,
            $slug,
            $excerpt,
            $content,
            $featuredImageUrl,
            $featuredImageAlt,
            $bannerUrl,
            $bannerAlt,
            $authorId,
            $category,
            $publishedAt,
            $readingTime
        );
        
        if ($stmt->execute()) {
            $publicationId = $stmt->insert_id;
            
            // Add tags if provided
            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->addTags($publicationId, $data['tags']);
            }
            
            return $publicationId;
        }
        
        // Surface DB error to caller (caught and returned as JSON in controller)
        throw new \Exception('Failed to insert publication: ' . $stmt->error);
    }
    
    /**
     * Update a publication
     * @param int $id
     * @param array $data
     * @return boolean
     */
    public function updatePublication($id, $data) {
        if (!$this->canEdit()) {
            return false;
        }
        
        $updates = [];
        $params = [];
        $types = "";
        
        if (isset($data['title'])) {
            $updates[] = "title = ?";
            $params[] = $data['title'];
            $types .= "s";
            
            // Update slug if title changed
            $slug = $this->generateSlug($data['title']);
            $slug = $this->ensureUniqueSlug($slug, 'publications', $id);
            $updates[] = "slug = ?";
            $params[] = $slug;
            $types .= "s";
        }
        
        if (isset($data['excerpt'])) {
            $updates[] = "excerpt = ?";
            $params[] = $data['excerpt'];
            $types .= "s";
        }
        
        if (isset($data['content'])) {
            $content = $this->sanitizeHTML($data['content']);
            $updates[] = "content = ?";
            $params[] = $content;
            $types .= "s";
            
            // Recalculate reading time
            $readingTime = $this->calculateReadingTime($content);
            $updates[] = "reading_time = ?";
            $params[] = $readingTime;
            $types .= "i";
        }
        
        if (isset($data['featuredImage'])) {
            $updates[] = "featured_image_url = ?, featured_image_alt = ?";
            $params[] = $data['featuredImage']['url'] ?? null;
            $params[] = $data['featuredImage']['alt'] ?? null;
            $types .= "ss";
        }
        
        if (isset($data['banner'])) {
            $updates[] = "banner_url = ?, banner_alt = ?";
            $params[] = $data['banner']['url'] ?? null;
            $params[] = $data['banner']['alt'] ?? null;
            $types .= "ss";
        }
        
        if (isset($data['authorId'])) {
            $updates[] = "author_id = ?";
            $params[] = $data['authorId'];
            $types .= "i";
        }
        
        if (isset($data['category'])) {
            $updates[] = "category = ?";
            $params[] = $data['category'];
            $types .= "s";
        }
        
        if (isset($data['publishedAt'])) {
            $updates[] = "published_at = ?";
            $params[] = $data['publishedAt'];
            $types .= "s";
        }
        
        if (empty($updates)) {
            return true; // Nothing to update
        }
        
        $sql = "UPDATE publications SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            // Update tags if provided
            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->deleteTags($id);
                $this->addTags($id, $data['tags']);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete a publication
     * @param int $id
     * @return boolean
     */
    public function deletePublication($id) {
        if (!$this->canDelete()) {
            return false;
        }
        
        // Get publication to delete associated files
        $publication = $this->getPublication($id);
        if ($publication) {
            // Delete images
            if (!empty($publication['featuredImage']['url'])) {
                $this->deleteFile($publication['featuredImage']['url']);
            }
            if (!empty($publication['banner']['url'])) {
                $this->deleteFile($publication['banner']['url']);
            }
            
            // Delete attachments
            if (!empty($publication['attachments'])) {
                foreach ($publication['attachments'] as $attachment) {
                    $this->deleteFile($attachment['url']);
                }
            }
        }
        
        $stmt = $this->conn->prepare("DELETE FROM publications WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Add tags to a publication
     * @param int $publicationId
     * @param array $tags
     */
    private function addTags($publicationId, $tags) {
        $stmt = $this->conn->prepare("INSERT INTO publication_tags (publication_id, tag) VALUES (?, ?)");
        
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $stmt->bind_param("is", $publicationId, $tag);
                $stmt->execute();
            }
        }
    }

    /**
     * Save uploaded attachments for a publication
     * @param int $publicationId
     * @param array $filesArray $_FILES['attachments']
     */
    public function addAttachments($publicationId, $filesArray) {
        if (!isset($filesArray) || !isset($filesArray['name']) || empty($filesArray['name'])) {
            return;
        }

        $count = is_array($filesArray['name']) ? count($filesArray['name']) : 0;
        if ($count === 0) return;

        $stmt = $this->conn->prepare("INSERT INTO publication_attachments (publication_id, name, url, size, type) VALUES (?, ?, ?, ?, ?)");

        for ($i = 0; $i < $count; $i++) {
            if (empty($filesArray['tmp_name'][$i])) continue;

            $singleFile = [
                'name' => $filesArray['name'][$i],
                'type' => $filesArray['type'][$i] ?? null,
                'tmp_name' => $filesArray['tmp_name'][$i],
                'error' => $filesArray['error'][$i],
                'size' => $filesArray['size'][$i]
            ];

            $upload = $this->handleFileUpload($singleFile, 'publications');
            if (!$upload['success']) continue;

            $fileName = $filesArray['name'][$i];
            $fileUrl = $upload['path'];
            $fileSize = $this->formatFileSize($filesArray['size'][$i]);
            $fileType = strtolower(pathinfo($filesArray['name'][$i], PATHINFO_EXTENSION));

            $stmt->bind_param("issss", $publicationId, $fileName, $fileUrl, $fileSize, $fileType);
            $stmt->execute();
        }
    }
    
    /**
     * Delete all tags for a publication
     * @param int $publicationId
     */
    private function deleteTags($publicationId) {
        $stmt = $this->conn->prepare("DELETE FROM publication_tags WHERE publication_id = ?");
        $stmt->bind_param("i", $publicationId);
        $stmt->execute();
    }
    
    /**
     * Get tags for a publication
     * @param int $publicationId
     * @return array
     */
    private function getTags($publicationId) {
        $stmt = $this->conn->prepare("SELECT tag FROM publication_tags WHERE publication_id = ?");
        $stmt->bind_param("i", $publicationId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row['tag'];
        }
        
        return $tags;
    }
    
    /**
     * Get attachments for a publication
     * @param int $publicationId
     * @return array
     */
    private function getAttachments($publicationId) {
        $stmt = $this->conn->prepare("SELECT * FROM publication_attachments WHERE publication_id = ?");
        $stmt->bind_param("i", $publicationId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attachments = [];
        while ($row = $result->fetch_assoc()) {
            $attachments[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'url' => $row['url'],
                'size' => $row['size'],
                'type' => $row['type']
            ];
        }
        
        return $attachments;
    }
    
    /**
     * Format publication data for output
     * @param array $row
     * @param boolean $full
     * @return array
     */
    private function formatPublication($row, $full = false) {
        $publication = [
            'id' => $row['id'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'excerpt' => $row['excerpt'],
            'featuredImage' => [
                'url' => $row['featured_image_url'],
                'alt' => $row['featured_image_alt']
            ],
            'category' => $row['category'],
            'tags' => $this->getTags($row['id']),
            'author' => [
                'id' => $row['author_id'],
                'name' => $row['author_name'] ?? 'Unknown'
            ],
            'publishedAt' => $row['published_at'],
            'readingTime' => $row['reading_time']
        ];
        
        if ($full) {
            $publication['content'] = $row['content'];
            $publication['banner'] = [
                'url' => $row['banner_url'],
                'alt' => $row['banner_alt']
            ];
            $publication['updatedAt'] = $row['updated_at'];
            $publication['attachments'] = $this->getAttachments($row['id']);
        }
        
        return $publication;
    }
    
    /**
     * Get all categories
     * @return array
     */
    public function getCategories() {
        $stmt = $this->conn->query("SELECT DISTINCT category FROM publications ORDER BY category");
        $categories = [];
        while ($row = $stmt->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        return $categories;
    }
    
    /**
     * Get all tags
     * @return array
     */
    public function getAllTags() {
        $stmt = $this->conn->query("SELECT DISTINCT tag FROM publication_tags ORDER BY tag");
        $tags = [];
        while ($row = $stmt->fetch_assoc()) {
            $tags[] = $row['tag'];
        }
        return $tags;
    }
}
?>

