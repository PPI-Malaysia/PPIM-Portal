<?php
// Load the new StudentDatabase class
require_once("assets/php/student-database.php");

// Credit: fill your name as the person who created this page here
$credit = "Christopher Bertrand";
$credit_footer = '
    <a href="https://github.com/Zentoboo" target="_blank">
        Christopher Bertrand
    </a>
';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Student Database | PPI Malaysia</title>
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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Student Database</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Database</a></li>
                            <li class="breadcrumb-item active">Student Database</li>
                        </ol>
                    </div>
                </div>

                <!-- User Info Display -->
                <?php 
                $userInfo = $studentDB->getUserInfo();
                ?>

                <!-- Navigation Card -->
                <div class="row">
                    <div class="col">
                        <div class="card mb-3">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">Database Tables Navigation</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-navigation d-flex flex-wrap gap-2">
                                    <a href="#university_type" class="btn btn-outline-primary btn-sm">University
                                        Types</a>
                                    <a href="#qualification_level" class="btn btn-outline-primary btn-sm">Qualification
                                        Levels</a>
                                    <a href="#student_status" class="btn btn-outline-primary btn-sm">Student Status</a>
                                    <a href="#postcode" class="btn btn-outline-primary btn-sm">Postcodes</a>
                                    <a href="#university" class="btn btn-outline-primary btn-sm">Universities</a>
                                    <a href="#student" class="btn btn-outline-primary btn-sm">Students</a>
                                    <a href="#ppim" class="btn btn-outline-primary btn-sm">PPIM</a>
                                    <a href="#ppi_campus" class="btn btn-outline-primary btn-sm">PPI Campus</a>
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
                                                <button type="submit" class="btn btn-success">Add University
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
                                                <button type="submit" class="btn btn-success">Add Qualification
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
                                                <button type="submit" class="btn btn-success">Add Student
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

                <!-- Postcodes Section -->
                <div id="postcode" class="row table-section">
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
                                                <button type="submit" class="btn btn-success">Add Postcode</button>
                                            </div>
                                        </form>
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
                                            <?php
                                            $data = $studentDB->getTableData('postcode');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['zip_code']) ?></td>
                                                <td><?= htmlspecialchars($row['city']) ?></td>
                                                <td><?= htmlspecialchars($row['state_name']) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPostcodeModal<?= $row['zip_code'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="postcode">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['zip_code']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
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

                <!-- Universities CRUD -->
                <div id="university" class="row table-section">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">Universities</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addUniversity">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Add Form (Collapsible) -->
                                <div class="collapse mb-3" id="addUniversity">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New University</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="university">
                                            <div class="col-md-4">
                                                <label class="form-label">University Name</label>
                                                <input type="text" name="university_name" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Address</label>
                                                <textarea name="address" class="form-control" rows="1"></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Type</label>
                                                <select name="type_id" class="form-select" required>
                                                    <option value="">Select Type</option>
                                                    <?php
                                                    $types = $studentDB->getDropdownOptions('university_type', 'type_id', 'type_name');
                                                    foreach ($types as $type): ?>
                                                    <option value="<?= htmlspecialchars($type['type_id']) ?>">
                                                        <?= htmlspecialchars($type['type_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Postcode</label>
                                                <select name="postcode_id" class="form-select">
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
                                                <label class="form-label">Status</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">Add University</button>
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
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Type</th>
                                                <th>Postcode</th>
                                                <th>Active</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $studentDB->getTableDataWithJoins('university');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['university_id']) ?></td>
                                                <td><?= htmlspecialchars($row['university_name']) ?></td>
                                                <td><?= htmlspecialchars($row['address'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['type_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['zip_code'] ?? '') ?> -
                                                    <?= htmlspecialchars($row['city'] ?? '') ?></td>
                                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editUniversityModal<?= $row['university_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="university">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['university_id']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade"
                                                        id="editUniversityModal<?= $row['university_id'] ?>"
                                                        tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit University</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table"
                                                                            value="university">
                                                                        <input type="hidden" name="university_id"
                                                                            value="<?= htmlspecialchars($row['university_id']) ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">University
                                                                                Name</label>
                                                                            <input type="text" name="university_name"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['university_name']) ?>"
                                                                                required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Address</label>
                                                                            <textarea name="address"
                                                                                class="form-control"
                                                                                rows="3"><?= htmlspecialchars($row['address'] ?? '') ?></textarea>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Type</label>
                                                                            <select name="type_id" class="form-select"
                                                                                required>
                                                                                <option value="">Select Type</option>
                                                                                <?php
                                                                                $types = $studentDB->getDropdownOptions('university_type', 'type_id', 'type_name');
                                                                                foreach ($types as $type): ?>
                                                                                <option
                                                                                    value="<?= htmlspecialchars($type['type_id']) ?>"
                                                                                    <?= $row['type_id'] == $type['type_id'] ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($type['type_name']) ?>
                                                                                </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Postcode</label>
                                                                            <select name="postcode_id"
                                                                                class="form-select">
                                                                                <option value="">Select Postcode
                                                                                </option>
                                                                                <?php
                                                                                $postcodes = $studentDB->getDropdownOptions('postcode', 'zip_code', 'city');
                                                                                foreach ($postcodes as $postcode): ?>
                                                                                <option
                                                                                    value="<?= htmlspecialchars($postcode['zip_code']) ?>"
                                                                                    <?= $row['postcode_id'] == $postcode['zip_code'] ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($postcode['zip_code']) ?>
                                                                                    -
                                                                                    <?= htmlspecialchars($postcode['city']) ?>
                                                                                </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select name="is_active"
                                                                                class="form-select">
                                                                                <option value="1"
                                                                                    <?= $row['is_active'] ? 'selected' : '' ?>>
                                                                                    Active</option>
                                                                                <option value="0"
                                                                                    <?= !$row['is_active'] ? 'selected' : '' ?>>
                                                                                    Inactive</option>
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students CRUD -->
                <div id="student" class="row table-section">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">Students</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addStudent">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
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
                                                <label class="form-label">Postcode</label>
                                                <select name="postcode_id" class="form-select">
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
                                                <button type="submit" class="btn btn-success">Add Student</button>
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
                                                <th>Full Name</th>
                                                <th>University</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Degree</th>
                                                <th>Active</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $studentDB->getTableDataWithJoins('student');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                                <td><?= htmlspecialchars($row['university_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                                <td><span
                                                        class="badge bg-<?= $row['status_id'] == 1 ? 'success' : ($row['status_id'] == 2 ? 'primary' : 'secondary') ?>"><?= htmlspecialchars($row['status_name'] ?? '') ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($row['degree'] ?? '') ?></td>
                                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editStudentModal<?= $row['student_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="student">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['student_id']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PPIM CRUD -->
                <div id="ppim" class="row table-section">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">PPIM</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addPpim">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Add Form -->
                                <div class="collapse mb-3" id="addPpim">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New PPIM Record</h5>
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
                                                <button type="submit" class="btn btn-success">Add PPIM Record</button>
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
                                                <th>Student Name</th>
                                                <th>Start Year</th>
                                                <th>End Year</th>
                                                <th>Department</th>
                                                <th>Position</th>
                                                <th>Active</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $studentDB->getTableDataWithJoins('ppim');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['ppim_id']) ?></td>
                                                <td><?= htmlspecialchars($row['fullname'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['start_year']) ?></td>
                                                <td><?= htmlspecialchars($row['end_year'] ?? 'Current') ?></td>
                                                <td><?= htmlspecialchars($row['department']) ?></td>
                                                <td><?= htmlspecialchars($row['position'] ?? '') ?></td>
                                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPpimModal<?= $row['ppim_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="ppim">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['ppim_id']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
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
                                                                    <h5 class="modal-title">Edit PPIM Record</h5>
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
                                                                                <option value="1"
                                                                                    <?= $row['is_active'] ? 'selected' : '' ?>>
                                                                                    Active</option>
                                                                                <option value="0"
                                                                                    <?= !$row['is_active'] ? 'selected' : '' ?>>
                                                                                    Inactive</option>
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PPI Campus CRUD -->
                <div id="ppi_campus" class="row table-section">
                    <div class="col">
                        <div class="card mb-4">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">PPI Campus</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#addPpiCampus">
                                    <i class="ti ti-plus fs-16"></i> Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Add Form -->
                                <div class="collapse mb-3" id="addPpiCampus">
                                    <div class="card card-body">
                                        <h5 class="card-title">Add New PPI Campus Record</h5>
                                        <form method="POST" class="row g-3">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="table" value="ppi_campus">
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
                                            <div class="col-md-6">
                                                <label class="form-label">University</label>
                                                <select name="university_id" class="form-select" required>
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
                                                <input type="text" name="department" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Position</label>
                                                <input type="text" name="position" class="form-control"
                                                    placeholder="President, Member">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">Add PPI Campus
                                                    Record</button>
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
                                                <th>Student Name</th>
                                                <th>University</th>
                                                <th>Start Year</th>
                                                <th>End Year</th>
                                                <th>Position</th>
                                                <th>Active</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $studentDB->getTableDataWithJoins('ppi_campus');
                                            foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['ppi_campus_id']) ?></td>
                                                <td><?= htmlspecialchars($row['fullname'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['university_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['start_year']) ?></td>
                                                <td><?= htmlspecialchars($row['end_year'] ?? 'Current') ?></td>
                                                <td><?= htmlspecialchars($row['position'] ?? '') ?></td>
                                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPpiCampusModal<?= $row['ppi_campus_id'] ?>">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="ppi_campus">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['ppi_campus_id']) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade"
                                                        id="editPpiCampusModal<?= $row['ppi_campus_id'] ?>"
                                                        tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit PPI Campus Record</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action"
                                                                            value="update">
                                                                        <input type="hidden" name="table"
                                                                            value="ppi_campus">
                                                                        <input type="hidden" name="ppi_campus_id"
                                                                            value="<?= htmlspecialchars($row['ppi_campus_id']) ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Student</label>
                                                                            <input type="text" class="form-control"
                                                                                value="<?= htmlspecialchars($row['fullname'] ?? '') ?>"
                                                                                readonly>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">University</label>
                                                                            <input type="text" class="form-control"
                                                                                value="<?= htmlspecialchars($row['university_name'] ?? '') ?>"
                                                                                readonly>
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
                                                                                value="<?= htmlspecialchars($row['department'] ?? '') ?>">
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
                                                                                <option value="1"
                                                                                    <?= $row['is_active'] ? 'selected' : '' ?>>
                                                                                    Active</option>
                                                                                <option value="0"
                                                                                    <?= !$row['is_active'] ? 'selected' : '' ?>>
                                                                                    Inactive</option>
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