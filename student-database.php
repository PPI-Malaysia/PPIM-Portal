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
    <title>Students | PPI Malaysia</title>
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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Students Management</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">University Database</a></li>
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