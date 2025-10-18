<?php
// team-members.php - Team members management class
require_once("content-management.php");

class TeamMembers extends ContentManagement {
    
    /**
     * Get all team members with filters
     * @param string|null $department
     * @param string $sort
     * @return array
     */
    public function getTeamMembers($department = null, $sort = 'order') {
        $sql = "SELECT * FROM team_members";
        
        $where = [];
        $params = [];
        $types = "";
        
        if ($department) {
            $where[] = "department = ?";
            $params[] = $department;
            $types .= "s";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Sort
        if ($sort === 'name') {
            $sql .= " ORDER BY name ASC";
        } else {
            $sql .= " ORDER BY order_position ASC, name ASC";
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $this->formatTeamMember($row);
        }
        
        return $members;
    }
    
    /**
     * Get team member by ID
     * @param int $id
     * @return array|null
     */
    public function getTeamMember($id) {
        $stmt = $this->conn->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->formatTeamMember($row, true);
        }
        
        return null;
    }
    
    /**
     * Create a new team member
     * @param array $data
     * @param array|null $image - The uploaded image from $_FILES
     * @return int|false - Team member ID or false
     */
    public function createTeamMember($data, $image = null) {
        if (!$this->canCreate()) {
            return false;
        }
        
        $imageUrl = null;
        
        // Handle image upload if provided
        if ($image && isset($image['tmp_name']) && !empty($image['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($image, 'team', $allowedTypes, 5242880); // 5MB max
            
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['path'];
            }
        } elseif (isset($data['image'])) {
            $imageUrl = $data['image'];
        }
        
        // Parse JSON fields
        $socialLinks = null;
        if (isset($data['socialLinks'])) {
            $socialLinks = is_string($data['socialLinks']) ? $data['socialLinks'] : json_encode($data['socialLinks']);
        }
        
        $achievements = null;
        if (isset($data['achievements'])) {
            $achievements = is_string($data['achievements']) ? $data['achievements'] : json_encode($data['achievements']);
        }
        
        $sql = "INSERT INTO team_members (name, position, image_url, bio, department, email, phone, 
                social_links, order_position, joined_at, achievements) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssis",
            $data['name'],
            $data['position'],
            $imageUrl,
            $data['bio'] ?? null,
            $data['department'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $socialLinks,
            $data['order'] ?? 0,
            $data['joinedAt'] ?? null,
            $achievements
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update a team member
     * @param int $id
     * @param array $data
     * @param array|null $image - The uploaded image from $_FILES (optional)
     * @return boolean
     */
    public function updateTeamMember($id, $data, $image = null) {
        if (!$this->canEdit()) {
            return false;
        }
        
        $updates = [];
        $params = [];
        $types = "";
        
        // Handle image upload if provided
        if ($image && isset($image['tmp_name']) && !empty($image['tmp_name'])) {
            // Get old member to delete old image
            $oldMember = $this->getTeamMember($id);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($image, 'team', $allowedTypes, 5242880);
            
            if ($uploadResult['success']) {
                // Delete old image
                if ($oldMember && !empty($oldMember['image'])) {
                    $this->deleteFile($oldMember['image']);
                }
                
                $updates[] = "image_url = ?";
                $params[] = $uploadResult['path'];
                $types .= "s";
            }
        }
        
        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
            $types .= "s";
        }
        
        if (isset($data['position'])) {
            $updates[] = "position = ?";
            $params[] = $data['position'];
            $types .= "s";
        }
        
        if (isset($data['bio'])) {
            $updates[] = "bio = ?";
            $params[] = $data['bio'];
            $types .= "s";
        }
        
        if (isset($data['department'])) {
            $updates[] = "department = ?";
            $params[] = $data['department'];
            $types .= "s";
        }
        
        if (isset($data['email'])) {
            $updates[] = "email = ?";
            $params[] = $data['email'];
            $types .= "s";
        }
        
        if (isset($data['phone'])) {
            $updates[] = "phone = ?";
            $params[] = $data['phone'];
            $types .= "s";
        }
        
        if (isset($data['socialLinks'])) {
            $updates[] = "social_links = ?";
            $socialLinks = is_string($data['socialLinks']) ? $data['socialLinks'] : json_encode($data['socialLinks']);
            $params[] = $socialLinks;
            $types .= "s";
        }
        
        if (isset($data['order'])) {
            $updates[] = "order_position = ?";
            $params[] = $data['order'];
            $types .= "i";
        }
        
        if (isset($data['joinedAt'])) {
            $updates[] = "joined_at = ?";
            $params[] = $data['joinedAt'];
            $types .= "s";
        }
        
        if (isset($data['achievements'])) {
            $updates[] = "achievements = ?";
            $achievements = is_string($data['achievements']) ? $data['achievements'] : json_encode($data['achievements']);
            $params[] = $achievements;
            $types .= "s";
        }
        
        if (empty($updates)) {
            return true; // Nothing to update
        }
        
        $sql = "UPDATE team_members SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a team member
     * @param int $id
     * @return boolean
     */
    public function deleteTeamMember($id) {
        if (!$this->canDelete()) {
            return false;
        }
        
        // Get member to delete image
        $member = $this->getTeamMember($id);
        if ($member && !empty($member['image'])) {
            $this->deleteFile($member['image']);
        }
        
        $stmt = $this->conn->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Format team member data for output
     * @param array $row
     * @param boolean $full
     * @return array
     */
    private function formatTeamMember($row, $full = false) {
        $member = [
            'id' => $row['id'],
            'name' => $row['name'],
            'position' => $row['position'],
            'image' => $row['image_url'],
            'bio' => $row['bio'],
            'department' => $row['department'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'socialLinks' => $row['social_links'] ? json_decode($row['social_links'], true) : null,
            'order' => $row['order_position']
        ];
        
        if ($full) {
            $member['joinedAt'] = $row['joined_at'];
            $member['achievements'] = $row['achievements'] ? json_decode($row['achievements'], true) : null;
        }
        
        return $member;
    }
    
    /**
     * Get all departments
     * @return array
     */
    public function getDepartments() {
        $stmt = $this->conn->query("SELECT DISTINCT department FROM team_members WHERE department IS NOT NULL ORDER BY department");
        $departments = [];
        while ($row = $stmt->fetch_assoc()) {
            $departments[] = $row['department'];
        }
        return $departments;
    }
    
    /**
     * Reorder team members
     * @param array $orderMap - Array of ['id' => order_position]
     * @return boolean
     */
    public function reorderMembers($orderMap) {
        if (!$this->canEdit()) {
            return false;
        }
        
        $stmt = $this->conn->prepare("UPDATE team_members SET order_position = ? WHERE id = ?");
        
        foreach ($orderMap as $id => $position) {
            $stmt->bind_param("ii", $position, $id);
            $stmt->execute();
        }
        
        return true;
    }
}
?>

