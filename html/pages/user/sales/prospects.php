<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// Get user context
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

// Get filter data
$allOrgs = Admin::organisation_data_mini([], false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$leadSources = Data::lead_sources([], false, $DBConn);
$teams = Sales::prospect_teams(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'isActive'=>'Y'), false, $DBConn);
$territories = Sales::prospect_territories(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'isActive'=>'Y'), false, $DBConn);
$industries = Sales::prospect_industries(array('isActive'=>'Y'), false, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);

// Build filters from GET parameters
$filters = array(
    'orgDataID' => $orgDataID,
    'entityID' => $entityID
);

if (isset($_GET['businessUnitID']) && !empty($_GET['businessUnitID'])) {
    $filters['businessUnitID'] = Utility::clean_string($_GET['businessUnitID']);
}
if (isset($_GET['leadSourceID']) && !empty($_GET['leadSourceID'])) {
    $filters['leadSourceID'] = Utility::clean_string($_GET['leadSourceID']);
}
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['salesProspectStatus'] = Utility::clean_string($_GET['status']);
}
if (isset($_GET['qualification']) && !empty($_GET['qualification'])) {
    $filters['leadQualificationStatus'] = Utility::clean_string($_GET['qualification']);
}
if (isset($_GET['teamID']) && !empty($_GET['teamID'])) {
    $filters['assignedTeamID'] = Utility::clean_string($_GET['teamID']);
}
if (isset($_GET['territoryID']) && !empty($_GET['territoryID'])) {
    $filters['territoryID'] = Utility::clean_string($_GET['territoryID']);
}
if (isset($_GET['industryID']) && !empty($_GET['industryID'])) {
    $filters['industryID'] = Utility::clean_string($_GET['industryID']);
}
if (isset($_GET['ownerID']) && !empty($_GET['ownerID'])) {
    $filters['ownerID'] = Utility::clean_string($_GET['ownerID']);
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = Utility::clean_string($_GET['search']);
}

// Pagination
$pagination = array(
    'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 50,
    'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0,
    'orderBy' => isset($_GET['orderBy']) ? Utility::clean_string($_GET['orderBy']) : 'p.DateAdded',
    'orderDir' => isset($_GET['orderDir']) ? Utility::clean_string($_GET['orderDir']) : 'DESC'
);

// Get prospects with advanced filtering
$prospectsData = Sales::sales_prospects_advanced($filters, $pagination, $DBConn);
$prospects = $prospectsData['data'];
$totalProspects = $prospectsData['total'];

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">Prospecting Dashboard
            <span class="badge bg-primary-transparent ms-2"><?= $totalProspects ?> Prospects</span>
        </h1>
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= "{$base}html/?s=user&ss=sales&p=home" ?>">Sales</a></li>
                <li class="breadcrumb-item active" aria-current="page">Prospects</li>
            </ol>
        </nav>
    </div>
    <div class="ms-md-1 ms-0 mt-md-0 mt-2">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#quickAddProspectModal">
                <i class="ri-add-line me-1"></i> Quick Add
            </button>
            <button type="button" class="btn btn-primary btn-wave dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#quickAddProspectModal">
                    <i class="ri-flashlight-line me-2"></i>Quick Add
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addProspectWizardModal">
                    <i class="ri-guide-line me-2"></i>Full Wizard
                </a></li>
            </ul>
        </div>
        <button type="button" class="btn btn-secondary btn-wave ms-2" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
            <i class="ri-upload-line me-1"></i> Import
        </button>
        <button type="button" class="btn btn-info btn-wave" id="exportProspectsBtn">
            <i class="ri-download-line me-1"></i> Export
        </button>
        <button type="button" class="btn btn-outline-primary btn-wave ms-2" data-bs-toggle="modal" data-bs-target="#prospectHelpModal" title="Help & Documentation">
            <i class="ri-question-line me-1"></i> Help
        </button>
    </div>
</div>

<!-- Advanced Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header d-flex justify-content-between">
        <div class="card-title">
            Advanced Filters
            <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top"
               title="Use filters to narrow down prospects by various criteria. Click 'Toggle Filters' to expand/collapse."></i>
        </div>
        <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false">
            <i class="ri-filter-3-line"></i> Toggle Filters
        </a>
    </div>
    <div class="card-body collapse" id="advancedFilters">
        <form method="GET" action="" id="filterForm">
            <input type="hidden" name="s" value="<?= $s ?>">
            <input type="hidden" name="ss" value="<?= $ss ?>">
            <input type="hidden" name="p" value="<?= $p ?>">

            <div class="row g-3">
                <!-- Organization/Entity -->
                <?php if($isValidAdmin || $isSuperAdmin): ?>
                <div class="col-md-3">
                    <label class="form-label">Organization</label>
                    <select class="form-select" name="orgDataID" id="orgDataIDFilter">
                        <?= Form::populate_select_element_from_object($allOrgs, 'orgDataID', 'orgName', $orgDataID, '', 'All Organizations') ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Business Unit -->
                <div class="col-md-3">
                    <label class="form-label">Business Unit</label>
                    <select class="form-select" name="businessUnitID" id="businessUnitIDFilter">
                        <?= Form::populate_select_element_from_object($businessUnits, 'businessUnitID', 'businessUnitName', $filters['businessUnitID'] ?? '', '', 'All Business Units') ?>
                    </select>
                </div>

                <!-- Lead Source -->
                <div class="col-md-3">
                    <label class="form-label">Lead Source</label>
                    <select class="form-select" name="leadSourceID" id="leadSourceIDFilter">
                        <?= Form::populate_select_element_from_object($leadSources, 'leadSourceID', 'leadSourceName', $filters['leadSourceID'] ?? '', '', 'All Sources') ?>
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="open" <?= (isset($filters['salesProspectStatus']) && $filters['salesProspectStatus'] == 'open') ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= (isset($filters['salesProspectStatus']) && $filters['salesProspectStatus'] == 'closed') ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>

                <!-- Qualification Status -->
                <div class="col-md-3">
                    <label class="form-label">Qualification</label>
                    <select class="form-select" name="qualification" id="qualificationFilter">
                        <option value="">All Qualifications</option>
                        <option value="unqualified" <?= (isset($filters['leadQualificationStatus']) && $filters['leadQualificationStatus'] == 'unqualified') ? 'selected' : '' ?>>Unqualified</option>
                        <option value="cold" <?= (isset($filters['leadQualificationStatus']) && $filters['leadQualificationStatus'] == 'cold') ? 'selected' : '' ?>>Cold</option>
                        <option value="warm" <?= (isset($filters['leadQualificationStatus']) && $filters['leadQualificationStatus'] == 'warm') ? 'selected' : '' ?>>Warm</option>
                        <option value="hot" <?= (isset($filters['leadQualificationStatus']) && $filters['leadQualificationStatus'] == 'hot') ? 'selected' : '' ?>>Hot</option>
                        <option value="qualified" <?= (isset($filters['leadQualificationStatus']) && $filters['leadQualificationStatus'] == 'qualified') ? 'selected' : '' ?>>Qualified</option>
                    </select>
                </div>

                <!-- Team -->
                <div class="col-md-3">
                    <label class="form-label">Team</label>
                    <select class="form-select" name="teamID" id="teamIDFilter">
                        <?= Form::populate_select_element_from_object($teams, 'teamID', 'teamName', $filters['assignedTeamID'] ?? '', '', 'All Teams') ?>
                    </select>
                </div>

                <!-- Territory -->
                <div class="col-md-3">
                    <label class="form-label">Territory</label>
                    <select class="form-select" name="territoryID" id="territoryIDFilter">
                        <?= Form::populate_select_element_from_object($territories, 'territoryID', 'territoryName', $filters['territoryID'] ?? '', '', 'All Territories') ?>
                    </select>
                </div>

                <!-- Industry -->
                <div class="col-md-3">
                    <label class="form-label">Industry</label>
                    <select class="form-select" name="industryID" id="industryIDFilter">
                        <?= Form::populate_select_element_from_object($industries, 'industryID', 'industryName', $filters['industryID'] ?? '', '', 'All Industries') ?>
                    </select>
                </div>

                <!-- Owner -->
                <div class="col-md-3">
                    <label class="form-label">Owner</label>
                    <select class="form-select" name="ownerID" id="ownerIDFilter">
                        <?= Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', $filters['ownerID'] ?? '', '', 'All Owners') ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" id="searchFilter" placeholder="Search by name, email, or case name..." value="<?= $filters['search'] ?? '' ?>">
                </div>

                <!-- Action Buttons -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="ri-search-line me-1"></i> Apply Filters
                    </button>
                    <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}" ?>" class="btn btn-secondary">
                        <i class="ri-refresh-line me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Prospects Table Card -->
<div class="card custom-card">
    <div class="card-header d-flex justify-content-between">
        <div class="card-title">Prospects List</div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="bulkActionSelect" style="width: auto;">
                <option value="">Bulk Actions</option>
                <option value="assign_team">Assign to Team</option>
                <option value="update_status">Update Status</option>
                <option value="update_qualification">Update Qualification</option>
                <option value="export_selected">Export Selected</option>
                <option value="delete_selected">Delete Selected</option>
            </select>
            <button type="button" class="btn btn-sm btn-primary" id="applyBulkAction" disabled>Apply</button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-hover text-nowrap" id="prospectsTable" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllProspects"></th>
                        <th>Prospect Name</th>
                        <th>Company/Case</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Qualification</th>
                        <th>Lead Score</th>
                        <th>Est. Value</th>
                        <th>Source</th>
                        <th>Team</th>
                        <th>Owner</th>
                        <th>Last Contact</th>
                        <th>Next Follow-up</th>
                        <th>Days in Pipeline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($prospects && count($prospects) > 0): ?>
                        <?php foreach ($prospects as $prospect): ?>
                            <tr>
                                <td><input type="checkbox" class="prospect-checkbox" value="<?= $prospect->salesProspectID ?>"></td>
                                <td>
                                    <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=prospect_details&prospectID={$prospect->salesProspectID}" ?>" class="fw-semibold">
                                        <?= htmlspecialchars($prospect->salesProspectName) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($prospect->prospectCaseName ?? '-') ?></td>
                                <td><?= htmlspecialchars($prospect->prospectEmail ?? '-') ?></td>
                                <td><?= htmlspecialchars($prospect->prospectPhone ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $prospect->salesProspectStatus == 'open' ? 'success' : 'secondary' ?>-transparent">
                                        <?= ucfirst($prospect->salesProspectStatus) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $qualBadge = array(
                                        'unqualified' => 'secondary',
                                        'cold' => 'info',
                                        'warm' => 'warning',
                                        'hot' => 'danger',
                                        'qualified' => 'success'
                                    );
                                    $badgeColor = $qualBadge[$prospect->leadQualificationStatus] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeColor ?>-transparent">
                                        <?= ucfirst($prospect->leadQualificationStatus) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= $prospect->leadScore ?></span>
                                        <div class="progress" style="width: 50px; height: 6px;">
                                            <div class="progress-bar bg-<?= $prospect->leadScore >= 70 ? 'success' : ($prospect->leadScore >= 40 ? 'warning' : 'danger') ?>"
                                                 style="width: <?= $prospect->leadScore ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= number_format($prospect->estimatedValue ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($prospect->leadSourceName ?? '-') ?></td>
                                <td><?= htmlspecialchars($prospect->teamName ?? '-') ?></td>
                                <td><?= htmlspecialchars($prospect->ownerName ?? '-') ?></td>
                                <td><?= $prospect->lastContactDate ? date('M d, Y', strtotime($prospect->lastContactDate)) : '-' ?></td>
                                <td>
                                    <?php if ($prospect->nextFollowUpDate): ?>
                                        <span class="badge bg-<?= $prospect->followUpStatus == 'Overdue' ? 'danger' : ($prospect->followUpStatus == 'Due Today' ? 'warning' : 'info') ?>-transparent">
                                            <?= date('M d, Y', strtotime($prospect->nextFollowUpDate)) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= $prospect->daysInPipeline ?> days</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=prospect_details&prospectID={$prospect->salesProspectID}" ?>"
                                           class="btn btn-sm btn-primary-light" title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-info-light editProspectBtn"
                                                data-prospect-id="<?= $prospect->salesProspectID ?>" title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success-light logInteractionBtn"
                                                data-prospect-id="<?= $prospect->salesProspectID ?>" title="Log Interaction">
                                            <i class="ri-chat-3-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="16" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ri-inbox-line fs-1"></i>
                                    <p class="mt-2">No prospects found matching your criteria</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalProspects > $pagination['limit']): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing <?= $pagination['offset'] + 1 ?> to <?= min($pagination['offset'] + $pagination['limit'], $totalProspects) ?> of <?= $totalProspects ?> prospects
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <?php
                    $totalPages = ceil($totalProspects / $pagination['limit']);
                    $currentPage = floor($pagination['offset'] / $pagination['limit']) + 1;

                    // Previous button
                    if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, array('offset' => ($currentPage - 2) * $pagination['limit']))) ?>">Previous</a>
                        </li>
                    <?php endif;

                    // Page numbers
                    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, array('offset' => ($i - 1) * $pagination['limit']))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor;

                    // Next button
                    if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, array('offset' => $currentPage * $pagination['limit']))) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all prospects checkbox
    const selectAll = document.getElementById('selectAllProspects');
    const checkboxes = document.querySelectorAll('.prospect-checkbox');
    const bulkActionSelect = document.getElementById('bulkActionSelect');
    const applyBulkActionBtn = document.getElementById('applyBulkAction');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionButton();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionButton);
    });

    function updateBulkActionButton() {
        const checkedCount = document.querySelectorAll('.prospect-checkbox:checked').length;
        applyBulkActionBtn.disabled = checkedCount === 0;
        applyBulkActionBtn.textContent = checkedCount > 0 ? `Apply (${checkedCount})` : 'Apply';
    }

    // Apply bulk action
    applyBulkActionBtn.addEventListener('click', function() {
        const action = bulkActionSelect.value;
        const selectedIds = Array.from(document.querySelectorAll('.prospect-checkbox:checked')).map(cb => cb.value);

        if (!action || selectedIds.length === 0) {
            return;
        }

        // Handle different bulk actions
        switch(action) {
            case 'assign_team':
                // Show team assignment modal
                console.log('Assign to team:', selectedIds);
                break;
            case 'update_status':
                // Show status update modal
                console.log('Update status:', selectedIds);
                break;
            case 'update_qualification':
                // Show qualification update modal
                console.log('Update qualification:', selectedIds);
                break;
            case 'export_selected':
                // Export selected prospects
                exportProspects(selectedIds);
                break;
            case 'delete_selected':
                // Confirm and delete
                if (confirm(`Are you sure you want to delete ${selectedIds.length} prospect(s)?`)) {
                    deleteProspects(selectedIds);
                }
                break;
        }
    });

    // Export functionality
    document.getElementById('exportProspectsBtn').addEventListener('click', function() {
        const currentFilters = new URLSearchParams(window.location.search);
        window.location.href = `<?= "{$base}php/scripts/sales/export_prospects.php" ?>?${currentFilters.toString()}`;
    });

    function exportProspects(ids) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= "{$base}php/scripts/sales/export_prospects.php" ?>';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'prospectIDs';
        input.value = JSON.stringify(ids);

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function deleteProspects(ids) {
        // Implement delete functionality
        console.log('Delete prospects:', ids);
    }

    // Edit prospect buttons
    const editButtons = document.querySelectorAll('.editProspectBtn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const prospectID = this.getAttribute('data-prospect-id');
            if (prospectID && typeof loadProspectForEdit === 'function') {
                loadProspectForEdit(prospectID);
            }
        });
    });
});
</script>

<?php
// Include modals
include "includes/scripts/sales/modals/quick_add_prospect.php";
include "includes/scripts/sales/modals/add_prospect_wizard.php";
include "includes/scripts/sales/modals/bulk_import_prospects.php";
include "includes/scripts/sales/modals/edit_prospect.php";
include "includes/scripts/sales/modals/prospect_help.php";
?>

<!-- SweetAlert2 Library -->
<link rel="stylesheet" href="<?= $base ?>assets/libs/sweetalert2/sweetalert2.min.css">
<script src="<?= $base ?>assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add drag-to-scroll functionality for table
    const tableContainer = document.querySelector('.table-responsive');
    if (tableContainer) {
        let isDown = false;
        let startX;
        let scrollLeft;

        tableContainer.style.cursor = 'grab';

        tableContainer.addEventListener('mousedown', (e) => {
            // Don't interfere with checkbox or link clicks
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'A' || e.target.closest('a')) {
                return;
            }

            isDown = true;
            tableContainer.style.cursor = 'grabbing';
            startX = e.pageX - tableContainer.offsetLeft;
            scrollLeft = tableContainer.scrollLeft;
            e.preventDefault();
        });

        tableContainer.addEventListener('mouseleave', () => {
            isDown = false;
            tableContainer.style.cursor = 'grab';
        });

        tableContainer.addEventListener('mouseup', () => {
            isDown = false;
            tableContainer.style.cursor = 'grab';
        });

        tableContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - tableContainer.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed multiplier
            tableContainer.scrollLeft = scrollLeft - walk;
        });
    }
});
</script>


