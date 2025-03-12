<?php
$page = "form";
?>
<!doctype html>

<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-starter" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Demo: Page 1 - Starter Kit | Vuexy - Bootstrap Dashboard PRO</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="../../assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="../../assets/vendor/libs/node-waves/node-waves.css" />

    <link rel="stylesheet" href="../../assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="../../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.css" />
    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="../../assets/vendor/js/template-customizer.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="../../assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <?php
          include ('chunk/menu.php');
         ?>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php
          include ('chunk/nav.php');
         ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Scrollable -->
                        <div class="card">
                            <div
                                class="card-header text-md-start text-center border-bottom d-flex justify-content-between">
                                <h5>PPI Malaysia Form: Pusdatin</h5>
                                <a href="formbuilder.php" type="button" class="btn btn-primary">
                                    <span class="d-none d-lg-block">
                                        <b><i class="menu-icon icon-base ti tabler-plus"
                                                style="margin-bottom: 3px;"></i></b>Create Form
                                    </span>
                                    <span class="d-block d-lg-none" style="margin-left: 7px;">
                                        <i class="menu-icon icon-base ti tabler-plus"></i>
                                    </span>
                                </a>
                            </div>

                            <div class="card-datatable text-nowrap">
                                <table class="dt-scrollableTable table table-bordered table-responsive">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Title</th>
                                            <th>Url</th>
                                            <th>Responded</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <!--/ Scrollable -->
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div
                                class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                                <div class="text-body">
                                    ©
                                    <script>
                                    document.write(new Date().getFullYear());
                                    </script>
                                    , made by <a target="_blank" class="footer-link">Pusdatin 2024-2025</a>
                                </div>

                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js -->

    <script src="../../assets/vendor/libs/jquery/jquery.js"></script>

    <script src="../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/node-waves/node-waves.js"></script>

    <script src="../../assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <script src="../../assets/vendor/libs/pickr/pickr.js"></script>

    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../../assets/vendor/libs/hammer/hammer.js"></script>

    <script src="../../assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="../../assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

    <!-- Main JS -->

    <script src="../../assets/js/main.js"></script>

    <!-- Page JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function(e) {
        const dt_scrollable_table = document.querySelector('.dt-scrollableTable');
        let dt_scrollableTable;
        if (dt_scrollable_table) {
            dt_scrollableTable = new DataTable(dt_scrollable_table, {
                ajax: 'api/formList.php',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'url'
                    },
                    {
                        data: 'responded'
                    },
                    {
                        data: ''
                    }, // Status badge column
                    {
                        data: ''
                    } // Actions column
                ],
                columnDefs: [{
                        // Status Badge
                        targets: -2,
                        render: function(data, type, full, meta) {
                            const statusMapping = {
                                open: {
                                    title: 'Open',
                                    class: 'bg-label-success'
                                },
                                private: {
                                    title: 'Private',
                                    class: 'bg-label-warning'
                                },
                                closed: {
                                    title: 'Closed',
                                    class: 'bg-label-danger'
                                },
                                Shared: {
                                    title: "Shared",
                                    class: 'bg-label-info'
                                }
                            };

                            const status = statusMapping[full.status] || {
                                title: 'Unknown',
                                class: 'bg-label-secondary'
                            };

                            return `
                                <span class="badge ${status.class}">
                                    ${status.title}
                                </span>
                            `;
                        }
                    },
                    {
                        targets: -1,
                        title: 'Actions',
                        searchable: false,
                        className: 'd-flex align-items-center',
                        orderable: false,
                        render: function(data, type, full, meta) {
                            return `
                                <div class="d-inline-block">
                                    <a href="javascript:;" class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="icon-base ti tabler-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end m-0">
                                        <a href="${full.url}" target="_blank" class="dropdown-item">Edit Form</a>
                                        <a href="javascript:;" class="dropdown-item">Archive</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:;" class="dropdown-item text-danger delete-record">Delete</a>
                                    </div>
                                </div>
                                <a href="javascript:;" class="btn btn-icon text-body"><i class="icon-base ti tabler-eye"></i></a>
                                <a href="javascript:;" class="btn btn-icon text-body"><i class="icon-base ti tabler-chart-histogram"></i></a>
                            `;
                        }
                    }
                ],
                // Scroll options
                scrollY: '300px',
                scrollX: true,
                layout: {
                    topStart: {
                        rowClass: 'row mx-3 my-0 justify-content-between',
                        features: [{
                            pageLength: {
                                menu: [7, 10, 25, 50, 100],
                                text: 'Show _MENU_ entries'
                            }
                        }]
                    },
                    topEnd: {
                        search: {
                            placeholder: 'Search Forms...'
                        }
                    },
                    bottomStart: {
                        rowClass: 'row mx-3 justify-content-between',
                        features: ['info']
                    },
                    bottomEnd: 'paging'
                },
                language: {
                    paginate: {
                        next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
                        previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
                        first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
                        last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
                    }
                },
                initComplete: function(settings, json) {
                    // Add the mti-n1 class to the first row in tbody
                    dt_scrollable_table.querySelector('tbody tr:first-child').classList.add(
                        'border-top-0');
                }
            });
        }
    });
    </script>

</body>

</html>