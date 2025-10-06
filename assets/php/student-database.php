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
            
            if (!$this->hasFullAccess() && !$this->isPPICampus()) {
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
        if ($action === 'bulk_student_upload') {
            $this->handleBulkStudentUpload();
            return;
        }
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
                    $stmt = $this->conn->prepare("INSERT INTO university (university_name, address, email, phone_num, type_id, postcode_id, is_active) VALUES (?, ?, ?, ?, ?)");
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
                    $is_active = (int)($data['is_active']);
                    $ppi_campus_id = (int)($data['ppi_campus_id'] ?? 0);
                    
                    $stmt->bind_param("isssii", $end_year, $dept, $position, $desc, $is_active, $ppi_campus_id);
                    break;

                default:
                    throw new Exception("Update not implemented for table: " . $table);
            }
        } else {
            $university_id = $this->getCampusID();
            switch ($table) {
                case 'student':
                    $stmt = $this->conn->prepare("UPDATE student SET fullname = ?, university_id = ?, dob = ?, email = ?, passport = ?, phone_number = ?, postcode_id = ?, address = ?, expected_graduate = ?, degree = ?, level_of_qualification_id = ?, status_id = ?, is_active = ? WHERE student_id = ?");
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

                case 'ppi_campus':
                    $stmt = $this->conn->prepare("UPDATE ppi_campus SET end_year = ?, department = ?, position = ?, description = ?, is_active = ? WHERE ppi_campus_id = ?");
                    $end_year = empty($data['end_year']) ? null : (int)$data['end_year'];
                    $dept = empty($data['department']) ? null : $data['department'];
                    $position = empty($data['position']) ? null : $data['position'];
                    $desc = empty($data['description']) ? null : $data['description'];
                    $is_active = (int)($data['is_active']);
                    $ppi_campus_id = (int)($data['ppi_campus_id'] ?? 0);
                    
                    $stmt->bind_param("isssii", $end_year, $dept, $position, $desc, $is_active, $ppi_campus_id);
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
                    $stmt->bind_param("ii", $id, $unviersity_id);
                    break;

                case 'ppi_campus':
                    $stmt = $this->conn->prepare("DELETE FROM ppi_campus WHERE ppi_campus_id = ? AND university_id = ?");
                    $id = (int)($data['id'] ?? 0);
                    $stmt->bind_param("ii", $id, $unviersity_id);
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
            'postcode'             => ['zip_code','city','state','country'],
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

    private function handleBulkStudentUpload(): void
    {
        if ($this->hasFullAccess()) {
            $this->showAlert('Bulk upload is only available for PPI Campus accounts.', 'warning');
            return;
        }

        if (!$this->isPPICampus()) {
            $this->showAlert('You do not have permission to perform bulk uploads.', 'danger');
            return;
        }

        $campusId = $this->getCampusID();
        if (!$campusId) {
            $this->showAlert('Unable to determine your campus ID. Please contact the administrator.', 'danger');
            return;
        }

        if (!isset($_FILES['file'])) {
            $this->showAlert('No file detected for bulk upload.', 'warning');
            return;
        }

        $upload = $this->flattenUploadedFile($_FILES['file']);
        $errorCode = (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            $this->showAlert($this->translateUploadError($errorCode), 'danger');
            return;
        }

        if (empty($upload['tmp_name']) || !is_file($upload['tmp_name'])) {
            $this->showAlert('Upload failed: temporary file was not found.', 'danger');
            return;
        }

        $extension = strtolower(pathinfo($upload['name'] ?? '', PATHINFO_EXTENSION));
        if ($extension !== 'xlsx') {
            $this->showAlert('Invalid file format. Please upload an .xlsx workbook.', 'danger');
            return;
        }

        try {
            $sheet = $this->readXlsxSheet($upload['tmp_name']);
        } catch (Exception $e) {
            $this->showAlert('Unable to read workbook: ' . $e->getMessage(), 'danger');
            return;
        }

        if (!$sheet['header']) {
            $this->showAlert('The uploaded workbook does not contain any header row.', 'danger');
            return;
        }

        $columnMap = $this->buildBulkColumnMap($sheet['header']);
        if (!isset($columnMap['fullname'])) {
            $this->showAlert('The uploaded workbook is missing the "Full Name" column.', 'danger');
            return;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO student (fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            $this->showAlert('Database error: ' . $this->conn->error, 'danger');
            return;
        }

        $fullName = $dob = $email = $passport = $phone = $postcode = $address = $expectedGrad = $degree = null;
        $campusIdParam = (int)$campusId;
        $stmt->bind_param(
            'sissssssss',
            $fullName,
            $campusIdParam,
            $dob,
            $email,
            $passport,
            $phone,
            $postcode,
            $address,
            $expectedGrad,
            $degree
        );

        $imported = 0;
        $skipped = [];
        $failed = [];

        foreach ($sheet['rows'] as $rowNumber => $cells) {
            $fullName = $this->normaliseString($cells[$columnMap['fullname']] ?? null);
            if ($fullName === null) {
                $skipped[] = $rowNumber;
                continue;
            }

            $dob = isset($columnMap['dob'])
                ? $this->normaliseExcelDate($cells[$columnMap['dob']] ?? null)
                : null;

            $email = isset($columnMap['email'])
                ? $this->normaliseString($cells[$columnMap['email']] ?? null)
                : null;

            $passportRaw = isset($columnMap['passport'])
                ? $this->normaliseString($cells[$columnMap['passport']] ?? null)
                : null;
            $passport = $passportRaw ? strtoupper(str_replace(' ', '', $passportRaw)) : null;

            $phoneRaw = isset($columnMap['phone'])
                ? $this->normaliseString($cells[$columnMap['phone']] ?? null)
                : null;
            $phone = $phoneRaw ? preg_replace('/(?!^\\+)[^0-9]/', '', $phoneRaw) : null;

            $postcode = isset($columnMap['postcode'])
                ? $this->normaliseString($cells[$columnMap['postcode']] ?? null)
                : null;

            $address = isset($columnMap['address'])
                ? $this->normaliseString($cells[$columnMap['address']] ?? null)
                : null;

            $expectedGrad = isset($columnMap['expected'])
                ? $this->normaliseExcelDate($cells[$columnMap['expected']] ?? null)
                : null;

            $degree = isset($columnMap['degree'])
                ? $this->normaliseString($cells[$columnMap['degree']] ?? null)
                : null;

            if (!$stmt->execute()) {
                $failed[] = "Row {$rowNumber}: " . $stmt->error;
                continue;
            }

            $imported++;
        }

        $stmt->close();

        if ($imported === 0 && empty($failed) && empty($skipped)) {
            $this->showAlert('No rows were found in the uploaded workbook.', 'warning');
            return;
        }

        $summary = "Bulk upload finished. Imported {$imported} student(s).";
        if ($skipped) {
            $summary .= ' Skipped rows without full name: ' . implode(', ', array_slice($skipped, 0, 5));
            if (count($skipped) > 5) {
                $summary .= '…';
            }
            $summary .= '.';
        }
        if ($failed) {
            error_log('[Bulk Student Upload] ' . implode(' | ', $failed));
            $summary .= ' Failed rows were logged for review.';
            $this->showAlert($summary, 'warning');
            return;
        }

        $this->showAlert($summary, 'success');
    }

    private function flattenUploadedFile(array $file): array
    {
        if (!is_array($file['name'])) {
            return $file;
        }

        foreach ($file['name'] as $index => $name) {
            if ((int)$file['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            return [
                'name' => $name,
                'type' => $file['type'][$index] ?? '',
                'tmp_name' => $file['tmp_name'][$index],
                'error' => $file['error'][$index],
                'size' => $file['size'][$index] ?? 0,
            ];
        }

        return [
            'error' => $file['error'][0] ?? UPLOAD_ERR_NO_FILE,
        ];
    }

    private function readXlsxSheet(string $path): array
    {
        if (!class_exists('\\ZipArchive')) {
            throw new Exception('Zip extension is required to read .xlsx files.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception('Unable to open workbook.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sharedRoot = @simplexml_load_string($sharedXml);
            if ($sharedRoot !== false) {
                foreach ($sharedRoot->si as $index => $si) {
                    $value = '';
                    if (isset($si->t)) {
                        $value = (string)$si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $run) {
                            $value .= (string)$run->t;
                        }
                    }
                    $sharedStrings[(int)$index] = $value;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new Exception('Worksheet "sheet1" was not found.');
        }

        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false || !isset($sheet->sheetData)) {
            throw new Exception('The worksheet could not be parsed.');
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowIndex = isset($row['r']) ? (int)$row['r'] : null;
            if (!$rowIndex) {
                continue;
            }

            $rowData = [];
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                if (!preg_match('/([A-Z]+)(\\d+)/', $ref, $matches)) {
                    continue;
                }

                $column = $matches[1];
                $type = (string)$cell['t'];
                $value = '';

                if ($type === 's') {
                    $idx = (int)$cell->v;
                    $value = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $value = (string)$cell->is->t;
                } elseif (isset($cell->v)) {
                    $value = (string)$cell->v;
                }

                $rowData[$column] = $value;
            }

            if ($rowData) {
                $rows[$rowIndex] = $rowData;
            }
        }

        if (!$rows) {
            return ['header' => [], 'rows' => []];
        }

        ksort($rows, SORT_NUMERIC);
        $headerKey = array_key_first($rows);
        $header = $rows[$headerKey];
        unset($rows[$headerKey]);

        return ['header' => $header, 'rows' => $rows];
    }

    private function buildBulkColumnMap(array $header): array
    {
        $normalised = [];
        foreach ($header as $column => $label) {
            $key = $this->normaliseHeaderKey($label);
            if ($key !== '') {
                $normalised[$key] = $column;
            }
        }

        $aliases = [
            'fullname' => ['full name', 'fullname'],
            'dob' => ['date of birth', 'dob'],
            'email' => ['email', 'e-mail'],
            'passport' => ['passport'],
            'phone' => ['phone number', 'phone'],
            'postcode' => ['zip code', 'postcode', 'postal code'],
            'address' => ['address', 'residential address'],
            'expected' => ['expected graduate', 'expected graduation', 'expected graduate date'],
            'degree' => ['degree programme', 'degree program', 'degree', 'programme'],
        ];

        $map = [];
        foreach ($aliases as $key => $candidates) {
            foreach ($candidates as $candidate) {
                if (isset($normalised[$candidate])) {
                    $map[$key] = $normalised[$candidate];
                    break;
                }
            }
        }

        return $map;
    }

    private function normaliseHeaderKey(?string $label): string
    {
        $label = $this->normaliseString($label);
        if ($label === null) {
            return '';
        }

        return preg_replace('/\\s+/', ' ', strtolower($label));
    }

    private function normaliseString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            if (is_numeric($value)) {
                $value = (string)$value;
            } else {
                return null;
            }
        }

        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function normaliseExcelDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float)$value;
            if ($serial > 0) {
                $timestamp = ($serial - 25569) * 86400;
                if ($timestamp >= 0) {
                    return gmdate('Y-m-d', (int)round($timestamp));
                }
            }
        }

        $value = $this->normaliseString($value);
        if ($value === null) {
            return null;
        }

        if (preg_match('/^\\d{4}$/', $value)) {
            return $value . '-01-01';
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'd.m.Y'] as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            if ($dt instanceof \DateTime) {
                return $dt->format('Y-m-d');
            }
        }

        $dt = date_create($value);
        return $dt ? $dt->format('Y-m-d') : null;
    }

    private function translateUploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the maximum allowed size.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write the uploaded file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown file upload error.',
        };
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
}

// Initialize the student database class
$studentDB = new StudentDatabase();
?>