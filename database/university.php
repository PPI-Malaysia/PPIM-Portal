<?php
// Load the new StudentDatabase class
require_once("../assets/php/student-database.php");

// Credit: fill your name as the person who created this page here
$credit = "Christopher Bertrand,  Rafi Daffa Ramadhani";
$credit_footer = '
    <a href="https://github.com/Zentoboo" target="_blank">
        Christopher Bertrand
    </a>and
    <a href="https://rafidaffa.com" target="_blank">
        Rafi Daffa
    </a>
';

// Pagination and search parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get paginated data and total count
$data = $studentDB->getPaginatedTableDataWithJoins('university', $page, $limit, $search);
$totalRecords = $studentDB->getTotalCount('university', $search);
$totalPages = ceil($totalRecords / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Universities | PPI Malaysia</title>
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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Universities Management</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Student Database</a></li>
                            <li class="breadcrumb-item active">Universities</li>
                        </ol>
                    </div>
                </div>

                <!-- Universities CRUD -->
                <div id="university" class="row">
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
                                                <label class="form-label">University Name*</label>
                                                <input type="text" name="university_name" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="phone_num" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Address*</label>
                                                <textarea name="address" class="form-control" rows="1"></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Type*</label>
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
                                                <label class="form-label">Status*</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-secondary">Add University</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Search Form -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <form method="GET" class="d-flex">
                                            <input type="text" name="search" class="form-control me-2"
                                                placeholder="Search universities..."
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
                                            Showing <?= count($data) ?> of <?= $totalRecords ?> universities
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
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Address</th>
                                                <th>Type</th>
                                                <th>Postcode</th>
                                                <th>Active</th>
                                                <th width="200">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($data)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="ti ti-search fs-24 mb-2"></i>
                                                        <p class="mb-0">
                                                            <?php if (!empty($search)): ?>
                                                            No universities found matching
                                                            "<?= htmlspecialchars($search) ?>"
                                                            <?php else: ?>
                                                            No universities found
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
                                                <td><?= $colnum ?></td>
                                                <td><?= htmlspecialchars($row['university_name']) ?></td>
                                                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['phone_num'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['address'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['type_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['zip_code'] ?? '') ?> -
                                                    <?= htmlspecialchars($row['city'] ?? '') ?></td>
                                                <td><?= $row['is_active'] ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-soft-secondary rounded-pill btn-sm me-1"
                                                        data-bs-toggle="modal" data-bs-target="#ppiaccountmodal"
                                                        data-id="<?= $row['university_id']; ?>">
                                                        <i class="ti ti-user"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-soft-primary rounded-pill btn-sm me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editUniversityModal<?= $row['university_id'] ?>">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="table" value="university">
                                                        <input type="hidden" name="id"
                                                            value="<?= htmlspecialchars($row['university_id']) ?>">
                                                        <button type="submit"
                                                            class="btn btn-soft-danger rounded-pill btn-sm"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="ti ti-trash"></i>
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
                                                                            <label class="form-label">Email</label>
                                                                            <input type="email" name="email"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['email']) ?>">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Phone
                                                                                Number</label>
                                                                            <input type="text" name="phone_num"
                                                                                class="form-control"
                                                                                value="<?= htmlspecialchars($row['phone_num']) ?>">
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
                                                                        <button type="button" class="btn btn-primary"
                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit"
                                                                            class="btn btn-secondary">Update</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- user account modal -->
                                                    <div class="modal fade" id="ppiaccountmodal" tabindex="-1"
                                                        aria-labelledby="ppiaccountmodalLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="ppiaccountmodalLabel">
                                                                        PPI Campus Account Credential
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body" id="modal-body-content">
                                                                    <!-- Content will be loaded here -->
                                                                    <div class="text-center">
                                                                        <div class="spinner-border" role="status">
                                                                            <span
                                                                                class="visually-hidden">Loading...</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
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

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('ppiaccountmodal');
            const modalBody = document.getElementById('modal-body-content');

            modal.addEventListener('show.bs.modal', function(event) {
                // Button that triggered the modal
                const button = event.relatedTarget;

                // Get the data-id attribute
                const dataId = button.getAttribute('data-id');

                // Show loading spinner
                modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

                // Build the URL with the ID parameter
                const phpUrl = `../assets/php/page/ppi_campus_account_modal.php?id=${dataId}`;

                // Fetch content from PHP file
                fetch(phpUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(data => {
                        modalBody.innerHTML = data;

                        // ✅ Attach submit handler after content is loaded
                        const form = document.getElementById('addPPICampusAccForm');
                        if (!form) return;

                        form.addEventListener('submit', function(e) {
                            e.preventDefault();

                            // Generate secure password
                            const generatePassword = () => {
                                const chars =
                                    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                                const specialChars = '!@#$%^&*()-_=+[]{}|;:,.<>?';
                                let password = '';

                                for (let i = 0; i < 10; i++) {
                                    password += chars.charAt(Math.floor(Math.random() *
                                        chars.length));
                                }

                                for (let i = 0; i < 2; i++) {
                                    const specialChar = specialChars.charAt(Math.floor(
                                        Math.random() * specialChars.length));
                                    const position = Math.floor(Math.random() * (
                                        password.length + 1));
                                    password = password.slice(0, position) +
                                        specialChar + password.slice(position);
                                }

                                return password;
                            };

                            const password = generatePassword();
                            document.getElementById('password').value = password;

                            const formData = new FormData(form);
                            const username = formData.get('username');
                            const usertypeText = document.getElementById('user-type')
                                .selectedOptions[0].text;

                            fetch(form.action, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        document.getElementById('result-username')
                                            .textContent = username;
                                        document.getElementById('result-password')
                                            .textContent = password;
                                        document.getElementById('result-usertype')
                                            .textContent = usertypeText;
                                        document.getElementById('result-container')
                                            .style.display = 'block';
                                    } else {
                                        alert('Error: ' + (data.message ||
                                            'Failed to create user'));
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred while creating the user');
                                });
                        });
                    })
                    .catch(error => {
                        modalBody.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        Error loading content: ${error.message}
                    </div>
                `;
                    });
            });
        });
        </script>

</body>

</html>