<?php
/**
 * Client Collaborations Kanban Board
 * Comprehensive view of all client activities and tasks across different statuses
 * @package    Tija CRM
 * @subpackage Client Collaborations
 */

$clientID = isset($_GET['client_id']) ? Utility::clean_string($_GET['client_id']) : '';

// Get client information
$client = Client::clients(array('clientID' => $clientID), true, $DBConn);

// Get task statuses
$taskStatuses = Projects::task_status(array('Suspended' => 'N'), false, $DBConn);

// Get all project tasks for this client
$projectTasks = Projects::projects_tasks(array('clientID' => $clientID, 'Suspended' => 'N'), false, $DBConn);

// Get assigned tasks for this client
// $assignedTasks = Projects::assigned_task(array('clientID' => $clientID, 'Suspended' => 'N'), false, $DBConn);
$assignedTasks = array(); // Initialize to prevent undefined variable error

// Get sales cases for this client
$salesCases = Sales::sales_case_mid(array('clientID' => $clientID, 'Suspended' => 'N'), false, $DBConn);

// Get time logs for this client's projects
$timeLogs = array();
if ($projectTasks && is_array($projectTasks)) {
    foreach ($projectTasks as $task) {
        $logs = TimeAttendance::project_tasks_time_logs_between_dates(
            array('projectTaskID' => $task->projectTaskID, 'Suspended' => 'N'),
            date('Y-01-01'), date('Y-m-d'), false, $DBConn
        );
        if ($logs && is_array($logs)) {
            $timeLogs = array_merge($timeLogs, $logs);
        }
    }
}

// Organize tasks by status
$tasksByStatus = array();
if ($taskStatuses && is_array($taskStatuses)) {
    foreach ($taskStatuses as $status) {
        $tasksByStatus[$status->taskStatusID] = array(
            'status' => $status,
            'tasks' => array()
        );
    }
}

// Populate tasks by status
if ($projectTasks && is_array($projectTasks)) {
    foreach ($projectTasks as $task) {
        $statusID = $task->taskStatusID ? $task->taskStatusID : '1'; // Default to first status if none
        if (isset($tasksByStatus[$statusID])) {
            $tasksByStatus[$statusID]['tasks'][] = $task;
        }
    }
}

// Get employee information for assigned tasks
$employeeInfo = array(); // Initialize to prevent undefined variable error
// if ($assignedTasks && is_array($assignedTasks)) {
//     foreach ($assignedTasks as $assignment) {
//         if (!isset($employeeInfo[$assignment->userID])) {
//             $employee = Employee::employees(array('ID' => $assignment->userID), true, $DBConn);
//             $employeeInfo[$assignment->userID] = $employee;
//         }
//     }
// }
?>

<div class="container-fluid">
    <!-- Client Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><?= $client ? htmlspecialchars($client->clientName) : 'Client Collaborations' ?></h4>
                            <p class="text-muted mb-0"><?= $client ? htmlspecialchars($client->clientCode) : '' ?> - Kanban Board</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6"><?= isset($projectTasks) && is_array($projectTasks) ? count($projectTasks) : 0 ?> Tasks</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-kanban-line me-2"></i>Project Tasks & Activities</h5>
                    <div class="card-options">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshKanban()">
                            <i class="ri-refresh-line me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="kanban-board kanban-board-collaborations" id="kanbanBoard">
                        <?php if ($taskStatuses && is_array($taskStatuses)): ?>
                            <?php foreach ($taskStatuses as $status): ?>
                                <div class="kanban-column" data-status-id="<?= $status->taskStatusID ?>">
                                    <div class="kanban-header">
                                        <h6 class="mb-0"><?= htmlspecialchars($status->taskStatusName) ?></h6>
                                        <span class="badge bg-light text-dark"><?= isset($tasksByStatus[$status->taskStatusID]) ? count($tasksByStatus[$status->taskStatusID]['tasks']) : 0 ?></span>
                                    </div>
                                    <div class="kanban-body" data-status-id="<?= $status->taskStatusID ?>">
                                        <?php if (isset($tasksByStatus[$status->taskStatusID]['tasks'])): ?>
                                            <?php foreach ($tasksByStatus[$status->taskStatusID]['tasks'] as $task): ?>
                                                <div class="kanban-card" data-task-id="<?= $task->projectTaskID ?>" draggable="true">
                                                    <div class="card-header">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <h6 class="mb-0"><?= htmlspecialchars($task->projectTaskName) ?></h6>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                                    <i class="ri-more-2-line"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" href="#" onclick="viewTaskDetails(<?= $task->projectTaskID ?>)"><i class="ri-eye-line me-2"></i>View Details</a></li>
                                                                    <li><a class="dropdown-item" href="#" onclick="editTask(<?= $task->projectTaskID ?>)"><i class="ri-edit-line me-2"></i>Edit Task</a></li>
                                                                    <li><a class="dropdown-item" href="#" onclick="addTimeLog(<?= $task->projectTaskID ?>)"><i class="ri-time-line me-2"></i>Log Time</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="card-text small text-muted"><?= htmlspecialchars($task->taskDescription) ?></p>

                                                        <!-- Project Info -->
                                                        <div class="mb-2">
                                                            <small class="text-primary">
                                                                <i class="ri-project-line me-1"></i>
                                                                <?= htmlspecialchars($task->projectName) ?>
                                                            </small>
                                                        </div>

                                                        <!-- Progress -->
                                                        <div class="mb-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <small class="text-muted">Progress</small>
                                                                <small class="text-muted"><?= $task->progress ?>%</small>
                                                            </div>
                                                            <div class="progress" style="height: 4px;">
                                                                <div class="progress-bar" role="progressbar" style="width: <?= $task->progress ?>%" aria-valuenow="<?= $task->progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </div>

                                                        <!-- Assigned Users -->
                                                        <?php if ($assignedTasks && is_array($assignedTasks)): ?>
                                                            <?php
                                                            $taskAssignments = array_filter($assignedTasks, function($assignment) use ($task) {
                                                                return $assignment->projectTaskID == $task->projectTaskID;
                                                            });
                                                            ?>
                                                            <?php if (!empty($taskAssignments)): ?>
                                                                <div class="mb-2">
                                                                    <small class="text-muted">Assigned to:</small>
                                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                                        <?php foreach ($taskAssignments as $assignment): ?>
                                                                            <?php if (isset($employeeInfo[$assignment->userID])): ?>
                                                                                <span class="badge bg-light text-dark">
                                                                                    <i class="ri-user-line me-1"></i>
                                                                                    <?= htmlspecialchars($employeeInfo[$assignment->userID]->employeeName) ?>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <!-- Time Tracking -->
                                                        <?php
                                                        $taskTimeLogs = array_filter($timeLogs, function($log) use ($task) {
                                                            return $log->projectTaskID == $task->projectTaskID;
                                                        });
                                                        $totalHours = 0;
                                                        foreach ($taskTimeLogs as $log) {
                                                            $totalHours += Utility::time_to_seconds($log->taskDuration) / 3600;
                                                        }
                                                        ?>
                                                        <?php if ($totalHours > 0): ?>
                                                            <div class="mb-2">
                                                                <small class="text-info">
                                                                    <i class="ri-time-line me-1"></i>
                                                                    <?= number_format($totalHours, 1) ?> hrs logged
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>

                                                        <!-- Deadlines -->
                                                        <?php if ($task->taskDeadline): ?>
                                                            <div class="mb-2">
                                                                <?php
                                                                $deadline = new DateTime($task->taskDeadline);
                                                                $now = new DateTime();
                                                                $isOverdue = $deadline < $now && $task->progress < 100;
                                                                $isDueSoon = $deadline <= $now->modify('+3 days') && $task->progress < 100;
                                                                ?>
                                                                <small class="<?= $isOverdue ? 'text-danger' : ($isDueSoon ? 'text-warning' : 'text-muted') ?>">
                                                                    <i class="ri-calendar-line me-1"></i>
                                                                    Due: <?= $deadline->format('M d, Y') ?>
                                                                    <?php if ($isOverdue): ?>
                                                                        <i class="ri-alert-line ms-1"></i>
                                                                    <?php elseif ($isDueSoon): ?>
                                                                        <i class="ri-time-line ms-1"></i>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Cases Section -->
    <?php if ($salesCases && is_array($salesCases)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-2"></i>Sales Cases</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($salesCases as $salesCase): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($salesCase->salesCaseName) ?></h6>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge <?= $salesCase->probability >= 80 ? 'bg-success' : ($salesCase->probability >= 50 ? 'bg-warning' : 'bg-danger') ?>">
                                                <?= $salesCase->probability ?>% Probability
                                            </span>
                                            <small class="text-primary">KES <?= number_format($salesCase->salesCaseEstimate, 2) ?></small>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Stage: <?= htmlspecialchars($salesCase->saleStage) ?></small>
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
    <?php endif; ?>
</div>

<style>
.kanban-board {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding: 1rem 0;
}

.kanban-board-collaborations {
    width: 100%;
    max-width: none;
}

.kanban-board-collaborations .kanban-column {
    min-width: 370px;
    flex: 0 0 370px;
}

.kanban-column {
    min-width: 300px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #e9ecef;
}

.kanban-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

.kanban-header h6 {
    margin: 0;
    flex-grow: 1;
}

.kanban-body {
    min-height: 200px;
}

.kanban-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 0.75rem;
    cursor: move;
    transition: all 0.2s ease;
}

.kanban-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.kanban-card.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.kanban-card .card-header {
    padding: 0.75rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.kanban-card .card-body {
    padding: 0.75rem;
}

.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    background-color: #0d6efd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag and drop functionality
    initializeDragAndDrop();

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function initializeDragAndDrop() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-body');

    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
    });
}

function handleDragStart(e) {
    e.target.classList.add('dragging');
    e.dataTransfer.setData('text/plain', e.target.dataset.taskId);
}

function handleDragEnd(e) {
    e.target.classList.remove('dragging');
}

function handleDragOver(e) {
    e.preventDefault();
}

function handleDrop(e) {
    e.preventDefault();
    const taskId = e.dataTransfer.getData('text/plain');
    const newStatusId = e.target.dataset.statusId;

    // Update task status
    updateTaskStatus(taskId, newStatusId);

    // Move card to new column
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    if (card && e.target.classList.contains('kanban-body')) {
        e.target.appendChild(card);
        updateColumnCounts();
    }
}

function updateTaskStatus(taskId, newStatusId) {
    // Here you would typically make an AJAX call to update the task status
    console.log(`Updating task ${taskId} to status ${newStatusId}`);

    // Example AJAX call:
    /*
    fetch('update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            taskId: taskId,
            statusId: newStatusId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Task status updated successfully');
        } else {
            console.error('Failed to update task status');
        }
    })
    .catch(error => {
        console.error('Error updating task status:', error);
    });
    */
}

function updateColumnCounts() {
    const columns = document.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const statusId = column.dataset.statusId;
        const taskCount = column.querySelectorAll('.kanban-card').length;
        const badge = column.querySelector('.badge');
        if (badge) {
            badge.textContent = taskCount;
        }
    });
}

function refreshKanban() {
    location.reload();
}

function viewTaskDetails(taskId) {
    // Implement task details modal or page
    console.log('View task details:', taskId);
}

function editTask(taskId) {
    // Implement task editing functionality
    console.log('Edit task:', taskId);
}

function addTimeLog(taskId) {
    // Implement time logging functionality
    console.log('Add time log for task:', taskId);
}
</script>
