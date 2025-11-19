<?php
// load main functions
require_once("assets/php/dashboard.php");
$main = new Dashboard();
// Credit: fill your name as the person who created this page here
$credit = "Rafi Daffa Ramadhani";
$credit_footer = '
<a href="https://www.linkedin.com/in/rafi-daffa/" target="_blank">Rafi Daffa</a>
';

// For calendar events, we still need Calendar class
require_once("assets/php/calendar.php");
$calendar = new Calendar();
$upcomingEvents = $calendar->getUpcomingEventsForDisplay(5);
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


                <!-- Stat Cards Row -->
                <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
                    <!-- Total Students Card -->
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">Total Students</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                            <i class="ti ti-users fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold" id="total-students">-</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2" id="active-students">
                                        <i class="ti ti-check"></i> - Active
                                    </span>
                                    <span class="text-danger" id="graduated-students">
                                        <i class="ti ti-school"></i> - Graduated
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <!-- Active PPIM Members Card -->
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">Active PPIM Members</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-success rounded-circle fs-22">
                                            <i class="ti ti-user-check fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold" id="active-ppim-members">-</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-nowrap" id="ppim-year">Current Year (2025)</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <!-- Total Universities Card -->
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">Total Universities</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-info rounded-circle fs-22">
                                            <i class="ti ti-building fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold" id="total-universities">-</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-nowrap">Registered universities</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <!-- PPI Campus Chapters Card -->
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">PPI Campus Chapters</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                            <i class="ti ti-school fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold" id="active-ppi-chapters">-</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-nowrap">Active chapters</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->
                </div><!-- end row -->

                <!-- Charts Row -->
                <div class="row">
                    <!-- Students by Qualification Level -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">Students by Qualification Level</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-qualification-level"></div>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <!-- Students by University -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">Top 10 Universities</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-students-university"></div>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <!-- PPIM Members by Department -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">PPIM Members by Department</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-ppim-department"></div>
                            </div>
                        </div>
                    </div><!-- end col -->
                </div><!-- end row -->

                <!-- Calendar Events & Coverage Map Section -->
                <div class="row">
                    <!-- Calendar Events -->
                    <div class="col-xxl-6 col-lg-6">
                        <div class="card">
                            <div
                                class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title mb-0">
                                    <i class="ti ti-calendar-event me-2"></i>Upcoming Events
                                </h4>
                                <a href="calendar.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="timeline-alt py-0" data-simplebar style="max-height: 400px;">
                                    <?php if (!empty($upcomingEvents)): ?>
                                    <?php foreach ($upcomingEvents as $event): ?>
                                    <div class="timeline-item">
                                        <i
                                            class="ti ti-calendar-stats <?php echo $event['is_ongoing'] ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info'; ?> timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                            </a>
                                            <span class="mb-1">
                                                <?php if (!empty($event['creator_name'])): ?>
                                                <?php echo htmlspecialchars($event['creator_name']); ?> -
                                                <?php endif; ?>
                                                <?php echo $event['formatted_date']; ?>
                                            </span>
                                            <p class="mb-0 pb-3">
                                                <small
                                                    class="text-muted <?php echo $event['is_ongoing'] ? 'text-success fw-medium' : ''; ?>">
                                                    <?php echo $event['time_until']; ?>
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <div class="timeline-item">
                                        <i
                                            class="ti ti-calendar-off bg-secondary-subtle text-secondary timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <span class="fw-semibold mb-1 d-block text-muted">No upcoming events</span>
                                            <p class="mb-0 pb-3">
                                                <small class="text-muted">Check back later for new activities</small>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <!-- end timeline -->
                            </div>
                        </div>
                    </div><!-- end col -->

                    <!-- Coverage Map - Students Location -->
                    <div class="col-xxl-6 col-lg-6">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title mb-0">
                                    <i class="ti ti-map-pin me-2"></i>Coverage Map - Students by State
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>State</th>
                                                <th class="text-end">Students</th>
                                                <th class="text-end">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody id="coverage-table-body">
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">
                                                    <i class="ti ti-loader fs-3"></i>
                                                    <br>Loading data...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                </div><!-- end row -->

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

    <!-- Dashboard js -->
    <script src="assets/js/pages/dashboard.js"></script>

</body>

</html>