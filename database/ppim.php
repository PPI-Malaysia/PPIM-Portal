<?php
// Load the new StudentDatabase class
require_once("../assets/php/student-database.php");

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

//check if user have access
if(!$studentDB->hasFullAccess()){
    header("location: ../index.php");
}

// Pagination, search, sorting, and page size
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$allowedLimits = [10, 100, 500];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, $allowedLimits, true)) { $limit = 10; }
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : null; // id, fullname, start_year, end_year, department, position
$dir = isset($_GET['dir']) ? strtolower($_GET['dir']) : 'asc';
$dir = $dir === 'desc' ? 'desc' : 'asc';

// Get paginated data and total count
$data = $studentDB->getPaginatedTableDataWithJoins('ppim', $page, $limit, $search, $sort, $dir);
$totalRecords = $studentDB->getTotalCount('ppim', $search);
$totalPages = ceil($totalRecords / $limit);

//check status
function status($int){
    switch ($int){
        case "0":
            return "unverified";
        case "1":
            return "active";
        case "2":
            return "ended";
        default:
            return "unknown";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>PPIM Members | PPI Malaysia</title>
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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">PPIM Members Management</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Student Database</a></li>
                            <li class="breadcrumb-item active">PPIM Members</li>
                        </ol>
                    </div>
                </div>

                <!-- PPIM CRUD -->
                <div id="ppim" class="row">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">PPIM Members</h4>
                                <div>
                                    <button type="button" class="btn btn-success btn-sm mx-2" id="exportBtn">
                                        <i class="ti ti-download fs-16"></i> Export
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#addPpim">
                                        <i class="ti ti-plus fs-16"></i> Add New
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Add Form -->
                                <div class="collapse mb-3" id="addPpim">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New PPIM Member</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="ppim">
                                            <div class="col-md-6">
                                                <label class="form-label">Student</label>
                                                <select name="student_id" class="form-select" required>
                                                    <option value="">Select Student</option>
                                                    <?php
                                                    $students = $studentDB->getDropdownOptions('student', 'student_id', 'fullname');
                                                    foreach ($students as $student): ?>
                                                    <option value="<?= htmlspecialchars($student['student_id']) ?>">
                                                        <?= htmlspecialchars($student['fullname']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Start Year</label>
                                                <input type="number" name="start_year" class="form-control" required
                                                    min="1900" max="2100">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">End Year</label>
                                                <input type="number" name="end_year" class="form-control" min="1900"
                                                    max="2100">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Department</label>
                                                <input type="text" name="department" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Position</label>
                                                <input type="text" name="position" class="form-control"
                                                    placeholder="President, Secretary">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-secondary">Add PPIM Member</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

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
                                                placeholder="Search members (name, department, position)..."
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
                                            Showing <?= count($data) ?> of <?= $totalRecords ?> members
                                            <?php if (!empty($search)): ?>(filtered from total)<?php endif; ?>
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

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <?php
                                                function sort_link_ppim($label, $key, $currentSort, $currentDir, $search, $limit) {
                                                    $newDir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc';
                                                    $qs = http_build_query(array_filter([
                                                        'page' => 1,
                                                        'search' => $search !== '' ? $search : null,
                                                        'sort' => $key,
                                                        'dir' => $newDir,
                                                        'limit' => $limit,
                                                    ]));
                                                    $arrow = '';
                                                    if ($currentSort === $key) {
                                                        $arrow = $currentDir === 'asc' ? ' ▲' : ' ▼';
                                                    }
                                                    return '<a class="text-white text-decoration-none" href="?' . htmlspecialchars($qs) . '">' . htmlspecialchars($label) . $arrow . '</a>';
                                                }
                                                ?>
                                                <th><?= sort_link_ppim('No', 'id', $sort, $dir, $search, $limit) ?></th>
                                                <th><?= sort_link_ppim('Student Name', 'fullname', $sort, $dir, $search, $limit) ?>
                                                </th>
                                                <th><?= sort_link_ppim('Start Year', 'start_year', $sort, $dir, $search, $limit) ?>
                                                </th>
                                                <th><?= sort_link_ppim('End Year', 'end_year', $sort, $dir, $search, $limit) ?>
                                                </th>
                                                <th><?= sort_link_ppim('Department', 'department', $sort, $dir, $search, $limit) ?>
                                                </th>
                                                <th><?= sort_link_ppim('Position', 'position', $sort, $dir, $search, $limit) ?>
                                                </th>
                                                <th><?= sort_link_ppim('Status', 'Status', $sort, $dir, $search, $limit) ?>
                                                </th>
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
                                                            No PPIM member found matching
                                                            "<?= htmlspecialchars($search) ?>"
                                                            <?php else: ?>
                                                            No PPIM member found
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php
                                                $rowNum = ($dir === 'desc') ? (($page - 1) * $limit + count($data)) : (($page - 1) * $limit + 1);
                                                foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= $dir === 'desc' ? $rowNum-- : $rowNum++ ?></td>
                                                <td><?= htmlspecialchars($row['fullname'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['start_year']) ?></td>
                                                <td><?= htmlspecialchars($row['end_year'] ?? 'Current') ?></td>
                                                <td><?= htmlspecialchars($row['department']) ?></td>
                                                <td><?= htmlspecialchars($row['position'] ?? '') ?></td>
                                                <td><?= status($row['is_active']) ?></td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-soft-primary rounded-pill btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPpimModal<?= $row['ppim_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="ppim">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['ppim_id']) ?>">
                                                        <button type="submit"
                                                            class="btn btn-soft-danger rounded-pill btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editPpimModal<?= $row['ppim_id'] ?>"
                                                        tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit PPIM Member</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table" value="ppim">
                                                                        <input type="hidden" name="ppim_id"
                                                                            value="<?= htmlspecialchars($row['ppim_id']) ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Student</label>
                                                                            <input type="text" class="form-control"
                                                                                value="<?= htmlspecialchars($row['fullname'] ?? '') ?>"
                                                                                readonly>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Start Year</label>
                                                                            <input type="number" name="start_year"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['start_year'] ?? '') ?>"
                                                                                min="1900" max="2100">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">End Year</label>
                                                                            <input type="number" name="end_year"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['end_year'] ?? '') ?>"
                                                                                min="1900" max="2100">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Department</label>
                                                                            <input type="text" name="department"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['department']) ?>"
                                                                                required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Position</label>
                                                                            <input type="text" name="position"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['position'] ?? '') ?>">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label
                                                                                class="form-label">Description</label>
                                                                            <textarea name="description"
                                                                                class="form-control"
                                                                                rows="3"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select name="is_active"
                                                                                class="form-select">
                                                                                <option value="0"
                                                                                    <?= !$row['is_active'] ? 'selected' : '' ?>>
                                                                                    Unverified</option>
                                                                                <option value="1"
                                                                                    <?= $row['is_active'] ? 'selected' : '' ?>>
                                                                                    Active</option>
                                                                                <option value="2"
                                                                                    <?= status($row['is_active']) == "ended" ? 'selected' : '' ?>>
                                                                                    Ended</option>
                                                                            </select>
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
                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <small class="text-muted">
                                            Page <?= $page ?> of <?= $totalPages ?>
                                        </small>
                                    </div>
                                    <nav aria-label="PPIM pagination">
                                        <ul class="pagination pagination-sm mb-0">
                                            <!-- Previous Page -->
                                            <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>">
                                                    <i class="ti ti-chevron-left"></i> Previous
                                                </a>
                                            </li>
                                            <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="ti ti-chevron-left"></i> Previous
                                                </span>
                                            </li>
                                            <?php endif; ?>

                                            <!-- Page Numbers -->
                                            <?php
                                            $startPage = max(1, $page - 2);
                                            $endPage = min($totalPages, $page + 2);
                                            
                                            if ($startPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>">1</a>
                                            </li>
                                            <?php if ($startPage > 2): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                            <?php endif; ?>
                                            <?php endif; ?>

                                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link"
                                                    href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>"><?= $i ?></a>
                                            </li>
                                            <?php endfor; ?>

                                            <?php if ($endPage < $totalPages): ?>
                                            <?php if ($endPage < $totalPages - 1): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=<?= $totalPages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>"><?= $totalPages ?></a>
                                            </li>
                                            <?php endif; ?>

                                            <!-- Next Page -->
                                            <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) : '' ?><?= '&limit=' . urlencode((string)$limit) ?>">
                                                    Next <i class="ti ti-chevron-right"></i>
                                                </a>
                                            </li>
                                            <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    Next <i class="ti ti-chevron-right"></i>
                                                </span>
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

        <!-- Export functionality -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const exportBtn = document.getElementById('exportBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    // Get current search and sort parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    const search = urlParams.get('search') || '';
                    const sort = urlParams.get('sort') || '';
                    const dir = urlParams.get('dir') || '';

                    // Build export URL with current filters
                    let exportUrl = '../assets/php/API/export-ppim.php?export=csv';
                    if (search) exportUrl += '&search=' + encodeURIComponent(search);
                    if (sort) exportUrl += '&sort=' + encodeURIComponent(sort);
                    if (dir) exportUrl += '&dir=' + encodeURIComponent(dir);

                    // Trigger download
                    window.location.href = exportUrl;
                });
            }
        });
        </script>

</body>

</html>