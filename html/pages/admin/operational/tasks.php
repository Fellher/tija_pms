<?php
/**
 * Tasks Management - Admin
 *
 * Manage operational task instances (admin view)
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
$status = $_GET['status'] ?? 'all';
$functionalArea = $_GET['functionalArea'] ?? '';
$assigneeID = $_GET['assigneeID'] ?? '';
$dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
$dateTo = $_GET['dateTo'] ?? date('Y-m-t');

// Get tasks
$filters = [];
if ($status !== 'all') $filters['status'] = $status;
if ($functionalArea) $filters['functionalArea'] = $functionalArea;
if ($assigneeID) $filters['assigneeID'] = $assigneeID;

// Get all operational tasks with filters
$whereArr = ['Suspended' => 'N'];
if ($status !== 'all') $whereArr['status'] = $status;
if ($assigneeID) $whereArr['assigneeID'] = $assigneeID;

$cols = ['operationalTaskID', 'templateID', 'instanceNumber', 'dueDate', 'status', 'assigneeID', 'processID', 'actualDuration', 'startDate', 'completedDate'];
$tasks = $DBConn->retrieve_db_table_rows('tija_operational_tasks', $cols, $whereArr);

// Enrich with template and assignee info
if ($tasks) {
    foreach ($tasks as &$task) {
        $template = OperationalTaskTemplate::getTemplate($task['templateID'], $DBConn);
        $task['templateName'] = $template['templateName'] ?? 'Unknown';
        $task['estimatedDuration'] = $template['estimatedDuration'] ?? null;
        $task['functionalArea'] = $template['functionalArea'] ?? null;

        if ($task['assigneeID']) {
            $assignee = Data::users(['ID' => $task['assigneeID']], true, $DBConn);
            $task['assigneeName'] = $assignee ? ($assignee->FirstName . ' ' . $assignee->Surname) : 'Unknown';
        }

        $task['taskName'] = $task['templateName'];
    }

    // Filter by functional area if specified
    if ($functionalArea) {
        $tasks = array_filter($tasks, function($t) use ($functionalArea) {
            return ($t['functionalArea'] ?? '') === $functionalArea;
        });
    }
}

// Get templates for filter
$templates = OperationalTaskTemplate::listTemplates(['isActive' => 'Y'], $DBConn);

$pageTitle = "Operational Tasks Management";
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
                        <li class="breadcrumb-item active">Tasks</li>
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
                            <p class="text-truncate font-size-14 mb-2">Total Tasks</p>
                            <h4 class="mb-2"><?php echo is_array($tasks) ? count($tasks) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-task-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Pending</p>
                            <h4 class="mb-2 text-warning"><?php
                                $pending = is_array($tasks) ? array_filter($tasks, function($t) {
                                    return ($t['status'] ?? '') === 'pending';
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
                            <p class="text-truncate font-size-14 mb-2">In Progress</p>
                            <h4 class="mb-2 text-info"><?php
                                $inProgress = is_array($tasks) ? array_filter($tasks, function($t) {
                                    return ($t['status'] ?? '') === 'in_progress';
                                }) : [];
                                echo count($inProgress);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-play-circle-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Overdue</p>
                            <h4 class="mb-2 text-danger"><?php
                                $overdue = is_array($tasks) ? array_filter($tasks, function($t) {
                                    return ($t['status'] ?? '') === 'overdue' ||
                                           (!empty($t['dueDate']) && $t['dueDate'] < date('Y-m-d') && ($t['status'] ?? '') !== 'completed');
                                }) : [];
                                echo count($overdue);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-danger rounded-3">
                                <i class="ri-alarm-warning-line font-size-18"></i>
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
                            <select class="form-select" id="statusFilter" style="width: 150px;">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="overdue" <?php echo $status === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
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
                            <input type="date" class="form-control" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>" style="width: 150px;">
                            <input type="date" class="form-control" id="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>" style="width: 150px;">
                        </div>
                        <div>
                            <button class="btn btn-success" data-action="export-tasks">
                                <i class="ri-download-line me-1"></i>Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Operational Tasks</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($tasks)): ?>
                        <div class="text-center py-5">
                            <i class="ri-task-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Tasks Found</h5>
                            <p class="text-muted">No operational tasks match your filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tasksTable">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Template</th>
                                        <th>Assignee</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th width="150" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task):
                                        $isOverdue = !empty($task['dueDate']) && $task['dueDate'] < date('Y-m-d') && ($task['status'] ?? '') !== 'completed';
                                    ?>
                                        <tr class="<?php echo $isOverdue ? 'table-danger' : ''; ?>">
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($task['taskName'] ?? 'Task #' . $task['operationalTaskID']); ?></div>
                                                <small class="text-muted">Instance #<?php echo $task['instanceNumber'] ?? '1'; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($task['templateName'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($task['assigneeName'])): ?>
                                                    <?php echo htmlspecialchars($task['assigneeName']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Unassigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                                    <?php echo !empty($task['dueDate']) ? date('M d, Y', strtotime($task['dueDate'])) : 'N/A'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    $status = $task['status'] ?? 'pending';
                                                    echo $status === 'completed' ? 'success' :
                                                        ($status === 'in_progress' ? 'info' :
                                                        ($status === 'overdue' || $isOverdue ? 'danger' : 'warning'));
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($task['actualDuration'])): ?>
                                                    <?php echo number_format($task['actualDuration'], 2); ?> hrs
                                                <?php elseif (!empty($task['estimatedDuration'])): ?>
                                                    <span class="text-muted">Est: <?php echo number_format($task['estimatedDuration'], 2); ?> hrs</span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="?s=admin&ss=operational&p=tasks&action=view&id=<?php echo $task['operationalTaskID']; ?>"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="?s=admin&ss=operational&p=tasks&action=edit&id=<?php echo $task['operationalTaskID']; ?>"
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
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
    if (document.getElementById('tasksTable')) {
        $('#tasksTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[3, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ tasks per page"
            }
        });
    }

    // Filter handlers
    document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
    document.getElementById('functionalAreaFilter')?.addEventListener('change', applyFilters);
    document.getElementById('dateFrom')?.addEventListener('change', applyFilters);
    document.getElementById('dateTo')?.addEventListener('change', applyFilters);
});

function applyFilters() {
    const url = new URL(window.location);
    const status = document.getElementById('statusFilter').value;
    const functionalArea = document.getElementById('functionalAreaFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

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

    if (dateFrom) {
        url.searchParams.set('dateFrom', dateFrom);
    }

    if (dateTo) {
        url.searchParams.set('dateTo', dateTo);
    }

    window.location.href = url.toString();
}

function exportTasks() {
    // Build export URL with current filters
    const url = new URL(window.location);
    url.searchParams.set('export', 'csv');
    window.location.href = url.toString();
}

// Event delegation for tasks
document.addEventListener('click', function(e) {
    const action = e.target.closest('[data-action]')?.getAttribute('data-action');
    if (!action) return;

    if (action === 'export-tasks') {
        exportTasks();
    }
});
</script>

