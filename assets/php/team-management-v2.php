<?php
// team-management-v2.php - Enhanced Team Management System
// Implements Period, Department, and Member management
require_once(__DIR__."/content-management.php");

class TeamManagementV2 extends ContentManagement {
    
    // Position level constants
    const POSITION_LEVELS = [
        'ketua_umum' => 'Ketua Umum',
        'wakil_ketua' => 'Wakil Ketua',
        'sekretaris' => 'Sekretaris Umum',
        'wakil_sekretaris' => 'Wakil Sekretaris',
        'bendahara' => 'Bendahara Umum',
        'wakil_bendahara' => 'Wakil Bendahara',
        'kepala_biro' => 'Kepala Biro',
        'wakil_kepala_biro' => 'Wakil Kepala Biro',
        'kepala_dept' => 'Kepala Departemen',
        'wakil_kepala_dept' => 'Wakil Kepala Departemen',
        'staff' => 'Staff'
    ];

    // Core team positions (no department)
    const CORE_TEAM_POSITIONS = ['ketua_umum', 'wakil_ketua', 'sekretaris', 'wakil_sekretaris', 'bendahara', 'wakil_bendahara'];

    // Biro positions
    const BIRO_POSITIONS = ['kepala_biro', 'wakil_kepala_biro', 'staff'];

    // Departemen positions
    const DEPT_POSITIONS = ['kepala_dept', 'wakil_kepala_dept', 'staff'];

    // Common icons for departments
    const DEPARTMENT_ICONS = [
        'bi-megaphone-fill' => 'Communications',
        'bi-file-earmark-text-fill' => 'Administration',
        'bi-mortarboard-fill' => 'Academic',
        'bi-trophy-fill' => 'Sports',
        'bi-palette-fill' => 'Arts & Culture',
        'bi-globe' => 'External Relations',
        'bi-people-fill' => 'Community',
        'bi-calendar-event-fill' => 'Events',
        'bi-book-fill' => 'Education',
        'bi-heart-fill' => 'Social',
        'bi-cash-stack' => 'Finance',
        'bi-briefcase-fill' => 'Professional',
        'bi-camera-fill' => 'Media',
        'bi-laptop-fill' => 'Technology',
        'bi-building' => 'Organization'
    ];

    // ============================================================
    // PERIOD MANAGEMENT
    // ============================================================

    /**
     * Get all periods
     * @param bool $activeOnly
     * @return array
     */
    public function getPeriods($activeOnly = false) {
        $sql = "SELECT * FROM team_periods";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY start_date DESC";
        
        $result = $this->conn->query($sql);
        $periods = [];
        while ($row = $result->fetch_assoc()) {
            $periods[] = $this->formatPeriod($row);
        }
        return $periods;
    }

    /**
     * Get period by ID or slug
     * @param mixed $identifier
     * @return array|null
     */
    public function getPeriod($identifier) {
        $sql = "SELECT * FROM team_periods WHERE ";
        $sql .= is_numeric($identifier) ? "id = ?" : "slug = ?";
        
        $stmt = $this->conn->prepare($sql);
        $type = is_numeric($identifier) ? "i" : "s";
        $stmt->bind_param($type, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->formatPeriod($row, true);
        }
        return null;
    }

    /**
     * Get active period
     * @return array|null
     */
    public function getActivePeriod() {
        $stmt = $this->conn->prepare("SELECT * FROM team_periods WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->formatPeriod($row);
        }
        return null;
    }

    /**
     * Create a new period
     * @param array $data
     * @return int|false
     */
    public function createPeriod($data) {
        if (!$this->canCreate()) {
            return false;
        }

        $slug = $this->generateSlug($data['slug'] ?? $data['name']);
        $slug = $this->ensureUniqueSlug($slug, 'team_periods');

        // If this is set as active, deactivate others first
        if (!empty($data['isActive'])) {
            $this->conn->query("UPDATE team_periods SET is_active = 0");
        }

        $sql = "INSERT INTO team_periods (name, slug, start_date, end_date, theme, description, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $isActive = !empty($data['isActive']) ? 1 : 0;
        $stmt->bind_param(
            "ssssssi",
            $data['name'],
            $slug,
            $data['startDate'],
            $data['endDate'],
            $data['theme'],
            $data['description'],
            $isActive
        );

        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    /**
     * Update period
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePeriod($id, $data) {
        if (!$this->canEdit()) {
            return false;
        }

        $updates = [];
        $params = [];
        $types = "";

        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
            $types .= "s";
        }

        if (isset($data['slug'])) {
            $slug = $this->generateSlug($data['slug']);
            $slug = $this->ensureUniqueSlug($slug, 'team_periods', $id);
            $updates[] = "slug = ?";
            $params[] = $slug;
            $types .= "s";
        }

        if (isset($data['startDate'])) {
            $updates[] = "start_date = ?";
            $params[] = $data['startDate'];
            $types .= "s";
        }

        if (isset($data['endDate'])) {
            $updates[] = "end_date = ?";
            $params[] = $data['endDate'];
            $types .= "s";
        }

        if (isset($data['theme'])) {
            $updates[] = "theme = ?";
            $params[] = $data['theme'];
            $types .= "s";
        }

        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
            $types .= "s";
        }

        if (empty($updates)) {
            return true;
        }

        $sql = "UPDATE team_periods SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    /**
     * Activate a period (deactivates all others)
     * @param int $id
     * @return bool
     */
    public function activatePeriod($id) {
        if (!$this->canEdit()) {
            return false;
        }

        // Deactivate all periods
        $this->conn->query("UPDATE team_periods SET is_active = 0");

        // Activate selected period
        $stmt = $this->conn->prepare("UPDATE team_periods SET is_active = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Delete period
     * @param int $id
     * @return bool
     */
    public function deletePeriod($id) {
        if (!$this->canDelete()) {
            return false;
        }

        // Check if period has members
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM team_members_v2 WHERE period_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            return false; // Cannot delete period with members
        }

        $stmt = $this->conn->prepare("DELETE FROM team_periods WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Format period for output
     */
    private function formatPeriod($row, $full = false) {
        $period = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'startDate' => $row['start_date'] ?? null,
            'endDate' => $row['end_date'] ?? null,
            'theme' => $row['theme'] ?? null,
            'isActive' => (bool)($row['is_active'] ?? false)
        ];

        if ($full) {
            $period['description'] = $row['description'];
            $period['createdAt'] = $row['created_at'];
            $period['updatedAt'] = $row['updated_at'];
            
            // Get member counts
            $period['memberCount'] = $this->getPeriodMemberCount($row['id']);
        }

        return $period;
    }

    /**
     * Get member count for period
     */
    private function getPeriodMemberCount($periodId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM team_members_v2 WHERE period_id = ?");
        $stmt->bind_param("i", $periodId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['count'];
    }

    // ============================================================
    // DEPARTMENT MANAGEMENT
    // ============================================================

    /**
     * Get all departments
     * @param bool $activeOnly
     * @return array
     */
    public function getDepartments($activeOnly = true) {
        $sql = "SELECT * FROM departments";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC, name ASC";
        
        $result = $this->conn->query($sql);
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $this->formatDepartment($row);
        }
        return $departments;
    }

    /**
     * Get department by ID or slug
     * @param mixed $identifier
     * @return array|null
     */
    public function getDepartment($identifier) {
        $sql = "SELECT * FROM departments WHERE ";
        $sql .= is_numeric($identifier) ? "id = ?" : "slug = ?";
        
        $stmt = $this->conn->prepare($sql);
        $type = is_numeric($identifier) ? "i" : "s";
        $stmt->bind_param($type, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->formatDepartment($row, true);
        }
        return null;
    }

    /**
     * Get departments for a specific period with members
     * @param mixed $periodIdentifier
     * @return array
     */
    public function getDepartmentsForPeriod($periodIdentifier) {
        $period = $this->getPeriod($periodIdentifier);
        if (!$period) return [];

        $departments = $this->getDepartments(true);
        
        foreach ($departments as &$dept) {
            $dept['members'] = $this->getMembersByDepartment($period['id'], $dept['id']);
            $dept['periodInfo'] = $this->getDepartmentPeriodInfo($dept['id'], $period['id']);
        }

        return $departments;
    }

    /**
     * Create department
     * @param array $data
     * @return int|false
     */
    public function createDepartment($data) {
        if (!$this->canCreate()) {
            return false;
        }

        $slug = $this->generateSlug($data['slug'] ?? $data['name']);
        $slug = $this->ensureUniqueSlug($slug, 'departments');

        // Get max display order
        $maxOrder = $this->conn->query("SELECT MAX(display_order) as max FROM departments")->fetch_assoc()['max'];
        $displayOrder = isset($data['displayOrder']) ? $data['displayOrder'] : ($maxOrder + 1);

        $sql = "INSERT INTO departments (name, slug, short_name, description, icon, color, is_biro, display_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $isBiro = isset($data['isBiro']) ? ($data['isBiro'] ? 1 : 0) : 1;
        $isActive = isset($data['isActive']) ? ($data['isActive'] ? 1 : 0) : 1;
        
        $stmt->bind_param(
            "ssssssiis",
            $data['name'],
            $slug,
            $data['shortName'],
            $data['description'],
            $data['icon'],
            $data['color'],
            $isBiro,
            $displayOrder,
            $isActive
        );

        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    /**
     * Update department
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateDepartment($id, $data) {
        if (!$this->canEdit()) {
            return false;
        }

        $updates = [];
        $params = [];
        $types = "";

        $fields = [
            'name' => 's',
            'shortName' => 's',
            'description' => 's',
            'icon' => 's',
            'color' => 's'
        ];

        foreach ($fields as $field => $type) {
            if (isset($data[$field])) {
                $dbField = $this->camelToSnake($field);
                $updates[] = "$dbField = ?";
                $params[] = $data[$field];
                $types .= $type;
            }
        }

        if (isset($data['slug'])) {
            $slug = $this->generateSlug($data['slug']);
            $slug = $this->ensureUniqueSlug($slug, 'departments', $id);
            $updates[] = "slug = ?";
            $params[] = $slug;
            $types .= "s";
        }

        if (isset($data['isBiro'])) {
            $updates[] = "is_biro = ?";
            $params[] = $data['isBiro'] ? 1 : 0;
            $types .= "i";
        }

        if (isset($data['displayOrder'])) {
            $updates[] = "display_order = ?";
            $params[] = $data['displayOrder'];
            $types .= "i";
        }

        if (isset($data['isActive'])) {
            $updates[] = "is_active = ?";
            $params[] = $data['isActive'] ? 1 : 0;
            $types .= "i";
        }

        if (empty($updates)) {
            return true;
        }

        $sql = "UPDATE departments SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    /**
     * Reorder departments
     * @param array $orderMap - Array of [id => order]
     * @return bool
     */
    public function reorderDepartments($orderMap) {
        if (!$this->canEdit()) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE departments SET display_order = ? WHERE id = ?");
        
        foreach ($orderMap as $id => $order) {
            $stmt->bind_param("ii", $order, $id);
            $stmt->execute();
        }

        return true;
    }

    /**
     * Delete department
     * @param int $id
     * @return bool
     */
    public function deleteDepartment($id) {
        if (!$this->canDelete()) {
            return false;
        }

        // Set department_id to null for members (foreign key handles this)
        $stmt = $this->conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Format department for output
     */
    private function formatDepartment($row, $full = false) {
        $dept = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'shortName' => $row['short_name'] ?? null,
            'description' => $row['description'] ?? null,
            'icon' => $row['icon'] ?? null,
            'color' => $row['color'] ?? null,
            'isBiro' => (bool)($row['is_biro'] ?? true),
            'displayOrder' => (int)($row['display_order'] ?? 0),
            'isActive' => (bool)($row['is_active'] ?? true)
        ];

        if ($full) {
            $dept['createdAt'] = $row['created_at'];
            $dept['updatedAt'] = $row['updated_at'];
        }

        return $dept;
    }

    // ============================================================
    // DEPARTMENT PERIOD INFO MANAGEMENT
    // ============================================================

    /**
     * Get department period info
     */
    public function getDepartmentPeriodInfo($departmentId, $periodId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM department_period_info WHERE department_id = ? AND period_id = ?"
        );
        $stmt->bind_param("ii", $departmentId, $periodId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return [
                'id' => (int)$row['id'],
                'departmentId' => (int)$row['department_id'],
                'periodId' => (int)$row['period_id'],
                'vision' => $row['vision'],
                'mission' => $row['mission'],
                'programs' => $row['programs'] ? json_decode($row['programs'], true) : [],
                'achievements' => $row['achievements'] ? json_decode($row['achievements'], true) : [],
                'gallery' => $row['gallery'] ? json_decode($row['gallery'], true) : []
            ];
        }
        return null;
    }

    /**
     * Save department period info
     */
    public function saveDepartmentPeriodInfo($departmentId, $periodId, $data) {
        if (!$this->canEdit()) {
            return false;
        }

        $programs = isset($data['programs']) ? json_encode($data['programs']) : null;
        $achievements = isset($data['achievements']) ? json_encode($data['achievements']) : null;
        $gallery = isset($data['gallery']) ? json_encode($data['gallery']) : null;

        $sql = "INSERT INTO department_period_info (department_id, period_id, vision, mission, programs, achievements, gallery)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE vision = VALUES(vision), mission = VALUES(mission), 
                programs = VALUES(programs), achievements = VALUES(achievements), gallery = VALUES(gallery)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "iisssss",
            $departmentId,
            $periodId,
            $data['vision'],
            $data['mission'],
            $programs,
            $achievements,
            $gallery
        );

        return $stmt->execute();
    }

    // ============================================================
    // MEMBER MANAGEMENT
    // ============================================================

    /**
     * Get members with filters
     * @param array $filters
     * @return array
     */
    public function getMembers($filters = []) {
        $sql = "SELECT m.*, d.name as department_name, d.slug as department_slug, d.is_biro,
                       p.name as period_name, p.slug as period_slug
                FROM team_members_v2 m
                LEFT JOIN departments d ON m.department_id = d.id
                LEFT JOIN team_periods p ON m.period_id = p.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($filters['periodId'])) {
            $sql .= " AND m.period_id = ?";
            $params[] = $filters['periodId'];
            $types .= "i";
        }

        if (!empty($filters['departmentId'])) {
            if ($filters['departmentId'] === 'core') {
                $sql .= " AND m.department_id IS NULL";
            } else {
                $sql .= " AND m.department_id = ?";
                $params[] = $filters['departmentId'];
                $types .= "i";
            }
        }

        if (!empty($filters['positionLevel'])) {
            $sql .= " AND m.position_level = ?";
            $params[] = $filters['positionLevel'];
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (m.name LIKE ? OR m.position LIKE ? OR m.university LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        if (isset($filters['isActive'])) {
            $sql .= " AND m.is_active = ?";
            $params[] = $filters['isActive'] ? 1 : 0;
            $types .= "i";
        }

        $sql .= " ORDER BY m.display_order ASC, m.name ASC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $this->formatMember($row);
        }

        return $members;
    }

    /**
     * Get members by department for a period
     */
    public function getMembersByDepartment($periodId, $departmentId = null) {
        return $this->getMembers([
            'periodId' => $periodId,
            'departmentId' => $departmentId ?? 'core',
            'isActive' => true
        ]);
    }

    /**
     * Get core team members for a period
     */
    public function getCoreTeam($periodId) {
        return $this->getMembersByDepartment($periodId, 'core');
    }

    /**
     * Get member by ID
     * @param int $id
     * @return array|null
     */
    public function getMember($id) {
        $stmt = $this->conn->prepare(
            "SELECT m.*, d.name as department_name, d.slug as department_slug, d.is_biro,
                    p.name as period_name, p.slug as period_slug
             FROM team_members_v2 m
             LEFT JOIN departments d ON m.department_id = d.id
             LEFT JOIN team_periods p ON m.period_id = p.id
             WHERE m.id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->formatMember($row, true);
        }
        return null;
    }

    /**
     * Create member
     * @param array $data
     * @param array|null $image
     * @return int|false
     */
    public function createMember($data, $image = null) {
        if (!$this->canCreate()) {
            return false;
        }

        // Validate required fields
        if (empty($data['periodId']) || empty($data['name']) || empty($data['position']) || empty($data['positionLevel'])) {
            return false;
        }

        // Handle image upload
        $imageUrl = null;
        if ($image && isset($image['tmp_name']) && !empty($image['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($image, 'team', $allowedTypes, 5242880);
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['path'];
            }
        } elseif (isset($data['imageUrl'])) {
            $imageUrl = $data['imageUrl'];
        }

        // Generate slug
        $slug = $this->generateSlug($data['slug'] ?? $data['name']);
        $slug = $this->ensureUniqueSlug($slug, 'team_members_v2');

        // Get max display order for this period/department combo
        $maxOrder = $this->getMaxMemberOrder($data['periodId'], $data['departmentId'] ?? null);
        $displayOrder = isset($data['displayOrder']) ? $data['displayOrder'] : ($maxOrder + 1);

        $sql = "INSERT INTO team_members_v2 
                (period_id, department_id, name, slug, position, position_level, image_url, bio, 
                 email, phone, linkedin, instagram, university, major, display_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $isActive = isset($data['isActive']) ? ($data['isActive'] ? 1 : 0) : 1;
        $departmentId = !empty($data['departmentId']) ? $data['departmentId'] : null;

        $stmt->bind_param(
            "iissssssssssssii",
            $data['periodId'],
            $departmentId,
            $data['name'],
            $slug,
            $data['position'],
            $data['positionLevel'],
            $imageUrl,
            $data['bio'],
            $data['email'],
            $data['phone'],
            $data['linkedin'],
            $data['instagram'],
            $data['university'],
            $data['major'],
            $displayOrder,
            $isActive
        );

        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    /**
     * Update member
     * @param int $id
     * @param array $data
     * @param array|null $image
     * @return bool
     */
    public function updateMember($id, $data, $image = null) {
        if (!$this->canEdit()) {
            return false;
        }

        $updates = [];
        $params = [];
        $types = "";

        // Handle image upload
        if ($image && isset($image['tmp_name']) && !empty($image['tmp_name'])) {
            $oldMember = $this->getMember($id);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $uploadResult = $this->handleFileUpload($image, 'team', $allowedTypes, 5242880);
            
            if ($uploadResult['success']) {
                if ($oldMember && !empty($oldMember['imageUrl'])) {
                    $this->deleteFile($oldMember['imageUrl']);
                }
                $updates[] = "image_url = ?";
                $params[] = $uploadResult['path'];
                $types .= "s";
            }
        }

        // Map of data fields to database columns
        $fieldMap = [
            'periodId' => ['period_id', 'i'],
            'departmentId' => ['department_id', 'i'],
            'name' => ['name', 's'],
            'position' => ['position', 's'],
            'positionLevel' => ['position_level', 's'],
            'bio' => ['bio', 's'],
            'email' => ['email', 's'],
            'phone' => ['phone', 's'],
            'linkedin' => ['linkedin', 's'],
            'instagram' => ['instagram', 's'],
            'university' => ['university', 's'],
            'major' => ['major', 's'],
            'displayOrder' => ['display_order', 'i'],
            'isActive' => ['is_active', 'i']
        ];

        foreach ($fieldMap as $dataKey => $dbInfo) {
            if (isset($data[$dataKey])) {
                $updates[] = "{$dbInfo[0]} = ?";
                $value = $data[$dataKey];
                
                // Handle special cases
                if ($dataKey === 'isActive') {
                    $value = $value ? 1 : 0;
                } elseif ($dataKey === 'departmentId' && empty($value)) {
                    $value = null;
                }
                
                $params[] = $value;
                $types .= $dbInfo[1];
            }
        }

        if (isset($data['slug'])) {
            $slug = $this->generateSlug($data['slug']);
            $slug = $this->ensureUniqueSlug($slug, 'team_members_v2', $id);
            $updates[] = "slug = ?";
            $params[] = $slug;
            $types .= "s";
        }

        if (empty($updates)) {
            return true;
        }

        $sql = "UPDATE team_members_v2 SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    /**
     * Delete member
     * @param int $id
     * @return bool
     */
    public function deleteMember($id) {
        if (!$this->canDelete()) {
            return false;
        }

        // Delete image
        $member = $this->getMember($id);
        if ($member && !empty($member['imageUrl'])) {
            $this->deleteFile($member['imageUrl']);
        }

        $stmt = $this->conn->prepare("DELETE FROM team_members_v2 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Reorder members
     * @param array $orderMap
     * @return bool
     */
    public function reorderMembers($orderMap) {
        if (!$this->canEdit()) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE team_members_v2 SET display_order = ? WHERE id = ?");
        
        foreach ($orderMap as $id => $order) {
            $stmt->bind_param("ii", $order, $id);
            $stmt->execute();
        }

        return true;
    }

    /**
     * Get max member order
     */
    private function getMaxMemberOrder($periodId, $departmentId = null) {
        $sql = "SELECT MAX(display_order) as max FROM team_members_v2 WHERE period_id = ?";
        $params = [$periodId];
        $types = "i";

        if ($departmentId === null) {
            $sql .= " AND department_id IS NULL";
        } else {
            $sql .= " AND department_id = ?";
            $params[] = $departmentId;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['max'] ?? 0;
    }

    /**
     * Format member for output
     */
    private function formatMember($row, $full = false) {
        $positionLevel = $row['position_level'] ?? 'staff';
        $member = [
            'id' => (int)$row['id'],
            'periodId' => (int)($row['period_id'] ?? 0),
            'periodName' => $row['period_name'] ?? null,
            'periodSlug' => $row['period_slug'] ?? null,
            'departmentId' => !empty($row['department_id']) ? (int)$row['department_id'] : null,
            'departmentName' => $row['department_name'] ?? (!empty($row['department_id']) ? null : 'Core Team'),
            'departmentSlug' => $row['department_slug'] ?? null,
            'isBiro' => isset($row['is_biro']) ? (bool)$row['is_biro'] : null,
            'name' => $row['name'] ?? '',
            'slug' => $row['slug'] ?? null,
            'position' => $row['position'] ?? '',
            'positionLevel' => $positionLevel,
            'positionLevelLabel' => self::POSITION_LEVELS[$positionLevel] ?? $positionLevel,
            'imageUrl' => $row['image_url'] ?? null,
            'bio' => $row['bio'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'linkedin' => $row['linkedin'] ?? null,
            'instagram' => $row['instagram'] ?? null,
            'university' => $row['university'] ?? null,
            'major' => $row['major'] ?? null,
            'displayOrder' => (int)($row['display_order'] ?? 0),
            'isActive' => (bool)($row['is_active'] ?? true)
        ];

        if ($full) {
            $member['createdAt'] = $row['created_at'];
            $member['updatedAt'] = $row['updated_at'];
        }

        return $member;
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Convert camelCase to snake_case
     */
    private function camelToSnake($str) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }

    /**
     * Get position levels for a department type
     * @param string|null $departmentType - 'core', 'biro', 'dept', or null for all
     * @return array
     */
    public function getPositionLevels($departmentType = null) {
        switch ($departmentType) {
            case 'core':
                return array_intersect_key(self::POSITION_LEVELS, array_flip(self::CORE_TEAM_POSITIONS));
            case 'biro':
                return array_intersect_key(self::POSITION_LEVELS, array_flip(self::BIRO_POSITIONS));
            case 'dept':
                return array_intersect_key(self::POSITION_LEVELS, array_flip(self::DEPT_POSITIONS));
            default:
                return self::POSITION_LEVELS;
        }
    }

    /**
     * Get available icons
     * @return array
     */
    public function getAvailableIcons() {
        return self::DEPARTMENT_ICONS;
    }

    /**
     * Get full team structure for a period
     * @param mixed $periodIdentifier
     * @return array
     */
    public function getFullTeamStructure($periodIdentifier) {
        $period = $this->getPeriod($periodIdentifier);
        if (!$period) return null;

        $structure = [
            'period' => $period,
            'ketuaUmum' => null,
            'coreTeam' => [],
            'departments' => []
        ];

        // Get core team
        $coreTeam = $this->getCoreTeam($period['id']);
        foreach ($coreTeam as $member) {
            if ($member['positionLevel'] === 'ketua_umum') {
                $structure['ketuaUmum'] = $member;
            } else {
                $structure['coreTeam'][] = $member;
            }
        }

        // Get departments with members
        $structure['departments'] = $this->getDepartmentsForPeriod($period['id']);

        return $structure;
    }
}
?>
