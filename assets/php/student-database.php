<?php
/**
 * Student Database Management Class
 * Only accessible to user_type >= 5 and < 100
 * COMPLETE VERSION with all CRUD operations
 */

require_once(__DIR__."/main.php");

class StudentDatabase extends ppim {
    private $allowedTables = [
        'university_type',
        'qualification_level', 
        'student_status',
        'postcode',
        'university',
        'student',
        'ppim',
        'ppi_campus'
    ];

    /**
     * Constructor - Initialize with access control
     */
    public function __construct() {
        try {
            parent::__construct();
            
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            if (!$this->hasAccess()) {
                header('Location: /access-denied.php');
                exit();
            }
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->handleRequest();
            }
            
        } catch (Exception $e) {
            $this->showAlert('Constructor Error: ' . htmlspecialchars($e->getMessage()), 'danger');
        }
    }

    /**
     * Check if user has access to student database
     * @return boolean
     */
    private function hasAccess() {
        return $this->hasPermission("student_db_view") || $this->hasPermission("student_db_edit") || $this->hasPermission("student_db_add");
    }
    

    /**
     * Handle incoming POST requests for CRUD operations
     */
    private function handleRequest() {
        $action = $_POST['action'] ?? '';
        $table = $_POST['table'] ?? '';

        if (!$this->validateTableName($table)) {
            $this->showAlert('Error: Invalid table name', 'danger');
            return;
        }

        try {
            switch ($action) {
                case 'create':
                    $this->handleCreate($table, $_POST);
                    break;
                case 'update':
                    $this->handleUpdate($table, $_POST);
                    break;
                case 'delete':
                    $this->handleDelete($table, $_POST);
                    break;
                default:
                    $this->showAlert('Error: Invalid action', 'danger');
            }
        } catch (Exception $e) {
            $this->showAlert('Error: ' . htmlspecialchars($e->getMessage()), 'danger');
        }
    }

    /**
     * Validate table name against allowed tables
     */
    private function validateTableName($table) {
        return in_array($table, $this->allowedTables, true);
    }

    /**
     * Show alert message using Toastify.js
     */
    private function showAlert($message, $type = 'success') {
        $configs = [
            'success' => [
                'background' => '#28a745',
                'duration' => 3000
            ],
            'danger' => [
                'background' => '#dc3545', 
                'duration' => 5000
            ],
            'warning' => [
                'background' => '#ffc107',
                'duration' => 4000
            ],
            'info' => [
                'background' => '#17a2b8',
                'duration' => 3000
            ]
        ];
        
        $config = $configs[$type] ?? $configs['success'];
        
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Toastify({
                        text: '" . htmlspecialchars($message, ENT_QUOTES) . "',
                        duration: {$config['duration']},
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '{$config['background']}',
                        stopOnFocus: true
                    }).showToast();
                });
              </script>";
    }

    /**
     * Show specific success messages for different operations
     */
    private function showSuccessMessage($action, $table) {
        $messages = [
            'create' => [
                'student' => 'New student added to database',
                'ppim' => 'Student added to PPIM committee',
                'ppi_campus' => 'Student joined PPI campus chapter',
                'university' => 'New university added',
                'university_type' => 'University type created',
                'qualification_level' => 'Qualification level added',
                'student_status' => 'Student status created',
                'postcode' => 'Postcode added'
            ],
            'update' => [
                'student' => 'Student information updated',
                'ppim' => 'PPIM record updated',
                'ppi_campus' => 'PPI Campus record updated',
                'university' => 'University information updated',
                'university_type' => 'University type updated',
                'qualification_level' => 'Qualification level updated',
                'student_status' => 'Student status updated',
                'postcode' => 'Postcode updated'
            ],
            'delete' => [
                'student' => 'Student removed from database',
                'ppim' => 'PPIM record deleted',
                'ppi_campus' => 'PPI Campus record deleted',
                'university' => 'University deleted',
                'university_type' => 'University type deleted',
                'qualification_level' => 'Qualification level deleted',
                'student_status' => 'Student status deleted',
                'postcode' => 'Postcode deleted'
            ]
        ];
        
        $message = $messages[$action][$table] ?? ucfirst($action) . ' operation completed successfully';
        $this->showAlert($message, 'success');
    }

    /**
     * Handle CREATE operations - COMPLETE VERSION
     */
    private function handleCreate($table, $data) {
        switch ($table) {
            case 'university_type':
                $stmt = $this->conn->prepare("INSERT INTO university_type (type_name, description) VALUES (?, ?)");
                $desc = empty($data['description']) ? null : $data['description'];
                $stmt->bind_param("ss", $data['type_name'], $desc);
                break;

            case 'qualification_level':
                $stmt = $this->conn->prepare("INSERT INTO qualification_level (level_name, level_order, description) VALUES (?, ?, ?)");
                $level_order = (int)($data['level_order'] ?? 0);
                $desc = empty($data['description']) ? null : $data['description'];
                $stmt->bind_param("sis", $data['level_name'], $level_order, $desc);
                break;

            case 'student_status':
                $stmt = $this->conn->prepare("INSERT INTO student_status (status_name, description) VALUES (?, ?)");
                $desc = empty($data['description']) ? null : $data['description'];
                $stmt->bind_param("ss", $data['status_name'], $desc);
                break;

            case 'postcode':
                $stmt = $this->conn->prepare("INSERT INTO postcode (zip_code, city, state_name) VALUES (?, ?, ?)");
                $zip_code = (int)($data['zip_code'] ?? 0);
                $stmt->bind_param("iss", $zip_code, $data['city'], $data['state_name']);
                break;

            case 'university':
                $stmt = $this->conn->prepare("INSERT INTO university (university_name, address, email, phone_num, type_id, postcode_id, is_active) VALUES (?, ?, ?, ?, ?)");
                $type_id = empty($data['type_id']) ? null : (int)$data['type_id'];
                $email = empty($data['email']) ? '' : $data['email'];
                $phone_num = empty($data['phone_num']) ? '' : $data['phone_num'];
                $postcode_id = empty($data['postcode_id']) ? null : (int)$data['postcode_id'];
                $address = empty($data['address']) ? null : $data['address'];
                $is_active = (int)($data['is_active'] ?? 1);
                $stmt->bind_param("ssssiii", $data['university_name'], $email, $phone_num, $address, $type_id, $postcode_id, $is_active);
                break;

            case 'student':
                $stmt = $this->conn->prepare("INSERT INTO student (fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree, level_of_qualification_id, status_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $university_id = empty($data['university_id']) ? null : (int)$data['university_id'];
                $dob = empty($data['dob']) ? null : $data['dob'];
                $email = empty($data['email']) ? null : $data['email'];
                $passport = empty($data['passport']) ? null : $data['passport'];
                $phone = empty($data['phone_number']) ? null : $data['phone_number'];
                $postcode_id = empty($data['postcode_id']) ? null : (int)$data['postcode_id'];
                $address = empty($data['address']) ? null : $data['address'];
                $grad = empty($data['expected_graduate']) ? null : $data['expected_graduate'];
                $degree = empty($data['degree']) ? null : $data['degree'];
                $qual_id = empty($data['level_of_qualification_id']) ? null : (int)$data['level_of_qualification_id'];
                $status_id = empty($data['status_id']) ? 1 : (int)$data['status_id'];
                $is_active = (int)($data['is_active'] ?? 1);
                
                $stmt->bind_param("sissssisssiii", 
                    $data['fullname'], $university_id, $dob, $email, $passport, 
                    $phone, $postcode_id, $address, $grad, $degree, 
                    $qual_id, $status_id, $is_active
                );
                break;

            case 'ppim':
                $stmt = $this->conn->prepare("INSERT INTO ppim (student_id, start_year, end_year, department, position, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $student_id = (int)($data['student_id'] ?? 0);
                $start_year = (int)($data['start_year'] ?? 0);
                $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                $position = empty($data['position']) ? null : $data['position'];
                $desc = empty($data['description']) ? null : $data['description'];
                $is_active = (int)($data['is_active'] ?? 1);
                
                $stmt->bind_param("iiisssi", $student_id, $start_year, $end_year, $data['department'], $position, $desc, $is_active);
                break;

            case 'ppi_campus':
                $stmt = $this->conn->prepare("INSERT INTO ppi_campus (student_id, start_year, end_year, university_id, department, position, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $student_id = (int)($data['student_id'] ?? 0);
                $start_year = (int)($data['start_year'] ?? 0);
                $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                $university_id = (int)($data['university_id'] ?? 0);
                $dept = empty($data['department']) ? null : $data['department'];
                $position = empty($data['position']) ? null : $data['position'];
                $desc = empty($data['description']) ? null : $data['description'];
                $is_active = (int)($data['is_active'] ?? 1);
                
                $stmt->bind_param("iiiisssi", $student_id, $start_year, $end_year, $university_id, $dept, $position, $desc, $is_active);
                break;

            default:
                throw new Exception("Unsupported table for create operation: " . $table);
        }

        if ($stmt->execute()) {
            $this->showSuccessMessage('create', $table);
        } else {
            throw new Exception("Failed to create record: " . $stmt->error);
        }
        $stmt->close();
    }

    /**
     * Handle UPDATE operations - COMPLETE VERSION
     */
    private function handleUpdate($table, $data) {
        switch ($table) {
            case 'university_type':
                $stmt = $this->conn->prepare("UPDATE university_type SET type_name = ?, description = ? WHERE type_id = ?");
                $type_id = (int)($data['type_id'] ?? 0);
                $desc = empty($data['description']) ? null : $data['description'];
                $stmt->bind_param("ssi", $data['type_name'], $desc, $type_id);
                break;

            case 'qualification_level':
                $stmt = $this->conn->prepare("UPDATE qualification_level SET level_name = ?, level_order = ?, description = ? WHERE level_id = ?");
                $level_order = (int)($data['level_order'] ?? 0);
                $level_id = (int)($data['level_id'] ?? 0);
                $desc = empty($data['description']) ? null : $data['description'];
                $stmt->bind_param("sisi", $data['level_name'], $level_order, $desc, $level_id);
                break;

            case 'student_status':
                $stmt = $this->conn->prepare("UPDATE student_status SET status_name = ?, description = ? WHERE status_id = ?");
                $status_id = (int)($data['status_id'] ?? 0);
                $desc = empty($data['description']) ? null : $data['description'];
                $stmt->bind_param("ssi", $data['status_name'], $desc, $status_id);
                break;

            case 'postcode':
                $stmt = $this->conn->prepare("UPDATE postcode SET city = ?, state_name = ? WHERE zip_code = ?");
                $zip_code = (int)($data['zip_code'] ?? 0);
                $stmt->bind_param("ssi", $data['city'], $data['state_name'], $zip_code);
                break;

            case 'university':
                $stmt = $this->conn->prepare("UPDATE university SET university_name = ?, email = ?, phone_num = ?, address = ?, type_id = ?, postcode_id = ?, is_active = ? WHERE university_id = ?");
                $type_id = empty($data['type_id']) ? null : (int)$data['type_id'];
                $email = empty($data['email']) ? '' : $data['email'];
                $phone_num = empty($data['phone_num']) ? '' : $data['phone_num'];
                $postcode_id = empty($data['postcode_id']) ? null : (int)$data['postcode_id'];
                $address = empty($data['address']) ? null : $data['address'];
                $is_active = (int)($data['is_active'] ?? 1);
                $university_id = (int)($data['university_id'] ?? 0);

                $stmt->bind_param("ssssiiii", $data['university_name'], $email, $phone_num, $address, $type_id, $postcode_id, $is_active, $university_id);
                break;

            case 'student':
                $stmt = $this->conn->prepare("UPDATE student SET fullname = ?, university_id = ?, dob = ?, email = ?, passport = ?, phone_number = ?, postcode_id = ?, address = ?, expected_graduate = ?, degree = ?, level_of_qualification_id = ?, status_id = ?, is_active = ? WHERE student_id = ?");
                
                $university_id = empty($data['university_id']) ? null : (int)$data['university_id'];
                $dob = empty($data['dob']) ? null : $data['dob'];
                $email = empty($data['email']) ? null : $data['email'];
                $passport = empty($data['passport']) ? null : $data['passport'];
                $phone = empty($data['phone_number']) ? null : $data['phone_number'];
                $postcode_id = empty($data['postcode_id']) ? null : (int)$data['postcode_id'];
                $address = empty($data['address']) ? null : $data['address'];
                $grad = empty($data['expected_graduate']) ? null : $data['expected_graduate'];
                $degree = empty($data['degree']) ? null : $data['degree'];
                $qual_id = empty($data['level_of_qualification_id']) ? null : (int)$data['level_of_qualification_id'];
                $status_id = empty($data['status_id']) ? 1 : (int)$data['status_id'];
                $is_active = (int)($data['is_active'] ?? 1);
                $student_id = (int)($data['student_id'] ?? 0);
                
                $stmt->bind_param("sissssisssiiii", 
                    $data['fullname'], $university_id, $dob, $email, $passport, 
                    $phone, $postcode_id, $address, $grad, $degree, 
                    $qual_id, $status_id, $is_active, $student_id
                );
                break;

            case 'ppim':
                $stmt = $this->conn->prepare("UPDATE ppim SET end_year = ?, department = ?, position = ?, description = ?, is_active = ? WHERE ppim_id = ?");
                $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                $position = empty($data['position']) ? null : $data['position'];
                $desc = empty($data['description']) ? null : $data['description'];
                $is_active = (int)($data['is_active'] ?? 1);
                $ppim_id = (int)($data['ppim_id'] ?? 0);
                
                $stmt->bind_param("isssii", $end_year, $data['department'], $position, $desc, $is_active, $ppim_id);
                break;

            case 'ppi_campus':
                $stmt = $this->conn->prepare("UPDATE ppi_campus SET end_year = ?, department = ?, position = ?, description = ?, is_active = ? WHERE ppi_campus_id = ?");
                $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                $dept = empty($data['department']) ? null : $data['department'];
                $position = empty($data['position']) ? null : $data['position'];
                $desc = empty($data['description']) ? null : $data['description'];
                $is_active = (int)($data['is_active'] ?? 1);
                $ppi_campus_id = (int)($data['ppi_campus_id'] ?? 0);
                
                $stmt->bind_param("isssii", $end_year, $dept, $position, $desc, $is_active, $ppi_campus_id);
                break;

            default:
                throw new Exception("Update not implemented for table: " . $table);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $this->showSuccessMessage('update', $table);
            } else {
                $this->showAlert('No changes were made', 'info');
            }
        } else {
            throw new Exception("Failed to update record: " . $stmt->error);
        }
        $stmt->close();
    }

    /**
     * Handle DELETE operations - COMPLETE VERSION
     */
    private function handleDelete($table, $data) {
        switch ($table) {
            case 'university_type':
                $stmt = $this->conn->prepare("DELETE FROM university_type WHERE type_id = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            case 'qualification_level':
                $stmt = $this->conn->prepare("DELETE FROM qualification_level WHERE level_id = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            case 'student_status':
                $stmt = $this->conn->prepare("DELETE FROM student_status WHERE status_id = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            case 'postcode':
                $stmt = $this->conn->prepare("DELETE FROM postcode WHERE zip_code = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            case 'university':
                $stmt = $this->conn->prepare("DELETE FROM university WHERE university_id = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            case 'student':
                $stmt = $this->conn->prepare("DELETE FROM student WHERE student_id = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            case 'ppim':
                $stmt = $this->conn->prepare("DELETE FROM ppim WHERE ppim_id = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            case 'ppi_campus':
                $stmt = $this->conn->prepare("DELETE FROM ppi_campus WHERE ppi_campus_id = ?");
                $id = (int)($data['id'] ?? 0);
                $stmt->bind_param("i", $id);
                break;

            default:
                throw new Exception("Delete not implemented for table: " . $table);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $this->showSuccessMessage('delete', $table);
            } else {
                $this->showAlert('Record not found or already deleted', 'warning');
            }
        } else {
            throw new Exception("Failed to delete record: " . $stmt->error);
        }
        $stmt->close();
    }

    /**
     * Get all data from a table
     */
    public function getTableData($table) {
        if (!$this->validateTableName($table)) {
            throw new Exception("Invalid table name: " . $table);
        }

        $query = "SELECT * FROM `$table`";
        
        // Add ORDER BY for specific tables
        switch ($table) {
            case 'qualification_level':
                $query .= " ORDER BY level_order ASC";
                break;
            case 'postcode':
                $query .= " ORDER BY zip_code ASC";
                break;
            default:
                $query .= " ORDER BY 1 ASC"; // Order by first column
                break;
        }

        $result = $this->conn->query($query);
        
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get dropdown options for select fields
     */
    public function getDropdownOptions($table, $valueField, $textField, $additionalField = null) {
        if (!$this->validateTableName($table)) {
            return [];
        }

        $fields = "`$valueField`, `$textField`";
        if ($additionalField) {
            $fields .= ", `$additionalField`";
        }

        $query = "SELECT $fields FROM `$table`";
        
        // Add ordering
        switch ($table) {
            case 'qualification_level':
                $query .= " ORDER BY level_order ASC";
                break;
            case 'postcode':
                $query .= " ORDER BY zip_code ASC";
                break;
            default:
                $query .= " ORDER BY `$textField` ASC";
                break;
        }

        $result = $this->conn->query($query);
        
        if (!$result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get data with joins for complex tables
     */
    public function getTableDataWithJoins($table) {
        switch ($table) {
            case 'university':
                $query = "SELECT u.*, ut.type_name, p.zip_code, p.city 
                         FROM university u 
                         LEFT JOIN university_type ut ON u.type_id = ut.type_id 
                         LEFT JOIN postcode p ON u.postcode_id = p.zip_code
                         ORDER BY u.university_name ASC";
                break;

            case 'student':
                $query = "SELECT s.*, u.university_name, p.zip_code, p.city, 
                                ql.level_name, ss.status_name 
                         FROM student s 
                         LEFT JOIN university u ON s.university_id = u.university_id 
                         LEFT JOIN postcode p ON s.postcode_id = p.zip_code 
                         LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                         LEFT JOIN student_status ss ON s.status_id = ss.status_id
                         ORDER BY s.fullname ASC";
                break;

            case 'ppim':
                $query = "SELECT p.*, s.fullname 
                         FROM ppim p 
                         LEFT JOIN student s ON p.student_id = s.student_id
                         ORDER BY p.start_year DESC, s.fullname ASC";
                break;

            case 'ppi_campus':
                $query = "SELECT pc.*, s.fullname, u.university_name 
                         FROM ppi_campus pc 
                         LEFT JOIN student s ON pc.student_id = s.student_id 
                         LEFT JOIN university u ON pc.university_id = u.university_id
                         ORDER BY pc.start_year DESC, s.fullname ASC";
                break;

            default:
                return $this->getTableData($table);
        }

        $result = $this->conn->query($query);
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get paginated data with joins and search functionality
     */
    public function getPaginatedTableDataWithJoins($table, $page = 1, $limit = 10, $search = '', $sort = null, $dir = 'asc') {
        $offset = ($page - 1) * $limit;
        
        switch ($table) {
            case 'university':
                $baseQuery = "SELECT u.*, ut.type_name, p.zip_code, p.city 
                             FROM university u 
                             LEFT JOIN university_type ut ON u.type_id = ut.type_id 
                             LEFT JOIN postcode p ON u.postcode_id = p.zip_code";
                
                $whereClause = "";
                $params = [];
                $types = "";
                
                if (!empty($search)) {
                    $whereClause = " WHERE (u.university_name LIKE ? OR u.address LIKE ? OR ut.type_name LIKE ? OR p.city LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    $types = "ssss";
                }
                
                $orderClause = " ORDER BY u.university_name ASC";
                $limitClause = " LIMIT ? OFFSET ?";
                
                $query = $baseQuery . $whereClause . $orderClause . $limitClause;
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";
                
                break;

            case 'student':
                $baseQuery = "SELECT s.*, u.university_name, p.zip_code, p.city, 
                                    ql.level_name, ss.status_name 
                             FROM student s 
                             LEFT JOIN university u ON s.university_id = u.university_id 
                             LEFT JOIN postcode p ON s.postcode_id = p.zip_code 
                             LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                             LEFT JOIN student_status ss ON s.status_id = ss.status_id";
                
                $whereClause = "";
                $params = [];
                $types = "";
                
                if (!empty($search)) {
                    $whereClause = " WHERE (s.fullname LIKE ? OR s.email LIKE ? OR u.university_name LIKE ? OR p.city LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    $types = "ssss";
                }
                
                // Sorting support for specific columns
                $allowedSorts = [
                    'id' => 's.student_id',
                    'fullname' => 's.fullname',
                    'university' => 'u.university_name',
                    'degree' => 's.degree',
                ];
                $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
                if ($sort && isset($allowedSorts[$sort])) {
                    $orderClause = " ORDER BY " . $allowedSorts[$sort] . " " . $dir;
                } else {
                    $orderClause = " ORDER BY s.fullname ASC"; // default
                }
                $limitClause = " LIMIT ? OFFSET ?";
                
                $query = $baseQuery . $whereClause . $orderClause . $limitClause;
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";
                
                break;
            
            case 'postcode':
                $baseQuery = "SELECT p.* 
                             FROM postcode p";
                
                $whereClause = "";
                $params = [];
                $types = "";
                
                if (!empty($search)) {
                    $whereClause = " WHERE (p.zip_code LIKE ? OR p.city LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam];
                    $types = "ss";
                }
                
                $orderClause = " ORDER BY p.zip_code ASC";
                $limitClause = " LIMIT ? OFFSET ?";
                
                $query = $baseQuery . $whereClause . $orderClause . $limitClause;
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";
                
                break;
            
            case 'ppi_campus':
                $baseQuery = "SELECT pc.*, s.fullname, u.university_name 
                             FROM ppi_campus pc 
                             LEFT JOIN student s ON pc.student_id = s.student_id 
                             LEFT JOIN university u ON pc.university_id = u.university_id";

                $whereClause = "";
                $params = [];
                $types = "";

                if (!empty($search)) {
                    $whereClause = " WHERE (s.fullname LIKE ? OR u.university_name LIKE ? OR pc.department LIKE ? OR pc.position LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    $types = "ssss";
                }

                $orderClause = " ORDER BY pc.start_year DESC, s.fullname ASC";
                $limitClause = " LIMIT ? OFFSET ?";

                $query = $baseQuery . $whereClause . $orderClause . $limitClause;
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";
                
                break;
            
            case 'ppim':
                $baseQuery = "SELECT p.*, s.fullname 
                             FROM ppim p 
                             LEFT JOIN student s ON p.student_id = s.student_id";

                $whereClause = "";
                $params = [];
                $types = "";

                if (!empty($search)) {
                    $whereClause = " WHERE (s.fullname LIKE ? OR p.department LIKE ? OR p.position LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam];
                    $types = "sss";
                }

                // Sorting support
                $allowedSorts = [
                    'id' => 'p.ppim_id',
                    'fullname' => 's.fullname',
                    'start_year' => 'p.start_year',
                    'end_year' => 'p.end_year',
                    'department' => 'p.department',
                    'position' => 'p.position',
                ];
                $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
                if ($sort && isset($allowedSorts[$sort])) {
                    $orderClause = " ORDER BY " . $allowedSorts[$sort] . " " . $dir;
                } else {
                    $orderClause = " ORDER BY p.start_year DESC, s.fullname ASC";
                }
                $limitClause = " LIMIT ? OFFSET ?";

                $query = $baseQuery . $whereClause . $orderClause . $limitClause;
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";

                break;
            default:
                throw new Exception("Pagination not implemented for table: " . $table);
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount($table, $search = '') {
        switch ($table) {
            case 'university':
                $baseQuery = "SELECT COUNT(*) as total 
                             FROM university u 
                             LEFT JOIN university_type ut ON u.type_id = ut.type_id 
                             LEFT JOIN postcode p ON u.postcode_id = p.zip_code";
                
                $whereClause = "";
                $params = [];
                $types = "";
                
                if (!empty($search)) {
                    $whereClause = " WHERE (u.university_name LIKE ? OR u.address LIKE ? OR ut.type_name LIKE ? OR p.city LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    $types = "ssss";
                }
                
                $query = $baseQuery . $whereClause;
                break;

            case 'student':
                $baseQuery = "SELECT COUNT(*) as total 
                             FROM student s 
                             LEFT JOIN university u ON s.university_id = u.university_id 
                             LEFT JOIN postcode p ON s.postcode_id = p.zip_code 
                             LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                             LEFT JOIN student_status ss ON s.status_id = ss.status_id";
                
                $whereClause = "";
                $params = [];
                $types = "";
                
                if (!empty($search)) {
                    $whereClause = " WHERE (s.fullname LIKE ? OR s.email LIKE ? OR u.university_name LIKE ? OR p.city LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    $types = "ssss";
                }
                
                $query = $baseQuery . $whereClause;
                break;

            case 'postcode':
                $baseQuery = "SELECT COUNT(*) as total 
                             FROM postcode p";
                
                $whereClause = "";
                $params = [];
                $types = "";
                
                if (!empty($search)) {
                    $whereClause = " WHERE (p.zip_code LIKE ? OR p.city LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam];
                    $types = "ss";
                }
                
                $query = $baseQuery . $whereClause;
                break;
            
            case 'ppi_campus':
                $baseQuery = "SELECT COUNT(*) as total 
                             FROM ppi_campus pc 
                             LEFT JOIN student s ON pc.student_id = s.student_id 
                             LEFT JOIN university u ON pc.university_id = u.university_id";

                $whereClause = "";
                $params = [];
                $types = "";

                if (!empty($search)) {
                    $whereClause = " WHERE (s.fullname LIKE ? OR u.university_name LIKE ? OR pc.department LIKE ? OR pc.position LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    $types = "ssss";
                }

                $query = $baseQuery . $whereClause;
                break;
            
            case 'ppim':
                $baseQuery = "SELECT COUNT(*) as total 
                             FROM ppim p 
                             LEFT JOIN student s ON p.student_id = s.student_id";

                $whereClause = "";
                $params = [];
                $types = "";

                if (!empty($search)) {
                    $whereClause = " WHERE (s.fullname LIKE ? OR p.department LIKE ? OR p.position LIKE ?)";
                    $searchParam = "%$search%";
                    $params = [$searchParam, $searchParam, $searchParam];
                    $types = "sss";
                }

                $query = $baseQuery . $whereClause;
                break;
            default:
                throw new Exception("Count not implemented for table: " . $table);
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Get user permissions info for frontend
     */
    public function getUserInfo() {
        $userTypes = [
            1 => 'Department Staff',
            2 => 'Communications',
            3 => 'Social Affairs',
            5 => 'Secretary',
            6 => 'Data Administrator',
            99 => 'Super Admin'
        ];

        return [
            'user_id' => $this->getUserId(),
            'user_name' => $this->getUserName(),
            'user_type' => $userTypes[$this->getUserType()] ?? 'Unknown',
            'user_type_id' => $this->getUserType(),
            'has_access' => $this->hasAccess()
        ];
    }
}

// Initialize the student database class
$studentDB = new StudentDatabase();
?>
