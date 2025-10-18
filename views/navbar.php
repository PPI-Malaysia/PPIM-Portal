<!-- Sidenav Menu Start -->
<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="index.html" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="../assets/images/logo.png" alt="logo"></span>
            <span class="logo-sm text-center"><img src="../assets/images/logo-sm.png" alt="small logo"></span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg"><img src="../assets/images/logo-dark.png" alt="dark logo"></span>
            <span class="logo-sm text-center"><img src="../assets/images/logo-sm.png" alt="small logo"></span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-sm-hover">
        <i class="ti ti-circle align-middle"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-fullsidebar">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div data-simplebar>

        <!--- Sidenav Menu -->
        <ul class="side-nav">

            <li class="side-nav-item">
                <a href="../index.php" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <li class="side-nav-title mt-2">Apps & Pages</li>

            <li class="side-nav-item">
                <a href="../calendar.php" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-calendar-filled"></i></span>
                    <span class="menu-text"> Calendar </span>
                </a>
            </li>
            <!--
            <li class="side-nav-item">
                <a href="calendar.php" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-calendar-filled"></i></span>
                    <span class="menu-text"> Menfess </span>
                </a>
            </li>
-->
            <?php if ($main->hasPermission("student_db_view") || $main->hasPermission("campus_student_db_view")){ ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#studentdatabasesidebar" aria-expanded="false"
                    aria-controls="sidebarPages" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-database"></i></span>
                    <span class="menu-text"> Student Database </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="studentdatabasesidebar">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="../database/student.php" class="side-nav-link">
                                <span class="menu-text">Students</span>
                            </a>
                        </li>
                        <?php if ($main->hasPermission("student_db_view")){ ?>
                        <li class="side-nav-item">
                            <a href="../database/ppim.php" class="side-nav-link">
                                <span class="menu-text">PPIM Members</span>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="side-nav-item">
                            <a href="../database/ppi-campus.php" class="side-nav-link">
                                <span class="menu-text">PPI Campus Members</span>
                            </a>
                        </li>
                        <?php if ($main->hasPermission("student_db_view")){ ?>
                        <li class="side-nav-item">
                            <a href="../database/university.php" class="side-nav-link">
                                <span class="menu-text">Universities</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="../database/postcode.php" class="side-nav-link">
                                <span class="menu-text">Postcodes</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="../database/others.php" class="side-nav-link">
                                <span class="menu-text">Others</span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </li>
            <?php } ?>
            <?php if ($main->isUserType(999)){ ?>
            <li class="side-nav-title mt-2">Super Admin Setting</li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#userlist" aria-expanded="false" aria-controls="sidebarPages"
                    class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-user-filled"></i></span>
                    <span class="menu-text"> User </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="userlist">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="../useraccount/user.php" class="side-nav-link">
                                <span class="menu-text">User List</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="../useraccount/user-type.php" class="side-nav-link">
                                <span class="menu-text">User Type</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php } ?>

            <?php if ($main->hasPermission("content_view")){ ?>
            <li class="side-nav-title mt-2">Content Management</li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#contentmgmt" aria-expanded="false"
                    aria-controls="contentmgmt" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-news"></i></span>
                    <span class="menu-text"> Content </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="contentmgmt">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="../content-management.php" class="side-nav-link">
                                <span class="menu-text">Publications</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="../documents-management.php" class="side-nav-link">
                                <span class="menu-text">Documents</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="../team-management.php" class="side-nav-link">
                                <span class="menu-text">Team Members</span>
                            </a>
                        </li>
                        
                    </ul>
                </div>
            </li>
            <?php } ?>




        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- Sidenav Menu End -->


<!-- Topbar Start -->
<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <!-- Brand Logo -->
            <a href="index.html" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="../assets/images/logo.png" alt="logo"></span>
                    <span class="logo-sm"><img src="../assets/images/logo-sm.png" alt="small logo"></span>
                </span>

                <span class="logo-dark">
                    <span class="logo-lg"><img src="../assets/images/logo-dark.png" alt="dark logo"></span>
                    <span class="logo-sm"><img src="../assets/images/logo-sm.png" alt="small logo"></span>
                </span>
            </a>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn btn-secondary btn-icon">
                <i class="ti ti-menu-deep fs-24"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="ti ti-menu-deep fs-22"></i>
            </button>

            <!-- Button Trigger Search Modal -->
            <div class="topbar-search text-muted d-none d-xl-flex gap-2 align-items-center" data-bs-toggle="modal"
                data-bs-target="#searchModal" type="button">
                <i class="ti ti-search fs-18"></i>
                <span class="me-2">Search something..</span>
                <button type="submit" class="ms-auto btn btn-sm btn-primary shadow-none">⌘K</span>
            </div>

        </div>

        <div class="d-flex align-items-center gap-2">

            <!-- Search for small devices -->
            <div class="topbar-item d-flex d-xl-none">
                <button class="topbar-link btn btn-outline-primary btn-icon" data-bs-toggle="modal"
                    data-bs-target="#searchModal" type="button">
                    <i class="ti ti-search fs-22"></i>
                </button>
            </div>

            <!-- Notification Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link btn btn-outline-primary btn-icon dropdown-toggle drop-arrow-none"
                        data-bs-toggle="dropdown" data-bs-offset="0,24" type="button" data-bs-auto-close="outside"
                        aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-bell animate-ring fs-22"></i>
                        <span class="noti-icon-badge"></span>
                    </button>

                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg" style="min-height: 300px;">
                        <div class="p-3 border-bottom border-dashed">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle drop-arrow-none link-dark"
                                            data-bs-toggle="dropdown" data-bs-offset="0,15" aria-expanded="false">
                                            <i class="ti ti-settings fs-22 align-middle"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Mark as Read</a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Delete All</a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Do not
                                                Disturb</a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Other
                                                Settings</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative z-2 rounded-0" style="max-height: 300px;" data-simplebar>
                            <!-- item-->
                            <div class="dropdown-item notification-item py-2 text-wrap active" id="notification-1">
                                <span class="d-flex align-items-center">
                                    <span class="me-3 position-relative flex-shrink-0">
                                        <img src="../assets/images/users/avatar-2.jpg" class="avatar-md rounded-circle"
                                            alt="" />
                                        <span class="position-absolute rounded-pill bg-danger notification-badge">
                                            <i class="ti ti-message-circle"></i>
                                            <span class="visually-hidden">unread messages</span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Glady Haid</span> commented on <span
                                            class="fw-medium text-body">paces admin status</span>
                                        <br />
                                        <span class="fs-12">25m ago</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button"
                                            class="btn btn-ghost-danger rounded-circle btn-sm btn-icon"
                                            data-dismissible="#notification-1">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>

                            <!-- item-->
                            <div class="dropdown-item notification-item py-2 text-wrap" id="notification-2">
                                <span class="d-flex align-items-center">
                                    <span class="me-3 position-relative flex-shrink-0">
                                        <img src="../assets/images/users/avatar-4.jpg" class="avatar-md rounded-circle"
                                            alt="" />
                                        <span class="position-absolute rounded-pill bg-info notification-badge">
                                            <i class="ti ti-currency-dollar"></i>
                                            <span class="visually-hidden">unread messages</span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Tommy Berry</span> donated <span
                                            class="text-success">$100.00</span> for <span
                                            class="fw-medium text-body">Carbon removal program</span>
                                        <br />
                                        <span class="fs-12">58m ago</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button"
                                            class="btn btn-ghost-danger rounded-circle btn-sm btn-icon"
                                            data-dismissible="#notification-2">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>

                            <!-- item-->
                            <div class="dropdown-item notification-item py-2 text-wrap" id="notification-3">
                                <span class="d-flex align-items-center">
                                    <div class="avatar-md flex-shrink-0 me-3">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-22">
                                            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <span class="flex-grow-1 text-muted">
                                        You withdraw a <span class="fw-medium text-body">$500</span> by <span
                                            class="fw-medium text-body">New York ATM</span>
                                        <br />
                                        <span class="fs-12">2h ago</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button"
                                            class="btn btn-ghost-danger rounded-circle btn-sm btn-icon"
                                            data-dismissible="#notification-3">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>

                            <!-- item-->
                            <div class="dropdown-item notification-item py-2 text-wrap" id="notification-4">
                                <span class="d-flex align-items-center">
                                    <span class="me-3 position-relative flex-shrink-0">
                                        <img src="../assets/images/users/avatar-7.jpg" class="avatar-md rounded-circle"
                                            alt="" />
                                        <span class="position-absolute rounded-pill bg-secondary notification-badge">
                                            <i class="ti ti-plus"></i>
                                            <span class="visually-hidden">unread messages</span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Richard Allen</span> followed you in
                                        <span class="fw-medium text-body">Facebook</span>
                                        <br />
                                        <span class="fs-12">3h ago</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button"
                                            class="btn btn-ghost-danger rounded-circle btn-sm btn-icon"
                                            data-dismissible="#notification-4">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>

                            <!-- item-->
                            <div class="dropdown-item notification-item py-2 text-wrap" id="notification-5">
                                <span class="d-flex align-items-center">
                                    <span class="me-3 position-relative flex-shrink-0">
                                        <img src="../assets/images/users/avatar-10.jpg" class="avatar-md rounded-circle"
                                            alt="" />
                                        <span class="position-absolute rounded-pill bg-danger notification-badge">
                                            <i class="ti ti-heart-filled"></i>
                                            <span class="visually-hidden">unread messages</span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Victor Collier</span> liked you recent
                                        photo in <span class="fw-medium text-body">Instagram</span>
                                        <br />
                                        <span class="fs-12">10h ago</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button"
                                            class="btn btn-ghost-danger rounded-circle btn-sm btn-icon"
                                            data-dismissible="#notification-5">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- All-->
                        <a href="javascript:void(0);"
                            class="dropdown-item notification-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            <!-- Apps Dropdown -->
            <div class="topbar-item d-none d-sm-flex">
                <div class="dropdown">
                    <button class="topbar-link btn btn-outline-primary btn-icon dropdown-toggle drop-arrow-none"
                        data-bs-toggle="dropdown" data-bs-offset="0,24" type="button" aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-apps fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg p-0">
                        <div class="p-2">
                            <div class="row g-0">
                                <div class="col">
                                    <a class="dropdown-icon-item" href="#">
                                        <img src="../assets/images/brands/instagram.svg" alt="instagram">
                                        <span>Instagram</span>
                                    </a>
                                </div>
                                <div class="col">
                                    <a class="dropdown-icon-item" href="#">
                                        <img src="../assets/images/brands/linkedin.svg" alt="linkedin">
                                        <span>LinkedIn</span>
                                    </a>
                                </div>
                                <div class="col">
                                    <a class="dropdown-icon-item" href="#">
                                        <img src="../assets/images/brands/twitter.svg" alt="x">
                                        <span>X</span>
                                    </a>
                                </div>
                            </div>

                            <div class="row g-0">
                                <div class="col">
                                    <a class="dropdown-icon-item" href="#">
                                        <img src="../assets/images/brands/facebook.svg" alt="facebook">
                                        <span>Facebook</span>
                                    </a>
                                </div>
                                <div class="col">
                                    <a class="dropdown-icon-item" href="#">
                                        <img src="../assets/images/brands/facebook.svg" alt="youtube">
                                        <span>Youtube</span>
                                    </a>
                                </div>
                                <div class="col">
                                    <a class="dropdown-icon-item" href="#">
                                        <img src="../assets/images/browsers/web.svg" alt="website">
                                        <span>Website</span>
                                    </a>
                                </div>
                            </div> <!-- end row-->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button Trigger Customizer Offcanvas -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link btn btn-outline-primary btn-icon" data-bs-toggle="offcanvas"
                    data-bs-target="#theme-settings-offcanvas" type="button">
                    <i class="ti ti-settings fs-22"></i>
                </button>
            </div>

            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link btn btn-outline-primary btn-icon" id="light-dark-mode" type="button">
                    <i class="ti ti-moon fs-22"></i>
                </button>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <a class="topbar-link btn btn-outline-primary dropdown-toggle drop-arrow-none"
                        data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" aria-haspopup="false"
                        aria-expanded="false">
                        <img src="../assets/images/users/avatar-1.jpg" width="24" class="rounded-circle me-lg-2 d-flex"
                            alt="user-image">
                        <span class="d-lg-flex flex-column gap-1 d-none">
                            <?php echo $main->getUserName(); ?>.
                        </span>
                        <i class="ti ti-chevron-down d-none d-lg-block align-middle ms-2"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome !</h6>
                        </div>

                        <!-- item-->
                        <a href="../account.php" class="dropdown-item">
                            <i class="ti ti-user-hexagon me-1 fs-17 align-middle"></i>
                            <span class="align-middle">My Account</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <!-- item-->
                        <a href="../assets/php/page/logout.php" class="dropdown-item active fw-semibold text-danger">
                            <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Topbar End -->

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-transparent">
            <div class="card mb-0 shadow-none">
                <div class="px-3 py-2 d-flex flex-row align-items-center" id="top-search">
                    <i class="ti ti-search fs-22"></i>
                    <input type="search" class="form-control border-0" id="search-modal-input"
                        placeholder="Search for actions, people,">
                    <button type="button" class="btn p-0" data-bs-dismiss="modal" aria-label="Close">[esc]</button>
                </div>
            </div>
        </div>
    </div>
</div>