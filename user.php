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
                                                        name="username">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="user-type" class="form-label">User Type</label>
                                                    <select class="form-select" id="user-type" name="usertype">
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
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
                            <div class="card-body">
                                <p class="text-muted">
                                    User Type 1 can access <code>Dashboard</code>, <code>Form</code>,
                                    <code>Ticketing</code>, <code>Calendar</code>, and <code>pengurus PPIM</code>
                                    <br>
                                    User Type 2 can access <code>Type 1 access</code> and <code>menfess</code>
                                    <br>
                                    User Type 3 can access <code>Type 2 access</code>,
                                    <code>View Student Database</code>,
                                    <code>View All Form</code>, and
                                    <code>View All Ticketing</code>
                                    <br>
                                    User Type 4 can access <code>Type 3 access</code>, <code>Edit All Form</code>, and
                                    <code>Edit All Ticketing</code>
                                    <br>
                                    User Type 5 can access <code>Type 4 access</code> and
                                    <code>Edit Student Database</code>
                                    <br>
                                    Super Admin can access <code>All Content</code>
                                </p>
                                <p class="d-block d-md-none">Note: Please use bigger device to edit device!</p>
                                <div class="table-responsive-sm">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>User Type</th>
                                                <th>Edit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($main->getUsers() as $user){
                                                if ($user['type'] == 6){
                                                    $edit = "-";
                                                    $useracctype = "Super Admin";
                                                } else {
                                                    $edit = '<a href="#" class="link-secondary d-none d-md-block">Edit</a>';
                                                    $useracctype = "Type ".$user['type'];
                                                }
                                                echo '
                                                <tr>
                                                    <td>'.$user['name'].'</td>
                                                    <td>'.$useracctype.'</td>
                                                    <td>'.$edit.'</td>
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

    <!-- Page js -->
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
                    document.getElementById('result-usertype').textContent = usertype;
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
    </script>

</body>

</html>