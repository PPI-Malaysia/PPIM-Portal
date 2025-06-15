<?php

/**
 * Student Database Management Class
 * Only accessible to user_type >= 5 and < 100
 * COMPLETE VERSION with all CRUD operations - CLEANED
 */

require_once("assets/php/main.php");

class DatabaseLogger
{
    private $user_id;
    private $user_name;
    private $log_file;
    private $log_dir;

    public function __construct($user_id, $user_name)
    {
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->log_dir = __DIR__ . '/../../logs/';
        $this->log_file = $this->log_dir . 'database.log';
        $this->createLogDirectory();
    }

    /**
     * Create log directory if it doesn't exist
     */
    private function createLogDirectory()
    {
        if (!is_dir($this->log_dir)) {
            mkdir($this->log_dir, 0755, true);
        }

        // Create .htaccess to protect log files
        $htaccess_file = $this->log_dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Order Deny,Allow\nDeny from all");
        }
    }

    /**
     * Log an operation
     */
    public function log($level, $table, $action, $message, $details = null)
    {
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $log_entry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'table' => $table,
            'action' => $action,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'message' => $message,
            'details' => $details,
            'ip_address' => $ip_address
        ];

        $log_line = json_encode($log_entry) . "\n";

        // Append to log file
        file_put_contents($this->log_file, $log_line, FILE_APPEND | LOCK_EX);

        // Rotate log file if it gets too large (>5MB)
        if (file_exists($this->log_file) && filesize($this->log_file) > 5 * 1024 * 1024) {
            $this->rotateLogFile();
        }
    }

    /**
     * Rotate log file
     */
    private function rotateLogFile()
    {
        $backup_file = $this->log_dir . 'database_' . date('Y-m-d_H-i-s') . '.log';
        rename($this->log_file, $backup_file);
        touch($this->log_file);
    }

    /**
     * Get logs with optional filters
     */
    private function getLogsFromLargeFile($limit, $filters = [])
    {
        // Simple implementation for large files
        $handle = fopen($this->log_file, 'r');
        if (!$handle) return [];

        $logs = [];
        $lines = [];

        // Read last 1000 lines efficiently
        fseek($handle, -min(filesize($this->log_file), 50000), SEEK_END);
        while (($line = fgets($handle)) !== false) {
            $lines[] = trim($line);
        }
        fclose($handle);

        // Process similar to original method
        $lines = array_reverse($lines);
        foreach ($lines as $line) {
            if (empty($line)) continue;
            $log_entry = json_decode($line, true);
            if (!$log_entry) continue;

            // Apply filters (same logic as original)
            if (!empty($filters['level']) && $log_entry['level'] !== $filters['level']) continue;
            if (!empty($filters['table']) && $log_entry['table'] !== $filters['table']) continue;
            if (!empty($filters['action']) && $log_entry['action'] !== $filters['action']) continue;
            if (!empty($filters['date']) && date('Y-m-d', strtotime($log_entry['timestamp'])) !== $filters['date']) continue;

            $logs[] = $log_entry;
            if (count($logs) >= $limit) break;
        }

        return $logs;
    }

    public function getLogs($limit = 50, $filters = [])
    {
        if (!file_exists($this->log_file)) {
            return [];
        }

        // Check file size before loading (prevent memory issues)
        $fileSize = filesize($this->log_file);
        if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            // For large files, read from the end
            return $this->getLogsFromLargeFile($limit, $filters);
        }

        $logs = [];
        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Reverse to get newest first
        $lines = array_reverse($lines);

        foreach ($lines as $line) {
            $log_entry = json_decode($line, true);
            if (!$log_entry) continue;

            // Apply filters
            if (!empty($filters['level']) && $log_entry['level'] !== $filters['level']) continue;
            if (!empty($filters['table']) && $log_entry['table'] !== $filters['table']) continue;
            if (!empty($filters['action']) && $log_entry['action'] !== $filters['action']) continue;
            if (!empty($filters['date']) && date('Y-m-d', strtotime($log_entry['timestamp'])) !== $filters['date']) continue;

            $logs[] = $log_entry;

            // Limit results
            if (count($logs) >= $limit) break;
        }

        return $logs;
    }

    /**
     * Get log statistics for today
     */
    public function getLogStats()
    {
        $today = date('Y-m-d');
        $stats = ['total' => 0, 'success' => 0, 'warning' => 0, 'error' => 0, 'info' => 0];

        if (!file_exists($this->log_file)) {
            return $stats;
        }

        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $log_entry = json_decode($line, true);
            if (!$log_entry) continue;

            // Only count today's logs
            if (date('Y-m-d', strtotime($log_entry['timestamp'])) === $today) {
                $stats['total']++;
                $level = $log_entry['level'] ?? 'info';
                if (isset($stats[$level])) {
                    $stats[$level]++;
                }
            }
        }

        return $stats;
    }

    /**
     * Clear old logs (older than specified days)
     */
    public function clearOldLogs($days = 30)
    {
        if (!file_exists($this->log_file)) {
            return 0;
        }

        $cutoff_date = date('Y-m-d', strtotime("-$days days"));
        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $kept_lines = [];
        $deleted_count = 0;

        foreach ($lines as $line) {
            $log_entry = json_decode($line, true);
            if (!$log_entry) continue;

            $log_date = date('Y-m-d', strtotime($log_entry['timestamp']));
            if ($log_date >= $cutoff_date) {
                $kept_lines[] = $line;
            } else {
                $deleted_count++;
            }
        }

        // Rewrite file with only recent logs
        file_put_contents($this->log_file, implode("\n", $kept_lines) . "\n");

        $this->log('info', 'system_logs', 'cleanup', "Cleared $deleted_count old log entries (older than $days days)");

        return $deleted_count;
    }

    /**
     * Get total log count
     */
    public function getTotalLogCount()
    {
        if (!file_exists($this->log_file)) {
            return 0;
        }

        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return count($lines);
    }
}

class StudentDatabase extends ppim
{
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

    private $logger;

    /**
     * Constructor - Initialize with access control
     */
    public function __construct()
    {
        try {
            parent::__construct();

            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }

            // Initialize logger
            $this->logger = new DatabaseLogger($this->getUserId(), $this->getUserName());

            if (!$this->hasAccess()) {
                // Log unauthorized access attempt
                $this->logger->log('warning', 'system', 'access_denied', 'Unauthorized access attempt to student database');
                header('Location: /access-denied.php');
                exit();
            }

            // Handle GET requests for download template
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] === 'download_template') {
                $this->generateCsvTemplate();
                exit(); // Important: Stop execution after download
            }

            // Handle POST requests
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->handleRequest();
            }
        } catch (Exception $e) {
            // Try to log constructor errors if logger was initialized
            if (isset($this->logger)) {
                $this->logger->log('error', 'system', 'constructor', 'Constructor Error: ' . $e->getMessage());
            }
            // Show error and exit gracefully
            die('Database initialization error: ' . htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Check if user has access to student database
     * @return boolean
     */
    private function hasAccess()
    {
        $userType = $this->getUserType();
        return ($userType >= 5 && $userType < 100);
    }

    /**
     * Handle incoming POST requests for CRUD operations
     */
    private function handleRequest()
    {
        $action = $_POST['action'] ?? '';
        $table = $_POST['table'] ?? '';

        // Handle clear logs action
        if ($action === 'clear_logs') {
            try {
                $deleted = $this->logger->clearOldLogs(30);
                $this->showAlert("Cleared $deleted old log entries", 'success');
            } catch (Exception $e) {
                $this->showAlert('Error clearing logs: ' . $e->getMessage(), 'danger');
            }
            return;
        }

        // Handle CSV upload
        if ($action === 'csv_upload') {
            $this->handleCsvUpload();
            return;
        }

        if (!$this->validateTableName($table)) {
            // Log invalid table attempts
            $this->logger->log('error', 'system', 'validation', 'Invalid table name attempted: ' . $table);
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
                    // Log invalid actions
                    $this->logger->log('error', 'system', 'validation', 'Invalid action attempted: ' . $action);
                    $this->showAlert('Error: Invalid action', 'danger');
            }
        } catch (Exception $e) {
            // Log general errors
            $this->logger->log('error', $table, $action, 'Exception in handleRequest: ' . $e->getMessage(), $_POST);
            $this->showAlert('Error: ' . htmlspecialchars($e->getMessage()), 'danger');
        }
    }

    /**
     * Validate table name against allowed tables
     */
    private function validateTableName($table)
    {
        return in_array($table, $this->allowedTables, true);
    }

    /**
     * Show alert message using Toastify.js
     */
    private function showAlert($message, $type = 'success')
    {
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
    private function showSuccessMessage($action, $table)
    {
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
    private function handleCreate($table, $data)
    {
        try {
            // Log the attempt
            $this->logger->log('info', $table, 'create', "Attempting to create new record in $table", $data);

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
                    $stmt = $this->conn->prepare("INSERT INTO university (university_name, address, type_id, postcode_id, is_active) VALUES (?, ?, ?, ?, ?)");
                    $type_id = empty($data['type_id']) ? null : (int)$data['type_id'];
                    $postcode_id = empty($data['postcode_id']) ? null : (int)$data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $stmt->bind_param("ssiii", $data['university_name'], $address, $type_id, $postcode_id, $is_active);
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

                    $stmt->bind_param(
                        "sissssisssiii",
                        $data['fullname'],
                        $university_id,
                        $dob,
                        $email,
                        $passport,
                        $phone,
                        $postcode_id,
                        $address,
                        $grad,
                        $degree,
                        $qual_id,
                        $status_id,
                        $is_active
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
                $record_id = $this->conn->insert_id;

                // Log success
                $this->logger->log(
                    'success',
                    $table,
                    'create',
                    "Successfully created record in $table" . ($record_id ? " (ID: $record_id)" : ""),
                    ['record_id' => $record_id, 'data' => $data]
                );

                $this->showSuccessMessage('create', $table);
            } else {
                throw new Exception("Failed to create record: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            // Log error
            $this->logger->log(
                'error',
                $table,
                'create',
                "Failed to create record in $table: " . $e->getMessage(),
                ['data' => $data, 'error' => $e->getMessage()]
            );
            throw $e;
        }
    }

    /**
     * Handle UPDATE operations - COMPLETE VERSION
     */
    private function handleUpdate($table, $data)
    {
        try {
            // Log the attempt
            $this->logger->log('info', $table, 'update', "Attempting to update record in $table", $data);

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
                    $stmt = $this->conn->prepare("UPDATE university SET university_name = ?, address = ?, type_id = ?, postcode_id = ?, is_active = ? WHERE university_id = ?");
                    $type_id = empty($data['type_id']) ? null : (int)$data['type_id'];
                    $postcode_id = empty($data['postcode_id']) ? null : (int)$data['postcode_id'];
                    $address = empty($data['address']) ? null : $data['address'];
                    $is_active = (int)($data['is_active'] ?? 1);
                    $university_id = (int)($data['university_id'] ?? 0);

                    $stmt->bind_param("ssiiii", $data['university_name'], $address, $type_id, $postcode_id, $is_active, $university_id);
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

                    $stmt->bind_param(
                        "sissssisssiiii",
                        $data['fullname'],
                        $university_id,
                        $dob,
                        $email,
                        $passport,
                        $phone,
                        $postcode_id,
                        $address,
                        $grad,
                        $degree,
                        $qual_id,
                        $status_id,
                        $is_active,
                        $student_id
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
        } catch (Exception $e) {
            // Log error
            $this->logger->log('error', $table, 'update', "Failed to update record in $table: " . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * Handle DELETE operations
     */
    private function handleDelete($table, $data)
    {
        try {
            // Log the attempt
            $this->logger->log('info', $table, 'delete', "Attempting to delete record in $table", $data);

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
                    // Log success
                    $this->logger->log('success', $table, 'delete', "Successfully deleted record from $table", $data);
                    $this->showSuccessMessage('delete', $table);
                } else {
                    $this->showAlert('Record not found or already deleted', 'warning');
                }
            } else {
                throw new Exception("Failed to delete record: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            // Log error
            $this->logger->log('error', $table, 'delete', "Failed to delete record from $table: " . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * Get all data from a table
     */
    public function getTableData($table)
    {
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
    public function getDropdownOptions($table, $valueField, $textField, $additionalField = null)
    {
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
    public function getTableDataWithJoins($table)
    {
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
     * Handle CSV upload for students
     */
    private function handleCsvUpload()
    {
        try {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }

            $file = $_FILES['csv_file'];

            // Validate file type
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExtension, ['csv', 'txt'])) {
                throw new Exception('Please upload a CSV file');
            }

            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('File size too large. Maximum 5MB allowed');
            }

            // Log the upload attempt
            $this->logger->log('info', 'student', 'csv_upload', 'Starting CSV upload: ' . $file['name'], [
                'filename' => $file['name'],
                'filesize' => $file['size']
            ]);

            // Process the CSV file
            $result = $this->processCsvFile($file['tmp_name']);

            // Log success
            $this->logger->log(
                'success',
                'student',
                'csv_upload',
                "CSV upload completed successfully. Processed: {$result['total']}, Success: {$result['success']}, Errors: {$result['errors']}",
                $result
            );

            // Show success message
            $message = "CSV upload completed! Processed {$result['total']} rows: {$result['success']} successful, {$result['errors']} errors.";
            if ($result['errors'] > 0) {
                $message .= " Check the details below for error information.";
            }

            $this->showAlert($message, $result['errors'] > 0 ? 'warning' : 'success');

            // If there are errors, show them in a simple format
            if ($result['errors'] > 0 && !empty($result['error_details'])) {
                $this->showCsvErrors($result['error_details']);
            }
        } catch (Exception $e) {
            $this->logger->log('error', 'student', 'csv_upload', 'CSV upload failed: ' . $e->getMessage(), [
                'filename' => $_FILES['csv_file']['name'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            $this->showAlert('CSV upload failed: ' . $e->getMessage(), 'danger');
        }
    }

    /**
     * Show CSV errors in a simple format
     */
    private function showCsvErrors($errorDetails)
    {
        $errorCount = min(count($errorDetails), 10); // Show max 10 errors
        $errorList = "";

        for ($i = 0; $i < $errorCount; $i++) {
            $error = $errorDetails[$i];
            $errorList .= "Row {$error['row']}: {$error['error']}\\n";
        }

        if (count($errorDetails) > 10) {
            $errorList .= "... and " . (count($errorDetails) - 10) . " more errors.";
        }

        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-warning alert-dismissible fade show mt-3';
                    errorAlert.innerHTML = `
                        <h6><i class='ti ti-alert-triangle me-1'></i>Import Errors Found:</h6>
                        <pre style='white-space: pre-wrap; font-size: 0.875em;'>" . htmlspecialchars($errorList, ENT_QUOTES) . "</pre>
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    `;
                    
                    const csvSection = document.getElementById('csvUpload');
                    if (csvSection && csvSection.classList.contains('show')) {
                        csvSection.querySelector('.card-body').appendChild(errorAlert);
                    }
                });
              </script>";
    }

    /**
     * Process CSV file and import students
     */
    private function processCsvFile($filePath)
    {
        $results = [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'error_details' => []
        ];

        if (($handle = fopen($filePath, "r")) === FALSE) {
            throw new Exception('Could not open CSV file');
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new Exception('CSV file appears to be empty');
        }

        // Normalize headers (remove BOM, trim, lowercase)
        $header = array_map(function ($h) {
            return strtolower(trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF"));
        }, $header);

        // Expected columns mapping
        $expectedColumns = [
            'fullname' => ['fullname', 'full_name', 'name', 'student_name'],
            'university_id' => ['university_id', 'university', 'uni_id'],
            'dob' => ['dob', 'date_of_birth', 'birth_date'],
            'email' => ['email', 'email_address'],
            'passport' => ['passport', 'passport_number'],
            'phone_number' => ['phone_number', 'phone', 'mobile'],
            'postcode_id' => ['postcode_id', 'postcode', 'zip_code'],
            'address' => ['address', 'home_address'],
            'expected_graduate' => ['expected_graduate', 'graduation_date', 'grad_date'],
            'degree' => ['degree', 'course', 'program'],
            'level_of_qualification_id' => ['level_of_qualification_id', 'qualification_level', 'level'],
            'status_id' => ['status_id', 'status']
        ];

        // Map CSV columns to database columns
        $columnMap = [];
        foreach ($expectedColumns as $dbColumn => $possibleNames) {
            foreach ($possibleNames as $possible) {
                if (in_array($possible, $header)) {
                    $columnMap[$dbColumn] = array_search($possible, $header);
                    break;
                }
            }
        }

        // Check if required columns exist
        if (!isset($columnMap['fullname'])) {
            fclose($handle);
            throw new Exception('Required column "fullname" not found in CSV. Please check your CSV headers.');
        }

        $rowNumber = 1; // Start from 1 (header row)

        while (($row = fgetcsv($handle)) !== FALSE) {
            $rowNumber++;
            $results['total']++;

            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Prepare data for insertion
                $studentData = $this->mapCsvRowToStudent($row, $columnMap);

                // Validate required fields
                if (empty($studentData['fullname'])) {
                    throw new Exception("Full name is required");
                }

                // Insert student
                $this->insertStudentFromCsv($studentData);
                $results['success']++;
            } catch (Exception $e) {
                $results['errors']++;
                $results['error_details'][] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage()
                ];
            }
        }

        fclose($handle);
        return $results;
    }

    /**
     * Map CSV row to student data array
     */
    private function mapCsvRowToStudent($row, $columnMap)
    {
        $studentData = [];

        foreach ($columnMap as $dbColumn => $csvIndex) {
            $value = isset($row[$csvIndex]) ? trim($row[$csvIndex]) : null;

            // Convert empty strings to null
            if ($value === '') {
                $value = null;
            }

            // Special handling for specific columns
            switch ($dbColumn) {
                case 'university_id':
                case 'postcode_id':
                case 'level_of_qualification_id':
                case 'status_id':
                    $studentData[$dbColumn] = $value ? (int)$value : null;
                    break;

                case 'dob':
                case 'expected_graduate':
                    if ($value) {
                        // Try to parse date in various formats
                        $date = $this->parseDate($value);
                        $studentData[$dbColumn] = $date;
                    } else {
                        $studentData[$dbColumn] = null;
                    }
                    break;

                case 'email':
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Invalid email format: $value");
                    }
                    $studentData[$dbColumn] = $value;
                    break;

                default:
                    $studentData[$dbColumn] = $value;
            }
        }

        // Set defaults for required fields
        if (!isset($studentData['status_id']) || $studentData['status_id'] === null) {
            $studentData['status_id'] = 1; // Default to active status
        }
        if (!isset($studentData['is_active'])) {
            $studentData['is_active'] = 1; // Default to active
        }

        return $studentData;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateString)
    {
        // Common date formats to try
        $formats = [
            'Y-m-d',        // 2024-01-15 (ISO format)
            'd/m/Y',        // 15/01/2024
            'm/d/Y',        // 01/15/2024
            'd-m-Y',        // 15-01-2024
            'm-d-Y',        // 01-15-2024
            'Y/m/d',        // 2024/01/15
            'd.m.Y',        // 15.01.2024
            'Y.m.d'         // 2024.01.15
        ];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Try strtotime as fallback
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        throw new Exception("Invalid date format: $dateString (expected YYYY-MM-DD)");
    }

    /**
     * Insert student from CSV data
     */
    private function insertStudentFromCsv($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO student (
                fullname, university_id, dob, email, passport, phone_number, 
                postcode_id, address, expected_graduate, degree, 
                level_of_qualification_id, status_id, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        // Ensure all values are properly typed
        $fullname = $data['fullname'] ?? '';
        $university_id = $data['university_id'] ?? null;
        $dob = $data['dob'] ?? null;
        $email = $data['email'] ?? null;
        $passport = $data['passport'] ?? null;
        $phone_number = $data['phone_number'] ?? null;
        $postcode_id = $data['postcode_id'] ?? null;
        $address = $data['address'] ?? null;
        $expected_graduate = $data['expected_graduate'] ?? null;
        $degree = $data['degree'] ?? null;
        $level_of_qualification_id = $data['level_of_qualification_id'] ?? null;
        $status_id = $data['status_id'] ?? 1;
        $is_active = $data['is_active'] ?? 1;

        $stmt->bind_param(
            "sissssisssiii",
            $fullname,                      // string
            $university_id,                 // int or null
            $dob,                          // string or null (date)
            $email,                        // string or null
            $passport,                     // string or null
            $phone_number,                 // string or null
            $postcode_id,                  // int or null
            $address,                      // string or null
            $expected_graduate,            // string or null (date)
            $degree,                       // string or null
            $level_of_qualification_id,    // int or null
            $status_id,                    // int
            $is_active                     // int
        );

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $studentId = $this->conn->insert_id;
        $stmt->close();

        return $studentId;
    }

    /**
     * Generate CSV template for download
     */
    public function generateCsvTemplate()
    {
        try {
            // Log the download attempt
            $this->logger->log('info', 'student', 'download_template', 'CSV template downloaded');

            $headers = [
                'fullname',
                'university_id',
                'dob',
                'email',
                'passport',
                'phone_number',
                'postcode_id',
                'address',
                'expected_graduate',
                'degree',
                'level_of_qualification_id',
                'status_id'
            ];

            // Clear any previous output
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set proper headers for download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="student_import_template.csv"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8 (helps with Excel)
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header row
            fputcsv($output, $headers);

            // Add sample row with example data
            fputcsv($output, [
                'Ahmad Bin Ali',           // fullname
                '1',                       // university_id (example: 1)
                '1995-01-15',             // dob (YYYY-MM-DD format)
                'ahmad.ali@email.com',     // email
                'A1234567',               // passport
                '+60123456789',           // phone_number
                '50000',                  // postcode_id (example: 50000)
                'No 123, Jalan Example, Kuala Lumpur', // address
                '2025-12-31',             // expected_graduate
                'Computer Science',        // degree
                '1',                      // level_of_qualification_id (example: 1)
                '1'                       // status_id (example: 1 for Active)
            ]);

            // Add instruction row (as comment)
            fputcsv($output, [
                '// Instructions: Replace this row with actual data',
                '// university_id: Get from Universities table',
                '// Date format: YYYY-MM-DD',
                '// Use valid email format',
                '// postcode_id: Must exist in postcode table',
                '// Phone: Include country code',
                '// postcode_id: 5-digit postal code',
                '// Address: Full address',
                '// Date format: YYYY-MM-DD',
                '// Degree/Course name',
                '// qualification_level_id: From qualification table',
                '// status_id: 1=Active, 2=Inactive, etc.'
            ]);

            fclose($output);
        } catch (Exception $e) {
            // Log error
            $this->logger->log('error', 'student', 'download_template', 'Failed to generate CSV template: ' . $e->getMessage());

            // Clear output and show error
            if (ob_get_level()) {
                ob_end_clean();
            }

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/plain');
            echo 'Error generating CSV template: ' . $e->getMessage();
        }

        exit();
    }

    /**
     * Get user permissions info for frontend
     */
    public function getUserInfo()
    {
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

    /**
     * Get logs for display
     */
    public function getSystemLogs($limit = 50, $filters = [])
    {
        return $this->logger->getLogs($limit, $filters);
    }

    /**
     * Get log statistics
     */
    public function getLogStatistics()
    {
        return $this->logger->getLogStats();
    }

    /**
     * Clear old logs
     */
    public function clearOldLogs($days = 30)
    {
        return $this->logger->clearOldLogs($days);
    }
}

// Initialize the student database class
$studentDB = new StudentDatabase();
