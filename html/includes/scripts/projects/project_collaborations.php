<?php
/**
 * Project Collaborations - Kanban Board
 * Draggable task management interface for project collaborations
 * @package    Tija CRM
 * @subpackage Project Collaborations
 */

// Get project details
$project = Projects::projects_full(array('projectID' => $projectID), true, $DBConn);

if (!$project) {
    echo '<div class="alert alert-danger">Project not found.</div>';
    return;
}

// Get task statuses
$taskStatuses = Projects::task_status(array(), false, $DBConn);

// Get project tasks
$projectTasks = Projects::projects_tasks(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Get assigned tasks
$assignedTasks = Projects::assigned_task(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Get time logs for this project
$timeLogs = TimeAttendance::project_tasks_time_logs_between_dates(
    array('projectID' => $projectID, 'Suspended' => 'N'),
    date('Y-01-01'), date('Y-m-d'), false, $DBConn
);

// Get employee information
$employeeInfo = array();
if ($assignedTasks && is_array($assignedTasks)) {
    foreach ($assignedTasks as $assignment) {
        if (!isset($employeeInfo[$assignment->userID])) {
            $employee = Employee::employees(array('ID' => $assignment->userID), true, $DBConn);
            $employeeInfo[$assignment->userID] = $employee;
        }
    }
}

// Organize tasks by status
$tasksByStatus = array();
if ($taskStatuses && is_array($taskStatuses)) {
    foreach ($taskStatuses as $status) {
        $tasksByStatus[$status->taskStatusID] = [
            'status' => $status,
            'tasks' => array()
        ];
    }
}

// Populate tasks by status
if ($projectTasks && is_array($projectTasks)) {
    foreach ($projectTasks as $task) {
        // If task has no statusID or statusID is null/empty, assign to "Not Started" (statusID = 1)
        $statusID = $task->taskStatusID;
        if (empty($statusID) || $statusID === null) {
            $statusID = 1; // Default to "Not Started" status
        }

        // If status doesn't exist in our status list, default to "Not Started"
        if (!isset($tasksByStatus[$statusID])) {
            $statusID = 1; // Default to "Not Started" status
        }

        // Get task assignments
        $taskAssignments = array_filter($assignedTasks, function($assignment) use ($task) {
            return $assignment->projectTaskID == $task->projectTaskID;
        });

        // Get task time logs
        $taskHours = 0;
        if ($timeLogs && is_array($timeLogs)) {
            foreach ($timeLogs as $log) {
                if ($log->projectTaskID == $task->projectTaskID) {
                    $taskHours += Utility::time_to_seconds($log->taskDuration) / 3600;
                }
            }
        }

        // Get task assignees
        $taskAssignees = array();
        foreach ($taskAssignments as $assignment) {
            if (isset($employeeInfo[$assignment->userID])) {
                $taskAssignees[] = $employeeInfo[$assignment->userID];
            }
        }

        $tasksByStatus[$statusID]['tasks'][] = [
            'task' => $task,
            'assignees' => $taskAssignees,
            'hours_logged' => $taskHours,
            'assignments' => $taskAssignments
        ];
    }
}

// Calculate project metrics
$totalTasks = is_array($projectTasks) ? count($projectTasks) : 0;
$completedTasks = 0;
$inProgressTasks = 0;
$totalHours = 0;

if ($projectTasks && is_array($projectTasks)) {
    foreach ($projectTasks as $task) {
        if ($task->progress >= 100) {
            $completedTasks++;
        } elseif ($task->progress > 0) {
            $inProgressTasks++;
        }
    }
}

if ($timeLogs && is_array($timeLogs)) {
    foreach ($timeLogs as $log) {
        $totalHours += Utility::time_to_seconds($log->taskDuration) / 3600;
    }
}

// Project data loaded successfully
?>

<div class="container-fluid">
    <!-- Project Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($project->projectName) ?></h4>
                            <p class="text-muted mb-0"><?= htmlspecialchars($project->projectCode) ?> - Task Collaborations</p>
                            <small class="text-muted">
                                Project Status: <span class="badge bg-info"><?= $project->projectStatus ?  htmlspecialchars($project->projectStatus) : "innactive" ?></span>
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h5 class="mb-0"><?= $totalTasks ?></h5>
                                    <small class="text-muted">Total Tasks</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="mb-0"><?= $completedTasks ?></h5>
                                    <small class="text-muted">Completed</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="mb-0"><?= number_format($totalHours, 1) ?></h5>
                                    <small class="text-muted">Hours Logged</small>
                                </div>
                            </div>
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
                    <h5 class="card-title mb-0">
                        <i class="ri-drag-move-line me-2"></i>Project Task Collaborations
                        <small class="text-muted ms-2">Drag tasks between columns to update status</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="kanban-board" id="projectKanbanBoard">
                        <?php if ($taskStatuses && is_array($taskStatuses)): ?>
                            <?php foreach ($taskStatuses as $status): ?>
                                <div class="kanban-column" data-status-id="<?= $status->taskStatusID ?>">
                                    <div class="kanban-column-header">
                                        <h6 class="mb-0">
                                            <?= htmlspecialchars($status->taskStatusName) ?>
                                            <span class="badge bg-light text-dark ms-2">
                                                <?= isset($tasksByStatus[$status->taskStatusID]) ? count($tasksByStatus[$status->taskStatusID]['tasks']) : 0 ?>
                                            </span>
                                        </h6>
                                        <!-- <small class="text-muted"><?= htmlspecialchars($status->taskStatusDescription) ?></small> -->
                                    </div>
                                    <div class="kanban-column-body" data-status-id="<?= $status->taskStatusID ?>">
                                        <?php if (isset($tasksByStatus[$status->taskStatusID]['tasks'])): ?>
                                            <?php foreach ($tasksByStatus[$status->taskStatusID]['tasks'] as $taskData): ?>
                                                <?php
                                                $task = $taskData['task'];
                                                $assignees = $taskData['assignees'];
                                                $hoursLogged = $taskData['hours_logged'];
                                                ?>
                                                <div class="kanban-card"
                                                     draggable="true"
                                                     data-task-id="<?= $task->projectTaskID ?>"
                                                     data-current-status="<?= $status->taskStatusID ?>"
                                                     data-task-name="<?= htmlspecialchars($task->projectTaskName) ?>"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#manage_project_task"
                                                     data-projectTaskID="<?= $task->projectTaskID ?>"
                                                     data-projectID="<?= $projectID ?>"
                                                     data-taskStatusID="<?= $status->taskStatusID ?>"
                                                     data-taskName="<?= htmlspecialchars($task->projectTaskName) ?>"
                                                     data-taskStart="<?= $task->taskStart ?>"
                                                     data-taskDeadline="<?= $task->taskDeadline ?>"
                                                     data-hoursAllocated="<?= $task->hoursAllocated ?>"
                                                     data-taskWeighting="<?= $task->taskWeighting ?>">

                                                    <!-- Task Header -->
                                                    <div class="kanban-card-header">
                                                        <h6 class="kanban-card-title"><?= htmlspecialchars($task->projectTaskName) ?></h6>
                                                        <div class="kanban-card-actions">
                                                            <span class="badge <?= $task->progress >= 100 ? 'bg-success' : ($task->progress > 0 ? 'bg-warning' : 'bg-secondary') ?>">
                                                                <?= $task->progress ?>%
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Task Progress -->
                                                    <div class="kanban-card-progress">
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar"
                                                                 role="progressbar"
                                                                 style="width: <?= $task->progress ?>%"
                                                                 aria-valuenow="<?= $task->progress ?>"
                                                                 aria-valuemin="0"
                                                                 aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Task Assignees -->
                                                    <div class="kanban-card-assignees">
                                                        <?php if (!empty($assignees)): ?>
                                                            <div class="assignee-avatars">
                                                                <?php foreach ($assignees as $assignee): ?>
                                                                    <div class="avatar avatar-xs" title="<?= htmlspecialchars($assignee->employeeName) ?>">
                                                                        <span class="avatar-initials bg-primary text-white">
                                                                            <?= strtoupper(substr($assignee->employeeName, 0, 2)) ?>
                                                                        </span>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-muted small">
                                                                <i class="ri-user-line me-1"></i>Unassigned
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Task Details -->
                                                    <div class="kanban-card-details">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <small class="text-muted">
                                                                    <i class="ri-time-line me-1"></i>
                                                                    <?= number_format($hoursLogged, 1) ?>h
                                                                </small>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <small class="text-muted">
                                                                    <?= $task->hoursAllocated ? $task->hoursAllocated . 'h' : 'No limit' ?>
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <?php if ($task->taskDeadline): ?>
                                                            <?php
                                                            $deadline = new DateTime($task->taskDeadline);
                                                            $now = new DateTime();
                                                            $isOverdue = $deadline < $now && $task->progress < 100;
                                                            $isDueSoon = $deadline <= $now->modify('+3 days') && $task->progress < 100;
                                                            ?>
                                                            <div class="mt-2">
                                                                <small class="<?= $isOverdue ? 'text-danger' : ($isDueSoon ? 'text-warning' : 'text-muted') ?>">
                                                                    <i class="ri-calendar-line me-1"></i>
                                                                    <?= $deadline->format('M d, Y') ?>
                                                                    <?php if ($isOverdue): ?>
                                                                        <i class="ri-alert-line ms-1"></i>
                                                                    <?php elseif ($isDueSoon): ?>
                                                                        <i class="ri-time-line ms-1"></i>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ($task->taskWeighting): ?>
                                                            <div class="mt-1">
                                                                <small class="text-info">
                                                                    <i class="ri-weight-line me-1"></i>
                                                                    <?= $task->taskWeighting ?>% Weight
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Task Phase -->
                                                    <?php if ($task->projectPhaseName): ?>
                                                        <div class="kanban-card-phase">
                                                            <span class="badge bg-light text-dark">
                                                                <?= htmlspecialchars($task->projectPhaseName) ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
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
</div>

<!-- Custom CSS for Kanban Board -->
<style>
.kanban-board {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding: 1rem 0;
    min-height: 600px;
    width: 100%;
}

.kanban-column {
    min-width: 300px;
    flex: 1;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
}

.kanban-column-header {
    padding: 1rem;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    border-radius: 8px 8px 0 0;
}

.kanban-column-body {
    flex: 1;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    min-height: 500px;
}

.kanban-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    cursor: grab;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.kanban-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.kanban-card:active {
    cursor: grabbing;
}

.kanban-card.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.kanban-column.drag-over {
    background: #e3f2fd;
    border-color: #2196f3;
}

.kanban-card-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.kanban-card-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
    flex: 1;
    line-height: 1.3;
}

.kanban-card-actions {
    margin-left: auto;
}

.kanban-card-progress {
    margin-bottom: 0.75rem;
}

.kanban-card-assignees {
    margin-bottom: 0.75rem;
}

.assignee-avatars {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
}

.avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.kanban-card-details {
    margin-bottom: 0.75rem;
}

.kanban-card-phase {
    margin-top: 0.5rem;
}

/* Drag and Drop Visual Feedback */
.kanban-card.drag-over {
    border-color: #2196f3;
    background: #e3f2fd;
}

.kanban-column.drag-over {
    background: #e3f2fd;
    border-color: #2196f3;
}

/* Responsive Design */
@media (max-width: 768px) {
    .kanban-board {
        flex-direction: column;
    }

    .kanban-column {
        min-width: 100%;
        flex: 1;
    }
}

/* Status-specific colors */
.kanban-column[data-status-id="1"] .kanban-column-header {
    border-left: 4px solid #6c757d; /* Not Started */
}

.kanban-column[data-status-id="2"] .kanban-column-header {
    border-left: 4px solid #ffc107; /* In Progress */
}

.kanban-column[data-status-id="3"] .kanban-column-header {
    border-left: 4px solid #17a2b8; /* Review */
}

.kanban-column[data-status-id="4"] .kanban-column-header {
    border-left: 4px solid #28a745; /* Completed */
}

.kanban-column[data-status-id="5"] .kanban-column-header {
    border-left: 4px solid #dc3545; /* On Hold */
}
</style>

<!-- Drag and Drop JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kanbanBoard = document.getElementById('projectKanbanBoard');
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column-body');

    let draggedCard = null;

    // Add drag event listeners to cards
    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    // Add drop event listeners to columns
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
    });

    function handleDragStart(e) {
        draggedCard = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.outerHTML);
    }

    function handleDragEnd(e) {
        this.classList.remove('dragging');
        draggedCard = null;

        // Remove drag-over class from all columns
        columns.forEach(column => {
            column.classList.remove('drag-over');
        });
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDragEnter(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    }

    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }

    function handleDrop(e) {
        e.preventDefault();
        this.classList.remove('drag-over');

        if (draggedCard) {
            const newStatusID = this.getAttribute('data-status-id');
            const currentStatusID = draggedCard.getAttribute('data-current-status');

            // Only move if status is different
            if (newStatusID !== currentStatusID) {
                // Update the card's data attributes
                draggedCard.setAttribute('data-current-status', newStatusID);
                draggedCard.setAttribute('data-taskStatusID', newStatusID);

                // Move the card to the new column
                this.appendChild(draggedCard);

                // Update the column counts
                updateColumnCounts();

                // Show success message
                showNotification('Task status updated successfully!', 'success');

                // Here you would typically make an AJAX call to update the database
                updateTaskStatus(draggedCard.getAttribute('data-task-id'), newStatusID);
            }
        }
    }

    function updateColumnCounts() {
        const columnHeaders = document.querySelectorAll('.kanban-column-header');
        columnHeaders.forEach(header => {
            const columnBody = header.nextElementSibling;
            const taskCount = columnBody.querySelectorAll('.kanban-card').length;
            const badge = header.querySelector('.badge');
            if (badge) {
                badge.textContent = taskCount;
            }
        });
    }

    function updateTaskStatus(taskID, newStatusID) {
        // AJAX call to update task status in database
        fetch('includes/scripts/projects/update_task_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                taskID: taskID,
                statusID: newStatusID,
                projectID: '<?= $projectID ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Task status updated successfully');
            } else {
                console.error('Error updating task status:', data.message);
                showNotification('Error updating task status: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating task status', 'error');
        });
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    // Initialize column counts
    updateColumnCounts();
});
</script>

<?php
// Include the modal for task management
echo Utility::form_modal_header("manage_project_task", "projects/manage_project_task.php", "Manage Task Details", array('modal-lg', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/modals/manage_project_task_collaborations.php';
echo Utility::form_modal_footer("Save Task", "manageTsk", 'btn btn-primary btn-sm');
?>