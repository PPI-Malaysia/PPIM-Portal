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
    <title>Calendar | PPI Malaysia</title>
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
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Calendar</h4>
                    </div>

                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>

                            <li class="breadcrumb-item active">Calendar</li>
                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="row">
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <button class="btn btn-primary w-100" id="btn-new-event">
                                        <i class="ti ti-plus me-2 align-middle"></i> Create New Event
                                    </button>

                                    <div id="external-events" class="mt-2">
                                        <p class="text-muted">Drag and drop your event or click in the calendar</p>
                                        <div class="external-event fc-event bg-success-subtle text-success"
                                            data-class="bg-success-subtle">
                                            <i class="ti ti-circle-filled me-2"></i>Online/Offline Event
                                        </div>
                                        <div class="external-event fc-event bg-info-subtle text-info"
                                            data-class="bg-info-subtle">
                                            <i class="ti ti-circle-filled me-2"></i>Meeting
                                        </div>
                                        <div class="external-event fc-event bg-warning-subtle text-warning"
                                            data-class="bg-warning-subtle">
                                            <i class="ti ti-circle-filled me-2"></i>Deadline
                                        </div>
                                        <div class="external-event fc-event bg-danger-subtle text-danger"
                                            data-class="bg-danger-subtle">
                                            <i class="ti ti-circle-filled me-2"></i>Important
                                        </div>
                                        <div class="external-event fc-event bg-dark-subtle text-dark"
                                            data-class="bg-dark-subtle">
                                            <i class="ti ti-circle-filled me-2"></i>Other
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div> <!-- end col-->

                        <div class="col-xl-9">
                            <div class="card">
                                <div class="card-body">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div><!-- end col -->
                    </div>
                    <!--end row-->

                </div>
                <!--end row-->

                <!-- Add New Event MODAL -->
                <div class="modal fade" id="event-modal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form class="needs-validation" name="event-form" id="forms-event" novalidate>
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modal-title">
                                        Create Event
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-2">
                                                <label class="control-label form-label" for="event-title">Event
                                                    Name</label>
                                                <input class="form-control" placeholder="Insert Event Name" type="text"
                                                    name="title" id="event-title" required />
                                                <div class="invalid-feedback">Please provide a valid event name</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-2">
                                                <label class="control-label form-label"
                                                    for="event-category">Category</label>
                                                <select class="form-select" name="category" id="event-category"
                                                    required>
                                                    <option value="bg-success-subtle text-success">Online/Offline Event
                                                    </option>
                                                    <option value="bg-info-subtle text-info">Meeting</option>
                                                    <option value="bg-warning-subtle text-warning">Deadline</option>
                                                    <option value="bg-danger-subtle text-danger">Important</option>
                                                    <option value="bg-dark-subtle text-dark">Other</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a valid event category</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <button type="button" class="btn btn-danger" id="btn-delete-event">
                                            Delete
                                        </button>

                                        <button type="button" class="btn btn-light ms-auto" data-bs-dismiss="modal">
                                            Close
                                        </button>

                                        <button type="submit" class="btn btn-primary" id="btn-save-event">
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- end modal-content-->
                    </div>
                    <!-- end modal dialog-->
                </div>
                <!-- end modal-->

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

    <!-- Fullcalendar js -->
    <script src="assets/vendor/fullcalendar/index.global.min.js"></script>

    <!-- Calendar js -->
    <script src="assets/js/pages/calendar.js"></script>

</body>

</html>