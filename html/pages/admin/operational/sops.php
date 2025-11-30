<?php
/**
 * SOP Management - Admin
 *
 * Manage Standard Operating Procedures
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
$search = $_GET['search'] ?? '';

// Get SOPs
$whereArr = ['Suspended' => 'N'];
if ($status !== 'all') {
    if ($status === 'active') {
        $whereArr['isActive'] = 'Y';
    } else {
        $whereArr['isActive'] = 'N';
    }
}

$sops = $DBConn->retrieve_db_table_rows('tija_sops',
    ['sopID', 'sopCode', 'sopTitle', 'sopDescription', 'functionalArea', 'sopVersion', 'isActive', 'DateAdded', 'approvalStatus'],
    $whereArr);

// Filter by functional area if specified
if ($functionalArea && $sops) {
    $sops = array_filter($sops, function($s) use ($functionalArea) {
        $fa = is_object($s) ? ($s->functionalArea ?? '') : ($s['functionalArea'] ?? '');
        return $fa === $functionalArea;
    });
}

$pageTitle = "SOP Management";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        Manage Standard Operating Procedures (SOPs) that document how tasks should be performed.
                        <?php echo renderHelpPopover('SOP Management', 'Standard Operating Procedures are versioned to track changes over time. When you update an SOP, create a new version and submit it for approval. Only approved versions are active. Function heads approve SOPs before they become active.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">SOPs</li>
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
                            <p class="text-truncate font-size-14 mb-2">Total SOPs</p>
                            <h4 class="mb-2"><?php echo is_array($sops) ? count($sops) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-file-text-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Active SOPs</p>
                            <h4 class="mb-2 text-success"><?php
                                $active = is_array($sops) ? array_filter($sops, function($s) {
                                    $isActive = is_object($s) ? ($s->isActive ?? 'N') : ($s['isActive'] ?? 'N');
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
                            <p class="text-truncate font-size-14 mb-2">Pending Approval</p>
                            <h4 class="mb-2 text-warning"><?php
                                $pending = is_array($sops) ? array_filter($sops, function($s) {
                                    $status = is_object($s) ? ($s->approvalStatus ?? '') : ($s['approvalStatus'] ?? '');
                                    return $status === 'pending_approval';
                                }) : [];
                                echo count($pending);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
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
                            <p class="text-truncate font-size-14 mb-2">Approved</p>
                            <h4 class="mb-2 text-info"><?php
                                $approved = is_array($sops) ? array_filter($sops, function($s) {
                                    $status = is_object($s) ? ($s->approvalStatus ?? '') : ($s['approvalStatus'] ?? '');
                                    return $status === 'approved';
                                }) : [];
                                echo count($approved);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-check-line font-size-18"></i>
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
                                <input type="text" class="form-control" id="searchInput" placeholder="Search SOPs..." value="<?php echo htmlspecialchars($search); ?>">
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
                        </div>
                        <div>
                            <a href="?s=admin&ss=operational&p=sops&action=create" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i>Create SOP
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SOPs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Standard Operating Procedures</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($sops)): ?>
                        <div class="text-center py-5">
                            <i class="ri-file-text-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No SOPs Found</h5>
                            <p class="text-muted">Get started by creating your first SOP.</p>
                            <a href="?s=admin&ss=operational&p=sops&action=create" class="btn btn-primary mt-3">
                                <i class="ri-add-line me-1"></i>Create SOP
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="sopsTable">
                                <thead>
                                    <tr>
                                        <th>SOP Code</th>
                                        <th>SOP Name</th>
                                        <th>Functional Area</th>
                                        <th>Version</th>
                                        <th>Status</th>
                                        <th>Approval</th>
                                        <th>Created</th>
                                        <th width="200" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sops as $sop):
                                        // Handle both object and array access
                                        $sopID = is_object($sop) ? ($sop->sopID ?? null) : ($sop['sopID'] ?? null);
                                        $sopCode = is_object($sop) ? ($sop->sopCode ?? 'N/A') : ($sop['sopCode'] ?? 'N/A');
                                        $sopTitle = is_object($sop) ? ($sop->sopTitle ?? 'Unknown') : ($sop['sopTitle'] ?? 'Unknown');
                                        $sopDescription = is_object($sop) ? ($sop->sopDescription ?? '') : ($sop['sopDescription'] ?? '');
                                        $functionalArea = is_object($sop) ? ($sop->functionalArea ?? 'N/A') : ($sop['functionalArea'] ?? 'N/A');
                                        $sopVersion = is_object($sop) ? ($sop->sopVersion ?? '1.0') : ($sop['sopVersion'] ?? '1.0');
                                        $isActive = is_object($sop) ? ($sop->isActive ?? 'N') : ($sop['isActive'] ?? 'N');
                                        $approvalStatus = is_object($sop) ? ($sop->approvalStatus ?? 'draft') : ($sop['approvalStatus'] ?? 'draft');
                                        $dateAdded = is_object($sop) ? ($sop->DateAdded ?? '') : ($sop['DateAdded'] ?? '');
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($sopCode); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($sopTitle); ?></div>
                                                <?php if (!empty($sopDescription)): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($sopDescription, 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($functionalArea); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">v<?php echo htmlspecialchars($sopVersion); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $isActive === 'Y' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $isActive === 'Y' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $approvalStatus === 'approved' ? 'success' :
                                                        ($approvalStatus === 'archived' ? 'secondary' :
                                                        ($approvalStatus === 'pending_approval' ? 'warning' : 'info'));
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $approvalStatus)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo !empty($dateAdded) ? date('M d, Y', strtotime($dateAdded)) : 'N/A'; ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="?s=admin&ss=operational&p=sops&action=view&id=<?php echo $sopID; ?>"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="?s=admin&ss=operational&p=sops&action=edit&id=<?php echo $sopID; ?>"
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <?php if ($approvalStatus === 'pending_approval'): ?>
                                                        <button type="button" class="btn btn-sm btn-success"
                                                                data-action="approve-sop" data-sop-id="<?php echo $sopID; ?>" title="Approve">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-action="delete-sop" data-sop-id="<?php echo $sopID; ?>" title="Delete">
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
    if (document.getElementById('sopsTable')) {
        $('#sopsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ SOPs per page"
            }
        });
    }

    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const table = $('#sopsTable').DataTable();
        table.search(this.value).draw();
    });

    document.getElementById('statusFilter')?.addEventListener('change', function() {
        const status = this.value;
        const url = new URL(window.location);
        if (status && status !== 'all') {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
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
});

function approveSOP(sopID) {
    if (confirm('Approve this SOP?')) {
        const formData = new FormData();
        formData.append('action', 'approve');
        formData.append('sopID', sopID);

        fetch('<?php echo $base; ?>php/scripts/operational/sops/manage_sop.php?action=approve', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('SOP approved successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the SOP');
        });
    }
}

function deleteSOP(sopID) {
    if (confirm('Are you sure you want to delete this SOP? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('sopID', sopID);

        fetch('<?php echo $base; ?>php/scripts/operational/sops/manage_sop.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('SOP deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the SOP');
        });
    }
}

// Event delegation for SOPs
document.addEventListener('click', function(e) {
    const action = e.target.closest('[data-action]')?.getAttribute('data-action');
    if (!action) return;

    const element = e.target.closest('[data-action]');

    switch(action) {
        case 'approve-sop':
            const sopID = element.getAttribute('data-sop-id');
            if (sopID) {
                approveSOP(parseInt(sopID));
            }
            break;

        case 'delete-sop':
            const deleteSopID = element.getAttribute('data-sop-id');
            if (deleteSopID) {
                deleteSOP(parseInt(deleteSopID));
            }
            break;
    }
});
</script>

