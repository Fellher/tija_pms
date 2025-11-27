<?php
/**
 * Kanban Board View for Project Tasks
 * Drag-and-drop task management with status columns
 *
 * @package Tija Practice Management System
 * @subpackage Projects
 * @version 2.0
 */

// Use already-loaded project tasks from project.php
// If not available, try to load them
if (!isset($projectTasks) || !$projectTasks) {
    if (isset($projectID)) {
        // Load tasks using the correct Projects method
        // Check if filtering by billing cycle
        $taskFilters = ['projectID' => $projectID, 'Suspended' => 'N'];
        if (isset($_GET['billingCycleID']) && !empty($_GET['billingCycleID'])) {
            $taskFilters['billingCycleID'] = intval($_GET['billingCycleID']);
        }

        $projectTasks = Projects::project_tasks($taskFilters, false, $DBConn);
        if (!$projectTasks) {
            $projectTasks = [];
        }
    } else {
        $projectTasks = [];
    }
}

// Get project details if not already loaded
if (!isset($projectDetails) && isset($projectID)) {
    $projectDetails = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
}

// Organize tasks by status
$tasksByStatus = [
    'todo' => [],
    'in_progress' => [],
    'review' => [],
    'done' => []
];

if ($projectTasks && is_array($projectTasks)) {
    foreach ($projectTasks as $task) {
        // Check various possible status field names
        $status = strtolower($task->taskStatus ?? $task->status ?? $task->taskStatusName ?? 'todo');

        // Map status to Kanban columns
        if (in_array($status, ['todo', 'pending', 'not_started', 'new'])) {
            $tasksByStatus['todo'][] = $task;
        } elseif (in_array($status, ['in_progress', 'active', 'working', 'ongoing'])) {
            $tasksByStatus['in_progress'][] = $task;
        } elseif (in_array($status, ['review', 'in_review', 'testing', 'qa'])) {
            $tasksByStatus['review'][] = $task;
        } elseif (in_array($status, ['done', 'completed', 'finished', 'closed'])) {
            $tasksByStatus['done'][] = $task;
        } else {
            // Default to todo
            $tasksByStatus['todo'][] = $task;
        }
    }
}

// Priority colors
$priorityColors = [
    'low' => 'info',
    'medium' => 'warning',
    'high' => 'danger',
    'critical' => 'dark'
];
?>

<!-- Kanban Board Container -->
<div class="kanban-board-wrapper">
    <!-- Kanban Controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="fas fa-columns me-2"></i>Task Board</h5>
            <small class="text-muted">Drag and drop tasks between columns</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddTaskModal()">
                <i class="fas fa-plus me-1"></i>Add Task
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleFilters()">
                <i class="fas fa-filter me-1"></i>Filters
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="exportKanban()"><i class="fas fa-download me-2"></i>Export Board</a></li>
                    <li><a class="dropdown-item" href="#" onclick="printKanban()"><i class="fas fa-print me-2"></i>Print Board</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="switchToListView()"><i class="fas fa-list me-2"></i>List View</a></li>
                    <li><a class="dropdown-item" href="#" onclick="switchToGanttView()"><i class="fas fa-chart-gantt me-2"></i>Gantt View</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters (Collapsible) -->
    <div class="card mb-3 d-none" id="kanbanFilters">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <select class="form-select form-select-sm" id="filterAssignee">
                        <option value="">All Team Members</option>
                        <!-- Populate with team members -->
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" id="filterPriority">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <?php
                // Check if project is recurring and add billing cycle filter
                $isRecurring = false;
                $billingCycles = [];
                if (isset($projectDetails)) {
                    if (isset($projectDetails->isRecurring) && $projectDetails->isRecurring === 'Y') {
                        $isRecurring = true;
                    } elseif (isset($projectDetails->projectType) && $projectDetails->projectType === 'recurrent') {
                        $isRecurring = true;
                    }
                }

                if ($isRecurring && isset($projectID)) {
                    $billingCycles = Projects::get_billing_cycles(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);
                }
                ?>
                <?php if ($isRecurring && $billingCycles && count($billingCycles) > 0): ?>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" id="filterBillingCycle">
                        <option value="">All Billing Cycles</option>
                        <?php foreach ($billingCycles as $cycle): ?>
                            <option value="<?= $cycle->billingCycleID ?>">
                                Cycle #<?= $cycle->cycleNumber ?>
                                (<?= date('d M', strtotime($cycle->cycleStartDate)) ?> - <?= date('d M', strtotime($cycle->cycleEndDate)) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" id="filterDueDate" placeholder="Due date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" id="filterStartDate" placeholder="Start date">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-primary w-100" onclick="applyFilters()">
                        <i class="fas fa-filter me-1"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="kanban-board">
        <div class="row g-3 mx-0">
            <!-- To Do Column -->
            <div class="col-xl-3 col-md-6">
                <div class="kanban-column" data-status="todo">
                    <div class="kanban-column-header bg-secondary-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-circle text-secondary me-2"></i>To Do
                            </h6>
                            <span class="badge bg-secondary"><?= count($tasksByStatus['todo']) ?></span>
                        </div>
                    </div>
                    <div class="kanban-column-body" id="kanban-todo" data-status="todo">
                        <?php foreach ($tasksByStatus['todo'] as $task): ?>
                            <?php include 'kanban_task_card.php'; ?>
                        <?php endforeach; ?>

                        <?php if (empty($tasksByStatus['todo'])): ?>
                            <div class="empty-column-message text-center py-4">
                                <i class="fas fa-inbox text-muted fs-24"></i>
                                <p class="text-muted mt-2 mb-0">No tasks</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="col-xl-3 col-md-6">
                <div class="kanban-column" data-status="in_progress">
                    <div class="kanban-column-header bg-primary-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-circle text-primary me-2"></i>In Progress
                            </h6>
                            <span class="badge bg-primary"><?= count($tasksByStatus['in_progress']) ?></span>
                        </div>
                    </div>
                    <div class="kanban-column-body" id="kanban-in_progress" data-status="in_progress">
                        <?php foreach ($tasksByStatus['in_progress'] as $task): ?>
                            <?php include 'kanban_task_card.php'; ?>
                        <?php endforeach; ?>

                        <?php if (empty($tasksByStatus['in_progress'])): ?>
                            <div class="empty-column-message text-center py-4">
                                <i class="fas fa-inbox text-muted fs-24"></i>
                                <p class="text-muted mt-2 mb-0">No tasks</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Review Column -->
            <div class="col-xl-3 col-md-6">
                <div class="kanban-column" data-status="review">
                    <div class="kanban-column-header bg-warning-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-circle text-warning me-2"></i>In Review
                            </h6>
                            <span class="badge bg-warning"><?= count($tasksByStatus['review']) ?></span>
                        </div>
                    </div>
                    <div class="kanban-column-body" id="kanban-review" data-status="review">
                        <?php foreach ($tasksByStatus['review'] as $task): ?>
                            <?php include 'kanban_task_card.php'; ?>
                        <?php endforeach; ?>

                        <?php if (empty($tasksByStatus['review'])): ?>
                            <div class="empty-column-message text-center py-4">
                                <i class="fas fa-inbox text-muted fs-24"></i>
                                <p class="text-muted mt-2 mb-0">No tasks</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Done Column -->
            <div class="col-xl-3 col-md-6">
                <div class="kanban-column" data-status="done">
                    <div class="kanban-column-header bg-success-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-circle text-success me-2"></i>Done
                            </h6>
                            <span class="badge bg-success"><?= count($tasksByStatus['done']) ?></span>
                        </div>
                    </div>
                    <div class="kanban-column-body" id="kanban-done" data-status="done">
                        <?php foreach ($tasksByStatus['done'] as $task): ?>
                            <?php include 'kanban_task_card.php'; ?>
                        <?php endforeach; ?>

                        <?php if (empty($tasksByStatus['done'])): ?>
                            <div class="empty-column-message text-center py-4">
                                <i class="fas fa-inbox text-muted fs-24"></i>
                                <p class="text-muted mt-2 mb-0">No tasks</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Initialize Kanban board with drag-and-drop
document.addEventListener('DOMContentLoaded', function() {
    initializeKanbanBoard();
});

function initializeKanbanBoard() {
    const columns = ['todo', 'in_progress', 'review', 'done'];

    columns.forEach(status => {
        const el = document.getElementById(`kanban-${status}`);
        if (el) {
            new Sortable(el, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'kanban-ghost',
                dragClass: 'kanban-drag',
                handle: '.kanban-task-card',
                onEnd: function(evt) {
                    handleTaskMove(evt);
                }
            });
        }
    });
}

function handleTaskMove(evt) {
    const taskCard = evt.item;
    const taskID = taskCard.dataset.taskId;
    const newStatus = evt.to.dataset.status;
    const oldStatus = evt.from.dataset.status;

    if (newStatus === oldStatus) return;

    // Update task status via AJAX
    updateTaskStatus(taskID, newStatus, oldStatus);
}

function updateTaskStatus(taskID, newStatus, oldStatus) {
    const formData = new FormData();
    formData.append('taskID', taskID);
    formData.append('newStatus', newStatus);

    fetch('<?= $base ?>php/scripts/projects/update_task_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update badge counts
            updateColumnCounts();

            // Show toast notification
            showToast(`Task moved to ${newStatus.replace('_', ' ')}`, 'success');

            // Update task card appearance
            updateTaskCardStatus(taskID, newStatus);
        } else {
            showToast('Failed to update task status: ' + data.message, 'danger');
            // Revert the move
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating task status', 'danger');
        location.reload();
    });
}

function updateColumnCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const status = column.dataset.status;
        const count = column.querySelectorAll('.kanban-task-card').length;
        column.querySelector('.badge').textContent = count;
    });
}

function updateTaskCardStatus(taskID, newStatus) {
    const taskCard = document.querySelector(`[data-task-id="${taskID}"]`);
    if (taskCard) {
        taskCard.dataset.status = newStatus;
        // Update visual indicators as needed
    }
}

function openAddTaskModal() {
    // Open task creation modal
    const modal = new bootstrap.Modal(document.getElementById('addTaskModal'));
    modal.show();
}

function toggleFilters() {
    const filters = document.getElementById('kanbanFilters');
    filters.classList.toggle('d-none');
}

function applyFilters() {
    const assignee = document.getElementById('filterAssignee')?.value || '';
    const priority = document.getElementById('filterPriority')?.value || '';
    const dueDate = document.getElementById('filterDueDate')?.value || '';
    const startDate = document.getElementById('filterStartDate')?.value || '';
    const billingCycle = document.getElementById('filterBillingCycle')?.value || '';

    // Filter tasks based on criteria
    document.querySelectorAll('.kanban-task-card').forEach(card => {
        let show = true;

        if (assignee && card.dataset.assignee !== assignee) show = false;
        if (priority && card.dataset.priority !== priority) show = false;
        if (dueDate && card.dataset.dueDate !== dueDate) show = false;
        if (startDate && card.dataset.startDate !== startDate) show = false;
        if (billingCycle && card.dataset.billingCycle !== billingCycle) show = false;

        card.style.display = show ? 'block' : 'none';

        // Update column counts after filtering
        updateColumnCounts();
    });

    // Show message if no tasks match
    const visibleTasks = document.querySelectorAll('.kanban-task-card[style="display: block;"]').length;
    if (visibleTasks === 0) {
        if (typeof showToast === 'function') {
            showToast('No tasks match the selected filters', 'info');
        }
    }
}

function exportKanban() {
    window.location.href = '<?= $base ?>php/scripts/projects/export_kanban.php?projectID=<?= $projectID ?>';
}

function printKanban() {
    window.print();
}

function switchToListView() {
    window.location.href = '?s=user&ss=projects&p=project&projectID=<?= $projectID ?>&view=list';
}

function switchToGanttView() {
    window.location.href = '?s=user&ss=projects&p=project&projectID=<?= $projectID ?>&view=gantt';
}

function showToast(message, type = 'info') {
    // Simple toast implementation (can be enhanced with library)
    alert(message);
}
</script>

<style>
.kanban-board-wrapper {
    margin-bottom: 20px;
    width: 100% !important;
    max-width: 100% !important;
    padding: 0;
}

.kanban-board {
    min-height: 600px;
    width: 100% !important;
    max-width: 100% !important;
    padding: 0;
}

.kanban-column {
    background: #f8f9fa;
    border-radius: 8px;
    height: 100%;
    min-width: 0; /* Prevents flex item from overflowing */
    width: 100%;
    max-width: 100%;
}

.kanban-column-header {
    padding: 12px 15px;
    border-radius: 8px 8px 0 0;
    border-bottom: 2px solid rgba(0,0,0,0.05);
}

.kanban-column-body {
    padding: 15px;
    min-height: 500px;
    max-height: 70vh;
    overflow-y: auto;
    width: 100%;
}

/* Ensure columns use full available width */
.kanban-board .row {
    width: 100% !important;
    max-width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

.kanban-board .row > [class*="col-"] {
    padding-left: 12px;
    padding-right: 12px;
    width: 100%;
    max-width: 100%;
}

/* Override Bootstrap column constraints */
@media (min-width: 992px) {
    .kanban-board .row > .col-xl-3 {
        flex: 0 0 25% !important;
        max-width: 25% !important;
        width: 25% !important;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .kanban-board .row > .col-md-6 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
    }
}

@media (max-width: 767px) {
    .kanban-board .row > [class*="col-"] {
        flex: 0 0 100% !important;
        max-width: 100% !important;
        width: 100% !important;
    }
}

.kanban-column-body::-webkit-scrollbar {
    width: 6px;
}

.kanban-column-body::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.kanban-column-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.kanban-ghost {
    opacity: 0.4;
    background: #e9ecef;
}

.kanban-drag {
    opacity: 1;
    cursor: move;
}

.empty-column-message {
    opacity: 0.5;
}

@media print {
    .kanban-board {
        display: flex;
        flex-wrap: nowrap;
    }

    .kanban-column {
        page-break-inside: avoid;
    }
}
</style>

