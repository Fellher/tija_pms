<?php
/**
 * My Operational Tasks - User
 *
 * View and manage assigned operational tasks
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

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

global $DBConn, $userID;

// Get filters
$status = $_GET['status'] ?? 'all';
$view = $_GET['view'] ?? 'list'; // list, kanban, calendar
$dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
$dateTo = $_GET['dateTo'] ?? date('Y-m-t');

// Get user's tasks
$whereArr = ['assigneeID' => $userID, 'Suspended' => 'N'];
if ($status !== 'all') $whereArr['status'] = $status;

$cols = ['operationalTaskID', 'templateID', 'instanceNumber', 'dueDate', 'status', 'assigneeID', 'processID', 'actualDuration', 'startDate', 'completedDate'];
$tasks = $DBConn->retrieve_db_table_rows('tija_operational_tasks', $cols, $whereArr);

// Enrich with template info
if ($tasks) {
    foreach ($tasks as &$task) {
        $template = OperationalTaskTemplate::getTemplate($task['templateID'], $DBConn);
        $task['templateName'] = $template['templateName'] ?? 'Unknown';
        $task['templateDescription'] = $template['templateDescription'] ?? '';
        $task['estimatedDuration'] = $template['estimatedDuration'] ?? null;
        $task['functionalArea'] = $template['functionalArea'] ?? null;
    }
}

$pageTitle = "My Operational Tasks";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=user">User</a></li>
                        <li class="breadcrumb-item"><a href="?s=user&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">My Tasks</li>
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
                                    return !empty($t['dueDate']) && $t['dueDate'] < date('Y-m-d') && ($t['status'] ?? '') !== 'completed';
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

    <!-- Filters and View Toggle -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group" role="group">
                                <a href="?s=user&ss=operational&p=tasks&view=list"
                                   class="btn btn-sm btn-<?php echo $view === 'list' ? 'primary' : 'outline-primary'; ?>">
                                    <i class="ri-list-check me-1"></i>List
                                </a>
                                <a href="?s=user&ss=operational&p=tasks&view=kanban"
                                   class="btn btn-sm btn-<?php echo $view === 'kanban' ? 'primary' : 'outline-primary'; ?>">
                                    <i class="ri-layout-column-line me-1"></i>Kanban
                                </a>
                                <a href="?s=user&ss=operational&p=tasks&view=calendar"
                                   class="btn btn-sm btn-<?php echo $view === 'calendar' ? 'primary' : 'outline-primary'; ?>">
                                    <i class="ri-calendar-line me-1"></i>Calendar
                                </a>
                            </div>
                            <select class="form-select" id="statusFilter" style="width: 150px;">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="overdue" <?php echo $status === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                            </select>
                            <?php echo renderHelpPopover('Task Status', 'Pending: Task created but not started. In Progress: Task is being worked on. Completed: Task finished with all checklist items done. Overdue: Task past due date.', 'top'); ?>
                            <input type="date" class="form-control" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>" style="width: 150px;">
                            <input type="date" class="form-control" id="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>" style="width: 150px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Display -->
    <div class="row">
        <div class="col-12">
            <?php if ($view === 'kanban'): ?>
                <!-- Kanban View -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-header bg-warning-transparent">
                                <h6 class="mb-0">Pending</h6>
                            </div>
                            <div class="card-body" style="min-height: 400px;">
                                <?php
                                $pendingTasks = is_array($tasks) ? array_filter($tasks, function($t) {
                                    return ($t['status'] ?? '') === 'pending';
                                }) : [];
                                ?>
                                <?php foreach ($pendingTasks as $task): ?>
                                    <div class="card mb-2 task-card" onclick="viewTask(<?php echo $task['operationalTaskID']; ?>)">
                                        <div class="card-body p-2">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($task['templateName']); ?></h6>
                                            <small class="text-muted">Due: <?php echo date('M d', strtotime($task['dueDate'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-header bg-info-transparent">
                                <h6 class="mb-0">In Progress</h6>
                            </div>
                            <div class="card-body" style="min-height: 400px;">
                                <?php
                                $inProgressTasks = is_array($tasks) ? array_filter($tasks, function($t) {
                                    return ($t['status'] ?? '') === 'in_progress';
                                }) : [];
                                ?>
                                <?php foreach ($inProgressTasks as $task): ?>
                                    <div class="card mb-2 task-card" onclick="viewTask(<?php echo $task['operationalTaskID']; ?>)">
                                        <div class="card-body p-2">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($task['templateName']); ?></h6>
                                            <small class="text-muted">Due: <?php echo date('M d', strtotime($task['dueDate'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-header bg-success-transparent">
                                <h6 class="mb-0">Completed</h6>
                            </div>
                            <div class="card-body" style="min-height: 400px;">
                                <?php
                                $completedTasks = is_array($tasks) ? array_filter($tasks, function($t) {
                                    return ($t['status'] ?? '') === 'completed';
                                }) : [];
                                ?>
                                <?php foreach ($completedTasks as $task): ?>
                                    <div class="card mb-2 task-card" onclick="viewTask(<?php echo $task['operationalTaskID']; ?>)">
                                        <div class="card-body p-2">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($task['templateName']); ?></h6>
                                            <small class="text-muted">Completed: <?php echo date('M d', strtotime($task['completedDate'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-header bg-danger-transparent">
                                <h6 class="mb-0">Overdue</h6>
                            </div>
                            <div class="card-body" style="min-height: 400px;">
                                <?php
                                $overdueTasks = is_array($tasks) ? array_filter($tasks, function($t) {
                                    return !empty($t['dueDate']) && $t['dueDate'] < date('Y-m-d') && ($t['status'] ?? '') !== 'completed';
                                }) : [];
                                ?>
                                <?php foreach ($overdueTasks as $task): ?>
                                    <div class="card mb-2 task-card border-danger" onclick="viewTask(<?php echo $task['operationalTaskID']; ?>)">
                                        <div class="card-body p-2">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($task['templateName']); ?></h6>
                                            <small class="text-danger">Due: <?php echo date('M d', strtotime($task['dueDate'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- List View -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">My Tasks</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tasks)): ?>
                            <div class="text-center py-5">
                                <i class="ri-task-line fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No Tasks Found</h5>
                                <p class="text-muted">You don't have any operational tasks assigned.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="tasksTable">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Template</th>
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
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($task['templateName'] ?? 'Task #' . $task['operationalTaskID']); ?></div>
                                                    <small class="text-muted">Instance #<?php echo $task['instanceNumber'] ?? '1'; ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($task['templateName'] ?? 'N/A'); ?></span>
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
                                                    <a href="?s=user&ss=operational&p=tasks&action=execute&id=<?php echo $task['operationalTaskID']; ?>"
                                                       class="btn btn-sm btn-primary" title="Execute">
                                                        <i class="ri-play-line me-1"></i>Execute
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('tasksTable')) {
        $('#tasksTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[2, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ tasks per page"
            }
        });
    }

    document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
    document.getElementById('dateFrom')?.addEventListener('change', applyFilters);
    document.getElementById('dateTo')?.addEventListener('change', applyFilters);
});

function applyFilters() {
    const url = new URL(window.location);
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

    if (status && status !== 'all') {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }

    if (dateFrom) {
        url.searchParams.set('dateFrom', dateFrom);
    }

    if (dateTo) {
        url.searchParams.set('dateTo', dateTo);
    }

    window.location.href = url.toString();
}

function viewTask(taskID) {
    window.location.href = '?s=user&ss=operational&p=tasks&action=execute&id=' + taskID;
}
</script>

<style>
.task-card {
    cursor: pointer;
    transition: transform 0.2s;
}
.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

