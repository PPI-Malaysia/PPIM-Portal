<?php
// load main functions
require_once("assets/php/main.php");
$main = new ppim();

// Credit: fill your name as the person who created this page here
$credit = "Christopher Bertrand";
$credit_footer = '
<a href="https://github.com/Zentoboo" target="_blank">Christopher Bertrand</a>
';

// Database connection
// Database configuration
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

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';

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
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

function handleCreate($pdo, $table, $data)
{
    switch ($table) {
        case 'university_type':
            $stmt = $pdo->prepare("INSERT INTO university_type (type_name, description) VALUES (?, ?)");
            $stmt->execute([$data['type_name'], $data['description']]);
            break;
        case 'qualification_level':
            $stmt = $pdo->prepare("INSERT INTO qualification_level (level_name, level_order, description) VALUES (?, ?, ?)");
            $stmt->execute([$data['level_name'], $data['level_order'], $data['description']]);
            break;
        case 'postcode':
            $stmt = $pdo->prepare("INSERT INTO postcode (zip_code, city, state_name) VALUES (?, ?, ?)");
            $stmt->execute([$data['zip_code'], $data['city'], $data['state_name']]);
            break;
        case 'university':
            $stmt = $pdo->prepare("INSERT INTO university (university_name, address, type_id, postcode_id, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['university_name'], $data['address'], $data['type_id'], $data['postcode_id'], $data['is_active'] ?? 1]);
            break;
        case 'student':
            $stmt = $pdo->prepare("INSERT INTO student (fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree, level_of_qualification_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['fullname'], $data['university_id'], $data['dob'], $data['email'], $data['passport'], $data['phone_number'], $data['postcode_id'], $data['address'], $data['expected_graduate'], $data['degree'], $data['level_of_qualification_id'], $data['is_active'] ?? 1]);
            break;
        case 'ppim':
            $stmt = $pdo->prepare("INSERT INTO ppim (student_id, start_year, department, description, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['student_id'], $data['start_year'], $data['department'], $data['description'], $data['is_active'] ?? 1]);
            break;
        case 'ppi_campus':
            $stmt = $pdo->prepare("INSERT INTO ppi_campus (student_id, start_year, university_id, department, description, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['student_id'], $data['start_year'], $data['university_id'], $data['department'], $data['description'], $data['is_active'] ?? 1]);
            break;
    }
    echo "<div class='alert alert-success'>Record created successfully!</div>";
}

function handleUpdate($pdo, $table, $data)
{
    switch ($table) {
        case 'university_type':
            $stmt = $pdo->prepare("UPDATE university_type SET type_name = ?, description = ? WHERE type_id = ?");
            $stmt->execute([$data['type_name'], $data['description'], $data['type_id']]);
            break;
        case 'qualification_level':
            $stmt = $pdo->prepare("UPDATE qualification_level SET level_name = ?, level_order = ?, description = ? WHERE level_id = ?");
            $stmt->execute([$data['level_name'], $data['level_order'], $data['description'], $data['level_id']]);
            break;
        case 'postcode':
            $stmt = $pdo->prepare("UPDATE postcode SET city = ?, state_name = ? WHERE zip_code = ?");
            $stmt->execute([$data['city'], $data['state_name'], $data['zip_code']]);
            break;
        case 'university':
            $stmt = $pdo->prepare("UPDATE university SET university_name = ?, address = ?, type_id = ?, postcode_id = ?, is_active = ? WHERE university_id = ?");
            $stmt->execute([$data['university_name'], $data['address'], $data['type_id'], $data['postcode_id'], $data['is_active'], $data['university_id']]);
            break;
        case 'student':
            $stmt = $pdo->prepare("UPDATE student SET fullname = ?, university_id = ?, dob = ?, email = ?, passport = ?, phone_number = ?, postcode_id = ?, address = ?, expected_graduate = ?, degree = ?, level_of_qualification_id = ?, is_active = ? WHERE student_id = ?");
            $stmt->execute([$data['fullname'], $data['university_id'], $data['dob'], $data['email'], $data['passport'], $data['phone_number'], $data['postcode_id'], $data['address'], $data['expected_graduate'], $data['degree'], $data['level_of_qualification_id'], $data['is_active'], $data['student_id']]);
            break;
        case 'ppim':
            $stmt = $pdo->prepare("UPDATE ppim SET department = ?, description = ?, is_active = ? WHERE student_id = ? AND start_year = ?");
            $stmt->execute([$data['department'], $data['description'], $data['is_active'], $data['student_id'], $data['start_year']]);
            break;
        case 'ppi_campus':
            $stmt = $pdo->prepare("UPDATE ppi_campus SET department = ?, description = ?, is_active = ? WHERE student_id = ? AND start_year = ? AND university_id = ?");
            $stmt->execute([$data['department'], $data['description'], $data['is_active'], $data['student_id'], $data['start_year'], $data['university_id']]);
            break;
    }
    echo "<div class='alert alert-success'>Record updated successfully!</div>";
}

function handleDelete($pdo, $table, $data)
{
    switch ($table) {
        case 'university_type':
            $stmt = $pdo->prepare("DELETE FROM university_type WHERE type_id = ?");
            $stmt->execute([$data['id']]);
            break;
        case 'qualification_level':
            $stmt = $pdo->prepare("DELETE FROM qualification_level WHERE level_id = ?");
            $stmt->execute([$data['id']]);
            break;
        case 'postcode':
            $stmt = $pdo->prepare("DELETE FROM postcode WHERE zip_code = ?");
            $stmt->execute([$data['id']]);
            break;
        case 'university':
            $stmt = $pdo->prepare("DELETE FROM university WHERE university_id = ?");
            $stmt->execute([$data['id']]);
            break;
        case 'student':
            $stmt = $pdo->prepare("DELETE FROM student WHERE student_id = ?");
            $stmt->execute([$data['id']]);
            break;
        case 'ppim':
            $stmt = $pdo->prepare("DELETE FROM ppim WHERE student_id = ? AND start_year = ?");
            $stmt->execute([$data['student_id'], $data['start_year']]);
            break;
        case 'ppi_campus':
            $stmt = $pdo->prepare("DELETE FROM ppi_campus WHERE student_id = ? AND start_year = ? AND university_id = ?");
            $stmt->execute([$data['student_id'], $data['start_year'], $data['university_id']]);
            break;
    }
    echo "<div class='alert alert-success'>Record deleted successfully!</div>";
}

function getTableData($pdo, $table)
{
    $stmt = $pdo->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDropdownOptions($pdo, $table, $valueField, $textField)
{
    $stmt = $pdo->query("SELECT $valueField, $textField FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Student Database | PPI Malaysia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $credit; ?>" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Database css -->
    <link href="assets/css/student-database.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">


        <?php $main->renderNavbar(); ?>

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="page-content">
            <div class="page-container">

                <h3 style="padding: 20px;">
                    <!-- Navigation for tables -->
                    <div class="table-navigation">
                        <ul>
                            <li><a href="#university_type">University Types</a></li>
                            <li><a href="#qualification_level">Qualification Levels</a></li>
                            <li><a href="#postcode">Postcodes</a></li>
                            <li><a href="#university">Universities</a></li>
                            <li><a href="#student">Students</a></li>
                            <li><a href="#ppim">PPIM</a></li>
                            <li><a href="#ppi_campus">PPI Campus</a></li>
                        </ul>
                    </div>

                    <!-- University Types CRUD -->
                    <div id="university_type" class="table-section">
                        <h3>University Types</h3>

                        <!-- Create Form -->
                        <h4>Add New University Type</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="table" value="university_type">
                            <input type="text" name="type_name" placeholder="Type Name" required>
                            <textarea name="description" placeholder="Description"></textarea>
                            <button type="submit">Add</button>
                        </form>

                        <!-- Read -->
                        <h4>Existing University Types</h4>
                        <table border="1">
                            <tr>
                                <th>ID</th>
                                <th>Type Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            $data = getTableData($pdo, 'university_type');
                            foreach ($data as $row): ?>
                                <tr>
                                    <td><?= $row['type_id'] ?></td>
                                    <td><?= $row['type_name'] ?></td>
                                    <td><?= $row['description'] ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="table" value="university_type">
                                            <input type="hidden" name="type_id" value="<?= $row['type_id'] ?>">
                                            <input type="text" name="type_name" value="<?= $row['type_name'] ?>" required>
                                            <textarea name="description"><?= $row['description'] ?></textarea>
                                            <button type="submit">Update</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="table" value="university_type">
                                            <input type="hidden" name="id" value="<?= $row['type_id'] ?>">
                                            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <!-- Qualification Levels CRUD -->
                    <div id="qualification_level" class="table-section">
                        <h3>Qualification Levels</h3>

                        <!-- Create Form -->
                        <h4>Add New Qualification Level</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="table" value="qualification_level">
                            <input type="text" name="level_name" placeholder="Level Name" required>
                            <input type="number" name="level_order" placeholder="Level Order" required>
                            <textarea name="description" placeholder="Description"></textarea>
                            <button type="submit">Add</button>
                        </form>

                        <!-- Read -->
                        <h4>Existing Qualification Levels</h4>
                        <table border="1">
                            <tr>
                                <th>ID</th>
                                <th>Level Name</th>
                                <th>Order</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            $data = getTableData($pdo, 'qualification_level');
                            foreach ($data as $row): ?>
                                <tr>
                                    <td><?= $row['level_id'] ?></td>
                                    <td><?= $row['level_name'] ?></td>
                                    <td><?= $row['level_order'] ?></td>
                                    <td><?= $row['description'] ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="table" value="qualification_level">
                                            <input type="hidden" name="level_id" value="<?= $row['level_id'] ?>">
                                            <input type="text" name="level_name" value="<?= $row['level_name'] ?>" required>
                                            <input type="number" name="level_order" value="<?= $row['level_order'] ?>" required>
                                            <textarea name="description"><?= $row['description'] ?></textarea>
                                            <button type="submit">Update</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="table" value="qualification_level">
                                            <input type="hidden" name="id" value="<?= $row['level_id'] ?>">
                                            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <!-- Postcodes CRUD -->
                    <div id="postcode" class="table-section">
                        <h3>Postcodes</h3>

                        <!-- Create Form -->
                        <h4>Add New Postcode</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="table" value="postcode">
                            <input type="number" name="zip_code" placeholder="Zip Code" required>
                            <input type="text" name="city" placeholder="City" required>
                            <input type="text" name="state_name" placeholder="State" required>
                            <button type="submit">Add</button>
                        </form>

                        <!-- Read -->
                        <h4>Existing Postcodes</h4>
                        <table border="1">
                            <tr>
                                <th>Zip Code</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            $data = getTableData($pdo, 'postcode');
                            foreach ($data as $row): ?>
                                <tr>
                                    <td><?= $row['zip_code'] ?></td>
                                    <td><?= $row['city'] ?></td>
                                    <td><?= $row['state_name'] ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="table" value="postcode">
                                            <input type="hidden" name="zip_code" value="<?= $row['zip_code'] ?>">
                                            <input type="text" name="city" value="<?= $row['city'] ?>" required>
                                            <input type="text" name="state_name" value="<?= $row['state_name'] ?>" required>
                                            <button type="submit">Update</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="table" value="postcode">
                                            <input type="hidden" name="id" value="<?= $row['zip_code'] ?>">
                                            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <!-- Universities CRUD -->
                    <div id="university" class="table-section">
                        <h3>Universities</h3>

                        <!-- Create Form -->
                        <h4>Add New University</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="table" value="university">
                            <input type="text" name="university_name" placeholder="University Name" required>
                            <textarea name="address" placeholder="Address"></textarea>
                            <select name="type_id" required>
                                <option value="">Select Type</option>
                                <?php
                                $types = getDropdownOptions($pdo, 'university_type', 'type_id', 'type_name');
                                foreach ($types as $type): ?>
                                    <option value="<?= $type['type_id'] ?>"><?= $type['type_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="postcode_id">
                                <option value="">Select Postcode</option>
                                <?php
                                $postcodes = getDropdownOptions($pdo, 'postcode', 'zip_code', 'city');
                                foreach ($postcodes as $postcode): ?>
                                    <option value="<?= $postcode['zip_code'] ?>"><?= $postcode['zip_code'] ?> - <?= $postcode['city'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <button type="submit">Add</button>
                        </form>

                        <!-- Read -->
                        <h4>Existing Universities</h4>
                        <table border="1">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Type</th>
                                <th>Postcode</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            $stmt = $pdo->query("SELECT u.*, ut.type_name, p.zip_code, p.city FROM university u 
                                                LEFT JOIN university_type ut ON u.type_id = ut.type_id 
                                                LEFT JOIN postcode p ON u.postcode_id = p.zip_code");
                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($data as $row): ?>
                                <tr>
                                    <td><?= $row['university_id'] ?></td>
                                    <td><?= $row['university_name'] ?></td>
                                    <td><?= $row['address'] ?></td>
                                    <td><?= $row['type_name'] ?></td>
                                    <td><?= $row['zip_code'] ?> - <?= $row['city'] ?></td>
                                    <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="table" value="university">
                                            <input type="hidden" name="university_id" value="<?= $row['university_id'] ?>">
                                            <input type="text" name="university_name" value="<?= $row['university_name'] ?>" required>
                                            <textarea name="address"><?= $row['address'] ?></textarea>
                                            <select name="type_id" required>
                                                <?php
                                                $types = getDropdownOptions($pdo, 'university_type', 'type_id', 'type_name');
                                                foreach ($types as $type): ?>
                                                    <option value="<?= $type['type_id'] ?>" <?= $type['type_id'] == $row['type_id'] ? 'selected' : '' ?>><?= $type['type_name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="postcode_id">
                                                <option value="">Select Postcode</option>
                                                <?php
                                                $postcodes = getDropdownOptions($pdo, 'postcode', 'zip_code', 'city');
                                                foreach ($postcodes as $postcode): ?>
                                                    <option value="<?= $postcode['zip_code'] ?>" <?= $postcode['zip_code'] == $row['postcode_id'] ? 'selected' : '' ?>><?= $postcode['zip_code'] ?> - <?= $postcode['city'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="is_active">
                                                <option value="1" <?= $row['is_active'] ? 'selected' : '' ?>>Active</option>
                                                <option value="0" <?= !$row['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                            <button type="submit">Update</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="table" value="university">
                                            <input type="hidden" name="id" value="<?= $row['university_id'] ?>">
                                            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <!-- Students CRUD -->
                    <div id="student" class="table-section">
                        <h3>Students</h3>

                        <!-- Create Form -->
                        <h4>Add New Student</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="table" value="student">
                            <input type="text" name="fullname" placeholder="Full Name" required>
                            <select name="university_id">
                                <option value="">Select University</option>
                                <?php
                                $universities = getDropdownOptions($pdo, 'university', 'university_id', 'university_name');
                                foreach ($universities as $uni): ?>
                                    <option value="<?= $uni['university_id'] ?>"><?= $uni['university_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="dob" placeholder="Date of Birth">
                            <input type="email" name="email" placeholder="Email">
                            <input type="text" name="passport" placeholder="Passport">
                            <input type="text" name="phone_number" placeholder="Phone Number">
                            <select name="postcode_id">
                                <option value="">Select Postcode</option>
                                <?php
                                $postcodes = getDropdownOptions($pdo, 'postcode', 'zip_code', 'city');
                                foreach ($postcodes as $postcode): ?>
                                    <option value="<?= $postcode['zip_code'] ?>"><?= $postcode['zip_code'] ?> - <?= $postcode['city'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="address" placeholder="Address"></textarea>
                            <input type="date" name="expected_graduate" placeholder="Expected Graduate">
                            <input type="text" name="degree" placeholder="Degree">
                            <select name="level_of_qualification_id">
                                <option value="">Select Qualification Level</option>
                                <?php
                                $levels = getDropdownOptions($pdo, 'qualification_level', 'level_id', 'level_name');
                                foreach ($levels as $level): ?>
                                    <option value="<?= $level['level_id'] ?>"><?= $level['level_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <button type="submit">Add</button>
                        </form>

                        <!-- Read -->
                        <h4>Existing Students</h4>
                        <table border="1">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>University</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            $stmt = $pdo->query("SELECT s.*, u.university_name, ql.level_name FROM student s 
                                                LEFT JOIN university u ON s.university_id = u.university_id 
                                                LEFT JOIN qualification_level ql ON s.level_of_qualification_id = ql.level_id");
                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($data as $row): ?>
                                <tr>
                                    <td><?= $row['student_id'] ?></td>
                                    <td><?= $row['fullname'] ?></td>
                                    <td><?= $row['university_name'] ?></td>
                                    <td><?= $row['email'] ?></td>
                                    <td><?= $row['phone_number'] ?></td>
                                    <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="table" value="student">
                                            <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                            <input type="text" name="fullname" value="<?= $row['fullname'] ?>" required>
                                            <input type="email" name="email" value="<?= $row['email'] ?>">
                                            <input type="text" name="phone_number" value="<?= $row['phone_number'] ?>">
                                            <select name="is_active">
                                                <option value="1" <?= $row['is_active'] ? 'selected' : '' ?>>Active</option>
                                                <option value="0" <?= !$row['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                            <button type="submit">Update</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="table" value="student">
                                            <input type="hidden" name="id" value="<?= $row['student_id'] ?>">
                                            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </h3>

                <!-- PPIM CRUD -->
                <div id="ppim" class="table-section">
                    <h3>PPIM</h3>

                    <!-- Create Form -->
                    <h4>Add New PPIM Record</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="table" value="ppim">
                        <select name="student_id" required>
                            <option value="">Select Student</option>
                            <?php
                            $students = getDropdownOptions($pdo, 'student', 'student_id', 'fullname');
                            foreach ($students as $student): ?>
                                <option value="<?= $student['student_id'] ?>"><?= $student['fullname'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="start_year" placeholder="Start Year" required min="1900" max="2099">
                        <input type="text" name="department" placeholder="Department" required>
                        <textarea name="description" placeholder="Description"></textarea>
                        <select name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <button type="submit">Add</button>
                    </form>

                    <!-- Read -->
                    <h4>Existing PPIM Records</h4>
                    <table border="1">
                        <tr>
                            <th>Student</th>
                            <th>Start Year</th>
                            <th>Department</th>
                            <th>Description</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                        <?php
                        $stmt = $pdo->query("SELECT p.*, s.fullname FROM ppim p 
                                                LEFT JOIN student s ON p.student_id = s.student_id");
                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($data as $row): ?>
                            <tr>
                                <td><?= $row['fullname'] ?></td>
                                <td><?= $row['start_year'] ?></td>
                                <td><?= $row['department'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="table" value="ppim">
                                        <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                        <input type="hidden" name="start_year" value="<?= $row['start_year'] ?>">
                                        <input type="text" name="department" value="<?= $row['department'] ?>" required>
                                        <textarea name="description"><?= $row['description'] ?></textarea>
                                        <select name="is_active">
                                            <option value="1" <?= $row['is_active'] ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= !$row['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                        <button type="submit">Update</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="table" value="ppim">
                                        <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                        <input type="hidden" name="start_year" value="<?= $row['start_year'] ?>">
                                        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <!-- PPI Campus CRUD -->
                <div id="ppi_campus" class="table-section">
                    <h3>PPI Campus</h3>

                    <!-- Create Form -->
                    <h4>Add New PPI Campus Record</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="table" value="ppi_campus">
                        <select name="student_id" required>
                            <option value="">Select Student</option>
                            <?php
                            $students = getDropdownOptions($pdo, 'student', 'student_id', 'fullname');
                            foreach ($students as $student): ?>
                                <option value="<?= $student['student_id'] ?>"><?= $student['fullname'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="start_year" placeholder="Start Year" required min="1900" max="2099">
                        <select name="university_id" required>
                            <option value="">Select University</option>
                            <?php
                            $universities = getDropdownOptions($pdo, 'university', 'university_id', 'university_name');
                            foreach ($universities as $uni): ?>
                                <option value="<?= $uni['university_id'] ?>"><?= $uni['university_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="department" placeholder="Department">
                        <textarea name="description" placeholder="Description"></textarea>
                        <select name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <button type="submit">Add</button>
                    </form>

                    <!-- Read -->
                    <h4>Existing PPI Campus Records</h4>
                    <table border="1">
                        <tr>
                            <th>Student</th>
                            <th>Start Year</th>
                            <th>University</th>
                            <th>Department</th>
                            <th>Description</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                        <?php
                        $stmt = $pdo->query("SELECT pc.*, s.fullname, u.university_name FROM ppi_campus pc 
                                                LEFT JOIN student s ON pc.student_id = s.student_id
                                                LEFT JOIN university u ON pc.university_id = u.university_id");
                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($data as $row): ?>
                            <tr>
                                <td><?= $row['fullname'] ?></td>
                                <td><?= $row['start_year'] ?></td>
                                <td><?= $row['university_name'] ?></td>
                                <td><?= $row['department'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="table" value="ppi_campus">
                                        <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                        <input type="hidden" name="start_year" value="<?= $row['start_year'] ?>">
                                        <input type="hidden" name="university_id" value="<?= $row['university_id'] ?>">
                                        <input type="text" name="department" value="<?= $row['department'] ?>">
                                        <textarea name="description"><?= $row['description'] ?></textarea>
                                        <select name="is_active">
                                            <option value="1" <?= $row['is_active'] ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= !$row['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                        <button type="submit">Update</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="table" value="ppi_campus">
                                        <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                        <input type="hidden" name="start_year" value="<?= $row['start_year'] ?>">
                                        <input type="hidden" name="university_id" value="<?= $row['university_id'] ?>">
                                        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

            </div> <!-- container -->

            <!-- Footer Start -->
            <footer class="footer">
                <div class="page-container">
                    <div class="row">
                        <div class="col-md-6 text-center text-md-start">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © <?php echo $credit_footer; ?> - Pusdatin PPIM 2024/2025</span>
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-end footer-links d-none d-md-block">
                                <a href="javascript: void(0);">About</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Theme Settings -->
    <?php $main->renderTheme(); ?>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- Custom js -->
    <script src="assets/js/database-nav.js"></script>

</body>

</html>