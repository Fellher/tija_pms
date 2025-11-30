<?php
/**
 * Activities Management - Admin
 *
 * Manage BAU activities within processes
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

global $DBConn, $userID;

// Get filters
$processID = $_GET['processID'] ?? '';
$functionalArea = $_GET['functionalArea'] ?? '';
$search = $_GET['search'] ?? '';

// Get processes for filter
$processes = BAUTaxonomy::getProcesses(null, ['Suspended' => 'N'], false, $DBConn);

// Get activities
$filters = ['Suspended' => 'N'];
if ($processID) $filters['processID'] = $processID;
if ($functionalArea) $filters['functionalArea'] = $functionalArea;

$activities = BAUTaxonomy::getActivities($processID, $filters, false, $DBConn);

$pageTitle = "Activities Management";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Activities</li>
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
                            <p class="text-truncate font-size-14 mb-2">Total Activities</p>
                            <h4 class="mb-2"><?php echo is_array($activities) ? count($activities) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-list-check font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Active Activities</p>
                            <h4 class="mb-2 text-success"><?php
                                $active = is_array($activities) ? array_filter($activities, function($a) {
                                    $isActive = is_object($a) ? ($a->isActive ?? 'N') : ($a['isActive'] ?? 'N');
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
                            <p class="text-truncate font-size-14 mb-2">Processes</p>
                            <h4 class="mb-2"><?php echo is_array($processes) ? count($processes) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
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
                            <p class="text-truncate font-size-14 mb-2">Avg per Process</p>
                            <h4 class="mb-2 text-warning"><?php
                                $processCount = is_array($processes) ? count($processes) : 1;
                                $activityCount = is_array($activities) ? count($activities) : 0;
                                echo $processCount > 0 ? round($activityCount / $processCount, 1) : 0;
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-bar-chart-line font-size-18"></i>
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
                                <input type="text" class="form-control" id="searchInput" placeholder="Search activities..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <select class="form-select" id="processFilter" style="width: 250px;">
                                <option value="">All Processes</option>
                                <?php if (is_array($processes)): ?>
                                    <?php foreach ($processes as $proc): ?>
                                        <?php
                                        $procID = is_object($proc) ? ($proc->processID ?? '') : ($proc['processID'] ?? '');
                                        $procName = is_object($proc) ? ($proc->processName ?? '') : ($proc['processName'] ?? '');
                                        ?>
                                        <option value="<?php echo $procID; ?>" <?php echo $processID == $procID ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($procID . ' - ' . $procName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                            <a href="?s=admin&ss=operational&p=activities&action=create" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i>Create Activity
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Activities</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <div class="text-center py-5">
                            <i class="ri-list-check fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Activities Found</h5>
                            <p class="text-muted">Get started by creating your first activity.</p>
                            <a href="?s=admin&ss=operational&p=activities&action=create" class="btn btn-primary mt-3">
                                <i class="ri-add-line me-1"></i>Create Activity
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="activitiesTable">
                                <thead>
                                    <tr>
                                        <th>Activity Name</th>
                                        <th>Process</th>
                                        <th>Functional Area</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th width="150" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity):
                                        // Handle both object and array access
                                        $activityID = is_object($activity) ? ($activity->activityID ?? null) : ($activity['activityID'] ?? null);
                                        $activityName = is_object($activity) ? ($activity->activityName ?? 'Unknown') : ($activity['activityName'] ?? 'Unknown');
                                        $activityDescription = is_object($activity) ? ($activity->activityDescription ?? '') : ($activity['activityDescription'] ?? '');
                                        $processID = is_object($activity) ? ($activity->processID ?? 'N/A') : ($activity['processID'] ?? 'N/A');
                                        $processName = is_object($activity) ? ($activity->processName ?? '') : ($activity['processName'] ?? '');
                                        $functionalArea = is_object($activity) ? ($activity->functionalArea ?? 'N/A') : ($activity['functionalArea'] ?? 'N/A');
                                        $isActive = is_object($activity) ? ($activity->isActive ?? 'N') : ($activity['isActive'] ?? 'N');
                                        $dateAdded = is_object($activity) ? ($activity->DateAdded ?? '') : ($activity['DateAdded'] ?? '');
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($activityName); ?></div>
                                                <?php if (!empty($activityDescription)): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($activityDescription, 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($processID); ?></span>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($processName); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($functionalArea); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $isActive === 'Y' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $isActive === 'Y' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo !empty($dateAdded) ? date('M d, Y', strtotime($dateAdded)) : 'N/A'; ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="?s=admin&ss=operational&p=activities&action=view&id=<?php echo $activityID; ?>"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="?s=admin&ss=operational&p=activities&action=edit&id=<?php echo $activityID; ?>"
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-action="delete-activity" data-activity-id="<?php echo $activityID; ?>" title="Delete">
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
    if (document.getElementById('activitiesTable')) {
        $('#activitiesTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ activities per page"
            }
        });
    }

    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const table = $('#activitiesTable').DataTable();
        table.search(this.value).draw();
    });

    document.getElementById('processFilter')?.addEventListener('change', function() {
        const processID = this.value;
        const url = new URL(window.location);
        if (processID) {
            url.searchParams.set('processID', processID);
        } else {
            url.searchParams.delete('processID');
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

function deleteActivity(activityID) {
    if (confirm('Are you sure you want to delete this activity? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('activityID', activityID);

        fetch('<?php echo $base; ?>php/scripts/operational/activities/manage_activity.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Activity deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the activity');
        });
    }
}

// Event delegation for activities
document.addEventListener('click', function(e) {
    const action = e.target.closest('[data-action]')?.getAttribute('data-action');
    if (!action) return;

    const element = e.target.closest('[data-action]');

    if (action === 'delete-activity') {
        const activityID = element.getAttribute('data-activity-id');
        if (activityID) {
            deleteActivity(parseInt(activityID));
        }
    }
});
</script>

