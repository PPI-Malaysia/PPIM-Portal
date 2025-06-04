<?php
// load main functions
require_once("assets/php/main.php");
$main = new ppim();

// Database configuration & connection
$host = 'localhost';
$port = 3306;
$dbname = 'ppimjtxz_ppimalaysia';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Define allowed tables to prevent SQL injection via table names
$allowedTables = [
    'university_type',
    'qualification_level',
    'postcode',
    'university',
    'student',
    'ppim',
    'ppi_campus'
];

// Function to validate table name
function validateTableName($table, $allowedTables)
{
    return in_array($table, $allowedTables, true);
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';

    // Validate table name to prevent SQL injection
    if (!validateTableName($table, $allowedTables)) {
        echo "<div class='alert alert-danger'>Error: Invalid table name</div>";
        exit;
    }

    try {
        switch ($action) {
            case 'create':
                handleCreate($pdo, $table, $_POST);
                break;
            case 'update':
                handleUpdate($pdo, $table, $_POST);
                break;
            case 'delete':
                handleDelete($pdo, $table, $_POST);
                break;
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

function handleCreate($pdo, $table, $data)
{
    switch ($table) {
        case 'university_type':
            $stmt = $pdo->prepare("INSERT INTO `university_type` (`type_name`, `description`) VALUES (?, ?)");
            $stmt->execute([
                $data['type_name'] ?? '',
                $data['description'] ?? ''
            ]);
            break;

        case 'qualification_level':
            $stmt = $pdo->prepare("INSERT INTO `qualification_level` (`level_name`, `level_order`, `description`) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['level_name'] ?? '',
                (int)($data['level_order'] ?? 0),
                $data['description'] ?? ''
            ]);
            break;

        case 'postcode':
            $stmt = $pdo->prepare("INSERT INTO `postcode` (`zip_code`, `city`, `state_name`) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['zip_code'] ?? '',
                $data['city'] ?? '',
                $data['state_name'] ?? ''
            ]);
            break;

        case 'university':
            $stmt = $pdo->prepare("INSERT INTO `university` (`university_name`, `address`, `type_id`, `postcode_id`, `is_active`) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['university_name'] ?? '',
                $data['address'] ?? '',
                !empty($data['type_id']) ? (int)$data['type_id'] : null,
                !empty($data['postcode_id']) ? $data['postcode_id'] : null,
                (int)($data['is_active'] ?? 1)
            ]);
            break;

        case 'student':
            $stmt = $pdo->prepare("INSERT INTO `student` (`fullname`, `university_id`, `dob`, `email`, `passport`, `phone_number`, `postcode_id`, `address`, `expected_graduate`, `degree`, `level_of_qualification_id`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['fullname'] ?? '',
                !empty($data['university_id']) ? (int)$data['university_id'] : null,
                !empty($data['dob']) ? $data['dob'] : null,
                $data['email'] ?? '',
                $data['passport'] ?? '',
                $data['phone_number'] ?? '',
                !empty($data['postcode_id']) ? $data['postcode_id'] : null,
                $data['address'] ?? '',
                !empty($data['expected_graduate']) ? $data['expected_graduate'] : null,
                $data['degree'] ?? '',
                !empty($data['level_of_qualification_id']) ? (int)$data['level_of_qualification_id'] : null,
                (int)($data['is_active'] ?? 1)
            ]);
            break;

        case 'ppim':
            $stmt = $pdo->prepare("INSERT INTO `ppim` (`student_id`, `start_year`, `department`, `description`, `is_active`) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                (int)($data['student_id'] ?? 0),
                (int)($data['start_year'] ?? 0),
                $data['department'] ?? '',
                $data['description'] ?? '',
                (int)($data['is_active'] ?? 1)
            ]);
            break;

        case 'ppi_campus':
            $stmt = $pdo->prepare("INSERT INTO `ppi_campus` (`student_id`, `start_year`, `university_id`, `department`, `description`, `is_active`) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                (int)($data['student_id'] ?? 0),
                (int)($data['start_year'] ?? 0),
                (int)($data['university_id'] ?? 0),
                $data['department'] ?? '',
                $data['description'] ?? '',
                (int)($data['is_active'] ?? 1)
            ]);
            break;
    }
    echo "<div class='alert alert-success'>Record created successfully!</div>";
}

function handleUpdate($pdo, $table, $data)
{
    switch ($table) {
        case 'university_type':
            $stmt = $pdo->prepare("UPDATE `university_type` SET `type_name` = ?, `description` = ? WHERE `type_id` = ?");
            $stmt->execute([
                $data['type_name'] ?? '',
                $data['description'] ?? '',
                (int)($data['type_id'] ?? 0)
            ]);
            break;

        case 'qualification_level':
            $stmt = $pdo->prepare("UPDATE `qualification_level` SET `level_name` = ?, `level_order` = ?, `description` = ? WHERE `level_id` = ?");
            $stmt->execute([
                $data['level_name'] ?? '',
                (int)($data['level_order'] ?? 0),
                $data['description'] ?? '',
                (int)($data['level_id'] ?? 0)
            ]);
            break;

        case 'postcode':
            $stmt = $pdo->prepare("UPDATE `postcode` SET `city` = ?, `state_name` = ? WHERE `zip_code` = ?");
            $stmt->execute([
                $data['city'] ?? '',
                $data['state_name'] ?? '',
                $data['zip_code'] ?? ''
            ]);
            break;

        case 'university':
            $stmt = $pdo->prepare("UPDATE `university` SET `university_name` = ?, `address` = ?, `type_id` = ?, `postcode_id` = ?, `is_active` = ? WHERE `university_id` = ?");
            $stmt->execute([
                $data['university_name'] ?? '',
                $data['address'] ?? '',
                !empty($data['type_id']) ? (int)$data['type_id'] : null,
                !empty($data['postcode_id']) ? $data['postcode_id'] : null,
                (int)($data['is_active'] ?? 1),
                (int)($data['university_id'] ?? 0)
            ]);
            break;

        case 'student':
            $stmt = $pdo->prepare("UPDATE `student` SET `fullname` = ?, `university_id` = ?, `dob` = ?, `email` = ?, `passport` = ?, `phone_number` = ?, `postcode_id` = ?, `address` = ?, `expected_graduate` = ?, `degree` = ?, `level_of_qualification_id` = ?, `is_active` = ? WHERE `student_id` = ?");
            $stmt->execute([
                $data['fullname'] ?? '',
                !empty($data['university_id']) ? (int)$data['university_id'] : null,
                !empty($data['dob']) ? $data['dob'] : null,
                $data['email'] ?? '',
                $data['passport'] ?? '',
                $data['phone_number'] ?? '',
                !empty($data['postcode_id']) ? $data['postcode_id'] : null,
                $data['address'] ?? '',
                !empty($data['expected_graduate']) ? $data['expected_graduate'] : null,
                $data['degree'] ?? '',
                !empty($data['level_of_qualification_id']) ? (int)$data['level_of_qualification_id'] : null,
                (int)($data['is_active'] ?? 1),
                (int)($data['student_id'] ?? 0)
            ]);
            break;

        case 'ppim':
            $stmt = $pdo->prepare("UPDATE `ppim` SET `department` = ?, `description` = ?, `is_active` = ? WHERE `student_id` = ? AND `start_year` = ?");
            $stmt->execute([
                $data['department'] ?? '',
                $data['description'] ?? '',
                (int)($data['is_active'] ?? 1),
                (int)($data['student_id'] ?? 0),
                (int)($data['start_year'] ?? 0)
            ]);
            break;

        case 'ppi_campus':
            $stmt = $pdo->prepare("UPDATE `ppi_campus` SET `department` = ?, `description` = ?, `is_active` = ? WHERE `student_id` = ? AND `start_year` = ? AND `university_id` = ?");
            $stmt->execute([
                $data['department'] ?? '',
                $data['description'] ?? '',
                (int)($data['is_active'] ?? 1),
                (int)($data['student_id'] ?? 0),
                (int)($data['start_year'] ?? 0),
                (int)($data['university_id'] ?? 0)
            ]);
            break;
    }
    echo "<div class='alert alert-success'>Record updated successfully!</div>";
}

function handleDelete($pdo, $table, $data)
{
    switch ($table) {
        case 'university_type':
            $stmt = $pdo->prepare("DELETE FROM `university_type` WHERE `type_id` = ?");
            $stmt->execute([(int)($data['id'] ?? 0)]);
            break;

        case 'qualification_level':
            $stmt = $pdo->prepare("DELETE FROM `qualification_level` WHERE `level_id` = ?");
            $stmt->execute([(int)($data['id'] ?? 0)]);
            break;

        case 'postcode':
            $stmt = $pdo->prepare("DELETE FROM `postcode` WHERE `zip_code` = ?");
            $stmt->execute([$data['id'] ?? '']);
            break;

        case 'university':
            $stmt = $pdo->prepare("DELETE FROM `university` WHERE `university_id` = ?");
            $stmt->execute([(int)($data['id'] ?? 0)]);
            break;

        case 'student':
            $stmt = $pdo->prepare("DELETE FROM `student` WHERE `student_id` = ?");
            $stmt->execute([(int)($data['id'] ?? 0)]);
            break;

        case 'ppim':
            $stmt = $pdo->prepare("DELETE FROM `ppim` WHERE `student_id` = ? AND `start_year` = ?");
            $stmt->execute([
                (int)($data['student_id'] ?? 0),
                (int)($data['start_year'] ?? 0)
            ]);
            break;

        case 'ppi_campus':
            $stmt = $pdo->prepare("DELETE FROM `ppi_campus` WHERE `student_id` = ? AND `start_year` = ? AND `university_id` = ?");
            $stmt->execute([
                (int)($data['student_id'] ?? 0),
                (int)($data['start_year'] ?? 0),
                (int)($data['university_id'] ?? 0)
            ]);
            break;
    }
    echo "<div class='alert alert-success'>Record deleted successfully!</div>";
}

function getTableData($pdo, $table, $allowedTables)
{
    // Validate table name
    if (!validateTableName($table, $allowedTables)) {
        throw new Exception("Invalid table name");
    }

    // Use backticks to prevent SQL injection with table names
    $stmt = $pdo->prepare("SELECT * FROM `$table`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDropdownOptions($pdo, $table, $valueField, $textField, $allowedTables)
{
    // Validate table name
    if (!validateTableName($table, $allowedTables)) {
        throw new Exception("Invalid table name");
    }

    // Define allowed field combinations for each table to prevent injection
    $allowedFields = [
        'university_type' => ['type_id', 'type_name'],
        'qualification_level' => ['level_id', 'level_name'],
        'postcode' => ['zip_code', 'city'],
        'university' => ['university_id', 'university_name'],
        'student' => ['student_id', 'fullname']
    ];

    if (
        !isset($allowedFields[$table]) ||
        !in_array($valueField, $allowedFields[$table]) ||
        !in_array($textField, $allowedFields[$table])
    ) {
        throw new Exception("Invalid field names");
    }

    $stmt = $pdo->prepare("SELECT `$valueField`, `$textField` FROM `$table`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>