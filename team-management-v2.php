<?php
// team-management-v2.php - Enhanced Team Management Portal Page
require_once("assets/php/team-management-v2.php");
$team = new TeamManagementV2();

$credit = "PPIM Content Management System";
$credit_footer = '<a href="https://ppimalaysia.org" target="_blank">PPI Malaysia</a>';

// Get current data
$periods = $team->getPeriods();
$activePeriod = $team->getActivePeriod();
$departments = $team->getDepartments(false); // Get all including inactive
$positionLevels = $team->getPositionLevels();
$icons = $team->getAvailableIcons();

// Current view tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'members';
$selectedPeriod = isset($_GET['period']) ? $_GET['period'] : ($activePeriod ? $activePeriod['id'] : null);
$selectedDepartment = isset($_GET['department']) ? $_GET['department'] : null;

// Get members based on filters
$memberFilters = [];
if ($selectedPeriod) $memberFilters['periodId'] = $selectedPeriod;
if ($selectedDepartment) $memberFilters['departmentId'] = $selectedDepartment;
$members = $team->getMembers($memberFilters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Team Management | PPI Malaysia Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo htmlspecialchars($credit); ?>" name="author" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script src="assets/js/config.js"></script>
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="assets/css/content-management.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .team-tabs { border-bottom: 2px solid #e9ecef; margin-bottom: 1.5rem; }
        .team-tabs .nav-link { border: none; color: #6c757d; padding: 0.75rem 1.5rem; font-weight: 500; }
        .team-tabs .nav-link.active { color: #0d6efd; border-bottom: 2px solid #0d6efd; margin-bottom: -2px; }
        .team-tabs .nav-link:hover:not(.active) { color: #495057; }
        .period-badge { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
        .period-active { background-color: #198754; }
        .dept-card { border-left: 4px solid #dee2e6; transition: all 0.2s; cursor: move; }
        .dept-card:hover { box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1); }
        .dept-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 1.5rem; }
        .member-photo { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .member-photo-placeholder { width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d; }
        .icon-picker-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; max-height: 200px; overflow-y: auto; }
        .icon-picker-item { padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; text-align: center; transition: all 0.15s; }
        .icon-picker-item:hover, .icon-picker-item.selected { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .color-picker-wrapper { position: relative; }
        .color-preview { width: 24px; height: 24px; border-radius: 4px; border: 1px solid #dee2e6; cursor: pointer; }
        .filter-bar { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; align-items: center; }
        .filter-bar .form-select, .filter-bar .form-control { max-width: 200px; }
        .action-buttons { display: flex; gap: 0.25rem; }
        .position-badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; }
        .sortable-ghost { opacity: 0.4; }
        .drag-handle { cursor: move; color: #6c757d; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php $team->renderNavbar(); ?>

        <div class="page-content">
            <div class="page-container">
                <!-- Page Title -->
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold mb-0">Team Management</h4>
                    </div>
                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Content Management</a></li>
                            <li class="breadcrumb-item active">Team</li>
                        </ol>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav team-tabs" id="teamTabs">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tab === 'members' ? 'active' : ''; ?>" href="?tab=members<?php echo $selectedPeriod ? '&period='.$selectedPeriod : ''; ?>">
                            <i class="ti ti-users me-1"></i> Members
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tab === 'periods' ? 'active' : ''; ?>" href="?tab=periods">
                            <i class="ti ti-calendar me-1"></i> Periods
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tab === 'departments' ? 'active' : ''; ?>" href="?tab=departments">
                            <i class="ti ti-building me-1"></i> Departments
                        </a>
                    </li>
                </ul>

                <!-- Members Tab -->
                <div class="tab-content" id="membersTab" style="<?php echo $tab !== 'members' ? 'display:none;' : ''; ?>">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title">Team Members</h4>
                            <?php if ($team->canCreate()): ?>
                            <button class="btn btn-primary" onclick="openMemberModal()">
                                <i class="ti ti-plus me-1"></i> Add Member
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <div class="filter-bar">
                                <select class="form-select" id="periodFilter">
                                    <option value="">All Periods</option>
                                    <?php foreach ($periods as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" <?php echo $selectedPeriod == $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['name']); ?>
                                        <?php echo $p['isActive'] ? '(Active)' : ''; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="form-select" id="departmentFilter">
                                    <option value="">All Departments</option>
                                    <option value="core" <?php echo $selectedDepartment === 'core' ? 'selected' : ''; ?>>Core Team</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo $selectedDepartment == $d['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" class="form-control" id="searchFilter" placeholder="Search members...">
                                <button class="btn btn-secondary" onclick="applyMemberFilters()">
                                    <i class="ti ti-filter me-1"></i> Apply
                                </button>
                                <button class="btn btn-outline-secondary" onclick="clearMemberFilters()">
                                    <i class="ti ti-x me-1"></i> Clear
                                </button>
                            </div>

                            <!-- Members Table -->
                            <div class="table-responsive">
                                <table class="table table-hover" id="membersTable">
                                    <thead>
                                        <tr>
                                            <th width="50"><i class="ti ti-grip-vertical text-muted"></i></th>
                                            <th>Photo</th>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Department</th>
                                            <th>University</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="membersList">
                                        <?php if (empty($members)): ?>
                                        <tr><td colspan="8" class="text-center py-4">No team members found</td></tr>
                                        <?php else: ?>
                                        <?php foreach ($members as $m): ?>
                                        <tr data-id="<?php echo $m['id']; ?>">
                                            <td class="drag-handle"><i class="ti ti-grip-vertical"></i></td>
                                            <td>
                                                <?php if ($m['imageUrl']): ?>
                                                <img src="<?php echo htmlspecialchars($m['imageUrl']); ?>" class="member-photo" alt="">
                                                <?php else: ?>
                                                <div class="member-photo-placeholder"><i class="ti ti-user"></i></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                                            <td>
                                                <?php echo htmlspecialchars($m['position']); ?>
                                                <span class="badge bg-secondary position-badge ms-1"><?php echo htmlspecialchars($m['positionLevelLabel']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($m['departmentName'] ?? 'Core Team'); ?></td>
                                            <td><?php echo htmlspecialchars($m['university'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($m['isActive']): ?>
                                                <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-info" onclick="viewMember(<?php echo $m['id']; ?>)" title="View">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <?php if ($team->canEdit()): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editMember(<?php echo $m['id']; ?>)" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($team->canDelete()): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteMember(<?php echo $m['id']; ?>)" title="Delete">
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
                        </div>
                    </div>
                </div>

                <!-- Periods Tab -->
                <div class="tab-content" id="periodsTab" style="<?php echo $tab !== 'periods' ? 'display:none;' : ''; ?>">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title">Period Management</h4>
                            <?php if ($team->canCreate()): ?>
                            <button class="btn btn-primary" onclick="openPeriodModal()">
                                <i class="ti ti-plus me-1"></i> Add Period
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Theme</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($periods)): ?>
                                        <tr><td colspan="7" class="text-center py-4">No periods found</td></tr>
                                        <?php else: ?>
                                        <?php foreach ($periods as $p): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                            <td><code><?php echo htmlspecialchars($p['slug']); ?></code></td>
                                            <td><?php echo date('d M Y', strtotime($p['startDate'])); ?></td>
                                            <td><?php echo date('d M Y', strtotime($p['endDate'])); ?></td>
                                            <td><?php echo htmlspecialchars($p['theme'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($p['isActive']): ?>
                                                <span class="badge bg-success period-badge">✓ Active</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary period-badge">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($team->canEdit() && !$p['isActive']): ?>
                                                    <button class="btn btn-sm btn-success" onclick="activatePeriod(<?php echo $p['id']; ?>)" title="Set Active">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($team->canEdit()): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editPeriod(<?php echo $p['id']; ?>)" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($team->canDelete() && !$p['isActive']): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="deletePeriod(<?php echo $p['id']; ?>)" title="Delete">
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
                        </div>
                    </div>
                </div>

                <!-- Departments Tab -->
                <div class="tab-content" id="departmentsTab" style="<?php echo $tab !== 'departments' ? 'display:none;' : ''; ?>">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title">Department Management</h4>
                            <?php if ($team->canCreate()): ?>
                            <button class="btn btn-primary" onclick="openDepartmentModal()">
                                <i class="ti ti-plus me-1"></i> Add Department
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3"><i class="ti ti-info-circle me-1"></i> Drag and drop to reorder departments</p>
                            <div class="row" id="departmentsList">
                                <?php foreach ($departments as $d): ?>
                                <div class="col-md-6 col-lg-4 mb-3" data-id="<?php echo $d['id']; ?>">
                                    <div class="card dept-card h-100" style="border-left-color: <?php echo $d['color'] ?? '#dee2e6'; ?>;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <div class="dept-icon me-3" style="background-color: <?php echo ($d['color'] ?? '#6c757d') . '20'; ?>; color: <?php echo $d['color'] ?? '#6c757d'; ?>;">
                                                    <i class="<?php echo $d['icon'] ?? 'bi-building'; ?>"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1"><?php echo htmlspecialchars($d['name']); ?></h5>
                                                    <p class="text-muted mb-2 small"><?php echo htmlspecialchars($d['shortName'] ?? ''); ?></p>
                                                    <div class="d-flex gap-1">
                                                        <span class="badge <?php echo $d['isBiro'] ? 'bg-primary' : 'bg-info'; ?>">
                                                            <?php echo $d['isBiro'] ? 'Biro' : 'Departemen'; ?>
                                                        </span>
                                                        <?php if (!$d['isActive']): ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <?php if ($team->canEdit()): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="editDepartment(<?php echo $d['id']; ?>)"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                                        <?php endif; ?>
                                                        <?php if ($team->canDelete()): ?>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteDepartment(<?php echo $d['id']; ?>)"><i class="ti ti-trash me-2"></i>Delete</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="page-container">
                    <div class="row">
                        <div class="col-md-6 text-center text-md-start">
                            <script>document.write(new Date().getFullYear())</script> © <?php echo $credit_footer; ?>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Period Modal -->
    <div class="modal fade" id="periodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="periodModalTitle">Add Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="periodForm">
                    <div class="modal-body">
                        <input type="hidden" id="periodId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" id="periodName" name="name" required placeholder="e.g., 2024/2025">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" id="periodSlug" name="slug" placeholder="e.g., 24-25 (auto-generated if empty)">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="periodStartDate" name="startDate" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="periodEndDate" name="endDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Theme</label>
                            <input type="text" class="form-control" id="periodTheme" name="theme" placeholder="e.g., Bersatu Membangun Negeri">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="periodDescription" name="description" rows="2"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="periodIsActive" name="isActive">
                            <label class="form-check-label" for="periodIsActive">Set as Active Period</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Period</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Department Modal -->
    <div class="modal fade" id="departmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="departmentModalTitle">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="departmentForm">
                    <div class="modal-body">
                        <input type="hidden" id="departmentId" name="id">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" id="departmentName" name="name" required placeholder="e.g., Biro Komunikasi dan Informasi">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Short Name</label>
                                <input type="text" class="form-control" id="departmentShortName" name="shortName" placeholder="e.g., Kominfo">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="departmentDescription" name="description" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type *</label>
                                <select class="form-select" id="departmentIsBiro" name="isBiro" required>
                                    <option value="1">Biro</option>
                                    <option value="0">Departemen</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="departmentColor" name="color" value="#0d6efd">
                                    <input type="text" class="form-control" id="departmentColorText" placeholder="#0d6efd">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <div class="icon-picker-grid" id="iconPicker">
                                <?php foreach ($icons as $icon => $label): ?>
                                <div class="icon-picker-item" data-icon="<?php echo $icon; ?>" title="<?php echo $label; ?>">
                                    <i class="<?php echo $icon; ?>"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="departmentIcon" name="icon">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="departmentIsActive" name="isActive" checked>
                            <label class="form-check-label" for="departmentIsActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Member Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalTitle">Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="memberForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="memberId" name="id">
                        
                        <div class="row">
                            <div class="col-md-4 mb-3 text-center">
                                <div class="mb-2">
                                    <img id="memberPhotoPreview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Crect fill='%23e9ecef' width='120' height='120'/%3E%3Ctext x='50%25' y='50%25' fill='%236c757d' text-anchor='middle' dy='.3em'%3EPhoto%3C/text%3E%3C/svg%3E" 
                                         class="rounded" style="width: 120px; height: 120px; object-fit: cover;">
                                </div>
                                <input type="file" class="form-control form-control-sm" id="memberImage" name="image" accept="image/*">
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="memberName" name="name" required>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Period *</label>
                                        <select class="form-select" id="memberPeriodId" name="periodId" required>
                                            <option value="">Select Period</option>
                                            <?php foreach ($periods as $p): ?>
                                            <option value="<?php echo $p['id']; ?>" <?php echo $p['isActive'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($p['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Department</label>
                                        <select class="form-select" id="memberDepartmentId" name="departmentId" onchange="updatePositionLevels()">
                                            <option value="">Core Team</option>
                                            <?php foreach ($departments as $d): ?>
                                            <option value="<?php echo $d['id']; ?>" data-is-biro="<?php echo $d['isBiro'] ? '1' : '0'; ?>">
                                                <?php echo htmlspecialchars($d['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position Title *</label>
                                <input type="text" class="form-control" id="memberPosition" name="position" required placeholder="e.g., Ketua Umum PPI Malaysia">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position Level *</label>
                                <select class="form-select" id="memberPositionLevel" name="positionLevel" required>
                                    <!-- Options populated by JavaScript -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" id="memberBio" name="bio" rows="3"></textarea>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3">Academic Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">University</label>
                                <input type="text" class="form-control" id="memberUniversity" name="university">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Major</label>
                                <input type="text" class="form-control" id="memberMajor" name="major">
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3">Contact & Social</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="memberEmail" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="memberPhone" name="phone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" id="memberLinkedin" name="linkedin" placeholder="https://linkedin.com/in/...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" class="form-control" id="memberInstagram" name="instagram">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="memberDisplayOrder" name="displayOrder" value="0" min="0">
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="memberIsActive" name="isActive" checked>
                                    <label class="form-check-label" for="memberIsActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Member Modal -->
    <div class="modal fade" id="viewMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Member Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewMemberContent">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <?php $team->renderTheme(); ?>

    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="assets/js/team-management-v2.js"></script>
</body>
</html>
