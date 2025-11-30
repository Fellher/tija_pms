<?php
/**
 * Task Templates Management - Admin
 *
 * Manage operational task templates
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
include __DIR__ . '/../../../includes/components/operational_help.php';

global $DBConn, $userID;

// Get filters
$functionalArea = $_GET['functionalArea'] ?? '';
$status = $_GET['status'] ?? 'all';
$processingMode = $_GET['processingMode'] ?? '';
$search = $_GET['search'] ?? '';

// Get templates
$filters = ['Suspended' => 'N'];
if ($status !== 'all') {
    if ($status === 'active') {
        $filters['isActive'] = 'Y';
    } else {
        $filters['isActive'] = 'N';
    }
}
if ($functionalArea) $filters['functionalArea'] = $functionalArea;
if ($processingMode) $filters['processingMode'] = $processingMode;

$templates = OperationalTaskTemplate::listTemplates($filters, $DBConn);

$pageTitle = "Task Templates Management";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        Manage operational task templates that define recurring tasks with schedules, checklists, and assignment rules.
                        <?php echo renderHelpPopover('Task Templates', 'Templates define recurring operational tasks with schedules, checklists, and assignment rules. They can be processed automatically (cron), manually, or both. Templates link to processes, workflows, and SOPs.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Templates</li>
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
                            <p class="text-truncate font-size-14 mb-2">Total Templates</p>
                            <h4 class="mb-2"><?php echo is_array($templates) ? count($templates) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-file-copy-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Active Templates</p>
                            <h4 class="mb-2 text-success"><?php
                                $active = is_array($templates) ? array_filter($templates, function($t) {
                                    $isActive = is_object($t) ? ($t->isActive ?? 'N') : ($t['isActive'] ?? 'N');
                                    return $isActive === 'Y';
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
                            <p class="text-truncate font-size-14 mb-2">Cron Mode</p>
                            <h4 class="mb-2 text-info"><?php
                                $cron = is_array($templates) ? array_filter($templates, function($t) {
                                    $mode = is_object($t) ? ($t->processingMode ?? 'cron') : ($t['processingMode'] ?? 'cron');
                                    return $mode === 'cron' || $mode === 'both';
                                }) : [];
                                echo count($cron);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-time-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Manual Mode</p>
                            <h4 class="mb-2 text-warning"><?php
                                $manual = is_array($templates) ? array_filter($templates, function($t) {
                                    $mode = is_object($t) ? ($t->processingMode ?? '') : ($t['processingMode'] ?? '');
                                    return $mode === 'manual' || $mode === 'both';
                                }) : [];
                                echo count($manual);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-user-line font-size-18"></i>
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
                                <input type="text" class="form-control" id="searchInput" placeholder="Search templates..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <select class="form-select" id="statusFilter" style="width: 150px;">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <select class="form-select" id="functionalAreaFilter" style="width: 200px;">
                                <option value="">All Functional Areas</option>
                                <option value="Finance" <?php echo $functionalArea == 'Finance' ? 'selected' : ''; ?>>Finance</option>
                                <option value="HR" <?php echo $functionalArea == 'HR' ? 'selected' : ''; ?>>HR</option>
                                <option value="IT" <?php echo $functionalArea == 'IT' ? 'selected' : ''; ?>>IT</option>
                                <option value="Sales" <?php echo $functionalArea == 'Sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="Marketing" <?php echo $functionalArea == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Legal" <?php echo $functionalArea == 'Legal' ? 'selected' : ''; ?>>Legal</option>
                                <option value="Facilities" <?php echo $functionalArea == 'Facilities' ? 'selected' : ''; ?>>Facilities</option>
                            </select>
                            <select class="form-select" id="processingModeFilter" style="width: 180px;">
                                <option value="">All Processing Modes</option>
                                <option value="cron" <?php echo $processingMode == 'cron' ? 'selected' : ''; ?>>Cron</option>
                                <option value="manual" <?php echo $processingMode == 'manual' ? 'selected' : ''; ?>>Manual</option>
                                <option value="both" <?php echo $processingMode == 'both' ? 'selected' : ''; ?>>Both</option>
                            </select>
                            <?php echo renderHelpPopover('Processing Mode', 'Cron: Tasks automatically created by scheduled jobs. Manual: Tasks created when users manually trigger them. Both: Tasks can be created automatically or manually.', 'top'); ?>
                        </div>
                        <div>
                            <a href="?s=admin&ss=operational&p=templates&action=create" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i>Create Template
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Task Templates</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($templates)): ?>
                        <div class="text-center py-5">
                            <i class="ri-file-copy-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Templates Found</h5>
                            <p class="text-muted">Get started by creating your first template.</p>
                            <a href="?s=admin&ss=operational&p=templates&action=create" class="btn btn-primary mt-3">
                                <i class="ri-add-line me-1"></i>Create Template
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="templatesTable">
                                <thead>
                                    <tr>
                                        <th>Template Code</th>
                                        <th>Template Name</th>
                                        <th>Functional Area</th>
                                        <th>Frequency</th>
                                        <th>Processing Mode</th>
                                        <th>Status</th>
                                        <th width="200" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($templates as $template):
                                        // Handle both object and array access
                                        $templateID = is_object($template) ? ($template->templateID ?? null) : ($template['templateID'] ?? null);
                                        $templateCode = is_object($template) ? ($template->templateCode ?? 'N/A') : ($template['templateCode'] ?? 'N/A');
                                        $templateName = is_object($template) ? ($template->templateName ?? 'Unknown') : ($template['templateName'] ?? 'Unknown');
                                        $templateDescription = is_object($template) ? ($template->templateDescription ?? '') : ($template['templateDescription'] ?? '');
                                        $functionalArea = is_object($template) ? ($template->functionalArea ?? 'N/A') : ($template['functionalArea'] ?? 'N/A');
                                        $frequencyType = is_object($template) ? ($template->frequencyType ?? 'custom') : ($template['frequencyType'] ?? 'custom');
                                        $frequencyInterval = is_object($template) ? ($template->frequencyInterval ?? null) : ($template['frequencyInterval'] ?? null);
                                        $processingMode = is_object($template) ? ($template->processingMode ?? 'cron') : ($template['processingMode'] ?? 'cron');
                                        $isActive = is_object($template) ? ($template->isActive ?? 'N') : ($template['isActive'] ?? 'N');
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($templateCode); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($templateName); ?></div>
                                                <?php if (!empty($templateDescription)): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($templateDescription, 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($functionalArea); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php
                                                        echo ucfirst($frequencyType);
                                                        if (!empty($frequencyInterval) && $frequencyInterval > 1) {
                                                            echo ' (every ' . $frequencyInterval . ')';
                                                        }
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $processingMode === 'cron' ? 'success' : ($processingMode === 'manual' ? 'warning' : 'info');
                                                ?>">
                                                    <?php echo ucfirst($processingMode); ?>
                                                </span>
                                                <?php echo renderHelpIcon('Processing mode controls how tasks are created: Cron (automatic), Manual (user-triggered), or Both (flexible).', 'left'); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $isActive === 'Y' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $isActive === 'Y' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="?s=admin&ss=operational&p=templates&action=view&id=<?php echo $templateID; ?>"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="?s=admin&ss=operational&p=templates&action=edit&id=<?php echo $templateID; ?>"
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-<?php echo $isActive === 'Y' ? 'warning' : 'success'; ?>"
                                                            data-action="toggle-template" data-template-id="<?php echo $templateID; ?>" data-new-status="<?php echo $isActive === 'Y' ? 'N' : 'Y'; ?>"
                                                            title="<?php echo $isActive === 'Y' ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="ri-<?php echo $isActive === 'Y' ? 'pause' : 'play'; ?>-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-action="delete-template" data-template-id="<?php echo $templateID; ?>" title="Delete">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('templatesTable')) {
        $('#templatesTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ templates per page"
            }
        });
    }

    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const table = $('#templatesTable').DataTable();
        table.search(this.value).draw();
    });

    document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
    document.getElementById('functionalAreaFilter')?.addEventListener('change', applyFilters);
    document.getElementById('processingModeFilter')?.addEventListener('change', applyFilters);
});

function applyFilters() {
    const url = new URL(window.location);
    const status = document.getElementById('statusFilter').value;
    const functionalArea = document.getElementById('functionalAreaFilter').value;
    const processingMode = document.getElementById('processingModeFilter').value;

    if (status && status !== 'all') {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }

    if (functionalArea) {
        url.searchParams.set('functionalArea', functionalArea);
    } else {
        url.searchParams.delete('functionalArea');
    }

    if (processingMode) {
        url.searchParams.set('processingMode', processingMode);
    } else {
        url.searchParams.delete('processingMode');
    }

    window.location.href = url.toString();
}

function toggleTemplate(templateID, newStatus) {
    const action = newStatus === 'Y' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this template?`)) {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('templateID', templateID);
        formData.append('isActive', newStatus);

        fetch('<?php echo $base; ?>php/scripts/operational/templates/manage_template.php?action=toggle', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the template');
        });
    }
}

function deleteTemplate(templateID) {
    if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('templateID', templateID);

        fetch('<?php echo $base; ?>php/scripts/operational/templates/manage_template.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Template deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the template');
        });
    }
}
// Event delegation for templates
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const action = e.target.closest('[data-action]')?.getAttribute('data-action');
        if (!action) return;

        const element = e.target.closest('[data-action]');

        switch(action) {
            case 'toggle-template':
                const templateID = element.getAttribute('data-template-id');
                const newStatus = element.getAttribute('data-new-status');
                if (templateID && newStatus) {
                    toggleTemplate(parseInt(templateID), newStatus);
                }
                break;

            case 'delete-template':
                const deleteTemplateID = element.getAttribute('data-template-id');
                if (deleteTemplateID && confirm('Are you sure you want to delete this template?')) {
                    deleteTemplate(parseInt(deleteTemplateID));
                }
                break;
        }
    });
});
</script>

