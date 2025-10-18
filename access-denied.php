<?php
// Access Denied page
http_response_code(403);
require_once("assets/php/main.php");
$main = new ppim();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Access Denied | PPI Malaysia</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" href="assets/images/favicon.ico">
	<script src="assets/js/config.js"></script>
	<link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
	<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<div class="wrapper">
		<?php $main->renderNavbar(); ?>

		<div class="page-content">
			<div class="page-container">
				<div class="row justify-content-center mt-5">
					<div class="col-md-8 col-lg-6">
						<div class="card">
							<div class="card-body text-center p-5">
								<div class="mb-3">
									<span class="avatar-title bg-danger-subtle text-danger rounded-circle" style="width:72px;height:72px;display:inline-flex;align-items:center;justify-content:center;">
										<i class="ti ti-lock fs-32"></i>
									</span>
								</div>
								<h3 class="fw-bold mb-2">Access Denied</h3>
								<p class="text-muted mb-4">You don't have permission to access this page.</p>
								<div class="d-flex gap-2 justify-content-center">
									<a href="index.php" class="btn btn-primary"><i class="ti ti-home me-1"></i>Back to Dashboard</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php $main->renderTheme(); ?>

	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.js"></script>
</body>

</html>


