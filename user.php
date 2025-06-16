<?php
// load main functions
require_once("assets/php/user.php");
$main = new User();

// Credit: fill your name as the person who created this page here
$credit = "Rafi Daffa Ramadhani";
$credit_footer = '
<a href="https://www.linkedin.com/in/rafi-daffa/" target="_blank">Rafi Daffa</a>
';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>User List | PPI Malaysia</title>
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

                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">User List</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Super Admin</a></li>
                            <li class="breadcrumb-item active">User List</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">User Account List</h4>
                                <div>
                                    <a class="topbar-link btn btn-outline-secondary btn-icon"
                                        style="margin-right: 10px;" href="user_types.php" title="Manage User Types">
                                        <i class="ti ti-settings fs-22"></i>
                                    </a>
                                    <a class="topbar-link btn btn-outline-primary btn-icon" style="margin-right: 10px;"
                                        type="button" href="javascript:window.location.reload(true)">
                                        <i class="ti ti-refresh fs-22"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addUser">Add User</button>
                                </div>
                            </div>

                            <!-- Add User modal content -->
                            <div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="addUserModalLabel">Add User Account</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addUserForm" action="assets/php/page/add_user.php" method="post">
                                                <div class="mb-3">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input type="text" id="username" class="form-control"
                                                        name="username" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="user-type" class="form-label">User Type</label>
                                                    <select class="form-select" id="user-type" name="usertype" required>
                                                        <option value="">Select User Type</option>
                                                        <?php
                                                        $userTypes = $main->getUserTypes();
                                                        foreach ($userTypes as $userType) {
                                                            echo '<option value="' . $userType['id'] . '">' . 
                                                                 htmlspecialchars($userType['name']) . ' (ID: ' . $userType['id'] . ')</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <!-- Hidden password field that will be set by JavaScript -->
                                                <input type="hidden" id="password" name="password">
                                                <button type="submit" class="btn btn-primary">Create Account</button>
                                            </form>
                                            <div id="result-container" class="mt-3" style="display:none;">
                                                <div class="alert alert-success">
                                                    <h5>Account Created Successfully</h5>
                                                    <p><strong>Username:</strong> <span id="result-username"></span></p>
                                                    <p><strong>Password:</strong> <span id="result-password"></span></p>
                                                    <p><strong>User Type:</strong> <span id="result-usertype"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->

                            <!-- Edit User Modal -->
                            <div class="modal fade" id="editUser" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Edit User Account</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="editUserForm" action="assets/php/page/edit_user.php"
                                                method="post">
                                                <input type="hidden" id="edit-user-id" name="user_id">

                                                <div class="mb-3">
                                                    <label for="edit-username" class="form-label">Username</label>
                                                    <input type="text" id="edit-username" class="form-control"
                                                        name="username" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="edit-user-type" class="form-label">User Type</label>
                                                    <select class="form-select" id="edit-user-type" name="usertype"
                                                        required>
                                                        <option value="">Select User Type</option>
                                                        <?php
                                                        $userTypes = $main->getUserTypes();
                                                        foreach ($userTypes as $userType) {
                                                            echo '<option value="' . $userType['id'] . '">' . 
                                                                 htmlspecialchars($userType['name']) . ' (ID: ' . $userType['id'] . ')</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="reset-password" name="reset_password">
                                                        <label class="form-check-label" for="reset-password">
                                                            Reset Password (Generate new random password)
                                                        </label>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary">Update User</button>
                                            </form>

                                            <div id="edit-result-container" class="mt-3" style="display:none;">
                                                <div class="alert alert-success">
                                                    <h5>User Updated Successfully</h5>
                                                    <p><strong>Username:</strong> <span
                                                            id="edit-result-username"></span></p>
                                                    <p><strong>User Type:</strong> <span
                                                            id="edit-result-usertype"></span></p>
                                                    <div id="new-password-section" style="display:none;">
                                                        <p><strong>New Password:</strong> <span
                                                                id="edit-result-password"></span></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.modal -->

                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="text-muted mb-0">
                                        Manage user accounts and their access levels.
                                        <a href="user_types.php" class="link-primary">Manage User Types</a> to customize
                                        permissions.
                                    </p>
                                </div>

                                <!-- Dynamic User Type Legend -->
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">User Types:</h6>
                                    <?php
                                    $userTypes = $main->getUserTypes();
                                    foreach ($userTypes as $userType) {
                                        $permissions = $main->getUserTypePermissions($userType['id']);
                                        $permissionCount = count($permissions);
                                        echo '<div class="mb-1">
                                                <span class="badge bg-primary me-2">' . $userType['id'] . '</span>
                                                <strong>' . htmlspecialchars($userType['name']) . '</strong>: ' . 
                                                htmlspecialchars($userType['description']) . ' (' . $permissionCount . ' permissions)
                                              </div>';
                                    }
                                    ?>
                                    <div class="mb-1">
                                        <span class="badge bg-danger me-2">999</span>
                                        <strong>Super Admin</strong>: Full system access (All permissions)
                                    </div>
                                </div>

                                <p class="d-block d-md-none">Note: Please use bigger device to edit user information!
                                </p>

                                <div class="table-responsive-sm">
                                    <table class="table table-striped w-100">
                                        <thead>
                                            <tr>
                                                <th style="width: 30%;">Username</th>
                                                <th style="width: 40%;">User Type</th>
                                                <th style="width: 30%;" class="d-none d-md-table-cell">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($main->getUsers() as $user) {
                                                if ($user['type'] == 999) {
                                                    $edit = "-";
                                                    $delete = "-";
                                                    $useracctype = '<span class="badge bg-danger">Super Admin</span>';
                                                } else {
                                                    $edit = '<button class="btn btn-sm btn-outline-primary me-1" onclick="editUser(' . $user['id'] . ')">
                                                                <i class="ti ti-edit"></i> Edit
                                                            </button>';
                                                    $delete = '<button class="btn btn-sm btn-outline-danger" onclick="deleteUser(' . $user['id'] . ', \'' . htmlspecialchars($user['name'], ENT_QUOTES) . '\')">
                                                                <i class="ti ti-trash"></i> Delete
                                                            </button>';
                                                    $typeName = $user['type_name'] ? htmlspecialchars($user['type_name']) : 'Type ' . $user['type'];
                                                    $useracctype = '<span class="badge bg-primary me-1">' . $user['type'] . '</span>' . $typeName;
                                                }
                                                echo '
                                                <tr>
                                                    <td>' . htmlspecialchars($user['name']) . '</td>
                                                    <td>' . $useracctype . '</td>
                                                    <td class="d-none d-md-table-cell">
                                                        <div class="d-flex gap-2">
                                                            ' . $edit . '' . $delete . '
                                                        </div>
                                                    </td>
                                                </tr>
                                                ';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div> <!-- end table-responsive-->
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->

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
    <script>
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Generate random password (12 characters including special chars)
        const generatePassword = () => {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            const specialChars = '!@#$%^&*()-_=+[]{}|;:,.<>?';
            let password = '';

            // Add 10 regular characters
            for (let i = 0; i < 10; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            // Add 2 special characters at random positions
            for (let i = 0; i < 2; i++) {
                const specialChar = specialChars.charAt(Math.floor(Math.random() * specialChars.length));
                const position = Math.floor(Math.random() * (password.length + 1));
                password = password.slice(0, position) + specialChar + password.slice(position);
            }

            return password;
        };

        // Generate password and set it in the hidden field
        const password = generatePassword();
        document.getElementById('password').value = password;

        // Get form data
        const formData = new FormData(this);
        const username = formData.get('username');
        const usertype = formData.get('usertype');
        const usertypeText = document.getElementById('user-type').selectedOptions[0].text;

        // Submit form with AJAX
        fetch('assets/php/page/add_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display result
                    document.getElementById('result-username').textContent = username;
                    document.getElementById('result-password').textContent = password;
                    document.getElementById('result-usertype').textContent = usertypeText;
                    document.getElementById('result-container').style.display = 'block';
                } else {
                    alert('Error: ' + (data.message || 'Failed to create user'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the user');
            });
    });
    // Edit user function
    function editUser(userId) {
        // Load user data
        fetch(`assets/php/page/get_user.php?id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit-user-id').value = userId;
                    document.getElementById('edit-username').value = data.user.name;
                    document.getElementById('edit-user-type').value = data.user.type;
                    document.getElementById('reset-password').checked = false;
                    document.getElementById('edit-result-container').style.display = 'none';

                    // Show modal
                    new bootstrap.Modal(document.getElementById('editUser')).show();
                } else {
                    alert('Error loading user data: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error loading user data');
            });
    }

    // Handle edit user form
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const resetPassword = document.getElementById('reset-password').checked;

        // Generate new password if reset is checked
        let newPassword = null;
        if (resetPassword) {
            const generatePassword = () => {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                const specialChars = '!@#$%^&*()-_=+[]{}|;:,.<>?';
                let password = '';

                for (let i = 0; i < 10; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }

                for (let i = 0; i < 2; i++) {
                    const specialChar = specialChars.charAt(Math.floor(Math.random() *
                        specialChars.length));
                    const position = Math.floor(Math.random() * (password.length + 1));
                    password = password.slice(0, position) + specialChar + password.slice(
                        position);
                }

                return password;
            };

            newPassword = generatePassword();
            formData.append('new_password', newPassword);
        }

        const username = formData.get('username');
        const usertypeText = document.getElementById('edit-user-type').selectedOptions[0].text;

        fetch('assets/php/page/edit_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display result
                    document.getElementById('edit-result-username').textContent = username;
                    document.getElementById('edit-result-usertype').textContent = usertypeText;

                    if (resetPassword && newPassword) {
                        document.getElementById('edit-result-password').textContent =
                            newPassword;
                        document.getElementById('new-password-section').style.display = 'block';
                    } else {
                        document.getElementById('new-password-section').style.display = 'none';
                    }

                    document.getElementById('edit-result-container').style.display = 'block';

                    // Reload page after 3 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    alert('Error: ' + (data.message || 'Failed to update user'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the user');
            });
    });

    // Delete user function
    function deleteUser(userId, username) {
        if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
            fetch('assets/php/page/delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete user'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error while deleting user');
                });
        }
    }
    </script>

</body>

</html>