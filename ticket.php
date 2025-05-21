<?php
// load main functions
require_once("assets/php/main.php");
$main = new ppim();

$credit = "Your Name";
$credit_footer = '
<a href="https://www.linkedin.com/in/your-profile/" target="_blank">Your Name</a>
';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Ticket Management | PPI Malaysia</title>
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

    <!-- Datatable css -->
    <link href="assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
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
                                <h4 class="fs-18 text-uppercase fw-bold m-0">Ticket Management</h4>
                            </div>
                            <div class="mt-3 mt-sm-0">
                                <form action="javascript:void(0);">
                                    <div class="row g-2 mb-0 align-items-center">
                                        <div class="col-sm-auto">
                                            <div class="input-group">
                                                <input type="text" class="form-control" data-provider="flatpickr"
                                                    data-default-date="today" data-date-format="d M Y">
                                                <span class="input-group-text bg-primary border-primary text-white">
                                                    <i class="ti ti-calendar fs-15"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div><!-- end card header -->
                    </div>
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">Total Tickets</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                            <i class="ti ti-ticket fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">0</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> 0%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">Active Events</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-success rounded-circle fs-22">
                                            <i class="ti ti-calendar-event fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">0</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> 0%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">Tickets Sold</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-info rounded-circle fs-22">
                                            <i class="ti ti-receipt fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">0</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> 0%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase">Revenue</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                            <i class="ti ti-cash fs-2"></i>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">RM 0</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> 0%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->
                </div><!-- end row -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Manage Tickets</h4>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                                    <i class="ti ti-plus me-1"></i> Create New Ticket
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="tickets-datatable" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Event Name</th>
                                            <th>Ticket Type</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Sold</th>
                                            <th>Event Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create Ticket Modal -->
                <div class="modal fade" id="createTicketModal" tabindex="-1" aria-labelledby="createTicketModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createTicketModalLabel">Create New Ticket</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="createTicketForm">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="eventName" class="form-label">Event Name</label>
                                            <input type="text" class="form-control" id="eventName" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ticketType" class="form-label">Ticket Type</label>
                                            <select class="form-select" id="ticketType" required>
                                                <option value="">Select Ticket Type</option>
                                                <option value="Standard">Standard</option>
                                                <option value="VIP">VIP</option>
                                                <option value="Early Bird">Early Bird</option>
                                                <option value="Student">Student</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ticketPrice" class="form-label">Price (RM)</label>
                                            <input type="number" class="form-control" id="ticketPrice" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ticketQuantity" class="form-label">Quantity Available</label>
                                            <input type="number" class="form-control" id="ticketQuantity" min="1" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="eventDate" class="form-label">Event Date</label>
                                            <input type="text" class="form-control" id="eventDate" data-provider="flatpickr" data-date-format="Y-m-d" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="eventTime" class="form-label">Event Time</label>
                                            <input type="text" class="form-control" id="eventTime" data-provider="flatpickr" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="eventLocation" class="form-label">Event Location</label>
                                        <input type="text" class="form-control" id="eventLocation" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="eventDescription" class="form-label">Event Description</label>
                                        <textarea class="form-control" id="eventDescription" rows="4" required></textarea>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="saleStartDate" class="form-label">Sale Start Date</label>
                                            <input type="text" class="form-control" id="saleStartDate" data-provider="flatpickr" data-date-format="Y-m-d" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="saleEndDate" class="form-label">Sale End Date</label>
                                            <input type="text" class="form-control" id="saleEndDate" data-provider="flatpickr" data-date-format="Y-m-d" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="eventImage" class="form-label">Event Image</label>
                                        <input type="file" class="form-control" id="eventImage" accept="image/*">
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveTicketBtn">Create Ticket</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Ticket Modal -->
                <div class="modal fade" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewTicketModalLabel">Ticket Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="ticket-image-container mb-3">
                                            <img id="viewTicketImage" src="assets/images/placeholder.jpg" class="img-fluid rounded" alt="Event Image">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h4 id="viewEventName">Event Name</h4>
                                        <p class="text-muted" id="viewEventDate">Event Date</p>
                                        <div class="d-flex mb-3">
                                            <div class="badge bg-primary me-2" id="viewTicketType">Ticket Type</div>
                                            <div class="badge bg-success" id="viewTicketStatus">Status</div>
                                        </div>
                                        <div class="mb-3">
                                            <h5>Price</h5>
                                            <p id="viewTicketPrice">RM 0.00</p>
                                        </div>
                                        <div class="mb-3">
                                            <h5>Availability</h5>
                                            <div class="progress mb-2" style="height: 10px;">
                                                <div id="viewTicketProgress" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small id="viewTicketAvailability">0 sold out of 0 (0% sold)</small>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h5>Event Location</h5>
                                        <p id="viewEventLocation">Location</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Sale Period</h5>
                                        <p id="viewSalePeriod">Start Date - End Date</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5>Event Description</h5>
                                    <p id="viewEventDescription">Description</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="editTicketBtn">Edit Ticket</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- container -->
        </div>
        <!-- content -->

        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © PPI Malaysia
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-none d-md-flex gap-4 align-item-center justify-content-md-end">
                            <p class="mb-0">Developed by <?php echo $credit_footer; ?></p>
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

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- Datatable js -->
    <script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/buttons.print.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

    <!-- Ticket Management js -->
    <script src="assets/js/pages/ticket.js"></script>

</body>

</html>