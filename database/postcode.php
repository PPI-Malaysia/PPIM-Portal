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

// Pagination and search parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get paginated data and total count
$data = $studentDB->getPaginatedTableDataWithJoins('postcode', $page, $limit, $search);
$totalRecords = $studentDB->getTotalCount('postcode', $search);
$totalPages = ceil($totalRecords / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Postcodes | PPI Malaysia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo htmlspecialchars($credit); ?>" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Postcodes Management</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Student Database</a></li>
                            <li class="breadcrumb-item active">Postcodes</li>
                        </ol>
                    </div>
                </div>

                <!-- Postcodes Section -->
                <div id="postcode" class="row">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">Postcodes</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addPostcode">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Add Form (Collapsible) -->
                                <div class="collapse mb-3" id="addPostcode">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New Postcode</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="postcode">
                                            <div class="col-md-4">
                                                <label class="form-label">Zip Code</label>
                                                <input type="number" name="zip_code" class="form-control" required
                                                    min="10000" max="99999">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" name="city" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">State Name</label>
                                                <input type="text" name="state_name" class="form-control" required>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-secondary">Add Postcode</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Search Form -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <form method="GET" class="d-flex">
                                            <input type="text" name="search" class="form-control me-2"
                                                placeholder="Search postcode..."
                                                value="<?= htmlspecialchars($search) ?>">
                                            <button type="submit" class="btn btn-outline-primary">
                                                <i class="ti ti-search"></i> Search
                                            </button>
                                            <?php if (!empty($search)): ?>
                                            <a href="?" class="btn btn-outline-secondary ms-2">
                                                <i class="ti ti-x"></i> Clear
                                            </a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            Showing <?= count($data) ?> of <?= $totalRecords ?> postcode
                                            <?php if (!empty($search)): ?>
                                            (filtered by "<?= htmlspecialchars($search) ?>")
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Zip Code</th>
                                                <th>City</th>
                                                <th>State</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($data)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="ti ti-search fs-24 mb-2"></i>
                                                        <p class="mb-0">
                                                            <?php if (!empty($search)): ?>
                                                            No postcode found matching
                                                            "<?= htmlspecialchars($search) ?>"
                                                            <?php else: ?>
                                                            No postcode found
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php
                                            $colnum = 1;
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['zip_code']) ?></td>
                                                <td><?= htmlspecialchars($row['city']) ?></td>
                                                <td><?= htmlspecialchars($row['state_name']) ?></td>

                                                <td>
                                                    <button type="button"
                                                        class="btn btn-soft-primary rounded-pill btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPostcodeModal<?= $row['zip_code'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="postcode">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['zip_code']) ?>">
                                                        <button type="submit"
                                                            class="btn btn-soft-danger rounded-pill btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade"
                                                        id="editPostcodeModal<?= $row['zip_code'] ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Postcode</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table"
                                                                            value="postcode">
                                                                        <input type="hidden" name="zip_code"
                                                                            value="<?= htmlspecialchars($row['zip_code']) ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Zip Code</label>
                                                                            <input type="number" name="zip_code_display"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['zip_code']) ?>"
                                                                                readonly>
                                                                            <small class="text-muted">Zip code cannot be
                                                                                changed</small>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">City</label>
                                                                            <input type="text" name="city"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['city']) ?>"
                                                                                required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">State Name</label>
                                                                            <input type="text" name="state_name"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['state_name']) ?>"
                                                                                required>
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
                                            </tr>
                                            <?php $colnum++; ?>
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
                                    <nav aria-label="University pagination">
                                        <ul class="pagination pagination-sm mb-0">
                                            <!-- Previous Page -->
                                            <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
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
                                                    href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">1</a>
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
                                                    href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
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
                                                    href="?page=<?= $totalPages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $totalPages ?></a>
                                            </li>
                                            <?php endif; ?>

                                            <!-- Next Page -->
                                            <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
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

</body>

</html>