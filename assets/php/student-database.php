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

            // Only check permissions and redirect if not in API mode
            if (!defined('API_MODE') && !$this->hasFullAccess() && !$this->isPPICampus()) {
                header('Location: /access-denied.php');
                exit();
            }

            // Only handle POST requests if not called from API
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !defined('API_MODE')) {
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
    public function hasFullAccess() {
        return $this->hasPermission("student_db_view") || $this->hasPermission("student_db_edit") || $this->hasPermission("student_db_add");
    }

    /**
     * Check if user is PPI Campus
     * @return boolean
     */
    public function isPPICampus() {
        return $this->hasPermission("campus_student_db_view");
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
    protected function showAlert($message, $type = 'success') {
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
        if($this->hasFullAccess()){
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
                    $stmt = $this->conn->prepare("INSERT INTO university (university_name, email, phone_num, address, type_id, postcode_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $type_id = empty($data['type_id']) ? null : (int)$data['type_id'];
                    $email = empty($data['email']) ? '' : $data['email'];
                    $phone_num = empty($data['phone_num']) ? '' : $data['phone_num'];
                    $postcode_id = empty($data['postcode_id']) ? null : $data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $stmt->bind_param("ssssisi", $data['university_name'], $email, $phone_num, $address, $type_id, $postcode_id, $is_active);
                    break;

                case 'student':
                    $stmt = $this->conn->prepare("INSERT INTO student (fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree, level_of_qualification_id, status_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $university_id = empty($data['university_id']) ? null : (int)$data['university_id'];
                    $dob = empty($data['dob']) ? null : $data['dob'];
                    $email = empty($data['email']) ? null : $data['email'];
                    $passport = empty($data['passport']) ? null : $data['passport'];
                    $phone = empty($data['phone_number']) ? null : $data['phone_number'];
                    $postcode_id = empty($data['postcode_id']) ? null : $data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $grad = empty($data['expected_graduate']) ? null : $data['expected_graduate'];
                    $degree = empty($data['degree']) ? null : $data['degree'];
                    $qual_id = empty($data['level_of_qualification_id']) ? null : (int)$data['level_of_qualification_id'];
                    $status_id = empty($data['status_id']) ? 1 : (int)$data['status_id'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    
                    $stmt->bind_param("sissssssssiii", 
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
        }
        else{
            $university_id = $this->getCampusID();
            switch ($table) {
                case 'student':
                    $stmt = $this->conn->prepare("INSERT INTO student (fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree, level_of_qualification_id, status_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $dob = empty($data['dob']) ? null : $data['dob'];
                    $email = empty($data['email']) ? null : $data['email'];
                    $passport = empty($data['passport']) ? null : $data['passport'];
                    $phone = empty($data['phone_number']) ? null : $data['phone_number'];
                    $postcode_id = empty($data['postcode_id']) ? null : $data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $grad = empty($data['expected_graduate']) ? null : $data['expected_graduate'];
                    $degree = empty($data['degree']) ? null : $data['degree'];
                    $qual_id = empty($data['level_of_qualification_id']) ? null : (int)$data['level_of_qualification_id'];
                    $status_id = empty($data['status_id']) ? 1 : (int)$data['status_id'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    
                    $stmt->bind_param("sissssssssiii", 
                        $data['fullname'], $university_id, $dob, $email, $passport, 
                        $phone, $postcode_id, $address, $grad, $degree, 
                        $qual_id, $status_id, $is_active
                    );
                    break;

                case 'ppi_campus':
                    $stmt = $this->conn->prepare("INSERT INTO ppi_campus (student_id, start_year, end_year, university_id, department, position, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $student_id = (int)($data['student_id'] ?? 0);
                    $start_year = (int)($data['start_year'] ?? 0);
                    $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                    $dept = empty($data['department']) ? null : $data['department'];
                    $position = empty($data['position']) ? null : $data['position'];
                    $desc = empty($data['description']) ? null : $data['description'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    
                    $stmt->bind_param("iiiisssi", $student_id, $start_year, $end_year, $university_id, $dept, $position, $desc, $is_active);
                    break;

                default:
                    throw new Exception("Unsupported table for create operation: " . $table);
            }
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
        if($this->hasFullAccess()){
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
                    $zip_code = ($data['zip_code'] ?? 0);
                    $stmt->bind_param("sss", $data['city'], $data['state_name'], $zip_code);
                    break;

                case 'university':
                    $stmt = $this->conn->prepare("UPDATE university SET university_name = ?, email = ?, phone_num = ?, address = ?, type_id = ?, postcode_id = ?, is_active = ? WHERE university_id = ?");
                    $type_id = empty($data['type_id']) ? null : (int)$data['type_id'];
                    $email = empty($data['email']) ? '' : $data['email'];
                    $phone_num = empty($data['phone_num']) ? '' : $data['phone_num'];
                    $postcode_id = empty($data['postcode_id']) ? null : $data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $university_id = (int)($data['university_id'] ?? 0);

                    $stmt->bind_param("ssssssii", $data['university_name'], $email, $phone_num, $address, $type_id, $postcode_id, $is_active, $university_id);
                    break;

                case 'student':
                    $stmt = $this->conn->prepare("UPDATE student SET fullname = ?, university_id = ?, dob = ?, email = ?, passport = ?, phone_number = ?, postcode_id = ?, address = ?, expected_graduate = ?, degree = ?, level_of_qualification_id = ?, status_id = ?, is_active = ? WHERE student_id = ?");
                    
                    $university_id = empty($data['university_id']) ? null : (int)$data['university_id'];
                    $dob = empty($data['dob']) ? null : $data['dob'];
                    $email = empty($data['email']) ? null : $data['email'];
                    $passport = empty($data['passport']) ? null : $data['passport'];
                    $phone = empty($data['phone_number']) ? null : $data['phone_number'];
                    $postcode_id = empty($data['postcode_id']) ? null : $data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $grad = empty($data['expected_graduate']) ? null : $data['expected_graduate'];
                    $degree = empty($data['degree']) ? null : $data['degree'];
                    $qual_id = empty($data['level_of_qualification_id']) ? null : (int)$data['level_of_qualification_id'];
                    $status_id = empty($data['status_id']) ? 1 : (int)$data['status_id'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $student_id = (int)($data['student_id'] ?? 0);
                    
                    $stmt->bind_param("sissssssssiiii", 
                        $data['fullname'], $university_id, $dob, $email, $passport, 
                        $phone, $postcode_id, $address, $grad, $degree, 
                        $qual_id, $status_id, $is_active, $student_id
                    );
                    break;

                case 'ppim':
                    $stmt = $this->conn->prepare("UPDATE ppim SET start_year = ?, end_year = ?, department = ?, position = ?, description = ?, is_active = ? WHERE ppim_id = ?");
                    $start_year = empty($data['start_year']) ? null : (int)$data['start_year'];
                    $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                    $position = empty($data['position']) ? null : $data['position'];
                    $desc = empty($data['description']) ? null : $data['description'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $ppim_id = (int)($data['ppim_id'] ?? 0);
                    
                    $stmt->bind_param("iisssii", $start_year, $end_year, $data['department'], $position, $desc, $is_active, $ppim_id);
                    break;

                case 'ppi_campus':
                    $stmt = $this->conn->prepare("UPDATE ppi_campus SET start_year = ?, end_year = ?, department = ?, position = ?, description = ?, is_active = ? WHERE ppi_campus_id = ?");
                    $start_year = empty($data['start_year']) ? null : (int)$data['start_year'];
                    $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                    $dept = empty($data['department']) ? null : $data['department'];
                    $position = empty($data['position']) ? null : $data['position'];
                    $desc = empty($data['description']) ? null : $data['description'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $ppi_campus_id = (int)($data['ppi_campus_id'] ?? 0);
                    
                    $stmt->bind_param("iisssii", $start_year, $end_year, $dept, $position, $desc, $is_active, $ppi_campus_id);
                    break;

                default:
                    throw new Exception("Update not implemented for table: " . $table);
            }
        } else {
            $university_id = $this->getCampusID();
            switch ($table) {
                case 'student':
                    $stmt = $this->conn->prepare("UPDATE student SET fullname = ?, university_id = ?, dob = ?, email = ?, passport = ?, phone_number = ?, postcode_id = ?, address = ?, expected_graduate = ?, degree = ?, level_of_qualification_id = ?, status_id = ?, is_active = ? WHERE student_id = ? AND university_id = ?");
                    $dob = empty($data['dob']) ? null : $data['dob'];
                    $email = empty($data['email']) ? null : $data['email'];
                    $passport = empty($data['passport']) ? null : $data['passport'];
                    $phone = empty($data['phone_number']) ? null : $data['phone_number'];
                    $postcode_id = empty($data['postcode_id']) ? null : $data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $grad = empty($data['expected_graduate']) ? null : $data['expected_graduate'];
                    $degree = empty($data['degree']) ? null : $data['degree'];
                    $qual_id = empty($data['level_of_qualification_id']) ? null : (int)$data['level_of_qualification_id'];
                    $status_id = empty($data['status_id']) ? 1 : (int)$data['status_id'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $student_id = (int)($data['student_id'] ?? 0);
                    
                    $stmt->bind_param("sissssssssiiiii", 
                        $data['fullname'], $university_id, $dob, $email, $passport, 
                        $phone, $postcode_id, $address, $grad, $degree, 
                        $qual_id, $status_id, $is_active, $student_id, $university_id
                    );
                    break;

                case 'ppi_campus':
                    $stmt = $this->conn->prepare("UPDATE ppi_campus SET start_year = ?, end_year = ?, department = ?, position = ?, description = ?, is_active = ? WHERE ppi_campus_id = ? AND university_id = ?");
                    $start_year = empty($data['start_year']) ? null : (int)$data['start_year'];
                    $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                    $dept = empty($data['department']) ? null : $data['department'];
                    $position = empty($data['position']) ? null : $data['position'];
                    $desc = empty($data['description']) ? null : $data['description'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $ppi_campus_id = (int)($data['ppi_campus_id'] ?? 0);
                    
                    $stmt->bind_param("iisssiii", $start_year, $end_year, $dept, $position, $desc, $is_active, $ppi_campus_id, $university_id);
                    break;

                default:
                    throw new Exception("Update not implemented for table: " . $table);
            }
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
        if($this->hasFullAccess()){
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
        }
        else {
            $university_id = $this->getCampusID();
            switch ($table) {
                case 'student':
                    $stmt = $this->conn->prepare("DELETE FROM student WHERE student_id = ? AND university_id = ?");
                    $id = (int)($data['id'] ?? 0);
                    $stmt->bind_param("ii", $id, $university_id);
                    break;

                case 'ppi_campus':
                    $stmt = $this->conn->prepare("DELETE FROM ppi_campus WHERE ppi_campus_id = ? AND university_id = ?");
                    $id = (int)($data['id'] ?? 0);
                    $stmt->bind_param("ii", $id, $university_id);
                    break;

                default:
                    throw new Exception("Delete not implemented for table: " . $table);
            }
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
    public function getDropdownOptions(string $table, string $valueField, string $textField, ?string $additionalField = null): array
    {
        if (!$this->validateTableName($table)) return [];

        // Allow only [a-z0-9_]
        $ok = fn($c) => (bool)preg_match('/^[a-zA-Z0-9_]+$/', $c);
        foreach ([$valueField, $textField, $additionalField] as $c) {
            if ($c !== null && !$ok($c)) return [];
        }

        // Per-table allowed columns (add as needed)
        $allow = [
            'university'           => ['university_id','university_name','type_id','postcode_id','address'],
            'university_type'      => ['type_id','type_name'],
            'qualification_level'  => ['level_id','level_name','level_order'],
            'postcode'             => ['zip_code','city','state_name'],
            'student_status'       => ['status_id', 'status_name'],
            'student'              => ['student_id','fullname','university_id','email'],
            'ppim'                 => ['ppim_id','student_id','department','position','start_year','end_year'],
            'ppi_campus'           => ['ppi_campus_id','student_id','university_id','department','position','start_year','end_year'],
        ];
        if (!isset($allow[$table])) return [];

        foreach ([$valueField, $textField, $additionalField] as $c) {
            if ($c !== null && !in_array($c, $allow[$table], true)) return [];
        }

        $fields = "`$valueField`, `$textField`" . ($additionalField ? ", `$additionalField`" : "");
        $base   = "SELECT $fields FROM `$table`";

        // Limited access filter for tables with university_id
        $hasUniCol = in_array('university_id', $allow[$table], true);
        $where = '';
        $types = '';
        $params = [];

        if (!$this->hasFullAccess() && $hasUniCol) {
            $where = " WHERE `university_id` = ?";
            $types = 'i';
            $params[] = $this->getCampusID();
        }

        // Ordering
        switch ($table) {
            case 'qualification_level':
                $order = " ORDER BY `level_order` ASC, `$textField` ASC";
                break;
            case 'postcode':
                // if textField is not zip_code, still sort by zip_code first for numeric order
                $order = " ORDER BY `zip_code` ASC, `$textField` ASC";
                break;
            default:
                $order = " ORDER BY `$textField` ASC";
                break;
        }

        $sql = $base . $where . $order;

        if ($types !== '') {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $this->conn->query($sql);
        }
        if (!$res) return [];

        return $res->fetch_all(MYSQLI_ASSOC);
    }


    /**
     * Get data with joins for complex tables
     */
    public function getTableDataWithJoins(string $table) {
        $conn = $this->conn;

        // Map allowed tables
        $allowed = ['university','student','ppim','ppi_campus'];
        if (!in_array($table, $allowed, true)) return $this->getTableData($table);

        $sql = null;
        $bind = null; // ['types', $var1, $var2, ...]
        
        if ($this->hasFullAccess()) {
            switch ($table) {
                case 'university':
                    $sql = "
                        SELECT u.*, ut.type_name, p.zip_code, p.city
                        FROM university u
                        LEFT JOIN university_type ut ON u.type_id = ut.type_id
                        LEFT JOIN postcode p        ON u.postcode_id = p.zip_code
                        ORDER BY u.university_name ASC";
                    break;

                case 'student':
                    $sql = "
                        SELECT s.*, u.university_name, p.zip_code, p.city,
                            ql.level_name, ss.status_name
                        FROM student s
                        LEFT JOIN university u          ON s.university_id = u.university_id
                        LEFT JOIN postcode p            ON s.postcode_id = p.zip_code
                        LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                        LEFT JOIN student_status ss     ON s.status_id = ss.status_id
                        ORDER BY s.fullname ASC";
                    break;

                case 'ppim':
                    $sql = "
                        SELECT p.*, s.fullname
                        FROM ppim p
                        LEFT JOIN student s ON p.student_id = s.student_id
                        ORDER BY p.start_year DESC, s.fullname ASC";
                    break;

                case 'ppi_campus':
                    $sql = "
                        SELECT pc.*, s.fullname, u.university_name
                        FROM ppi_campus pc
                        LEFT JOIN student s   ON pc.student_id = s.student_id
                        LEFT JOIN university u ON pc.university_id = u.university_id
                        ORDER BY pc.start_year DESC, s.fullname ASC";
                    break;
            }
        } else {
            $university_id = $this->getCampusID();

            switch ($table) {
                case 'student':
                    $sql = "
                        SELECT s.*, u.university_name, p.zip_code, p.city,
                            ql.level_name, ss.status_name
                        FROM student s
                        LEFT JOIN university u          ON s.university_id = u.university_id
                        LEFT JOIN postcode p            ON s.postcode_id = p.zip_code
                        LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                        LEFT JOIN student_status ss     ON s.status_id = ss.status_id
                        WHERE s.university_id = ?
                        ORDER BY s.fullname ASC";
                    $bind = ['i', $university_id];
                    break;

                case 'ppi_campus':
                    $sql = "
                        SELECT pc.*, s.fullname, u.university_name
                        FROM ppi_campus pc
                        LEFT JOIN student s   ON pc.student_id = s.student_id
                        LEFT JOIN university u ON pc.university_id = u.university_id
                        WHERE pc.university_id = ?
                        ORDER BY pc.start_year DESC, s.fullname ASC";
                    $bind = ['i', $university_id];
                    break;

                default:
                    return null;
            }
        }

        if ($sql === null) return null;

        if ($bind) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param($bind[0], $bind[1]);
            $stmt->execute();
            $res = $stmt->get_result();
            if (!$res) throw new Exception("Query failed: " . $conn->error);
            return $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $res = $conn->query($sql);
            if (!$res) throw new Exception("Query failed: " . $conn->error);
            return $res->fetch_all(MYSQLI_ASSOC);
        }
    }


    /**
     * Get paginated data with joins and search functionality
     */
    public function getPaginatedTableDataWithJoins($table, $page = 1, $limit = 10, $search = '', $sort = null, $dir = 'asc') {
        $offset = ($page - 1) * $limit;
        $conn = $this->conn;

        $params = [];
        $types  = '';
        $whereClause = '';

        $searchParam = "%$search%";

        // ===== FULL ACCESS =====
        if ($this->hasFullAccess()) {
            switch ($table) {
                case 'university':
                    $baseQuery = "
                        SELECT u.*, ut.type_name, p.zip_code, p.city
                        FROM university u
                        LEFT JOIN university_type ut ON u.type_id = ut.type_id
                        LEFT JOIN postcode p ON u.postcode_id = p.zip_code";

                    if (!empty($search)) {
                        $whereClause = " WHERE (u.university_name LIKE ? OR u.address LIKE ? OR ut.type_name LIKE ? OR p.city LIKE ?)";
                        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                        $types = "ssss";
                    }

                    $orderClause = " ORDER BY u.university_name ASC";
                    break;

                case 'student':
                    $baseQuery = "
                        SELECT s.*, u.university_name, p.zip_code, p.city,
                            ql.level_name, ss.status_name
                        FROM student s
                        LEFT JOIN university u          ON s.university_id = u.university_id
                        LEFT JOIN postcode p            ON s.postcode_id = p.zip_code
                        LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                        LEFT JOIN student_status ss     ON s.status_id = ss.status_id";

                    if (!empty($search)) {
                        $whereClause = " WHERE (s.fullname LIKE ? OR s.email LIKE ? OR u.university_name LIKE ? OR p.city LIKE ?)";
                        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                        $types = "ssss";
                    }

                    $allowedSorts = [
                        'id' => 's.student_id',
                        'fullname' => 's.fullname',
                        'university' => 'u.university_name',
                        'degree' => 's.degree',
                    ];
                    $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
                    $orderClause = isset($allowedSorts[$sort])
                        ? " ORDER BY {$allowedSorts[$sort]} $dir"
                        : " ORDER BY s.fullname ASC";
                    break;

                case 'postcode':
                    $baseQuery = "SELECT p.* FROM postcode p";
                    if (!empty($search)) {
                        $whereClause = " WHERE (p.zip_code LIKE ? OR p.city LIKE ?)";
                        $params = [$searchParam, $searchParam];
                        $types = "ss";
                    }
                    $orderClause = " ORDER BY p.zip_code ASC";
                    break;

                case 'ppi_campus':
                    $baseQuery = "
                        SELECT pc.*, s.fullname, u.university_name
                        FROM ppi_campus pc
                        LEFT JOIN student s ON pc.student_id = s.student_id
                        LEFT JOIN university u ON pc.university_id = u.university_id";

                    if (!empty($search)) {
                        $whereClause = " WHERE (s.fullname LIKE ? OR u.university_name LIKE ? OR pc.department LIKE ? OR pc.position LIKE ?)";
                        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                        $types = "ssss";
                    }
                    $orderClause = " ORDER BY pc.start_year DESC, s.fullname ASC";
                    break;

                case 'ppim':
                    $baseQuery = "
                        SELECT p.*, s.fullname
                        FROM ppim p
                        LEFT JOIN student s ON p.student_id = s.student_id";

                    if (!empty($search)) {
                        $whereClause = " WHERE (s.fullname LIKE ? OR p.department LIKE ? OR p.position LIKE ?)";
                        $params = [$searchParam, $searchParam, $searchParam];
                        $types = "sss";
                    }

                    $allowedSorts = [
                        'id' => 'p.ppim_id',
                        'fullname' => 's.fullname',
                        'start_year' => 'p.start_year',
                        'end_year' => 'p.end_year',
                        'department' => 'p.department',
                        'position' => 'p.position',
                    ];
                    $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
                    $orderClause = isset($allowedSorts[$sort])
                        ? " ORDER BY {$allowedSorts[$sort]} $dir"
                        : " ORDER BY p.start_year DESC, s.fullname ASC";
                    break;

                default:
                    throw new Exception("Pagination not implemented for table: " . $table);
            }
        }

        // ===== LIMITED ACCESS =====
        else {
            $university_id = $this->getCampusID();

            switch ($table) {
                case 'student':
                    $baseQuery = "
                        SELECT s.*, u.university_name, p.zip_code, p.city,
                            ql.level_name, ss.status_name
                        FROM student s
                        LEFT JOIN university u          ON s.university_id = u.university_id
                        LEFT JOIN postcode p            ON s.postcode_id = p.zip_code
                        LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                        LEFT JOIN student_status ss     ON s.status_id = ss.status_id
                        WHERE s.university_id = ?";

                    $params = [$university_id];
                    $types = 'i';

                    if (!empty($search)) {
                        $baseQuery .= " AND (s.fullname LIKE ? OR s.email LIKE ?)";
                        $params[] = $searchParam;
                        $params[] = $searchParam;
                        $types .= "ss";
                    }

                    $orderClause = " ORDER BY s.fullname ASC";
                    break;

                case 'ppi_campus':
                    $baseQuery = "
                        SELECT pc.*, s.fullname, u.university_name
                        FROM ppi_campus pc
                        LEFT JOIN student s ON pc.student_id = s.student_id
                        LEFT JOIN university u ON pc.university_id = u.university_id
                        WHERE pc.university_id = ?";

                    $params = [$university_id];
                    $types = 'i';

                    if (!empty($search)) {
                        $baseQuery .= " AND (s.fullname LIKE ? OR pc.department LIKE ?)";
                        $params[] = $searchParam;
                        $params[] = $searchParam;
                        $types .= "ss";
                    }

                    $orderClause = " ORDER BY pc.start_year DESC, s.fullname ASC";
                    break;

                default:
                    throw new Exception("Limited access pagination not implemented for table: " . $table);
            }
        }

        // finalize query
        $limitClause = " LIMIT ? OFFSET ?";
        $query = ($baseQuery ?? '') . ($whereClause ?? '') . ($orderClause ?? '') . $limitClause;

        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) throw new Exception("Query failed: " . $conn->error);

        return $result->fetch_all(MYSQLI_ASSOC);
    }


    /**
     * Get total count for pagination
     */
    public function getTotalCount($table, $search = ''): int {
        $conn = $this->conn;
        $searchParam = "%$search%";

        if ($this->hasFullAccess()) {
            switch ($table) {
                case 'university': {
                    $sql = "
                    SELECT COUNT(*) AS total
                    FROM university u
                    LEFT JOIN university_type ut ON u.type_id = ut.type_id
                    LEFT JOIN postcode p        ON u.postcode_id = p.zip_code";
                    $types = '';
                    $params = [];
                    if ($search !== '') {
                        $sql .= " WHERE (u.university_name LIKE ? OR u.address LIKE ? OR ut.type_name LIKE ? OR p.city LIKE ?)";
                        $types = 'ssss';
                        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    }
                    break;
                }
                case 'student': {
                    $sql = "
                    SELECT COUNT(*) AS total
                    FROM student s
                    LEFT JOIN university u          ON s.university_id = u.university_id
                    LEFT JOIN postcode p            ON s.postcode_id = p.zip_code
                    LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                    LEFT JOIN student_status ss     ON s.status_id = ss.status_id";
                    $types = '';
                    $params = [];
                    if ($search !== '') {
                        $sql .= " WHERE (s.fullname LIKE ? OR s.email LIKE ? OR u.university_name LIKE ? OR p.city LIKE ?)";
                        $types = 'ssss';
                        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    }
                    break;
                }
                case 'postcode': {
                    $sql = "SELECT COUNT(*) AS total FROM postcode p";
                    $types = '';
                    $params = [];
                    if ($search !== '') {
                        $sql .= " WHERE (p.zip_code LIKE ? OR p.city LIKE ?)";
                        $types = 'ss';
                        $params = [$searchParam, $searchParam];
                    }
                    break;
                }
                case 'ppi_campus': {
                    $sql = "
                    SELECT COUNT(*) AS total
                    FROM ppi_campus pc
                    LEFT JOIN student s   ON pc.student_id = s.student_id
                    LEFT JOIN university u ON pc.university_id = u.university_id";
                    $types = '';
                    $params = [];
                    if ($search !== '') {
                        $sql .= " WHERE (s.fullname LIKE ? OR u.university_name LIKE ? OR pc.department LIKE ? OR pc.position LIKE ?)";
                        $types = 'ssss';
                        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                    }
                    break;
                }
                case 'ppim': {
                    $sql = "
                    SELECT COUNT(*) AS total
                    FROM ppim p
                    LEFT JOIN student s ON p.student_id = s.student_id";
                    $types = '';
                    $params = [];
                    if ($search !== '') {
                        $sql .= " WHERE (s.fullname LIKE ? OR p.department LIKE ? OR p.position LIKE ?)";
                        $types = 'sss';
                        $params = [$searchParam, $searchParam, $searchParam];
                    }
                    break;
                }
                default:
                    throw new Exception("Count not implemented for table: $table");
            }
        } else {
            $university_id = $this->getCampusID();

            switch ($table) {
                case 'student': {
                    $sql = "
                    SELECT COUNT(*) AS total
                    FROM student s
                    LEFT JOIN university u          ON s.university_id = u.university_id
                    LEFT JOIN postcode p            ON s.postcode_id = p.zip_code
                    LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
                    LEFT JOIN student_status ss     ON s.status_id = ss.status_id
                    WHERE s.university_id = ?";
                    $types = 'i';
                    $params = [$university_id];
                    if ($search !== '') {
                        $sql .= " AND (s.fullname LIKE ? OR s.email LIKE ?)";
                        $types .= 'ss';
                        $params[] = $searchParam;
                        $params[] = $searchParam;
                    }
                    break;
                }
                case 'ppi_campus': {
                    $sql = "
                    SELECT COUNT(*) AS total
                    FROM ppi_campus pc
                    LEFT JOIN student s   ON pc.student_id = s.student_id
                    LEFT JOIN university u ON pc.university_id = u.university_id
                    WHERE pc.university_id = ?";
                    $types = 'i';
                    $params = [$university_id];
                    if ($search !== '') {
                        $sql .= " AND (s.fullname LIKE ? OR pc.department LIKE ? OR pc.position LIKE ?)";
                        $types .= 'sss';
                        $params[] = $searchParam;
                        $params[] = $searchParam;
                        $params[] = $searchParam;
                    }
                    break;
                }
                default:
                    throw new Exception("Limited access count not implemented for table: $table");
            }
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        if ($types !== '') $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res) throw new Exception("Query failed: " . $conn->error);

        $row = $res->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }


    /**
     * If user type == 1000 (PPI Campus), get campus ID
     */
    public function getCampusID(){
        if ($this->getUserType() == 1000){
            $userID = $this->getUserId();
            $stmt = $this->conn->prepare(
                "SELECT university_id FROM university_user WHERE user_id = ? LIMIT 1"
            );

            if ($stmt) {
                $stmt->bind_param("i", $userID);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if ($row) {
                    return (int)$row['university_id'];
                }
            }
        }
        return null;
    }

    /**
     * Get all students for export (no pagination)
     * Supports search and sorting like the main view
     */
    public function getAllStudentsForExport($search = '', $sort = null, $dir = 'asc') {
        $params = [];
        $types = '';
        $whereClause = '';
        $searchParam = "%$search%";

        // Base query with all necessary joins
        $baseQuery = "
            SELECT
                s.student_id,
                s.fullname,
                s.dob,
                s.email,
                s.passport,
                s.phone_number,
                s.postcode_id,
                p.city,
                s.address,
                ql.level_name as qualification_level,
                s.degree,
                s.expected_graduate,
                u.university_name,
                ss.status_name,
                s.is_active
            FROM student s
            LEFT JOIN university u          ON s.university_id = u.university_id
            LEFT JOIN postcode p            ON s.postcode_id = p.zip_code
            LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id
            LEFT JOIN student_status ss     ON s.status_id = ss.status_id";

        // Check access level
        if ($this->hasFullAccess()) {
            // Full access - can see all students
            if (!empty($search)) {
                $whereClause = " WHERE (s.fullname LIKE ? OR s.email LIKE ? OR u.university_name LIKE ? OR p.city LIKE ?)";
                $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                $types = "ssss";
            }
        } else {
            // Limited access - filter by university
            $university_id = $this->getCampusID();
            $whereClause = " WHERE s.university_id = ?";
            $params[] = $university_id;
            $types .= 'i';

            if (!empty($search)) {
                $whereClause .= " AND (s.fullname LIKE ? OR s.email LIKE ?)";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $types .= "ss";
            }
        }

        // Add sorting
        $allowedSorts = [
            'id' => 's.student_id',
            'fullname' => 's.fullname',
            'university' => 'u.university_name',
            'degree' => 's.degree',
        ];
        $orderDir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $orderClause = isset($allowedSorts[$sort])
            ? " ORDER BY {$allowedSorts[$sort]} $orderDir"
            : " ORDER BY s.fullname ASC";

        // Execute query
        $query = $baseQuery . $whereClause . $orderClause;
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

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
     * Get all PPI Campus members for export (no pagination)
     * Supports search like the main view
     */
    public function getAllPPICampusForExport($search = '') {
        $params = [];
        $types = '';
        $whereClause = '';
        $searchParam = "%$search%";

        // Base query with all necessary joins
        $baseQuery = "
            SELECT
                pc.ppi_campus_id,
                s.fullname,
                u.university_name,
                pc.start_year,
                pc.end_year,
                pc.department,
                pc.position,
                pc.description,
                pc.is_active
            FROM ppi_campus pc
            LEFT JOIN student s ON pc.student_id = s.student_id
            LEFT JOIN university u ON pc.university_id = u.university_id";

        // Check access level
        if ($this->hasFullAccess()) {
            // Full access - can see all PPI campus members
            if (!empty($search)) {
                $whereClause = " WHERE (s.fullname LIKE ? OR u.university_name LIKE ? OR pc.department LIKE ? OR pc.position LIKE ?)";
                $params = [$searchParam, $searchParam, $searchParam, $searchParam];
                $types = "ssss";
            }
        } else {
            // Limited access - filter by university
            $university_id = $this->getCampusID();
            $whereClause = " WHERE pc.university_id = ?";
            $params[] = $university_id;
            $types .= 'i';

            if (!empty($search)) {
                $whereClause .= " AND (s.fullname LIKE ? OR pc.department LIKE ?)";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $types .= "ss";
            }
        }

        // Add default sorting
        $orderClause = " ORDER BY pc.start_year DESC, s.fullname ASC";

        // Execute query
        $query = $baseQuery . $whereClause . $orderClause;
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

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
     * Get all PPIM members for export (no pagination)
     * Supports search and sorting like the main view
     */
    public function getAllPPIMForExport($search = '', $sort = null, $dir = 'asc') {
        $params = [];
        $types = '';
        $whereClause = '';
        $searchParam = "%$search%";

        // Base query with all necessary joins
        $baseQuery = "
            SELECT
                p.ppim_id,
                s.fullname,
                p.start_year,
                p.end_year,
                p.department,
                p.position,
                p.description,
                p.is_active
            FROM ppim p
            LEFT JOIN student s ON p.student_id = s.student_id";

        // Add search filter if provided
        if (!empty($search)) {
            $whereClause = " WHERE (s.fullname LIKE ? OR p.department LIKE ? OR p.position LIKE ?)";
            $params = [$searchParam, $searchParam, $searchParam];
            $types = "sss";
        }

        // Add sorting
        $allowedSorts = [
            'id' => 'p.ppim_id',
            'fullname' => 's.fullname',
            'start_year' => 'p.start_year',
            'end_year' => 'p.end_year',
            'department' => 'p.department',
            'position' => 'p.position',
        ];
        $orderDir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $orderClause = isset($allowedSorts[$sort])
            ? " ORDER BY {$allowedSorts[$sort]} $orderDir"
            : " ORDER BY p.start_year DESC, s.fullname ASC";

        // Execute query
        $query = $baseQuery . $whereClause . $orderClause;
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

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
            'has_access' => $this->hasFullAccess(),
            'is_ppi_campus' => $this->isPPICampus(),
        ];
    }

    /**
     * Insert a single student (for bulk upload API)
     * @param array $data Student data
     * @return array Result with success status and message
     */
    public function insertSingleStudent(array $data): array {
        try {
            // Get campus ID
            $campusId = $this->getCampusID();
            if (!$campusId) {
                return ['success' => false, 'error' => 'Unable to determine campus ID'];
            }

            // Validate required field
            $fullname = trim($data['fullname'] ?? '');
            if (empty($fullname)) {
                return ['success' => false, 'error' => 'Full name is required'];
            }

            // Process optional fields
            $dob = !empty($data['dob']) ? trim($data['dob']) : null;
            $email = !empty($data['email']) ? trim($data['email']) : null;
            $passport = !empty($data['passport']) ? strtoupper(str_replace(' ', '', trim($data['passport']))) : null;
            $phone = !empty($data['phone_number']) ? trim($data['phone_number']) : null;
            $postcode = !empty($data['postcode_id']) ? trim($data['postcode_id']) : null;
            $address = !empty($data['address']) ? trim($data['address']) : null;
            $expectedGrad = !empty($data['expected_graduate']) ? trim($data['expected_graduate']) : null;
            $degree = !empty($data['degree']) ? trim($data['degree']) : null;
            $levelOfQual = !empty($data['level_of_qualification_id']) ? (int)$data['level_of_qualification_id'] : null;
            $statusId = !empty($data['status_id']) ? (int)$data['status_id'] : 1;

            // Validate phone number - should be all numbers (with optional + at start)
            if ($phone !== null) {
                $cleanPhone = preg_replace('/(?!^\\+)[^0-9]/', '', $phone);
                if (!preg_match('/^\+?\d+$/', $cleanPhone)) {
                    return ['success' => false, 'error' => 'Phone number must contain only numbers'];
                }
                $phone = $cleanPhone;
            }

            // Validate date formats
            if ($dob !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                return ['success' => false, 'error' => 'Date of birth must be in YYYY-MM-DD format'];
            }

            if ($expectedGrad !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expectedGrad)) {
                return ['success' => false, 'error' => 'Expected graduate date must be in YYYY-MM-DD format'];
            }

            // Prepare insert statement
            $stmt = $this->conn->prepare(
                "INSERT INTO student (fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree, level_of_qualification_id, status_id, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
            );

            if (!$stmt) {
                return ['success' => false, 'error' => 'Database error: ' . $this->conn->error];
            }

            $stmt->bind_param(
                'sissssssssii',
                $fullname,
                $campusId,
                $dob,
                $email,
                $passport,
                $phone,
                $postcode,
                $address,
                $expectedGrad,
                $degree,
                $levelOfQual,
                $statusId
            );

            if ($stmt->execute()) {
                $studentId = $this->conn->insert_id;
                $stmt->close();
                return [
                    'success' => true,
                    'message' => 'Student added successfully',
                    'student_id' => $studentId
                ];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'error' => 'Failed to insert student: ' . $error];
            }

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Server error: ' . $e->getMessage()];
        }
    }

    /**
     * Get universities with optional search filter
     * @param string $search Search term (optional)
     * @param int $limit Maximum number of results (optional)
     * @return array Array of universities
     */
    public function getUniversities($search = '', $limit = 0) {
        $query = "SELECT u.university_id, u.university_name, u.address,
                         ut.type_name as university_type,
                         p.zip_code, p.city, p.state_name
                  FROM university u
                  LEFT JOIN university_type ut ON u.type_id = ut.type_id
                  LEFT JOIN postcode p ON u.postcode_id = p.zip_code";

        $params = [];
        $types = '';

        // Add search filter if provided
        if (!empty($search)) {
            $query .= " WHERE u.university_name LIKE ? OR p.city LIKE ? OR p.state_name LIKE ?";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
            $types = 'sss';
        }

        // Order by university name
        $query .= " ORDER BY u.university_name ASC";

        // Add limit if specified
        if ($limit > 0) {
            $query .= " LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
        }

        // Prepare and execute statement
        $stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all universities
        $universities = [];
        while ($row = $result->fetch_assoc()) {
            $universities[] = [
                'university_id' => $row['university_id'],
                'university_name' => $row['university_name'],
                'address' => $row['address'],
                'university_type' => $row['university_type'],
                'postcode' => [
                    'zip_code' => $row['zip_code'],
                    'city' => $row['city'],
                    'state_name' => $row['state_name']
                ]
            ];
        }

        $stmt->close();
        return $universities;
    }

    /**
     * Get postcodes with optional filters
     * @param string $search Search term (optional)
     * @param string $city Filter by city (optional)
     * @param string $state Filter by state (optional)
     * @param int $limit Maximum number of results (optional)
     * @return array Array of postcodes
     */
    public function getPostcodes($search = '', $city = '', $state = '', $limit = 0) {
        $query = "SELECT zip_code, city, state_name FROM postcode WHERE 1=1";

        $params = [];
        $types = '';

        // Add filters if provided
        if (!empty($search)) {
            $query .= " AND (zip_code LIKE ? OR city LIKE ? OR state_name LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            $types .= 'sss';
        }

        if (!empty($city)) {
            $query .= " AND city = ?";
            $params[] = $city;
            $types .= 's';
        }

        if (!empty($state)) {
            $query .= " AND state_name = ?";
            $params[] = $state;
            $types .= 's';
        }

        // Order by zip code
        $query .= " ORDER BY zip_code ASC";

        // Add limit if specified
        if ($limit > 0) {
            $query .= " LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
        }

        // Prepare and execute statement
        $stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all postcodes
        $postcodes = [];
        while ($row = $result->fetch_assoc()) {
            $postcodes[] = [
                'zip_code' => $row['zip_code'],
                'city' => $row['city'],
                'state_name' => $row['state_name']
            ];
        }

        $stmt->close();
        return $postcodes;
    }
}

// Initialize the student database class
$studentDB = new StudentDatabase();
?>