<?php
// Dashboard.php - Dashboard statistics and analytics functionality

require_once(__DIR__ . "/main.php");

class Dashboard extends ppim {

    /**
     * Check if user is a PPI Campus account
     * PPI Campus accounts are linked to a specific university via university_user table
     * @return boolean
     */
    private function isPPICampusAccount() {
        $query = "SELECT COUNT(*) as count FROM university_user WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return isset($result['count']) && (int)$result['count'] > 0;
    }

    /**
     * Get university ID for PPI Campus account
     * @return int|null
     */
    private function getPPICampusUniversityId() {
        $query = "SELECT university_id FROM university_user WHERE user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return isset($result['university_id']) ? (int)$result['university_id'] : null;
    }

    /**
     * Get total student count
     * For PPI Campus: only students from their university
     * For others: all students
     * @return array
     */
    public function getTotalStudents() {
        $isPPICampus = $this->isPPICampusAccount();
        $universityId = $isPPICampus ? $this->getPPICampusUniversityId() : null;

        $query = "
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) as graduated
            FROM student
        ";

        if ($isPPICampus && $universityId) {
            $query .= " WHERE university_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $universityId);
        } else {
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return [
            'total' => (int)$result['total'],
            'active' => (int)$result['active'],
            'graduated' => (int)$result['graduated']
        ];
    }

    /**
     * Get active PPIM members count
     * For PPI Campus: only members from their university
     * For others: all PPIM members
     * @return array
     */
    public function getActivePPIMMembers() {
        $currentYear = date('Y');
        $isPPICampus = $this->isPPICampusAccount();
        $universityId = $isPPICampus ? $this->getPPICampusUniversityId() : null;

        // For PPI Campus, get PPI Campus members instead of PPIM members
        if ($isPPICampus && $universityId) {
            $query = "
                SELECT COUNT(*) as count
                FROM ppi_campus
                WHERE is_active = 1
                AND university_id = ?
                AND (end_year IS NULL OR end_year >= ?)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $universityId, $currentYear);
        } else {
            // For non-PPI Campus, get PPIM members
            $query = "
                SELECT COUNT(*) as count
                FROM ppim
                WHERE is_active = 1
                AND (end_year IS NULL OR end_year >= ?)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $currentYear);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return [
            'active' => (int)$result['count'],
            'year' => (int)$currentYear
        ];
    }

    /**
     * Get total universities count
     * For PPI Campus: only show 1 (their university)
     * For others: all active universities
     * @return int
     */
    public function getTotalUniversities() {
        $isPPICampus = $this->isPPICampusAccount();

        if ($isPPICampus) {
            return 1; // PPI Campus only sees their own university
        }

        $query = "SELECT COUNT(*) as count FROM university WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return (int)$result['count'];
    }

    /**
     * Get active PPI Campus chapters count
     * For PPI Campus: only their chapter
     * For others: all active chapters
     * @return int
     */
    public function getActivePPIChapters() {
        $currentYear = date('Y');
        $isPPICampus = $this->isPPICampusAccount();
        $universityId = $isPPICampus ? $this->getPPICampusUniversityId() : null;

        if ($isPPICampus && $universityId) {
            // Check if their university has active PPI Campus members
            $query = "
                SELECT COUNT(DISTINCT university_id) as count
                FROM ppi_campus
                WHERE is_active = 1
                AND university_id = ?
                AND (end_year IS NULL OR end_year >= ?)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $universityId, $currentYear);
        } else {
            // Count distinct universities with active PPI Campus members
            $query = "
                SELECT COUNT(DISTINCT university_id) as count
                FROM ppi_campus
                WHERE is_active = 1
                AND (end_year IS NULL OR end_year >= ?)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $currentYear);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return (int)$result['count'];
    }

    /**
     * Get students by qualification level
     * For PPI Campus: filtered by their university
     * For others: all students
     * @return array
     */
    public function getStudentsByQualificationLevel() {
        $isPPICampus = $this->isPPICampusAccount();
        $universityId = $isPPICampus ? $this->getPPICampusUniversityId() : null;

        $query = "
            SELECT
                ql.level_name as name,
                COUNT(s.student_id) as count
            FROM qualification_level ql
            LEFT JOIN student s ON ql.level_id = s.level_of_qualification_id
        ";

        if ($isPPICampus && $universityId) {
            $query .= " AND s.university_id = ?";
        }

        $query .= " GROUP BY ql.level_id, ql.level_name ORDER BY ql.level_order ASC";

        if ($isPPICampus && $universityId) {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $universityId);
        } else {
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'name' => $row['name'],
                'count' => (int)$row['count']
            ];
        }

        return $data;
    }

    /**
     * Get top universities by student count
     * For PPI Campus: only their university
     * For others: top 10 universities
     * @param int $limit
     * @return array
     */
    public function getTopUniversities($limit = 10) {
        $isPPICampus = $this->isPPICampusAccount();
        $universityId = $isPPICampus ? $this->getPPICampusUniversityId() : null;

        if ($isPPICampus && $universityId) {
            $query = "
                SELECT
                    u.university_name as name,
                    COUNT(s.student_id) as count
                FROM university u
                LEFT JOIN student s ON u.university_id = s.university_id
                WHERE u.university_id = ?
                GROUP BY u.university_id, u.university_name
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $universityId);
        } else {
            $query = "
                SELECT
                    u.university_name as name,
                    COUNT(s.student_id) as count
                FROM university u
                LEFT JOIN student s ON u.university_id = s.university_id
                WHERE u.is_active = 1
                GROUP BY u.university_id, u.university_name
                ORDER BY count DESC
                LIMIT ?
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $limit);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'name' => $row['name'],
                'count' => (int)$row['count']
            ];
        }

        return $data;
    }

    /**
     * Get PPIM members by department
     * For PPI Campus: PPI Campus members from their university by department
     * For others: all PPIM members by department
     * @return array
     */
    public function getMembersByDepartment() {
        $currentYear = date('Y');
        $isPPICampus = $this->isPPICampusAccount();
        $universityId = $isPPICampus ? $this->getPPICampusUniversityId() : null;

        if ($isPPICampus && $universityId) {
            // Get PPI Campus members by department for their university
            $query = "
                SELECT
                    COALESCE(department, 'Unspecified') as name,
                    COUNT(*) as count
                FROM ppi_campus
                WHERE is_active = 1
                AND university_id = ?
                AND (end_year IS NULL OR end_year >= ?)
                GROUP BY department
                ORDER BY count DESC
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $universityId, $currentYear);
        } else {
            // Get PPIM members by department
            $query = "
                SELECT
                    COALESCE(department, 'Unspecified') as name,
                    COUNT(*) as count
                FROM ppim
                WHERE is_active = 1
                AND (end_year IS NULL OR end_year >= ?)
                GROUP BY department
                ORDER BY count DESC
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $currentYear);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['name'])) {
                $data[] = [
                    'name' => $row['name'],
                    'count' => (int)$row['count']
                ];
            }
        }

        return $data;
    }

    /**
     * Get coverage map - students by state
     * For PPI Campus: filtered by their university
     * For others: all students
     * @return array
     */
    public function getCoverageByState() {
        $isPPICampus = $this->isPPICampusAccount();
        $universityId = $isPPICampus ? $this->getPPICampusUniversityId() : null;

        $query = "
            SELECT
                p.state_name as state,
                COUNT(s.student_id) as count
            FROM postcode p
            LEFT JOIN student s ON p.zip_code = s.postcode_id
        ";

        if ($isPPICampus && $universityId) {
            $query .= " AND s.university_id = ?";
        }

        $query .= " GROUP BY p.state_name HAVING count > 0 ORDER BY count DESC";

        if ($isPPICampus && $universityId) {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $universityId);
        } else {
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Calculate total for percentage
        $total = 0;
        $rawData = [];
        while ($row = $result->fetch_assoc()) {
            $count = (int)$row['count'];
            $total += $count;
            $rawData[] = [
                'state' => $row['state'],
                'count' => $count
            ];
        }

        // Calculate percentages
        $data = [];
        foreach ($rawData as $item) {
            $data[] = [
                'state' => $item['state'],
                'count' => $item['count'],
                'percentage' => $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0
            ];
        }

        return $data;
    }

    /**
     * Get all dashboard statistics
     * @return array
     */
    public function getAllStats() {
        return [
            'students' => $this->getTotalStudents(),
            'ppimMembers' => $this->getActivePPIMMembers(),
            'universities' => [
                'total' => $this->getTotalUniversities()
            ],
            'ppiChapters' => [
                'active' => $this->getActivePPIChapters()
            ],
            'qualificationLevels' => $this->getStudentsByQualificationLevel(),
            'topUniversities' => $this->getTopUniversities(10),
            'ppimDepartments' => $this->getMembersByDepartment(),
            'coverage' => $this->getCoverageByState(),
            'isPPICampusAccount' => $this->isPPICampusAccount()
        ];
    }
}
?>
