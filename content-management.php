<?php
// Load publications management
require_once("assets/php/publications.php");
$publications = new Publications();

// Credit
$credit = "PPIM Content Management System";
$credit_footer = '<a href="https://ppimalaysia.org" target="_blank">PPI Malaysia</a>';

// Get categories and tags for filters
$categories = $publications->getCategories();
$allTags = $publications->getAllTags();

// Get paginated data
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$tag = isset($_GET['tag']) ? $_GET['tag'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

$publicationsList = $publications->getPublications($page, $limit, $category, $tag, $search);
$totalRecords = $publications->getTotalCount($category, $tag, $search);
$totalPages = ceil($totalRecords / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Publications Management | PPI Malaysia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo htmlspecialchars($credit); ?>" name="author" />

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

    <!-- Content Management css -->
    <link href="assets/css/content-management.css" rel="stylesheet" type="text/css" />

    <!-- Quill Editor CSS -->
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet" type="text/css" />

    <!-- Toast CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">
        <?php $publications->renderNavbar(); ?>

        <!-- Start Page Content here -->
        <div class="page-content">
            <div class="page-container">

                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Publications Management</h4>
                    </div>
                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Content Management</a></li>
                            <li class="breadcrumb-item active">Publications</li>
                        </ol>
                    </div>
                </div>

                <!-- Publications Management -->
                <div class="row">
                    <div class="col">
                        <div class="card mb-4">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                                <h4 class="header-title">Publications</h4>
                                <div class="content-management-actions">
                                    <?php if ($publications->canCreate()): ?>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPublicationModal">
                                        <i class="ti ti-plus me-1"></i> Add Publication
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Pass current user ID and name to JavaScript -->
                                <input type="hidden" id="current-user-id" value="<?php echo $publications->getUserId(); ?>">
                                <input type="hidden" id="current-user-name" value="<?php echo $publications->getUserName(); ?>">
                                <!-- Filters -->
                                <div class="filter-bar">
                                    <select class="form-select" id="categoryFilter">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category == $cat) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <select class="form-select" id="tagFilter">
                                        <option value="">All Tags</option>
                                        <?php foreach ($allTags as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($tag == $t) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($t); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search publications..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                    
                                    <button class="btn btn-secondary" onclick="applyFilters()">
                                        <i class="ti ti-filter me-1"></i> Apply Filters
                                    </button>
                                    
                                    <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                        <i class="ti ti-x me-1"></i> Clear
                                    </button>
                                </div>

                                <!-- Publications Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover content-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Tags</th>
                                                <th>Author</th>
                                                <th>Published Date</th>
                                                <th>Reading Time</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($publicationsList)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No publications found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($publicationsList as $pub): ?>
                                            <tr>
                                                <td><?php echo $pub['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($pub['title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($pub['excerpt'], 0, 100)); ?>...</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary publication-category-badge">
                                                        <?php echo htmlspecialchars($pub['category']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="publication-tags">
                                                        <?php foreach ($pub['tags'] as $tag): ?>
                                                        <span class="publication-tag"><?php echo htmlspecialchars($tag); ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($pub['author']['name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($pub['publishedAt'])); ?></td>
                                                <td>
                                                    <span class="reading-time">
                                                        <i class="ti ti-clock"></i>
                                                        <?php echo $pub['readingTime']; ?> min
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn btn-sm btn-info" onclick="viewPublication(<?php echo $pub['id']; ?>)" title="View">
                                                            <i class="ti ti-eye"></i>
                                                        </button>
                                                        <?php if ($publications->canEdit()): ?>
                                                        <button class="btn btn-sm btn-warning" onclick="editPublication(<?php echo $pub['id']; ?>)" title="Edit">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <?php if ($publications->canDelete()): ?>
                                                        <button class="btn btn-sm btn-danger" onclick="deletePublication(<?php echo $pub['id']; ?>)" title="Delete">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                <nav>
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&category=<?php echo urlencode($category); ?>&tag=<?php echo urlencode($tag); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&tag=<?php echo urlencode($tag); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&category=<?php echo urlencode($category); ?>&tag=<?php echo urlencode($tag); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- container -->

            <!-- Footer -->
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
            <!-- end Footer -->

        </div>
        <!-- End Page content -->

    </div>
    <!-- END wrapper -->

    <!-- Add/Edit Publication Modal -->
    <div class="modal fade" id="addPublicationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Publication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="publicationForm">
                    <div class="modal-body">
                        <input type="hidden" id="publicationId" name="id">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt *</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <div id="editor" class="quill-editor-container"></div>
                            <textarea id="content" name="content" style="display:none;"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="News">News</option>
                                    <option value="Journal">Journal</option>
                                    <option value="Article">Article</option>
                                    <option value="Program">Program</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label">Tags (comma-separated)</label>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="tag1, tag2, tag3">
                            </div>
                        </div>
                        
                        <!-- Author Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="authorName" class="form-label">Author Name *</label>
                                <input type="text" class="form-control" id="authorName" name="authorName" 
                                       placeholder="e.g., Dr. Ahmad Ibrahim" required>
                                <small class="text-muted">Enter the author's full name</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="authorAffiliation" class="form-label">Author Affiliation</label>
                                <input type="text" class="form-control" id="authorAffiliation" name="authorAffiliation" 
                                       placeholder="e.g., Universiti Malaya">
                                <small class="text-muted">University, organization, or institution</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="publishedAt" class="form-label">Published Date</label>
                            <input type="datetime-local" class="form-control" id="publishedAt" name="publishedAt">
                        </div>
                        
                        <!-- Image Uploads -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="featuredImageFile" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="featuredImageFile" name="featuredImageFile" accept="image/*">
                                <small class="text-muted">Upload image for thumbnail/preview (JPG, PNG, WebP)</small>
                                <div id="featuredImagePreview" class="mt-2"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="bannerFile" class="form-label">Banner Image</label>
                                <input type="file" class="form-control" id="bannerFile" name="bannerFile" accept="image/*">
                                <small class="text-muted">Upload banner image for article header</small>
                                <div id="bannerPreview" class="mt-2"></div>
                            </div>
                        </div>
                        
                        <!-- Alternative: URLs (if you still want to support external URLs) -->
                        <div class="accordion mb-3" id="urlAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#urlOptions">
                                        Or use external image URLs (optional)
                                    </button>
                                </h2>
                                <div id="urlOptions" class="accordion-collapse collapse" data-bs-parent="#urlAccordion">
                                    <div class="accordion-body">
                                        <div class="mb-3">
                                            <label for="featuredImageUrl" class="form-label">Featured Image URL</label>
                                            <input type="url" class="form-control" id="featuredImageUrl" name="featuredImageUrl">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bannerUrl" class="form-label">Banner URL</label>
                                            <input type="url" class="form-control" id="bannerUrl" name="bannerUrl">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                            <small class="text-muted">Optional. You can upload multiple files (PDF, images, documents).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Publication</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Theme Settings -->
    <?php $publications->renderTheme(); ?>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- Quill Editor -->
    <script src="assets/vendor/quill/quill.min.js"></script>

    <!-- Toast -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- Publications Management JS -->
    <script src="assets/js/publications-management.js"></script>
    
    <script>
        // Filter functions
        function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const tag = document.getElementById('tagFilter').value;
            const search = document.getElementById('searchInput').value;
            
            let url = 'content-management.php?';
            if (category) url += 'category=' + encodeURIComponent(category) + '&';
            if (tag) url += 'tag=' + encodeURIComponent(tag) + '&';
            if (search) url += 'search=' + encodeURIComponent(search);
            
            window.location.href = url;
        }
        
        function clearFilters() {
            window.location.href = 'content-management.php';
        }
    </script>
</body>
</html>

