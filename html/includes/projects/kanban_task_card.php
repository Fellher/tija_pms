<?php
/**
 * Kanban Task Card Component
 * Individual task card for drag-and-drop interface
 *
 * @var object $task Task object with all details
 */

// Get task ID (try different field names)
$taskID = $task->projectTaskID ?? $task->taskID ?? 0;
$taskName = $task->projectTaskName ?? $task->taskName ?? 'Untitled Task';
$taskDescription = $task->taskDescription ?? '';
$taskStatus = strtolower($task->status ?? $task->taskStatus ?? 'todo');
$assigneeID = $task->assigneeID ?? $task->assignedTo ?? '';
$assigneeName = $task->assigneeName ?? 'Unassigned';
$taskDeadline = $task->taskDeadline ?? $task->dueDate ?? null;
$priority = $task->priority ?? 'medium';

$priorityColor = $priorityColors[$priority] ?? 'secondary';
$isOverdue = $taskDeadline && strtotime($taskDeadline) < time() && !in_array($taskStatus, ['done', 'completed', 'closed']);
?>

<?php
// Get billing cycle info if task has billingCycleID
$taskBillingCycle = null;
if (isset($task->billingCycleID) && $task->billingCycleID) {
    $taskBillingCycle = Projects::get_billing_cycles(['billingCycleID' => $task->billingCycleID], true, $DBConn);
}
?>
<div class="kanban-task-card mb-3"
    data-task-id="<?= $taskID ?>"
    data-status="<?= $taskStatus ?>"
    data-priority="<?= $priority ?>"
    data-assignee="<?= $assigneeID ?>"
    data-due-date="<?= $taskDeadline ?? '' ?>"
    data-billing-cycle="<?= $task->billingCycleID ?? '' ?>"
    data-start-date="<?= $task->taskStart ?? '' ?>">

    <div class="card">
        <div class="card-body p-3">
            <!-- Task Header -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0 task-title" onclick="openTaskDetails(<?= $taskID ?>)" style="cursor: pointer;">
                    <?= htmlspecialchars($taskName) ?>
                </h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light p-0" type="button" data-bs-toggle="dropdown" style="width: 24px; height: 24px;">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="editTask(<?= $taskID ?>)">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="duplicateTask(<?= $taskID ?>)">
                            <i class="fas fa-copy me-2"></i>Duplicate
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask(<?= $taskID ?>)">
                            <i class="fas fa-trash me-2"></i>Delete
                        </a></li>
                    </ul>
                </div>
            </div>

            <!-- Task Description (truncated) -->
            <?php if (!empty($taskDescription)): ?>
                <p class="text-muted small mb-2 task-description">
                    <?= mb_strlen($taskDescription) > 80
                        ? htmlspecialchars(mb_substr($taskDescription, 0, 80)) . '...'
                        : htmlspecialchars($taskDescription) ?>
                </p>
            <?php endif; ?>

            <!-- Task Metadata -->
            <div class="d-flex flex-wrap gap-1 mb-2">
                <!-- Priority Badge -->
                <span class="badge bg-<?= $priorityColor ?>-transparent">
                    <?= ucfirst($priority) ?>
                </span>

                <!-- Task ID -->
                <span class="badge bg-light text-dark">
                    #<?= $taskID ?>
                </span>

                <!-- Subtasks (if any) -->
                <?php if (isset($task->subtaskCount) && $task->subtaskCount > 0): ?>
                    <span class="badge bg-info-transparent">
                        <i class="fas fa-tasks me-1"></i><?= $task->subtaskCompleted ?? 0 ?>/<?= $task->subtaskCount ?>
                    </span>
                <?php endif; ?>

                <!-- Comments (if any) -->
                <?php if (isset($task->commentCount) && $task->commentCount > 0): ?>
                    <span class="badge bg-secondary-transparent">
                        <i class="fas fa-comment me-1"></i><?= $task->commentCount ?>
                    </span>
                <?php endif; ?>

                <!-- Attachments (if any) -->
                <?php if (isset($task->attachmentCount) && $task->attachmentCount > 0): ?>
                    <span class="badge bg-secondary-transparent">
                        <i class="fas fa-paperclip me-1"></i><?= $task->attachmentCount ?>
                    </span>
                <?php endif; ?>

                <!-- Billing Cycle Badge (for recurring projects) -->
                <?php if ($taskBillingCycle): ?>
                    <span class="badge bg-info-transparent" title="Billing Cycle #<?= $taskBillingCycle->cycleNumber ?>">
                        <i class="ri-repeat-line me-1"></i>Cycle #<?= $taskBillingCycle->cycleNumber ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Task Footer -->
            <div class="d-flex justify-content-between align-items-center mt-2">
                <!-- Assignee -->
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xs bg-primary-transparent me-1" title="<?= htmlspecialchars($assigneeName) ?>">
                        <span class="avatar-initials">
                            <?= strtoupper(substr($assigneeName, 0, 1)) ?>
                        </span>
                    </div>
                    <small class="text-muted"><?= htmlspecialchars($assigneeName) ?></small>
                </div>

                <!-- Due Date -->
                <?php if ($taskDeadline): ?>
                    <div>
                        <small class="<?= $isOverdue ? 'text-danger' : 'text-muted' ?>">
                            <i class="fas fa-calendar me-1"></i>
                            <?= date('M d', strtotime($taskDeadline)) ?>
                            <?php if ($isOverdue): ?>
                                <i class="fas fa-exclamation-triangle ms-1"></i>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Progress Bar (if task has progress tracking) -->
            <?php if (isset($task->progress) && $task->progress > 0): ?>
                <div class="mt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: <?= $task->progress ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.kanban-task-card {
    cursor: move;
    transition: all 0.2s ease;
    width: 100%;
    max-width: 100%;
}

.kanban-task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.kanban-task-card .card {
    border: 1px solid #dee2e6;
    width: 100%;
    max-width: 100%;
}

.task-title:hover {
    color: #0d6efd;
    text-decoration: underline;
}

.avatar-xs {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 10px;
}

.avatar-initials {
    font-weight: 600;
}
</style>

