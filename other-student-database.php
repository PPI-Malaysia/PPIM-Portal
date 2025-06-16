<?php
// Load the new StudentDatabase class
require_once("assets/php/student-database.php");

// Credit: fill your name as the person who created this page here
$credit = "Christopher Bertrand, Rafi Daffa Ramadhani";
$credit_footer = '
    <a href="https://github.com/Zentoboo" target="_blank">
        Christopher Bertrand
    </a>
    <a href="https://rafidaffa.com" target="_blank">
        Rafi Daffa
    </a>
';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Other Settings | PPI Malaysia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo htmlspecialchars($credit); ?>" name="author" />

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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Other Settings Management</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Student Database</a></li>
                            <li class="breadcrumb-item active">Others</li>
                        </ol>
                    </div>
                </div>

                <!-- Navigation Card -->
                <div class="row">
                    <div class="col">
                        <div class="card mb-3">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">Navigation</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-navigation d-flex flex-wrap gap-2">
                                    <a href="#university_type" class="btn btn-outline-secondary btn-sm">University
                                        Types</a>
                                    <a href="#qualification_level"
                                        class="btn btn-outline-secondary btn-sm">Qualification Levels</a>
                                    <a href="#student_status" class="btn btn-outline-secondary btn-sm">Student
                                        Status</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- University Types Section -->
                <div id="university_type" class="row table-section">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">University Types</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addUniversityType">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Add Form (Collapsible) -->
                                <div class="collapse mb-3" id="addUniversityType">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New University Type</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="university_type">
                                            <div class="col-md-6">
                                                <label class="form-label">Type Name</label>
                                                <input type="text" name="type_name" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="1"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-secondary">Add University
                                                    Type</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Type Name</th>
                                                <th>Description</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $studentDB->getTableData('university_type');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['type_id']) ?></td>
                                                <td><?= htmlspecialchars($row['type_name']) ?></td>
                                                <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editUniversityTypeModal<?= $row['type_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="university_type">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['type_id']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade"
                                                        id="editUniversityTypeModal<?= $row['type_id'] ?>"
                                                        tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit University Type</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table"
                                                                            value="university_type">
                                                                        <input type="hidden" name="type_id"
                                                                            value="<?= htmlspecialchars($row['type_id']) ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Type Name</label>
                                                                            <input type="text" name="type_name"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['type_name']) ?>"
                                                                                required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label
                                                                                class="form-label">Description</label>
                                                                            <textarea name="description"
                                                                                class="form-control"
                                                                                rows="3"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Qualification Levels Section -->
                <div id="qualification_level" class="row table-section">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">Qualification Levels</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addQualificationLevel">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Add Form (Collapsible) -->
                                <div class="collapse mb-3" id="addQualificationLevel">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New Qualification Level</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="qualification_level">
                                            <div class="col-md-4">
                                                <label class="form-label">Level Name</label>
                                                <input type="text" name="level_name" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Level Order</label>
                                                <input type="number" name="level_order" class="form-control" required
                                                    min="1">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="1"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-secondary">Add Qualification
                                                    Level</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Level Name</th>
                                                <th>Order</th>
                                                <th>Description</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $studentDB->getTableData('qualification_level');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['level_id']) ?></td>
                                                <td><?= htmlspecialchars($row['level_name']) ?></td>
                                                <td><?= htmlspecialchars($row['level_order']) ?></td>
                                                <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editQualificationModal<?= $row['level_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="qualification_level">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['level_id']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade"
                                                        id="editQualificationModal<?= $row['level_id'] ?>"
                                                        tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Qualification Level
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table"
                                                                            value="qualification_level">
                                                                        <input type="hidden" name="level_id"
                                                                            value="<?= htmlspecialchars($row['level_id']) ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Level Name</label>
                                                                            <input type="text" name="level_name"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['level_name']) ?>"
                                                                                required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Level
                                                                                Order</label>
                                                                            <input type="number" name="level_order"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['level_order']) ?>"
                                                                                required min="1">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label
                                                                                class="form-label">Description</label>
                                                                            <textarea name="description"
                                                                                class="form-control"
                                                                                rows="3"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Status Section -->
                <div id="student_status" class="row table-section">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">Student Status</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addStudentStatus">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Add Form (Collapsible) -->
                                <div class="collapse mb-3" id="addStudentStatus">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New Student Status</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="student_status">
                                            <div class="col-md-6">
                                                <label class="form-label">Status Name</label>
                                                <input type="text" name="status_name" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="1"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-secondary">Add Student
                                                    Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Status Name</th>
                                                <th>Description</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $studentDB->getTableData('student_status');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['status_id']) ?></td>
                                                <td><?= htmlspecialchars($row['status_name']) ?></td>
                                                <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editStatusModal<?= $row['status_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="student_status">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['status_id']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editStatusModal<?= $row['status_id'] ?>"
                                                        tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Student Status</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table"
                                                                            value="student_status">
                                                                        <input type="hidden" name="status_id"
                                                                            value="<?= htmlspecialchars($row['status_id']) ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Status
                                                                                Name</label>
                                                                            <input type="text" name="status_name"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['status_name']) ?>"
                                                                                required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label
                                                                                class="form-label">Description</label>
                                                                            <textarea name="description"
                                                                                class="form-control"
                                                                                rows="3"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
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
                                        </tbody>
                                    </table>
                                </div>
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
        <script src="assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="assets/js/app.js"></script>

        <!-- Toast notification js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.js"
            integrity="sha512-MnKz2SbnWiXJ/e0lSfSzjaz9JjJXQNb2iykcZkEY2WOzgJIWVqJBFIIPidlCjak0iTH2bt2u1fHQ4pvKvBYy6Q=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Custom js -->
        <script src="assets/js/database-nav.js"></script>

</body>

</html>