<?php
/**
 * Function Heads Management - Admin
 *
 * Assign function heads to functional areas
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

// Get function head assignments (table doesn't have Suspended column, using empty where array)
$assignments = $DBConn->retrieve_db_table_rows('tija_function_head_assignments',
    ['assignmentID', 'employeeID', 'functionalArea', 'isActive', 'DateAdded'],
    []);

// Enrich with employee info
if ($assignments) {
    foreach ($assignments as &$assignment) {
        // Handle both object and array access
        $employeeID = is_object($assignment) ? ($assignment->employeeID ?? null) : ($assignment['employeeID'] ?? null);

        $employee = $employeeID ? Data::users(['ID' => $employeeID], true, $DBConn) : null;

        // Convert to array if object to add new fields
        if (is_object($assignment)) {
            $assignment->employeeName = $employee ? ($employee->FirstName . ' ' . $employee->Surname) : 'Unknown';
            $assignment->employeeEmail = $employee ? $employee->Email : '';
        } else {
            $assignment['employeeName'] = $employee ? ($employee->FirstName . ' ' . $employee->Surname) : 'Unknown';
            $assignment['employeeEmail'] = $employee ? $employee->Email : '';
        }
    }
}

$pageTitle = "Function Heads Management";
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
                        <li class="breadcrumb-item active">Function Heads</li>
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
                            <p class="text-truncate font-size-14 mb-2">Total Assignments</p>
                            <h4 class="mb-2"><?php echo is_array($assignments) ? count($assignments) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-team-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Active Assignments</p>
                            <h4 class="mb-2 text-success"><?php
                                $active = is_array($assignments) ? array_filter($assignments, function($a) {
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
                            <p class="text-truncate font-size-14 mb-2">Covered Areas</p>
                            <h4 class="mb-2 text-info"><?php
                                if (is_array($assignments) && !empty($assignments)) {
                                    $areas = [];
                                    foreach ($assignments as $a) {
                                        $fa = is_object($a) ? ($a->functionalArea ?? '') : ($a['functionalArea'] ?? '');
                                        if ($fa) {
                                            $areas[] = $fa;
                                        }
                                    }
                                    $areas = array_unique($areas);
                                    echo count($areas);
                                } else {
                                    echo '0';
                                }
                            ?></h4>
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
                            <p class="text-truncate font-size-14 mb-2">Unassigned Areas</p>
                            <h4 class="mb-2 text-warning"><?php
                                $allAreas = ['Finance', 'HR', 'IT', 'Sales', 'Marketing', 'Legal', 'Facilities'];
                                if (is_array($assignments) && !empty($assignments)) {
                                    $assignedAreas = [];
                                    foreach ($assignments as $a) {
                                        $fa = is_object($a) ? ($a->functionalArea ?? '') : ($a['functionalArea'] ?? '');
                                        if ($fa) {
                                            $assignedAreas[] = $fa;
                                        }
                                    }
                                    $assignedAreas = array_unique($assignedAreas);
                                    echo count(array_diff($allAreas, $assignedAreas));
                                } else {
                                    echo count($allAreas);
                                }
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-alert-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Function Head Assignments</h4>
                        <button class="btn btn-primary btn-sm" data-action="show-assign-modal">
                            <i class="ri-add-line me-1"></i>Assign Function Head
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="functionHeadsTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Email</th>
                                    <th>Functional Area</th>
                                    <th>Status</th>
                                    <th>Assigned Date</th>
                                    <th width="150" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (is_array($assignments) && !empty($assignments)): ?>
                                    <?php foreach ($assignments as $assignment):
                                        // Handle both object and array access
                                        $assignmentID = is_object($assignment) ? ($assignment->assignmentID ?? null) : ($assignment['assignmentID'] ?? null);
                                        $employeeName = is_object($assignment) ? ($assignment->employeeName ?? 'Unknown') : ($assignment['employeeName'] ?? 'Unknown');
                                        $employeeEmail = is_object($assignment) ? ($assignment->employeeEmail ?? '') : ($assignment['employeeEmail'] ?? '');
                                        $functionalArea = is_object($assignment) ? ($assignment->functionalArea ?? 'N/A') : ($assignment['functionalArea'] ?? 'N/A');
                                        $isActive = is_object($assignment) ? ($assignment->isActive ?? 'N') : ($assignment['isActive'] ?? 'N');
                                        $dateAdded = is_object($assignment) ? ($assignment->DateAdded ?? '') : ($assignment['DateAdded'] ?? '');
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($employeeName); ?></div>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars($employeeEmail); ?></small>
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
                                                    <button class="btn btn-sm btn-primary" data-action="edit-assignment" data-assignment-id="<?php echo $assignmentID; ?>" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-<?php echo $isActive === 'Y' ? 'warning' : 'success'; ?>"
                                                            data-action="toggle-assignment" data-assignment-id="<?php echo $assignmentID; ?>" data-new-status="<?php echo $isActive === 'Y' ? 'N' : 'Y'; ?>"
                                                            title="<?php echo $isActive === 'Y' ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="ri-<?php echo $isActive === 'Y' ? 'pause' : 'play'; ?>-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" data-action="remove-assignment" data-assignment-id="<?php echo $assignmentID; ?>" title="Remove">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No function head assignments found. Assign function heads to manage operational work.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('functionHeadsTable')) {
        $('#functionHeadsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[2, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ assignments per page"
            }
        });
    }
});

function showAssignModal() {
    // Show modal for assigning function head
    // This would typically open a Bootstrap modal with employee and functional area selection
    alert('Please use the "Assign Function Head" button to open the assignment form');
}

function editAssignment(assignmentID) {
    // Redirect to edit page or open modal
    window.location.href = '?s=admin&ss=operational&p=function_heads&action=edit&id=' + assignmentID;
}

function toggleAssignment(assignmentID, newStatus) {
    const action = newStatus === 'Y' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this assignment?`)) {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('assignmentID', assignmentID);
        formData.append('isActive', newStatus);

        fetch('<?php echo $base; ?>php/scripts/operational/function_heads/manage_assignment.php?action=toggle', {
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
            alert('An error occurred while updating the assignment');
        });
    }
}

function removeAssignment(assignmentID) {
    if (confirm('Are you sure you want to remove this function head assignment?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('assignmentID', assignmentID);

        fetch('<?php echo $base; ?>php/scripts/operational/function_heads/manage_assignment.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Assignment removed successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the assignment');
        });
    }
}

// Event delegation for function heads
document.addEventListener('click', function(e) {
    const action = e.target.closest('[data-action]')?.getAttribute('data-action');
    if (!action) return;

    const element = e.target.closest('[data-action]');

    switch(action) {
        case 'show-assign-modal':
            showAssignModal();
            break;

        case 'edit-assignment':
            const assignmentID = element.getAttribute('data-assignment-id');
            if (assignmentID) {
                editAssignment(parseInt(assignmentID));
            }
            break;

        case 'toggle-assignment':
            const toggleAssignmentID = element.getAttribute('data-assignment-id');
            const newStatus = element.getAttribute('data-new-status');
            if (toggleAssignmentID && newStatus) {
                toggleAssignment(parseInt(toggleAssignmentID), newStatus);
            }
            break;

        case 'remove-assignment':
            const removeAssignmentID = element.getAttribute('data-assignment-id');
            if (removeAssignmentID && confirm('Are you sure you want to remove this assignment?')) {
                removeAssignment(parseInt(removeAssignmentID));
            }
            break;
    }
});
</script>

