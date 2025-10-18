<?php
require_once("assets/php/team-members.php");
$team = new TeamMembers();

$credit = "PPIM Content Management System";
$credit_footer = '<a href="https://ppimalaysia.org" target="_blank">PPI Malaysia</a>';

$department = isset($_GET['department']) ? $_GET['department'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'order';

$members = $team->getTeamMembers($department, $sort);
$departments = $team->getDepartments();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Team Members | PPI Malaysia</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta content="<?php echo htmlspecialchars($credit); ?>" name="author" />

	<link rel="shortcut icon" href="assets/images/favicon.ico">
	<script src="assets/js/config.js"></script>
	<link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
	<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/content-management.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<div class="wrapper">
		<?php $team->renderNavbar(); ?>

		<div class="page-content">
			<div class="page-container">
				<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
					<div class="flex-grow-1">
						<h4 class="fs-18 text-uppercase fw-bold mb-0">Team Members</h4>
					</div>
					<div class="text-end">
						<ol class="breadcrumb m-0 py-0">
							<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="javascript: void(0);">Content Management</a></li>
							<li class="breadcrumb-item active">Team Members</li>
						</ol>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<div class="card mb-4">
							<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
								<h4 class="header-title">Team</h4>
								<div class="content-management-actions">
									<?php if ($team->canCreate()): ?>
									<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
										<i class="ti ti-plus me-1"></i> Add Member
									</button>
									<?php endif; ?>
								</div>
							</div>
							<div class="card-body">
								<div class="filter-bar">
									<select class="form-select" id="deptFilter">
										<option value="">All Departments</option>
										<?php foreach ($departments as $d): ?>
										<option value="<?php echo htmlspecialchars($d); ?>" <?php echo ($department == $d) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d); ?></option>
										<?php endforeach; ?>
									</select>
									<select class="form-select" id="sortFilter">
										<option value="order" <?php echo ($sort=='order') ? 'selected' : ''; ?>>Order</option>
										<option value="name" <?php echo ($sort=='name') ? 'selected' : ''; ?>>Name</option>
									</select>
									<button class="btn btn-secondary" onclick="applyTeamFilters()"><i class="ti ti-filter me-1"></i> Apply</button>
									<button class="btn btn-outline-secondary" onclick="clearTeamFilters()"><i class="ti ti-x me-1"></i> Clear</button>
								</div>

								<div class="table-responsive">
									<table class="table table-hover content-table">
										<thead>
											<tr>
												<th>ID</th>
												<th>Name</th>
												<th>Position</th>
												<th>Department</th>
												<th>Order</th>
												<th>Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php if (empty($members)): ?>
											<tr><td colspan="6" class="text-center">No team members found</td></tr>
											<?php else: ?>
											<?php foreach ($members as $m): ?>
											<tr>
												<td><?php echo $m['id']; ?></td>
												<td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
												<td><?php echo htmlspecialchars($m['position']); ?></td>
												<td><?php echo htmlspecialchars($m['department'] ?? ''); ?></td>
												<td><?php echo (int)($m['order'] ?? 0); ?></td>
												<td>
													<div class="action-buttons">
														<button class="btn btn-sm btn-info" onclick="viewMember(<?php echo $m['id']; ?>)"><i class="ti ti-eye"></i></button>
														<?php if ($team->canEdit()): ?><button class="btn btn-sm btn-warning" onclick="editMember(<?php echo $m['id']; ?>)"><i class="ti ti-edit"></i></button><?php endif; ?>
														<?php if ($team->canDelete()): ?><button class="btn btn-sm btn-danger" onclick="deleteMember(<?php echo $m['id']; ?>)"><i class="ti ti-trash"></i></button><?php endif; ?>
													</div>
												</td>
											</tr>
											<?php endforeach; ?>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<footer class="footer">
				<div class="page-container">
					<div class="row">
						<div class="col-md-6 text-center text-md-start">
							<script>document.write(new Date().getFullYear())</script> © <?php echo $credit_footer; ?>
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

	<div class="modal fade" id="addTeamModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="teamModalTitle">Add Team Member</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<form id="teamForm" enctype="multipart/form-data">
					<div class="modal-body">
						<input type="hidden" id="memberId" name="id">
						<div class="mb-3"><label class="form-label" for="memberName">Name *</label><input type="text" class="form-control" id="memberName" name="name" required></div>
						<div class="mb-3"><label class="form-label" for="memberPosition">Position *</label><input type="text" class="form-control" id="memberPosition" name="position" required></div>
						<div class="mb-3"><label class="form-label" for="memberDepartment">Department</label><input type="text" class="form-control" id="memberDepartment" name="department"></div>
						<div class="mb-3"><label class="form-label" for="memberEmail">Email</label><input type="email" class="form-control" id="memberEmail" name="email"></div>
						<div class="mb-3"><label class="form-label" for="memberPhone">Phone</label><input type="text" class="form-control" id="memberPhone" name="phone"></div>
						<div class="mb-3"><label class="form-label" for="memberImage">Image</label><input type="file" class="form-control" id="memberImage" name="image"></div>
						<div class="mb-3"><label class="form-label" for="memberOrder">Order</label><input type="number" class="form-control" id="memberOrder" name="order" value="0"></div>
						<div class="mb-3"><label class="form-label" for="memberJoined">Joined At</label><input type="date" class="form-control" id="memberJoined" name="joinedAt"></div>
						<div class="mb-3"><label class="form-label" for="memberBio">Bio</label><textarea class="form-control" id="memberBio" name="bio" rows="3"></textarea></div>
						<div class="mb-3"><label class="form-label" for="memberSocial">Social Links (JSON)</label><textarea class="form-control" id="memberSocial" name="socialLinks" rows="2" placeholder='{"linkedin":"..."}'></textarea></div>
						<div class="mb-3"><label class="form-label" for="memberAchievements">Achievements (JSON)</label><textarea class="form-control" id="memberAchievements" name="achievements" rows="2" placeholder='["Achievement 1", "Achievement 2"]'></textarea></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Save Member</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<?php $team->renderTheme(); ?>

	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.js"></script>
	<script src="assets/js/team-management.js"></script>
	<script>
		function applyTeamFilters() {
			const dept = document.getElementById('deptFilter').value;
			const sort = document.getElementById('sortFilter').value;
			let url = 'team-management.php?';
			if (dept) url += 'department=' + encodeURIComponent(dept) + '&';
			if (sort) url += 'sort=' + encodeURIComponent(sort);
			window.location.href = url;
		}
		function clearTeamFilters() { window.location.href = 'team-management.php'; }
	</script>
</body>

</html>


