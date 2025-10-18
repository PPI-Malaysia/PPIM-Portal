<?php
// campuses.php - Campuses management class
require_once(__DIR__."/content-management.php");

class Campuses extends ContentManagement {
    
    /**
     * Get all campuses with filters
     * @param string|null $city
     * @param string|null $state
     * @param string|null $search
     * @return array
     */
    public function getCampuses($city = null, $state = null, $search = null) {
        $sql = "SELECT * FROM campuses";
        
        $where = [];
        $params = [];
        $types = "";
        
        if ($city) {
            $where[] = "city = ?";
            $params[] = $city;
            $types .= "s";
        }
        
        if ($state) {
            $where[] = "state = ?";
            $params[] = $state;
            $types .= "s";
        }
        
        if ($search) {
            $where[] = "(name LIKE ? OR short_name LIKE ? OR description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $campuses = [];
        while ($row = $result->fetch_assoc()) {
            $campuses[] = $this->formatCampus($row);
        }
        
        return $campuses;
    }
    
    /**
     * Get campus by ID
     * @param int $id
     * @return array|null
     */
    public function getCampus($id) {
        $stmt = $this->conn->prepare("SELECT * FROM campuses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->formatCampus($row, true);
        }
        
        return null;
    }
    
    /**
     * Create a new campus
     * @param array $data
     * @param array|null $logo - The uploaded logo from $_FILES
     * @param array|null $cover - The uploaded cover image from $_FILES
     * @return int|false - Campus ID or false
     */
    public function createCampus($data, $logo = null, $cover = null) {
        if (!$this->canCreate()) {
            return false;
        }
        
        $logoUrl = null;
        $coverUrl = null;
        
        // Handle logo upload if provided
        if ($logo && isset($logo['tmp_name']) && !empty($logo['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($logo, 'campuses/logos', $allowedTypes, 5242880); // 5MB max
            
            if ($uploadResult['success']) {
                $logoUrl = $uploadResult['path'];
            }
        } elseif (isset($data['logo'])) {
            $logoUrl = $data['logo'];
        }
        
        // Handle cover image upload if provided
        if ($cover && isset($cover['tmp_name']) && !empty($cover['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($cover, 'campuses/covers', $allowedTypes, 10485760); // 10MB max
            
            if ($uploadResult['success']) {
                $coverUrl = $uploadResult['path'];
            }
        } elseif (isset($data['coverImage'])) {
            $coverUrl = $data['coverImage'];
        }
        
        // Parse JSON fields
        $address = null;
        if (isset($data['address'])) {
            $address = is_string($data['address']) ? $data['address'] : json_encode($data['address']);
        }
        
        $socialLinks = null;
        if (isset($data['socialLinks'])) {
            $socialLinks = is_string($data['socialLinks']) ? $data['socialLinks'] : json_encode($data['socialLinks']);
        }
        
        $programs = null;
        if (isset($data['programs'])) {
            $programs = is_string($data['programs']) ? $data['programs'] : json_encode($data['programs']);
        }
        
        $sql = "INSERT INTO campuses (name, short_name, city, state, country, contact_email, contact_phone, 
                description, logo_url, cover_image_url, website, address, social_links, student_count, 
                established_year, programs) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssssis",
            $data['name'],
            $data['shortName'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['country'] ?? 'Malaysia',
            $data['contactEmail'] ?? null,
            $data['contactPhone'] ?? null,
            $data['description'] ?? null,
            $logoUrl,
            $coverUrl,
            $data['website'] ?? null,
            $address,
            $socialLinks,
            $data['studentCount'] ?? 0,
            $data['establishedYear'] ?? null,
            $programs
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update a campus
     * @param int $id
     * @param array $data
     * @param array|null $logo - The uploaded logo from $_FILES (optional)
     * @param array|null $cover - The uploaded cover image from $_FILES (optional)
     * @return boolean
     */
    public function updateCampus($id, $data, $logo = null, $cover = null) {
        if (!$this->canEdit()) {
            return false;
        }
        
        $updates = [];
        $params = [];
        $types = "";
        
        // Handle logo upload if provided
        if ($logo && isset($logo['tmp_name']) && !empty($logo['tmp_name'])) {
            // Get old campus to delete old logo
            $oldCampus = $this->getCampus($id);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($logo, 'campuses/logos', $allowedTypes, 5242880);
            
            if ($uploadResult['success']) {
                // Delete old logo
                if ($oldCampus && !empty($oldCampus['logo'])) {
                    $this->deleteFile($oldCampus['logo']);
                }
                
                $updates[] = "logo_url = ?";
                $params[] = $uploadResult['path'];
                $types .= "s";
            }
        }
        
        // Handle cover image upload if provided
        if ($cover && isset($cover['tmp_name']) && !empty($cover['tmp_name'])) {
            // Get old campus to delete old cover
            if (!isset($oldCampus)) {
                $oldCampus = $this->getCampus($id);
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($cover, 'campuses/covers', $allowedTypes, 10485760);
            
            if ($uploadResult['success']) {
                // Delete old cover
                if ($oldCampus && !empty($oldCampus['coverImage'])) {
                    $this->deleteFile($oldCampus['coverImage']);
                }
                
                $updates[] = "cover_image_url = ?";
                $params[] = $uploadResult['path'];
                $types .= "s";
            }
        }
        
        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
            $types .= "s";
        }
        
        if (isset($data['shortName'])) {
            $updates[] = "short_name = ?";
            $params[] = $data['shortName'];
            $types .= "s";
        }
        
        if (isset($data['city'])) {
            $updates[] = "city = ?";
            $params[] = $data['city'];
            $types .= "s";
        }
        
        if (isset($data['state'])) {
            $updates[] = "state = ?";
            $params[] = $data['state'];
            $types .= "s";
        }
        
        if (isset($data['country'])) {
            $updates[] = "country = ?";
            $params[] = $data['country'];
            $types .= "s";
        }
        
        if (isset($data['contactEmail'])) {
            $updates[] = "contact_email = ?";
            $params[] = $data['contactEmail'];
            $types .= "s";
        }
        
        if (isset($data['contactPhone'])) {
            $updates[] = "contact_phone = ?";
            $params[] = $data['contactPhone'];
            $types .= "s";
        }
        
        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
            $types .= "s";
        }
        
        if (isset($data['website'])) {
            $updates[] = "website = ?";
            $params[] = $data['website'];
            $types .= "s";
        }
        
        if (isset($data['address'])) {
            $updates[] = "address = ?";
            $address = is_string($data['address']) ? $data['address'] : json_encode($data['address']);
            $params[] = $address;
            $types .= "s";
        }
        
        if (isset($data['socialLinks'])) {
            $updates[] = "social_links = ?";
            $socialLinks = is_string($data['socialLinks']) ? $data['socialLinks'] : json_encode($data['socialLinks']);
            $params[] = $socialLinks;
            $types .= "s";
        }
        
        if (isset($data['studentCount'])) {
            $updates[] = "student_count = ?";
            $params[] = $data['studentCount'];
            $types .= "i";
        }
        
        if (isset($data['establishedYear'])) {
            $updates[] = "established_year = ?";
            $params[] = $data['establishedYear'];
            $types .= "i";
        }
        
        if (isset($data['programs'])) {
            $updates[] = "programs = ?";
            $programs = is_string($data['programs']) ? $data['programs'] : json_encode($data['programs']);
            $params[] = $programs;
            $types .= "s";
        }
        
        if (empty($updates)) {
            return true; // Nothing to update
        }
        
        $sql = "UPDATE campuses SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a campus
     * @param int $id
     * @return boolean
     */
    public function deleteCampus($id) {
        if (!$this->canDelete()) {
            return false;
        }
        
        // Get campus to delete images
        $campus = $this->getCampus($id);
        if ($campus) {
            if (!empty($campus['logo'])) {
                $this->deleteFile($campus['logo']);
            }
            if (!empty($campus['coverImage'])) {
                $this->deleteFile($campus['coverImage']);
            }
        }
        
        $stmt = $this->conn->prepare("DELETE FROM campuses WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Format campus data for output
     * @param array $row
     * @param boolean $full
     * @return array
     */
    private function formatCampus($row, $full = false) {
        $campus = [
            'id' => $row['id'],
            'name' => $row['name'],
            'shortName' => $row['short_name'],
            'city' => $row['city'],
            'state' => $row['state'],
            'country' => $row['country'],
            'contactEmail' => $row['contact_email'],
            'contactPhone' => $row['contact_phone'],
            'description' => $row['description'],
            'logo' => $row['logo_url'],
            'coverImage' => $row['cover_image_url'],
            'website' => $row['website'],
            'studentCount' => $row['student_count'],
            'establishedYear' => $row['established_year']
        ];
        
        if ($full) {
            $campus['address'] = $row['address'] ? json_decode($row['address'], true) : null;
            $campus['socialLinks'] = $row['social_links'] ? json_decode($row['social_links'], true) : null;
            $campus['programs'] = $row['programs'] ? json_decode($row['programs'], true) : null;
        }
        
        return $campus;
    }
    
    /**
     * Get all cities
     * @return array
     */
    public function getCities() {
        $stmt = $this->conn->query("SELECT DISTINCT city FROM campuses WHERE city IS NOT NULL ORDER BY city");
        $cities = [];
        while ($row = $stmt->fetch_assoc()) {
            $cities[] = $row['city'];
        }
        return $cities;
    }
    
    /**
     * Get all states
     * @return array
     */
    public function getStates() {
        $stmt = $this->conn->query("SELECT DISTINCT state FROM campuses WHERE state IS NOT NULL ORDER BY state");
        $states = [];
        while ($row = $stmt->fetch_assoc()) {
            $states[] = $row['state'];
        }
        return $states;
    }
}
?>