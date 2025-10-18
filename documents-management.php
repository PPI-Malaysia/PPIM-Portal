<?php
require_once("assets/php/documents.php");
$documents = new Documents();

$credit = "PPIM Content Management System";
$credit_footer = '<a href="https://ppimalaysia.org" target="_blank">PPI Malaysia</a>';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

$docsList = $documents->getDocuments($page, $limit, $category, $search);
$totalRecords = $documents->getTotalCount($category, $search);
$totalPages = max(1, (int)ceil($totalRecords / max(1, $limit)));
$categories = $documents->getCategories();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Documents Management | PPI Malaysia</title>
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
		<?php $documents->renderNavbar(); ?>

		<div class="page-content">
			<div class="page-container">
				<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
					<div class="flex-grow-1">
						<h4 class="fs-18 text-uppercase fw-bold mb-0">Documents Management</h4>
					</div>
					<div class="text-end">
						<ol class="breadcrumb m-0 py-0">
							<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="javascript: void(0);">Content Management</a></li>
							<li class="breadcrumb-item active">Documents</li>
						</ol>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<div class="card mb-4">
							<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
								<h4 class="header-title">Documents</h4>
								<div class="content-management-actions">
									<?php if ($documents->canCreate()): ?>
									<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
										<i class="ti ti-plus me-1"></i> Add Document
									</button>
									<?php endif; ?>
								</div>
							</div>
							<div class="card-body">
								<div class="filter-bar">
									<select class="form-select" id="docCategoryFilter">
										<option value="">All Categories</option>
										<?php foreach ($categories as $cat): ?>
										<option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category == $cat) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
										<?php endforeach; ?>
									</select>
									<input type="text" class="form-control" id="docSearchInput" placeholder="Search documents..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
									<button class="btn btn-secondary" onclick="applyDocFilters()"><i class="ti ti-filter me-1"></i> Apply</button>
									<button class="btn btn-outline-secondary" onclick="clearDocFilters()"><i class="ti ti-x me-1"></i> Clear</button>
								</div>

								<div class="table-responsive">
									<table class="table table-hover content-table">
										<thead>
											<tr>
												<th>ID</th>
												<th>Title</th>
												<th>Category</th>
												<th>File</th>
												<th>Uploaded</th>
												<th>Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php if (empty($docsList)): ?>
											<tr><td colspan="6" class="text-center">No documents found</td></tr>
											<?php else: ?>
											<?php foreach ($docsList as $doc): ?>
											<tr>
												<td><?php echo $doc['id']; ?></td>
												<td><strong><?php echo htmlspecialchars($doc['title']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars(substr($doc['description'] ?? '', 0, 100)); ?>...</small></td>
												<td><?php echo htmlspecialchars($doc['category']); ?></td>
												<td><a href="<?php echo htmlspecialchars($doc['fileUrl']); ?>" target="_blank">Download</a></td>
												<td><?php echo $doc['uploadedAt']; ?></td>
												<td>
													<div class="action-buttons">
														<button class="btn btn-sm btn-info" onclick="viewDocument(<?php echo $doc['id']; ?>)"><i class="ti ti-eye"></i></button>
														<?php if ($documents->canEdit()): ?>
														<button class="btn btn-sm btn-warning" onclick="editDocument(<?php echo $doc['id']; ?>)"><i class="ti ti-edit"></i></button>
														<?php endif; ?>
														<?php if ($documents->canDelete()): ?>
														<button class="btn btn-sm btn-danger" onclick="deleteDocument(<?php echo $doc['id']; ?>)"><i class="ti ti-trash"></i></button>
														<?php endif; ?>
													</div>
												</td>
											</tr>
											<?php endforeach; ?>
											<?php endif; ?>
										</tbody>
									</table>
								</div>

								<?php if ($totalPages > 1): ?>
								<nav>
									<ul class="pagination justify-content-center">
										<li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
											<a class="page-link" href="?page=<?php echo ($page - 1); ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
										</li>
										<?php for ($i = 1; $i <= $totalPages; $i++): ?>
										<li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
											<a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
										</li>
										<?php endfor; ?>
										<li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
											<a class="page-link" href="?page=<?php echo ($page + 1); ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>">Next</a>
										</li>
									</ul>
								</nav>
								<?php endif; ?>
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

	<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="docModalTitle">Add Document</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<form id="documentForm" enctype="multipart/form-data">
					<div class="modal-body">
						<input type="hidden" id="docId" name="id">
						<div class="mb-3">
							<label class="form-label" for="docTitle">Title *</label>
							<input type="text" class="form-control" id="docTitle" name="title" required>
						</div>
						<div class="mb-3">
							<label class="form-label" for="docDesc">Description *</label>
							<textarea class="form-control" id="docDesc" name="description" rows="3" required></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label" for="docCategory">Category *</label>
							<input type="text" class="form-control" id="docCategory" name="category" required>
						</div>
						<div class="mb-3">
							<label class="form-label" for="docFile">File *</label>
							<input type="file" class="form-control" id="docFile" name="file" required>
							<small class="text-muted">PDF, DOCX, XLSX (max 20MB)</small>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Save Document</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<?php $documents->renderTheme(); ?>

	<script src="assets/js/vendor.min.js"></script>
	<script src="assets/js/app.js"></script>
	<script src="assets/js/publications-management.js"></script>
	<script src="assets/js/documents-management.js"></script>
	<script>
		function applyDocFilters() {
			const category = document.getElementById('docCategoryFilter').value;
			const search = document.getElementById('docSearchInput').value;
			let url = 'documents-management.php?';
			if (category) url += 'category=' + encodeURIComponent(category) + '&';
			if (search) url += 'search=' + encodeURIComponent(search);
			window.location.href = url;
		}
		function clearDocFilters() { window.location.href = 'documents-management.php'; }
	</script>
</body>

</html>


