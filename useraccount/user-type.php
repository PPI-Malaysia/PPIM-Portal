<?php
// load main functions
require_once("../assets/php/user.php");
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
    <title>User Type Management | PPI Malaysia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $credit; ?>" name="author" />

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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">User Type Management</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Super Admin</a></li>
                            <li class="breadcrumb-item active">User Types</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">User Type Management</h4>
                                <div>
                                    <a class="topbar-link btn btn-outline-primary btn-icon" style="margin-right: 10px;"
                                        type="button" href="javascript:window.location.reload(true)">
                                        <i class="ti ti-refresh fs-22"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addUserType">Create User Type</button>
                                </div>
                            </div>

                            <!-- Create User Type Modal -->
                            <div class="modal fade" id="addUserType" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Create New User Type</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addUserTypeForm" action="assets/php/page/add_user_type.php"
                                                method="post">
                                                <div class="mb-3">
                                                    <label for="usertype-name" class="form-label">User Type Name</label>
                                                    <input type="text" id="usertype-name" class="form-control"
                                                        name="usertype_name" required>
                                                    <div class="form-text">User Type ID will be automatically assigned
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="usertype-description"
                                                        class="form-label">Description</label>
                                                    <textarea id="usertype-description" class="form-control"
                                                        name="usertype_description" rows="3"></textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Permissions</label>
                                                    <div class="row">
                                                        <?php 
                                                        $permissions = $main->getPermissions();
                                                        foreach ($permissions as $category => $categoryPermissions): 
                                                        ?>
                                                        <div class="col-md-6 mb-3">
                                                            <div class="border rounded p-3">
                                                                <h6 class="text-uppercase fw-bold mb-2">
                                                                    <?php echo ucfirst(str_replace('_', ' ', $category)); ?>
                                                                </h6>
                                                                <?php foreach ($categoryPermissions as $permission): ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="permissions[]"
                                                                        value="<?php echo $permission['id']; ?>"
                                                                        id="perm_<?php echo $permission['id']; ?>">
                                                                    <label class="form-check-label"
                                                                        for="perm_<?php echo $permission['id']; ?>">
                                                                        <?php echo $permission['description']; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary">Create User Type</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <p class="text-muted mb-4">
                                    Manage user types and their permissions. Each user type can have specific
                                    permissions for different features.
                                </p>

                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Permissions Count</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $userTypes = $main->getUserTypes();
                                            foreach ($userTypes as $userType) {
                                                $permissions = $main->getUserTypePermissions($userType['id']);
                                                $permissionCount = count($permissions);
                                                $isCampus = $userType['id'] == 1000;
                                                echo '
                                                <tr>
                                                    <td><span class="badge bg-primary">' . $userType['id'] . '</span></td>
                                                    <td>' . htmlspecialchars($userType['name']) . '</td>
                                                    <td>' . htmlspecialchars($userType['description']) . '</td>
                                                ';
                                                echo $isCampus?  '
                                                    <td> - </td>
                                                    <td> - </td>
                                                </tr>
                                                ' :  '
                                                    <td>' . $permissionCount . ' permissions</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="editUserType(' . $userType['id'] . ')">
                                                            <i class="ti ti-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteUserType(' . $userType['id'] . ')">
                                                            <i class="ti ti-trash"></i> Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                                ';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit User Type Modal -->
                <div class="modal fade" id="editUserType" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit User Type</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editUserTypeForm" action="assets/php/page/edit_user_type.php" method="post">
                                    <input type="hidden" id="edit-usertype-id" name="usertype_id">

                                    <div class="mb-3">
                                        <label class="form-label">Permissions</label>
                                        <div class="row" id="edit-permissions-container">
                                            <!-- Permissions will be loaded here -->
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Update Permissions</button>
                                </form>
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
                                </script> © <?php echo $credit_footer; ?> - Pusdatin PPIM 2024/2025
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-end footer-links d-none d-md-block">
                                    <a href="javascript: void(0);">About</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Theme Settings -->
    <?php $main->renderTheme(); ?>

    <!-- Vendor js -->
    <script src="../assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../assets/js/app.js"></script>

    <script>
    // Load next available ID when modal opens
    document.getElementById('addUserType').addEventListener('show.bs.modal', function() {
        fetch('assets/php/page/get_next_user_type_id.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('next-id-preview').innerHTML =
                        `(Next ID will be: <strong>${data.next_id}</strong>)`;
                } else {
                    document.getElementById('next-id-preview').innerHTML = `(${data.message})`;
                }
            })
            .catch(() => {
                document.getElementById('next-id-preview').innerHTML = '';
            });
    });

    // Handle create user type form
    document.getElementById('addUserTypeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('assets/php/page/add_user_type.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`User type created successfully with ID: ${data.id}`);
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to create user type'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the user type');
            });
    });

    // Edit user type function
    function editUserType(userTypeId) {
        console.log('Editing user type:', userTypeId); // Debug log

        // Load current permissions for this user type
        fetch(`assets/php/page/get_user_type_permissions.php?id=${userTypeId}`)
            .then(response => {
                console.log('Response status:', response.status); // Debug log
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Debug log

                if (data.success) {
                    document.getElementById('edit-usertype-id').value = userTypeId;

                    // Build permissions HTML
                    let permissionsHtml = '';

                    if (data.permissions && typeof data.permissions === 'object') {
                        Object.keys(data.permissions).forEach(category => {
                            // Format category name
                            const categoryDisplay = category.replace(/_/g, ' ').replace(/\b\w/g, l => l
                                .toUpperCase());

                            permissionsHtml += `
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3">
                                    <h6 class="text-uppercase fw-bold mb-2">${categoryDisplay}</h6>
                        `;

                            if (Array.isArray(data.permissions[category])) {
                                data.permissions[category].forEach(permission => {
                                    const checked = data.userPermissions.includes(parseInt(
                                        permission.id)) ? 'checked' : '';
                                    permissionsHtml += `
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="permissions[]" value="${permission.id}" 
                                               id="edit_perm_${permission.id}" ${checked}>
                                        <label class="form-check-label" for="edit_perm_${permission.id}">
                                            ${permission.description}
                                        </label>
                                    </div>
                                `;
                                });
                            }

                            permissionsHtml += '</div></div>';
                        });
                    } else {
                        permissionsHtml =
                            '<div class="col-12"><p class="text-danger">Error loading permissions</p></div>';
                    }

                    document.getElementById('edit-permissions-container').innerHTML = permissionsHtml;

                    // Show modal
                    new bootstrap.Modal(document.getElementById('editUserType')).show();
                } else {
                    alert('Error loading user type permissions: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error loading user type permissions');
            });
    }

    // Handle edit user type form
    document.getElementById('editUserTypeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('assets/php/page/edit_user_type.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User type updated successfully!');
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editUserType')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update user type'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error while updating user type');
            });
    });

    // Delete user type function
    function deleteUserType(userTypeId) {
        if (confirm('Are you sure you want to delete this user type? This action cannot be undone.')) {
            fetch('assets/php/page/delete_user_type.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        usertype_id: userTypeId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User type deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete user type'));
                    }
                });
        }
    }
    </script>

</body>

</html>