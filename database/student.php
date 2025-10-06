<?php
// Load the new StudentDatabase class
require_once("../assets/php/student-database.php");

// Pagination, search, and sorting parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Entries per page: allow 10, 100, 500
$allowedLimits = [10, 100, 500];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, $allowedLimits, true)) { $limit = 10; }
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : null; // allowed: id, fullname, university, degree
$dir = isset($_GET['dir']) ? strtolower($_GET['dir']) : 'asc';
$dir = $dir === 'desc' ? 'desc' : 'asc';

//check if user has a full access or just a ppi campus
$hasFullAccess = $studentDB->hasFullAccess();

// Get paginated data
$data = $studentDB->getPaginatedTableDataWithJoins('student', $page, $limit, $search, $sort, $dir);
$totalRecords = $studentDB->getTotalCount('student', $search);
$totalPages = ceil($totalRecords / $limit);

// Credit: fill your name as the person who created this page here
$credit = "Christopher Bertrand, Rafi Daffa Ramadhani";
$credit_footer = '
    <a href="https://github.com/Zentoboo" target="_blank">
        Christopher Bertrand
    </a>and
    <a href="https://rafidaffa.com" target="_blank">
        Rafi Daffa
    </a>
';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Students | PPI Malaysia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo htmlspecialchars($credit); ?>" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">

    <!-- Theme Config Js -->
    <script src="../assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="../assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="../assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Database css -->
    <link href="../assets/css/student-database.css" rel="stylesheet" type="text/css" />

    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php $studentDB->renderNavbar(); ?>

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="page-content">
            <div class="page-container">

                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Students Management</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Student Database</a></li>
                            <li class="breadcrumb-item active">Students</li>
                        </ol>
                    </div>
                </div>

                <!-- Students CRUD -->
                <div id="student" class="row">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">Students</h4>
                                <div>
                                    <?php if (!$hasFullAccess) { ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm mx-2"
                                        data-bs-toggle="collapse" data-bs-target="#importStudent">
                                        <i class="ti ti-plus fs-16"></i> Add Bulk
                                    </button>
                                    <?php } ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#addStudent">
                                        <i class="ti ti-plus fs-16"></i> Add New
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Search Form -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <form method="GET" class="d-flex gap-2">
                                            <?php if (!empty($sort)): ?>
                                            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                                            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                                            <?php endif; ?>
                                            <input type="hidden" name="limit" value="<?= htmlspecialchars($limit) ?>">
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Search students..."
                                                value="<?= htmlspecialchars($search) ?>">
                                            <button type="submit" class="btn btn-outline-primary">
                                                <i class="ti ti-search"></i> Search
                                            </button>
                                            <?php if (!empty($search)): ?>
                                            <a href="?" class="btn btn-outline-secondary">
                                                <i class="ti ti-x"></i> Clear
                                            </a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            Showing <?= count($data) ?> of <?= $totalRecords ?> students
                                            <?php if (!empty($search)): ?>
                                            (filtered from total)
                                            <?php endif; ?>
                                        </small>
                                        <div class="mt-1">
                                            <form method="GET" class="d-inline-flex align-items-center gap-2">
                                                <input type="hidden" name="page" value="1">
                                                <?php if ($search !== ''): ?>
                                                <input type="hidden" name="search"
                                                    value="<?= htmlspecialchars($search) ?>">
                                                <?php endif; ?>
                                                <?php if (!empty($sort)): ?>
                                                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                                                <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                                                <?php endif; ?>
                                                <label for="limitSelect" class="me-1 text-muted mb-0">Show:</label>
                                                <select id="limitSelect" name="limit"
                                                    class="form-select form-select-sm w-auto"
                                                    onchange="this.form.submit()">
                                                    <?php foreach ([10, 100, 500] as $opt): ?>
                                                    <option value="<?= $opt ?>"
                                                        <?= ($limit === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!$hasFullAccess) { ?>
                                <!-- Import Form -->
                                <div class="collapse mb-3" id="importStudent">
                                    <div class="card card-body">
                                        <h3>Step</h3>
                                        <p>
                                            1. Download the template file <a
                                                href="../assets/file/template_mahasiswa.xlsx">(template_mahasiswa.xlsx)</a><br>
                                            2. Fill in at least the "full name" column for each student<br>
                                            3. Drag and drop the completed file into the upload section below
                                        </p>
                                        <h3>Note!</h3>
                                        <p>
                                            1. The only required column is "Full Name"<br>
                                            2. All other columns are optional<br>
                                            3. Do not delete, move, or rename any columns in the template<br>
                                            4. Leave cells blank if the information is unknown<br>
                                            5. every phone number should have their region code, including the "+" (eg.
                                            +60)<br>
                                            6. Passport consist of 1 character and 7 number (eg. A1234567)
                                        </p>
                                        <hr>
                                        <h5 class="card-title">Import New Student</h5>
                                        <form action="" method="post" class="dropzone dz-clickable"
                                            id="myAwesomeDropzone" data-plugin="dropzone"
                                            data-previews-container="#file-previews"
                                            data-upload-preview-template="#uploadPreviewTemplate"
                                            enctype="multipart/form-data">
                                            <input type="hidden" name="action" value="bulk_student_upload">
                                            <input type="hidden" name="table" value="student">
                                            <div class="dz-message needsclick">
                                                <i class="ti ti-cloud-upload h1 text-muted"></i>
                                                <h3>Drop files here or click to upload.</h3>
                                                <span class="text-muted fs-13">(Only support
                                                    <strong>.Xlsx</strong> format)</span>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <?php } ?>



                                <!-- Add Form -->
                                <div class="collapse mb-3" id="addStudent">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New Student</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="student">
                                            <div class="col-md-4">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" name="fullname" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">University</label>
                                                <select name="university_id" class="form-select">
                                                    <option value="">Select University</option>
                                                    <?php
                                                    $universities = $studentDB->getDropdownOptions('university', 'university_id', 'university_name');
                                                    foreach ($universities as $university): ?>
                                                    <option
                                                        value="<?= htmlspecialchars($university['university_id']) ?>">
                                                        <?= htmlspecialchars($university['university_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Status</label>
                                                <select name="status_id" class="form-select">
                                                    <?php
                                                    $statuses = $studentDB->getDropdownOptions('student_status', 'status_id', 'status_name');
                                                    foreach ($statuses as $status): ?>
                                                    <option value="<?= htmlspecialchars($status['status_id']) ?>"
                                                        <?= $status['status_id'] == 1 ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($status['status_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Date of Birth</label>
                                                <input type="date" name="dob" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Passport Number</label>
                                                <input type="text" name="passport" class="form-control"
                                                    placeholder="A1234567">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="phone_number" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Postcode*</label>
                                                <select name="postcode_id" class="form-control"
                                                    id="choices-single-no-sorting" data-choices
                                                    data-choices-sorting-false>
                                                    <option value="">Select Postcode</option>
                                                    <?php
                                                    $postcodes = $studentDB->getDropdownOptions('postcode', 'zip_code', 'city');
                                                    foreach ($postcodes as $postcode): ?>
                                                    <option value="<?= htmlspecialchars($postcode['zip_code']) ?>">
                                                        <?= htmlspecialchars($postcode['zip_code']) ?> -
                                                        <?= htmlspecialchars($postcode['city']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Expected Graduate</label>
                                                <input type="date" name="expected_graduate" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Address</label>
                                                <textarea name="address" class="form-control" rows="2"></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Degree</label>
                                                <input type="text" name="degree" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Qualification Level</label>
                                                <select name="level_of_qualification_id" class="form-select">
                                                    <option value="">Select Level</option>
                                                    <?php
                                                    $levels = $studentDB->getDropdownOptions('qualification_level', 'level_id', 'level_name');
                                                    foreach ($levels as $level): ?>
                                                    <option value="<?= htmlspecialchars($level['level_id']) ?>">
                                                        <?= htmlspecialchars($level['level_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-secondary">Add Student</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <?php
                                                // Helper to build sort URLs with toggle
                                                function sort_link($label, $key, $currentSort, $currentDir, $search) {
                                                    $newDir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc';
                                                    $qs = http_build_query(array_filter([
                                                        'page' => 1, // reset page on sort
                                                        'search' => $search !== '' ? $search : null,
                                                        'sort' => $key,
                                                        'dir' => $newDir,
                                                    ]));
                                                    $arrow = '';
                                                    if ($currentSort === $key) {
                                                        $arrow = $currentDir === 'asc' ? ' ▲' : ' ▼';
                                                    }
                                                    return '<a class="text-white text-decoration-none" href="?' . htmlspecialchars($qs) . '">' . htmlspecialchars($label) . $arrow . '</a>';
                                                }
                                                ?>
                                                <th><?= sort_link('No', 'id', $sort, $dir, $search) ?></th>
                                                <th><?= sort_link('Full Name', 'fullname', $sort, $dir, $search) ?></th>
                                                <th><?= sort_link('University', 'university', $sort, $dir, $search) ?>
                                                </th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th><?= sort_link('Degree', 'degree', $sort, $dir, $search) ?></th>
                                                <th>Active</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($data)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="ti ti-search fs-24 mb-2"></i>
                                                        <p class="mb-0">
                                                            <?php if (!empty($search)): ?>
                                                            No students found matching
                                                            "<?= htmlspecialchars($search) ?>"
                                                            <?php else: ?>
                                                            No students found
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php
                                            $rowNum = ($dir === 'desc')
                                                ? (($page - 1) * $limit + count($data))
                                                : (($page - 1) * $limit + 1);
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= $dir === 'desc' ? $rowNum-- : $rowNum++ ?></td>
                                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                                <td><?= htmlspecialchars($row['university_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                                <td><span
                                                        class="badge bg-<?= $row['status_id'] == 1 ? 'success' : ($row['status_id'] == 2 ? 'primary' : 'secondary') ?>"><?= htmlspecialchars($row['status_name'] ?? '') ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($row['degree'] ?? '') ?></td>
                                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-soft-primary rounded-pill btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editStudentModal<?= $row['student_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="student">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['student_id']) ?>">
                                                        <button type="submit"
                                                            class="btn btn-soft-danger rounded-pill btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade"
                                                        id="editStudentModal<?= $row['student_id'] ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Student</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table"
                                                                            value="student">
                                                                        <input type="hidden" name="student_id"
                                                                            value="<?= htmlspecialchars($row['student_id']) ?>">
                                                                        <div class="row g-3">
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Full
                                                                                    Name</label>
                                                                                <input type="text" name="fullname"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($row['fullname']) ?>"
                                                                                    required>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Email</label>
                                                                                <input type="email" name="email"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($row['email'] ?? '') ?>">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label
                                                                                    class="form-label">University</label>
                                                                                <select name="university_id"
                                                                                    class="form-select">
                                                                                    <option value="">Select University
                                                                                    </option>
                                                                                    <?php
                                                                                    $universities = $studentDB->getDropdownOptions('university', 'university_id', 'university_name');
                                                                                    foreach ($universities as $university): ?>
                                                                                    <option
                                                                                        value="<?= htmlspecialchars($university['university_id']) ?>"
                                                                                        <?= $row['university_id'] == $university['university_id'] ? 'selected' : '' ?>>
                                                                                        <?= htmlspecialchars($university['university_name']) ?>
                                                                                    </option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Status</label>
                                                                                <select name="status_id"
                                                                                    class="form-select">
                                                                                    <?php
                                                                                    $statuses = $studentDB->getDropdownOptions('student_status', 'status_id', 'status_name');
                                                                                    foreach ($statuses as $status): ?>
                                                                                    <option
                                                                                        value="<?= htmlspecialchars($status['status_id']) ?>"
                                                                                        <?= $row['status_id'] == $status['status_id'] ? 'selected' : '' ?>>
                                                                                        <?= htmlspecialchars($status['status_name']) ?>
                                                                                    </option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label
                                                                                    class="form-label">Passport</label>
                                                                                <input type="text" name="passport"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($row['passport'] ?? '') ?>">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Phone</label>
                                                                                <input type="text" name="phone_number"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($row['phone_number'] ?? '') ?>">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Date of
                                                                                    Birth</label>
                                                                                <input type="date" name="dob"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($row['dob'] ?? '') ?>">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Expected
                                                                                    Graduate</label>
                                                                                <input type="date"
                                                                                    name="expected_graduate"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($row['expected_graduate'] ?? '') ?>">
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <label class="form-label">Degree</label>
                                                                                <input type="text" name="degree"
                                                                                    class="form-control"
                                                                                    value="<?= htmlspecialchars($row['degree'] ?? '') ?>">
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <label
                                                                                    class="form-label">Address</label>
                                                                                <textarea name="address"
                                                                                    class="form-control"
                                                                                    rows="3"><?= htmlspecialchars($row['address'] ?? '') ?></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit"
                                                                            class="btn btn-primary">Update</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Controls -->
                                <?php if ($totalPages > 1): ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <small class="text-muted">
                                            Page <?= $page ?> of <?= $totalPages ?>
                                        </small>
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm mb-0">
                                            <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>">
                                                    <i class="ti ti-chevron-left"></i> Previous
                                                </a>
                                            </li>
                                            <?php endif; ?>

                                            <?php
                                            $start = max(1, $page - 2);
                                            $end = min($totalPages, $page + 2);
                                            
                                            for ($i = $start; $i <= $end; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link"
                                                    href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>">
                                                    Next <i class="ti ti-chevron-right"></i>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

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
        <?php $studentDB->renderTheme(); ?>

        <!-- Vendor js -->
        <script src="../assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="../assets/js/app.js"></script>

        <!-- Toast notification js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.js"
            integrity="sha512-MnKz2SbnWiXJ/e0lSfSzjaz9JjJXQNb2iykcZkEY2WOzgJIWVqJBFIIPidlCjak0iTH2bt2u1fHQ4pvKvBYy6Q=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Custom js -->
        <script src="../assets/js/database-nav.js"></script>

</body>

</html>