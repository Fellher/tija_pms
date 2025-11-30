<?php
/**
 * Processes Management - Admin
 *
 * Manage APQC processes and process groups
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

if (!$isAdmin && !$isValidAdmin) {
    Alert::error("Access denied. Administrator privileges required.", true);
    return;
}

// Include help component
include 'includes/components/operational_help.php';

global $DBConn, $userID, $orgDataID;

// Get filters
$functionalArea = $_GET['functionalArea'] ?? '';
$categoryID = $_GET['categoryID'] ?? '';
$search = $_GET['search'] ?? '';

// Get functional areas from database
$functionalAreas = [];
try {
    $faCols = ['functionalAreaID', 'functionalAreaCode', 'functionalAreaName', 'functionalAreaDescription', 'isShared', 'isActive', 'displayOrder'];
    $faWhere = ['isActive' => 'Y'];
    $allFunctionalAreas = $DBConn->retrieve_db_table_rows('tija_functional_areas', $faCols, $faWhere);

    // Filter by organization if needed
    if ($orgDataID) {
        $linkedAreas = $DBConn->retrieve_db_table_rows(
            'tija_organization_functional_areas',
            ['functionalAreaID'],
            ['orgDataID' => $orgDataID, 'isActive' => 'Y']
        );
        // Handle both object and array formats
        $linkedIDs = array_map(function($item) {
            return is_object($item) ? $item->functionalAreaID : $item['functionalAreaID'];
        }, $linkedAreas ?: []);

        $functionalAreas = array_filter($allFunctionalAreas, function($fa) use ($linkedIDs) {
            $isShared = is_object($fa) ? ($fa->isShared ?? 'N') : ($fa['isShared'] ?? 'N');
            $faID = is_object($fa) ? ($fa->functionalAreaID ?? null) : ($fa['functionalAreaID'] ?? null);
            return $isShared === 'Y' || in_array($faID, $linkedIDs);
        });
    } else {
        $functionalAreas = array_filter($allFunctionalAreas, function($fa) {
            $isShared = is_object($fa) ? ($fa->isShared ?? 'N') : ($fa['isShared'] ?? 'N');
            return $isShared === 'Y';
        });
    }

    // Sort by display order and name
    usort($functionalAreas, function($a, $b) {
        $aDisplayOrder = is_object($a) ? ($a->displayOrder ?? 0) : ($a['displayOrder'] ?? 0);
        $bDisplayOrder = is_object($b) ? ($b->displayOrder ?? 0) : ($b['displayOrder'] ?? 0);
        if ($aDisplayOrder != $bDisplayOrder) {
            return $aDisplayOrder <=> $bDisplayOrder;
        }
        $aName = is_object($a) ? ($a->functionalAreaName ?? '') : ($a['functionalAreaName'] ?? '');
        $bName = is_object($b) ? ($b->functionalAreaName ?? '') : ($b['functionalAreaName'] ?? '');
        return strcmp($aName, $bName);
    });
    $functionalAreas = array_values($functionalAreas);
} catch (Exception $e) {
    // Fallback to empty array if table doesn't exist yet
    $functionalAreas = [];
}

// Get categories for filter
$categories = BAUTaxonomy::getCategories([], false, $DBConn);

// Get all process groups for modal dropdown
$allProcessGroups = [];
if ($categories) {
    foreach ($categories as $cat) {
        // Handle both object and array access
        $catID = is_object($cat) ? ($cat->categoryID ?? null) : ($cat['categoryID'] ?? null);
        if (!$catID) continue;

        $groups = BAUTaxonomy::getProcessGroups($catID, ['Suspended' => 'N'], false, $DBConn);
        if ($groups) {
            foreach ($groups as $group) {
                // Convert object to array if needed for array_merge
                if (is_object($group)) {
                    $groupArray = (array)$group;
                } else {
                    $groupArray = $group;
                }
                $allProcessGroups[] = array_merge($groupArray, ['categoryID' => $catID]);
            }
        }
    }
}

// Get processes - Use searchProcesses to get all processes with filters
$processes = [];
if (!empty($search)) {
    $processes = BAUTaxonomy::searchProcesses($search, ['Suspended' => 'N'], $DBConn);
} else {
    // Get all processes by querying all process groups
    $filters = ['Suspended' => 'N'];
    if ($functionalArea) $filters['functionalArea'] = $functionalArea;

    // Get all processes from all groups
    foreach ($allProcessGroups as $group) {
        // Handle both object and array access
        $groupCatID = is_object($group) ? ($group->categoryID ?? null) : ($group['categoryID'] ?? null);
        $groupID = is_object($group) ? ($group->processGroupID ?? null) : ($group['processGroupID'] ?? null);
        $groupName = is_object($group) ? ($group->processGroupName ?? '') : ($group['processGroupName'] ?? '');

        if ($categoryID && $groupCatID != $categoryID) {
            continue; // Skip if category filter is set
        }

        $groupProcesses = BAUTaxonomy::getProcesses($groupID, $filters, false, $DBConn);
        if ($groupProcesses) {
            // Find category name
            $catName = '';
            if ($categories) {
                foreach ($categories as $cat) {
                    $catID = is_object($cat) ? ($cat->categoryID ?? null) : ($cat['categoryID'] ?? null);
                    if ($catID == $groupCatID) {
                        $catName = is_object($cat) ? ($cat->categoryName ?? '') : ($cat['categoryName'] ?? '');
                        break;
                    }
                }
            }

            foreach ($groupProcesses as $proc) {
                // Convert object to array if needed
                if (is_object($proc)) {
                    $proc = (array)$proc;
                }
                $proc['categoryID'] = $groupCatID;
                $proc['categoryName'] = $catName;
                $proc['processGroupName'] = $groupName;
                $processes[] = $proc;
            }
        }
    }
}

// If no processes found and no filters, try to get at least some processes
if (empty($processes) && empty($functionalArea) && empty($categoryID)) {
    // Get first few process groups and their processes
    $sampleGroups = array_slice($allProcessGroups, 0, 5);
    foreach ($sampleGroups as $group) {
        // Handle both object and array access
        $groupCatID = is_object($group) ? ($group->categoryID ?? null) : ($group['categoryID'] ?? null);
        $groupID = is_object($group) ? ($group->processGroupID ?? null) : ($group['processGroupID'] ?? null);
        $groupName = is_object($group) ? ($group->processGroupName ?? '') : ($group['processGroupName'] ?? '');

        $groupProcesses = BAUTaxonomy::getProcesses($groupID, ['Suspended' => 'N'], false, $DBConn);
        if ($groupProcesses) {
            // Find category name
            $catName = '';
            if ($categories) {
                foreach ($categories as $cat) {
                    $catID = is_object($cat) ? ($cat->categoryID ?? null) : ($cat['categoryID'] ?? null);
                    if ($catID == $groupCatID) {
                        $catName = is_object($cat) ? ($cat->categoryName ?? '') : ($cat['categoryName'] ?? '');
                        break;
                    }
                }
            }

            foreach ($groupProcesses as $proc) {
                // Convert object to array if needed
                if (is_object($proc)) {
                    $proc = (array)$proc;
                }
                $proc['categoryID'] = $groupCatID;
                $proc['categoryName'] = $catName;
                $proc['processGroupName'] = $groupName;
                $processes[] = $proc;
            }
        }
    }
}

$pageTitle = "Process Management";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        Manage APQC processes and process groups for your functional area.
                        <?php echo renderHelpPopover('APQC Process Classification', 'APQC (American Productivity & Quality Center) provides a standard taxonomy for classifying business processes. Each process has a unique ID (e.g., 8.6.1 for Cash Management). You can use standard APQC processes or create custom ones for your organization.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Processes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Processes</p>
                            <h4 class="mb-2"><?php echo is_array($processes) ? count($processes) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-flow-chart-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Active Processes</p>
                            <h4 class="mb-2 text-success"><?php
                                $active = is_array($processes) ? array_filter($processes, function($p) {
                                    return ($p['isActive'] ?? 'N') === 'Y';
                                }) : [];
                                echo count($active);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-checkbox-circle-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Categories</p>
                            <h4 class="mb-2"><?php echo is_array($categories) ? count($categories) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-folder-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Custom Processes</p>
                            <h4 class="mb-2 text-warning"><?php
                                $custom = is_array($processes) ? array_filter($processes, function($p) {
                                    return ($p['isCustom'] ?? 'N') === 'Y';
                                }) : [];
                                echo count($custom);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-file-edit-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="input-group" style="width: 300px;">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search processes..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <select class="form-select" id="categoryFilter" style="width: 200px;">
                                <option value="">All Categories</option>
                                <?php if ($categories): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <?php
                                        $catID = is_object($cat) ? ($cat->categoryID ?? '') : ($cat['categoryID'] ?? '');
                                        $catName = is_object($cat) ? ($cat->categoryName ?? '') : ($cat['categoryName'] ?? '');
                                        ?>
                                        <option value="<?php echo $catID; ?>" <?php echo $categoryID == $catID ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($catName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <select class="form-select" id="functionalAreaFilter" style="width: 200px;" title="Filter by functional area">
                                <option value="">All Functional Areas</option>
                                <option value="Finance" <?php echo $functionalArea == 'Finance' ? 'selected' : ''; ?>>Finance</option>
                                <option value="HR" <?php echo $functionalArea == 'HR' ? 'selected' : ''; ?>>HR</option>
                                <option value="IT" <?php echo $functionalArea == 'IT' ? 'selected' : ''; ?>>IT</option>
                                <option value="Sales" <?php echo $functionalArea == 'Sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="Marketing" <?php echo $functionalArea == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Legal" <?php echo $functionalArea == 'Legal' ? 'selected' : ''; ?>>Legal</option>
                                <option value="Facilities" <?php echo $functionalArea == 'Facilities' ? 'selected' : ''; ?>>Facilities</option>
                            </select>
                            <?php echo renderHelpIcon('The department or business unit responsible for processes. This helps organize processes and assign function heads.', 'top'); ?>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#processModal" data-action="open-process-modal" data-process-action="create">
                                <i class="ri-add-line me-1"></i>Create Process
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Processes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Processes</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($processes)): ?>
                        <div class="text-center py-5">
                            <i class="ri-flow-chart-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Processes Found</h5>
                            <p class="text-muted">Get started by creating your first process.</p>
                            <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#processModal" data-action="open-process-modal" data-process-action="create">
                                <i class="ri-add-line me-1"></i>Create Process
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="processesTable">
                                <thead>
                                    <tr>
                                        <th>Process ID</th>
                                        <th>Process Name</th>
                                        <th>Category</th>
                                        <th>Functional Area</th>
                                        <th>Status</th>
                                        <th>Type</th>
                                        <th width="150" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($processes as $process): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($process['processID'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($process['processName'] ?? 'Unknown'); ?></div>
                                                <?php if (!empty($process['processDescription'])): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($process['processDescription'], 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($process['categoryName'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($process['functionalArea'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($process['isActive'] ?? 'N') === 'Y' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ($process['isActive'] ?? 'N') === 'Y' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($process['isCustom'] ?? 'N') === 'Y' ? 'warning' : 'primary'; ?>">
                                                    <?php echo ($process['isCustom'] ?? 'N') === 'Y' ? 'Custom' : 'Standard'; ?>
                                                </span>
                                                <?php if (($process['isCustom'] ?? 'N') === 'Y'): ?>
                                                    <?php echo renderHelpIcon('Custom processes are organization-specific processes that don\'t fit the standard APQC taxonomy. Both standard and custom processes can be used to create operational task templates.', 'left'); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="?s=admin&ss=operational&p=processes&action=view&id=<?php echo $process['processID']; ?>"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-primary" title="Edit"
                                                            data-bs-toggle="modal" data-bs-target="#processModal"
                                                            data-action="open-process-modal" data-process-action="edit" data-process-id="<?php echo $process['processID']; ?>">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-action="delete-process" data-process-id="<?php echo $process['processID']; ?>" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process Modal -->
<div class="modal fade" id="processModal" tabindex="-1" aria-labelledby="processModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processModalLabel">Create Process</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="processForm">
                    <input type="hidden" id="processFormAction" name="action" value="create">
                    <input type="hidden" id="processFormProcessID" name="processID" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="processCode" class="form-label">
                                Process Code <span class="text-danger">*</span>
                                <?php echo renderHelpPopover('Process Code', 'A unique identifier for the process. For standard APQC processes, use the official APQC code (e.g., 7.3.1). For custom processes, use your organization\'s naming convention (e.g., CUSTOM-001). This code is used as the processID and must be unique. Click "Generate" to auto-create the next sequential code based on the selected process group.', 'right'); ?>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="processCode" name="processCode" required
                                       placeholder="e.g., 7.3.1 or CUSTOM-001">
                                <button type="button" class="btn btn-outline-secondary" data-action="generate-process-code"
                                        title="Auto-generate process code">
                                    <i class="ri-magic-line"></i> Generate
                                </button>
                            </div>
                            <small class="text-muted">APQC code for standard processes, or custom code for organization-specific processes. This will be used as the processID.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="processName" class="form-label">
                                Process Name <span class="text-danger">*</span>
                                <?php echo renderHelpPopover('Process Name', 'A descriptive name for the process that clearly identifies what it does. This name will be displayed in lists, reports, and when selecting processes for templates. Use clear, action-oriented names (e.g., "Manage Payroll", "Process Invoices", "Handle Customer Complaints").', 'right'); ?>
                            </label>
                            <input type="text" class="form-control" id="processName" name="processName" required
                                   placeholder="e.g., Manage Payroll">
                        </div>

                        <div class="col-md-6">
                            <label for="categoryID" class="form-label">
                                Category <span class="text-danger">*</span>
                                <?php echo renderHelpPopover('APQC Category', 'Categories are the top-level domains in the APQC Process Classification Framework (e.g., "7.0 Develop and Manage Human Capital"). Categories group related process groups together. Select an existing category or click the + button to create a new one. Categories help organize processes hierarchically.', 'right'); ?>
                            </label>
                            <div class="input-group">
                                <select class="form-select" id="categoryID" name="categoryID" required onchange="loadProcessGroups()">
                                    <option value="">Select Category</option>
                                    <?php if ($categories): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <?php
                                            $catID = is_object($cat) ? ($cat->categoryID ?? '') : ($cat['categoryID'] ?? '');
                                            $catCode = is_object($cat) ? ($cat->categoryCode ?? '') : ($cat['categoryCode'] ?? '');
                                            $catName = is_object($cat) ? ($cat->categoryName ?? '') : ($cat['categoryName'] ?? '');
                                            ?>
                                            <option value="<?php echo $catID; ?>">
                                                <?php echo htmlspecialchars($catCode . ' - ' . $catName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#categoryModal"
                                        data-action="open-category-modal" data-category-action="create" title="Create New Category">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="processGroupID" class="form-label">
                                Process Group <span class="text-danger">*</span>
                                <?php echo renderHelpPopover('Process Group', 'Process Groups are functional areas within a category (e.g., "7.3 Reward and Retain Employees" within category "7.0 Develop and Manage Human Capital"). Process groups contain related processes. You must select a category first to see its process groups. Click the + button to create a new process group for the selected category.', 'right'); ?>
                            </label>
                            <div class="input-group">
                                <select class="form-select" id="processGroupID" name="processGroupID" required data-action="update-process-code-hint">
                                    <option value="">Select Category First</option>
                                </select>
                                <button type="button" class="btn btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#processGroupModal"
                                        data-action="open-process-group-modal" data-process-group-action="create" title="Create New Process Group">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="functionalArea" class="form-label">
                                Functional Area <span class="text-danger">*</span>
                                <?php echo renderHelpPopover('Functional Area', 'The department or business unit responsible for this process. Functional areas help organize processes and enable assignment of function heads who oversee processes in their area. Examples: Finance, HR, IT, Sales, Marketing, Legal, Facilities. This determines who can manage and approve processes.', 'right'); ?>
                            </label>
                            <div class="input-group">
                                <select class="form-select" id="functionalArea" name="functionalAreaID" required>
                                    <option value="">Select Functional Area</option>
                                    <?php if ($functionalAreas): ?>
                                        <?php foreach ($functionalAreas as $fa): ?>
                                            <?php
                                            $faID = is_object($fa) ? ($fa->functionalAreaID ?? '') : ($fa['functionalAreaID'] ?? '');
                                            $faName = is_object($fa) ? ($fa->functionalAreaName ?? '') : ($fa['functionalAreaName'] ?? '');
                                            ?>
                                            <option value="<?php echo htmlspecialchars($faID); ?>">
                                                <?php echo htmlspecialchars($faName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary"
                                        data-action="open-functional-areas" data-url="<?php echo $base; ?>html/?s=admin&ss=operational&p=functional_areas"
                                        title="Manage Functional Areas">
                                    <i class="ri-settings-3-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="isCustom" class="form-label">
                                Process Type
                                <?php echo renderHelpPopover('Process Type', 'Standard processes follow the official APQC Process Classification Framework taxonomy. Custom processes are organization-specific processes that don\'t fit the standard APQC structure. Both types can be used to create operational task templates. Custom processes are marked with a warning badge for easy identification.', 'right'); ?>
                            </label>
                            <select class="form-select" id="isCustom" name="isCustom">
                                <option value="N">Standard (APQC)</option>
                                <option value="Y">Custom (Organization-specific)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="isActive" class="form-label">
                                Status
                                <?php echo renderHelpPopover('Process Status', 'Active processes are available for use in templates and workflows. Inactive processes are hidden from selection but remain in the system for historical reference. Set to Inactive if a process is deprecated or no longer in use.', 'right'); ?>
                            </label>
                            <select class="form-select" id="isActive" name="isActive">
                                <option value="Y">Active</option>
                                <option value="N">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="processDescription" class="form-label">
                                Description
                                <?php echo renderHelpPopover('Process Description', 'Provide a detailed description of what this process does, its purpose, key activities, and expected outcomes. This helps users understand when and how to use this process. Good descriptions include: what the process accomplishes, who is involved, key steps or activities, and any important notes or requirements.', 'right'); ?>
                            </label>
                            <textarea class="form-control" id="processDescription" name="processDescription" rows="4"
                                      placeholder="Describe the process, its purpose, and key activities..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-danger d-none mt-3" id="processFormError"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="processFormSubmit">
                    <span class="spinner-border spinner-border-sm d-none" id="processFormSpinner" role="status"></span>
                    <span id="processFormSubmitText">Create Process</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Create Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="categoryFormAction" name="action" value="create">
                    <input type="hidden" id="categoryFormID" name="categoryID" value="">

                    <div class="mb-3">
                        <label for="categoryCode" class="form-label">
                            Category Code <span class="text-danger">*</span>
                            <?php echo renderHelpPopover('Category Code', 'The APQC category code (e.g., 7.0, 8.0, 9.0). This is the top-level identifier in the APQC Process Classification Framework. Each category represents a major business domain. The code must be unique and typically follows the pattern X.0 where X is a number.', 'right'); ?>
                        </label>
                        <input type="text" class="form-control" id="categoryCode" name="categoryCode" required
                               placeholder="e.g., 7.0">
                        <small class="text-muted">APQC category code (e.g., 7.0 for Develop and Manage Human Capital)</small>
                    </div>

                    <div class="mb-3">
                        <label for="categoryName" class="form-label">
                            Category Name <span class="text-danger">*</span>
                            <?php echo renderHelpPopover('Category Name', 'A descriptive name for the category that clearly identifies the business domain it represents. This name will be displayed in dropdowns and lists. Use the official APQC category name when following the standard taxonomy, or create a clear, descriptive name for custom categories.', 'right'); ?>
                        </label>
                        <input type="text" class="form-control" id="categoryName" name="categoryName" required
                               placeholder="e.g., Develop and Manage Human Capital">
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">
                            Description
                            <?php echo renderHelpPopover('Category Description', 'Provide a detailed description of what this category encompasses. Explain the scope of processes that belong to this category, its purpose in the organization, and how it relates to other categories. This helps users understand the category\'s role in the taxonomy.', 'right'); ?>
                        </label>
                        <textarea class="form-control" id="categoryDescription" name="categoryDescription" rows="3"
                                  placeholder="Describe the category..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoryDisplayOrder" class="form-label">
                                    Display Order
                                    <?php echo renderHelpPopover('Display Order', 'Controls the order in which categories appear in dropdowns and lists. Lower numbers appear first. Use this to organize categories in a logical sequence (e.g., 1, 2, 3... or 10, 20, 30 for easy insertion). Default is 0.', 'right'); ?>
                                </label>
                                <input type="number" class="form-control" id="categoryDisplayOrder" name="displayOrder" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoryIsActive" class="form-label">
                                    Status
                                    <?php echo renderHelpPopover('Category Status', 'Active categories are available for selection when creating processes. Inactive categories are hidden from dropdowns but remain in the system for historical reference. Set to Inactive if a category is deprecated or no longer used.', 'right'); ?>
                                </label>
                                <select class="form-select" id="categoryIsActive" name="isActive">
                                    <option value="Y">Active</option>
                                    <option value="N">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger d-none mt-3" id="categoryFormError"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="categoryFormSubmit">
                    <span class="spinner-border spinner-border-sm d-none" id="categoryFormSpinner" role="status"></span>
                    <span id="categoryFormSubmitText">Create Category</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Process Group Modal -->
<div class="modal fade" id="processGroupModal" tabindex="-1" aria-labelledby="processGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processGroupModalLabel">Create Process Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="processGroupForm">
                    <input type="hidden" id="processGroupFormAction" name="action" value="create">
                    <input type="hidden" id="processGroupFormID" name="processGroupID" value="">

                    <div class="mb-3">
                        <label for="processGroupCategoryID" class="form-label">
                            Category <span class="text-danger">*</span>
                            <?php echo renderHelpPopover('Category Selection', 'Select the APQC category this process group belongs to. Process groups are functional areas within a category. For example, category "7.0 Develop and Manage Human Capital" contains process groups like "7.3 Reward and Retain Employees". The category determines the code prefix for this process group.', 'right'); ?>
                        </label>
                        <select class="form-select" id="processGroupCategoryID" name="categoryID" required onchange="generateProcessGroupCode()">
                            <option value="">Select Category</option>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <?php
                                    $catID = is_object($cat) ? ($cat->categoryID ?? '') : ($cat['categoryID'] ?? '');
                                    $catCode = is_object($cat) ? ($cat->categoryCode ?? '') : ($cat['categoryCode'] ?? '');
                                    $catName = is_object($cat) ? ($cat->categoryName ?? '') : ($cat['categoryName'] ?? '');
                                    ?>
                                    <option value="<?php echo $catID; ?>">
                                        <?php echo htmlspecialchars($catCode . ' - ' . $catName); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="processGroupCode" class="form-label">
                            Process Group Code <span class="text-danger">*</span>
                            <?php echo renderHelpPopover('Process Group Code', 'A unique code for the process group within its category. For APQC standard groups, use the official code (e.g., 7.3). The code typically follows the pattern {categoryCode}.{number}. Click "Generate" to auto-create the next sequential code based on the selected category. This code must be unique across all process groups.', 'right'); ?>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="processGroupCode" name="processGroupCode" required
                                   placeholder="e.g., 7.3">
                            <button type="button" class="btn btn-outline-secondary" data-action="generate-process-group-code"
                                    title="Auto-generate process group code">
                                <i class="ri-magic-line"></i> Generate
                            </button>
                        </div>
                        <small class="text-muted">APQC process group code (e.g., 7.3 for Reward and Retain Employees)</small>
                    </div>

                    <div class="mb-3">
                        <label for="processGroupName" class="form-label">
                            Process Group Name <span class="text-danger">*</span>
                            <?php echo renderHelpPopover('Process Group Name', 'A descriptive name that identifies the functional area or process group. This name will be displayed in dropdowns when selecting process groups. Use clear, descriptive names that indicate the scope of processes within this group (e.g., "Reward and Retain Employees", "Manage Financial Resources").', 'right'); ?>
                        </label>
                        <input type="text" class="form-control" id="processGroupName" name="processGroupName" required
                               placeholder="e.g., Reward and Retain Employees">
                    </div>

                    <div class="mb-3">
                        <label for="processGroupDescription" class="form-label">
                            Description
                            <?php echo renderHelpPopover('Process Group Description', 'Provide a detailed description of what this process group encompasses. Explain the types of processes that belong to this group, its purpose, and how it relates to the parent category. This helps users understand which processes to create within this group.', 'right'); ?>
                        </label>
                        <textarea class="form-control" id="processGroupDescription" name="processGroupDescription" rows="3"
                                  placeholder="Describe the process group..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="processGroupDisplayOrder" class="form-label">
                                    Display Order
                                    <?php echo renderHelpPopover('Display Order', 'Controls the order in which process groups appear within their category in dropdowns and lists. Lower numbers appear first. Use this to organize process groups logically (e.g., 1, 2, 3... or 10, 20, 30 for easy insertion). Default is 0.', 'right'); ?>
                                </label>
                                <input type="number" class="form-control" id="processGroupDisplayOrder" name="displayOrder" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="processGroupIsActive" class="form-label">
                                    Status
                                    <?php echo renderHelpPopover('Process Group Status', 'Active process groups are available for selection when creating processes. Inactive process groups are hidden from dropdowns but remain in the system for historical reference. Set to Inactive if a process group is deprecated or no longer used.', 'right'); ?>
                                </label>
                                <select class="form-select" id="processGroupIsActive" name="isActive">
                                    <option value="Y">Active</option>
                                    <option value="N">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger d-none mt-3" id="processGroupFormError"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="processGroupFormSubmit">
                    <span class="spinner-border spinner-border-sm d-none" id="processGroupFormSpinner" role="status"></span>
                    <span id="processGroupFormSubmitText">Create Process Group</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Native table search functionality
    const searchInput = document.getElementById('searchInput');
    const processesTable = document.getElementById('processesTable');

    if (searchInput && processesTable) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = processesTable.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const textContent = row.textContent.toLowerCase();
                if (textContent.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    document.getElementById('categoryFilter')?.addEventListener('change', function() {
        const categoryID = this.value;
        const url = new URL(window.location);
        if (categoryID) {
            url.searchParams.set('categoryID', categoryID);
        } else {
            url.searchParams.delete('categoryID');
        }
        window.location.href = url.toString();
    });

    document.getElementById('functionalAreaFilter')?.addEventListener('change', function() {
        const functionalArea = this.value;
        const url = new URL(window.location);
        if (functionalArea) {
            url.searchParams.set('functionalArea', functionalArea);
        } else {
            url.searchParams.delete('functionalArea');
        }
        window.location.href = url.toString();
    });

    // Global listener to ensure backdrop is removed when any modal closes
    document.addEventListener('hidden.bs.modal', function(e) {
        // Wait a bit to ensure Bootstrap has finished its cleanup
        setTimeout(function() {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }

            // Ensure body classes are cleaned up
            if (document.querySelectorAll('.modal.show').length === 0) {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }, 50);
    });

    // Event delegation for all data-action handlers
    document.addEventListener('click', function(e) {
        const action = e.target.closest('[data-action]')?.getAttribute('data-action');
        if (!action) return;

        const element = e.target.closest('[data-action]');

        switch(action) {
            case 'open-process-modal':
                const processAction = element.getAttribute('data-process-action');
                const processID = element.getAttribute('data-process-id');
                openProcessModal(processAction, processID ? parseInt(processID) : null);
                break;

            case 'delete-process':
                const deleteProcessID = element.getAttribute('data-process-id');
                if (deleteProcessID && confirm('Are you sure you want to delete this process?')) {
                    deleteProcess(parseInt(deleteProcessID));
                }
                break;

            case 'generate-process-code':
                generateProcessCode();
                break;

            case 'generate-process-group-code':
                generateProcessGroupCode();
                break;

            case 'open-category-modal':
                const categoryAction = element.getAttribute('data-category-action');
                const categoryID = element.getAttribute('data-category-id');
                openCategoryModal(categoryAction, categoryID ? parseInt(categoryID) : null);
                break;

            case 'open-process-group-modal':
                const processGroupAction = element.getAttribute('data-process-group-action');
                const processGroupID = element.getAttribute('data-process-group-id');
                openProcessGroupModal(processGroupAction, processGroupID ? parseInt(processGroupID) : null);
                break;

            case 'open-functional-areas':
                const url = element.getAttribute('data-url');
                if (url) {
                    window.open(url, '_blank');
                }
                break;
        }
    });

    // Event delegation for change events
    document.addEventListener('change', function(e) {
        const action = e.target.getAttribute('data-action');
        if (!action) return;

        switch(action) {
            case 'update-process-code-hint':
                updateProcessCodeHint();
                break;
        }
    });
});

// Process Groups data for modal
const processGroupsData = <?php echo json_encode($allProcessGroups); ?>;
const categoriesData = <?php echo json_encode($categories ?: []); ?>;

// Auto-generate process code
function generateProcessCode() {
    const categoryID = document.getElementById('categoryID').value;
    const processGroupID = document.getElementById('processGroupID').value;

    if (!categoryID || !processGroupID) {
        alert('Please select both Category and Process Group first');
        return;
    }

    // Get next process code directly from process group
    fetch(`<?php echo $base; ?>php/scripts/operational/processes/manage_process.php?action=get_next_process_code&processGroupID=${processGroupID}`, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.nextCode) {
                document.getElementById('processCode').value = data.nextCode;
            } else {
                // Fallback: use process group code + .1
                const processGroup = processGroupsData.find(pg => pg.processGroupID == processGroupID);
                if (processGroup) {
                    document.getElementById('processCode').value = processGroup.processGroupCode + '.1';
                } else {
                    alert('Failed to generate process code');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback: use process group code + .1
            const processGroup = processGroupsData.find(pg => pg.processGroupID == processGroupID);
            if (processGroup) {
                document.getElementById('processCode').value = processGroup.processGroupCode + '.1';
            } else {
                alert('Failed to generate process code');
            }
        });
}

// Update process code hint when process group changes
function updateProcessCodeHint() {
    const processGroupID = document.getElementById('processGroupID').value;
    if (processGroupID) {
        const processGroup = processGroupsData.find(pg => pg.processGroupID == processGroupID);
        if (processGroup) {
            const processCodeInput = document.getElementById('processCode');
            if (!processCodeInput.value) {
                processCodeInput.placeholder = `e.g., ${processGroup.processGroupCode}.1`;
            }
        }
    }
}

// Auto-generate process group code
function generateProcessGroupCode() {
    const categoryID = document.getElementById('processGroupCategoryID').value;

    if (!categoryID) {
        alert('Please select a Category first');
        return;
    }

    fetch(`<?php echo $base; ?>php/scripts/operational/taxonomy/manage_process_group.php?action=get_next_code&categoryID=${categoryID}`, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.nextCode) {
                document.getElementById('processGroupCode').value = data.nextCode;
            } else {
                alert('Failed to generate process group code');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to generate process group code');
        });
}

function loadProcessGroups() {
    const categoryID = document.getElementById('categoryID').value;
    const processGroupSelect = document.getElementById('processGroupID');

    // Clear existing options
    processGroupSelect.innerHTML = '<option value="">Select Process Group</option>';

    if (!categoryID) {
        processGroupSelect.innerHTML = '<option value="">Select Category First</option>';
        return;
    }

    // Filter process groups by category
    const filteredGroups = processGroupsData.filter(group => group.categoryID == categoryID);

    filteredGroups.forEach(group => {
        const option = document.createElement('option');
        option.value = group.processGroupID;
        option.textContent = `${group.processGroupCode} - ${group.processGroupName}`;
        processGroupSelect.appendChild(option);
    });
}

// Initialize popovers in a modal
function initializeModalPopovers(modalElement) {
    // Destroy existing popovers first
    const existingPopovers = modalElement.querySelectorAll('[data-bs-toggle="popover"]');
    existingPopovers.forEach(el => {
        const existingPopover = bootstrap.Popover.getInstance(el);
        if (existingPopover) {
            existingPopover.dispose();
        }
    });

    // Initialize new popovers
    const popoverTriggerList = modalElement.querySelectorAll('[data-bs-toggle="popover"]');
    popoverTriggerList.forEach(popoverTriggerEl => {
        new bootstrap.Popover(popoverTriggerEl);
    });
}

// Properly close modal and remove backdrop
function closeModalAndRemoveBackdrop(modalElementOrId) {
    const modalElement = typeof modalElementOrId === 'string'
        ? document.getElementById(modalElementOrId)
        : modalElementOrId;

    if (!modalElement) {
        return;
    }

    const modal = bootstrap.Modal.getInstance(modalElement);

    if (modal) {
        // Hide the modal
        modal.hide();

        // Wait for modal to fully hide, then ensure backdrop is removed
        modalElement.addEventListener('hidden.bs.modal', function cleanupModal() {
            // Remove backdrop if it exists
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }

            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            // Dispose of modal instance to prevent memory leaks
            modal.dispose();

            // Remove event listener
            modalElement.removeEventListener('hidden.bs.modal', cleanupModal);
        }, { once: true });
    } else {
        // If no modal instance exists, manually clean up
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
}

function openProcessModal(action, processID = null) {
    const modalElement = document.getElementById('processModal');
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('processForm');
    const modalTitle = document.getElementById('processModalLabel');
    const submitText = document.getElementById('processFormSubmitText');
    const formAction = document.getElementById('processFormAction');
    const formProcessID = document.getElementById('processFormProcessID');
    const errorDiv = document.getElementById('processFormError');
    const submitBtn = document.getElementById('processFormSubmit');

    // Reset form
    form.reset();
    errorDiv.classList.add('d-none');
    errorDiv.textContent = '';
    formAction.value = action;
    formProcessID.value = '';
    submitBtn.disabled = false;

    if (action === 'create') {
        modalTitle.textContent = 'Create Process';
        submitText.textContent = 'Create Process';
        // Set defaults
        document.getElementById('isActive').value = 'Y';
        document.getElementById('isCustom').value = 'N';
        modal.show();

        // Initialize popovers after modal is shown
        modalElement.addEventListener('shown.bs.modal', function() {
            initializeModalPopovers(modalElement);
        }, { once: true });
    } else if (action === 'edit' && processID) {
        modalTitle.textContent = 'Edit Process';
        submitText.textContent = 'Update Process';
        formProcessID.value = processID;

        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Loading...';
        modal.show();

        // Load process data
        fetch(`<?php echo $base; ?>php/scripts/operational/processes/manage_process.php?action=get&processID=${processID}`, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                submitBtn.disabled = false;
                submitText.textContent = 'Update Process';

                if (data.success && data.process) {
                    const p = data.process.process || data.process;

                    // Populate form fields
                    if (document.getElementById('processCode')) {
                        document.getElementById('processCode').value = p.processCode || '';
                    }
                    if (formProcessID) {
                        formProcessID.value = p.processID || processID;
                    }
                    if (document.getElementById('processName')) {
                        document.getElementById('processName').value = p.processName || '';
                    }
                    if (document.getElementById('processDescription')) {
                        document.getElementById('processDescription').value = p.processDescription || '';
                    }
                    // Handle both functionalAreaID (new) and functionalArea (old) for backward compatibility
                    if (document.getElementById('functionalArea')) {
                        document.getElementById('functionalArea').value = p.functionalAreaID || p.functionalArea || '';
                    }
                    if (document.getElementById('isCustom')) {
                        document.getElementById('isCustom').value = p.isCustom || 'N';
                    }
                    if (document.getElementById('isActive')) {
                        document.getElementById('isActive').value = p.isActive || 'Y';
                    }

                    // Set category and process group if available
                    if (data.process.processGroup) {
                        const pg = data.process.processGroup;
                        const categorySelect = document.getElementById('categoryID');
                        if (categorySelect) {
                            categorySelect.value = pg.categoryID || '';
                            loadProcessGroups();
                            setTimeout(() => {
                                const processGroupSelect = document.getElementById('processGroupID');
                                if (processGroupSelect) {
                                    processGroupSelect.value = p.processGroupID || '';
                                    updateProcessCodeHint();
                                }
                            }, 200);
                        }
                    } else if (p.processGroupID) {
                        // If processGroupID exists in process but not in response, try to find category
                        // This handles cases where the API doesn't return full hierarchy
                        const processGroup = processGroupsData.find(pg => pg.processGroupID == p.processGroupID);
                        if (processGroup) {
                            const categorySelect = document.getElementById('categoryID');
                            if (categorySelect) {
                                categorySelect.value = processGroup.categoryID || '';
                                loadProcessGroups();
                                setTimeout(() => {
                                    const processGroupSelect = document.getElementById('processGroupID');
                                    if (processGroupSelect) {
                                        processGroupSelect.value = p.processGroupID || '';
                                        updateProcessCodeHint();
                                    }
                                }, 200);
                            }
                        }
                    }

                    console.log('Process data loaded successfully:', p);
                } else {
                    console.error('Failed to load process data:', data);
                    showProcessError(data.message || 'Failed to load process data');
                }
            })
            .catch(error => {
                console.error('Error loading process data:', error);
                submitBtn.disabled = false;
                submitText.textContent = 'Update Process';
                showProcessError('Failed to load process data: ' + error.message);
            });

        // Initialize popovers after modal is shown
        modalElement.addEventListener('shown.bs.modal', function() {
            initializeModalPopovers(modalElement);
        }, { once: true });
    }
}

function showProcessError(message) {
    const errorDiv = document.getElementById('processFormError');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
}

// Handle form submission
document.getElementById('processFormSubmit').addEventListener('click', function() {
    const form = document.getElementById('processForm');
    const formData = new FormData(form);
    const action = formData.get('action');
    const submitBtn = this;
    const spinner = document.getElementById('processFormSpinner');
    const submitText = document.getElementById('processFormSubmitText');
    const errorDiv = document.getElementById('processFormError');

    // Validate required fields
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Show loading state
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    errorDiv.classList.add('d-none');

    // For create, use processCode as processID (API expects processID to be the code)
    if (action === 'create') {
        const processCode = formData.get('processCode');
        if (!processCode) {
            showProcessError('Process Code is required');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            return;
        }
        formData.set('processID', processCode);
    } else {
        // For update, use the actual processID from the hidden field
        const actualProcessID = formData.get('processID');
        if (!actualProcessID) {
            showProcessError('Process ID is required');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            return;
        }
    }

    // Prepare API endpoint
    const apiAction = action === 'create' ? 'create' : 'update';
    const url = `<?php echo $base; ?>php/scripts/operational/processes/manage_process.php?action=${apiAction}`;

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message (use info/warning style for no-changes message)
            const messageType = data.noChanges ? 'info' : 'success';
            if (typeof showToast === 'function') {
                showToast(data.message || 'Process saved successfully', messageType);
            } else {
                alert(data.message || 'Process saved successfully');
            }

            // Only reload page if there were actual changes
            if (data.noChanges) {
                // For no-changes, just close the modal and reset form state
                closeModalAndRemoveBackdrop('processModal');

                // Reset button state
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            } else {
                // Close modal (backdrop will be removed on reload, but we'll clean it up anyway)
                const modalElement = document.getElementById('processModal');
                const modal = bootstrap.Modal.getInstance(modalElement);

                if (modal) {
                    modal.hide();
                    // Manually remove backdrop immediately before reload
                    setTimeout(() => {
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }, 100);
                }

                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        } else {
            showProcessError(data.message || 'Failed to save process');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showProcessError('An error occurred while saving the process');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
});

function deleteProcess(processID) {
    if (confirm('Are you sure you want to delete this process? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('processID', processID);

        fetch('<?php echo $base; ?>php/scripts/operational/processes/manage_process.php?action=delete', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Process deleted successfully', 'success');
                } else {
                    alert('Process deleted successfully');
                }
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the process');
        });
    }
}

// Category Modal Functions
function openCategoryModal(action, categoryID = null) {
    const modalElement = document.getElementById('categoryModal');
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('categoryForm');
    const modalTitle = document.getElementById('categoryModalLabel');
    const submitText = document.getElementById('categoryFormSubmitText');
    const formAction = document.getElementById('categoryFormAction');
    const formID = document.getElementById('categoryFormID');
    const errorDiv = document.getElementById('categoryFormError');

    form.reset();
    errorDiv.classList.add('d-none');
    errorDiv.textContent = '';
    formAction.value = action;
    formID.value = '';

    if (action === 'create') {
        modalTitle.textContent = 'Create Category';
        submitText.textContent = 'Create Category';
        document.getElementById('categoryIsActive').value = 'Y';
    } else if (action === 'edit' && categoryID) {
        modalTitle.textContent = 'Edit Category';
        submitText.textContent = 'Update Category';
        formID.value = categoryID;

        fetch(`<?php echo $base; ?>php/scripts/operational/taxonomy/manage_category.php?action=get&categoryID=${categoryID}`, {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.category) {
                    const cat = data.category;
                    document.getElementById('categoryCode').value = cat.categoryCode || '';
                    document.getElementById('categoryName').value = cat.categoryName || '';
                    document.getElementById('categoryDescription').value = cat.categoryDescription || '';
                    document.getElementById('categoryDisplayOrder').value = cat.displayOrder || 0;
                    document.getElementById('categoryIsActive').value = cat.isActive || 'Y';
                } else {
                    showCategoryError(data.message || 'Failed to load category data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCategoryError('Failed to load category data');
            });
    }

    modal.show();

    // Initialize popovers after modal is shown
    modalElement.addEventListener('shown.bs.modal', function() {
        initializeModalPopovers(modalElement);
    }, { once: true });
}

function showCategoryError(message) {
    const errorDiv = document.getElementById('categoryFormError');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
}

// Process Group Modal Functions
function openProcessGroupModal(action, processGroupID = null) {
    const modalElement = document.getElementById('processGroupModal');
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('processGroupForm');
    const modalTitle = document.getElementById('processGroupModalLabel');
    const submitText = document.getElementById('processGroupFormSubmitText');
    const formAction = document.getElementById('processGroupFormAction');
    const formID = document.getElementById('processGroupFormID');
    const errorDiv = document.getElementById('processGroupFormError');

    form.reset();
    errorDiv.classList.add('d-none');
    errorDiv.textContent = '';
    formAction.value = action;
    formID.value = '';

    // Pre-select category from process modal if available
    const processCategoryID = document.getElementById('categoryID')?.value;
    if (processCategoryID && action === 'create') {
        document.getElementById('processGroupCategoryID').value = processCategoryID;
    }

    if (action === 'create') {
        modalTitle.textContent = 'Create Process Group';
        submitText.textContent = 'Create Process Group';
        document.getElementById('processGroupIsActive').value = 'Y';
    } else if (action === 'edit' && processGroupID) {
        modalTitle.textContent = 'Edit Process Group';
        submitText.textContent = 'Update Process Group';
        formID.value = processGroupID;

        fetch(`<?php echo $base; ?>php/scripts/operational/taxonomy/manage_process_group.php?action=get&processGroupID=${processGroupID}`, {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.processGroup) {
                    const pg = data.processGroup;
                    document.getElementById('processGroupCategoryID').value = pg.categoryID || '';
                    document.getElementById('processGroupCode').value = pg.processGroupCode || '';
                    document.getElementById('processGroupName').value = pg.processGroupName || '';
                    document.getElementById('processGroupDescription').value = pg.processGroupDescription || '';
                    document.getElementById('processGroupDisplayOrder').value = pg.displayOrder || 0;
                    document.getElementById('processGroupIsActive').value = pg.isActive || 'Y';
                } else {
                    showProcessGroupError(data.message || 'Failed to load process group data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showProcessGroupError('Failed to load process group data');
            });
    }

    modal.show();

    // Initialize popovers after modal is shown
    modalElement.addEventListener('shown.bs.modal', function() {
        initializeModalPopovers(modalElement);
    }, { once: true });
}

function showProcessGroupError(message) {
    const errorDiv = document.getElementById('processGroupFormError');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
}

// Refresh categories dropdown
function refreshCategories() {
    // Reload page to get updated categories
    location.reload();
}

// Category form submission
document.getElementById('categoryFormSubmit')?.addEventListener('click', function() {
    const form = document.getElementById('categoryForm');
    const formData = new FormData(form);
    const action = formData.get('action');
    const submitBtn = this;
    const spinner = document.getElementById('categoryFormSpinner');
    const submitText = document.getElementById('categoryFormSubmitText');
    const errorDiv = document.getElementById('categoryFormError');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    errorDiv.classList.add('d-none');

    const apiAction = action === 'create' ? 'create' : 'update';
    const url = `<?php echo $base; ?>php/scripts/operational/taxonomy/manage_category.php?action=${apiAction}`;

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModalAndRemoveBackdrop('categoryModal');

            if (typeof showToast === 'function') {
                showToast(data.message || 'Category saved successfully', 'success');
            } else {
                alert(data.message || 'Category saved successfully');
            }

            // Refresh categories dropdown by reloading
            refreshCategories();
        } else {
            showCategoryError(data.message || 'Failed to save category');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showCategoryError('An error occurred while saving the category');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
});

// Process Group form submission
document.getElementById('processGroupFormSubmit')?.addEventListener('click', function() {
    const form = document.getElementById('processGroupForm');
    const formData = new FormData(form);
    const action = formData.get('action');
    const submitBtn = this;
    const spinner = document.getElementById('processGroupFormSpinner');
    const submitText = document.getElementById('processGroupFormSubmitText');
    const errorDiv = document.getElementById('processGroupFormError');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    errorDiv.classList.add('d-none');

    const apiAction = action === 'create' ? 'create' : 'update';
    const url = `<?php echo $base; ?>php/scripts/operational/taxonomy/manage_process_group.php?action=${apiAction}`;

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModalAndRemoveBackdrop('processGroupModal');

            if (typeof showToast === 'function') {
                showToast(data.message || 'Process Group saved successfully', 'success');
            } else {
                alert(data.message || 'Process Group saved successfully');
            }

            // Refresh process groups dropdown
            const categoryID = document.getElementById('categoryID').value || document.getElementById('processGroupCategoryID').value;
            if (categoryID) {
                document.getElementById('categoryID').value = categoryID;
                loadProcessGroups();

                // If process group was created from process modal, select it
                if (action === 'create' && data.processGroupID) {
                    setTimeout(() => {
                        document.getElementById('processGroupID').value = data.processGroupID;
                        updateProcessCodeHint();
                    }, 200);
                }
            } else {
                // Reload page to refresh data
                location.reload();
            }
        } else {
            showProcessGroupError(data.message || 'Failed to save process group');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showProcessGroupError('An error occurred while saving the process group');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
});
</script>

