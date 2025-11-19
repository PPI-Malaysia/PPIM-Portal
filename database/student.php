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
                                            1. Open and copy the google sheets <a
                                                href="https://docs.google.com/spreadsheets/d/1SmJdL_xxc75lAuuCfXxfvL99Hv_U5WwISeAiV2i1epo/edit?usp=sharing">(template_mahasiswa)</a><br>
                                            2. Fill in at least the "full name" column for each student<br>
                                            3. Savce as csv (.csv) file format<br>
                                            4. Drag and drop the completed file into the upload section below
                                        </p>
                                        <h3>Note!</h3>
                                        <p>
                                            1. The only required column is "Full Name"<br>
                                            2. All other columns are optional<br>
                                            3. Do not delete, move, or rename any columns in the template<br>
                                            4. Leave cells blank if the information is unknown<br>
                                            5. every phone number should have their region code (eg.
                                            60 / 62)<br>
                                        </p>
                                        <hr>
                                        <h5 class="card-title">Import New Student</h5>

                                        <!-- CSV uploader form (no actual submission) -->
                                        <form id="csvDropForm" class="mb-0" novalidate>

                                            <style>
                                            /* minimal local styles for the drop area */
                                            .custom-dropzone {
                                                cursor: pointer;
                                                border: 2px dashed #d9dfea;
                                                border-radius: .375rem;
                                                background: #fbfcfe;
                                            }

                                            .custom-dropzone.drag-over {
                                                background: #e9f7ff;
                                                border-color: #7cc4ff;
                                            }
                                            </style>

                                            <div id="csvDropArea" class="custom-dropzone text-center p-4" tabindex="0"
                                                aria-label="Drop CSV here or click to upload">
                                                <i class="ti ti-cloud-upload h1 text-muted"></i>
                                                <h3 id="csvDropTitle">Drop files here or click to upload.</h3>
                                                <span class="text-muted fs-13">(Only support <strong>.CSV</strong>
                                                    format)</span>
                                                <input type="file" id="csvFileInput" name="csv_file"
                                                    accept=".csv,text/csv" style="display:none" />
                                            </div>

                                            <div id="csvPreview" class="mt-3 d-none">
                                                <div class="card card-body p-2 small">
                                                    <div id="csvFileName" class="text-truncate"></div>
                                                </div>
                                                <div class="mt-2">
                                                    <!-- Check button: validates/prints CSV preview -->
                                                    <button type="button" id="checkButton"
                                                        class="btn btn-primary btn-sm" disabled>Check</button>
                                                    <!-- Import button: shown after successful check -->
                                                    <button type="button" id="importButton"
                                                        class="btn btn-success btn-sm d-none ms-2">
                                                        <i class="ti ti-upload"></i> Import Students
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- CSV table preview -->
                                            <div id="csvTablePreview" class="mt-3 d-none"></div>
                                            <!-- Progress bar -->
                                            <div id="importProgress" class="mt-3 d-none">
                                                <div class="card card-body">
                                                    <h5 class="mb-3">Importing Students...</h5>
                                                    <div class="progress mb-2" style="height: 25px;">
                                                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                                            role="progressbar" style="width: 0%">0%</div>
                                                    </div>
                                                    <div id="progressText" class="text-muted small">Preparing...</div>
                                                </div>
                                            </div>
                                            <!-- Results summary -->
                                            <div id="importResults" class="mt-3 d-none">
                                                <div class="card card-body">
                                                    <h5 id="resultsTitle" class="mb-3">Import Complete</h5>
                                                    <div id="resultsContent"></div>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- END REPLACED -->
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

        <script>
        (function() {
            const dropArea = document.getElementById('csvDropArea');
            const fileInput = document.getElementById('csvFileInput');
            const preview = document.getElementById('csvPreview');
            const fileNameEl = document.getElementById('csvFileName');
            const checkBtn = document.getElementById('checkButton');
            const importBtn = document.getElementById('importButton');
            const tablePreview = document.getElementById('csvTablePreview');
            const progressSection = document.getElementById('importProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const resultsSection = document.getElementById('importResults');
            const resultsTitle = document.getElementById('resultsTitle');
            const resultsContent = document.getElementById('resultsContent');

            let parsedRows = []; // Store parsed rows for import

            function showToast(msg, bg) {
                Toastify({
                    text: msg,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: bg || "linear-gradient(to right, #333, #555)"
                }).showToast();
            }

            function isCsvFile(file) {
                const name = (file && file.name) ? file.name.toLowerCase() : '';
                const type = (file && file.type) ? file.type.toLowerCase() : '';
                return name.endsWith('.csv') || type === 'text/csv' || type === 'application/csv';
            }

            function handleFiles(files) {
                if (!files || !files.length) return;
                const file = files[0];
                if (!isCsvFile(file)) {
                    showToast('Only .csv files are allowed', 'linear-gradient(to right, #ff5f6d, #ffc371)');
                    fileInput.value = '';
                    preview.classList.add('d-none');
                    checkBtn.disabled = true;
                    importBtn.classList.add('d-none');
                    tablePreview.classList.add('d-none');
                    parsedRows = [];
                    return;
                }
                fileNameEl.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                preview.classList.remove('d-none');
                checkBtn.disabled = false;
                importBtn.classList.add('d-none');
                tablePreview.classList.add('d-none');
                resultsSection.classList.add('d-none');
                parsedRows = [];
            }

            // click to open file picker
            dropArea.addEventListener('click', function() {
                fileInput.click();
            });

            // keyboard accessibility: Enter or Space opens file picker
            dropArea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    fileInput.click();
                }
            });

            // file input change
            fileInput.addEventListener('change', function(e) {
                handleFiles(e.target.files);
            });

            // drag over / enter
            ['dragenter', 'dragover'].forEach(evt => {
                dropArea.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropArea.classList.add('drag-over');
                });
            });

            // drag leave / end
            ['dragleave', 'dragend', 'drop'].forEach(evt => {
                dropArea.addEventListener(evt, function(e) {
                    dropArea.classList.remove('drag-over');
                });
            });

            // drop
            dropArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    handleFiles(dt.files);
                    // mirror file into file input (for future form submit if implemented)
                    try {
                        const file = dt.files[0];
                        // create a DataTransfer to set the file input files (modern browsers)
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;
                    } catch (err) {
                        // ignore if unsupported
                    }
                }
            });

            // CSV parsing utility (handles quoted fields)
            function parseCSVLine(line) {
                const result = [];
                let cur = '';
                let inQuotes = false;
                for (let i = 0; i < line.length; i++) {
                    const ch = line[i];
                    if (ch === '"') {
                        if (inQuotes && i + 1 < line.length && line[i + 1] === '"') {
                            cur += '"'; // escaped quote
                            i++;
                        } else {
                            inQuotes = !inQuotes;
                        }
                    } else if (ch === ',' && !inQuotes) {
                        result.push(cur);
                        cur = '';
                    } else {
                        cur += ch;
                    }
                }
                result.push(cur);
                return result;
            }

            // Expected header columns (exact order)
            const expectedHeader = [
                'Full Name', 'Date of Birth', 'Email', 'Passport', 'Phone Number', 'Zip Code',
                'Address', 'Qualification Level', 'Degree Programme', 'Expected Graduate'
            ];

            // Parse date from various formats to YYYY-MM-DD
            function parseDate(dateStr) {
                if (!dateStr || dateStr.trim() === '') return null;

                dateStr = dateStr.trim();

                // Already in YYYY-MM-DD format (validate it)
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                    const parts = dateStr.split('-');
                    const year = parseInt(parts[0]);
                    const month = parseInt(parts[1]);
                    const day = parseInt(parts[2]);

                    // Validate the date
                    if (month >= 1 && month <= 12 && day >= 1 && day <= 31) {
                        return dateStr;
                    }
                }

                // Try various formats
                const formats = [
                    {regex: /^(\d{2})\/(\d{2})\/(\d{4})$/, order: 'dmy'},  // DD/MM/YYYY
                    {regex: /^(\d{2})-(\d{2})-(\d{4})$/, order: 'dmy'},    // DD-MM-YYYY
                    {regex: /^(\d{4})\/(\d{2})\/(\d{2})$/, order: 'ymd'},  // YYYY/MM/DD
                    {regex: /^(\d{4})-(\d{2})-(\d{2})$/, order: 'ymd'},    // YYYY-MM-DD
                ];

                for (let format of formats) {
                    const match = dateStr.match(format.regex);
                    if (match) {
                        let year, month, day;

                        if (format.order === 'ymd') {
                            year = parseInt(match[1]);
                            month = parseInt(match[2]);
                            day = parseInt(match[3]);
                        } else { // dmy
                            day = parseInt(match[1]);
                            month = parseInt(match[2]);
                            year = parseInt(match[3]);
                        }

                        // Validate the parsed values
                        if (year >= 1900 && year <= 2100 && month >= 1 && month <= 12 && day >= 1 && day <= 31) {
                            // Additional validation: check if date is actually valid
                            const testDate = new Date(year, month - 1, day);
                            if (testDate.getFullYear() === year &&
                                testDate.getMonth() === month - 1 &&
                                testDate.getDate() === day) {
                                return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                            }
                        }
                    }
                }

                // If all else fails, try native Date parsing
                try {
                    const date = new Date(dateStr);
                    if (!isNaN(date.getTime())) {
                        const year = date.getFullYear();
                        const month = date.getMonth() + 1;
                        const day = date.getDate();

                        if (year >= 1900 && year <= 2100) {
                            return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        }
                    }
                } catch (e) {
                    // ignore
                }

                return null;
            }

            // Click "Check": read file, validate header, render table of rows where Full Name exists
            checkBtn.addEventListener('click', function() {
                const files = fileInput.files;
                if (!files || !files.length) {
                    showToast('No file selected', 'linear-gradient(to right, #ff5f6d, #ffc371)');
                    return;
                }
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const text = e.target.result || '';
                    // Normalize newlines and split
                    const rawLines = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
                    // Remove leading/trailing empty lines
                    while (rawLines.length && rawLines[0].trim() === '') rawLines.shift();
                    while (rawLines.length && rawLines[rawLines.length - 1].trim() === '') rawLines.pop();

                    if (!rawLines.length) {
                        showToast("Error: can't read the file, please use the template format!",
                            'linear-gradient(to right, #ff5f6d, #ffc371)');
                        return;
                    }
                    const headerFields = parseCSVLine(rawLines[0]).map(s => s.trim());
                    // Validate header exact columns and order
                    if (headerFields.length !== expectedHeader.length || !expectedHeader.every((v, i) =>
                            headerFields[i] === v)) {
                        showToast("Error: can't read the file, please use the template format!",
                            'linear-gradient(to right, #ff5f6d, #ffc371)');
                        return;
                    }

                    // Collect valid rows (first comma exists and first column non-empty)
                    const rows = [];
                    for (let i = 1; i < rawLines.length; i++) {
                        const line = rawLines[i];
                        if (!line || line.indexOf(',') === -1) continue; // require at least first comma
                        const fields = parseCSVLine(line);
                        const fullName = (fields[0] || '').trim();
                        if (!fullName) continue; // skip rows without full name
                        // ensure array length equals header length (pad with empty strings)
                        while (fields.length < expectedHeader.length) fields.push('');
                        rows.push(fields.slice(0, expectedHeader.length).map(f => f.trim()));
                    }

                    if (!rows.length) {
                        tablePreview.innerHTML =
                            '<div class="text-muted">No valid rows with Full Name found.</div>';
                        tablePreview.classList.remove('d-none');
                        importBtn.classList.add('d-none');
                        parsedRows = [];
                        return;
                    }

                    // Store parsed rows for import
                    parsedRows = rows;

                    // Build table HTML
                    let html =
                        '<div class="table-responsive"><table class="table table-sm table-striped"><thead><tr>';
                    for (const h of expectedHeader) html += '<th>' + h + '</th>';
                    html += '</tr></thead><tbody>';
                    for (const r of rows) {
                        html += '<tr>';
                        for (const c of r) {
                            html += '<td>' + (c ? escapeHtml(c) : '') + '</td>';
                        }
                        html += '</tr>';
                    }
                    html += '</tbody></table></div>';
                    html += '<div class="small text-muted mt-1">Showing ' + rows.length +
                        ' row(s) ready to import</div>';
                    tablePreview.innerHTML = html;
                    tablePreview.classList.remove('d-none');

                    // Show import button
                    importBtn.classList.remove('d-none');
                };
                reader.onerror = function() {
                    showToast("Error reading file", 'linear-gradient(to right, #ff5f6d, #ffc371)');
                };
                reader.readAsText(file);
            });

            // Import button click handler
            importBtn.addEventListener('click', async function() {
                if (!parsedRows || parsedRows.length === 0) {
                    showToast('No data to import', 'linear-gradient(to right, #ff5f6d, #ffc371)');
                    return;
                }

                // Disable buttons during import
                checkBtn.disabled = true;
                importBtn.disabled = true;

                // Show progress section
                progressSection.classList.remove('d-none');
                resultsSection.classList.add('d-none');

                const total = parsedRows.length;
                let success = 0;
                let failed = [];

                for (let i = 0; i < parsedRows.length; i++) {
                    const row = parsedRows[i];
                    const rowNum = i + 2; // +2 because row 1 is header, and array is 0-indexed

                    // Update progress
                    const percent = Math.round(((i + 1) / total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                    progressText.textContent = `Processing row ${i + 1} of ${total}...`;

                    // Map CSV columns to API fields
                    // Index: 0=Full Name, 1=DOB, 2=Email, 3=Passport, 4=Phone, 5=Zip Code,
                    //        6=Address, 7=Qualification Level, 8=Degree Programme, 9=Expected Graduate
                    const studentData = {
                        fullname: row[0] || '',
                        dob: parseDate(row[1]),
                        email: row[2] || null,
                        passport: row[3] || null,
                        phone_number: row[4] || null,
                        postcode_id: row[5] || null,
                        address: row[6] || null,
                        level_of_qualification_id: row[7] ? parseInt(row[7]) : null,
                        degree: row[8] || null,
                        expected_graduate: parseDate(row[9])
                    };

                    try {
                        const response = await fetch('../assets/php/API/bulk-upload-student.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(studentData)
                        });

                        const result = await response.json();

                        if (result.success) {
                            success++;
                        } else {
                            failed.push({
                                row: rowNum,
                                name: studentData.fullname,
                                error: result.error || 'Unknown error'
                            });
                        }
                    } catch (error) {
                        failed.push({
                            row: rowNum,
                            name: studentData.fullname,
                            error: 'Network error: ' + error.message
                        });
                    }
                }

                // Hide progress, show results
                progressSection.classList.add('d-none');
                resultsSection.classList.remove('d-none');

                // Build results summary
                let resultsHtml = `<div class="alert alert-${failed.length > 0 ? 'warning' : 'success'}" role="alert">`;
                resultsHtml += `<strong>${success} of ${total} students imported successfully!</strong>`;
                resultsHtml += `</div>`;

                if (failed.length > 0) {
                    resultsHtml += `<h6 class="mt-3 mb-2 text-danger">Failed Imports (${failed.length}):</h6>`;
                    resultsHtml += `<div class="table-responsive">`;
                    resultsHtml += `<table class="table table-sm table-bordered">`;
                    resultsHtml += `<thead><tr><th>Row</th><th>Name</th><th>Error</th></tr></thead><tbody>`;
                    for (const fail of failed) {
                        resultsHtml += `<tr>`;
                        resultsHtml += `<td>${fail.row}</td>`;
                        resultsHtml += `<td>${escapeHtml(fail.name)}</td>`;
                        resultsHtml += `<td class="text-danger">${escapeHtml(fail.error)}</td>`;
                        resultsHtml += `</tr>`;
                    }
                    resultsHtml += `</tbody></table></div>`;
                }

                resultsContent.innerHTML = resultsHtml;

                // Re-enable buttons
                checkBtn.disabled = false;
                importBtn.disabled = false;

                // Show toast notification
                if (failed.length === 0) {
                    showToast(`All ${success} students imported successfully!`, 'linear-gradient(to right, #00b09b, #96c93d)');
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showToast(`Import completed with ${failed.length} errors`, 'linear-gradient(to right, #ff5f6d, #ffc371)');
                }
            });

            // simple HTML escape
            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, function(m) {
                    return ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    } [m]);
                });
            }
        })();
        </script>

</body>

</html>