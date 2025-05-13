<?php
// load main functions
require_once("assets/php/main.php");
$main = new ppim();

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
    <title>Dashboard | PPI Malaysia</title>
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

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-18 text-uppercase fw-bold m-0">Dashboard</h4>
                            </div>
                            <div class="mt-3 mt-sm-0">
                                <form action="javascript:void(0);">
                                    <div class="row g-2 mb-0 align-items-center">
                                        <!--end col-->
                                        <div class="col-sm-auto">
                                            <div class="input-group">
                                                <input type="text" class="form-control" data-provider="flatpickr"
                                                    data-default-date="today" data-date-format="d M Y">
                                                <span class="input-group-text bg-primary border-primary text-white">
                                                    <i class="ti ti-calendar fs-15"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <!--end col-->
                                    </div>
                                    <!--end row-->
                                </form>
                            </div>
                        </div><!-- end card header -->
                    </div>
                    <!--end col-->
                </div> <!-- end row-->

                <div class="row">
                    <div class="col">
                        <div class="row row-cols-xxl-3 row-cols-md-2 row-cols-1 text-center">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted fs-13 text-uppercase" title="Number of Orders">Website
                                            Traffic</h5>
                                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                            <div class="user-img fs-42 flex-shrink-0">
                                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                                    <i class="ti ti-world fs-2"></i>
                                                </span>
                                            </div>
                                            <h3 class="mb-0 fw-bold">3219</h3>
                                        </div>
                                        <p class="mb-0 text-muted">
                                            <span class="text-danger me-2"><i class="ti ti-caret-down-filled"></i>
                                                9.19%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted fs-13 text-uppercase" title="Number of Orders">Instagram
                                            Followers</h5>
                                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                            <div class="user-img fs-42 flex-shrink-0">
                                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                                    <i class="ti ti-brand-instagram fs-2"></i>
                                                </span>
                                            </div>
                                            <h3 class="mb-0 fw-bold">21.9k</h3>
                                        </div>
                                        <p class="mb-0 text-muted">
                                            <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>
                                                2.6%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted fs-13 text-uppercase" title="Number of Orders">X Followers
                                        </h5>
                                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                            <div class="user-img fs-42 flex-shrink-0">
                                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                                    <i class="ti ti-brand-x fs-2"></i>
                                                </span>
                                            </div>
                                            <h3 class="mb-0 fw-bold">384</h3>
                                        </div>
                                        <p class="mb-0 text-muted">
                                            <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>
                                                -%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted fs-13 text-uppercase" title="Number of Orders">Youtube
                                            Subscriber</h5>
                                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                            <div class="user-img fs-42 flex-shrink-0">
                                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                                    <i class="ti ti-brand-youtube fs-2"></i>
                                                </span>
                                            </div>
                                            <h3 class="mb-0 fw-bold">6.33K</h3>
                                        </div>
                                        <p class="mb-0 text-muted">
                                            <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>
                                                -%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted fs-13 text-uppercase" title="Number of Orders">Facebook
                                            Followers</h5>
                                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                            <div class="user-img fs-42 flex-shrink-0">
                                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                                    <i class="ti ti-brand-facebook fs-2"></i>
                                                </span>
                                            </div>
                                            <h3 class="mb-0 fw-bold">-</h3>
                                        </div>
                                        <p class="mb-0 text-muted">
                                            <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>
                                                -%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted fs-13 text-uppercase" title="Number of Orders">LinkedIn
                                            Followers</h5>
                                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                            <div class="user-img fs-42 flex-shrink-0">
                                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                                    <i class="ti ti-brand-youtube fs-2"></i>
                                                </span>
                                            </div>
                                            <h3 class="mb-0 fw-bold">888</h3>
                                        </div>
                                        <p class="mb-0 text-muted">
                                            <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>
                                                -%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- end col -->
                        </div><!-- end row -->




                    </div> <!-- end col-->

                    <div class="col-auto info-sidebar">
                        <div class="card">
                            <div class="card-body p-0">
                                <h4 class="header-title px-3 mb-2 mt-3">Upcoming Activity</h4>
                                <div class="my-3 px-3" data-simplebar style="max-height: 400px;">
                                    <div class="timeline-alt py-0">
                                        <div class="timeline-item">
                                            <i class="ti ti-calendar-stats bg-info-subtle text-info timeline-icon"></i>
                                            <div class="timeline-item-info">
                                                <a href="javascript:void(0);"
                                                    class="link-reset fw-semibold mb-1 d-block">Ngariung</a>
                                                <span class="mb-1">Kelembagaan - 7-8 June 2025</span>
                                                <p class="mb-0 pb-3">
                                                    <small class="text-muted">in 30 Days</small>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <i class="ti ti-calendar-stats bg-info-subtle text-info timeline-icon"></i>
                                            <div class="timeline-item-info">
                                                <a href="javascript:void(0);"
                                                    class="link-reset fw-semibold mb-1 d-block">Instellar</a>
                                                <span class="mb-1">Segaya - xx xx 2025
                                                </span>
                                                <p class="mb-0 pb-3">
                                                    <small class="text-muted">in X days</small>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <i class="ti ti-calendar-stats bg-info-subtle text-info timeline-icon"></i>
                                            <div class="timeline-item-info">
                                                <a href="javascript:void(0);"
                                                    class="link-reset fw-semibold mb-1 d-block">Robert Delaney</a>
                                                <span class="mb-1">Kominfo - xx xx 2025
                                                </span>
                                                <p class="mb-0 pb-3">
                                                    <small class="text-muted">in X days</small>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <i
                                                class="ti ti-calendar-stats bg-warning-subtle text-warning timeline-icon"></i>
                                            <div class="timeline-item-info">
                                                <a href="javascript:void(0);"
                                                    class="link-reset fw-semibold mb-1 d-block">Deadline X</a>
                                                <span class="mb-1">Pusdatin - xx xx 2025
                                                </span>
                                                <p class="mb-0 pb-3">
                                                    <small class="text-muted">in X days</small>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <i class="ti ti-basket bg-info-subtle text-info timeline-icon"></i>
                                            <div class="timeline-item-info">
                                                <a href="javascript:void(0);"
                                                    class="link-reset fw-semibold mb-1 d-block">You sold an item</a>
                                                <span class="mb-1">Paul Burgess just purchased “My - Admin
                                                    Dashboard”!</span>
                                                <p class="mb-0 pb-3">
                                                    <small class="text-muted">16 hours ago</small>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <i class="ti ti-rocket bg-primary-subtle text-primary timeline-icon"></i>
                                            <div class="timeline-item-info">
                                                <a href="javascript:void(0);"
                                                    class="link-reset fw-semibold mb-1 d-block">Product on the Bootstrap
                                                    Market</a>
                                                <span class="mb-1">Reviewer added
                                                    <span class="fw-medium">Admin Dashboard</span>
                                                </span>
                                                <p class="mb-0 pb-3">
                                                    <small class="text-muted">22 hours ago</small>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <i class="ti ti-message bg-info-subtle text-info timeline-icon"></i>
                                            <div class="timeline-item-info">
                                                <a href="javascript:void(0);"
                                                    class="link-reset fw-semibold mb-1 d-block">Robert Delaney</a>
                                                <span class="mb-1">Send you message
                                                    <span class="fw-medium">"Are you there?"</span>
                                                </span>
                                                <p class="mb-0 pb-2">
                                                    <small class="text-muted">2 days ago</small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end timeline -->
                                </div> <!-- end slimscroll -->
                            </div>
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div> <!-- end row-->

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

    <!-- Apex Chart js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Projects Analytics Dashboard App js -->
    <script src="assets/js/pages/dashboard-sales.js"></script>

</body>

</html>